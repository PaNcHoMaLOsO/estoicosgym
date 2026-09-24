<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * En PostgreSQL, la extensión que deja buscar sin tildes.
 *
 * En MySQL «hernandez» encuentra a «Hernández» sin hacer nada; en PostgreSQL
 * —el del servidor— no, y el buscador del mesón dejaba de encontrar a los
 * socios. Con `unaccent` se buscan sin tildes (ver App\Support\Parecido).
 *
 * Si el usuario de la base no tiene permiso para instalarla, no se detiene
 * nada: se busca igual sin mirar mayúsculas, y las tildes cuentan. En MySQL y
 * en SQLite esto no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        } catch (\Throwable $e) {
            logger()->warning('No se pudo instalar unaccent en PostgreSQL: las búsquedas distinguirán tildes. ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        // Se deja instalada: otra cosa podría estar usándola.
    }
};
