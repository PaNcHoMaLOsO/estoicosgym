<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membresia;
use App\Models\PrecioMembresia;
use App\Models\HistorialPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembresiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $membresias = Membresia::withCount('inscripciones')->with(['precios', 'inscripciones'])->paginate(20);
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
            'duracion_meses' => 'required|integer|min:0|max:12',
            'duracion_dias' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:1000',
            'precio_normal' => 'required|numeric|min:0',
            'precio_convenio' => 'nullable|numeric|min:0|lt:precio_normal',
            'activo' => 'boolean',
        ]);

        $membresia = Membresia::create([
            'nombre' => $validated['nombre'],
            'duracion_meses' => $validated['duracion_meses'],
            'duracion_dias' => $validated['duracion_dias'],
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ]);

        // Crear precio inicial
        PrecioMembresia::create([
            'id_membresia' => $membresia->id,
            'precio_normal' => $validated['precio_normal'],
            'precio_convenio' => $validated['precio_convenio'] ?? null,
            'fecha_vigencia_desde' => now(),
            'activo' => true,
        ]);

        // Registrar en historial
        HistorialPrecio::create([
            'id_precio_membresia' => $membresia->precios()->first()->id,
            'precio_anterior' => 0,
            'precio_nuevo' => $validated['precio_normal'],
            'razon_cambio' => 'Creación de membresía',
            'usuario_cambio' => Auth::user()?->name ?? 'Sistema',
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
        )->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.membresias.show', compact('membresia', 'historialPrecios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Membresia $membresia)
    {
        $membresia->load('precios');
        $precioActual = $membresia->precios()
            ->where('activo', true)
            ->where('fecha_vigencia_desde', '<=', now())
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
            'duracion_meses' => 'required|integer|min:0|max:12',
            'duracion_dias' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:1000',
            'precio_normal' => 'required|numeric|min:0',
            'precio_convenio' => 'nullable|numeric|min:0|lt:precio_normal',
            'razon_cambio' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        // Verificar si hay cambios críticos que afecten inscripciones existentes
        $tieneCambiosCriticos = false;
        $cambiosCriticos = [];

        if ($membresia->duracion_meses != $validated['duracion_meses'] || 
            $membresia->duracion_dias != $validated['duracion_dias']) {
            $tieneCambiosCriticos = true;
            $cambiosCriticos[] = 'duración';
        }

        $precioActual = $membresia->precios()
            ->where('activo', true)
            ->first();

        if ($precioActual && $validated['precio_normal'] != $precioActual->precio_normal) {
            $tieneCambiosCriticos = true;
            $cambiosCriticos[] = 'precio';
        }

        // Si hay cambios críticos e inscripciones activas, advertir
        // Usar id_estado para verificar inscripciones activas
        $inscripcionesActivas = $membresia->inscripciones()
            ->whereNotIn('id_estado', [3, 5]) // Excluyendo vencida (3) y cancelada (5)
            ->count();

        if ($tieneCambiosCriticos && $inscripcionesActivas > 0) {
            // Registrar el cambio en auditoría
            $cambioDetalles = implode(', ', $cambiosCriticos);
            $usuario = Auth::user()?->name ?? 'Sistema';
            \Log::warning("Cambios críticos en membresía {$membresia->nombre}: {$cambioDetalles}. Inscripciones activas: {$inscripcionesActivas}. Usuario: {$usuario}");
        }

        $precioAnterior = $precioActual->precio_normal ?? 0;

        // Actualizar membresía
        $membresia->update([
            'nombre' => $validated['nombre'],
            'duracion_meses' => $validated['duracion_meses'],
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
                'precio_convenio' => $validated['precio_convenio'] ?? null,
                'fecha_vigencia_desde' => now(),
                'activo' => true,
            ]);

            // Registrar en historial
            HistorialPrecio::create([
                'id_precio_membresia' => $nuevoPrecio->id,
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $validated['precio_normal'],
                'razon_cambio' => $validated['razon_cambio'] ?? 'Actualización de precio',
                'usuario_cambio' => Auth::user()?->name ?? 'Sistema',
            ]);
        }

        return redirect()->route('admin.membresias.show', $membresia)
            ->with('success', 'Membresía actualizada exitosamente' . 
                ($tieneCambiosCriticos && $inscripcionesActivas > 0 ? '. Advertencia: Se detectaron ' . $inscripcionesActivas . ' inscripción(es) activa(s) afectada(s).' : ''));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Membresia $membresia)
    {
        $forceDelete = request()->input('force_delete') === '1';
        $nombreMembresia = $membresia->nombre;
        
        // Verificar si hay inscripciones activas
        $inscripcionesActivas = $membresia->inscripciones()
            ->whereNotIn('id_estado', [3, 5]) // Excluyendo vencida (3) y cancelada (5)
            ->count();

        // Si hay inscripciones activas y no es eliminación forzada, solo desactivar
        if ($inscripcionesActivas > 0 && !$forceDelete) {
            // Desactivar la membresía
            $membresia->update(['activo' => false]);
            
            \Log::info("Membresía desactivada: {$nombreMembresia}. Inscripciones activas: {$inscripcionesActivas}. Usuario: " . (Auth::user()?->name ?? 'Sistema'));
            
            return redirect()->route('admin.membresias.index')
                ->with('success', "Membresía '{$nombreMembresia}' desactivada exitosamente. " .
                    "Tiene {$inscripcionesActivas} inscripción(es) activa(s) que se mantendrán hasta su vencimiento.");
        }

        // Si force_delete = true, eliminar completamente
        if ($forceDelete) {
            $inscripcionesTotales = $membresia->inscripciones()->count();
            
            \Log::warning("Membresía ELIMINADA: {$nombreMembresia}. Total de inscripciones asociadas: {$inscripcionesTotales}. Usuario: " . (Auth::user()?->name ?? 'Sistema'));
            
            $membresia->delete();
            
            return redirect()->route('admin.membresias.index')
                ->with('success', "Membresía '{$nombreMembresia}' eliminada completamente. Se registraron {$inscripcionesTotales} inscripción(es) histórica(s).");
        }

        // Si no hay inscripciones activas, eliminar directamente
        $inscripcionesTotales = $membresia->inscripciones()->count();
        
        \Log::info("Membresía eliminada: {$nombreMembresia}. Total de inscripciones: {$inscripcionesTotales}. Usuario: " . (Auth::user()?->name ?? 'Sistema'));
        
        $membresia->delete();
        
        return redirect()->route('admin.membresias.index')
            ->with('success', "Membresía '{$nombreMembresia}' eliminada exitosamente. Se registraron {$inscripcionesTotales} inscripción(es) histórica(s).");
    }
}
