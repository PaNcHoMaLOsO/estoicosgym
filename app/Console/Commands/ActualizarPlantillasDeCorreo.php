<?php

namespace App\Console\Commands;

use App\Support\PlantillasDeFabrica;
use Illuminate\Console\Command;

/**
 * Pone las plantillas de correo nuevas —con {variables} en vez de texto de
 * muestra— sin pisar las que alguien editó.
 *
 * Una plantilla se cambia SOLO si sigue idéntica a la versión vieja de fábrica
 * (sin contar espacios ni los colores que ya cambió una migración). Las que se
 * retocaron en Configuración → Plantillas de correo se dejan y se listan, y se
 * avisa si todavía llevan texto de muestra como «Juan Pérez».
 *
 * Sin --confirmar solo mira y no escribe nada. Es un comando y no una
 * migración a propósito: así primero se ve qué va a cambiar. Se puede correr
 * las veces que se quiera; lo que ya está al día no se vuelve a tocar.
 */
class ActualizarPlantillasDeCorreo extends Command
{
    protected $signature = 'plantillas:actualizar {--confirmar : Hacerlo de verdad}';

    protected $description = 'Actualiza las plantillas de correo de fábrica que nadie editó';

    private const QUE_PASA = [
        'creada' => ['No estaba: se crea', 'Creada'],
        'actualizada' => ['Se actualiza', 'Actualizada'],
        'al_dia' => ['Ya está al día', 'Ya estaba al día'],
        'editada' => ['Editada: se deja como está', 'Editada: se dejó como está'],
    ];

    public function handle(): int
    {
        $aplicar = (bool) $this->option('confirmar');
        $informe = PlantillasDeFabrica::actualizar($aplicar);
        $columna = $aplicar ? 1 : 0;

        $this->table(
            ['Plantilla', 'Cuerpo', 'Asunto'],
            array_map(fn (array $fila) => [
                $fila['nombre'],
                self::QUE_PASA[$fila['resultado']][$columna],
                match ($fila['asunto']) {
                    'actualizado' => $aplicar ? 'Actualizado' : 'Se actualiza',
                    'al_dia' => 'Al día',
                    default => 'Editado: se deja',
                },
            ], $informe)
        );

        foreach ($informe as $fila) {
            if ($fila['muestras'] !== []) {
                $this->warn(sprintf(
                    '«%s» está editada y aún lleva texto de muestra (%s): corrígela a mano en Configuración → Plantillas de correo.',
                    $fila['nombre'],
                    implode(', ', $fila['muestras'])
                ));
            }
        }

        if (! $aplicar) {
            $this->line('');
            $this->info('No se cambió nada. Para hacerlo: php artisan plantillas:actualizar --confirmar');
        }

        return self::SUCCESS;
    }
}
