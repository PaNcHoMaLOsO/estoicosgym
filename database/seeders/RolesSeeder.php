<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Control total del sistema',
                'permisos' => json_encode(['*']),
                'activo' => true,
            ],
            [
                'nombre' => 'Recepcionista',
                'descripcion' => 'Registro de clientes y pagos',
                'permisos' => json_encode([
                    'ver_clientes',
                    'crear_cliente',
                    'editar_cliente',
                    'ver_pagos',
                    'registrar_pago',
                ]),
                'activo' => true,
            ],
        ]);
    }
}
