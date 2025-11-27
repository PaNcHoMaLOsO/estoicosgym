<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero, desactivar chequeos de FK
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar tabla actual (sin truncate por las FK)
        DB::table('metodos_pago')->delete();
        
        // Reactivar chequeos de FK
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Refactorizar tabla
        Schema::table('metodos_pago', function (Blueprint $table) {
            // Eliminar campo descripción si existe para simplificar
            if (Schema::hasColumn('metodos_pago', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            
            // Agregar campo codigo único
            if (!Schema::hasColumn('metodos_pago', 'codigo')) {
                $table->string('codigo', 20)->unique()->after('id')->comment('efectivo, tarjeta, transferencia, otro');
            }
        });
        
        // Insertar 4 métodos de pago simplificados
        DB::table('metodos_pago')->insert([
            [
                'codigo' => 'efectivo',
                'nombre' => 'Efectivo',
                'requiere_comprobante' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'tarjeta',
                'nombre' => 'Débito/Crédito',
                'requiere_comprobante' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'transferencia',
                'nombre' => 'Transferencia',
                'requiere_comprobante' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'otro',
                'nombre' => 'Otro',
                'requiere_comprobante' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        // Eliminar métodos agregados (mantener tabla intacta)
        DB::table('metodos_pago')->whereIn('codigo', ['efectivo', 'tarjeta', 'transferencia', 'otro'])->delete();
        
        Schema::table('metodos_pago', function (Blueprint $table) {
            if (Schema::hasColumn('metodos_pago', 'codigo')) {
                $table->dropUnique(['codigo']);
                $table->dropColumn('codigo');
            }
            
            // Restaurar descripción si fue eliminada
            if (!Schema::hasColumn('metodos_pago', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
        });
    }
};
