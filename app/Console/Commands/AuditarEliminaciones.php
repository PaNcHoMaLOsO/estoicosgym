<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class AuditarEliminaciones extends Command
{
    protected $signature = 'audit:eliminaciones';
    protected $description = 'Audita efectos colaterales de eliminar pagos, inscripciones o desactivar clientes';

    private $problemas = [];

    public function handle()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║     AUDITORÍA DE ELIMINACIONES Y EFECTOS COLATERALES                        ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // PARTE 1: ANÁLISIS DE CÓDIGO
        $this->info('📋 PARTE 1: ANÁLISIS DE VALIDACIONES EN CÓDIGO');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->analizarEliminacionPago();
        $this->analizarEliminacionInscripcion();
        $this->analizarDesactivacionCliente();
        
        // PARTE 2: INTEGRIDAD DE DATOS
        $this->newLine();
        $this->info('📋 PARTE 2: INTEGRIDAD DE DATOS ACTUAL');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->verificarPagosHuerfanos();
        $this->verificarClientesSinInscripciones();
        $this->verificarInscripcionesActivasSinPagos();
        
        // PARTE 3: SIMULACIÓN DE ESCENARIOS
        $this->newLine();
        $this->info('📋 PARTE 3: ESCENARIOS DE RIESGO');
        $this->line('──────────────────────────────────────────────────────────────────────');
        
        $this->escenarioEliminarUnicoPago();
        $this->escenarioEliminarInscripcionConPagos();
        $this->escenarioDesactivarClienteActivo();
        
        // RESUMEN
        $this->mostrarResumen();
        
        return count($this->problemas) > 0 ? 1 : 0;
    }

    // ===================== ANÁLISIS DE CÓDIGO =====================

    private function analizarEliminacionPago()
    {
        $this->line('');
        $this->comment('🔍 1.1 Validaciones al eliminar PAGO...');
        
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
        
        // Buscar el método destroy
        if (preg_match('/function destroy\(.*?\{(.*?)\n\s*\}/s', $controllerCode, $matches)) {
            $destroyCode = $matches[1];
            
            $validaciones = [];
            
            // Verificar si hay validación de inscripción activa
            if (strpos($destroyCode, 'inscripcion') !== false && strpos($destroyCode, 'estado') !== false) {
                $validaciones[] = 'Verifica estado de inscripción';
            }
            
            // Verificar si hay validación de único pago
            if (strpos($destroyCode, 'count') !== false || strpos($destroyCode, 'pagos') !== false) {
                $validaciones[] = 'Verifica si es el único pago';
            }
            
            // Verificar si hay recálculo de saldos
            if (strpos($destroyCode, 'monto') !== false || strpos($destroyCode, 'recalcular') !== false) {
                $validaciones[] = 'Recalcula saldos';
            }
            
            if (empty($validaciones)) {
                $this->warn('   ⚠ NO hay validaciones al eliminar pago');
                $this->line('     Riesgos:');
                $this->line('     - Puede dejar inscripciones sin pagos');
                $this->line('     - No recalcula saldos pendientes');
                $this->line('     - Puede eliminar el único registro de pago');
                $this->problemas[] = 'Eliminar pago: sin validaciones';
            } else {
                $this->info('   ✓ Validaciones encontradas: ' . implode(', ', $validaciones));
            }
        }
    }

    private function analizarEliminacionInscripcion()
    {
        $this->line('');
        $this->comment('🔍 1.2 Validaciones al eliminar INSCRIPCIÓN...');
        
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/InscripcionController.php'));
        
        if (preg_match('/function destroy\(.*?\{(.*?)\n\s{4}\}/s', $controllerCode, $matches)) {
            $destroyCode = $matches[1];
            
            $validaciones = [];
            
            // Verificar si hay validación de pagos asociados
            if (strpos($destroyCode, 'pagos') !== false) {
                $validaciones[] = 'Verifica pagos asociados';
            }
            
            // Verificar si hay validación de estado
            if (strpos($destroyCode, 'id_estado') !== false || strpos($destroyCode, 'activ') !== false) {
                $validaciones[] = 'Verifica estado de inscripción';
            }
            
            // Verificar si elimina en cascada
            if (strpos($destroyCode, '->pagos()->delete') !== false) {
                $validaciones[] = 'Elimina pagos en cascada';
            }
            
            if (empty($validaciones)) {
                $this->warn('   ⚠ NO hay validaciones al eliminar inscripción');
                $this->line('     Riesgos:');
                $this->line('     - Puede dejar pagos huérfanos (FK puede fallar)');
                $this->line('     - Puede eliminar inscripción activa');
                $this->line('     - Pierde historial de pagos');
                $this->problemas[] = 'Eliminar inscripción: sin validaciones';
            } else {
                $this->info('   ✓ Validaciones encontradas: ' . implode(', ', $validaciones));
            }
        }
    }

    private function analizarDesactivacionCliente()
    {
        $this->line('');
        $this->comment('🔍 1.3 Validaciones al desactivar CLIENTE...');
        
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/ClienteController.php'));
        
        $validaciones = [];
        
        if (strpos($controllerCode, 'inscripciones') !== false && strpos($controllerCode, 'whereIn') !== false) {
            $validaciones[] = 'Verifica inscripciones activas';
        }
        
        if (strpos($controllerCode, 'pagos') !== false && strpos($controllerCode, 'pendientes') !== false) {
            $validaciones[] = 'Verifica pagos pendientes';
        }
        
        if (strpos($controllerCode, 'soft delete') !== false || strpos($controllerCode, 'activo') !== false) {
            $validaciones[] = 'Usa soft delete (desactivación)';
        }
        
        if (count($validaciones) >= 2) {
            $this->info('   ✓ Validaciones correctas: ' . implode(', ', $validaciones));
        } else {
            $this->warn('   ⚠ Validaciones parciales: ' . implode(', ', $validaciones));
        }
    }

    // ===================== INTEGRIDAD DE DATOS =====================

    private function verificarPagosHuerfanos()
    {
        $this->line('');
        $this->comment('🔍 2.1 Pagos huérfanos (sin inscripción válida)...');
        
        $pagosHuerfanos = Pago::whereDoesntHave('inscripcion')->count();
        
        if ($pagosHuerfanos > 0) {
            $this->warn("   ⚠ Hay {$pagosHuerfanos} pagos sin inscripción asociada");
            $this->problemas[] = "{$pagosHuerfanos} pagos huérfanos";
        } else {
            $this->info('   ✓ OK - Todos los pagos tienen inscripción válida');
        }
    }

    private function verificarClientesSinInscripciones()
    {
        $this->line('');
        $this->comment('🔍 2.2 Clientes activos sin inscripciones...');
        
        $clientesSinInscripciones = Cliente::where('activo', true)
            ->whereDoesntHave('inscripciones')
            ->count();
        
        if ($clientesSinInscripciones > 0) {
            $this->info("   ℹ Hay {$clientesSinInscripciones} clientes activos sin inscripciones (puede ser normal)");
        } else {
            $this->info('   ✓ OK - Todos los clientes activos tienen al menos una inscripción');
        }
    }

    private function verificarInscripcionesActivasSinPagos()
    {
        $this->line('');
        $this->comment('🔍 2.3 Inscripciones ACTIVAS sin ningún pago...');
        
        $sinPagos = Inscripcion::where('id_estado', 100) // Activa
            ->whereDoesntHave('pagos')
            ->with(['cliente', 'membresia'])
            ->get();
        
        if ($sinPagos->count() > 0) {
            $this->warn("   ⚠ Hay {$sinPagos->count()} inscripciones activas sin pagos:");
            foreach ($sinPagos->take(5) as $insc) {
                $this->line("     - #{$insc->id}: {$insc->cliente->nombres} - {$insc->membresia->nombre}");
            }
            $this->problemas[] = "{$sinPagos->count()} inscripciones activas sin pagos";
        } else {
            $this->info('   ✓ OK - Todas las inscripciones activas tienen al menos un pago');
        }
    }

    // ===================== ESCENARIOS DE RIESGO =====================

    private function escenarioEliminarUnicoPago()
    {
        $this->line('');
        $this->comment('🧪 3.1 ESCENARIO: Eliminar el único pago de una inscripción activa...');
        
        // Buscar inscripciones activas con exactamente 1 pago
        $inscripcionesUnPago = Inscripcion::where('id_estado', 100)
            ->withCount('pagos')
            ->having('pagos_count', 1)
            ->count();
        
        if ($inscripcionesUnPago > 0) {
            $this->info("   ℹ Hay {$inscripcionesUnPago} inscripciones activas con un solo pago");
            
            // Verificar si el código tiene validación
            $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/PagoController.php'));
            if (strpos($controllerCode, 'único pago') !== false || strpos($controllerCode, 'totalPagos') !== false) {
                $this->info('   ✓ El código impide eliminar el único pago de inscripciones activas');
            } else {
                $this->warn('     → Si se elimina ese pago, la inscripción quedaría sin registro');
                $this->problemas[] = 'Sin validación para único pago';
            }
        } else {
            $this->info('   ✓ No hay inscripciones con un único pago (bajo riesgo)');
        }
    }

    private function escenarioEliminarInscripcionConPagos()
    {
        $this->line('');
        $this->comment('🧪 3.2 ESCENARIO: Eliminar inscripción que tiene pagos...');
        
        $inscripcionesConPagos = Inscripcion::has('pagos')->count();
        
        $this->line("   → Hay {$inscripcionesConPagos} inscripciones con pagos asociados");
        
        // Verificar FK constraint
        $fkExists = DB::select("
            SELECT COUNT(*) as cnt 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'pagos' 
            AND COLUMN_NAME = 'id_inscripcion' 
            AND REFERENCED_TABLE_NAME = 'inscripciones'
        ");
        
        if (!empty($fkExists) && $fkExists[0]->cnt > 0) {
            $this->info('   ✓ FK existe: La BD impide eliminar inscripción con pagos');
        } else {
            $this->warn('   ⚠ No hay FK o no se pudo verificar');
            $this->line('     → RECOMENDACIÓN: Agregar validación en InscripcionController::destroy()');
            $this->problemas[] = 'Falta validación al eliminar inscripción con pagos';
        }
    }

    private function escenarioDesactivarClienteActivo()
    {
        $this->line('');
        $this->comment('🧪 3.3 ESCENARIO: Desactivar cliente con inscripción activa...');
        
        $clientesConInscActiva = Cliente::where('activo', true)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 100); // Activa
            })
            ->count();
        
        $this->line("   → Hay {$clientesConInscActiva} clientes activos con inscripción activa");
        
        // Verificar si el código valida esto
        $controllerCode = file_get_contents(app_path('Http/Controllers/Admin/ClienteController.php'));
        if (strpos($controllerCode, 'INSCRIPCION_REQUIERE_CLIENTE_ACTIVO') !== false) {
            $this->info('   ✓ El código valida inscripciones activas antes de desactivar cliente');
        } else {
            $this->warn('   ⚠ No se encontró validación explícita');
            $this->problemas[] = 'Falta validación clara al desactivar cliente';
        }
    }

    // ===================== RESUMEN =====================

    private function mostrarResumen()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        
        if (count($this->problemas) > 0) {
            $this->warn('║  ⚠ RESULTADO: Se encontraron ' . count($this->problemas) . ' problemas potenciales                       ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->warn('Resumen de problemas:');
            foreach ($this->problemas as $i => $problema) {
                $this->line("  " . ($i + 1) . ". {$problema}");
            }
            $this->newLine();
            $this->comment('RECOMENDACIONES:');
            $this->line('  1. Agregar validación en PagoController::destroy() para evitar dejar inscripciones sin pagos');
            $this->line('  2. Agregar validación en InscripcionController::destroy() para verificar pagos asociados');
            $this->line('  3. Considerar usar soft delete para pagos en lugar de eliminación física');
        } else {
            $this->info('║  ✅ RESULTADO: No se encontraron problemas                                  ║');
            $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        }
        $this->newLine();
    }
}
