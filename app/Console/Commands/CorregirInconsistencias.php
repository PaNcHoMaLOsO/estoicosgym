<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Enums\EstadosCodigo;
use Carbon\Carbon;

class CorregirInconsistencias extends Command
{
    protected $signature = 'fix:inconsistencias {--dry-run : Mostrar qué se corregiría sin hacer cambios}';
    protected $description = 'Corrige las inconsistencias de datos encontradas en la auditoría';

    private int $correccionesRealizadas = 0;
    private bool $dryRun = false;

    public function handle()
    {
        $this->dryRun = $this->option('dry-run');
        
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║           CORRECCIÓN DE INCONSISTENCIAS - ESTOICOS GYM                       ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        
        if ($this->dryRun) {
            $this->warn('');
            $this->warn('  ⚠️  MODO DRY-RUN: Solo se mostrarán las correcciones, no se aplicarán');
            $this->warn('');
        }
        
        $this->info('');

        // Corrección 1: Inscripciones traspasadas con saldo pendiente
        $this->corregirInscripcionesTraspasadasConSaldo();

        // Corrección 2: Inscripciones finalizadas con vencimiento futuro
        $this->corregirInscripcionesFinalizadasConVencimientoFuturo();

        // Corrección 3: Pagos marcados como PAGADOS con monto pendiente
        $this->corregirPagosPagadosConPendiente();

        // Corrección 4: Pagos con inconsistencia en montos
        $this->corregirInconsistenciaMontosPago();

        // Corrección 5: Clientes inactivos con inscripciones activas
        $this->corregirClientesInactivosConInscripcionesActivas();

        // Corrección 6: Inscripciones activas sin pagos
        $this->corregirInscripcionesActivasSinPagos();

        // Resumen
        $this->line('');
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        if ($this->dryRun) {
            $this->info('║  📋 RESUMEN: ' . str_pad($this->correccionesRealizadas, 3) . ' correcciones identificadas (dry-run)              ║');
        } else {
            $this->info('║  ✅ RESUMEN: ' . str_pad($this->correccionesRealizadas, 3) . ' correcciones aplicadas exitosamente               ║');
        }
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->line('');

        return 0;
    }

    /**
     * Corrección 1: Inscripciones traspasadas con saldo pendiente
     * Solución: Marcar los pagos como TRASPASADOS y el monto pendiente = 0
     * Si no hay pagos, crear uno con estado TRASPASADO
     */
    private function corregirInscripcionesTraspasadasConSaldo()
    {
        $this->line('');
        $this->comment('🔧 1. Corrigiendo inscripciones traspasadas con saldo pendiente...');

        $inscripciones = Inscripcion::where(function($q) {
                $q->where('es_traspaso', true)
                  ->orWhere('id_estado', EstadosCodigo::INSCRIPCION_TRASPASADA);
            })
            ->with(['cliente', 'pagos'])
            ->get();

        $problemas = $inscripciones->filter(function ($insc) {
            $estadoPago = $insc->obtenerEstadoPago();
            return $estadoPago['pendiente'] > 0;
        });

        if ($problemas->count() === 0) {
            $this->info('   ✓ No hay inscripciones traspasadas con saldo pendiente');
            return;
        }

        foreach ($problemas as $inscripcion) {
            $estadoPago = $inscripcion->obtenerEstadoPago();
            
            $this->line("   → Inscripción #{$inscripcion->id} ({$inscripcion->cliente->nombre_completo})");
            $this->line("     Pendiente: \${$estadoPago['pendiente']} → \$0");
            
            if (!$this->dryRun) {
                // Si no tiene pagos, crear uno con estado TRASPASADO
                if ($inscripcion->pagos->isEmpty()) {
                    Pago::create([
                        'uuid' => \Illuminate\Support\Str::uuid(),
                        'id_inscripcion' => $inscripcion->id,
                        'id_cliente' => $inscripcion->id_cliente,
                        'monto_total' => $inscripcion->precio_final,
                        'monto_abonado' => $inscripcion->precio_final,
                        'monto_pendiente' => 0,
                        'fecha_pago' => $inscripcion->fecha_traspaso ?? $inscripcion->updated_at ?? now(),
                        'id_metodo_pago' => 1, // Efectivo por defecto
                        'id_estado' => EstadosCodigo::PAGO_TRASPASADO,
                        'tipo_pago' => 'completo',
                        'observaciones' => 'Pago registrado automáticamente por traspaso - ' . now()->format('Y-m-d'),
                    ]);
                    $this->line("     ⚡ Creado pago de regularización");
                } else {
                    // Marcar todos los pagos existentes como traspasados
                    foreach ($inscripcion->pagos as $pago) {
                        if ($pago->monto_pendiente > 0) {
                            $pago->monto_pendiente = 0;
                            $pago->monto_abonado = $pago->monto_total;
                            $pago->id_estado = EstadosCodigo::PAGO_TRASPASADO;
                            $pago->observaciones = ($pago->observaciones ? $pago->observaciones . ' | ' : '') . 
                                'Saldo condonado por traspaso - ' . now()->format('Y-m-d');
                            $pago->save();
                        }
                    }
                }
                
                // Asegurar que la inscripción tenga estado correcto
                if ($inscripcion->id_estado != EstadosCodigo::INSCRIPCION_TRASPASADA) {
                    $inscripcion->id_estado = EstadosCodigo::INSCRIPCION_TRASPASADA;
                    $inscripcion->save();
                }
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$problemas->count()} inscripciones procesadas");
    }

    /**
     * Corrección 2: Inscripciones finalizadas con vencimiento futuro
     * Solución: Ajustar la fecha de vencimiento al día del traspaso/cancelación
     */
    private function corregirInscripcionesFinalizadasConVencimientoFuturo()
    {
        $this->line('');
        $this->comment('🔧 2. Corrigiendo inscripciones finalizadas con vencimiento futuro...');

        $estadosFinalizados = [
            EstadosCodigo::INSCRIPCION_CANCELADA,
            EstadosCodigo::INSCRIPCION_TRASPASADA,
        ];

        $problemas = Inscripcion::whereIn('id_estado', $estadosFinalizados)
            ->where('fecha_vencimiento', '>', now())
            ->with('cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ No hay inscripciones finalizadas con vencimiento futuro');
            return;
        }

        foreach ($problemas as $inscripcion) {
            $fechaOriginal = $inscripcion->fecha_vencimiento->format('Y-m-d');
            $nuevaFecha = $inscripcion->fecha_traspaso ?? $inscripcion->updated_at ?? now();
            
            $this->line("   → Inscripción #{$inscripcion->id} ({$inscripcion->cliente->nombre_completo})");
            $this->line("     Vencimiento: {$fechaOriginal} → {$nuevaFecha->format('Y-m-d')}");
            
            if (!$this->dryRun) {
                $inscripcion->fecha_vencimiento = $nuevaFecha;
                $inscripcion->observaciones = ($inscripcion->observaciones ? $inscripcion->observaciones . ' | ' : '') . 
                    "Fecha ajustada (era: {$fechaOriginal}) - " . now()->format('Y-m-d');
                $inscripcion->save();
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$problemas->count()} inscripciones procesadas");
    }

    /**
     * Corrección 3: Pagos marcados como PAGADOS con monto pendiente > 0
     * Solución: Cambiar estado a PARCIAL o ajustar el monto pendiente
     */
    private function corregirPagosPagadosConPendiente()
    {
        $this->line('');
        $this->comment('🔧 3. Corrigiendo pagos marcados como PAGADOS con saldo pendiente...');

        $problemas = Pago::where('id_estado', EstadosCodigo::PAGO_PAGADO)
            ->where('monto_pendiente', '>', 0)
            ->with('inscripcion.cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ No hay pagos PAGADOS con saldo pendiente');
            return;
        }

        foreach ($problemas as $pago) {
            $cliente = $pago->inscripcion->cliente->nombre_completo ?? 'N/A';
            
            $this->line("   → Pago #{$pago->id} ({$cliente})");
            
            // Si tiene algo abonado, cambiar a PARCIAL. Si no, el pendiente debería ser 0
            if ($pago->monto_abonado > 0 && $pago->monto_abonado < $pago->monto_total) {
                $this->line("     Estado: PAGADO → PARCIAL (abonado: \${$pago->monto_abonado})");
                
                if (!$this->dryRun) {
                    $pago->id_estado = EstadosCodigo::PAGO_PARCIAL;
                    $pago->save();
                }
            } else {
                // El abonado cubre el total, entonces pendiente debe ser 0
                $this->line("     Pendiente: \${$pago->monto_pendiente} → \$0 (ya está pagado)");
                
                if (!$this->dryRun) {
                    $pago->monto_pendiente = 0;
                    $pago->save();
                }
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$problemas->count()} pagos procesados");
    }

    /**
     * Corrección 4: Pagos donde total ≠ abonado + pendiente
     * Solución: Recalcular el monto pendiente
     */
    private function corregirInconsistenciaMontosPago()
    {
        $this->line('');
        $this->comment('🔧 4. Corrigiendo inconsistencias en montos de pagos...');

        $problemas = Pago::whereRaw('ABS(CAST(monto_total AS DECIMAL(10,2)) - (CAST(monto_abonado AS DECIMAL(10,2)) + CAST(monto_pendiente AS DECIMAL(10,2)))) > 1')
            ->with('inscripcion.cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ No hay pagos con montos inconsistentes');
            return;
        }

        foreach ($problemas as $pago) {
            $cliente = $pago->inscripcion->cliente->nombre_completo ?? 'N/A';
            $pendienteCorrecto = max(0, $pago->monto_total - $pago->monto_abonado);
            
            $this->line("   → Pago #{$pago->id} ({$cliente})");
            $this->line("     Pendiente: \${$pago->monto_pendiente} → \${$pendienteCorrecto}");
            
            if (!$this->dryRun) {
                $pago->monto_pendiente = $pendienteCorrecto;
                
                // Ajustar estado según el nuevo pendiente
                if ($pendienteCorrecto == 0) {
                    $pago->id_estado = EstadosCodigo::PAGO_PAGADO;
                } elseif ($pago->monto_abonado > 0) {
                    $pago->id_estado = EstadosCodigo::PAGO_PARCIAL;
                } else {
                    $pago->id_estado = EstadosCodigo::PAGO_PENDIENTE;
                }
                
                $pago->save();
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$problemas->count()} pagos procesados");
    }

    /**
     * Corrección 5: Clientes inactivos con inscripciones activas
     * Solución: Suspender las inscripciones activas del cliente inactivo
     */
    private function corregirClientesInactivosConInscripcionesActivas()
    {
        $this->line('');
        $this->comment('🔧 5. Corrigiendo clientes inactivos con inscripciones activas...');

        $problemas = Cliente::where('activo', false)
            ->whereHas('inscripciones', function ($query) {
                $query->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA);
            })
            ->with(['inscripciones' => function ($query) {
                $query->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA);
            }])
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ No hay clientes inactivos con inscripciones activas');
            return;
        }

        foreach ($problemas as $cliente) {
            $this->line("   → Cliente #{$cliente->id} ({$cliente->nombre_completo})");
            
            foreach ($cliente->inscripciones as $inscripcion) {
                $this->line("     Inscripción #{$inscripcion->id}: ACTIVA → SUSPENDIDA");
                
                if (!$this->dryRun) {
                    $inscripcion->id_estado = EstadosCodigo::INSCRIPCION_SUSPENDIDA;
                    $inscripcion->observaciones = ($inscripcion->observaciones ? $inscripcion->observaciones . ' | ' : '') . 
                        'Suspendida por cliente inactivo - ' . now()->format('Y-m-d');
                    $inscripcion->save();
                }
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$problemas->count()} clientes procesados");
    }

    /**
     * Corrección 6: Inscripciones activas sin pagos
     * Solución: Crear un pago PENDIENTE para que el sistema refleje la deuda
     */
    private function corregirInscripcionesActivasSinPagos()
    {
        $this->line('');
        $this->comment('🔧 6. Corrigiendo inscripciones activas sin pagos...');

        $inscripciones = Inscripcion::where('id_estado', 100) // Activa
            ->whereDoesntHave('pagos')
            ->with(['cliente', 'membresia'])
            ->get();

        if ($inscripciones->count() === 0) {
            $this->info('   ✓ No hay inscripciones activas sin pagos');
            return;
        }

        foreach ($inscripciones as $inscripcion) {
            $precio = $inscripcion->precio_final ?? $inscripcion->precio_base ?? 0;
            
            $this->line("   → Inscripción #{$inscripcion->id} ({$inscripcion->cliente->nombres} - {$inscripcion->membresia->nombre})");
            $this->line("     Precio: \${$precio} - Creando pago PENDIENTE");
            
            if (!$this->dryRun && $precio > 0) {
                Pago::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'id_inscripcion' => $inscripcion->id,
                    'id_cliente' => $inscripcion->id_cliente,
                    'monto_total' => $precio,
                    'monto_abonado' => 0,
                    'monto_pendiente' => $precio,
                    'fecha_pago' => $inscripcion->fecha_inscripcion ?? now(),
                    'id_metodo_pago' => 1, // Efectivo por defecto
                    'id_estado' => 200, // Pendiente
                    'tipo_pago' => 'pendiente',
                    'observaciones' => 'Pago pendiente creado automáticamente - ' . now()->format('Y-m-d H:i'),
                ]);
            }
            
            $this->correccionesRealizadas++;
        }

        $this->info("   ✓ {$inscripciones->count()} inscripciones procesadas");
    }
}
