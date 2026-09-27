<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Las fotos de los socios, de la carpeta pública a la privada.
 *
 * Estaban en storage/app/public/clientes: cualquiera con el enlace veía la
 * cara de un socio sin entrar al panel. Ahora se sirven solo por el panel
 * (ver Cliente::DISCO_FOTOS). Se mueve cada archivo; la ruta en la base no
 * cambia, porque es la misma dentro de cada carpeta.
 */
return new class extends Migration
{
    public function up(): void
    {
        $publica = Storage::disk('public');
        $privada = Storage::disk('local');

        DB::table('clientes')->whereNotNull('foto_perfil')->pluck('foto_perfil')->each(function (string $ruta) use ($publica, $privada) {
            if ($publica->exists($ruta) && ! $privada->exists($ruta)) {
                $privada->put($ruta, $publica->get($ruta));
                $publica->delete($ruta);
            }
        });
    }

    public function down(): void
    {
        $publica = Storage::disk('public');
        $privada = Storage::disk('local');

        DB::table('clientes')->whereNotNull('foto_perfil')->pluck('foto_perfil')->each(function (string $ruta) use ($publica, $privada) {
            if ($privada->exists($ruta) && ! $publica->exists($ruta)) {
                $publica->put($ruta, $privada->get($ruta));
                $privada->delete($ruta);
            }
        });
    }
};
