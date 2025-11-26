<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreciosMembresiasSeeder extends Seeder
{
    public function run(): void
    {
        // Precios realistas para gimnasio en Santiago, Chile
        // NOTA: El descuento por convenio SOLO aplica para membresía Mensual
        
        // Membresía Anual (1 año = 365 días) - SIN descuento por convenio
        DB::table('precios_membresias')->insert([
            [
                'id_membresia' => 1,
                'precio_normal' => 299000.00,
                'precio_convenio' => null, // Sin descuento por convenio
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Membresía Semestral (6 meses = 180 días) - SIN descuento por convenio
            [
                'id_membresia' => 2,
                'precio_normal' => 170000.00,
                'precio_convenio' => null, // Sin descuento por convenio
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Membresía Trimestral (3 meses = 90 días) - SIN descuento por convenio
            [
                'id_membresia' => 3,
                'precio_normal' => 99000.00,
                'precio_convenio' => null, // Sin descuento por convenio
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Membresía Mensual (1 mes = 30 días) - CON descuento por convenio ($40k -> $25k)
            [
                'id_membresia' => 4,
                'precio_normal' => 40000.00,
                'precio_convenio' => 25000.00, // Descuento disponible para convenios
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pase Diario (1 día) - SIN descuento por convenio
            [
                'id_membresia' => 5,
                'precio_normal' => 8000.00,
                'precio_convenio' => null, // Sin descuento por convenio
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
