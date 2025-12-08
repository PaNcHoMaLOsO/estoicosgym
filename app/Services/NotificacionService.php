<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Models\LogNotificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Programa notificaciones para membresías próximas a vencer
     */
    public function programarNotificacionesPorVencer(): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_POR_VENCER)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado o inactivo'];
        }

        $diasAnticipacion = $tipoNotificacion->dias_anticipacion;
        $fechaObjetivo = Carbon::today()->addDays($diasAnticipacion);

        // Buscar inscripciones que vencen en X días
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100) // Activa
            ->whereDate('fecha_vencimiento', $fechaObjetivo)
            ->whereHas('cliente', function ($q) {
                $q->where('activo', true)
                  ->whereNotNull('email')
                  ->where('email', '!=', '');
            })
            ->get();

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            // Verificar si ya existe una notificación para esta inscripción y tipo
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_ENVIADO])
                ->exists();

            if ($existe) {
                continue;
            }

            $this->crearNotificacion($tipoNotificacion, $inscripcion);
            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de membresías por vencer"
        ];
    }

    /**
     * Programa notificaciones para membresías vencidas hoy
     */
    public function programarNotificacionesVencidas(): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_VENCIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado o inactivo'];
        }

        // Buscar inscripciones que vencen hoy
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100) // Aún activa (se marcará como vencida después)
            ->whereDate('fecha_vencimiento', Carbon::today())
            ->whereHas('cliente', function ($q) {
                $q->where('activo', true)
                  ->whereNotNull('email')
                  ->where('email', '!=', '');
            })
            ->get();

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_ENVIADO])
                ->exists();

            if ($existe) {
                continue;
            }

            $this->crearNotificacion($tipoNotificacion, $inscripcion);
            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de membresías vencidas"
        ];
    }

    /**
     * Carga y procesa plantilla HTML con datos dinámicos
     */
    private function cargarPlantillaHTML(string $nombreArchivo, array $datos): string
    {
        $rutaPlantilla = storage_path("app/test_emails/preview/{$nombreArchivo}");
        
        if (!file_exists($rutaPlantilla)) {
            throw new \Exception("Plantilla no encontrada: {$nombreArchivo}");
        }

        $contenido = file_get_contents($rutaPlantilla);
        
        // Extraer solo el body
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
            $contenido = $matches[1];
        }

        // Reemplazar cada variable
        foreach ($datos as $clave => $valor) {
            $contenido = str_replace($clave, $valor, $contenido);
        }

        return $contenido;
    }

    /**
     * Crea una notificación para una inscripción
     */
    public function crearNotificacion(TipoNotificacion $tipo, Inscripcion $inscripcion): Notificacion
    {
        $cliente = $inscripcion->cliente;
        $membresia = $inscripcion->membresia;

        $diasRestantes = Carbon::today()->diffInDays($inscripcion->fecha_vencimiento, false);

        // Determinar el email destino y nombre del destinatario
        $emailDestino = $cliente->email;
        $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
        
        // Si es menor de edad y tiene email de apoderado, enviar al apoderado
        if ($cliente->es_menor_edad && !empty($cliente->apoderado_email)) {
            $emailDestino = $cliente->apoderado_email;
        }

        // Preparar contenido según tipo de notificación
        $contenido = '';
        $asunto = '';

        try {
            switch ($tipo->codigo) {
                case TipoNotificacion::MEMBRESIA_POR_VENCER:
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        '5 días' => $diasRestantes . ' días',
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                    ];
                    $contenido = $this->cargarPlantillaHTML('03_membresia_por_vencer.html', $datos);
                    $asunto = '⏰ Tu membresía ' . $membresia->nombre . ' vence pronto';
                    break;

                case TipoNotificacion::MEMBRESIA_VENCIDA:
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                    ];
                    $contenido = $this->cargarPlantillaHTML('04_membresia_vencida.html', $datos);
                    $asunto = '❌ Tu membresía ' . $membresia->nombre . ' ha vencido';
                    break;

                case TipoNotificacion::PAGO_COMPLETADO:
                    $ultimoPago = $inscripcion->pagos()->orderBy('id', 'desc')->first();
                    $metodoPago = $ultimoPago ? $ultimoPago->metodoPago->nombre ?? 'No especificado' : 'No especificado';
                    $fechaPago = $ultimoPago ? Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
                    $montoPago = $ultimoPago ? '$' . number_format($ultimoPago->monto_abonado, 0, ',', '.') : '$0';
                    
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        'Trimestral' => $membresia->nombre,
                        '$65.000' => $montoPago,
                        'Transferencia' => $metodoPago,
                        '06/12/2025' => $fechaPago,
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                    ];
                    $contenido = $this->cargarPlantillaHTML('02_pago_completado.html', $datos);
                    $asunto = '✅ Pago completado - PROGYM';
                    break;

                case TipoNotificacion::RENOVACION:
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        'Trimestral' => $membresia->nombre,
                        '06/12/2025' => Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'),
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                    ];
                    $contenido = $this->cargarPlantillaHTML('08_renovacion.html', $datos);
                    $asunto = '🎊 Renovación exitosa - PROGYM';
                    break;

                case TipoNotificacion::PAUSA_INSCRIPCION:
                    $fechaPausa = $inscripcion->fecha_pausa_inicio ? Carbon::parse($inscripcion->fecha_pausa_inicio)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
                    $fechaReactivacion = $inscripcion->fecha_pausa_fin ? Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y') : 'A definir';
                    $motivo = $inscripcion->razon_pausa ?? 'Motivo personal';
                    
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        '06/12/2025' => $fechaPausa,
                        'Viaje por trabajo' => $motivo,
                        '15/01/2026' => $fechaReactivacion,
                    ];
                    $contenido = $this->cargarPlantillaHTML('05_pausa_inscripcion.html', $datos);
                    $asunto = '⏸️ Membresía pausada - PROGYM';
                    break;

                case TipoNotificacion::ACTIVACION_INSCRIPCION:
                    $fechaActivacion = Carbon::now()->format('d/m/Y');
                    
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        '06/12/2025' => $fechaActivacion,
                        'Trimestral' => $membresia->nombre,
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                    ];
                    $contenido = $this->cargarPlantillaHTML('06_activacion_inscripcion.html', $datos);
                    $asunto = '▶️ ¡Bienvenido/a de vuelta! - PROGYM';
                    break;

                case 'confirmacion_tutor_legal':
                    // Esta notificación se envía al tutor legal cuando se registra un menor
                    $clienteMenor = $cliente;
                    $nombreMenor = trim($clienteMenor->nombres . ' ' . $clienteMenor->apellido_paterno);
                    $runMenor = $clienteMenor->run_pasaporte ?? 'No especificado';
                    $fechaNacimientoMenor = $clienteMenor->fecha_nacimiento ? Carbon::parse($clienteMenor->fecha_nacimiento)->format('d/m/Y') : 'No especificada';
                    
                    // Datos del tutor (ya está en $emailDestino si es menor de edad)
                    $nombreTutor = $clienteMenor->apoderado_nombre ?? 'Tutor Legal';
                    $runTutor = $clienteMenor->apoderado_run ?? 'No especificado';
                    
                    $precioTotal = '$' . number_format($inscripcion->precio_final, 0, ',', '.');
                    
                    $datos = [
                        'María González' => $nombreTutor,
                        'Juanito Pérez' => $nombreMenor,
                        '25.555.666-7' => $runMenor,
                        '15/03/2010' => $fechaNacimientoMenor,
                        '11.222.333-4' => $runTutor,
                        'Trimestral' => $membresia->nombre,
                        '06/12/2025' => Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'),
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                        '$$65.000' => $precioTotal,
                    ];
                    $contenido = $this->cargarPlantillaHTML('09_confirmacion_tutor_legal.html', $datos);
                    $asunto = '📋 Confirmación de Tutor Legal - PROGYM';
                    break;

                case TipoNotificacion::PAGO_PENDIENTE:
                    $totalPagado = $inscripcion->pagos()->sum('monto_abonado');
                    $saldoPendiente = $inscripcion->precio_final - $totalPagado;
                    $precioTotal = '$' . number_format($inscripcion->precio_final, 0, ',', '.');
                    $saldoFormateado = '$' . number_format($saldoPendiente, 0, ',', '.');
                    
                    // Reemplazar el formato especial $$25.000 con un placeholder temporal
                    $rutaPlantilla = storage_path('app/test_emails/preview/07_pago_pendiente.html');
                    $contenidoOriginal = file_get_contents($rutaPlantilla);
                    
                    // Extraer solo el body
                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenidoOriginal, $matches)) {
                        $contenidoOriginal = $matches[1];
                    }
                    
                    // Primero reemplazar $$25.000 y $$65.000 para evitar confusión
                    $contenidoOriginal = str_replace('$$25.000', '___SALDO___', $contenidoOriginal);
                    $contenidoOriginal = str_replace('$$65.000', '___TOTAL___', $contenidoOriginal);
                    
                    $datos = [
                        'Juan Pérez' => $nombreCompleto,
                        'Trimestral' => $membresia->nombre,
                        '___SALDO___' => $saldoFormateado,
                        '___TOTAL___' => $precioTotal,
                        '06/03/2026' => Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'),
                        '$65.000' => $precioTotal,
                    ];
                    
                    $contenido = $contenidoOriginal;
                    foreach ($datos as $clave => $valor) {
                        $contenido = str_replace($clave, $valor, $contenido);
                    }
                    
                    $asunto = '💰 Recordatorio de saldo pendiente - PROGYM';
                    break;

                default:
                    // Fallback al método antiguo si no está implementado
                    $datos = [
                        'nombre' => $nombreCompleto,
                        'nombre_cliente' => $nombreCompleto,
                        'es_menor_edad' => $cliente->es_menor_edad,
                        'membresia' => $membresia->nombre,
                        'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
                        'dias_restantes' => max(0, $diasRestantes),
                        'fecha_inicio' => $inscripcion->fecha_inicio->format('d/m/Y'),
                    ];
                    $renderizado = $tipo->renderizar($datos);
                    $contenido = $renderizado['contenido'];
                    $asunto = $renderizado['asunto'];
                    break;
            }
        } catch (\Exception $e) {
            // Si falla, usar método antiguo
            Log::warning("Error al cargar plantilla HTML: " . $e->getMessage());
            $datos = [
                'nombre' => $nombreCompleto,
                'membresia' => $membresia->nombre,
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
                'dias_restantes' => max(0, $diasRestantes),
            ];
            $renderizado = $tipo->renderizar($datos);
            $contenido = $renderizado['contenido'];
            $asunto = $renderizado['asunto'];
        }

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $emailDestino,
            'asunto' => $asunto,
            'contenido' => $contenido,
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => Carbon::today(),
        ]);

        $logMensaje = $cliente->es_menor_edad && !empty($cliente->apoderado_email) 
            ? "Notificación programada para apoderado: {$emailDestino}" 
            : 'Notificación programada automáticamente';
            
        $notificacion->registrarLog('programada', $logMensaje);

        return $notificacion;
    }

    /**
     * Envía las notificaciones pendientes
     */
    public function enviarPendientes(): array
    {
        $notificaciones = Notificacion::paraEnviarHoy()
            ->with(['cliente', 'tipoNotificacion'])
            ->get();

        $enviadas = 0;
        $fallidas = 0;

        foreach ($notificaciones as $notificacion) {
            try {
                $notificacion->registrarLog('enviando', 'Iniciando envío de correo');

                Mail::html($notificacion->contenido, function ($message) use ($notificacion) {
                    $message->to($notificacion->email_destino)
                            ->subject($notificacion->asunto);
                });

                $notificacion->marcarComoEnviada();
                $enviadas++;

                Log::info("Notificación enviada", [
                    'id' => $notificacion->id,
                    'email' => $notificacion->email_destino,
                    'tipo' => $notificacion->tipoNotificacion->codigo ?? 'N/A'
                ]);

            } catch (\Exception $e) {
                $notificacion->marcarComoFallida($e->getMessage());
                $fallidas++;

                Log::error("Error al enviar notificación", [
                    'id' => $notificacion->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'enviadas' => $enviadas,
            'fallidas' => $fallidas,
            'total' => $notificaciones->count(),
            'mensaje' => "Enviadas: {$enviadas}, Fallidas: {$fallidas}"
        ];
    }

    /**
     * Reintenta enviar notificaciones fallidas
     */
    public function reintentarFallidas(): array
    {
        $notificaciones = Notificacion::fallidas()
            ->where('intentos', '<', \DB::raw('max_intentos'))
            ->with(['cliente', 'tipoNotificacion'])
            ->get();

        $reenviadas = 0;
        $fallidasNuevamente = 0;

        foreach ($notificaciones as $notificacion) {
            try {
                $notificacion->registrarLog('reintentando', "Reintento #{$notificacion->intentos}");
                $notificacion->update(['id_estado' => Notificacion::ESTADO_PENDIENTE]);

                Mail::html($notificacion->contenido, function ($message) use ($notificacion) {
                    $message->to($notificacion->email_destino)
                            ->subject($notificacion->asunto);
                });

                $notificacion->marcarComoEnviada();
                $reenviadas++;

            } catch (\Exception $e) {
                $notificacion->marcarComoFallida($e->getMessage());
                $fallidasNuevamente++;
            }
        }

        return [
            'reenviadas' => $reenviadas,
            'fallidas' => $fallidasNuevamente,
            'mensaje' => "Reenviadas: {$reenviadas}, Fallidas nuevamente: {$fallidasNuevamente}"
        ];
    }

    /**
     * Envía notificación de bienvenida a un cliente nuevo
     */
    public function enviarBienvenida(Inscripcion $inscripcion): ?Notificacion
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion || !$inscripcion->cliente->email) {
            return null;
        }

        return $this->crearNotificacion($tipoNotificacion, $inscripcion);
    }

    /**
     * Obtiene estadísticas de notificaciones
     */
    public function obtenerEstadisticas(): array
    {
        return [
            'pendientes' => Notificacion::pendientes()->count(),
            'enviadas_hoy' => Notificacion::enviadas()
                ->whereDate('fecha_envio', Carbon::today())
                ->count(),
            'enviadas_mes' => Notificacion::enviadas()
                ->whereMonth('fecha_envio', Carbon::now()->month)
                ->whereYear('fecha_envio', Carbon::now()->year)
                ->count(),
            'fallidas' => Notificacion::fallidas()->count(),
            'total' => Notificacion::count(),
        ];
    }

    /**
     * Envía notificación de renovación exitosa
     * 
     * @param Inscripcion $inscripcion La nueva inscripción (renovada)
     * @return Notificacion|null
     */
    public function enviarNotificacionRenovacion(Inscripcion $inscripcion): ?Notificacion
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::RENOVACION_EXITOSA)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion || !$inscripcion->cliente->email) {
            return null;
        }

        $cliente = $inscripcion->cliente;
        $membresia = $inscripcion->membresia;

        $datos = [
            'nombre' => $cliente->nombre_completo,
            'membresia' => $membresia->nombre,
            'fecha_inicio' => $inscripcion->fecha_inicio->format('d/m/Y'),
            'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
            'dias_vigencia' => $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento),
            'precio' => number_format($inscripcion->precio_final, 0, ',', '.'),
        ];

        $renderizado = $tipoNotificacion->renderizar($datos);

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipoNotificacion->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $cliente->email,
            'asunto' => $renderizado['asunto'],
            'contenido' => $renderizado['contenido'],
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => Carbon::today(),
        ]);

        $notificacion->registrarLog('programada', 'Notificación de renovación programada');

        // Intentar enviar inmediatamente
        try {
            Mail::html($notificacion->contenido, function ($message) use ($notificacion) {
                $message->to($notificacion->email_destino)
                        ->subject($notificacion->asunto);
            });
            $notificacion->marcarComoEnviada();
        } catch (\Exception $e) {
            Log::warning('Notificación de renovación quedó pendiente: ' . $e->getMessage());
        }

        return $notificacion;
    }

    /**
     * Programa notificaciones de pago pendiente
     * 
     * @param int $diasVencimiento Días desde que venció el pago
     * @return array
     */
    public function programarNotificacionesPagoPendiente(int $diasVencimiento = 7): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::PAGO_PENDIENTE)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado'];
        }

        // Buscar inscripciones activas con pagos pendientes hace X días
        $inscripciones = Inscripcion::with(['cliente', 'membresia', 'pagos'])
            ->where('id_estado', 100) // Activa
            ->whereHas('cliente', function ($q) {
                $q->where('activo', true)
                  ->whereNotNull('email')
                  ->where('email', '!=', '');
            })
            ->get()
            ->filter(function ($inscripcion) {
                $estadoPago = $inscripcion->obtenerEstadoPago();
                return $estadoPago['estado'] !== 'Pagado' && $estadoPago['pendiente'] > 0;
            });

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            // Verificar si ya se envió notificación reciente
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->where('fecha_programada', '>=', Carbon::today()->subDays($diasVencimiento))
                ->exists();

            if ($existe) {
                continue;
            }

            $cliente = $inscripcion->cliente;
            $estadoPago = $inscripcion->obtenerEstadoPago();

            $datos = [
                'nombre' => $cliente->nombre_completo,
                'membresia' => $inscripcion->membresia->nombre,
                'monto_pendiente' => number_format($estadoPago['pendiente'], 0, ',', '.'),
                'monto_total' => number_format($inscripcion->precio_final, 0, ',', '.'),
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
            ];

            $renderizado = $tipoNotificacion->renderizar($datos);

            Notificacion::create([
                'id_tipo_notificacion' => $tipoNotificacion->id,
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $inscripcion->id,
                'email_destino' => $cliente->email,
                'asunto' => $renderizado['asunto'],
                'contenido' => $renderizado['contenido'],
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => Carbon::today(),
            ]);

            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de pago pendiente"
        ];
    }

    /**
     * Enviar notificación de bienvenida automáticamente al crear inscripción
     */
    /**
     * Envía notificación de confirmación al tutor legal cuando se registra un menor
     */
    public function enviarNotificacionTutorLegal(Inscripcion $inscripcion): array
    {
        // Cargar relaciones necesarias
        $inscripcion->load(['cliente', 'membresia']);
        $cliente = $inscripcion->cliente;

        // Verificar que es menor de edad y tiene datos del tutor
        if (!$cliente->es_menor_edad || empty($cliente->apoderado_email)) {
            return [
                'enviada' => false,
                'mensaje' => 'Cliente no es menor de edad o no tiene email de apoderado'
            ];
        }

        // Cargar plantilla
        $rutaPlantilla = storage_path('app/test_emails/preview/09_confirmacion_tutor_legal.html');
        if (!file_exists($rutaPlantilla)) {
            return [
                'enviada' => false,
                'mensaje' => 'Plantilla de confirmación de tutor no encontrada'
            ];
        }

        $contenido = file_get_contents($rutaPlantilla);

        // Extraer solo el body
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
            $contenido = $matches[1];
        }

        // Preparar datos dinámicos
        $nombreMenor = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
        $runMenor = $cliente->run_pasaporte ?? 'No especificado';
        $fechaNacimientoMenor = $cliente->fecha_nacimiento ? Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : 'No especificada';
        $nombreTutor = $cliente->apoderado_nombre ?? 'Tutor Legal';
        $runTutor = $cliente->apoderado_run ?? 'No especificado';
        $precioTotal = '$' . number_format($inscripcion->precio_final, 0, ',', '.');

        // Reemplazar variables
        $contenido = str_replace('María González', $nombreTutor, $contenido);
        $contenido = str_replace('Juanito Pérez', $nombreMenor, $contenido);
        $contenido = str_replace('25.555.666-7', $runMenor, $contenido);
        $contenido = str_replace('15/03/2010', $fechaNacimientoMenor, $contenido);
        $contenido = str_replace('11.222.333-4', $runTutor, $contenido);
        $contenido = str_replace('Trimestral', $inscripcion->membresia->nombre, $contenido);
        $contenido = str_replace('06/12/2025', Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'), $contenido);
        $contenido = str_replace('06/03/2026', Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'), $contenido);
        $contenido = str_replace('$$65.000', $precioTotal, $contenido);

        // Crear registro de notificación (buscar tipo o crear temporal)
        $tipoTutor = TipoNotificacion::where('codigo', 'confirmacion_tutor_legal')->first();
        
        if (!$tipoTutor) {
            // Si no existe el tipo, usar notificacion_manual como fallback
            $tipoTutor = TipoNotificacion::where('codigo', TipoNotificacion::NOTIFICACION_MANUAL)->first();
        }

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipoTutor->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $cliente->apoderado_email,
            'asunto' => '📋 Confirmación de Tutor Legal - PROGYM',
            'contenido' => $contenido,
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => Carbon::now(),
        ]);

        // Intentar enviar inmediatamente
        try {
            $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
                'from' => 'PROGYM <onboarding@resend.dev>',
                'to' => [$cliente->apoderado_email],
                'subject' => $notificacion->asunto,
                'html' => $contenido,
            ]);

            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_ENVIADO,
                'fecha_envio' => Carbon::now(),
                'id_email_proveedor' => $resultado->id ?? null,
            ]);

            LogNotificacion::create([
                'id_notificacion' => $notificacion->id,
                'accion' => LogNotificacion::ACCION_ENVIADA,
                'detalle' => json_encode(['resend_id' => $resultado->id, 'tipo' => 'tutor_legal']),
            ]);

            return [
                'enviada' => true,
                'mensaje' => 'Notificación enviada al tutor legal',
                'notificacion_id' => $notificacion->id
            ];

        } catch (\Exception $e) {
            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_FALLIDO,
                'intentos_envio' => 1,
            ]);

            LogNotificacion::create([
                'id_notificacion' => $notificacion->id,
                'accion' => 'fallida',
                'detalle' => json_encode(['error' => $e->getMessage()]),
            ]);

            return [
                'enviada' => false,
                'mensaje' => 'Error al enviar: ' . $e->getMessage(),
                'notificacion_id' => $notificacion->id
            ];
        }
    }

    /**
     * Envía notificación de bienvenida automática
     */
    public function enviarNotificacionBienvenida(Inscripcion $inscripcion): array
    {
        // Buscar tipo de notificación de bienvenida
        $tipoBienvenida = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoBienvenida) {
            return [
                'enviada' => false,
                'mensaje' => 'Tipo de notificación de bienvenida no encontrado o inactivo'
            ];
        }

        // Cargar relaciones necesarias
        $inscripcion->load(['cliente', 'membresia']);
        $cliente = $inscripcion->cliente;

        // Validar que el cliente tenga email
        if (!$cliente || !$cliente->email) {
            return [
                'enviada' => false,
                'mensaje' => 'Cliente sin email registrado'
            ];
        }

        // Verificar si ya existe una notificación de bienvenida para esta inscripción
        $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
            ->where('id_tipo_notificacion', $tipoBienvenida->id)
            ->exists();

        if ($existe) {
            return [
                'enviada' => false,
                'mensaje' => 'Ya existe una notificación de bienvenida para esta inscripción'
            ];
        }

        // Cargar plantilla de bienvenida
        $rutaPlantilla = storage_path('app/test_emails/preview/01_bienvenida.html');
        if (!file_exists($rutaPlantilla)) {
            return [
                'enviada' => false,
                'mensaje' => 'Plantilla de bienvenida no encontrada'
            ];
        }

        $contenido = file_get_contents($rutaPlantilla);

        // Extraer solo el body
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $contenido, $matches)) {
            $contenido = $matches[1];
        }

        // Obtener información del pago más reciente
        $pago = $inscripcion->pagos()->orderBy('id', 'desc')->first();
        
        // Calcular montos
        $totalPagado = $inscripcion->pagos()->sum('monto_abonado');
        $saldoPendienteNum = $inscripcion->precio_final - $totalPagado;
        
        // Formatear valores
        $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
        $precioFinal = '$' . number_format($inscripcion->precio_final, 0, ',', '.');
        $montoPagado = '$' . number_format($totalPagado, 0, ',', '.');
        $saldoPendiente = '$' . number_format($saldoPendienteNum, 0, ',', '.');
        $tipoPago = $saldoPendienteNum > 0 ? 'Parcial' : 'Completo';
        
        // Reemplazar variables en la plantilla
        $contenido = str_replace('Juan Pérez', $nombreCompleto, $contenido);
        $contenido = str_replace('Trimestral', $inscripcion->membresia->nombre, $contenido);
        $contenido = str_replace('$65.000', $precioFinal, $contenido);
        $contenido = str_replace('06/12/2025', \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'), $contenido);
        $contenido = str_replace('06/03/2026', \Carbon\Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'), $contenido);
        $contenido = str_replace('Parcial', $tipoPago, $contenido);
        $contenido = str_replace('$40.000', $montoPagado, $contenido);
        $contenido = str_replace('$25.000', $saldoPendiente, $contenido);

        // Crear registro de notificación
        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipoBienvenida->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $cliente->email,
            'asunto' => '🎉 ¡Bienvenido a PROGYM Los Ángeles!',
            'contenido' => $contenido,
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => Carbon::now(),
        ]);

        // Intentar enviar inmediatamente
        try {
            $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
                'from' => 'PROGYM <onboarding@resend.dev>',
                'to' => [$cliente->email],
                'subject' => $notificacion->asunto,
                'html' => $contenido,
            ]);

            // Actualizar estado a enviado
            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_ENVIADO,
                'fecha_envio' => Carbon::now(),
                'id_email_proveedor' => $resultado->id ?? null,
            ]);

            // Log de éxito
            LogNotificacion::create([
                'id_notificacion' => $notificacion->id,
                'accion' => 'enviada',
                'detalle' => json_encode(['resend_id' => $resultado->id]),
            ]);

            return [
                'enviada' => true,
                'mensaje' => 'Notificación de bienvenida enviada exitosamente',
                'notificacion_id' => $notificacion->id
            ];

        } catch (\Exception $e) {
            // Marcar como fallida
            $notificacion->update([
                'id_estado' => Notificacion::ESTADO_FALLIDO,
                'intentos_envio' => 1,
            ]);

            // Log de error
            LogNotificacion::create([
                'id_notificacion' => $notificacion->id,
                'accion' => 'fallida',
                'detalle' => json_encode(['error' => $e->getMessage()]),
            ]);

            return [
                'enviada' => false,
                'mensaje' => 'Error al enviar notificación: ' . $e->getMessage(),
                'notificacion_id' => $notificacion->id
            ];
        }
    }
}
