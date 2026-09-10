<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use Tests\CasoConCatalogos;

/**
 * Edición de la ficha de un socio.
 *
 * Lo que más se rompe al editar es la validación de «único»: si el RUT y el
 * correo se comprueban sin excluir al propio socio, abrir su ficha y guardarla
 * sin tocar nada la rechaza por duplicada consigo misma.
 */
class EdicionDeFichaTest extends CasoConCatalogos
{
    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + [
            'activo' => true,
            'run_pasaporte' => '12.345.678-5',
            'email' => 'socio@example.org',
            'celular' => '+56 9 1234 5678',
        ]);
    }

    /** @return array<string,mixed> */
    private function ficha(Cliente $cliente, array $extra = []): array
    {
        return array_merge([
            'run_pasaporte' => $cliente->run_pasaporte,
            'nombres' => $cliente->nombres,
            'apellido_paterno' => $cliente->apellido_paterno,
            'apellido_materno' => $cliente->apellido_materno,
            'celular' => $cliente->celular,
            'email' => $cliente->email,
            'es_menor_edad' => false,
        ], $extra);
    }

    private function guardar(Cliente $cliente, array $datos)
    {
        return $this->actingAs($this->administrador())
            ->put("/panel/clientes/{$cliente->uuid}", $datos);
    }

    /**
     * EL BUG QUE ESTO EVITA.
     *
     * Con `unique:clientes,email` a secas, abrir una ficha y guardarla sin
     * tocar nada la rechazaba: su propio correo ya estaba en la tabla.
     */
    public function test_guardar_una_ficha_sin_cambiar_nada_no_la_rechaza(): void
    {
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio))
            ->assertSessionHasNoErrors();
    }

    /**
     * El celular se guarda en dígitos, sin el +56 ni los espacios: se escribe
     * de cinco maneras distintas y así dos fichas del mismo número se ven
     * iguales al compararlas.
     */
    public function test_se_corrige_un_telefono_mal_escrito(): void
    {
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, ['celular' => '+56 9 8765 4321']))
            ->assertSessionHasNoErrors();

        $this->assertSame('987654321', $socio->fresh()->celular);
    }

    /** El correo de OTRO socio sigue estando ocupado. */
    public function test_no_se_puede_poner_el_correo_de_otro_socio(): void
    {
        $otro = Cliente::factory()->create([
            'email' => 'ocupado@example.org',
            'run_pasaporte' => '11.111.111-1',
        ]);
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, ['email' => $otro->email]))
            ->assertSessionHasErrors('email');

        $this->assertSame('socio@example.org', $socio->fresh()->email);
    }

    public function test_no_se_puede_poner_el_rut_de_otro_socio(): void
    {
        $otro = Cliente::factory()->create([
            'run_pasaporte' => '11.111.111-1',
            'email' => 'otro@example.org',
        ]);
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, ['run_pasaporte' => $otro->run_pasaporte]))
            ->assertSessionHasErrors('run_pasaporte');
    }

    /** El nombre se guarda escrito como un nombre, igual que al crear. */
    public function test_al_editar_el_nombre_tambien_se_escribe_bien(): void
    {
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, ['apellido_paterno' => 'álvarez']));

        $this->assertSame('Álvarez', $socio->fresh()->apellido_paterno);
    }

    /** Marcarlo como menor exige los datos del apoderado. */
    public function test_marcar_como_menor_exige_apoderado(): void
    {
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, ['es_menor_edad' => true]))
            ->assertSessionHasErrors(['apoderado_nombre', 'apoderado_email']);

        $this->assertFalse((bool) $socio->fresh()->es_menor_edad);
    }

    public function test_se_puede_marcar_como_menor_con_su_apoderado(): void
    {
        $socio = $this->socio();

        $this->guardar($socio, $this->ficha($socio, [
            'es_menor_edad' => true,
            'consentimiento_apoderado' => true,
            'apoderado_nombre' => 'María Soto',
            'apoderado_rut' => '11.111.111-1',
            'apoderado_email' => 'maria@example.org',
            'apoderado_telefono' => '+56 9 1111 1111',
            'apoderado_parentesco' => 'Madre',
        ]))->assertSessionHasNoErrors();

        $socio = $socio->fresh();

        $this->assertTrue((bool) $socio->es_menor_edad);
        $this->assertSame('maria@example.org', $socio->apoderado_email);
    }

    /**
     * Al dejar de ser menor, los datos del apoderado SE BORRAN. Si se quedaran
     * escondidos y alguien volviera a marcar «es menor» por error, aparecerían
     * los de antes como si siguieran valiendo.
     */
    public function test_dejar_de_ser_menor_borra_los_datos_del_apoderado(): void
    {
        $socio = $this->socio([
            'es_menor_edad' => true,
            'apoderado_nombre' => 'María Soto',
            'apoderado_email' => 'maria@example.org',
            'apoderado_rut' => '11.111.111-1',
        ]);

        $this->guardar($socio, $this->ficha($socio, ['es_menor_edad' => false]))
            ->assertSessionHasNoErrors();

        $socio = $socio->fresh();

        $this->assertFalse((bool) $socio->es_menor_edad);
        $this->assertNull($socio->apoderado_nombre);
        $this->assertNull($socio->apoderado_email);
    }

    /** La pantalla de editar trae la ficha ya rellena. */
    public function test_la_pantalla_trae_la_ficha_rellena(): void
    {
        $socio = $this->socio();

        $respuesta = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}/editar");

        $respuesta->assertOk();

        $props = $respuesta->viewData('page')['props']['cliente'];

        $this->assertSame($socio->email, $props['email']);
        $this->assertSame($socio->run_pasaporte, $props['run_pasaporte']);
    }

    /** Editar la ficha NO toca la membresía ni los pagos. */
    public function test_editar_la_ficha_no_toca_lo_cobrado(): void
    {
        $socio = $this->socio();

        $inscripcion = \App\Models\Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_estado' => 100,
            'precio_final' => 40000,
        ]);

        $this->guardar($socio, $this->ficha($socio, ['nombres' => 'Otro Nombre']));

        $inscripcion->refresh();

        $this->assertSame(100, (int) $inscripcion->id_estado);
        $this->assertEquals(40000, $inscripcion->precio_final);
    }
}
