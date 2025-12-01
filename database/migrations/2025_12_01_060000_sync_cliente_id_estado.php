<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración para sincronizar id_estado en clientes
 * 
 * Problema: La tabla clientes tiene dos sistemas de estado:
 * - `activo` (boolean): usado en controladores
 * - `id_estado` (int): referencia a estados.codigo (400-402)
 * 
 * Esta migración sincroniza id_estado basándose en el valor de activo:
 * - activo = true  → id_estado = 400 (Cliente Activo)
 * - activo = false → id_estado = 402 (Cliente Cancelado)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sincronizar id_estado basándose en activo
        DB::table('clientes')
            ->where('activo', true)
            ->whereNull('id_estado')
            ->update(['id_estado' => 400]); // Cliente Activo

        DB::table('clientes')
            ->where('activo', false)
            ->whereNull('id_estado')
            ->update(['id_estado' => 402]); // Cliente Cancelado

        // También actualizar cualquier registro con id_estado = 0
        DB::table('clientes')
            ->where('id_estado', 0)
            ->update(['id_estado' => 400]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay nada que revertir - los datos se mantienen
    }
};
