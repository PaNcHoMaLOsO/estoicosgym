<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Services\CorreoService;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Escribirle a un socio a mano desde el panel.
 *
 * Un correo sale UNA VEZ y no se puede recoger. Lo que se comprueba aquí es
 * justamente lo que pasa cuando algo va mal: que quede constancia, y que no
 * salga a medio escribir.
 */
class EnvioManualTest extends CasoConCatalogos
{
    private function plantilla(array $extra = []): TipoNotificacion
    {
        return TipoNotificacion::create(array_merge([
            'codigo' => 'prueba_' . uniqid(),
            'nombre' => 'Aviso de prueba',
            'descripcion' => 'Para las pruebas',
            'asunto_email' => 'Hola {nombre}',
            'plantilla_email' => '<html><body><p>Tu plan {membresia} vence el {fecha_vencimiento}.</p></body></html>',
            'activo' => true,
        ], $extra));
    }

    private function socio(array $extra = []): Cliente
    {
        $cliente = Cliente::factory()->create($extra + [
            'activo' => true,
            'email' => 'socio' . uniqid() . '@progym.cl',
        ]);

        Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
        ]);

        return $cliente->fresh();
    }

    /** El correo se manda de verdad, sin tocar la red. */
    private function fingirCorreo(?\Throwable $falla = null): void
    {
        $doble = Mockery::mock(CorreoService::class);

        if ($falla) {
            $doble->shouldReceive('enviar')->andThrow($falla);
        } else {
            $doble->shouldReceive('enviar')->andReturn('smtp');
        }

        $this->app->instance(CorreoService::class, $doble);
    }

    /**
     * El MISMO usuario en todas las llamadas.
     *
     * El turno anti-duplicado se guarda por usuario: con uno nuevo en cada
     * peticion, dos envios seguidos nunca coincidirian y la prueba del doble
     * envio pasaria sin comprobar nada.
     */
    private function usuario()
    {
        return $this->admin ??= $this->administrador();
    }

    private $admin = null;

    private function enviar(Cliente $socio, TipoNotificacion $plantilla, array $extra = [])
    {
        return $this->actingAs($this->usuario())
            ->post('/panel/notificaciones/enviar', array_merge([
                'form_submit_token' => uniqid('t', true),
                'cliente_id' => $socio->id,
                'plantilla_id' => $plantilla->id,
            ], $extra));
    }

    public function test_se_envia_y_queda_registrado(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();

        $this->enviar($socio, $this->plantilla())->assertSessionHasNoErrors();

        $notificacion = Notificacion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertSame(EstadosCodigo::NOTIFICACION_ENVIADA, (int) $notificacion->id_estado);
        $this->assertSame($socio->email, $notificacion->email_destino);
        $this->assertSame('manual', $notificacion->tipo_envio);
        $this->assertNotNull($notificacion->fecha_envio);
    }

    /**
     * EL BUG.
     *
     * Si el correo no salía, la fila se quedaba en «Pendiente» PARA SIEMPRE y
     * el motivo solo llegaba al navegador de quien lo intentó. Al día siguiente
     * nadie sabía que ese aviso no se había mandado, ni por qué.
     */
    public function test_si_el_correo_no_sale_queda_marcado_como_fallido_y_con_el_motivo(): void
    {
        $this->fingirCorreo(new \RuntimeException('El servidor de correo no responde.'));

        $socio = $this->socio();

        $this->enviar($socio, $this->plantilla())->assertSessionHasErrors('envio');

        $notificacion = Notificacion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertSame(EstadosCodigo::NOTIFICACION_FALLIDA, (int) $notificacion->id_estado);
        $this->assertStringContainsString('no responde', $notificacion->error_mensaje);
        $this->assertSame(1, (int) $notificacion->intentos);
    }

    /**
     * EL OTRO BUG.
     *
     * Dos plantillas de vencimiento usan {nombre_cliente} y el envío manual no
     * conocía esa variable: el correo salía diciendo «la membresía de
     * {nombre_cliente} vence en 30 días», con las llaves y todo. El envío
     * automático sí la rellenaba, así que la misma plantilla se veía bien por
     * un camino y rota por el otro.
     */
    public function test_la_variable_nombre_cliente_se_rellena(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();
        $plantilla = $this->plantilla([
            'asunto_email' => 'La membresía de {nombre_cliente} vence',
            'plantilla_email' => '<p>Hola {nombre_cliente}</p>',
        ]);

        $this->enviar($socio, $plantilla)->assertSessionHasNoErrors();

        $notificacion = Notificacion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertStringNotContainsString('{nombre_cliente}', $notificacion->asunto);
        $this->assertStringContainsString($socio->nombres, $notificacion->asunto);
    }

    /**
     * Un correo a medio rellenar NO sale. Que le llegue al socio diciendo «tu
     * plan {loquesea} vence» es peor que no mandarlo: el socio lo ve, el
     * gimnasio no se entera, y no se puede recoger.
     */
    public function test_una_plantilla_con_una_variable_desconocida_no_se_manda(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();
        $plantilla = $this->plantilla([
            'asunto_email' => 'Hola {inventada_por_alguien}',
        ]);

        $this->enviar($socio, $plantilla)->assertSessionHasErrors('plantilla_id');

        $this->assertSame(0, Notificacion::where('id_cliente', $socio->id)->count());
    }

    /** A quien no tiene correo no se le puede escribir. */
    public function test_no_se_escribe_a_un_socio_sin_correo(): void
    {
        $this->fingirCorreo();

        $socio = Cliente::factory()->create(['activo' => true, 'email' => null]);

        $this->enviar($socio, $this->plantilla())->assertSessionHasErrors('cliente_id');

        $this->assertSame(0, Notificacion::where('id_cliente', $socio->id)->count());
    }

    /** Y tampoco aparece en la búsqueda: ofrecerlo solo lleva a ese callejón. */
    public function test_quien_no_tiene_correo_no_sale_en_la_busqueda(): void
    {
        Cliente::factory()->create([
            'activo' => true,
            'email' => null,
            'nombres' => 'Sincorreo',
            'apellido_paterno' => 'Perez',
        ]);

        $respuesta = $this->actingAs($this->administrador())
            ->getJson('/panel/notificaciones/buscar-socio?q=Sincorreo');

        $this->assertCount(0, $respuesta->json('clientes'));
    }

    /** La nota del mesón se escapa: va dentro del HTML del correo. */
    public function test_la_nota_se_escapa(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();

        $this->enviar($socio, $this->plantilla(), [
            'nota' => 'Ojo con el <script>alert(1)</script>',
        ])->assertSessionHasNoErrors();

        $notificacion = Notificacion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertStringNotContainsString('<script>', $notificacion->contenido);
        $this->assertStringContainsString('&lt;script&gt;', $notificacion->contenido);
    }

    /** El mismo formulario enviado dos veces no manda dos correos. */
    public function test_reenviar_el_formulario_no_manda_el_correo_dos_veces(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();
        $plantilla = $this->plantilla();
        $token = uniqid('t', true);

        $this->enviar($socio, $plantilla, ['form_submit_token' => $token]);
        $this->enviar($socio, $plantilla, ['form_submit_token' => $token]);

        $this->assertSame(1, Notificacion::where('id_cliente', $socio->id)->count());
    }

    /** La vista previa compone sin mandar nada. */
    public function test_la_vista_previa_no_manda_el_correo(): void
    {
        $this->fingirCorreo();

        $socio = $this->socio();
        $plantilla = $this->plantilla();

        $respuesta = $this->actingAs($this->administrador())
            ->postJson('/panel/notificaciones/vista-previa', [
                'cliente_id' => $socio->id,
                'plantilla_id' => $plantilla->id,
            ]);

        $respuesta->assertOk();
        $this->assertSame($socio->email, $respuesta->json('destino'));
        $this->assertSame([], $respuesta->json('pendientes'));

        $this->assertSame(0, Notificacion::where('id_cliente', $socio->id)->count());
    }

    /**
     * EL BUG DEL REENVIO.
     *
     * El del panel viejo ponia ESTA notificacion en pendiente y a continuacion
     * llamaba a «enviar todas las pendientes»: reintentar un correo fallido
     * disparaba de golpe todos los demas que hubiera en cola, que es lo ultimo
     * que quiere quien solo intentaba arreglar uno.
     */
    public function test_reintentar_uno_no_manda_los_demas(): void
    {
        $this->fingirCorreo();

        $fallida = $this->notificacionEn(EstadosCodigo::NOTIFICACION_FALLIDA);
        $enCola = $this->notificacionEn(EstadosCodigo::NOTIFICACION_PENDIENTE);
        $otraEnCola = $this->notificacionEn(EstadosCodigo::NOTIFICACION_PENDIENTE);

        $this->actingAs($this->usuario())
            ->post("/panel/notificaciones/{$fallida->uuid}/reenviar")
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadosCodigo::NOTIFICACION_ENVIADA, (int) $fallida->fresh()->id_estado);

        // Las que estaban en cola siguen en cola.
        $this->assertSame(EstadosCodigo::NOTIFICACION_PENDIENTE, (int) $enCola->fresh()->id_estado);
        $this->assertSame(EstadosCodigo::NOTIFICACION_PENDIENTE, (int) $otraEnCola->fresh()->id_estado);
    }

    /** Si tampoco sale, se dice y queda anotado otra vez. */
    public function test_si_el_reintento_tampoco_sale_se_dice(): void
    {
        $this->fingirCorreo(new \RuntimeException('Sigue sin responder.'));

        $fallida = $this->notificacionEn(EstadosCodigo::NOTIFICACION_FALLIDA);

        $this->actingAs($this->usuario())
            ->post("/panel/notificaciones/{$fallida->uuid}/reenviar")
            ->assertSessionHasErrors('envio');

        $this->assertSame(EstadosCodigo::NOTIFICACION_FALLIDA, (int) $fallida->fresh()->id_estado);
        $this->assertStringContainsString('Sigue sin responder', $fallida->fresh()->error_mensaje);
    }

    /** Un correo que ya salio no se reenvia: no se recoge y no se repite. */
    public function test_no_se_reenvia_uno_que_ya_salio(): void
    {
        $this->fingirCorreo();

        $enviada = $this->notificacionEn(EstadosCodigo::NOTIFICACION_ENVIADA);

        $this->actingAs($this->usuario())
            ->post("/panel/notificaciones/{$enviada->uuid}/reenviar")
            ->assertSessionHas('error');
    }

    /** Uno que todavia no ha salido se puede parar. */
    public function test_se_cancela_uno_que_no_ha_salido(): void
    {
        $pendiente = $this->notificacionEn(EstadosCodigo::NOTIFICACION_PENDIENTE);

        $this->actingAs($this->usuario())
            ->post("/panel/notificaciones/{$pendiente->uuid}/cancelar")
            ->assertSessionHasNoErrors();

        $this->assertSame(EstadosCodigo::NOTIFICACION_CANCELADA, (int) $pendiente->fresh()->id_estado);
    }

    public function test_no_se_cancela_uno_que_ya_salio(): void
    {
        $enviada = $this->notificacionEn(EstadosCodigo::NOTIFICACION_ENVIADA);

        $this->actingAs($this->usuario())
            ->post("/panel/notificaciones/{$enviada->uuid}/cancelar")
            ->assertSessionHas('error');

        $this->assertSame(EstadosCodigo::NOTIFICACION_ENVIADA, (int) $enviada->fresh()->id_estado);
    }

    /** Una notificacion cualquiera, en el estado que haga falta. */
    private function notificacionEn(int $estado): Notificacion
    {
        $socio = $this->socio();

        return Notificacion::create([
            'id_tipo_notificacion' => $this->plantilla()->id,
            'id_cliente' => $socio->id,
            'email_destino' => $socio->email,
            'asunto' => 'Un aviso',
            'contenido' => '<p>Hola</p>',
            'id_estado' => $estado,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
        ]);
    }

    /** Los estados de una notificación tienen nombre, no salen «Desconocido». */
    public function test_los_estados_de_notificacion_tienen_nombre(): void
    {
        foreach ([600, 601, 602, 603] as $codigo) {
            $this->assertNotSame(
                'Desconocido',
                EstadosCodigo::getNombre($codigo),
                "El estado {$codigo} de notificación no tiene nombre."
            );
        }
    }
}
