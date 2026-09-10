<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que la gente se lleva del mesón y paga después.
 *
 * Una barra de proteína, una bebida, un candado. Se apuntaba en una libreta y
 * se perdía; o peor, se apuntaba de memoria.
 *
 * VA APARTE DE `pagos`, y no es por comodidad. `pagos` es el dinero de las
 * membresías: de ahí salen la caja del día, los informes de ingresos y el saldo
 * de cada socio. Meter ahí una bebida de $1.500 descuadraría las tres cosas —el
 * socio aparecería debiendo su membresía cuando lo que debe es una bebida— y
 * ningún informe sabría separarlas después.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiados', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid()->unique();

            /*
             * El socio, cuando lo es.
             *
             * Va nullable a proposito: al meson tambien se acerca quien viene
             * de visita o el hermano que espera fuera. Cuando hay socio se usa
             * el, para que la deuda salga tambien en su ficha; cuando no, queda
             * el nombre a mano.
             */
            $tabla->foreignId('id_cliente')->nullable()->constrained('clientes');
            $tabla->string('nombre', 100)->nullable();

            $tabla->string('concepto', 120);
            // Entero: en pesos no hay centavos, y un decimal solo invita a
            // sumar mal.
            $tabla->unsignedInteger('monto');

            $tabla->boolean('pagado')->default(false);
            $tabla->timestamp('pagado_en')->nullable();
            $tabla->foreignId('id_usuario_cobro')->nullable()->constrained('users');

            // Quien lo apunto: si al final del dia no cuadra, se le pregunta.
            $tabla->foreignId('id_usuario')->constrained('users');

            $tabla->timestamps();

            // Se consulta siempre igual: lo que sigue debiendose.
            $tabla->index(['pagado', 'id_cliente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiados');
    }
};
