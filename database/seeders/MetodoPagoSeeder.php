<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
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
        ];

        DB::table('metodos_pago')->insert(array_map(
            fn ($metodo) => $metodo + ['created_at' => now(), 'updated_at' => now()],
            $metodos
        ));
    }
}
