<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El correo del especialista, para su perfil: hay quien prefiere escribir un
 * correo antes que un WhatsApp a alguien que no conoce.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('email', 150)->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropColumn('email');
        });
    }
};
