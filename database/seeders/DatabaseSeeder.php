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
            RolesSeeder::class,              // 1. Roles de usuario
            EstadoSeeder::class,             // 2. Estados (100-199 membresías, 200-299 pagos, 600-699 notificaciones)
            MetodoPagoSeeder::class,         // 3. Métodos de pago (Efectivo, Débito, Crédito, etc.)
            MotivoDescuentoSeeder::class,    // 4. Motivos de descuento
            MembresiasSeeder::class,         // 5. Tipos de membresías (Anual, Semestral, Trimestral, Mensual, Diario)
            PreciosMembresiasSeeder::class,  // 6. Precios por membresía
            ConveniosSeeder::class,          // 7. Convenios con empresas/instituciones
            PlantillasProgymSeeder::class,   // 8. Plantillas de email (8 plantillas: bienvenida, vencimiento, pagos, etc.)
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

        // ===== NOTA: DATOS DE PRUEBA =====
        // Los clientes se crean manualmente mediante el sistema
        // Para pruebas específicas, usar seeders opcionales:
        //   - ClientesPruebaCompletoSeeder (12+ escenarios)
        //   - DatosRealistasSeeder (datos realistas chilenos)
        // Ejecutar: php artisan db:seed --class=NombreDelSeeder

        $this->command->newLine();
        $this->command->info('🎉 ¡Base de datos lista!');
    }
}
