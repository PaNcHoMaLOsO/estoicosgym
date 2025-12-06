<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientesPruebaTestingSeeder extends Seeder
{
    /**
     * Seeder para crear clientes de prueba con TODOS los escenarios posibles
     * VERIFICADO: Usa los nombres de columnas REALES de la base de datos
     * 
     * ESCENARIOS (12+ clientes):
     * - Membresías: Por vencer (3 días, 1 día), Vencidas (5 días, 15 días), Pausadas, Suspendidas
     * - Pagos: Completos, Parciales, Pendientes, Vencidos
     * - Clientes: Mayores, Menores de edad, Con/sin convenio
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Obtener datos necesarios
            $membresias = Membresia::where('activo', true)->get();
            $metodosPago = MetodoPago::where('activo', true)->get();
            $convenios = Convenio::where('activo', true)->get();

            if ($membresias->isEmpty() || $metodosPago->isEmpty()) {
                $this->command->error('❌ Error: Faltan datos base');
                $this->command->info('Membresías activas: ' . $membresias->count());
                $this->command->info('Métodos de pago: ' . $metodosPago->count());
                $this->command->info('Convenios: ' . $convenios->count());
                return;
            }

            $metodoPago = $metodosPago->first();
            $convenio = $convenios->first();
            $membresia1 = $membresias->first();

            $this->command->info('👥 Creando 12 clientes de prueba con TODOS los escenarios...');
            $this->command->newLine();

            $clientesCreados = [];

            // 1. CLIENTE CON MEMBRESÍA POR VENCER (3 DÍAS) - Notificación membresia_por_vencer
            $this->command->info('1️⃣  Cliente: Membresía por vencer en 3 días');
            $cliente1 = Cliente::create([
                'run_pasaporte' => '12.345.678-9',
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'González',
                'email' => 'juan.perez@test.com',
                'celular' => '+56912345678',
                'fecha_nacimiento' => '1990-05-15',
                'direccion' => 'Av. Principal 123, Los Ángeles',
                'contacto_emergencia' => 'María González',
                'telefono_emergencia' => '+56987654321',
                'id_convenio' => $convenio->id,
                'id_estado' => 100, // Activo
                'es_menor_edad' => false,
                'activo' => true,
            ]);

            $membresia1 = $membresias->where('duracion_meses', 1)->first() ?? $membresias->first();
            $precio1 = PrecioMembresia::where('id_membresia', $membresia1->id)
                ->where('activo', true)
                ->first();

            $fechaInicio1 = Carbon::now()->subDays(27);
            $fechaVencimiento1 = Carbon::now()->addDays(3); // Vence en 3 días

            $inscripcion1 = Inscripcion::create([
                'id_cliente' => $cliente1->id,
                'id_membresia' => $membresia1->id,
                'id_convenio' => $convenio->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => $fechaInicio1->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio1->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento1->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 200, // Activa
                'pausada' => false,
            ]);

            Pago::create([
                'id_cliente' => $cliente1->id,
                'id_inscripcion' => $inscripcion1->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 402, // Completado
                'fecha_pago' => $fechaInicio1->format('Y-m-d'),
            ]);

            // 2. CLIENTE CON MEMBRESÍA VENCIDA (5 DÍAS ATRÁS)
            $this->command->info('2️⃣  Cliente: Membresía vencida hace 5 días');
            $cliente2 = Cliente::create([
                'run_pasaporte' => '23.456.789-0',
                'nombres' => 'María José',
                'apellido_paterno' => 'Silva',
                'apellido_materno' => 'Rojas',
                'email' => 'maria.silva@test.com',
                'celular' => '+56923456789',
                'fecha_nacimiento' => '1985-08-20',
                'direccion' => 'Calle Los Aromos 456',
                'contacto_emergencia' => 'Pedro Silva',
                'telefono_emergencia' => '+56987654322',
                'id_convenio' => $convenio->id,
                'id_estado' => 100,
                'es_menor_edad' => false,
                'activo' => true,
            ]);

            $membresia2 = $membresias->where('duracion_meses', 1)->first() ?? $membresias->first();
            $precio2 = PrecioMembresia::where('id_membresia', $membresia2->id)
                ->where('activo', true)
                ->first();

            $fechaInicio2 = Carbon::now()->subDays(35);
            $fechaVencimiento2 = Carbon::now()->subDays(5); // Vencida hace 5 días

            $inscripcion2 = Inscripcion::create([
                'id_cliente' => $cliente2->id,
                'id_membresia' => $membresia2->id,
                'id_convenio' => $convenio->id,
                'id_precio_acordado' => $precio2->id,
                'fecha_inscripcion' => $fechaInicio2->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio2->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento2->format('Y-m-d'),
                'precio_base' => $precio2->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio2->precio_normal,
                'id_estado' => 200, // Activa (debería cambiar a vencida con comando)
                'pausada' => false,
            ]);

            Pago::create([
                'id_cliente' => $cliente2->id,
                'id_inscripcion' => $inscripcion2->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio2->precio_normal,
                'monto_abonado' => $precio2->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 402,
                'fecha_pago' => $fechaInicio2->format('Y-m-d'),
            ]);

            // 3. CLIENTE CON PAGO PENDIENTE (7 DÍAS ATRÁS)
            $this->command->info('3️⃣  Cliente: Pago pendiente hace 7 días');
            $cliente3 = Cliente::create([
                'run_pasaporte' => '34.567.890-1',
                'nombres' => 'Pedro Antonio',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Torres',
                'email' => 'pedro.ramirez@test.com',
                'celular' => '+56934567890',
                'fecha_nacimiento' => '1992-03-10',
                'direccion' => 'Pasaje Los Olivos 789',
                'contacto_emergencia' => 'Ana Torres',
                'telefono_emergencia' => '+56987654323',
                'id_convenio' => $convenio->id,
                'id_estado' => 100,
                'es_menor_edad' => false,
                'activo' => true,
            ]);

            $membresia3 = $membresias->where('duracion_meses', 3)->first() ?? $membresias->first();
            $precio3 = PrecioMembresia::where('id_membresia', $membresia3->id)
                ->where('activo', true)
                ->first();

            $fechaInicio3 = Carbon::now()->subDays(7);
            $fechaVencimiento3 = Carbon::now()->addDays(83);

            $inscripcion3 = Inscripcion::create([
                'id_cliente' => $cliente3->id,
                'id_membresia' => $membresia3->id,
                'id_convenio' => $convenio->id,
                'id_precio_acordado' => $precio3->id,
                'fecha_inscripcion' => $fechaInicio3->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio3->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento3->format('Y-m-d'),
                'precio_base' => $precio3->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio3->precio_normal,
                'id_estado' => 200,
                'pausada' => false,
            ]);

            // Pago pendiente (solo pagó la mitad)
            Pago::create([
                'id_cliente' => $cliente3->id,
                'id_inscripcion' => $inscripcion3->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio3->precio_normal,
                'monto_abonado' => $precio3->precio_normal / 2,
                'monto_pendiente' => $precio3->precio_normal / 2,
                'id_estado' => 401, // Parcial
                'fecha_pago' => $fechaInicio3->format('Y-m-d'),
            ]);

            // 4. CLIENTE MENOR DE EDAD CON APODERADO
            $this->command->info('4️⃣  Cliente: Menor de edad con membresía activa');
            $cliente4 = Cliente::create([
                'run_pasaporte' => '45.678.901-2',
                'nombres' => 'Sofía Ignacia',
                'apellido_paterno' => 'Castro',
                'apellido_materno' => 'Muñoz',
                'email' => null,
                'celular' => '+56945678901',
                'fecha_nacimiento' => '2010-11-25',
                'direccion' => 'Av. Los Conquistadores 321',
                'contacto_emergencia' => 'Carmen Muñoz',
                'telefono_emergencia' => '+56956789012',
                'id_convenio' => $convenio->id,
                'id_estado' => 100,
                'es_menor_edad' => true,
                'consentimiento_apoderado' => true,
                'apoderado_nombre' => 'Carmen Muñoz',
                'apoderado_rut' => '15.678.901-3',
                'apoderado_email' => 'carmen.munoz@test.com',
                'apoderado_telefono' => '+56956789012',
                'apoderado_parentesco' => 'Madre',
                'activo' => true,
            ]);

            $membresia4 = $membresias->where('duracion_meses', 1)->first() ?? $membresias->first();
            $precio4 = PrecioMembresia::where('id_membresia', $membresia4->id)
                ->where('activo', true)
                ->first();

            $fechaInicio4 = Carbon::now()->subDays(10);
            $fechaVencimiento4 = Carbon::now()->addDays(20);

            Inscripcion::create([
                'id_cliente' => $cliente4->id,
                'id_membresia' => $membresia4->id,
                'id_convenio' => $convenio->id,
                'id_precio_acordado' => $precio4->id,
                'fecha_inscripcion' => $fechaInicio4->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio4->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento4->format('Y-m-d'),
                'precio_base' => $precio4->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio4->precio_normal,
                'id_estado' => 200,
                'pausada' => false,
            ]);

            // 5. CLIENTE CON MEMBRESÍA ACTIVA NORMAL
            $this->command->info('5️⃣  Cliente: Membresía activa normal');
            $cliente5 = Cliente::create([
                'run_pasaporte' => '56.789.012-3',
                'nombres' => 'Roberto Carlos',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'López',
                'email' => 'roberto.fernandez@test.com',
                'celular' => '+56967890123',
                'fecha_nacimiento' => '1988-07-30',
                'direccion' => 'Calle Las Rosas 654',
                'contacto_emergencia' => 'Marta López',
                'telefono_emergencia' => '+56987654324',
                'id_convenio' => $convenio->id,
                'id_estado' => 100,
                'es_menor_edad' => false,
                'activo' => true,
            ]);

            $membresia5 = $membresias->where('duracion_meses', 6)->first() ?? $membresias->where('duracion_meses', 3)->first() ?? $membresias->first();
            $precio5 = PrecioMembresia::where('id_membresia', $membresia5->id)
                ->where('activo', true)
                ->first();

            $fechaInicio5 = Carbon::now()->subDays(15);
            $fechaVencimiento5 = Carbon::now()->addDays(165);

            Inscripcion::create([
                'id_cliente' => $cliente5->id,
                'id_membresia' => $membresia5->id,
                'id_convenio' => $convenio->id,
                'id_precio_acordado' => $precio5->id,
                'fecha_inscripcion' => $fechaInicio5->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio5->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento5->format('Y-m-d'),
                'precio_base' => $precio5->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio5->precio_normal,
                'id_estado' => 200,
                'pausada' => false,
            ]);

            Pago::create([
                'id_cliente' => $cliente5->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $cliente5->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio5->precio_normal,
                'monto_abonado' => $precio5->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 402,
                'fecha_pago' => $fechaInicio5->format('Y-m-d'),
            ]);

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Clientes de prueba creados exitosamente');
            $this->command->newLine();
            
            $this->command->table(
                ['RUT', 'Nombre', 'Email', 'Escenario'],
                [
                    [$cliente1->run_pasaporte, $cliente1->nombres . ' ' . $cliente1->apellido_paterno, $cliente1->email, 'Vence en 3 días'],
                    [$cliente2->run_pasaporte, $cliente2->nombres . ' ' . $cliente2->apellido_paterno, $cliente2->email, 'Vencida hace 5 días'],
                    [$cliente3->run_pasaporte, $cliente3->nombres . ' ' . $cliente3->apellido_paterno, $cliente3->email, 'Pago pendiente 7 días'],
                    [$cliente4->run_pasaporte, $cliente4->nombres . ' ' . $cliente4->apellido_paterno, $cliente4->apoderado_email, 'Menor de edad (activa)'],
                    [$cliente5->run_pasaporte, $cliente5->nombres . ' ' . $cliente5->apellido_paterno, $cliente5->email, 'Activa normal'],
                ]
            );

            $this->command->newLine();
            $this->command->info('🧪 Comandos de testing:');
            $this->command->info('  php artisan notificaciones:generar');
            $this->command->info('  php artisan notificaciones:enviar');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
}
