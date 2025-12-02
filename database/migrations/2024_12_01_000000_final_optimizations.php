<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN FINAL DE OPTIMIZACIONES
 * 
 * Esta es la ÚLTIMA migración de estructura de la base de datos.
 * Después de esta, la BD queda CONGELADA.
 * 
 * Cambios incluidos:
 * 1. Soft Deletes en clientes e inscripciones
 * 2. Índices compuestos para optimización de consultas
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. SOFT DELETES
        // ══════════════════════════════════════════════════════════════
        
        // Agregar deleted_at a clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Agregar deleted_at a inscripciones
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════════════════
        // 2. ÍNDICES COMPUESTOS
        // ══════════════════════════════════════════════════════════════

        // Índice compuesto en inscripciones (id_cliente, id_estado)
        // Optimiza: búsqueda de inscripciones por cliente y estado
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->index(['id_cliente', 'id_estado'], 'idx_inscripciones_cliente_estado');
        });

        // Índice compuesto en pagos (id_inscripcion, id_estado)
        // Optimiza: búsqueda de pagos por inscripción y estado
        Schema::table('pagos', function (Blueprint $table) {
            $table->index(['id_inscripcion', 'id_estado'], 'idx_pagos_inscripcion_estado');
        });

        // Índice compuesto en notificaciones (fecha_programada, id_estado)
        // Optimiza: consulta de notificaciones pendientes por fecha
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(['fecha_programada', 'id_estado'], 'idx_notificaciones_fecha_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ══════════════════════════════════════════════════════════════
        // REVERTIR ÍNDICES COMPUESTOS
        // ══════════════════════════════════════════════════════════════

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_notificaciones_fecha_estado');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex('idx_pagos_inscripcion_estado');
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropIndex('idx_inscripciones_cliente_estado');
        });

        // ══════════════════════════════════════════════════════════════
        // REVERTIR SOFT DELETES
        // ══════════════════════════════════════════════════════════════

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
