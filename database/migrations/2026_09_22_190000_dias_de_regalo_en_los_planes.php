<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Días de regalo: los que un plan suma sobre lo que dura.
 *
 * EL GIMNASIO LOS REGALA Y NO TENÍA DÓNDE ANOTARLOS. Al anual se le daban unos
 * días de más por pagar todo junto, y eso se resolvía escribiendo un
 * vencimiento a mano en la planilla: no quedaba dicho en ninguna parte que el
 * plan los incluye, así que dependía de quién atendiera ese día.
 *
 * Van aparte de la duración a propósito. Sumarlos a los días del plan —«anual:
 * 370 días»— haría imposible contestar «¿cuánto dura de verdad?» y «¿cuánto
 * estamos regalando?», que son dos preguntas distintas y las dos se hacen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membresias', function (Blueprint $tabla) {
            $tabla->unsignedSmallInteger('dias_regalo')->default(0)->after('duracion_dias');
        });
    }

    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $tabla) {
            $tabla->dropColumn('dias_regalo');
        });
    }
};
