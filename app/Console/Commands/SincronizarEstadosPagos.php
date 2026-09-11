<?php

namespace App\Console\Commands;

use App\Enums\EstadosCodigo;
use App\Models\Inscripcion;
use Illuminate\Console\Command;

/**
 * Vuelve a cuadrar el saldo y el estado de los pagos de cada membresía vigente.
 *
 * EL MODELO MANDA. Esta tarea tenía reglas propias y ninguna servía: su primera
 * consulta pedía una columna que no existe —fecha_vencimiento_cuota— y se caía
 * en cada corrida. Si hubiera llegado más lejos, marcaba «vencido» cada pago
 * parcial con la cuota pasada, y los informes de ingresos solo cuentan los
 * pagados y los parciales: la plata ya cobrada se habría ido de los informes.
 *
 * Ahora hace exactamente lo mismo que cada corrección de un pago:
 * Inscripcion::recalcularSusPagos(). Donde ya cuadra no escribe nada, así que
 * pasar todas las noches no cuesta ni ensucia; donde no cuadra —un pago tocado
 * a mano en la base, una pantalla vieja que no recalculaba— lo deja bien.
 */
class SincronizarEstadosPagos extends Command
{
    protected $signature = 'pagos:sincronizar-estados {--dry-run : Contar lo que se corregiría, sin guardar nada}';

    protected $description = 'Vuelve a cuadrar el saldo y el estado de los pagos de cada membresía vigente';

    /** Las membresías cuyo saldo todavía importa. */
    private const VIGENTES = [
        EstadosCodigo::INSCRIPCION_ACTIVA,
        EstadosCodigo::INSCRIPCION_PAUSADA,
        EstadosCodigo::INSCRIPCION_VENCIDA,
    ];

    public function handle(): int
    {
        $guardar = ! $this->option('dry-run');
        $revisadas = 0;
        $membresias = 0;
        $pagos = 0;

        Inscripcion::whereIn('id_estado', self::VIGENTES)
            ->whereHas('pagos')
            ->chunkById(200, function ($inscripciones) use ($guardar, &$revisadas, &$membresias, &$pagos) {
                foreach ($inscripciones as $inscripcion) {
                    $revisadas++;
                    $descuadrados = $inscripcion->recalcularSusPagos($guardar);

                    if ($descuadrados > 0) {
                        $membresias++;
                        $pagos += $descuadrados;
                    }
                }
            });

        $this->info(sprintf(
            'Revisadas %d membresías%s: %s',
            $revisadas,
            $guardar ? '' : ' (sin guardar)',
            $pagos === 0
                ? 'todos los pagos cuadran.'
                : sprintf('%d pagos de %d membresías %s.', $pagos, $membresias, $guardar ? 'corregidos' : 'se corregirían')
        ));

        return self::SUCCESS;
    }
}
