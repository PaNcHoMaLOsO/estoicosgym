<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Estado;
use App\Http\Controllers\Traits\ValidatesFormToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    use ValidatesFormToken;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pago::with(['cliente', 'inscripcion.cliente', 'inscripcion.membresia', 'metodoPago', 'estado']);
        
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
        $estados = Estado::where('categoria', 'pago')->get();
        
        return view('admin.pagos.index', compact('pagos', 'metodos_pago', 'estados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Obtener inscripciones con saldo pendiente y que no estén finalizadas
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->whereNotIn('id_estado', EstadosCodigo::INSCRIPCION_FINALIZADOS)
            ->whereHas('cliente', function($q) {
                $q->where('activo', true); // Solo clientes activos
            })
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function($insc) {
                $total = $insc->precio_final ?? $insc->precio_base;
                $pagos = $insc->pagos()->sum('monto_abonado');
                return $total > $pagos; // Solo mostrar si hay saldo pendiente
            })
            ->values();
        
        $metodos_pago = MetodoPago::where('activo', true)->get();
        
        // Capturar parámetro de inscripción pre-seleccionada (desde botón "Nuevo Pago" en show)
        $inscripcion_id_preselect = $request->query('inscripcion_id');
        
        return view('admin.pagos.create', compact('inscripciones', 'metodos_pago', 'inscripcion_id_preselect'));
    }

    /**
     * Store a newly created resource in storage.
     * Soporta tres modos: abono parcial, pago completo, pago mixto
     */
    public function store(Request $request)
    {
        if (!$this->validateFormToken($request, 'pago_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // VALIDACIÓN: Verificar estado de inscripción antes de crear pago
        $inscripcionCheck = Inscripcion::find($request->input('id_inscripcion'));
        if ($inscripcionCheck) {
            // No permitir pagos en inscripciones finalizadas (Cancelada, Cambiada, Traspasada)
            if (in_array($inscripcionCheck->id_estado, EstadosCodigo::INSCRIPCION_FINALIZADOS)) {
                $estadoNombre = EstadosCodigo::getNombre($inscripcionCheck->id_estado);
                return back()->with('error', "No se puede registrar pago para una inscripción con estado '{$estadoNombre}'.");
            }
            // Verificar que el cliente esté activo
            if ($inscripcionCheck->cliente && !$inscripcionCheck->cliente->activo) {
                return back()->with('error', 'No se puede registrar pago para un cliente inactivo.');
            }
        }

        $tipoPago = $request->input('tipo_pago', 'abono');
        
        // Validaciones base
        $baseRules = [
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'tipo_pago' => 'required|in:abono,completo,mixto',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
        ];
        
        // Método de pago requerido solo para abono y completo
        if ($tipoPago !== 'mixto') {
            $baseRules['id_metodo_pago'] = 'required|exists:metodos_pago,id';
        } else {
            // Para mixto, validar los dos métodos
            $baseRules['id_metodo_pago1'] = 'required|exists:metodos_pago,id';
            $baseRules['id_metodo_pago2'] = 'required|exists:metodos_pago,id|different:id_metodo_pago1';
            $baseRules['monto_metodo1'] = 'required|integer|min:1';
            $baseRules['monto_metodo2'] = 'required|integer|min:1';
        }
        
        $validated = $request->validate($baseRules);

        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;

        // Validar que hay saldo pendiente
        $montoPagado = $inscripcion->pagos()->sum('monto_abonado');
        if ($montoPagado >= $montoTotal) {
            return back()->withErrors([
                'id_inscripcion' => "Esta inscripción ya está pagada completamente"
            ])->withInput();
        }

        $montoAbonado = 0;
        $montoPendiente = $montoTotal - $montoPagado;

        // ABONO PARCIAL
        if ($tipoPago === 'abono') {
            $request->validate([
                'monto_abonado' => 'required|integer|min:1000|max:' . intval($montoPendiente),
            ]);

            $montoAbonado = $request->input('monto_abonado');

            if ($montoAbonado < 1000 || $montoAbonado > $montoPendiente) {
                return back()->withErrors([
                    'monto_abonado' => "El monto debe ser entre $1.000 y $" . number_format($montoPendiente, 0, ',', '.') . " (saldo pendiente)"
                ])->withInput();
            }
        }
        // PAGO COMPLETO
        else if ($tipoPago === 'completo') {
            // Validación ya hecha arriba

            $montoAbonado = $montoPendiente;
        }
        // PAGO MIXTO
        else if ($tipoPago === 'mixto') {
            $monto1 = intval($request->input('monto_metodo1', 0));
            $monto2 = intval($request->input('monto_metodo2', 0));
            $montoAbonado = $monto1 + $monto2;

            if ($montoAbonado != intval($montoPendiente)) {
                return back()->withErrors([
                    'monto_metodo1' => "La suma de los montos debe ser exactamente " . number_format($montoPendiente, 0, ',', '.') . " (saldo pendiente)"
                ])->withInput();
            }
            
            // Validar que los métodos sean diferentes (respaldo servidor)
            if ($validated['id_metodo_pago1'] == $validated['id_metodo_pago2']) {
                return back()->withErrors([
                    'id_metodo_pago2' => "Los métodos de pago deben ser diferentes"
                ])->withInput();
            }
        }

        $cantidadCuotas = $validated['cantidad_cuotas'] ?? 1;
        $montoCuota = $montoAbonado / $cantidadCuotas;

        // Obtener códigos de estados (la FK referencia a codigo, no a id)
        $nuevoSaldoPendiente = $montoPendiente - $montoAbonado;
        $idEstado = $nuevoSaldoPendiente <= 0 ? 201 : 202; // 201=Pagado, 202=Parcial

        // Crear pago
        $idCliente = $inscripcion->id_cliente;
        $montoTotal = $montoAbonado + $nuevoSaldoPendiente;
        
        // Preparar datos del pago
        $datosPago = [
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $idCliente,
            'monto_total' => $montoTotal,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => $nuevoSaldoPendiente,
            'cantidad_cuotas' => $cantidadCuotas,
            'numero_cuota' => 1,
            'monto_cuota' => $montoCuota,
            'fecha_pago' => $validated['fecha_pago'],
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
            'tipo_pago' => $tipoPago,
            'referencia_pago' => $validated['referencia_pago'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'id_estado' => $idEstado,
        ];
        
        // Agregar campos según tipo de pago
        if ($tipoPago === 'mixto') {
            $datosPago['id_metodo_pago'] = $validated['id_metodo_pago1'];
            $datosPago['id_metodo_pago2'] = $validated['id_metodo_pago2'];
            $datosPago['monto_metodo1'] = $request->input('monto_metodo1');
            $datosPago['monto_metodo2'] = $request->input('monto_metodo2');
        } else {
            $datosPago['id_metodo_pago'] = $validated['id_metodo_pago'];
        }
        
        $pago = Pago::create($datosPago);

        return redirect()->route('admin.pagos.show', $pago->uuid)
            ->with('success', "Pago registrado exitosamente ({$tipoPago}). Verifica los detalles abajo.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load([
            'cliente', // Cliente original que pagó
            'inscripcion.cliente', // Cliente actual de la inscripción (puede ser diferente si hubo traspaso)
            'inscripcion.membresia',
            'inscripcion.estado',
            'inscripcion.pagos.estado',
            'inscripcion.pagos.metodoPago',
            'inscripcion.pagos.metodoPago2',
            'metodoPago',
            'metodoPago2',
            'estado'
        ]);
        return view('admin.pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        // Cargar todas las relaciones necesarias
        $pago->load([
            'inscripcion.cliente',
            'inscripcion.membresia',
            'metodoPago',
            'estado'
        ]);

        $metodos_pago = MetodoPago::where('activo', true)->get();
        return view('admin.pagos.edit', compact('pago', 'metodos_pago'));
    }

    /**
     * Update the specified resource in storage.
     * Permite editar todos los detalles del pago incluyendo monto, fecha, método y estado
     */
    public function update(Request $request, Pago $pago)
    {
        if (!$this->validateFormToken($request, 'pago_update_' . $pago->id)) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|integer|min:1|max:999999999',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'id_metodo_pago' => 'required|exists:metodos_pago,id',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Obtener inscripción y validar
        $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);
        $montoTotal = $inscripcion->precio_final ?? $inscripcion->precio_base;
        $montoAbonado = intval($validated['monto_abonado']);

        // Validación de monto
        if ($montoAbonado > $montoTotal) {
            return back()->withErrors([
                'monto_abonado' => "El monto no puede exceder $" . number_format($montoTotal, 0, ',', '.') . " (precio de membresía)"
            ])->withInput();
        }

        if ($montoAbonado <= 0) {
            return back()->withErrors([
                'monto_abonado' => "El monto debe ser mayor a 0"
            ])->withInput();
        }

        // Calcular montos
        $montoPendiente = $montoTotal - $montoAbonado;
        $cantidadCuotas = $validated['cantidad_cuotas'] ?? 1;
        $montoCuota = $montoAbonado / $cantidadCuotas;

        // Determinar estado automáticamente según monto (la FK referencia a codigo)
        $nuevoIdEstado = $montoAbonado >= $montoTotal ? 201 : 202; // 201=Pagado, 202=Parcial

        // Actualizar pago con todos los campos
        $pago->update([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $montoTotal,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => $montoPendiente,
            'cantidad_cuotas' => $cantidadCuotas,
            'monto_cuota' => $montoCuota,
            'fecha_pago' => $validated['fecha_pago'],
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'referencia_pago' => $validated['referencia_pago'],
            'observaciones' => $validated['observaciones'] ?? null,
            'id_estado' => $nuevoIdEstado,
        ]);

        // Recargar relaciones para mostrar datos actualizados
        $pago->refresh();

        return redirect()->route('admin.pagos.show', $pago)
            ->with('success', 'Pago actualizado exitosamente. El estado se asignó automáticamente: ' . $pago->estado->nombre);
    }

    /**
     * Remove the specified resource from storage.
     * Incluye validaciones para proteger la integridad de datos.
     */
    public function destroy(Pago $pago)
    {
        // Cargar inscripción relacionada
        $pago->load('inscripcion');
        
        // Validar que el pago no sea de una inscripción traspasada (estado 205 = Traspasado)
        if ($pago->id_estado == 205) {
            return redirect()->route('admin.pagos.show', $pago)
                ->with('error', 'No se puede eliminar un pago que fue traspasado. Este registro es parte del historial de traspaso.');
        }
        
        // Validar que no sea el único pago de una inscripción activa
        if ($pago->inscripcion && $pago->inscripcion->id_estado == 100) { // Inscripción Activa
            $totalPagos = $pago->inscripcion->pagos()->count();
            if ($totalPagos <= 1) {
                return redirect()->route('admin.pagos.show', $pago)
                    ->with('error', 'No se puede eliminar el único pago de una inscripción activa. Esto dejaría la inscripción sin registro de pago.');
            }
        }
        
        // Guardar info para el mensaje
        $inscripcionId = $pago->id_inscripcion;
        $montoEliminado = $pago->monto_abonado;
        $inscripcion = $pago->inscripcion;
        
        // Usar transacción para asegurar consistencia
        DB::transaction(function() use ($pago, $inscripcion) {
            $pago->delete();
            
            // Recalcular estado de los pagos restantes de la inscripción
            if ($inscripcion) {
                $pagosRestantes = $inscripcion->pagos()->get();
                $totalAbonado = $pagosRestantes->sum('monto_abonado');
                $precioFinal = $inscripcion->precio_final;
                
                // Actualizar monto_pendiente en cada pago restante
                foreach ($pagosRestantes as $pagoRestante) {
                    $nuevoPendiente = max(0, $precioFinal - $totalAbonado);
                    
                    // Determinar nuevo estado del pago
                    if ($totalAbonado >= $precioFinal) {
                        $nuevoEstado = 201; // Pagado
                        $nuevoPendiente = 0;
                    } elseif ($totalAbonado > 0) {
                        $nuevoEstado = 202; // Parcial
                    } else {
                        $nuevoEstado = 200; // Pendiente
                    }
                    
                    $pagoRestante->update([
                        'monto_pendiente' => $nuevoPendiente,
                        'id_estado' => $nuevoEstado,
                    ]);
                }
            }
        });

        return redirect()->route('admin.pagos.index')
            ->with('success', "Pago de \${$montoEliminado} eliminado exitosamente. Inscripción #{$inscripcionId}. Los pagos restantes han sido actualizados.");
    }

    /**
     * Obtener historial de pagos de una inscripción (API)
     */
    public function historial($id)
    {
        $pagos = Pago::where('id_inscripcion', $id)
            ->with('metodoPagoPrincipal')
            ->orderBy('fecha_pago', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['pagos' => $pagos]);
    }
}
