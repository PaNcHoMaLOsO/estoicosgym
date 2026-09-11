<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Facades\Cache;
use Tests\CasoConCatalogos;

/**
 * «Mi membresía»: la consulta pública desde la web.
 *
 * Con el RUT solo, cualquiera que lo supiera veía el nombre completo de la
 * persona, si era socia, su plan y cuándo pagó. Ahora se piden dos datos, se
 * responde lo justo y los frenos cuentan por conexión Y por RUT, para que
 * cambiar de IP no sirva para probar dígitos.
 */
class ConsultaMembresiaTest extends CasoConCatalogos
{
    private Cliente $socio;

    protected function setUp(): void
    {
        parent::setUp();

        // Los frenos viven en la caché, que dura entre pruebas.
        Cache::flush();

        $this->socio = Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Camila Andrea',
            'apellido_paterno' => 'Rojas',
            'run_pasaporte' => '12345678-5',
            'celular' => '912345678',
        ]);

        Inscripcion::factory()->create([
            'id_cliente' => $this->socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 40000,
            'descuento_aplicado' => 0,
            'precio_final' => 40000,
            'fecha_inicio' => now()->subDays(5),
            'fecha_vencimiento' => now()->addDays(25),
            'pausada' => false,
        ]);
    }

    private function consultar(array $datos, string $ip = '10.0.0.1')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/consultar-membresia', $datos);
    }

    // ---------- Lo que ve el socio ----------

    public function test_con_el_rut_y_los_4_digitos_ve_su_membresia(): void
    {
        $respuesta = $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Camila')
            ->assertJsonPath('data.membresia', 'Mensual')
            ->assertJsonPath('data.estado', 'Activa');

        // Lo justo: ni apellido, ni historial de pagos, ni el token que nadie usaba.
        $this->assertStringNotContainsString('Rojas', $respuesta->getContent());
        $this->assertArrayNotHasKey('pagos', $respuesta->json('data'));
        $this->assertNull($respuesta->json('token'));
    }

    public function test_si_debe_algo_se_le_dice_cuanto(): void
    {
        Pago::factory()->create([
            'id_cliente' => $this->socio->id,
            'id_inscripcion' => Inscripcion::where('id_cliente', $this->socio->id)->value('id'),
            'monto_total' => 40000,
            'monto_abonado' => 15000,
            'monto_pendiente' => 25000,
            'id_estado' => 202,
        ]);

        $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'])
            ->assertOk()
            ->assertJsonPath('data.saldo', 25000);
    }

    // ---------- Dos datos, y la misma respuesta al fallar ----------

    public function test_el_rut_solo_ya_no_basta(): void
    {
        $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5'])->assertStatus(422);
    }

    /**
     * Dígitos equivocados responden EXACTAMENTE igual que un RUT que no es socio.
     *
     * Si respondieran distinto, «¿esta persona es socia?» se contestaría sin
     * saber los dígitos.
     */
    public function test_digitos_equivocados_responden_igual_que_un_rut_que_no_es_socio(): void
    {
        $equivocados = $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '0000'], '10.0.0.2');
        $desconocido = $this->consultar(['tipo' => 'rut', 'rut' => '11.111.111-1', 'digitos' => '5678'], '10.0.0.3');

        $equivocados->assertStatus(404);
        $desconocido->assertStatus(404);
        $this->assertSame($desconocido->json(), $equivocados->json());
    }

    // ---------- Los frenos ----------

    /** Cinco fallos cierran ese RUT, aunque cada intento venga de otra conexión. */
    public function test_cinco_fallos_bloquean_ese_rut_aunque_cambie_la_ip(): void
    {
        foreach (range(1, 5) as $i) {
            $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '000' . $i], "10.1.0.{$i}")
                ->assertStatus(404);
        }

        // Ni con los dígitos buenos, desde una conexión nueva.
        $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'], '10.9.9.9')
            ->assertStatus(429)
            ->assertJsonPath('blocked', true);
    }

    public function test_mas_de_tres_consultas_seguidas_desde_la_misma_ip_se_frenan(): void
    {
        foreach (range(1, 3) as $i) {
            $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'], '10.2.0.1')
                ->assertOk();
        }

        $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'], '10.2.0.1')
            ->assertStatus(429);
    }

    public function test_la_trampa_para_bots_responde_como_si_no_existiera(): void
    {
        $bot = $this->consultar(['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678', 'website' => 'http://spam'], '10.3.0.1');
        $nadie = $this->consultar(['tipo' => 'rut', 'rut' => '11.111.111-1', 'digitos' => '1234'], '10.3.0.2');

        $bot->assertStatus(404);
        $this->assertSame($nadie->json(), $bot->json());
    }

    // ---------- Por celular, para quien no tiene RUT ----------

    /**
     * Por celular y nombre SÍ encuentra a la persona.
     *
     * No funcionaba nunca: al encontrarla, el registro de la consulta usaba
     * una variable que solo existe en la búsqueda por RUT y la respuesta era
     * un error 500. La página mostraba «Error de conexión».
     */
    public function test_por_celular_y_nombre_encuentra_al_socio(): void
    {
        $this->consultar(['tipo' => 'celular', 'celular' => '9 1234 5678', 'nombre' => 'Camila'], '10.4.0.1')
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Camila');
    }

    /** Antes bastaban dos letras que estuvieran dentro del nombre. */
    public function test_dos_letras_del_nombre_ya_no_abren_la_ficha(): void
    {
        $this->consultar(['tipo' => 'celular', 'celular' => '912345678', 'nombre' => 'am'], '10.5.0.1')
            ->assertStatus(404);
    }

    // ---------- El formulario de contacto ----------

    /** Cinco mensajes cada 10 minutos por conexión; el sexto se frena. */
    public function test_el_formulario_de_contacto_frena_al_sexto_envio(): void
    {
        foreach (range(1, 5) as $i) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.7.0.1'])->post('/contacto', []);
        }

        $sexto = $this->withServerVariables(['REMOTE_ADDR' => '10.7.0.1'])->post('/contacto', []);

        $sexto->assertRedirect();
        $this->assertTrue(
            session()->has('error') || session()->has('errors'),
            'El sexto envío no avisó de nada.'
        );
        $this->assertStringContainsStringIgnoringCase(
            'intent',
            (string) (session('error') ?? collect(session('errors')?->all() ?? [])->implode(' ')),
            'El aviso del freno no dice que se intente más tarde.'
        );
    }
}
