<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos opcionales para sistema de cuotas y referencias
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Referencia de pago (transferencia, comprobante, etc)
            $table->string('referencia_pago', 100)->nullable()->after('id_metodo_pago');
            
            // Sistema de cuotas (opcional)
            $table->unsignedTinyInteger('cantidad_cuotas')->default(1)->after('referencia_pago');
            $table->unsignedTinyInteger('numero_cuota')->default(1)->after('cantidad_cuotas');
            $table->decimal('monto_cuota', 12, 2)->nullable()->after('numero_cuota');
            
            // Período que cubre el pago
            $table->date('periodo_inicio')->nullable()->after('monto_cuota');
            $table->date('periodo_fin')->nullable()->after('periodo_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'referencia_pago',
                'cantidad_cuotas',
                'numero_cuota',
                'monto_cuota',
                'periodo_inicio',
                'periodo_fin',
            ]);
        });
    }
};
