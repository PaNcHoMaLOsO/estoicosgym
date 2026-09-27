<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los códigos del segundo factor, cifrados y con tope de intentos.
 *
 * Se guardaban tal cual: quien pudiera leer la tabla —un respaldo, una copia
 * de la base— tenía el código vigente para entrar. Ahora se guarda su huella
 * (HMAC con la APP_KEY) y se compara esa. Y cada código aguanta 5 intentos
 * fallidos: antes el único freno era por IP, y probando desde varias se
 * podían intentar muchos.
 *
 * Los códigos que había se invalidan: duran diez minutos y no hay cómo
 * pasarlos a huella sin saber cuál era cada uno (sí se sabe, pero da igual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->string('code', 64)->change();
            $table->unsignedTinyInteger('intentos')->default(0)->after('is_used');
        });

        DB::table('verification_codes')->where('is_used', false)->update(['is_used' => true]);
    }

    public function down(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropColumn('intentos');
        });
    }
};
