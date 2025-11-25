<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('tipo')
                ->comment('Porcentaje de descuento (0-100%)');
            $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje')
                ->comment('Descuento en pesos fijos');
        });
    }

    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->dropColumn(['descuento_porcentaje', 'descuento_monto']);
        });
    }
};
