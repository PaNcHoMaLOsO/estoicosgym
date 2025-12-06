<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Cliente;
use App\Models\Inscripcion;

class SimularNotificacionesCommand extends Command
{
    protected $signature = 'simular:notificaciones {email}';
    protected $description = 'Simula el envío de notificaciones con datos reales de clientes';

    public function handle()
    {
        $emailDestino = $this->argument('email');
        
        $this->info("🔄 Iniciando simulación de notificaciones...");
        $this->info("📧 Todos los correos se enviarán a: {$emailDestino}");
        $this->newLine();

        // Obtener clientes activos con inscripciones, excluyendo Pase Diario
        $clientes = Cliente::with(['inscripciones.membresia', 'inscripciones.pagos'])
            ->whereHas('inscripciones', function($query) {
                $query->whereHas('membresia', function($q) {
                    $q->whereNotIn('nombre', ['Pase Diario', 'pase diario', 'PASE DIARIO']);
                });
            })
            ->take(15)
            ->get();

        if ($clientes->isEmpty()) {
            $this->error('❌ No hay clientes con inscripciones en la base de datos');
            return 1;
        }

        $this->info("✅ Encontrados {$clientes->count()} clientes para simular");
        $this->newLine();

        $enviados = 0;
        $errores = 0;

        // Tipos de notificaciones para probar todas las plantillas
        $tiposNotificacion = ['bienvenida', 'membresia_por_vencer', 'membresia_vencida', 'pago_pendiente', 'pausa_inscripcion', 'activacion_inscripcion', 'pago_completado'];
        $indiceCliente = 0;

        foreach ($clientes as $cliente) {
            $inscripcion = $cliente->inscripciones->first();
            
            if (!$inscripcion) continue;

            // Verificar si la membresía es recurrente
            $nombreMembresia = strtolower($inscripcion->membresia->nombre ?? '');
            $membresiasPermitidas = ['mensual', 'trimestral', 'semestral', 'anual'];
            
            $esRecurrente = false;
            foreach ($membresiasPermitidas as $tipo) {
                if (strpos($nombreMembresia, $tipo) !== false) {
                    $esRecurrente = true;
                    break;
                }
            }
            
            if (!$esRecurrente) {
                $this->warn("  ⏭️  Membresía '{$nombreMembresia}' no aplica para notificaciones automáticas");
                continue;
            }

            // Asignar un tipo diferente a cada cliente para probar todas las plantillas
            $tipoNotificacion = $tiposNotificacion[$indiceCliente % count($tiposNotificacion)];
            $indiceCliente++;

            $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
            $this->line("👤 Procesando: {$nombreCompleto} ({$nombreMembresia}) - Tipo: {$tipoNotificacion}");

            // Obtener plantilla
            $plantilla = DB::table('tipo_notificaciones')
                ->where('codigo', $tipoNotificacion)
                ->first();

            if (!$plantilla) {
                $this->error("  ❌ Plantilla '{$tipoNotificacion}' no encontrada");
                $errores++;
                continue;
            }

            // Preparar datos
            $datos = $this->prepararDatos($cliente, $inscripcion, $tipoNotificacion);
            
            // Reemplazar variables en la plantilla
            $contenidoEmail = $plantilla->plantilla_email;
            $asuntoEmail = $plantilla->asunto_email;

            foreach ($datos as $variable => $valor) {
                $contenidoEmail = str_replace("{{$variable}}", $valor, $contenidoEmail);
                $asuntoEmail = str_replace("{{$variable}}", $valor, $asuntoEmail);
            }

            // Enviar email
            try {
                Mail::html($contenidoEmail, function ($message) use ($emailDestino, $asuntoEmail) {
                    $message->to($emailDestino)
                            ->subject($asuntoEmail);
                });

                $this->info("  ✅ Enviado: {$plantilla->nombre}");
                $enviados++;
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error al enviar: " . $e->getMessage());
                $errores++;
            }

            // Pequeña pausa entre envíos
            usleep(500000); // 0.5 segundos
        }

        $this->newLine();
        $this->info("📊 Resumen:");
        $this->info("   ✅ Enviados: {$enviados}");
        $this->info("   ❌ Errores: {$errores}");
        $this->info("   📬 Revisa la bandeja: {$emailDestino}");

        return 0;
    }

    private function determinarTipoNotificacion($inscripcion)
    {
        // Filtrar solo membresías recurrentes (excluir Pase Diario)
        $nombreMembresia = strtolower($inscripcion->membresia->nombre ?? '');
        $membresiasPermitidas = ['mensual', 'trimestral', 'semestral', 'anual'];
        
        $esRecurrente = false;
        foreach ($membresiasPermitidas as $tipo) {
            if (strpos($nombreMembresia, $tipo) !== false) {
                $esRecurrente = true;
                break;
            }
        }
        
        if (!$esRecurrente) {
            return null; // No enviar notificaciones para Pase Diario u otras
        }

        $fechaVencimiento = \Carbon\Carbon::parse($inscripcion->fecha_vencimiento);
        $hoy = \Carbon\Carbon::now();
        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);

        // Si el cliente acaba de completar un pago pendiente/parcial (pago en los últimos 3 días)
        if ($inscripcion->pagos->count() > 1) {
            $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
            if ($ultimoPago) {
                $fechaUltimoPago = \Carbon\Carbon::parse($ultimoPago->fecha_pago);
                if ($hoy->diffInDays($fechaUltimoPago) <= 3 && $ultimoPago->monto_pendiente == 0) {
                    // Verificar que había un pago anterior con saldo pendiente
                    $pagoAnterior = $inscripcion->pagos->sortByDesc('fecha_pago')->skip(1)->first();
                    if ($pagoAnterior && $pagoAnterior->monto_pendiente > 0) {
                        return 'pago_completado';
                    }
                }
            }
        }

        // Si la inscripción está pausada
        if ($inscripcion->activo === false && $inscripcion->fecha_pausa_inicio) {
            return 'pausa_inscripcion';
        }

        // Si la inscripción fue reactivada recientemente (menos de 2 días)
        if ($inscripcion->fecha_pausa_fin && $inscripcion->activo === true) {
            $fechaReactivacion = \Carbon\Carbon::parse($inscripcion->fecha_pausa_fin);
            if ($hoy->diffInDays($fechaReactivacion) <= 2) {
                return 'activacion_inscripcion';
            }
        }

        // Si la inscripción es reciente (menos de 7 días desde inicio)
        $fechaInicio = \Carbon\Carbon::parse($inscripcion->fecha_inicio);
        if ($hoy->diffInDays($fechaInicio) <= 7) {
            return 'bienvenida';
        }

        // Si está vencida
        if ($diasRestantes < 0) {
            return 'membresia_vencida';
        }

        // Si está por vencer (solo 3 días o menos)
        if ($diasRestantes >= 0 && $diasRestantes <= 3) {
            return 'membresia_por_vencer';
        }

        // Si tiene pagos pendientes o parciales
        $totalPagado = $inscripcion->pagos->sum('monto');
        if ($totalPagado < $inscripcion->precio_base) {
            return 'pago_pendiente';
        }

        return null;
    }

    private function prepararDatos($cliente, $inscripcion, $tipo)
    {
        $fechaVencimiento = \Carbon\Carbon::parse($inscripcion->fecha_vencimiento ?? $inscripcion->fecha_fin);
        $hoy = \Carbon\Carbon::now();
        $diasRestantes = max(0, $hoy->diffInDays($fechaVencimiento, false));

        $totalPagado = $inscripcion->pagos->sum('monto');
        $montoPendiente = $inscripcion->precio_base - $totalPagado;

        // Construir nombre completo
        $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
        
        // Determinar tipo de pago
        $tipoPago = 'Pendiente';
        $colorSaldo = '#E0001A'; // Rojo por defecto
        
        if ($totalPagado == 0) {
            $tipoPago = 'Pendiente';
            $colorSaldo = '#E0001A';
        } elseif ($totalPagado < $inscripcion->precio_base) {
            $tipoPago = 'Parcial';
            $colorSaldo = '#FFC107'; // Amarillo
        } elseif ($totalPagado >= $inscripcion->precio_base) {
            $tipoPago = 'Completo';
            $colorSaldo = '#2EB872'; // Verde
            $montoPendiente = 0; // Si pagó completo, el saldo es 0
        }
        
        // Si hay múltiples pagos, es pago mixto
        if ($inscripcion->pagos->count() > 1) {
            $tipoPago = 'Mixto (' . $inscripcion->pagos->count() . ' pagos)';
        }
        
        $datos = [
            'nombre' => $nombreCompleto,
            'apellido' => $cliente->apellido_paterno,
            'email' => $cliente->email,
            'membresia' => $inscripcion->membresia->nombre ?? 'Membresía',
            'fecha_inicio' => \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y'),
            'fecha_vencimiento' => $fechaVencimiento->format('d/m/Y'),
            'dias_restantes' => $diasRestantes,
            'monto_total' => number_format($inscripcion->precio_base, 0, ',', '.'),
            'monto_pagado' => number_format($totalPagado, 0, ',', '.'),
            'monto_pendiente' => number_format($montoPendiente, 0, ',', '.'),
            'tipo_pago' => $tipoPago,
            'color_saldo' => $colorSaldo,
        ];

        // Agregar datos específicos para pausa
        if ($tipo === 'pausa_inscripcion' && $inscripcion->fecha_pausa_inicio) {
            $fechaPausa = \Carbon\Carbon::parse($inscripcion->fecha_pausa_inicio);
            $datos['fecha_pausa'] = $fechaPausa->format('d/m/Y');
            
            // Si hay fecha de reactivación programada, usarla; sino estimar 30 días
            if ($inscripcion->fecha_pausa_fin) {
                $fechaReactivacion = \Carbon\Carbon::parse($inscripcion->fecha_pausa_fin);
            } else {
                $fechaReactivacion = $fechaPausa->copy()->addDays(30);
            }
            $datos['fecha_reactivacion'] = $fechaReactivacion->format('d/m/Y');
        }

        // Agregar datos específicos para activación
        if ($tipo === 'activacion_inscripcion') {
            $fechaActivacion = $inscripcion->fecha_pausa_fin 
                ? \Carbon\Carbon::parse($inscripcion->fecha_pausa_fin)
                : $hoy;
            $datos['fecha_activacion'] = $fechaActivacion->format('d/m/Y');
            
            // Calcular días pausados
            if ($inscripcion->fecha_pausa_inicio) {
                $fechaPausa = \Carbon\Carbon::parse($inscripcion->fecha_pausa_inicio);
                $diasPausados = $fechaPausa->diffInDays($fechaActivacion);
                $datos['dias_pausados'] = $diasPausados;
            } else {
                $datos['dias_pausados'] = 0;
            }
        }

        // Agregar datos específicos para pago completado
        if ($tipo === 'pago_completado') {
            $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
            if ($ultimoPago) {
                $datos['fecha_pago'] = \Carbon\Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y');
                $datos['monto_abonado'] = number_format($ultimoPago->monto_abonado, 0, ',', '.');
                $datos['saldo_pendiente'] = number_format($ultimoPago->monto_pendiente, 0, ',', '.');
                $datos['cantidad_pagos'] = $inscripcion->pagos->count();
                
                // Mensaje dinámico según si completó el pago o aún queda saldo
                if ($ultimoPago->monto_pendiente == 0) {
                    $datos['mensaje_estado'] = '¡Felicidades! Has completado el pago de tu membresía';
                    $datos['color_saldo'] = '#2EB872'; // Verde
                } else {
                    $datos['mensaje_estado'] = 'Gracias por tu abono. Tu saldo ha sido actualizado';
                    $datos['color_saldo'] = '#FFC107'; // Amarillo
                }
            }
        }

        return $datos;
    }
}
