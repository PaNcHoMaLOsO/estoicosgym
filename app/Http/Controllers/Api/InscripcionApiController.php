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
        $precioActual = PrecioMembresia::where('id_membresia', $id)
            ->where('vigencia_desde', '<=', now())
            ->orderBy('vigencia_desde', 'desc')
            ->first();

        return response()->json([
            'id' => $membresia->id,
            'nombre' => $membresia->nombre,
            'duracion_meses' => $membresia->duracion_meses,
            'duracion_dias' => $membresia->duracion_dias,
            'precio' => $precioActual ? $precioActual->precio : 0,
            'id_precio' => $precioActual ? $precioActual->id : null,
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

        return response()->json([
            'id' => $convenio->id,
            'nombre' => $convenio->nombre,
            'descuento_porcentaje' => $convenio->descuento_porcentaje ?? 0,
            'descuento_monto' => $convenio->descuento_monto ?? 0,
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
        
        // Calcular fecha de vencimiento
        $fechaVencimiento = $fechaInicio->clone()
            ->addMonths($membresia->duracion_meses)
            ->addDays($membresia->duracion_dias)
            ->subDay();

        // Calcular descuento
        $descuento = 0;
        if ($validated['id_convenio']) {
            $convenio = Convenio::find($validated['id_convenio']);
            if ($convenio) {
                $descuento = $convenio->descuento_monto ?? 0;
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
