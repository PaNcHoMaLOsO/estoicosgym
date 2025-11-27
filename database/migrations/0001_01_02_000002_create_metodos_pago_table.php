<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLA METODOS_PAGO
 * 
 * Tipos de métodos de pago disponibles:
 * - efectivo: Dinero en efectivo
 * - tarjeta: Tarjeta débito/crédito
 * - transferencia: Transferencia bancaria
 * - otro: Otros métodos
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            
            // Código único para identificación en API/lógica
            $table->string('codigo', 20)->unique()
                  ->comment('Identificador único: efectivo, tarjeta, transferencia, otro');
            
            // Nombre para mostrar en UI
            $table->string('nombre', 50)->unique()
                  ->comment('Nombre para mostrar en formularios');
            
            // Descripción opcional
            $table->text('descripcion')->nullable()
                  ->comment('Descripción adicional del método');
            
            // Control de comprobantes
            $table->boolean('requiere_comprobante')->default(false)
                  ->comment('¿Requiere comprobante o referencia?');
            
            // Estado
            $table->boolean('activo')->default(true)
                  ->comment('¿Está disponible para usar?');
            
            // Timestamps
            $table->timestamps();

            // ========== ÍNDICES ==========
            $table->index('activo');
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
    }
};

