<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotivoDescuentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('motivos_descuento')->insert([
            [
                'nombre' => 'Convenio Estudiante',
                'descripcion' => 'Descuento por convenio con institución educativa',
                'activo' => true,
            ],
            [
                'nombre' => 'Promoción Mensual',
                'descripcion' => 'Oferta promocional del mes',
                'activo' => true,
            ],
            [
                'nombre' => 'Cliente Frecuente',
                'descripcion' => 'Descuento por fidelidad',
                'activo' => true,
            ],
            [
                'nombre' => 'Acuerdo Especial',
                'descripcion' => 'Negociación directa con el dueño',
                'activo' => true,
            ],
            [
                'nombre' => 'Otro',
                'descripcion' => 'Motivo no especificado',
                'activo' => true,
            ],
        ]);
    }
}
