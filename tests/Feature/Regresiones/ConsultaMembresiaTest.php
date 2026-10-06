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
 * persona, su plan, cuándo pagó y cuánto debía. Se pidieron además los 4
 * últimos dígitos del celular, pero casi ningún socio lo tiene guardado y la
 * consulta no le servía a nadie: desde el 6-oct-2026 vuelve a ser solo el RUT,
 * respondiendo lo mínimo —primer nombre, plan, días y si debe algo, SIN el
 * monto— y con los frenos por conexión, por RUT y del día.
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

    public function test_con_el_rut_ve_su_membresia(): void
    {
        $respuesta = $this->consultar(['rut' => '12.345.678-5'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Camila')
            ->assertJsonPath('data.membresia', 'Mensual')
            ->assertJsonPath('data.estado', 'Activa');

        // Lo justo: ni apellido, ni historial de pagos, ni el token que nadie usaba.
        $this->assertStringNotContainsString('Rojas', $respuesta->getContent());
        $this->assertArrayNotHasKey('pagos', $respuesta->json('data'));
        $this->assertNull($respuesta->json('token'));
    }

    /** Que debe, sí; cuánto, no: con el RUT solo, otro vería la deuda. */
    public function test_si_debe_algo_se_le_dice_pero_no_cuanto(): void
    {
        Pago::factory()->create([
            'id_cliente' => $this->socio->id,
            'id_inscripcion' => Inscripcion::where('id_cliente', $this->socio->id)->value('id'),
            'monto_total' => 40000,
            'monto_abonado' => 15000,
            'monto_pendiente' => 25000,
            'id_estado' => 202,
        ]);

        $this->consultar(['rut' => '12.345.678-5'])
            ->assertOk()
            ->assertJsonPath('data.debe', true)
            ->assertJsonMissingPath('data.saldo');
    }

    // ---------- Lo que no es socio ----------

    public function test_un_rut_que_no_es_socio_no_encuentra_nada(): void
    {
        $this->consultar(['rut' => '11.111.111-1'], '10.0.0.3')->assertStatus(404);
    }

    /** Lo que antes eran los 4 dígitos ya no se pide: si llegan, no estorban. */
    public function test_los_digitos_ya_no_hacen_falta(): void
    {
        $this->consultar(['rut' => '12.345.678-5', 'digitos' => '0000'], '10.0.0.4')->assertOk();
    }

    // ---------- Los frenos ----------

    /** Cinco fallos con un RUT lo cierran, aunque cada intento venga de otra conexión. */
    public function test_cinco_fallos_bloquean_ese_rut_aunque_cambie_la_ip(): void
    {
        foreach (range(1, 5) as $i) {
            $this->consultar(['rut' => '11.111.111-1'], "10.1.0.{$i}")->assertStatus(404);
        }

        $this->consultar(['rut' => '11.111.111-1'], '10.9.9.9')
            ->assertStatus(429)
            ->assertJsonPath('blocked', true);
    }

    public function test_mas_de_tres_consultas_seguidas_desde_la_misma_ip_se_frenan(): void
    {
        foreach (range(1, 3) as $i) {
            $this->consultar(['rut' => '12.345.678-5'], '10.2.0.1')
                ->assertOk();
        }

        $this->consultar(['rut' => '12.345.678-5'], '10.2.0.1')
            ->assertStatus(429);
    }

    public function test_la_trampa_para_bots_responde_como_si_no_existiera(): void
    {
        $bot = $this->consultar(['rut' => '12.345.678-5', 'website' => 'http://spam'], '10.3.0.1');
        $nadie = $this->consultar(['rut' => '11.111.111-1'], '10.3.0.2');

        $bot->assertStatus(404);
        $this->assertSame($nadie->json(), $bot->json());
    }

    /** La búsqueda por celular y nombre ya no existe: se busca por RUT. */
    public function test_por_celular_ya_no_se_busca(): void
    {
        $this->consultar(['tipo' => 'celular', 'celular' => '912345678', 'nombre' => 'Camila'], '10.4.0.1')
            ->assertStatus(422);
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

    // ---------- IPv6 y el tope del día ----------

    public function test_con_ipv6_se_cuenta_la_red_entera_y_no_cada_direccion(): void
    {
        // Tres consultas desde tres direcciones de la misma red: el tope por conexión.
        for ($i = 1; $i <= 3; $i++) {
            $this->consultar(['rut' => '11.111.111-2'], "2800:150:1:2::{$i}");
        }

        $this->consultar(['rut' => '12.345.678-5'], '2800:150:1:2::99')
            ->assertStatus(429);
    }

    public function test_pasados_los_fallos_del_dia_la_consulta_se_hace_en_el_meson(): void
    {
        \Illuminate\Support\Facades\RateLimiter::increment('consulta_fallidos:del-dia', 86400, 200);

        $this->consultar(['rut' => '12.345.678-5'], '10.7.0.1')
            ->assertStatus(429)
            ->assertJsonPath('message', 'Hoy la consulta en línea no está disponible. Pregunta en el mesón.');
    }
}
