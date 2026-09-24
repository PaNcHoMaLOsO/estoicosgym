<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Support\SocioRepetido;
use Tests\CasoConCatalogos;

/**
 * Que no se registre dos veces a la misma persona.
 *
 * UN DUPLICADO SALE CARO Y NO SE VE: el historial, los pagos y el contrato
 * quedan repartidos en dos fichas, y nadie se entera hasta que algo no cuadra.
 * Se colaba por tres lados: el RUT escrito de otra forma, el socio de baja o
 * en la papelera que no aparecía en el aviso, y quien no trae RUT.
 */
class SocioRepetidoTest extends CasoConCatalogos
{
    private function alta(array $datos): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->administrador())->post('/panel/clientes', $datos + [
            'form_submit_token' => uniqid('t', true),
            'flujo_cliente' => 'solo_cliente',
            'tipo_documento' => 'rut',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Hernández',
            'celular' => '+56 9 8765 4321',
        ]);
    }

    /** El RUT se guarda siempre igual, lo escriba quien lo escriba. */
    public function test_el_rut_se_guarda_con_puntos_y_guion(): void
    {
        $this->alta(['run_pasaporte' => '214107082'])->assertSessionHasNoErrors();

        $this->assertSame('21.410.708-2', Cliente::firstOrFail()->run_pasaporte);
    }

    /**
     * «21410708-2» Y «21.410.708-2» SON EL MISMO. La regla de siempre comparaba
     * el texto tal cual y los daba por distintos: pasaba el duplicado.
     */
    public function test_el_mismo_rut_escrito_de_otra_forma_no_pasa(): void
    {
        Cliente::factory()->create(['run_pasaporte' => '21.410.708-2', 'activo' => true]);

        $this->alta(['run_pasaporte' => '21410708-2'])->assertSessionHasErrors('run_pasaporte');

        $this->assertSame(1, Cliente::count());
    }

    /**
     * EL DE LA PAPELERA TAMBIÉN CUENTA, y el mensaje dice qué hacer. Antes
     * chocaba con un error que no decía de quién era el RUT.
     */
    public function test_el_rut_de_uno_en_la_papelera_dice_que_se_restaure(): void
    {
        $viejo = Cliente::factory()->create(['run_pasaporte' => '21.410.708-2', 'nombres' => 'Juan', 'apellido_paterno' => 'Hernández']);
        $viejo->delete();

        $respuesta = $this->alta(['run_pasaporte' => '21.410.708-2']);

        $respuesta->assertSessionHasErrors('run_pasaporte');
        $this->assertStringContainsString('papelera', session('errors')->first('run_pasaporte'));
        $this->assertSame(0, Cliente::count());
    }

    /** La consulta del alta encuentra al de baja y dice que se le puede vender. */
    public function test_la_consulta_encuentra_al_de_baja(): void
    {
        $socio = Cliente::factory()->create(['run_pasaporte' => '21.410.708-2', 'activo' => false]);

        $respuesta = $this->actingAs($this->administrador())
            ->getJson('/panel/clientes/verificar?rut=214107082')
            ->assertOk();

        $this->assertSame((string) $socio->uuid, $respuesta->json('por_rut.uuid'));
        $this->assertSame('baja', $respuesta->json('por_rut.estado'));
    }

    /**
     * SIN RUT SOLO SE SOSPECHA: mismo celular o mismo nombre. Se avisa, no se
     * bloquea —dos hermanos pueden compartir teléfono—.
     */
    public function test_sin_rut_avisa_por_celular_y_por_nombre(): void
    {
        Cliente::factory()->create(['celular' => '+56 9 8765 4321', 'nombres' => 'Otra', 'apellido_paterno' => 'Persona']);
        Cliente::factory()->create(['celular' => '+56 9 1111 2222', 'nombres' => 'Juan Pablo', 'apellido_paterno' => 'Hernández']);

        $parecidos = $this->actingAs($this->administrador())
            ->getJson('/panel/clientes/verificar?celular=987654321&nombres=Juan&apellido=Hern%C3%A1ndez')
            ->assertOk()
            ->json('parecidos');

        $this->assertCount(2, $parecidos);
        $this->assertEqualsCanonicalizing(['mismo celular', 'mismo nombre'], array_column($parecidos, 'porque'));

        // Y el alta pasa igual: es una sospecha, no un bloqueo.
        $this->alta(['run_pasaporte' => ''])->assertSessionHasNoErrors();
    }

    /** Editar su propia ficha no choca consigo misma aunque el RUT venga sin puntos. */
    public function test_la_ficha_no_se_da_por_repetida_a_si_misma(): void
    {
        $this->assertSame('21.410.708-2', SocioRepetido::rut('21410708-2'));
        $this->assertSame('12.345.678-K', SocioRepetido::rut('12345678k'));

        $socio = Cliente::factory()->create(['run_pasaporte' => '21.410.708-2']);

        $this->assertNull(SocioRepetido::porRut('214107082', $socio->id));
    }
}
