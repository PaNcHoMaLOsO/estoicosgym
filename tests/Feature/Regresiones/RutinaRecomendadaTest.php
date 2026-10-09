<?php

namespace Tests\Feature\Regresiones;

use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Support\CalentamientoYEstiramiento;
use App\Support\EntrenamientoDeHoy;
use App\Support\RutinaSugerida;
use App\Support\RutinasDeEjemplo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Las rutinas en la web: /rutina hace cuatro preguntas (un formulario GET que
 * funciona sin JavaScript) y arma el entrenamiento de hoy; /rutinas es la
 * lista por objetivo; cada rutina tiene su página con sus días y ejercicios
 * (cada uno con foto o mapa muscular), y /ejercicios muestra lo que hay en la
 * sala. Los QR impresos con ?objetivo=&nivel=&dias= siguen llevando a su rutina.
 */
class RutinaRecomendadaTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();
        RutinasDeEjemplo::cargar();
    }

    public function test_la_lista_agrupa_por_objetivo_y_filtra_sin_javascript(): void
    {
        $this->get('/rutinas')
            ->assertOk()
            ->assertSee('<title>Rutinas de gimnasio | PRO GYM', false)
            ->assertSeeInOrder(['Estoy empezando', 'Bajar de peso', 'Ganar fuerza y músculo', 'Mantenerme'])
            ->assertSee('Primeros pasos · 2 días')
            ->assertSee(route('landing.rutina.ver', 'primeros-pasos-2-dias'), false);

        $this->get('/rutinas?objetivo=fuerza')
            ->assertOk()
            ->assertSee('Fuerza desde cero · 2 días')
            ->assertDontSee('Primeros pasos · 2 días');

        // Los enlaces de antes a la lista siguen llegando.
        $this->get('/rutina?todas=1')->assertRedirect(route('landing.rutinas'));
        $this->get('/rutina?objetivo=fuerza')->assertRedirect(route('landing.rutinas', ['objetivo' => 'fuerza']));
    }

    /** Las cuatro preguntas son un formulario común: sin JavaScript se contestan seguidas y se envían. */
    public function test_las_preguntas_funcionan_sin_javascript(): void
    {
        $respuesta = $this->get('/rutina')
            ->assertOk()
            ->assertSee('<title>Qué entrenar hoy | PRO GYM', false)
            ->assertSee('<form method="GET" action="' . route('landing.rutina') . '"', false)
            ->assertSeeInOrder([
                '¿Cuántos días entrenas a la semana?', 'name="dias" value="2"', 'name="dias" value="6"',
                '¿Cómo vas?', 'Estoy empezando', 'Ya entreno hace un tiempo', 'Entreno hace años',
                '¿Qué entrenaste estos últimos días?', 'type="checkbox" name="hice[]" value="pecho"', 'Combinados', 'Pecho y tríceps', 'Completos', 'Cuerpo completo', 'Cardio',
                'name="hice[]" value="nada" class="peer sr-only" checked', 'Nada, descansé',
                '¿Qué te gustaría entrenar hoy?', 'Divididos', 'name="hoy"', 'Combinados', 'Espalda y bíceps', 'Piernas y glúteos', 'Hombros y abdomen',
                'Completos', 'Otros', 'Estirar y movilidad', '<button type="submit"',
            ], false)
            ->assertSee('href="' . route('landing.rutinas') . '"', false)
            // Los pasos de a uno los pone el script; sin él, nada queda escondido.
            ->assertDontSee('<fieldset data-paso hidden', false);

        // Casillas solo para lo de estos días: nada de cronómetros. (Desde el
        // 8-oct-2026 el celular sí guarda el entrenamiento de hoy, a pedido
        // del dueño: ver test_el_entrenamiento_de_hoy_no_se_pierde.)
        foreach (['setInterval', 'type="checkbox" name="hoy"'] as $nada) {
            $respuesta->assertDontSee($nada, false);
        }

        // Lo que ya contestó queda marcado y lo de ayer (el enlace viejo), como «mejor no».
        $this->get('/rutina?dias=4&nivel=algo&ayer=piernas&cambiar=1')
            ->assertOk()
            ->assertSee('name="dias" value="4" class="peer sr-only" checked', false)
            ->assertSee('name="hice[]" value="piernas" class="peer sr-only" checked', false)
            // Con 4 días, después de pierna toca torso.
            ->assertSee('name="hoy" value="torso_espalda" class="peer sr-only" checked', false)
            ->assertSee('opacity-55"  data-descansa', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    /** Pide un grupo que su rutina tiene: sale ese día de la rutina. */
    public function test_el_resultado_es_el_dia_de_su_rutina_que_calza(): void
    {
        $e = EntrenamientoDeHoy::armar(5, 'algo', 'nada', 'espalda');
        $this->assertSame('División por grupos · 5 días', $e['rutina']->nombre);
        $this->assertSame(2, $e['dia']);

        $this->get('/rutina?dias=5&nivel=algo&ayer=nada&hoy=espalda')
            ->assertOk()
            ->assertSee('<title>Tu entrenamiento de hoy', false)
            ->assertSeeInOrder(['Tu entrenamiento de hoy', 'Espalda', 'Jalón al pecho', '4 × 8-10', '<span class="sr-only">Descanso </span>90 s', 'Otras opciones'], false)
            ->assertSee('href="' . route('landing.rutina.ver', 'division-por-grupos-5-dias') . '"', false)
            ->assertSee('Ver la semana')
            ->assertSee('href="' . e(route('landing.rutina', ['dias' => 5, 'nivel' => 'algo', 'hice' => ['nada'], 'hoy' => 'espalda', 'cambiar' => 1])) . '"', false)
            ->assertSee('Volver a empezar')
            ->assertSee('aria-label="Trabaja: espalda', false);
    }

    /** Sin elegir nada para hoy, se sugiere otra cosa que lo de ayer, y el día no lo repite. */
    public function test_no_repite_lo_de_ayer(): void
    {
        foreach ([[5, 'algo', 'piernas'], [4, 'hace_tiempo', 'pecho'], [3, 'nunca', 'espalda'], [6, 'nunca', 'hombros_brazos']] as [$dias, $nivel, $ayer]) {
            $hoy = EntrenamientoDeHoy::sugerencia($ayer, $dias);
            $this->assertNotSame($ayer, $hoy);

            $e = EntrenamientoDeHoy::armar($dias, $nivel, $ayer, $hoy);
            $deAyer = array_filter($e['lineas'], fn ($l) => EntrenamientoDeHoy::grupoDe($l['principal'], $l['zona']) === $ayer);
            $this->assertLessThanOrEqual(1, count($deAyer), "{$dias}/{$nivel}/{$ayer}: " . implode(', ', array_column($deAyer, 'nombre')));
        }

        // Ayer cuerpo completo: hoy, cardio.
        $this->assertSame('cardio', EntrenamientoDeHoy::sugerencia('cuerpo_completo', 3));
        $this->get('/rutina?dias=3&nivel=nunca&ayer=cuerpo_completo')
            ->assertOk()
            ->assertSee('<h1 class="mt-1 font-display text-4xl uppercase leading-none text-pg-tiza sm:text-5xl lg:text-6xl">Cardio', false);
    }

    /** Pide piernas y su rutina es de cuerpo completo: el día se arma con el catálogo. */
    public function test_arma_con_el_catalogo_cuando_ningun_dia_calza(): void
    {
        $e = EntrenamientoDeHoy::armar(2, 'nunca', 'nada', 'piernas');

        $this->assertSame('Primeros pasos · 2 días', $e['rutina']->nombre);
        $this->assertNull($e['dia']);
        $this->assertCount(6, $e['lineas']);
        $this->assertSame(['piernas'], array_values(array_unique(array_map(fn ($l) => EntrenamientoDeHoy::grupoDe($l['principal'], $l['zona']), $e['lineas']))));

        // Los que mueven más músculos primero, los accesorios después.
        $basico = array_map(fn ($l) => $l['secundarios'] !== [], $e['lineas']);
        $this->assertSame($basico, array_values(array_merge(array_filter($basico), array_filter($basico, fn ($b) => ! $b))));
        $this->assertTrue($basico[0]);

        // Series y repeticiones de quien empieza.
        $this->assertSame([3, '12 a 15'], [$e['lineas'][0]['series'], $e['lineas'][0]['repeticiones']]);

        $avanzado = EntrenamientoDeHoy::armar(3, 'hace_tiempo', 'nada', 'hombros_brazos');
        $this->assertNull($avanzado['dia']);
        $this->assertSame([5, '6 a 8'], [$avanzado['lineas'][0]['series'], $avanzado['lineas'][0]['repeticiones']]);

        $this->get('/rutina?dias=2&nivel=nunca&ayer=nada&hoy=piernas')
            ->assertOk()
            ->assertSee('6 ejercicios')
            ->assertSee('3 × 12-15')
            ->assertSee('href="' . route('landing.rutina.ver', 'primeros-pasos-2-dias') . '"', false);
    }

    /** Los QR que ya están impresos: la búsqueda de siempre, hacia su página. */
    public function test_los_qr_impresos_llevan_a_su_rutina(): void
    {
        $this->get('/rutina?objetivo=fuerza&nivel=algo&dias=4')
            ->assertRedirect(route('landing.rutina.ver', 'torso-y-pierna-4-dias'));

        $this->get('/rutina?objetivo=fuerza&nivel=algo&dias=5')
            ->assertRedirect(route('landing.rutina.ver', 'division-por-grupos-5-dias'));

        // Sin una exacta se afloja como antes (aquí, el nivel).
        $esperada = RutinaSugerida::buscar('fuerza', 'nunca', 6);
        $this->get('/rutina?objetivo=fuerza&nivel=nunca&dias=6')->assertRedirect(route('landing.rutina.ver', $esperada->slug));
    }

    public function test_la_pagina_de_una_rutina_muestra_cada_ejercicio_con_su_imagen(): void
    {
        $this->get('/rutinas/fuerza-cuerpo-completo-3-dias')
            ->assertOk()
            ->assertSee('<title>Rutina Fuerza cuerpo completo: 3 días | PRO GYM', false)
            ->assertSeeInOrder(['Día 1 · Día A', 'Sentadilla con barra', '4 series × 6 a 8 repeticiones', 'Descanso 120 s', 'Día 2 · Día B', 'Día 3 · Día C'])
            ->assertSee('Para avanzar:', false)
            ->assertSee('sube 2,5 kg o una repetición')
            ->assertSee('Si está ocupada:', false)
            // Sin foto, el mapa muscular.
            ->assertSee('aria-label="Trabaja: cuádriceps', false)
            ->assertDontSee('Mandármela por WhatsApp');

        $this->get('/rutinas/no-existe')->assertNotFound();
    }

    public function test_la_foto_del_panel_reemplaza_al_mapa(): void
    {
        Ejercicio::where('nombre', 'Sentadilla con barra')->update(['imagen' => 'ejercicios/sentadilla.webp']);

        $this->get('/rutinas/fuerza-cuerpo-completo-3-dias')
            ->assertOk()
            ->assertSee('src="' . asset('storage/ejercicios/sentadilla.webp') . '" alt="Sentadilla con barra"', false);
    }

    public function test_series_y_repeticiones_se_leen_bien(): void
    {
        $this->assertSame('3 series × 10 a 12 repeticiones', RutinaSugerida::dosis(3, '10 a 12'));
        $this->assertSame('3 series × 30 segundos', RutinaSugerida::dosis(3, '30 segundos'));
        $this->assertSame('15 minutos', RutinaSugerida::dosis(1, '15 minutos'));
        $this->assertSame('12 repeticiones', RutinaSugerida::dosis(1, '12'));
        $this->assertSame('3 × 10-12', RutinaSugerida::corta(3, '10 a 12'));
        $this->assertSame('3 × 10 por pierna', RutinaSugerida::corta(3, '10 por pierna'));
        $this->assertSame('15 minutos', RutinaSugerida::corta(1, '15 minutos'));
    }

    public function test_corregir_el_nombre_deja_la_direccion_vieja_redirigiendo(): void
    {
        $rutina = Rutina::where('nombre', 'Primeros pasos · 2 días')->sole();
        $rutina->update(['nombre' => 'Primeros pasos en la sala · 2 días']);

        $this->assertSame('primeros-pasos-en-la-sala-2-dias', $rutina->fresh()->slug);
        $this->get('/rutinas/primeros-pasos-2-dias')->assertRedirect(route('landing.rutina.ver', 'primeros-pasos-en-la-sala-2-dias'));
    }

    public function test_duplicar_no_repite_la_direccion(): void
    {
        $original = Rutina::where('nombre', 'Primeros pasos · 2 días')->sole();

        $this->actingAs($this->administrador())->post("/panel/rutinas/{$original->uuid}/duplicar")->assertSessionHasNoErrors();

        $copia = Rutina::where('nombre', 'Primeros pasos · 2 días (copia)')->sole();
        $this->assertNotSame($original->slug, $copia->slug);
    }

    public function test_los_ejercicios_del_gimnasio_por_grupo(): void
    {
        Ejercicio::where('nombre', 'Burpees')->update(['activo' => false]);

        $this->get('/ejercicios')
            ->assertOk()
            ->assertSee('<title>Ejercicios del gimnasio', false)
            ->assertSeeInOrder(['Pecho', 'Press de pecho en máquina', 'Espalda', 'Jalón al pecho', 'Piernas', 'Hombros', 'Brazos', 'Abdomen', 'Cardio'])
            ->assertSee('Espalda pegada al respaldo')
            ->assertSee('aria-label="Trabaja: pecho', false)
            ->assertDontSee('Burpees');
    }

    public function test_la_foto_y_los_musculos_se_guardan_desde_el_panel(): void
    {
        Storage::fake('public');
        $admin = $this->administrador();

        $this->actingAs($admin)->post('/panel/ejercicios', [
            'nombre' => 'Sentadilla en la máquina del rincón', 'zona' => 'piernas', 'equipo' => 'maquina', 'activo' => true,
            'musculo_principal' => 'cuadriceps', 'musculos_secundarios' => ['gluteos', 'cuadriceps'],
            'imagen' => UploadedFile::fake()->image('smith.jpg', 1600, 1200),
        ])->assertSessionHasNoErrors();

        $ejercicio = Ejercicio::where('nombre', 'Sentadilla en la máquina del rincón')->sole();
        $this->assertSame(['principal' => 'cuadriceps', 'secundarios' => ['gluteos']], $ejercicio->musculos);
        $this->assertNotNull($ejercicio->imagen);
        Storage::disk('public')->assertExists($ejercicio->imagen);
        [$ancho] = getimagesize(Storage::disk('public')->path($ejercicio->imagen));
        $this->assertLessThanOrEqual(900, $ancho);

        // Un GIF liviano va tal cual (así sigue moviéndose) y la foto anterior se borra.
        $anterior = $ejercicio->imagen;
        $this->actingAs($admin)->post("/panel/ejercicios/{$ejercicio->uuid}", [
            '_method' => 'put', 'nombre' => 'Sentadilla en la máquina del rincón', 'zona' => 'piernas', 'equipo' => 'maquina',
            'musculo_principal' => 'cuadriceps', 'imagen' => UploadedFile::fake()->create('smith.gif', 300, 'image/gif'),
        ])->assertSessionHasNoErrors();

        $ejercicio->refresh();
        $this->assertStringEndsWith('.gif', $ejercicio->imagen);
        Storage::disk('public')->assertMissing($anterior);

        // Editar sin archivo no la quita; «quitar» sí.
        $this->actingAs($admin)->put("/panel/ejercicios/{$ejercicio->uuid}", [
            'nombre' => 'Sentadilla en la máquina del rincón', 'zona' => 'piernas', 'equipo' => 'maquina', 'musculo_principal' => 'cuadriceps',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($ejercicio->fresh()->imagen);

        $this->actingAs($admin)->put("/panel/ejercicios/{$ejercicio->uuid}", [
            'nombre' => 'Sentadilla en la máquina del rincón', 'zona' => 'piernas', 'equipo' => 'maquina', 'quitar_imagen' => true,
        ])->assertSessionHasNoErrors();
        $this->assertNull($ejercicio->fresh()->imagen);
        $this->assertNull($ejercicio->fresh()->musculos);

        // Ni otro tipo de archivo ni un músculo que no existe.
        $this->actingAs($admin)->put("/panel/ejercicios/{$ejercicio->uuid}", [
            'nombre' => 'Sentadilla en la máquina del rincón', 'zona' => 'piernas', 'equipo' => 'maquina',
            'musculo_principal' => 'codo', 'imagen' => UploadedFile::fake()->create('video.mp4', 100, 'video/mp4'),
        ])->assertSessionHasErrors(['musculo_principal', 'imagen']);
    }

    /** Cada ejercicio trae otras opciones del mismo músculo, sin cardio y sin repetir las de arriba si hay de dónde. */
    public function test_cada_ejercicio_trae_otras_opciones_distintas(): void
    {
        $e = EntrenamientoDeHoy::armar(4, 'algo', 'piernas', 'torso_espalda');
        $primeras = array_column($e['lineas'][0]['opciones'], 'nombre');

        $this->assertGreaterThanOrEqual(2, count($primeras));
        foreach ($e['lineas'] as $l) {
            foreach ($l['opciones'] as $o) {
                $this->assertNotContains($o['nombre'], array_column($e['lineas'], 'nombre'), 'Ofrece algo que ya está en el día.');
                $this->assertStringNotContainsString('ergómetro', $o['nombre']);
            }
        }
        $this->assertNotSame($primeras, array_column($e['lineas'][1]['opciones'], 'nombre'), 'Dos ejercicios seguidos repiten las mismas opciones.');
    }

    /** Los músculos principales de un día, contados. */
    private function contar(array $lineas): array
    {
        return array_count_values(array_map(fn ($l) => $l['principal'] ?? '-', $lineas));
    }

    /** Las combinaciones clásicas: el grupo grande y el chico, en sus cantidades. */
    public function test_las_combinaciones_clasicas_arman_lo_correcto(): void
    {
        foreach (['nunca', 'algo', 'hace_tiempo'] as $nivel) {
            $pt = $this->contar(EntrenamientoDeHoy::desdeElCatalogo('pecho_triceps', $nivel));
            $this->assertSame(['pecho' => 3, 'triceps' => 2], $pt, "Pecho y tríceps ({$nivel})");

            $eb = $this->contar(EntrenamientoDeHoy::desdeElCatalogo('espalda_biceps', $nivel));
            $this->assertSame(['espalda' => 3, 'biceps' => 2], $eb, "Espalda y bíceps ({$nivel})");

            $pg = $this->contar(EntrenamientoDeHoy::desdeElCatalogo('piernas_gluteos', $nivel));
            $this->assertGreaterThanOrEqual(1, $pg['cuadriceps'] ?? 0);
            $this->assertGreaterThanOrEqual(1, $pg['isquios'] ?? 0);
            $this->assertSame(2, $pg['gluteos'] ?? 0, "Piernas y glúteos ({$nivel})");

            $ha = EntrenamientoDeHoy::desdeElCatalogo('hombros_abdomen', $nivel);
            $this->assertSame(3, $this->contar($ha)['hombros'] ?? 0);
            $abdomen = count(array_filter($ha, fn ($l) => $l['zona'] === 'core'));
            $this->assertTrue($abdomen >= 2 && $abdomen <= 3, "Hombros y abdomen ({$nivel}): {$abdomen} de abdomen");
        }

        // Pecho y tríceps: primero el pecho, después el tríceps.
        $e = EntrenamientoDeHoy::armar(2, 'algo', 'nada', 'pecho_triceps');
        $this->assertSame('Pecho y tríceps', $e['titulo']);
        $this->assertSame(['pecho', 'pecho', 'pecho', 'triceps', 'triceps'], array_column($e['lineas'], 'principal'));

        $this->get('/rutina?dias=2&nivel=algo&hice[]=nada&hoy=espalda_biceps')
            ->assertOk()
            ->assertSee('Espalda y bíceps')
            ->assertSee('5 ejercicios');
    }

    /** Lo de estos días se marca varias veces: la sugerencia y el día esquivan todo. */
    public function test_la_seleccion_multiple_evita_todo_lo_marcado(): void
    {
        foreach ([
            [5, ['pecho', 'espalda']],
            [4, ['torso_pecho', 'piernas']],
            [6, ['pecho_triceps', 'espalda_biceps']],
            [3, ['pecho', 'piernas_gluteos']],
            [5, ['hombros_brazos', 'piernas', 'pecho']],
        ] as [$dias, $hechos]) {
            $hoy = EntrenamientoDeHoy::sugerencia($hechos, $dias);
            $this->assertFalse(EntrenamientoDeHoy::choca($hoy, $hechos), "{$dias} días, " . implode('+', $hechos) . " sugiere {$hoy}");

            $cansados = array_merge(...array_map(fn ($h) => EntrenamientoDeHoy::CARGA[$h], $hechos));
            $e = EntrenamientoDeHoy::armar($dias, 'algo', $hechos, $hoy);
            $pisan = array_filter($e['lineas'], fn ($l) => in_array($l['principal'], $cansados, true) && ! in_array($l['zona'], ['core', 'cardio'], true));
            $this->assertLessThanOrEqual(1, count($pisan), "{$hoy}: " . implode(', ', array_column($pisan, 'nombre')));
        }

        // Con 5 días, pecho y espalda estos días: hoy piernas.
        $this->get('/rutina?dias=5&nivel=algo&hice[]=pecho&hice[]=espalda')
            ->assertOk()
            ->assertSee('<h1 class="mt-1 font-display text-4xl uppercase leading-none text-pg-tiza sm:text-5xl lg:text-6xl">Piernas', false);

        // Todo lo marcado (y lo que choca) sale como «mejor no»; «nada» junto a otra cosa no cuenta.
        $respuesta = $this->get('/rutina?dias=5&nivel=algo&hice[]=pecho&hice[]=piernas&hice[]=nada&cambiar=1')
            ->assertOk()
            ->assertSee('name="hice[]" value="pecho" class="peer sr-only" checked', false)
            ->assertSee('name="hice[]" value="piernas" class="peer sr-only" checked', false)
            ->assertDontSee('name="hice[]" value="nada" class="peer sr-only" checked', false);

        $contenido = $respuesta->getContent();
        foreach (['pecho', 'piernas', 'pecho_triceps', 'piernas_gluteos', 'cuerpo_completo'] as $clave) {
            $this->assertMatchesRegularExpression('/name="hoy" value="' . $clave . '"[^>]*>\s*<span[^>]*" +data-descansa *>/', $contenido, "{$clave} debería decir «mejor no».");
        }
        foreach (['espalda', 'espalda_biceps', 'cardio', 'movilidad'] as $clave) {
            $this->assertDoesNotMatchRegularExpression('/name="hoy" value="' . $clave . '"[^>]*>\s*<span[^>]*" +data-descansa *>/', $contenido, "{$clave} no choca.");
        }
    }

    /** Los enlaces y QR con ?ayer= de antes siguen armando lo mismo. */
    public function test_el_ayer_de_antes_sigue_funcionando(): void
    {
        $viejo = $this->get('/rutina?dias=5&nivel=algo&ayer=piernas')->assertOk()->assertSee('Hombros y brazos');
        $nuevo = $this->get('/rutina?dias=5&nivel=algo&hice[]=piernas')->assertOk();
        $this->assertSame(EntrenamientoDeHoy::hechos(null, 'piernas'), EntrenamientoDeHoy::hechos(['piernas']));
        $this->assertSame([], EntrenamientoDeHoy::hechos(null, 'nada'));
        $this->assertNull(EntrenamientoDeHoy::hechos(null, 'cualquiera'));
        $this->assertSame(substr_count($viejo->getContent(), '<h3'), substr_count($nuevo->getContent(), '<h3'));

        // Lo que no se contestó vuelve a las preguntas.
        $this->get('/rutina?dias=5&nivel=algo&hice[]=inventado')->assertOk()->assertSee('¿Qué entrenaste estos últimos días?');
    }

    /** Antes, calentamiento con banda según lo de hoy; al final, estiramientos de lo trabajado. */
    public function test_calentamiento_con_banda_y_estiramientos_de_lo_de_hoy(): void
    {
        $pecho = EntrenamientoDeHoy::armar(2, 'algo', 'nada', 'pecho_triceps');
        $this->assertSame('Cardio suave', $pecho['calentamiento'][0]['nombre']);
        $conBanda = array_slice($pecho['calentamiento'], 1);
        $this->assertCount(4, $conBanda);
        foreach ($conBanda as $c) {
            $this->assertTrue($c['banda']);
            $this->assertSame('arriba', CalentamientoYEstiramiento::CALENTAMIENTO[$c['nombre']][2], "{$c['nombre']} no es de tren superior.");
        }

        $estira = array_merge(...array_column($pecho['estiramientos'], 'musculos'));
        $this->assertContains('pecho', $estira);
        $this->assertContains('triceps', $estira);
        $this->assertTrue(count($pecho['estiramientos']) >= 3 && count($pecho['estiramientos']) <= 5);

        $piernas = EntrenamientoDeHoy::armar(2, 'algo', 'nada', 'piernas_gluteos');
        foreach (array_slice($piernas['calentamiento'], 1) as $c) {
            $this->assertSame('abajo', CalentamientoYEstiramiento::CALENTAMIENTO[$c['nombre']][2]);
        }
        $estira = array_merge(...array_column($piernas['estiramientos'], 'musculos'));
        foreach (['cuadriceps', 'isquios', 'gluteos'] as $m) {
            $this->assertContains($m, $estira);
        }

        $this->get('/rutina?dias=2&nivel=algo&hice[]=nada&hoy=piernas_gluteos')
            ->assertOk()
            ->assertSeeInOrder(['Calentamiento', 'Cardio suave', '5 minutos', $piernas['calentamiento'][1]['nombre'], '2 × ',
                'Entrenamiento', $piernas['lineas'][0]['nombre'],
                'Para terminar, estira', $piernas['estiramientos'][0]['nombre'], '30 segundos'])
            ->assertSee(', con banda</span>', false)
            ->assertSee('aria-label="Trabaja: glúteos', false);
    }

    /** «Estirar y movilidad»: un día liviano entero, sin pesas, de los pies a la cabeza. */
    public function test_estirar_y_movilidad_arma_su_rutina(): void
    {
        $e = EntrenamientoDeHoy::armar(3, 'nunca', ['piernas'], 'movilidad');

        $this->assertSame([], $e['lineas']);
        $this->assertCount(5, $e['movilidad']);
        $this->assertNotEmpty(array_filter($e['movilidad'], fn ($m) => $m['banda']));
        $this->assertGreaterThanOrEqual(8, count($e['estiramientos']));
        $this->assertTrue($e['minutos'] >= 15 && $e['minutos'] <= 20, "Dura {$e['minutos']} minutos.");
        // Empieza por los pies y termina en los brazos.
        $this->assertSame(['pantorrillas'], $e['estiramientos'][0]['musculos']);
        $this->assertContains(end($e['estiramientos'])['musculos'][0], ['triceps', 'biceps']);

        // Nunca choca con lo hecho: estirar no cansa.
        $this->assertFalse(EntrenamientoDeHoy::choca('movilidad', ['cuerpo_completo', 'cardio']));
        $this->assertSame('movilidad', EntrenamientoDeHoy::sugerencia(['cuerpo_completo', 'cardio'], 3));

        $this->get('/rutina?dias=3&nivel=nunca&hice[]=cuerpo_completo&hice[]=cardio')
            ->assertOk()
            ->assertSee('Estirar y movilidad')
            ->assertSee('Unos ' . $e['minutos'] . ' minutos')
            ->assertSeeInOrder(['Calentamiento', 'Movilidad', 'Estira, de pies a cabeza', 'Pantorrilla en la pared'])
            ->assertDontSee('Entrenamiento</h2>', false)
            ->assertDontSee('Descanso ');
    }

    /** Una fila por ejercicio, grande y legible, con el detalle en <details> cerrados (sin JavaScript). */
    public function test_el_resultado_y_las_preguntas_son_compactos(): void
    {
        $e = EntrenamientoDeHoy::armar(5, 'algo', 'nada', 'pecho');
        $html = $this->get('/rutina?dias=5&nivel=algo&hice[]=nada&hoy=pecho')
            ->assertOk()
            ->assertSee('class="h-24 w-28 shrink-0 sm:h-32 sm:w-36 lg:h-40 lg:w-44"', false)
            ->assertSeeInOrder(['Calentamiento', '5 min cardio + 4 con banda', 'Entrenamiento', 'Para terminar, estira', count($e['estiramientos']) . ' de 30 s'], false)
            ->assertSee('Cómo y otras opciones')
            ->getContent();

        // Una fila por ejercicio, cerradas; el calentamiento y los estiramientos también.
        $this->assertSame(count($e['lineas']), substr_count($html, '<details class="group rounded-xl'));
        $this->assertStringNotContainsString(' open>', $html);
        $this->assertSame(2, substr_count($html, '<details class="group mt-5 lg:'));

        // Las opciones de grupos, de a cuatro (una fila por sección) y bajas.
        $this->get('/rutina')->assertOk()->assertSee('mt-4 grid grid-cols-4 gap-1.5 sm:gap-3', false)->assertDontSee('h-16 w-20', false);
    }

    /** Sin JavaScript: el formulario manda hice[] tal cual y «nada» viene marcado. */
    public function test_la_seleccion_multiple_funciona_sin_javascript(): void
    {
        $this->get('/rutina?dias=3&nivel=nunca')
            ->assertOk()
            ->assertSee('name="hice[]" value="nada" class="peer sr-only" checked', false)
            ->assertSee('data-seguir hidden', false);

        // Lo que manda el navegador al enviar el formulario.
        $this->get('/rutina?dias=3&nivel=nunca&hice%5B%5D=nada&hoy=cardio')
            ->assertOk()
            ->assertSee('<title>Tu entrenamiento de hoy', false);

        $this->get('/rutina?dias=3&nivel=nunca&hice%5B%5D=pecho&hice%5B%5D=espalda&hoy=cardio')
            ->assertOk()
            ->assertSee('href="' . e(route('landing.rutina', ['dias' => 3, 'nivel' => 'nunca', 'hice' => ['pecho', 'espalda'], 'hoy' => 'cardio', 'cambiar' => 1])) . '"', false);
    }

    /**
     * Si se le cierra el navegador, no pierde el entrenamiento: el celular
     * guarda la dirección y lo que marcó como hecho, y al volver a las
     * preguntas se ofrece seguir. También se lo puede mandar por WhatsApp.
     * Nada de esto pasa por el servidor.
     */
    public function test_el_entrenamiento_de_hoy_no_se_pierde(): void
    {
        $html = $this->get('/rutina?dias=4&nivel=algo&hice[]=nada&hoy=pecho')->assertOk()->getContent();

        // Un botón de hecho por ejercicio, escondido hasta que hay JavaScript.
        $this->assertGreaterThan(2, substr_count($html, 'data-marcar hidden'));
        $this->assertStringContainsString("localStorage.setItem(clave", $html);

        // El mensaje lleva la lista y el enlace para volver.
        preg_match('#href="(https://wa\.me/\?text=[^"]+)"#', $html, $m);
        $texto = rawurldecode(html_entity_decode($m[1] ?? ''));
        $this->assertStringContainsString('Mi entrenamiento de hoy en PRO GYM: Pecho', $texto);
        $this->assertStringContainsString("\n1. ", $texto);
        $this->assertStringContainsString('/rutina?dias=4', $texto);

        // «Volver a empezar» no ofrece seguir con el de hoy.
        $this->assertStringContainsString('href="' . route('landing.rutina', ['nuevo' => 1]) . '"', $html);

        // Las preguntas traen el aviso para seguir, escondido hasta que el
        // script encuentra uno guardado de hoy.
        $this->get('/rutina')->assertOk()->assertSee('<a data-guardada hidden', false);
    }

    /**
     * Variedad (8-oct-2026): un día de rutina salía igual toda la semana y
     * para todos los que contestaban lo mismo. Los dos básicos se quedan; lo
     * demás cambia con el día y con la variante de cada celular.
     */
    public function test_los_basicos_se_quedan_y_lo_demas_cambia(): void
    {
        $rutina = RutinaSugerida::buscar('fuerza', 'algo', 4);
        $piernaDeLaRutina = collect(RutinaSugerida::dias($rutina))->firstWhere('titulo', 'Pierna pesada');
        $basicos = array_slice(array_column($piernaDeLaRutina['lineas'], 'nombre'), 0, 2);

        $vistos = [];
        foreach (range(0, 6) as $d) {
            \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-10-12')->addDays($d));
            foreach (range(1, EntrenamientoDeHoy::VARIANTES) as $v) {
                $e = EntrenamientoDeHoy::armar(4, 'algo', ['nada'], 'piernas', $v);
                $nombres = array_column($e['lineas'], 'nombre');

                $this->assertNotNull($e['dia'], 'Sale de la rutina');
                $this->assertSame($basicos, array_slice($nombres, 0, 2), 'Los básicos no cambian');
                $this->assertSame(count($nombres), count(array_unique($nombres)), 'Sin repetidos');
                // Las mismas series y repeticiones de la rutina en cada puesto.
                $this->assertSame(array_column($piernaDeLaRutina['lineas'], 'series'), array_column($e['lineas'], 'series'));
                $vistos[implode('|', $nombres)] = true;
            }
        }
        \Illuminate\Support\Carbon::setTestNow();

        $this->assertGreaterThan(4, count($vistos), 'En una semana, con cuatro celulares, salen días distintos');

        // El mismo celular, el mismo día: siempre lo mismo (al volver atrás o recargar).
        $this->assertSame(
            array_column(EntrenamientoDeHoy::armar(4, 'algo', ['nada'], 'piernas', 2)['lineas'], 'nombre'),
            array_column(EntrenamientoDeHoy::armar(4, 'algo', ['nada'], 'piernas', 2)['lineas'], 'nombre'),
        );

        // La variante viaja en la dirección y se conserva al cambiar lo de hoy.
        $this->get('/rutina?dias=4&nivel=algo&hice[]=nada&hoy=piernas&v=3')->assertOk()
            ->assertSee('v=3&amp;cambiar=1', false);
        $this->get('/rutina')->assertSee('name="v" value="" data-variante disabled', false);
    }

    /** Salía peso muerto en «Torso, más espalda» el día después de piernas. */
    public function test_el_torso_no_carga_las_piernas(): void
    {
        foreach (['nunca', 'algo', 'hace_tiempo'] as $nivel) {
            foreach (range(1, EntrenamientoDeHoy::VARIANTES) as $v) {
                foreach (['torso_espalda', 'torso_pecho'] as $hoy) {
                    $e = EntrenamientoDeHoy::armar(4, $nivel, ['piernas'], $hoy, $v);
                    $nombres = implode(', ', array_column($e['lineas'], 'nombre'));

                    $this->assertStringNotContainsString('Peso muerto', $nombres, "$nivel/$hoy/$v");
                    $this->assertStringNotContainsString('Hiperextensiones', $nombres, "$nivel/$hoy/$v");
                }
            }
        }

        // La espalda se trabaja tirando de arriba y de adelante: un jalón (o
        // dominadas) y dos remos entre los tres primeros, con cualquier variante.
        // En pecho, un press plano y uno inclinado.
        foreach (range(1, EntrenamientoDeHoy::VARIANTES) as $v) {
            foreach (['espalda', 'torso_espalda'] as $hoy) {
                $tres = array_slice(array_column(EntrenamientoDeHoy::armar(4, 'algo', ['nada'], $hoy, $v)['lineas'], 'nombre'), 0, 3);
                $this->assertCount(1, preg_grep('/^Jalón|^Dominadas/u', $tres), "$hoy/$v: " . implode(', ', $tres));
                $this->assertCount(2, preg_grep('/^Remo/u', $tres), "$hoy/$v: " . implode(', ', $tres));
            }

            $pecho = array_column(EntrenamientoDeHoy::armar(4, 'algo', ['nada'], 'pecho', $v)['lineas'], 'nombre');
            $this->assertNotEmpty(preg_grep('/inclinad/u', $pecho));
            $this->assertNotEmpty(preg_grep('/^Press (de banca|de pecho)/u', $pecho));
        }
    }

    /** En el celular los botones flotantes tapaban «Seguir» y el ✓ de cada ejercicio. */
    public function test_sin_botones_flotantes_en_el_telefono(): void
    {
        $this->get('/rutina')->assertSee('<div  class="max-lg:hidden"', false);
        $this->get('/rutina?dias=4&nivel=algo&hice[]=nada&hoy=pecho')->assertSee('<div  class="max-lg:hidden"', false);
        $this->get('/')->assertDontSee('class="max-lg:hidden"', false);
    }
}
