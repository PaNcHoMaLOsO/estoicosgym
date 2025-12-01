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
        Schema::table('membresias', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_pausas')->default(3)->after('duracion_dias')
                  ->comment('Número máximo de pausas permitidas por inscripción');
        });

        // Actualizar membresías existentes con valores sugeridos según duración
        DB::table('membresias')->where('duracion_meses', '>=', 12)->update(['max_pausas' => 3]); // Anual+
        DB::table('membresias')->where('duracion_meses', 6)->update(['max_pausas' => 2]);         // Semestral
        DB::table('membresias')->where('duracion_meses', 3)->update(['max_pausas' => 1]);         // Trimestral
        DB::table('membresias')->where('duracion_meses', 1)->update(['max_pausas' => 1]);         // Mensual
        DB::table('membresias')->where('duracion_meses', 0)->update(['max_pausas' => 0]);         // Pase Diario

        // Actualizar inscripciones existentes con el max_pausas de su membresía
        DB::statement("
            UPDATE inscripciones i
            INNER JOIN membresias m ON i.id_membresia = m.id
            SET i.max_pausas_permitidas = m.max_pausas
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropColumn('max_pausas');
        });
    }
};
