<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cómo se entrena cada rutina y en qué se enfoca, para las preguntas de la web.
 *
 *  · `estilo`: maquinas (todo guiado), mixto (máquinas y pesas) o funcional
 *    (circuitos con el propio peso, pesa rusa y cajón).
 *  · `enfoque`: parejo (todo el cuerpo por igual) o gluteo (glúteo y pierna).
 *
 * Solo agrega columnas. Las rutinas que ya están quedan en «mixto» y
 * «parejo»; las del catálogo se marcan al correr `rutinas:ejemplos`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            if (! Schema::hasColumn('rutinas', 'estilo')) {
                $table->string('estilo', 20)->default('mixto')->after('dias_por_semana');
            }

            if (! Schema::hasColumn('rutinas', 'enfoque')) {
                $table->string('enfoque', 20)->default('parejo')->after('estilo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            $table->dropColumn(['estilo', 'enfoque']);
        });
    }
};
