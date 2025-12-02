<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración para agregar el estado 205 (Pago Traspasado)
 * 
 * Este estado se usa cuando un pago se transfiere de una inscripción
 * a otra durante un traspaso de membresía.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Verificar si no existe antes de insertar
        $existe = DB::table('estados')->where('codigo', 205)->exists();
        
        if (!$existe) {
            DB::table('estados')->insert([
                'codigo' => 205,
                'nombre' => 'Traspasado',
                'descripcion' => 'Pago traspasado a otra inscripción',
                'categoria' => 'pago',
                'color' => 'purple',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('estados')->where('codigo', 205)->delete();
    }
};
