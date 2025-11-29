<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar estado 105 "Cambiada" para tracking de cambios de plan
     */
    public function up(): void
    {
        // Verificar si el estado 105 ya existe
        $exists = DB::table('estados')->where('codigo', 105)->exists();
        
        if (!$exists) {
            DB::table('estados')->insert([
                'codigo' => 105,
                'nombre' => 'Cambiada',
                'descripcion' => 'Membresía cambiada a otro plan (upgrade/downgrade)',
                'categoria' => 'membresia',
                'color' => 'info',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('estados')->where('codigo', 105)->delete();
    }
};
