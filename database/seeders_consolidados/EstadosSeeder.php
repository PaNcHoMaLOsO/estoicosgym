<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER CONSOLIDADO: Estados
 * 
 * Incluye TODOS los estados del sistema:
 * - Estados originales (100-504)
 * - Estado 205 (Traspasado) de add_estado_traspasado_pago
 * - Estados 600-603 (Notificaciones) de add_notificacion_estados
 */
class EstadosSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            // ===== MEMBRESÍAS (100-199) =====
            ['codigo' => 100, 'nombre' => 'Activa', 'descripcion' => 'Membresía activa', 'categoria' => 'membresia', 'color' => 'success', 'activo' => true],
            ['codigo' => 101, 'nombre' => 'Pausada', 'descripcion' => 'Membresía pausada temporalmente', 'categoria' => 'membresia', 'color' => 'warning', 'activo' => true],
            ['codigo' => 102, 'nombre' => 'Vencida', 'descripcion' => 'Membresía vencida', 'categoria' => 'membresia', 'color' => 'danger', 'activo' => true],
            ['codigo' => 103, 'nombre' => 'Cancelada', 'descripcion' => 'Membresía cancelada', 'categoria' => 'membresia', 'color' => 'secondary', 'activo' => true],
            ['codigo' => 105, 'nombre' => 'Cambiada', 'descripcion' => 'Membresía cambiada por upgrade', 'categoria' => 'membresia', 'color' => 'info', 'activo' => true],

            // ===== PAGOS (200-299) =====
            ['codigo' => 200, 'nombre' => 'Pendiente', 'descripcion' => 'Pago pendiente', 'categoria' => 'pago', 'color' => 'warning', 'activo' => true],
            ['codigo' => 201, 'nombre' => 'Pagado', 'descripcion' => 'Pago completado', 'categoria' => 'pago', 'color' => 'success', 'activo' => true],
            ['codigo' => 202, 'nombre' => 'Parcial', 'descripcion' => 'Pago parcial', 'categoria' => 'pago', 'color' => 'info', 'activo' => true],
            
            // ✅ CONSOLIDADO: Estado 205 de add_estado_traspasado_pago
            ['codigo' => 205, 'nombre' => 'Traspasado', 'descripcion' => 'Pago traspasado a otra inscripción', 'categoria' => 'pago', 'color' => 'purple', 'activo' => true],

            // ===== CONVENIOS (300-399) =====
            ['codigo' => 300, 'nombre' => 'Activo', 'descripcion' => 'Convenio activo', 'categoria' => 'convenio', 'color' => 'success', 'activo' => true],
            ['codigo' => 301, 'nombre' => 'Inactivo', 'descripcion' => 'Convenio inactivo', 'categoria' => 'convenio', 'color' => 'secondary', 'activo' => true],
            ['codigo' => 302, 'nombre' => 'Vencido', 'descripcion' => 'Convenio vencido', 'categoria' => 'convenio', 'color' => 'danger', 'activo' => true],

            // ===== CLIENTES (400-499) =====
            ['codigo' => 400, 'nombre' => 'Activo', 'descripcion' => 'Cliente activo', 'categoria' => 'cliente', 'color' => 'success', 'activo' => true],
            ['codigo' => 401, 'nombre' => 'Inactivo', 'descripcion' => 'Cliente inactivo', 'categoria' => 'cliente', 'color' => 'secondary', 'activo' => true],
            ['codigo' => 402, 'nombre' => 'Suspendido', 'descripcion' => 'Cliente suspendido temporalmente', 'categoria' => 'cliente', 'color' => 'warning', 'activo' => true],

            // ===== ESTADOS GENÉRICOS (500-599) =====
            ['codigo' => 500, 'nombre' => 'Activo', 'descripcion' => 'Registro activo', 'categoria' => 'generico', 'color' => 'success', 'activo' => true],
            ['codigo' => 501, 'nombre' => 'Inactivo', 'descripcion' => 'Registro inactivo', 'categoria' => 'generico', 'color' => 'secondary', 'activo' => true],
            ['codigo' => 502, 'nombre' => 'Pendiente', 'descripcion' => 'Pendiente de aprobación', 'categoria' => 'generico', 'color' => 'warning', 'activo' => true],
            ['codigo' => 503, 'nombre' => 'Aprobado', 'descripcion' => 'Aprobado', 'categoria' => 'generico', 'color' => 'success', 'activo' => true],
            ['codigo' => 504, 'nombre' => 'Rechazado', 'descripcion' => 'Rechazado', 'categoria' => 'generico', 'color' => 'danger', 'activo' => true],

            // ✅ CONSOLIDADO: Estados 600-603 de add_notificacion_estados
            ['codigo' => 600, 'nombre' => 'Pendiente', 'descripcion' => 'Notificación programada pendiente de envío', 'categoria' => 'notificacion', 'color' => 'warning', 'activo' => true],
            ['codigo' => 601, 'nombre' => 'Enviada', 'descripcion' => 'Notificación enviada exitosamente', 'categoria' => 'notificacion', 'color' => 'success', 'activo' => true],
            ['codigo' => 602, 'nombre' => 'Fallida', 'descripcion' => 'Error al enviar la notificación', 'categoria' => 'notificacion', 'color' => 'danger', 'activo' => true],
            ['codigo' => 603, 'nombre' => 'Cancelada', 'descripcion' => 'Notificación cancelada manualmente', 'categoria' => 'notificacion', 'color' => 'secondary', 'activo' => true],
        ];

        foreach ($estados as $estado) {
            DB::table('estados')->updateOrInsert(
                ['codigo' => $estado['codigo']],
                array_merge($estado, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Estados consolidados insertados correctamente');
    }
}
