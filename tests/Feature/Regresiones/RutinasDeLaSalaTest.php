<?php

namespace Tests\Feature\Regresiones;

use App\Models\Rutina;
use App\Models\RutinaEjercicio;
use App\Support\RutinasDeEjemplo;
use Tests\CasoConCatalogos;

/**
 * Las rutinas del QR de la sala: se cargan enteras, cada combinación de
 * objetivo y días tiene algo, y la página muestra la variante de cada
 * ejercicio y las otras rutinas del mismo objetivo.
 */
class RutinasDeLaSalaTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('rutinas:ejemplos')->assertSuccessful();
    }

    public function test_se_cargan_y_se_pueden_volver_a_cargar(): void
    {
        $this->assertSame(count(RutinasDeEjemplo::rutinas()), Rutina::count());

        // Otra vez: no se duplican.
        $this->artisan('rutinas:ejemplos')->assertSuccessful();
        $this->assertSame(count(RutinasDeEjemplo::rutinas()), Rutina::count());
    }

    /** Cada rutina tiene tantos días como dice y ningún día vacío. */
    public function test_cada_rutina_esta_completa(): void
    {
        foreach (Rutina::with('dias.ejercicios')->get() as $rutina) {
            $this->assertCount($rutina->dias_por_semana, $rutina->dias, $rutina->nombre);

            foreach ($rutina->dias as $dia) {
                $this->assertNotEmpty($dia->ejercicios, "{$rutina->nombre}, día {$dia->numero}");
            }
        }
    }

    /** Las máquinas traen qué hacer si están ocupadas. */
    public function test_las_maquinas_traen_alternativa(): void
    {
        $sinAlternativa = RutinaEjercicio::with('ejercicio')->get()
            ->filter(fn ($l) => $l->ejercicio->equipo === 'maquina' && ! $l->id_alternativa);

        $this->assertCount(0, $sinAlternativa, $sinAlternativa->pluck('ejercicio.nombre')->unique()->implode(', '));
    }

    /** Todos los objetivos tienen rutina para cada cantidad de días que se ofrece. */
    public function test_siempre_sale_una_rutina_del_objetivo_elegido(): void
    {
        foreach (array_keys(Rutina::OBJETIVOS) as $objetivo) {
            foreach (array_keys(Rutina::NIVELES) as $nivel) {
                foreach ([2, 3, 4, 5, 6] as $dias) {
                    $rutina = \App\Support\RutinaSugerida::buscar($objetivo, $nivel, $dias);
                    $this->assertSame($objetivo, $rutina->objetivo, "{$objetivo}/{$nivel}/{$dias}");
                }
            }
        }
    }

    public function test_la_pagina_muestra_la_alternativa_y_las_variantes(): void
    {
        $this->get('/rutina?objetivo=fuerza&nivel=algo&dias=4')
            ->assertOk()
            ->assertSee('Torso y pierna · 4 días')
            ->assertSee('Si está ocupada:', false)
            ->assertSee('Otras variantes')
            ->assertSee('Empuje, tirón y pierna · 6 días');
    }
}
