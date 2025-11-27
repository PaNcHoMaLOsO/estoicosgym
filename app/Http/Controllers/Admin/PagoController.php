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
        $query = Pago::with(['inscripcion.cliente', 'metodoPagoPrincipal', 'estado']);
        
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
            $query->where('id_metodo_pago_principal', $request->metodo_pago);
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
        $estados = Estado::where('categoria', 'pago')->get();
        
        return view('admin.pagos.index', compact('pagos', 'metodos_pago', 'estados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Obtener todas las inscripciones activas con sus relaciones
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 1) // Solo inscripciones activas
            ->orderBy('id', 'desc')
            ->get();
        
        $metodos_pago = MetodoPago::where('activo', true)->get();
        
        return view('admin.pagos.create', compact('inscripciones', 'metodos_pago'));
    }

    /**
     * Store a newly created resource in storage.
     * Soporta tres modos: abono parcial, pago completo, pago mixto
     */
    public function store(Request $request)
    {
        $tipoPago = $request->input('tipo_pago', 'abono');
        
        // Validaciones base
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'tipo_pago' => 'required|in:abono,completo,mixto',
            'fecha_pago' => 'required|date|max:today',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
        ]);

        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;

        // Validar estado de inscripción
        if ($inscripcion->id_estado != 1) {
            return back()->withErrors([
                'id_inscripcion' => "La inscripción no está activa"
            ])->withInput();
        }

        $montoAbonado = 0;

        // ABONO PARCIAL
        if ($tipoPago === 'abono') {
            $request->validate([
                'monto_abonado' => 'required|numeric|min:1000|max:' . $montoTotal,
                'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            ]);

            $montoAbonado = $request->input('monto_abonado');

            if ($montoAbonado <= 0 || $montoAbonado > $montoTotal) {
                return back()->withErrors([
                    'monto_abonado' => "El monto debe ser entre 0 y {$montoTotal}"
                ])->withInput();
            }
        }
        // PAGO COMPLETO
        else if ($tipoPago === 'completo') {
            $request->validate([
                'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            ]);

            $montoAbonado = $montoTotal;
        }
        // PAGO MIXTO
        else if ($tipoPago === 'mixto') {
            $monto1 = floatval($request->input('monto_metodo1', 0));
            $monto2 = floatval($request->input('monto_metodo2', 0));
            $montoAbonado = $monto1 + $monto2;

            if ($montoAbonado != $montoTotal) {
                return back()->withErrors([
                    'monto_metodo1' => "La suma de los montos debe ser exactamente {$montoTotal}"
                ])->withInput();
            }
        }

        $montoPendiente = $montoTotal - $montoAbonado;
        $cantidadCuotas = $validated['cantidad_cuotas'] ?? 1;
        $montoCuota = $montoAbonado / $cantidadCuotas;

        // Crear pago
        $pago = Pago::create([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'id_membresia' => $inscripcion->id_membresia,
            'monto_total' => $montoTotal,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => $montoPendiente,
            'cantidad_cuotas' => $cantidadCuotas,
            'numero_cuota' => 1,
            'monto_cuota' => $montoCuota,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago_principal' => $request->input('id_metodo_pago_principal'),
            'referencia_pago' => $validated['referencia_pago'],
            'observaciones' => $validated['observaciones'] . " [Tipo: {$tipoPago}]",
            'id_estado' => $montoAbonado >= $montoTotal ? 102 : 103, // 102=Pagado, 103=Parcial
        ]);

        return redirect()->route('admin.pagos.index')
            ->with('success', "Pago registrado exitosamente ({$tipoPago})");
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load(['inscripcion', 'metodoPagoPrincipal']);
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
        $es_pago_simple = $request->has('es_pago_simple');
        
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date|max:today',
            'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;
        $montoAbonado = $validated['monto_abonado'];

        // Validaciones básicas
        if ($montoAbonado > $montoTotal) {
            return back()->withErrors([
                'monto_abonado' => "El monto no puede exceder {$montoTotal}"
            ])->withInput();
        }

        // Calcular monto pendiente
        $montoPendiente = $montoTotal - $montoAbonado;
        
        // Determinar cantidad de cuotas
        $cantidadCuotas = $es_pago_simple ? 1 : ($validated['cantidad_cuotas'] ?? 1);
        $montoCuota = $montoAbonado / $cantidadCuotas;

        // Actualizar pago
        $pago->update([
            'id_inscripcion' => $validated['id_inscripcion'],
            'monto_total' => $montoTotal,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => $montoPendiente,
            'cantidad_cuotas' => $cantidadCuotas,
            'numero_cuota' => 1,
            'monto_cuota' => $montoCuota,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
            'referencia_pago' => $validated['referencia_pago'],
            'observaciones' => $validated['observaciones'],
            'id_estado' => $montoAbonado >= $montoTotal ? 102 : 103,
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
