<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use Illuminate\Http\Request;

class MembresiaApiController extends Controller
{
    /**
     * Obtener todas las membresias activas
     */
    public function index()
    {
        $membresias = Membresia::where('activo', true)
            ->with(['precios' => function($q) {
                $q->where('activo', true)
                    ->orWhere(function($subq) {
                        $subq->where('fecha_vigencia_desde', '<=', now());
                    })
                    ->latest('fecha_vigencia_desde');
            }])
            ->get()
            ->map(function($membresia) {
                $precioActual = $membresia->precios->first();
                return [
                    'id' => $membresia->id,
                    'nombre' => $membresia->nombre,
                    'duracion_dias' => $membresia->duracion_dias,
                    'duracion_meses' => $membresia->duracion_meses,
                    'descripcion' => $membresia->descripcion,
                    'precio_normal' => $precioActual?->precio_normal ?? 0,
                    'precio_convenio' => $precioActual?->precio_convenio ?? 0,
                ];
            });

        return response()->json($membresias);
    }

    /**
     * Obtener una membresía específica con su precio actual
     */
    public function show($id)
    {
        $membresia = Membresia::findOrFail($id);
        
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
            'duracion_dias' => $membresia->duracion_dias,
            'duracion_meses' => $membresia->duracion_meses,
            'descripcion' => $membresia->descripcion,
            'precio_normal' => $precioActual?->precio_normal ?? 0,
            'precio_convenio' => $precioActual?->precio_convenio ?? 0,
            'activo' => $membresia->activo,
            'inscripciones_count' => $membresia->inscripciones()->count(),
        ]);
    }

    /**
     * Buscar membresias por nombre
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $membresias = Membresia::where('activo', true)
            ->where('nombre', 'like', '%' . $query . '%')
            ->with(['precios' => function($q) {
                $q->where('activo', true)
                    ->orWhere('fecha_vigencia_desde', '<=', now())
                    ->latest('fecha_vigencia_desde');
            }])
            ->limit(10)
            ->get()
            ->map(function($membresia) {
                $precioActual = $membresia->precios->first();
                return [
                    'id' => $membresia->id,
                    'text' => $membresia->nombre . ' (' . $membresia->duracion_dias . ' días)',
                    'nombre' => $membresia->nombre,
                    'duracion_dias' => $membresia->duracion_dias,
                    'precio_normal' => $precioActual?->precio_normal ?? 0,
                    'precio_convenio' => $precioActual?->precio_convenio ?? 0,
                ];
            });

        return response()->json($membresias);
    }
}
