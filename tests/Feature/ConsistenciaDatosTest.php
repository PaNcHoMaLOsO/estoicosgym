<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Enums\EstadosCodigo;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests para detectar inconsistencias de datos en el sistema
 * Busca combinaciones que no deberían existir según las reglas de negocio
 */
class ConsistenciaDatosTest extends TestCase
{
    /**
     * =====================================================
     * PARTE 1: INCONSISTENCIAS EN TRASPASOS DE MEMBRESÍA
     * =====================================================
     */

    /**
     * TEST 1.1: Inscripciones traspasadas NO deben tener saldo pendiente
     * Regla: Cuando se traspasa una membresía, el saldo debería transferirse o cancelarse
     */
    public function test_inscripciones_traspasadas_no_deben_tener_saldo_pendiente()
    {
        $inscripcionesConProblema = Inscripcion::where('es_traspaso', true)
            ->orWhere('id_estado', EstadosCodigo::INSCRIPCION_TRASPASADA)
            ->get()
            ->filter(function ($inscripcion) {
                $estadoPago = $inscripcion->obtenerEstadoPago();
                return $estadoPago['pendiente'] > 0;
            });

        $detalles = [];
        foreach ($inscripcionesConProblema as $insc) {
            $estadoPago = $insc->obtenerEstadoPago();
            $detalles[] = [
                'inscripcion_id' => $insc->id,
                'uuid' => $insc->uuid,
                'cliente' => $insc->cliente->nombre_completo ?? 'N/A',
                'cliente_original' => $insc->id_cliente_original ? Cliente::find($insc->id_cliente_original)?->nombre_completo : 'N/A',
                'precio_final' => $estadoPago['monto_total'],
                'total_pagado' => $estadoPago['total_abonado'],
                'pendiente' => $estadoPago['pendiente'],
                'es_traspaso' => $insc->es_traspaso,
                'id_estado' => $insc->id_estado,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA ENCONTRADA: Inscripciones traspasadas con saldo pendiente\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "ID: {$d['inscripcion_id']} | UUID: {$d['uuid']}\n";
                echo "   Cliente actual: {$d['cliente']}\n";
                echo "   Cliente original: {$d['cliente_original']}\n";
                echo "   Precio: \${$d['precio_final']} | Pagado: \${$d['total_pagado']} | PENDIENTE: \${$d['pendiente']}\n";
                echo "   es_traspaso: " . ($d['es_traspaso'] ? 'SI' : 'NO') . " | id_estado: {$d['id_estado']}\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $inscripcionesConProblema, 
            "Se encontraron " . count($inscripcionesConProblema) . " inscripciones traspasadas con saldo pendiente");
    }

    /**
     * TEST 1.2: Inscripciones ORIGEN de traspaso deben tener estado TRASPASADA (106)
     * Regla: La inscripción original debe quedar marcada como traspasada
     */
    public function test_inscripciones_origen_traspaso_deben_tener_estado_correcto()
    {
        // Buscar inscripciones que son origen de un traspaso
        $inscripcionesOrigen = Inscripcion::whereIn('id', function ($query) {
            $query->select('id_inscripcion_origen')
                ->from('inscripciones')
                ->whereNotNull('id_inscripcion_origen');
        })->get();

        $inscripcionesConProblema = $inscripcionesOrigen->filter(function ($insc) {
            return $insc->id_estado != EstadosCodigo::INSCRIPCION_TRASPASADA;
        });

        $detalles = [];
        foreach ($inscripcionesConProblema as $insc) {
            $detalles[] = [
                'inscripcion_id' => $insc->id,
                'cliente' => $insc->cliente->nombre_completo ?? 'N/A',
                'id_estado_actual' => $insc->id_estado,
                'estado_esperado' => EstadosCodigo::INSCRIPCION_TRASPASADA,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Inscripciones origen de traspaso con estado incorrecto\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "ID: {$d['inscripcion_id']} | Cliente: {$d['cliente']}\n";
                echo "   Estado actual: {$d['id_estado_actual']} | Debería ser: {$d['estado_esperado']} (TRASPASADA)\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $inscripcionesConProblema,
            "Se encontraron " . count($inscripcionesConProblema) . " inscripciones origen con estado incorrecto");
    }

    /**
     * TEST 1.3: Pagos de inscripciones traspasadas deben tener estado TRASPASADO o PAGADO
     * Regla: Los pagos no deben quedar como PENDIENTE o PARCIAL si la inscripción fue traspasada
     */
    public function test_pagos_inscripcion_traspasada_no_deben_estar_pendientes()
    {
        $inscripcionesTraspasadas = Inscripcion::where('id_estado', EstadosCodigo::INSCRIPCION_TRASPASADA)
            ->pluck('id');

        $pagosConProblema = Pago::whereIn('id_inscripcion', $inscripcionesTraspasadas)
            ->whereIn('id_estado', [EstadosCodigo::PAGO_PENDIENTE, EstadosCodigo::PAGO_PARCIAL])
            ->get();

        $detalles = [];
        foreach ($pagosConProblema as $pago) {
            $detalles[] = [
                'pago_id' => $pago->id,
                'inscripcion_id' => $pago->id_inscripcion,
                'cliente' => $pago->inscripcion->cliente->nombre_completo ?? 'N/A',
                'monto_total' => $pago->monto_total,
                'monto_abonado' => $pago->monto_abonado,
                'monto_pendiente' => $pago->monto_pendiente,
                'id_estado_pago' => $pago->id_estado,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Pagos pendientes en inscripciones traspasadas\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "Pago ID: {$d['pago_id']} | Inscripcion: {$d['inscripcion_id']}\n";
                echo "   Cliente: {$d['cliente']}\n";
                echo "   Total: \${$d['monto_total']} | Abonado: \${$d['monto_abonado']} | Pendiente: \${$d['monto_pendiente']}\n";
                echo "   Estado pago: {$d['id_estado_pago']} (debería ser 201-PAGADO o 205-TRASPASADO)\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $pagosConProblema,
            "Se encontraron " . count($pagosConProblema) . " pagos pendientes en inscripciones traspasadas");
    }

    /**
     * =====================================================
     * PARTE 2: INCONSISTENCIAS EN ESTADOS DE INSCRIPCIÓN
     * =====================================================
     */

    /**
     * TEST 2.1: Inscripciones ACTIVAS con fecha_vencimiento en el pasado
     * Regla: Si está vencida, no debería tener estado ACTIVA (100)
     */
    public function test_inscripciones_activas_no_deben_estar_vencidas()
    {
        $inscripcionesConProblema = Inscripcion::where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
            ->where('fecha_vencimiento', '<', now())
            ->get();

        $detalles = [];
        foreach ($inscripcionesConProblema as $insc) {
            $detalles[] = [
                'inscripcion_id' => $insc->id,
                'cliente' => $insc->cliente->nombre_completo ?? 'N/A',
                'fecha_vencimiento' => $insc->fecha_vencimiento?->format('Y-m-d'),
                'dias_vencida' => abs($insc->dias_restantes),
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🟡 ADVERTENCIA: Inscripciones marcadas como ACTIVAS pero ya vencidas\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "ID: {$d['inscripcion_id']} | Cliente: {$d['cliente']}\n";
                echo "   Venció: {$d['fecha_vencimiento']} (hace {$d['dias_vencida']} días)\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        // Esto puede ser normal si no se ha corrido el cron, así que solo advertimos
        if (count($inscripcionesConProblema) > 0) {
            $this->markTestIncomplete(
                "Se encontraron " . count($inscripcionesConProblema) . " inscripciones activas vencidas. " .
                "Puede ser normal si no se ejecutó el cron de actualización de estados."
            );
        }

        $this->assertTrue(true);
    }

    /**
     * TEST 2.2: Inscripciones CANCELADAS o TRASPASADAS no deben tener fecha_vencimiento futura
     * Regla: Si fue cancelada/traspasada, la fecha debería ser pasada o null
     */
    public function test_inscripciones_finalizadas_no_deben_tener_vencimiento_futuro()
    {
        $estadosFinalizados = [
            EstadosCodigo::INSCRIPCION_CANCELADA,
            EstadosCodigo::INSCRIPCION_TRASPASADA,
        ];

        $inscripcionesConProblema = Inscripcion::whereIn('id_estado', $estadosFinalizados)
            ->where('fecha_vencimiento', '>', now())
            ->get();

        $detalles = [];
        foreach ($inscripcionesConProblema as $insc) {
            $estadoNombre = match($insc->id_estado) {
                103 => 'CANCELADA',
                106 => 'TRASPASADA',
                default => $insc->id_estado
            };
            $detalles[] = [
                'inscripcion_id' => $insc->id,
                'cliente' => $insc->cliente->nombre_completo ?? 'N/A',
                'estado' => $estadoNombre,
                'fecha_vencimiento' => $insc->fecha_vencimiento?->format('Y-m-d'),
                'dias_restantes' => $insc->dias_restantes,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Inscripciones finalizadas con fecha de vencimiento futura\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "ID: {$d['inscripcion_id']} | Cliente: {$d['cliente']}\n";
                echo "   Estado: {$d['estado']} | Vence: {$d['fecha_vencimiento']} ({$d['dias_restantes']} días restantes)\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $inscripcionesConProblema,
            "Se encontraron " . count($inscripcionesConProblema) . " inscripciones finalizadas con vencimiento futuro");
    }

    /**
     * =====================================================
     * PARTE 3: INCONSISTENCIAS EN PAGOS
     * =====================================================
     */

    /**
     * TEST 3.1: Pagos PAGADOS no deben tener monto_pendiente > 0
     * Regla: Si está marcado como pagado, el pendiente debe ser 0
     */
    public function test_pagos_pagados_no_deben_tener_monto_pendiente()
    {
        $pagosConProblema = Pago::where('id_estado', EstadosCodigo::PAGO_PAGADO)
            ->where('monto_pendiente', '>', 0)
            ->get();

        $detalles = [];
        foreach ($pagosConProblema as $pago) {
            $detalles[] = [
                'pago_id' => $pago->id,
                'cliente' => $pago->inscripcion->cliente->nombre_completo ?? 'N/A',
                'monto_total' => $pago->monto_total,
                'monto_abonado' => $pago->monto_abonado,
                'monto_pendiente' => $pago->monto_pendiente,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Pagos marcados como PAGADOS con monto pendiente\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "Pago ID: {$d['pago_id']} | Cliente: {$d['cliente']}\n";
                echo "   Total: \${$d['monto_total']} | Abonado: \${$d['monto_abonado']} | PENDIENTE: \${$d['monto_pendiente']}\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $pagosConProblema,
            "Se encontraron " . count($pagosConProblema) . " pagos marcados como PAGADOS con saldo pendiente");
    }

    /**
     * TEST 3.2: Pagos donde monto_abonado > monto_total (sobrepago)
     * Regla: No debería abonarse más de lo que se debe
     */
    public function test_pagos_no_deben_tener_sobrepago()
    {
        $pagosConProblema = Pago::whereRaw('monto_abonado > monto_total')
            ->where('monto_total', '>', 0)
            ->get();

        $detalles = [];
        foreach ($pagosConProblema as $pago) {
            $detalles[] = [
                'pago_id' => $pago->id,
                'cliente' => $pago->inscripcion->cliente->nombre_completo ?? 'N/A',
                'monto_total' => $pago->monto_total,
                'monto_abonado' => $pago->monto_abonado,
                'diferencia' => $pago->monto_abonado - $pago->monto_total,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Pagos con sobrepago (abonado > total)\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "Pago ID: {$d['pago_id']} | Cliente: {$d['cliente']}\n";
                echo "   Total: \${$d['monto_total']} | Abonado: \${$d['monto_abonado']} | SOBREPAGO: \${$d['diferencia']}\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $pagosConProblema,
            "Se encontraron " . count($pagosConProblema) . " pagos con sobrepago");
    }

    /**
     * TEST 3.3: Pagos huérfanos (sin inscripción válida)
     * Regla: Todo pago debe tener una inscripción asociada
     */
    public function test_pagos_deben_tener_inscripcion_valida()
    {
        $pagosConProblema = Pago::whereNotIn('id_inscripcion', function ($query) {
            $query->select('id')->from('inscripciones');
        })->get();

        $detalles = [];
        foreach ($pagosConProblema as $pago) {
            $detalles[] = [
                'pago_id' => $pago->id,
                'id_inscripcion' => $pago->id_inscripcion,
                'monto_total' => $pago->monto_total,
                'fecha_pago' => $pago->fecha_pago?->format('Y-m-d'),
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Pagos sin inscripción válida (huérfanos)\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "Pago ID: {$d['pago_id']} | Inscripción inexistente: {$d['id_inscripcion']}\n";
                echo "   Monto: \${$d['monto_total']} | Fecha: {$d['fecha_pago']}\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $pagosConProblema,
            "Se encontraron " . count($pagosConProblema) . " pagos sin inscripción válida");
    }

    /**
     * =====================================================
     * PARTE 4: INCONSISTENCIAS EN CLIENTES
     * =====================================================
     */

    /**
     * TEST 4.1: Clientes INACTIVOS con inscripciones ACTIVAS
     * Regla: Si el cliente está inactivo, no debería tener membresías activas
     */
    public function test_clientes_inactivos_no_deben_tener_inscripciones_activas()
    {
        $clientesConProblema = Cliente::where('activo', false)
            ->whereHas('inscripciones', function ($query) {
                $query->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA);
            })
            ->get();

        $detalles = [];
        foreach ($clientesConProblema as $cliente) {
            $inscripcionesActivas = $cliente->inscripciones()
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
                ->count();
            
            $detalles[] = [
                'cliente_id' => $cliente->id,
                'nombre' => $cliente->nombre_completo,
                'activo' => $cliente->activo ? 'SI' : 'NO',
                'inscripciones_activas' => $inscripcionesActivas,
            ];
        }

        if (count($detalles) > 0) {
            echo "\n\n🔴 INCONSISTENCIA: Clientes inactivos con inscripciones activas\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($detalles as $d) {
                echo "Cliente ID: {$d['cliente_id']} | {$d['nombre']}\n";
                echo "   Cliente activo: {$d['activo']} | Inscripciones activas: {$d['inscripciones_activas']}\n";
                echo str_repeat("-", 60) . "\n";
            }
        }

        $this->assertCount(0, $clientesConProblema,
            "Se encontraron " . count($clientesConProblema) . " clientes inactivos con inscripciones activas");
    }
}
