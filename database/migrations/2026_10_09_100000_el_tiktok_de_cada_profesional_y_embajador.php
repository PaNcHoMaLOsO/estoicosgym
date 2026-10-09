<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El TikTok de cada profesional y de cada embajador, al lado del Instagram.
 *
 * Igual que el Instagram, se guarda solo el usuario («progym») y el enlace lo
 * arma el modelo. Solo se agrega una columna: lo cargado no se toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('tiktok', 40)->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropColumn('tiktok');
        });
    }
};
