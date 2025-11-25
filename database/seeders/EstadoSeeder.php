<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            // Estados de inscripciones (200-299)
            [
                'codigo' => 201,
                'nombre' => 'Activa',
                'descripcion' => 'Membresía vigente y activa',
                'categoria' => 'inscripcion',
                'activo' => true,
            ],
            [
                'codigo' => 202,
                'nombre' => 'Vencida',
                'descripcion' => 'Membresía expirada',
                'categoria' => 'inscripcion',
                'activo' => true,
            ],
            [
                'codigo' => 203,
                'nombre' => 'Pausada',
                'descripcion' => 'Membresía temporalmente suspendida',
                'categoria' => 'inscripcion',
                'activo' => true,
            ],
            [
                'codigo' => 204,
                'nombre' => 'Cancelada',
                'descripcion' => 'Membresía cancelada por el cliente',
                'categoria' => 'inscripcion',
                'activo' => true,
            ],
            [
                'codigo' => 205,
                'nombre' => 'Pendiente',
                'descripcion' => 'Pago pendiente, inicio futuro',
                'categoria' => 'inscripcion',
                'activo' => true,
            ],
            // Estados de pagos (300-399)
            [
                'codigo' => 301,
                'nombre' => 'Pendiente',
                'descripcion' => 'Pago incompleto o por realizar',
                'categoria' => 'pago',
                'activo' => true,
            ],
            [
                'codigo' => 302,
                'nombre' => 'Pagado',
                'descripcion' => 'Pago completado',
                'categoria' => 'pago',
                'activo' => true,
            ],
            [
                'codigo' => 303,
                'nombre' => 'Parcial',
                'descripcion' => 'Abono realizado, resta saldo',
                'categoria' => 'pago',
                'activo' => true,
            ],
            [
                'codigo' => 304,
                'nombre' => 'Vencido',
                'descripcion' => 'Pago no realizado en fecha límite',
                'categoria' => 'pago',
                'activo' => true,
            ],
        ]);
    }
}
