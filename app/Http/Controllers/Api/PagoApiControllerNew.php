<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Estado;
use Illuminate\Http\Request;

class PagoApiController extends Controller
{
    /**
     * Listar pagos recientes
     */
    public function index()
    {
        $pagos = Pago::with(['inscripcion.cliente', 'metodoPago', 'estado'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'cliente' => $pago->inscripcion->cliente->nombres . ' ' . $pago->inscripcion->cliente->apellido_paterno,
                    'inscripcion_id' => $pago->inscripcion->id,
                    'monto' => $pago->monto_abonado,
                    'fecha' => $pago->fecha_pago->format('Y-m-d'),
                    'metodo' => $pago->metodoPago?->nombre,
                    'estado' => $pago->estado?->nombre,
                    'estado_color' => $pago->estado?->color,
                ];
            });

        return response()->json($pagos);
    }

    /**
     * Obtener pago específico
     */
    public function show($id)
    {
        $pago = Pago::with(['inscripcion.cliente', 'inscripcion.membresia', 'metodoPago', 'estado'])
            ->findOrFail($id);

        return response()->json([
            'id' => $pago->id,
            'cliente' => $pago->inscripcion->cliente->nombres . ' ' . $pago->inscripcion->cliente->apellido_paterno,
            'inscripcion_id' => $pago->inscripcion->id,
            'membresia' => $pago->inscripcion->membresia?->nombre,
            'fecha_pago' => $pago->fecha_pago->format('Y-m-d H:i'),
            'montos' => [
                'total' => $pago->monto_total,
                'abonado' => $pago->monto_abonado,
                'pendiente' => $pago->monto_pendiente,
                'descuento' => $pago->descuento_aplicado,
            ],
            'metodo_pago' => $pago->metodoPago?->nombre,
            'referencia' => $pago->referencia_pago,
            'estado' => [
                'nombre' => $pago->estado?->nombre,
                'color' => $pago->estado?->color,
            ],
        ]);
    }

    /**
     * Pagos por mes (para gráficos)
     */
    public function porMes()
    {
        $pagos = Pago::selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as año, SUM(monto_abonado) as total, COUNT(*) as cantidad')
            ->where('fecha_pago', '>=', now()->subMonths(12))
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        return response()->json($pagos);
    }

    /**
     * Pagos por método de pago
     */
    public function porMetodo()
    {
        $pagos = Pago::with('metodoPago')
            ->selectRaw('id_metodo_pago, SUM(monto_abonado) as total, COUNT(*) as cantidad')
            ->groupBy('id_metodo_pago')
            ->get()
            ->map(function($pago) {
                return [
                    'metodo' => $pago->metodoPago?->nombre ?? 'Desconocido',
                    'total' => $pago->total,
                    'cantidad' => $pago->cantidad,
                ];
            });

        return response()->json($pagos);
    }

    /**
     * Pagos pendientes
     */
    public function pendientes()
    {
        $estadoPendiente = Estado::where('nombre', 'Pendiente')->where('categoria', 'pago')->first();
        
        $pagos = Pago::where('id_estado', $estadoPendiente?->id ?? 301)
            ->with(['inscripcion.cliente', 'metodoPago'])
            ->get()
            ->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'cliente' => $pago->inscripcion->cliente->nombres . ' ' . $pago->inscripcion->cliente->apellido_paterno,
                    'monto_pendiente' => $pago->monto_pendiente,
                    'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
                ];
            });

        return response()->json($pagos);
    }
}
