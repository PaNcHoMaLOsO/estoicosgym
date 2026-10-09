<?php

namespace Tests\Feature\Regresiones;

use App\Http\Controllers\Panel\PlantillaController;
use App\Models\Clase;
use App\Models\Especialista;
use App\Models\TipoNotificacion;
use App\Support\Ajustes;
use App\Support\Especialidades;
use Tests\CasoConCatalogos;

/**
 * Una página por tema, para lo que la gente busca: «clases de judo en Los
 * Ángeles», «kinesiólogo en Los Ángeles».
 *
 * Lo que se vigila: que cada clase y cada especialidad tengan su página con
 * su título, su ficha y sus migas; que las direcciones viejas de una clase
 * redirijan; que lo que no existe dé 404; que el mapa del sitio las lleve; y
 * que la reseña de Google se pida donde toca, también en los correos.
 */
class SeoPaginasPorTemaTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
    }

    private function clase(array $datos = []): Clase
    {
        return Clase::create($datos + [
            'nombre' => 'Judo',
            'para_quien' => 'Niños desde 8 años',
            'profesor' => 'Pedro Soto',
            'precio_mensual' => 25000,
            'color' => 'azul',
            'horario' => [
                ['dia' => 'miercoles', 'desde' => '19:00', 'hasta' => '20:30'],
                ['dia' => 'lunes', 'desde' => '19:00', 'hasta' => '20:30'],
            ],
            'activo' => true,
            'orden' => 1,
        ]);
    }

    private function fichas(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    private function h1(string $html): string
    {
        $this->assertSame(1, substr_count($html, '<h1'));
        preg_match('#<h1[^>]*>(.*?)</h1>#s', $html, $m);

        return trim(strip_tags($m[1]));
    }

    // ---------- Una página por clase ----------

    public function test_cada_clase_tiene_su_pagina(): void
    {
        $judo = $this->clase();
        $this->clase(['nombre' => 'Lucha olímpica', 'orden' => 2, 'precio_mensual' => null]);

        $this->assertSame('judo', $judo->slug);

        $html = $this->get('/clases/judo')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Clases de Judo en Los Ángeles | PRO GYM</title>', $html);
        $this->assertSame('Clases de Judo en Los Ángeles', $this->h1($html));
        $this->assertStringContainsString('content="Clases de Judo en PRO GYM, Los Ángeles: Lun y Mié · 19:00 a 20:30. Niños desde 8 años. $25.000 al mes."', $html);
        $this->assertStringContainsString('Con Pedro Soto', $html);
        $this->assertStringContainsString('Inscribirme', $html);
        // Abajo, las otras clases.
        $this->assertStringContainsString('href="' . route('landing.clase', 'lucha-olimpica') . '"', $html);

        $fichas = collect($this->fichas('/clases/judo'));
        $servicio = $fichas->firstWhere('@type', 'Service');
        $this->assertSame(25000, $servicio['offers']['price']);
        $this->assertSame(url('/') . '#gimnasio', $servicio['provider']['@id']);

        $migas = $fichas->firstWhere('@type', 'BreadcrumbList');
        $this->assertSame(['PRO GYM', 'Clases', 'Judo'], array_column($migas['itemListElement'], 'name'));
    }

    public function test_la_lista_y_el_calendario_llevan_a_cada_clase(): void
    {
        $this->clase();

        $html = $this->get('/clases')->assertOk()->getContent();

        $this->assertStringContainsString('href="' . route('landing.clase', 'judo') . '"', $html);
        $this->assertStringNotContainsString('href="#clase-', $html);
    }

    public function test_al_cambiar_el_nombre_la_direccion_vieja_redirige(): void
    {
        $clase = $this->clase();
        $clase->update(['nombre' => 'Judo infantil']);

        $this->assertSame('judo-infantil', $clase->fresh()->slug);
        $this->get('/clases/judo')->assertStatus(301)->assertRedirect(route('landing.clase', 'judo-infantil'));
    }

    public function test_una_clase_que_no_esta_da_404(): void
    {
        $this->get('/clases/judo')->assertNotFound();

        $this->clase(['activo' => false]);

        $this->get('/clases/judo')->assertNotFound();
    }

    // ---------- Una página por especialidad ----------

    public function test_las_especialidades_se_juntan_sin_tildes_ni_femenino(): void
    {
        $this->assertSame(
            ['nutricionista' => 'Nutricionista', 'preparador-fisico' => 'Preparador Fisico'],
            Especialidades::de('Nutricionista - Preparador Fisico')
        );
        $this->assertSame('kinesiologo', Especialidades::clave('Kinesióloga'));
        $this->assertSame('preparador-fisico', Especialidades::clave('Preparadora Física'));
    }

    public function test_cada_especialidad_tiene_su_pagina(): void
    {
        $camila = Especialista::create(['nombre' => 'Camila Rojas', 'especialidad' => 'Kinesióloga', 'activo' => true]);
        Especialista::create(['nombre' => 'Diego Soto', 'especialidad' => 'Preparador Físico - Kinesiólogo', 'activo' => true]);

        $html = $this->get('/especialidades/kinesiologo')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Kinesiólogo en Los Ángeles | PRO GYM</title>', $html);
        $this->assertSame('Kinesiólogo en Los Ángeles', $this->h1($html));
        $this->assertStringContainsString('Camila Rojas', $html);
        $this->assertStringContainsString('Diego Soto', $html);
        $this->assertStringContainsString('href="' . route('landing.especialista', $camila->slug) . '"', $html);

        // Diego cuenta también como preparador físico; Camila no.
        $this->get('/especialidades/preparador-fisico')->assertOk()->assertSee('Diego Soto')->assertDontSee('Camila Rojas');

        // Enlaces desde la lista y desde el perfil.
        $this->get('/especialistas')->assertSee('href="' . route('landing.especialidad', 'kinesiologo') . '"', false);
        $this->get(route('landing.especialista', $camila->slug))->assertSee('Más Kinesiólogo en PRO GYM');
    }

    /**
     * Lo que había en la web el 8-oct-2026: el título de /especialistas salía
     * con 120 caracteres, «Judoka y preparador físico» era una sola página y
     * quien era embajador y especialista quedaba en «leonardo-gutierrez-2».
     */
    public function test_especialidades_con_detalle_o_con_y_y_el_titulo_que_cabe(): void
    {
        $this->assertSame(['entrenador-personal' => 'Entrenador personal'], Especialidades::de('Entrenador personal: estética y funcionalidad'));
        $this->assertSame(['judoka' => 'Judoka', 'preparador-fisico' => 'preparador físico'], Especialidades::de('Judoka y preparador físico'));

        Especialista::create(['nombre' => 'Benjamín Bascur', 'especialidad' => 'Entrenador personal: estética y funcionalidad', 'activo' => true]);
        Especialista::create(['tipo' => 'embajador', 'nombre' => 'Leonardo Gutiérrez', 'especialidad' => 'Judoka', 'activo' => true]);
        $leo = Especialista::create(['nombre' => 'Leonardo Gutiérrez', 'especialidad' => 'Judoka y preparador físico', 'activo' => true]);
        Especialista::create(['nombre' => 'Isidora Aravena', 'especialidad' => 'Preparadora física y creadora de contenido', 'activo' => true]);

        $this->assertSame('leonardo-gutierrez', $leo->slug);
        $this->assertNull(Especialista::where('tipo', 'embajador')->value('slug'));

        // Lo que más gente hace va primero, y solo lo que cabe.
        $this->get('/especialistas')->assertOk()
            ->assertSee('<title>Preparador físico y personal trainer en Los Ángeles | PRO GYM</title>', false);

        // Como lo busca la gente en Chile: «personal trainer», con la otra forma al lado.
        $this->get('/especialidades/entrenador-personal')->assertOk()
            ->assertSee('<title>Personal trainer y entrenador personal en Los Ángeles | PRO GYM</title>', false);

        $this->get('/especialidades/preparador-fisico')->assertOk()->assertSee('Leonardo Gutiérrez')->assertSee('Isidora Aravena');

        // Las direcciones de antes llevan a la nueva.
        $this->get('/especialidades/judoka-y-preparador-fisico')->assertRedirect(route('landing.especialidad', 'judoka'))->assertStatus(301);
        $this->get('/especialidades/entrenador-personal-estetico-y-funcionalidad')->assertRedirect(route('landing.especialidad', 'entrenador-personal'));
        $this->get('/especialidades/judo')->assertNotFound();
    }

    public function test_una_especialidad_sin_nadie_da_404(): void
    {
        $this->get('/especialidades/kinesiologo')->assertNotFound();

        Especialista::create(['nombre' => 'Camila Rojas', 'especialidad' => 'Kinesióloga', 'activo' => false]);

        $this->get('/especialidades/kinesiologo')->assertNotFound();
    }

    public function test_el_mapa_del_sitio_lleva_clases_y_especialidades(): void
    {
        $this->clase();
        Especialista::create(['nombre' => 'Camila Rojas', 'especialidad' => 'Kinesióloga', 'activo' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>' . route('landing.clase', 'judo') . '</loc>', false)
            ->assertSee('<loc>' . route('landing.especialidad', 'kinesiologo') . '</loc>', false);
    }

    // ---------- Las rutinas se encuentran ----------

    public function test_la_pagina_de_rutinas_tiene_enlaces_y_va_en_el_mapa(): void
    {
        $rutina = 'href="' . route('landing.rutina') . '"';

        $this->get('/planes')->assertOk()->assertSee($rutina, false);
        $this->get('/')->assertOk()->assertSee('Rutinas para entrenar')->assertSee($rutina, false);
        $this->get('/el-gimnasio')->assertOk()->assertSee('Mira las rutinas');
        $this->get('/sitemap.xml')->assertSee('<loc>' . route('landing.rutina') . '</loc>', false);
    }

    // ---------- La reseña de Google ----------

    public function test_la_resena_se_pide_tras_la_consulta_de_membresia(): void
    {
        $this->get('/mi-membresia')->assertOk()->assertDontSee('¿Te gusta entrenar aquí?');

        Ajustes::guardar(['web.resenas' => 'https://g.page/r/progym/review']);
        Ajustes::olvidar();

        $this->get('/mi-membresia')
            ->assertSee('¿Te gusta entrenar aquí?')
            ->assertSee('https://g.page/r/progym/review', false);
    }

    public function test_los_correos_saben_poner_el_enlace_de_la_resena(): void
    {
        $this->assertArrayHasKey('enlace_resena', PlantillaController::VARIABLES);

        Ajustes::guardar(['web.resenas' => 'https://g.page/r/progym/review']);
        Ajustes::olvidar();

        $tipo = new TipoNotificacion(['asunto_email' => 'Hola {nombre}', 'plantilla_email' => '<a href="{enlace_resena}">Reseña</a>']);

        $this->assertSame('<a href="https://g.page/r/progym/review">Reseña</a>', $tipo->renderizar(['nombre' => 'Ana'])['contenido']);
    }
}
