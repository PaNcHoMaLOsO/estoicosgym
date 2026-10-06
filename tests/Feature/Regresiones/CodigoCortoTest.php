<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Support\CodigoCorto;
use Tests\CasoConCatalogos;

/**
 * El código corto de cada pago y membresía (#E5E42D49): se ve en su ficha y
 * con él se la encuentra en la lista.
 */
class CodigoCortoTest extends CasoConCatalogos
{
    private function datos(): array
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Tamara', 'apellido_paterno' => 'Fuenzalida']);
        $otro = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create(['id_cliente' => $socio->id, 'id_estado' => 100]);
        $pago = Pago::factory()->create(['id_cliente' => $socio->id, 'id_inscripcion' => $inscripcion->id]);
        $ajena = Inscripcion::factory()->create(['id_cliente' => $otro->id, 'id_estado' => 100]);

        return [$inscripcion, $pago, $ajena];
    }

    public function test_sale_de_los_primeros_ocho_del_uuid(): void
    {
        $this->assertSame('#E5E42D49', CodigoCorto::de('e5e42d49-c39c-45ff-99c1-6dc9336852ae'));
        $this->assertSame('e5e42d49', CodigoCorto::leer('#E5E42D49'));
        $this->assertSame('e5e42d49', CodigoCorto::leer(' e5e42d49 '));
        $this->assertNull(CodigoCorto::leer('Tamara'));
    }

    public function test_se_ve_en_las_fichas(): void
    {
        [$inscripcion, $pago] = $this->datos();
        $admin = $this->administrador();

        $this->assertSame(CodigoCorto::de($pago->uuid), $this->actingAs($admin)->get("/panel/pagos/{$pago->uuid}")
            ->assertOk()->viewData('page')['props']['pago']['codigo']);
        $this->assertSame(CodigoCorto::de($inscripcion->uuid), $this->actingAs($admin)->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertOk()->viewData('page')['props']['inscripcion']['codigo']);
    }

    public function test_se_encuentra_escribiendolo_en_la_lista(): void
    {
        [$inscripcion, $pago, $ajena] = $this->datos();
        $admin = $this->administrador();

        $pagos = $this->actingAs($admin)->get('/panel/pagos?buscar=' . urlencode(CodigoCorto::de($pago->uuid)))
            ->assertOk()->viewData('page')['props'];
        $this->assertStringContainsString($pago->uuid, json_encode($pagos));

        $lista = $this->actingAs($admin)->get('/panel/inscripciones?buscar=' . substr($inscripcion->uuid, 0, 8))
            ->assertOk()->viewData('page')['props'];
        $this->assertStringContainsString($inscripcion->uuid, json_encode($lista));
        // La lista, no «el último registrado» que la pantalla muestra aparte.
        $this->assertStringNotContainsString($ajena->uuid, json_encode($lista['inscripciones']));

        // El nombre del socio sigue funcionando.
        $this->actingAs($admin)->get('/panel/pagos?buscar=Fuenzalida')->assertOk()->assertSee($pago->uuid, false);
    }
}
