<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->primary();
            $table->uuid('uuid')->unique()->comment('UUID único para identificación externa');
            $table->string('run_pasaporte', 20)->nullable()->unique()->comment('NULL para indocumentados');
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50)->nullable();
            $table->string('celular', 20);
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('contacto_emergencia', 100)->nullable();
            $table->string('telefono_emergencia', 20)->nullable();
            $table->unsignedInteger('id_convenio')->nullable()->comment('Convenio asociado al cliente');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
            $table->index('run_pasaporte');
            $table->index(['nombres', 'apellido_paterno']);
            $table->index('id_convenio');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
