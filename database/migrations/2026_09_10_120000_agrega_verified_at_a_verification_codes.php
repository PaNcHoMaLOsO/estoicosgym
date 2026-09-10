<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la columna `verified_at`, que el modelo daba por existente.
 *
 * VerificationCode la declaraba en $fillable y en $casts, y markAsUsed() la
 * escribia al dar por bueno un codigo — pero la migracion original nunca la
 * creo. Resultado: verificar un codigo CORRECTO terminaba en un error 1054 de
 * MySQL y devolvia un 500, asi que nadie con verificacion en dos pasos podia
 * completar el acceso.
 *
 * No se veia porque el login dejaba entrar sin segundo factor cuando el envio
 * fallaba: con el 2FA saltandose solo, esta linea no se ejecutaba nunca.
 *
 * Se crea la columna en vez de quitarla del modelo porque el dato sirve: deja
 * constancia de cuando se uso cada codigo, que es lo que se mira cuando hay que
 * revisar un acceso.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('verification_codes', 'verified_at')) {
            return;
        }

        Schema::table('verification_codes', function (Blueprint $tabla) {
            $tabla->timestamp('verified_at')->nullable()->after('is_used');
        });
    }

    public function down(): void
    {
        Schema::table('verification_codes', function (Blueprint $tabla) {
            $tabla->dropColumn('verified_at');
        });
    }
};
