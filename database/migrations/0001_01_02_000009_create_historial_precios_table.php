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
            $table->decimal('precio_anterior', 10, 2)->comment('Precio anterior');
            $table->decimal('precio_nuevo', 10, 2)->comment('Precio nuevo');
            $table->string('razon_cambio', 255)->nullable()->comment('Razón del cambio');
            $table->string('usuario_cambio', 255)->nullable()->comment('Usuario que realizó el cambio');
            $table->timestamps();

            $table->foreign('id_precio_membresia')->references('id')->on('precios_membresias')->onDelete('restrict');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};
