<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Corregir o anular un pago ya registrado.
 *
 * Un monto mal escrito descuadra la caja del día y el saldo del socio a la vez.
 * Lo que más se comprueba aquí es que, al tocar un pago, los DEMÁS pagos de esa
 * membresía queden diciendo la verdad: el saldo de cada fila es lo que quedaba
 * después de ella, así que cambiar uno invalida a los que vienen detrás.
 */
class CorreccionDePagoTest extends CasoConCatalogos
{
    private const PRECIO = 100000;

    private function membresia(): Inscripcion
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 3,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => self::PRECIO,
            'precio_final' => self::PRECIO,
        ]);
    }

    private function pago(Inscripcion $inscripcion, int $monto, string $cuando = 'today'): Pago
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
            'fecha_pago' => $cuando === 'today' ? now()->format('Y-m-d') : $cuando,
        ]);
    }

    private function corregir(Pago $pago, array $datos = [])
    {
        return $this->actingAs($this->administrador())
            ->put("/panel/pagos/{$pago->uuid}", array_merge([
                'form_submit_token' => uniqid('t', true),
                // Como entero, igual que lo manda la pantalla: la columna es
                // decimal y su valor crudo llega como «10000.00».
                'monto_abonado' => (int) $pago->monto_abonado,
                'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
                'id_metodo_pago' => $pago->id_metodo_pago,
            ], $datos));
    }

    public function test_se_corrige_un_monto_mal_escrito(): void
    {
        $inscripcion = $this->membresia();
        $pago = $this->pago($inscripcion, 40000);

        $this->corregir($pago, ['monto_abonado' => 4000])->assertSessionHasNoErrors();

        $this->assertEquals(4000, $pago->fresh()->monto_abonado);
    }

    /**
     * EL BUG.
     *
     * El de Blade actualizaba SOLO el pago editado. Con tres pagos, corregir el
     * primero dejaba a los otros dos diciendo un saldo que ya no era: en la
     * ficha del socio se veían tres pagos que no cuadraban entre sí.
     */
    public function test_corregir_uno_recalcula_los_demas_pagos_de_esa_membresia(): void
    {
        $inscripcion = $this->membresia();

        $primero = $this->pago($inscripcion, 20000, '2026-01-01');
        $segundo = $this->pago($inscripcion, 20000, '2026-02-01');
        $tercero = $this->pago($inscripcion, 20000, '2026-03-01');

        // El primero eran 50.000, no 20.000. Cabe: los otros dos suman 40.000
        // de una membresía de 100.000.
        $this->corregir($primero, ['monto_abonado' => 50000])->assertSessionHasNoErrors();

        // 100.000 - 50.000 = 50.000 tras el primero.
        $this->assertEquals(50000, $primero->fresh()->monto_pendiente);
        // - 20.000 = 30.000 tras el segundo.
        $this->assertEquals(30000, $segundo->fresh()->monto_pendiente);
        // - 20.000 = 10.000 tras el tercero.
        $this->assertEquals(10000, $tercero->fresh()->monto_pendiente);
    }

    /** Y si con la corrección la membresía queda saldada, TODOS quedan pagados. */
    public function test_si_queda_saldada_todos_los_pagos_lo_dicen(): void
    {
        $inscripcion = $this->membresia();

        $primero = $this->pago($inscripcion, 40000);
        $segundo = $this->pago($inscripcion, 20000);

        $this->corregir($primero, ['monto_abonado' => 80000]);

        $this->assertSame(EstadosCodigo::PAGO_PAGADO, (int) $primero->fresh()->id_estado);
        $this->assertSame(
            EstadosCodigo::PAGO_PAGADO,
            (int) $segundo->fresh()->id_estado,
            'El otro pago se quedó diciendo que la membresía sigue a medias.'
        );
    }

    /**
     * Entre todos los pagos no pueden sumar más de lo que vale la membresía, o
     * el socio saldría con saldo a favor que nadie le va a devolver.
     */
    public function test_no_se_puede_cobrar_mas_que_el_precio(): void
    {
        $inscripcion = $this->membresia();

        $este = $this->pago($inscripcion, 30000);
        $this->pago($inscripcion, 50000);

        // Quedan 50.000 del precio: 60.000 no cabe.
        $this->corregir($este, ['monto_abonado' => 60000])
            ->assertSessionHasErrors('monto_abonado');

        $this->assertEquals(30000, $este->fresh()->monto_abonado);
    }

    public function test_un_pago_no_puede_tener_fecha_futura(): void
    {
        $pago = $this->pago($this->membresia(), 10000);

        $this->corregir($pago, ['fecha_pago' => now()->addDays(3)->format('Y-m-d')])
            ->assertSessionHasErrors('fecha_pago');
    }

    public function test_se_corrige_el_metodo_de_pago(): void
    {
        $pago = $this->pago($this->membresia(), 10000);
        $otro = MetodoPago::where('id', '!=', $pago->id_metodo_pago)->firstOrFail();

        $this->corregir($pago, ['id_metodo_pago' => $otro->id])->assertSessionHasNoErrors();

        $this->assertSame($otro->id, (int) $pago->fresh()->id_metodo_pago);
    }

    /** Anular manda a la papelera, no al vacío. */
    public function test_anular_manda_el_pago_a_la_papelera(): void
    {
        $pago = $this->pago($this->membresia(), 10000);

        $this->actingAs($this->administrador())
            ->delete("/panel/pagos/{$pago->uuid}")
            ->assertSessionHasNoErrors();

        $this->assertNull(Pago::find($pago->id));
        $this->assertNotNull(Pago::withTrashed()->find($pago->id));
    }

    /** Y el saldo del socio vuelve a subir: ese dinero ya no está cobrado. */
    public function test_anular_devuelve_el_saldo_a_los_demas_pagos(): void
    {
        $inscripcion = $this->membresia();

        $anulado = $this->pago($inscripcion, 60000, '2026-01-01');
        $queda = $this->pago($inscripcion, 40000, '2026-02-01');

        $this->actingAs($this->administrador())->delete("/panel/pagos/{$anulado->uuid}");

        // Solo quedan los 40.000: faltan 60.000 por cobrar.
        $this->assertEquals(60000, $queda->fresh()->monto_pendiente);
        $this->assertSame(EstadosCodigo::PAGO_PARCIAL, (int) $queda->fresh()->id_estado);
    }

    /** Anular el único pago deja la membresía sin cobrar nada. */
    public function test_anular_el_unico_pago_deja_la_membresia_sin_cobrar(): void
    {
        $inscripcion = $this->membresia();
        $pago = $this->pago($inscripcion, 100000);

        $this->actingAs($this->administrador())->delete("/panel/pagos/{$pago->uuid}");

        $this->assertEquals(0, $inscripcion->pagos()->sum('monto_abonado'));
    }

    /** El pago anulado sale de la caja: los informes dejan de contarlo. */
    public function test_un_pago_anulado_no_cuenta_en_los_ingresos(): void
    {
        $inscripcion = $this->membresia();
        $pago = $this->pago($inscripcion, 50000);

        $antes = (int) Pago::ingresos()->sum('monto_abonado');

        $this->actingAs($this->administrador())->delete("/panel/pagos/{$pago->uuid}");

        $this->assertSame($antes - 50000, (int) Pago::ingresos()->sum('monto_abonado'));
    }

    /** La pantalla dice cuánto cabe como máximo en este pago. */
    public function test_la_pantalla_dice_cuanto_cabe(): void
    {
        $inscripcion = $this->membresia();

        $este = $this->pago($inscripcion, 30000);
        $this->pago($inscripcion, 20000);

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/pagos/{$este->uuid}/editar");

        $respuesta->assertOk();

        // 100.000 - 20.000 del otro pago.
        $this->assertSame(80000, $respuesta->viewData('page')['props']['pago']['tope']);
    }
}
