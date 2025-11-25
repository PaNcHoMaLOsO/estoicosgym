<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;
use App\Models\MetodoPago;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hoy = Carbon::now();
        
        // Obtener IDs de estados
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $idEstadoActiva = $estadoActiva ? $estadoActiva->id : 1;
        
        // Estadísticas principales
        $totalClientes = Cliente::where('activo', true)->count();
        $clientesInactivos = Cliente::where('activo', false)->count();
        $totalInscripciones = Inscripcion::where('id_estado', $idEstadoActiva)->count();
        $inscripcionesVencidas = Inscripcion::where('id_estado', Estado::where('nombre', 'Vencida')->first()?->id ?? 202)->count();
        
        $pagosDelMes = Pago::whereYear('fecha_pago', $hoy->year)
            ->whereMonth('fecha_pago', $hoy->month)
            ->sum('monto_abonado');
        $ingresosTotales = Pago::sum('monto_abonado');
        $pagosPendientes = Pago::sum('monto_pendiente');
        
        // Últimos pagos
        $ultimosPagos = Pago::with('inscripcion.cliente', 'metodoPago', 'estado')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Inscripciones recientes
        $inscripcionesRecientes = Inscripcion::with('cliente', 'membresia', 'estado')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        
        // Inscripciones próximas a vencer
        $proximasAVencer = Inscripcion::where('id_estado', $idEstadoActiva)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->with(['cliente', 'membresia'])
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();
        
        // Membresías más vendidas
        $membresiasVendidas = Membresia::withCount('inscripciones')
            ->orderBy('inscripciones_count', 'desc')
            ->limit(5)
            ->get();
        
        // Máximo de inscripciones para graficar
        $maxInscripciones = max(1, $membresiasVendidas->max('inscripciones_count') ?? 1);
        
        // Métodos de pago más usados
        $metodosPago = MetodoPago::withCount('pagos')
            ->orderBy('pagos_count', 'desc')
            ->get();
        
        // Inscripciones por estado (contar inscripciones agrupadas por estado de MEMBRESÍA)
        $inscripcionesPorEstado = Inscripcion::selectRaw('id_estado, count(*) as total')
            ->groupBy('id_estado')
            ->with(['estado' => function($q) {
                $q->where('categoria', 'membresia');
            }])
            ->whereHas('estado', function($q) {
                $q->where('categoria', 'membresia');
            })
            ->get();
        
        // Filtrar solo los que tengan estado (evitar nulos)
        $inscripcionesPorEstado = $inscripcionesPorEstado->filter(fn($item) => $item->estado !== null);
        
        // Datos para gráficos - Ingresos por mes (últimos 6 meses)
        $ingresosPorMes = Pago::selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as año, SUM(monto_abonado) as total')
            ->where('fecha_pago', '>=', now()->subMonths(6))
            ->groupByRaw('YEAR(fecha_pago), MONTH(fecha_pago)')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();
        
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $etiquetasMeses = $ingresosPorMes->count() > 0 ? $ingresosPorMes->map(function($d) use ($meses) {
            return ($d->mes && $d->mes >= 1 && $d->mes <= 12 ? $meses[$d->mes - 1] : 'Desconocido') . ' ' . $d->año;
        })->toArray() : [];
        $datosIngresos = $ingresosPorMes->count() > 0 ? $ingresosPorMes->map(fn($d) => (float)$d->total)->toArray() : [];
        
        // Datos para gráfico de estados
        $coloresEstados = [
            'success' => '#28a745',
            'danger' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#17a2b8',
            'primary' => '#007bff',
            'secondary' => '#6c757d',
        ];
        
        $etiquetasEstados = $inscripcionesPorEstado->count() > 0 ? $inscripcionesPorEstado->map(fn($d) => $d->estado?->nombre ?? 'Desconocido')->toArray() : [];
        $datosEstados = $inscripcionesPorEstado->count() > 0 ? $inscripcionesPorEstado->map(fn($d) => (int)$d->total)->toArray() : [];
        $coloresDispuestos = $inscripcionesPorEstado->count() > 0 ? $inscripcionesPorEstado->map(fn($d) => $coloresEstados[$d->estado?->color ?? 'secondary'])->toArray() : [];
        
        return view('dashboard.index', compact(
            'totalClientes',
            'clientesInactivos',
            'totalInscripciones',
            'inscripcionesVencidas',
            'pagosDelMes',
            'ingresosTotales',
            'pagosPendientes',
            'ultimosPagos',
            'inscripcionesRecientes',
            'proximasAVencer',
            'membresiasVendidas',
            'metodosPago',
            'inscripcionesPorEstado',
            'etiquetasMeses',
            'datosIngresos',
            'etiquetasEstados',
            'datosEstados',
            'coloresDispuestos'
        ));
    }
}

