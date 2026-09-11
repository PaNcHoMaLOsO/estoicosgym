<?php

namespace Tests\Feature\Regresiones;

use App\Models\Convenio;
use App\Models\Especialista;
use App\Support\Ajustes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Los convenios y los especialistas en la web de los clientes, manejados
 * desde el panel.
 *
 * Lo que se vigila: que solo salga lo marcado para la web —el catálogo trae
 * convenios de ejemplo—, que el logo y la foto no se pierdan al editar otra
 * cosa, y que los enlaces de WhatsApp e Instagram los arme el sistema y no
 * alguien escribiendo a mano.
 */
class ConveniosYEspecialistasTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Ajustes::olvidar();
    }

    private function convenio(array $extra = []): Convenio
    {
        return Convenio::create($extra + [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'activo' => true,
        ]);
    }

    private function admin()
    {
        return $this->actingAs($this->administrador());
    }

    // ---------- Convenios en la web ----------

    public function test_un_convenio_marcado_sale_en_la_web_bajo_su_categoria(): void
    {
        $this->convenio(['mostrar_en_web' => true, 'requisito_web' => 'Estudiantes con credencial vigente']);
        $this->convenio(['nombre' => 'Banco de Ejemplo', 'tipo' => 'empresa']);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="convenios"', false)
            ->assertSee('Universidades e institutos')
            ->assertSee('UCSC')
            ->assertSee('Estudiantes con credencial vigente')
            ->assertSee('href="#convenios"', false)
            // El que no se marcó no sale, ni su categoría.
            ->assertDontSee('Banco de Ejemplo')
            ->assertDontSee('>Empresas<', false);
    }

    /** Sin convenios marcados no hay sección ni enlace en el menú. */
    public function test_sin_convenios_marcados_no_hay_seccion(): void
    {
        $this->convenio();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="convenios"', false)
            ->assertDontSee('href="#convenios"', false);
    }

    /** El precio de convenio sale del catálogo de planes. */
    public function test_la_seccion_dice_el_precio_con_convenio(): void
    {
        $this->convenio(['mostrar_en_web' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Con convenio, el plan Mensual queda en', false)
            ->assertSee('$25.000', false);
    }

    // ---------- El logo ----------

    public function test_el_logo_se_sube_y_cambiarlo_borra_el_anterior(): void
    {
        $this->admin()->post('/panel/convenios', [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'activo' => true,
            'mostrar_en_web' => true,
            'logo' => UploadedFile::fake()->image('ucsc.png', 400, 150),
        ])->assertSessionHasNoErrors();

        $convenio = Convenio::where('nombre', 'UCSC')->firstOrFail();
        $viejo = $convenio->logo;
        Storage::disk('public')->assertExists($viejo);

        // Como lo manda el formulario con un archivo: POST diciendo que es PUT.
        $this->admin()->post("/panel/convenios/{$convenio->uuid}", [
            '_method' => 'PUT',
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'logo' => UploadedFile::fake()->image('ucsc-nuevo.png', 400, 150),
        ])->assertSessionHasNoErrors();

        $nuevo = $convenio->refresh()->logo;

        $this->assertNotSame($viejo, $nuevo);
        Storage::disk('public')->assertMissing($viejo);
        Storage::disk('public')->assertExists($nuevo);
    }

    /**
     * EL QUE IMPORTA: editar sin elegir logo no borra el que había.
     *
     * Es el mismo error que tuvo la foto de los socios: si el logo fuera un
     * campo más, corregir el nombre lo dejaría vacío.
     */
    public function test_editar_sin_elegir_logo_no_borra_el_que_habia(): void
    {
        Storage::disk('public')->put('convenios/ucsc.png', 'imagen');
        $convenio = $this->convenio(['logo' => 'convenios/ucsc.png', 'mostrar_en_web' => true]);

        $this->admin()->put("/panel/convenios/{$convenio->uuid}", [
            'nombre' => 'UCSC Los Ángeles',
            'tipo' => 'institucion_educativa',
            'logo' => null,
            'quitar_logo' => false,
        ])->assertSessionHasNoErrors();

        $convenio->refresh();

        $this->assertSame('UCSC Los Ángeles', $convenio->nombre);
        $this->assertSame('convenios/ucsc.png', $convenio->logo);
        Storage::disk('public')->assertExists('convenios/ucsc.png');
    }

    /** Un formulario que no manda la casilla de la web no puede esconder el convenio. */
    public function test_editar_sin_la_casilla_de_la_web_no_lo_esconde(): void
    {
        $convenio = $this->convenio(['mostrar_en_web' => true]);

        $this->admin()->put("/panel/convenios/{$convenio->uuid}", [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($convenio->refresh()->mostrar_en_web);
    }

    public function test_quitar_el_logo_borra_el_archivo(): void
    {
        Storage::disk('public')->put('convenios/ucsc.png', 'imagen');
        $convenio = $this->convenio(['logo' => 'convenios/ucsc.png']);

        $this->admin()->put("/panel/convenios/{$convenio->uuid}", [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'quitar_logo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertNull($convenio->refresh()->logo);
        Storage::disk('public')->assertMissing('convenios/ucsc.png');
    }

    /** Un SVG puede llevar código que se ejecutaría al abrirlo desde la web. */
    public function test_un_svg_no_se_acepta_como_logo(): void
    {
        $this->admin()->post('/panel/convenios', [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'logo' => UploadedFile::fake()->create('ucsc.svg', 4, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');

        $this->assertSame(0, Convenio::where('nombre', 'UCSC')->count());
    }

    // ---------- Especialistas ----------

    public function test_se_crea_un_especialista_y_sus_enlaces_se_guardan_limpios(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Diego Soto',
            'especialidad' => 'Personal trainer',
            'descripcion' => 'Fuerza e hipertrofia.',
            'whatsapp' => '+56 9 1234 5678',
            'instagram' => 'https://www.instagram.com/diego.fit/?hl=es',
            'orden' => 1,
            'activo' => true,
            'foto' => UploadedFile::fake()->image('diego.jpg', 300, 300),
        ])->assertSessionHasNoErrors();

        $especialista = Especialista::where('nombre', 'Diego Soto')->firstOrFail();

        $this->assertSame('56912345678', $especialista->whatsapp);
        $this->assertSame('diego.fit', $especialista->instagram);
        Storage::disk('public')->assertExists($especialista->foto);
    }

    public function test_el_especialista_sale_en_la_web_con_sus_botones(): void
    {
        Especialista::create([
            'nombre' => 'Diego Soto',
            'especialidad' => 'Personal trainer',
            'whatsapp' => '56912345678',
            'instagram' => 'diego.fit',
            'activo' => true,
        ]);

        $saludo = rawurlencode('Hola Diego Soto, te escribo desde la web de PRO GYM.');

        $this->get('/')
            ->assertOk()
            ->assertSee('NUESTROS ESPECIALISTAS')
            ->assertSee('href="#especialistas"', false)
            ->assertSee('https://wa.me/56912345678?text=' . $saludo, false)
            ->assertSee('https://www.instagram.com/diego.fit/', false);
    }

    public function test_un_especialista_oculto_no_sale(): void
    {
        Especialista::create(['nombre' => 'Diego Soto', 'especialidad' => 'Personal trainer', 'activo' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Diego Soto')
            ->assertDontSee('id="especialistas"', false);
    }

    /** Ocultar y volver a mostrar va por el mismo interruptor que los otros catálogos. */
    public function test_se_oculta_con_el_interruptor_de_los_catalogos(): void
    {
        $especialista = Especialista::create(['nombre' => 'Diego Soto', 'especialidad' => 'Personal trainer', 'activo' => true]);

        $this->admin()->patch("/panel/catalogos/especialistas/{$especialista->uuid}/alternar")
            ->assertSessionHasNoErrors();

        $this->assertFalse($especialista->refresh()->activo);
    }

    public function test_un_whatsapp_que_no_es_celular_se_rechaza(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Diego Soto',
            'especialidad' => 'Personal trainer',
            'whatsapp' => '12345',
        ])->assertSessionHasErrors('whatsapp');
    }

    /** Lo que termina en un enlace de la página pública no puede ser código. */
    public function test_un_instagram_con_codigo_se_rechaza(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Diego Soto',
            'especialidad' => 'Personal trainer',
            'instagram' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('instagram');

        $this->assertSame(0, Especialista::count());
    }

    /** Editar sin elegir foto no borra la que había. */
    public function test_editar_sin_elegir_foto_no_la_borra(): void
    {
        Storage::disk('public')->put('especialistas/diego.jpg', 'foto');
        $especialista = Especialista::create([
            'nombre' => 'Diego Soto',
            'especialidad' => 'Personal trainer',
            'foto' => 'especialistas/diego.jpg',
            'activo' => true,
        ]);

        $this->admin()->put("/panel/especialistas/{$especialista->uuid}", [
            'nombre' => 'Diego Soto',
            'especialidad' => 'Preparador físico',
            'foto' => null,
            'quitar_foto' => false,
            'activo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame('especialistas/diego.jpg', $especialista->refresh()->foto);
        Storage::disk('public')->assertExists('especialistas/diego.jpg');
    }

    public function test_recepcion_no_maneja_los_especialistas(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/especialistas')
            ->assertForbidden();
    }
}
