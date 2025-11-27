<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            // ===== RANGO 100-199: ESTADOS DE MEMBRESÍAS =====
            // Códigos: 100=Activa, 101=Pausada, 102=Vencida, 103=Cancelada, 104=Suspendida
            
            [
                'codigo' => 100,
                'nombre' => 'Activa',
                'descripcion' => 'Membresía vigente y activa',
                'categoria' => 'membresia',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 101,
                'nombre' => 'Pausada',
                'descripcion' => 'Membresía pausada temporalmente',
                'categoria' => 'membresia',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 102,
                'nombre' => 'Vencida',
                'descripcion' => 'Membresía expirada',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 103,
                'nombre' => 'Cancelada',
                'descripcion' => 'Membresía cancelada',
                'categoria' => 'membresia',
                'color' => 'secondary',
                'activo' => true,
            ],
            [
                'codigo' => 104,
                'nombre' => 'Suspendida',
                'descripcion' => 'Membresía suspendida por deuda',
                'categoria' => 'membresia',
                'color' => 'danger',
                'activo' => true,
            ],
            
            // ===== RANGO 200-299: ESTADOS DE PAGOS =====
            // Códigos: 200=Pendiente, 201=Pagado, 202=Parcial, 203=Vencido, 204=Cancelado
            
            [
                'codigo' => 200,
                'nombre' => 'Pendiente',
                'descripcion' => 'Pago pendiente de realizar',
                'categoria' => 'pago',
                'color' => 'warning',
                'activo' => true,
            ],
            [
                'codigo' => 201,
                'nombre' => 'Pagado',
                'descripcion' => 'Pago completado',
                'categoria' => 'pago',
                'color' => 'success',
                'activo' => true,
            ],
            [
                'codigo' => 202,
                'nombre' => 'Parcial',
                'descripcion' => 'Pago parcial, saldo pendiente',
                'categoria' => 'pago',
                'color' => 'info',
                'activo' => true,
            ],
            [
                'codigo' => 203,
                'nombre' => 'Vencido',
                'descripcion' => 'Pago vencido sin realizar',
                'categoria' => 'pago',
                'color' => 'danger',
                'activo' => true,
            ],
            [
                'codigo' => 204,
                'nombre' => 'Cancelado',
                'descripcion' => 'Pago cancelado',
                'categoria' => 'pago',
                'color' => 'secondary',
                'activo' => true,
            ],
            
            // ===== RANGO 300-399: ESTADOS DE CONVENIOS (FUTURO) =====
            // Reservado para estados de convenios
            
            // ===== RANGO 400-499: ESTADOS DE CLIENTES (FUTURO) =====
            // Reservado para estados de clientes
        ]);
    }
}
