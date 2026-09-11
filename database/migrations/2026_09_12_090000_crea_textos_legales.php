<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El contrato, los términos y condiciones y la política de privacidad, con sus
 * versiones.
 *
 * UNA FILA POR VERSIÓN, y la que alguien ya firmó no se vuelve a tocar: al
 * editarla se crea la siguiente. Así la respuesta a «¿qué aceptó este socio?»
 * es un texto concreto y no «el que hubiera ese día».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textos_legales', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('tipo', 20)->comment('contrato, terminos o privacidad');
            $tabla->unsignedInteger('version');
            $tabla->longText('contenido')->comment('Markdown con {variables}.');
            $tabla->foreignId('id_usuario')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Quién la guardó. NULL = el texto base del sistema, sin revisar.');
            $tabla->timestamps();

            $tabla->unique(['tipo', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textos_legales');
    }
};
