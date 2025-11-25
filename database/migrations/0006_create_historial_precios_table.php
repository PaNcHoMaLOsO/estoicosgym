<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->unsignedInteger('id_precio_membresia');
            $table->decimal('precio_anterior_normal', 10, 2);
            $table->decimal('precio_anterior_convenio', 10, 2)->nullable();
            $table->decimal('precio_nuevo_normal', 10, 2);
            $table->decimal('precio_nuevo_convenio', 10, 2)->nullable();
            $table->date('fecha_cambio');
            $table->text('motivo_cambio')->nullable();
            $table->unsignedInteger('usuario_modificador')->nullable()->comment('Futuro: ID del usuario que hizo el cambio');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('id_precio_membresia')->references('id')->on('precios_membresias')->onDelete('restrict');
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};
