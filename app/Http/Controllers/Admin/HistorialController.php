<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialTraspaso;
use App\Models\Cliente;
use App\Models\Membresia;
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
        
        // Datos de traspasos
        $traspasos = $this->getTraspasos($request);
        
        $clientes = Cliente::orderBy('nombres')->get();
        $membresias = Membresia::orderBy('nombre')->get();

        // Estadísticas generales
        $estadisticas = [
            'total_traspasos' => HistorialTraspaso::count(),
            'traspasos_mes' => HistorialTraspaso::whereMonth('fecha_traspaso', now()->month)
                                                ->whereYear('fecha_traspaso', now()->year)
                                                ->count(),
            'con_deuda_transferida' => HistorialTraspaso::where('se_transfirio_deuda', true)->count(),
            'total_deuda_transferida' => HistorialTraspaso::where('se_transfirio_deuda', true)
                                                          ->sum('deuda_transferida'),
        ];

        return view('admin.historial.index', compact(
            'traspasos',
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
}
