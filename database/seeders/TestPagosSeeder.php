<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Models\Estado;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TestPagosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Genera clientes, inscripciones y pagos de prueba para testing del módulo de pagos
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener membresías disponibles
        $membresias = Membresia::all();
        if ($membresias->isEmpty()) {
            $this->command->warn('No hay membresías. Ejecuta: php artisan db:seed --class=MembresiasSeeder');
            return;
        }

        // Estado para pagos: buscamos uno de la categoría 'pago'
        $estado_pago = Estado::where('categoria', 'pago')->first();
        if (!$estado_pago) {
            $this->command->warn('No hay estado de pago. Ejecuta: php artisan db:seed --class=EstadoSeeder');
            return;
        }

        // Crear 10 clientes con inscripciones activas y con saldo pendiente
        for ($i = 1; $i <= 10; $i++) {
            $cliente = Cliente::create([
                'nombres' => $faker->firstName(),
                'apellido_paterno' => $faker->lastName(),
                'apellido_materno' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'celular' => $faker->phoneNumber(),
                'run_pasaporte' => $faker->numerify('##.###.###-#'),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'direccion' => $faker->address(),
                'contacto_emergencia' => $faker->firstName() . ' ' . $faker->lastName(),
                'telefono_emergencia' => $faker->phoneNumber(),
                'activo' => true,
            ]);

            // Crear 2-3 inscripciones por cliente
            $inscripciones_count = $faker->numberBetween(2, 3);
            for ($j = 0; $j < $inscripciones_count; $j++) {
                $membresia = $membresias->random();
                
                // Obtener el precio vigente de la membresía
                $precio = PrecioMembresia::where('id_membresia', $membresia->id)
                    ->where('activo', 1)
                    ->orderBy('fecha_vigencia_desde', 'desc')
                    ->first();
                
                if (!$precio) {
                    continue; // Saltar si no hay precio disponible
                }
                
                // Precio final con descuento aleatorio (0-20%)
                $precio_base = $precio->precio_normal;
                $descuento_porcentaje = $faker->numberBetween(0, 20);
                $descuento_aplicado = $precio_base * ($descuento_porcentaje / 100);
                $precio_final = $precio_base - $descuento_aplicado;

                $fecha_inicio = now()->subDays($faker->numberBetween(5, 30));
                $fecha_vencimiento = now()->addDays($faker->numberBetween(30, 365));

                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_precio_acordado' => $precio->id,
                    'id_estado' => 1, // ACTIVA
                    'fecha_inscripcion' => $fecha_inicio,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'precio_base' => $precio_base,
                    'descuento_aplicado' => $descuento_aplicado,
                    'precio_final' => $precio_final,
                    'dia_pago' => $faker->numberBetween(1, 28),
                    'pausada' => false,
                    'dias_pausa' => 0,
                    'pausas_realizadas' => 0,
                    'max_pausas_permitidas' => 2,
                ]);

                $this->command->line("✓ Inscripción #{$inscripcion->id} creada - Cliente: {$cliente->nombres} - Membresía: {$membresia->nombre}");

                // Crear 1-3 pagos parciales (para que quede saldo pendiente)
                $pagos_count = $faker->numberBetween(1, 3);
                $pagos_totales = 0;

                for ($k = 0; $k < $pagos_count; $k++) {
                    // Pagar entre 30-60% del total
                    $monto_pago = $precio_final * $faker->randomFloat(2, 0.3, 0.6);
                    $pagos_totales += $monto_pago;

                    // No pagar más de lo debido
                    if ($pagos_totales > $precio_final) {
                        $monto_pago = $precio_final - ($pagos_totales - $monto_pago);
                    }

                    if ($monto_pago > 0) {
                        $monto_pendiente = $precio_final - $pagos_totales;
                        if ($monto_pendiente < 0) {
                            $monto_pendiente = 0;
                        }
                        
                        Pago::create([
                            'id_inscripcion' => $inscripcion->id,
                            'id_metodo_pago_principal' => $faker->randomElement([1, 2, 3]),
                            'monto_abonado' => $monto_pago,
                            'monto_pendiente' => $monto_pendiente,
                            'cantidad_cuotas' => 1,
                            'numero_cuota' => 1,
                            'fecha_pago' => now()->subDays($faker->numberBetween(0, 5)),
                            'referencia_pago' => $faker->optional(0.5)->bothify('REF-########'),
                            'es_plan_cuotas' => false,
                            'observaciones' => 'Pago de prueba #' . ($k + 1) . ' para testing',
                            'id_estado' => $estado_pago->id,
                        ]);

                        $this->command->line("  → Pago parcial: ${monto_pago} (Saldo pendiente: ${monto_pendiente})");
                    }

                    if ($pagos_totales >= $precio_final) {
                        break;
                    }
                }

                // Asegurar que queda saldo pendiente (al menos $5000)
                if ($pagos_totales >= $precio_final) {
                    // Revertir el último pago para dejar saldo
                    $ultimo_pago = Pago::where('id_inscripcion', $inscripcion->id)
                        ->latest('id')
                        ->first();
                    
                    if ($ultimo_pago) {
                        $ultimo_pago->delete();
                        $this->command->line("  → Último pago eliminado para garantizar saldo pendiente");
                    }
                }
            }

            $this->command->line("");
        }

        $this->command->info("✓ Datos de prueba generados exitosamente!");
        $this->command->info("Total: " . Cliente::count() . " clientes, " . Inscripcion::count() . " inscripciones, " . Pago::count() . " pagos");
    }
}
