<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * El embajador sin dirección, y el especialista con la suya sin «-2».
 *
 * El embajador no tiene página propia, pero se le daba una dirección igual, y
 * quien era embajador y especialista a la vez quedaba con su perfil en
 * /especialistas/leonardo-gutierrez-2. Aquí se le quita al embajador y el
 * especialista se queda con la de su nombre; la que tenía se guarda para
 * redirigir, porque ya puede estar compartida.
 *
 * Solo datos, y nada se borra: sin vuelta atrás que hacer.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('especialistas')->where('tipo', 'embajador')->update(['slug' => null]);

        $especialistas = DB::table('especialistas')->where('tipo', '!=', 'embajador')->orderBy('id')->get(['id', 'nombre', 'slug', 'slugs_anteriores']);
        $usados = $especialistas->pluck('slug')->filter()->all();

        foreach ($especialistas as $e) {
            $base = Str::slug($e->nombre) ?: 'especialista';

            if (! $e->slug || $e->slug === $base || in_array($base, $usados, true)) {
                continue;
            }

            $anteriores = json_decode((string) $e->slugs_anteriores, true) ?: [];
            $anteriores[] = $e->slug;

            DB::table('especialistas')->where('id', $e->id)->update([
                'slug' => $base,
                'slugs_anteriores' => json_encode(array_values(array_unique($anteriores))),
            ]);

            $usados = array_values(array_diff($usados, [$e->slug]));
            $usados[] = $base;
        }
    }

    public function down(): void
    {
        //
    }
};
