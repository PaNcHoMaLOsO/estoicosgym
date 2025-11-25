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
            $table->unsignedInteger('codigo')->unique()->comment('Rango: 01-99 membresias, 101-108 pagos, 200-299 convenios, 300-399 clientes');
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['general', 'membresia', 'pago', 'convenio', 'cliente']);
            $table->boolean('activo')->default(true);
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
