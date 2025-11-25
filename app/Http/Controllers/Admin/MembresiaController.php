<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use App\Models\PrecioMembresia;
use App\Models\HistorialPrecio;
use Illuminate\Http\Request;

class MembresiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $membresias = Membresia::withCount('inscripciones')->with(['precios', 'inscripciones'])->paginate(15);
        return view('admin.membresias.index', compact('membresias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.membresias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:membresias',
            'duracion_meses' => 'nullable|integer|min:0',
            'duracion_dias' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:1000',
            'precio_normal' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $membresia = Membresia::create([
            'nombre' => $validated['nombre'],
            'duracion_meses' => $validated['duracion_meses'] ?? 0,
            'duracion_dias' => $validated['duracion_dias'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ]);

        // Crear precio inicial
        PrecioMembresia::create([
            'id_membresia' => $membresia->id,
            'precio_normal' => $validated['precio_normal'],
            'precio_convenio' => $validated['precio_normal'],
            'fecha_vigencia_desde' => now(),
            'activo' => true,
        ]);

        // Registrar en historial
        HistorialPrecio::create([
            'id_precio_membresia' => $membresia->precios()->first()->id,
            'precio_anterior' => 0,
            'precio_nuevo' => $validated['precio_normal'],
            'razon_cambio' => 'Creación de membresía',
            'usuario_cambio' => auth()->user()->name,
        ]);

        return redirect()->route('admin.membresias.show', $membresia)
            ->with('success', 'Membresía creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Membresia $membresia)
    {
        $membresia->load([
            'precios',
            'inscripciones' => function($q) {
                $q->with(['cliente', 'estado'])->orderBy('created_at', 'desc');
            }
        ]);
        
        $historialPrecios = HistorialPrecio::whereIn(
            'id_precio_membresia',
            $membresia->precios()->pluck('id')
        )->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.membresias.show', compact('membresia', 'historialPrecios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Membresia $membresia)
    {
        $membresia->load('precios');
        $precioActual = $membresia->precios()
            ->where(function($q) {
                $q->where('activo', true)
                  ->orWhere('fecha_vigencia_desde', '<=', now());
            })
            ->orderBy('fecha_vigencia_desde', 'desc')
            ->first();

        return view('admin.membresias.edit', compact('membresia', 'precioActual'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Membresia $membresia)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:membresias,nombre,' . $membresia->id,
            'duracion_meses' => 'nullable|integer|min:0',
            'duracion_dias' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:1000',
            'precio_normal' => 'required|numeric|min:0',
            'razon_cambio' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        // Obtener precio actual
        $precioActual = $membresia->precios()
            ->where('activo', true)
            ->first();

        $precioAnterior = $precioActual->precio_normal ?? 0;

        // Actualizar membresía
        $membresia->update([
            'nombre' => $validated['nombre'],
            'duracion_meses' => $validated['duracion_meses'] ?? 0,
            'duracion_dias' => $validated['duracion_dias'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ]);

        // Si el precio cambió, crear nuevo registro
        if ($precioActual && $validated['precio_normal'] != $precioAnterior) {
            // Desactivar precio anterior
            $precioActual->update(['activo' => false]);

            // Crear nuevo precio
            $nuevoPrecio = PrecioMembresia::create([
                'id_membresia' => $membresia->id,
                'precio_normal' => $validated['precio_normal'],
                'precio_convenio' => $validated['precio_normal'],
                'fecha_vigencia_desde' => now(),
                'activo' => true,
            ]);

            // Registrar en historial
            HistorialPrecio::create([
                'id_precio_membresia' => $nuevoPrecio->id,
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $validated['precio_normal'],
                'razon_cambio' => $validated['razon_cambio'] ?? 'Actualización de precio',
                'usuario_cambio' => auth()->user()->name,
            ]);
        }

        return redirect()->route('admin.membresias.show', $membresia)
            ->with('success', 'Membresía actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Membresia $membresia)
    {
        // Eliminará la membresía (las relaciones se manejan según la BD)
        $membresia->delete();

        return redirect()->route('admin.membresias.index')
            ->with('success', 'Membresía eliminada exitosamente');
    }
}
