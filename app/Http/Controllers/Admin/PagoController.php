<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Estado;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pago::with(['inscripcion.cliente', 'metodoPago', 'estado']);
        
        // Filtro por inscripción (desde el link de Ver Pagos en inscripciones)
        if ($request->filled('id_inscripcion')) {
            $query->where('id_inscripcion', $request->id_inscripcion);
        }
        
        // Filtro por cliente
        if ($request->filled('cliente')) {
            $query->whereHas('inscripcion.cliente', function($q) use ($request) {
                $q->where('nombres', 'like', '%' . $request->cliente . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $request->cliente . '%');
            });
        }
        
        // Filtro por método de pago
        if ($request->filled('metodo_pago')) {
            $query->where('id_metodo_pago', $request->metodo_pago);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('id_estado', $request->estado);
        }
        
        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_pago', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }
        
        // Ordenamiento - solo campos de la tabla pagos
        $ordenar = $request->get('ordenar', 'fecha_pago');
        $direccion = $request->get('direccion', 'desc');
        
        // Validar que el campo sea válido
        $camposValidos = ['id', 'id_inscripcion', 'id_cliente', 'monto_total', 'monto_abonado', 'fecha_pago', 'id_metodo_pago', 'id_estado', 'created_at'];
        if (!in_array($ordenar, $camposValidos)) {
            $ordenar = 'fecha_pago';
        }
        
        $query->orderBy($ordenar, $direccion);
        
        $pagos = $query->paginate(20);
        $metodos_pago = MetodoPago::all();
        
        return view('admin.pagos.index', compact('pagos', 'metodos_pago'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inscripciones = Inscripcion::with('cliente')->limit(30)->get();
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
        $inscripciones = Inscripcion::with('cliente')->limit(30)->get();
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
