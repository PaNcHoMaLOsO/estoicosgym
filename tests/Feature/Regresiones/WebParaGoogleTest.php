<?php

namespace Tests\Feature\Regresiones;

use App\Models\ContenidoWeb;
use App\Support\Ajustes;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Lo que la web le dice a Google y a las redes, sacado de Configuración.
 *
 * Lo que se vigila: que las comunas, la ubicación y las redes lleguen a la
 * ficha que lee Google; que al compartir la página salga una foto del
 * gimnasio; que el botón de reseñas aparezca donde se piden; y que la web ya
 * no dependa del Tailwind de pruebas que se armaba en cada visita.
 */
class WebParaGoogleTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Ajustes::olvidar();
    }

    private function guardar(array $valores): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/configuracion', $valores)
            ->assertSessionHasNoErrors();

        Ajustes::olvidar();
    }

    /** Las fichas para Google de una página, en el orden en que salen. */
    private function fichas(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_las_comunas_y_la_ubicacion_llegan_a_la_ficha(): void
    {
        $this->guardar([
            'web.comunas' => 'Nacimiento, Mulchén ,',
            'web.coordenadas' => '-37.46973, -72.35366',
        ]);

        $ficha = $this->fichas('/')[0];

        $this->assertSame(['Los Ángeles', 'Nacimiento', 'Mulchén'], array_column($ficha['areaServed'], 'name'));
        $this->assertSame(-37.46973, $ficha['geo']['latitude']);
        $this->assertSame(-72.35366, $ficha['geo']['longitude']);
    }

    public function test_una_ubicacion_mal_pegada_se_rechaza(): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/configuracion', ['web.coordenadas' => 'Los Ángeles centro'])
            ->assertSessionHasErrors('web.coordenadas');
    }

    /** Al compartir la página por WhatsApp o Facebook sale una foto del gimnasio, no el logo. */
    public function test_al_compartir_sale_la_primera_foto(): void
    {
        $this->get('/')->assertSee('<meta property="og:image" content="' . asset('images/progym-logo.png') . '">', false);

        ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'Sala de máquinas', 'imagen' => 'web/sala.jpg', 'activo' => true]);
        $foto = url(Storage::disk('public')->url('web/sala.jpg'));

        $this->get('/planes')->assertSee('<meta property="og:image" content="' . $foto . '">', false);
        $this->assertContains($foto, $this->fichas('/')[0]['image']);
    }

    public function test_el_boton_de_resenas_aparece_en_contacto_y_en_el_pie(): void
    {
        $this->get('/contacto')->assertOk()->assertDontSee('Déjanos tu reseña');

        $this->guardar(['web.resenas' => 'https://g.page/r/progym/review']);

        $this->get('/contacto')
            ->assertSee('https://g.page/r/progym/review', false)
            ->assertSee('Déjanos tu reseña');

        // En el pie, que está en todas las páginas.
        $this->get('/planes')->assertSee('https://g.page/r/progym/review', false);
    }

    public function test_tiktok_y_youtube_van_en_el_pie_y_en_la_ficha(): void
    {
        $this->guardar([
            'web.tiktok' => 'https://www.tiktok.com/@progym',
            'web.youtube' => 'https://www.youtube.com/@progym',
        ]);

        $this->get('/')
            ->assertSee('https://www.tiktok.com/@progym', false)
            ->assertSee('https://www.youtube.com/@progym', false);

        $this->assertContains('https://www.tiktok.com/@progym', $this->fichas('/')[0]['sameAs']);
    }

    public function test_la_verificacion_de_bing(): void
    {
        $this->guardar(['web.bing' => 'ABCDEF0123456789ABCDEF0123456789']);

        $this->get('/')->assertSee('<meta name="msvalidate.01" content="ABCDEF0123456789ABCDEF0123456789">', false);
    }

    /** La descripción escrita en Configuración reemplaza a la que se arma sola. */
    public function test_la_descripcion_escrita_reemplaza_la_automatica(): void
    {
        $this->get('/')->assertSee('musculación, cardio y orientación en sala', false);

        $this->guardar(['web.descripcion' => 'El gimnasio de Los Ángeles con horario extendido.']);

        $this->get('/')->assertSee('<meta name="description" content="El gimnasio de Los Ángeles con horario extendido.">', false);
    }

    /** Cada página, salvo la portada, lleva sus migas: Google las muestra en vez de la dirección. */
    public function test_las_paginas_llevan_migas(): void
    {
        $this->assertCount(1, $this->fichas('/'));

        $migas = collect($this->fichas('/planes'))->firstWhere('@type', 'BreadcrumbList');

        $this->assertNotNull($migas);
        $this->assertSame('Planes y precios', $migas['itemListElement'][1]['name']);
        $this->assertSame(route('landing.planes'), $migas['itemListElement'][1]['item']);
    }

    /** La web ya no arma sus estilos en el navegador de cada visita: van compilados. */
    public function test_la_web_no_carga_el_tailwind_de_pruebas(): void
    {
        $respuesta = $this->get('/')->assertOk();

        $this->assertStringNotContainsString('cdn.tailwindcss.com', $respuesta->getContent());
        $this->assertMatchesRegularExpression('#/build/assets/landing-[^"]+\.css#', $respuesta->getContent());
        $this->assertStringNotContainsString('cdn.tailwindcss.com', (string) $respuesta->headers->get('Content-Security-Policy'));
    }
}
