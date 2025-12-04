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
        Schema::table('inscripciones', function (Blueprint $table) {
            // Campo para guardar los días restantes al momento de pausar
            // Permite reanudar con la cantidad correcta de días
            $table->integer('dias_restantes_al_pausar')->nullable()->after('dias_pausa')
                ->comment('Días restantes de membresía guardados al momento de pausar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn('dias_restantes_al_pausar');
        });
    }
};
