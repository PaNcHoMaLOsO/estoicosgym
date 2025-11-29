<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos para el sistema de pausas de membresías
     */
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            // Indica si la inscripción está actualmente pausada
            $table->boolean('pausada')->default(false)->after('observaciones');
            
            // Días de pausa seleccionados (7, 14, 30 o null para indefinida)
            $table->unsignedSmallInteger('dias_pausa')->nullable()->after('pausada');
            
            // Fechas de inicio y fin de la pausa
            $table->date('fecha_pausa_inicio')->nullable()->after('dias_pausa');
            $table->date('fecha_pausa_fin')->nullable()->after('fecha_pausa_inicio');
            
            // Razón de la pausa (obligatoria para pausas indefinidas)
            $table->text('razon_pausa')->nullable()->after('fecha_pausa_fin');
            
            // Indica si es una pausa indefinida (hasta nuevo aviso)
            $table->boolean('pausa_indefinida')->default(false)->after('razon_pausa');
            
            // Control de límite de pausas
            $table->unsignedTinyInteger('pausas_realizadas')->default(0)->after('pausa_indefinida');
            $table->unsignedTinyInteger('max_pausas_permitidas')->default(2)->after('pausas_realizadas');
            
            // Días originales que se agregarán al vencimiento al reanudar
            $table->unsignedSmallInteger('dias_compensacion')->default(0)->after('max_pausas_permitidas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn([
                'pausada',
                'dias_pausa',
                'fecha_pausa_inicio',
                'fecha_pausa_fin',
                'razon_pausa',
                'pausa_indefinida',
                'pausas_realizadas',
                'max_pausas_permitidas',
                'dias_compensacion',
            ]);
        });
    }
};
