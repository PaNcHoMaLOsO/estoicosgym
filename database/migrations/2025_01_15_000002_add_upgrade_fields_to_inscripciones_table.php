<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Campos para tracking de cambios de plan (upgrades/downgrades)
     */
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            // Referencia a inscripción anterior (si es upgrade/downgrade)
            $table->foreignId('id_inscripcion_anterior')->nullable()->after('id_membresia');
            
            // Indica si esta inscripción es resultado de un cambio de plan
            $table->boolean('es_cambio_plan')->default(false)->after('id_inscripcion_anterior');
            
            // Tipo de cambio: upgrade (plan mayor) o downgrade (plan menor)
            $table->enum('tipo_cambio', ['upgrade', 'downgrade'])->nullable()->after('es_cambio_plan');
            
            // Crédito aplicado del plan anterior (monto pagado que se descuenta)
            $table->decimal('credito_plan_anterior', 10, 2)->default(0)->after('tipo_cambio');
            
            // Precio original del nuevo plan (antes de aplicar crédito)
            $table->decimal('precio_nuevo_plan', 10, 2)->nullable()->after('credito_plan_anterior');
            
            // Diferencia a pagar (precio_nuevo_plan - credito_plan_anterior)
            $table->decimal('diferencia_a_pagar', 10, 2)->nullable()->after('precio_nuevo_plan');
            
            // Fecha del cambio de plan
            $table->timestamp('fecha_cambio_plan')->nullable()->after('diferencia_a_pagar');
            
            // Motivo del cambio
            $table->text('motivo_cambio_plan')->nullable()->after('fecha_cambio_plan');

            // Índice para búsquedas
            $table->index('id_inscripcion_anterior');
            $table->index('es_cambio_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropIndex(['id_inscripcion_anterior']);
            $table->dropIndex(['es_cambio_plan']);
            
            $table->dropColumn([
                'id_inscripcion_anterior',
                'es_cambio_plan',
                'tipo_cambio',
                'credito_plan_anterior',
                'precio_nuevo_plan',
                'diferencia_a_pagar',
                'fecha_cambio_plan',
                'motivo_cambio_plan',
            ]);
        });
    }
};
