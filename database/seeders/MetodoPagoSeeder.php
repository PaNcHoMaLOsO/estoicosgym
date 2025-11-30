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
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo en el gimnasio',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Transferencia',
                'descripcion' => 'Transferencia bancaria',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Tarjeta',
                'descripcion' => 'Tarjeta de débito o crédito',
                'requiere_comprobante' => false,
                'activo' => true,
            ],
        ]);
    }
}
