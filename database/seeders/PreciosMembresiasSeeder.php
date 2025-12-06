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
        
        // Precios PROGYM Los Ángeles 2024
        DB::table('precios_membresias')->insert([
            // Membresía Anual (1 año = 365 días)
            [
                'id_membresia' => 1,
                'precio_normal' => 250000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Membresía Semestral (6 meses = 180 días)
            [
                'id_membresia' => 2,
                'precio_normal' => 150000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Membresía Trimestral (3 meses = 90 días)
            [
                'id_membresia' => 3,
                'precio_normal' => 100000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mensualidad (1 mes = 30 días) - CON descuento por convenio
            [
                'id_membresia' => 4,
                'precio_normal' => 40000.00,
                'precio_convenio' => 25000.00, // Con convenio
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pase Diario (1 día)
            [
                'id_membresia' => 5,
                'precio_normal' => 5000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
