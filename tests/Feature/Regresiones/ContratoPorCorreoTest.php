<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Notificacion;
use App\Models\TextoLegal;
use App\Models\TipoNotificacion;
use App\Services\ContratoDigitalService;
use App\Services\CorreoService;
use App\Support\TextosLegales;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\CasoConCatalogos;

/**
 * El contrato que se firma por correo.
 *
 * Lo que se vigila: que le llegue un enlace que nadie más tiene —tampoco el
 * registro de correos—; que lo firmado quede tal cual, con su huella; que no
 * se pueda firmar sin aceptar, sin firma, con otro RUT, con el enlace vencido
 * o reemplazado; y que si es menor firme su apoderado.
 */
class ContratoPorCorreoTest extends CasoConCatalogos
{
    /** @var list<array{para:string, asunto:string, html:string}> */
    private array $enviados = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->fingirCorreo();
    }

    /** El correo «sale» sin tocar la red, y queda a mano lo que se mandó. */
    private function fingirCorreo(?\Throwable $falla = null): void
    {
        $doble = Mockery::mock(CorreoService::class);

        if ($falla) {
            $doble->shouldReceive('enviar')->andThrow($falla);
        } else {
            $doble->shouldReceive('enviar')->andReturnUsing(function (string $para, string $asunto, string $html) {
                $this->enviados[] = compact('para', 'asunto', 'html');

                return 'smtp';
            });
        }

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

    /** Lo manda desde la ficha y devuelve el enlace que le llegó. */
    private function mandar(Cliente $socio): string
    {
        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('success');

        preg_match('#/contrato/([A-Za-z0-9]{48})#', end($this->enviados)['html'], $enlace);
        $this->assertNotEmpty($enlace, 'El correo no trae el enlace para firmar.');

        return $enlace[1];
    }

    /** Una firma: un PNG blanco armado a mano, para no depender de la extensión GD. */
    private function firma(int $ancho = 300, int $alto = 100): string
    {
        $fila = "\0" . str_repeat("\xff\xff\xff", $ancho);
        $trozo = fn (string $tipo, string $datos) => pack('N', strlen($datos)) . $tipo . $datos . pack('N', crc32($tipo . $datos));

        $png = "\x89PNG\r\n\x1a\n"
            . $trozo('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0))
            . $trozo('IDAT', gzcompress(str_repeat($fila, $alto)))
            . $trozo('IEND', '');

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /** @return array<string,mixed> */
    private function datosDeFirma(array $cambios = [], array $sin = []): array
    {
        $datos = $cambios + [
            'nombre' => 'Camila Rojas Soto',
            'rut' => '12345678-5',
            'firma' => $this->firma(),
            'acepto_contrato' => '1',
            'acepto_terminos' => '1',
            'leido_privacidad' => '1',
            'consentimiento_imagen' => '1',
            'version_contrato' => TextosLegales::vigente('contrato')->version,
            'version_terminos' => TextosLegales::vigente('terminos')->version,
            'version_privacidad' => TextosLegales::vigente('privacidad')->version,
        ];

        return array_diff_key($datos, array_flip($sin));
    }

    public function test_le_llega_un_enlace_que_no_queda_guardado_en_ninguna_parte(): void
    {
        $token = $this->mandar($this->socio());

        $this->assertCount(1, $this->enviados);
        $this->assertSame('camila@correo.cl', $this->enviados[0]['para']);

        $contrato = Contrato::sole();
        $this->assertSame('pendiente', $contrato->estado());
        $this->assertSame(hash('sha256', $token), $contrato->token_hash);

        // Ni en el registro de correos: con el enlace, cualquiera que lo mire
        // podría firmar por el socio.
        $registro = Notificacion::latest('id')->first();
        $this->assertStringNotContainsString($token, $registro->contenido);
        $this->assertStringContainsString(ContratoDigitalService::ENLACE_OCULTO, $registro->contenido);
    }

    public function test_el_enlace_muestra_el_contrato_con_sus_datos_y_su_plan(): void
    {
        $token = $this->mandar($this->socio());

        $this->get("/contrato/{$token}")
            ->assertOk()
            ->assertSee('Camila Rojas Soto')
            ->assertSee(Membresia::find(4)->nombre)
            ->assertSee('$40.000')
            ->assertSee('noindex', false);
    }

    /** EL QUE IMPORTA: queda el documento tal como se firmó, con su huella, y la ficha al día. */
    public function test_al_firmar_queda_el_documento_con_su_huella_y_la_ficha_al_dia(): void
    {
        $socio = $this->socio();
        $token = $this->mandar($socio);

        $this->post("/contrato/{$token}", $this->datosDeFirma())
            ->assertSessionHasNoErrors()
            ->assertRedirect("/contrato/{$token}");

        $contrato = Contrato::sole();
        $this->assertSame('firmado', $contrato->estado());
        $this->assertTrue($contrato->integro());
        $this->assertStringContainsString('Camila Rojas Soto', $contrato->contenido);
        $this->assertStringContainsString('data:image/png;base64,', $contrato->contenido);
        $this->assertSame(TextosLegales::vigente('contrato')->version, $contrato->version_contrato);

        $socio->refresh();
        $this->assertSame((string) $contrato->version_contrato, $socio->contrato_version);
        $this->assertSame(today()->format('Y-m-d'), $socio->contrato_firmado_en->format('Y-m-d'));
        $this->assertTrue($socio->consentimiento_imagen);
        $this->assertFalse($socio->consentimiento_difusion);

        // Y le vuelve su copia, completa.
        $this->assertCount(2, $this->enviados);
        $this->assertSame('camila@correo.cl', $this->enviados[1]['para']);
        $this->assertStringContainsString('Contrato de prestación de servicios deportivos', $this->enviados[1]['html']);

        // Lo guardado ya no cambia sin que se note: la huella lo delata.
        $contrato->update(['contenido' => str_replace('Camila', 'Otra', $contrato->contenido)]);
        $this->assertFalse($contrato->fresh()->integro());
    }

    public function test_una_vez_firmado_el_enlace_muestra_la_copia_y_no_deja_firmar_otra_vez(): void
    {
        $token = $this->mandar($this->socio());
        $this->post("/contrato/{$token}", $this->datosDeFirma());
        $huella = Contrato::sole()->huella;

        $this->get("/contrato/{$token}")->assertOk()->assertSee('Firmado por Camila Rojas Soto');

        $this->post("/contrato/{$token}", $this->datosDeFirma(['nombre' => 'Otra Persona']))
            ->assertRedirect("/contrato/{$token}");

        $this->assertSame($huella, Contrato::sole()->huella);
    }

    public function test_sin_aceptar_los_terminos_no_se_firma(): void
    {
        $token = $this->mandar($this->socio());

        $this->post("/contrato/{$token}", $this->datosDeFirma([], ['acepto_terminos']))
            ->assertSessionHasErrors('acepto_terminos');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_sin_firma_dibujada_no_se_firma(): void
    {
        $token = $this->mandar($this->socio());

        $this->post("/contrato/{$token}", $this->datosDeFirma(['firma' => 'data:image/png;base64,AAAA']))
            ->assertSessionHasErrors('firma');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_con_otro_rut_no_se_firma(): void
    {
        $token = $this->mandar($this->socio());

        $this->post("/contrato/{$token}", $this->datosDeFirma(['rut' => '11.111.111-1']))
            ->assertSessionHasErrors('rut');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    /** Si el gimnasio cambió el texto mientras lo leía, firma el nuevo y no el viejo. */
    public function test_si_el_texto_cambio_mientras_lo_leia_no_se_firma_el_viejo(): void
    {
        $token = $this->mandar($this->socio());
        $leido = $this->datosDeFirma();

        TextoLegal::create([
            'tipo' => 'terminos',
            'version' => TextosLegales::vigente('terminos')->version + 1,
            'contenido' => 'Términos nuevos',
        ]);

        $this->post("/contrato/{$token}", $leido)->assertSessionHasErrors('version');

        $this->assertSame('pendiente', Contrato::sole()->estado());
    }

    public function test_el_enlace_vencido_no_sirve(): void
    {
        $token = $this->mandar($this->socio());

        $this->travel(30)->days();

        $this->get("/contrato/{$token}")->assertStatus(410);
        $this->post("/contrato/{$token}", $this->datosDeFirma())->assertRedirect("/contrato/{$token}");
        $this->assertNull(Contrato::sole()->firmado_en);
    }

    /** Un solo enlace vivo por socio: mandar otro deja sin efecto el anterior. */
    public function test_mandar_otro_anula_el_anterior(): void
    {
        $socio = $this->socio();
        $primero = $this->mandar($socio);
        $segundo = $this->mandar($socio);

        $this->get("/contrato/{$primero}")->assertNotFound();
        $this->get("/contrato/{$segundo}")->assertOk();
    }

    public function test_un_enlace_inventado_no_abre_nada(): void
    {
        $this->get('/contrato/' . str_repeat('a', 48))->assertNotFound();
        $this->get('/contrato/corto')->assertNotFound();
    }

    /** Si es menor, el correo va a su apoderado y firma él. */
    public function test_un_menor_lo_firma_su_apoderado(): void
    {
        $socio = $this->socio([
            'es_menor_edad' => true,
            'email' => null,
            'apoderado_nombre' => 'Pedro Rojas',
            'apoderado_rut' => '9.876.543-2',
            'apoderado_email' => 'pedro@correo.cl',
            'apoderado_parentesco' => 'Padre',
            'consentimiento_apoderado' => false,
        ]);

        $token = $this->mandar($socio);
        $this->assertSame('pedro@correo.cl', $this->enviados[0]['para']);

        $this->get("/contrato/{$token}")->assertOk()->assertSee('como apoderado de');

        $this->post("/contrato/{$token}", $this->datosDeFirma(['nombre' => 'Pedro Rojas', 'rut' => '9876543-2']))
            ->assertSessionHasNoErrors();

        $contrato = Contrato::sole();
        $this->assertSame('apoderado', $contrato->firmante_tipo);
        $this->assertSame('firmado', $contrato->estado());
        $this->assertTrue($socio->fresh()->consentimiento_apoderado);
    }

    public function test_sin_correo_no_se_manda(): void
    {
        $socio = $this->socio(['email' => null]);

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('error');

        $this->assertSame(0, Contrato::count());
        $this->assertSame([], $this->enviados);
    }

    /** Si el correo no sale, el enlace no queda vivo y se sabe por qué. */
    public function test_si_el_correo_no_sale_el_enlace_no_queda_vivo(): void
    {
        $this->fingirCorreo(new RuntimeException('El servidor de correo no responde'));
        $socio = $this->socio();

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato/enviar")
            ->assertSessionHas('error');

        $this->assertSame('fallido', Contrato::sole()->estado());
        $this->assertSame(Notificacion::ESTADO_FALLIDO, (int) Notificacion::latest('id')->first()->id_estado);
    }

    /** Desde Notificaciones saldría sin enlace: no se reenvía. */
    public function test_desde_notificaciones_no_se_reenvia_un_contrato_sin_enlace(): void
    {
        $this->mandar($this->socio());
        $registro = Notificacion::latest('id')->first();
        $registro->update(['id_estado' => Notificacion::ESTADO_FALLIDO]);

        $this->actingAs($this->administrador())
            ->post("/panel/notificaciones/{$registro->uuid}/reenviar")
            ->assertSessionHasErrors('envio');

        $this->assertCount(1, $this->enviados);
    }

    public function test_las_plantillas_del_contrato_no_se_ofrecen_para_mandar_a_mano(): void
    {
        $plantillas = $this->actingAs($this->administrador())
            ->get('/panel/notificaciones/enviar')
            ->viewData('page')['props']['plantillas'];

        $this->assertNotContains('Contrato para firmar', array_column($plantillas, 'nombre'));
    }

    public function test_la_vista_previa_de_la_plantilla_no_deja_variables_sueltas(): void
    {
        $this->socio();
        $plantilla = TipoNotificacion::where('codigo', ContratoDigitalService::PLANTILLA_ENVIO)->sole();

        $this->actingAs($this->administrador())
            ->getJson("/panel/notificaciones/plantillas/{$plantilla->id}/vista-previa")
            ->assertOk()
            ->assertJsonPath('pendientes', []);
    }

    public function test_el_panel_muestra_la_copia_firmada_con_su_constancia(): void
    {
        $token = $this->mandar($this->socio());
        $this->post("/contrato/{$token}", $this->datosDeFirma());

        $this->actingAs($this->recepcionista())
            ->get('/panel/contratos/' . Contrato::sole()->uuid)
            ->assertOk()
            ->assertSee('su huella cuadra');
    }

    /** Al dar de alta, con una casilla, le llega el contrato. */
    public function test_al_dar_de_alta_se_le_puede_mandar_el_contrato(): void
    {
        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'solo_cliente',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '912345678',
            'email' => 'camila.rojas@example.com',
            'enviar_contrato' => true,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('camila.rojas@example.com', $this->enviados[0]['para'] ?? null);
        $this->assertSame(1, Contrato::count());
    }
}
