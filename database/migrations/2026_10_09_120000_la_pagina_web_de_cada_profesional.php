<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La página web de cada profesional: su sitio, su Linktree, su ficha de
 * Doctoralia o donde tenga la agenda. Sale como botón en su perfil.
 *
 * Solo se agrega una columna: lo cargado no se toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('sitio_web', 255)->nullable()->after('tiktok');
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropColumn('sitio_web');
        });
    }
};
