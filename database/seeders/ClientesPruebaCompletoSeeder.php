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

class ClientesPruebaCompletoSeeder extends Seeder
{
    /**
     * Seeder COMPLETO con 12+ clientes cubriendo TODOS los escenarios posibles
     * VERIFICADO: Usa los nombres de columnas REALES de la base de datos
     * 
     * ESCENARIOS:
     * 1. Membresía vence en 3 días (notif: membresia_por_vencer)
     * 2. Membresía vence mañana (notif: membresia_por_vencer urgente)
     * 3. Membresía vencida hace 5 días (notif: membresia_vencida)
     * 4. Membresía vencida hace 15 días (notif: membresia_vencida crítico)
     * 5. Pago pendiente total - 7 días (notif: pago_pendiente)
     * 6. Pago parcial 50% - 10 días (notif: pago_pendiente)
     * 7. Pago vencido - 20 días (notif: pago_pendiente crítico)
     * 8. Membresía pausada activa (no notificación)
     * 9. Membresía pausada vencida (notif especial)
     * 10. Menor de edad - activa (email a apoderado)
     * 11. Con convenio - activa normal (control)
     * 12. Suspendida por deuda (notif: activacion_inscripcion)
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
                $this->command->info('Membresías: ' . $membresias->count());
                $this->command->info('Métodos de pago: ' . $metodosPago->count());
                return;
            }

            $metodoPago = $metodosPago->first();
            $metodoPago2 = $metodosPago->count() > 1 ? $metodosPago->get(1) : $metodoPago;
            $convenio = $convenios->first();
            
            $this->command->info('👥 Creando 12 clientes de prueba - TODOS los escenarios');
            $this->command->newLine();

            $clientes = [];

            // ============================================================
            // 1. MEMBRESÍA POR VENCER EN 3 DÍAS
            // ============================================================
            $this->command->info('1️⃣  Vence en 3 días');
            $memb1 = $membresias->where('duracion_meses', 1)->first() ?? $membresias->first();
            $precio1 = PrecioMembresia::where('id_membresia', $memb1->id)->where('activo', true)->first();
            
            $c1 = Cliente::create([
                'run_pasaporte' => '12.345.678-9',
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'González',
                'email' => 'juan.perez@test.com',
                'celular' => '+56912345678',
                'fecha_nacimiento' => '1990-05-15',
                'direccion' => 'Av. Principal 123, Los Ángeles',
                'id_convenio' => $convenio?->id,
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c1->id,
                'id_membresia' => $memb1->id,
                'id_convenio' => $convenio?->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(27)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(27)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c1->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c1->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(27)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c1->run_pasaporte, $c1->nombres . ' ' . $c1->apellido_paterno, $c1->email, 'Vence en 3 días'];

            // ============================================================
            // 2. MEMBRESÍA VENCE MAÑANA
            // ============================================================
            $this->command->info('2️⃣  Vence mañana (URGENTE)');
            
            $c2 = Cliente::create([
                'run_pasaporte' => '11.222.333-4',
                'nombres' => 'Ana María',
                'apellido_paterno' => 'Torres',
                'apellido_materno' => 'Vega',
                'email' => 'ana.torres@test.com',
                'celular' => '+56911222333',
                'fecha_nacimiento' => '1988-03-10',
                'direccion' => 'Los Pinos 789',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c2->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(29)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(29)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDay()->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c2->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c2->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(29)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c2->run_pasaporte, $c2->nombres . ' ' . $c2->apellido_paterno, $c2->email, 'Vence mañana'];

            // ============================================================
            // 3. MEMBRESÍA VENCIDA HACE 5 DÍAS
            // ============================================================
            $this->command->info('3️⃣  Vencida hace 5 días');
            
            $c3 = Cliente::create([
                'run_pasaporte' => '23.456.789-0',
                'nombres' => 'María José',
                'apellido_paterno' => 'Silva',
                'apellido_materno' => 'Rojas',
                'email' => 'maria.silva@test.com',
                'celular' => '+56923456789',
                'fecha_nacimiento' => '1985-08-20',
                'direccion' => 'Calle Los Aromos 456',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c3->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(35)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(35)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 102,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c3->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c3->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(35)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c3->run_pasaporte, $c3->nombres . ' ' . $c3->apellido_paterno, $c3->email, 'Vencida 5 días'];

            // ============================================================
            // 4. MEMBRESÍA VENCIDA HACE 15 DÍAS (CRÍTICO)
            // ============================================================
            $this->command->info('4️⃣  Vencida hace 15 días (CRÍTICO)');
            
            $c4 = Cliente::create([
                'run_pasaporte' => '15.678.901-2',
                'nombres' => 'Carlos Alberto',
                'apellido_paterno' => 'Muñoz',
                'apellido_materno' => 'López',
                'email' => 'carlos.munoz@test.com',
                'celular' => '+56915678901',
                'fecha_nacimiento' => '1982-11-05',
                'direccion' => 'San Martín 234',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c4->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 102,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c4->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c4->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(45)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c4->run_pasaporte, $c4->nombres . ' ' . $c4->apellido_paterno, $c4->email, 'Vencida 15 días'];

            // ============================================================
            // 5. PAGO PENDIENTE TOTAL (7 DÍAS ATRÁS)
            // ============================================================
            $this->command->info('5️⃣  Pago pendiente total - 7 días');
            
            $c5 = Cliente::create([
                'run_pasaporte' => '34.567.890-1',
                'nombres' => 'Pedro Antonio',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Castro',
                'email' => 'pedro.ramirez@test.com',
                'celular' => '+56934567890',
                'fecha_nacimiento' => '1995-02-28',
                'direccion' => 'Las Heras 567',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c5->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(23)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c5->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c5->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => 0,
                'monto_pendiente' => $precio1->precio_normal,
                'id_estado' => 200,
                'fecha_pago' => Carbon::now()->subDays(7)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c5->run_pasaporte, $c5->nombres . ' ' . $c5->apellido_paterno, $c5->email, 'Pago pendiente 100%'];

            // ============================================================
            // 6. PAGO PARCIAL 50% (10 DÍAS ATRÁS)
            // ============================================================
            $this->command->info('6️⃣  Pago parcial 50% - 10 días');
            
            $c6 = Cliente::create([
                'run_pasaporte' => '18.901.234-5',
                'nombres' => 'Lorena Patricia',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Díaz',
                'email' => 'lorena.fernandez@test.com',
                'celular' => '+56918901234',
                'fecha_nacimiento' => '1992-07-14',
                'direccion' => 'O\'Higgins 890',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c6->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            $montoParcial = $precio1->precio_normal / 2;
            Pago::create([
                'id_cliente' => $c6->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c6->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $montoParcial,
                'monto_pendiente' => $montoParcial,
                'id_estado' => 202,
                'fecha_pago' => Carbon::now()->subDays(10)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c6->run_pasaporte, $c6->nombres . ' ' . $c6->apellido_paterno, $c6->email, 'Pago parcial 50%'];

            // ============================================================
            // 7. PAGO VENCIDO (20 DÍAS ATRÁS)
            // ============================================================
            $this->command->info('7️⃣  Pago vencido - 20 días (CRÍTICO)');
            
            $c7 = Cliente::create([
                'run_pasaporte' => '19.012.345-6',
                'nombres' => 'Diego Andrés',
                'apellido_paterno' => 'Vargas',
                'apellido_materno' => 'Soto',
                'email' => 'diego.vargas@test.com',
                'celular' => '+56919012345',
                'fecha_nacimiento' => '1987-09-22',
                'direccion' => 'Lautaro 456',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c7->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c7->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c7->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => 0,
                'monto_pendiente' => $precio1->precio_normal,
                'id_estado' => 203,
                'fecha_pago' => Carbon::now()->subDays(20)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c7->run_pasaporte, $c7->nombres . ' ' . $c7->apellido_paterno, $c7->email, 'Pago vencido'];

            // ============================================================
            // 8. MEMBRESÍA PAUSADA ACTIVA
            // ============================================================
            $this->command->info('8️⃣  Membresía pausada (dentro de vigencia)');
            
            $c8 = Cliente::create([
                'run_pasaporte' => '20.123.456-7',
                'nombres' => 'Claudia Beatriz',
                'apellido_paterno' => 'Morales',
                'apellido_materno' => 'Fuentes',
                'email' => 'claudia.morales@test.com',
                'celular' => '+56920123456',
                'fecha_nacimiento' => '1991-04-18',
                'direccion' => 'Colón 123',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c8->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 101,
                'pausada' => true,
            ]);
            
            Pago::create([
                'id_cliente' => $c8->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c8->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(15)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c8->run_pasaporte, $c8->nombres . ' ' . $c8->apellido_paterno, $c8->email, 'Pausada activa'];

            // ============================================================
            // 9. MEMBRESÍA PAUSADA VENCIDA
            // ============================================================
            $this->command->info('9️⃣  Membresía pausada vencida');
            
            $c9 = Cliente::create([
                'run_pasaporte' => '21.234.567-8',
                'nombres' => 'Rodrigo Ignacio',
                'apellido_paterno' => 'Carrasco',
                'apellido_materno' => 'Bravo',
                'email' => 'rodrigo.carrasco@test.com',
                'celular' => '+56921234567',
                'fecha_nacimiento' => '1989-12-30',
                'direccion' => 'Balmaceda 678',
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c9->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(40)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(40)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 101,
                'pausada' => true,
            ]);
            
            Pago::create([
                'id_cliente' => $c9->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c9->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(40)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c9->run_pasaporte, $c9->nombres . ' ' . $c9->apellido_paterno, $c9->email, 'Pausada vencida'];

            // ============================================================
            // 10. MENOR DE EDAD - ACTIVA (Email a apoderado)
            // ============================================================
            $this->command->info('🔟 Menor de edad con apoderado');
            
            $c10 = Cliente::create([
                'run_pasaporte' => '17.890.123-4',
                'nombres' => 'Sofía Ignacia',
                'apellido_paterno' => 'Castro',
                'apellido_materno' => 'Núñez',
                'email' => 'sofia.castro@test.com',
                'celular' => '+56917890123',
                'fecha_nacimiento' => Carbon::now()->subYears(16)->format('Y-m-d'),
                'direccion' => 'Las Rosas 234',
                'id_estado' => 400,
                'es_menor_edad' => true,
                'consentimiento_apoderado' => true,
                'apoderado_nombre' => 'Carmen Núñez Rojas',
                'apoderado_rut' => '14.567.890-3',
                'apoderado_email' => 'carmen.nunez@test.com',
                'apoderado_telefono' => '+56987654330',
                'apoderado_parentesco' => 'Madre',
                'activo' => true,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c10->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c10->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c10->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => $precio1->precio_normal,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(20)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c10->run_pasaporte, $c10->nombres . ' ' . $c10->apellido_paterno, $c10->apoderado_email, 'Menor de edad'];

            // ============================================================
            // 11. CON CONVENIO - ACTIVA NORMAL (CONTROL)
            // ============================================================
            $this->command->info('1️⃣1️⃣  Con convenio - control normal');
            
            $c11 = Cliente::create([
                'run_pasaporte' => '56.789.012-3',
                'nombres' => 'Roberto Carlos',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Pino',
                'email' => 'roberto.fernandez@test.com',
                'celular' => '+56956789012',
                'fecha_nacimiento' => '1986-06-25',
                'direccion' => 'Cochrane 345',
                'id_convenio' => $convenio?->id,
                'id_estado' => 400,
                'es_menor_edad' => false,
                'activo' => true,
            ]);
            
            $precioConvenio = $precio1->precio_convenio ?? ($precio1->precio_normal * 0.9);
            
            Inscripcion::create([
                'id_cliente' => $c11->id,
                'id_membresia' => $memb1->id,
                'id_convenio' => $convenio?->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'descuento_aplicado' => $precio1->precio_normal - $precioConvenio,
                'precio_final' => $precioConvenio,
                'id_estado' => 100,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c11->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c11->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precioConvenio,
                'monto_abonado' => $precioConvenio,
                'monto_pendiente' => 0,
                'id_estado' => 201,
                'fecha_pago' => Carbon::now()->subDays(10)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c11->run_pasaporte, $c11->nombres . ' ' . $c11->apellido_paterno, $c11->email, 'Con convenio'];

            // ============================================================
            // 12. SUSPENDIDA POR DEUDA
            // ============================================================
            $this->command->info('1️⃣2️⃣  Suspendida por deuda');
            
            $c12 = Cliente::create([
                'run_pasaporte' => '22.345.678-9',
                'nombres' => 'Patricia Andrea',
                'apellido_paterno' => 'Valenzuela',
                'apellido_materno' => 'Herrera',
                'email' => 'patricia.valenzuela@test.com',
                'celular' => '+56922345678',
                'fecha_nacimiento' => '1993-01-08',
                'direccion' => 'Freire 567',
                'id_estado' => 401,
                'es_menor_edad' => false,
                'activo' => false,
            ]);
            
            Inscripcion::create([
                'id_cliente' => $c12->id,
                'id_membresia' => $memb1->id,
                'id_precio_acordado' => $precio1->id,
                'fecha_inscripcion' => Carbon::now()->subDays(50)->format('Y-m-d'),
                'fecha_inicio' => Carbon::now()->subDays(50)->format('Y-m-d'),
                'fecha_vencimiento' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'precio_base' => $precio1->precio_normal,
                'precio_final' => $precio1->precio_normal,
                'id_estado' => 104,
                'pausada' => false,
            ]);
            
            Pago::create([
                'id_cliente' => $c12->id,
                'id_inscripcion' => Inscripcion::where('id_cliente', $c12->id)->first()->id,
                'id_metodo_pago' => $metodoPago->id,
                'monto_total' => $precio1->precio_normal,
                'monto_abonado' => 0,
                'monto_pendiente' => $precio1->precio_normal,
                'id_estado' => 203,
                'fecha_pago' => Carbon::now()->subDays(50)->format('Y-m-d'),
            ]);
            
            $clientes[] = [$c12->run_pasaporte, $c12->nombres . ' ' . $c12->apellido_paterno, $c12->email, 'Suspendida deuda'];

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ 12 clientes de prueba creados exitosamente');
            $this->command->newLine();
            
            $this->command->table(
                ['RUT', 'Nombre', 'Email', 'Escenario'],
                $clientes
            );

            $this->command->newLine();
            $this->command->info('🧪 Comandos de testing:');
            $this->command->info('  php artisan notificaciones:generar');
            $this->command->info('  php artisan notificaciones:enviar');
            $this->command->newLine();
            $this->command->warn('📊 Escenarios cubiertos:');
            $this->command->info('  ✓ Membresías por vencer (3 días, 1 día)');
            $this->command->info('  ✓ Membresías vencidas (5 días, 15 días)');
            $this->command->info('  ✓ Pagos pendientes (total, parcial 50%)');
            $this->command->info('  ✓ Pagos vencidos (20 días)');
            $this->command->info('  ✓ Membresías pausadas (activa, vencida)');
            $this->command->info('  ✓ Menor de edad con apoderado');
            $this->command->info('  ✓ Con convenio aplicado');
            $this->command->info('  ✓ Suspendida por deuda');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Línea: ' . $e->getLine());
            $this->command->error($e->getTraceAsString());
        }
    }
}
