<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        if (!$this->validateFormToken($request, 'convenio_create')) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
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
        if (!$this->validateFormToken($request, 'convenio_update_' . $convenio->id)) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
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

    /**
     * Validar token de formulario para prevenir doble envío.
     */
    private function validateFormToken(Request $request, string $action): bool
    {
        $token = $request->input('form_submit_token');
        if (!$token) return false;
        
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_submit_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        if (Cache::has($cacheKey)) return false;
        Cache::put($cacheKey, true, 10);
        return true;
    }
}
