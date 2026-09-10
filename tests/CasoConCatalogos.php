<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\EstadoSeeder;
use Database\Seeders\MembresiasSeeder;
use Database\Seeders\MetodoPagoSeeder;
use Database\Seeders\PreciosMembresiasSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base para las pruebas que necesitan la base poblada.
 *
 * Siembra SOLO los catalogos —roles, estados, membresias con sus precios y
 * formas de pago—, que es lo que el sistema da por sentado que existe: los
 * codigos de estado estan escritos en el codigo (100 activa, 201 pagado) y sin
 * esas filas no se puede registrar nada. Los socios y los pagos los crea cada
 * prueba con lo que necesite.
 */
abstract class CasoConCatalogos extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            EstadoSeeder::class,
            MetodoPagoSeeder::class,
            MembresiasSeeder::class,
            PreciosMembresiasSeeder::class,
        ]);
    }

    /** Usuario con rol de administrador, ya autenticado. */
    protected function administrador(): User
    {
        return User::factory()->create(['id_rol' => 1, 'activo' => true]);
    }

    /** Usuario con rol de recepcion, ya autenticado. */
    protected function recepcionista(): User
    {
        return User::factory()->create(['id_rol' => 2, 'activo' => true]);
    }
}
