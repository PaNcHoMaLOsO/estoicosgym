<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Con qué se pagó lo fiado.
 *
 * La caja sabía cuánto entró del mesón pero no cuánto fue en efectivo, así que
 * el efectivo del cajón no se podía cuadrar. Queda en blanco en los cobros de
 * antes: no se sabe y no se inventa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiados', function (Blueprint $table) {
            $table->foreignId('id_metodo_pago')->nullable()->after('pagado_en')
                ->constrained('metodos_pago')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fiados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_metodo_pago');
        });
    }
};
