<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;
use App\Models\Membresia;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * Obtener estadísticas principales
     */
    public function stats()
    {
        $hoy = Carbon::now();
        
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $idEstadoActiva = $estadoActiva ? $estadoActiva->id : 1;
        
        $totalClientes = Cliente::where('activo', true)->count();
        $totalInscripciones = Inscripcion::where('id_estado', $idEstadoActiva)->count();
        $pagosDelMes = Pago::whereYear('created_at', $hoy->year)
            ->whereMonth('created_at', $hoy->month)
            ->sum('monto_abonado');
        $ingresosTotales = Pago::sum('monto_abonado');
        $pagosVencidos = Pago::where('id_estado', Estado::where('nombre', 'Vencido')->where('categoria', 'pago')->first()?->id ?? 304)->count();
        $inscripcionesVencidas = Inscripcion::where('id_estado', Estado::where('nombre', 'Vencida')->first()?->id ?? 202)->count();

        return response()->json([
            'clientes' => [
                'total' => $totalClientes,
                'activos' => $totalClientes,
                'inactivos' => Cliente::where('activo', false)->count(),
            ],
            'inscripciones' => [
                'total' => Inscripcion::count(),
                'activas' => $totalInscripciones,
                'vencidas' => $inscripcionesVencidas,
                'pausadas' => Inscripcion::where('id_estado', Estado::where('nombre', 'Pausada')->first()?->id ?? 203)->count(),
            ],
            'pagos' => [
                'mes_actual' => $pagosDelMes,
                'total' => $ingresosTotales,
                'vencidos' => $pagosVencidos,
                'cantidad_pagos' => Pago::count(),
                'promedio_pago' => Pago::count() > 0 ? $ingresosTotales / Pago::count() : 0,
            ],
        ]);
    }

    /**
     * Ingresos por mes (últimos 12 meses)
     */
    public function ingresosPorMes()
    {
        $datos = Pago::selectRaw('MONTH(fecha_pago) as mes, YEAR(fecha_pago) as año, SUM(monto_abonado) as total')
            ->where('fecha_pago', '>=', now()->subMonths(12))
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        return response()->json([
            'labels' => $datos->map(fn($d) => $meses[$d->mes - 1] . ' ' . $d->año)->toArray(),
            'data' => $datos->map(fn($d) => (float)$d->total)->toArray(),
        ]);
    }

    /**
     * Inscripciones por estado
     */
    public function inscripcionesPorEstado()
    {
        $datos = Inscripcion::selectRaw('id_estado, count(*) as total')
            ->groupBy('id_estado')
            ->with('estado')
            ->get();

        return response()->json([
            'labels' => $datos->map(fn($d) => $d->estado?->nombre ?? 'Desconocido')->toArray(),
            'data' => $datos->map(fn($d) => (int)$d->total)->toArray(),
            'colors' => $datos->map(fn($d) => match($d->estado?->color ?? 'secondary') {
                'success' => '#28a745',
                'danger' => '#dc3545',
                'warning' => '#ffc107',
                'info' => '#17a2b8',
                'primary' => '#007bff',
                'secondary' => '#6c757d',
            })->toArray(),
        ]);
    }

    /**
     * Membresías más vendidas
     */
    public function membresiasPopulares()
    {
        $datos = Membresia::withCount('inscripciones')
            ->orderBy('inscripciones_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json($datos->map(function($m) {
            return [
                'nombre' => $m->nombre,
                'inscripciones' => $m->inscripciones_count,
                'duracion_dias' => $m->duracion_dias,
            ];
        }));
    }

    /**
     * Métodos de pago más usados
     */
    public function metodosPagoPopulares()
    {
        $datos = Pago::selectRaw('id_metodo_pago_principal, COUNT(*) as total, SUM(monto_abonado) as monto')
            ->groupBy('id_metodo_pago_principal')
            ->with('metodoPagoPrincipal')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json($datos->map(function($p) {
            return [
                'metodo' => $p->metodoPagoPrincipal?->nombre ?? 'Desconocido',
                'cantidad' => $p->total,
                'monto_total' => $p->monto,
            ];
        }));
    }

    /**
     * Últimos pagos
     */
    public function ultimosPagos($limit = 10)
    {
        $pagos = Pago::with(['inscripcion.cliente', 'metodoPago', 'estado'])
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json($pagos->map(function($p) {
            return [
                'id' => $p->id,
                'cliente' => $p->inscripcion->cliente->nombres . ' ' . $p->inscripcion->cliente->apellido_paterno,
                'monto' => $p->monto_abonado,
                'fecha' => \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y H:i'),
                'metodo' => $p->metodoPago?->nombre,
                'estado' => $p->estado?->nombre,
                'estado_color' => $p->estado?->color,
            ];
        }));
    }

    /**
     * Inscripciones próximas a vencer
     */
    public function proximasAVencer()
    {
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        
        $inscripciones = Inscripcion::where('id_estado', $estadoActiva?->id ?? 1)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->with(['cliente', 'membresia'])
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        return response()->json($inscripciones->map(function($i) {
            $dias = now()->diffInDays(\Carbon\Carbon::parse($i->fecha_vencimiento));
            return [
                'id' => $i->id,
                'cliente' => $i->cliente->nombres . ' ' . $i->cliente->apellido_paterno,
                'membresia' => $i->membresia?->nombre,
                'vencimiento' => \Carbon\Carbon::parse($i->fecha_vencimiento)->format('d/m/Y'),
                'dias_restantes' => $dias,
                'urgencia' => $dias <= 7 ? 'alta' : ($dias <= 14 ? 'media' : 'baja'),
            ];
        }));
    }

    /**
     * Resumen de clientes
     */
    public function resumenClientes()
    {
        return response()->json([
            'total' => Cliente::count(),
            'activos' => Cliente::where('activo', true)->count(),
            'inactivos' => Cliente::where('activo', false)->count(),
            'con_inscripcion' => Cliente::whereHas('inscripciones')->count(),
            'sin_inscripcion' => Cliente::whereDoesntHave('inscripciones')->count(),
            'por_convenio' => Cliente::where('id_convenio', '!=', null)->count(),
        ]);
    }
}
