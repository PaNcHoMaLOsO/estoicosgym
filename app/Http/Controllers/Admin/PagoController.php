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
        $camposValidos = ['id', 'id_inscripcion', 'monto_abonado', 'fecha_pago', 'id_metodo_pago_principal', 'id_estado', 'created_at'];
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
        $inscripcion = null;
        
        // Si viene desde inscripción.show, cargar esa inscripción específica
        if ($request->filled('id_inscripcion')) {
            $inscripcion = Inscripcion::with('cliente', 'membresia')->find($request->id_inscripcion);
        }
        // Si no viene con id_inscripcion, la vista mostrará el select2 para buscar
        
        $metodos_pago = MetodoPago::all();
        return view('admin.pagos.create', compact('inscripcion', 'metodos_pago'));
    }

    /**
     * Store a newly created resource in storage.
     * Soporta pagos parciales, múltiples cuotas y validación dinámica de estado
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
            'numero_cuota' => 'nullable|integer|min:1',
            'fecha_vencimiento_cuota' => 'nullable|date',
            'referencia_pago' => 'nullable|string|max:100|unique:pagos,referencia_pago',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $inscripcion = Inscripcion::find($validated['id_inscripcion']);
        
        // Verificar que la inscripción exista
        if (!$inscripcion) {
            return back()->withErrors([
                'id_inscripcion' => 'La inscripción especificada no existe'
            ])->withInput();
        }

        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;

        // VALIDACIONES COMPREHENSIVAS
        
        // 1. Verificar que la inscripción esté activa
        if ($inscripcion->id_estado != 1) {
            return back()->withErrors([
                'id_inscripcion' => "La inscripción no está activa (Estado: {$inscripcion->estado->nombre})"
            ])->withInput();
        }

        // 2. Validar que número de cuota no sea mayor que cantidad de cuotas
        if ($validated['numero_cuota'] && $validated['cantidad_cuotas'] && $validated['numero_cuota'] > $validated['cantidad_cuotas']) {
            return back()->withErrors([
                'numero_cuota' => 'El número de cuota no puede ser mayor que la cantidad total de cuotas'
            ])->withInput();
        }

        // 3. Validar que monto abonado no sea mayor que monto total
        if ($validated['monto_abonado'] > $montoTotal) {
            return back()->withErrors([
                'monto_abonado' => "El monto abonado no puede exceder el total ({$montoTotal})"
            ])->withInput();
        }

        // 4. Validar que no se duplique referencia_pago
        if ($validated['referencia_pago']) {
            $existente = Pago::where('referencia_pago', $validated['referencia_pago'])
                ->where('id_metodo_pago_principal', $validated['id_metodo_pago_principal'])
                ->exists();
            
            if ($existente) {
                return back()->withErrors([
                    'referencia_pago' => 'Ya existe un pago con esta referencia para este método'
                ])->withInput();
            }
        }

        // 5. Validar coherencia de fechas
        if ($validated['fecha_vencimiento_cuota']) {
            $fechaVencimientoCuota = \Carbon\Carbon::parse($validated['fecha_vencimiento_cuota']);
            if ($fechaVencimientoCuota->isBefore(now())) {
                return back()->withErrors([
                    'fecha_vencimiento_cuota' => 'La fecha de vencimiento no puede ser en el pasado'
                ])->withInput();
            }
        }

        // Generar grupo_pago para cuotas relacionadas
        $grupoPago = $validated['cantidad_cuotas'] > 1 ? \Illuminate\Support\Str::uuid() : null;

        // Calcular monto de cuota
        $montoCuota = $validated['monto_abonado'] / $validated['cantidad_cuotas'];
        $montoPendiente = $montoTotal - $validated['monto_abonado'];

        $pago = Pago::create([
            'grupo_pago' => $grupoPago,
            'id_inscripcion' => $validated['id_inscripcion'],
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => $montoPendiente,
            'id_motivo_descuento' => $inscripcion->id_motivo_descuento,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
            'referencia_pago' => $validated['referencia_pago'],
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'numero_cuota' => $validated['numero_cuota'],
            'monto_cuota' => $montoCuota,
            'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
            'id_estado' => 101, // Pendiente (será actualizado dinámicamente)
            'observaciones' => $validated['observaciones'],
        ]);

        // Actualizar estado dinámicamente
        $pago->id_estado = $pago->calculateEstadoDinamico();
        $pago->save();

        // Registrar en auditoría
        \Log::info("Pago registrado: ID={$pago->id}, Cuota {$validated['numero_cuota']}/{$validated['cantidad_cuotas']}, Monto=\${$validated['monto_abonado']}, Usuario=" . (auth()->user()?->name ?? 'Sistema'));

        return redirect()->route('admin.pagos.index')
            ->with('success', "Pago registrado exitosamente (Cuota {$validated['numero_cuota']} de {$validated['cantidad_cuotas']})");
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
     * Actualiza pagos con soporte para cuotas y validación dinámica de estado
     */
    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
            'numero_cuota' => 'nullable|integer|min:1',
            'fecha_vencimiento_cuota' => 'nullable|date',
            'referencia_pago' => 'nullable|string|max:100|unique:pagos,referencia_pago,' . $pago->id,
            'observaciones' => 'nullable|string|max:500',
        ]);
        $inscripcion = Inscripcion::find($validated['id_inscripcion']);

        // VALIDACIONES COMPREHENSIVAS

        // 1. Verificar que la inscripción exista y sea válida
        if (!$inscripcion) {
            return back()->withErrors([
                'id_inscripcion' => 'La inscripción especificada no existe'
            ])->withInput();
        }

        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;

        // 2. Validar que número de cuota no sea mayor que cantidad de cuotas
        if ($validated['numero_cuota'] && $validated['cantidad_cuotas'] && $validated['numero_cuota'] > $validated['cantidad_cuotas']) {
            return back()->withErrors([
                'numero_cuota' => 'El número de cuota no puede ser mayor que la cantidad total de cuotas'
            ])->withInput();
        }

        // 3. Validar que monto abonado no sea mayor que monto total
        if ($validated['monto_abonado'] > $montoTotal) {
            return back()->withErrors([
                'monto_abonado' => "El monto abonado no puede exceder el total ({$montoTotal})"
            ])->withInput();
        }

        // 4. Validar que referencia_pago sea única (excepto para este pago)
        if ($validated['referencia_pago'] && $validated['referencia_pago'] !== $pago->referencia_pago) {
            $existente = Pago::where('referencia_pago', $validated['referencia_pago'])
                ->where('id_metodo_pago_principal', $validated['id_metodo_pago_principal'])
                ->where('id', '!=', $pago->id)
                ->exists();
            
            if ($existente) {
                return back()->withErrors([
                    'referencia_pago' => 'Ya existe otro pago con esta referencia para este método'
                ])->withInput();
            }
        }

        // 5. Validar coherencia de fechas
        if ($validated['fecha_vencimiento_cuota']) {
            $fechaVencimientoCuota = \Carbon\Carbon::parse($validated['fecha_vencimiento_cuota']);
            if ($fechaVencimientoCuota->isBefore(now())) {
                return back()->withErrors([
                    'fecha_vencimiento_cuota' => 'La fecha de vencimiento no puede ser en el pasado'
                ])->withInput();
            }
        }

        $montoCuota = $validated['monto_abonado'] / $validated['cantidad_cuotas'];
        $montoPendiente = $montoTotal - $validated['monto_abonado'];

        $pago->update([
            'id_inscripcion' => $validated['id_inscripcion'],
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => $montoPendiente,
            'id_motivo_descuento' => $inscripcion->id_motivo_descuento,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
            'referencia_pago' => $validated['referencia_pago'],
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'numero_cuota' => $validated['numero_cuota'],
            'monto_cuota' => $montoCuota,
            'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
            'observaciones' => $validated['observaciones'],
        ]);

        // Actualizar estado dinámicamente
        $pago->id_estado = $pago->calculateEstadoDinamico();
        $pago->save();

        // Registrar en auditoría
        \Log::info("Pago actualizado: ID={$pago->id}, Cuota {$validated['numero_cuota']}/{$validated['cantidad_cuotas']}, Monto=\${$validated['monto_abonado']}, Usuario=" . (auth()->user()?->name ?? 'Sistema'));

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
