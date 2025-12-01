<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    /**
     * Validar token de formulario para prevenir envío duplicado
     */
    private function validateFormToken(Request $request, string $action): bool
    {
        $token = $request->input('form_submit_token');
        
        if (!$token) {
            return false;
        }
        
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_submit_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        if (Cache::has($cacheKey)) {
            return false;
        }
        
        Cache::put($cacheKey, true, 10);
        
        return true;
    }

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
        // Obtener todas las inscripciones con saldo pendiente (sin importar estado)
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
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

            if ($montoAbonado <= 0 || $montoAbonado > $montoPendiente) {
                return back()->withErrors([
                    'monto_abonado' => "El monto debe ser entre 0 y {$montoPendiente} (saldo pendiente)"
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

        // Obtener IDs de estados correctos (por codigo, no id)
        $estadoPagado = Estado::where('codigo', 201)->firstOrFail();
        $estadoParcial = Estado::where('codigo', 202)->firstOrFail();
        $nuevoSaldoPendiente = $montoPendiente - $montoAbonado;
        $idEstado = $nuevoSaldoPendiente <= 0 ? $estadoPagado->id : $estadoParcial->id;

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

        // Determinar estado automáticamente según monto
        $estadoPagado = Estado::where('codigo', 201)->firstOrFail(); // Pagado
        $estadoParcial = Estado::where('codigo', 202)->firstOrFail(); // Parcial
        $nuevoIdEstado = $montoAbonado >= $montoTotal ? $estadoPagado->id : $estadoParcial->id;

        // Actualizar pago con todos los campos
        $pago->update([
            'id_inscripcion' => $validated['id_inscripcion'],
            'id_cliente' => $inscripcion->id_cliente,
            'id_membresia' => $inscripcion->id_membresia,
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
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();

        return redirect()->route('admin.pagos.index')
            ->with('success', 'Pago eliminado exitosamente');
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
