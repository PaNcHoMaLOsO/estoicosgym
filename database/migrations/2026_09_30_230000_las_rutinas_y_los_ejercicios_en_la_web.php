<?php

use App\Support\RutinasDeEjemplo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Las rutinas y los ejercicios, a la vista en la web.
 *
 *  · Cada rutina en su página, /rutinas/primeros-pasos-2-dias: el `slug` sale
 *    del nombre, y si se corrige el nombre la dirección vieja se guarda para
 *    redirigir (ya puede estar compartida).
 *  · Cada ejercicio con su imagen: una foto o un GIF corto que sube el
 *    gimnasio desde el panel, y mientras no la haya, un mapa muscular con lo
 *    que trabaja. `musculos` guarda {"principal": "pecho", "secundarios":
 *    ["triceps"]}. Los del catálogo de ejemplo se completan aquí por nombre,
 *    solo si están vacíos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejercicios', function (Blueprint $table) {
            if (! Schema::hasColumn('ejercicios', 'musculos')) {
                $table->json('musculos')->nullable()->after('zona');
            }

            if (! Schema::hasColumn('ejercicios', 'imagen')) {
                $table->string('imagen')->nullable()->after('indicacion');
            }
        });

        Schema::table('rutinas', function (Blueprint $table) {
            if (! Schema::hasColumn('rutinas', 'slug')) {
                $table->string('slug', 90)->nullable()->unique()->after('nombre');
            }

            if (! Schema::hasColumn('rutinas', 'slugs_anteriores')) {
                $table->json('slugs_anteriores')->nullable()->after('slug');
            }
        });

        foreach (RutinasDeEjemplo::MUSCULOS as $nombre => [$principal, $secundarios]) {
            DB::table('ejercicios')->where('nombre', $nombre)->whereNull('musculos')
                ->update(['musculos' => json_encode(['principal' => $principal, 'secundarios' => $secundarios])]);
        }

        $usados = DB::table('rutinas')->whereNotNull('slug')->pluck('slug')->all();

        foreach (DB::table('rutinas')->whereNull('slug')->orderBy('id')->get(['id', 'nombre']) as $rutina) {
            $base = Str::slug($rutina->nombre) ?: 'rutina';
            $slug = $base;

            for ($n = 2; in_array($slug, $usados, true); $n++) {
                $slug = "{$base}-{$n}";
            }

            $usados[] = $slug;
            DB::table('rutinas')->where('id', $rutina->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('rutinas', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'slugs_anteriores']);
        });

        Schema::table('ejercicios', function (Blueprint $table) {
            $table->dropColumn(['imagen', 'musculos']);
        });
    }
};
