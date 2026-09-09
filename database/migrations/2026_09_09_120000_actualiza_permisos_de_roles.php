<?php

use Database\Seeders\RolesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pasa los permisos al vocabulario «modulo.accion».
 *
 * Los roles ya sembrados llevaban nombres sueltos («ver_clientes»,
 * «registrar_pago») que no cubrian ni la mitad de los modulos y que ademas
 * nadie leia, porque hasta ahora no habia middleware que los comprobara. Al
 * empezar a exigirlos de verdad, una recepcion con la lista vieja se habria
 * quedado sin poder inscribir a nadie.
 *
 * Solo se toca el rol que sigue teniendo la lista antigua: si alguien ya ajusto
 * los permisos a mano, esto no se los pisa.
 */
return new class extends Migration
{
    private const PERMISOS_ANTIGUOS = [
        'ver_clientes',
        'crear_cliente',
        'editar_cliente',
        'ver_pagos',
        'registrar_pago',
    ];

    public function up(): void
    {
        $rol = DB::table('roles')->where('id', 2)->first();

        if (! $rol) {
            return;
        }

        $actuales = json_decode((string) $rol->permisos, true) ?: [];

        sort($actuales);
        $antiguos = self::PERMISOS_ANTIGUOS;
        sort($antiguos);

        if ($actuales !== $antiguos) {
            return;
        }

        DB::table('roles')->where('id', 2)->update([
            'permisos' => json_encode(RolesSeeder::PERMISOS_RECEPCION),
            'descripcion' => 'Atención de socios: altas, inscripciones y cobros',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->where('id', 2)->update([
            'permisos' => json_encode(self::PERMISOS_ANTIGUOS),
            'updated_at' => now(),
        ]);
    }
};
