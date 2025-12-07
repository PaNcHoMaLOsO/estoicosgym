<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN CONSOLIDADA: estados
 * 
 * Incluye:
 * - Estructura original de estados
 * - Categoria 'notificacion' en ENUM (de add_notificacion_estados)
 * - Índice en 'nombre' (de add_optimization_indexes)
 * 
 * NOTA: Los estados 205 (Traspasado) y 600-603 (Notificaciones) se insertan
 * en el seeder EstadosSeeder.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('codigo')->unique()->comment('Rango: 100-199 membresías, 200-299 pagos, 300-302 convenios, 400-402 clientes, 500-504 genéricos, 600-609 notificaciones');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            
            // ✅ CONSOLIDADO: Categoria 'notificacion' agregada de add_notificacion_estados
            $table->enum('categoria', ['general', 'membresia', 'pago', 'convenio', 'cliente', 'generico', 'notificacion']);
            
            $table->boolean('activo')->default(true);
            $table->string('color', 20)->default('secondary')->comment('Color Bootstrap: primary, success, danger, warning, info, secondary');
            $table->timestamps();

            // Índices originales
            $table->index('activo');
            $table->index('categoria');
            
            // ✅ CONSOLIDADO: Índice agregado de add_optimization_indexes
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
