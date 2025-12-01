<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Membresia;
use App\Models\Convenio;
use App\Models\MotivoDescuento;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\HistorialTraspaso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscripcionController extends Controller
{
    // Campos permitidos para ordenamiento
    protected $camposValidos = [
        'id', 'id_cliente', 'id_membresia', 'id_estado', 
        'fecha_inicio', 'fecha_vencimiento', 'precio_base', 
        'precio_final', 'created_at'
    ];

    /**
     * Validar token de formulario para prevenir envíos duplicados
     *
     * @param \Illuminate\Http\Request $request
     * @param string $action
     * @return bool
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
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Inscripcion::with(['cliente', 'estado', 'membresia']);
        
        // Aplicar filtros
        $this->aplicarFiltros($query, $request);
        
        // Aplicar ordenamiento
        $this->aplicarOrdenamiento($query, $request);
        
        $inscripciones = $query->paginate(20);
        
        // Datos para los selects de filtro
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        
        return view('admin.inscripciones.index', compact('inscripciones', 'estados', 'membresias'));
    }

    /**
     * Aplicar filtros a la query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function aplicarFiltros($query, Request $request)
    {
        // Filtro por cliente (nombres, apellido, email)
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $busqueda = '%' . $request->cliente . '%';
                $q->where('nombres', 'like', $busqueda)
                  ->orWhere('apellido_paterno', 'like', $busqueda)
                  ->orWhere('email', 'like', $busqueda);
            });
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('id_estado', $request->estado);
        }
        
        // Filtro por membresía
        if ($request->filled('membresia')) {
            $query->where('id_membresia', $request->membresia);
        }
        
        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_inicio', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }
    }

    /**
     * Aplicar ordenamiento a la query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function aplicarOrdenamiento($query, Request $request)
    {
        $ordenar = $request->get('ordenar', 'fecha_inicio');
        $direccion = $request->get('direccion', 'desc');
        
        // Validar que el campo sea válido
        if (!in_array($ordenar, $this->camposValidos)) {
            $ordenar = 'fecha_inicio';
        }
        
        // Validar dirección
        if (!in_array($direccion, ['asc', 'desc'])) {
            $direccion = 'desc';
        }
        
        $query->orderBy($ordenar, $direccion);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Clientes que pueden tener una nueva inscripción:
        // - Clientes activos
        // - Se carga con inscripciones para filtrar en la vista
        
        $clientes = Cliente::where('activo', true)
            ->with(['inscripciones' => function($q) {
                $q->orderBy('fecha_vencimiento', 'desc');
            }])
            ->orderBy('nombres')
            ->get();

        $estados = Estado::where('categoria', 'membresia')->get();
        $estadoActiva = Estado::where('codigo', 100)->first(); // Estado "Activa"
        $membresias = Membresia::with('precios')->where('activo', true)->get();
        $convenios = Convenio::where('activo', true)->get();
        $motivos = MotivoDescuento::where('activo', true)->get();
        $metodosPago = MetodoPago::where('activo', true)->get();

        return view('admin.inscripciones.create', compact('clientes', 'estados', 'estadoActiva', 'membresias', 'convenios', 'motivos', 'metodosPago'));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!$this->validateFormToken($request, 'inscripcion_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // Verificar tipo de pago
        $tipoPago = $request->input('tipo_pago', 'completo');
        $pagoPendiente = $tipoPago === 'pendiente';
        $pagoMixto = $tipoPago === 'mixto';

        // Validación base
        $rules = [
            'id_cliente' => 'required|exists:clientes,id',
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_estado' => 'required|exists:estados,codigo',
            'fecha_inicio' => 'required|date',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
            'tipo_pago' => 'required|in:completo,abono,mixto,pendiente',
        ];

        // Agregar reglas según tipo de pago
        if ($pagoMixto) {
            $rules['detalle_pagos_mixto'] = 'required|string';
            $rules['total_mixto'] = 'required|numeric|min:1';
            $rules['fecha_pago'] = 'required|date';
        } elseif (!$pagoPendiente) {
            $rules['monto_abonado'] = 'required|numeric|min:1';
            $rules['id_metodo_pago'] = 'required|exists:metodos_pago,id';
            $rules['fecha_pago'] = 'required|date';
        }

        $validated = $request->validate($rules);

        // Obtener datos de membresía y calcular precios
        $membresia = Membresia::findOrFail($validated['id_membresia']);
        $precioBase = $this->obtenerPrecioMembresia($membresia, $validated);
        $descuentoTotal = $this->calcularDescuentoTotal($membresia, $validated, $precioBase);
        $precioFinal = max(0, $precioBase - $descuentoTotal);

        // Calcular fecha de vencimiento
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaVencimiento = $this->calcularFechaVencimiento($fechaInicio, $membresia);

        // Crear inscripción con datos validados y calculados
        $validated['precio_base'] = $precioBase;
        $validated['precio_final'] = $precioFinal;
        $validated['descuento_aplicado'] = $descuentoTotal;
        $validated['fecha_inscripcion'] = now()->format('Y-m-d');
        $validated['fecha_vencimiento'] = $fechaVencimiento->format('Y-m-d');
        $validated['id_precio_acordado'] = 1;

        $inscripcion = Inscripcion::create($validated);

        // Crear pago(s) según tipo de pago
        $tipoPago = $validated['tipo_pago'] ?? 'completo';
        
        if ($tipoPago === 'mixto') {
            // Pago mixto: crear dos pagos con diferentes métodos
            $this->crearPagoMixto($inscripcion, $validated, $precioFinal);
        } elseif (!$pagoPendiente && isset($validated['monto_abonado']) && $validated['monto_abonado'] > 0) {
            $this->crearPagoInicial($inscripcion, $validated, $precioFinal);
        }

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción creada exitosamente' . ($pagoPendiente ? ' - Pago pendiente de registrar' : ' con pago registrado'));
    }

    /**
     * Obtener el precio vigente de la membresía
     *
     * @param \App\Models\Membresia $membresia
     * @param array $validated
     * @return float
     */
    protected function obtenerPrecioMembresia(Membresia $membresia, array $validated)
    {
        $precioMembresia = $membresia->precios()
            ->where('activo', true)
            ->where('fecha_vigencia_desde', '<=', now())
            ->orderBy('fecha_vigencia_desde', 'desc')
            ->first();
        
        return $precioMembresia->precio_normal ?? 0;
    }

    /**
     * Calcular el descuento total (convenio + adicional)
     *
     * @param \App\Models\Membresia $membresia
     * @param array $validated
     * @param float $precioBase
     * @return float
     */
    protected function calcularDescuentoTotal(Membresia $membresia, array $validated, float $precioBase)
    {
        $descuentoConvenio = 0;
        
        // Descuento automático del convenio (si tiene precio_convenio definido)
        if (!empty($validated['id_convenio'])) {
            $precioMembresia = $membresia->precios()
                ->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderBy('fecha_vigencia_desde', 'desc')
                ->first();
            
            if ($precioMembresia && $precioMembresia->precio_convenio) {
                $descuentoConvenio = $precioBase - $precioMembresia->precio_convenio;
            }
        }
        
        $descuentoAdicional = (float) ($validated['descuento_aplicado'] ?? 0);
        return max(0, $descuentoConvenio + $descuentoAdicional);
    }

    /**
     * Calcular la fecha de vencimiento según duración de membresía
     *
     * @param \Carbon\Carbon $fechaInicio
     * @param \App\Models\Membresia $membresia
     * @return \Carbon\Carbon
     */
    protected function calcularFechaVencimiento(Carbon $fechaInicio, Membresia $membresia)
    {
        if ($membresia->duracion_dias && $membresia->duracion_dias > 0) {
            return $fechaInicio->clone()->addDays($membresia->duracion_dias)->subDay();
        }
        
        $duracionMeses = $membresia->duracion_meses ?? 1;
        return $fechaInicio->clone()->addMonths($duracionMeses)->subDay();
    }

    /**
     * Crear pago inicial para la inscripción
     *
     * @param \App\Models\Inscripcion $inscripcion
     * @param array $validated
     * @param float $precioFinal
     * @return void
     */
    protected function crearPagoInicial(Inscripcion $inscripcion, array $validated, float $precioFinal)
    {
        // Ya NO hay cuotas - Los abonos se irán acumulando en la tabla pagos
        $montoAbonado = $validated['monto_abonado'];
        // Estados de PAGO: 201=Pagado, 202=Parcial (NO confundir con estados de inscripción 102/103)
        $idEstadoPago = $montoAbonado >= $precioFinal ? 201 : 202;

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $validated['id_cliente'],
            'monto_total' => $precioFinal,
            'monto_abonado' => $montoAbonado,
            'monto_pendiente' => max(0, $precioFinal - $montoAbonado),
            'id_estado' => $idEstadoPago,
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'fecha_pago' => $validated['fecha_pago'],
            'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
            'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
        ]);
    }

    /**
     * Crear pagos mixtos (múltiples métodos de pago)
     *
     * @param \App\Models\Inscripcion $inscripcion
     * @param array $validated
     * @param float $precioFinal
     * @return void
     */
    protected function crearPagoMixto(Inscripcion $inscripcion, array $validated, float $precioFinal)
    {
        // El detalle viene como JSON desde el formulario
        $detallePagos = json_decode($validated['detalle_pagos_mixto'] ?? '[]', true);
        
        if (empty($detallePagos)) {
            return;
        }

        $montoTotalAbonado = 0;
        foreach ($detallePagos as $detalle) {
            $montoTotalAbonado += (float) ($detalle['monto'] ?? 0);
        }
        
        // Estados de PAGO: 201=Pagado, 202=Parcial (NO confundir con estados de inscripción 102/103)
        $idEstadoPago = $montoTotalAbonado >= $precioFinal ? 201 : 202;
        $montoPendienteRestante = $precioFinal;

        foreach ($detallePagos as $index => $detalle) {
            $monto = (float) ($detalle['monto'] ?? 0);
            $idMetodo = $detalle['id_metodo_pago'] ?? null;
            $metodoNombre = $detalle['metodo_nombre'] ?? 'Método ' . ($index + 1);
            
            if ($monto > 0 && $idMetodo) {
                $montoPendienteRestante -= $monto;
                
                Pago::create([
                    'id_inscripcion' => $inscripcion->id,
                    'id_cliente' => $validated['id_cliente'],
                    'monto_total' => $precioFinal,
                    'monto_abonado' => $monto,
                    'monto_pendiente' => max(0, $montoPendienteRestante),
                    'id_estado' => $idEstadoPago,
                    'id_metodo_pago' => $idMetodo,
                    'fecha_pago' => $validated['fecha_pago'],
                    'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
                    'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                    'observacion' => 'Pago mixto - ' . $metodoNombre,
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\View\View
     */
    public function show(Inscripcion $inscripcion)
    {
        $inscripcion->load([
            'cliente',
            'estado',
            'membresia',
            'convenio',
            'motivoDescuento',
            'pagos.metodoPago',
            'pagos.estado',
            // Relaciones de cambio de plan
            'inscripcionAnterior.membresia',
            'inscripcionesPosteriores.membresia',
        ]);
        $estadoPago = $inscripcion->obtenerEstadoPago();
        return view('admin.inscripciones.show', compact('inscripcion', 'estadoPago'));
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\View\View
     */
    public function edit(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'estado', 'membresia', 'convenio', 'motivoDescuento', 'pagos']);
        $clientes = Cliente::active()->get();
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
        $metodosPago = MetodoPago::where('activo', true)->get();
        
        // Obtener información de traspaso para la sección de traspaso
        $infoTraspaso = $inscripcion->getInfoTraspaso();
        
        return view('admin.inscripciones.edit', compact('inscripcion', 'clientes', 'estados', 'membresias', 'convenios', 'motivos', 'metodosPago', 'infoTraspaso'));
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Inscripcion $inscripcion)
    {
        if (!$this->validateFormToken($request, 'inscripcion_update_' . $inscripcion->id)) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

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

        // Calcular precio_final: precio_base - descuento
        $validated['precio_final'] = $validated['precio_base'] - ($validated['descuento_aplicado'] ?? 0);

        $inscripcion->update($validated);

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Inscripcion $inscripcion)
    {
        $inscripcion->delete();

        return redirect()->route('admin.inscripciones.index')
            ->with('success', 'Inscripción eliminada exitosamente');
    }

    /**
     * Pausar una membresía
     * POST /admin/inscripciones/{inscripcion}/pausar
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\JsonResponse
     */
    public function pausar(Request $request, Inscripcion $inscripcion)
    {
        $indefinida = $request->boolean('indefinida', false);
        
        $rules = [
            'razon' => 'nullable|string|max:500',
            'indefinida' => 'nullable|boolean',
        ];
        
        // Si no es indefinida, días es requerido
        if (!$indefinida) {
            $rules['dias'] = 'required|in:7,14,30';
        } else {
            // Para pausa indefinida, la razón es obligatoria
            $rules['razon'] = 'required|string|min:5|max:500';
        }

        try {
            $validated = $request->validate($rules);
            
            $inscripcion->load(['cliente', 'estado']);

            // Verificar que pueda pausarse
            if (!$inscripcion->puedeRealizarPausa()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta membresía no puede ser pausada. Verifique el estado y las pausas disponibles.',
                ], 422);
            }

            $dias = $indefinida ? null : (int) $validated['dias'];
            $inscripcion->pausar($dias, $validated['razon'] ?? '', $indefinida);

            return response()->json([
                'success' => true,
                'message' => $indefinida 
                    ? 'Membresía pausada indefinidamente' 
                    : "Membresía pausada por {$dias} días",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al pausar inscripción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la pausa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reanudar una membresía pausada
     * POST /admin/inscripciones/{inscripcion}/reanudar
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\JsonResponse
     */
    public function reanudar(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load(['cliente', 'estado']);

            if (!$inscripcion->pausada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta membresía no está pausada',
                ], 422);
            }

            // Calcular días que estuvo pausada
            $diasEnPausa = $inscripcion->fecha_pausa_inicio 
                ? $inscripcion->fecha_pausa_inicio->diffInDays(now()) 
                : 0;

            $inscripcion->reanudar();

            return response()->json([
                'success' => true,
                'message' => "Membresía reanudada. Se agregaron {$diasEnPausa} días al vencimiento.",
            ]);
        } catch (\Exception $e) {
            Log::error('Error al reanudar inscripción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reanudar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // MEJORA DE PLAN (UPGRADE)
    // ============================================

    /**
     * Obtener información de precios para mejora de plan
     * GET /admin/inscripciones/{inscripcion}/info-cambio-plan
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\JsonResponse
     */
    public function infoCambioPlan(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load(['membresia', 'pagos']);
            
            $precioActual = (float) $inscripcion->precio_final;
            
            // Obtener solo membresías de MAYOR precio (upgrade)
            $membresias = Membresia::with(['precios' => function($q) {
                    $q->where('activo', true)
                      ->where('fecha_vigencia_desde', '<=', now())
                      ->orderBy('fecha_vigencia_desde', 'desc');
                }])
                ->where('activo', true)
                ->where('id', '!=', $inscripcion->id_membresia)
                ->get()
                ->map(function($membresia) {
                    $precioVigente = $membresia->precios->first();
                    return [
                        'id' => $membresia->id,
                        'nombre' => $membresia->nombre,
                        'descripcion' => $membresia->descripcion,
                        'duracion_dias' => $membresia->duracion_dias,
                        'duracion_meses' => $membresia->duracion_meses,
                        'precio' => $precioVigente ? (float) $precioVigente->precio_normal : 0,
                    ];
                })
                ->filter(function($membresia) use ($precioActual) {
                    // Solo mostrar planes de mayor precio (upgrade)
                    return $membresia['precio'] > $precioActual;
                })
                ->values();

            // Calcular el crédito disponible (lo que ya pagó)
            $creditoDisponible = $inscripcion->monto_pagado;

            return response()->json([
                'success' => true,
                'inscripcion' => [
                    'id' => $inscripcion->id,
                    'uuid' => $inscripcion->uuid,
                    'membresia_actual' => $inscripcion->membresia->nombre,
                    'precio_actual' => (float) $inscripcion->precio_final,
                    'monto_pagado' => $creditoDisponible,
                    'monto_pendiente' => (float) $inscripcion->monto_pendiente,
                    'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                ],
                'membresias_disponibles' => $membresias,
                'credito_disponible' => $creditoDisponible,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener info de cambio de plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ejecutar mejora de plan (upgrade)
     * POST /admin/inscripciones/{inscripcion}/cambiar-plan
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarPlan(Request $request, Inscripcion $inscripcion)
    {
        try {
            $validated = $request->validate([
                'id_membresia_nueva' => 'required|exists:membresias,id|different:id_membresia_actual',
                'motivo_cambio' => 'nullable|string|max:500',
                'id_metodo_pago' => 'required|exists:metodos_pago,id',
                'monto_abonado' => 'nullable|numeric|min:0',
                'aplicar_credito' => 'nullable|boolean', // El admin decide si aplica crédito
            ]);

            $inscripcion->load(['cliente', 'membresia', 'pagos']);

            // Verificar que puede cambiar de plan
            if (!$inscripcion->puedeCambiarPlan()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta inscripción no puede cambiar de plan. Verifique que esté activa y no pausada.',
                ], 422);
            }

            // Obtener la nueva membresía y su precio
            $nuevaMembresia = Membresia::with(['precios' => function($q) {
                $q->where('activo', true)
                  ->where('fecha_vigencia_desde', '<=', now())
                  ->orderBy('fecha_vigencia_desde', 'desc');
            }])->findOrFail($validated['id_membresia_nueva']);

            $precioNuevo = $nuevaMembresia->precios->first();
            if (!$precioNuevo) {
                return response()->json([
                    'success' => false,
                    'message' => 'La membresía seleccionada no tiene un precio vigente.',
                ], 422);
            }

            $precioNuevoPlan = (float) $precioNuevo->precio_normal;
            
            // Verificar que sea realmente un upgrade
            if ($precioNuevoPlan <= $inscripcion->precio_final) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se permiten mejoras de plan (planes de mayor precio).',
                ], 422);
            }
            
            // El admin decide si aplica el crédito del plan anterior
            $aplicarCredito = $request->boolean('aplicar_credito', false);
            $creditoDisponible = $aplicarCredito ? (float) $inscripcion->monto_pagado : 0;
            $diferencia = $precioNuevoPlan - $creditoDisponible;
            $tipoCambio = 'upgrade';

            // Calcular nueva fecha de vencimiento
            $fechaInicio = now();
            if ($nuevaMembresia->duracion_dias && $nuevaMembresia->duracion_dias > 0) {
                $fechaVencimiento = $fechaInicio->clone()->addDays($nuevaMembresia->duracion_dias)->subDay();
            } else {
                $duracionMeses = $nuevaMembresia->duracion_meses ?? 1;
                $fechaVencimiento = $fechaInicio->clone()->addMonths($duracionMeses)->subDay();
            }

            // Usar transacción para mantener integridad
            DB::beginTransaction();

            try {
                // 1. Marcar inscripción anterior como "Cambiada" (estado 105)
                $inscripcion->update([
                    'id_estado' => 105, // Estado: Cambiada a otro plan
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '') 
                        . "[" . now()->format('d/m/Y H:i') . "] Cambio de plan a: {$nuevaMembresia->nombre}",
                ]);

                // 2. Crear nueva inscripción con los datos del cambio
                $nuevaInscripcion = Inscripcion::create([
                    'id_cliente' => $inscripcion->id_cliente,
                    'id_membresia' => $nuevaMembresia->id,
                    'id_convenio' => $inscripcion->id_convenio, // Mantener convenio si tenía
                    'id_precio_acordado' => $precioNuevo->id,
                    'fecha_inscripcion' => now()->format('Y-m-d'),
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'precio_base' => $precioNuevoPlan,
                    'descuento_aplicado' => 0,
                    'precio_final' => $precioNuevoPlan,
                    'id_estado' => 100, // Activa
                    'observaciones' => "Cambio de plan desde: {$inscripcion->membresia->nombre}",
                    // Campos de tracking de cambio
                    'id_inscripcion_anterior' => $inscripcion->id,
                    'es_cambio_plan' => true,
                    'tipo_cambio' => $tipoCambio,
                    'credito_plan_anterior' => $creditoDisponible,
                    'precio_nuevo_plan' => $precioNuevoPlan,
                    'diferencia_a_pagar' => max(0, $diferencia),
                    'fecha_cambio_plan' => now(),
                    'motivo_cambio_plan' => $validated['motivo_cambio'] ?? null,
                ]);

                // 3. Si hay diferencia a favor del gym (upgrade), crear pago
                if ($diferencia > 0 && isset($validated['monto_abonado']) && $validated['monto_abonado'] > 0) {
                    $montoAbonado = min($validated['monto_abonado'], $diferencia);
                    // Estados de PAGO: 201=Pagado, 202=Parcial (NO confundir con estados de inscripción 102/103)
                    $estadoPago = $montoAbonado >= $diferencia ? 201 : 202;

                    Pago::create([
                        'id_inscripcion' => $nuevaInscripcion->id,
                        'id_cliente' => $inscripcion->id_cliente,
                        'monto_total' => $diferencia,
                        'monto_abonado' => $montoAbonado,
                        'monto_pendiente' => max(0, $diferencia - $montoAbonado),
                        'id_estado' => $estadoPago,
                        'id_metodo_pago' => $validated['id_metodo_pago'],
                        'fecha_pago' => now()->format('Y-m-d'),
                        'periodo_inicio' => $fechaInicio->format('Y-m-d'),
                        'periodo_fin' => $fechaVencimiento->format('Y-m-d'),
                    ]);
                }

                // Nota sobre el crédito aplicado
                $mensajeCredito = '';
                if ($aplicarCredito && $inscripcion->monto_pagado > 0) {
                    $mensajeCredito = " Se aplicó crédito de $" . number_format($inscripcion->monto_pagado, 0, ',', '.') . " del plan anterior.";
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Plan cambiado exitosamente de {$inscripcion->membresia->nombre} a {$nuevaMembresia->nombre}." . $mensajeCredito,
                    'nueva_inscripcion' => [
                        'uuid' => $nuevaInscripcion->uuid,
                        'membresia' => $nuevaMembresia->nombre,
                        'fecha_vencimiento' => $fechaVencimiento->format('d/m/Y'),
                        'tipo_cambio' => $tipoCambio,
                        'diferencia' => $diferencia,
                    ],
                    'redirect_url' => route('admin.inscripciones.show', $nuevaInscripcion),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al cambiar plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el cambio de plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // TRASPASO DE MEMBRESÍA
    // ============================================

    /**
     * Buscar clientes disponibles para recibir traspaso
     * GET /admin/inscripciones/{inscripcion}/buscar-clientes-traspaso
     */
    public function buscarClientesTraspaso(Request $request, Inscripcion $inscripcion)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'clientes' => [],
                ]);
            }

            // Buscar clientes que NO tienen membresía activa y NO son el cliente actual
            $clientesConMembresiaActiva = Inscripcion::whereIn('id_estado', [100, 101])
                ->where('fecha_vencimiento', '>=', now())
                ->pluck('id_cliente')
                ->toArray();

            // Incluir todos los clientes (activos e inactivos) que no tienen membresía activa
            $clientes = Cliente::where('id', '!=', $inscripcion->id_cliente)
                ->whereNotIn('id', $clientesConMembresiaActiva)
                ->where(function($q) use ($query) {
                    $q->where('nombres', 'LIKE', "%{$query}%")
                      ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                      ->orWhere('apellido_materno', 'LIKE', "%{$query}%")
                      ->orWhere('run_pasaporte', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('celular', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get()
                ->map(function($cliente) {
                    // Verificar si tiene membresías vencidas
                    $ultimaInscripcion = Inscripcion::where('id_cliente', $cliente->id)
                        ->orderBy('fecha_vencimiento', 'desc')
                        ->first();
                    
                    $estado = 'nuevo';
                    $ultimaMembresia = null;
                    
                    if ($ultimaInscripcion) {
                        $estado = 'vencido';
                        $ultimaMembresia = $ultimaInscripcion->membresia->nombre ?? 'N/A';
                    }
                    
                    return [
                        'id' => $cliente->id,
                        'nombre_completo' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
                        'rut' => $cliente->run_pasaporte,
                        'email' => $cliente->email,
                        'telefono' => $cliente->celular,
                        'estado' => $estado,
                        'ultima_membresia' => $ultimaMembresia,
                        'activo' => $cliente->activo,
                    ];
                });

            return response()->json([
                'success' => true,
                'clientes' => $clientes,
            ]);

        } catch (\Exception $e) {
            Log::error('Error buscando clientes para traspaso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ejecutar traspaso de membresía
     * POST /admin/inscripciones/{inscripcion}/traspasar
     */
    public function traspasar(Request $request, Inscripcion $inscripcion)
    {
        Log::info('=== INICIO TRASPASAR ===', [
            'inscripcion_id' => $inscripcion->id,
            'inscripcion_uuid' => $inscripcion->uuid,
            'request_data' => $request->all()
        ]);
        
        try {
            $validated = $request->validate([
                'id_cliente_destino' => 'required|exists:clientes,id',
                'motivo_traspaso' => 'required|string|max:500',
                'ignorar_deuda' => 'nullable|boolean',
                'transferir_deuda' => 'nullable|boolean',
            ]);
            
            Log::info('Validación pasada', $validated);
            
            // Validar que no sea el mismo cliente
            if ($validated['id_cliente_destino'] == $inscripcion->id_cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes traspasar la membresía al mismo cliente.',
                ], 422);
            }

            $ignorarDeuda = $request->boolean('ignorar_deuda', false);
            $transferirDeuda = $request->boolean('transferir_deuda', false);

            // Verificar que la inscripción puede ser traspasada
            if (!$inscripcion->puedeTraspasarse($ignorarDeuda)) {
                $infoTraspaso = $inscripcion->getInfoTraspaso();
                
                if ($infoTraspaso['tiene_deuda']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta inscripción tiene una deuda pendiente de $' . number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') . '. Active la opción "Ignorar requisito de pago completo" si desea continuar.',
                        'tiene_deuda' => true,
                        'monto_pendiente' => $infoTraspaso['monto_pendiente'],
                    ], 422);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Esta inscripción no puede ser traspasada. Debe estar activa y tener días restantes.',
                ], 422);
            }

            // Verificar que el cliente destino puede recibir el traspaso
            $clienteDestino = Cliente::findOrFail($validated['id_cliente_destino']);
            
            if (!Inscripcion::clientePuedeRecibirTraspaso($clienteDestino->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente seleccionado ya tiene una membresía activa.',
                ], 422);
            }

            $inscripcion->load(['cliente', 'membresia', 'pagos']);
            $infoTraspaso = $inscripcion->getInfoTraspaso();

            DB::beginTransaction();

            try {
                // 1. Marcar inscripción original como "Traspasada" (estado 106)
                $observacionTraspaso = "[" . now()->format('d/m/Y H:i') . "] Traspasada a: {$clienteDestino->nombres} {$clienteDestino->apellido_paterno}";
                if ($infoTraspaso['tiene_deuda'] && $ignorarDeuda) {
                    $observacionTraspaso .= " | Deuda transferida: $" . number_format($infoTraspaso['monto_pendiente'], 0, ',', '.');
                }
                
                $inscripcion->update([
                    'id_estado' => 106, // Estado: Traspasada
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '') . $observacionTraspaso,
                ]);

                // 2. Crear nueva inscripción para el cliente destino
                $nuevaInscripcion = Inscripcion::create([
                    'id_cliente' => $clienteDestino->id,
                    'id_membresia' => $inscripcion->id_membresia,
                    'id_convenio' => null, // Los convenios no se traspasan
                    'id_precio_acordado' => $inscripcion->id_precio_acordado,
                    'fecha_inscripcion' => now()->format('Y-m-d'),
                    'fecha_inicio' => now()->format('Y-m-d'),
                    'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
                    'precio_base' => $inscripcion->precio_base,
                    'descuento_aplicado' => $inscripcion->descuento_aplicado ?? 0,
                    'precio_final' => $inscripcion->precio_final,
                    'id_estado' => 100, // Activa
                    'observaciones' => "Traspaso recibido de: {$inscripcion->cliente->nombres} {$inscripcion->cliente->apellido_paterno}",
                    // Campos de tracking de traspaso
                    'es_traspaso' => true,
                    'id_inscripcion_origen' => $inscripcion->id,
                    'id_cliente_original' => $inscripcion->id_cliente,
                    'fecha_traspaso' => now(),
                    'motivo_traspaso' => $validated['motivo_traspaso'],
                ]);

                // 3. Crear pago(s) para la nueva inscripción
                // Si se transfiere la deuda, creamos un pago con lo que se había abonado y otro pendiente
                if ($infoTraspaso['tiene_deuda'] && $transferirDeuda) {
                    // Pago por el monto ya abonado
                    if ($infoTraspaso['monto_pagado'] > 0) {
                        Pago::create([
                            'id_inscripcion' => $nuevaInscripcion->id,
                            'id_cliente' => $clienteDestino->id,
                            'monto_total' => $infoTraspaso['monto_total'],
                            'monto_abonado' => $infoTraspaso['monto_pagado'],
                            'monto_pendiente' => $infoTraspaso['monto_pendiente'],
                            'id_estado' => 200, // Pendiente (parcialmente pagado)
                            'id_metodo_pago' => 1,
                            'fecha_pago' => now()->format('Y-m-d'),
                            'observaciones' => "Pago transferido por traspaso (incluye deuda) desde inscripción #{$inscripcion->id}",
                            'referencia_pago' => 'TRASPASO-DEUDA-' . $inscripcion->id,
                        ]);
                    } else {
                        // Solo deuda, sin abono previo
                        Pago::create([
                            'id_inscripcion' => $nuevaInscripcion->id,
                            'id_cliente' => $clienteDestino->id,
                            'monto_total' => $infoTraspaso['monto_total'],
                            'monto_abonado' => 0,
                            'monto_pendiente' => $infoTraspaso['monto_pendiente'],
                            'id_estado' => 200, // Pendiente
                            'id_metodo_pago' => 1,
                            'fecha_pago' => now()->format('Y-m-d'),
                            'observaciones' => "Deuda transferida por traspaso desde inscripción #{$inscripcion->id}",
                            'referencia_pago' => 'TRASPASO-DEUDA-' . $inscripcion->id,
                        ]);
                    }
                } else {
                    // Sin deuda o ignorando deuda sin transferir
                    Pago::create([
                        'id_inscripcion' => $nuevaInscripcion->id,
                        'id_cliente' => $clienteDestino->id,
                        'monto_total' => $infoTraspaso['monto_pagado'], // Solo lo pagado
                        'monto_abonado' => $infoTraspaso['monto_pagado'],
                        'monto_pendiente' => 0,
                        'id_estado' => 201, // Pagado
                        'id_metodo_pago' => 1,
                        'fecha_pago' => now()->format('Y-m-d'),
                        'observaciones' => "Pago transferido por traspaso desde inscripción #{$inscripcion->id}",
                        'referencia_pago' => 'TRASPASO-' . $inscripcion->id,
                    ]);
                }

                // 4. Registrar en el historial de traspasos
                HistorialTraspaso::create([
                    'inscripcion_origen_id' => $inscripcion->id,
                    'inscripcion_destino_id' => $nuevaInscripcion->id,
                    'cliente_origen_id' => $inscripcion->id_cliente,
                    'cliente_destino_id' => $clienteDestino->id,
                    'membresia_id' => $inscripcion->id_membresia,
                    'fecha_traspaso' => now(),
                    'motivo' => $validated['motivo_traspaso'],
                    'dias_restantes_traspasados' => $infoTraspaso['dias_restantes'],
                    'fecha_vencimiento_original' => $inscripcion->fecha_vencimiento,
                    'monto_pagado' => $infoTraspaso['monto_pagado'],
                    'deuda_transferida' => ($infoTraspaso['tiene_deuda'] && $transferirDeuda) ? $infoTraspaso['monto_pendiente'] : 0,
                    'se_transfirio_deuda' => ($infoTraspaso['tiene_deuda'] && $transferirDeuda),
                    'usuario_id' => auth()->id(),
                ]);

                DB::commit();

                $mensajeExito = "Membresía traspasada exitosamente a {$clienteDestino->nombres} {$clienteDestino->apellido_paterno}.";
                if ($infoTraspaso['tiene_deuda'] && $transferirDeuda) {
                    $mensajeExito .= " Se transfirió una deuda de $" . number_format($infoTraspaso['monto_pendiente'], 0, ',', '.');
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensajeExito,
                    'nueva_inscripcion' => [
                        'uuid' => $nuevaInscripcion->uuid,
                        'cliente' => $clienteDestino->nombres . ' ' . $clienteDestino->apellido_paterno,
                        'membresia' => $inscripcion->membresia->nombre,
                        'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
                        'dias_restantes' => $nuevaInscripcion->dias_restantes,
                        'deuda_transferida' => ($infoTraspaso['tiene_deuda'] && $transferirDeuda) ? $infoTraspaso['monto_pendiente'] : 0,
                    ],
                    'redirect_url' => route('admin.inscripciones.show', $nuevaInscripcion),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al traspasar membresía: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el traspaso: ' . $e->getMessage(),
            ], 500);
        }
    }
}
