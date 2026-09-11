<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Constancia del contrato firmado y de lo que el socio autorizó.
 *
 * EL CONTRATO SE FIRMA EN PAPEL. Aquí solo queda la constancia: qué día firmó y
 * QUÉ VERSIÓN firmó. La versión es lo que de verdad hace falta: el día que se
 * cambie el contrato hay que poder saber cuál aceptó cada uno, y sin ese dato
 * la respuesta es «alguna de las dos».
 *
 * Los dos permisos van SEPARADOS a propósito. Un consentimiento sirve para una
 * finalidad: «tu foto la ve quien atiende el mesón» y «tu foto sale en nuestro
 * Instagram» son cosas distintas, y una firma para la primera no autoriza la
 * segunda. Juntarlas en una sola casilla dejaría la segunda sin valer.
 *
 * Si algún día se quiere firmar en pantalla, estas mismas columnas sirven: lo
 * que cambiaría es quién las rellena, no lo que se guarda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $tabla) {
            $tabla->string('contrato_version', 20)
                ->nullable()
                ->after('apoderado_observaciones')
                ->comment('Version del contrato que firmo. NULL = no consta que haya firmado.');

            $tabla->date('contrato_firmado_en')
                ->nullable()
                ->after('contrato_version')
                ->comment('Fecha de la firma en papel.');

            $tabla->boolean('consentimiento_imagen')
                ->default(false)
                ->after('contrato_firmado_en')
                ->comment('Autorizo su foto en la ficha interna, visible solo para el personal.');

            $tabla->boolean('consentimiento_difusion')
                ->default(false)
                ->after('consentimiento_imagen')
                ->comment('Autorizo el uso de su imagen en redes sociales o publicidad. NO lo usa este sistema.');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $tabla) {
            $tabla->dropColumn([
                'contrato_version',
                'contrato_firmado_en',
                'consentimiento_imagen',
                'consentimiento_difusion',
            ]);
        });
    }
};
