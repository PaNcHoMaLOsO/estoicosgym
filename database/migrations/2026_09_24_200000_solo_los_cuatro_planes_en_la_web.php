<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * En la web salen cuatro planes: mensual, trimestral, semestral y anual.
 *
 * Lo demás —dos meses, quincena, semana, el pase de un día— son casos que se
 * arreglan en el mesón con cada persona. Siguen pudiendo venderse; lo que no
 * hacen es anunciarse.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('membresias')
            ->whereNotIn('duracion_meses', [1, 3, 6, 12])
            ->update(['en_la_web' => false]);
    }

    public function down(): void
    {
        //
    }
};
