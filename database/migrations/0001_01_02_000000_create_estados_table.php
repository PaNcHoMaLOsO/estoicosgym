<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->unsignedInteger('codigo')->unique()->comment('Rango: 100-199 membresías, 200-299 pagos, 300-302 convenios, 400-402 clientes, 500-504 genéricos');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['general', 'membresia', 'pago', 'convenio', 'cliente', 'generico']);
            $table->boolean('activo')->default(true);
            $table->string('color', 20)->default('secondary')->comment('Color Bootstrap: primary, success, danger, warning, info, secondary');
            $table->timestamps();

            $table->index('activo');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
