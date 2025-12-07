<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN CONSOLIDADA: precios_membresias
 * 
 * Incluye:
 * - Estructura original
 * - Índices adicionales de add_optimization_indexes:
 *   - activo
 *   - [id_membresia, fecha_vigencia_desde]
 * 
 * NOTA: El índice id_membresia ya existía
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_membresias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_membresia');
            $table->decimal('precio_normal', 10, 2);
            $table->decimal('precio_convenio', 10, 2)->nullable()->comment('NULL si no aplica convenio');
            $table->date('fecha_vigencia_desde');
            $table->date('fecha_vigencia_hasta')->nullable()->comment('NULL = vigente actualmente');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('restrict');
            
            // Índices originales
            $table->index(['fecha_vigencia_desde', 'fecha_vigencia_hasta'], 'idx_fechas_vigencia');
            $table->index('id_membresia');
            
            // ✅ CONSOLIDADO: Índices agregados de add_optimization_indexes
            $table->index('activo');
            $table->index(['id_membresia', 'fecha_vigencia_desde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_membresias');
    }
};
