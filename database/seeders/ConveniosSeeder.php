<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('convenios')->insert([
            [
                'nombre' => 'INACAP',
                'tipo' => 'institucion_educativa',
                'descripcion' => 'Instituto profesional',
                'activo' => true,
            ],
            [
                'nombre' => 'DUOC',
                'tipo' => 'institucion_educativa',
                'descripcion' => 'Instituto profesional',
                'activo' => true,
            ],
            [
                'nombre' => 'Cruz Verde',
                'tipo' => 'empresa',
                'descripcion' => 'Cadena de farmacias',
                'activo' => true,
            ],
            [
                'nombre' => 'Falabella',
                'tipo' => 'empresa',
                'descripcion' => 'Retail',
                'activo' => true,
            ],
        ]);
    }
}
