<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Pagos del panel nuevo (Inertia + React).
 */
class PagoController extends Controller
{
    /** Codigos de la tabla `estados` para la categoria pago. */
    private const PAGADO = 201;
    private const PARCIAL = 202;

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');

        $filtro = (string) $request->query('filtro', '');
        $orden = (string) $request->query('orden', '');
        $hoy = Carbon::today();
        // El segundo medio de un pago mixto no tiene relación en el modelo: se
        // busca aquí por su id, una sola vez para toda la página.
        $metodos = \App\Models\MetodoPago::pluck('nombre', 'id');

        $pagos = Pago::query()
            ->with([
                'cliente',
                'metodoPago',
                // La membresía y lo abonado a ella: con eso la fila dice si
                // quedó pagada o cuánto falta HOY, no lo que faltaba el día
                // de ese pago.
                'inscripcion' => fn ($q) => $q->withTrashed()
                    ->with('membresia')
                    ->withSum('pagos as abonado', 'monto_abonado')
                    // Cuántas veces se cobró esta membresía: un cobro de 5.000
                    // «de 25.000» con la membresía pagada no es un error, es
                    // que hubo otro antes. La fila tiene que poder decirlo.
                    ->withCount('pagos'),
            ])
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%");
                });
            })
            ->when(is_numeric($estado), fn ($q) => $q->where('id_estado', (int) $estado))
            // Los pases diarios, en su grupo; al buscar se encuentran igual.
            ->when(
                isset(self::FILTROS[$filtro]) || $busqueda === '',
                fn ($q) => $this->filtrar($q, isset(self::FILTROS[$filtro]) ? $filtro : ''),
            )
            // Lo último cobrado arriba, salvo que se pida por monto: «¿cuál
            // fue el cobro más grande del mes?» no se responde bajando la lista.
            ->when(true, fn ($q) => match ($orden) {
                'monto_desc' => $q->orderByDesc('monto_abonado'),
                'monto_asc' => $q->orderBy('monto_abonado'),
                'antiguos' => $q->orderBy('fecha_pago'),
                default => $q->orderByDesc('fecha_pago'),
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Pago $pago) use ($metodos) {
                $cliente = $pago->cliente;
                $inscripcion = $pago->inscripcion;
                $segundo = $pago->tipo_pago === 'mixto' ? ($metodos[$pago->id_metodo_pago2] ?? null) : null;

                return [
                    'uuid' => $pago->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'fecha' => $pago->fecha_pago?->format('d/m/Y'),
                    'metodo' => $segundo
                        ? "{$pago->metodoPago?->nombre} + {$segundo}"
                        : $pago->metodoPago?->nombre,
                    // La UI muestra «Abono»; en la base ese caso se guarda como
                    // 'parcial', que es el valor del enum de la tabla pagos.
                    'tipo' => $pago->tipo_pago === 'parcial' ? 'Abono' : ucfirst((string) $pago->tipo_pago),
                    'id_estado' => $pago->id_estado,
                    'total' => (int) $pago->monto_total,
                    'cobros' => (int) ($inscripcion?->pagos_count ?? 1),
                    'abonado' => (int) $pago->monto_abonado,
                    'pendiente' => (int) $pago->monto_pendiente,
                    'membresia' => $inscripcion?->membresia?->nombre,
                    // Lo que la membresía debe HOY. null = la membresía ya no
                    // se cobra (cancelada) o no existe.
                    'debe_hoy' => $inscripcion && in_array((int) $inscripcion->id_estado, Inscripcion::ESTADOS_CON_DEUDA, true)
                        ? $inscripcion->deuda
                        : null,
                ];
            });


        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'filtros' => ['buscar' => $busqueda, 'estado' => $estado, 'filtro' => $filtro, 'orden' => $orden],
            'cantidades' => [
                'total' => $this->filtrar(Pago::query(), '')->count(),
                'hoy' => $this->filtrar(Pago::query(), 'hoy')->count(),
                'mes' => $this->filtrar(Pago::query(), 'mes')->count(),
                'abonos' => $this->filtrar(Pago::query(), 'abonos')->count(),
                'pendientes' => $this->filtrar(Pago::query(), 'pendientes')->count(),
                'pases' => $this->filtrar(Pago::query(), 'pases')->count(),
            ],
            'resumen' => [
                'recaudado_hoy' => (int) Pago::ingresos()->whereDate('fecha_pago', $hoy)->sum('monto_abonado'),
                'recaudado_mes' => (int) Pago::ingresos()->whereYear('fecha_pago', $hoy->year)
                    ->whereMonth('fecha_pago', $hoy->month)
                    ->sum('monto_abonado'),
                // Lo que queda por cobrar es la cifra que mueve a actuar.
                // De las membresías, no de los pagos: ver Inscripcion::conDeuda().
                'por_cobrar' => Inscripcion::porCobrar(),
                'completados' => Pago::where('id_estado', self::PAGADO)->count(),
            ],
        ]);
    }

    private const FILTROS = ['hoy' => 1, 'mes' => 1, 'abonos' => 1, 'pendientes' => 1, 'pases' => 1];

    /**
     * Los grupos de la lista, sin los pases diarios, que van en el suyo. Las
     * cifras de arriba (lo que entró hoy y en el mes) SÍ los cuentan: esa
     * plata entró igual.
     */
    private function filtrar($consulta, string $filtro)
    {
        $hoy = Carbon::today();
        $esPase = fn ($q) => $q->soloPases();

        if ($filtro === 'pases') {
            return $consulta->whereHas('inscripcion', $esPase);
        }

        $consulta->whereDoesntHave('inscripcion', $esPase);

        return match ($filtro) {
            'hoy' => $consulta->whereDate('fecha_pago', $hoy),
            'mes' => $consulta->whereBetween('fecha_pago', [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()]),
            'abonos' => $consulta->where('id_estado', 202),
            'pendientes' => $consulta->where('id_estado', 200),
            default => $consulta,
        };
    }
}
