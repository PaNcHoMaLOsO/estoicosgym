<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Regresiones de la renovación.
 *
 * El bug de fondo es que renovar creaba la membresía nueva y DEJABA LA VIEJA
 * TAL CUAL: nadie la cerraba. Como se puede renovar hasta 30 días antes de que
 * venza, el socio se quedaba con dos membresías activas a la vez.
 */
class RenovacionTest extends CasoConCatalogos
{
    /** Mensual: $40.000. */
    private const PLAN_MENSUAL = 4;

    /** Una membresía a punto de vencer, que es cuando se renueva. */
    private function porVencer(int $diasQueQuedan = 5): Inscripcion
    {
        $cliente = Cliente::factory()->create(['activo' => true]);

        return Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => now()->subDays(25),
            'fecha_vencimiento' => now()->addDays($diasQueQuedan),
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);
    }

    /** @return array<string,mixed> */
    private function formulario(array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->addDays(6)->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'monto_abonado' => 40000,
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    private function renovar(Inscripcion $inscripcion, array $datos = [])
    {
        return $this->actingAs($this->administrador())
            ->post("/panel/inscripciones/{$inscripcion->uuid}/renovar", $this->formulario($datos));
    }

    /**
     * EL BUG.
     *
     * Renovar antes de que venciera dejaba DOS membresías activas para el mismo
     * socio: contado dos veces en el panel y en los informes, y encima la
     * comprobación de «ya tiene una membresía activa» impedía volver a
     * inscribirlo más adelante.
     */
    public function test_al_renovar_la_membresia_anterior_deja_de_estar_vigente(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior)->assertSessionHasNoErrors();

        $vigentes = Inscripcion::where('id_cliente', $anterior->id_cliente)
            ->whereIn('id_estado', [
                EstadosCodigo::INSCRIPCION_ACTIVA,
                EstadosCodigo::INSCRIPCION_PAUSADA,
            ])
            ->count();

        $this->assertSame(1, $vigentes, 'Un socio no puede quedar con dos membresías vigentes.');

        $this->assertSame(
            EstadosCodigo::INSCRIPCION_VENCIDA,
            (int) $anterior->fresh()->id_estado,
            'La membresía renovada tiene que quedar cerrada.'
        );
    }

    public function test_la_nueva_queda_encadenada_a_la_anterior(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior);

        $nueva = Inscripcion::where('id_inscripcion_anterior', $anterior->id)->firstOrFail();

        $this->assertSame(EstadosCodigo::INSCRIPCION_ACTIVA, (int) $nueva->id_estado);
        $this->assertSame('renovacion', $nueva->tipo_cambio);
        $this->assertSame($anterior->id_cliente, $nueva->id_cliente);
    }

    /** Queda anotado en el historial de la membresía. */
    public function test_la_renovacion_queda_en_el_historial(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior);

        $nueva = Inscripcion::where('id_inscripcion_anterior', $anterior->id)->firstOrFail();

        $this->assertDatabaseHas('historial_cambios', [
            'inscripcion_id' => $nueva->id,
            'tipo_cambio' => 'renovacion',
        ]);
    }

    /**
     * Renovar cobra otra vez, así que tiene que dejar su pago. Con «no paga
     * ahora» pasaba lo mismo que en el alta: `fecha_pago` iba en NULL sobre una
     * columna NOT NULL y toda la renovación se caía.
     */
    public function test_renovar_sin_pagar_deja_la_deuda_registrada(): void
    {
        $anterior = $this->porVencer();

        $respuesta = $this->renovar($anterior, [
            'tipo_pago' => 'pendiente',
            'monto_abonado' => null,
            'id_metodo_pago' => null,
        ]);

        $respuesta->assertSessionHasNoErrors();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $anterior->id)->firstOrFail();
        $pago = Pago::where('id_inscripcion', $nueva->id)->firstOrFail();

        $this->assertSame(EstadosCodigo::PAGO_PENDIENTE, (int) $pago->id_estado);
        $this->assertEquals(40000, $pago->monto_pendiente);
        $this->assertNotNull($pago->fecha_pago);
    }

    public function test_renovar_pagando_deja_la_nueva_al_dia(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior);

        $nueva = Inscripcion::where('id_inscripcion_anterior', $anterior->id)->firstOrFail();
        $pago = Pago::where('id_inscripcion', $nueva->id)->firstOrFail();

        $this->assertSame(EstadosCodigo::PAGO_PAGADO, (int) $pago->id_estado);
        $this->assertEquals(0, $pago->monto_pendiente);
    }

    /**
     * Con más de un mes por delante no hay nada que renovar, y hacerlo cortaría
     * la membresía en curso: el socio perdería los días que le quedaban.
     */
    public function test_no_se_renueva_una_membresia_que_todavia_tiene_mucho_por_delante(): void
    {
        $reciente = $this->porVencer(diasQueQuedan: 90);

        $respuesta = $this->renovar($reciente);

        $respuesta->assertSessionHas('error');
        $this->assertSame(
            0,
            Inscripcion::where('id_inscripcion_anterior', $reciente->id)->count()
        );
    }

    /** Una membresía ya cerrada no se renueva: se inscribe de nuevo. */
    public function test_no_se_renueva_una_membresia_cancelada(): void
    {
        $cancelada = $this->porVencer();
        $cancelada->update(['id_estado' => EstadosCodigo::INSCRIPCION_CANCELADA]);

        $this->renovar($cancelada)->assertSessionHas('error');

        $this->assertSame(0, Inscripcion::where('id_inscripcion_anterior', $cancelada->id)->count());
    }

    /**
     * Renovar dos veces la misma crearía una tercera membresía encadenada a una
     * que ya no está vigente, y el socio acabaría con dos activas otra vez.
     */
    public function test_no_se_renueva_dos_veces_la_misma_membresia(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior);
        $this->renovar($anterior);

        $this->assertSame(
            1,
            Inscripcion::where('id_inscripcion_anterior', $anterior->id)->count()
        );
    }

    /** Una membresía vencida sí se renueva, aunque haga días que caducó. */
    public function test_una_membresia_ya_vencida_se_puede_renovar(): void
    {
        $vencida = $this->porVencer(diasQueQuedan: -10);
        $vencida->update(['id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA]);

        $this->renovar($vencida, ['fecha_inicio' => now()->format('Y-m-d')])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Inscripcion::where('id_inscripcion_anterior', $vencida->id)->count());
    }

    /**
     * La pantalla sugiere empezar el día DESPUÉS del vencimiento: si empezara
     * hoy se solaparían y el socio pagaría dos veces los días que le quedaban.
     */
    public function test_la_pantalla_sugiere_empezar_cuando_termina_la_anterior(): void
    {
        $anterior = $this->porVencer(diasQueQuedan: 5);

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$anterior->uuid}/renovar");

        $respuesta->assertOk();

        $sugerido = $respuesta->viewData('page')['props']['inscripcion']['empieza_sugerido'];

        $this->assertSame(
            $anterior->fecha_vencimiento->copy()->addDay()->format('Y-m-d'),
            $sugerido
        );
    }

    /**
     * La que vence HOY todavía sirve hoy: la nueva empieza mañana.
     *
     * `fecha_vencimiento` se guarda a medianoche y se miraba con `isPast()`, que
     * compara el instante: desde las 00:01 del día del vencimiento la pantalla
     * proponía empezar hoy, encima del último día que el socio ya tenía pagado.
     * El resto del sistema no lo ve así —la tarea nocturna, los avisos y
     * `esta_vencida` la dan por vigente hasta el día siguiente—, y era el único
     * borde que las pruebas no miraban: estaban el de cinco días y el de veinte
     * pasados, nunca el cero.
     */
    public function test_la_que_vence_hoy_empieza_manana_y_no_se_solapa(): void
    {
        $anterior = $this->porVencer(diasQueQuedan: 0);
        // A medianoche, que es como la escribe el alta de verdad.
        $anterior->update(['fecha_vencimiento' => today()]);

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$anterior->uuid}/renovar");

        $respuesta->assertOk();

        $this->assertSame(
            today()->addDay()->format('Y-m-d'),
            $respuesta->viewData('page')['props']['inscripcion']['empieza_sugerido'],
            'Empezar hoy pisa el último día que el socio ya pagó.'
        );
    }

    /** Si ya venció, empieza hoy: retomar desde una fecha pasada regala días. */
    public function test_si_ya_vencio_la_nueva_empieza_hoy(): void
    {
        $vencida = $this->porVencer(diasQueQuedan: -20);
        $vencida->update(['id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA]);

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$vencida->uuid}/renovar");

        $sugerido = $respuesta->viewData('page')['props']['inscripcion']['empieza_sugerido'];

        $this->assertSame(now()->format('Y-m-d'), $sugerido);
    }

    /** Renovar cambiando de plan cobra el precio del plan nuevo. */
    public function test_se_puede_renovar_cambiando_de_plan(): void
    {
        $anterior = $this->porVencer();

        // 3 = Trimestral, $100.000.
        $this->renovar($anterior, [
            'id_membresia' => 3,
            'monto_abonado' => 100000,
        ])->assertSessionHasNoErrors();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $anterior->id)->firstOrFail();

        $this->assertSame(3, (int) $nueva->id_membresia);
        $this->assertEquals(100000, $nueva->precio_final);
    }

    /** No se puede cobrar de más al renovar: un abono mayor que el precio se rechaza. */
    public function test_no_se_cobra_mas_que_el_precio_del_plan(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior, ['tipo_pago' => 'abono', 'monto_abonado' => 90000])
            ->assertSessionHasErrors('monto_abonado');

        $this->assertSame(0, Inscripcion::where('id_inscripcion_anterior', $anterior->id)->count());
    }

    /**
     * QUIEN RENOVÓ NO SALE COMO QUE SE FUE.
     *
     * Al renovar, la membresía anterior se cierra como vencida. El resumen
     * contaba las vencidas por su estado, así que quien renovaba salía en
     * «se fueron sin renovar»: el número se inflaba justo con los socios que sí
     * se quedaron.
     */
    public function test_quien_renovo_no_sale_como_que_se_fue(): void
    {
        $anterior = $this->porVencer();

        $this->renovar($anterior)->assertSessionHasNoErrors();

        $this->assertSame(EstadosCodigo::INSCRIPCION_VENCIDA, (int) $anterior->fresh()->id_estado);

        $props = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props'];

        $this->assertSame(0, $props['cifras']['sin_renovar']);
        $this->assertSame([], $props['sinRenovar']);
    }

    /** Y quien se fue de verdad, sí: con su membresía vencida y nada vigente. */
    public function test_quien_se_fue_sin_renovar_sale_en_el_resumen(): void
    {
        $vencida = $this->porVencer(diasQueQuedan: -5);
        $vencida->update(['id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA]);

        $props = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props'];

        $this->assertSame(1, $props['cifras']['sin_renovar']);
        $this->assertSame((string) $vencida->uuid, (string) $props['sinRenovar'][0]['uuid']);
        $this->assertSame(5, $props['sinRenovar'][0]['dias']);
    }
}
