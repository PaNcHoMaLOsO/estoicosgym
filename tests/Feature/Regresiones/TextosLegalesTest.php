<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\TextoLegal;
use App\Support\Ajustes;
use App\Support\TextosLegales;
use Tests\CasoConCatalogos;

/**
 * El contrato, los términos y condiciones y la política de privacidad.
 *
 * Lo que se vigila: que la web publique lo que el gimnasio escribió, con sus
 * datos; que lo escrito a mano no pueda meter nada en la página; y que lo que
 * alguien ya firmó no cambie al editar.
 */
class TextosLegalesTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
    }

    public function test_la_web_publica_los_terminos_y_la_privacidad_con_los_datos_del_gimnasio(): void
    {
        Ajustes::guardar(['gimnasio.nombre' => 'PRO GYM', 'gimnasio.email' => 'hola@progym.cl']);

        $this->get('/terminos')
            ->assertOk()
            ->assertSee('Términos y condiciones')
            ->assertSee('hola@progym.cl')
            ->assertDontSee('{email_gimnasio}');

        $this->get('/privacidad')
            ->assertOk()
            ->assertSee('Agencia de Protección de Datos Personales')
            ->assertSee('hola@progym.cl')
            ->assertDontSee('{gimnasio}');
    }

    public function test_el_pie_de_la_web_lleva_a_los_terminos(): void
    {
        $this->get('/')->assertOk()->assertSee(route('landing.terminos'), false);
    }

    public function test_el_mapa_del_sitio_incluye_los_terminos(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('landing.terminos'), false);
    }

    public function test_lo_que_se_escribe_en_el_panel_sale_en_la_web(): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/textos-legales/terminos', [
                'contenido' => "# Términos\n\nEn {gimnasio} no se entra con mascotas.",
            ])
            ->assertSessionHas('success');

        $this->get('/terminos')->assertSee('no se entra con mascotas');
    }

    /**
     * Lo escribe quien tenga Configuración y se publica en la web: una
     * etiqueta o un enlace «javascript:» no pueden llegar a la página.
     */
    public function test_el_html_escrito_a_mano_no_llega_a_la_web(): void
    {
        $this->actingAs($this->administrador())->put('/panel/textos-legales/terminos', [
            'contenido' => "Hola <script>alert('x')</script> y [un enlace](javascript:alert(1)).",
        ]);

        $this->get('/terminos')
            ->assertOk()
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertDontSee('javascript:alert', false);
    }

    /** Un dato del gimnasio con un «<» no puede colar nada tampoco. */
    public function test_los_datos_se_escapan(): void
    {
        Ajustes::guardar(['gimnasio.direccion' => 'Colón <b>123</b>']);

        $this->get('/terminos')->assertDontSee('<b>123</b>', false);
    }

    /** Una {variable} mal escrita saldría con las llaves puestas en el contrato de un socio. */
    public function test_una_variable_mal_escrita_no_se_guarda(): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/textos-legales/contrato', ['contenido' => 'Firma {nombre_del_socio}'])
            ->assertSessionHasErrors('contenido');
    }

    public function test_si_nadie_lo_firmo_se_corrige_sin_cambiar_de_version(): void
    {
        $antes = TextosLegales::vigente('contrato');

        $this->actingAs($this->administrador())
            ->put('/panel/textos-legales/contrato', ['contenido' => 'Contrato corregido para {socio}']);

        $this->assertSame($antes->version, TextosLegales::vigente('contrato')->version);
        $this->assertSame(1, TextoLegal::where('tipo', 'contrato')->count());
        $this->assertSame('Contrato corregido para {socio}', TextosLegales::vigente('contrato')->contenido);
    }

    /**
     * EL QUE IMPORTA: si alguien ya la firmó, se crea la versión siguiente y
     * la firmada queda como estaba. Es lo que esa persona aceptó.
     */
    public function test_si_alguien_ya_lo_firmo_se_crea_otra_version(): void
    {
        $firmada = TextosLegales::vigente('contrato');
        Cliente::factory()->create(['contrato_version' => (string) $firmada->version]);

        $this->actingAs($this->administrador())
            ->put('/panel/textos-legales/contrato', ['contenido' => 'Contrato nuevo para {socio}']);

        $this->assertSame($firmada->version + 1, TextosLegales::vigente('contrato')->version);
        $this->assertSame($firmada->contenido, $firmada->fresh()->contenido);
    }

    /** Guardarlo sin cambios lo marca como revisado: deja de ser el texto base. */
    public function test_guardarlo_sin_cambios_lo_da_por_revisado(): void
    {
        $base = TextosLegales::vigente('privacidad');
        $this->assertTrue(TextosLegales::esElTextoBase($base));

        $this->actingAs($this->administrador())
            ->put('/panel/textos-legales/privacidad', ['contenido' => $base->contenido]);

        $this->assertFalse(TextosLegales::esElTextoBase($base->fresh()));
    }

    public function test_recepcion_no_cambia_los_textos(): void
    {
        $this->actingAs($this->recepcionista())
            ->put('/panel/textos-legales/terminos', ['contenido' => 'Otra cosa'])
            ->assertForbidden();
    }

    public function test_la_pantalla_abre_y_la_vista_previa_rellena_un_socio_de_muestra(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)->get('/panel/textos-legales/contrato')->assertOk();

        $this->actingAs($admin)
            ->postJson('/panel/textos-legales/contrato/vista-previa', ['contenido' => 'Socio: {socio}. Plan {nada}.'])
            ->assertOk()
            ->assertJsonPath('desconocidas', ['nada'])
            ->assertSee('Camila Rojas Soto');
    }
}
