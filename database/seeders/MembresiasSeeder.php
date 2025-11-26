<?php

namespace Database\Seeders;

use App\Models\Membresia;
use Illuminate\Database\Seeder;

class MembresiasSeeder extends Seeder
{
    public function run(): void
    {
        Membresia::create([
            'nombre' => 'Anual',
            'duracion_meses' => 12,
            'duracion_dias' => 365,
            'descripcion' => 'Membresía válida por 12 meses',
            'activo' => true,
        ]);

        Membresia::create([
            'nombre' => 'Semestral',
            'duracion_meses' => 6,
            'duracion_dias' => 180,
            'descripcion' => 'Membresía válida por 6 meses',
            'activo' => true,
        ]);

        Membresia::create([
            'nombre' => 'Trimestral',
            'duracion_meses' => 3,
            'duracion_dias' => 90,
            'descripcion' => 'Membresía válida por 3 meses',
            'activo' => true,
        ]);

        Membresia::create([
            'nombre' => 'Mensual',
            'duracion_meses' => 1,
            'duracion_dias' => 30,
            'descripcion' => 'Membresía válida por 1 mes',
            'activo' => true,
        ]);

        Membresia::create([
            'nombre' => 'Pase Diario',
            'duracion_meses' => 0,
            'duracion_dias' => 1,
            'descripcion' => 'Acceso por un solo día',
            'activo' => true,
        ]);
    }
}
