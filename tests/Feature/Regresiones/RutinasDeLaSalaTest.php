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

    /** Quien entrena 5 o 6 días encuentra una rutina de 5 o 6 días para su nivel, sin aflojar los días. */
    public function test_hay_rutinas_de_5_y_6_dias_para_cada_nivel(): void
    {
        foreach (\App\Support\EntrenamientoDeHoy::OBJETIVO_POR_NIVEL as $nivel => $objetivo) {
            foreach ([5, 6] as $dias) {
                $rutina = \App\Support\RutinaSugerida::buscar($objetivo, $nivel, $dias);
                $this->assertSame($dias, $rutina->dias_por_semana, "{$objetivo}/{$nivel}/{$dias}");
            }

            $this->assertTrue(Rutina::where(['nivel' => $nivel, 'dias_por_semana' => 5, 'activa' => true])->exists(), "Falta 5 días para {$nivel}");
        }

        $this->assertTrue(Rutina::where(['objetivo' => 'bajar_grasa', 'dias_por_semana' => 5, 'nivel' => 'algo'])->exists());
        $this->assertSame(['Pecho', 'Espalda', 'Piernas', 'Hombros y brazos', 'Cuerpo completo C'],
            Rutina::where('nombre', 'División por grupos · 5 días')->sole()->dias->pluck('titulo')->all());
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

    /** Cada objetivo y nivel tiene una rutina propia de 2, 3 y 4 días, sin aflojar nada. */
    public function test_cada_objetivo_y_nivel_tiene_2_3_y_4_dias(): void
    {
        foreach (array_keys(Rutina::OBJETIVOS) as $objetivo) {
            foreach (array_keys(Rutina::NIVELES) as $nivel) {
                foreach ([2, 3, 4] as $dias) {
                    $this->assertTrue(
                        Rutina::where(['objetivo' => $objetivo, 'nivel' => $nivel, 'dias_por_semana' => $dias, 'activa' => true])->exists(),
                        "Falta {$objetivo}/{$nivel}/{$dias}"
                    );
                }
            }
        }
    }

    /** Todos los ejercicios del catálogo traen su mapa muscular, con grupos que existen. */
    public function test_cada_ejercicio_del_catalogo_tiene_musculos(): void
    {
        $this->assertSame([], array_values(array_diff(array_keys(RutinasDeEjemplo::EJERCICIOS), array_keys(RutinasDeEjemplo::MUSCULOS))));
        $this->assertSame(0, \App\Models\Ejercicio::whereNull('musculos')->count());

        foreach (RutinasDeEjemplo::MUSCULOS as $nombre => [$principal, $secundarios]) {
            foreach ([$principal, ...$secundarios] as $grupo) {
                $this->assertArrayHasKey($grupo, \App\Models\Ejercicio::MUSCULOS, $nombre);
            }
        }

        foreach (RutinasDeEjemplo::ALTERNATIVAS as $ejercicio => $alternativa) {
            $this->assertArrayHasKey($ejercicio, RutinasDeEjemplo::EJERCICIOS);
            $this->assertArrayHasKey($alternativa, RutinasDeEjemplo::EJERCICIOS);
        }
    }

    /** Volver a cargar no pisa lo que el gimnasio cambió en el panel. */
    public function test_volver_a_cargar_no_pisa_lo_editado(): void
    {
        $rutina = Rutina::where('nombre', 'Primeros pasos · 2 días')->sole();
        $rutina->update(['descripcion' => 'La editó el entrenador.']);
        $rutina->dias()->first()->update(['titulo' => 'Día del entrenador']);
        \App\Models\Ejercicio::where('nombre', 'Plancha')->update(['indicacion' => 'A su manera.']);

        $this->artisan('rutinas:ejemplos')->assertSuccessful();

        $this->assertSame('La editó el entrenador.', $rutina->fresh()->descripcion);
        $this->assertSame('Día del entrenador', $rutina->dias()->first()->titulo);
        $this->assertSame('A su manera.', \App\Models\Ejercicio::where('nombre', 'Plancha')->value('indicacion'));
        $this->assertSame(1, Rutina::where('nombre', 'Primeros pasos · 2 días')->count());
    }

    public function test_la_pagina_muestra_la_alternativa_y_las_variantes(): void
    {
        $this->followingRedirects()->get('/rutina?objetivo=fuerza&nivel=algo&dias=4')
            ->assertOk()
            ->assertSee('Torso y pierna · 4 días')
            ->assertSee('Si está ocupada:', false)
            ->assertSee('Otras variantes')
            ->assertSee('Empuje, tirón y pierna · 6 días');
    }
}
