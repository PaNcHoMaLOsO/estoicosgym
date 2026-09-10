<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Corregir los datos de una membresía ya vendida.
 *
 * Para el error de tecleo: una fecha de inicio equivocada corre todo el
 * periodo. Pero corregir NO es rehacer: el socio, el estado y el plan no se
 * tocan aquí, porque cada uno arrastra cuentas que un formulario de corrección
 * se saltaría enteras.
 */
class CorreccionDeMembresiaTest extends CasoConCatalogos
{
    private const PRECIO = 100000;

    private function membresia(array $extra = []): Inscripcion
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        return Inscripcion::factory()->create($extra + [
            'id_cliente' => $socio->id,
            'id_membresia' => 3,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => '2026-03-01',
            'fecha_vencimiento' => '2026-05-30',
            'precio_base' => self::PRECIO,
            'descuento_aplicado' => 0,
            'precio_final' => self::PRECIO,
        ]);
    }

    private function conPago(Inscripcion $inscripcion, int $monto): Pago
    {
        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => self::PRECIO,
            'monto_abonado' => $monto,
            'monto_pendiente' => max(0, self::PRECIO - $monto),
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    private function corregir(Inscripcion $inscripcion, array $datos = [])
    {
        return $this->actingAs($this->administrador())
            ->put("/panel/inscripciones/{$inscripcion->uuid}", array_merge([
                'form_submit_token' => uniqid('t', true),
                'fecha_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                'precio_base' => (int) $inscripcion->precio_base,
            ], $datos));
    }

    public function test_se_corrige_una_fecha_de_inicio_mal_puesta(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, [
            'fecha_inicio' => '2026-04-01',
            'fecha_vencimiento' => '2026-06-30',
        ])->assertSessionHasNoErrors();

        $inscripcion->refresh();

        $this->assertSame('2026-04-01', $inscripcion->fecha_inicio->format('Y-m-d'));
        $this->assertSame('2026-06-30', $inscripcion->fecha_vencimiento->format('Y-m-d'));
    }

    /** Una membresía tiene que vencer después de empezar. */
    public function test_no_puede_vencer_antes_de_empezar(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, [
            'fecha_inicio' => '2026-05-01',
            'fecha_vencimiento' => '2026-04-01',
        ])->assertSessionHasErrors('fecha_vencimiento');
    }

    /** Cambiar el precio mueve el saldo de todos sus pagos. */
    public function test_corregir_el_precio_recalcula_los_pagos(): void
    {
        $inscripcion = $this->membresia();
        $pago = $this->conPago($inscripcion, 60000);

        // El plan valía 80.000, no 100.000.
        $this->corregir($inscripcion, ['precio_base' => 80000])->assertSessionHasNoErrors();

        $pago->refresh();

        $this->assertEquals(80000, $pago->monto_total);
        $this->assertEquals(20000, $pago->monto_pendiente);
        $this->assertSame(EstadosCodigo::PAGO_PARCIAL, (int) $pago->id_estado);
    }

    /** Y si el precio nuevo ya está cubierto, la membresía queda saldada. */
    public function test_si_el_precio_baja_hasta_lo_cobrado_queda_saldada(): void
    {
        $inscripcion = $this->membresia();
        $pago = $this->conPago($inscripcion, 60000);

        $this->corregir($inscripcion, ['precio_base' => 60000])->assertSessionHasNoErrors();

        $this->assertEquals(0, $pago->fresh()->monto_pendiente);
        $this->assertSame(EstadosCodigo::PAGO_PAGADO, (int) $pago->fresh()->id_estado);
    }

    /**
     * EL PRECIO NO BAJA DE LO YA COBRADO.
     *
     * Si se cobraron 60.000 y el precio se corrige a 40.000, el socio queda con
     * 20.000 a favor que nadie le va a devolver y que ningún informe sabe
     * contar. Primero se corrige o se anula el pago de más.
     */
    public function test_el_precio_no_puede_bajar_de_lo_que_ya_se_cobro(): void
    {
        $inscripcion = $this->membresia();
        $this->conPago($inscripcion, 60000);

        $this->corregir($inscripcion, ['precio_base' => 40000])
            ->assertSessionHasErrors('precio_base');

        $this->assertEquals(self::PRECIO, $inscripcion->fresh()->precio_final);
    }

    /** El descuento no puede superar el precio. */
    public function test_el_descuento_no_puede_superar_el_precio(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, [
            'precio_base' => 50000,
            'descuento_aplicado' => 80000,
        ])->assertSessionHasErrors('descuento_aplicado');
    }

    public function test_el_precio_final_sale_del_precio_menos_el_descuento(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, [
            'precio_base' => 100000,
            'descuento_aplicado' => 25000,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(75000, $inscripcion->fresh()->precio_final);
    }

    /**
     * NO se puede mover la membresía a otro socio.
     *
     * El formulario de Blade lo permitía: sus pagos se quedaban apuntando al
     * primero, y el socio nuevo aparecía con una membresía que nadie le cobró.
     * Para eso está el traspaso, que mueve las dos cosas.
     */
    public function test_no_se_puede_cambiar_el_socio(): void
    {
        $inscripcion = $this->membresia();
        $otro = Cliente::factory()->create(['activo' => true]);
        $original = $inscripcion->id_cliente;

        $this->corregir($inscripcion, ['id_cliente' => $otro->id]);

        $this->assertSame($original, $inscripcion->fresh()->id_cliente);
    }

    /**
     * NI el estado: pausar, reanudar y cancelar llevan cuentas —días
     * compensados, pausas gastadas— que un desplegable se salta.
     */
    public function test_no_se_puede_cambiar_el_estado(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, ['id_estado' => EstadosCodigo::INSCRIPCION_CANCELADA]);

        $this->assertSame(
            EstadosCodigo::INSCRIPCION_ACTIVA,
            (int) $inscripcion->fresh()->id_estado
        );
    }

    /** Ni el plan: cambiar de plan recalcula el precio y acredita lo pagado. */
    public function test_no_se_puede_cambiar_el_plan(): void
    {
        $inscripcion = $this->membresia();

        $this->corregir($inscripcion, ['id_membresia' => 1]);

        $this->assertSame(3, (int) $inscripcion->fresh()->id_membresia);
    }

    /** La pantalla dice cuánto se lleva cobrado, que es el suelo del precio. */
    public function test_la_pantalla_dice_cuanto_se_cobro(): void
    {
        $inscripcion = $this->membresia();
        $this->conPago($inscripcion, 35000);

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}/editar");

        $respuesta->assertOk();

        $this->assertSame(
            35000,
            $respuesta->viewData('page')['props']['inscripcion']['cobrado']
        );
    }
}
