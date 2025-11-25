<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreciosMembresiasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('precios_membresias')->insert([
            [
                'id_membresia' => 1,
                'precio_normal' => 250000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
            ],
            [
                'id_membresia' => 2,
                'precio_normal' => 150000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
            ],
            [
                'id_membresia' => 3,
                'precio_normal' => 90000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
            ],
            [
                'id_membresia' => 4,
                'precio_normal' => 40000.00,
                'precio_convenio' => 25000.00,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
            ],
            [
                'id_membresia' => 5,
                'precio_normal' => 5000.00,
                'precio_convenio' => null,
                'fecha_vigencia_desde' => Carbon::now()->toDateString(),
                'fecha_vigencia_hasta' => null,
                'activo' => true,
            ],
        ]);
    }
}
