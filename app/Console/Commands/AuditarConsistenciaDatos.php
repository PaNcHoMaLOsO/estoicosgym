<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Enums\EstadosCodigo;

class AuditarConsistenciaDatos extends Command
{
    protected $signature = 'audit:consistencia {--fix : Intentar corregir las inconsistencias}';
    protected $description = 'Audita la base de datos en busca de inconsistencias lógicas';

    private $inconsistenciasEncontradas = 0;

    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║           AUDITORÍA DE CONSISTENCIA DE DATOS - ESTOICOS GYM                 ║');
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->info('');

        // ═══════════════════════════════════════════════════════════════
        // PARTE 1: INCONSISTENCIAS EN TRASPASOS
        // ═══════════════════════════════════════════════════════════════
        $this->line('');
        $this->info('📋 PARTE 1: TRASPASOS DE MEMBRESÍA');
        $this->line(str_repeat('─', 70));
        
        $this->auditarInscripcionesTraspasadasConSaldo();
        $this->auditarInscripcionesOrigenTraspaso();
        $this->auditarPagosInscripcionTraspasada();

        // ═══════════════════════════════════════════════════════════════
        // PARTE 2: INCONSISTENCIAS EN ESTADOS
        // ═══════════════════════════════════════════════════════════════
        $this->line('');
        $this->info('📋 PARTE 2: ESTADOS DE INSCRIPCIÓN');
        $this->line(str_repeat('─', 70));
        
        $this->auditarInscripcionesActivasVencidas();
        $this->auditarInscripcionesFinalizadasConVencimientoFuturo();

        // ═══════════════════════════════════════════════════════════════
        // PARTE 3: INCONSISTENCIAS EN PAGOS
        // ═══════════════════════════════════════════════════════════════
        $this->line('');
        $this->info('📋 PARTE 3: PAGOS');
        $this->line(str_repeat('─', 70));
        
        $this->auditarPagosPagadosConPendiente();
        $this->auditarPagosConSobrepago();
        $this->auditarPagosHuerfanos();
        $this->auditarInconsistenciaMontosPago();

        // ═══════════════════════════════════════════════════════════════
        // PARTE 4: INCONSISTENCIAS EN CLIENTES
        // ═══════════════════════════════════════════════════════════════
        $this->line('');
        $this->info('📋 PARTE 4: CLIENTES');
        $this->line(str_repeat('─', 70));
        
        $this->auditarClientesInactivosConInscripcionesActivas();

        // ═══════════════════════════════════════════════════════════════
        // RESUMEN FINAL
        // ═══════════════════════════════════════════════════════════════
        $this->line('');
        $this->line('');
        $this->info('╔══════════════════════════════════════════════════════════════════════════════╗');
        
        if ($this->inconsistenciasEncontradas === 0) {
            $this->info('║  ✅ RESULTADO: No se encontraron inconsistencias                            ║');
        } else {
            $this->error('║  ⚠️  RESULTADO: Se encontraron ' . str_pad($this->inconsistenciasEncontradas, 3) . ' inconsistencias                           ║');
        }
        
        $this->info('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->line('');

        return $this->inconsistenciasEncontradas > 0 ? 1 : 0;
    }

    /**
     * TEST 1.1: Inscripciones traspasadas con saldo pendiente
     */
    private function auditarInscripcionesTraspasadasConSaldo()
    {
        $this->line('');
        $this->comment('🔍 1.1 Inscripciones traspasadas con saldo pendiente...');

        $inscripciones = Inscripcion::where('es_traspaso', true)
            ->orWhere('id_estado', EstadosCodigo::INSCRIPCION_TRASPASADA)
            ->with(['cliente', 'pagos'])
            ->get();

        $problemas = $inscripciones->filter(function ($insc) {
            $estadoPago = $insc->obtenerEstadoPago();
            return $estadoPago['pendiente'] > 0;
        });

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay inscripciones traspasadas con saldo pendiente');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} inscripciones traspasadas con saldo");
        
        $tableData = [];
        foreach ($problemas as $insc) {
            $estadoPago = $insc->obtenerEstadoPago();
            $tableData[] = [
                $insc->id,
                substr($insc->cliente->nombre_completo ?? 'N/A', 0, 25),
                '$' . number_format($estadoPago['monto_total'], 0),
                '$' . number_format($estadoPago['total_abonado'], 0),
                '$' . number_format($estadoPago['pendiente'], 0),
                $insc->es_traspaso ? 'SI' : 'NO',
                $insc->id_estado,
            ];
        }
        
        $this->table(
            ['ID', 'Cliente', 'Total', 'Pagado', 'PENDIENTE', 'Es Traspaso', 'Estado'],
            $tableData
        );
    }

    /**
     * TEST 1.2: Inscripciones origen de traspaso sin estado correcto
     */
    private function auditarInscripcionesOrigenTraspaso()
    {
        $this->line('');
        $this->comment('🔍 1.2 Inscripciones origen de traspaso con estado incorrecto...');

        $inscripcionesOrigen = Inscripcion::whereIn('id', function ($query) {
            $query->select('id_inscripcion_origen')
                ->from('inscripciones')
                ->whereNotNull('id_inscripcion_origen');
        })->with('cliente')->get();

        $problemas = $inscripcionesOrigen->filter(function ($insc) {
            return $insc->id_estado != EstadosCodigo::INSCRIPCION_TRASPASADA;
        });

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - Inscripciones origen tienen estado correcto');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} inscripciones origen con estado incorrecto");
        
        $tableData = [];
        foreach ($problemas as $insc) {
            $tableData[] = [
                $insc->id,
                substr($insc->cliente->nombre_completo ?? 'N/A', 0, 30),
                $insc->id_estado,
                EstadosCodigo::INSCRIPCION_TRASPASADA . ' (TRASPASADA)',
            ];
        }
        
        $this->table(
            ['ID', 'Cliente', 'Estado Actual', 'Debería Ser'],
            $tableData
        );
    }

    /**
     * TEST 1.3: Pagos pendientes en inscripciones traspasadas
     */
    private function auditarPagosInscripcionTraspasada()
    {
        $this->line('');
        $this->comment('🔍 1.3 Pagos pendientes en inscripciones traspasadas...');

        $inscripcionesTraspasadas = Inscripcion::where('id_estado', EstadosCodigo::INSCRIPCION_TRASPASADA)
            ->pluck('id');

        if ($inscripcionesTraspasadas->isEmpty()) {
            $this->info('   ✓ OK - No hay inscripciones traspasadas para verificar');
            return;
        }

        $pagosProblema = Pago::whereIn('id_inscripcion', $inscripcionesTraspasadas)
            ->whereIn('id_estado', [EstadosCodigo::PAGO_PENDIENTE, EstadosCodigo::PAGO_PARCIAL])
            ->with('inscripcion.cliente')
            ->get();

        if ($pagosProblema->count() === 0) {
            $this->info('   ✓ OK - No hay pagos pendientes en inscripciones traspasadas');
            return;
        }

        $this->inconsistenciasEncontradas += $pagosProblema->count();
        $this->error("   ✗ PROBLEMA: {$pagosProblema->count()} pagos pendientes en inscripciones traspasadas");
        
        $tableData = [];
        foreach ($pagosProblema as $pago) {
            $tableData[] = [
                $pago->id,
                $pago->id_inscripcion,
                substr($pago->inscripcion->cliente->nombre_completo ?? 'N/A', 0, 25),
                '$' . number_format($pago->monto_pendiente ?? 0, 0),
                $pago->id_estado,
            ];
        }
        
        $this->table(
            ['Pago ID', 'Inscripción', 'Cliente', 'Pendiente', 'Estado'],
            $tableData
        );
    }

    /**
     * TEST 2.1: Inscripciones activas pero vencidas
     */
    private function auditarInscripcionesActivasVencidas()
    {
        $this->line('');
        $this->comment('🔍 2.1 Inscripciones ACTIVAS con fecha de vencimiento pasada...');

        $problemas = Inscripcion::where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
            ->where('fecha_vencimiento', '<', now())
            ->with('cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay inscripciones activas vencidas');
            return;
        }

        // Esto puede ser normal si no se corre el cron
        $this->warn("   ⚡ ADVERTENCIA: {$problemas->count()} inscripciones activas pero vencidas");
        $this->warn("      (Puede ser normal si no se ejecutó el cron de actualización)");
        
        $tableData = [];
        foreach ($problemas->take(10) as $insc) {
            $tableData[] = [
                $insc->id,
                substr($insc->cliente->nombre_completo ?? 'N/A', 0, 30),
                $insc->fecha_vencimiento?->format('Y-m-d'),
                abs($insc->dias_restantes) . ' días',
            ];
        }
        
        if ($problemas->count() > 10) {
            $this->line("      (Mostrando 10 de {$problemas->count()})");
        }
        
        $this->table(
            ['ID', 'Cliente', 'Venció', 'Hace'],
            $tableData
        );
    }

    /**
     * TEST 2.2: Inscripciones finalizadas con vencimiento futuro
     */
    private function auditarInscripcionesFinalizadasConVencimientoFuturo()
    {
        $this->line('');
        $this->comment('🔍 2.2 Inscripciones CANCELADAS/TRASPASADAS con vencimiento futuro...');

        $estadosFinalizados = [
            EstadosCodigo::INSCRIPCION_CANCELADA,
            EstadosCodigo::INSCRIPCION_TRASPASADA,
        ];

        $problemas = Inscripcion::whereIn('id_estado', $estadosFinalizados)
            ->where('fecha_vencimiento', '>', now())
            ->with('cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay inscripciones finalizadas con vencimiento futuro');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} inscripciones finalizadas con vencimiento futuro");
        
        $tableData = [];
        foreach ($problemas as $insc) {
            $estado = match($insc->id_estado) {
                103 => 'CANCELADA',
                106 => 'TRASPASADA',
                default => $insc->id_estado
            };
            $tableData[] = [
                $insc->id,
                substr($insc->cliente->nombre_completo ?? 'N/A', 0, 30),
                $estado,
                $insc->fecha_vencimiento?->format('Y-m-d'),
                $insc->dias_restantes . ' días',
            ];
        }
        
        $this->table(
            ['ID', 'Cliente', 'Estado', 'Vencimiento', 'Días Restantes'],
            $tableData
        );
    }

    /**
     * TEST 3.1: Pagos marcados como PAGADOS con monto pendiente
     */
    private function auditarPagosPagadosConPendiente()
    {
        $this->line('');
        $this->comment('🔍 3.1 Pagos marcados como PAGADOS con monto pendiente > 0...');

        $problemas = Pago::where('id_estado', EstadosCodigo::PAGO_PAGADO)
            ->where('monto_pendiente', '>', 0)
            ->with('inscripcion.cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay pagos "pagados" con saldo pendiente');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} pagos marcados como PAGADOS con saldo");
        
        $tableData = [];
        foreach ($problemas as $pago) {
            $tableData[] = [
                $pago->id,
                substr($pago->inscripcion->cliente->nombre_completo ?? 'N/A', 0, 25),
                '$' . number_format($pago->monto_total ?? 0, 0),
                '$' . number_format($pago->monto_abonado ?? 0, 0),
                '$' . number_format($pago->monto_pendiente ?? 0, 0),
            ];
        }
        
        $this->table(
            ['Pago ID', 'Cliente', 'Total', 'Abonado', 'PENDIENTE'],
            $tableData
        );
    }

    /**
     * TEST 3.2: Pagos con sobrepago
     */
    private function auditarPagosConSobrepago()
    {
        $this->line('');
        $this->comment('🔍 3.2 Pagos donde monto_abonado > monto_total (sobrepago)...');

        $problemas = Pago::whereRaw('CAST(monto_abonado AS DECIMAL(10,2)) > CAST(monto_total AS DECIMAL(10,2))')
            ->where('monto_total', '>', 0)
            ->with('inscripcion.cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay pagos con sobrepago');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} pagos con sobrepago");
        
        $tableData = [];
        foreach ($problemas as $pago) {
            $diferencia = ($pago->monto_abonado ?? 0) - ($pago->monto_total ?? 0);
            $tableData[] = [
                $pago->id,
                substr($pago->inscripcion->cliente->nombre_completo ?? 'N/A', 0, 25),
                '$' . number_format($pago->monto_total ?? 0, 0),
                '$' . number_format($pago->monto_abonado ?? 0, 0),
                '$' . number_format($diferencia, 0),
            ];
        }
        
        $this->table(
            ['Pago ID', 'Cliente', 'Total', 'Abonado', 'SOBREPAGO'],
            $tableData
        );
    }

    /**
     * TEST 3.3: Pagos huérfanos (sin inscripción válida)
     */
    private function auditarPagosHuerfanos()
    {
        $this->line('');
        $this->comment('🔍 3.3 Pagos sin inscripción válida (huérfanos)...');

        $problemas = Pago::whereNotIn('id_inscripcion', function ($query) {
            $query->select('id')->from('inscripciones');
        })->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay pagos huérfanos');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} pagos sin inscripción válida");
        
        $tableData = [];
        foreach ($problemas as $pago) {
            $tableData[] = [
                $pago->id,
                $pago->id_inscripcion,
                '$' . number_format($pago->monto_total ?? 0, 0),
                $pago->fecha_pago?->format('Y-m-d'),
            ];
        }
        
        $this->table(
            ['Pago ID', 'Inscripción (inexistente)', 'Monto', 'Fecha'],
            $tableData
        );
    }

    /**
     * TEST 3.4: Inconsistencia en montos (monto_total != monto_abonado + monto_pendiente)
     */
    private function auditarInconsistenciaMontosPago()
    {
        $this->line('');
        $this->comment('🔍 3.4 Pagos donde total ≠ abonado + pendiente...');

        $problemas = Pago::whereRaw('ABS(CAST(monto_total AS DECIMAL(10,2)) - (CAST(monto_abonado AS DECIMAL(10,2)) + CAST(monto_pendiente AS DECIMAL(10,2)))) > 1')
            ->with('inscripcion.cliente')
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - Los montos de pagos cuadran correctamente');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} pagos con montos inconsistentes");
        
        $tableData = [];
        foreach ($problemas as $pago) {
            $suma = ($pago->monto_abonado ?? 0) + ($pago->monto_pendiente ?? 0);
            $diferencia = ($pago->monto_total ?? 0) - $suma;
            $tableData[] = [
                $pago->id,
                substr($pago->inscripcion->cliente->nombre_completo ?? 'N/A', 0, 20),
                '$' . number_format($pago->monto_total ?? 0, 0),
                '$' . number_format($pago->monto_abonado ?? 0, 0),
                '$' . number_format($pago->monto_pendiente ?? 0, 0),
                '$' . number_format($diferencia, 0),
            ];
        }
        
        $this->table(
            ['Pago ID', 'Cliente', 'Total', 'Abonado', 'Pendiente', 'Diferencia'],
            $tableData
        );
    }

    /**
     * TEST 4.1: Clientes inactivos con inscripciones activas
     */
    private function auditarClientesInactivosConInscripcionesActivas()
    {
        $this->line('');
        $this->comment('🔍 4.1 Clientes INACTIVOS con inscripciones ACTIVAS...');

        $problemas = Cliente::where('activo', false)
            ->whereHas('inscripciones', function ($query) {
                $query->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA);
            })
            ->with(['inscripciones' => function ($query) {
                $query->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA);
            }])
            ->get();

        if ($problemas->count() === 0) {
            $this->info('   ✓ OK - No hay clientes inactivos con inscripciones activas');
            return;
        }

        $this->inconsistenciasEncontradas += $problemas->count();
        $this->error("   ✗ PROBLEMA: {$problemas->count()} clientes inactivos con inscripciones activas");
        
        $tableData = [];
        foreach ($problemas as $cliente) {
            $tableData[] = [
                $cliente->id,
                substr($cliente->nombre_completo, 0, 30),
                $cliente->activo ? 'SI' : 'NO',
                $cliente->inscripciones->count(),
            ];
        }
        
        $this->table(
            ['Cliente ID', 'Nombre', 'Activo', 'Inscripciones Activas'],
            $tableData
        );
    }
}
