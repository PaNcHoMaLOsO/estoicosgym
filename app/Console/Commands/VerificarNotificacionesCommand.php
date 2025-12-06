<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class VerificarNotificacionesCommand extends Command
{
    protected $signature = 'verificar:notificaciones {--limit=10} {--solo-test : Mostrar solo clientes de prueba}';
    protected $description = 'Verifica qué notificaciones se enviarían SIN enviar emails realmente';

    public function handle()
    {
        $limit = $this->option('limit');
        $soloTest = $this->option('solo-test');
        
        $this->info("🔍 VERIFICACIÓN DE NOTIFICACIONES (SIN ENVIAR EMAILS)");
        $this->info("═══════════════════════════════════════════════════");
        $this->newLine();

        // Emails de clientes de prueba
        $emailsTest = [
            'test.nuevo@progym.test',
            'test.parcial@progym.test',
            'test.pendiente@progym.test',
            'test.mixto@progym.test',
            'test.completado@progym.test',
            'test.porvencer@progym.test',
            'test.vencido@progym.test',
            'test.deuda@progym.test',
            'test.pausado@progym.test',
            'test.reactivado@progym.test',
        ];

        // Obtener clientes únicos con inscripciones
        $clientes = Cliente::with(['inscripciones' => function($query) {
                $query->latest()->limit(1); // Solo la inscripción más reciente
            }, 'inscripciones.membresia', 'inscripciones.pagos'])
            ->whereHas('inscripciones', function($query) {
                $query->whereHas('membresia', function($q) {
                    $q->whereNotIn('nombre', ['Pase Diario', 'pase diario', 'PASE DIARIO']);
                });
            })
            ->when($soloTest, function($query) use ($emailsTest) {
                $query->whereIn('email', $emailsTest);
            })
            ->limit($limit)
            ->get();

        if ($clientes->isEmpty()) {
            $this->error('❌ No hay clientes con inscripciones');
            return 1;
        }

        $this->info("📊 Encontrados {$clientes->count()} clientes para verificar");
        $this->newLine();

        $resumen = [
            'bienvenida' => 0,
            'membresia_por_vencer' => 0,
            'membresia_vencida' => 0,
            'pago_pendiente' => 0,
            'pago_completado' => 0,
            'pausa_inscripcion' => 0,
            'activacion_inscripcion' => 0,
            'sin_notificacion' => 0,
        ];

        foreach ($clientes as $cliente) {
            $inscripcion = $cliente->inscripciones->first();
            
            if (!$inscripcion) {
                $this->warn("⚠️  Cliente {$cliente->nombres} {$cliente->apellido_paterno} - Sin inscripción");
                $resumen['sin_notificacion']++;
                continue;
            }

            $nombreMembresia = strtolower($inscripcion->membresia->nombre ?? '');
            $tipo = $this->determinarTipoNotificacion($inscripcion);

            if (!$tipo) {
                $resumen['sin_notificacion']++;
            } else {
                $resumen[$tipo]++;
            }

            $this->mostrarCliente($cliente, $inscripcion, $tipo);
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════════════");
        $this->info("📊 RESUMEN DE NOTIFICACIONES");
        $this->info("═══════════════════════════════════════════════════");
        
        foreach ($resumen as $tipo => $cantidad) {
            if ($cantidad > 0) {
                $emoji = $this->getEmoji($tipo);
                $this->line("  {$emoji} {$tipo}: {$cantidad}");
            }
        }

        $this->newLine();
        $this->info("✅ Verificación completada - NO se enviaron emails");
        
        return 0;
    }

    private function mostrarCliente($cliente, $inscripcion, $tipo)
    {
        $nombreCompleto = trim($cliente->nombres . ' ' . $cliente->apellido_paterno);
        $membresia = $inscripcion->membresia->nombre ?? 'N/A';
        $email = $cliente->email ?? 'Sin email';
        
        $this->newLine();
        $this->line("👤 <fg=cyan>{$nombreCompleto}</>");
        $this->line("   📧 Email: {$email}");
        $this->line("   💳 Membresía: {$membresia}");
        $this->line("   📅 Vencimiento: " . ($inscripcion->fecha_vencimiento ?? $inscripcion->fecha_fin ?? 'N/A'));
        
        // Información de pagos
        $totalPagado = $inscripcion->pagos->sum('monto_abonado');
        $precioBase = $inscripcion->precio_base ?? $inscripcion->precio_final ?? 0;
        $saldoPendiente = $precioBase - $totalPagado;
        
        $this->line("   💰 Precio: $" . number_format($precioBase, 0, ',', '.'));
        $this->line("   ✅ Pagado: $" . number_format($totalPagado, 0, ',', '.'));
        
        if ($saldoPendiente > 0) {
            $this->line("   ⚠️  Saldo: <fg=red>$" . number_format($saldoPendiente, 0, ',', '.') . "</>");
        } else {
            $this->line("   ✅ Saldo: <fg=green>$0</>");
        }
        
        $this->line("   🔢 Cantidad de pagos: " . $inscripcion->pagos->count());
        
        // Estado de inscripción
        if ($inscripcion->pausada ?? false) {
            $this->line("   ⏸️  Estado: <fg=yellow>PAUSADA</>");
        } elseif (isset($inscripcion->id_estado)) {
            $estados = [100 => 'Activa', 101 => 'Pausada', 102 => 'Vencida', 103 => 'Cancelada'];
            $estado = $estados[$inscripcion->id_estado] ?? $inscripcion->id_estado;
            $this->line("   📍 Estado: {$estado}");
        }
        
        // Tipo de notificación
        if ($tipo) {
            $emoji = $this->getEmoji($tipo);
            $this->line("   📬 Notificación: <fg=green>{$emoji} {$tipo}</>");
        } else {
            $this->line("   📬 Notificación: <fg=gray>Ninguna (no aplica criterios)</>");
        }
    }

    private function determinarTipoNotificacion($inscripcion)
    {
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
            return null;
        }

        $hoy = \Carbon\Carbon::now();
        
        // PRIORIDAD 1: Estados de membresía (Pausada, Vencida, Por Vencer)
        // Verificar si está PAUSADA (revisar id_estado = 101)
        if (isset($inscripcion->id_estado) && $inscripcion->id_estado == 101) {
            return 'pausa_inscripcion';
        }
        
        // Verificar si está VENCIDA (revisar id_estado = 102)
        if (isset($inscripcion->id_estado) && $inscripcion->id_estado == 102) {
            return 'membresia_vencida';
        }

        // Verificar por fecha de vencimiento
        $fechaVencimiento = \Carbon\Carbon::parse($inscripcion->fecha_vencimiento ?? $inscripcion->fecha_fin);
        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);

        // Si está vencida por fecha (aunque el estado no esté actualizado)
        if ($diasRestantes < 0) {
            return 'membresia_vencida';
        }

        // Si está por vencer (7 días o menos)
        if ($diasRestantes >= 0 && $diasRestantes <= 7) {
            return 'membresia_por_vencer';
        }

        // PRIORIDAD 2: Si fue reactivada recientemente
        if ($inscripcion->fecha_pausa_fin && isset($inscripcion->id_estado) && $inscripcion->id_estado == 100) {
            $fechaReactivacion = \Carbon\Carbon::parse($inscripcion->fecha_pausa_fin);
            if ($hoy->diffInDays($fechaReactivacion) <= 2) {
                return 'activacion_inscripcion';
            }
        }

        // PRIORIDAD 3: Si el cliente acaba de completar un pago pendiente/parcial HOY
        if ($inscripcion->pagos->count() > 1) {
            $ultimoPago = $inscripcion->pagos->sortByDesc('fecha_pago')->first();
            if ($ultimoPago) {
                $fechaUltimoPago = \Carbon\Carbon::parse($ultimoPago->fecha_pago);
                $totalPagado = $inscripcion->pagos->sum('monto_abonado');
                $precioBase = $inscripcion->precio_base ?? $inscripcion->precio_final ?? 0;
                
                // Completó el pago HOY y ahora saldo = 0
                if ($hoy->isSameDay($fechaUltimoPago) && $totalPagado >= $precioBase) {
                    $pagoAnterior = $inscripcion->pagos->sortByDesc('fecha_pago')->skip(1)->first();
                    if ($pagoAnterior) {
                        $totalAnterior = $inscripcion->pagos->filter(function($p) use ($ultimoPago) {
                            return $p->id != $ultimoPago->id;
                        })->sum('monto_abonado');
                        
                        if ($totalAnterior < $precioBase) {
                            return 'pago_completado';
                        }
                    }
                }
            }
        }

        // PRIORIDAD 4: Si tiene pagos pendientes (deuda)
        $totalPagado = $inscripcion->pagos->sum('monto_abonado');
        $precioBase = $inscripcion->precio_base ?? $inscripcion->precio_final ?? 0;
        $saldoPendiente = $precioBase - $totalPagado;
        
        // Si tiene deuda y la inscripción tiene más de 7 días
        $fechaInicio = \Carbon\Carbon::parse($inscripcion->fecha_inicio ?? $inscripcion->fecha_inscripcion);
        if ($saldoPendiente > 0 && $hoy->diffInDays($fechaInicio) > 7) {
            return 'pago_pendiente';
        }

        // PRIORIDAD 5: Si es inscripción reciente (bienvenida) - últimos 7 días
        if ($hoy->diffInDays($fechaInicio) <= 7) {
            return 'bienvenida';
        }

        return null;
    }

    private function getEmoji($tipo)
    {
        return match($tipo) {
            'bienvenida' => '🎉',
            'membresia_por_vencer' => '⏰',
            'membresia_vencida' => '❗',
            'pago_pendiente' => '💳',
            'pago_completado' => '✅',
            'pausa_inscripcion' => '⏸️',
            'activacion_inscripcion' => '▶️',
            'sin_notificacion' => '➖',
            default => '📧',
        };
    }
}
