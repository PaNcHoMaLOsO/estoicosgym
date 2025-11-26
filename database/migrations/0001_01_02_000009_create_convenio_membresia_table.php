<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_membresia', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->unsignedInteger('id_convenio');
            $table->unsignedInteger('id_membresia');

            // Precios del acuerdo
            $table->decimal('precio_convenio', 10, 2)->comment('Precio fijo para esta membresia bajo este convenio');
            $table->decimal('descuento_monto', 10, 2)->default(0)->comment('Descuento en monto absoluto');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->comment('Descuento en porcentaje');

            // Control
            $table->boolean('activo')->default(true)->comment('Si este acuerdo está vigente');
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('cascade');
            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('cascade');

            // Índices y restricción de unicidad
            $table->unique(['id_convenio', 'id_membresia']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_membresia');
    }
};
