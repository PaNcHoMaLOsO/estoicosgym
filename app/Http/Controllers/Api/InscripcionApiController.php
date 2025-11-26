<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use App\Models\Convenio;
use App\Models\PrecioMembresia;
use Illuminate\Http\Request;

class InscripcionApiController extends Controller
{
    /**
     * Obtener información de una membresía (precio y duración)
     * GET /api/membresias/{id}
     */
    public function showMembresia($id)
    {
        $membresia = Membresia::find($id);
        
        if (!$membresia) {
            return response()->json(['error' => 'Membresía no encontrada'], 404);
        }

        // Obtener precio actual vigente
        $precioActual = $membresia->precios()
            ->where(function($q) {
                $q->where('activo', true)
                    ->orWhere('fecha_vigencia_desde', '<=', now());
            })
            ->orderBy('fecha_vigencia_desde', 'desc')
            ->first();

        return response()->json([
            'id' => $membresia->id,
            'nombre' => $membresia->nombre,
            'duracion_meses' => $membresia->duracion_meses,
            'duracion_dias' => $membresia->duracion_dias,
            'precio_normal' => $precioActual?->precio_normal ?? 0,
            'id_precio' => $precioActual?->id ?? null,
        ]);
    }

    /**
     * Obtener descuento por convenio
     * GET /api/convenios/{id}/descuento
     */
    public function getConvenioDescuento($id)
    {
        $convenio = Convenio::find($id);
        
        if (!$convenio) {
            return response()->json(['error' => 'Convenio no encontrado'], 404);
        }

        // Los descuentos ahora se definen en la tabla precios_membresias (precio_convenio)
        // Este endpoint devuelve info del convenio únicamente
        return response()->json([
            'id' => $convenio->id,
            'nombre' => $convenio->nombre,
            'tipo' => $convenio->tipo,
            'descripcion' => $convenio->descripcion,
        ]);
    }

    /**
     * Calcular precio final y fecha vencimiento
     * POST /api/inscripciones/calcular
     */
    public function calcular(Request $request)
    {
        $validated = $request->validate([
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $fechaInicio = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['fecha_inicio']);
        $membresia = Membresia::find($validated['id_membresia']);
        
        // Calcular fecha de vencimiento usando duracion_dias si está disponible
        if ($membresia->duracion_dias && $membresia->duracion_dias > 0) {
            // Para Pase Diario y otros con duracion_dias: usar directamente
            $fechaVencimiento = $fechaInicio->clone()
                ->addDays($membresia->duracion_dias)
                ->subDay();
        } else {
            // Para membresías por meses: sumar meses y restar 1 día
            $duracionMeses = $membresia->duracion_meses ?? 1;
            $fechaVencimiento = $fechaInicio->clone()
                ->addMonths($duracionMeses)
                ->subDay();
        }

        // Calcular descuento (ahora soporta descuento automático de convenio para Mensual)
        $descuento = 0;
        if ($validated['id_convenio'] && $membresia->id === 4) {
            // Solo la membresía Mensual (id=4) tiene descuento por convenio
            $precioMembresia = $membresia->precios()
                ->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderBy('fecha_vigencia_desde', 'desc')
                ->first();

            if ($precioMembresia && $precioMembresia->precio_convenio) {
                $descuento = $validated['precio_base'] - $precioMembresia->precio_convenio;
            }
        }

        $precioFinal = $validated['precio_base'] - $descuento;

        return response()->json([
            'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
            'descuento_aplicado' => $descuento,
            'precio_final' => max(0, $precioFinal),
        ]);
    }
}
