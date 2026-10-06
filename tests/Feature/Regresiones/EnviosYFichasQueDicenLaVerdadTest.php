<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\HistorialCambio;
use App\Models\HistorialTraspaso;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use App\Services\CorreoService;
use App\Services\EnvioManualService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Pantallas que decian algo que no era: «ya se envió» de un correo que no
 * salio, un correo reenviado dos veces, una vista previa de la version vieja,
 * un traspaso «a las 00:00» y un abono viejo que pedia cobrar un saldo de $0.
 */
class EnviosYFichasQueDicenLaVerdadTest extends CasoConCatalogos
{
    private $admin = null;

    /** El turno anti-duplicado es por usuario: tiene que ser el mismo. */
    private function usuario()
    {
        return $this->admin ??= $this->administrador();
    }

    private function fingirCorreo(int $veces = -1): void
    {
        $doble = Mockery::mock(CorreoService::class);
        $esperado = $doble->shouldReceive('enviar')->andReturn('smtp');

        if ($veces >= 0) {
            $esperado->times($veces);
        }

        $this->app->instance(CorreoService::class, $doble);
    }

    private function socioConMembresia(): Cliente
    {
        $socio = Cliente::factory()->create(['activo' => true, 'email' => 'socio' . uniqid() . '@progym.cl']);

        Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
        ]);

        return $socio->fresh();
    }

    private function plantilla(array $extra = []): TipoNotificacion
    {
        return TipoNotificacion::create(array_merge([
            'codigo' => 'prueba_' . uniqid(),
            'nombre' => 'Plantilla ' . uniqid(),
            'asunto_email' => 'Hola {nombre}',
            'plantilla_email' => '<p>Tu plan {membresia}.</p>',
            'activo' => true,
        ], $extra));
    }

    public function test_un_correo_rechazado_se_puede_reenviar_con_el_mismo_formulario(): void
    {
        $this->fingirCorreo();
        $socio = $this->socioConMembresia();
        $plantilla = $this->plantilla(['asunto_email' => 'Hola {inventada}']);
        $token = uniqid('t', true);
        $datos = ['form_submit_token' => $token, 'cliente_id' => $socio->id, 'plantilla_id' => $plantilla->id];

        $this->actingAs($this->usuario())->post('/panel/notificaciones/enviar', $datos)
            ->assertSessionHasErrors('plantilla_id');

        // Se arregla la plantilla y se vuelve a pulsar sin recargar.
        $plantilla->update(['asunto_email' => 'Hola {nombre}']);

        $this->actingAs($this->usuario())->post('/panel/notificaciones/enviar', $datos)
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        $this->assertSame(1, Notificacion::where('id_cliente', $socio->id)->count());
    }

    public function test_un_aviso_masivo_rechazado_se_puede_reenviar(): void
    {
        $this->fingirCorreo();
        $datos = [
            'form_submit_token' => uniqid('t', true),
            'grupo' => 'todos',
            'asunto' => 'Cerramos el lunes',
            'mensaje' => 'Hola {nombre}, el lunes cerramos.',
        ];

        // Sin nadie con correo, el grupo se rechaza sin mandar nada.
        $this->actingAs($this->usuario())->post('/panel/notificaciones/masivo', $datos)
            ->assertSessionHasErrors('grupo');

        Cliente::factory()->create(['activo' => true, 'email' => 'uno@progym.cl']);

        $this->actingAs($this->usuario())->post('/panel/notificaciones/masivo', $datos)
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');
    }

    /**
     * El doble clic del reenvio: la segunda peticion trae la notificacion
     * leida ANTES de que la primera la marcara enviada. No tiene que mandar.
     */
    public function test_reenviar_mira_el_estado_de_la_base_y_no_manda_dos_veces(): void
    {
        $this->fingirCorreo(0);
        $socio = $this->socioConMembresia();

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $this->plantilla()->id,
            'id_cliente' => $socio->id,
            'email_destino' => $socio->email,
            'asunto' => 'Un aviso',
            'contenido' => '<p>Hola</p>',
            'id_estado' => EstadosCodigo::NOTIFICACION_FALLIDA,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
        ]);

        // La copia que tenia la segunda peticion sigue diciendo «fallida».
        $leidaAntes = Notificacion::find($notificacion->id);
        $notificacion->update(['id_estado' => EstadosCodigo::NOTIFICACION_ENVIADA]);

        $this->actingAs($this->usuario());

        try {
            app(EnvioManualService::class)->reenviar($leidaAntes);
            $this->fail('Reenvio un correo que ya habia salido.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('envio', $e->errors());
        }
    }

    public function test_la_vista_previa_usa_lo_escrito_sin_guardarlo(): void
    {
        Cliente::factory()->create(['email' => 'real@progym.cl', 'nombres' => 'Rosa']);
        $plantilla = $this->plantilla(['asunto_email' => 'Version vieja {nombre}']);

        $vista = $this->actingAs($this->administrador())
            ->postJson("/panel/notificaciones/plantillas/{$plantilla->id}/vista-previa", [
                'asunto_email' => 'Version nueva {nombre}',
                'plantilla_email' => '<p>Texto sin guardar</p>',
            ])
            ->assertOk();

        $this->assertStringStartsWith('Version nueva Rosa', $vista->json('asunto'));
        $this->assertStringContainsString('Texto sin guardar', $vista->json('contenido'));

        $this->assertSame('Version vieja {nombre}', $plantilla->fresh()->asunto_email);
    }

    public function test_la_vista_previa_sin_permiso_de_configuracion_no_se_abre(): void
    {
        $plantilla = $this->plantilla();

        $this->actingAs($this->recepcionista())
            ->postJson("/panel/notificaciones/plantillas/{$plantilla->id}/vista-previa", [
                'asunto_email' => 'Hola',
                'plantilla_email' => '<p>x</p>',
            ])
            ->assertForbidden();
    }

    public function test_un_traspaso_sale_con_su_hora_y_en_su_orden(): void
    {
        Carbon::setTestNow('2026-10-02 18:30:00');

        $origen = Cliente::factory()->create();
        $destino = Cliente::factory()->create();
        $inscripcion = Inscripcion::factory()->create(['id_cliente' => $origen->id, 'id_membresia' => 4]);

        // Un cambio a media mañana; el traspaso, despues, a las 18:30.
        HistorialCambio::create([
            'uuid' => (string) Str::uuid(),
            'tipo_cambio' => 'pausa',
            'entidad' => 'cliente',
            'entidad_id' => $origen->id,
            'cliente_id' => $origen->id,
            'estado_anterior' => 100,
            'estado_nuevo' => 101,
            'fecha_cambio' => now()->setTime(10, 0),
        ]);

        HistorialTraspaso::create([
            'inscripcion_origen_id' => $inscripcion->id,
            'inscripcion_destino_id' => $inscripcion->id,
            'cliente_origen_id' => $origen->id,
            'cliente_destino_id' => $destino->id,
            'membresia_id' => 4,
            'fecha_traspaso' => now(),
            'motivo' => 'Se lo cede a su hermano',
            'dias_restantes_traspasados' => 10,
            'fecha_vencimiento_original' => now()->addDays(10),
        ]);

        $movimientos = $this->actingAs($this->administrador())
            ->get('/panel/historial')
            ->assertOk()
            ->viewData('page')['props']['movimientos'];

        $this->assertSame('traspaso', $movimientos[0]['clase']);
        $this->assertSame('02/10/2026 18:30', $movimientos[0]['cuando']);

        Carbon::setTestNow();
    }

    public function test_un_abono_viejo_de_una_membresia_saldada_no_pide_cobrar(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 3,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => 30000,
            'precio_final' => 30000,
        ]);

        $pago = fn (int $abonado, int $quedaba) => Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 30000,
            'monto_abonado' => $abonado,
            'monto_pendiente' => $quedaba,
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $abonoViejo = $pago(10000, 20000);
        $pago(20000, 0);

        $props = $this->actingAs($this->administrador())
            ->get("/panel/pagos/{$abonoViejo->uuid}")
            ->assertOk()
            ->viewData('page')['props'];

        // Lo que quedaba tras ese abono sigue siendo historia...
        $this->assertSame(20000, $props['pago']['pendiente']);
        // ...pero hoy no se debe nada, que es lo que decide el boton.
        $this->assertSame(0, $props['inscripcion']['debe']);
    }
}
