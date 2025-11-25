<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            // ===== RANGO 01-99: ESTADOS DE MEMBRESÍAS =====
            
            // Membresías - Estados Base (01-19)
            [
                'codigo' => 1,
                'nombre' => 'Activa',
                'descripcion' => 'Membresía vigente y activa - Usuario puede acceder',
                'categoria' => 'membresia',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 2,
                'nombre' => 'Pausada - 7 días',
                'descripcion' => 'Membresía pausada por 7 días - Sin acceso temporal',
                'categoria' => 'membresia',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 3,
                'nombre' => 'Pausada - 14 días',
                'descripcion' => 'Membresía pausada por 14 días - Sin acceso temporal',
                'categoria' => 'membresia',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 4,
                'nombre' => 'Pausada - 30 días',
                'descripcion' => 'Membresía pausada por 30 días (1 mes) - Sin acceso temporal',
                'categoria' => 'membresia',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 5,
                'nombre' => 'Vencida',
                'descripcion' => 'Membresía expirada - Requiere renovación',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 6,
                'nombre' => 'Cancelada',
                'descripcion' => 'Membresía cancelada por el cliente - Sin acceso',
                'categoria' => 'membresia',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 7,
                'nombre' => 'Suspendida - Deuda',
                'descripcion' => 'Membresía suspendida por deuda de pago',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 8,
                'nombre' => 'Pendiente de Activación',
                'descripcion' => 'Membresía creada pero sin activar - Pago pendiente',
                'categoria' => 'membresia',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 9,
                'nombre' => 'En Revisión',
                'descripcion' => 'Membresía en revisión por soporte',
                'categoria' => 'membresia',
                'color' => 'primary',
                'activo' => true,
            ],
            
            // ===== RANGO 100-199: ESTADOS DE PAGOS =====
            
            // Pagos - Estados (101-119)
            [
                'codigo' => 101,
                'nombre' => 'Pendiente',
                'descripcion' => 'Pago pendiente de realizar',
                'categoria' => 'pago',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 102,
                'nombre' => 'Pagado',
                'descripcion' => 'Pago completado exitosamente',
                'categoria' => 'pago',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 103,
                'nombre' => 'Parcial',
                'descripcion' => 'Abono realizado, resta saldo pendiente',
                'categoria' => 'pago',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 104,
                'nombre' => 'Vencido',
                'descripcion' => 'Pago no realizado después de fecha límite',
                'categoria' => 'pago',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 105,
                'nombre' => 'En Disputa',
                'descripcion' => 'Pago en revisión por disputa del cliente',
                'categoria' => 'pago',
                'color' => 'primary',
                'activo' => true,
            ],
            [
                'codigo' => 106,
                'nombre' => 'Reembolso',
                'descripcion' => 'Pago siendo reembolsado al cliente',
                'categoria' => 'pago',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 107,
                'nombre' => 'Reembolsado',
                'descripcion' => 'Pago completamente reembolsado',
                'categoria' => 'pago',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 108,
                'nombre' => 'Cancelado',
                'descripcion' => 'Pago cancelado por el cliente',
                'categoria' => 'pago',
                'color' => 'secondary',
                'activo' => true,
            ],
            
            // ===== RANGO 200-299: ESTADOS DE CONVENIOS (futuro) =====
            // Reservado para estados de convenios
            
            // ===== RANGO 300-399: ESTADOS DE CLIENTES (futuro) =====
            // Reservado para estados de clientes
        ]);
    }
}
