<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las clases que da el gimnasio: judo, lucha olímpica, boxeo…
 *
 * NO SON LOS TALLERES. Un taller es una institución que arrienda horas y se
 * le factura el mes; una clase la da el gimnasio, la abre a cualquiera —socio
 * o no— y cobra una mensualidad por participar en sus horarios.
 *
 * El horario va en una columna JSON y no en una tabla aparte: son dos o tres
 * bloques por clase, se leen siempre con ella y nunca se buscan sueltos.
 * Una tabla más solo agregaría una consulta por clase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clases', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid('uuid')->unique();
            $tabla->string('nombre', 100);
            $tabla->string('descripcion', 300)->nullable();
            $tabla->string('profesor', 100)->nullable();
            // «Niños desde 8 años», «Adultos, todo nivel».
            $tabla->string('para_quien', 100)->nullable();
            // Sin precio, la web dice «Consulta el valor».
            $tabla->unsignedInteger('precio_mensual')->nullable();
            $tabla->string('imagen', 255)->nullable()->comment('Ruta dentro de storage/app/public/web/clases/');
            $tabla->json('horario')->comment('Lista de {dia, desde, hasta}');
            $tabla->string('color', 20)->comment('Uno de App\Models\Clase::COLORES');
            $tabla->boolean('activo')->default(true)->comment('Se muestra en la web');
            $tabla->integer('orden')->default(0);
            $tabla->timestamps();

            $tabla->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};
