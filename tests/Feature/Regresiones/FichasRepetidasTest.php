<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Support\FichasRepetidas;
use Tests\CasoConCatalogos;

/**
 * Las fichas repetidas de las planillas: la misma persona con una ficha sin
 * RUT y otra con él. Se detectan, se comparan y se juntan sin perder nada.
 */
class FichasRepetidasTest extends CasoConCatalogos
{
    private function socio(array $datos): Cliente
    {
        return Cliente::factory()->create($datos + ['activo' => false, 'apellido_materno' => null])->fresh();
    }

    private function conPago(Cliente $socio, int $monto): Inscripcion
    {
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 102,
            'precio_base' => $monto,
            'precio_final' => $monto,
        ]);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => $monto,
            'monto_abonado' => $monto,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => now(),
        ]);

        return $inscripcion;
    }

    public function test_detecta_la_misma_persona_con_y_sin_rut(): void
    {
        $sinRut = $this->socio(['nombres' => 'Juan', 'apellido_paterno' => 'Parra', 'run_pasaporte' => null]);
        $conRut = $this->socio(['nombres' => 'juan', 'apellido_paterno' => 'Párra', 'run_pasaporte' => '16.394.445-6']);
        $this->socio(['nombres' => 'Pedro', 'apellido_paterno' => 'Soto']);

        $grupos = FichasRepetidas::grupos();

        $this->assertCount(1, $grupos);
        $this->assertSame('Mismo nombre', $grupos[0]['porque']);
        $this->assertEqualsCanonicalizing([$sinRut->id, $conRut->id], array_column($grupos[0]['fichas'], 'id'));

        // En la ficha de cada uno se avisa de la otra.
        $this->assertSame($conRut->id, FichasRepetidas::de($sinRut)[0]['id']);
    }

    public function test_juntar_pasa_todo_a_la_que_queda_y_completa_lo_que_falta(): void
    {
        $sinRut = $this->socio(['nombres' => 'Juan', 'apellido_paterno' => 'Parra', 'run_pasaporte' => null, 'celular' => '+56 9 1234 5678']);
        $conRut = $this->socio(['nombres' => 'Juan', 'apellido_paterno' => 'Parra', 'run_pasaporte' => '16.394.445-6', 'celular' => null]);

        $vieja = $this->conPago($sinRut, 20000);
        $this->conPago($conRut, 25000);
        Fiado::create(['id_cliente' => $sinRut->id, 'concepto' => 'Barra', 'monto' => 2000, 'id_usuario' => $this->administrador()->id]);

        $this->actingAs($this->administrador())
            ->post('/panel/clientes/duplicados/juntar', ['queda' => $conRut->uuid, 'salen' => [$sinRut->uuid]])
            ->assertSessionHasNoErrors();

        $conRut->refresh();
        $this->assertSame(2, Inscripcion::where('id_cliente', $conRut->id)->count());
        $this->assertSame(2, Pago::where('id_cliente', $conRut->id)->count());
        $this->assertSame($conRut->id, $vieja->fresh()->id_cliente);
        $this->assertSame(1, Fiado::where('id_cliente', $conRut->id)->count());

        // Lo que le faltaba se completa; el RUT que tenía no se pisa.
        $this->assertSame('16.394.445-6', $conRut->run_pasaporte);
        $this->assertNotNull($conRut->celular);

        // La otra, en la papelera y sin nada colgando.
        $this->assertSoftDeleted('clientes', ['id' => $sinRut->id]);
        $this->assertSame(0, Pago::where('id_cliente', $sinRut->id)->count());
        $this->assertSame([], FichasRepetidas::grupos());
    }

    public function test_el_rut_pasa_si_la_que_queda_no_lo_tiene(): void
    {
        $sinRut = $this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Rojas', 'run_pasaporte' => null]);
        $conRut = $this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Rojas', 'run_pasaporte' => '20.903.901-K']);

        $this->actingAs($this->administrador())
            ->post('/panel/clientes/duplicados/juntar', ['queda' => $sinRut->uuid, 'salen' => [$conRut->uuid]])
            ->assertSessionHasNoErrors();

        $this->assertSame('20.903.901-K', $sinRut->fresh()->run_pasaporte);
    }

    public function test_personas_distintas_no_vuelven_a_salir(): void
    {
        $a = $this->socio(['nombres' => 'Camila', 'apellido_paterno' => 'Vidal', 'run_pasaporte' => '19.324.990-6']);
        $b = $this->socio(['nombres' => 'Camila', 'apellido_paterno' => 'Vidal', 'run_pasaporte' => '20.921.109-2']);

        $this->actingAs($this->recepcionista())
            ->post('/panel/clientes/duplicados/distintos', ['socios' => [$a->uuid, $b->uuid]])
            ->assertSessionHasNoErrors();

        $this->assertSame([], FichasRepetidas::grupos());
        $this->assertNotSoftDeleted('clientes', ['id' => $a->id]);
    }

    /** Recepción ve la lista y descarta; juntar lo hace el administrador. */
    public function test_recepcion_no_junta_fichas(): void
    {
        $a = $this->socio(['nombres' => 'Luis', 'apellido_paterno' => 'Bascur']);
        $b = $this->socio(['nombres' => 'Luis', 'apellido_paterno' => 'Bascur']);

        $this->actingAs($this->recepcionista())->get('/panel/clientes/duplicados')->assertOk();
        $this->actingAs($this->recepcionista())
            ->post('/panel/clientes/duplicados/juntar', ['queda' => $a->uuid, 'salen' => [$b->uuid]])
            ->assertForbidden();

        $this->assertNotSoftDeleted('clientes', ['id' => $b->id]);
    }

    /** Mismo nombre pero distinto segundo apellido: son dos personas. */
    public function test_distinto_apellido_materno_no_es_la_misma(): void
    {
        $this->socio(['nombres' => 'Javiera', 'apellido_paterno' => 'Muñoz', 'apellido_materno' => 'Castillo']);
        $this->socio(['nombres' => 'Javiera', 'apellido_paterno' => 'Muñoz', 'apellido_materno' => 'Pérez']);

        $this->assertSame([], FichasRepetidas::grupos());
    }

    /** Con dos RUT distintos se muestra aparte, como poco probable, y la ficha no avisa. */
    public function test_con_rut_distinto_es_poco_probable(): void
    {
        $a = $this->socio(['nombres' => 'Camila', 'apellido_paterno' => 'Vidal', 'run_pasaporte' => '19.324.990-6']);
        $this->socio(['nombres' => 'Camila', 'apellido_paterno' => 'Vidal', 'run_pasaporte' => '20.921.109-2']);

        $this->assertFalse(FichasRepetidas::grupos()[0]['probable']);
        $this->assertSame([], FichasRepetidas::de($a));
    }
}
