<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_membresia', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_convenio')->comment('FK a convenios');
            $table->unsignedInteger('id_membresia')->comment('FK a membresias');
            $table->decimal('precio_convenio', 10, 2)->comment('Precio específico de esta membresia con este convenio');
            $table->decimal('descuento_monto', 10, 2)->default(0)->comment('Monto fijo de descuento en pesos');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->comment('Porcentaje de descuento (referencia, se calcula en app)');
            $table->boolean('activo')->default(true)->comment('¿Está vigente esta relación?');
            $table->timestamps();

            // Relaciones
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('cascade');
            $table->foreign('id_membresia')->references('id')->on('membresias')->onDelete('cascade');

            // Índices
            $table->unique(['id_convenio', 'id_membresia']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_membresia');
    }
};
