<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa');
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_cliente')->comment('Redundante pero útil para queries');

            $table->decimal('monto_total', 10, 2)->comment('Total a pagar');
            $table->decimal('monto_abonado', 10, 2)->comment('Lo que se pagó en este registro');
            $table->decimal('monto_pendiente', 10, 2)->comment('Saldo restante');

            $table->decimal('descuento_aplicado', 10, 2)->default(0);
            $table->unsignedBigInteger('id_motivo_descuento')->nullable();

            $table->date('fecha_pago');
            $table->date('periodo_inicio')->comment('Inicio del período cubierto');
            $table->date('periodo_fin')->comment('Fin del período cubierto');

            $table->unsignedBigInteger('id_metodo_pago');
            $table->string('referencia_pago', 100)->nullable()->comment('Futuro: Nº de transferencia, comprobante');

            $table->unsignedInteger('id_estado')->comment('Pendiente, Pagado, Parcial, Vencido');

            // Tipo de pago (completo, parcial, pendiente, mixto)
            $table->enum('tipo_pago', ['completo', 'parcial', 'pendiente', 'mixto'])->default('completo')->comment('Tipo de pago realizado');

            // Campos para manejo de cuotas
            $table->unsignedTinyInteger('cantidad_cuotas')->default(1)->comment('Total de cuotas en que se pagará');
            $table->unsignedTinyInteger('numero_cuota')->default(1)->comment('Cuota número (ej: 1 de 3)');
            $table->decimal('monto_cuota', 10, 2)->nullable()->comment('Monto de cada cuota');
            $table->date('fecha_vencimiento_cuota')->nullable()->comment('Fecha de vencimiento para esta cuota');

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('restrict');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('id_motivo_descuento')->references('id')->on('motivos_descuento')->onDelete('set null');
            $table->foreign('id_metodo_pago')->references('id')->on('metodos_pago')->onDelete('restrict');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('restrict');

            $table->index('id_cliente');
            $table->index('id_inscripcion');
            $table->index('fecha_pago');
            $table->index('id_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
