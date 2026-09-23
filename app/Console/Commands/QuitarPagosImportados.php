<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deja los socios y sus membresías, y quita la plata que vino de las planillas.
 *
 * POR QUÉ. Al traer las planillas se le puso un pago a cada venta para que
 * nadie saliera debiendo. Pero de esos pagos solo se sabía el monto: el medio
 * —efectivo o transferencia— no venía en ninguna parte, y el sistema terminó
 * afirmando cosas que no constan. Preguntarle a mil setecientas personas con
 * qué pagaron hace dos años no se puede, así que se prefiere no decir nada
 * antes que decir algo inventado.
 *
 * QUÉ SE QUEDA. Lo que la planilla sí decía y es lo que vale: quién es cada
 * socio, qué plan tomó y entre qué fechas. Con eso se sabe cuánta gente hay,
 * quién sigue vigente y cuándo se fue cada uno.
 *
 * QUÉ SE VA. Los pagos importados y el precio de esas membresías —que se pone
 * en cero—. Si solo se borraran los pagos, cada membresía pasaría a deber su
 * precio entero y el panel abriría con mil setecientos morosos falsos, que es
 * peor que el problema que se vino a arreglar.
 *
 * SE PUEDE DESHACER: `datos:importar-planillas --con-pagos` los vuelve a crear
 * desde el CSV, que sigue guardado.
 */
class QuitarPagosImportados extends Command
{
    protected $signature = 'datos:quitar-pagos-importados {--confirmar : Hacerlo de verdad}';

    protected $description = 'Quita los pagos traídos de las planillas y deja las membresías sin precio, sin tocar a los socios';

    /** La marca que dejó la importación en cada fila que creó. */
    private const MARCA = 'Importado de %';

    public function handle(): int
    {
        $pagos = Pago::where('observaciones', 'like', self::MARCA);
        $inscripciones = Inscripcion::where('observaciones', 'like', self::MARCA)->where('precio_final', '>', 0);

        $cuantosPagos = $pagos->count();
        $suma = (int) $pagos->sum('monto_abonado');
        $cuantasMembresias = $inscripciones->count();

        $this->info($cuantosPagos.' pagos importados, $'.number_format($suma, 0, ',', '.').' en total.');
        $this->info($cuantasMembresias.' membresías importadas con precio, que quedarán en $0.');
        $this->line('Los socios y las fechas de sus membresías no se tocan.');

        if (! $this->option('confirmar')) {
            $this->warn('Esto fue solo la cuenta. Vuelve a correrlo con --confirmar para hacerlo.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($pagos, $inscripciones) {
            // Del todo y no a la papelera: son filas que el sistema se inventó
            // para poder guardar, no cobros que alguien hizo.
            $pagos->forceDelete();

            $inscripciones->update([
                'precio_base' => 0,
                'descuento_aplicado' => 0,
                'precio_final' => 0,
            ]);
        });

        $this->info('Listo. Quedan los socios y sus membresías; de plata, nada que no conste.');

        return self::SUCCESS;
    }
}
