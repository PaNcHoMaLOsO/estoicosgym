<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los puestos de especialistas y embajadores, en 1, 2, 3… cada lista por su lado.
 *
 * El orden se escribía a mano y se contaba entre las dos listas juntas: quedaron
 * embajadores 1, 2, 4, 6 y especialistas 3, 5, 7. Desde ahora el orden se lleva
 * solo; esto arregla el que ya había, respetando quién iba antes de quién.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['especialista', 'embajador'] as $tipo) {
            $ids = DB::table('especialistas')
                ->where('tipo', $tipo)
                ->orderBy('orden')
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $i => $id) {
                DB::table('especialistas')->where('id', $id)->update(['orden' => $i + 1]);
            }
        }
    }

    public function down(): void
    {
        // Nada que deshacer: el orden de antes no tenía sentido.
    }
};
