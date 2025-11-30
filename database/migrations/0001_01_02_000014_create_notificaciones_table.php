<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de plantillas/tipos de notificaciones
        Schema::create('tipo_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código único: membresia_por_vencer, membresia_vencida, pago_pendiente, etc.');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('asunto_email', 255)->comment('Asunto del correo');
            $table->text('plantilla_email')->comment('Contenido HTML del correo con variables {nombre}, {fecha}, etc.');
            $table->unsignedTinyInteger('dias_anticipacion')->default(0)->comment('Días antes del evento para enviar (ej: 5 días antes de vencer)');
            $table->boolean('activo')->default(true);
            $table->boolean('enviar_email')->default(true);
            $table->timestamps();
        });

        // Tabla de notificaciones programadas/enviadas
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('id_tipo_notificacion');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_inscripcion')->nullable();
            $table->unsignedBigInteger('id_pago')->nullable();
            
            $table->string('email_destino', 150);
            $table->string('asunto', 255);
            $table->text('contenido')->comment('Contenido renderizado del correo');
            
            $table->unsignedInteger('id_estado')->comment('600=Pendiente, 601=Enviado, 602=Fallido, 603=Cancelado');
            $table->date('fecha_programada')->comment('Fecha en que se debe enviar');
            $table->timestamp('fecha_envio')->nullable()->comment('Fecha real de envío');
            
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->unsignedTinyInteger('max_intentos')->default(3);
            $table->text('error_mensaje')->nullable()->comment('Último mensaje de error si falló');
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_tipo_notificacion')->references('id')->on('tipo_notificaciones')->onDelete('restrict');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('set null');
            $table->foreign('id_pago')->references('id')->on('pagos')->onDelete('set null');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('restrict');

            // Índices
            $table->index('id_cliente');
            $table->index('id_estado');
            $table->index('fecha_programada');
            $table->index(['id_estado', 'fecha_programada']);
            $table->index('id_tipo_notificacion');
        });

        // Tabla de log de envíos (historial detallado)
        Schema::create('log_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_notificacion');
            $table->enum('accion', ['programada', 'enviando', 'enviada', 'fallida', 'reintentando', 'cancelada']);
            $table->text('detalle')->nullable();
            $table->string('ip_servidor', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_notificacion')->references('id')->on('notificaciones')->onDelete('cascade');
            $table->index('id_notificacion');
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_notificaciones');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('tipo_notificaciones');
    }
};
