<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Convenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConvenioController extends Controller
{
    use ValidatesFormToken;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $convenios = Convenio::paginate(20);
        return view('admin.convenios.index', compact('convenios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.convenios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->validateFormToken($request, 'convenio_create')) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:convenios',
            'tipo' => 'required|in:institucion_educativa,empresa,organizacion,otro',
            'descripcion' => 'nullable|string',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:100',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'descuento_monto' => 'nullable|numeric|min:0',
            'activo' => 'boolean',
        ]);

        Convenio::create($validated);

        return redirect()->route('admin.convenios.index')
            ->with('success', 'Convenio creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Convenio $convenio)
    {
        $convenio->load('clientes');
        return view('admin.convenios.show', compact('convenio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Convenio $convenio)
    {
        return view('admin.convenios.edit', compact('convenio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Convenio $convenio)
    {
        if (!$this->validateFormToken($request, 'convenio_update_' . $convenio->id)) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:convenios,nombre,' . $convenio->id,
            'tipo' => 'required|in:institucion_educativa,empresa,organizacion,otro',
            'descripcion' => 'nullable|string',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:100',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'descuento_monto' => 'nullable|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $convenio->update($validated);

        return redirect()->route('admin.convenios.show', $convenio)
            ->with('success', 'Convenio actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     * Implementa SoftDelete: envía el convenio a la papelera
     */
    public function destroy(Convenio $convenio)
    {
        // Verificar si tiene clientes activos asociados
        $clientesActivos = $convenio->clientes()->where('activo', true)->count();
        
        if ($clientesActivos > 0) {
            return redirect()->back()
                ->with('error', "No se puede eliminar el convenio '{$convenio->nombre}'. Tiene {$clientesActivos} cliente(s) activo(s) asociado(s).");
        }

        $nombreConvenio = $convenio->nombre;
        
        // SoftDelete: enviar a papelera
        $convenio->delete();

        return redirect()->route('admin.convenios.index')
            ->with('success', "Convenio '{$nombreConvenio}' enviado a la papelera.");
    }

    /**
     * Desactivar un convenio (cambio de estado, NO eliminación)
     */
    public function deactivate(Convenio $convenio)
    {
        $convenio->update(['activo' => false]);

        return redirect()->back()
            ->with('success', "Convenio '{$convenio->nombre}' desactivado exitosamente.");
    }

    /**
     * Activar un convenio
     */
    public function activate(Convenio $convenio)
    {
        $convenio->update(['activo' => true]);

        return redirect()->back()
            ->with('success', "Convenio '{$convenio->nombre}' activado exitosamente.");
    }

    // ==========================================
    // PAPELERA (SoftDeletes)
    // ==========================================

    /**
     * Mostrar convenios eliminados (papelera)
     */
    public function trashed()
    {
        $convenios = Convenio::onlyTrashed()
            ->withCount(['clientes' => function($q) { $q->withTrashed(); }])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        $totalEliminados = Convenio::onlyTrashed()->count();

        return view('admin.convenios.trashed', compact('convenios', 'totalEliminados'));
    }

    /**
     * Restaurar un convenio eliminado
     */
    public function restore($id)
    {
        $convenio = Convenio::onlyTrashed()->findOrFail($id);
        $convenio->restore();

        return redirect()->route('admin.convenios.trashed')
            ->with('success', "Convenio '{$convenio->nombre}' restaurado exitosamente.");
    }

    /**
     * Eliminar permanentemente un convenio
     */
    public function forceDelete($id)
    {
        $convenio = Convenio::onlyTrashed()->findOrFail($id);
        
        if ($convenio->clientes()->withTrashed()->exists()) {
            return redirect()->route('admin.convenios.trashed')
                ->with('error', 'No se puede eliminar. El convenio tiene clientes asociados.');
        }

        $nombreConvenio = $convenio->nombre;
        $convenio->forceDelete();

        return redirect()->route('admin.convenios.trashed')
            ->with('success', "Convenio '{$nombreConvenio}' eliminado permanentemente.");
    }
}
