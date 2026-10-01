<?php

namespace App\Console\Commands;

use App\Support\RutinasDeEjemplo;
use Illuminate\Console\Command;

/**
 * Carga (o quita) las rutinas de la sala: el catálogo de ejercicios y las
 * variantes por objetivo, nivel y días. Ver App\Support\RutinasDeEjemplo.
 *
 * Se puede correr las veces que haga falta: los ejercicios y las rutinas se
 * buscan por nombre y lo que ya está no se toca.
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

        [$ejercicios, $rutinas, $nuevas] = RutinasDeEjemplo::cargar();

        $this->info("{$ejercicios} ejercicios y {$rutinas} rutinas en el catálogo; {$nuevas} rutinas nuevas.");
        $this->line('Las que ya estaban (por nombre) quedaron como estaban.');
        $this->line('Se ven en /rutina. Conviene que las revise el entrenador de la sala.');

        return self::SUCCESS;
    }
}
