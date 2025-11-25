<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Membresia;
use App\Models\Convenio;
use App\Models\MotivoDescuento;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inscripciones = Inscripcion::with(['cliente', 'estado'])->paginate(15);
        return view('admin.inscripciones.index', compact('inscripciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener IDs de estados activos para inscripciones
        $estadoActiva = Estado::where('nombre', 'Activa')->first();
        $estadoActivaId = $estadoActiva?->id ?? 1;
        
        // Clientes con inscripción ACTIVA (no pueden tener otra)
        $clientesConInscripcionActiva = Inscripcion::where('id_estado', $estadoActivaId)
            ->pluck('id_cliente')
            ->unique()
            ->toArray();
        
        // Mostrar SOLO: Clientes activos SIN inscripción activa
        $clientes = Cliente::where('activo', true)
            ->whereNotIn('id', $clientesConInscripcionActiva)
            ->orderBy('nombres')
            ->get();
        
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
        return view('admin.inscripciones.create', compact('clientes', 'estados', 'membresias', 'convenios', 'motivos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_estado' => 'required|exists:estados,id',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'precio_base' => 'required|numeric|min:0.01',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Calcular precio_final
        $validated['precio_final'] = $validated['precio_base'] - ($validated['descuento_aplicado'] ?? 0);
        $validated['fecha_inscripcion'] = now()->format('Y-m-d');
        $validated['id_precio_acordado'] = 1; // Temporal: necesita API para seleccionar precio

        Inscripcion::create($validated);

        return redirect()->route('admin.inscripciones.index')
            ->with('success', 'Inscripción creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'estado', 'pagos']);
        return view('admin.inscripciones.show', compact('inscripcion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inscripcion $inscripcion)
    {
        $clientes = Cliente::active()->get();
        $estados = Estado::where('categoria', 'inscripcion')->get();
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
        return view('admin.inscripciones.edit', compact('inscripcion', 'clientes', 'estados', 'membresias', 'convenios', 'motivos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscripcion $inscripcion)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_estado' => 'required|exists:estados,id',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'precio_base' => 'required|numeric|min:0.01',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Calcular precio_final
        $validated['precio_final'] = $validated['precio_base'] - ($validated['descuento_aplicado'] ?? 0);

        $inscripcion->update($validated);

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inscripcion $inscripcion)
    {
        $inscripcion->delete();

        return redirect()->route('admin.inscripciones.index')
            ->with('success', 'Inscripción eliminada exitosamente');
    }
}
