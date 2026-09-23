<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La cotización de un taller: lo que se le manda al colegio ANTES del mes.
 *
 * ES EL PAPEL CON EL QUE EMPIEZA TODO. El colegio pide «cuánto sale el mes de
 * abril», y se le manda una cotización con las horas que tocan y el total. Se
 * hacía en un Word que se copiaba del mes anterior y se le cambiaban a mano el
 * número, las fechas y la cifra —cotización N° 38, N° 77…—, contando antes las
 * clases con el calendario al lado.
 *
 * LO QUE COTIZA NO ES FIJO, y por eso esto guarda las clases una por una en vez
 * de una cifra: el colegio suspende una semana, hay feriado, se corre un
 * horario. Con el detalle guardado, quitar dos clases y volver a mandar la
 * cotización es marcar dos casillas; con una cifra a secas, hay que rehacer la
 * cuenta entera y volver a confiar en ella.
 *
 * NO ES EL COBRO. La cotización dice lo que se espera cobrar; el cobro
 * —`cobros_taller`— se cierra al final del mes con las clases que de verdad se
 * hicieron. Que no coincidan es lo normal, y es justo lo que hay que poder ver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones_taller', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->foreignId('id_taller')->constrained('talleres')->cascadeOnDelete();
            // El número que va en el papel. Lo lleva el gimnasio, no el
            // sistema: viene de una serie que ya iba por el 77 en abril de
            // 2026, así que se propone el siguiente y se puede corregir.
            $tabla->unsignedInteger('numero');
            // El mes que se cotiza —«abril de 2026»—, que no es el día en que
            // se escribe: la de abril se manda en marzo.
            $tabla->string('periodo', 7)->nullable();
            $tabla->date('fecha');
            // Hasta cuándo vale el precio. En las del gimnasio siempre ha sido
            // un mes, y esa es la propuesta.
            $tabla->date('valido_hasta');
            // Tal cual va escrito en el papel: «Uso instalaciones deportivas
            // para clase grupal».
            $tabla->string('descripcion', 200);
            // Con IVA incluido, como se acuerda y como se habla.
            $tabla->unsignedInteger('precio_hora');
            $tabla->decimal('horas', 6, 2);
            $tabla->unsignedInteger('total');
            $tabla->unsignedInteger('neto');
            $tabla->unsignedInteger('iva');
            // Las clases cotizadas, una por una:
            // [{"fecha":"2026-04-03","detalle":"15:00 a 16:00","horas":1,"incluida":true}, …]
            // Guardadas aquí y no leídas del horario a propósito: el horario de
            // hoy no dice qué se cotizó en marzo.
            $tabla->json('detalle')->nullable();
            // borrador · enviada · aceptada · rechazada. Sirve para saber qué
            // se mandó y qué quedó a medias.
            $tabla->string('estado', 20)->default('borrador');
            $tabla->text('notas')->nullable();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->timestamps();
            $tabla->softDeletes();

            $tabla->index(['id_taller', 'periodo']);
            $tabla->index('numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones_taller');
    }
};
