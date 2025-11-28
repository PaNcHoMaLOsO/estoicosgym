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
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_cliente');

            $table->decimal('monto_total', 12, 2)->comment('Monto total de la inscripción');
            $table->decimal('monto_abonado', 12, 2)->comment('Monto pagado en esta transacción');
            $table->decimal('monto_pendiente', 12, 2)->comment('Monto que falta pagar');

            $table->date('fecha_pago');
            $table->unsignedBigInteger('id_metodo_pago');
            $table->unsignedInteger('id_estado')->comment('Estado: 200=Pendiente, 201=Pagado, 202=Parcial');

            $table->enum('tipo_pago', ['completo', 'parcial', 'pendiente', 'mixto'])->default('completo');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_inscripcion')->references('id')->on('inscripciones')->onDelete('restrict');
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('id_metodo_pago')->references('id')->on('metodos_pago')->onDelete('restrict');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('restrict');

            // Índices
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
