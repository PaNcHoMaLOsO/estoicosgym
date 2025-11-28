<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;
use App\Models\MetodoPago;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ==== KPIs PRINCIPALES ====
        // Total Clientes
        $totalClientes = Cliente::where('activo', true)->count();
        
        // Inscripciones Activas (estado_id = 100 = Activa)
        $inscripcionesActivas = Inscripcion::where('id_estado', 100)->count();
        
        // Ingresos del Mes (pagos completados)
        $ingresosMes = Pago::whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto_abonado');
        
        // Por Vencer en próximos 7 días
        $porVencer7Dias = Inscripcion::where('id_estado', 100)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->count();
        
        // ==== GRÁFICO DONA: Distribución de Membresías (SOLO inscripciones activas) ====
        $membresiasDistribucion = Inscripcion::where('id_estado', 100)
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->select('membresias.nombre', DB::raw('count(*) as total'))
            ->groupBy('membresias.id', 'membresias.nombre')
            ->pluck('total', 'nombre');
        
        // Preparar datos para Chart.js - Dona
        $etiquetasMembresias = $membresiasDistribucion->keys()->toArray();
        $datosMembresias = $membresiasDistribucion->values()->toArray();
        
        // ==== GRÁFICO BARRAS: Ingresos Últimos 6 Meses ====
        $ingresos6Meses = Pago::where('fecha_pago', '>=', now()->subMonths(5))
            ->selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as anio, SUM(monto_abonado) as total')
            ->groupByRaw('YEAR(fecha_pago), MONTH(fecha_pago)')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();
        
        // Preparar datos para Chart.js - Barras
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $etiquetasIngresos = $ingresos6Meses->map(function($d) use ($meses) {
            $mesNombre = $d->mes && $d->mes >= 1 && $d->mes <= 12 ? $meses[$d->mes - 1] : 'Desconocido';
            return $mesNombre . ' ' . $d->anio;
        })->toArray();
        $datosIngresosBarras = $ingresos6Meses->map(fn($d) => (float)$d->total)->toArray();
        
        // ==== TABLA: Clientes por Vencer (próximos 7 días) ====
        $clientesPorVencer = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();
        
        // ==== TABLA: Top Membresías ====
        $topMembresias = Inscripcion::where('id_estado', 100)
            ->select('id_membresia', DB::raw('count(*) as total'))
            ->groupBy('id_membresia')
            ->with('membresia')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        
        $maxMembresias = $topMembresias->max('total') ?? 1;
        
        // ==== TABLA: Últimos Pagos ====
        $ultimosPagos = Pago::with(['inscripcion.cliente', 'metodoPago', 'estado'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
        
        // ==== TABLA: Inscripciones Recientes ====
        $inscripcionesRecientes = Inscripcion::with(['cliente', 'membresia', 'estado'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
        
        return view('dashboard.index', compact(
            'totalClientes',
            'inscripcionesActivas',
            'ingresosMes',
            'porVencer7Dias',
            'etiquetasMembresias',
            'datosMembresias',
            'etiquetasIngresos',
            'datosIngresosBarras',
            'clientesPorVencer',
            'topMembresias',
            'maxMembresias',
            'ultimosPagos',
            'inscripcionesRecientes'
        ));
    }
}

