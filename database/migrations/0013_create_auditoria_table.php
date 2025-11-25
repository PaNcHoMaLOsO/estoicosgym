<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->string('tabla_afectada', 50);
            $table->unsignedInteger('id_registro_afectado');
            $table->enum('accion', ['INSERT', 'UPDATE', 'DELETE']);
            $table->json('datos_anteriores')->nullable()->comment('Estado previo (solo UPDATE/DELETE)');
            $table->json('datos_nuevos')->nullable()->comment('Estado nuevo (solo INSERT/UPDATE)');
            $table->unsignedInteger('usuario_id')->nullable()->comment('Futuro: ID del usuario que hizo la acción');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
            
            $table->index(['tabla_afectada', 'id_registro_afectado']);
            $table->index('fecha_hora');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
