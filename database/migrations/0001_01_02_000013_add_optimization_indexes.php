<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // TABLA: CLIENTES
            Schema::table('clientes', function (Blueprint $table) {
                if (!$this->indexExists('clientes', 'clientes_run_pasaporte_index')) {
                    $table->index('run_pasaporte');
                }
                if (!$this->indexExists('clientes', 'clientes_email_index')) {
                    $table->index('email');
                }
                if (!$this->indexExists('clientes', 'clientes_activo_index')) {
                    $table->index('activo');
                }
                if (!$this->indexExists('clientes', 'clientes_id_convenio_index')) {
                    $table->index('id_convenio');
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }

        try {
            // TABLA: INSCRIPCIONES
            Schema::table('inscripciones', function (Blueprint $table) {
                if (!$this->indexExists('inscripciones', 'inscripciones_id_estado_index')) {
                    $table->index('id_estado');
                }
                if (!$this->indexExists('inscripciones', 'inscripciones_id_cliente_index')) {
                    $table->index('id_cliente');
                }
                if (!$this->indexExists('inscripciones', 'inscripciones_id_membresia_index')) {
                    $table->index('id_membresia');
                }
                if (!$this->indexExists('inscripciones', 'inscripciones_fecha_vencimiento_index')) {
                    $table->index('fecha_vencimiento');
                }
                if (!$this->indexExists('inscripciones', 'inscripciones_id_cliente_id_estado_index')) {
                    $table->index(['id_cliente', 'id_estado']);
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }

        try {
            // TABLA: PAGOS
            Schema::table('pagos', function (Blueprint $table) {
                if (!$this->indexExists('pagos', 'pagos_id_estado_index')) {
                    $table->index('id_estado');
                }
                if (!$this->indexExists('pagos', 'pagos_id_inscripcion_index')) {
                    $table->index('id_inscripcion');
                }
                if (!$this->indexExists('pagos', 'pagos_fecha_pago_index')) {
                    $table->index('fecha_pago');
                }
                if (!$this->indexExists('pagos', 'pagos_fecha_pago_id_estado_index')) {
                    $table->index(['fecha_pago', 'id_estado']);
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }

        try {
            // TABLA: PRECIOS_MEMBRESIAS
            Schema::table('precios_membresias', function (Blueprint $table) {
                if (!$this->indexExists('precios_membresias', 'precios_membresias_id_membresia_index')) {
                    $table->index('id_membresia');
                }
                if (!$this->indexExists('precios_membresias', 'precios_membresias_activo_index')) {
                    $table->index('activo');
                }
                if (!$this->indexExists('precios_membresias', 'precios_membresias_id_membresia_fecha_vigencia_desde_index')) {
                    $table->index(['id_membresia', 'fecha_vigencia_desde']);
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }

        try {
            // TABLA: ESTADOS
            Schema::table('estados', function (Blueprint $table) {
                if (!$this->indexExists('estados', 'estados_nombre_index')) {
                    $table->index('nombre');
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }

        try {
            // TABLA: MEMBRESIAS
            Schema::table('membresias', function (Blueprint $table) {
                if (!$this->indexExists('membresias', 'membresias_activo_index')) {
                    $table->index('activo');
                }
            });
        } catch (\Exception $e) {
            // Silenciar si ya existen
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada al revertir para evitar problemas
    }

    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
};
