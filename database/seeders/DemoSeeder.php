<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Seeder para generar datos de demostración
     * Ejecutar: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->command->info('🎭 Generando datos de DEMOSTRACIÓN...');

        // 1. Crear 5 clientes de prueba
        $this->crearClientes();
        
        // 2. Crear inscripciones con diferentes estados
        $this->crearInscripciones();
        
        // 3. Crear pagos con diferentes estados
        $this->crearPagos();
        
        // 4. Crear algunas notificaciones de prueba
        $this->crearNotificaciones();

        $this->command->info('');
        $this->command->info('✅ Datos de demostración creados exitosamente');
        $this->command->info('');
        $this->command->info('📊 Resumen:');
        $this->command->info('   • 5 Clientes creados');
        $this->command->info('   • 5 Inscripciones (Activa, Por Vencer, Vencida, Suspendida, Renovada)');
        $this->command->info('   • 7 Pagos (Pagado, Pendiente, Parcial, Vencido)');
        $this->command->info('   • 3 Notificaciones programadas');
        $this->command->info('');
    }

    private function crearClientes(): void
    {
        $this->command->info('👥 Creando clientes de prueba...');

        $clientes = [
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'run_pasaporte' => '12345678-5',
                'nombres' => 'Juan Carlos',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'Pérez',
                'email' => 'juan.gonzalez@demo.cl',
                'celular' => '+56912345678',
                'direccion' => 'Av. Libertador Bernardo O\'Higgins 1234',
                'fecha_nacimiento' => '1990-05-15',
                'id_estado' => 100, // Cliente Activo
                'activo' => true,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'run_pasaporte' => '23456789-6',
                'nombres' => 'María Fernanda',
                'apellido_paterno' => 'Silva',
                'apellido_materno' => 'Rojas',
                'email' => 'maria.silva@demo.cl',
                'celular' => '+56923456789',
                'direccion' => 'Calle Moneda 567',
                'fecha_nacimiento' => '1985-08-22',
                'id_estado' => 100,
                'activo' => true,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'run_pasaporte' => '34567890-7',
                'nombres' => 'Pedro Antonio',
                'apellido_paterno' => 'Muñoz',
                'apellido_materno' => 'Lagos',
                'email' => 'pedro.munoz@demo.cl',
                'celular' => '+56934567890',
                'direccion' => 'Pasaje Los Aromos 890',
                'fecha_nacimiento' => '1995-12-10',
                'id_estado' => 100,
                'activo' => true,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'run_pasaporte' => '45678901-8',
                'nombres' => 'Ana Luisa',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Castro',
                'email' => 'ana.fernandez@demo.cl',
                'celular' => '+56945678901',
                'direccion' => 'Av. Providencia 2345',
                'fecha_nacimiento' => '1988-03-28',
                'id_estado' => 100,
                'activo' => true,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'run_pasaporte' => '56789012-9',
                'nombres' => 'Carlos Eduardo',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Vidal',
                'email' => 'carlos.ramirez@demo.cl',
                'celular' => '+56956789012',
                'direccion' => 'Calle Huérfanos 678',
                'fecha_nacimiento' => '1992-11-05',
                'id_estado' => 100,
                'activo' => true,
            ],
        ];

        foreach ($clientes as $cliente) {
            DB::table('clientes')->insert(array_merge($cliente, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('   ✓ 5 clientes creados');
    }

    private function crearInscripciones(): void
    {
        $this->command->info('📝 Creando inscripciones con diferentes estados...');

        // 1. Inscripción ACTIVA (más de 5 días restantes)
        DB::table('inscripciones')->insert([
            'id_cliente' => 1,
            'id_membresia' => 3, // Trimestral
            'fecha_inicio' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'fecha_termino' => Carbon::now()->addDays(60)->format('Y-m-d'),
            'monto_total' => 15000,
            'monto_pagado' => 15000,
            'id_estado' => 200, // Activa
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Inscripción POR VENCER (0-5 días restantes)
        DB::table('inscripciones')->insert([
            'id_cliente' => 2,
            'id_membresia' => 4, // Mensual
            'fecha_inicio' => Carbon::now()->subDays(27)->format('Y-m-d'),
            'fecha_termino' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'monto_total' => 8000,
            'monto_pagado' => 8000,
            'id_estado' => 201, // Por Vencer
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Inscripción VENCIDA (días negativos)
        DB::table('inscripciones')->insert([
            'id_cliente' => 3,
            'id_membresia' => 4, // Mensual
            'fecha_inicio' => Carbon::now()->subDays(40)->format('Y-m-d'),
            'fecha_termino' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'monto_total' => 8000,
            'monto_pagado' => 8000,
            'id_estado' => 202, // Vencida
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Inscripción SUSPENDIDA
        DB::table('inscripciones')->insert([
            'id_cliente' => 4,
            'id_membresia' => 2, // Semestral
            'fecha_inicio' => Carbon::now()->subDays(60)->format('Y-m-d'),
            'fecha_termino' => Carbon::now()->addDays(120)->format('Y-m-d'),
            'monto_total' => 25000,
            'monto_pagado' => 15000, // Pago parcial
            'id_estado' => 203, // Suspendida
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Inscripción RENOVADA (histórico)
        DB::table('inscripciones')->insert([
            'id_cliente' => 5,
            'id_membresia' => 1, // Anual
            'fecha_inicio' => Carbon::now()->format('Y-m-d'),
            'fecha_termino' => Carbon::now()->addDays(365)->format('Y-m-d'),
            'monto_total' => 45000,
            'monto_pagado' => 45000,
            'id_estado' => 205, // Renovada
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('   ✓ 5 inscripciones creadas (Activa, Por Vencer, Vencida, Suspendida, Renovada)');
    }

    private function crearPagos(): void
    {
        $this->command->info('💰 Creando pagos con diferentes estados...');

        // Pago 1: PAGADO COMPLETO - Efectivo
        DB::table('pagos')->insert([
            'id_inscripcion' => 1,
            'id_metodo_pago' => 1, // Efectivo
            'fecha_pago' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'monto' => 15000,
            'id_estado' => 300, // Pagado
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 2: PAGADO COMPLETO - Tarjeta
        DB::table('pagos')->insert([
            'id_inscripcion' => 2,
            'id_metodo_pago' => 2, // Tarjeta
            'fecha_pago' => Carbon::now()->subDays(27)->format('Y-m-d'),
            'monto' => 8000,
            'id_estado' => 300, // Pagado
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 3: PAGADO COMPLETO - Transferencia
        DB::table('pagos')->insert([
            'id_inscripcion' => 3,
            'id_metodo_pago' => 3, // Transferencia
            'fecha_pago' => Carbon::now()->subDays(40)->format('Y-m-d'),
            'monto' => 8000,
            'id_estado' => 300, // Pagado
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 4: PARCIAL (inscripción suspendida)
        DB::table('pagos')->insert([
            'id_inscripcion' => 4,
            'id_metodo_pago' => 1, // Efectivo
            'fecha_pago' => Carbon::now()->subDays(60)->format('Y-m-d'),
            'monto' => 15000,
            'id_estado' => 302, // Parcial
            'observaciones' => 'Abono inicial - Falta $10,000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 5: PENDIENTE
        DB::table('pagos')->insert([
            'id_inscripcion' => 5,
            'id_metodo_pago' => 2, // Tarjeta
            'fecha_pago' => Carbon::now()->format('Y-m-d'),
            'monto' => 0,
            'id_estado' => 301, // Pendiente
            'observaciones' => 'Pago programado para fin de mes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 6: VENCIDO
        DB::table('pagos')->insert([
            'id_inscripcion' => 3,
            'id_metodo_pago' => 1,
            'fecha_pago' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'fecha_vencimiento' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'monto' => 0,
            'id_estado' => 303, // Vencido
            'observaciones' => 'Pago de renovación vencido',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pago 7: Segundo pago PAGADO para mostrar múltiples
        DB::table('pagos')->insert([
            'id_inscripcion' => 5,
            'id_metodo_pago' => 3, // Transferencia
            'fecha_pago' => Carbon::now()->format('Y-m-d'),
            'monto' => 45000,
            'id_estado' => 300, // Pagado
            'observaciones' => 'Pago anual completo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('   ✓ 7 pagos creados (4 Pagados, 1 Pendiente, 1 Parcial, 1 Vencido)');
    }

    private function crearNotificaciones(): void
    {
        $this->command->info('📧 Creando notificaciones programadas...');

        // Notificación 1: Membresía por vencer
        DB::table('notificaciones')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'id_tipo_notificacion' => 3, // membresia_por_vencer
            'id_cliente' => 2,
            'id_inscripcion' => 2,
            'email_destino' => 'maria.silva@demo.cl',
            'asunto' => '⏰ María Fernanda, tu membresía vence en 3 días',
            'contenido' => 'Tu membresía mensual está próxima a vencer...',
            'id_estado' => 600, // Pendiente
            'fecha_programada' => Carbon::now()->format('Y-m-d'),
            'intentos' => 0,
            'max_intentos' => 3,
            'tipo_envio' => 'automatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notificación 2: Membresía vencida
        DB::table('notificaciones')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'id_tipo_notificacion' => 4, // membresia_vencida
            'id_cliente' => 3,
            'id_inscripcion' => 3,
            'email_destino' => 'pedro.munoz@demo.cl',
            'asunto' => '❗ Pedro Antonio, tu membresía en PROGYM ha vencido',
            'contenido' => 'Tu membresía ha vencido hace 10 días...',
            'id_estado' => 601, // Enviado
            'fecha_programada' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'fecha_envio' => Carbon::now()->subDays(10),
            'intentos' => 1,
            'max_intentos' => 3,
            'tipo_envio' => 'automatica',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        // Notificación 3: Bienvenida
        DB::table('notificaciones')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'id_tipo_notificacion' => 1, // bienvenida
            'id_cliente' => 5,
            'id_inscripcion' => 5,
            'email_destino' => 'carlos.ramirez@demo.cl',
            'asunto' => '🎉 Bienvenido/a Carlos Eduardo a PROGYM',
            'contenido' => 'Gracias por unirte a PROGYM...',
            'id_estado' => 601, // Enviado
            'fecha_programada' => Carbon::now()->format('Y-m-d'),
            'fecha_envio' => Carbon::now(),
            'intentos' => 1,
            'max_intentos' => 3,
            'tipo_envio' => 'automatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('   ✓ 3 notificaciones creadas (1 Pendiente, 2 Enviadas)');
    }
}
