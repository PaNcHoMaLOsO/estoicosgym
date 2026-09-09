<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Notificaciones del panel nuevo (Inertia + React).
 *
 * Solo el listado: componer y enviar sigue en las pantallas Blade.
 */
class NotificacionController extends Controller
{
    /** Codigos de la categoria `notificacion` en la tabla `estados`. */
    private const PENDIENTE = 600;
    private const ENVIADA = 601;
    private const FALLIDA = 602;

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $notificaciones = Notificacion::query()
            ->with(['cliente', 'tipoNotificacion'])
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where('email_destino', 'like', "%{$busqueda}%")
                    ->orWhere('asunto', 'like', "%{$busqueda}%");
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Notificacion $n) {
                $cliente = $n->cliente;

                return [
                    'uuid' => $n->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno}")
                        : '—',
                    'email' => $n->email_destino,
                    'asunto' => $n->asunto,
                    'tipo' => $n->tipoNotificacion?->nombre,
                    'id_estado' => $n->id_estado,
                    'envio' => $n->tipo_envio === 'automatica' ? 'Automática' : 'Manual',
                    'programada' => $n->fecha_programada?->format('d/m/Y'),
                    'enviada' => $n->fecha_envio?->format('d/m/Y H:i'),
                    'intentos' => $n->intentos,
                    'max_intentos' => $n->max_intentos,
                    // Solo interesa el motivo cuando fallo; en el resto de los
                    // casos la columna estaria siempre vacia ocupando ancho.
                    'error' => $n->id_estado === self::FALLIDA ? $n->error_mensaje : null,
                ];
            });

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'filtros' => ['buscar' => $busqueda],
            'resumen' => [
                'pendientes' => Notificacion::where('id_estado', self::PENDIENTE)->count(),
                'enviadas' => Notificacion::where('id_estado', self::ENVIADA)->count(),
                // Lo unico accionable: una fallida hay que reintentarla.
                'fallidas' => Notificacion::where('id_estado', self::FALLIDA)->count(),
            ],
        ]);
    }
}
