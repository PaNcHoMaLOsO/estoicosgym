<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\MotivoDescuento;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class MotivoDescuentoController extends Controller
{
    use ValidatesFormToken;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $motivos = MotivoDescuento::paginate(20);
        return view('admin.motivos-descuento.index', compact('motivos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.motivos-descuento.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->validateFormToken($request, 'motivo_descuento_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:motivos_descuento',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ]);

        MotivoDescuento::create($validated);

        return redirect()->route('admin.motivos-descuento.index')
            ->with('success', 'Motivo de Descuento creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(MotivoDescuento $motivoDescuento)
    {
        return view('admin.motivos-descuento.show', compact('motivoDescuento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MotivoDescuento $motivoDescuento)
    {
        return view('admin.motivos-descuento.edit', compact('motivoDescuento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MotivoDescuento $motivoDescuento)
    {
        if (!$this->validateFormToken($request, 'motivo_descuento_update_' . $motivoDescuento->id)) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:motivos_descuento,nombre,' . $motivoDescuento->id,
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ]);

        $motivoDescuento->update($validated);

        return redirect()->route('admin.motivos-descuento.show', $motivoDescuento)
            ->with('success', 'Motivo de Descuento actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MotivoDescuento $motivoDescuento)
    {
        // Verificar si está siendo usado en inscripciones
        $usadoEnInscripciones = Inscripcion::where('id_motivo_descuento', $motivoDescuento->id)->exists();

        if ($usadoEnInscripciones) {
            return redirect()->route('admin.motivos-descuento.index')
                ->with('error', 'No se puede eliminar este motivo porque está siendo usado en inscripciones.');
        }

        $motivoDescuento->delete();

        return redirect()->route('admin.motivos-descuento.index')
            ->with('success', 'Motivo de Descuento eliminado exitosamente');
    }
}
