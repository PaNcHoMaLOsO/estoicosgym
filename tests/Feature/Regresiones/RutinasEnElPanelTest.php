<?php

namespace Tests\Feature\Regresiones;

use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Support\RutinasDeEjemplo;
use Tests\CasoConCatalogos;

/**
 * Las rutinas de la sala se editan desde el panel: enteras, se duplican para
 * armar otra variante y se apagan. Y el catálogo de ejercicios, también.
 */
class RutinasEnElPanelTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();
        RutinasDeEjemplo::cargar();
    }

    private function id(string $nombre): int
    {
        return Ejercicio::where('nombre', $nombre)->value('id');
    }

    private function datos(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Mi rutina de prueba',
            'objetivo' => 'fuerza',
            'nivel' => 'algo',
            'descripcion' => 'Para probar.',
            'activa' => true,
            'dias' => [
                ['titulo' => 'Torso', 'foco' => '', 'ejercicios' => [
                    ['id_ejercicio' => $this->id('Press de pecho en máquina'), 'id_alternativa' => $this->id('Flexiones de brazos'), 'series' => 3, 'repeticiones' => '10', 'descanso_seg' => 90, 'nota' => ''],
                    ['id_ejercicio' => $this->id('Jalón al pecho'), 'id_alternativa' => '', 'series' => 3, 'repeticiones' => '10 a 12', 'descanso_seg' => 60, 'nota' => 'Sin balancearse'],
                ]],
                ['titulo' => 'Pierna', 'foco' => 'Glúteos', 'ejercicios' => [
                    ['id_ejercicio' => $this->id('Prensa de piernas'), 'id_alternativa' => '', 'series' => 4, 'repeticiones' => '12', 'descanso_seg' => 120, 'nota' => ''],
                ]],
            ],
        ], $extra);
    }

    public function test_se_crea_una_rutina_con_sus_dias(): void
    {
        $this->actingAs($this->administrador())->post('/panel/rutinas', $this->datos())->assertSessionHasNoErrors();

        $rutina = Rutina::with('dias.ejercicios')->where('nombre', 'Mi rutina de prueba')->sole();
        $this->assertSame(2, $rutina->dias_por_semana);
        $this->assertCount(2, $rutina->dias[0]->ejercicios);
        $this->assertSame($this->id('Flexiones de brazos'), $rutina->dias[0]->ejercicios[0]->id_alternativa);
        $this->assertSame('Sin balancearse', $rutina->dias[0]->ejercicios[1]->nota);
    }

    public function test_editar_rehace_los_dias(): void
    {
        $rutina = Rutina::first();
        $datos = $this->datos(['nombre' => 'Otra', 'dias' => [$this->datos()['dias'][1]]]);

        $this->actingAs($this->administrador())->put("/panel/rutinas/{$rutina->uuid}", $datos)->assertSessionHasNoErrors();

        $rutina->refresh()->load('dias.ejercicios');
        $this->assertSame('Otra', $rutina->nombre);
        $this->assertSame(1, $rutina->dias_por_semana);
        $this->assertCount(1, $rutina->dias);
    }

    public function test_un_dia_sin_ejercicios_se_rechaza(): void
    {
        $datos = $this->datos(['dias' => [['titulo' => 'Vacío', 'foco' => '', 'ejercicios' => []]]]);

        $this->actingAs($this->administrador())->post('/panel/rutinas', $datos)->assertSessionHasErrors('dias.0.ejercicios');
    }

    public function test_duplicar_arma_una_variante_apagada(): void
    {
        $original = Rutina::with('dias.ejercicios')->first();

        $this->actingAs($this->administrador())->post("/panel/rutinas/{$original->uuid}/duplicar")->assertRedirect();

        $copia = Rutina::with('dias.ejercicios')->where('nombre', $original->nombre . ' (copia)')->sole();
        $this->assertFalse($copia->activa);
        $this->assertSame($original->dias->count(), $copia->dias->count());
        $this->assertSame($original->dias[0]->ejercicios->count(), $copia->dias[0]->ejercicios->count());
    }

    public function test_apagada_no_sale_en_la_web(): void
    {
        $rutina = Rutina::where('nombre', 'Torso y pierna · 4 días')->sole();

        $this->actingAs($this->administrador())->patch("/panel/rutinas/{$rutina->uuid}/alternar");
        $this->assertFalse($rutina->fresh()->activa);

        $this->followingRedirects()->get('/rutina?objetivo=fuerza&nivel=algo&dias=4')->assertOk()->assertDontSee('Torso y pierna · 4 días');
        $this->get('/rutina')->assertOk()->assertDontSee('Torso y pierna · 4 días');
        $this->get("/rutinas/{$rutina->slug}")->assertNotFound();
    }

    public function test_el_catalogo_de_ejercicios_se_edita(): void
    {
        $this->actingAs($this->administrador())->post('/panel/ejercicios', [
            'nombre' => 'Remo T en máquina', 'zona' => 'espalda', 'equipo' => 'maquina', 'indicacion' => 'Pecho al cojín.', 'activo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ejercicios', ['nombre' => 'Remo T en máquina', 'zona' => 'espalda']);

        $this->actingAs($this->administrador())->post('/panel/ejercicios', [
            'nombre' => 'Remo T en máquina', 'zona' => 'espalda', 'equipo' => 'maquina',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_recepcion_no_edita_rutinas(): void
    {
        $this->actingAs($this->recepcionista())->get('/panel/rutinas')->assertForbidden();
        $this->actingAs($this->recepcionista())->post('/panel/rutinas', $this->datos())->assertForbidden();
    }
}
