<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dos fichas con el mismo nombre que alguien revisó y dijo: son personas
 * distintas. Sin esto, «Posibles duplicados» las volvería a mostrar cada vez,
 * y una lista que siempre trae lo mismo se deja de mirar.
 *
 * El par se guarda con el id menor primero, para que A-B y B-A sean uno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('socios_distintos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('id_a')->constrained('clientes')->cascadeOnDelete();
            $tabla->foreignId('id_b')->constrained('clientes')->cascadeOnDelete();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->timestamps();

            $tabla->unique(['id_a', 'id_b']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios_distintos');
    }
};
