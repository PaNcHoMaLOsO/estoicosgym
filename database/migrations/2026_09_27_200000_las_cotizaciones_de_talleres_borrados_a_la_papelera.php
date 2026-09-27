<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las cotizaciones de un taller que ya está en la papelera, también a la
 * papelera. Antes borrar un taller las dejaba sueltas y la pantalla de la
 * cotización se abría con los datos del taller en blanco. Ahora el modelo
 * Taller se las lleva; esto arregla las que quedaron de antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('talleres')->whereNotNull('deleted_at')->get(['id', 'deleted_at'])->each(function ($taller) {
            DB::table('cotizaciones_taller')
                ->where('id_taller', $taller->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $taller->deleted_at]);
        });
    }

    public function down(): void
    {
        // Nada: devolverlas las dejaría otra vez sueltas.
    }
};
