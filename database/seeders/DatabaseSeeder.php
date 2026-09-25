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

            //PlantillasProgymSeeder::class,   // 8. Plantillas de email (8 plantillas: bienvenida, vencimiento, pagos, etc.)
        ]);

        $this->command->info('✅ Seeders base completados');
        $this->command->newLine();

        // ===== USUARIOS DEL SISTEMA =====
        // SIN CLAVE CONOCIDA. Antes las dos cuentas nacían con «password», y
        // esa clave estaba escrita en los documentos del repositorio, que es
        // público: cualquier instalación hecha siguiendo la guía quedaba con un
        // administrador al que entraba cualquiera. Ahora se crean solo si no hay
        // ninguna cuenta, con una clave al azar que se muestra UNA vez.
        if (User::query()->exists()) {
            $this->command->info('👥 Ya hay usuarios: no se crea ninguno.');
        } else {
            $this->command->info('👥 Creando usuarios del sistema...');

            foreach ([
                ['Administrador', 'admin@progym.cl', 1],
                ['Recepcionista', 'recepcion@progym.cl', 2],
            ] as [$nombre, $correo, $rol]) {
                $clave = \Illuminate\Support\Str::password(16, symbols: false);

                User::factory()->create([
                    'name' => $nombre,
                    'email' => $correo,
                    'id_rol' => $rol,
                    'password' => $clave,
                ]);

                $this->command->warn("   {$correo}  clave: {$clave}  (anótala y cámbiala al entrar)");
            }
        }
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
