<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientesTestSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs necesarios
        $metodoPagoEfectivo = DB::table('metodos_pago')->where('nombre', 'Efectivo')->first();
        if (!$metodoPagoEfectivo) {
            $idMetodoPago = DB::table('metodos_pago')->insertGetId([
                'nombre' => 'Efectivo',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $idMetodoPago = $metodoPagoEfectivo->id;
        }

        $membresiaMensual = DB::table('membresias')->where('nombre', 'Mensual')->first();
        $membresiaTrimestral = DB::table('membresias')->where('nombre', 'Trimestral')->first();
        $membresiaSemestral = DB::table('membresias')->where('nombre', 'Semestral')->first();
        $membresiaAnual = DB::table('membresias')->where('nombre', 'Anual')->first();

        $precioMensual = DB::table('precios_membresias')->where('id_membresia', $membresiaMensual->id)->where('activo', true)->first();
        $precioTrimestral = DB::table('precios_membresias')->where('id_membresia', $membresiaTrimestral->id)->where('activo', true)->first();
        $precioSemestral = DB::table('precios_membresias')->where('id_membresia', $membresiaSemestral->id)->where('activo', true)->first();
        $precioAnual = DB::table('precios_membresias')->where('id_membresia', $membresiaAnual->id)->where('activo', true)->first();

        $estadoActiva = 100;
        $estadoPausada = 101;
        $estadoVencida = 102;
        $estadoPagoPendiente = 200;
        $estadoPagado = 201;
        $estadoParcial = 202;

        // 1. CLIENTE NUEVO - Pago Completo
        $this->info('1. Creando cliente con pago completo...');
        $idCliente1 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '20111222-3',
            'nombres' => 'Carlos',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Pérez',
            'email' => 'test.nuevo@progym.test',
            'celular' => '+56912345001',
            'fecha_nacimiento' => '1998-05-15',
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idInscripcion1 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente1,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today(),
            'fecha_inicio' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today()->addMonth(),
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion1,
            'id_cliente' => $idCliente1,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 25000,
            'monto_abonado' => 25000,
            'monto_pendiente' => 0,
            'fecha_pago' => Carbon::today(),
            'tipo_pago' => 'completo',
            'id_estado' => $estadoPagado,
            'observaciones' => 'Pago completo - Cliente nuevo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. CLIENTE NUEVO - Pago Parcial
        $this->info('2. Creando cliente con pago parcial...');
        $idCliente2 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '19222333-4',
            'nombres' => 'María',
            'apellido_paterno' => 'Rodríguez',
            'apellido_materno' => 'Silva',
            'email' => 'test.parcial@progym.test',
            'celular' => '+56912345002',
            'fecha_nacimiento' => '1995-08-20',
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idInscripcion2 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente2,
            'id_membresia' => $membresiaTrimestral->id,
            'id_precio_acordado' => $precioTrimestral->id,
            'fecha_inscripcion' => Carbon::today(),
            'fecha_inicio' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today()->addMonths(3),
            'precio_base' => 65000,
            'descuento_aplicado' => 0,
            'precio_final' => 65000,
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion2,
            'id_cliente' => $idCliente2,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 65000,
            'monto_abonado' => 40000,
            'monto_pendiente' => 25000,
            'fecha_pago' => Carbon::today(),
            'tipo_pago' => 'parcial',
            'id_estado' => $estadoParcial,
            'observaciones' => 'Pago parcial - Primera cuota',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. CLIENTE NUEVO - Sin Pagos/Pendiente
        $this->info('3. Creando cliente con pago pendiente...');
        $idCliente3 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '18333444-5',
            'nombres' => 'José',
            'apellido_paterno' => 'Martínez',
            'apellido_materno' => 'López',
            'email' => 'test.pendiente@progym.test',
            'celular' => '+56912345003',
            'fecha_nacimiento' => '1992-03-10',
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idInscripcion3 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente3,
            'id_membresia' => $membresiaSemestral->id,
            'id_precio_acordado' => $precioSemestral->id,
            'fecha_inscripcion' => Carbon::today(),
            'fecha_inicio' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today()->addMonths(6),
            'precio_base' => 120000,
            'descuento_aplicado' => 0,
            'precio_final' => 120000,
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion3,
            'id_cliente' => $idCliente3,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 120000,
            'monto_abonado' => 0,
            'monto_pendiente' => 120000,
            'fecha_pago' => Carbon::today(),
            'tipo_pago' => 'pendiente',
            'id_estado' => $estadoPagoPendiente,
            'observaciones' => 'Pendiente de pago',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. CLIENTE NUEVO - Pago Mixto (múltiples cuotas)
        $this->info('4. Creando cliente con pago mixto...');
        $idCliente4 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '21444555-6',
            'nombres' => 'Ana',
            'apellido_paterno' => 'Fernández',
            'apellido_materno' => 'Castro',
            'email' => 'test.mixto@progym.test',
            'celular' => '+56912345004',
            'fecha_nacimiento' => '2000-11-25',
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idInscripcion4 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente4,
            'id_membresia' => $membresiaAnual->id,
            'id_precio_acordado' => $precioAnual->id,
            'fecha_inscripcion' => Carbon::today(),
            'fecha_inicio' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today()->addYear(),
            'precio_base' => 200000,
            'descuento_aplicado' => 0,
            'precio_final' => 200000,
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'id_inscripcion' => $idInscripcion4,
                'id_cliente' => $idCliente4,
                'id_metodo_pago' => $idMetodoPago,
                'monto_total' => 200000,
                'monto_abonado' => 50000,
                'monto_pendiente' => 150000,
                'fecha_pago' => Carbon::today()->subDays(15),
                'tipo_pago' => 'parcial',
                'numero_cuota' => 1,
                'cantidad_cuotas' => 4,
                'id_estado' => $estadoParcial,
                'observaciones' => 'Primer pago - cuota 1/4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'id_inscripcion' => $idInscripcion4,
                'id_cliente' => $idCliente4,
                'id_metodo_pago' => $idMetodoPago,
                'monto_total' => 200000,
                'monto_abonado' => 50000,
                'monto_pendiente' => 100000,
                'fecha_pago' => Carbon::today()->subDays(5),
                'tipo_pago' => 'parcial',
                'numero_cuota' => 2,
                'cantidad_cuotas' => 4,
                'id_estado' => $estadoParcial,
                'observaciones' => 'Segundo pago - cuota 2/4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. CLIENTE QUE COMPLETA SU PAGO (pago_completado) ⭐
        $this->info('5. Creando cliente que completa su pago...');
        $idCliente5 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '17555666-7',
            'nombres' => 'Daniela',
            'apellido_paterno' => 'Vega',
            'apellido_materno' => 'Moreno',
            'email' => 'test.completado@progym.test',
            'celular' => '+56912345005',
            'fecha_nacimiento' => '1997-03-12',
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idInscripcion5 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente5,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today()->subDays(10),
            'fecha_inicio' => Carbon::today()->subDays(10),
            'fecha_vencimiento' => Carbon::today()->addDays(20),
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'id_estado' => $estadoActiva,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Primer pago parcial hace 10 días
        DB::table('pagos')->insert([
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'id_inscripcion' => $idInscripcion5,
                'id_cliente' => $idCliente5,
                'id_metodo_pago' => $idMetodoPago,
                'monto_total' => 25000,
                'monto_abonado' => 15000,
                'monto_pendiente' => 10000,
                'fecha_pago' => Carbon::today()->subDays(10),
                'tipo_pago' => 'parcial',
                'id_estado' => $estadoParcial,
                'observaciones' => 'Pago parcial inicial',
                'created_at' => Carbon::today()->subDays(10),
                'updated_at' => Carbon::today()->subDays(10),
            ],
            [
                'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'id_inscripcion' => $idInscripcion5,
                'id_cliente' => $idCliente5,
                'id_metodo_pago' => $idMetodoPago,
                'monto_total' => 25000,
                'monto_abonado' => 10000,
                'monto_pendiente' => 0,
                'fecha_pago' => Carbon::today(),
                'tipo_pago' => 'completo',
                'id_estado' => $estadoPagado,
                'observaciones' => 'Pago final - completado hoy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 6. Cliente con membresía por vencer (5 días antes)
        $this->info('6. Creando cliente con membresía por vencer...');
        $idCliente6 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '18765432-1',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Silva',
            'apellido_materno' => 'Torres',
            'celular' => '+56987654326',
            'email' => 'test.porvencer@progym.test',
            'created_at' => Carbon::today()->subDays(25),
            'updated_at' => now(),
        ]);

        $idInscripcion6 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente6,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today()->subDays(25),
            'fecha_inicio' => Carbon::today()->subDays(25),
            'fecha_vencimiento' => Carbon::today()->addDays(5), // Vence en 5 días
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'id_estado' => $estadoActiva,
            'created_at' => Carbon::today()->subDays(25),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion6,
            'id_cliente' => $idCliente6,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 25000,
            'monto_abonado' => 25000,
            'monto_pendiente' => 0,
            'fecha_pago' => Carbon::today()->subDays(25),
            'tipo_pago' => 'completo',
            'id_estado' => $estadoPagado,
            'observaciones' => 'Pago completo - membresía por vencer',
            'created_at' => Carbon::today()->subDays(25),
            'updated_at' => now(),
        ]);

        // 7. Cliente con membresía vencida (5 días después)
        $this->info('7. Creando cliente con membresía vencida...');
        $idCliente7 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '17654321-9',
            'nombres' => 'Laura',
            'apellido_paterno' => 'Muñoz',
            'apellido_materno' => 'Soto',
            'celular' => '+56987654327',
            'email' => 'test.vencido@progym.test',
            'created_at' => Carbon::today()->subDays(35),
            'updated_at' => now(),
        ]);

        $idInscripcion7 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente7,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today()->subDays(35),
            'fecha_inicio' => Carbon::today()->subDays(35),
            'fecha_vencimiento' => Carbon::today()->subDays(5), // Vencida hace 5 días
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'id_estado' => $estadoVencida,
            'created_at' => Carbon::today()->subDays(35),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion7,
            'id_cliente' => $idCliente7,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 25000,
            'monto_abonado' => 25000,
            'monto_pendiente' => 0,
            'fecha_pago' => Carbon::today()->subDays(35),
            'tipo_pago' => 'completo',
            'id_estado' => $estadoPagado,
            'observaciones' => 'Pago completo - membresía ya vencida',
            'created_at' => Carbon::today()->subDays(35),
            'updated_at' => now(),
        ]);

        // 8. Cliente con deuda grande (pago pendiente recurrente)
        $this->info('8. Creando cliente con deuda pendiente...');
        $idCliente8 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '16543210-8',
            'nombres' => 'Roberto',
            'apellido_paterno' => 'Campos',
            'apellido_materno' => 'Vargas',
            'celular' => '+56987654328',
            'email' => 'test.deuda@progym.test',
            'created_at' => Carbon::today()->subDays(20),
            'updated_at' => now(),
        ]);

        $idInscripcion8 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente8,
            'id_membresia' => $membresiaTrimestral->id,
            'id_precio_acordado' => $precioTrimestral->id,
            'fecha_inscripcion' => Carbon::today()->subDays(20),
            'fecha_inicio' => Carbon::today()->subDays(20),
            'fecha_vencimiento' => Carbon::today()->addDays(70),
            'precio_base' => 65000,
            'descuento_aplicado' => 0,
            'precio_final' => 65000,
            'id_estado' => $estadoActiva,
            'created_at' => Carbon::today()->subDays(20),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion8,
            'id_cliente' => $idCliente8,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 65000,
            'monto_abonado' => 20000,
            'monto_pendiente' => 45000,
            'fecha_pago' => Carbon::today()->subDays(20),
            'tipo_pago' => 'parcial',
            'id_estado' => $estadoParcial,
            'observaciones' => 'Pago inicial pequeño - saldo grande pendiente',
            'created_at' => Carbon::today()->subDays(20),
            'updated_at' => now(),
        ]);

        // 9. Cliente con membresía pausada
        $this->info('9. Creando cliente con membresía pausada...');
        $idCliente9 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '15432109-7',
            'nombres' => 'Valentina',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Morales',
            'celular' => '+56987654329',
            'email' => 'test.pausado@progym.test',
            'created_at' => Carbon::today()->subDays(15),
            'updated_at' => now(),
        ]);

        $idInscripcion9 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente9,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today()->subDays(15),
            'fecha_inicio' => Carbon::today()->subDays(15),
            'fecha_vencimiento' => Carbon::today()->addDays(30), // Se extenderá por pausa
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'fecha_pausa_inicio' => Carbon::today(),
            'fecha_pausa_fin' => Carbon::today()->addDays(15),
            'id_estado' => $estadoPausada,
            'created_at' => Carbon::today()->subDays(15),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion9,
            'id_cliente' => $idCliente9,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 25000,
            'monto_abonado' => 25000,
            'monto_pendiente' => 0,
            'fecha_pago' => Carbon::today()->subDays(15),
            'tipo_pago' => 'completo',
            'id_estado' => $estadoPagado,
            'observaciones' => 'Pago completo - membresía pausada temporalmente',
            'created_at' => Carbon::today()->subDays(15),
            'updated_at' => now(),
        ]);

        // 10. Cliente recién reactivado (estaba pausado, se reactivó hoy)
        $this->info('10. Creando cliente recién reactivado...');
        $idCliente10 = DB::table('clientes')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'run_pasaporte' => '14321098-6',
            'nombres' => 'Sebastián',
            'apellido_paterno' => 'Parra',
            'apellido_materno' => 'Lagos',
            'celular' => '+56987654330',
            'email' => 'test.reactivado@progym.test',
            'created_at' => Carbon::today()->subDays(40),
            'updated_at' => now(),
        ]);

        $idInscripcion10 = DB::table('inscripciones')->insertGetId([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_cliente' => $idCliente10,
            'id_membresia' => $membresiaMensual->id,
            'id_precio_acordado' => $precioMensual->id,
            'fecha_inscripcion' => Carbon::today()->subDays(40),
            'fecha_inicio' => Carbon::today()->subDays(40),
            'fecha_vencimiento' => Carbon::today()->addDays(30), // Extendida por pausa de 15 días
            'precio_base' => 25000,
            'descuento_aplicado' => 0,
            'precio_final' => 25000,
            'fecha_pausa_inicio' => Carbon::today()->subDays(15),
            'fecha_pausa_fin' => Carbon::today(), // Reactivada hoy
            'id_estado' => $estadoActiva, // Ahora activa
            'created_at' => Carbon::today()->subDays(40),
            'updated_at' => now(),
        ]);

        DB::table('pagos')->insert([
            'uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'id_inscripcion' => $idInscripcion10,
            'id_cliente' => $idCliente10,
            'id_metodo_pago' => $idMetodoPago,
            'monto_total' => 25000,
            'monto_abonado' => 25000,
            'monto_pendiente' => 0,
            'fecha_pago' => Carbon::today()->subDays(40),
            'tipo_pago' => 'completo',
            'id_estado' => $estadoPagado,
            'observaciones' => 'Pago completo - reactivada hoy después de pausa',
            'created_at' => Carbon::today()->subDays(40),
            'updated_at' => now(),
        ]);

        $this->info('✅ Se crearon 10 clientes de prueba');
        $this->info('⭐ Cliente #5 (Daniela Vega) completa hoy su pago pendiente!');
        $this->info('⏰ Cliente #6 (Pedro Silva) membresía vence en 5 días');
        $this->info('⚠️  Cliente #7 (Laura Muñoz) membresía vencida hace 5 días');
        $this->info('💳 Cliente #8 (Roberto Campos) tiene $45.000 de deuda');
        $this->info('⏸️  Cliente #9 (Valentina Rojas) membresía pausada');
        $this->info('▶️  Cliente #10 (Sebastián Parra) reactivado hoy');
    }

    private function info($message)
    {
        if (property_exists($this, 'command') && $this->command) {
            $this->command->info($message);
        }
    }
}
