<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TextoLegal;
use App\Models\TipoNotificacion;
use App\Services\ContratoDigitalService;
use App\Services\CorreoService;
use App\Support\TextosLegales;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\CasoConCatalogos;
use Tests\FirmaDePrueba;

/**
 * Lo que salió de la revisión de seguridad del contrato por correo
 * (30-sep-2026). Cada prueba es un agujero que estuvo abierto:
 *
 * - Se firmaba un texto distinto del leído si se corregía sin cambiar de versión.
 * - Sin RUT en la ficha, cualquiera con el enlace firmaba con otro nombre.
 * - Se aceptaba una firma en blanco, o de 8000 × 8000 píxeles.
 * - Un nombre con «https://…» salía como enlace dentro del contrato.
 * - En la ficha se anotaba cualquier versión y se borraba una firma por correo.
 * - Notificaciones guardaba el contrato entero, y el enlace si iba en el asunto.
 * - Los enlaces salían con la dirección que traía la petición.
 */
class SeguridadDelContratoTest extends CasoConCatalogos
{
    use FirmaDePrueba;

    /** @var list<array{para:string, asunto:string, html:string}> */
    private array $enviados = [];

    protected function setUp(): void
    {
        parent::setUp();

        // El contrato por correo viene apagado de fábrica: estas pruebas lo usan.
        \App\Support\Ajustes::guardar(['tareas.contrato_por_correo' => '1']);

        Storage::fake('public');
        Storage::fake('local');

        $doble = Mockery::mock(CorreoService::class);
        $doble->shouldReceive('enviar')->andReturnUsing(function (string $para, string $asunto, string $html) {
            $this->enviados[] = compact('para', 'asunto', 'html');

            return 'smtp';
        });
        $this->app->instance(CorreoService::class, $doble);
    }

    private function socio(array $extra = []): Cliente
    {
        $cliente = Cliente::factory()->create($extra + [
            'activo' => true,
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Soto',
            'run_pasaporte' => '12.345.678-5',
            'email' => 'camila@correo.cl',
            'es_menor_edad' => false,
        ]);

        Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);

        return $cliente->fresh();
    }

    private function mandar(Cliente $socio): string
    {
        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('success');

        auth()->logout();

        preg_match('#/contrato/([A-Za-z0-9]{48})#', end($this->enviados)['html'], $enlace);
        $this->assertNotEmpty($enlace, 'El correo no trae el enlace para firmar.');

        return $enlace[1];
    }

    /** @return array<string,mixed> */
    private function datosDeFirma(array $cambios = []): array
    {
        return $cambios + [
            'nombre' => 'Camila Rojas Soto',
            'rut' => '12345678-5',
            'firma' => $this->pngDeFirma(),
            'acepto' => '1',
            'version_contrato' => TextosLegales::vigente('contrato')->version,
            'version_terminos' => TextosLegales::vigente('terminos')->version,
            'version_privacidad' => TextosLegales::vigente('privacidad')->version,
            'lectura' => $this->lecturaDeHoy(),
        ];
    }

    // ---- 1. Lo que firma es lo que leyó ----------------------------------

    public function test_la_pagina_lleva_la_huella_de_lo_que_se_lee(): void
    {
        $token = $this->mandar($this->socio());

        $this->get("/contrato/{$token}")
            ->assertOk()
            ->assertSee('name="lectura" value="' . $this->lecturaDeHoy() . '"', false);
    }

    /** Corregir una versión sin firmas no cambia el número: igual se rechaza. */
    public function test_si_corrigen_el_texto_sin_cambiar_de_version_no_se_firma_el_nuevo_a_ciegas(): void
    {
        $token = $this->mandar($this->socio());
        $leido = $this->datosDeFirma();

        [, $como] = TextosLegales::guardar('contrato', "# Contrato\n\nCláusula que no leyó.", $this->administrador()->id);
        $this->assertSame('misma', $como);

        $this->post("/contrato/{$token}", $leido)->assertSessionHasErrors('version');
        $this->assertSame('pendiente', Contrato::sole()->estado());

        // Con la página nueva, sí.
        $this->post("/contrato/{$token}", $this->datosDeFirma())->assertSessionHasNoErrors();
        $this->assertStringContainsString('Cláusula que no leyó', Contrato::sole()->contenido);
    }

    /** El precio va en el contrato: si cambia mientras lee, también. */
    public function test_si_cambia_el_precio_mientras_lee_no_se_firma(): void
    {
        $socio = $this->socio();
        $token = $this->mandar($socio);
        $leido = $this->datosDeFirma();

        Inscripcion::where('id_cliente', $socio->id)->update(['precio_final' => 55000]);

        $this->post("/contrato/{$token}", $leido)->assertSessionHasErrors('version');
        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_sin_la_huella_de_lectura_no_se_firma(): void
    {
        $token = $this->mandar($this->socio());
        $datos = $this->datosDeFirma();
        unset($datos['lectura']);

        $this->post("/contrato/{$token}", $datos)->assertSessionHasErrors('lectura');
        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    // ---- 2. Quién firma --------------------------------------------------

    public function test_sin_rut_en_la_ficha_no_se_manda(): void
    {
        $socio = $this->socio(['run_pasaporte' => null]);

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('error', fn ($error) => str_contains($error, 'Anota su RUT en la ficha'));

        $this->assertSame(0, Contrato::count());
        $this->assertSame([], $this->enviados);
    }

    public function test_la_ficha_dice_por_que_no_se_puede_mandar(): void
    {
        $socio = $this->socio(['run_pasaporte' => null]);

        $firma = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props']['cliente']['firma_digital'];

        $this->assertStringContainsString('Anota su RUT en la ficha', $firma['no_se_puede']);
    }

    public function test_un_enlace_de_antes_sin_rut_en_la_ficha_no_firma(): void
    {
        $socio = $this->socio();
        $token = $this->mandar($socio);
        // Le borran el RUT después de mandarlo.
        $socio->update(['run_pasaporte' => null]);

        $this->post("/contrato/{$token}", $this->datosDeFirma(['nombre' => 'Fulano Impostor', 'rut' => '11.111.111-1']))
            ->assertSessionHasErrors('rut');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_un_menor_sin_rut_del_apoderado_no_se_manda(): void
    {
        $socio = $this->socio([
            'es_menor_edad' => true,
            'email' => null,
            'apoderado_nombre' => 'Pedro Rojas',
            'apoderado_rut' => null,
            'apoderado_email' => 'pedro@correo.cl',
        ]);

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('error', fn ($error) => str_contains($error, 'RUT de su apoderado'));

        $this->assertSame(0, Contrato::count());
    }

    /** Si el correo del apoderado es el del menor, el menor se autorizaría solo. */
    public function test_un_menor_con_el_correo_del_apoderado_igual_al_suyo_no_se_manda(): void
    {
        $socio = $this->socio([
            'es_menor_edad' => true,
            'email' => 'menor@correo.cl',
            'apoderado_nombre' => 'Pedro Rojas',
            'apoderado_rut' => '9.876.543-2',
            'apoderado_email' => 'MENOR@correo.cl ',
        ]);

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('error', fn ($error) => str_contains($error, 'mismo del socio'));

        $this->assertSame(0, Contrato::count());
    }

    // ---- 4. La firma ------------------------------------------------------

    public function test_una_firma_en_blanco_no_se_acepta(): void
    {
        if (! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('Sin GD no se revisa si la firma está en blanco.');
        }

        $token = $this->mandar($this->socio());

        $this->post("/contrato/{$token}", $this->datosDeFirma(['firma' => $this->pngDeFirma(300, 100, conTrazo: false)]))
            ->assertSessionHasErrors('firma');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_una_firma_desmedida_no_se_acepta(): void
    {
        $token = $this->mandar($this->socio());

        foreach ([[2001, 100], [300, 1001], [2500, 1200]] as [$ancho, $alto]) {
            $this->post("/contrato/{$token}", $this->datosDeFirma(['firma' => $this->pngDeFirma($ancho, $alto)]))
                ->assertSessionHasErrors('firma');
        }

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_una_firma_del_tamano_del_recuadro_se_acepta(): void
    {
        $token = $this->mandar($this->socio());

        $this->post("/contrato/{$token}", $this->datosDeFirma(['firma' => $this->pngDeFirma(1536, 352)]))
            ->assertSessionHasNoErrors();

        $this->assertSame('firmado', Contrato::sole()->estado());
    }

    // ---- 5. Los datos del socio no se vuelven enlaces ---------------------

    public function test_un_nombre_con_una_direccion_no_sale_como_enlace(): void
    {
        $html = TextosLegales::html('Socio: {socio}. Correo: {email_socio}. Web: {sitio}/privacidad', [
            'socio' => 'https://pagina-falsa.cl www.otra-falsa.cl',
            'email_socio' => 'camila@correo.cl',
            'sitio' => 'https://progym.cl',
        ]);

        $this->assertStringNotContainsString('pagina-falsa.cl"', $html);
        $this->assertStringNotContainsString('otra-falsa.cl"', $html);
        $this->assertStringNotContainsString('mailto:camila', $html);
        // Se lee igual.
        $this->assertStringContainsString('https://pagina-falsa.cl www.otra-falsa.cl', $html);
        // Y el enlace del gimnasio sigue siendo enlace.
        $this->assertStringContainsString('<a href="https://progym.cl/privacidad">', $html);
    }

    public function test_los_datos_del_socio_se_siguen_leyendo_tal_cual(): void
    {
        $html = TextosLegales::html('{rut_socio} {precio} {fecha}', [
            'rut_socio' => '12.345.678-5',
            'precio' => '$40.000',
            'fecha' => '30/09/2026',
        ]);

        $this->assertStringContainsString('12.345.678-5 $40.000 30/09/2026', $html);
    }

    // ---- 6. La constancia en papel, desde la ficha -------------------------

    public function test_en_la_ficha_no_se_anota_una_version_que_no_existe(): void
    {
        $socio = $this->socio();

        $this->actingAs($this->recepcionista())
            ->post("/panel/clientes/{$socio->uuid}/contrato", [
                'contrato_version' => '999',
                'contrato_firmado_en' => today()->toDateString(),
            ])
            ->assertSessionHasErrors('contrato_version');

        $this->assertNull($socio->fresh()->contrato_version);

        $vigente = (string) TextosLegales::vigente('contrato')->version;
        $this->actingAs($this->recepcionista())
            ->post("/panel/clientes/{$socio->uuid}/contrato", [
                'contrato_version' => $vigente,
                'contrato_firmado_en' => today()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($vigente, $socio->fresh()->contrato_version);
    }

    /** La versión que ya tenía anotada, aunque sea de antes del sistema, se respeta. */
    public function test_la_version_que_ya_tenia_anotada_se_puede_volver_a_guardar(): void
    {
        $socio = $this->socio(['contrato_version' => '2024-A', 'contrato_firmado_en' => '2024-03-01']);

        $this->actingAs($this->recepcionista())
            ->post("/panel/clientes/{$socio->uuid}/contrato", [
                'contrato_version' => '2024-A',
                'contrato_firmado_en' => '2024-03-01',
                'consentimiento_imagen' => true,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_desde_la_ficha_no_se_borra_una_firma_por_correo(): void
    {
        $socio = $this->socio();
        $token = $this->mandar($socio);
        $this->post("/contrato/{$token}", $this->datosDeFirma())->assertSessionHasNoErrors();
        $version = $socio->fresh()->contrato_version;

        $this->actingAs($this->recepcionista())
            ->post("/panel/clientes/{$socio->uuid}/contrato", [
                'contrato_version' => '',
                'contrato_firmado_en' => '',
            ])
            ->assertSessionHasErrors('contrato_firmado_en');

        $this->assertSame($version, $socio->fresh()->contrato_version);
        $this->assertNotNull($socio->fresh()->contrato_firmado_en);
    }

    // ---- 7. Lo que queda en Notificaciones --------------------------------

    public function test_el_enlace_tampoco_queda_en_el_asunto(): void
    {
        TipoNotificacion::where('codigo', ContratoDigitalService::PLANTILLA_ENVIO)
            ->update(['asunto_email' => 'Tu contrato: {enlace_contrato}']);

        $token = $this->mandar($this->socio());

        // Al socio le llega con el enlace…
        $this->assertStringContainsString($token, $this->enviados[0]['asunto']);

        // …y en el registro no queda.
        $registro = Notificacion::latest('id')->first();
        $this->assertStringNotContainsString($token, $registro->asunto);
        $this->assertStringContainsString(ContratoDigitalService::ENLACE_OCULTO, $registro->asunto);
    }

    public function test_la_copia_no_guarda_el_contrato_entero_en_notificaciones(): void
    {
        $token = $this->mandar($this->socio(['celular' => '987654321']));
        $this->post("/contrato/{$token}", $this->datosDeFirma())->assertSessionHasNoErrors();

        // Al socio le llega el contrato completo…
        $copia = end($this->enviados)['html'];
        $this->assertStringContainsString('Anexo · Términos y condiciones', $copia);
        $this->assertStringContainsString(Contrato::sole()->huella, $copia);

        // …pero en Notificaciones queda solo el mensaje de la plantilla.
        $registro = Notificacion::latest('id')->first();
        $this->assertStringNotContainsString('Anexo · Términos y condiciones', $registro->contenido);
        $this->assertStringNotContainsString('987654321', $registro->contenido);
        $this->assertStringNotContainsString($token, $registro->contenido);
    }

    // ---- 8. Los enlaces con APP_URL ---------------------------------------

    public function test_los_enlaces_salen_con_app_url_y_no_con_la_direccion_de_la_peticion(): void
    {
        config(['app.url' => 'https://progym.test']);

        $token = $this->mandar($this->socio());
        $this->assertStringContainsString("https://progym.test/contrato/{$token}", $this->enviados[0]['html']);

        // Firma pidiendo la página con otra dirección.
        $this->post("http://pagina-falsa.test/contrato/{$token}", $this->datosDeFirma())
            ->assertSessionHasNoErrors();

        $contrato = Contrato::sole();
        $this->assertSame('firmado', $contrato->estado());
        $this->assertStringNotContainsString('pagina-falsa.test', $contrato->contenido);
        $this->assertStringContainsString('https://progym.test/privacidad', $contrato->contenido);

        $copia = end($this->enviados)['html'];
        $this->assertStringNotContainsString('pagina-falsa.test', $copia);
        $this->assertStringContainsString("https://progym.test/contrato/{$token}", $copia);
    }

    public function test_el_sitio_de_los_textos_legales_es_app_url(): void
    {
        config(['app.url' => 'https://progym.test/']);

        $this->assertSame('https://progym.test', TextosLegales::datosDelGimnasio()['sitio']);
    }
}
