<?php

namespace Tests\Feature\Regresiones;

use App\Models\Membresia;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * La web de los clientes, vista por Google.
 *
 * Lo que se vigila: que diga dónde está el gimnasio con las palabras que la
 * gente busca, que la ficha estructurada traiga los planes y precios de
 * verdad, que no le muestre a nadie la puerta del panel, y que Analytics solo
 * se cargue si alguien lo configuró —y con el consentimiento denegado hasta
 * que la persona acepte—.
 */
class WebPublicaTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        // La caché de ajustes vive entre pruebas y arrastraría la anterior.
        Ajustes::olvidar();
    }

    private function guardarAjustes(array $valores)
    {
        return $this->actingAs($this->administrador())->put('/panel/configuracion', $valores);
    }

    /** La ficha schema.org que va en la portada, ya decodificada. */
    private function fichaParaGoogle(): array
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $this->assertNotEmpty($m, 'La portada no trae la ficha para Google.');

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    // ---------- Dónde está ----------

    public function test_el_titulo_dice_gimnasio_en_los_angeles(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>PRO GYM | Gimnasio en Los Ángeles, Biobío</title>', false)
            ->assertSee('Gimnasio en Los Ángeles', false)
            ->assertSee('<link rel="canonical" href="' . url('/') . '">', false);
    }

    /** La ficha lleva los planes y los precios del catálogo, no unos escritos a mano. */
    public function test_la_ficha_para_google_trae_los_planes_y_precios_reales(): void
    {
        $ficha = $this->fichaParaGoogle();

        $this->assertSame('ExerciseGym', $ficha['@type']);
        $this->assertSame('Los Ángeles', $ficha['address']['addressLocality']);
        $this->assertSame('Biobío', $ficha['address']['addressRegion']);
        $this->assertSame('CL', $ficha['address']['addressCountry']);

        $esperados = Membresia::where('activo', true)
            ->with(['precios' => fn ($q) => $q->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderByDesc('fecha_vigencia_desde')])
            ->get()
            ->filter(fn (Membresia $m) => $m->precios->isNotEmpty())
            ->map(fn (Membresia $m) => (int) $m->precios->first()->precio_normal)
            ->sort()
            ->values()
            ->all();

        $this->assertSame($esperados, collect($ficha['makesOffer'])->pluck('price')->sort()->values()->all());
    }

    /** Cambiar la ciudad en Configuración cambia lo que lee Google. */
    public function test_la_ciudad_sale_de_configuracion(): void
    {
        $this->guardarAjustes(['web.ciudad' => 'Nacimiento'])->assertSessionHasNoErrors();
        Ajustes::olvidar();

        $this->assertSame('Nacimiento', $this->fichaParaGoogle()['address']['addressLocality']);
    }

    // ---------- La puerta del panel ----------

    /**
     * La web de los clientes no enlaza al panel.
     *
     * Quien trabaja ahí ya sabe la dirección; el botón «Acceder» solo le
     * mostraba la puerta a todos los demás.
     */
    public function test_la_web_no_enlaza_al_panel(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('login'), false)
            ->assertDontSee('/panel', false)
            ->assertDontSee('Acceder', false);
    }

    /** robots.txt apunta al mapa del sitio y NO nombra el panel: ese archivo lo lee cualquiera. */
    public function test_robots_apunta_al_mapa_sin_nombrar_el_panel(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: ' . route('landing.sitemap'), false)
            ->assertDontSee('login', false)
            ->assertDontSee('panel', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>' . url('/') . '</loc>', false);
    }

    // ---------- Analytics ----------

    public function test_sin_id_no_se_carga_analytics_ni_el_aviso(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag', false)
            ->assertDontSee('aviso-cookies', false);
    }

    /** Con ID se carga, pero con el consentimiento denegado hasta que la persona acepta. */
    public function test_con_id_se_carga_con_el_consentimiento_denegado(): void
    {
        $this->guardarAjustes(['web.google_analytics' => 'G-ABC123XYZ9'])->assertSessionHasNoErrors();
        Ajustes::olvidar();

        $this->get('/')
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-ABC123XYZ9', false)
            ->assertSee("analytics_storage: 'denied'", false)
            ->assertSee('aviso-cookies', false);
    }

    public function test_un_id_de_analytics_mal_copiado_se_rechaza(): void
    {
        $this->guardarAjustes(['web.google_analytics' => 'UA-12345-1'])
            ->assertSessionHasErrors('web.google_analytics');
    }

    /** La política de contenido deja pasar a Analytics: si no, se bloquearía en silencio. */
    public function test_la_politica_de_contenido_deja_pasar_analytics(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://www.googletagmanager.com', $csp);
        $this->assertStringContainsString('google-analytics.com', $csp);
    }

    // ---------- Redes y mapa ----------

    /**
     * Un enlace «javascript:» no se acepta.
     *
     * Termina en un href de la página pública: sería meter código en lo que
     * ven los clientes.
     */
    public function test_un_enlace_javascript_no_se_acepta_como_red_social(): void
    {
        $this->guardarAjustes(['web.instagram' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('web.instagram');

        $this->guardarAjustes(['web.instagram' => 'https://www.instagram.com/progym'])
            ->assertSessionHasNoErrors();
    }

    public function test_con_redes_y_mapa_aparecen_en_la_web_y_en_la_ficha(): void
    {
        $this->guardarAjustes([
            'web.instagram' => 'https://www.instagram.com/progym',
            'web.google_maps' => 'https://maps.app.goo.gl/abc123',
        ])->assertSessionHasNoErrors();
        Ajustes::olvidar();

        $this->get('/')
            ->assertOk()
            ->assertSee('https://www.instagram.com/progym', false)
            ->assertSee('Cómo llegar', false);

        $ficha = $this->fichaParaGoogle();

        $this->assertContains('https://www.instagram.com/progym', $ficha['sameAs']);
        $this->assertSame('https://maps.app.goo.gl/abc123', $ficha['hasMap']);
    }
}
