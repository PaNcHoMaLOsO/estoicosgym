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
            ->sum('monto');
        $ingresosTotales = Pago::sum('monto');
        
        // Últimos pagos
        $ultimosPagos = Pago::with('inscripcion.cliente', 'metodo_pago')
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
        
        // Clientes por estado
        $clientesPorEstado = Estado::withCount('clientes')
            ->orderBy('clientes_count', 'desc')
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
            'clientesPorEstado'
        ));
    }
}

