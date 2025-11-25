<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            // Cambiar el ENUM para incluir 'membresia' en lugar de 'inscripcion'
            // y agregar 'cliente' como nueva opción
            $table->enum('categoria', ['general', 'membresia', 'pago', 'convenio', 'cliente'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->enum('categoria', ['general', 'inscripcion', 'pago', 'convenio'])->change();
        });
    }
};
