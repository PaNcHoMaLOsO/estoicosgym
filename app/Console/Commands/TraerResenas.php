<?php

namespace App\Console\Commands;

use App\Services\ResenasDeGoogle;
use Illuminate\Console\Command;

/** Trae la nota y las reseñas de Google Maps a la portada. */
class TraerResenas extends Command
{
    protected $signature = 'resenas:traer';

    protected $description = 'Trae la nota y las reseñas de la ficha de Google Maps a la portada de la web';

    public function handle(): int
    {
        // Sin llave no es un error: quedan los testimonios cargados a mano.
        if (! ResenasDeGoogle::conectado()) {
            $this->line('Google no está conectado: falta la llave en Configuración → Página web.');

            return self::SUCCESS;
        }

        try {
            $enPortada = ResenasDeGoogle::traer();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Reseñas en la portada: {$enPortada}.");

        return self::SUCCESS;
    }
}
