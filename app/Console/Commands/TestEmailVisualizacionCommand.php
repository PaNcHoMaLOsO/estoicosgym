<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\TipoNotificacion;
use Resend\Laravel\Facades\Resend;
use Carbon\Carbon;

class TestEmailVisualizacionCommand extends Command
{
    protected $signature = 'test:email-visual {--html : Guardar HTML en archivos en lugar de enviar}';
    protected $description = 'Genera TODAS las plantillas con datos REALES para verificación visual';

    public function handle()
    {
        $guardarHTML = $this->option('html');
        
        $this->info("📧 TEST DE VISUALIZACIÓN DE EMAILS");
        $this->info("═══════════════════════════════════════════");
        $this->info("🎯 Modo: " . ($guardarHTML ? "Guardar HTML" : "Enviar emails"));
        $this->newLine();

        // Emails de clientes de prueba
        $emailsTest = [
            'test.nuevo@progym.test',      // Pago completo
            'test.parcial@progym.test',    // Pago parcial
            'test.pendiente@progym.test',  // Sin pagos
            'test.mixto@progym.test',      // Pago mixto
            'test.completado@progym.test', // Completa pago
            'test.porvencer@progym.test',  // Por vencer
            'test.vencido@progym.test',    // Vencido
            'test.deuda@progym.test',      // Con deuda
            'test.pausado@progym.test',    // Pausado
            'test.reactivado@progym.test', // Reactivado
        ];

        $clientes = Cliente::with(['inscripciones' => function($query) {
                $query->latest()->limit(1);
            }, 'inscripciones.membresia', 'inscripciones.pagos', 'inscripciones.precioAcordado'])
            ->whereIn('email', $emailsTest)
            ->get();

        if ($clientes->isEmpty()) {
            $this->error('❌ No hay clientes de prueba. Ejecuta: php artisan db:seed --class=ClientesTestSeeder');
            return 1;
        }

        $this->info("📊 Encontrados {$clientes->count()} clientes de prueba");
        $this->newLine();

        $enviados = 0;

        foreach ($clientes as $cliente) {
            $inscripcion = $cliente->inscripciones->first();
            
            if (!$inscripcion || !$inscripcion->membresia) {
                continue;
            }

            $tipoNotificacion = $this->determinarTipoNotificacion($inscripcion);
            
            if ($tipoNotificacion === 'sin_notificacion') {
                continue;
            }

            $plantilla = TipoNotificacion::where('codigo', $tipoNotificacion)->first();
            
            if (!$plantilla) {
                $this->warn("⚠️  Plantilla '{$tipoNotificacion}' no encontrada");
                continue;
            }

            $datos = $this->prepararDatosCompletos($cliente, $inscripcion, $tipoNotificacion);
            $htmlFinal = $this->reemplazarVariables($plantilla->plantilla_email, $datos);

            if ($guardarHTML) {
                // Guardar como archivo HTML
                $nombreArchivo = str_pad($enviados + 1, 2, '0', STR_PAD_LEFT) . "_" . $tipoNotificacion . ".html";
                $rutaArchivo = storage_path("app/test_emails/{$nombreArchivo}");
                
                if (!file_exists(dirname($rutaArchivo))) {
                    mkdir(dirname($rutaArchivo), 0777, true);
                }
                
                file_put_contents($rutaArchivo, $htmlFinal);
                
                $enviados++;
                $emoji = $this->obtenerEmoji($tipoNotificacion);
                $this->info("{$emoji} #{$enviados} Guardado: {$nombreArchivo}");
                $this->line("   Cliente: {$cliente->nombres} {$cliente->apellido_paterno}");
                $this->line("   Membresía: {$inscripcion->membresia->nombre}");
                $this->line("   📋 Datos incluidos:");
                
                // Mostrar datos clave para verificación
                $this->line("      • total_pagado: {$datos['total_pagado']}");
                $this->line("      • saldo_pendiente: {$datos['saldo_pendiente']}");
                $this->line("      • tipo_pago: {$datos['tipo_pago']}");
                $this->line("      • dias_extendidos: {$datos['dias_extendidos']}");
                $this->line("      • cantidad_pagos: {$datos['cantidad_pagos']}");
                $this->newLine();
            } else {
                // Enviar por email (modo original - requiere dominio verificado)
                try {
                    Resend::emails()->send([
                        'from' => 'PROGYM <estoicosgymlosangeles@gmail.com>',
                        'to' => ['delivered@resend.dev'], // Email de prueba de Resend
                        'subject' => "[TEST {$tipoNotificacion}] " . $plantilla->asunto_email,
                        'html' => $htmlFinal,
                    ]);

                    $enviados++;
                    $emoji = $this->obtenerEmoji($tipoNotificacion);
                    $this->info("{$emoji} Email #{$enviados} enviado: {$tipoNotificacion}");
                    $this->newLine();

                    sleep(2); // Resend permite 2 req/seg

                } catch (\Exception $e) {
                    $this->error("❌ Error enviando {$tipoNotificacion}: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════");
        
        if ($guardarHTML) {
            $this->info("✅ Prueba completada: {$enviados} archivos HTML generados");
            $this->info("📂 Ubicación: storage/app/test_emails/");
            $this->newLine();
            $this->info("🌐 Abre los archivos en tu navegador para verificar:");
            $this->line("   • Variables de monto, saldo y días");
            $this->line("   • Colores y estilos correctos");
            $this->line("   • Información completa de cada cliente");
        } else {
            $this->info("✅ Prueba completada: {$enviados} emails enviados");
        }

        return 0;
    }

    private function prepararDatosCompletos($cliente, $inscripcion, $tipo)
    {
        $hoy = Carbon::now();
        $fechaInicio = Carbon::parse($inscripcion->fecha_inicio);
        $fechaVencimiento = Carbon::parse($inscripcion->fecha_vencimiento);
        
        // Calcular totales de pagos
        $totalPagado = $inscripcion->pagos->sum('monto_abonado');
        // Usar precio_final de la inscripción (es el precio acordado al momento de inscribirse)
        $precioBase = $inscripcion->precio_final ?? $inscripcion->precio_base ?? 0;
        $saldoPendiente = $precioBase - $totalPagado;
        $cantidadPagos = $inscripcion->pagos->count();

        // Calcular días extendidos por pausa
        $diasExtendidos = 0;
        if ($inscripcion->fecha_pausa_inicio && $inscripcion->fecha_pausa_fin) {
            $fechaPausa = Carbon::parse($inscripcion->fecha_pausa_inicio);
            $fechaReactivacion = Carbon::parse($inscripcion->fecha_pausa_fin);
            $diasExtendidos = $fechaPausa->diffInDays($fechaReactivacion);
        }

        // Determinar tipo de pago
        $tipoPago = 'Pendiente';
        if ($totalPagado >= $precioBase) {
            $tipoPago = 'Completo';
        } elseif ($totalPagado > 0) {
            if ($cantidadPagos > 1) {
                $tipoPago = 'Mixto';
            } else {
                $tipoPago = 'Parcial';
            }
        }

        $datos = [
            // Nombres usados por las plantillas
            'nombre' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
            'apellido' => $cliente->apellido_paterno,
            'membresia' => $inscripcion->membresia->nombre,
            'fecha_inicio' => $fechaInicio->format('d/m/Y'),
            'fecha_vencimiento' => $fechaVencimiento->format('d/m/Y'),
            'monto_total' => number_format($precioBase, 0, ',', '.'),
            'monto_pagado' => number_format($totalPagado, 0, ',', '.'),
            'monto_pendiente' => number_format($saldoPendiente, 0, ',', '.'),
            'cantidad_pagos' => $cantidadPagos,
            'tipo_pago' => $tipoPago,
            'dias_extendidos' => $diasExtendidos,
            
            // Alias para reporte en consola
            'nombre_cliente' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
            'email_cliente' => $cliente->email,
            'nombre_membresia' => $inscripcion->membresia->nombre,
            'precio_base' => number_format($precioBase, 0, ',', '.'),
            'total_pagado' => number_format($totalPagado, 0, ',', '.'),
            'saldo_pendiente' => number_format($saldoPendiente, 0, ',', '.'),
        ];

        // Colores según estado de pago
        $datos['color_saldo'] = '#2EB872'; // Verde por defecto
        if ($saldoPendiente > 0) {
            $datos['color_saldo'] = '#FFC107'; // Amarillo si hay saldo
        }
        if ($totalPagado == 0) {
            $datos['color_saldo'] = '#E0001A'; // Rojo si no ha pagado nada
        }

        // Datos específicos por tipo de notificación
        switch ($tipo) {
            case 'membresia_por_vencer':
                $diasRestantes = $hoy->diffInDays($fechaVencimiento);
                $datos['dias_restantes'] = $diasRestantes;
                break;

            case 'membresia_vencida':
                $diasVencido = $hoy->diffInDays($fechaVencimiento);
                $datos['dias_vencido'] = $diasVencido;
                break;

            case 'pago_pendiente':
                $datos['fecha_ultimo_aviso'] = $hoy->subDays(15)->format('d/m/Y');
                break;

            case 'pausa_inscripcion':
                if ($inscripcion->fecha_pausa_inicio) {
                    $datos['fecha_pausa'] = Carbon::parse($inscripcion->fecha_pausa_inicio)->format('d/m/Y');
                }
                if ($inscripcion->fecha_pausa_fin) {
                    $datos['fecha_reactivacion'] = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
                }
                break;

            case 'activacion_inscripcion':
                if ($inscripcion->fecha_pausa_fin) {
                    $datos['fecha_activacion'] = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
                }
                $datos['dias_pausados'] = $diasExtendidos;
                $datos['nueva_fecha_vencimiento'] = $fechaVencimiento->format('d/m/Y');
                break;

            case 'pago_completado':
                $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
                if ($ultimoPago) {
                    $datos['fecha_pago'] = Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y');
                    $datos['monto_ultimo_pago'] = number_format($ultimoPago->monto_abonado, 0, ',', '.');
                    
                    if ($saldoPendiente == 0) {
                        $datos['mensaje_estado'] = '¡Felicidades! Has completado el pago de tu membresía';
                        $datos['color_saldo'] = '#2EB872';
                        $datos['mensaje_detalle'] = 'Tu membresía está 100% pagada';
                    } else {
                        $datos['mensaje_estado'] = 'Gracias por tu abono';
                        $datos['color_saldo'] = '#FFC107';
                        $datos['mensaje_detalle'] = 'Aún tienes un saldo pendiente de $' . number_format($saldoPendiente, 0, ',', '.');
                    }
                }
                break;
        }

        return $datos;
    }

    private function determinarTipoNotificacion($inscripcion)
    {
        $hoy = Carbon::now();
        $fechaInicio = Carbon::parse($inscripcion->fecha_inicio);
        $fechaVencimiento = Carbon::parse($inscripcion->fecha_vencimiento);

        // Excluir Pase Diario
        if (in_array(strtolower($inscripcion->membresia->nombre), ['pase diario', 'pase_diario'])) {
            return 'sin_notificacion';
        }

        // Verificar si completó pago recientemente
        if ($inscripcion->pagos->count() > 1) {
            $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
            $fechaUltimoPago = Carbon::parse($ultimoPago->fecha_pago);
            
            if ($hoy->diffInDays($fechaUltimoPago) <= 3 && $ultimoPago->monto_pendiente == 0) {
                $pagoAnterior = $inscripcion->pagos->sortByDesc('fecha_pago')->skip(1)->first();
                if ($pagoAnterior && $pagoAnterior->monto_pendiente > 0) {
                    return 'pago_completado';
                }
            }
        }

        // Verificar pausas y reactivaciones
        if ($inscripcion->pausada) {
            return 'pausa_inscripcion';
        }

        if ($inscripcion->fecha_pausa_fin) {
            $fechaReactivacion = Carbon::parse($inscripcion->fecha_pausa_fin);
            if ($hoy->diffInDays($fechaReactivacion) <= 3) {
                return 'activacion_inscripcion';
            }
        }

        // Bienvenida (inscripción reciente)
        if ($hoy->diffInDays($fechaInicio) <= 7 && $fechaInicio->lte($hoy)) {
            return 'bienvenida';
        }

        // Por vencer
        if ($fechaVencimiento->isFuture() && $hoy->diffInDays($fechaVencimiento) <= 3) {
            return 'membresia_por_vencer';
        }

        // Vencida
        if ($fechaVencimiento->isPast()) {
            return 'membresia_vencida';
        }

        // Pago pendiente
        $totalPagado = $inscripcion->pagos->sum('monto_abonado');
        $precioBase = $inscripcion->precioAcordado->precio ?? 0;
        if ($totalPagado < $precioBase) {
            return 'pago_pendiente';
        }

        return 'sin_notificacion';
    }

    private function reemplazarVariables($html, $datos)
    {
        foreach ($datos as $clave => $valor) {
            // Reemplazar tanto {variable} como ${variable}
            $html = str_replace('{' . $clave . '}', $valor, $html);
            $html = str_replace('${' . $clave . '}', $valor, $html);
        }
        return $html;
    }

    private function obtenerEmoji($tipo)
    {
        $emojis = [
            'bienvenida' => '🎉',
            'membresia_por_vencer' => '⏰',
            'membresia_vencida' => '⚠️',
            'pago_pendiente' => '💳',
            'pago_completado' => '✅',
            'pausa_inscripcion' => '⏸️',
            'activacion_inscripcion' => '▶️',
        ];

        return $emojis[$tipo] ?? '📧';
    }
}
