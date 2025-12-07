<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN CONSOLIDADA: pagos
 * 
 * Incluye:
 * - Estructura original de pagos
 * - Índice compuesto [fecha_pago, id_estado] (de add_optimization_indexes)
 * 
 * NOTA: Los índices individuales ya estaban en la original
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_cliente');

            $table->decimal('monto_total', 12, 2)->comment('Monto total de la inscripción');
            $table->decimal('monto_abonado', 12, 2)->comment('Monto pagado en esta transacción');
            $table->decimal('monto_pendiente', 12, 2)->comment('Monto que falta pagar');

            $table->date('fecha_pago');
            $table->unsignedBigInteger('id_metodo_pago')->nullable();
            $table->unsignedInteger('id_estado')->comment('Estado: 200=Pendiente, 201=Pagado, 202=Parcial, 205=Traspasado');

            $table->enum('tipo_pago', ['completo', 'parcial', 'pendiente', 'mixto'])->default('completo');
            
            // Campos para pago mixto (dos métodos de pago)
            $table->unsignedBigInteger('id_metodo_pago2')->nullable()->comment('Segundo método para pago mixto');
            $table->decimal('monto_metodo1', 12, 2)->nullable()->comment('Monto del primer método en pago mixto');
            $table->decimal('monto_metodo2', 12, 2)->nullable()->comment('Monto del segundo método en pago mixto');
            
            // Campos para pagos en cuotas y referencias
            $table->string('referencia_pago')->nullable()->comment('Número de transacción, voucher, etc.');
            $table->unsignedTinyInteger('cantidad_cuotas')->nullable()->comment('Total de cuotas para pago fraccionado');
            $table->unsignedTinyInteger('numero_cuota')->nullable()->comment('Número de la cuota actual');
            $table->decimal('monto_cuota', 12, 2)->nullable()->comment('Monto de cada cuota');
            $table->date('periodo_inicio')->nullable()->comment('Inicio del período que cubre este pago');
            $table->date('periodo_fin')->nullable()->comment('Fin del período que cubre este pago');
            
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('restrict');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('id_metodo_pago')->references('id')->on('metodos_pago')->onDelete('restrict');
            $table->foreign('id_metodo_pago2')->references('id')->on('metodos_pago')->onDelete('restrict');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('restrict');

            // Índices originales
            $table->index('id_cliente');
            $table->index('id_inscripcion');
            $table->index('fecha_pago');
            $table->index('id_estado');
            $table->index('referencia_pago');
            $table->index(['id_inscripcion', 'id_estado'], 'idx_pagos_inscripcion_estado');
            
            // ✅ CONSOLIDADO: Índice compuesto agregado de add_optimization_indexes
            $table->index(['fecha_pago', 'id_estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
