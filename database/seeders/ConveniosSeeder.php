<?php

namespace Database\Seeders;

use App\Models\Convenio;
use Illuminate\Database\Seeder;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        Convenio::create([
            'nombre' => 'INACAP',
            'tipo' => 'institucion_educativa',
            'descripcion' => 'Instituto profesional',
            'descuento_porcentaje' => 10,
            'descuento_monto' => 0,
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'DUOC',
            'tipo' => 'institucion_educativa',
            'descripcion' => 'Instituto profesional',
            'descuento_porcentaje' => 10,
            'descuento_monto' => 0,
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Cruz Verde',
            'tipo' => 'empresa',
            'descripcion' => 'Cadena de farmacias',
            'descuento_porcentaje' => 5,
            'descuento_monto' => 0,
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Falabella',
            'tipo' => 'empresa',
            'descripcion' => 'Retail',
            'descuento_porcentaje' => 5,
            'descuento_monto' => 0,
            'activo' => true,
        ]);
    }
}
