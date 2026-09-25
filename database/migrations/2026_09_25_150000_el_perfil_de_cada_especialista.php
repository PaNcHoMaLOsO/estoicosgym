<?php

use App\Models\Especialista;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada especialista con su propio perfil en la web.
 *
 * La presentación iba encima de la foto, en el panel de la lista, y con más de
 * dos líneas la tapaba. Ahora el panel lleva solo la foto, la especialidad y
 * el nombre, y lo demás va en su página (/especialistas/su-nombre):
 *
 *  · `slug`: la dirección de esa página.
 *  · `descripcion` pasa de 300 caracteres a texto: en el perfil cabe una
 *    presentación de verdad.
 *  · `temas`: en qué se enfoca (nutrición deportiva, lesiones…), como etiquetas.
 *  · `modalidad`: presencial, online o las dos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->string('slug', 120)->nullable()->unique()->after('nombre');
            $tabla->text('temas')->nullable()->after('descripcion');
            $tabla->string('modalidad', 20)->nullable()->after('temas');
        });

        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->text('descripcion')->nullable()->change();
        });

        // Los que ya estaban, con su dirección.
        Especialista::query()->orderBy('id')->get()->each(fn (Especialista $e) => $e->save());
    }

    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $tabla) {
            $tabla->dropUnique(['slug']);
            $tabla->dropColumn(['slug', 'temas', 'modalidad']);
        });
    }
};
