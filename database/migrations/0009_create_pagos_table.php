<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa');
            $table->unsignedInteger('id_inscripcion');
            $table->unsignedInteger('id_cliente')->comment('Redundante pero útil para queries');
            
            $table->decimal('monto_total', 10, 2)->comment('Total a pagar');
            $table->decimal('monto_abonado', 10, 2)->comment('Lo que se pagó en este registro');
            $table->decimal('monto_pendiente', 10, 2)->comment('Saldo restante');
            
            $table->decimal('descuento_aplicado', 10, 2)->default(0);
            $table->date('fecha_pago')->comment('Fecha en que se realizó o realizará el pago');
            $table->date('periodo_inicio')->comment('Inicio del período de cobertura');
            $table->date('periodo_fin')->comment('Fin del período de cobertura');
            
            $table->unsignedInteger('id_metodo_pago')->comment('Forma de pago');
            $table->unsignedInteger('id_estado')->comment('Pendiente, Pagado, Cancelado, etc');
            
            $table->unsignedTinyInteger('cantidad_cuotas')->default(1)->comment('Cantidad de cuotas');
            $table->unsignedTinyInteger('numero_cuota')->default(1)->comment('Número de cuota en que se dividió');
            $table->decimal('monto_cuota', 10, 2)->comment('Monto por cada cuota');
            
            $table->timestamps();
            
            // Relaciones
            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('cascade');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('id_metodo_pago')->references('id')->on('metodos_pago')->onDelete('restrict');
            $table->foreign('id_estado')->references('id')->on('estados')->onDelete('restrict');
            
            // Índices
            $table->index('id_cliente');
            $table->index('id_inscripcion');
            $table->index('id_estado');
            $table->index('fecha_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
