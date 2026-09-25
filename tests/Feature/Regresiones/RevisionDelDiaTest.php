<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Cache;
use Tests\CasoConCatalogos;

/**
 * La revisión del día corre sola al abrir el panel.
 *
 * Dependía de una tarea de Windows que quedó apagada, y no corría nunca:
 * membresías activas con la fecha vencida y socios «activos» sin nada vigente,
 * sin un solo error a la vista.
 */
class RevisionDelDiaTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.revision_en_pruebas' => true]);
        Cache::flush();
    }

    public function test_abrir_el_panel_marca_las_vencidas_y_da_de_baja_a_quien_quedo_sin_plan(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $vencida = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => now()->subDays(40),
            'fecha_vencimiento' => now()->subDays(9),
        ]);

        $this->actingAs($this->administrador())->get('/panel')->assertOk();

        $this->assertSame(102, (int) $vencida->fresh()->id_estado);
        $this->assertFalse((bool) $socio->fresh()->activo);
    }

    /** Una sola vez al día: la segunda visita no la repite. */
    public function test_no_se_repite_en_el_mismo_dia(): void
    {
        $this->actingAs($this->administrador())->get('/panel')->assertOk();

        $socio = Cliente::factory()->create(['activo' => true]);
        $vencida = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => now()->subDays(40),
            'fecha_vencimiento' => now()->subDays(9),
        ]);

        $this->actingAs($this->administrador())->get('/panel')->assertOk();

        $this->assertSame(100, (int) $vencida->fresh()->id_estado);
    }
}
