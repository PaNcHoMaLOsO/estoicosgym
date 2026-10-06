<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que le faltaba a la libreta del mesón.
 *
 *  · El celular de quien no es socio, para poder recordarle la cuenta.
 *  · «Quitar» deja rastro: la línea se esconde en vez de borrarse, con quién
 *    la quitó. Un «bebida $1.500» borrado sin dejar huella es también la
 *    forma de hacer desaparecer una deuda que sí existía.
 *  · Un registro de lo que cambia una cuenta sin cobrarla: lo quitado, los
 *    cobros deshechos y las cuentas pasadas a un socio. Guarda el socio por
 *    su id (si se borran sus datos, ahí ya no queda su nombre) y el nombre
 *    escrito solo cuando no era socio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiados', function (Blueprint $tabla) {
            $tabla->string('celular', 20)->nullable()->after('nombre');
            $tabla->foreignId('id_usuario_quito')->nullable()->constrained('users');
            $tabla->softDeletes();
        });

        Schema::create('fiado_registros', function (Blueprint $tabla) {
            $tabla->id();
            // quitado · reabierto · asignado
            $tabla->string('accion', 20);
            $tabla->foreignId('id_cliente')->nullable()->constrained('clientes');
            $tabla->string('nombre', 100)->nullable();
            $tabla->string('detalle', 255);
            $tabla->unsignedInteger('monto')->default(0);
            $tabla->foreignId('id_usuario')->nullable()->constrained('users');
            $tabla->timestamp('created_at')->nullable();

            $tabla->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiado_registros');

        Schema::table('fiados', function (Blueprint $tabla) {
            $tabla->dropSoftDeletes();
            $tabla->dropConstrainedForeignId('id_usuario_quito');
            $tabla->dropColumn('celular');
        });
    }
};
