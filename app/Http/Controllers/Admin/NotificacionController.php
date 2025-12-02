<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Models\LogNotificacion;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Membresia;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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

    /**
     * Mostrar formulario para enviar notificaciones masivas
     */
    public function crear()
    {
        // Obtener todos los clientes con sus inscripciones
        $clientes = Cliente::with(['inscripciones.membresia', 'inscripciones.estado'])
            ->orderBy('nombres')
            ->get();
        
        // Conteos para los filtros
        $totalClientes = $clientes->whereNotNull('email')->count();
        
        $clientesActivos = Inscripcion::where('id_estado', 100)
            ->whereHas('cliente', function($q) {
                $q->whereNotNull('email');
            })
            ->distinct('id_cliente')
            ->count('id_cliente');
            
        $clientesVencidos = Inscripcion::where('id_estado', 102)
            ->whereHas('cliente', function($q) {
                $q->whereNotNull('email');
            })
            ->distinct('id_cliente')
            ->count('id_cliente');
            
        // Clientes sin inscripción activa ni vencida
        $clientesConInscripcion = Inscripcion::whereIn('id_estado', [100, 102])
            ->pluck('id_cliente')
            ->unique();
        $clientesInactivos = Cliente::whereNotNull('email')
            ->whereNotIn('id', $clientesConInscripcion)
            ->count();

        return view('admin.notificaciones.crear', compact(
            'clientes', 
            'totalClientes', 
            'clientesActivos', 
            'clientesVencidos', 
            'clientesInactivos'
        ));
    }

    /**
     * Obtener destinatarios según el grupo seleccionado
     */
    public function obtenerDestinatarios(Request $request)
    {
        $grupo = $request->input('grupo');
        $membresiaId = $request->input('membresia_id');
        
        $query = Cliente::where('activo', true)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        switch ($grupo) {
            case 'todos':
                // Ya filtrado por activo y con email
                break;
                
            case 'pagos_pendientes':
                $clienteIds = Pago::where('id_estado', 200)
                    ->pluck('id_cliente')
                    ->unique();
                $query->whereIn('id', $clienteIds);
                break;
                
            case 'por_vencer_7':
                $clienteIds = Inscripcion::where('id_estado', 100)
                    ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(7)])
                    ->pluck('id_cliente')
                    ->unique();
                $query->whereIn('id', $clienteIds);
                break;
                
            case 'por_vencer_15':
                $clienteIds = Inscripcion::where('id_estado', 100)
                    ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(15)])
                    ->pluck('id_cliente')
                    ->unique();
                $query->whereIn('id', $clienteIds);
                break;
                
            case 'vencidas':
                $clienteIds = Inscripcion::where('id_estado', 102)
                    ->pluck('id_cliente')
                    ->unique();
                $query->whereIn('id', $clienteIds);
                break;
                
            case 'activas':
                $clienteIds = Inscripcion::where('id_estado', 100)
                    ->pluck('id_cliente')
                    ->unique();
                $query->whereIn('id', $clienteIds);
                break;
                
            case 'membresia':
                if ($membresiaId) {
                    $clienteIds = Inscripcion::where('id_estado', 100)
                        ->where('id_membresia', $membresiaId)
                        ->pluck('id_cliente')
                        ->unique();
                    $query->whereIn('id', $clienteIds);
                }
                break;
        }

        $clientes = $query->select('id', 'nombres', 'apellido_paterno', 'email')
            ->orderBy('nombres')
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre_completo,
                    'email' => $cliente->email,
                ];
            });

        return response()->json([
            'total' => $clientes->count(),
            'clientes' => $clientes
        ]);
    }

    /**
     * Enviar notificación masiva
     */
    public function enviarMasivo(Request $request)
    {
        $request->validate([
            'grupo' => 'required|string',
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'cliente_ids' => 'nullable|string',
            'programar' => 'nullable|boolean',
            'fecha_programada' => 'nullable|date|after_or_equal:today',
        ]);

        $grupo = $request->input('grupo');
        
        // Decodificar cliente_ids si viene como JSON string
        $clienteIdsRaw = $request->input('cliente_ids', '[]');
        $clienteIds = is_string($clienteIdsRaw) ? json_decode($clienteIdsRaw, true) : $clienteIdsRaw;
        $clienteIds = is_array($clienteIds) ? $clienteIds : [];
        
        $asunto = $request->input('asunto');
        $mensaje = $request->input('mensaje');
        $programar = $request->boolean('programar');
        $fechaProgramada = $programar && $request->filled('fecha_programada') 
            ? Carbon::parse($request->fecha_programada) 
            : Carbon::today();

        // Si no se especificaron clientes, obtener según el grupo
        if (empty($clienteIds)) {
            $clienteIds = $this->obtenerClienteIdsPorGrupo($grupo, $request->input('membresia_id'));
        }

        if (empty($clienteIds)) {
            return back()->with('error', 'No se encontraron destinatarios para este grupo');
        }

        $clientes = Cliente::whereIn('id', $clienteIds)
            ->where('activo', true)
            ->whereNotNull('email')
            ->get();

        // Obtener o crear tipo de notificación "manual"
        $tipoManual = TipoNotificacion::firstOrCreate(
            ['codigo' => 'notificacion_manual'],
            [
                'nombre' => 'Notificación Manual',
                'descripcion' => 'Notificaciones enviadas manualmente por el administrador',
                'asunto_email' => '{asunto}',
                'plantilla_email' => '{mensaje}',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
            ]
        );

        $programadas = 0;
        $errores = [];

        foreach ($clientes as $cliente) {
            try {
                // Obtener última inscripción del cliente para referencia
                $inscripcion = Inscripcion::where('id_cliente', $cliente->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Personalizar mensaje
                $mensajePersonalizado = str_replace(
                    ['{nombre}', '{email}'],
                    [$cliente->nombre_completo, $cliente->email],
                    $mensaje
                );

                $notificacion = Notificacion::create([
                    'id_tipo_notificacion' => $tipoManual->id,
                    'id_cliente' => $cliente->id,
                    'id_inscripcion' => $inscripcion?->id,
                    'email_destino' => $cliente->email,
                    'asunto' => $asunto,
                    'contenido' => $mensajePersonalizado,
                    'id_estado' => Notificacion::ESTADO_PENDIENTE,
                    'fecha_programada' => $fechaProgramada,
                ]);

                $notificacion->registrarLog('programada', "Notificación manual programada para grupo: {$grupo}");
                $programadas++;

            } catch (\Exception $e) {
                $errores[] = "Error con {$cliente->email}: " . $e->getMessage();
            }
        }

        // Si no es programada para después, enviar inmediatamente
        if (!$programar || $fechaProgramada->isToday()) {
            $resultado = $this->notificacionService->enviarPendientes();
            return redirect()->route('admin.notificaciones.index')
                ->with('success', "Se programaron {$programadas} notificaciones. Enviadas: {$resultado['enviadas']}, Fallidas: {$resultado['fallidas']}");
        }

        return redirect()->route('admin.notificaciones.index')
            ->with('success', "Se programaron {$programadas} notificaciones para el {$fechaProgramada->format('d/m/Y')}");
    }

    /**
     * Obtener IDs de clientes según el grupo
     */
    private function obtenerClienteIdsPorGrupo(string $grupo, ?int $membresiaId = null): array
    {
        switch ($grupo) {
            case 'todos':
                return Cliente::where('activo', true)
                    ->whereNotNull('email')
                    ->pluck('id')
                    ->toArray();
                
            case 'pagos_pendientes':
                return Pago::where('id_estado', 200)
                    ->pluck('id_cliente')
                    ->unique()
                    ->toArray();
                
            case 'por_vencer_7':
                return Inscripcion::where('id_estado', 100)
                    ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(7)])
                    ->pluck('id_cliente')
                    ->unique()
                    ->toArray();
                
            case 'por_vencer_15':
                return Inscripcion::where('id_estado', 100)
                    ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(15)])
                    ->pluck('id_cliente')
                    ->unique()
                    ->toArray();
                
            case 'vencidas':
                return Inscripcion::where('id_estado', 102)
                    ->pluck('id_cliente')
                    ->unique()
                    ->toArray();
                
            case 'activas':
                return Inscripcion::where('id_estado', 100)
                    ->pluck('id_cliente')
                    ->unique()
                    ->toArray();
                
            case 'membresia':
                if ($membresiaId) {
                    return Inscripcion::where('id_estado', 100)
                        ->where('id_membresia', $membresiaId)
                        ->pluck('id_cliente')
                        ->unique()
                        ->toArray();
                }
                return [];
                
            default:
                return [];
        }
    }
}
