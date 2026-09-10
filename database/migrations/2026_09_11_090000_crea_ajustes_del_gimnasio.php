<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los ajustes del gimnasio.
 *
 * Clave y valor, y no una columna por ajuste. Un ajuste nuevo es una fila, no
 * una migración: si cada uno necesitara su columna, añadir «cuántos días antes
 * se avisa» costaría tocar la base, el modelo y la pantalla, y por eso acaban
 * quedándose escritos a mano en el código.
 *
 * Que es exactamente lo que pasaba: el nombre del gimnasio estaba dentro de las
 * plantillas de correo, «se puede renovar 30 días antes» estaba en un
 * controlador y «una nota lleva 3 días» dentro de un modelo. Nadie del gimnasio
 * podía cambiar ninguno de los tres.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes', function (Blueprint $tabla) {
            // La clave ES la clave primaria: no hay dos ajustes con el mismo
            // nombre, y un id numerico aqui solo seria una columna mas que
            // nadie mira.
            $tabla->string('clave', 60)->primary();

            // Texto para todos, y cada ajuste sabe leer el suyo. Guardar el
            // tipo en otra columna obligaria a mantenerlos en dos sitios.
            $tabla->text('valor')->nullable();

            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
