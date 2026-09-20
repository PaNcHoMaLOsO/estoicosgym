<?php

namespace App\Support;

use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Support\Collection;

/**
 * Cuánto entró por cada medio de pago en un periodo.
 *
 * UN PAGO MIXTO SE REPARTE entre sus dos medios. Antes iba entero al primero:
 * 20.000 en efectivo y 10.000 por transferencia se leían como 30.000 en
 * efectivo, y la caja no cuadraba con el banco.
 *
 * Vive aquí y no dentro de una pantalla porque lo preguntan dos —la Caja por el
 * mes en curso y el informe de ingresos por el año—, y con dos copias la
 * primera corrección que se hiciera en una las dejaría distintas.
 */
class IngresosPorMetodo
{
    /** La condición que reconoce un pago repartido entre dos medios. */
    private const MIXTO = 'pagos.id_metodo_pago2 IS NOT NULL AND pagos.monto_metodo1 IS NOT NULL';

    /**
     * @param callable(\Illuminate\Database\Eloquent\Builder): mixed $periodo  el recorte de fechas
     * @return Collection<int, array{nombre:string,total:int,cantidad:int}>
     */
    public static function en(callable $periodo): Collection
    {
        $mixto = self::MIXTO;

        $primero = Pago::ingresos()
            ->tap($periodo)
            ->selectRaw("pagos.id_metodo_pago as metodo, SUM(CASE WHEN {$mixto} THEN pagos.monto_metodo1 ELSE pagos.monto_abonado END) as total, COUNT(*) as cantidad")
            ->groupBy('pagos.id_metodo_pago')
            ->get();

        $segundo = Pago::ingresos()
            ->tap($periodo)
            ->whereRaw($mixto)
            ->selectRaw('pagos.id_metodo_pago2 as metodo, SUM(pagos.monto_abonado - pagos.monto_metodo1) as total, COUNT(*) as cantidad')
            ->groupBy('pagos.id_metodo_pago2')
            ->get();

        // Los métodos desactivados también tienen que poder nombrarse: un cobro
        // viejo hecho con un medio que ya no se usa no puede quedar como
        // «Sin método» en el informe del año pasado.
        $nombres = MetodoPago::query()->withoutGlobalScopes()->pluck('nombre', 'id');

        return $primero->concat($segundo)
            ->groupBy('metodo')
            ->map(fn ($filas, $metodo) => [
                'nombre' => $nombres[$metodo] ?? 'Sin método',
                'total' => (int) $filas->sum('total'),
                'cantidad' => (int) $filas->sum('cantidad'),
            ])
            ->sortByDesc('total')
            ->values();
    }
}
