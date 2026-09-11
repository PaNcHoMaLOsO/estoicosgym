<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Support\Ajustes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Constancia del contrato y de los permisos del socio.
 *
 * EL CONTRATO SE FIRMA EN PAPEL: el sistema solo anota que se firmó, qué día y
 * QUÉ VERSIÓN. Lo que se vigila aquí es que esa constancia sirva para algo:
 * que la versión quede guardada —si no, el día que cambie el texto nadie sabrá
 * qué aceptó cada uno—, que los dos permisos sean independientes, y que
 * retirar el de la foto se lleve la foto de verdad.
 */
class ContratoDelSocioTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        // La caché de ajustes vive entre pruebas y arrastraría la anterior.
        Ajustes::olvidar();
    }

    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true]);
    }

    private function anotar(Cliente $socio, array $datos)
    {
        return $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/contrato", $datos);
    }

    private function conFoto(Cliente $socio): Cliente
    {
        $this->actingAs($this->administrador())->post(
            "/panel/clientes/{$socio->uuid}/foto",
            ['foto_perfil' => UploadedFile::fake()->image('camila.jpg')]
        );

        return $socio->refresh();
    }

    // ---------- La constancia ----------

    public function test_se_anota_la_firma_con_su_fecha_y_su_version(): void
    {
        $socio = $this->socio();

        $this->anotar($socio, [
            'contrato_firmado_en' => '2026-09-01',
            'contrato_version' => '1',
            'consentimiento_imagen' => true,
            'consentimiento_difusion' => false,
        ])->assertSessionHasNoErrors();

        $socio->refresh();

        $this->assertSame('2026-09-01', $socio->contrato_firmado_en->format('Y-m-d'));
        $this->assertSame('1', $socio->contrato_version);
        $this->assertTrue($socio->consentimiento_imagen);
        $this->assertFalse($socio->consentimiento_difusion);
    }

    /**
     * Anotar la fecha sin decir la versión toma la que se firma HOY.
     *
     * Es lo que acaba de pasar en el mesón: escribirla a mano sería una ocasión
     * más de teclear mal un número que después nadie va a poder verificar.
     */
    public function test_sin_version_toma_la_vigente(): void
    {
        Ajustes::guardar(['reglas.version_contrato' => '3']);
        Ajustes::olvidar();

        $socio = $this->socio();

        $this->anotar($socio, ['contrato_firmado_en' => '2026-09-01']);

        $this->assertSame('3', $socio->refresh()->contrato_version);
    }

    /** Una firma con fecha futura es un dedazo, no un contrato. */
    public function test_una_firma_con_fecha_futura_se_rechaza(): void
    {
        $socio = $this->socio();

        $this->anotar($socio, ['contrato_firmado_en' => now()->addDay()->format('Y-m-d')])
            ->assertSessionHasErrors('contrato_firmado_en');

        $this->assertNull($socio->refresh()->contrato_firmado_en);
    }

    // ---------- Los dos permisos son independientes ----------

    /**
     * «Lo ve el mesón» y «sale en Instagram» son finalidades distintas.
     *
     * Si una casilla arrastrara a la otra, el consentimiento de difusión sería
     * inválido: nadie lo habría dado. Tienen que poder ir por separado.
     */
    public function test_los_dos_permisos_no_se_arrastran(): void
    {
        $socio = $this->socio();

        $this->anotar($socio, [
            'consentimiento_imagen' => true,
            'consentimiento_difusion' => false,
        ]);

        $socio->refresh();
        $this->assertTrue($socio->consentimiento_imagen);
        $this->assertFalse($socio->consentimiento_difusion);

        // Y al revés: puede salir en redes sin querer foto en la ficha.
        $this->anotar($socio, [
            'consentimiento_imagen' => false,
            'consentimiento_difusion' => true,
        ]);

        $socio->refresh();
        $this->assertFalse($socio->consentimiento_imagen);
        $this->assertTrue($socio->consentimiento_difusion);
    }

    // ---------- Retirar el permiso ----------

    /**
     * EL QUE IMPORTA: retirar el permiso se lleva la foto.
     *
     * El contrato le dice al socio que puede retirarlo cuando quiera. Si al
     * desmarcar la casilla la foto siguiera en el disco, esa frase sería
     * mentira y quedaría guardada la cara de alguien que ya dijo que no.
     */
    public function test_retirar_el_permiso_borra_la_foto(): void
    {
        $socio = $this->socio();

        $this->anotar($socio, ['consentimiento_imagen' => true]);
        $socio = $this->conFoto($socio);

        $ruta = $socio->foto_perfil;
        $this->assertNotNull($ruta);

        $this->anotar($socio, ['consentimiento_imagen' => false]);

        $this->assertNull($socio->refresh()->foto_perfil);
        Storage::disk('public')->assertMissing($ruta);
    }

    /** Y guardar sin tocar el permiso no se lleva nada por delante. */
    public function test_guardar_sin_tocar_el_permiso_no_borra_la_foto(): void
    {
        $socio = $this->socio();

        $this->anotar($socio, ['consentimiento_imagen' => true]);
        $socio = $this->conFoto($socio);
        $ruta = $socio->foto_perfil;

        $this->anotar($socio, [
            'consentimiento_imagen' => true,
            'consentimiento_difusion' => true,
        ]);

        $this->assertSame($ruta, $socio->refresh()->foto_perfil);
        Storage::disk('public')->assertExists($ruta);
    }

    // ---------- El alta ----------

    public function test_el_alta_guarda_la_firma_y_los_permisos(): void
    {
        Ajustes::guardar(['reglas.version_contrato' => '2']);
        Ajustes::olvidar();

        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'solo_cliente',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '912345678',
            'email' => 'camila.rojas@example.com',
            'contrato_firmado_en' => '2026-09-05',
            'consentimiento_imagen' => true,
            'consentimiento_difusion' => false,
        ])->assertSessionHasNoErrors();

        $socio = Cliente::where('email', 'camila.rojas@example.com')->firstOrFail();

        $this->assertSame('2026-09-05', $socio->contrato_firmado_en->format('Y-m-d'));
        $this->assertSame('2', $socio->contrato_version);
        $this->assertTrue($socio->consentimiento_imagen);
        $this->assertFalse($socio->consentimiento_difusion);
    }

    /** Sin firma no se inventa una versión: queda constancia de que no consta. */
    public function test_un_alta_sin_firma_no_inventa_version(): void
    {
        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'solo_cliente',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Soto',
            'celular' => '912345679',
            'email' => 'pedro.soto@example.com',
        ])->assertSessionHasNoErrors();

        $socio = Cliente::where('email', 'pedro.soto@example.com')->firstOrFail();

        $this->assertNull($socio->contrato_firmado_en);
        $this->assertNull($socio->contrato_version);
        $this->assertFalse($socio->consentimiento_imagen);
    }

    // ---------- La pantalla ----------

    public function test_la_ficha_dice_si_firmo_una_version_vieja(): void
    {
        Ajustes::guardar(['reglas.version_contrato' => '1']);
        Ajustes::olvidar();

        $socio = $this->socio();
        $this->anotar($socio, ['contrato_firmado_en' => '2026-01-10']);

        // El gimnasio cambia el contrato.
        Ajustes::guardar(['reglas.version_contrato' => '2']);
        Ajustes::olvidar();

        $contrato = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props']['cliente']['contrato'];

        $this->assertSame('1', $contrato['version']);
        $this->assertSame('2', $contrato['version_vigente']);
    }

    /** Recepción anota contratos: es parte de inscribir a alguien. */
    public function test_recepcion_puede_anotar_el_contrato(): void
    {
        $socio = $this->socio();

        $this->actingAs($this->recepcionista())
            ->post("/panel/clientes/{$socio->uuid}/contrato", [
                'contrato_firmado_en' => '2026-09-01',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($socio->refresh()->contrato_firmado_en);
    }
}
