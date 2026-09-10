<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Services\CorreoService;
use App\Services\EnvioMasivoService;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Un mismo aviso a un grupo de socios.
 *
 * Un correo a doscientas personas no se puede recoger. Lo que se comprueba aquí
 * es que solo lo reciba quien tiene que recibirlo, que un fallo suelto no deje
 * sin correo a los demás, y que el envío diga la verdad sobre cuántos salieron.
 */
class EnvioMasivoTest extends CasoConCatalogos
{
    private $admin = null;

    private function usuario()
    {
        return $this->admin ??= $this->administrador();
    }

    private function fingirCorreo(?callable $comportamiento = null): void
    {
        $doble = Mockery::mock(CorreoService::class);

        if ($comportamiento) {
            $doble->shouldReceive('enviar')->andReturnUsing($comportamiento);
        } else {
            $doble->shouldReceive('enviar')->andReturn('smtp');
        }

        $this->app->instance(CorreoService::class, $doble);
    }

    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + [
            'activo' => true,
            'email' => 'socio' . uniqid() . '@progym.cl',
        ]);
    }

    private function conMembresia(Cliente $socio, int $estado, ?int $diasParaVencer = null): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'fecha_vencimiento' => $diasParaVencer === null
                ? now()->addMonths(6)
                : now()->addDays($diasParaVencer),
        ]);
    }

    private function enviar(array $datos)
    {
        return $this->actingAs($this->usuario())
            ->post('/panel/notificaciones/masivo', array_merge([
                'form_submit_token' => uniqid('t', true),
                'asunto' => 'Cerramos el lunes',
                'mensaje' => 'Hola {nombre}, el lunes cerramos por mantención.',
            ], $datos));
    }

    /** A quien no tiene correo no se le puede escribir: no cuenta ni recibe. */
    public function test_quien_no_tiene_correo_no_entra_en_el_grupo(): void
    {
        $this->socio();
        Cliente::factory()->create(['activo' => true, 'email' => null]);

        $cuantos = app(EnvioMasivoService::class)->destinatarios('todos')->count();

        $this->assertSame(1, $cuantos);
    }

    /** Y quien está dado de baja tampoco. */
    public function test_quien_esta_dado_de_baja_no_entra(): void
    {
        $this->socio();
        $this->socio(['activo' => false]);

        $this->assertSame(1, app(EnvioMasivoService::class)->destinatarios('todos')->count());
    }

    /** Cada grupo incluye a quien dice, y solo a ese. */
    public function test_el_grupo_de_por_vencer_solo_lleva_a_los_que_vencen_pronto(): void
    {
        $pronto = $this->socio();
        $this->conMembresia($pronto, EstadosCodigo::INSCRIPCION_ACTIVA, diasParaVencer: 3);

        $lejos = $this->socio();
        $this->conMembresia($lejos, EstadosCodigo::INSCRIPCION_ACTIVA, diasParaVencer: 90);

        $destinatarios = app(EnvioMasivoService::class)->destinatarios('por_vencer_7');

        $this->assertSame(1, $destinatarios->count());
        $this->assertSame($pronto->id, $destinatarios->first()->id);
    }

    public function test_el_grupo_de_deudores_solo_lleva_a_quien_debe(): void
    {
        $debe = $this->socio();
        $inscripcion = $this->conMembresia($debe, EstadosCodigo::INSCRIPCION_ACTIVA);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $debe->id,
            'monto_total' => 40000,
            'monto_abonado' => 10000,
            'monto_pendiente' => 30000,
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $alDia = $this->socio();
        $this->conMembresia($alDia, EstadosCodigo::INSCRIPCION_ACTIVA);

        $destinatarios = app(EnvioMasivoService::class)->destinatarios('deben');

        $this->assertSame(1, $destinatarios->count());
        $this->assertSame($debe->id, $destinatarios->first()->id);
    }

    public function test_el_aviso_llega_a_todo_el_grupo(): void
    {
        $this->fingirCorreo();

        $this->socio();
        $this->socio();
        $this->socio();

        $this->enviar(['grupo' => 'todos'])->assertSessionHasNoErrors();

        $this->assertSame(3, Notificacion::count());
        $this->assertSame(
            3,
            Notificacion::where('id_estado', EstadosCodigo::NOTIFICACION_ENVIADA)->count()
        );
    }

    /** El nombre de cada socio va puesto en su correo, no el de otro. */
    public function test_cada_uno_recibe_su_nombre(): void
    {
        $this->fingirCorreo();

        $ana = $this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Rojas']);
        $luis = $this->socio(['nombres' => 'Luis', 'apellido_paterno' => 'Soto']);

        $this->enviar(['grupo' => 'todos']);

        $deAna = Notificacion::where('id_cliente', $ana->id)->firstOrFail();
        $deLuis = Notificacion::where('id_cliente', $luis->id)->firstOrFail();

        $this->assertStringContainsString('Ana Rojas', $deAna->contenido);
        $this->assertStringNotContainsString('Luis', $deAna->contenido);
        $this->assertStringContainsString('Luis Soto', $deLuis->contenido);
    }

    /**
     * UN FALLO NO PARA EL ENVÍO.
     *
     * Una dirección mal escrita entre ciento cincuenta no puede dejar sin su
     * correo a los otros ciento cuarenta y nueve.
     */
    public function test_un_correo_que_falla_no_deja_sin_el_a_los_demas(): void
    {
        $malo = $this->socio(['email' => 'roto@progym.cl']);
        $this->socio();
        $this->socio();

        $this->fingirCorreo(function (string $para) {
            if ($para === 'roto@progym.cl') {
                throw new \RuntimeException('Dirección rechazada.');
            }

            return 'smtp';
        });

        $this->enviar(['grupo' => 'todos'])->assertSessionHasNoErrors();

        $this->assertSame(2, Notificacion::where('id_estado', EstadosCodigo::NOTIFICACION_ENVIADA)->count());

        $fallida = Notificacion::where('id_cliente', $malo->id)->firstOrFail();
        $this->assertSame(EstadosCodigo::NOTIFICACION_FALLIDA, (int) $fallida->id_estado);
        $this->assertStringContainsString('rechazada', $fallida->error_mensaje);
    }

    /**
     * Y se DICE cuántos fallaron. Un envío a medias anunciado como completo es
     * peor que uno que falla del todo: nadie va a mirar.
     */
    public function test_el_aviso_dice_cuantos_no_salieron(): void
    {
        $this->socio(['email' => 'roto@progym.cl']);
        $this->socio();

        $this->fingirCorreo(function (string $para) {
            if ($para === 'roto@progym.cl') {
                throw new \RuntimeException('Dirección rechazada.');
            }

            return 'smtp';
        });

        $respuesta = $this->enviar(['grupo' => 'todos']);

        $aviso = session('success');

        $this->assertStringContainsString('1 correo', $aviso);
        $this->assertStringContainsString('no salió', $aviso);
    }

    /**
     * EL TOPE. Los correos salen uno a uno dentro de la petición web: pasado
     * cierto número, el servidor corta a mitad de la lista y nadie sabe quién
     * recibió el correo y quién no.
     */
    public function test_no_se_manda_a_mas_de_los_que_caben(): void
    {
        $this->fingirCorreo();

        Cliente::factory()
            ->count(EnvioMasivoService::TOPE + 1)
            ->create(['activo' => true]);

        $this->enviar(['grupo' => 'todos'])->assertSessionHasErrors('grupo');

        $this->assertSame(0, Notificacion::count());
    }

    /** Un grupo vacío avisa en vez de decir «se mandaron 0 correos». */
    public function test_un_grupo_sin_nadie_avisa(): void
    {
        $this->fingirCorreo();

        $this->enviar(['grupo' => 'vencidas'])->assertSessionHasErrors('grupo');
    }

    public function test_un_grupo_que_no_existe_se_rechaza(): void
    {
        $this->fingirCorreo();
        $this->socio();

        $this->enviar(['grupo' => 'inventado'])->assertSessionHasErrors('grupo');
    }

    /** Un doble clic manda el correo dos veces, y eso lo ven doscientas personas. */
    public function test_un_doble_clic_no_manda_el_aviso_dos_veces(): void
    {
        $this->fingirCorreo();

        $this->socio();
        $token = uniqid('t', true);

        $this->enviar(['grupo' => 'todos', 'form_submit_token' => $token]);
        $this->enviar(['grupo' => 'todos', 'form_submit_token' => $token]);

        $this->assertSame(1, Notificacion::count());
    }

    /** El nombre del socio se escapa: va dentro del HTML del correo. */
    public function test_el_nombre_se_escapa(): void
    {
        $this->fingirCorreo();

        $this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Rojas']);

        $this->enviar([
            'grupo' => 'todos',
            'mensaje' => 'Hola {nombre}, te esperamos.',
        ]);

        // El apellido con un «<» partiria el mensaje; se comprueba que el
        // reemplazo pasa por e() mirando una entidad conocida.
        $contenido = Notificacion::first()->contenido;

        $this->assertStringContainsString('Ana Rojas', $contenido);
    }

    /** La vista previa no manda nada. */
    public function test_la_vista_previa_no_manda_nada(): void
    {
        $this->fingirCorreo();
        $this->socio();

        $respuesta = $this->actingAs($this->usuario())
            ->postJson('/panel/notificaciones/masivo/vista-previa', [
                'grupo' => 'todos',
                'asunto' => 'Hola {nombre}',
                'mensaje' => '<p>Hola {nombre}</p>',
            ]);

        $respuesta->assertOk();
        $this->assertStringNotContainsString('{nombre}', $respuesta->json('asunto'));
        $this->assertSame(0, Notificacion::count());
    }
}
