<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Support\IngresosDelNegocio;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use App\Support\EvolucionDelNegocio;

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
                // Igual que «Todos» en Socios: sin quien solo vino por el día
                // ni las fichas con datos borrados. Contándolos, aquí decía 94
                // y la lista 92 para el mismo gimnasio.
                'socios' => Cliente::where('activo', true)
                    ->whereNull('datos_borrados_en')
                    ->whereNot(fn ($q) => $q->whereHas('inscripciones', fn ($i) => $i->soloPases())
                        ->whereDoesntHave('inscripciones', fn ($i) => $i->sinPases()))
                    ->count(),
                // Las mensualidades al día: un pase diario no es una membresía.
                'activas' => Inscripcion::sinPases()->where('id_estado', self::ACTIVA)->count(),
                // Membresías, talleres y mesón: la misma cuenta que la Caja.
                // Contaba solo las membresías, y «Ingresos del mes» decía
                // menos que «entró este mes» de la Caja para el mismo mes.
                'ingresos_mes' => IngresosDelNegocio::entre($hoy->copy()->startOfMonth(), $hoy)['total'],
                'por_cobrar' => Inscripcion::porCobrar(),
            ],
        ]);
    }

    /**
     * Cómo evoluciona el negocio: si entra más gente de la que se va.
     *
     * LOS DEMÁS INFORMES SON FOTOS —lo de este mes, lo vigente hoy— y ninguno
     * contesta lo único que decide si el gimnasio crece: cuánta gente nueva
     * entra, cuánta renueva, cuánta deja de venir y si el que entra se queda.
     * Un gimnasio que pierde diez socios al mes y gana ocho se ve idéntico a
     * uno que crece si solo se miran fotos.
     */
    public function negocio(Request $request)
    {
        $meses = min(48, max(6, (int) $request->query('meses', 24)));
        $evolucion = new EvolucionDelNegocio($meses);

        return Inertia::render('Reportes/Negocio', [
            'meses' => $meses,
            'porMes' => $evolucion->porMes(),
            'retencion' => $evolucion->retencion(),
            'porConvenio' => $evolucion->porConvenio(),
            'diasDeGracia' => EvolucionDelNegocio::DIAS_DE_GRACIA,
            // De dónde salen los datos: con la mitad viniendo de las planillas
            // viejas, la retención que se ve es la de ellas.
            'importadas' => $evolucion->importadas(),
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
         * Tampoco se traen los pagos del año a PHP para contarlos (eran miles
         * de filas): se cuentan en la base con un rango por mes —desde el día 1
         * hasta antes del 1 del mes siguiente—, que funciona igual en las dos.
         */
        $columnas = [];
        $valores = [];

        foreach (range(1, 12) as $mes) {
            $columnas[] = "COALESCE(SUM(CASE WHEN fecha_pago >= ? AND fecha_pago < ? THEN 1 ELSE 0 END), 0) AS m{$mes}";
            array_push($valores, Carbon::create($anio, $mes, 1)->startOfDay(), Carbon::create($anio, $mes, 1)->addMonth()->startOfDay());
        }

        $cuentas = (array) Pago::ingresos()
            ->whereBetween('fecha_pago', [
                Carbon::create($anio, 1, 1)->startOfDay(),
                Carbon::create($anio, 12, 31)->endOfDay(),
            ])
            ->toBase()
            ->selectRaw(implode(', ', $columnas), $valores)
            ->first();

        $totalPorMes = collect(range(1, 12))
            ->mapWithKeys(fn (int $mes) => [$mes => (object) ['cantidad' => (int) ($cuentas["m{$mes}"] ?? 0)]]);

        // Los doce meses SIEMPRE, aunque no haya movimiento: un hueco en la
        // serie se lee como «no se cobró», y un mes que falta parece un error.
        //
        // Y CADA MES PARTIDO en membresías, talleres y mesón, con su total: la
        // misma cuenta que la Caja, de App\Support\IngresosDelNegocio. El
        // total del año contaba solo las membresías.
        //
        // Los doce se suman de una vez (IngresosDelNegocio::enVarios): eran
        // doce llamadas a entre() con tres consultas cada una.
        $sumados = IngresosDelNegocio::enVarios(array_map(
            fn (int $mes) => [Carbon::create($anio, $mes, 1), Carbon::create($anio, $mes, 1)->endOfMonth()],
            range(1, 12),
        ));

        $meses = collect(range(1, 12))->map(function (int $mes) use ($anio, $totalPorMes, $sumados) {
            $inicio = Carbon::create($anio, $mes, 1);
            $partes = $sumados[$mes - 1];

            return [
                'mes' => $inicio->translatedFormat('M'),
                'mes_largo' => $inicio->translatedFormat('F'),
                'total' => $partes['total'],
                'partes' => $partes,
                'pagos' => (int) ($totalPorMes[$mes]->cantidad ?? 0),
            ];
        });

        // Los talleres, por a quién se le cobró; el mesón, por qué se vendió.
        // Lo pagado cuenta aunque el taller esté en la papelera: si no, la
        // suma por institución dejaría de cuadrar con el total del año.
        $desde = Carbon::create($anio, 1, 1)->startOfDay();
        $hasta = Carbon::create($anio, 12, 31)->endOfDay();

        $porInstitucion = CobroTaller::with('taller.institucion')
            ->whereNotNull('pagado_en')
            ->whereBetween('pagado_en', [$desde->toDateString(), $hasta->toDateString()])
            ->get()
            ->groupBy(fn (CobroTaller $c) => $c->taller?->institucion?->nombre ?? 'Sin institución')
            ->map(fn ($cobros, $nombre) => [
                'nombre' => $nombre,
                'total' => (int) $cobros->sum('total'),
                'cantidad' => $cobros->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $porConcepto = Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$desde, $hasta])
            ->get(['concepto', 'monto'])
            // «Barra (abono)» es un trozo de «Barra»: van juntas, como en frecuentes().
            ->groupBy(fn (Fiado $f) => mb_strtolower(trim(preg_replace('/ \(abono\)$/u', '', (string) $f->concepto))) ?: 'sin detalle')
            ->map(fn ($lineas) => [
                'nombre' => ucfirst(preg_replace('/ \(abono\)$/u', '', (string) $lineas->first()->concepto) ?: 'Sin detalle'),
                'total' => (int) $lineas->sum('monto'),
                'cantidad' => $lineas->count(),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Membresías y mesón, igual que la Caja: el reparto contaba solo las
        // membresías y lo cobrado del fiado no salía en ningún medio. Los
        // pagos mixtos se reparten en App\Support\IngresosPorMetodo.
        $porMetodo = collect(IngresosDelNegocio::porMetodo($desde, $hasta));

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
            'fuentes' => IngresosDelNegocio::FUENTES,
            'totalesPorFuente' => collect(array_keys(IngresosDelNegocio::FUENTES))
                ->mapWithKeys(fn (string $f) => [$f => (int) $meses->sum(fn ($m) => $m['partes'][$f])])
                ->all(),
            'porInstitucion' => $porInstitucion->all(),
            'porConcepto' => $porConcepto->all(),
            'porMetodo' => $porMetodo->all(),
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
        /*
         * UNA FILA POR MEMBRESÍA. Antes era una por pago: quien abonó dos veces
         * salía dos veces, cada una con «lo que quedaba después de ese pago»,
         * y el total sumaba la misma deuda dos veces. Quien no había pagado ni
         * una cuota no salía.
         */
        $filas = Inscripcion::conDeuda()
            ->load(['cliente', 'membresia', 'pagos.metodoPago'])
            ->map(function (Inscripcion $i) {
                $cliente = $i->cliente;
                $ultimo = $i->pagos->sortByDesc(fn (Pago $p) => sprintf('%s-%010d', $p->fecha_pago?->format('Y-m-d'), $p->id))->first();

                return [
                    'uuid' => $i->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno}")
                        : 'Socio eliminado',
                    'membresia' => $i->membresia?->nombre,
                    'metodo' => $ultimo?->metodoPago?->nombre,
                    'fecha' => $ultimo?->fecha_pago?->format('d/m/Y'),
                    'total' => (int) $i->precio_final,
                    'abonado' => (int) $i->abonado,
                    'pendiente' => $i->deuda,
                ];
            })
            ->sortByDesc('pendiente')
            ->values();

        return Inertia::render('Reportes/Pendientes', [
            'pagos' => $filas,
            'total' => (int) $filas->sum('pendiente'),
            'abonado' => (int) $filas->sum('abonado'),
        ]);
    }

    /** Años en los que hubo movimiento, para el selector. */
    private function aniosConMovimiento(): array
    {
        // El año tambien en PHP, por lo mismo que arriba: YEAR() no existe
        // fuera de MySQL. Es una columna de fechas, no la tabla entera.
        //
        // Y sin traer todas las fechas: se busca la primera y la última, y de
        // los años entre medio se pregunta en una consulta cuáles tienen algún
        // pago (un año sin cobros en medio no sale, igual que antes).
        $extremos = Pago::ingresos()
            ->whereNotNull('fecha_pago')
            ->toBase()
            ->selectRaw('MIN(fecha_pago) AS primera, MAX(fecha_pago) AS ultima')
            ->first();

        $anios = [];

        if ($extremos?->primera) {
            $candidatos = range(Carbon::parse($extremos->primera)->year, Carbon::parse($extremos->ultima)->year);
            $columnas = [];
            $valores = [];

            foreach ($candidatos as $n => $anio) {
                $columnas[] = "MAX(CASE WHEN fecha_pago >= ? AND fecha_pago < ? THEN 1 ELSE 0 END) AS a{$n}";
                array_push($valores, Carbon::create($anio, 1, 1)->startOfDay(), Carbon::create($anio + 1, 1, 1)->startOfDay());
            }

            $hay = (array) Pago::ingresos()
                ->whereNotNull('fecha_pago')
                ->toBase()
                ->selectRaw(implode(', ', $columnas), $valores)
                ->first();

            $anios = collect($candidatos)
                ->filter(fn (int $anio, int $n) => (int) ($hay["a{$n}"] ?? 0) === 1)
                ->sortDesc()
                ->values()
                ->all();
        }

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
