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
            $table->unsignedBigInteger('id_convenio')->nullable();
            $table->unsignedBigInteger('id_precio_acordado');

            $table->date('fecha_inscripcion');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');

            $table->decimal('precio_base', 12, 2);
            $table->decimal('descuento_aplicado', 12, 2)->default(0);
            $table->decimal('precio_final', 12, 2);
            $table->unsignedBigInteger('id_motivo_descuento')->nullable();

            $table->unsignedInteger('id_estado')->comment('100=Activa, 102=Vencida, 103=Cancelada');
            $table->text('observaciones')->nullable();
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
            $table->index(['fecha_inicio', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
