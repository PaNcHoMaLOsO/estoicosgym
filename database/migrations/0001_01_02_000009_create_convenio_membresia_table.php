<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Simplificar tabla convenios - eliminar descuentos (van en precios_membresias)
        // Los descuentos ahora se manejan en la tabla precios_membresias con el campo precio_convenio
        if (Schema::hasColumn('convenios', 'descuento_porcentaje')) {
            Schema::table('convenios', function (Blueprint $table) {
                $table->dropColumn(['descuento_porcentaje', 'descuento_monto']);
            });
        }
    }

    public function down(): void
    {
        // Recrear descuentos en convenios para reverse (si es necesario)
        if (!Schema::hasColumn('convenios', 'descuento_porcentaje')) {
            Schema::table('convenios', function (Blueprint $table) {
                $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('tipo');
                $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
            });
        }
    }
};
