<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las publicaciones de Instagram que se muestran en la portada: una foto, su
 * texto y el enlace a la publicación. La foto y el texto ya tenían columna;
 * faltaba dónde guardar el enlace.
 *
 * Solo se agrega una columna: lo cargado no se toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contenidos_web', function (Blueprint $tabla) {
            $tabla->string('enlace', 255)->nullable()->after('imagen');
        });
    }

    public function down(): void
    {
        Schema::table('contenidos_web', function (Blueprint $tabla) {
            $tabla->dropColumn('enlace');
        });
    }
};
