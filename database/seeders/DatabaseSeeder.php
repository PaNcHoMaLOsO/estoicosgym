<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * ORDEN: roles → estados → configuraciones → datos relacionales
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders base del sistema...');
        $this->command->newLine();

        // ===== DATOS BASE DEL SISTEMA =====
        $this->call([
            RolesSeeder::class,              // 1. Primero (usuarios dependen)
            EstadoSeeder::class,             // 2. Estados (todo depende de estos)
            MetodoPagoSeeder::class,         // 3. Configuraciones base
            MotivoDescuentoSeeder::class,
            MembresiasSeeder::class,         // 4. Membresías y precios
            PreciosMembresiasSeeder::class,
            ConveniosSeeder::class,          // 5. Convenios
            PlantillasProgymSeeder::class,   // 6. Plantillas PROGYM (de test_emails/)
        ]);

        $this->command->info('✅ Seeders base completados');
        $this->command->newLine();

        // ===== USUARIOS DEL SISTEMA =====
        $this->command->info('👥 Creando usuarios del sistema...');

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@progym.cl',
            'id_rol' => 1,
        ]);

        User::factory()->create([
            'name' => 'Recepcionista',
            'email' => 'recepcion@progym.cl',
            'id_rol' => 2,
        ]);

        $this->command->info('✅ Usuarios creados');
        $this->command->newLine();

        // ===== DATOS DE PRUEBA (solo desarrollo) =====
        if (app()->environment('local', 'development')) {
            $this->command->warn('⚙️  Entorno de desarrollo detectado');
            
            // Descomentar cuando necesites datos de prueba:
            // $this->call(ClientesPruebaCompletoSeeder::class);  // 12+ escenarios completos
            // $this->call(DatosRealistasSeeder::class);           // Datos realistas chilenos
        }

        $this->command->newLine();
        $this->command->info('🎉 ¡Base de datos lista!');
    }
}
