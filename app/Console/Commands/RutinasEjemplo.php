<?php

namespace App\Console\Commands;

use App\Support\RutinasDeEjemplo;
use Illuminate\Console\Command;

/**
 * Carga (o quita) las rutinas de la sala: el catálogo de ejercicios y las
 * variantes por objetivo, nivel y días. Ver App\Support\RutinasDeEjemplo.
 *
 * Se puede correr las veces que haga falta: los ejercicios se buscan por
 * nombre y cada rutina se rehace entera.
 */
class RutinasEjemplo extends Command
{
    protected $signature = 'rutinas:ejemplos {--quitar : Quita las rutinas de ejemplo (los ejercicios quedan)}';

    protected $description = 'Carga las rutinas de la sala (QR «Qué entrenar hoy») con sus variantes';

    public function handle(): int
    {
        if ($this->option('quitar')) {
            $this->info(RutinasDeEjemplo::quitar() . ' rutinas quitadas.');

            return self::SUCCESS;
        }

        [$ejercicios, $rutinas] = RutinasDeEjemplo::cargar();

        $this->info("{$ejercicios} ejercicios y {$rutinas} rutinas cargadas.");
        $this->line('Se ven en /rutina. Conviene que las revise el entrenador de la sala.');

        return self::SUCCESS;
    }
}
