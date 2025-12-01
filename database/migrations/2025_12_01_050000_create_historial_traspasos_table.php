<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de traspasos de membresías
     * Registra cada traspaso realizado para auditoría y consultas
     */
    public function up(): void
    {
        Schema::create('historial_traspasos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Inscripción origen (la que se traspasa)
            $table->foreignId('inscripcion_origen_id')->constrained('inscripciones')->onDelete('cascade');
            
            // Inscripción destino (la nueva creada)
            $table->foreignId('inscripcion_destino_id')->constrained('inscripciones')->onDelete('cascade');
            
            // Cliente que cede la membresía
            $table->foreignId('cliente_origen_id')->constrained('clientes')->onDelete('cascade');
            
            // Cliente que recibe la membresía
            $table->foreignId('cliente_destino_id')->constrained('clientes')->onDelete('cascade');
            
            // Membresía traspasada
            $table->foreignId('membresia_id')->constrained('membresias')->onDelete('cascade');
            
            // Información del traspaso
            $table->date('fecha_traspaso');
            $table->text('motivo');
            $table->integer('dias_restantes_traspasados')->comment('Días que quedaban al momento del traspaso');
            $table->date('fecha_vencimiento_original');
            
            // Información financiera
            $table->decimal('monto_pagado', 10, 0)->default(0)->comment('Lo que había pagado el cliente origen');
            $table->decimal('deuda_transferida', 10, 0)->default(0)->comment('Deuda que se transfirió al destino');
            $table->boolean('se_transfirio_deuda')->default(false);
            
            // Usuario que realizó el traspaso
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('fecha_traspaso');
            $table->index('cliente_origen_id');
            $table->index('cliente_destino_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_traspasos');
    }
};
