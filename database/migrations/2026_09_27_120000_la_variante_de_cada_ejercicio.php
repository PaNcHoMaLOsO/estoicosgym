<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «Si está ocupada, haz esto otro.»
 *
 * En una sala llena la máquina de la rutina casi siempre tiene a alguien. Sin
 * una alternativa escrita, quien recién empieza se queda esperando o se salta
 * el ejercicio. Cada línea de la rutina puede nombrar otro ejercicio que
 * trabaje lo mismo con otro equipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutina_ejercicios', function (Blueprint $table) {
            $table->foreignId('id_alternativa')->nullable()->after('id_ejercicio')
                ->constrained('ejercicios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rutina_ejercicios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_alternativa');
        });
    }
};
