<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Los filtros rápidos de Socios, Membresías y Pagos: los de baja como un
 * filtro más, a quién llamar (se fueron o vencieron este mes), a quién pedirle
 * el celular, y los pagos por medio.
 */
class FiltrosRapidosTest extends CasoConCatalogos
{
    private function props(string $url): array
    {
        return $this->actingAs($this->administrador())->get($url)->assertOk()->viewData('page')['props'];
    }

    private function membresia(Cliente $socio, int $estado, string $vence): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'fecha_inicio' => now()->parse($vence)->subMonth(),
            'fecha_vencimiento' => $vence,
        ]);
    }

    public function test_socios_de_baja_y_los_que_se_fueron_este_mes(): void
    {
        $activo = Cliente::factory()->create(['activo' => true, 'celular' => null]);
        $this->membresia($activo, 100, now()->addDays(20)->toDateString());

        $recien = Cliente::factory()->create(['activo' => false]);
        $this->membresia($recien, 102, now()->subDays(10)->toDateString());

        $antiguo = Cliente::factory()->create(['activo' => false]);
        $this->membresia($antiguo, 102, now()->subYear()->toDateString());

        $resumen = $this->props('/panel/clientes')['resumen'];
        $this->assertSame(1, $resumen['total']);
        $this->assertSame(2, $resumen['bajas']);
        $this->assertSame(1, $resumen['se_fueron']);
        $this->assertSame(1, $resumen['sin_celular']);

        $this->assertSame([(string) $recien->uuid], collect($this->props('/panel/clientes?filtro=se_fueron')['clientes']['data'])->pluck('uuid')->map(fn ($u) => (string) $u)->all());
        $this->assertCount(2, $this->props('/panel/clientes?filtro=bajas')['clientes']['data']);
        // El enlace viejo sigue sirviendo.
        $this->assertCount(2, $this->props('/panel/clientes?bajas=1')['clientes']['data']);
    }

    public function test_membresias_que_vencieron_este_mes_sin_renovar(): void
    {
        $sinRenovar = Cliente::factory()->create();
        $this->membresia($sinRenovar, 102, now()->subDays(5)->toDateString());

        // Venció, pero renovó: no hay que llamarlo.
        $renovo = Cliente::factory()->create();
        $this->membresia($renovo, 102, now()->subDays(5)->toDateString());
        $this->membresia($renovo, 100, now()->addMonth()->toDateString());

        $vieja = Cliente::factory()->create();
        $this->membresia($vieja, 102, now()->subYear()->toDateString());

        $this->assertSame(1, $this->props('/panel/inscripciones')['resumen']['vencieron']);
    }

    public function test_pagos_por_medio(): void
    {
        $socio = Cliente::factory()->create();
        $inscripcion = $this->membresia($socio, 100, now()->addMonth()->toDateString());
        [$efectivo, $otro] = MetodoPago::where('activo', true)->orderBy('id')->take(2)->get();

        foreach ([$efectivo, $efectivo, $otro] as $m) {
            Pago::create([
                'id_inscripcion' => $inscripcion->id, 'id_cliente' => $socio->id, 'monto_total' => 10000, 'monto_abonado' => 1000,
                'monto_pendiente' => 0, 'id_estado' => 201, 'tipo_pago' => 'parcial', 'id_metodo_pago' => $m->id, 'fecha_pago' => now(),
            ]);
        }

        $props = $this->props('/panel/pagos');
        $this->assertSame(2, collect($props['medios'])->firstWhere('valor', (string) $efectivo->id)['cantidad']);

        $this->assertCount(2, $this->props("/panel/pagos?medio={$efectivo->id}")['pagos']['data']);
    }
}
