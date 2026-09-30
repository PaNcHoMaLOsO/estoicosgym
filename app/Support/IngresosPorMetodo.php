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
     * La parte del primer medio, ACOTADA entre cero y el monto del pago.
     *
     * El segundo medio se saca por diferencia, así que un monto_metodo1 más
     * grande que el pago lo dejaba en negativo. Pasó de verdad: un mixto de
     * $20.000 + $10.000 corregido a $3.000 conservaba los $20.000 del primero
     * y la caja mostraba «Transferencia −$17.000». La corrección ya exige que
     * el reparto cuadre; esto es para que una fila vieja o mal guardada no
     * vuelva a pintar un medio en negativo.
     */
    private const PRIMERA_PARTE = 'CASE WHEN pagos.monto_metodo1 < 0 THEN 0'
        . ' WHEN pagos.monto_metodo1 > pagos.monto_abonado THEN pagos.monto_abonado'
        . ' ELSE pagos.monto_metodo1 END';

    /**
     * @param callable(\Illuminate\Database\Eloquent\Builder): mixed $periodo  el recorte de fechas
     * @return Collection<int, array{nombre:string,total:int,cantidad:int}>
     */
    public static function en(callable $periodo): Collection
    {
        $mixto = self::MIXTO;
        $primeraParte = self::PRIMERA_PARTE;

        $primero = Pago::ingresos()
            ->tap($periodo)
            ->selectRaw("pagos.id_metodo_pago as metodo, SUM(CASE WHEN {$mixto} THEN {$primeraParte} ELSE pagos.monto_abonado END) as total, COUNT(*) as cantidad")
            ->groupBy('pagos.id_metodo_pago')
            ->get();

        $segundo = Pago::ingresos()
            ->tap($periodo)
            ->whereRaw($mixto)
            ->selectRaw("pagos.id_metodo_pago2 as metodo, SUM(pagos.monto_abonado - {$primeraParte}) as total, COUNT(*) as cantidad")
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
