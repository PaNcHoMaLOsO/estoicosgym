<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Constancia de que se borraron los datos personales de un socio.
 *
 * La ley da derecho a pedir que se borren. La fila del socio NO se borra —sus
 * pagos y membresías están en las cuentas y quitarlos descuadraría todo hacia
 * atrás—: se le quita lo que dice quién era y aquí queda cuándo, quién lo hizo
 * y por qué.
 *
 * El celular pasa a aceptar vacío: era obligatorio, y a una ficha sin dueño no
 * se le puede dejar un número inventado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $tabla) {
            $tabla->timestamp('datos_borrados_en')
                ->nullable()
                ->after('activo')
                ->comment('Cuándo se borraron sus datos personales. NULL = no se han borrado.');

            // Sin clave foránea a propósito: es una constancia, y si la cuenta
            // se borra algún día, el número tiene que seguir ahí.
            $tabla->unsignedBigInteger('datos_borrados_por')
                ->nullable()
                ->after('datos_borrados_en')
                ->comment('Usuario del panel que los borró.');

            $tabla->string('datos_borrados_motivo', 20)
                ->nullable()
                ->after('datos_borrados_por')
                ->comment('solicitud o plazo');
        });

        Schema::table('clientes', function (Blueprint $tabla) {
            $tabla->string('celular', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('clientes')->whereNull('celular')->update(['celular' => '']);

        Schema::table('clientes', function (Blueprint $tabla) {
            $tabla->string('celular', 20)->nullable(false)->change();
            $tabla->dropColumn(['datos_borrados_en', 'datos_borrados_por', 'datos_borrados_motivo']);
        });
    }
};
