<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Cada clase con su propia página: /clases/judo.
 *
 * La dirección sale del nombre, como la de los especialistas, y las viejas se
 * guardan para redirigir si se le corrige el nombre: ya pueden estar
 * compartidas por WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clases', function (Blueprint $tabla) {
            $tabla->string('slug', 120)->nullable()->unique()->after('nombre');
            $tabla->text('slugs_anteriores')->nullable()->after('slug');
        });

        // Las que ya existen, con su dirección desde ya.
        $usados = [];
        foreach (DB::table('clases')->orderBy('id')->get(['id', 'nombre']) as $clase) {
            $base = Str::slug($clase->nombre) ?: 'clase';
            $slug = $base;
            for ($n = 2; in_array($slug, $usados, true); $n++) {
                $slug = "{$base}-{$n}";
            }
            $usados[] = $slug;

            DB::table('clases')->where('id', $clase->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('clases', function (Blueprint $tabla) {
            $tabla->dropUnique(['slug']);
            $tabla->dropColumn(['slug', 'slugs_anteriores']);
        });
    }
};
