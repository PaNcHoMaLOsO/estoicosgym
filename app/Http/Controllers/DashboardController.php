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
        // ========== OBTENER ESTADOS POR NOMBRE (ROBUSTO) ==========
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $idEstadoActiva = $estadoActiva?->id;

        // ========== KPIs PRINCIPALES ==========
        // Total Clientes
        $totalClientes = Cliente::where('activo', true)->count();
        
        // Inscripciones Activas
        $inscripcionesActivas = $idEstadoActiva ? Inscripcion::where('id_estado', $idEstadoActiva)->count() : 0;
        
        // Ingresos del Mes (suma de pagos completados de este mes)
        $ingresosMes = Pago::whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto_abonado');
        
        // Por Vencer en próximos 7 días
        $porVencer7Dias = $idEstadoActiva ? Inscripcion::where('id_estado', $idEstadoActiva)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->count() : 0;

        // ========== GRÁFICO DONA: Distribución de Membresías ==========
        // SOLO inscripciones activas
        $membresiasDistribucion = $idEstadoActiva 
            ? Inscripcion::where('id_estado', $idEstadoActiva)
                ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
                ->select('membresias.nombre', DB::raw('count(*) as total'))
                ->groupBy('membresias.id', 'membresias.nombre')
                ->pluck('total', 'nombre')
            : collect([]);
        
        $etiquetasMembresias = $membresiasDistribucion->keys()->toArray();
        $datosMembresias = $membresiasDistribucion->values()->map(fn($v) => (int)$v)->toArray();

        // ========== GRÁFICO BARRAS: Ingresos Últimos 6 Meses ==========
        $ingresos6Meses = Pago::where('fecha_pago', '>=', now()->subMonths(5))
            ->selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as anio, SUM(monto_abonado) as total')
            ->groupByRaw('YEAR(fecha_pago), MONTH(fecha_pago)')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();
        
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $etiquetasIngresos = $ingresos6Meses->map(function($d) use ($meses) {
            $mesNombre = ($d->mes >= 1 && $d->mes <= 12) ? $meses[$d->mes - 1] : 'Desconocido';
            return $mesNombre . ' ' . $d->anio;
        })->toArray();
        $datosIngresosBarras = $ingresos6Meses->map(fn($d) => (float)$d->total)->toArray();

        // ========== TABLA: Clientes por Vencer (próximos 7 días) ==========
        $clientesPorVencer = $idEstadoActiva 
            ? Inscripcion::with(['cliente', 'membresia', 'estado'])
                ->where('id_estado', $idEstadoActiva)
                ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
                ->orderBy('fecha_vencimiento')
                ->limit(10)
                ->get()
            : collect([]);

        // ========== TABLA: Top Membresías ==========
        $topMembresias = $idEstadoActiva 
            ? Inscripcion::where('id_estado', $idEstadoActiva)
                ->select('id_membresia', DB::raw('count(*) as total'))
                ->groupBy('id_membresia')
                ->with('membresia')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
            : collect([]);
        
        $maxMembresias = $topMembresias->max('total') ?? 1;

        // ========== TABLA: Últimos Pagos ==========
        $ultimosPagos = Pago::with(['inscripcion.cliente', 'metodoPago', 'estado'])
            ->orderByDesc('fecha_pago')
            ->limit(8)
            ->get();

        // ========== TABLA: Inscripciones Recientes ==========
        $inscripcionesRecientes = Inscripcion::with(['cliente', 'membresia', 'estado'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // ========== MÉTODOS DE PAGO (PAGADOS) ==========
        $estadoPagado = Estado::where('nombre', 'Pagado')->first();
        $metodosPagoPopulares = $estadoPagado
            ? Pago::where('id_estado', $estadoPagado->id)
                ->select('id_metodo_pago', DB::raw('count(*) as total'))
                ->groupBy('id_metodo_pago')
                ->with('metodoPago')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
            : collect([]);

        $etiquetasMetodosPago = $metodosPagoPopulares->pluck('metodoPago.nombre')->toArray();
        $datosMetodosPago = $metodosPagoPopulares->pluck('total')->map(fn($v) => (int)$v)->toArray();

        // ========== RESUMEN DE ESTADOS DE INSCRIPCIONES ==========
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $estadoPausada = Estado::where('nombre', 'Pausada')->first();
        $estadoVencida = Estado::where('nombre', 'Vencida')->first();
        $estadoCancelada = Estado::where('nombre', 'Cancelada')->first();
        $estadoSuspendida = Estado::where('nombre', 'Suspendida')->first();
        
        $inscripcionesActivas = $estadoActiva ? Inscripcion::where('id_estado', $estadoActiva->id)->count() : 0;
        $inscripcionesPausadas = $estadoPausada ? Inscripcion::where('id_estado', $estadoPausada->id)->count() : 0;
        $inscripcionesVencidas = $estadoVencida ? Inscripcion::where('id_estado', $estadoVencida->id)->count() : 0;
        $inscripcionesCanceladas = $estadoCancelada ? Inscripcion::where('id_estado', $estadoCancelada->id)->count() : 0;
        $inscripcionesSuspendidas = $estadoSuspendida ? Inscripcion::where('id_estado', $estadoSuspendida->id)->count() : 0;

        // ========== TASA DE CONVERSIÓN MENSUAL ==========
        $totalInscripcionesEsteMes = Inscripcion::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $tasaConversion = $inscripcionesActivas > 0 ? ($totalInscripcionesEsteMes / $inscripcionesActivas) * 100 : 0;

        // ========== NUEVAS MÉTRICAS CRÍTICAS ==========
        
        // 1. Ingresos mes anterior
        $ingresosMesAnterior = Pago::whereYear('fecha_pago', now()->subMonth()->year)
            ->whereMonth('fecha_pago', now()->subMonth()->month)
            ->sum('monto_abonado');
        $variacionIngresos = $ingresosMesAnterior > 0 ? (($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100 : 0;
        
        // 2. Clientes nuevos mes anterior
        $clientesNuevosAnterior = Inscripcion::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $variacionClientes = $clientesNuevosAnterior > 0 ? (($totalInscripcionesEsteMes - $clientesNuevosAnterior) / $clientesNuevosAnterior) * 100 : 0;
        
        // 3. Ticket promedio (monto promedio por pago)
        $ticketPromedio = Pago::avg('monto_abonado') ?? 0;
        
        // 4. Tasa de cobranza (pagos completados vs pendientes)
        $totalPagos = Pago::count();
        $pagosPendientes = Estado::where('nombre', 'Pendiente')->first();
        $countPagosPendientes = $pagosPendientes ? Pago::where('id_estado', $pagosPendientes->id)->count() : 0;
        $tasaCobranza = $totalPagos > 0 ? (($totalPagos - $countPagosPendientes) / $totalPagos) * 100 : 0;
        
        // 5. Membresía con más ingresos (no solo cantidad, sino dinero real)
        $membresiasIngresos = Pago::with(['inscripcion.membresia'])
            ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
            ->select('inscripciones.id_membresia', 'membresias.nombre', DB::raw('SUM(pagos.monto_abonado) as totalIngresos'))
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->groupBy('inscripciones.id_membresia', 'membresias.nombre')
            ->orderByDesc('totalIngresos')
            ->take(3)
            ->get();
        
        // 6. Pagos vencidos (estado = Vencido, no membresías vencidas)
        $estadoVencidoPago = Estado::where('nombre', 'Vencido')->first();
        $pagosVencidos = $estadoVencidoPago ? Pago::where('id_estado', $estadoVencidoPago->id)->count() : 0;
        $montoPagosVencidos = $estadoVencidoPago ? Pago::where('id_estado', $estadoVencidoPago->id)->sum('monto_abonado') : 0;

        return view('dashboard.index', compact(
            'totalClientes',
            'inscripcionesActivas',
            'ingresosMes',
            'ingresosMesAnterior',
            'variacionIngresos',
            'porVencer7Dias',
            'etiquetasMembresias',
            'datosMembresias',
            'etiquetasIngresos',
            'datosIngresosBarras',
            'clientesPorVencer',
            'topMembresias',
            'maxMembresias',
            'ultimosPagos',
            'inscripcionesRecientes',
            'etiquetasMetodosPago',
            'datosMetodosPago',
            'inscripcionesActivas',
            'inscripcionesPausadas',
            'inscripcionesVencidas',
            'inscripcionesCanceladas',
            'inscripcionesSuspendidas',
            'totalInscripcionesEsteMes',
            'clientesNuevosAnterior',
            'variacionClientes',
            'tasaConversion',
            'ticketPromedio',
            'tasaCobranza',
            'membresiasIngresos',
            'pagosVencidos',
            'montoPagosVencidos'
        ));
    }
}

