<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Lo que se escribe en la web desde el panel: servicios, fotos, preguntas
 * frecuentes y testimonios.
 *
 * UNA tabla para los cuatro, con un «tipo»: son la misma cosa —un título, un
 * texto, a veces una imagen, un orden y un interruptor— y cuatro tablas
 * serían cuatro veces el mismo código.
 *
 * Nace con los tres servicios que la web ya mostraba, para que estrenar esto
 * no deje la página sin servicios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contenidos_web', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid('uuid')->unique();
            $tabla->string('tipo', 20)->comment('servicio, foto, pregunta, testimonio');
            $tabla->string('titulo', 150)->comment('Servicio, descripción de la foto, pregunta o nombre de quien opina');
            $tabla->text('texto')->nullable()->comment('Descripción, respuesta o el testimonio');
            $tabla->string('icono', 40)->nullable()->comment('Solo servicios: nombre del ícono');
            $tabla->string('imagen', 255)->nullable()->comment('Solo fotos: ruta dentro de storage/app/public/web/');
            $tabla->boolean('con_permiso')->default(false)->comment('Solo testimonios: la persona autorizó publicarlo');
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->boolean('activo')->default(true)->comment('Se muestra en la web');
            $tabla->timestamps();

            $tabla->index(['tipo', 'activo', 'orden']);
        });

        $ahora = now();

        DB::table('contenidos_web')->insert(array_map(fn (array $s, int $i) => $s + [
            'uuid' => (string) Str::uuid(),
            'tipo' => 'servicio',
            'orden' => $i + 1,
            'activo' => true,
            'con_permiso' => false,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ], [
            ['icono' => 'dumbbell', 'titulo' => 'Musculación', 'texto' => 'Máquinas y peso libre para entrenar la fuerza a tu ritmo.'],
            ['icono' => 'heartbeat', 'titulo' => 'Cardio', 'texto' => 'Equipos de cardio para calentar, quemar y ganar resistencia.'],
            ['icono' => 'user-check', 'titulo' => 'Orientación en sala', 'texto' => 'Te enseñamos a usar los equipos para que entrenes seguro.'],
        ], [0, 1, 2]));
    }

    public function down(): void
    {
        Schema::dropIfExists('contenidos_web');
    }
};
