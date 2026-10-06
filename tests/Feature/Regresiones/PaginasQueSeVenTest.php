<?php

namespace Tests\Feature\Regresiones;

use App\Support\Ajustes;
use App\Support\PaginasWeb;
use Tests\CasoConCatalogos;

/**
 * Configuración → Páginas que se ven: cada sección de la web se enciende
 * cuando está lista. Apagada no sale en el menú, ni en el pie, ni en la
 * portada, ni en el mapa del sitio, y su dirección no existe para el público.
 */
class PaginasQueSeVenTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
    }

    private function apagar(string ...$paginas): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/configuracion', collect($paginas)->mapWithKeys(fn ($p) => ["paginas.{$p}" => '0'])->all())
            ->assertSessionHasNoErrors();

        // Lo que sigue lo mira el público, sin sesión.
        auth()->logout();
    }

    public function test_sin_tocar_nada_todas_se_ven(): void
    {
        foreach (array_keys(PaginasWeb::PAGINAS) as $clave) {
            $this->assertTrue(PaginasWeb::encendida($clave), $clave);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee(route('landing.gimnasio'), false)
            ->assertSee(route('landing.arriendo'), false)
            ->assertSee(route('landing.membresia'), false)
            ->assertSee(route('landing.rutina'), false);
    }

    public function test_apagada_no_existe_para_el_publico_ni_sale_en_el_menu(): void
    {
        $this->apagar('arriendo', 'rutinas', 'gimnasio', 'membresia');

        $this->get('/arriendo-por-horas')->assertNotFound();
        $this->get('/rutina')->assertNotFound();
        $this->get('/rutinas')->assertNotFound();
        $this->get('/ejercicios')->assertNotFound();
        $this->get('/el-gimnasio')->assertNotFound();
        $this->get('/mi-membresia')->assertNotFound();
        $this->post('/consultar-membresia', ['rut' => '11.111.111-1'])->assertNotFound();

        $inicio = $this->get('/')->assertOk();
        $inicio->assertDontSee(route('landing.arriendo'), false)
            ->assertDontSee(route('landing.rutina'), false)
            ->assertDontSee(route('landing.gimnasio'), false)
            ->assertDontSee(route('landing.membresia'), false)
            ->assertDontSee('Rutinas para entrenar')
            ->assertDontSee('Conoce el gimnasio');

        // Lo demás sigue igual.
        $this->get('/planes')->assertOk();
        $this->get('/contacto')->assertOk()->assertDontSee(route('landing.arriendo'), false);
    }

    public function test_el_mapa_del_sitio_no_ofrece_las_apagadas(): void
    {
        $this->apagar('arriendo', 'rutinas');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('landing.arriendo'), false)
            ->assertDontSee(route('landing.rutinas'), false)
            ->assertDontSee('/rutinas/', false)
            ->assertSee(route('landing.planes'), false);
    }

    public function test_quien_entro_al_panel_la_ve_con_el_aviso(): void
    {
        $this->apagar('arriendo');

        $this->actingAs($this->administrador())
            ->get('/arriendo-por-horas')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('está apagada: solo tú la ves');

        // Encendida, la franja no aparece.
        $this->get('/planes')->assertOk()->assertDontSee('está apagada');
    }

    public function test_al_encenderla_vuelve(): void
    {
        $this->apagar('planes');
        $this->get('/planes')->assertNotFound();

        $this->actingAs($this->administrador())
            ->put('/panel/configuracion', ['paginas.planes' => '1'])
            ->assertSessionHasNoErrors();
        auth()->logout();

        $this->get('/planes')->assertOk();
        $this->get('/')->assertSee(route('landing.planes'), false);
    }

    public function test_la_seccion_se_abre_en_configuracion(): void
    {
        $ajustes = collect(
            $this->actingAs($this->administrador())
                ->get('/panel/configuracion/paginas')
                ->assertOk()
                ->viewData('page')['props']['grupo']['ajustes']
        );

        $this->assertSame(
            array_map(fn ($c) => "paginas.{$c}", array_keys(PaginasWeb::PAGINAS)),
            $ajustes->pluck('clave')->all()
        );
    }
}
