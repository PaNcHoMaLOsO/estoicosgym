<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los profesionales que recomienda el gimnasio, con cuándo y dónde atienden.
 *
 * La página de especialistas pasa a ser sobre todo de recomendados: nutricionistas,
 * kinesiólogos, masajistas de la ciudad que no trabajan en el gimnasio. Por eso:
 *
 *  · `vinculo`: «equipo» (trabaja en el gimnasio) o «recomendado» (externo). Los
 *    que ya estaban son del equipo; los nuevos entran como recomendados.
 *  · `dias`: qué días atiende en la ciudad (lun…dom).
 *  · `horario`: a qué hora, en texto libre («15:00 a 19:00», «solo mañanas»).
 *  · `lugar`: dónde atiende («Consulta en Colón 250», «A domicilio»).
 *
 * Solo se agregan columnas: lo cargado no se toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('vinculo', 20)->default('equipo')->after('tipo');
            $tabla->text('dias')->nullable()->after('modalidad');
            $tabla->string('horario', 100)->nullable()->after('dias');
            $tabla->string('lugar', 120)->nullable()->after('horario');
        });
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropColumn(['vinculo', 'dias', 'horario', 'lugar']);
        });
    }
};
