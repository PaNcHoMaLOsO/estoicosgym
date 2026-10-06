<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Support\IngresosPorMetodo;
use Tests\CasoConCatalogos;

/**
 * Un pago mixto tiene UNA forma en todo el sistema (App\Support\PagoMixto).
 *
 * Cobrar dejaba una fila con los dos medios; inscribir, renovar y el alta,
 * una fila por parte, con tres o cuatro partes y hasta «efectivo + efectivo».
 * Aquí se vigila que los tres caminos guarden lo mismo y rechacen lo mismo.
 */
class PagoMixtoUnicoTest extends CasoConCatalogos
{
    /** Mensual: $40.000. */
    private const PLAN_MENSUAL = 4;

    /** @return list<MetodoPago> */
    private function medios(): array
    {
        return MetodoPago::where('activo', true)->orderBy('id')->take(3)->get()->all();
    }

    /** @param list<array{0:int,1:int}> $partes [id del medio, monto] */
    private function detalle(array $partes): string
    {
        return json_encode(array_map(fn ($p) => ['id_metodo_pago' => $p[0], 'monto' => $p[1]], $partes));
    }

    private function inscribir(string $detalle)
    {
        return $this->actingAs($this->administrador())->post('/panel/inscripciones', [
            'form_submit_token' => uniqid('t', true),
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'mixto',
            'detalle_pagos_mixto' => $detalle,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    private function renovar(string $detalle)
    {
        $anterior = Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => now()->subDays(25),
            'fecha_vencimiento' => now()->addDays(5),
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);

        return $this->actingAs($this->administrador())->post("/panel/inscripciones/{$anterior->uuid}/renovar", [
            'form_submit_token' => uniqid('t', true),
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->addDays(6)->format('Y-m-d'),
            'tipo_pago' => 'mixto',
            'detalle_pagos_mixto' => $detalle,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    private function alta(string $detalle)
    {
        return $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'completo',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '+56 9 1234 5674',
            'run_pasaporte' => '11.111.111-1',
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->toDateString(),
            'fecha_pago' => now()->toDateString(),
            'tipo_pago' => 'mixto',
            // Un medio que quedó marcado en la pantalla no pisa el reparto.
            'id_metodo_pago' => $this->medios()[2]->id,
            'detalle_pagos_mixto' => $detalle,
        ]);
    }

    private function assertUnaFilaMixta(int $uno, int $dos, int $monto1, int $monto2, int $pendiente, int $estado): void
    {
        $pagos = Pago::all();

        $this->assertCount(1, $pagos, 'El mixto dejó más de una fila.');
        $pago = $pagos->first();
        $this->assertSame('mixto', $pago->tipo_pago);
        $this->assertSame([$uno, $dos], [(int) $pago->id_metodo_pago, (int) $pago->id_metodo_pago2]);
        $this->assertSame([$monto1, $monto2], [(int) $pago->monto_metodo1, (int) $pago->monto_metodo2]);
        $this->assertSame($monto1 + $monto2, (int) $pago->monto_abonado);
        $this->assertSame($pendiente, (int) $pago->monto_pendiente);
        $this->assertSame($estado, (int) $pago->id_estado);
    }

    public function test_inscribir_con_mixto_deja_una_fila_con_los_dos_medios(): void
    {
        [$uno, $dos] = $this->medios();

        $this->inscribir($this->detalle([[$uno->id, 25000], [$dos->id, 15000]]))->assertSessionHasNoErrors();

        $this->assertUnaFilaMixta($uno->id, $dos->id, 25000, 15000, 0, EstadosCodigo::PAGO_PAGADO);
    }

    public function test_renovar_con_mixto_deja_una_fila_con_los_dos_medios(): void
    {
        [$uno, $dos] = $this->medios();

        $this->renovar($this->detalle([[$uno->id, 30000], [$dos->id, 10000]]))->assertSessionHasNoErrors();

        $this->assertUnaFilaMixta($uno->id, $dos->id, 30000, 10000, 0, EstadosCodigo::PAGO_PAGADO);
    }

    public function test_el_alta_con_mixto_deja_una_fila_con_los_dos_medios(): void
    {
        [$uno, $dos] = $this->medios();

        $this->alta($this->detalle([[$uno->id, 20000], [$dos->id, 20000]]))->assertSessionHasNoErrors();

        $this->assertUnaFilaMixta($uno->id, $dos->id, 20000, 20000, 0, EstadosCodigo::PAGO_PAGADO);
    }

    /** Un reparto que no llega al precio sigue valiendo: es un abono por dos vías. */
    public function test_un_mixto_parcial_al_inscribir_queda_parcial_con_su_saldo(): void
    {
        [$uno, $dos] = $this->medios();

        $this->inscribir($this->detalle([[$uno->id, 10000], [$dos->id, 5000]]))->assertSessionHasNoErrors();

        $this->assertUnaFilaMixta($uno->id, $dos->id, 10000, 5000, 25000, EstadosCodigo::PAGO_PARCIAL);
    }

    public function test_un_mixto_parcial_en_el_alta_queda_parcial_con_su_saldo(): void
    {
        [$uno, $dos] = $this->medios();

        $this->alta($this->detalle([[$uno->id, 10000], [$dos->id, 5000]]))->assertSessionHasNoErrors();

        $this->assertUnaFilaMixta($uno->id, $dos->id, 10000, 5000, 25000, EstadosCodigo::PAGO_PARCIAL);
    }

    /** El mismo medio dos veces no es un mixto, en ninguno de los tres caminos. */
    public function test_el_mismo_medio_dos_veces_se_rechaza(): void
    {
        [$uno] = $this->medios();
        $detalle = $this->detalle([[$uno->id, 20000], [$uno->id, 20000]]);

        $this->inscribir($detalle)->assertSessionHasErrors('detalle_pagos_mixto');
        $this->renovar($detalle)->assertSessionHasErrors('detalle_pagos_mixto');
        $this->alta($detalle)->assertSessionHasErrors('detalle_pagos_mixto');

        $this->assertSame(0, Pago::count());
    }

    /** Tres partes no se recortan a dos: se rechazan. */
    public function test_tres_partes_se_rechazan(): void
    {
        [$uno, $dos, $tres] = $this->medios();
        $detalle = $this->detalle([[$uno->id, 10000], [$dos->id, 10000], [$tres->id, 10000]]);

        $this->inscribir($detalle)->assertSessionHasErrors('detalle_pagos_mixto');
        $this->renovar($detalle)->assertSessionHasErrors('detalle_pagos_mixto');
        $this->alta($detalle)->assertSessionHasErrors('detalle_pagos_mixto');

        $this->assertSame(0, Pago::count());
    }

    /** Un medio dado de baja no entra en un reparto. */
    public function test_un_medio_inactivo_se_rechaza(): void
    {
        [$uno] = $this->medios();
        $inactivo = MetodoPago::where('activo', false)->firstOrFail();

        $this->inscribir($this->detalle([[$uno->id, 20000], [$inactivo->id, 20000]]))
            ->assertSessionHasErrors('detalle_pagos_mixto');

        $this->assertSame(0, Pago::count());
    }

    /** En la caja cada medio cuenta su parte una vez, venga de donde venga el mixto. */
    public function test_la_caja_cuenta_cada_medio_una_vez(): void
    {
        [$uno, $dos] = $this->medios();

        $this->inscribir($this->detalle([[$uno->id, 25000], [$dos->id, 15000]]))->assertSessionHasNoErrors();

        $porMedio = IngresosPorMetodo::en(fn ($q) => $q->whereDate('fecha_pago', today()))->pluck('total', 'nombre');

        $this->assertSame(25000, $porMedio[$uno->nombre]);
        $this->assertSame(15000, $porMedio[$dos->nombre]);
        $this->assertSame(40000, (int) $porMedio->sum());
    }
}
