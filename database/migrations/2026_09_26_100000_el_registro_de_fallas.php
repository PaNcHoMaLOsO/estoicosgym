<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El registro de fallas: lo que salió mal, visto desde el panel.
 *
 * Los errores quedaban en storage/logs/laravel.log, un archivo que nadie abre
 * y que mezcla las pruebas con lo real. Aquí queda cada falla una sola vez
 * —agrupada por su huella— con cuántas veces pasó, cuándo fue la primera y la
 * última, y dónde. Las del servidor y las del navegador de quien usa el panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fallas', function (Blueprint $tabla) {
            $tabla->id();
            // La misma falla repetida es una fila con su contador, no mil filas.
            $tabla->string('huella', 64)->unique();
            $tabla->string('origen', 20)->default('servidor')->comment('servidor | navegador');
            $tabla->string('nivel', 20)->default('error');
            $tabla->string('tipo', 200)->nullable()->comment('Clase de la excepción');
            $tabla->text('mensaje');
            $tabla->string('lugar', 300)->nullable()->comment('archivo:línea');
            $tabla->string('metodo', 10)->nullable();
            $tabla->string('url', 500)->nullable();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->text('traza')->nullable();
            $tabla->text('contexto')->nullable();
            $tabla->unsignedInteger('veces')->default(1);
            $tabla->timestamp('primera_vez');
            $tabla->timestamp('ultima_vez')->index();
            $tabla->timestamp('resuelta_en')->nullable();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fallas');
    }
};
