<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Informes guardados del constructor.
 *
 * ARMAR UN INFORME CUESTA VEINTE CLICS y el mismo informe se pide todos los
 * meses: «los socios de Santo Tomás con su celular», «lo cobrado en efectivo
 * este mes». Sin dónde guardarlo, cada vez hay que acordarse de qué columnas
 * eran y qué filtros llevaba, y lo que pasa de verdad es que se deja de usar.
 *
 * Se guarda la RECETA, no el resultado: columnas, filtros y orden. Así el
 * informe del mes que viene trae los datos del mes que viene; guardar las filas
 * sería una foto vieja que envejece sola.
 *
 * Cada uno ve los suyos. El constructor deja mirar todo lo que hay —quien entra
 * ahí ya tiene el permiso de informes—, pero la lista de informes de alguien es
 * su forma de trabajar, y mezclarlas convertiría el menú en un cajón común.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_guardados', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->string('nombre', 80);
            $tabla->string('modulo', 40);
            // La receta entera: columnas, filtros, orden y cuántas filas.
            $tabla->json('configuracion');
            $tabla->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $tabla->timestamps();

            $tabla->unique(['id_usuario', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_guardados');
    }
};
