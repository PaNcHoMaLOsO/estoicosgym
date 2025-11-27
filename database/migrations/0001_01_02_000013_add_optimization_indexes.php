<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar índices para optimizar queries críticas
     */
    public function up(): void
    {
        // TABLA: CLIENTES
        // Índices para búsquedas comunes
        Schema::table('clientes', function (Blueprint $table) {
            // Ya existen índices en creación, pero reforzamos
            // Búsquedas por RUT
            $table->index('run_pasaporte');
            // Búsquedas por email
            $table->index('email');
            // Filtros por estado activo
            $table->index('activo');
            // Búsquedas por convenio
            $table->index('id_convenio');
        });

        // TABLA: INSCRIPCIONES
        // Índices para queries más rápidas
        Schema::table('inscripciones', function (Blueprint $table) {
            // Búsquedas por estado (crítico para módulo de pagos)
            $table->index('id_estado');
            // Búsquedas por cliente
            $table->index('id_cliente');
            // Búsquedas por membresía
            $table->index('id_membresia');
            // Filtros por fecha de vencimiento
            $table->index('fecha_vencimiento');
            // Índice compuesto para búsquedas comunes
            $table->index(['id_cliente', 'id_estado']);
        });

        // TABLA: PAGOS
        // Índices críticos para transacciones
        Schema::table('pagos', function (Blueprint $table) {
            // Búsquedas por estado de pago
            $table->index('id_estado');
            // Búsquedas por inscripción
            $table->index('id_inscripcion');
            // Búsquedas por método de pago
            $table->index('id_metodo_pago_principal');
            // Filtros por fecha
            $table->index('fecha_pago');
            // Búsquedas por planes de cuotas
            $table->index('es_plan_cuotas');
            // Índice compuesto para análisis de ingresos
            $table->index(['fecha_pago', 'id_estado']);
        });

        // TABLA: PRECIOS_MEMBRESIAS
        // Índices para cálculo de precios
        Schema::table('precios_membresias', function (Blueprint $table) {
            // Búsqueda de precio vigente
            $table->index('id_membresia');
            $table->index('activo');
            // Índice compuesto para buscar precio vigente actual
            $table->index(['id_membresia', 'fecha_vigencia_desde']);
        });

        // TABLA: ESTADOS
        // Índice para búsquedas rápidas (pocas filas)
        Schema::table('estados', function (Blueprint $table) {
            $table->index('nombre');
        });

        // TABLA: MEMBRESIAS
        // Índice para búsquedas comunes
        Schema::table('membresias', function (Blueprint $table) {
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['run_pasaporte']);
            $table->dropIndex(['email']);
            $table->dropIndex(['activo']);
            $table->dropIndex(['id_convenio']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropIndex(['id_estado']);
            $table->dropIndex(['id_cliente']);
            $table->dropIndex(['id_membresia']);
            $table->dropIndex(['fecha_vencimiento']);
            $table->dropIndex(['id_cliente', 'id_estado']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['id_estado']);
            $table->dropIndex(['id_inscripcion']);
            $table->dropIndex(['id_metodo_pago_principal']);
            $table->dropIndex(['fecha_pago']);
            $table->dropIndex(['es_plan_cuotas']);
            $table->dropIndex(['fecha_pago', 'id_estado']);
        });

        Schema::table('precios_membresias', function (Blueprint $table) {
            $table->dropIndex(['id_membresia']);
            $table->dropIndex(['activo']);
            $table->dropIndex(['id_membresia', 'fecha_vigencia_desde']);
        });

        Schema::table('estados', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
        });

        Schema::table('membresias', function (Blueprint $table) {
            $table->dropIndex(['activo']);
        });
    }
};
