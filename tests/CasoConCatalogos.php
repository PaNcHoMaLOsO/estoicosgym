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

        /*
         * EN POSTGRESQL LA NUMERACIÓN NO VUELVE ATRÁS con la transacción de
         * cada prueba: el primer plan de la segunda prueba sería el 6, no el 1,
         * y el catálogo y las pruebas que dicen «el plan 4 es el mensual»
         * dejarían de calzar. Se reinicia antes de sembrar, que es lo que en
         * MySQL y SQLite pasa solo.
         */
        //
        // Al siguiente del mayor que haya, no a 1: hay tablas que las propias
        // migraciones dejan con filas —las plantillas de correo, los textos de
        // la web— y empezar en 1 chocaría con ellas.
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            $tablas = \Illuminate\Support\Facades\DB::select(
                "SELECT table_name FROM information_schema.columns WHERE table_schema = 'public' AND column_name = 'id' AND column_default LIKE 'nextval%'"
            );

            foreach ($tablas as $t) {
                \Illuminate\Support\Facades\DB::select(
                    "SELECT setval(pg_get_serial_sequence('\"{$t->table_name}\"', 'id'), COALESCE((SELECT MAX(id) FROM \"{$t->table_name}\"), 0) + 1, false)"
                );
            }
        }

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
