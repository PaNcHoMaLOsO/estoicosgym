<?php

namespace App\Support;

use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Models\Pago;
use Illuminate\Support\Carbon;

/**
 * Lo que entró al gimnasio, partido por de dónde vino.
 *
 * EL GIMNASIO COBRA POR TRES LADOS y la caja contaba uno: las membresías.
 * Lo que se le factura al colegio por el arriendo de la sala y lo que se cobra
 * del fiado del mesón no aparecían en ninguna cifra, así que «entró este mes»
 * decía menos de lo que entró de verdad, y no había forma de saber cuánto
 * pesa cada parte del negocio.
 *
 *  · Membresías: los pagos de los socios, el día que se pagaron.
 *  · Talleres: lo facturado a colegios y empresas, el día que lo PAGARON —no
 *    el día que se emitió la factura—, con IVA incluido, que es lo que entra.
 *    También los de un taller que después se mandó a la papelera: esa plata
 *    ya entró, y borrar el taller no la saca del cajón.
 *  · Mesón: lo fiado que ya se cobró, el día que se cobró. Lo que se sigue
 *    debiendo no es un ingreso: va en «lo que se debe».
 *
 * SIN EL IVA DE LOS TALLERES (`$sinIva`): la factura al colegio lleva IVA y
 * ese IVA se le paga al SII, no es del gimnasio. Sin él, los talleres cuentan
 * su neto; las membresías y el mesón no cambian.
 *
 * Vive aquí y no en la caja porque lo pregunta también el informe de ingresos,
 * y con dos copias la primera corrección dejaría las dos cifras distintas.
 */
class IngresosDelNegocio
{
    /** Las tres fuentes, en el orden en que se enseñan. */
    public const FUENTES = [
        'membresias' => 'Membresías',
        'talleres' => 'Talleres y arriendos',
        'meson' => 'Mesón',
    ];

    /**
     * Lo que entró entre dos días, ambos incluidos, por fuente y en total.
     *
     * @return array{membresias:int, talleres:int, meson:int, total:int}
     */
    public static function entre(Carbon $desde, Carbon $hasta, bool $sinIva = false): array
    {
        $inicio = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->endOfDay();

        $partes = [
            'membresias' => (int) Pago::ingresos()
                ->whereBetween('fecha_pago', [$inicio, $fin])
                ->sum('monto_abonado'),
            'talleres' => (int) CobroTaller::whereNotNull('pagado_en')
                ->whereBetween('pagado_en', [$inicio->toDateString(), $fin->toDateString()])
                ->sum($sinIva ? 'neto' : 'total'),
            'meson' => (int) Fiado::where('pagado', true)
                ->whereBetween('pagado_en', [$inicio, $fin])
                ->sum('monto'),
        ];

        return $partes + ['total' => array_sum($partes)];
    }

    /**
     * Lo que entró cada día de un mes, por fuente. Tres consultas agrupadas y
     * no una por día: con treinta días serían noventa idas a la base.
     *
     * @return array<string,array{membresias:int, talleres:int, meson:int, total:int}> por «Y-m-d»
     */
    public static function porDia(Carbon $mes, bool $sinIva = false): array
    {
        $inicio = $mes->copy()->startOfMonth();
        $fin = $mes->copy()->endOfMonth();

        $membresias = Pago::ingresos()
            ->whereBetween('fecha_pago', [$inicio, $fin->copy()->endOfDay()])
            ->selectRaw('DATE(fecha_pago) as dia, SUM(monto_abonado) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $talleres = CobroTaller::whereNotNull('pagado_en')
            ->whereBetween('pagado_en', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('DATE(pagado_en) as dia, SUM(' . ($sinIva ? 'neto' : 'total') . ') as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $meson = Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$inicio, $fin->copy()->endOfDay()])
            ->selectRaw('DATE(pagado_en) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $dias = [];

        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            $clave = $dia->toDateString();
            $partes = [
                'membresias' => (int) ($membresias[$clave] ?? 0),
                'talleres' => (int) ($talleres[$clave] ?? 0),
                'meson' => (int) ($meson[$clave] ?? 0),
            ];
            $dias[$clave] = $partes + ['total' => array_sum($partes)];
        }

        return $dias;
    }

    /**
     * Con qué medio entró la plata de las membresías y del mesón, entre dos
     * días, ambos incluidos.
     *
     * LO PREGUNTAN LA CAJA Y EL INFORME DEL AÑO, y el informe contaba solo las
     * membresías: lo cobrado del fiado en efectivo no aparecía en «Efectivo»,
     * y el reparto por medio no cuadraba con el cajón. Los talleres no van: se
     * pagan siempre por transferencia y no se cuadran contra el cajón.
     *
     * Lo fiado cobrado antes de que se anotara el medio va como «Sin anotar»:
     * no se sabe y no se reparte a ojo.
     *
     * @return list<array{nombre:string, total:int, cantidad:int}>
     */
    public static function porMetodo(Carbon $desde, Carbon $hasta): array
    {
        $inicio = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->endOfDay();

        $membresias = IngresosPorMetodo::en(fn ($q) => $q->whereBetween('fecha_pago', [$inicio, $fin]));

        $meson = Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->with('metodoPago:id,nombre')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->metodoPago?->nombre ?? 'Sin anotar')
            ->map(fn ($filas, $nombre) => [
                'nombre' => $nombre,
                'total' => (int) $filas->sum('monto'),
                // Un cobro del fiado salda varias líneas de una vez.
                'cantidad' => $filas->unique(fn (Fiado $f) => $f->pagado_en?->toDateTimeString() . $f->claveDeCuenta())->count(),
            ])
            ->values();

        return $membresias->concat($meson)
            ->groupBy('nombre')
            ->map(fn ($filas, $nombre) => [
                'nombre' => $nombre,
                'total' => (int) $filas->sum('total'),
                'cantidad' => (int) $filas->sum('cantidad'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
