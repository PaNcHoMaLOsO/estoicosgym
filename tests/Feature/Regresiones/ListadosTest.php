<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Las listas de Clientes, Inscripciones y Pagos: sus filtros y lo que dicen.
 *
 * Lo que se vigila: que quien renovó no salga como «vencido» (renovar deja la
 * anterior vencida), que la fila del socio muestre la membresía que vale, y
 * que un pago viejo no diga que se debe algo que ya se terminó de pagar.
 */
class ListadosTest extends CasoConCatalogos
{
    private function membresia(Cliente $cliente, int $estado, int $diasParaVencer, int $precio = 40000): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'fecha_inicio' => today()->addDays($diasParaVencer - 30),
            'fecha_vencimiento' => today()->addDays($diasParaVencer),
        ]);
    }

    private function socio(): Cliente
    {
        return Cliente::factory()->create(['activo' => true]);
    }

    private function uuids(string $url, string $clave): array
    {
        return collect($this->actingAs($this->administrador())->get($url)->viewData('page')['props'][$clave]['data'])
            ->pluck('uuid')->map(fn ($u) => (string) $u)->sort()->values()->all();
    }

    public function test_quien_renovo_no_sale_como_vencido(): void
    {
        $renovo = $this->socio();
        $this->membresia($renovo, 102, -5);
        $this->membresia($renovo, 100, 25);

        $seFue = $this->socio();
        $this->membresia($seFue, 102, -5);

        $this->assertSame([(string) $seFue->uuid], $this->uuids('/panel/clientes?filtro=vencidos', 'clientes'));
        $this->assertSame([(string) $renovo->uuid], $this->uuids('/panel/clientes?filtro=al_dia', 'clientes'));

        $resumen = $this->actingAs($this->administrador())->get('/panel/clientes')->viewData('page')['props']['resumen'];
        $this->assertSame([1, 1], [$resumen['activos'], $resumen['vencidos']]);
    }

    public function test_la_fila_del_socio_muestra_la_membresia_que_vale(): void
    {
        $socio = $this->socio();
        $this->membresia($socio, 100, 20);
        // Una cancelada que vence más tarde no manda.
        $this->membresia($socio, 103, 60);

        $fila = $this->actingAs($this->administrador())->get('/panel/clientes')->viewData('page')['props']['clientes']['data'][0];

        $this->assertSame([100, 20], [(int) $fila['id_estado'], $fila['dias']]);
    }

    public function test_vencen_esta_semana_y_sin_plan(): void
    {
        $pronto = $this->socio();
        $this->membresia($pronto, 100, 3);
        $lejos = $this->socio();
        $this->membresia($lejos, 100, 25);
        $nuevo = $this->socio();

        $this->assertSame([(string) $pronto->uuid], $this->uuids('/panel/clientes?filtro=por_vencer', 'clientes'));
        $this->assertSame([(string) $nuevo->uuid], $this->uuids('/panel/clientes?filtro=sin_plan', 'clientes'));
        $this->assertSame(
            [(string) Inscripcion::where('id_cliente', $pronto->id)->value('uuid')],
            $this->uuids('/panel/inscripciones?filtro=por_vencer', 'inscripciones')
        );
    }

    public function test_un_abono_viejo_dice_que_la_membresia_ya_esta_pagada(): void
    {
        $inscripcion = $this->membresia($this->socio(), 100, 20);
        $metodo = MetodoPago::orderBy('id')->first()->id;

        foreach ([20000, 20000] as $monto) {
            Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_total' => 40000,
                'monto_abonado' => $monto,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'tipo_pago' => 'parcial',
                'id_metodo_pago' => $metodo,
                'fecha_pago' => today()->format('Y-m-d'),
            ]);
        }
        $inscripcion->recalcularSusPagos();

        $filas = $this->actingAs($this->administrador())->get('/panel/pagos')->viewData('page')['props']['pagos']['data'];

        $this->assertSame([0, 0], collect($filas)->pluck('debe_hoy')->all());

        $inscripciones = $this->actingAs($this->administrador())->get('/panel/inscripciones')->viewData('page')['props'];
        $this->assertSame(0, $inscripciones['inscripciones']['data'][0]['debe']);
        $this->assertSame(0, $inscripciones['resumen']['con_deuda']);
    }

    public function test_con_deuda_lista_lo_mismo_que_suma_por_cobrar(): void
    {
        $debe = $this->membresia($this->socio(), 100, 20);
        $this->membresia($this->socio(), 103, 20); // cancelada: no se cobra

        $this->assertSame([(string) $debe->uuid], $this->uuids('/panel/inscripciones?filtro=con_deuda', 'inscripciones'));
    }

    /** El pase diario del catálogo de pruebas: sin meses y de un día. */
    private function pase(Cliente $cliente, int $estado = 102): Inscripcion
    {
        $pase = \App\Models\Membresia::where('duracion_meses', 0)->where('duracion_dias', '<=', 1)->firstOrFail();

        return Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => $pase->id,
            'id_estado' => $estado,
            'precio_base' => 5000,
            'precio_final' => 5000,
            'fecha_inicio' => today()->subDays(3),
            'fecha_vencimiento' => today()->subDays(3),
        ]);
    }

    /**
     * EL QUE IMPORTA: quien solo vino por el día no llena las listas.
     *
     * Los pases vencen al día siguiente: mezclados, «vencidos» y «se fueron
     * sin renovar» se llenaban de gente que nunca pensó quedarse.
     */
    public function test_quien_solo_compro_pases_va_aparte(): void
    {
        $dePaso = $this->socio();
        $this->pase($dePaso);
        $socio = $this->socio();
        $this->membresia($socio, 102, -5);

        $this->assertSame([(string) $socio->uuid], $this->uuids('/panel/clientes', 'clientes'));
        $this->assertSame([(string) $socio->uuid], $this->uuids('/panel/clientes?filtro=vencidos', 'clientes'));
        $this->assertSame([(string) $dePaso->uuid], $this->uuids('/panel/clientes?filtro=pases', 'clientes'));

        // Buscándolo por su nombre, se le encuentra igual.
        $this->assertContains((string) $dePaso->uuid, $this->uuids('/panel/clientes?buscar=' . urlencode($dePaso->nombres), 'clientes'));

        $resumen = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props'];
        $this->assertSame(1, $resumen['cifras']['sin_renovar']);
        $this->assertSame(1, $resumen['cifras']['socios']);
    }

    public function test_un_socio_que_ademas_compro_un_pase_sigue_con_su_plan(): void
    {
        $socio = $this->socio();
        $this->membresia($socio, 100, 20);
        $this->pase($socio, 100);

        $fila = $this->actingAs($this->administrador())->get('/panel/clientes')->viewData('page')['props']['clientes']['data'][0];

        $this->assertSame([false, 100], [$fila['es_pase'], (int) $fila['id_estado']]);
    }

    public function test_los_pases_van_aparte_en_inscripciones_y_pagos(): void
    {
        $pase = $this->pase($this->socio());
        $plan = $this->membresia($this->socio(), 102, -5);

        foreach ([$pase, $plan] as $inscripcion) {
            Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_total' => $inscripcion->precio_final,
                'monto_abonado' => $inscripcion->precio_final,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'tipo_pago' => 'completo',
                'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
                'fecha_pago' => today()->format('Y-m-d'),
            ]);
        }

        $this->assertSame([(string) $plan->uuid], $this->uuids('/panel/inscripciones?filtro=vencidas', 'inscripciones'));
        $this->assertSame([(string) $pase->uuid], $this->uuids('/panel/inscripciones?filtro=pases', 'inscripciones'));

        $pagos = $this->actingAs($this->administrador())->get('/panel/pagos')->viewData('page')['props'];
        $this->assertCount(1, $pagos['pagos']['data']);
        $this->assertSame(1, $pagos['cantidades']['pases']);
        // La plata del pase sí está en lo que entró hoy.
        $this->assertSame(45000, $pagos['resumen']['recaudado_hoy']);
    }

    /** La ficha del socio dice hasta cuándo está pausada: con eso ofrece «Reanudar». */
    public function test_la_ficha_del_socio_sabe_de_su_pausa(): void
    {
        $socio = $this->socio();
        $pausada = $this->membresia($socio, 101, 20);
        $pausada->forceFill(['pausada' => true, 'fecha_pausa_fin' => today()->addDays(10)])->save();

        $props = $this->actingAs($this->administrador())->get("/panel/clientes/{$socio->uuid}")->viewData('page')['props'];
        $fila = collect($props['inscripciones'])->firstWhere('id_estado', 101);

        $this->assertSame(today()->addDays(10)->format('d/m/Y'), $fila['pausada_hasta']);
    }

    /**
     * Un cobro que cerró el saldo dice cuántos cobros hubo.
     *
     * «$5.000» con la membresía de $25.000 marcada como pagada parece un error
     * del sistema y manda a alguien a revisarlo a mano. Con «entre 2 cobros»
     * se entiende a la primera.
     */
    public function test_la_fila_del_pago_dice_cuantos_cobros_hubo(): void
    {
        $inscripcion = $this->membresia($this->socio(), 100, 20, 25000);
        $metodo = MetodoPago::orderBy('id')->first()->id;

        foreach ([20000, 5000] as $monto) {
            Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_total' => 25000,
                'monto_abonado' => $monto,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'tipo_pago' => 'parcial',
                'id_metodo_pago' => $metodo,
                'fecha_pago' => today()->format('Y-m-d'),
            ]);
        }
        $inscripcion->recalcularSusPagos();

        $filas = $this->actingAs($this->administrador())->get('/panel/pagos')->viewData('page')['props']['pagos']['data'];

        $this->assertSame([2, 2], collect($filas)->pluck('cobros')->all());
        $this->assertSame([0, 0], collect($filas)->pluck('debe_hoy')->all());
    }
    // ---------- Ordenar y filtrar por plan ----------

    /**
     * DE MAYOR A MENOR, Y AL REVÉS.
     *
     * Con mil setecientas membresías, «¿cuál es la más cara de este mes?» no se
     * responde bajando la lista. Lo que se prueba es que el orden lo haga la
     * base —no el navegador con la página que le tocó—, porque si no, ordenar
     * solo acomodaría las veinticinco filas que se están viendo.
     */
    public function test_las_inscripciones_se_ordenan_por_monto(): void
    {
        $barata = $this->membresia($this->socio(), 100, 10, 25000);
        $cara = $this->membresia($this->socio(), 100, 20, 250000);
        $media = $this->membresia($this->socio(), 100, 30, 100000);

        $mayor = $this->enOrden('/panel/inscripciones?orden=monto_desc');
        $this->assertSame(
            [(string) $cara->uuid, (string) $media->uuid, (string) $barata->uuid],
            $mayor,
        );

        $this->assertSame(array_reverse($mayor), $this->enOrden('/panel/inscripciones?orden=monto_asc'));
    }

    /** El plan elegido deja fuera a los demás. */
    public function test_se_puede_ver_un_solo_plan(): void
    {
        $mensual = $this->membresia($this->socio(), 100, 10);

        $anual = Inscripcion::factory()->create([
            'id_cliente' => $this->socio()->id,
            'id_membresia' => 1,
            'id_estado' => 100,
            'precio_base' => 250000,
            'precio_final' => 250000,
            'fecha_inicio' => today(),
            'fecha_vencimiento' => today()->addYear(),
        ]);

        $this->assertSame([(string) $anual->uuid], $this->enOrden('/panel/inscripciones?plan=1'));
        $this->assertSame([(string) $mensual->uuid], $this->enOrden('/panel/inscripciones?plan=4'));

        // Y los planes viajan a la pantalla para poder elegirlos.
        $planes = $this->actingAs($this->administrador())->get('/panel/inscripciones')
            ->viewData('page')['props']['planes'];
        $this->assertContains('Mensual', array_column($planes, 'nombre'));
    }

    /** Ordenar no puede deshacer el filtro que ya estaba puesto. */
    public function test_el_orden_respeta_el_filtro(): void
    {
        $vigente = $this->membresia($this->socio(), 100, 10, 250000);
        $this->membresia($this->socio(), 102, -10, 400000);

        $this->assertSame(
            [(string) $vigente->uuid],
            $this->enOrden('/panel/inscripciones?filtro=al_dia&orden=monto_desc'),
        );
    }

    /** Los pagos también: el cobro más grande, arriba. */
    public function test_los_pagos_se_ordenan_por_monto(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio, 100, 10, 250000);

        $chico = Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 250000,
            'monto_abonado' => 10000,
            'fecha_pago' => today()->subDays(2),
            'id_metodo_pago' => MetodoPago::value('id'),
            'id_estado' => 202,
        ]);
        $grande = Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 250000,
            'monto_abonado' => 240000,
            'fecha_pago' => today()->subDays(5),
            'id_metodo_pago' => MetodoPago::value('id'),
            'id_estado' => 201,
        ]);

        $porMonto = collect(
            $this->actingAs($this->administrador())->get('/panel/pagos?orden=monto_desc')
                ->viewData('page')['props']['pagos']['data']
        )->pluck('uuid')->map(fn ($u) => (string) $u)->all();

        $this->assertSame([(string) $grande->uuid, (string) $chico->uuid], $porMonto);
    }

    /** Los uuid de una lista, EN EL ORDEN EN QUE VIENEN. */
    private function enOrden(string $url): array
    {
        return collect($this->actingAs($this->administrador())->get($url)->viewData('page')['props']['inscripciones']['data'])
            ->pluck('uuid')->map(fn ($u) => (string) $u)->all();
    }
}
