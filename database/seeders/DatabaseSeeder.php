<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden importante: primero tablas base, luego relaciones
        $this->call([
            RolesSeeder::class,
            EstadoSeeder::class,
            MetodoPagoSeeder::class,
            MotivoDescuentoSeeder::class,
            MembresiasSeeder::class,
            PreciosMembresiasSeeder::class,
            ConveniosSeeder::class,
        ]);

        // Crear usuario admin
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@estoicos.gym',
            'id_rol' => 1,
        ]);

        // Crear usuario recepcionista
        User::factory()->create([
            'name' => 'Recepcionista',
            'email' => 'recepcionista@estoicos.gym',
            'id_rol' => 2,
        ]);
    }
}
