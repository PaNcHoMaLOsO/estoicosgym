<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar foreign key y columnas redundantes
            $table->dropForeign(['id_cliente']);
            $table->dropColumn([
                'id_cliente',                    // Redundante: puede obtenerse via inscripcion.id_cliente
                'monto_total',                   // Cálculado: inscripcion.precio_final
                'descuento_aplicado',            // Redundante: está en inscripcion.descuento_aplicado
                'periodo_inicio',                // Redundante: = inscripcion.fecha_inicio
                'periodo_fin',                   // Redundante: = inscripcion.fecha_vencimiento
            ]);

            // Agregar columna para agrupar cuotas relacionadas
            $table->uuid('grupo_pago')->nullable()->after('uuid')
                  ->comment('UUID para agrupar cuotas del mismo plan de pago');
            
            // Mejorar campo referencia_pago con índice unique donde sea aplicable
            $table->index(['id_metodo_pago', 'referencia_pago'], 'idx_metodo_referencia');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->unsignedInteger('id_cliente')->after('id_inscripcion')
                  ->comment('Redundante pero útil para queries');
            $table->decimal('monto_total', 10, 2)->after('id_cliente')
                  ->comment('Total a pagar');
            $table->decimal('descuento_aplicado', 10, 2)->default(0)
                  ->after('monto_pendiente');
            $table->date('periodo_inicio')->after('fecha_pago')
                  ->comment('Inicio del período cubierto');
            $table->date('periodo_fin')->after('periodo_inicio')
                  ->comment('Fin del período cubierto');

            // Restaurar foreign key
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');

            // Eliminar columnas nuevas
            $table->dropColumn('grupo_pago');
            $table->dropIndex('idx_metodo_referencia');
        });
    }
};
