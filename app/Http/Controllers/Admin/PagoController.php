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
    public function create(Request $request)
    {
        $inscripcion = null;
        
        // Si viene desde inscripción.show
        if ($request->filled('id_inscripcion')) {
            $inscripcion = Inscripcion::with('cliente', 'membresia')->find($request->id_inscripcion);
        } else {
            $inscripcion = Inscripcion::with('cliente', 'membresia')->latest()->first();
        }
        
        $metodos_pago = MetodoPago::all();
        return view('admin.pagos.create', compact('inscripcion', 'metodos_pago'));
    }

    /**
     * Store a newly created resource in storage.
     * Ahora soporta pagos parciales y múltiples cuotas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'id_metodo_pago' => 'required|exists:metodo_pagos,id',
            'cantidad_cuotas' => 'required|integer|min:1|max:12',
            'numero_cuota' => 'required|integer|min:1',
            'fecha_vencimiento_cuota' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $inscripcion = Inscripcion::find($validated['id_inscripcion']);
        
        // Calcular monto de cuota
        $montoCuota = $validated['monto_abonado'] / $validated['cantidad_cuotas'];

        $pago = Pago::create([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_base - $inscripcion->descuento_aplicado,
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => ($inscripcion->precio_base - $inscripcion->descuento_aplicado) - $validated['monto_abonado'],
            'descuento_aplicado' => $inscripcion->descuento_aplicado,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'numero_cuota' => $validated['numero_cuota'],
            'monto_cuota' => $montoCuota,
            'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
            'id_estado' => 102, // Pagado
            'observaciones' => $validated['observaciones'],
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
        ]);

        return redirect()->route('admin.pagos.index')
            ->with('success', 'Pago registrado exitosamente (Cuota ' . $validated['numero_cuota'] . ' de ' . $validated['cantidad_cuotas'] . ')');
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
     * Actualiza pagos con soporte para cuotas
     */
    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'id_metodo_pago' => 'required|exists:metodo_pagos,id',
            'cantidad_cuotas' => 'required|integer|min:1|max:12',
            'numero_cuota' => 'required|integer|min:1',
            'fecha_vencimiento_cuota' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $inscripcion = Inscripcion::find($validated['id_inscripcion']);
        $montoCuota = $validated['monto_abonado'] / $validated['cantidad_cuotas'];

        $pago->update([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_base - $inscripcion->descuento_aplicado,
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => ($inscripcion->precio_base - $inscripcion->descuento_aplicado) - $validated['monto_abonado'],
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'numero_cuota' => $validated['numero_cuota'],
            'monto_cuota' => $montoCuota,
            'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
            'observaciones' => $validated['observaciones'],
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
        ]);

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
