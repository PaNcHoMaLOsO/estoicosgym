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
        $estadoVencida = Estado::where('nombre', 'Vencida')->first();
        $idEstadoVencida = $estadoVencida ? $estadoVencida->id : 202;
        
        // KPIs Principales - Clientes
        $totalClientes = Cliente::where('activo', true)->count();
        $clientesInactivos = Cliente::where('activo', false)->count();
        
        // KPIs Principales - Inscripciones
        $totalInscripciones = Inscripcion::where('id_estado', $idEstadoActiva)->count();
        $inscripcionesVencidas = Inscripcion::where('id_estado', $idEstadoVencida)->count();
        
        // KPIs Principales - Ingresos
        $pagosDelMes = Pago::whereYear('fecha_pago', $hoy->year)
            ->whereMonth('fecha_pago', $hoy->month)
            ->sum('monto_abonado');
        $ingresosTotales = Pago::sum('monto_abonado');
        $estadoPagado = Estado::where('codigo', 201)->first();
        $pagosPendientes = Pago::where('id_estado', '!=', $estadoPagado->id)->sum('monto_abonado');
        
        // Últimos pagos con relaciones eager loaded
        $ultimosPagos = Pago::with(['inscripcion' => function($q) {
            $q->with('cliente');
        }, 'metodoPagoPrincipal', 'estado'])
            ->orderBy('fecha_pago', 'desc')
            ->limit(8)
            ->get();
        
        // Inscripciones recientes
        $inscripcionesRecientes = Inscripcion::with('cliente', 'membresia', 'estado')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
        
        // Inscripciones próximas a vencer (30 días)
        $proximasAVencer = Inscripcion::where('id_estado', $idEstadoActiva)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->with(['cliente', 'membresia'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->limit(8)
            ->get();
        
        // Membresías más vendidas
        $membresiasVendidas = Membresia::withCount('inscripciones')
            ->orderBy('inscripciones_count', 'desc')
            ->limit(5)
            ->get();
        
        // Métodos de pago más usados
        $metodosPago = MetodoPago::withCount('pagos')
            ->orderBy('pagos_count', 'desc')
            ->limit(5)
            ->get();
        
        // Inscripciones por estado - Gráfico de estados
        $inscripcionesPorEstado = Inscripcion::selectRaw('id_estado, count(*) as total')
            ->groupBy('id_estado')
            ->with('estado')
            ->get()
            ->filter(fn($item) => $item->estado !== null);
        
        // Datos para gráficos - Ingresos por mes (últimos 6 meses)
        $ingresosPorMes = Pago::selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as año, SUM(monto_abonado) as total')
            ->where('fecha_pago', '>=', now()->subMonths(5))
            ->groupByRaw('YEAR(fecha_pago), MONTH(fecha_pago)')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'asc')
            ->get();
        
        // Generar etiquetas y datos para gráfico de ingresos
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $etiquetasMeses = $ingresosPorMes->map(function($d) use ($meses) {
            return ($d->mes && $d->mes >= 1 && $d->mes <= 12 ? $meses[$d->mes - 1] : 'Desconocido') . ' ' . $d->año;
        })->toArray();
        $datosIngresos = $ingresosPorMes->map(fn($d) => (float)$d->total)->toArray();
        
        // Colores para estados
        $coloresEstados = [
            'primary' => '#007bff',    // Activa
            'success' => '#28a745',    // Completada
            'warning' => '#ffc107',    // Pausada
            'danger' => '#dc3545',     // Cancelada/Vencida
            'info' => '#17a2b8',       // Pendiente
            'secondary' => '#6c757d',  // Otros
        ];
        
        // Datos para gráfico de estados
        $etiquetasEstados = $inscripcionesPorEstado->map(fn($d) => $d->estado->nombre)->toArray();
        $datosEstados = $inscripcionesPorEstado->map(fn($d) => (int)$d->total)->toArray();
        $coloresDispuestos = $inscripcionesPorEstado->map(fn($d) => $coloresEstados[$d->estado->color ?? 'secondary'])->toArray();
        
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

