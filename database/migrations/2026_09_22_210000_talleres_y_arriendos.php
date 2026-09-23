<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Talleres y arriendos: la otra mitad del negocio.
 *
 * EL GIMNASIO NO SOLO VENDE MEMBRESÍAS. Le arrienda la sala a un colegio para
 * sus clases grupales y le factura POR HORA a fin de mes —«20 hrs, mes de julio
 * 2026, $600.000»—. Eso vivía fuera del sistema: un horario en un Excel, el
 * calendario de Windows abierto al lado para contar los días que tocaba clase,
 * y la cifra escrita a mano en la cotización. Contar a mano una vez al mes es
 * justo donde se pierde una hora, y una hora son treinta mil pesos.
 *
 * TRES PIEZAS:
 *  · La institución: a quién se le factura, con su RUT y su giro.
 *  · El taller: qué se le presta y a cuánto la hora, con el horario semanal.
 *  · Las horas: una fila por clase. Se proponen desde el horario y se corrigen
 *    —hubo feriado, se suspendió—, que es más rápido y menos peligroso que
 *    contarlas.
 *  · El cobro del mes: las horas cerradas, con su neto y su IVA.
 *
 * ESTO NO EMITE FACTURAS. La factura electrónica la timbra el SII a través de
 * un proveedor; aquí se prepara lo que hay que escribir en ella y queda
 * anotado qué se cobró, cuándo y si lo pagaron.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->string('nombre', 160);
            $tabla->string('rut', 20)->nullable();
            $tabla->string('giro', 120)->nullable();
            $tabla->string('direccion', 180)->nullable();
            $tabla->string('comuna', 80)->nullable();
            $tabla->string('contacto_nombre', 120)->nullable();
            $tabla->string('contacto_email', 120)->nullable();
            $tabla->string('contacto_telefono', 30)->nullable();
            $tabla->text('observaciones')->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->timestamps();
            $tabla->softDeletes();
        });

        Schema::create('talleres', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->foreignId('id_institucion')->constrained('instituciones')->cascadeOnDelete();
            $tabla->string('nombre', 160);
            // Lo que se escribe en la factura, tal cual: «USO INSTALACIONES
            // PARA CLASE GRUPAL». Sale distinto del nombre interno.
            $tabla->string('descripcion_factura', 200)->nullable();
            // CON IVA INCLUIDO, que es como se acuerda y como se habla: «la
            // hora sale treinta mil». El neto se calcula al cobrar.
            $tabla->unsignedInteger('precio_hora');
            // El horario semanal: {"lunes":[["15:30","16:30"]], …}. De aquí
            // salen propuestas las clases de cada mes.
            $tabla->json('horario')->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->timestamps();
            $tabla->softDeletes();
        });

        Schema::create('cobros_taller', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->foreignId('id_taller')->constrained('talleres')->cascadeOnDelete();
            // El mes que se cobra, no el día en que se emite: la factura de
            // julio se manda en agosto.
            $tabla->string('periodo', 7);
            $tabla->decimal('horas', 6, 2);
            $tabla->unsignedInteger('precio_hora');
            $tabla->unsignedInteger('total');
            $tabla->unsignedInteger('neto');
            $tabla->unsignedInteger('iva');
            // El folio de la factura de verdad, cuando se emita. Se anota aquí
            // para poder ir de un cobro del sistema al papel del SII.
            $tabla->string('folio', 30)->nullable();
            $tabla->date('emitido_en')->nullable();
            $tabla->date('pagado_en')->nullable();
            $tabla->text('observaciones')->nullable();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->timestamps();

            // Un mes se cobra UNA vez: sin esto, dos clics seguidos en «cerrar
            // el mes» facturarían julio dos veces.
            $tabla->unique(['id_taller', 'periodo']);
        });

        Schema::create('horas_taller', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();
            $tabla->foreignId('id_taller')->constrained('talleres')->cascadeOnDelete();
            $tabla->date('fecha');
            $tabla->decimal('horas', 5, 2);
            $tabla->string('detalle', 160)->nullable();
            // Cuando el mes se cierra, sus horas quedan enganchadas al cobro:
            // así no se pueden cambiar por detrás de una factura ya emitida.
            $tabla->foreignId('id_cobro')->nullable()->constrained('cobros_taller')->nullOnDelete();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $tabla->timestamps();

            $tabla->index(['id_taller', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horas_taller');
        Schema::dropIfExists('cobros_taller');
        Schema::dropIfExists('talleres');
        Schema::dropIfExists('instituciones');
    }
};
