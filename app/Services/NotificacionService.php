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
     * Crea una notificación para una inscripción
     */
    public function crearNotificacion(TipoNotificacion $tipo, Inscripcion $inscripcion): Notificacion
    {
        $cliente = $inscripcion->cliente;
        $membresia = $inscripcion->membresia;

        $diasRestantes = Carbon::today()->diffInDays($inscripcion->fecha_vencimiento, false);

        // Determinar el email destino y nombre del destinatario
        $emailDestino = $cliente->email;
        $nombreDestinatario = $cliente->nombre_completo;
        
        // Si es menor de edad y tiene email de apoderado, enviar al apoderado
        if ($cliente->es_menor_edad && !empty($cliente->apoderado_email)) {
            $emailDestino = $cliente->apoderado_email;
            $nombreDestinatario = $cliente->apoderado_nombre ?: 'Apoderado/a';
        }

        $datos = [
            'nombre' => $nombreDestinatario,
            'nombre_cliente' => $cliente->nombre_completo,  // Nombre del menor para el template
            'es_menor_edad' => $cliente->es_menor_edad,
            'membresia' => $membresia->nombre,
            'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
            'dias_restantes' => max(0, $diasRestantes),
            'fecha_inicio' => $inscripcion->fecha_inicio->format('d/m/Y'),
        ];

        $renderizado = $tipo->renderizar($datos);

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $emailDestino,
            'asunto' => $renderizado['asunto'],
            'contenido' => $renderizado['contenido'],
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

        // Reemplazar variables
        $contenido = str_replace('{nombre}', $cliente->nombres, $contenido);
        $contenido = str_replace('{apellido}', $cliente->apellido_paterno, $contenido);
        $contenido = str_replace('{membresia}', $inscripcion->membresia->nombre, $contenido);
        $contenido = str_replace('{fecha_inicio}', Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'), $contenido);
        $contenido = str_replace('{fecha_vencimiento}', Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y'), $contenido);
        
        $precioMembresia = $inscripcion->membresia->precios()->orderBy('id', 'desc')->first();
        $monto = $precioMembresia ? '$' . number_format($precioMembresia->precio_normal, 0, ',', '.') : '$0';
        $contenido = str_replace('{monto}', $monto, $contenido);

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
                'accion' => 'enviado',
                'resultado' => 'exito',
                'detalles' => json_encode(['resend_id' => $resultado->id]),
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
                'accion' => 'envio_fallido',
                'resultado' => 'error',
                'detalles' => json_encode(['error' => $e->getMessage()]),
            ]);

            return [
                'enviada' => false,
                'mensaje' => 'Error al enviar notificación: ' . $e->getMessage(),
                'notificacion_id' => $notificacion->id
            ];
        }
    }
}
