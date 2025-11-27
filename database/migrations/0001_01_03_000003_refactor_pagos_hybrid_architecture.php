<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refactorizar tabla pagos para arquitectura híbrida
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar campos innecesarios que quedaron del refactor anterior
            if (Schema::hasColumn('pagos', 'id_cliente')) {
                $table->dropForeign(['id_cliente']);
                $table->dropColumn('id_cliente');
            }
            
            if (Schema::hasColumn('pagos', 'monto_total')) {
                $table->dropColumn('monto_total');
            }
            
            if (Schema::hasColumn('pagos', 'descuento_aplicado')) {
                $table->dropColumn('descuento_aplicado');
            }
            
            if (Schema::hasColumn('pagos', 'periodo_inicio')) {
                $table->dropColumn('periodo_inicio');
            }
            
            if (Schema::hasColumn('pagos', 'periodo_fin')) {
                $table->dropColumn('periodo_fin');
            }
        });
        
        // Agregar nuevos campos para arquitectura híbrida
        Schema::table('pagos', function (Blueprint $table) {
            // Cambiar nombre de id_metodo_pago a id_metodo_pago_principal (para ser claro)
            if (Schema::hasColumn('pagos', 'id_metodo_pago') && !Schema::hasColumn('pagos', 'id_metodo_pago_principal')) {
                $table->renameColumn('id_metodo_pago', 'id_metodo_pago_principal');
            }
            
            // Agregar campo para métodos múltiples (pagos mixtos)
            if (!Schema::hasColumn('pagos', 'metodos_pago_json')) {
                $table->json('metodos_pago_json')
                    ->nullable()
                    ->after('id_metodo_pago_principal')
                    ->comment('{"efectivo": 100, "tarjeta": 50} - Para pagos mixtos');
            }
            
            // Agregar campo booleano para cuotas
            if (!Schema::hasColumn('pagos', 'es_plan_cuotas')) {
                $table->boolean('es_plan_cuotas')
                    ->default(false)
                    ->after('referencia_pago')
                    ->comment('¿Este pago es parte de un plan de cuotas?');
            }
            
            // Hacer campos de cuotas NULLABLE (ya existen pero eran NOT NULL)
            if (Schema::hasColumn('pagos', 'numero_cuota')) {
                $table->unsignedTinyInteger('numero_cuota')
                    ->nullable()
                    ->change()
                    ->comment('Número de cuota actual (null si es abono simple)');
            }
            
            if (Schema::hasColumn('pagos', 'cantidad_cuotas')) {
                $table->unsignedTinyInteger('cantidad_cuotas')
                    ->nullable()
                    ->change()
                    ->comment('Total de cuotas (null si es abono simple)');
            }
            
            if (Schema::hasColumn('pagos', 'fecha_vencimiento_cuota')) {
                $table->date('fecha_vencimiento_cuota')
                    ->nullable()
                    ->change()
                    ->comment('Fecha de vencimiento de esta cuota (null si es abono simple)');
            }
            
            // Asegurar que grupo_pago existe y es nullable
            if (!Schema::hasColumn('pagos', 'grupo_pago')) {
                $table->uuid('grupo_pago')
                    ->nullable()
                    ->after('fecha_vencimiento_cuota')
                    ->comment('UUID para agrupar cuotas del mismo plan');
            }
            
            // Agregar índice para búsquedas de cuotas
            $table->index('es_plan_cuotas');
            $table->index('grupo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->unsignedInteger('id_cliente')
                ->after('id_inscripcion')
                ->comment('Redundante pero útil para queries');
            
            $table->decimal('monto_total', 10, 2)
                ->after('id_cliente')
                ->comment('Total a pagar');
            
            $table->decimal('descuento_aplicado', 10, 2)
                ->default(0)
                ->after('monto_pendiente');
            
            $table->date('periodo_inicio')
                ->after('fecha_pago')
                ->comment('Inicio del período cubierto');
            
            $table->date('periodo_fin')
                ->after('periodo_inicio')
                ->comment('Fin del período cubierto');
            
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('restrict');
            
            // Revertir renaming
            if (Schema::hasColumn('pagos', 'id_metodo_pago_principal')) {
                $table->renameColumn('id_metodo_pago_principal', 'id_metodo_pago');
            }
            
            // Eliminar nuevos campos
            if (Schema::hasColumn('pagos', 'metodos_pago_json')) {
                $table->dropColumn('metodos_pago_json');
            }
            
            if (Schema::hasColumn('pagos', 'es_plan_cuotas')) {
                $table->dropIndex(['es_plan_cuotas']);
                $table->dropColumn('es_plan_cuotas');
            }
            
            if (Schema::hasColumn('pagos', 'grupo_pago')) {
                $table->dropIndex(['grupo_pago']);
                $table->dropColumn('grupo_pago');
            }
            
            // Revertir campos a NOT NULL
            if (Schema::hasColumn('pagos', 'numero_cuota')) {
                $table->unsignedTinyInteger('numero_cuota')
                    ->default(1)
                    ->change();
            }
            
            if (Schema::hasColumn('pagos', 'cantidad_cuotas')) {
                $table->unsignedTinyInteger('cantidad_cuotas')
                    ->default(1)
                    ->change();
            }
            
            if (Schema::hasColumn('pagos', 'fecha_vencimiento_cuota')) {
                $table->date('fecha_vencimiento_cuota')
                    ->change();
            }
        });
    }
};
