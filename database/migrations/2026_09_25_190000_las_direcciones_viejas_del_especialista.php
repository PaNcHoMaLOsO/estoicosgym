<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las direcciones que tuvo el perfil de un especialista.
 *
 * La dirección sale del nombre, y corregir el nombre la cambia. La vieja ya
 * estaba compartida —en un WhatsApp, en Google— y quedaba en «no existe».
 * Se guardan para mandar de la vieja a la nueva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->text('slugs_anteriores')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropColumn('slugs_anteriores');
        });
    }
};
