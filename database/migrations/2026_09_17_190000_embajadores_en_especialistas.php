<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los embajadores van en la misma tabla que los especialistas.
 *
 * Las dos son personas que salen en la web con su foto, una línea sobre ellas y
 * su Instagram o su WhatsApp. Lo que cambia es dónde salen: el especialista en
 * su página, el embajador en la portada. Con una columna basta, y así la foto y
 * los dos enlaces se siguen limpiando en UN solo sitio en vez de en dos copias
 * que se separarían a la primera corrección.
 *
 * Todo lo que ya había queda como especialista.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('tipo', 20)->default('especialista')->after('uuid')
                ->comment('especialista: sale en su página | embajador: sale en la portada');

            $tabla->index(['tipo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropIndex(['tipo', 'activo', 'orden']);
            $tabla->dropColumn('tipo');
        });
    }
};
