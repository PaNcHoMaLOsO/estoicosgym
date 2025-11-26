<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\PrecioMembresia;
use App\Models\MotivoDescuento;
use App\Models\Estado;
use App\Models\Convenio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InscripcionController extends Controller
{
    public function index(): View
    {
        $inscripciones = Inscripcion::with('cliente', 'membresia', 'estado')
            ->latest('fecha_inscripcion')
            ->paginate(15);
        
        return view('inscripciones.index', compact('inscripciones'));
    }

    public function create(): View
    {
        // Obtener clientes activos que NO tengan inscripciones activas
        $clientesConInscripcion = Inscripcion::whereIn('id_estado', [201, 202, 203, 205, 206])
            ->pluck('id_cliente')
            ->unique();
        
        $clientes = Cliente::where('activo', true)
            ->whereNotIn('id', $clientesConInscripcion)
            ->orderBy('nombres')
            ->get();
            
        $membresias = Membresia::where('activo', true)->get();
        $motivos = MotivoDescuento::where('activo', true)->get();
        $estados = Estado::whereIn('id', [201, 202, 203, 205, 206])->get();
        $convenios = Convenio::where('activo', true)->get();
        
        return view('admin.inscripciones.create', compact('clientes', 'membresias', 'motivos', 'estados', 'convenios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id',
            'id_membresia' => 'required|integer|exists:membresias,id',
            'id_estado' => 'required|integer|exists:estados,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'id_convenio' => 'nullable|integer|exists:convenios,id',
            'cantidad_cuotas' => 'required|integer|min:1|max:12',
            'id_motivo_descuento' => 'nullable|integer|exists:motivos_descuento,id',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $membresia = Membresia::find($validated['id_membresia']);
        $precioActual = PrecioMembresia::where('id_membresia', $membresia->id)
            ->whereNull('fecha_vigencia_hasta')
            ->first();

        if (!$precioActual) {
            return redirect()->back()->withErrors(['error' => 'No hay precio vigente para esta membresía.']);
        }

        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaVencimiento = $fechaInicio->clone()->addDays($membresia->duracion_dias);
        $descuento = $validated['descuento_aplicado'] ?? 0;
        $precioFinal = $precioActual->precio_normal - $descuento;
        
        // Calcular monto de cuota
        $montoCuota = $precioFinal / $validated['cantidad_cuotas'];

        Inscripcion::create([
            'id_cliente' => $validated['id_cliente'],
            'id_membresia' => $validated['id_membresia'],
            'id_precio_acordado' => $precioActual->id,
            'id_convenio' => $validated['id_convenio'],
            'id_estado' => $validated['id_estado'],
            'fecha_inscripcion' => Carbon::now(),
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'precio_base' => $precioActual->precio_normal,
            'descuento_aplicado' => $descuento,
            'id_motivo_descuento' => $validated['id_motivo_descuento'],
            'observaciones' => $validated['observaciones'],
        ]);

        return redirect()->route('admin.inscripciones.index')->with('success', 'Inscripción creada exitosamente.');
    }

    public function show(Inscripcion $inscripcion): View
    {
        $inscripcion->load('cliente', 'membresia', 'estado', 'motivoDescuento', 'pagos.estado');
        $estadoPago = $inscripcion->obtenerEstadoPago();
        
        return view('admin.inscripciones.show', compact('inscripcion', 'estadoPago'));
    }

    public function edit(Inscripcion $inscripcion): View
    {
        $motivos = MotivoDescuento::where('activo', true)->get();
        
        return view('inscripciones.edit', compact('inscripcion', 'motivos'));
    }

    public function update(Request $request, Inscripcion $inscripcion)
    {
        $validated = $request->validate([
            'id_estado' => 'required|integer|exists:estados,id',
            'id_motivo_descuento' => 'nullable|integer|exists:motivos_descuento,id',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $descuento = $validated['descuento_aplicado'] ?? $inscripcion->descuento_aplicado;

        $inscripcion->update([
            'descuento_aplicado' => $descuento,
            'id_estado' => $validated['id_estado'],
            'id_motivo_descuento' => $validated['id_motivo_descuento'],
            'observaciones' => $validated['observaciones'] ?? $inscripcion->observaciones,
        ]);

        return redirect()->route('admin.inscripciones.show', $inscripcion)->with('success', 'Inscripción actualizada exitosamente.');
    }

    public function destroy(Inscripcion $inscripcion)
    {
        $inscripcion->update(['id_estado' => 204]); // Cancelada

        return redirect()->route('admin.inscripciones.index')->with('success', 'Inscripción cancelada exitosamente.');
    }
}
