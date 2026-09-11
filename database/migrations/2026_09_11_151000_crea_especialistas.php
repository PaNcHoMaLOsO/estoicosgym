<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los profesionales que trabajan con el gimnasio y aparecen en la web:
 * personal trainer, preparador físico, nutricionista.
 *
 * El WhatsApp y el Instagram se guardan en su forma mínima —dígitos y
 * usuario— y los enlaces los arma el sistema: lo que termina en un enlace de
 * la página pública nunca es algo escrito a mano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialistas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid('uuid')->unique();
            $tabla->string('nombre', 100);
            $tabla->string('especialidad', 100)->comment('Personal trainer, preparador físico, nutricionista...');
            $tabla->string('descripcion', 300)->nullable();
            $tabla->string('foto', 255)->nullable()->comment('Ruta dentro de storage/app/public/especialistas/');
            $tabla->string('whatsapp', 20)->nullable()->comment('Solo dígitos, con el 56 delante: 56912345678');
            $tabla->string('instagram', 60)->nullable()->comment('Solo el usuario, sin @ ni enlace');
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->boolean('activo')->default(true)->comment('Se muestra en la web');
            $tabla->timestamps();

            $tabla->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialistas');
    }
};
