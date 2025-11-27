<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar columnas que se necesitarán para funcionalidades futuras
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Agregar id_cliente para consultas rápidas sin JOIN
            if (!Schema::hasColumn('pagos', 'id_cliente')) {
                $table->unsignedInteger('id_cliente')->nullable()->after('id_inscripcion')->comment('Denormalización para queries rápidas');
            }

            // Agregar id_membresia para análisis de ingresos por membresía
            if (!Schema::hasColumn('pagos', 'id_membresia')) {
                $table->unsignedInteger('id_membresia')->nullable()->after('id_cliente')->comment('Membresía asociada al pago');
            }

            // Agregar monto_total para cálculos de porcentaje sin sub-queries
            if (!Schema::hasColumn('pagos', 'monto_total')) {
                $table->decimal('monto_total', 10, 2)->nullable()->after('monto_abonado')->comment('Total a pagar de la inscripción');
            }

            // Agregar descuento_aplicado para tracking de descuentos
            if (!Schema::hasColumn('pagos', 'descuento_aplicado')) {
                $table->decimal('descuento_aplicado', 10, 2)->default(0)->after('monto_total')->comment('Descuento aplicado a este pago');
            }

            // Agregar periodo_inicio y periodo_fin para auditoría
            if (!Schema::hasColumn('pagos', 'periodo_inicio')) {
                $table->date('periodo_inicio')->nullable()->after('fecha_pago')->comment('Inicio del período cubierto por este pago');
            }

            if (!Schema::hasColumn('pagos', 'periodo_fin')) {
                $table->date('periodo_fin')->nullable()->after('periodo_inicio')->comment('Fin del período cubierto por este pago');
            }

            // Agregar id_metodo_pago secundario para pagos múltiples
            if (!Schema::hasColumn('pagos', 'id_metodo_pago')) {
                $table->unsignedInteger('id_metodo_pago')->nullable()->after('id_metodo_pago_principal')->comment('Método de pago secundario (para pagos mixtos)');
            }

            // Agregar índices para estas nuevas columnas
            if (!Schema::hasColumn('pagos', 'id_cliente')) {
                // Ya fue agregada arriba
            } else if ($this->indexDoesntExist('pagos', 'pagos_id_cliente_index')) {
                $table->index('id_cliente');
            }

            if (!Schema::hasColumn('pagos', 'id_membresia')) {
                // Ya fue agregada arriba
            } else if ($this->indexDoesntExist('pagos', 'pagos_id_membresia_index')) {
                $table->index('id_membresia');
            }
        });

        // Agregar foreign keys de forma segura
        try {
            Schema::table('pagos', function (Blueprint $table) {
                if (Schema::hasColumn('pagos', 'id_cliente')) {
                    $table->foreign('id_cliente')
                        ->references('id')
                        ->on('clientes')
                        ->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Ya existe la foreign key
        }

        try {
            Schema::table('pagos', function (Blueprint $table) {
                if (Schema::hasColumn('pagos', 'id_membresia')) {
                    $table->foreign('id_membresia')
                        ->references('id')
                        ->on('membresias')
                        ->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Ya existe la foreign key
        }

        try {
            Schema::table('pagos', function (Blueprint $table) {
                if (Schema::hasColumn('pagos', 'id_motivo_descuento') && !Schema::hasColumn('pagos', 'id_metodo_pago')) {
                    // Ya existe
                } else if (Schema::hasColumn('pagos', 'id_metodo_pago')) {
                    $table->foreign('id_metodo_pago')
                        ->references('id')
                        ->on('metodos_pago')
                        ->onDelete('restrict');
                }
            });
        } catch (\Exception $e) {
            // Ya existe la foreign key
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Remover foreign keys
            try {
                $table->dropForeignIfExists('pagos_id_cliente_foreign');
                $table->dropForeignIfExists('pagos_id_membresia_foreign');
                $table->dropForeignIfExists('pagos_id_metodo_pago_foreign');
            } catch (\Exception $e) {
                // Ya fue removido
            }

            // Remover índices
            $table->dropIndexIfExists('pagos_id_cliente_index');
            $table->dropIndexIfExists('pagos_id_membresia_index');

            // Remover columnas
            $table->dropColumnIfExists('id_cliente');
            $table->dropColumnIfExists('id_membresia');
            $table->dropColumnIfExists('monto_total');
            $table->dropColumnIfExists('descuento_aplicado');
            $table->dropColumnIfExists('periodo_inicio');
            $table->dropColumnIfExists('periodo_fin');
            $table->dropColumnIfExists('id_metodo_pago');
        });
    }

    /**
     * Verificar si un índice no existe
     */
    private function indexDoesntExist($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return false;
            }
        }
        return true;
    }
};
