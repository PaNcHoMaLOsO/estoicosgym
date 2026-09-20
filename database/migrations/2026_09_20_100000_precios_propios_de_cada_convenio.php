<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada convenio puede tener su propio precio para un plan.
 *
 * Hasta ahora el «precio con convenio» era UNO SOLO por plan: la mensualidad
 * costaba 25.000 con convenio, viniera de donde viniera la persona. Pero los
 * clubes deportivos —fútbol, básquetbol— negocian el suyo: uno paga 10.000,
 * otro 15.000, otro 20.000 por el mismo plan. Con un único precio, cobrarles
 * bien obligaba a escribir un descuento a mano cada vez, y un descuento a mano
 * no lo comprueba nadie.
 *
 * El precio del plan y el «con convenio» de siempre NO se tocan: siguen siendo
 * el precio general y el de quien trae un convenio sin trato propio. Esta tabla
 * guarda solo las excepciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * El tipo de convenio pasa de lista cerrada a texto.
         *
         * Hacía falta un tipo más —club deportivo—, y agregar un valor a una
         * lista cerrada obliga a reescribir la columna entera en MySQL y no se
         * puede en SQLite, donde corren las pruebas. Lo que vale como tipo lo
         * decide la validación del formulario, que es donde se puede leer.
         */
        Schema::table('convenios', function (Blueprint $table) {
            $table->string('tipo', 40)->change();
        });

        Schema::create('convenio_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_convenio')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('id_membresia')->constrained('membresias')->cascadeOnDelete();
            $table->unsignedInteger('precio');
            // Lo acordado, en una línea: «3 veces por semana, lunes a viernes».
            $table->string('condicion', 120)->nullable();
            $table->timestamps();

            // Un precio por convenio y plan: dos filas para lo mismo dejarían
            // el cobro a suerte de cuál se leyera primero.
            $table->unique(['id_convenio', 'id_membresia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_precios');
    }
};
