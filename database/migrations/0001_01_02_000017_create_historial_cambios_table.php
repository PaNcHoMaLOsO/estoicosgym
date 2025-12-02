<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial General del Sistema
 * 
 * Esta tabla registra todos los cambios de estado importantes:
 * - Pausas/Reanudaciones de membresías
 * - Cambios de plan (upgrade/downgrade)
 * - Cambios de estado de cliente
 * - Cambios de estado de inscripción
 * - Cancelaciones
 * 
 * Los traspasos tienen su propia tabla (historial_traspasos) por su complejidad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_cambios', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Tipo de cambio
            $table->enum('tipo_cambio', [
                'pausa',
                'reanudacion', 
                'cambio_plan',
                'cambio_estado_inscripcion',
                'cambio_estado_cliente',
                'cancelacion_inscripcion',
                'suspension',
                'vencimiento',
            ]);
            
            // Entidad afectada
            $table->enum('entidad', ['inscripcion', 'cliente', 'pago']);
            $table->unsignedBigInteger('entidad_id')->comment('ID de la inscripción, cliente o pago');
            
            // Cliente relacionado (siempre presente)
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            
            // Inscripción relacionada (opcional)
            $table->unsignedBigInteger('inscripcion_id')->nullable();
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones')->onDelete('set null');
            
            // Estados
            $table->unsignedInteger('estado_anterior')->nullable()->comment('Código de estado anterior');
            $table->unsignedInteger('estado_nuevo')->comment('Código de estado nuevo');
            
            // Detalles según tipo
            $table->json('detalles')->nullable()->comment('JSON con datos específicos del cambio');
            /*
             * Ejemplos de detalles:
             * Pausa: {"dias_pausa": 7, "razon": "Viaje", "indefinida": false}
             * Reanudación: {"dias_en_pausa": 5, "dias_compensados": 5}
             * Cambio plan: {"membresia_anterior": "Mensual", "membresia_nueva": "Trimestral", "diferencia": 15000}
             * Cancelación: {"motivo": "Solicitud cliente", "reembolso": 0}
             */
            
            // Motivo/comentario
            $table->text('motivo')->nullable();
            
            // Auditoría
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('fecha_cambio')->useCurrent();
            
            $table->timestamps();
            
            // Índices
            $table->index(['entidad', 'entidad_id']);
            $table->index('cliente_id');
            $table->index('tipo_cambio');
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_cambios');
    }
};
