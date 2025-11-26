<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_membresias', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->unsignedInteger('id_membresia');
            $table->decimal('precio_normal', 10, 2);
            $table->decimal('precio_convenio', 10, 2)->nullable()->comment('NULL si no aplica convenio');
            $table->date('fecha_vigencia_desde');
            $table->date('fecha_vigencia_hasta')->nullable()->comment('NULL = vigente actualmente');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('restrict');
            $table->index(['fecha_vigencia_desde', 'fecha_vigencia_hasta'], 'idx_fechas_vigencia');
            $table->index('id_membresia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_membresias');
    }
};
