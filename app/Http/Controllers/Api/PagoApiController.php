<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Estado;
use Illuminate\Http\Request;

class InscripcionApiController extends Controller
{
    /**
     * Listar todas las inscripciones
     */
    public function index()
    {
        $inscripciones = Inscripcion::with(['cliente', 'membresia', 'estado', 'pagos'])
            ->latest()
            ->get()
            ->map(function($ins) {
                return [
                    'id' => $ins->id,
                    'cliente' => $ins->cliente->nombres . ' ' . $ins->cliente->apellido_paterno,
                    'membresia' => $ins->membresia?->nombre,
                    'estado' => $ins->estado?->nombre,
                    'estado_color' => $ins->estado?->color,
                    'fecha_inicio' => $ins->fecha_inicio->format('Y-m-d'),
                    'fecha_vencimiento' => $ins->fecha_vencimiento->format('Y-m-d'),
                    'dias_restantes' => now()->diffInDays($ins->fecha_vencimiento, false),
                    'precio_final' => $ins->precio_final,
                    'total_pagado' => $ins->pagos->sum('monto_abonado'),
                ];
            });

        return response()->json($inscripciones);
    }

    /**
     * Obtener inscripción específica
     */
    public function show($id)
    {
        $inscripcion = Inscripcion::with(['cliente', 'membresia', 'estado', 'pagos.estado', 'convenio'])
            ->findOrFail($id);

        return response()->json([
            'id' => $inscripcion->id,
            'cliente' => [
                'id' => $inscripcion->cliente->id,
                'nombre' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                'email' => $inscripcion->cliente->email,
                'celular' => $inscripcion->cliente->celular,
            ],
            'membresia' => $inscripcion->membresia?->nombre,
            'convenio' => $inscripcion->convenio?->nombre,
            'estado' => [
                'nombre' => $inscripcion->estado?->nombre,
                'color' => $inscripcion->estado?->color,
            ],
            'fechas' => [
                'inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
                'vencimiento' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                'dias_restantes' => now()->diffInDays($inscripcion->fecha_vencimiento, false),
            ],
            'precios' => [
                'base' => $inscripcion->precio_base,
                'descuento' => $inscripcion->descuento_aplicado,
                'final' => $inscripcion->precio_final,
            ],
            'pagos' => $inscripcion->pagos->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'monto' => $pago->monto_abonado,
                    'fecha' => $pago->fecha_pago->format('Y-m-d'),
                    'estado' => $pago->estado?->nombre,
                    'metodo' => $pago->metodoPago?->nombre,
                ];
            }),
            'resumen' => [
                'total_pagado' => $inscripcion->pagos->sum('monto_abonado'),
                'pendiente' => $inscripcion->precio_final - $inscripcion->pagos->sum('monto_abonado'),
                'cantidad_pagos' => $inscripcion->pagos->count(),
            ],
        ]);
    }

    /**
     * Obtener inscripciones por estado
     */
    public function porEstado($estado)
    {
        $estadoObj = Estado::where('nombre', $estado)->first();
        
        if (!$estadoObj) {
            return response()->json(['error' => 'Estado no encontrado'], 404);
        }

        $inscripciones = Inscripcion::where('id_estado', $estadoObj->id)
            ->with(['cliente', 'membresia', 'estado'])
            ->get()
            ->map(function($ins) {
                return [
                    'id' => $ins->id,
                    'cliente' => $ins->cliente->nombres . ' ' . $ins->cliente->apellido_paterno,
                    'membresia' => $ins->membresia?->nombre,
                    'fecha_inicio' => $ins->fecha_inicio->format('Y-m-d'),
                    'fecha_vencimiento' => $ins->fecha_vencimiento->format('Y-m-d'),
                ];
            });

        return response()->json($inscripciones);
    }

    /**
     * Inscripciones próximas a vencer
     */
    public function proximasAVencer()
    {
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        
        $inscripciones = Inscripcion::where('id_estado', $estadoActiva?->id ?? 1)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->with(['cliente', 'membresia', 'estado'])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function($ins) {
                return [
                    'id' => $ins->id,
                    'cliente' => $ins->cliente->nombres . ' ' . $ins->cliente->apellido_paterno,
                    'membresia' => $ins->membresia?->nombre,
                    'fecha_vencimiento' => $ins->fecha_vencimiento->format('Y-m-d'),
                    'dias_restantes' => now()->diffInDays($ins->fecha_vencimiento),
                ];
            });

        return response()->json($inscripciones);
    }
}
