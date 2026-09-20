<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las rutinas que se leen con el QR de la sala.
 *
 * Quien llega y no sabe qué hacer escanea el código, dice qué busca y cuántos
 * días puede venir, y ve su semana. NO es un plan personal ni un registro de
 * entrenamientos: es el cartel de la sala, el mismo para todos, y no guarda
 * nada de quien lo mira.
 *
 * Tres tablas y no una: los ejercicios se repiten entre rutinas (el press de
 * banca es el mismo en las cuatro), y con el texto copiado en cada una, corregir
 * una indicación obligaría a buscarla en todas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Lo que hay en el gimnasio: cada máquina o movimiento, una vez.
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nombre', 80);
            // Qué trabaja: pecho, espalda, piernas, hombros, brazos, core, cardio.
            $table->string('zona', 30);
            // Con qué: maquina, mancuernas, barra, propio_peso, cardio.
            $table->string('equipo', 30);
            // Cómo se hace, en dos líneas. Lo lee alguien que está de pie
            // delante de la máquina, no en su casa.
            $table->text('indicacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['activo', 'zona']);
        });

        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nombre', 80);
            // Qué busca quien la elige: empezar, bajar_grasa, fuerza, mantener.
            $table->string('objetivo', 30);
            // Cuánto lleva entrenando: nunca, algo, hace_tiempo.
            $table->string('nivel', 30);
            $table->unsignedTinyInteger('dias_por_semana');
            $table->string('descripcion', 300)->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            // Por aquí se busca: objetivo + nivel + días es lo que responde
            // la persona en el celular.
            $table->index(['activa', 'objetivo', 'nivel', 'dias_por_semana'], 'rutinas_busqueda');
        });

        Schema::create('rutina_dias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rutina')->constrained('rutinas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->string('titulo', 60);
            $table->string('foco', 120)->nullable();
            $table->timestamps();

            $table->unique(['id_rutina', 'numero']);
        });

        Schema::create('rutina_ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dia')->constrained('rutina_dias')->cascadeOnDelete();
            $table->foreignId('id_ejercicio')->constrained('ejercicios');
            $table->unsignedTinyInteger('series')->default(3);
            // Texto y no número: «10 a 12», «30 segundos», «lo que aguantes
            // con buena técnica» son respuestas válidas.
            $table->string('repeticiones', 40);
            $table->unsignedSmallInteger('descanso_seg')->default(60);
            $table->string('nota', 200)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutina_ejercicios');
        Schema::dropIfExists('rutina_dias');
        Schema::dropIfExists('rutinas');
        Schema::dropIfExists('ejercicios');
    }
};
