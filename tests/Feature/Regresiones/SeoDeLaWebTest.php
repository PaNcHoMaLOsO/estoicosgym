<?php

namespace Tests\Feature\Regresiones;

use App\Models\Clase;
use App\Models\ContenidoWeb;
use App\Models\Especialista;
use App\Support\Ajustes;
use App\Support\Iconos;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Lo que la web pública le dice a Google, de la auditoría de SEO.
 *
 * Lo que se vigila: una sola dirección por página, un h1 que dice qué es y
 * dónde, el nombre, la dirección y el teléfono en todas partes, cuánto se
 * guarda en caché, la página de «no existe», qué no se indexa, cómo sale al
 * compartir, las fichas para Google, el mapa del sitio con fechas de verdad,
 * las fotos con sus medidas y los íconos sin Font Awesome por CDN.
 */
class SeoDeLaWebTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Ajustes::olvidar();
    }

    protected function tearDown(): void
    {
        // La prueba «en producción» deja fijados los nombres de equipo que
        // acepta Symfony, y eso es de la clase, no de la petición.
        \Symfony\Component\HttpFoundation\Request::setTrustedHosts([]);

        parent::tearDown();
    }

    private function ajustes(array $valores): void
    {
        Ajustes::guardar($valores);
        Ajustes::olvidar();
    }

    /** Las fichas para Google de una página, decodificadas. */
    private function fichas(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    private function h1(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, '<h1'), "La página {$url} no tiene exactamente un h1.");
        preg_match('#<h1[^>]*>(.*?)</h1>#s', $html, $m);

        return trim(strip_tags($m[1]));
    }

    private function especialista(array $datos = []): Especialista
    {
        return Especialista::create($datos + [
            'nombre' => 'Camila Rojas',
            'especialidad' => 'Nutricionista',
            'whatsapp' => '912345678',
            'instagram' => 'camila.nutri',
            'activo' => true,
        ]);
    }

    // ---------- 1. Una sola dirección ----------

    public function test_en_produccion_www_e_index_php_van_a_la_direccion_unica(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://progymlosangeles.cl']);

        $this->get('http://www.progymlosangeles.cl/planes?utm_source=x')
            ->assertStatus(301)
            ->assertRedirect('https://progymlosangeles.cl/planes?utm_source=x');

        // Como lo entrega el servidor de verdad: /index.php es el script y
        // /planes lo que va detrás.
        $this->call('GET', 'https://progymlosangeles.cl/index.php/planes', server: [
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
        ])
            ->assertStatus(301)
            ->assertRedirect('https://progymlosangeles.cl/planes');

        $this->get('https://progymlosangeles.cl/planes')->assertOk();
    }

    public function test_fuera_de_produccion_no_se_redirige(): void
    {
        $this->get('http://www.progymlosangeles.cl/planes')->assertOk();
    }

    // ---------- 2. Encabezados ----------

    public function test_el_h1_de_la_portada_dice_gimnasio_y_ciudad(): void
    {
        $this->ajustes(['portada.titulo_1' => 'TRANSFORMA', 'portada.titulo_2' => 'TU CUERPO']);

        $this->assertSame('Gimnasio en el centro de Los Ángeles', $this->h1('/'));
        // El eslogan sigue en la portada, como texto grande.
        $this->get('/')->assertSee('TRANSFORMA')->assertSee('TU CUERPO');
    }

    public function test_el_h1_de_contacto_y_sin_saltos_de_nivel(): void
    {
        $h1 = $this->h1('/contacto');

        $this->assertStringContainsString('Contacto', $h1);
        $this->assertStringContainsString('Los Ángeles', $h1);
        $this->assertStringNotContainsString('<h4', $this->get('/contacto')->getContent());
    }

    // ---------- 3. Nombre, dirección y teléfono ----------

    public function test_el_pie_y_contacto_llevan_la_direccion_y_el_telefono(): void
    {
        $this->ajustes([
            'gimnasio.direccion' => 'Av. Alemania 123',
            'gimnasio.telefono' => '+56 9 1234 5678',
            'web.google_maps' => 'https://maps.app.goo.gl/progym',
        ]);

        $this->get('/planes')
            ->assertSee('href="tel:+56912345678"', false)
            ->assertSee('Cómo llegar');

        $this->get('/contacto')
            ->assertSee('Av. Alemania 123, Los Ángeles, Biobío')
            ->assertDontSee('Cerca de:');

        $this->ajustes(['web.comunas' => 'Nacimiento, Mulchén']);

        $this->get('/contacto')->assertSee('Cerca de: Nacimiento, Mulchén');
    }

    // ---------- 4. Caché ----------

    public function test_cuanto_se_guarda_cada_pagina(): void
    {
        $portada = (string) $this->get('/')->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $portada);
        $this->assertStringContainsString('no-cache', $portada);
        $this->assertStringNotContainsString('no-store', $portada);

        $this->assertStringContainsString('no-store', (string) $this->get('/mi-membresia')->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $this->get('/login')->headers->get('Cache-Control'));

        foreach (['/robots.txt', '/sitemap.xml'] as $url) {
            $respuesta = $this->get($url)->assertOk();
            $this->assertStringContainsString('public', (string) $respuesta->headers->get('Cache-Control'));
            $this->assertStringContainsString('max-age=3600', (string) $respuesta->headers->get('Cache-Control'));
            // Sin cookie: una respuesta con cookie no la guarda Cloudflare.
            $this->assertEmpty($respuesta->headers->getCookies(), "{$url} deja cookies.");
        }
    }

    // ---------- 5. La página que no existe ----------

    public function test_la_pagina_que_no_existe_es_propia(): void
    {
        $html = $this->get('/esto-no-existe')->assertNotFound()->getContent();

        $this->assertStringContainsString('Esta página no existe', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        $this->assertStringContainsString('href="' . route('landing.planes') . '"', $html);
        $this->assertStringContainsString('href="' . route('landing.contacto') . '"', $html);
        $this->assertStringNotContainsString('href="' . route('landing.clases') . '"', $html);

        Clase::create([
            'nombre' => 'Judo', 'color' => 'azul', 'activo' => true, 'orden' => 1,
            'horario' => [['dia' => 'lunes', 'desde' => '19:00', 'hasta' => '20:00']],
        ]);

        $this->get('/esto-no-existe')->assertNotFound()->assertSee('href="' . route('landing.clases') . '"', false);
    }

    // ---------- 6. Qué no se indexa ----------

    public function test_robots_por_pagina(): void
    {
        $this->get('/')->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);
        $this->get('/mi-membresia')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/rutina')->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);
        // Un QR a medias lleva a la lista filtrada; la lista entera sí se indexa.
        $this->followingRedirects()->get('/rutina?objetivo=fuerza&nivel=principiante&dias=3')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/rutinas')->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);
        // El resultado de las preguntas es de quien las contestó.
        $this->get('/rutina?dias=3&nivel=nunca&ayer=nada&hoy=pecho')->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_sin_especialistas_la_pagina_no_existe(): void
    {
        $this->get('/profesionales')->assertNotFound();

        $this->especialista();

        $this->get('/profesionales')->assertOk()->assertSee('Camila Rojas');
    }

    // ---------- 7. Al compartir ----------

    public function test_al_compartir_la_direccion_es_la_canonica_y_twitter_va_con_name(): void
    {
        $html = $this->get('/planes?utm_source=instagram')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:url" content="' . route('landing.planes') . '">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringNotContainsString('property="twitter:', $html);
        // El logo tiene medidas conocidas: WhatsApp arma la vista previa sin bajarlo.
        $this->assertMatchesRegularExpression('#<meta property="og:image:width" content="\d+">#', $html);
    }

    public function test_el_perfil_se_comparte_con_la_foto_del_especialista(): void
    {
        $e = $this->especialista(['foto' => 'especialistas/camila.jpg']);

        $this->get(route('landing.especialista', $e->slug))
            ->assertSee('<meta property="og:image" content="' . url(Storage::disk('public')->url('especialistas/camila.jpg')) . '">', false);
    }

    // ---------- 8. Fichas para Google ----------

    public function test_la_ficha_del_gimnasio_tiene_id_y_sale_tambien_en_contacto(): void
    {
        $this->ajustes(['web.codigo_postal' => '4440000']);

        $ficha = $this->fichas('/')[0];
        $this->assertSame(url('/') . '#gimnasio', $ficha['@id']);
        $this->assertSame('4440000', $ficha['address']['postalCode']);

        $enContacto = collect($this->fichas('/contacto'))->firstWhere('@type', 'ExerciseGym');
        $this->assertSame(url('/') . '#gimnasio', $enContacto['@id']);
    }

    public function test_planes_lleva_lo_que_vende(): void
    {
        $ficha = collect($this->fichas('/planes'))->firstWhere('@type', 'ExerciseGym');

        $this->assertSame(url('/') . '#gimnasio', $ficha['@id']);
        $this->assertNotEmpty($ficha['makesOffer']);
        $this->assertSame('CLP', $ficha['makesOffer'][0]['priceCurrency']);
        $this->assertGreaterThan(0, $ficha['makesOffer'][0]['price']);
    }

    public function test_el_perfil_de_especialista_es_una_persona_del_gimnasio(): void
    {
        $e = $this->especialista(['foto' => 'especialistas/camila.jpg']);

        $persona = collect($this->fichas(route('landing.especialista', $e->slug)))->firstWhere('@type', 'Person');

        $this->assertSame('Camila Rojas', $persona['name']);
        $this->assertSame('Nutricionista', $persona['jobTitle']);
        $this->assertSame(route('landing.especialista', $e->slug), $persona['url']);
        $this->assertSame(['https://www.instagram.com/camila.nutri/'], $persona['sameAs']);
        $this->assertSame(url('/') . '#gimnasio', $persona['worksFor']['@id']);
        $this->assertStringContainsString('especialistas/camila.jpg', $persona['image']);
    }

    // ---------- 9. Título y descripción del perfil ----------

    public function test_titulo_y_descripcion_del_perfil(): void
    {
        $e = $this->especialista();

        $html = $this->get(route('landing.especialista', $e->slug))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Camila Rojas, nutricionista en Los Ángeles | PRO GYM</title>', $html);
        $this->assertStringContainsString('content="Camila Rojas, Nutricionista en PRO GYM, Los Ángeles. Escríbele por WhatsApp para agendar."', $html);
    }

    // ---------- 10. Mapa del sitio ----------

    public function test_el_mapa_del_sitio_lleva_la_fecha_de_cada_pagina(): void
    {
        $clase = Clase::create([
            'nombre' => 'Judo', 'color' => 'azul', 'activo' => true, 'orden' => 1,
            'horario' => [['dia' => 'lunes', 'desde' => '19:00', 'hasta' => '20:00']],
        ]);
        Clase::whereKey($clase->id)->update(['updated_at' => '2026-01-15 10:00:00']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>' . route('landing.clases') . '</loc><lastmod>2026-01-15</lastmod>', $xml);
        $this->assertStringContainsString('<loc>' . route('landing.arriendo') . '</loc>', $xml);
        // La consulta de membresía no se indexa: tampoco va en el mapa.
        $this->assertStringNotContainsString(route('landing.membresia'), $xml);
    }

    // ---------- 11. Fotos ----------

    public function test_la_galeria_lleva_medidas_y_la_ciudad_en_el_texto(): void
    {
        $imagen = imagecreatetruecolor(40, 30);
        Storage::disk('public')->makeDirectory('web');
        imagejpeg($imagen, Storage::disk('public')->path('web/sala.jpg'));
        ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'Sala de máquinas', 'imagen' => 'web/sala.jpg', 'activo' => true]);

        $this->get('/el-gimnasio')
            ->assertOk()
            ->assertSee('alt="Sala de máquinas, gimnasio en Los Ángeles"', false)
            ->assertSee('width="40" height="30"', false);
    }

    // ---------- 12. Íconos y fuentes ----------

    public function test_la_web_no_carga_font_awesome_ni_frena_con_las_fuentes(): void
    {
        foreach (['/', '/contacto', '/planes', '/mi-membresia'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('font-awesome', $html, $url);
            $this->assertStringNotContainsString('class="fas ', $html, $url);
            $this->assertStringNotContainsString('class="fab ', $html, $url);
            $this->assertStringContainsString('<svg class="icono', $html, $url);
            $this->assertStringContainsString('media="print" onload="this.media=\'all\'"', $html, $url);
        }
    }

    /** Todos los íconos que se pueden elegir para un servicio existen como SVG. */
    public function test_los_iconos_de_los_servicios_existen(): void
    {
        foreach (array_keys(ContenidoWeb::ICONOS) as $nombre) {
            $this->assertTrue(Iconos::existe($nombre), "Falta el ícono {$nombre}.");
        }

        $this->assertStringStartsWith('<svg class="icono text-xl"', (string) Iconos::svg('fab fa-instagram', 'text-xl'));
        $this->assertSame('', (string) Iconos::svg('no-existe'));
    }
}
