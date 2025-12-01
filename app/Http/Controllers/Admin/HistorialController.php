<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialTraspaso;
use App\Models\HistorialCambio;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Pago;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    /**
     * Página principal del módulo Historial
     * Muestra tabs para diferentes tipos de historial
     */
    public function index(Request $request)
    {
        // Tab activo (por defecto traspasos)
        $tab = $request->get('tab', 'traspasos');
        
        // Datos según tab
        $traspasos = $this->getTraspasos($request);
        $pausas = $this->getPausas($request);
        $cambiosPlan = $this->getCambiosPlan($request);
        $pagos = $this->getPagos($request);
        
        $clientes = Cliente::orderBy('nombres')->get();
        $membresias = Membresia::orderBy('nombre')->get();

        // Estadísticas generales
        $estadisticas = [
            // Traspasos
            'total_traspasos' => HistorialTraspaso::count(),
            'traspasos_mes' => HistorialTraspaso::whereMonth('fecha_traspaso', now()->month)
                                                ->whereYear('fecha_traspaso', now()->year)
                                                ->count(),
            'con_deuda_transferida' => HistorialTraspaso::where('se_transfirio_deuda', true)->count(),
            'total_deuda_transferida' => HistorialTraspaso::where('se_transfirio_deuda', true)
                                                          ->sum('deuda_transferida'),
            // Pausas
            'total_pausas' => HistorialCambio::pausas()->count(),
            'pausas_mes' => HistorialCambio::pausas()
                                           ->whereMonth('fecha_cambio', now()->month)
                                           ->whereYear('fecha_cambio', now()->year)
                                           ->count(),
            // Cambios de plan
            'total_cambios_plan' => HistorialCambio::cambiosPlan()->count(),
            'cambios_plan_mes' => HistorialCambio::cambiosPlan()
                                                 ->whereMonth('fecha_cambio', now()->month)
                                                 ->whereYear('fecha_cambio', now()->year)
                                                 ->count(),
            // Pagos
            'total_pagos' => Pago::count(),
            'pagos_mes' => Pago::whereMonth('fecha_pago', now()->month)
                               ->whereYear('fecha_pago', now()->year)
                               ->count(),
            'total_recaudado' => Pago::sum('monto_abonado'),
            'recaudado_mes' => Pago::whereMonth('fecha_pago', now()->month)
                                   ->whereYear('fecha_pago', now()->year)
                                   ->sum('monto_abonado'),
        ];

        return view('admin.historial.index', compact(
            'traspasos',
            'pausas',
            'cambiosPlan',
            'pagos',
            'clientes',
            'membresias',
            'estadisticas',
            'tab'
        ));
    }
    
    /**
     * Obtener traspasos con filtros
     */
    private function getTraspasos(Request $request)
    {
        $query = HistorialTraspaso::with([
            'clienteOrigen',
            'clienteDestino',
            'membresia',
            'inscripcionOrigen',
            'inscripcionDestino',
            'usuario'
        ]);

        if ($request->filled('cliente_id')) {
            $clienteId = $request->cliente_id;
            $query->where(function ($q) use ($clienteId) {
                $q->where('cliente_origen_id', $clienteId)
                  ->orWhere('cliente_destino_id', $clienteId);
            });
        }

        if ($request->filled('membresia_id')) {
            $query->where('membresia_id', $request->membresia_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_traspaso', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_traspaso', '<=', $request->fecha_hasta);
        }

        if ($request->filled('con_deuda')) {
            $query->where('se_transfirio_deuda', true);
        }

        return $query->orderBy('fecha_traspaso', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate(20);
    }

    /**
     * Muestra los detalles de un traspaso específico
     */
    public function showTraspaso(HistorialTraspaso $traspaso)
    {
        $traspaso->load([
            'clienteOrigen',
            'clienteDestino',
            'membresia',
            'inscripcionOrigen.estado',
            'inscripcionOrigen.pagos',
            'inscripcionDestino.estado',
            'inscripcionDestino.pagos',
            'usuario'
        ]);

        return view('admin.historial.traspaso-show', compact('traspaso'));
    }

    /**
     * Obtener pausas/reanudaciones con filtros
     */
    private function getPausas(Request $request)
    {
        $query = HistorialCambio::with(['cliente', 'inscripcion.membresia', 'usuario'])
            ->pausas();

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_cambio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_cambio', '<=', $request->fecha_hasta);
        }

        return $query->orderBy('fecha_cambio', 'desc')->paginate(20, ['*'], 'pausas_page');
    }

    /**
     * Obtener cambios de plan con filtros
     */
    private function getCambiosPlan(Request $request)
    {
        $query = HistorialCambio::with(['cliente', 'inscripcion.membresia', 'usuario'])
            ->cambiosPlan();

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_cambio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_cambio', '<=', $request->fecha_hasta);
        }

        return $query->orderBy('fecha_cambio', 'desc')->paginate(20, ['*'], 'cambios_page');
    }

    /**
     * Obtener pagos con filtros
     */
    private function getPagos(Request $request)
    {
        $query = Pago::with(['cliente', 'inscripcion.membresia', 'metodoPago', 'estado']);

        if ($request->filled('cliente_id')) {
            $query->where('id_cliente', $request->cliente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado_pago')) {
            $query->where('id_estado', $request->estado_pago);
        }

        return $query->orderBy('fecha_pago', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate(20, ['*'], 'pagos_page');
    }
}
