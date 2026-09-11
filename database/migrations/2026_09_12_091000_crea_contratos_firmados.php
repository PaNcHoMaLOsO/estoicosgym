<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los contratos que se mandan a firmar por correo.
 *
 * El enlace del correo NO se guarda, solo su huella: con el enlace cualquiera
 * podría firmar por el socio, y esta tabla se respalda y la mira más de una
 * persona.
 *
 * Firmado, queda el documento TAL COMO SE FIRMÓ —con la firma dibujada
 * dentro— y la huella de ese documento, que deja de cuadrar si alguien le
 * cambia una coma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid('uuid')->unique();
            $tabla->foreignId('id_cliente')->constrained('clientes');
            $tabla->foreignId('id_inscripcion')->nullable()->constrained('inscripciones')->nullOnDelete();

            $tabla->char('token_hash', 64)->unique()->comment('SHA-256 del enlace. El enlace no se guarda.');
            $tabla->string('firmante_tipo', 10)->comment('socio, o apoderado si es menor de edad');
            $tabla->string('email_destino', 150)->nullable();
            $tabla->timestamp('enviado_en')->nullable();
            $tabla->timestamp('vence_en')->nullable();
            $tabla->text('error_envio')->nullable();
            $tabla->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete()->comment('Quién lo mandó.');

            $tabla->timestamp('firmado_en')->nullable();
            $tabla->string('firmante_nombre', 150)->nullable();
            $tabla->string('firmante_rut', 20)->nullable();
            $tabla->string('ip', 45)->nullable();
            $tabla->string('navegador', 255)->nullable();
            $tabla->unsignedInteger('version_contrato')->nullable();
            $tabla->unsignedInteger('version_terminos')->nullable();
            $tabla->unsignedInteger('version_privacidad')->nullable();
            $tabla->boolean('consentimiento_imagen')->default(false);
            $tabla->boolean('consentimiento_difusion')->default(false);
            $tabla->longText('contenido')->nullable()->comment('El documento tal como se firmó, con la firma dentro.');
            $tabla->char('huella', 64)->nullable()->comment('SHA-256 de contenido.');

            $tabla->timestamp('anulado_en')->nullable()->comment('Reemplazado por otro envío, o anulado a mano.');
            $tabla->timestamp('datos_borrados_en')->nullable()->comment('Se borraron los datos del socio: queda la huella.');
            $tabla->timestamps();

            $tabla->index(['id_cliente', 'firmado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
