<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetodoPagoController extends Controller
{
    use ValidatesFormToken;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $metodos = MetodoPago::paginate(20);
        return view('admin.metodos-pago.index', compact('metodos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.metodos-pago.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->validateFormToken($request, 'metodo_pago_create')) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:metodos_pago',
            'descripcion' => 'nullable|string|max:500',
            'requiere_referencia' => 'boolean',
            'activo' => 'boolean',
        ]);

        MetodoPago::create($validated);

        return redirect()->route('admin.metodos-pago.index')
            ->with('success', 'Método de Pago creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoPago $metodoPago)
    {
        return view('admin.metodos-pago.show', compact('metodoPago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MetodoPago $metodoPago)
    {
        return view('admin.metodos-pago.edit', compact('metodoPago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodoPago $metodoPago)
    {
        if (!$this->validateFormToken($request, 'metodo_pago_update_' . $metodoPago->id)) return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:metodos_pago,nombre,' . $metodoPago->id,
            'descripcion' => 'nullable|string|max:500',
            'requiere_referencia' => 'boolean',
            'activo' => 'boolean',
        ]);

        $metodoPago->update($validated);

        return redirect()->route('admin.metodos-pago.show', $metodoPago)
            ->with('success', 'Método de Pago actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        return redirect()->route('admin.metodos-pago.index')
            ->with('success', 'Método de Pago eliminado exitosamente');
    }

}
