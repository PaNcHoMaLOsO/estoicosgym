<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('metodos_pago')->insert([
            [
                'codigo' => 'efectivo',
                'nombre' => 'Efectivo',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'transferencia',
                'nombre' => 'Transferencia',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'tarjeta',
                'nombre' => 'Tarjeta',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'otro',
                'nombre' => 'Mixto',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
        ]);
    }
}
