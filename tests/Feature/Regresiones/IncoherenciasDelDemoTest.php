<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\HistorialCambio;
use App\Models\HistorialTraspaso;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Models\TipoNotificacion;
use App\Services\CorreoService;
use App\Services\NotificacionService;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Lo que salió al armar unos datos de demostración que cuadraran.
 *
 * Cada caso es una pantalla que decía una cosa y una base que guardaba otra:
 * un abono que se leía como pago entero, un historial que nunca se llenaba,
 * un RUT de apoderado que nunca llegaba al correo, una membresía borrada que
 * dejaba al socio debiendo para siempre, un socio sin plan que seguía activo,
 * y el «cancélala primero» que no se podía hacer.
 */
class IncoherenciasDelDemoTest extends CasoConCatalogos
{
    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true]);
    }

    private function membresia(Cliente $socio, int $estado, array $extra = []): Inscripcion
    {
        return Inscripcion::factory()->create($extra + [
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'pausada' => $estado === EstadosCodigo::INSCRIPCION_PAUSADA,
            'precio_base' => 40000,
            'descuento_aplicado' => 0,
            'precio_final' => 40000,
            'fecha_inicio' => now()->subDays(5),
            'fecha_vencimiento' => now()->addDays(25),
        ]);
    }

    /** El pago en cero que deja una membresía vendida «sin pagar». */
    private function pagoPendiente(Inscripcion $inscripcion, int $abonado = 0): Pago
    {
        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => 40000,
            'monto_abonado' => $abonado,
            'monto_pendiente' => 40000 - $abonado,
            'id_estado' => $abonado > 0 ? EstadosCodigo::PAGO_PARCIAL : EstadosCodigo::PAGO_PENDIENTE,
            'tipo_pago' => $abonado > 0 ? 'parcial' : 'pendiente',
            'id_metodo_pago' => $abonado > 0 ? 1 : null,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 1. Cambio de plan
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Un abono a la diferencia se guarda como abono, y el cambio queda en el
     * historial: el filtro «Cambio de plan» salía siempre vacío.
     */
    public function test_el_cambio_de_plan_con_abono_queda_parcial_y_en_el_historial(): void
    {
        $planes = Membresia::where('activo', true)->get()
            ->map(fn (Membresia $m) => [$m, (int) ($m->precioVigente()?->precio_normal ?? 0)])
            ->filter(fn (array $p) => $p[1] > 0)
            ->sortBy(fn (array $p) => $p[1])
            ->values();
        [$barato, $precioBarato] = $planes->first();
        [$caro, $precioCaro] = $planes->last();
        $this->assertGreaterThan($precioBarato, $precioCaro);

        $socio = $this->socio();
        $vieja = $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA, [
            'id_membresia' => $barato->id,
            'precio_base' => $precioBarato,
            'precio_final' => $precioBarato,
        ]);
        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $vieja->id,
            'monto_total' => $precioBarato,
            'monto_abonado' => $precioBarato,
            'monto_pendiente' => 0,
            'id_estado' => 201,
        ]);

        $diferencia = $precioCaro - $precioBarato;

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$vieja->uuid}/cambiar-plan", [
                'id_membresia_actual' => $barato->id,
                'id_membresia_nueva' => $caro->id,
                'id_metodo_pago' => 1,
                'aplicar_credito' => true,
                'tipo_pago' => 'parcial',
                'monto_abonado' => intdiv($diferencia, 2),
                'motivo_cambio' => 'Quiere clases',
            ])
            ->assertOk();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->firstOrFail();
        $pago = Pago::where('id_inscripcion', $nueva->id)->firstOrFail();

        $this->assertSame(202, (int) $pago->id_estado);
        $this->assertSame('parcial', $pago->tipo_pago, 'Un abono a la diferencia no es un pago completo.');

        $historial = HistorialCambio::cambiosPlan()->where('cliente_id', $socio->id)->first();
        $this->assertNotNull($historial, 'El cambio de plan no quedó en el historial.');
        $this->assertSame($nueva->id, (int) $historial->inscripcion_id);
        $this->assertSame('Quiere clases', $historial->motivo);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. El RUT del apoderado en el correo
    // ─────────────────────────────────────────────────────────────────────

    public function test_el_correo_al_apoderado_lleva_su_rut(): void
    {
        $menor = $this->socio([
            'es_menor_edad' => true,
            'apoderado_nombre' => 'María González',
            'apoderado_rut' => '9.876.543-3',
            'apoderado_email' => 'apoderado@correo.cl',
        ]);
        $inscripcion = $this->membresia($menor, EstadosCodigo::INSCRIPCION_ACTIVA);

        TipoNotificacion::firstOrCreate(
            ['codigo' => 'confirmacion_tutor_legal'],
            // El aviso sale de la plantilla guardada: la de prueba pide el RUT.
            ['nombre' => 'Tutor legal', 'asunto_email' => 'x', 'plantilla_email' => '<p>Tutor: {nombre_apoderado}, RUT {rut_apoderado}</p>'],
        );

        // Sin salir a internet: lo que importa es lo que se escribió.
        $correo = Mockery::mock(CorreoService::class);
        $correo->shouldReceive('enviar')->andReturn('id-falso');

        (new NotificacionService($correo))->enviarNotificacionTutorLegal($inscripcion);

        $aviso = Notificacion::where('id_inscripcion', $inscripcion->id)->firstOrFail();
        $this->assertStringContainsString('9.876.543-3', $aviso->contenido);
        $this->assertStringNotContainsString('No especificado', $aviso->contenido);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. Borrar una membresía sin cobrar
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Su pago de $0 se va con ella, el socio se puede dar de baja, y al
     * recuperarla desde la papelera vuelve con su pago.
     */
    public function test_borrar_la_membresia_se_lleva_su_pago_en_cero_y_lo_devuelve(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio, EstadosCodigo::INSCRIPCION_VENCIDA, [
            'fecha_inicio' => now()->subMonths(2),
            'fecha_vencimiento' => now()->subMonth(),
        ]);
        $pago = $this->pagoPendiente($inscripcion);

        $admin = $this->administrador();
        $this->actingAs($admin)->delete("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted($inscripcion);
        $this->assertSoftDeleted($pago);

        // Ya no queda deuda fantasma que impida la baja.
        $this->actingAs($admin)->patch("/panel/clientes/{$socio->uuid}/desactivar")
            ->assertSessionHasNoErrors();
        $this->assertFalse((bool) $socio->fresh()->activo);

        // Recuperarla la devuelve entera, con su pago.
        $this->actingAs($admin)->patch("/panel/papelera/inscripciones/{$inscripcion->id}/restaurar");

        $this->assertNotSoftDeleted($inscripcion);
        $this->assertNotSoftDeleted($pago);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. El alta usa las mismas reglas que inscribir
    // ─────────────────────────────────────────────────────────────────────

    public function test_el_alta_toma_el_precio_vigente_y_las_pausas_del_plan(): void
    {
        Membresia::whereKey(4)->update(['max_pausas' => 0]);

        // Un precio que todavía no empieza y que además está apagado: el alta
        // lo tomaba porque solo miraba la fecha de fin.
        PrecioMembresia::create([
            'id_membresia' => 4,
            'precio_normal' => 99000,
            'precio_convenio' => 99000,
            'fecha_vigencia_desde' => now()->addYear()->toDateString(),
            'fecha_vigencia_hasta' => now()->addYears(2)->toDateString(),
            'activo' => false,
        ]);

        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'con_membresia',
            'run_pasaporte' => '11.111.111-1',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '+56912345674',
            'email' => 'camila@progym.cl',
            'fecha_nacimiento' => '1990-05-10',
            'id_membresia' => 4,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'pendiente',
        ])->assertSessionHasNoErrors();

        $inscripcion = Inscripcion::whereHas('cliente', fn ($q) => $q->where('nombres', 'Camila'))->firstOrFail();

        $this->assertSame(40000, (int) $inscripcion->precio_base, 'El alta tomó un precio que no está vigente.');
        $this->assertSame(0, (int) $inscripcion->max_pausas_permitidas, 'El alta no respetó las pausas del plan.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5. Días que quedan al renovar, con cambio de hora de por medio
    // ─────────────────────────────────────────────────────────────────────

    public function test_los_dias_para_renovar_no_pierden_uno_con_el_cambio_de_hora(): void
    {
        // El 6 de septiembre de 2026 Chile adelanta la hora: ese día dura 23.
        $this->travelTo(Carbon::parse('2026-09-05 10:00', 'America/Santiago'));

        $inscripcion = $this->membresia($this->socio(), EstadosCodigo::INSCRIPCION_ACTIVA, [
            'fecha_inicio' => '2026-08-10',
            'fecha_vencimiento' => '2026-09-10',
        ]);

        $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}/renovar")
            ->assertOk()
            ->assertInertia(fn ($pagina) => $pagina->where('inscripcion.dias', 5));
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6. La tarea nocturna da de baja a quien se quedó sin plan
    // ─────────────────────────────────────────────────────────────────────

    public function test_la_tarea_nocturna_da_de_baja_a_quien_se_quedo_sin_plan(): void
    {
        // Canceló su única membresía.
        $cancelo = $this->socio();
        $this->membresia($cancelo, EstadosCodigo::INSCRIPCION_CANCELADA);

        // Traspasó su única membresía: ya no le queda ninguna fila.
        $traspaso = $this->socio();
        $recibio = $this->socio();
        $deRecibio = $this->membresia($recibio, EstadosCodigo::INSCRIPCION_ACTIVA);
        HistorialTraspaso::create([
            'inscripcion_origen_id' => $deRecibio->id,
            'inscripcion_destino_id' => $deRecibio->id,
            'cliente_origen_id' => $traspaso->id,
            'cliente_destino_id' => $recibio->id,
            'membresia_id' => 4,
            'fecha_traspaso' => now(),
            'motivo' => 'Se fue de la ciudad',
            'dias_restantes_traspasados' => 25,
            'fecha_vencimiento_original' => now()->addDays(25),
        ]);

        // Recién registrado, todavía sin plan: no se toca.
        $nuevo = $this->socio();

        // Cambió de plan: la vieja quedó «cambiada», la nueva sigue activa.
        $cambio = $this->socio();
        $this->membresia($cambio, EstadosCodigo::INSCRIPCION_CAMBIADA);
        $this->membresia($cambio, EstadosCodigo::INSCRIPCION_ACTIVA);

        $this->artisan('clientes:desactivar-vencidos')->assertSuccessful();

        $this->assertFalse((bool) $cancelo->fresh()->activo, 'Quien canceló su única membresía sigue activo.');
        $this->assertFalse((bool) $traspaso->fresh()->activo, 'Quien traspasó su única membresía sigue activo.');
        $this->assertTrue((bool) $recibio->fresh()->activo);
        $this->assertTrue((bool) $nuevo->fresh()->activo, 'Un socio sin plan todavía no es un socio que se fue.');
        $this->assertTrue((bool) $cambio->fresh()->activo, 'Quien cambió de plan tiene una vigente.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // 7. Cancelar una membresía
    // ─────────────────────────────────────────────────────────────────────

    private function cancelar(Inscripcion $inscripcion, array $datos = ['motivo' => 'Se cambió de ciudad'], $usuario = null)
    {
        return $this->actingAs($usuario ?? $this->administrador())
            ->post("/panel/inscripciones/{$inscripcion->uuid}/cancelar", $datos);
    }

    public function test_cancelar_cierra_la_membresia_sin_tocar_sus_pagos(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA);
        $pago = $this->pagoPendiente($inscripcion, 15000);

        $this->cancelar($inscripcion)->assertSessionHasNoErrors()->assertRedirect();

        $inscripcion->refresh();
        $this->assertSame(EstadosCodigo::INSCRIPCION_CANCELADA, (int) $inscripcion->id_estado);

        // El pago es lo que se cobró: queda igual.
        $pago->refresh();
        $this->assertSame(15000, (int) $pago->monto_abonado);
        $this->assertSame(EstadosCodigo::PAGO_PARCIAL, (int) $pago->id_estado);

        // Lo que faltaba ya no se cobra.
        $this->assertSame(0, Inscripcion::porCobrar());

        $movimiento = HistorialCambio::where('inscripcion_id', $inscripcion->id)
            ->where('tipo_cambio', 'cancelacion_inscripcion')
            ->first();
        $this->assertNotNull($movimiento);
        $this->assertSame('Se cambió de ciudad', $movimiento->motivo);
        $this->assertSame(EstadosCodigo::INSCRIPCION_ACTIVA, (int) $movimiento->estado_anterior);
    }

    public function test_cancelar_una_pausada_le_quita_la_pausa(): void
    {
        $inscripcion = $this->membresia($this->socio(), EstadosCodigo::INSCRIPCION_PAUSADA);

        $this->cancelar($inscripcion)->assertSessionHasNoErrors();

        $inscripcion->refresh();
        $this->assertSame(EstadosCodigo::INSCRIPCION_CANCELADA, (int) $inscripcion->id_estado);
        $this->assertFalse((bool) $inscripcion->pausada);
    }

    public function test_cancelar_pide_motivo(): void
    {
        $inscripcion = $this->membresia($this->socio(), EstadosCodigo::INSCRIPCION_ACTIVA);

        $this->cancelar($inscripcion, ['motivo' => ''])->assertSessionHasErrors('motivo');

        $this->assertSame(EstadosCodigo::INSCRIPCION_ACTIVA, (int) $inscripcion->fresh()->id_estado);
    }

    public function test_una_vencida_no_se_cancela(): void
    {
        $inscripcion = $this->membresia($this->socio(), EstadosCodigo::INSCRIPCION_VENCIDA, [
            'fecha_vencimiento' => now()->subDay(),
        ]);

        $this->cancelar($inscripcion)->assertSessionHas('error');

        $this->assertSame(EstadosCodigo::INSCRIPCION_VENCIDA, (int) $inscripcion->fresh()->id_estado);
        $this->assertFalse(HistorialCambio::where('inscripcion_id', $inscripcion->id)->exists());
    }

    /** «Cancélala primero» ahora se puede hacer: después, baja y borrado. */
    public function test_despues_de_cancelar_se_puede_dar_de_baja_y_borrar_los_datos(): void
    {
        $admin = $this->administrador();

        $socio = $this->socio();
        $inscripcion = $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA);
        $this->pagoPendiente($inscripcion, 15000);

        $this->actingAs($admin)->patch("/panel/clientes/{$socio->uuid}/desactivar");
        $this->assertTrue((bool) $socio->fresh()->activo, 'Con la membresía vigente no debía darse de baja.');

        $this->cancelar($inscripcion, usuario: $admin)->assertSessionHasNoErrors();

        $this->actingAs($admin)->patch("/panel/clientes/{$socio->uuid}/desactivar")->assertSessionHasNoErrors();
        $this->assertFalse((bool) $socio->fresh()->activo);

        $otro = $this->socio();
        $suya = $this->membresia($otro, EstadosCodigo::INSCRIPCION_PAUSADA);
        $this->pagoPendiente($suya);
        $this->cancelar($suya, usuario: $admin)->assertSessionHasNoErrors();

        $this->actingAs($admin)->post("/panel/clientes/{$otro->uuid}/borrar-datos", [
            'motivo' => 'solicitud',
            'confirmacion' => 'BORRAR',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($otro->fresh()->datos_borrados_en);
    }

    public function test_recepcion_no_puede_cancelar_ni_ve_el_boton(): void
    {
        $recepcion = $this->recepcionista();
        $inscripcion = $this->membresia($this->socio(), EstadosCodigo::INSCRIPCION_ACTIVA);

        $this->cancelar($inscripcion, usuario: $recepcion)->assertForbidden();
        $this->assertSame(EstadosCodigo::INSCRIPCION_ACTIVA, (int) $inscripcion->fresh()->id_estado);

        $this->actingAs($recepcion)->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertInertia(fn ($pagina) => $pagina->where('puede.cancelar', false));

        $this->actingAs($this->administrador())->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertInertia(fn ($pagina) => $pagina->where('puede.cancelar', true));
    }
}
