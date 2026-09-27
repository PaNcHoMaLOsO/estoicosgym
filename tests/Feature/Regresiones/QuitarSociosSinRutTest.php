<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * datos:quitar-sin-rut deja a la vista solo a los socios con RUT: las fichas
 * sin RUT de las planillas van a la papelera (recuperables), las que son de
 * alguien con otra ficha con RUT se juntan con esa, y no toca a quien tiene
 * movimiento hecho en el panel.
 */
class QuitarSociosSinRutTest extends CasoConCatalogos
{
    private function socio(array $datos): Cliente
    {
        return Cliente::factory()->create($datos + ['apellido_materno' => null, 'activo' => false])->fresh();
    }

    private function importada(Cliente $socio): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 102,
            'precio_base' => 0,
            'precio_final' => 0,
            'observaciones' => 'Importado de Planilla Socios.xlsx',
        ]);
    }

    public function test_sin_confirmar_solo_cuenta(): void
    {
        $this->importada($this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Sin', 'run_pasaporte' => null]));

        $this->artisan('datos:quitar-sin-rut')->assertSuccessful();

        $this->assertSame(1, Cliente::whereNull('run_pasaporte')->count());
    }

    public function test_deja_solo_a_los_socios_con_rut(): void
    {
        $conRut = $this->socio(['nombres' => 'Luis', 'apellido_paterno' => 'Con', 'run_pasaporte' => '16.394.445-6']);
        $sinRut = $this->socio(['nombres' => 'Ana', 'apellido_paterno' => 'Sin', 'run_pasaporte' => null]);
        $membresia = $this->importada($sinRut);

        // Con movimiento del panel: se deja.
        $conPago = $this->socio(['nombres' => 'Pedro', 'apellido_paterno' => 'Pago', 'run_pasaporte' => null]);
        $suya = $this->importada($conPago);
        Pago::create([
            'id_inscripcion' => $suya->id, 'id_cliente' => $conPago->id, 'monto_total' => 20000, 'monto_abonado' => 20000,
            'monto_pendiente' => 0, 'id_estado' => 201, 'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id, 'fecha_pago' => now(),
        ]);

        $this->artisan('datos:quitar-sin-rut --confirmar')->assertSuccessful();

        $this->assertNotSoftDeleted('clientes', ['id' => $conRut->id]);
        $this->assertSoftDeleted('clientes', ['id' => $sinRut->id]);
        $this->assertSoftDeleted('inscripciones', ['id' => $membresia->id]);
        $this->assertNotSoftDeleted('clientes', ['id' => $conPago->id]);
    }

    /** La ficha sin RUT de alguien que tiene otra con RUT se junta: su historial no se pierde. */
    public function test_el_duplicado_se_junta_con_su_ficha_con_rut(): void
    {
        $conRut = $this->socio(['nombres' => 'Estefania', 'apellido_paterno' => 'Molina', 'run_pasaporte' => '20.903.901-K']);
        $sinRut = $this->socio(['nombres' => 'Estefania', 'apellido_paterno' => 'Molina', 'run_pasaporte' => null]);
        $vieja = $this->importada($sinRut);

        $this->artisan('datos:quitar-sin-rut --confirmar')->assertSuccessful();

        $this->assertSame($conRut->id, $vieja->fresh()->id_cliente);
        $this->assertNull($vieja->fresh()->deleted_at);
        $this->assertSoftDeleted('clientes', ['id' => $sinRut->id]);
    }
}
