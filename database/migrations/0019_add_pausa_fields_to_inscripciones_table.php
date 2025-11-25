<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            // Campos para control de pausas
            $table->boolean('pausada')->default(false)->comment('Si está en pausa');
            $table->integer('dias_pausa')->default(0)->comment('Días que durará la pausa');
            $table->date('fecha_pausa_inicio')->nullable()->comment('Cuándo inicia la pausa');
            $table->date('fecha_pausa_fin')->nullable()->comment('Cuándo termina la pausa');
            $table->text('razon_pausa')->nullable()->comment('Motivo de la pausa');
            $table->integer('pausas_realizadas')->default(0)->comment('Cantidad de pausas hechas');
            $table->integer('max_pausas_permitidas')->default(2)->comment('Máximo de pausas permitidas por año');
            
            // Indices para optimizar búsquedas
            $table->index('pausada');
            $table->index('fecha_pausa_fin');
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropIndex(['pausada']);
            $table->dropIndex(['fecha_pausa_fin']);
            $table->dropColumn([
                'pausada',
                'dias_pausa',
                'fecha_pausa_inicio',
                'fecha_pausa_fin',
                'razon_pausa',
                'pausas_realizadas',
                'max_pausas_permitidas'
            ]);
        });
    }
};
