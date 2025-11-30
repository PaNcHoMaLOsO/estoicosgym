<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Models\LogNotificacion;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Listado de notificaciones
     */
    public function index(Request $request)
    {
        $query = Notificacion::with(['cliente', 'tipoNotificacion', 'estado', 'inscripcion.membresia'])
            ->orderBy('created_at', 'desc');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('id_estado', $request->estado);
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('id_tipo_notificacion', $request->tipo);
        }

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Búsqueda por cliente
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('cliente', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $notificaciones = $query->paginate(20)->withQueryString();

        // Estadísticas
        $estadisticas = $this->notificacionService->obtenerEstadisticas();

        // Tipos de notificación para filtro
        $tiposNotificacion = TipoNotificacion::orderBy('nombre')->get();

        return view('admin.notificaciones.index', compact(
            'notificaciones',
            'estadisticas',
            'tiposNotificacion'
        ));
    }

    /**
     * Ver detalle de una notificación
     */
    public function show(Notificacion $notificacion)
    {
        $notificacion->load(['cliente', 'tipoNotificacion', 'estado', 'inscripcion.membresia', 'logs']);

        return view('admin.notificaciones.show', compact('notificacion'));
    }

    /**
     * Reenviar una notificación fallida
     */
    public function reenviar(Notificacion $notificacion)
    {
        if (!$notificacion->puedeReintentar() && $notificacion->id_estado !== Notificacion::ESTADO_FALLIDO) {
            return back()->with('error', 'Esta notificación no puede ser reenviada');
        }

        try {
            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::today(),
            ]);
            $notificacion->registrarLog('reintentando', 'Reenvío manual desde panel de administración');

            // Intentar enviar inmediatamente
            $resultado = $this->notificacionService->enviarPendientes();

            return back()->with('success', 'Notificación reenviada correctamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al reenviar: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar una notificación pendiente
     */
    public function cancelar(Notificacion $notificacion)
    {
        if ($notificacion->id_estado !== Notificacion::ESTADO_PENDIENTE) {
            return back()->with('error', 'Solo se pueden cancelar notificaciones pendientes');
        }

        $notificacion->cancelar('Cancelada manualmente desde panel de administración');

        return back()->with('success', 'Notificación cancelada');
    }

    /**
     * Programar y enviar notificaciones manualmente
     */
    public function ejecutar(Request $request)
    {
        $accion = $request->input('accion', 'todo');

        $resultados = [];

        if ($accion === 'programar' || $accion === 'todo') {
            $resultados['por_vencer'] = $this->notificacionService->programarNotificacionesPorVencer();
            $resultados['vencidas'] = $this->notificacionService->programarNotificacionesVencidas();
        }

        if ($accion === 'enviar' || $accion === 'todo') {
            $resultados['envio'] = $this->notificacionService->enviarPendientes();
        }

        if ($accion === 'reintentar' || $accion === 'todo') {
            $resultados['reintento'] = $this->notificacionService->reintentarFallidas();
        }

        $mensaje = 'Proceso ejecutado correctamente. ';
        if (isset($resultados['por_vencer'])) {
            $mensaje .= "Por vencer: {$resultados['por_vencer']['programadas']} programadas. ";
        }
        if (isset($resultados['vencidas'])) {
            $mensaje .= "Vencidas: {$resultados['vencidas']['programadas']} programadas. ";
        }
        if (isset($resultados['envio'])) {
            $mensaje .= "Enviadas: {$resultados['envio']['enviadas']}, Fallidas: {$resultados['envio']['fallidas']}. ";
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Gestión de tipos de notificación (plantillas)
     */
    public function plantillas()
    {
        $tipos = TipoNotificacion::withCount('notificaciones')->get();

        return view('admin.notificaciones.plantillas', compact('tipos'));
    }

    /**
     * Editar plantilla de notificación
     */
    public function editarPlantilla(TipoNotificacion $tipoNotificacion)
    {
        return view('admin.notificaciones.editar-plantilla', compact('tipoNotificacion'));
    }

    /**
     * Actualizar plantilla de notificación
     */
    public function actualizarPlantilla(Request $request, TipoNotificacion $tipoNotificacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'asunto_email' => 'required|string|max:255',
            'plantilla_email' => 'required|string',
            'dias_anticipacion' => 'required|integer|min:0|max:30',
            'activo' => 'boolean',
            'enviar_email' => 'boolean',
        ]);

        $tipoNotificacion->update([
            'nombre' => $request->nombre,
            'asunto_email' => $request->asunto_email,
            'plantilla_email' => $request->plantilla_email,
            'dias_anticipacion' => $request->dias_anticipacion,
            'activo' => $request->boolean('activo'),
            'enviar_email' => $request->boolean('enviar_email'),
        ]);

        return redirect()->route('admin.notificaciones.plantillas')
            ->with('success', 'Plantilla actualizada correctamente');
    }

    /**
     * Ver log de una notificación
     */
    public function logs(Notificacion $notificacion)
    {
        $logs = $notificacion->logs()->orderBy('created_at', 'desc')->get();

        return response()->json($logs);
    }
}
