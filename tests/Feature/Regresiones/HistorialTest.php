<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\HistorialCambio;
use Illuminate\Support\Str;
use Tests\CasoConCatalogos;

/**
 * El historial se busca por socio, se filtra por tipo y se ve más allá de los
 * últimos 100 movimientos.
 */
class HistorialTest extends CasoConCatalogos
{
    private function cambio(Cliente $socio, string $tipo, int $haceDias = 0): void
    {
        HistorialCambio::create([
            'uuid' => (string) Str::uuid(),
            'tipo_cambio' => $tipo,
            'entidad' => 'cliente',
            'entidad_id' => $socio->id,
            'cliente_id' => $socio->id,
            'estado_anterior' => 100,
            'estado_nuevo' => 101,
            'fecha_cambio' => now()->subDays($haceDias),
        ]);
    }

    private function historial(array $consulta = []): array
    {
        return $this->actingAs($this->administrador())
            ->get('/panel/historial?' . http_build_query($consulta))
            ->assertOk()
            ->viewData('page')['props'];
    }

    public function test_se_busca_por_socio_y_se_filtra_por_tipo(): void
    {
        $camila = Cliente::factory()->create(['nombres' => 'Camila', 'apellido_paterno' => 'Rojas', 'apellido_materno' => 'Soto']);
        $pedro = Cliente::factory()->create(['nombres' => 'Pedro', 'apellido_paterno' => 'Díaz', 'apellido_materno' => 'Soto']);

        $this->cambio($camila, 'pausa');
        $this->cambio($camila, 'reanudacion');
        $this->cambio($pedro, 'pausa');

        $this->assertCount(3, $this->historial()['movimientos']);
        $this->assertCount(2, $this->historial(['buscar' => 'camila'])['movimientos']);
        $this->assertCount(2, $this->historial(['tipo' => 'pausa'])['movimientos']);
        $this->assertCount(1, $this->historial(['buscar' => 'camila', 'tipo' => 'pausa'])['movimientos']);
    }

    public function test_se_ven_mas_de_los_ultimos_100(): void
    {
        $socio = Cliente::factory()->create();

        foreach (range(1, 105) as $dia) {
            $this->cambio($socio, 'pausa', $dia);
        }

        $primera = $this->historial();
        $this->assertCount(100, $primera['movimientos']);
        $this->assertTrue($primera['hayMas']);

        $todas = $this->historial(['cuantos' => 200]);
        $this->assertCount(105, $todas['movimientos']);
        $this->assertFalse($todas['hayMas']);
    }
}
