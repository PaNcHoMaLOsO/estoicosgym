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
use Illuminate\Support\Facades\Log;

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

        // BÃºsqueda por cliente
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('cliente', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $notificaciones = $query->paginate(20)->withQueryString();

        // EstadÃ­sticas
        $estadisticas = $this->notificacionService->obtenerEstadisticas();

        // Tipos de notificaciÃ³n para filtro
        $tiposNotificacion = TipoNotificacion::orderBy('nombre')->get();

        // Ãšltima ejecuciÃ³n automÃ¡tica (simulada - en producciÃ³n vendrÃ­a de logs)
        $ultimaEjecucion = (object)[
            'fecha' => now()->format('d/m/Y H:i'),
            'programadas' => Notificacion::whereDate('created_at', today())->where('id_estado', 600)->count(),
            'enviadas' => Notificacion::whereDate('fecha_envio', today())->where('id_estado', 601)->count(),
            'fallidas' => Notificacion::whereDate('created_at', today())->where('id_estado', 602)->count(),
        ];

        return view('admin.notificaciones.index', compact(
            'notificaciones',
            'estadisticas',
            'tiposNotificacion',
            'ultimaEjecucion'
        ));
    }

    /**
     * Mostrar historial de ejecuciones automÃ¡ticas
     */
    public function historial()
    {
        // Agrupar notificaciones por dÃ­a para mostrar resumen de ejecuciones
        $historial = Notificacion::selectRaw('DATE(created_at) as fecha, 
                                              COUNT(*) as total,
                                              SUM(CASE WHEN id_estado = 600 THEN 1 ELSE 0 END) as pendientes,
                                              SUM(CASE WHEN id_estado = 601 THEN 1 ELSE 0 END) as enviadas,
                                              SUM(CASE WHEN id_estado = 602 THEN 1 ELSE 0 END) as fallidas,
                                              SUM(CASE WHEN id_estado = 603 THEN 1 ELSE 0 END) as canceladas')
            ->whereNotNull('fecha_programada')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->paginate(30);

        return view('admin.notificaciones.historial', compact('historial'));
    }

    /**
     * Mostrar formulario para programar notificaciones
     */
    public function programar()
    {
        return view('admin.notificaciones.programar');
    }

    /**
     * Buscar clientes por nombre, RUT o email
     */
    public function buscarCliente(Request $request)
    {
        $query = $request->get('query');
        
        $clientes = Cliente::where('activo', true)
            ->where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                  ->orWhere('apellido_materno', 'LIKE', "%{$query}%")
                  ->orWhere('run_pasaporte', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'run_pasaporte')
            ->limit(10)
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->nombre_completo,
                    'email' => $cliente->email,
                    'run_pasaporte' => $cliente->run_pasaporte
                ];
            });

        return response()->json($clientes);
    }

    /**
     * Contar destinatarios segÃºn filtros
     */
    public function contarDestinatarios(Request $request)
    {
        $query = Cliente::where('activo', true);

        switch ($request->tipo_envio) {
            case 'membresia':
                if ($request->id_membresia) {
                    $query->whereHas('inscripciones', function($q) use ($request) {
                        $q->where('id_membresia', $request->id_membresia)
                          ->whereIn('id_estado', [100, 200, 400]); // Activo, Por Vencer, Pausado
                    });
                }
                break;
            
            case 'estado':
                if ($request->id_estado) {
                    $query->whereHas('inscripciones', function($q) use ($request) {
                        if ($request->id_estado == 200) {
                            // Por vencer (prÃ³ximos 7 dÃ­as)
                            $q->whereDate('fecha_vencimiento', '<=', now()->addDays(7))
                              ->whereDate('fecha_vencimiento', '>=', now())
                              ->where('id_estado', 100);
                        } else {
                            $q->where('id_estado', $request->id_estado);
                        }
                    });
                }
                break;
            
            case 'todos':
                $query->whereHas('inscripciones', function($q) {
                    $q->whereIn('id_estado', [100, 200, 400]);
                });
                break;
        }

        $count = $query->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Guardar notificaciÃ³n programada con validaciones anti-spam
     */
    public function guardarProgramada(Request $request)
    {
        // Validar datos bÃ¡sicos
        $validated = $request->validate([
            'tipo_envio' => 'required|in:todos,membresia,estado,individual',
            'id_tipo_notificacion' => 'required|exists:tipos_notificacion,id',
            'fecha_programada' => 'required_without:enviar_ahora|date|after_or_equal:today',
            'hora_programada' => 'required_without:enviar_ahora',
            'asunto_custom' => 'nullable|string|max:255',
            'mensaje_adicional' => 'nullable|string|max:1000',
            'enviar_ahora' => 'nullable|boolean',
        ]);

        // Obtener destinatarios segÃºn filtros
        $query = Cliente::where('activo', true);

        switch ($request->tipo_envio) {
            case 'individual':
                $request->validate(['id_cliente' => 'required|exists:clientes,id']);
                $query->where('id', $request->id_cliente);
                break;
            
            case 'membresia':
                $request->validate(['id_membresia' => 'nullable|exists:membresias,id']);
                if ($request->id_membresia) {
                    $query->whereHas('inscripciones', function($q) use ($request) {
                        $q->where('id_membresia', $request->id_membresia)
                          ->whereIn('id_estado', [100, 200, 400]);
                    });
                }
                break;
            
            case 'estado':
                if ($request->id_estado) {
                    $query->whereHas('inscripciones', function($q) use ($request) {
                        if ($request->id_estado == 200) {
                            $q->whereDate('fecha_vencimiento', '<=', now()->addDays(7))
                              ->whereDate('fecha_vencimiento', '>=', now())
                              ->where('id_estado', 100);
                        } else {
                            $q->where('id_estado', $request->id_estado);
                        }
                    });
                }
                break;
            
            case 'todos':
                $query->whereHas('inscripciones', function($q) {
                    $q->whereIn('id_estado', [100, 200, 400]);
                });
                break;
        }

        $clientes = $query->with('inscripciones.membresia')->get();

        if ($clientes->isEmpty()) {
            return back()->with('error', 'No se encontraron destinatarios con los filtros seleccionados.');
        }

        // VALIDACIÃ“N ANTI-SPAM: LÃ­mite diario general
        $notificacionesHoy = Notificacion::whereDate('created_at', today())->count();
        if ($notificacionesHoy >= 500) {
            return back()->with('error', 'Se ha alcanzado el lÃ­mite diario de 500 notificaciones. Intente maÃ±ana.');
        }

        // Verificar que no exceda el lÃ­mite con este envÃ­o
        if (($notificacionesHoy + $clientes->count()) > 500) {
            return back()->with('error', "Este envÃ­o excederÃ­a el lÃ­mite diario. Solo puede enviar " . (500 - $notificacionesHoy) . " notificaciones mÃ¡s hoy.");
        }

        // Determinar fecha/hora de programaciÃ³n
        $fechaEnvio = $request->enviar_ahora ? now() : Carbon::parse($request->fecha_programada . ' ' . $request->hora_programada);

        $notificacionService = new NotificacionService();
        $tipo = TipoNotificacion::findOrFail($request->id_tipo_notificacion);
        
        $creadas = 0;
        $rechazadas = 0;
        $errores = [];

        foreach ($clientes as $cliente) {
            // VALIDACIÃ“N ANTI-SPAM 1: MÃ¡ximo 3 notificaciones por cliente al dÃ­a
            $notificacionesClienteHoy = Notificacion::where('email_destinatario', $cliente->email)
                ->whereDate('created_at', today())
                ->count();

            if ($notificacionesClienteHoy >= 3) {
                $rechazadas++;
                $errores[] = "Cliente {$cliente->nombre_completo}: LÃ­mite diario alcanzado (3 notificaciones)";
                continue;
            }

            // VALIDACIÃ“N ANTI-SPAM 2: Intervalo mÃ­nimo de 2 horas entre envÃ­os
            $ultimaNotificacion = Notificacion::where('email_destinatario', $cliente->email)
                ->latest('created_at')
                ->first();

            if ($ultimaNotificacion && $ultimaNotificacion->created_at->diffInHours(now()) < 2) {
                $rechazadas++;
                $minutosRestantes = 120 - $ultimaNotificacion->created_at->diffInMinutes(now());
                $errores[] = "Cliente {$cliente->nombre_completo}: Debe esperar {$minutosRestantes} minutos";
                continue;
            }

            // VALIDACIÃ“N ANTI-SPAM 3: No duplicar notificaciones idÃ©nticas en 24 horas
            $notificacionDuplicada = Notificacion::where('email_destinatario', $cliente->email)
                ->where('id_tipo_notificacion', $tipo->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($notificacionDuplicada) {
                $rechazadas++;
                $errores[] = "Cliente {$cliente->nombre_completo}: NotificaciÃ³n idÃ©ntica enviada recientemente";
                continue;
            }

            // Validar que tenga email vÃ¡lido
            if (!$cliente->email && !($cliente->es_menor_edad && $cliente->apoderado_email)) {
                $rechazadas++;
                $errores[] = "Cliente {$cliente->nombre_completo}: Sin email vÃ¡lido";
                continue;
            }

            try {
                // Determinar email de destino
                $emailDestino = $cliente->email;
                $nombreDestinatario = $cliente->nombre_completo;

                if ($cliente->es_menor_edad && !empty($cliente->apoderado_email)) {
                    $emailDestino = $cliente->apoderado_email;
                    $nombreDestinatario = $cliente->apoderado_nombre ?: 'Apoderado/a';
                }

                // Obtener inscripciÃ³n activa
                $inscripcion = $cliente->inscripciones()
                    ->whereIn('id_estado', [100, 200, 400])
                    ->latest()
                    ->first();

                if (!$inscripcion) {
                    $rechazadas++;
                    $errores[] = "Cliente {$cliente->nombre_completo}: Sin inscripciÃ³n activa";
                    continue;
                }

                // Preparar datos para la notificaciÃ³n
                $data = [
                    'nombre' => $nombreDestinatario,
                    'nombre_cliente' => $cliente->nombre_completo,
                    'membresia' => $inscripcion->membresia->nombre,
                    'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
                    'dias_restantes' => max(0, $inscripcion->fecha_vencimiento->diffInDays(now(), false)),
                    'es_menor_edad' => $cliente->es_menor_edad,
                ];

                // Renderizar contenido
                $asunto = $request->asunto_custom ?: $tipo->renderizar($tipo->asunto, $data);
                $contenidoBase = $tipo->renderizar($tipo->plantilla, $data);
                
                // Agregar mensaje adicional si existe
                if ($request->mensaje_adicional) {
                    $contenidoBase = "<p><strong>" . nl2br(e($request->mensaje_adicional)) . "</strong></p><hr>" . $contenidoBase;
                }

                // Crear la notificaciÃ³n
                Notificacion::create([
                    'id_cliente' => $cliente->id,
                    'id_tipo_notificacion' => $tipo->id,
                    'email_destinatario' => $emailDestino,
                    'asunto' => $asunto,
                    'contenido' => $contenidoBase,
                    'fecha_programada' => $fechaEnvio,
                    'id_estado' => $request->enviar_ahora ? 600 : 600, // 600 = Pendiente
                    'intento' => 0,
                ]);

                $creadas++;

            } catch (\Exception $e) {
                $rechazadas++;
                $errores[] = "Cliente {$cliente->nombre_completo}: {$e->getMessage()}";
                \Log::error("Error al crear notificaciÃ³n programada: " . $e->getMessage());
            }
        }

        // Preparar mensaje de resultado
        $mensaje = "Notificaciones programadas: {$creadas}";
        
        if ($rechazadas > 0) {
            $mensaje .= " | Rechazadas: {$rechazadas}";
        }

        if ($request->enviar_ahora && $creadas > 0) {
            // Ejecutar envÃ­o inmediato
            \Artisan::call('notificaciones:enviar');
            $mensaje .= " | Enviando ahora...";
        }

        return redirect()->route('admin.notificaciones.index')
            ->with('success', $mensaje)
            ->with('errores_detalle', array_slice($errores, 0, 10)); // Solo mostrar primeros 10 errores
    }

    /**
     * Ver detalle de una notificaciÃ³n
     */
    public function show(Notificacion $notificacion)
    {
        $notificacion->load(['cliente', 'tipoNotificacion', 'estado', 'inscripcion.membresia', 'logs']);

        return view('admin.notificaciones.show', compact('notificacion'));
    }

    /**
     * Reenviar una notificaciÃ³n fallida
     */
    public function reenviar(Notificacion $notificacion)
    {
        if (!$notificacion->puedeReintentar() && $notificacion->id_estado !== Notificacion::ESTADO_FALLIDO) {
            return back()->with('error', 'Esta notificaciÃ³n no puede ser reenviada');
        }

        try {
            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::today(),
            ]);
            $notificacion->registrarLog('reintentando', 'ReenvÃ­o manual desde panel de administraciÃ³n');

            // Intentar enviar inmediatamente
            $resultado = $this->notificacionService->enviarPendientes();

            return back()->with('success', 'NotificaciÃ³n reenviada correctamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al reenviar: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar una notificaciÃ³n pendiente
     */
    public function cancelar(Notificacion $notificacion)
    {
        if ($notificacion->id_estado !== Notificacion::ESTADO_PENDIENTE) {
            return back()->with('error', 'Solo se pueden cancelar notificaciones pendientes');
        }

        $notificacion->cancelar('Cancelada manualmente desde panel de administraciÃ³n');

        return back()->with('success', 'NotificaciÃ³n cancelada');
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
     * GestiÃ³n de tipos de notificaciÃ³n (plantillas)
     */
    public function plantillas()
    {
        $tipos = TipoNotificacion::withCount('notificaciones')->get();

        return view('admin.notificaciones.plantillas', compact('tipos'));
    }

    /**
     * Editar plantilla de notificaciÃ³n
     */
    public function editarPlantilla(TipoNotificacion $tipoNotificacion)
    {
        return view('admin.notificaciones.editar-plantilla', compact('tipoNotificacion'));
    }

    /**
     * Actualizar plantilla de notificaciÃ³n
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
     * Ver log de una notificaciÃ³n
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
            
        // Clientes sin inscripciÃ³n activa ni vencida
        $clientesConInscripcion = Inscripcion::whereIn('id_estado', [100, 102])
            ->pluck('id_cliente')
            ->unique();
        $clientesInactivos = Cliente::whereNotNull('email')
            ->whereNotIn('id', $clientesConInscripcion)
            ->count();

        // Cargar solo las 4 plantillas manuales desde archivos HTML
        $plantillasPersonalizadas = [];
        $archivosPlantillas = [
            ['archivo' => '10_horario_especial.html', 'nombre' => 'Horario Especial', 'asunto' => 'Horario Especial', 'codigo' => 'horario_especial'],
            ['archivo' => '11_promocion.html', 'nombre' => 'Promocion', 'asunto' => 'Promocion Especial', 'codigo' => 'promocion'],
            ['archivo' => '12_anuncio.html', 'nombre' => 'Anuncio', 'asunto' => 'Anuncio Importante', 'codigo' => 'anuncio'],
            ['archivo' => '13_evento.html', 'nombre' => 'Evento', 'asunto' => 'No te pierdas nuestro evento', 'codigo' => 'evento'],
        ];

        foreach ($archivosPlantillas as $plantilla) {
            $ruta = storage_path('app/test_emails/preview/' . $plantilla['archivo']);
            if (file_exists($ruta)) {
                // Leer el archivo
                $contenido = file_get_contents($ruta);
                
                // Extraer solo el contenido entre <body> y </body>
                if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
                    $contenido = $matches[1];
                }
                
                // Si hay un div.container, extraer solo su contenido
                if (preg_match('/<div\s+class="container"[^>]*>(.*?)<\/div>\s*$/is', $contenido, $matches)) {
                    $contenido = $matches[1];
                }
                
                // Limpiar espacios innecesarios
                $contenido = trim($contenido);
                
                $plantillasPersonalizadas[] = [
                    'id' => str_replace('.html', '', $plantilla['archivo']),
                    'nombre' => $plantilla['nombre'],
                    'codigo' => $plantilla['codigo'],
                    'asunto_email' => $plantilla['asunto'],
                    'plantilla_email' => $contenido
                ];
            }
        }
        return view('admin.notificaciones.crear', compact(
            'clientes',
            'plantillasPersonalizadas', 
            'totalClientes', 
            'clientesActivos', 
            'clientesVencidos', 
            'clientesInactivos'
        ));
    }

    /**
     * Obtener destinatarios segÃºn el grupo seleccionado
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
     * Enviar notificaciÃ³n masiva
     */
    public function enviarMasivo(Request $request)
    {
        // Validaciones mejoradas
        $validated = $request->validate([
            'asunto' => 'required|string|min:5|max:255',
            'mensaje' => 'required|string|min:10|max:5000',
            'cliente_ids' => 'required|string',
            'plantilla_id' => 'nullable|integer|exists:tipo_notificaciones,id',
        ], [
            'asunto.required' => 'El asunto es obligatorio',
            'asunto.min' => 'El asunto debe tener al menos 5 caracteres',
            'asunto.max' => 'El asunto no puede exceder 255 caracteres',
            'mensaje.required' => 'El mensaje es obligatorio',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres',
            'mensaje.max' => 'El mensaje no puede exceder 5000 caracteres',
            'cliente_ids.required' => 'Debes seleccionar al menos un cliente',
        ]);
        
        // Decodificar cliente_ids
        $clienteIds = json_decode($request->cliente_ids, true);
        
        if (empty($clienteIds) || !is_array($clienteIds)) {
            return back()
                ->with('error', 'Debes seleccionar al menos un cliente')
                ->withInput();
        }

        // Validar que los clientes existan y tengan email
        $clientes = Cliente::whereIn('id', $clienteIds)
            ->where('activo', true)
            ->whereNotNull('email')
            ->get();

        if ($clientes->isEmpty()) {
            return back()
                ->with('error', 'No se encontraron clientes vÃ¡lidos con email')
                ->withInput();
        }

        $asunto = $request->asunto;
        $mensaje = $request->mensaje;

        // Obtener tipo de notificaciÃ³n (si se seleccionÃ³ plantilla) o crear manual
        if ($request->filled('plantilla_id') && $request->plantilla_id !== 'custom') {
            $tipoNotificacion = TipoNotificacion::find($request->plantilla_id);
        } else {
            $tipoNotificacion = TipoNotificacion::firstOrCreate(
                ['codigo' => 'notificacion_manual'],
                [
                    'nombre' => 'NotificaciÃ³n Manual',
                    'descripcion' => 'Notificaciones enviadas manualmente por el administrador',
                    'asunto_email' => '{asunto}',
                    'plantilla_email' => '{mensaje}',
                    'dias_anticipacion' => 0,
                    'activo' => true,
                    'enviar_email' => true,
                ]
            );
        }

        $creadas = 0;
        $errores = [];

        foreach ($clientes as $cliente) {
            try {
                // Obtener Ãºltima inscripciÃ³n del cliente para referencia
                $inscripcion = Inscripcion::where('id_cliente', $cliente->id)
                    ->with('membresia')
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Personalizar mensaje con variables del cliente
                $variables = [
                    '{nombre}' => $cliente->nombre_completo,
                    '{email}' => $cliente->email,
                    '{membresia}' => $inscripcion?->membresia?->nombre ?? 'Sin membresÃ­a',
                ];

                $asuntoPersonalizado = str_replace(
                    array_keys($variables),
                    array_values($variables),
                    $asunto
                );

                $mensajePersonalizado = str_replace(
                    array_keys($variables),
                    array_values($variables),
                    $mensaje
                );

                // Crear notificaciÃ³n
                $notificacion = Notificacion::create([
                    'id_tipo_notificacion' => $tipoNotificacion->id,
                    'id_cliente' => $cliente->id,
                    'id_inscripcion' => $inscripcion?->id,
                    'email_destino' => $cliente->email,
                    'asunto' => $asuntoPersonalizado,
                    'contenido' => $mensajePersonalizado,
                    'id_estado' => Notificacion::ESTADO_PENDIENTE,
                    'fecha_programada' => now(),
                    'enviado_por_user_id' => auth()->id(),
                ]);

                $notificacion->registrarLog('creada', 'NotificaciÃ³n manual creada desde el panel');
                $creadas++;

            } catch (\Exception $e) {
                Log::error('Error creando notificaciÃ³n masiva', [
                    'cliente_id' => $cliente->id,
                    'error' => $e->getMessage()
                ]);
                $errores[] = "Error con {$cliente->nombre_completo}: " . $e->getMessage();
            }
        }

        // Enviar inmediatamente las notificaciones creadas
        try {
            $resultado = $this->notificacionService->enviarPendientes();
            
            $mensaje = "âœ… Notificaciones enviadas: {$resultado['enviadas']} de {$creadas}";
            
            if ($resultado['fallidas'] > 0) {
                $mensaje .= " | âŒ Fallidas: {$resultado['fallidas']}";
            }
            
            if (!empty($errores)) {
                $mensaje .= " | âš ï¸ Errores: " . count($errores);
            }
            
            return redirect()->route('admin.notificaciones.index')
                ->with($resultado['fallidas'] === 0 ? 'success' : 'warning', $mensaje);
                
        } catch (\Exception $e) {
            Log::error('Error enviando notificaciones masivas', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.notificaciones.index')
                ->with('error', "Se crearon {$creadas} notificaciones pero hubo un error al enviarlas: " . $e->getMessage());
        }
    }

    /**
     * Obtener IDs de clientes segÃºn el grupo
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

    /**
     * Formulario simple para enviar notificaciÃ³n a un cliente especÃ­fico
     * (Vista simplificada - pendiente de implementaciÃ³n completa del rediseÃ±o)
     */
    public function enviarCliente()
    {
        $plantillas = TipoNotificacion::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.notificaciones.enviar-cliente', compact('plantillas'));
    }

    /**
     * Buscar cliente individual para envÃ­o manual
     */
    public function buscarClienteIndividual(Request $request)
    {
        $buscar = $request->input('buscar');

        if (empty($buscar)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar un criterio de bÃºsqueda'
            ]);
        }

        $clientes = Cliente::where(function($query) use ($buscar) {
            $query->where('run_pasaporte', 'like', "%{$buscar}%")
                  ->orWhere('nombres', 'like', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('celular', 'like', "%{$buscar}%");
        })
        ->with(['inscripciones' => function($query) {
            $query->latest()->limit(1)->with('membresia');
        }])
        ->limit(10)
        ->get()
        ->map(function($cliente) {
            $inscripcion = $cliente->inscripciones->first();
            return [
                'id' => $cliente->id,
                'nombre_completo' => $cliente->nombre_completo,
                'run_pasaporte' => $cliente->run_pasaporte,
                'email' => $cliente->email,
                'celular' => $cliente->celular,
                'membresia' => $inscripcion ? $inscripcion->membresia->nombre : 'Sin membresÃ­a',
                'estado_membresia' => $inscripcion ? $inscripcion->estado->nombre : 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'clientes' => $clientes,
            'total' => $clientes->count()
        ]);
    }

    /**
     * Vista previa del email antes de enviar
     */
    public function preview(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $plantillaId = $request->input('plantilla_id');
        $notaPersonalizada = $request->input('nota_personalizada');

        $cliente = Cliente::with(['inscripciones' => function($query) {
            $query->latest()->limit(1)->with(['membresia', 'pagos']);
        }])->findOrFail($clienteId);

        $plantilla = TipoNotificacion::findOrFail($plantillaId);
        $inscripcion = $cliente->inscripciones->first();

        // Preparar datos
        $datos = $this->prepararDatosCliente($cliente, $inscripcion);
        
        // Renderizar plantilla
        $asunto = str_replace(
            array_map(fn($k) => "{{$k}}", array_keys($datos)),
            array_values($datos),
            $plantilla->asunto_email
        );

        $contenido = str_replace(
            array_map(fn($k) => "{{$k}}", array_keys($datos)),
            array_values($datos),
            $plantilla->plantilla_email
        );

        // Agregar nota personalizada si existe
        if (!empty($notaPersonalizada)) {
            $contenido = str_replace(
                '</body>',
                '<div style="background: #fffbf0; border-left: 4px solid #FFC107; padding: 20px; margin: 20px 0;"><p style="margin: 0;"><strong>Nota del administrador:</strong></p><p style="margin: 10px 0 0 0;">' . nl2br(e($notaPersonalizada)) . '</p></div></body>',
                $contenido
            );
        }

        return response()->json([
            'success' => true,
            'asunto' => $asunto,
            'contenido' => $contenido,
            'email_destino' => $cliente->email,
            'datos' => $datos
        ]);
    }

    /**
     * Enviar notificaciÃ³n a cliente individual
     */
    public function enviarIndividual(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plantilla_id' => 'required|exists:tipo_notificaciones,id',
            'nota_personalizada' => 'nullable|string|max:1000',
        ]);

        try {
            $cliente = Cliente::with(['inscripciones' => function($query) {
                $query->latest()->limit(1)->with(['membresia', 'pagos']);
            }])->findOrFail($request->cliente_id);

            $plantilla = TipoNotificacion::findOrFail($request->plantilla_id);
            $inscripcion = $cliente->inscripciones->first();

            // Preparar datos del cliente
            $datos = $this->prepararDatosCliente($cliente, $inscripcion);
            
            // Renderizar plantilla
            $asunto = str_replace(
                array_map(fn($k) => "{{$k}}", array_keys($datos)),
                array_values($datos),
                $plantilla->asunto_email
            );

            $contenido = str_replace(
                array_map(fn($k) => "{{$k}}", array_keys($datos)),
                array_values($datos),
                $plantilla->plantilla_email
            );

            // Agregar nota personalizada si existe
            $notaPersonalizada = $request->input('nota_personalizada');
            if (!empty($notaPersonalizada)) {
                $contenido = str_replace(
                    '</body>',
                    '<div style="background: #fffbf0; border-left: 4px solid #FFC107; padding: 20px; margin: 20px 0;"><p style="margin: 0;"><strong>Nota del administrador:</strong></p><p style="margin: 10px 0 0 0;">' . nl2br(e($notaPersonalizada)) . '</p></div></body>',
                    $contenido
                );
            }

            // Crear notificaciÃ³n manual
            $notificacion = Notificacion::create([
                'id_tipo_notificacion' => $plantilla->id,
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $inscripcion?->id,
                'email_destino' => $cliente->email,
                'asunto' => $asunto,
                'contenido' => $contenido,
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => today(),
                'tipo_envio' => 'manual',
                'enviado_por_user_id' => auth()->id(),
                'nota_personalizada' => $notaPersonalizada,
            ]);

            $notificacion->registrarLog('programada', 'NotificaciÃ³n manual creada por ' . auth()->user()->name);

            // Enviar inmediatamente
            Mail::html($contenido, function ($message) use ($cliente, $asunto) {
                $message->to($cliente->email)
                        ->subject($asunto);
            });

            $notificacion->marcarComoEnviada();

            return response()->json([
                'success' => true,
                'message' => 'NotificaciÃ³n enviada correctamente a ' . $cliente->email,
                'notificacion_id' => $notificacion->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preparar datos del cliente para reemplazar variables en plantilla
     */
    private function prepararDatosCliente(Cliente $cliente, ?Inscripcion $inscripcion): array
    {
        $datos = [
            'nombre' => $cliente->nombre_completo,
            'nombres' => $cliente->nombres,
            'apellido' => $cliente->apellido_paterno,
            'email' => $cliente->email,
            'celular' => $cliente->celular ?? 'No registrado',
        ];

        if ($inscripcion) {
            $totalPagado = $inscripcion->pagos->sum('monto_abonado');
            $precioBase = $inscripcion->precio_final ?? $inscripcion->precio_base ?? 0;
            $saldoPendiente = $precioBase - $totalPagado;

            $datos['membresia'] = $inscripcion->membresia->nombre;
            $datos['fecha_inicio'] = $inscripcion->fecha_inicio->format('d/m/Y');
            $datos['fecha_vencimiento'] = $inscripcion->fecha_vencimiento->format('d/m/Y');
            $datos['dias_restantes'] = max(0, today()->diffInDays($inscripcion->fecha_vencimiento, false));
            $datos['monto_total'] = number_format($precioBase, 0, ',', '.');
            $datos['monto_pagado'] = number_format($totalPagado, 0, ',', '.');
            $datos['total_pagado'] = number_format($totalPagado, 0, ',', '.');
            $datos['monto_pendiente'] = number_format($saldoPendiente, 0, ',', '.');
            $datos['saldo_pendiente'] = number_format($saldoPendiente, 0, ',', '.');

            // Datos de pausa si aplica
            if ($inscripcion->fecha_pausa_inicio) {
                $datos['fecha_pausa'] = Carbon::parse($inscripcion->fecha_pausa_inicio)->format('d/m/Y');
            }
            if ($inscripcion->fecha_pausa_fin) {
                $datos['fecha_reactivacion'] = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
                $datos['fecha_activacion'] = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
            }

            // Ãšltimo pago
            $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
            if ($ultimoPago) {
                $datos['fecha_pago'] = Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y');
                $datos['monto_ultimo_pago'] = number_format($ultimoPago->monto_abonado, 0, ',', '.');
            }
        }

        return $datos;
    }
}
