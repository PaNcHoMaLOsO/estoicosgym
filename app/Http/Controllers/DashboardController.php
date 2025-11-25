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
        
        // Obtener ID del estado "Activa"
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $idEstadoActiva = $estadoActiva ? $estadoActiva->id : 1;
        
        // Estadísticas principales
        $totalClientes = Cliente::where('activo', true)->count();
        $totalInscripciones = Inscripcion::where('id_estado', $idEstadoActiva)->count();
        $pagosDelMes = Pago::whereYear('created_at', $hoy->year)
            ->whereMonth('created_at', $hoy->month)
            ->sum('monto_abonado');
        $ingresosTotales = Pago::sum('monto_abonado');
        
        // Últimos pagos
        $ultimosPagos = Pago::with('inscripcion.cliente', 'metodoPago')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Inscripciones recientes
        $inscripcionesRecientes = Inscripcion::with('cliente', 'membresia', 'estado')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Membresías más vendidas
        $membresiasVendidas = Membresia::withCount('inscripciones')
            ->orderBy('inscripciones_count', 'desc')
            ->limit(5)
            ->get();
        
        // Métodos de pago más usados
        $metodosPago = MetodoPago::withCount('pagos')
            ->orderBy('pagos_count', 'desc')
            ->get();
        
        // Inscripciones por estado (contar inscripciones agrupadas por estado)
        $inscripcionesPorEstado = Inscripcion::selectRaw('id_estado, count(*) as total')
            ->groupBy('id_estado')
            ->with('estado')
            ->get();
        
        return view('dashboard.index', compact(
            'totalClientes',
            'totalInscripciones',
            'pagosDelMes',
            'ingresosTotales',
            'ultimosPagos',
            'inscripcionesRecientes',
            'membresiasVendidas',
            'metodosPago',
            'inscripcionesPorEstado'
        ));
    }
}

