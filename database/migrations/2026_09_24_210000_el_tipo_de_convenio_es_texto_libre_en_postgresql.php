<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El tipo de convenio pasó de lista cerrada a texto, pero en PostgreSQL la
 * lista seguía puesta.
 *
 * En PostgreSQL un enum es un texto con una restricción CHECK, y ->change() a
 * string cambia el tipo pero deja la restricción. Resultado: «Club deportivo»,
 * el tipo que motivó el cambio, no se podía guardar en el servidor. Va en una
 * migración aparte y no corrigiendo la vieja porque el servidor puede haberla
 * corrido ya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE convenios DROP CONSTRAINT IF EXISTS convenios_tipo_check');
        }
    }

    public function down(): void
    {
        //
    }
};
