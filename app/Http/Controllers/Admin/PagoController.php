<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagos = Pago::with(['inscripcion', 'metodoPago'])->orderBy('fecha_pago', 'desc')->paginate(15);
        $metodos_pago = MetodoPago::all();
        return view('admin.pagos.index', compact('pagos', 'metodos_pago'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inscripciones = Inscripcion::with('cliente')->get();
        $metodos_pago = MetodoPago::all();
        return view('admin.pagos.create', compact('inscripciones', 'metodos_pago'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'id_metodo_pago' => 'required|exists:metodo_pagos,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        Pago::create($validated);

        return redirect()->route('admin.pagos.index')
            ->with('success', 'Pago registrado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load(['inscripcion', 'metodoPago']);
        return view('admin.pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        $inscripciones = Inscripcion::with('cliente')->get();
        $metodos_pago = MetodoPago::all();
        return view('admin.pagos.edit', compact('pago', 'inscripciones', 'metodos_pago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'id_metodo_pago' => 'required|exists:metodo_pagos,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $pago->update($validated);

        return redirect()->route('admin.pagos.show', $pago)
            ->with('success', 'Pago actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();

        return redirect()->route('admin.pagos.index')
            ->with('success', 'Pago eliminado exitosamente');
    }
}
