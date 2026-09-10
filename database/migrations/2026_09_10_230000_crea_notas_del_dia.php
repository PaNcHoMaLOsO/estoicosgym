<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El bloc de notas del mesón.
 *
 * Las cosas que hay que hacer hoy y que no caben en ninguna otra tabla: llamar
 * a un socio, avisar al técnico, que falta papel. Hasta ahora se apuntaban en
 * un papel al lado del teclado y se perdían con el turno.
 *
 * ES COMPARTIDA, no una libreta por usuario: en el mesón se turnan varias
 * personas y lo que deja escrito la de la mañana tiene que verlo la de la
 * tarde. Por eso se guarda quién escribió cada nota y quién la dio por hecha:
 * no para vigilar, sino para poder preguntarle si algo quedó a medias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();

            // Corta a proposito: esto es un recordatorio, no un documento. Lo
            // que necesite mas espacio va en las observaciones de su ficha.
            $tabla->string('texto', 280);

            $tabla->boolean('hecha')->default(false);
            $tabla->timestamp('hecha_en')->nullable();

            $tabla->foreignId('id_usuario')->constrained('users');
            // Quien la escribio y quien la tacho pueden ser personas distintas.
            $tabla->foreignId('id_usuario_hecha')->nullable()->constrained('users');

            $tabla->timestamps();

            // Se consulta siempre igual: primero las pendientes, y de las
            // hechas solo las de hoy.
            $tabla->index(['hecha', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
