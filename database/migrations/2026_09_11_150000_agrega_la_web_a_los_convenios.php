<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los convenios, en la página pública.
 *
 * «mostrar_en_web» nace APAGADO: los convenios del catálogo no se publican
 * solos. Hay acuerdos que no se anuncian, y el catálogo trae de ejemplo
 * instituciones con las que el gimnasio no tiene nada. Publicar el logo de
 * una institución es decir que hay un convenio con ella.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenios', function (Blueprint $tabla) {
            $tabla->string('logo', 255)->nullable()->after('descripcion')
                ->comment('Ruta dentro de storage/app/public/convenios/');
            $tabla->boolean('mostrar_en_web')->default(false)->after('logo');
            $tabla->string('requisito_web', 150)->nullable()->after('mostrar_en_web')
                ->comment('Lo que se lee en la web: quién accede y con qué credencial.');
        });
    }

    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $tabla) {
            $tabla->dropColumn(['logo', 'mostrar_en_web', 'requisito_web']);
        });
    }
};
