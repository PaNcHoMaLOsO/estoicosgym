<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use Illuminate\Http\Request;

class ConvenioController extends Controller
{
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:convenios',
            'tipo' => 'required|in:institucion_educativa,empresa,organizacion,otro',
            'descripcion' => 'nullable|string',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:100',
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:convenios,nombre,' . $convenio->id,
            'tipo' => 'required|in:institucion_educativa,empresa,organizacion,otro',
            'descripcion' => 'nullable|string',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:100',
            'activo' => 'boolean',
        ]);

        $convenio->update($validated);

        return redirect()->route('admin.convenios.show', $convenio)
            ->with('success', 'Convenio actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Convenio $convenio)
    {
        $convenio->delete();

        return redirect()->route('admin.convenios.index')
            ->with('success', 'Convenio eliminado exitosamente');
    }
}
