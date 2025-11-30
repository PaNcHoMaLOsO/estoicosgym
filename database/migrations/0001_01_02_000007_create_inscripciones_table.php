<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_membresia');
            
            // Campos para upgrade/downgrade
            $table->foreignId('id_inscripcion_anterior')->nullable();
            $table->boolean('es_cambio_plan')->default(false);
            $table->enum('tipo_cambio', ['upgrade', 'downgrade'])->nullable();
            $table->decimal('credito_plan_anterior', 12, 2)->default(0);
            $table->decimal('precio_nuevo_plan', 12, 2)->nullable();
            $table->decimal('diferencia_a_pagar', 12, 2)->nullable();
            $table->timestamp('fecha_cambio_plan')->nullable();
            $table->text('motivo_cambio_plan')->nullable();
            
            $table->unsignedBigInteger('id_convenio')->nullable();
            $table->unsignedBigInteger('id_precio_acordado');

            $table->date('fecha_inscripcion');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');

            $table->decimal('precio_base', 12, 2);
            $table->decimal('descuento_aplicado', 12, 2)->default(0);
            $table->decimal('precio_final', 12, 2);
            $table->unsignedBigInteger('id_motivo_descuento')->nullable();

            $table->unsignedInteger('id_estado')->comment('100=Activa, 101=Pausada, 102=Vencida, 103=Cancelada, 105=Cambiada');
            $table->text('observaciones')->nullable();
            
            // Campos para sistema de pausas
            $table->boolean('pausada')->default(false);
            $table->unsignedSmallInteger('dias_pausa')->nullable();
            $table->date('fecha_pausa_inicio')->nullable();
            $table->date('fecha_pausa_fin')->nullable();
            $table->text('razon_pausa')->nullable();
            $table->boolean('pausa_indefinida')->default(false);
            $table->unsignedTinyInteger('pausas_realizadas')->default(0);
            $table->unsignedTinyInteger('max_pausas_permitidas')->default(2);
            $table->unsignedSmallInteger('dias_compensacion')->default(0);
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('restrict');
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
            $table->foreign('id_precio_acordado')->references('id')->on('precios_membresias')->onDelete('restrict');
            $table->foreign('id_motivo_descuento')->references('id')->on('motivos_descuento')->onDelete('set null');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('restrict');

            // Índices
            $table->index('id_cliente');
            $table->index('id_estado');
            $table->index('id_inscripcion_anterior');
            $table->index('es_cambio_plan');
            $table->index('pausada');
            $table->index(['fecha_inicio', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
