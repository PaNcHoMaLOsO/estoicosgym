<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
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
            $table->unsignedBigInteger('id_convenio')->nullable()->comment('Convenio asociado al cliente');
            $table->unsignedInteger('id_estado')->nullable()->comment('Rango 400-402: estados del cliente');
            $table->text('observaciones')->nullable();
            
            // ===== CAMPOS PARA MENORES DE EDAD (Apoderado/Tutor) =====
            $table->boolean('es_menor_edad')->default(false)->comment('True si el cliente es menor de 18 años');
            $table->boolean('consentimiento_apoderado')->default(false)->comment('Checkbox de autorización firmada');
            $table->string('apoderado_nombre', 100)->nullable()->comment('Nombre completo del apoderado/tutor');
            $table->string('apoderado_rut', 20)->nullable()->comment('RUT del apoderado');
            $table->string('apoderado_telefono', 20)->nullable()->comment('Teléfono del apoderado');
            $table->string('apoderado_parentesco', 50)->nullable()->comment('Relación: Padre, Madre, Tutor, etc.');
            $table->text('apoderado_observaciones')->nullable()->comment('Notas adicionales sobre la autorización');
            
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('set null');
            $table->index('run_pasaporte');
            $table->index(['nombres', 'apellido_paterno']);
            $table->index('id_convenio');
            $table->index('id_estado');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
