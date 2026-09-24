<?php

namespace App\Console\Commands;

use App\Models\ContenidoWeb;
use App\Models\Especialista;
use Illuminate\Console\Command;

/**
 * Especialistas, embajadores y testimonios DE MENTIRA, para ver cómo queda la web.
 *
 * Esas secciones no se pintan si están vacías, así que hasta cargar gente de
 * verdad no hay forma de saber cómo se verán. Esto las llena con ejemplos y
 * `--quitar` los saca, reconociéndolos por el nombre: no toca nada que se haya
 * cargado a mano.
 *
 * NO SE PUBLICAN. Un testimonio inventado en la web de un negocio es publicidad
 * engañosa, y un especialista inventado promete una atención que no existe.
 * Van sin foto, sin WhatsApp y sin Instagram a propósito: cualquier número o
 * usuario inventado puede ser de una persona real.
 */
class EjemplosDeLaWeb extends Command
{
    protected $signature = 'web:ejemplos {--quitar : Borrar los ejemplos}';

    protected $description = 'Carga (o quita con --quitar) especialistas, embajadores y testimonios de ejemplo para ver cómo se ve la web';

    private const ESPECIALISTAS = [
        ['Valentina Soto', 'Nutricionista', 'Planes de alimentación para ganar masa muscular o bajar de peso, con control mensual de medidas.'],
        ['Matías Fuentes', 'Personal trainer', 'Entrenamiento uno a uno para quien empieza de cero o vuelve después de una lesión.'],
        ['Carolina Muñoz', 'Kinesióloga deportiva', 'Evaluación postural y recuperación de lesiones de rodilla, hombro y espalda.'],
    ];

    private const EMBAJADORES = [
        ['Diego Riquelme', 'Powerlifting'],
        ['Fernanda Castro', 'Fitness bikini'],
        ['Sebastián Vera', 'Culturismo clásico'],
        ['Javiera Pino', 'CrossFit'],
    ];

    private const TESTIMONIOS = [
        ['Andrea M.', 'Llegué sin saber usar ninguna máquina y desde el primer día me enseñaron. A los tres meses ya entreno sola.'],
        ['Rodrigo P.', 'El mejor ambiente de Los Ángeles. Las máquinas están siempre funcionando y el horario me acomoda para ir después del trabajo.'],
        ['Camila V.', 'Con el convenio de la universidad me sale mucho más barato. Los profes siempre están atentos a la técnica.'],
        ['Felipe G.', 'Bajé 12 kilos en seis meses con la rutina que me armaron. Muy recomendado.'],
    ];

    public function handle(): int
    {
        if ($this->option('quitar')) {
            return $this->quitar();
        }

        foreach (self::ESPECIALISTAS as $i => [$nombre, $especialidad, $descripcion]) {
            Especialista::updateOrCreate(
                ['nombre' => $nombre, 'tipo' => 'especialista'],
                ['especialidad' => $especialidad, 'descripcion' => $descripcion, 'orden' => $i + 1, 'activo' => true]
            );
        }

        foreach (self::EMBAJADORES as $i => [$nombre, $disciplina]) {
            Especialista::updateOrCreate(
                ['nombre' => $nombre, 'tipo' => 'embajador'],
                ['especialidad' => $disciplina, 'orden' => $i + 1, 'activo' => true]
            );
        }

        $orden = (int) ContenidoWeb::where('tipo', 'testimonio')->max('orden');

        foreach (self::TESTIMONIOS as [$nombre, $texto]) {
            ContenidoWeb::updateOrCreate(
                ['tipo' => 'testimonio', 'titulo' => $nombre],
                ['texto' => $texto, 'con_permiso' => true, 'activo' => true, 'orden' => ++$orden]
            );
        }

        $this->info(count(self::ESPECIALISTAS).' especialistas, '.count(self::EMBAJADORES).' embajadores y '.count(self::TESTIMONIOS).' testimonios de ejemplo.');
        $this->warn('Son inventados: quítalos con «php artisan web:ejemplos --quitar» antes de publicar la web.');

        return self::SUCCESS;
    }

    private function quitar(): int
    {
        $especialistas = Especialista::where('tipo', 'especialista')
            ->whereIn('nombre', array_column(self::ESPECIALISTAS, 0))
            ->delete();

        $embajadores = Especialista::where('tipo', 'embajador')
            ->whereIn('nombre', array_column(self::EMBAJADORES, 0))
            ->delete();

        $testimonios = ContenidoWeb::where('tipo', 'testimonio')
            ->whereIn('titulo', array_column(self::TESTIMONIOS, 0))
            ->delete();

        $this->info("Quitados: {$especialistas} especialistas, {$embajadores} embajadores y {$testimonios} testimonios de ejemplo.");

        return self::SUCCESS;
    }
}
