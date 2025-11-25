<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_inscripcion')->nullable()->comment('Si es notificación de vencimiento');
            $table->enum('tipo', ['vencimiento_proximo', 'vencimiento_cumplido', 'pago_pendiente', 'bienvenida', 'otro']);
            $table->enum('canal', ['email', 'whatsapp', 'sms'])->default('email');
            $table->string('destinatario', 100)->comment('Email o teléfono');
            $table->string('asunto', 200);
            $table->text('mensaje');
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->timestamp('fecha_envio')->nullable();
            $table->text('error_mensaje')->nullable()->comment('Detalle del error si falló');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('set null');
            
            $table->index('id_cliente');
            $table->index('estado');
            $table->index('fecha_envio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
