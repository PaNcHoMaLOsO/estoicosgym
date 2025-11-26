<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_membresia');
            $table->unsignedInteger('id_convenio')->nullable()->comment('Convenio aplicado al momento de la inscripción');
            $table->unsignedInteger('id_precio_acordado')->comment('Precio vigente al momento de la inscripción');
            
            $table->date('fecha_inscripcion')->comment('Fecha en que se registra');
            $table->date('fecha_inicio')->comment('Fecha en que inicia la membresía (puede ser futura)');
            $table->date('fecha_vencimiento')->comment('Fecha de expiración');
            $table->unsignedTinyInteger('dia_pago')->nullable()->comment('1-31: Día del mes elegido para pagar');
            
            $table->decimal('precio_base', 10, 2)->comment('Precio oficial de la membresía');
            $table->decimal('descuento_aplicado', 10, 2)->default(0)->comment('Descuento en pesos');
            $table->unsignedInteger('id_motivo_descuento')->nullable()->comment('Justificación del descuento');
            
            $table->unsignedInteger('id_estado')->comment('Activa, Vencida, Pausada, Cancelada, Pendiente');
            $table->text('observaciones')->nullable();
            
            // Campos para control de pausas
            $table->boolean('pausada')->default(false);
            $table->unsignedTinyInteger('dias_pausa')->default(0);
            $table->dateTime('fecha_pausa_inicio')->nullable();
            $table->dateTime('fecha_pausa_fin')->nullable();
            $table->text('razon_pausa')->nullable();
            $table->unsignedTinyInteger('pausas_realizadas')->default(0);
            $table->unsignedTinyInteger('max_pausas_permitidas')->default(2);
            
            $table->timestamps();
            
            // Relaciones
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('restrict');
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
            $table->foreign('id_precio_acordado')->references('id')->on('precios_membresias')->onDelete('restrict');
            $table->foreign('id_motivo_descuento')->references('id')->on('motivos_descuento')->onDelete('set null');
            $table->foreign('id_estado')->references('id')->on('estados')->onDelete('restrict');
            
            // Índices
            $table->index('id_cliente');
            $table->index('id_membresia');
            $table->index('id_estado');
            $table->index('pausada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
