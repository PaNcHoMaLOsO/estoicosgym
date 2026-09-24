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
        // «Solo las que no salieron»: es a lo que se llega desde el aviso del
        // Resumen, y lo único de esta pantalla que pide hacer algo.
        $soloFallidas = $request->query('estado') === 'fallidas';

        $notificaciones = Notificacion::query()
            ->with(['cliente', 'tipoNotificacion'])
            ->when($soloFallidas, fn ($q) => $q->where('id_estado', self::FALLIDA))
            // Agrupado: sin el paréntesis, el «o el asunto» se saltaba el filtro de fallidas.
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(fn ($q) => $q->whereParecido('email_destino', "%{$busqueda}%")
                    ->orWhereParecido('asunto', "%{$busqueda}%"));
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
                        : '-',
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
                    // Que se puede hacer con esta fila. Se decide aqui
                    // y no en la pantalla: son reglas del estado, no de
                    // como se pinta.
                    'puede_reenviar' => $n->id_estado === self::FALLIDA,
                    'puede_cancelar' => $n->id_estado === self::PENDIENTE,
                ];
            });

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'filtros' => ['buscar' => $busqueda, 'estado' => $soloFallidas ? 'fallidas' : null],
            'resumen' => [
                'pendientes' => Notificacion::where('id_estado', self::PENDIENTE)->count(),
                'enviadas' => Notificacion::where('id_estado', self::ENVIADA)->count(),
                // Lo unico accionable: una fallida hay que reintentarla.
                'fallidas' => Notificacion::where('id_estado', self::FALLIDA)->count(),
            ],
        ]);
    }
}
