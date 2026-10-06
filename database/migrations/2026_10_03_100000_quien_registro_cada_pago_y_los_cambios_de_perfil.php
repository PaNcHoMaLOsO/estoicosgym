<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dos rastros que faltaban.
 *
 *  · Quién registró cada pago. Sin esto no había cómo dejar que recepción
 *    corrija SUS cobros de hoy sin dejarla corregir los de otro turno: lo
 *    único que se sabía era la fecha. Los pagos de antes quedan sin autor
 *    —no se sabe y no se inventa—, así que nadie con el permiso estrecho los
 *    puede tocar: siguen pidiendo «Corregir cualquier pago».
 *  · Quién cambió lo que puede hacer un perfil, y qué. Darle a recepción
 *    «Anular pagos» es una decisión que alguien tiene que poder explicar
 *    después; el historial general no sirve, porque cada fila ahí cuelga de
 *    un socio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $tabla) {
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('cambios_de_perfil', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('id_rol')->constrained('roles')->cascadeOnDelete();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->json('agregados');
            $tabla->json('quitados');
            $tabla->timestamp('created_at')->nullable();

            $tabla->index(['id_rol', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_de_perfil');

        Schema::table('pagos', function (Blueprint $tabla) {
            $tabla->dropConstrainedForeignId('id_usuario');
        });
    }
};
