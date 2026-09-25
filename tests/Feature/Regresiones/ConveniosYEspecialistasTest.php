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

        $this->get('/convenios')
            ->assertOk()
            ->assertSee('id="convenios"', false)
            ->assertSee('Universidades e institutos')
            ->assertSee('UCSC')
            ->assertSee('Estudiantes con credencial vigente')
            // El que no se marcó no sale, ni su categoría.
            ->assertDontSee('Banco de Ejemplo')
            ->assertDontSee('>Empresas<', false);

        // El menú de todas las páginas lleva a los convenios.
        $this->get('/')
            ->assertOk()
            ->assertSee('href="' . route('landing.convenios') . '"', false);
    }

    /** Sin convenios marcados no hay enlace en el menú ni logos en la portada. */
    public function test_sin_convenios_marcados_no_hay_seccion(): void
    {
        $this->convenio();

        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('landing.convenios'), false)
            ->assertDontSee('Convenios con', false);

        // Quien llega directo a la página ve que no hay, no una página rota.
        $this->get('/convenios')
            ->assertOk()
            ->assertSee('Todavía no publicamos convenios');
    }

    /** El precio de convenio sale del catálogo de planes. */
    public function test_la_seccion_dice_el_precio_con_convenio(): void
    {
        $this->convenio(['mostrar_en_web' => true]);

        $this->get('/convenios')
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

    /**
     * El margen blanco del logo se recorta al subirlo.
     *
     * Era lo que hacía verse diminuto un logo en su recuadro: el espacio lo
     * ocupaba el blanco de alrededor, no la marca.
     */
    public function test_el_margen_blanco_del_logo_se_recorta(): void
    {
        $imagen = imagecreatetruecolor(400, 300);
        imagefill($imagen, 0, 0, imagecolorallocate($imagen, 255, 255, 255));
        imagefilledrectangle($imagen, 150, 120, 249, 179, imagecolorallocate($imagen, 200, 20, 30));
        ob_start();
        imagepng($imagen);
        $png = (string) ob_get_clean();

        $this->admin()->post('/panel/convenios', [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'activo' => true,
            'logo' => UploadedFile::fake()->createWithContent('ucsc.png', $png),
        ])->assertSessionHasNoErrors();

        $logo = Convenio::where('nombre', 'UCSC')->firstOrFail()->logo;

        $this->assertSame([100, 60], array_slice(getimagesize(Storage::disk('public')->path($logo)), 0, 2));
    }

    /** Un logo que ya llega justo no se toca. */
    public function test_un_logo_sin_margen_queda_igual(): void
    {
        $this->admin()->post('/panel/convenios', [
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'activo' => true,
            'logo' => UploadedFile::fake()->image('ucsc.png', 400, 150),
        ])->assertSessionHasNoErrors();

        $logo = Convenio::where('nombre', 'UCSC')->firstOrFail()->logo;

        $this->assertSame([400, 150], array_slice(getimagesize(Storage::disk('public')->path($logo)), 0, 2));
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

        $this->get('/especialistas')
            ->assertOk()
            ->assertSee('Diego Soto')
            ->assertSee('Personal trainer')
            ->assertSee('https://wa.me/56912345678?text=' . $saludo, false)
            ->assertSee(route('landing.especialista', 'diego-soto'), false);

        // El Instagram va en su perfil.
        $this->get('/especialistas/diego-soto')
            ->assertOk()
            ->assertSee('https://www.instagram.com/diego.fit/', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="' . route('landing.especialistas') . '"', false);
    }

    public function test_un_especialista_oculto_no_sale(): void
    {
        Especialista::create(['nombre' => 'Diego Soto', 'especialidad' => 'Personal trainer', 'activo' => false]);

        $this->get('/especialistas')
            ->assertOk()
            ->assertDontSee('Diego Soto');

        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('landing.especialistas'), false);
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

    // ---------- Embajadores ----------

    /**
     * El embajador sale en la PORTADA, no en la página de especialistas.
     *
     * Comparten tabla y pantalla del panel, así que lo que hay que vigilar es
     * que cada uno salga en su sitio. Y que un embajador no haga aparecer en el
     * menú la página de especialistas: esa página solo existe si hay alguno.
     */
    public function test_el_embajador_sale_en_la_portada_y_no_en_especialistas(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'tipo' => 'embajador',
            'nombre' => 'Valentina Pérez',
            'especialidad' => 'Powerlifting',
            'instagram' => '@vale.lifts',
            'activo' => true,
        ])->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('NUESTROS EMBAJADORES')
            ->assertSee('Valentina Pérez')
            ->assertSee('Powerlifting')
            ->assertSee('https://www.instagram.com/vale.lifts/', false)
            ->assertDontSee('href="' . route('landing.especialistas') . '"', false);

        $this->get('/especialistas')
            ->assertOk()
            ->assertDontSee('Valentina Pérez');
    }

    /** Y el especialista sigue en su página, sin colarse en la portada. */
    public function test_el_especialista_no_sale_entre_los_embajadores(): void
    {
        Especialista::create(['nombre' => 'Diego Soto', 'especialidad' => 'Personal trainer', 'activo' => true]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('NUESTROS EMBAJADORES');

        $this->get('/especialistas')
            ->assertOk()
            ->assertSee('Diego Soto');
    }

    /**
     * Editar sin mandar el tipo no convierte a un embajador en especialista.
     *
     * Si el tipo se pusiera por defecto al editar, bastaría un formulario que no
     * lo mandara para sacar a alguien de la portada sin que nadie lo pidiera.
     */
    public function test_editar_sin_el_tipo_respeta_el_que_tenia(): void
    {
        $embajador = Especialista::create([
            'tipo' => 'embajador',
            'nombre' => 'Valentina Pérez',
            'especialidad' => 'Powerlifting',
            'activo' => true,
        ]);

        $this->admin()->put("/panel/especialistas/{$embajador->uuid}", [
            'nombre' => 'Valentina Pérez',
            'especialidad' => 'Powerlifting y crossfit',
            'activo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame('embajador', $embajador->refresh()->tipo);
        $this->assertSame('Powerlifting y crossfit', $embajador->especialidad);
    }

    public function test_un_tipo_que_no_existe_se_rechaza(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'tipo' => 'influencer',
            'nombre' => 'Valentina Pérez',
            'especialidad' => 'Powerlifting',
        ])->assertSessionHasErrors('tipo');
    }
    /**
     * CADA UNO EN SU PANTALLA. Iban en la misma lista y al dueño no le
     * acomodaba: el especialista es un profesional al que se le escribe y el
     * embajador un socio que representa al gimnasio.
     */
    public function test_especialistas_y_embajadores_van_en_pantallas_separadas(): void
    {
        \App\Models\Especialista::create(['tipo' => 'especialista', 'nombre' => 'La nutricionista', 'especialidad' => 'Nutrición', 'activo' => true]);
        \App\Models\Especialista::create(['tipo' => 'embajador', 'nombre' => 'El powerlifter', 'especialidad' => 'Powerlifting', 'activo' => true]);

        $especialistas = $this->actingAs($this->administrador())->get('/panel/especialistas')
            ->assertOk()->viewData('page')['props'];
        $embajadores = $this->actingAs($this->administrador())->get('/panel/embajadores')
            ->assertOk()->viewData('page')['props'];

        $this->assertSame(['La nutricionista'], collect($especialistas['especialistas'])->pluck('nombre')->all());
        $this->assertSame(['El powerlifter'], collect($embajadores['especialistas'])->pluck('nombre')->all());
        $this->assertSame('embajador', $embajadores['tipo']);
    }
    /**
     * EL ORDEN SE LLEVA SOLO Y CADA LISTA POR SU LADO.
     *
     * Escrito a mano y contado entre las dos listas, quedaban embajadores 1, 2,
     * 4, 6 y especialistas 3, 5, 7.
     */
    public function test_cada_lista_se_numera_sola_y_por_separado(): void
    {
        foreach (['Ana' => 'especialista', 'Beto' => 'embajador', 'Carla' => 'especialista', 'Dani' => 'embajador'] as $nombre => $tipo) {
            $this->actingAs($this->administrador())->post('/panel/especialistas', [
                'tipo' => $tipo, 'nombre' => $nombre, 'especialidad' => 'Algo', 'activo' => true,
            ])->assertSessionHasNoErrors();
        }

        $puestos = fn (string $tipo) => \App\Models\Especialista::where('tipo', $tipo)->orderBy('orden')->pluck('orden', 'nombre')->all();

        $this->assertSame(['Ana' => 1, 'Carla' => 2], $puestos('especialista'));
        $this->assertSame(['Beto' => 1, 'Dani' => 2], $puestos('embajador'));

        // Con flechas, sin números.
        $carla = \App\Models\Especialista::where('nombre', 'Carla')->firstOrFail();
        $this->actingAs($this->administrador())->post("/panel/especialistas/{$carla->uuid}/mover", ['hacia' => 'arriba']);
        $this->assertSame(['Carla' => 1, 'Ana' => 2], $puestos('especialista'));
        // Mover en una lista no toca la otra.
        $this->assertSame(['Beto' => 1, 'Dani' => 2], $puestos('embajador'));
    }

    /** Eliminar se lleva la persona y su foto, y no deja hueco en la cuenta. */
    public function test_eliminar_a_una_persona_se_lleva_su_foto(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('especialistas/cara.jpg', 'foto');

        $primera = \App\Models\Especialista::create(['tipo' => 'embajador', 'nombre' => 'Uno', 'especialidad' => 'x', 'orden' => 1, 'foto' => 'especialistas/cara.jpg', 'activo' => true]);
        \App\Models\Especialista::create(['tipo' => 'embajador', 'nombre' => 'Dos', 'especialidad' => 'x', 'orden' => 2, 'activo' => true]);

        $this->actingAs($this->administrador())->delete("/panel/especialistas/{$primera->uuid}")->assertSessionHasNoErrors();

        $this->assertSame(['Dos' => 1], \App\Models\Especialista::pluck('orden', 'nombre')->all());
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('especialistas/cara.jpg');
    }

    // ---------- El perfil de cada especialista ----------

    /**
     * La presentación iba encima de la foto y la tapaba: ahora va en su
     * perfil, y en la lista queda lo justo para elegir.
     */
    public function test_la_presentacion_va_en_su_perfil_y_no_en_la_lista(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Camila Rojas',
            'especialidad' => 'Nutricionista',
            'descripcion' => "Nutricionista deportiva con diez años de experiencia.\n\nTrabajo con deportistas.",
            'temas' => ['nutrición deportiva', 'Nutrición deportiva', ' ', 'Composición corporal'],
            'modalidad' => 'ambas',
            'activo' => true,
        ])->assertSessionHasNoErrors();

        $camila = Especialista::firstWhere('nombre', 'Camila Rojas');
        $this->assertSame('camila-rojas', $camila->slug);
        $this->assertSame(['Nutrición deportiva', 'Composición corporal'], $camila->temas);

        $this->get('/especialistas')
            ->assertOk()
            ->assertSee('Camila Rojas')
            ->assertSee('Presencial y online')
            ->assertDontSee('diez años de experiencia');

        $this->get('/especialistas/camila-rojas')
            ->assertOk()
            ->assertSee('diez años de experiencia')
            ->assertSee('Trabajo con deportistas.')
            ->assertSee('Composición corporal')
            ->assertSee('Presencial y online');
    }

    public function test_el_perfil_de_un_oculto_o_de_un_embajador_no_existe(): void
    {
        Especialista::create(['nombre' => 'Oculto Uno', 'especialidad' => 'Kine', 'activo' => false]);
        Especialista::create(['nombre' => 'Diego Atleta', 'especialidad' => 'CrossFit', 'tipo' => 'embajador', 'activo' => true]);

        $this->get('/especialistas/oculto-uno')->assertNotFound();
        $this->get('/especialistas/diego-atleta')->assertNotFound();
        $this->get('/especialistas/no-existe')->assertNotFound();
    }

    public function test_dos_con_el_mismo_nombre_tienen_direcciones_distintas(): void
    {
        $uno = Especialista::create(['nombre' => 'José Pérez', 'especialidad' => 'Kine', 'activo' => true]);
        $dos = Especialista::create(['nombre' => 'Jose Perez', 'especialidad' => 'Nutri', 'activo' => true]);

        $this->assertSame('jose-perez', $uno->slug);
        $this->assertSame('jose-perez-2', $dos->slug);

        // Corregir el nombre cambia la dirección; guardar otra cosa, no.
        $uno->update(['especialidad' => 'Kinesiólogo']);
        $this->assertSame('jose-perez', $uno->fresh()->slug);
        $uno->update(['nombre' => 'José Pérez Soto']);
        $this->assertSame('jose-perez-soto', $uno->fresh()->slug);
    }

    public function test_el_perfil_sale_en_el_mapa_del_sitio(): void
    {
        Especialista::create(['nombre' => 'Camila Rojas', 'especialidad' => 'Nutricionista', 'activo' => true]);

        $this->get('/sitemap.xml')->assertOk()->assertSee(route('landing.especialista', 'camila-rojas'), false);
    }

    public function test_el_correo_sale_en_su_perfil(): void
    {
        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Camila Rojas', 'especialidad' => 'Nutricionista', 'email' => 'Camila@Correo.CL', 'activo' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame('camila@correo.cl', Especialista::first()->email);
        $this->get('/especialistas/camila-rojas')->assertOk()->assertSee('mailto:camila@correo.cl', false);

        $this->admin()->post('/panel/especialistas', [
            'nombre' => 'Otro', 'especialidad' => 'Kine', 'email' => 'no es correo', 'activo' => true,
        ])->assertSessionHasErrors('email');
    }

    /** Corregir el nombre no rompe la dirección que ya se compartió. */
    public function test_la_direccion_vieja_lleva_a_la_nueva(): void
    {
        $e = Especialista::create(['nombre' => 'Jose Perez', 'especialidad' => 'Kine', 'activo' => true]);
        $e->update(['nombre' => 'José Pérez Soto']);

        $this->get('/especialistas/jose-perez')->assertStatus(301)
            ->assertRedirect(route('landing.especialista', 'jose-perez-soto'));
        $this->get('/especialistas/jose-perez-soto')->assertOk();
    }
}
