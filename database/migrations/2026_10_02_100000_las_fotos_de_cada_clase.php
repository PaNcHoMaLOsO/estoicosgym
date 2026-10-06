<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Más fotos por clase: la principal sigue en «imagen» (la tarjeta, el
 * calendario y lo que sale al compartir) y estas forman la galería de su
 * página. Una lista de rutas del disco public, en el orden en que se subieron.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clases', function (Blueprint $tabla) {
            $tabla->text('fotos')->nullable()->after('imagen');
        });
    }

    public function down(): void
    {
        Schema::table('clases', function (Blueprint $tabla) {
            $tabla->dropColumn('fotos');
        });
    }
};
