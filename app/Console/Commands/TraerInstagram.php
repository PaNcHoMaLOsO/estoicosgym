<?php

namespace App\Console\Commands;

use App\Services\InstagramDelGimnasio;
use Illuminate\Console\Command;

/** Trae las publicaciones nuevas del Instagram del gimnasio a la portada. */
class TraerInstagram extends Command
{
    protected $signature = 'instagram:traer';

    protected $description = 'Trae las últimas publicaciones del Instagram del gimnasio a la portada de la web';

    public function handle(): int
    {
        // Sin llave no es un error: el gimnasio puede cargarlas a mano.
        if (! InstagramDelGimnasio::conectado()) {
            $this->line('Instagram no está conectado: falta la llave en Configuración → Página web.');

            return self::SUCCESS;
        }

        try {
            $nuevas = InstagramDelGimnasio::traer();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info($nuevas === 0 ? 'Sin publicaciones nuevas.' : "Publicaciones nuevas: {$nuevas}.");

        return self::SUCCESS;
    }
}
