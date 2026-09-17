<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
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

        $inscripciones = Inscripcion::query()
            ->with(['cliente', 'membresia'])
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%");
                });
            })
            ->when(is_numeric($estado), fn ($q) => $q->where('id_estado', (int) $estado))
            ->orderByDesc('fecha_vencimiento')
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
                ];
            });

        return Inertia::render('Inscripciones/Index', [
            'inscripciones' => $inscripciones,
            'filtros' => ['buscar' => $busqueda, 'estado' => $estado],
            'resumen' => [
                'total' => Inscripcion::count(),
                'activas' => Inscripcion::where('id_estado', self::ACTIVA)->count(),
                'pausadas' => Inscripcion::where('id_estado', self::PAUSADA)->count(),
                'vencidas' => Inscripcion::where('id_estado', self::VENCIDA)->count(),
            ],
        ]);
    }
}
