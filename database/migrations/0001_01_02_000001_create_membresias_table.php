<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa');
            $table->string('nombre', 50)->unique();
            $table->unsignedInteger('duracion_meses')->comment('Meses de duración (0 para pase diario)');
            $table->unsignedInteger('duracion_dias')->comment('0 para mensuales, 1 para pase diario, 365 para anual');
            $table->unsignedTinyInteger('max_pausas')->default(3)->comment('Número máximo de pausas permitidas por inscripción');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
