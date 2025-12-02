<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class AuditarLogicaNegocio extends Command
{
    protected $signature = 'audit:logica {--fix : Intentar corregir problemas encontrados}';
    protected $description = 'Auditoría profunda de lógica de negocio: pagos, inscripciones, clientes';

    private $problemas = [];
    private $fix = false;

    public function handle()
    {
        $this->fix = $this->option('fix');
        
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║        AUDITORÍA DE LÓGICA DE NEGOCIO - ESTOICOS GYM                        ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // PARTE 1: PAGOS
        $this->info('📋 PARTE 1: LÓGICA DE PAGOS');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->auditarPagosMultiples();
        $this->auditarPagosSinMetodo();
        $this->auditarMontosMixtos();
        $this->auditarPagosConMontoCero();
        
        // PARTE 2: INSCRIPCIONES
        $this->newLine();
        $this->info('📋 PARTE 2: LÓGICA DE INSCRIPCIONES');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->auditarInscripcionesSinPrecio();
        $this->auditarInscripcionesConFechasInvalidas();
        $this->auditarInscripcionesVencidasNoActualizadas();
        $this->auditarPausasExcedidas();
        
        // PARTE 3: CLIENTES
        $this->newLine();
        $this->info('📋 PARTE 3: LÓGICA DE CLIENTES');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->auditarRutsDuplicados();
        $this->auditarClientesSinCorreo();
        $this->auditarClientesSinRut();
        
        // PARTE 4: RELACIONES
        $this->newLine();
        $this->info('📋 PARTE 4: INTEGRIDAD DE RELACIONES');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->auditarPagosClienteIncorrecto();
        $this->auditarInscripcionesSinMembresia();
        
        // RESUMEN
        $this->mostrarResumen();
        
        return 0;
    }

    // ===================== PAGOS =====================

    private function auditarPagosMultiples()
    {
        $this->line('');
        $this->comment('🔍 1.1 Inscripciones con múltiples pagos donde suma > precio...');
        
        $inscripcionesProblema = Inscripcion::with('pagos')
            ->whereNotIn('id_estado', [103, 105, 106]) // No finalizadas
            ->get()
            ->filter(function($insc) {
                $precioTotal = $insc->precio_final ?? $insc->precio_base;
                $sumaAbonado = $insc->pagos->sum('monto_abonado');
                return $sumaAbonado > $precioTotal && $precioTotal > 0;
            });
        
        if ($inscripcionesProblema->count() > 0) {
            $this->warn("   ⚠ Encontradas {$inscripcionesProblema->count()} inscripciones con sobrepago:");
            foreach ($inscripcionesProblema as $insc) {
                $precio = $insc->precio_final ?? $insc->precio_base;
                $suma = $insc->pagos->sum('monto_abonado');
                $exceso = $suma - $precio;
                $this->line("     - ID {$insc->id}: Precio ${$precio}, Pagado ${$suma} (+${$exceso} exceso)");
                $this->problemas[] = "Inscripción #{$insc->id} con sobrepago de \${$exceso}";
            }
        } else {
            $this->info('   ✓ OK - No hay inscripciones con sobrepago');
        }
    }

    private function auditarPagosSinMetodo()
    {
        $this->line('');
        $this->comment('🔍 1.2 Pagos sin método de pago asignado...');
        
        $pagosSinMetodo = Pago::whereNull('id_metodo_pago')
            ->whereNotIn('id_estado', [205]) // Excluir traspasados
            ->count();
        
        if ($pagosSinMetodo > 0) {
            $this->warn("   ⚠ Hay {$pagosSinMetodo} pagos sin método de pago asignado");
            $this->problemas[] = "{$pagosSinMetodo} pagos sin método de pago";
        } else {
            $this->info('   ✓ OK - Todos los pagos tienen método asignado');
        }
    }

    private function auditarMontosMixtos()
    {
        $this->line('');
        $this->comment('🔍 1.3 Pagos mixtos con montos inconsistentes...');
        
        $pagosMixtos = Pago::where('tipo_pago', 'mixto')
            ->whereNotNull('id_metodo_pago2')
            ->get();
        
        $inconsistentes = 0;
        foreach ($pagosMixtos as $pago) {
            $suma = ($pago->monto_metodo1 ?? 0) + ($pago->monto_metodo2 ?? 0);
            if ($suma != $pago->monto_abonado) {
                $inconsistentes++;
                $this->line("     - Pago #{$pago->id}: metodo1+metodo2 = {$suma}, pero monto_abonado = {$pago->monto_abonado}");
                $this->problemas[] = "Pago mixto #{$pago->id} con suma incorrecta";
            }
        }
        
        if ($inconsistentes == 0) {
            $this->info('   ✓ OK - Pagos mixtos tienen montos consistentes');
        }
    }

    private function auditarPagosConMontoCero()
    {
        $this->line('');
        $this->comment('🔍 1.4 Pagos con monto_total = 0...');
        
        $pagosVacios = Pago::where('monto_total', 0)
            ->whereNotIn('id_estado', [205]) // Excluir traspasados
            ->get();
        
        if ($pagosVacios->count() > 0) {
            $this->warn("   ⚠ Hay {$pagosVacios->count()} pagos con monto_total = 0:");
            foreach ($pagosVacios->take(5) as $pago) {
                $this->line("     - Pago #{$pago->id}, Inscripción #{$pago->id_inscripcion}");
            }
            $this->problemas[] = "{$pagosVacios->count()} pagos con monto = 0";
            
            if ($this->fix) {
                $eliminados = Pago::where('monto_total', 0)
                    ->whereNotIn('id_estado', [205])
                    ->where('monto_abonado', 0)
                    ->delete();
                $this->info("   🔧 Eliminados {$eliminados} pagos vacíos");
            }
        } else {
            $this->info('   ✓ OK - No hay pagos con monto cero');
        }
    }

    // ===================== INSCRIPCIONES =====================

    private function auditarInscripcionesSinPrecio()
    {
        $this->line('');
        $this->comment('🔍 2.1 Inscripciones sin precio definido...');
        
        $sinPrecio = Inscripcion::where(function($q) {
                $q->whereNull('precio_final')
                  ->orWhere('precio_final', 0);
            })
            ->where(function($q) {
                $q->whereNull('precio_base')
                  ->orWhere('precio_base', 0);
            })
            ->whereNotIn('id_estado', [103, 105, 106])
            ->count();
        
        if ($sinPrecio > 0) {
            $this->warn("   ⚠ Hay {$sinPrecio} inscripciones activas sin precio definido");
            $this->problemas[] = "{$sinPrecio} inscripciones sin precio";
        } else {
            $this->info('   ✓ OK - Todas las inscripciones tienen precio');
        }
    }

    private function auditarInscripcionesConFechasInvalidas()
    {
        $this->line('');
        $this->comment('🔍 2.2 Inscripciones donde fecha_inicio > fecha_vencimiento...');
        
        $fechasInvalidas = Inscripcion::whereColumn('fecha_inicio', '>', 'fecha_vencimiento')
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_vencimiento')
            ->get();
        
        if ($fechasInvalidas->count() > 0) {
            $this->warn("   ⚠ Hay {$fechasInvalidas->count()} inscripciones con fechas inválidas:");
            foreach ($fechasInvalidas->take(5) as $insc) {
                $this->line("     - ID {$insc->id}: inicio={$insc->fecha_inicio}, venc={$insc->fecha_vencimiento}");
            }
            $this->problemas[] = "{$fechasInvalidas->count()} inscripciones con fechas invertidas";
        } else {
            $this->info('   ✓ OK - Las fechas de inscripciones son válidas');
        }
    }

    private function auditarInscripcionesVencidasNoActualizadas()
    {
        $this->line('');
        $this->comment('🔍 2.3 Inscripciones ACTIVAS vencidas hace más de 7 días...');
        
        $vencidasSinActualizar = Inscripcion::where('id_estado', 100) // Activa
            ->where('fecha_vencimiento', '<', now()->subDays(7))
            ->count();
        
        if ($vencidasSinActualizar > 0) {
            $this->warn("   ⚠ Hay {$vencidasSinActualizar} inscripciones activas vencidas hace >7 días");
            $this->problemas[] = "{$vencidasSinActualizar} inscripciones vencidas no actualizadas";
            
            if ($this->fix) {
                $actualizadas = Inscripcion::where('id_estado', 100)
                    ->where('fecha_vencimiento', '<', now()->subDays(7))
                    ->update(['id_estado' => 102]); // Marcar como vencida
                $this->info("   🔧 Actualizadas {$actualizadas} inscripciones a estado Vencida");
            }
        } else {
            $this->info('   ✓ OK - No hay inscripciones activas con vencimiento antiguo');
        }
    }

    private function auditarPausasExcedidas()
    {
        $this->line('');
        $this->comment('🔍 2.4 Inscripciones con pausas realizadas > max permitidas...');
        
        $pausasExcedidas = Inscripcion::whereColumn('pausas_realizadas', '>', 'max_pausas_permitidas')
            ->whereNotNull('pausas_realizadas')
            ->whereNotNull('max_pausas_permitidas')
            ->count();
        
        if ($pausasExcedidas > 0) {
            $this->warn("   ⚠ Hay {$pausasExcedidas} inscripciones con pausas excedidas");
            $this->problemas[] = "{$pausasExcedidas} inscripciones con pausas > máximo";
        } else {
            $this->info('   ✓ OK - Ninguna inscripción excede sus pausas permitidas');
        }
    }

    // ===================== CLIENTES =====================

    private function auditarRutsDuplicados()
    {
        $this->line('');
        $this->comment('🔍 3.1 RUTs/Pasaportes duplicados entre clientes...');
        
        $rutsDuplicados = Cliente::select('run_pasaporte')
            ->whereNotNull('run_pasaporte')
            ->where('run_pasaporte', '!=', '')
            ->groupBy('run_pasaporte')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('run_pasaporte');
        
        if ($rutsDuplicados->count() > 0) {
            $this->warn("   ⚠ Hay {$rutsDuplicados->count()} RUTs/Pasaportes duplicados:");
            foreach ($rutsDuplicados->take(5) as $rut) {
                $clientes = Cliente::where('run_pasaporte', $rut)->pluck('id')->implode(', ');
                $this->line("     - RUT {$rut}: Clientes #{$clientes}");
            }
            $this->problemas[] = "{$rutsDuplicados->count()} RUTs duplicados";
        } else {
            $this->info('   ✓ OK - No hay RUTs/Pasaportes duplicados');
        }
    }

    private function auditarClientesSinCorreo()
    {
        $this->line('');
        $this->comment('🔍 3.2 Clientes activos sin correo electrónico...');
        
        $sinCorreo = Cliente::where('activo', true)
            ->where(function($q) {
                $q->whereNull('email')
                  ->orWhere('email', '');
            })
            ->count();
        
        if ($sinCorreo > 0) {
            $this->warn("   ⚠ Hay {$sinCorreo} clientes activos sin correo (no pueden recibir notificaciones)");
            $this->problemas[] = "{$sinCorreo} clientes sin correo";
        } else {
            $this->info('   ✓ OK - Todos los clientes activos tienen correo');
        }
    }

    private function auditarClientesSinRut()
    {
        $this->line('');
        $this->comment('🔍 3.3 Clientes sin RUT/Pasaporte...');
        
        $sinRut = Cliente::where(function($q) {
                $q->whereNull('run_pasaporte')
                  ->orWhere('run_pasaporte', '');
            })
            ->count();
        
        if ($sinRut > 0) {
            $this->info("   ℹ Hay {$sinRut} clientes sin RUT/Pasaporte (permitido para indocumentados)");
            // No es un problema grave, solo informativo
        } else {
            $this->info('   ✓ OK - Todos los clientes tienen RUT/Pasaporte');
        }
    }

    // ===================== RELACIONES =====================

    private function auditarPagosClienteIncorrecto()
    {
        $this->line('');
        $this->comment('🔍 4.1 Pagos donde id_cliente ≠ cliente de inscripción...');
        
        $pagosClienteIncorrecto = Pago::with(['inscripcion'])
            ->whereNotIn('id_estado', [205]) // Excluir traspasados (es normal que difieran)
            ->get()
            ->filter(function($pago) {
                return $pago->inscripcion && 
                       $pago->id_cliente != $pago->inscripcion->id_cliente;
            });
        
        if ($pagosClienteIncorrecto->count() > 0) {
            $this->warn("   ⚠ Hay {$pagosClienteIncorrecto->count()} pagos con cliente diferente al de inscripción:");
            foreach ($pagosClienteIncorrecto->take(5) as $pago) {
                $this->line("     - Pago #{$pago->id}: cliente_pago={$pago->id_cliente}, cliente_insc={$pago->inscripcion->id_cliente}");
            }
            $this->problemas[] = "{$pagosClienteIncorrecto->count()} pagos con cliente incorrecto";
            
            if ($this->fix) {
                foreach ($pagosClienteIncorrecto as $pago) {
                    $pago->update(['id_cliente' => $pago->inscripcion->id_cliente]);
                }
                $this->info("   🔧 Corregidos {$pagosClienteIncorrecto->count()} pagos");
            }
        } else {
            $this->info('   ✓ OK - Los pagos corresponden al cliente de su inscripción');
        }
    }

    private function auditarInscripcionesSinMembresia()
    {
        $this->line('');
        $this->comment('🔍 4.2 Inscripciones sin membresía válida...');
        
        $sinMembresia = Inscripcion::whereNull('id_membresia')
            ->orWhereDoesntHave('membresia')
            ->count();
        
        if ($sinMembresia > 0) {
            $this->warn("   ⚠ Hay {$sinMembresia} inscripciones sin membresía válida");
            $this->problemas[] = "{$sinMembresia} inscripciones sin membresía";
        } else {
            $this->info('   ✓ OK - Todas las inscripciones tienen membresía válida');
        }
    }

    // ===================== RESUMEN =====================

    private function mostrarResumen()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        
        if (count($this->problemas) > 0) {
            $this->warn('║  ⚠ RESULTADO: Se encontraron ' . count($this->problemas) . ' problemas                                   ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->warn('Resumen de problemas:');
            foreach ($this->problemas as $i => $problema) {
                $this->line("  " . ($i + 1) . ". {$problema}");
            }
            if (!$this->fix) {
                $this->newLine();
                $this->comment('Ejecute con --fix para intentar corregir automáticamente algunos problemas');
            }
        } else {
            $this->info('║  ✅ RESULTADO: No se encontraron problemas de lógica                        ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        }
        $this->newLine();
    }
}
