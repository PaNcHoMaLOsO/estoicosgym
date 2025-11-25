<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->string('nombre', 100)->unique()->comment('Ej: INACAP, Cruz Verde, Falabella');
            $table->enum('tipo', ['institucion_educativa', 'empresa', 'organizacion', 'otro']);
            $table->text('descripcion')->nullable();
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('contacto_telefono', 20)->nullable();
            $table->string('contacto_email', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('tipo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
