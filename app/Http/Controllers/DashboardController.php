<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hoy = Carbon::now();
        
        // Estadísticas generales
        $totalClientes = Cliente::where('activo', true)->count();
        $clientesActivos = Inscripcion::where('id_estado', 201)->distinct('id_cliente')->count();
        
        // Ingresos del mes actual
        $ingresosMesActual = Pago::whereYear('fecha_pago', $hoy->year)
            ->whereMonth('fecha_pago', $hoy->month)
            ->sum('monto_abonado');
        
        // Pagos pendientes
        $pagosPendientes = Pago::whereIn('id_estado', [301, 303])
            ->sum('monto_pendiente');
        
        // Membresías por vencer (próximos 7 días)
        $membresiasProximas = Inscripcion::where('id_estado', 201)
            ->whereBetween('fecha_vencimiento', [
                $hoy->startOfDay(),
                $hoy->addDays(7)->endOfDay()
            ])
            ->with('cliente', 'membresia')
            ->get();
        
        // Pagos recientes
        $pagosRecientes = Pago::latest('fecha_pago')
            ->with('cliente', 'inscripcion.membresia', 'estado')
            ->limit(10)
            ->get();
        
        // Clientes recientes
        $clientesRecientes = Cliente::latest('created_at')
            ->limit(5)
            ->get();
        
        // Gráfico: Ingresos por método de pago (mes actual)
        $ingresosPorMetodo = Pago::whereYear('fecha_pago', $hoy->year)
            ->whereMonth('fecha_pago', $hoy->month)
            ->with('metodoPago')
            ->get()
            ->groupBy('id_metodo_pago')
            ->map(function ($pagos) {
                return [
                    'metodo' => $pagos->first()->metodoPago?->nombre ?? 'N/A',
                    'total' => $pagos->sum('monto_abonado')
                ];
            });
        
        // Membresías más vendidas
        $membresiasVendidas = Inscripcion::whereYear('fecha_inscripcion', $hoy->year)
            ->whereMonth('fecha_inscripcion', $hoy->month)
            ->with('membresia')
            ->get()
            ->groupBy('id_membresia')
            ->map(function ($inscripciones) {
                return [
                    'membresia' => $inscripciones->first()->membresia?->nombre ?? 'N/A',
                    'cantidad' => $inscripciones->count(),
                    'ingresos' => $inscripciones->sum('precio_final')
                ];
            })
            ->sortByDesc('cantidad')
            ->take(5);
        
        return view('dashboard.index', compact(
            'totalClientes',
            'clientesActivos',
            'ingresosMesActual',
            'pagosPendientes',
            'membresiasProximas',
            'pagosRecientes',
            'clientesRecientes',
            'ingresosPorMetodo',
            'membresiasVendidas'
        ));
    }
}
