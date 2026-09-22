<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Inscripciones del panel nuevo (Inertia + React).
 *
 * Las acciones sobre una membresia ya vendida (pausar, reanudar, cambiar de
 * plan, traspasar) siguen en Admin\InscripcionController, lo unico que queda
 * del panel viejo.
 */
class InscripcionController extends Controller
{
    /** Codigos de la tabla `estados`: id_estado guarda el CODIGO, no el id. */
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');

        $filtro = (string) $request->query('filtro', '');
        // Por qué plan, y en qué orden. Los grupos de arriba responden «¿en qué
        // estado está?»; esto responde «¿cuál plan?» y «¿cuánto?», que son
        // otras dos preguntas y no caben como más pastillas en la misma fila.
        $plan = $request->query('plan');
        $orden = (string) $request->query('orden', '');

        $inscripciones = Inscripcion::query()
            ->with(['cliente', 'membresia'])
            // Lo abonado, sumado en la misma consulta: la columna «Pago» dice
            // cuánto debe cada membresía sin una consulta por fila.
            ->withSum('pagos as abonado', 'monto_abonado')
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%");
                });
            })
            ->when(is_numeric($estado), fn ($q) => $q->where('id_estado', (int) $estado))
            ->when(is_numeric($plan), fn ($q) => $q->where('id_membresia', (int) $plan))
            // Sin filtro también se filtra: los pases diarios van en su propio
            // grupo. Salvo al buscar, que a quien se busca hay que encontrarlo.
            ->when(
                isset(self::FILTROS[$filtro]) || $busqueda === '',
                fn ($q) => $this->filtrar($q, isset(self::FILTROS[$filtro]) ? $filtro : ''),
            )
            // El orden que se pidió; y si no se pidió ninguno, el de siempre:
            // lo más reciente arriba, salvo en «vencen esta semana», donde lo
            // que importa es a quién hay que llamar primero.
            ->when(true, fn ($q) => match ($orden) {
                'monto_desc' => $q->orderByDesc('precio_final'),
                'monto_asc' => $q->orderBy('precio_final'),
                'vence' => $q->orderBy('fecha_vencimiento'),
                'antiguas' => $q->orderBy('fecha_inicio'),
                default => $filtro === 'por_vencer'
                    ? $q->orderBy('fecha_vencimiento')
                    : $q->orderByDesc('fecha_vencimiento'),
            })
            ->paginate(25)
            ->withQueryString()
            ->through(function (Inscripcion $inscripcion) {
                $cliente = $inscripcion->cliente;

                return [
                    'uuid' => $inscripcion->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'rut' => $cliente?->run_pasaporte,
                    'membresia' => $inscripcion->membresia?->nombre,
                    'id_estado' => $inscripcion->id_estado,
                    'inicio' => $inscripcion->fecha_inicio?->format('d/m/Y'),
                    'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
                    // Negativo = ya vencio. Se calcula aqui y no en el navegador
                    // para que la fila no dependa del reloj del equipo.
                    'dias_restantes' => $inscripcion->fecha_vencimiento
                        ? (int) Carbon::today()->diffInDays($inscripcion->fecha_vencimiento, false)
                        : null,
                    'precio_final' => (int) $inscripcion->precio_final,
                    'es_pase' => (bool) $inscripcion->membresia?->esPase(),
                    // Lo que falta pagar de ESTA membresía. Las canceladas no
                    // se salen a cobrar: para ellas no hay deuda que mostrar.
                    'debe' => in_array((int) $inscripcion->id_estado, Inscripcion::ESTADOS_CON_DEUDA, true)
                        ? $inscripcion->deuda
                        : 0,
                ];
            });

        return Inertia::render('Inscripciones/Index', [
            'inscripciones' => $inscripciones,
            'filtros' => [
                'buscar' => $busqueda,
                'estado' => $estado,
                'filtro' => $filtro,
                'plan' => $plan,
                'orden' => $orden,
            ],
            /*
             * LOS PLANES, ORDENADOS POR LO QUE DURAN y no por su id: en el
             * desplegable se lee «Semana, Quincena, Mensual, Dos meses…», que
             * es como los nombra quien atiende, con la cuenta de cuántas
             * membresías hay de cada uno.
             */
            'planes' => Membresia::query()
                ->withCount('inscripciones')
                ->orderByRaw('duracion_meses * 30 + duracion_dias')
                ->get()
                ->map(fn (Membresia $m) => [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'cuantas' => $m->inscripciones_count,
                    // Para separar en la pantalla lo que se vende por días
                    // —pase, semana, quincena— de las mensualidades.
                    'por_dias' => (int) $m->duracion_meses === 0,
                ])
                ->all(),
            'resumen' => [
                'total' => $this->filtrar(Inscripcion::query(), '')->count(),
                'activas' => $this->filtrar(Inscripcion::query(), 'al_dia')->count(),
                'por_vencer' => $this->filtrar(Inscripcion::query(), 'por_vencer')->count(),
                'pausadas' => $this->filtrar(Inscripcion::query(), 'pausadas')->count(),
                'vencidas' => $this->filtrar(Inscripcion::query(), 'vencidas')->count(),
                'con_deuda' => $this->filtrar(Inscripcion::query(), 'con_deuda')->count(),
                'pases' => $this->filtrar(Inscripcion::query(), 'pases')->count(),
            ],
        ]);
    }

    private const FILTROS = ['al_dia' => 1, 'por_vencer' => 1, 'pausadas' => 1, 'vencidas' => 1, 'con_deuda' => 1, 'pases' => 1];

    /** Días que cuentan como «vence esta semana». */
    private const DIAS_POR_VENCER = 7;

    /**
     * Los grupos de la lista. Todos hablan de MENSUALIDADES: los pases diarios
     * vencen al día siguiente y llenaban «vencidas» de gente que estaba de
     * paso. Tienen su propio grupo. Solo «con deuda» los incluye, porque un
     * pase sin pagar se debe igual que una mensualidad.
     */
    private function filtrar($consulta, string $filtro)
    {
        if ($filtro === 'pases') {
            return $consulta->soloPases();
        }

        // Las mismas que suma «Por cobrar»: una sola definición de deuda.
        if ($filtro === 'con_deuda') {
            return $consulta->whereIn('id', Inscripcion::conDeuda()->pluck('id'));
        }

        $consulta->sinPases();

        return match ($filtro) {
            'al_dia' => $consulta->where('id_estado', self::ACTIVA),
            'por_vencer' => $consulta->where('id_estado', self::ACTIVA)
                ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(self::DIAS_POR_VENCER)]),
            'pausadas' => $consulta->where('id_estado', self::PAUSADA),
            'vencidas' => $consulta->where('id_estado', self::VENCIDA),
            default => $consulta,
        };
    }
}
