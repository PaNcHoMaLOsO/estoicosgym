<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLA PAGOS - ARQUITECTURA HÍBRIDA
 * 
 * Almacena todos los pagos y abonos de inscripciones
 * Soporta:
 * - Pagos simples (monto completo)
 * - Abonos parciales
 * - Planes de cuotas (múltiples cuotas)
 * - Pagos mixtos (múltiples métodos)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            // ========== IDENTIFICADORES ==========
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa en URLs');
            $table->uuid('grupo_pago')->nullable()
                  ->comment('UUID para agrupar cuotas del mismo plan de pago');

            // ========== RELACIONES PRINCIPALES ==========
            $table->unsignedInteger('id_inscripcion')
                  ->comment('FK: Inscripción a la que pertenece este pago');
            $table->unsignedInteger('id_metodo_pago_principal')
                  ->comment('FK: Método principal de pago (efectivo, tarjeta, transferencia)');
            $table->unsignedInteger('id_estado')
                  ->comment('FK: Estado del pago (Pendiente, Pagado, Parcial, Vencido)');
            $table->unsignedInteger('id_motivo_descuento')->nullable()
                  ->comment('FK: Si aplica descuento');

            // ========== MONTOS (NO DESNORMALIZAR) ==========
            $table->decimal('monto_abonado', 10, 2)
                  ->comment('Cantidad abonada en este registro de pago');
            $table->decimal('monto_pendiente', 10, 2)
                  ->comment('Saldo restante de la inscripción');

            // ========== FECHAS ==========
            $table->date('fecha_pago')
                  ->comment('Fecha en que se realizó el pago');
            $table->date('fecha_vencimiento_cuota')->nullable()
                  ->comment('Si es cuota, fecha de vencimiento de esta cuota');

            // ========== REFERENCIA Y MÉTODOS MÚLTIPLES ==========
            $table->string('referencia_pago', 100)->nullable()
                  ->comment('Número de transferencia, comprobante, referencia');
            $table->json('metodos_pago_json')->nullable()
                  ->comment('{"efectivo": 100, "tarjeta": 50} para pagos mixtos');

            // ========== CUOTAS ==========
            $table->boolean('es_plan_cuotas')->default(false)
                  ->comment('¿Este pago es parte de un plan de cuotas?');
            $table->unsignedTinyInteger('cantidad_cuotas')->nullable()
                  ->comment('Total de cuotas en el plan (NULL si no es plan)');
            $table->unsignedTinyInteger('numero_cuota')->nullable()
                  ->comment('Número de cuota actual (NULL si no es plan)');
            $table->decimal('monto_cuota', 10, 2)->nullable()
                  ->comment('Monto individual de cada cuota');

            // ========== OBSERVACIONES ==========
            $table->text('observaciones')->nullable()
                  ->comment('Notas adicionales sobre el pago');

            // ========== TIMESTAMPS ==========
            $table->timestamps();

            // ========== FOREIGN KEYS ==========
            $table->foreign('id_inscripcion')
                  ->references('id')->on('inscripciones')
                  ->onDelete('restrict');
            $table->foreign('id_metodo_pago_principal')
                  ->references('id')->on('metodos_pago')
                  ->onDelete('restrict');
            $table->foreign('id_estado')
                  ->references('id')->on('estados')
                  ->onDelete('restrict');
            $table->foreign('id_motivo_descuento')
                  ->references('id')->on('motivos_descuento')
                  ->onDelete('set null');

            // ========== ÍNDICES ==========
            $table->index('id_inscripcion');
            $table->index('id_estado');
            $table->index('fecha_pago');
            $table->index('id_metodo_pago_principal');
            $table->index('es_plan_cuotas');
            $table->index('grupo_pago');
            $table->index(['id_metodo_pago_principal', 'referencia_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

