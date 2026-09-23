<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * La plata que vino de las planillas: la que no consta, no se inventa.
 *
 * DE ESOS PAGOS SOLO SE SABÍA EL MONTO. El medio —efectivo o transferencia— no
 * venía en ninguna planilla, y no se le puede preguntar a mil setecientas
 * personas con qué pagaron hace dos años. El sistema terminaba afirmando que
 * entraron setenta y tres millones en efectivo, que es falso, y una cifra falsa
 * es peor que una que falta porque nadie la revisa.
 *
 * Se quedan los socios y sus membresías, que es lo que la planilla sí decía.
 */
class PlataImportadaTest extends CasoConCatalogos
{
    private function importada(int $precio = 40000): Inscripcion
    {
        $cliente = Cliente::factory()->create(['activo' => true]);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'observaciones' => 'Importado de Planilla Socios definitva 1.xlsx',
        ]);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => $precio,
            'monto_abonado' => $precio,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
            'observaciones' => 'Importado de Planilla Socios definitva 1.xlsx. El medio de pago no venía en la planilla.',
        ]);

        return $inscripcion;
    }

    /**
     * SE VA LA PLATA Y SE QUEDA LA GENTE.
     *
     * Y la membresía queda en $0 a propósito: borrando solo el pago, pasaría a
     * deber su precio entero y el panel abriría con mil setecientos morosos
     * que no lo son.
     */
    public function test_quitar_los_pagos_importados_no_deja_morosos_falsos(): void
    {
        $inscripcion = $this->importada();

        $this->artisan('datos:quitar-pagos-importados --confirmar')->assertSuccessful();

        $this->assertSame(0, Pago::count());
        $this->assertSame(1, Cliente::count());
        $this->assertSame(1, Inscripcion::count());

        $inscripcion->refresh();

        $this->assertSame(0, (int) $inscripcion->precio_final);
        $this->assertSame(0, $inscripcion->deuda);
        $this->assertSame(0, Inscripcion::porCobrar());
    }

    /** Sin --confirmar solo cuenta: nada se toca por escribir el comando. */
    public function test_sin_confirmar_no_toca_nada(): void
    {
        $this->importada();

        $this->artisan('datos:quitar-pagos-importados')->assertSuccessful();

        $this->assertSame(1, Pago::count());
    }

    /** Y no se lleva por delante un cobro de verdad hecho en el mesón. */
    public function test_los_pagos_del_meson_no_se_tocan(): void
    {
        $this->importada();

        $cliente = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 30000,
            'precio_final' => 30000,
        ]);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => 30000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $this->artisan('datos:quitar-pagos-importados --confirmar')->assertSuccessful();

        $this->assertSame(1, Pago::count());
        $this->assertSame(30000, (int) $inscripcion->refresh()->precio_final);
    }
}
