<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_precios', function (Blueprint $table) {
            // Si la tabla existe pero con estructura antigua, recrear
            if (Schema::hasColumn('historial_precios', 'precio_anterior_normal')) {
                $table->dropColumn([
                    'precio_anterior_normal',
                    'precio_anterior_convenio',
                    'precio_nuevo_normal',
                    'precio_nuevo_convenio',
                    'fecha_cambio',
                    'motivo_cambio',
                    'usuario_modificador',
                ]);
            }

            // Agregar campos nuevos si no existen
            if (!Schema::hasColumn('historial_precios', 'precio_anterior')) {
                $table->decimal('precio_anterior', 10, 2)->default(0)->comment('Precio anterior');
            }
            if (!Schema::hasColumn('historial_precios', 'precio_nuevo')) {
                $table->decimal('precio_nuevo', 10, 2)->default(0)->comment('Precio nuevo');
            }
            if (!Schema::hasColumn('historial_precios', 'razon_cambio')) {
                $table->string('razon_cambio', 255)->nullable()->comment('Razón del cambio');
            }
            if (!Schema::hasColumn('historial_precios', 'usuario_cambio')) {
                $table->string('usuario_cambio', 255)->nullable()->comment('Usuario que realizó el cambio');
            }
            if (!Schema::hasColumn('historial_precios', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('historial_precios', function (Blueprint $table) {
            $table->dropColumn([
                'precio_anterior',
                'precio_nuevo',
                'razon_cambio',
                'usuario_cambio',
            ]);
        });
    }
};
