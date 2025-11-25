<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function index(): View
    {
        $pagos = Pago::with('cliente', 'inscripcion.membresia', 'estado', 'metodoPago')
            ->latest('fecha_pago')
            ->paginate(15);
        
        return view('pagos.index', compact('pagos'));
    }

    public function create(): View
    {
        $inscripciones = Inscripcion::where('id_estado', 201)
            ->with('cliente', 'membresia')
            ->get();
        $metodos = MetodoPago::where('activo', true)->get();
        
        return view('pagos.create', compact('inscripciones', 'metodos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|integer|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'id_metodo_pago' => 'required|integer|exists:metodos_pago,id',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $inscripcion = Inscripcion::find($validated['id_inscripcion']);
        
        // Calcular monto pendiente
        $montoAbonado = $validated['monto_abonado'];
        $montoPendiente = $inscripcion->precio_final - $montoAbonado;

        // Determinar estado del pago
        $estadoPago = $montoPendiente <= 0 ? 302 : 303; // 302: Pagado, 303: Parcial

        Pago::create([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_final,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => max(0, $montoPendiente),
            'fecha_pago' => Carbon::now()->toDateString(),
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'referencia_pago' => $validated['referencia_pago'],
            'id_estado' => $estadoPago,
            'observaciones' => $validated['observaciones'],
        ]);

        return redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.');
    }

    public function show(Pago $pago): View
    {
        $pago->load('cliente', 'inscripcion', 'metodoPago', 'estado');
        
        return view('pagos.show', compact('pago'));
    }

    public function edit(Pago $pago): View
    {
        $metodos = MetodoPago::where('activo', true)->get();
        
        return view('pagos.edit', compact('pago', 'metodos'));
    }

    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $pago->update($validated);

        return redirect()->route('pagos.show', $pago)->with('success', 'Pago actualizado exitosamente.');
    }
}
