<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Convenio;
use App\Models\EntradaCanje;
use App\Models\Fiado;
use App\Models\FiadoRegistro;
use App\Models\HoraTaller;
use App\Models\Inscripcion;
use App\Models\Institucion;
use App\Models\MetodoPago;
use App\Models\Nota;
use App\Models\Taller;
use App\Services\ContratoDigitalService;
use App\Services\CorreoService;
use App\Support\Ajustes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Lo que se guardaba dos veces con un doble clic, un reintento de la red o dos
 * pestañas abiertas: el abono del fiado, lo que se apunta a mano (fiado, nota,
 * canje, clase de taller), el enlace del contrato, la membresía recuperada y
 * las anotaciones de la libreta y de los perfiles.
 *
 * «La segunda petición después de que la primera terminó» se simula llamando
 * dos veces con lo mismo: es el caso que se puede probar sin dos procesos. Lo
 * de a la vez de verdad lo resuelven las trabas (lockForUpdate), que SQLite
 * en memoria no ejerce.
 */
class DoblesEnviosTest extends CasoConCatalogos
{
    private $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
    }

    private function como()
    {
        return $this->actingAs($this->admin ??= $this->administrador());
    }

    private function efectivo(): int
    {
        return (int) MetodoPago::where('nombre', 'like', '%fectivo%')->value('id');
    }

    private function fiar(array $datos)
    {
        return $this->como()->post('/panel/fiados', $datos + ['concepto' => 'Bebida']);
    }

    // ---------- El abono ----------

    public function test_el_mismo_abono_enviado_dos_veces_se_cobra_una(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'concepto' => 'Proteína', 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'concepto' => 'Barra', 'monto' => 1500]);

        $abono = ['id_cliente' => $socio->id, 'id_metodo_pago' => $this->efectivo(), 'monto' => 1000, 'debe_visto' => 4000];

        $this->como()->post('/panel/fiados/saldar', $abono)->assertSessionHasNoErrors();
        $this->como()->post('/panel/fiados/saldar', $abono)
            ->assertSessionHasErrors(['monto' => 'La cuenta cambió mientras cobrabas: revisa y vuelve a intentar.']);

        $this->assertSame(1000, (int) Fiado::where('pagado', true)->sum('monto'));
        $this->assertSame(3000, (int) Fiado::debiendo()->sum('monto'));
    }

    /** Lo apuntado mientras se cobraba también cambia la cuenta. */
    public function test_el_abono_sobre_una_cuenta_que_cambio_no_se_cobra(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);
        $this->fiar(['nombre' => 'Visita', 'monto' => 500]);

        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo(), 'monto' => 1000, 'debe_visto' => 2000])
            ->assertSessionHasErrors('monto');

        $this->assertSame(0, Fiado::where('pagado', true)->count());

        // Con lo que de verdad debe, pasa.
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo(), 'monto' => 1000, 'debe_visto' => 2500])
            ->assertSessionHasNoErrors();

        $this->assertSame(1500, (int) Fiado::debiendo()->sum('monto'));
    }

    // ---------- Lo que se apunta a mano ----------

    public function test_el_mismo_fiado_enviado_dos_veces_se_apunta_una(): void
    {
        $datos = ['nombre' => 'Visita', 'monto' => 1000, 'concepto' => 'Agua', 'form_submit_token' => 'f1a6d0c2-0000-4000-8000-000000000001'];

        $this->fiar($datos)->assertSessionHasNoErrors();
        $this->fiar($datos)->assertSessionHasNoErrors();

        $this->assertSame(1, Fiado::count());
    }

    /** Dos aguas de verdad, con dos formularios, son dos líneas. */
    public function test_dos_fiados_iguales_de_dos_formularios_son_dos(): void
    {
        $datos = ['nombre' => 'Visita', 'monto' => 1000, 'concepto' => 'Agua'];

        $this->fiar($datos + ['form_submit_token' => 'f1a6d0c2-0000-4000-8000-000000000001']);
        $this->fiar($datos + ['form_submit_token' => 'f1a6d0c2-0000-4000-8000-000000000002']);
        // Y sin token no se adivina nada por el contenido.
        $this->fiar($datos);
        $this->fiar($datos);

        $this->assertSame(4, Fiado::count());
    }

    /** Un error de dato no deja el token pillado: corregido, se guarda. */
    public function test_un_fiado_rechazado_no_gasta_el_token(): void
    {
        $token = 'f1a6d0c2-0000-4000-8000-000000000003';

        $this->fiar(['nombre' => 'Visita', 'monto' => 0, 'form_submit_token' => $token])->assertSessionHasErrors('monto');
        $this->fiar(['nombre' => 'Visita', 'monto' => 800, 'form_submit_token' => $token])->assertSessionHasNoErrors();

        $this->assertSame(1, Fiado::count());
    }

    /** Un reintento tardío —más de los dos minutos de antes— sigue siendo el mismo. */
    public function test_el_token_sigue_valiendo_pasados_unos_minutos(): void
    {
        $datos = ['nombre' => 'Visita', 'monto' => 1000, 'form_submit_token' => 'f1a6d0c2-0000-4000-8000-000000000004'];

        $this->fiar($datos);
        $this->travel(10)->minutes();
        $this->fiar($datos);

        $this->assertSame(1, Fiado::count());
    }

    public function test_la_misma_nota_enviada_dos_veces_se_apunta_una(): void
    {
        $datos = ['texto' => 'Llamar a Juan', 'form_submit_token' => 'a0b1c2d3-0000-4000-8000-000000000001'];

        $this->como()->post('/panel/notas', $datos);
        $this->como()->post('/panel/notas', $datos);
        $this->como()->post('/panel/notas', ['texto' => 'Llamar a Juan', 'form_submit_token' => 'a0b1c2d3-0000-4000-8000-000000000002']);

        $this->assertSame(2, Nota::count());
    }

    public function test_la_misma_entrada_de_canje_enviada_dos_veces_se_anota_una(): void
    {
        $hotel = Convenio::factory()->create(['activo' => true, 'canje' => true, 'nombre' => 'Hotel del Centro']);
        $datos = ['id_convenio' => $hotel->id, 'nombre' => 'John Smith', 'form_submit_token' => 'c4a7e000-0000-4000-8000-000000000001'];

        $this->como()->post('/panel/canje', $datos)->assertSessionHas('success');
        $this->como()->post('/panel/canje', $datos);

        $this->assertSame(1, EntradaCanje::count());
    }

    public function test_la_misma_clase_de_taller_enviada_dos_veces_se_anota_una(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'rut' => '65.154.436-K', 'giro' => 'Educación']);
        $taller = Taller::create([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'precio_hora' => 30000,
            'horario' => [],
            'activo' => true,
        ]);

        $datos = ['fecha' => today()->toDateString(), 'horas' => 1, 'form_submit_token' => '7a11e400-0000-4000-8000-000000000001'];

        $this->como()->post("/panel/talleres/{$taller->uuid}/horas", $datos)->assertSessionHasNoErrors();
        $this->como()->post("/panel/talleres/{$taller->uuid}/horas", $datos);
        // Otra clase el mismo día, desde otro formulario, sí entra.
        $this->como()->post("/panel/talleres/{$taller->uuid}/horas", ['form_submit_token' => '7a11e400-0000-4000-8000-000000000002'] + $datos);

        $this->assertSame(2, HoraTaller::count());
    }

    // ---------- El contrato ----------

    /**
     * Dos envíos que se cruzan: el segundo se crea y sale mientras el primero
     * todavía no había creado el suyo. Cada uno anulaba solo lo que había al
     * empezar, y quedaban dos enlaces válidos.
     */
    public function test_dos_envios_de_contrato_cruzados_dejan_un_solo_enlace(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $correo = Mockery::mock(CorreoService::class);
        $correo->shouldReceive('enviar')->andReturn('smtp');
        $this->app->instance(CorreoService::class, $correo);

        $socio = Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'run_pasaporte' => '12.345.678-5',
            'email' => 'camila@correo.cl',
            'es_menor_edad' => false,
        ]);
        Inscripcion::factory()->create(['id_cliente' => $socio->id, 'id_membresia' => 4, 'id_estado' => 100]);
        $socio = $socio->fresh();

        $this->actingAs($this->administrador());

        $cruzado = false;
        Contrato::creating(function () use (&$cruzado, $socio) {
            if (! $cruzado) {
                $cruzado = true;
                app(ContratoDigitalService::class)->enviar($socio);
            }
        });

        app(ContratoDigitalService::class)->enviar($socio);

        $this->assertSame(2, Contrato::count());
        $this->assertSame(1, Contrato::whereNull('anulado_en')->whereNull('firmado_en')->count());
    }

    // ---------- La papelera ----------

    public function test_recuperar_dos_membresias_activas_del_mismo_socio_deja_una(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $crear = fn () => Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => today()->subDays(5),
            'fecha_vencimiento' => today()->addDays(25),
        ]);
        $una = $crear();
        $otra = $crear();
        $una->delete();
        $otra->delete();

        $this->como()->patch("/panel/papelera/inscripciones/{$una->id}/restaurar")->assertSessionHas('success');
        $this->como()->patch("/panel/papelera/inscripciones/{$otra->id}/restaurar")->assertSessionHas('error');
        // La misma dos veces: la segunda ya no la encuentra en la papelera.
        $this->como()->patch("/panel/papelera/inscripciones/{$una->id}/restaurar")->assertNotFound();

        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    // ---------- Anotaciones repetidas ----------

    public function test_deshacer_un_cobro_dos_veces_lo_anota_una(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()]);
        $fiado = Fiado::firstOrFail();

        $this->como()->patch("/panel/fiados/{$fiado->uuid}/reabrir")->assertSessionHas('success');
        $this->como()->patch("/panel/fiados/{$fiado->uuid}/reabrir")->assertSessionHas('error');

        $this->assertSame(1, FiadoRegistro::where('accion', 'reabierto')->count());
        $this->assertSame(2000, (int) Fiado::debiendo()->sum('monto'));
    }

    public function test_quitar_una_linea_dos_veces_lo_anota_una(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);
        $fiado = Fiado::firstOrFail();

        $this->como()->delete("/panel/fiados/{$fiado->uuid}");
        $this->como()->delete("/panel/fiados/{$fiado->uuid}");

        $this->assertSame(1, FiadoRegistro::where('accion', 'quitado')->count());
    }

    public function test_pasar_una_cuenta_a_un_socio_dos_veces_lo_anota_una(): void
    {
        $this->fiar(['nombre' => 'Pedro', 'monto' => 2000]);
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->como()->post('/panel/fiados/asignar', ['nombre' => 'Pedro', 'id_cliente' => $socio->id])->assertSessionHas('success');
        $this->como()->post('/panel/fiados/asignar', ['nombre' => 'Pedro', 'id_cliente' => $socio->id])->assertSessionHas('error');

        $this->assertSame(1, FiadoRegistro::where('accion', 'asignado')->count());
        $this->assertSame($socio->id, (int) Fiado::firstOrFail()->id_cliente);
    }

    /** La anotación queda en la ficha del socio y sin su nombre escrito. */
    public function test_pasar_una_cuenta_anota_a_nombre_del_socio(): void
    {
        $this->fiar(['nombre' => 'Pedro', 'monto' => 2000]);
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Pedro', 'apellido_paterno' => 'Tapia']);

        $this->como()->post('/panel/fiados/asignar', ['nombre' => 'Pedro', 'id_cliente' => $socio->id]);

        $registro = FiadoRegistro::where('accion', 'asignado')->sole();
        $this->assertSame($socio->id, (int) $registro->id_cliente);
        $this->assertNull($registro->nombre);
        $this->assertStringNotContainsString('Tapia', $registro->detalle);
    }

    /** «Barra» y «Barra (abono)» son lo mismo en lo más vendido del mesón. */
    public function test_lo_abonado_suma_con_su_cosa_en_el_informe(): void
    {
        $this->fiar(['nombre' => 'Visita', 'concepto' => 'Barra', 'monto' => 2000]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo(), 'monto' => 500]);
        $this->fiar(['nombre' => 'Otra', 'concepto' => 'Barra', 'monto' => 1000]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Otra', 'id_metodo_pago' => $this->efectivo()]);

        $props = $this->como()->get('/panel/reportes/ingresos?anio=' . now()->year)->assertOk()->viewData('page')['props'];

        $this->assertCount(1, $props['porConcepto']);
        $this->assertSame('Barra', $props['porConcepto'][0]['nombre']);
        $this->assertSame(1500, $props['porConcepto'][0]['total']);
    }

    /** Quien edita perfiles no se quita a sí mismo la llave de Perfiles. */
    public function test_no_se_quita_el_acceso_a_perfiles_de_su_propio_perfil(): void
    {
        \App\Models\Rol::whereKey(2)->update(['permisos' => json_encode(['clientes.ver', 'usuarios.ver', 'usuarios.editar'])]);
        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)
            ->put('/panel/usuarios/perfiles/2', ['permisos' => ['clientes.ver']])
            ->assertSessionHasErrors('permisos');

        $this->assertContains('usuarios.editar', \App\Models\Rol::find(2)->permisos);

        // Lo demás de su perfil sí lo puede cambiar.
        $this->actingAs($recepcion)
            ->put('/panel/usuarios/perfiles/2', ['permisos' => ['clientes.ver', 'pagos.ver', 'usuarios.ver', 'usuarios.editar']])
            ->assertSessionHasNoErrors();

        // Y el Administrador se lo puede quitar.
        $this->como()->put('/panel/usuarios/perfiles/2', ['permisos' => ['clientes.ver']])->assertSessionHasNoErrors();
        $this->assertNotContains('usuarios.editar', \App\Models\Rol::find(2)->permisos);
    }

    public function test_guardar_un_perfil_dos_veces_lo_anota_una(): void
    {
        $datos = ['permisos' => ['pagos.corregir_hoy', 'clientes.ver']];

        $this->como()->put('/panel/usuarios/perfiles/2', $datos)->assertSessionHas('success');
        $this->como()->put('/panel/usuarios/perfiles/2', $datos)->assertSessionHas('info');

        $this->assertSame(1, DB::table('cambios_de_perfil')->where('id_rol', 2)->count());
    }
}
