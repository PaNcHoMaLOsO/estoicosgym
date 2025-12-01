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
        // ========== CÓDIGOS DE ESTADOS (usando 'codigo', NO 'id') ==========
        // La relación en Inscripcion/Pago usa 'codigo' como foreign key
        // Membresías: 100=Activa, 101=Pausada, 102=Vencida, 103=Cancelada, 104=Suspendida, 105=Cambiada, 106=Traspasada
        // Pagos: 200=Pendiente, 201=Pagado, 202=Parcial, 203=Vencido, 204=Cancelado
        
        $codigoActiva = 100;
        $codigoPausada = 101;
        $codigoVencida = 102;
        $codigoCancelada = 103;
        $codigoSuspendida = 104;
        
        $codigoPagoPendiente = 200;
        $codigoPagoPagado = 201;
        $codigoPagoParcial = 202;
        $codigoPagoVencido = 203;

        // ========== KPIs PRINCIPALES ==========
        
        // Total Clientes Activos
        $totalClientes = Cliente::where('activo', true)->count();
        
        // Inscripciones por Estado (usando CÓDIGO, no ID)
        $inscripcionesActivas = Inscripcion::where('id_estado', $codigoActiva)->count();
        $inscripcionesPausadas = Inscripcion::where('id_estado', $codigoPausada)->count();
        $inscripcionesVencidas = Inscripcion::where('id_estado', $codigoVencida)->count();
        $inscripcionesCanceladas = Inscripcion::where('id_estado', $codigoCancelada)->count();
        $inscripcionesSuspendidas = Inscripcion::where('id_estado', $codigoSuspendida)->count();
        
        // Ingresos del Mes (solo pagos completados o parciales)
        $ingresosMes = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto_abonado');
        
        // Ingresos Mes Anterior
        $ingresosMesAnterior = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereYear('fecha_pago', now()->subMonth()->year)
            ->whereMonth('fecha_pago', now()->subMonth()->month)
            ->sum('monto_abonado');
        
        // Variación de Ingresos
        $variacionIngresos = $ingresosMesAnterior > 0 
            ? (($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100 
            : ($ingresosMes > 0 ? 100 : 0);
        
        // Por Vencer en próximos 7 días (solo inscripciones activas)
        $porVencer7Dias = Inscripcion::where('id_estado', $codigoActiva)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->count();

        // ========== NUEVAS INSCRIPCIONES ==========
        
        $totalInscripcionesEsteMes = Inscripcion::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $clientesNuevosAnterior = Inscripcion::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $variacionClientes = $clientesNuevosAnterior > 0 
            ? (($totalInscripcionesEsteMes - $clientesNuevosAnterior) / $clientesNuevosAnterior) * 100 
            : ($totalInscripcionesEsteMes > 0 ? 100 : 0);

        // ========== ALERTAS DE PAGOS ==========
        
        // Pagos Pendientes
        $countPagosPendientes = Pago::where('id_estado', $codigoPagoPendiente)->count();
        $montoPagosPendientes = Pago::where('id_estado', $codigoPagoPendiente)->sum('monto_pendiente');
        
        // Pagos Vencidos
        $pagosVencidos = Pago::where('id_estado', $codigoPagoVencido)->count();
        $montoPagosVencidos = Pago::where('id_estado', $codigoPagoVencido)->sum('monto_pendiente');

        // ========== MÉTRICAS DE RENDIMIENTO ==========
        
        // Ticket Promedio (solo pagos completados)
        $ticketPromedio = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->avg('monto_abonado') ?? 0;
        
        // Tasa de Cobranza
        $totalPagosMes = Pago::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $pagosCobradosMes = Pago::where('id_estado', $codigoPagoPagado)
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->count();
        $tasaCobranza = $totalPagosMes > 0 ? ($pagosCobradosMes / $totalPagosMes) * 100 : 0;
        
        // Tasa de Retención
        $totalParaRetencion = $inscripcionesActivas + $inscripcionesVencidas + $inscripcionesCanceladas;
        $tasaRetencion = $totalParaRetencion > 0 ? ($inscripcionesActivas / $totalParaRetencion) * 100 : 0;
        
        // Tasa de Conversión
        $tasaConversion = $inscripcionesActivas > 0 
            ? ($totalInscripcionesEsteMes / $inscripcionesActivas) * 100 
            : 0;

        // ========== GRÁFICO DONA: Distribución de Membresías ==========
        
        $membresiasDistribucion = Inscripcion::where('id_estado', $codigoActiva)
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->select('membresias.nombre', DB::raw('count(*) as total'))
            ->groupBy('membresias.id', 'membresias.nombre')
            ->pluck('total', 'nombre');
        
        $etiquetasMembresias = $membresiasDistribucion->keys()->toArray();
        $datosMembresias = $membresiasDistribucion->values()->map(fn($v) => (int)$v)->toArray();

        // ========== GRÁFICO BARRAS: Ingresos Últimos 6 Meses ==========
        
        $ingresosHistorico = collect();
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $total = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
                ->whereYear('fecha_pago', $fecha->year)
                ->whereMonth('fecha_pago', $fecha->month)
                ->sum('monto_abonado');
            
            $ingresosHistorico->push([
                'mes' => $fecha->locale('es')->isoFormat('MMM YYYY'),
                'total' => (float) $total
            ]);
        }
        
        $etiquetasIngresos = $ingresosHistorico->pluck('mes')->toArray();
        $datosIngresosBarras = $ingresosHistorico->pluck('total')->toArray();

        // ========== TABLA: Clientes por Vencer ==========
        
        $clientesPorVencer = Inscripcion::with(['cliente', 'membresia', 'estado'])
            ->where('id_estado', $codigoActiva)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        // ========== TABLA: Top Membresías ==========
        
        $topMembresias = Inscripcion::where('id_estado', $codigoActiva)
            ->select('id_membresia', DB::raw('count(*) as total'))
            ->groupBy('id_membresia')
            ->with('membresia')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        
        $maxMembresias = $topMembresias->max('total') ?? 1;

        // ========== TABLA: Últimos Pagos ==========
        
        $ultimosPagos = Pago::with(['inscripcion.cliente', 'inscripcion.membresia', 'metodoPago', 'estado'])
            ->whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->orderByDesc('fecha_pago')
            ->limit(8)
            ->get();

        // ========== TABLA: Inscripciones Recientes ==========
        
        $inscripcionesRecientes = Inscripcion::with(['cliente', 'membresia', 'estado'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // ========== MÉTODOS DE PAGO ==========
        
        $metodosPagoPopulares = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->select('id_metodo_pago', DB::raw('count(*) as total'))
            ->groupBy('id_metodo_pago')
            ->with('metodoPago')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $etiquetasMetodosPago = $metodosPagoPopulares->pluck('metodoPago.nombre')->toArray();
        $datosMetodosPago = $metodosPagoPopulares->pluck('total')->map(fn($v) => (int)$v)->toArray();

        // ========== TOP MEMBRESÍAS POR INGRESOS ==========
        
        $membresiasIngresos = DB::table('pagos')
            ->join('inscripciones', 'pagos.id_inscripcion', '=', 'inscripciones.id')
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->whereIn('pagos.id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereYear('pagos.fecha_pago', now()->year)
            ->whereMonth('pagos.fecha_pago', now()->month)
            ->select('membresias.nombre', DB::raw('SUM(pagos.monto_abonado) as totalIngresos'))
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc('totalIngresos')
            ->limit(5)
            ->get();

        // ========== RESUMEN DEL DÍA ==========
        
        $ingresosHoy = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereDate('fecha_pago', today())
            ->sum('monto_abonado');
        
        $inscripcionesHoy = Inscripcion::whereDate('created_at', today())->count();
        
        $pagosHoy = Pago::whereIn('id_estado', [$codigoPagoPagado, $codigoPagoParcial])
            ->whereDate('fecha_pago', today())
            ->count();

        return view('dashboard.index', compact(
            // KPIs principales
            'totalClientes',
            'inscripcionesActivas',
            'ingresosMes',
            'ingresosMesAnterior',
            'variacionIngresos',
            'porVencer7Dias',
            
            // Estados de inscripciones
            'inscripcionesPausadas',
            'inscripcionesVencidas',
            'inscripcionesCanceladas',
            'inscripcionesSuspendidas',
            
            // Inscripciones nuevas
            'totalInscripcionesEsteMes',
            'clientesNuevosAnterior',
            'variacionClientes',
            
            // Pagos
            'countPagosPendientes',
            'montoPagosPendientes',
            'pagosVencidos',
            'montoPagosVencidos',
            
            // Métricas
            'ticketPromedio',
            'tasaCobranza',
            'tasaRetencion',
            'tasaConversion',
            
            // Gráficos
            'etiquetasMembresias',
            'datosMembresias',
            'etiquetasIngresos',
            'datosIngresosBarras',
            'etiquetasMetodosPago',
            'datosMetodosPago',
            
            // Tablas
            'clientesPorVencer',
            'topMembresias',
            'maxMembresias',
            'ultimosPagos',
            'inscripcionesRecientes',
            'membresiasIngresos',
            
            // Resumen del día
            'ingresosHoy',
            'inscripcionesHoy',
            'pagosHoy'
        ));
    }
}

