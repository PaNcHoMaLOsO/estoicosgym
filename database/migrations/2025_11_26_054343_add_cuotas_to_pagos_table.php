<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Cantidad total de cuotas en que se pagará la membresía
            $table->unsignedTinyInteger('cantidad_cuotas')->default(1)->comment('Total de cuotas en que se pagará');
            
            // Número de cuota actual
            $table->unsignedTinyInteger('numero_cuota')->default(1)->comment('Cuota número (ej: 1 de 3)');
            
            // Monto por cuota individual
            $table->decimal('monto_cuota', 10, 2)->nullable()->comment('Monto de cada cuota');
            
            // Fecha de vencimiento de esta cuota específica
            $table->date('fecha_vencimiento_cuota')->nullable()->comment('Fecha de vencimiento para esta cuota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['cantidad_cuotas', 'numero_cuota', 'monto_cuota', 'fecha_vencimiento_cuota']);
        });
    }
};
