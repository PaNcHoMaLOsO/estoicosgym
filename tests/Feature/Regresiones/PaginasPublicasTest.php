<?php

namespace Tests\Feature\Regresiones;

use App\Models\ContenidoWeb;
use App\Models\Convenio;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * La web de los clientes, en páginas separadas y manejada desde el panel.
 *
 * Lo que se vigila: que cada tema tenga su página con su propio título —es lo
 * que muestra Google—, que el menú lleve a ellas y no a anclas de una página
 * única, y que lo que se escribe en el panel —portada, aviso, horario,
 * WhatsApp, preguntas— salga donde tiene que salir y se vaya cuando tiene
 * que irse.
 */
class PaginasPublicasTest extends CasoConCatalogos
{
    private const PAGINAS = [
        '/' => 'landing',
        '/el-gimnasio' => 'landing.gimnasio',
        '/planes' => 'landing.planes',
        '/convenios' => 'landing.convenios',
        '/especialistas' => 'landing.especialistas',
        '/contacto' => 'landing.contacto',
        '/mi-membresia' => 'landing.membresia',
        '/privacidad' => 'landing.privacidad',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // La caché de ajustes vive entre pruebas y arrastraría la anterior.
        Ajustes::olvidar();
    }

    private function guardarAjustes(array $valores)
    {
        $respuesta = $this->actingAs($this->administrador())->put('/panel/configuracion', $valores);
        Ajustes::olvidar();

        return $respuesta;
    }

    /** La ficha para Google de una página, ya decodificada. */
    private function fichaDe(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $this->assertNotEmpty($m, "La página {$url} no trae ficha para Google.");

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    // ---------- Una página por tema ----------

    /** Cada página responde con su título, su dirección y un solo h1: no hay dos iguales. */
    public function test_cada_pagina_tiene_su_titulo_y_su_direccion(): void
    {
        $titulos = [];

        foreach (self::PAGINAS as $url => $ruta) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match('#<title>(.*?)</title>#s', $html, $m);
            $titulos[$url] = $m[1] ?? null;

            $this->assertStringContainsString('<link rel="canonical" href="' . route($ruta) . '">', $html, $url);
            $this->assertSame(1, substr_count($html, '<h1'), "La página {$url} no tiene exactamente un h1.");
        }

        $this->assertSame(count($titulos), count(array_unique($titulos)), 'Hay páginas con el mismo título.');
        $this->assertStringContainsString('Planes y precios', $titulos['/planes']);
        $this->assertStringContainsString('Los Ángeles', $titulos['/convenios']);
    }

    /** El menú lleva a las páginas, no a anclas de una página única. */
    public function test_el_menu_lleva_a_las_paginas(): void
    {
        $html = $this->get('/planes')->assertOk()->getContent();

        // La página en la que se está queda marcada.
        $this->assertMatchesRegularExpression(
            '#href="' . preg_quote(route('landing.planes'), '#') . '"\s+aria-current="page"#',
            $html
        );

        foreach (['landing.gimnasio', 'landing.contacto', 'landing.membresia'] as $ruta) {
            $this->assertStringContainsString('href="' . route($ruta) . '"', $html);
        }

        foreach (['#planes', '#contacto', '#consulta', '#servicios'] as $ancla) {
            $this->assertStringNotContainsString('href="' . $ancla . '"', $html);
        }
    }

    public function test_el_mapa_del_sitio_lista_las_paginas_con_contenido(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>' . route('landing.planes') . '</loc>', false)
            ->assertSee('<loc>' . route('landing.gimnasio') . '</loc>', false)
            ->assertSee('<loc>' . route('landing.contacto') . '</loc>', false)
            // Sin convenios publicados, su página no se le ofrece a Google.
            ->assertDontSee(route('landing.convenios'), false);

        Convenio::create(['nombre' => 'UCSC', 'tipo' => 'institucion_educativa', 'activo' => true, 'mostrar_en_web' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>' . route('landing.convenios') . '</loc>', false);
    }

    // ---------- Lo que se escribe en el panel ----------

    public function test_los_textos_de_la_portada_salen_de_configuracion(): void
    {
        $this->guardarAjustes([
            'portada.titulo_1' => 'ENTRENA',
            'portada.titulo_2' => 'CON NOSOTROS',
            'portada.subtitulo' => 'El gimnasio de Los Ángeles.',
        ])->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('ENTRENA')
            ->assertSee('CON NOSOTROS')
            ->assertSee('El gimnasio de Los Ángeles.');
    }

    /**
     * EL QUE IMPORTA: el aviso se va solo al pasar su fecha.
     *
     * Un «cerramos el sábado» que sigue ahí el lunes es peor que no avisar.
     */
    public function test_el_aviso_se_va_solo_al_pasar_su_fecha(): void
    {
        $this->guardarAjustes([
            'portada.aviso' => 'Este sábado cerramos a las 14:00',
            'portada.aviso_desde' => today()->toDateString(),
            'portada.aviso_hasta' => today()->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors();

        // Sale en todas las páginas, no solo en la portada.
        $this->get('/planes')->assertOk()->assertSee('Este sábado cerramos a las 14:00');

        $this->travel(3)->days();

        $this->get('/planes')->assertOk()->assertDontSee('Este sábado cerramos a las 14:00');
    }

    public function test_un_aviso_que_todavia_no_empieza_no_sale(): void
    {
        $this->guardarAjustes([
            'portada.aviso' => 'Horario de verano',
            'portada.aviso_desde' => today()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertDontSee('Horario de verano');
    }

    public function test_una_fecha_mal_escrita_se_rechaza(): void
    {
        $this->guardarAjustes(['portada.aviso_hasta' => '31-12-2026'])
            ->assertSessionHasErrors('portada.aviso_hasta');
    }

    /** El horario sale día por día y Google lo lee en la ficha del gimnasio. */
    public function test_el_horario_sale_en_la_pagina_y_en_la_ficha(): void
    {
        $this->guardarAjustes([
            'horario.lunes' => '07:00-22:00',
            'horario.sabado' => '9:00-13:00, 15:00-18:00',
            'horario.nota' => 'Festivos de 9:00 a 14:00',
        ])->assertSessionHasNoErrors();

        $html = $this->get('/contacto')->assertOk()->getContent();

        // El tramo se lee «07:00 a 22:00»: antes iba con un guion largo, que es
        // de lo que más delata un texto escrito por una máquina.
        $this->assertStringContainsString('07:00 a 22:00', $html);
        // «9:00» se escribe como «09:00», y la pausa del mediodía se respeta: el
        // día con dos tramos muestra los dos. Los tramos van en líneas aparte,
        // así que se piden de a uno: juntos en una línea era cómo se veían
        // antes, no lo que hay que garantizar.
        $this->assertStringContainsString('09:00 a 13:00', $html);
        $this->assertStringContainsString('15:00 a 18:00', $html);
        $this->assertStringContainsString('Festivos de 9:00 a 14:00', $html);
        $this->assertStringContainsString('Cerrado', $html);

        $sabado = collect($this->fichaDe('/')['openingHoursSpecification'])
            ->where('dayOfWeek', 'https://schema.org/Saturday')
            ->values();

        $this->assertCount(2, $sabado);
        $this->assertSame(['09:00', '13:00'], [$sabado[0]['opens'], $sabado[0]['closes']]);
    }

    public function test_un_horario_mal_escrito_se_rechaza(): void
    {
        $this->guardarAjustes(['horario.lunes' => 'de 7 a 22'])
            ->assertSessionHasErrors('horario.lunes');
    }

    public function test_el_whatsapp_flotante_sale_cuando_se_configura(): void
    {
        $this->get('/')->assertOk()->assertDontSee('wa.me/', false);

        $this->guardarAjustes(['web.whatsapp' => '+56 9 8765 4321'])->assertSessionHasNoErrors();

        $this->get('/planes')->assertOk()->assertSee('https://wa.me/56987654321?text=', false);
    }

    public function test_un_whatsapp_que_no_es_celular_se_rechaza(): void
    {
        $this->guardarAjustes(['web.whatsapp' => '41 234 5678'])
            ->assertSessionHasErrors('web.whatsapp');
    }

    /**
     * Las preguntas frecuentes ya no salen en la web (2026-09-18, decisión del
     * dueño). Y sin preguntas a la vista tampoco va la ficha FAQPage: Google no
     * acepta que se le declaren preguntas que el visitante no puede leer.
     */
    public function test_las_preguntas_no_salen_en_contacto_ni_su_ficha(): void
    {
        ContenidoWeb::create(['tipo' => 'pregunta', 'titulo' => '¿Necesito llevar candado?', 'texto' => 'Sí, para los casilleros.', 'activo' => true]);

        $respuesta = $this->get('/contacto')->assertOk()->assertDontSee('¿Necesito llevar candado?');

        $this->assertStringNotContainsString('FAQPage', $respuesta->getContent());
    }

    /** Los servicios salen de la base, no escritos en la vista: lo oculto no aparece. */
    public function test_los_servicios_salen_de_la_pagina_web_del_panel(): void
    {
        // Los tres de siempre vienen de fábrica.
        $this->get('/')->assertOk()->assertSee('Musculación')->assertSee('>Cardio<', false);

        ContenidoWeb::where('tipo', 'servicio')->where('titulo', 'Cardio')->update(['activo' => false]);
        ContenidoWeb::create(['tipo' => 'servicio', 'titulo' => 'Clases de spinning', 'texto' => 'Lunes y miércoles.', 'icono' => 'bicycle', 'activo' => true, 'orden' => 9]);

        $this->get('/el-gimnasio')
            ->assertOk()
            ->assertDontSee('>Cardio<', false)
            ->assertSee('Clases de spinning');
    }

    // ---------- La cinta de logos ----------

    /** Con uno o dos convenios los logos quedan quietos: el mismo pasando una y otra vez parece un error. */
    public function test_la_cinta_de_logos_se_mueve_desde_tres_convenios(): void
    {
        Convenio::create(['nombre' => 'UCSC', 'tipo' => 'institucion_educativa', 'activo' => true, 'mostrar_en_web' => true]);

        $this->get('/')->assertOk()->assertSee('class="cinta cinta-quieta"', false);

        foreach (['AIEP', 'Santo Tomás'] as $nombre) {
            Convenio::create(['nombre' => $nombre, 'tipo' => 'institucion_educativa', 'activo' => true, 'mostrar_en_web' => true]);
        }

        $this->get('/')
            ->assertOk()
            ->assertDontSee('class="cinta cinta-quieta"', false)
            // Las copias que llenan la vuelta no se leen dos veces con un lector de pantalla.
            ->assertSee('data-repetido aria-hidden="true"', false);
    }
}
