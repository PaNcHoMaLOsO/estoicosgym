<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Informes del panel nuevo (Inertia + React).
 *
 * Cubre los cuatro que se miran a diario. El constructor dinamico sigue en
 * Blade: es un formulario de cuarenta kilobytes que arma consultas a medida y
 * no se parece en nada a estas cuatro pantallas.
 *
 * TODO EL DINERO SALE DE Pago::ingresos(). Los informes de Blade sumaban solo
 * los pagos saldados y se dejaban fuera los abonos —la mitad de la caja—
 * mientras el panel de inicio los contaba: dos pantallas del mismo sistema
 * daban cifras distintas para el mismo mes.
 */
class ReporteController extends Controller
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    private const PAGO_PENDIENTE = 200;
    private const PAGO_PARCIAL = 202;

    public function index()
    {
        $hoy = Carbon::today();

        return Inertia::render('Reportes/Index', [
            'cifras' => [
                'socios' => Cliente::where('activo', true)->count(),
                'activas' => Inscripcion::where('id_estado', self::ACTIVA)->count(),
                'ingresos_mes' => (int) Pago::ingresos()
                    ->whereYear('fecha_pago', $hoy->year)
                    ->whereMonth('fecha_pago', $hoy->month)
                    ->sum('monto_abonado'),
                'por_cobrar' => (int) Pago::whereIn('id_estado', [self::PAGO_PENDIENTE, self::PAGO_PARCIAL])
                    ->sum('monto_pendiente'),
            ],
        ]);
    }

    /** Ingresos del año, desglosados por mes, forma de pago y plan. */
    public function ingresos(Request $request)
    {
        $anio = (int) $request->query('anio', Carbon::today()->year);

        /*
         * El mes se saca EN PHP, no con MONTH() de SQL.
         *
         * MONTH() y YEAR() son de MySQL: en SQLite —el de las pruebas— no
         * existen y la consulta revienta, asi que esta pantalla no se podia
         * probar. Y como es la pantalla del dinero, es justo la que mas falta
         * hace tener cubierta.
         *
         * Se traen los pagos del año y se agrupan aqui: son los de un año, no
         * los de toda la vida del gimnasio.
         */
        $totalPorMes = Pago::ingresos()
            ->whereBetween('fecha_pago', [
                Carbon::create($anio, 1, 1)->startOfDay(),
                Carbon::create($anio, 12, 31)->endOfDay(),
            ])
            ->get(['fecha_pago', 'monto_abonado'])
            ->groupBy(fn (Pago $p) => (int) Carbon::parse($p->fecha_pago)->month)
            ->map(fn ($pagos) => (object) [
                'total' => (int) $pagos->sum('monto_abonado'),
                'cantidad' => $pagos->count(),
            ]);

        // Los doce meses SIEMPRE, aunque no haya movimiento: un hueco en la
        // serie se lee como «no se cobró», y un mes que falta parece un error.
        $meses = collect(range(1, 12))->map(fn (int $mes) => [
            'mes' => Carbon::create($anio, $mes, 1)->translatedFormat('M'),
            'total' => (int) ($totalPorMes[$mes]->total ?? 0),
            'pagos' => (int) ($totalPorMes[$mes]->cantidad ?? 0),
        ]);

        $porMetodo = Pago::ingresos()
            ->selectRaw('metodos_pago.nombre, SUM(pagos.monto_abonado) as total, COUNT(*) as cantidad')
            ->join('metodos_pago', 'pagos.id_metodo_pago', '=', 'metodos_pago.id')
            ->whereYear('fecha_pago', $anio)
            ->groupBy('metodos_pago.id', 'metodos_pago.nombre')
            ->orderByDesc('total')
            ->get();

        $porMembresia = Pago::ingresos()
            ->selectRaw('membresias.nombre, SUM(pagos.monto_abonado) as total, COUNT(*) as cantidad')
            ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->whereYear('fecha_pago', $anio)
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Reportes/Ingresos', [
            'anio' => $anio,
            'anios' => $this->aniosConMovimiento(),
            'meses' => $meses,
            'total' => (int) $meses->sum('total'),
            'porMetodo' => $this->comoLista($porMetodo),
            'porMembresia' => $this->comoLista($porMembresia),
        ]);
    }

    /** Cómo se reparten las membresías y en qué estado están. */
    public function membresias()
    {
        $porPlan = Inscripcion::selectRaw('membresias.nombre, COUNT(*) as total, SUM(inscripciones.precio_final) as valor')
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->where('inscripciones.id_estado', self::ACTIVA)
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($f) => [
                'nombre' => $f->nombre,
                'total' => (int) $f->total,
                'valor' => (int) $f->valor,
            ]);

        $porEstado = Inscripcion::selectRaw('estados.nombre, estados.codigo, COUNT(*) as total')
            // El join va contra `codigo` y no contra `id`: la columna id_estado
            // guarda el CODIGO, que es lo que declara su clave foránea.
            ->join('estados', 'inscripciones.id_estado', '=', 'estados.codigo')
            ->groupBy('estados.codigo', 'estados.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($f) => [
                'nombre' => $f->nombre,
                'codigo' => (int) $f->codigo,
                'total' => (int) $f->total,
            ]);

        return Inertia::render('Reportes/Membresias', [
            'porPlan' => $porPlan,
            'porEstado' => $porEstado,
            'cifras' => [
                'activas' => Inscripcion::where('id_estado', self::ACTIVA)->count(),
                'pausadas' => Inscripcion::where('id_estado', self::PAUSADA)->count(),
                'vencidas' => Inscripcion::where('id_estado', self::VENCIDA)->count(),
            ],
        ]);
    }

    /** A quién hay que llamar esta semana. */
    public function porVencer(Request $request)
    {
        $dias = max(1, min(90, (int) $request->query('dias', 7)));
        $hoy = Carbon::today();

        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', self::ACTIVA)
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays($dias)])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function (Inscripcion $i) use ($hoy) {
                $cliente = $i->cliente;

                return [
                    'uuid' => $i->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    // Sin correo ni celular no hay forma de avisarle: ese socio
                    // hay que buscarlo a mano, y por eso se cuenta aparte.
                    'email' => $cliente?->email,
                    'celular' => $cliente?->celular,
                    'membresia' => $i->membresia?->nombre,
                    'vence' => $i->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => (int) $hoy->diffInDays($i->fecha_vencimiento, false),
                ];
            });

        return Inertia::render('Reportes/PorVencer', [
            'dias' => $dias,
            'inscripciones' => $inscripciones->values(),
            'sinContacto' => $inscripciones->filter(fn ($i) => ! $i['email'] && ! $i['celular'])->count(),
        ]);
    }

    /** Lo que el gimnasio tiene por cobrar. */
    public function pendientes()
    {
        $pagos = Pago::with(['cliente', 'inscripcion.membresia', 'metodoPago'])
            ->whereIn('id_estado', [self::PAGO_PENDIENTE, self::PAGO_PARCIAL])
            ->where('monto_pendiente', '>', 0)
            ->orderByDesc('monto_pendiente')
            ->get()
            ->map(function (Pago $p) {
                $cliente = $p->cliente;

                return [
                    'uuid' => $p->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno}")
                        : 'Socio eliminado',
                    'membresia' => $p->inscripcion?->membresia?->nombre,
                    'metodo' => $p->metodoPago?->nombre,
                    'fecha' => $p->fecha_pago?->format('d/m/Y'),
                    'total' => (int) $p->monto_total,
                    'abonado' => (int) $p->monto_abonado,
                    'pendiente' => (int) $p->monto_pendiente,
                ];
            });

        return Inertia::render('Reportes/Pendientes', [
            'pagos' => $pagos->values(),
            'total' => (int) $pagos->sum('pendiente'),
            'abonado' => (int) $pagos->sum('abonado'),
        ]);
    }

    /** Años en los que hubo movimiento, para el selector. */
    private function aniosConMovimiento(): array
    {
        // El año tambien en PHP, por lo mismo que arriba: YEAR() no existe
        // fuera de MySQL. Es una columna de fechas, no la tabla entera.
        $anios = Pago::ingresos()
            ->whereNotNull('fecha_pago')
            ->pluck('fecha_pago')
            ->map(fn ($fecha) => (int) Carbon::parse($fecha)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        // El año en curso va siempre, aunque todavía no se haya cobrado nada:
        // si no, cada 1 de enero el selector aparece sin la opción de hoy.
        $actual = Carbon::today()->year;

        if (! in_array($actual, $anios, true)) {
            array_unshift($anios, $actual);
        }

        return $anios;
    }

    /** @return \Illuminate\Support\Collection<int,array<string,mixed>> */
    private function comoLista($filas)
    {
        return $filas->map(fn ($f) => [
            'nombre' => $f->nombre,
            'total' => (int) $f->total,
            'cantidad' => (int) $f->cantidad,
        ]);
    }
}
