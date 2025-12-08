<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadosCodigo;
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
use App\Models\HistorialCambio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\ValidatesFormToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificacionService;

class InscripcionController extends Controller
{
    use ValidatesFormToken;

    // Campos permitidos para ordenamiento
    protected $camposValidos = [
        'id', 'id_cliente', 'id_membresia', 'id_estado', 
        'fecha_inicio', 'fecha_vencimiento', 'precio_base', 
        'precio_final', 'created_at'
    ];

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Si es una petición AJAX, devolver JSON para lazy loading
        if ($request->ajax()) {
            return $this->getInscripcionesJson($request);
        }

        // Cargar primeras 100 inscripciones para la vista inicial (todas, no excluir ninguna)
        $inscripciones = Inscripcion::with(['cliente', 'estado', 'membresia', 'convenio', 'pagos'])
            ->orderBy('fecha_inicio', 'desc')
            ->take(100)
            ->get();
        
        // Estadísticas por estado
        $totalInscripciones = Inscripcion::count();
        $activas = Inscripcion::where('id_estado', 100)->count();
        $vencidas = Inscripcion::where('id_estado', 102)->count();
        $pausadas = Inscripcion::where('id_estado', 101)->count();
        $canceladas = Inscripcion::where('id_estado', 103)->count();
        $suspendidas = Inscripcion::where('id_estado', 104)->count();
        $totalEliminadas = Inscripcion::onlyTrashed()->count();
        
        // Datos para los selects de filtro
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();

        // Preparar datos para JavaScript
        $inscripcionesData = $this->prepareInscripcionesData($inscripciones);
        
        return view('admin.inscripciones.index', compact(
            'inscripcionesData',
            'estados', 
            'membresias',
            'totalInscripciones',
            'activas',
            'vencidas',
            'pausadas',
            'canceladas',
            'suspendidas',
            'totalEliminadas'
        ));
    }

    /**
     * Obtener inscripciones en formato JSON para lazy loading
     */
    private function getInscripcionesJson(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = 100;

        $inscripciones = Inscripcion::with(['cliente', 'estado', 'membresia', 'convenio', 'pagos'])
            ->whereNotIn('id_estado', [103, 105, 106])
            ->orderBy('fecha_inicio', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = Inscripcion::whereNotIn('id_estado', [103, 105, 106])->count();
        $hasMore = $total > ($offset + $limit);

        return response()->json([
            'inscripciones' => $this->prepareInscripcionesData($inscripciones),
            'hasMore' => $hasMore,
            'nextOffset' => $offset + $limit
        ]);
    }

    /**
     * Preparar datos de inscripciones para el frontend
     */
    private function prepareInscripcionesData($inscripciones)
    {
        return $inscripciones->map(function($inscripcion) {
            $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
            $estadoPago = $inscripcion->obtenerEstadoPago();
            
            // Estado base para filtros (el código del estado)
            $estadoBase = $inscripcion->id_estado;
            $estadoNombre = $inscripcion->estado?->nombre ?? 'Sin estado';
            
            // Estado visual compuesto para mostrar
            $estadoDisplay = $estadoNombre;
            $estadoClass = strtolower($estadoNombre);
            $estadoIcon = 'fa-info-circle';
            $estadoSecundario = null; // Para mostrar estado combinado
            
            // Determinar estado visual combinado
            if ($inscripcion->estaPausada()) {
                // Mostrar como estado combinado: "Activa / Pausada"
                $estadoDisplay = 'Activa / Pausada';
                $estadoSecundario = $inscripcion->pausa_indefinida ? 'Indefinida' : ($inscripcion->dias_pausa . ' días');
                $estadoClass = 'pausada';
                $estadoIcon = 'fa-pause-circle';
            } else {
                switch ($estadoBase) {
                    case 100: // Activa
                        $estadoDisplay = 'Activa';
                        $estadoClass = 'activa';
                        $estadoIcon = 'fa-check-circle';
                        break;
                    case 102: // Vencida
                        $estadoDisplay = 'Vencida';
                        $estadoClass = 'vencida';
                        $estadoIcon = 'fa-clock';
                        break;
                    case 103: // Cancelada
                        $estadoDisplay = 'Cancelada';
                        $estadoClass = 'cancelada';
                        $estadoIcon = 'fa-times-circle';
                        break;
                    case 104: // Suspendida
                        $estadoDisplay = 'Suspendida';
                        $estadoClass = 'suspendida';
                        $estadoIcon = 'fa-ban';
                        break;
                    case 105: // Cambiada (upgrade)
                        $estadoDisplay = 'Mejorada';
                        $estadoClass = 'cambiada';
                        $estadoIcon = 'fa-exchange-alt';
                        break;
                    case 106: // Traspasada
                        $estadoDisplay = 'Traspasada';
                        $estadoClass = 'traspasada';
                        $estadoIcon = 'fa-share';
                        break;
                }
            }
            
            return [
                'id' => $inscripcion->id,
                'uuid' => $inscripcion->uuid,
                // Cliente
                'cliente_id' => $inscripcion->cliente?->id,
                'cliente_nombres' => $inscripcion->cliente?->nombres ?? 'Sin cliente',
                'cliente_apellido' => $inscripcion->cliente?->apellido_paterno ?? '',
                'cliente_rut' => $inscripcion->cliente?->run_pasaporte ?? 'Sin RUT',
                'cliente_initials' => strtoupper(
                    substr($inscripcion->cliente?->nombres ?? 'N', 0, 1) . 
                    substr($inscripcion->cliente?->apellido_paterno ?? 'A', 0, 1)
                ),
                // Membresía
                'membresia_nombre' => $inscripcion->membresia?->nombre ?? 'Sin membresía',
                'convenio_nombre' => $inscripcion->convenio?->nombre ?? null,
                // Fechas
                'fecha_inicio' => $inscripcion->fecha_inicio?->format('d/m/Y') ?? 'N/A',
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? 'N/A',
                'dias_restantes' => $diasRestantes,
                // Precios
                'precio_base' => $inscripcion->precio_base ?? 0,
                'precio_final' => $inscripcion->precio_final ?? $inscripcion->precio_base ?? 0,
                'descuento_aplicado' => $inscripcion->descuento_aplicado ?? 0,
                // Estado pago
                'estado_pago' => $estadoPago['estado'],
                'total_abonado' => $estadoPago['total_abonado'],
                'pago_pendiente' => $estadoPago['pendiente'],
                'porcentaje_pagado' => $estadoPago['porcentaje_pagado'],
                // Estado inscripción
                'id_estado' => $estadoBase,                    // Código para filtrar (100, 101, 102, etc.)
                'estado_nombre' => $estadoNombre,              // Nombre del estado en BD
                'estado_display' => $estadoDisplay,            // Texto a mostrar (puede ser combinado)
                'estado_secundario' => $estadoSecundario,      // Info adicional (ej: "14 días" para pausada)
                'estado_class' => $estadoClass,                // Clase CSS para el badge
                'estado_icon' => $estadoIcon,                  // Icono FontAwesome
                'esta_pausada' => $inscripcion->estaPausada(),
                'pausa_indefinida' => $inscripcion->pausa_indefinida ?? false,
                'dias_pausa' => $inscripcion->dias_pausa ?? 0,
                'pausas_realizadas' => $inscripcion->pausas_realizadas ?? 0,
                'max_pausas_permitidas' => $inscripcion->max_pausas_permitidas ?? 2,
                // URLs
                'showUrl' => route('admin.inscripciones.show', $inscripcion),
                'editUrl' => route('admin.inscripciones.edit', $inscripcion),
                'deleteUrl' => route('admin.inscripciones.destroy', $inscripcion),
                'pagoUrl' => route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]),
                'renovarUrl' => route('admin.inscripciones.renovar', $inscripcion),
                'dias_restantes' => $inscripcion->dias_restantes,
            ];
        })->values()->toArray();
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

        // VALIDACIÓN: Verificar que el cliente está activo antes de crear inscripción
        $cliente = Cliente::find($request->input('id_cliente'));
        if ($cliente && !$cliente->activo) {
            return back()->with('error', 'No se puede crear inscripción para un cliente inactivo. Por favor, reactive el cliente primero.');
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
        
        // VALIDACIÓN: El descuento no puede superar el precio base
        $descuentoAplicado = (float) ($validated['descuento_aplicado'] ?? 0);
        if ($descuentoAplicado > $precioBase) {
            return back()->withErrors([
                'descuento_aplicado' => 'El descuento ($' . number_format($descuentoAplicado, 0, ',', '.') . ') no puede superar el precio base ($' . number_format($precioBase, 0, ',', '.') . ').'
            ])->withInput();
        }
        
        $descuentoTotal = $this->calcularDescuentoTotal($membresia, $validated, $precioBase);
        $precioFinal = max(0, $precioBase - $descuentoTotal);
        
        // VALIDACIÓN: El monto abonado no puede superar el precio final
        if (!$pagoPendiente && !$pagoMixto && isset($validated['monto_abonado'])) {
            $montoAbonado = (float) $validated['monto_abonado'];
            if ($montoAbonado > $precioFinal) {
                return back()->withErrors([
                    'monto_abonado' => 'El monto a pagar ($' . number_format($montoAbonado, 0, ',', '.') . ') no puede superar el precio final ($' . number_format($precioFinal, 0, ',', '.') . ').'
                ])->withInput();
            }
        }

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
        $validated['max_pausas_permitidas'] = $membresia->max_pausas ?? 2;

        $inscripcion = Inscripcion::create($validated);

        // Crear pago(s) según tipo de pago
        $tipoPago = $validated['tipo_pago'] ?? 'completo';
        
        if ($tipoPago === 'mixto') {
            // Pago mixto: crear dos pagos con diferentes métodos
            $this->crearPagoMixto($inscripcion, $validated, $precioFinal);
        } elseif ($pagoPendiente) {
            // Pago pendiente: crear registro con estado Pendiente (200)
            $this->crearPagoPendiente($inscripcion, $validated, $precioFinal);
        } elseif (isset($validated['monto_abonado']) && $validated['monto_abonado'] > 0) {
            $this->crearPagoInicial($inscripcion, $validated, $precioFinal);
        }

        // Invalidar token para prevenir doble envío
        $this->invalidateFormToken($request, 'inscripcion_create');

        // 🎉 ENVIAR NOTIFICACIONES AUTOMÁTICAS
        try {
            $notificacionService = app(NotificacionService::class);
            
            // Enviar notificación de bienvenida (siempre)
            $notificacionService->enviarNotificacionBienvenida($inscripcion);
            Log::info("Notificación de bienvenida enviada para inscripción #{$inscripcion->id}");
            
            // Si es menor de edad, enviar también confirmación al tutor legal
            if ($inscripcion->cliente->es_menor_edad && !empty($inscripcion->cliente->apoderado_email)) {
                $resultadoTutor = $notificacionService->enviarNotificacionTutorLegal($inscripcion);
                if ($resultadoTutor['enviada']) {
                    Log::info("Notificación de tutor legal enviada a: {$inscripcion->cliente->apoderado_email}");
                } else {
                    Log::warning("No se pudo enviar notificación de tutor legal: {$resultadoTutor['mensaje']}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al enviar notificaciones: " . $e->getMessage());
            // No interrumpir el flujo si falla el envío del email
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
     * Crear pago pendiente cuando el cliente no paga al inscribirse
     *
     * @param \App\Models\Inscripcion $inscripcion
     * @param array $validated
     * @param float $precioFinal
     * @return void
     */
    protected function crearPagoPendiente(Inscripcion $inscripcion, array $validated, float $precioFinal)
    {
        // Estado de PAGO: 200=Pendiente
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $validated['id_cliente'],
            'monto_total' => $precioFinal,
            'monto_abonado' => 0,
            'monto_pendiente' => $precioFinal,
            'id_estado' => 200, // Pendiente
            'id_metodo_pago' => 1, // Efectivo por defecto (se actualizará cuando pague)
            'fecha_pago' => null,
            'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
            'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
            'observaciones' => 'Pago pendiente - Sin abono al momento de inscripción',
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
                    'observaciones' => 'Pago mixto - ' . $metodoNombre,
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
            // Relaciones de traspaso
            'inscripcionOrigen.membresia',
            'inscripcionOrigen.cliente',
            'clienteOriginal',
            'inscripcionesTraspasadas.cliente',
            'inscripcionesTraspasadas.membresia',
        ]);
        
        $estadoPago = $inscripcion->obtenerEstadoPago();
        
        // Obtener historial de cambios para esta inscripción
        $historialCambios = HistorialCambio::where('inscripcion_id', $inscripcion->id)
            ->orWhere('entidad_id', $inscripcion->id)
            ->with(['usuario', 'estadoAnterior', 'estadoNuevo'])
            ->orderByDesc('fecha_cambio')
            ->get();
        
        // Información de pausas
        $infoPausa = [
            'esta_pausada' => $inscripcion->pausada ?? false,
            'pausas_realizadas' => $inscripcion->pausas_realizadas ?? 0,
            'max_pausas' => $inscripcion->max_pausas_permitidas ?? 1,
            'pausas_disponibles' => max(0, ($inscripcion->max_pausas_permitidas ?? 1) - ($inscripcion->pausas_realizadas ?? 0)),
            'fecha_ultima_pausa' => $inscripcion->fecha_pausa_inicio ?? null,
            'fecha_fin_pausa' => $inscripcion->fecha_pausa_fin ?? null,
            'dias_restantes_pausa' => $inscripcion->fecha_pausa_fin 
                ? max(0, (int) now()->diffInDays($inscripcion->fecha_pausa_fin, false)) 
                : 0,
            'dias_restantes_al_pausar' => $inscripcion->dias_restantes_al_pausar ?? 0,
            'pausa_indefinida' => $inscripcion->pausa_indefinida ?? false,
            'razon_pausa' => $inscripcion->razon_pausa ?? null,
        ];
        
        // Información financiera
        $totalPagado = $inscripcion->pagos ? $inscripcion->pagos->sum('monto_abonado') : 0;
        $precioFinal = $inscripcion->precio_final ?? 0;
        $deudaPendiente = max(0, $precioFinal - $totalPagado);
        
        $infoFinanciera = [
            'total_pagado' => $totalPagado,
            'precio_final' => $precioFinal,
            'deuda_pendiente' => $deudaPendiente,
            'cantidad_pagos' => $inscripcion->pagos ? $inscripcion->pagos->count() : 0,
            'porcentaje_pagado' => $precioFinal > 0 ? round(($totalPagado / $precioFinal) * 100) : 100,
        ];
        
        // Puede editar si está activa, pausada o vencida
        $canEdit = in_array($inscripcion->id_estado, [100, 101, 102]);
        
        // Métodos de pago para posibles acciones
        $metodosPago = MetodoPago::where('activo', true)->get();
        
        return view('admin.inscripciones.show', compact(
            'inscripcion', 
            'estadoPago', 
            'historialCambios',
            'infoPausa',
            'infoFinanciera',
            'canEdit',
            'metodosPago'
        ));
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
        
        // Información de deuda para mejora de plan
        $infoMejora = [
            'tiene_deuda' => $inscripcion->monto_pendiente > 0,
            'monto_pendiente' => $inscripcion->monto_pendiente,
            'monto_pagado' => $inscripcion->monto_pagado,
            'precio_final' => $inscripcion->precio_final,
            'porcentaje_pagado' => $inscripcion->precio_final > 0 
                ? round(($inscripcion->monto_pagado / $inscripcion->precio_final) * 100) 
                : 100,
            'esta_pagada' => $inscripcion->esta_pagada,
        ];
        
        return view('admin.inscripciones.edit', compact('inscripcion', 'clientes', 'estados', 'membresias', 'convenios', 'motivos', 'metodosPago', 'infoTraspaso', 'infoMejora'));
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
            'id_estado' => 'required|exists:estados,codigo',
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_inicio',
            'precio_base' => 'required|numeric|min:0',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Calcular precio_final: precio_base - descuento
        $validated['precio_final'] = $validated['precio_base'] - ($validated['descuento_aplicado'] ?? 0);

        $inscripcion->update($validated);

        // Invalidar token para prevenir doble envío
        $this->invalidateFormToken($request, 'inscripcion_update_' . $inscripcion->id);

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     * Solo permite eliminar inscripciones canceladas, vencidas o sin pagos.
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Inscripcion $inscripcion)
    {
        try {
            // Cargar relaciones necesarias para el mensaje
            $inscripcion->load(['cliente', 'membresia']);
            
            $clienteNombre = $inscripcion->cliente->nombre_completo ?? 'Cliente';
            $membresiaNombre = $inscripcion->membresia->nombre ?? 'Membresía';
            
            // Soft delete - la inscripción va a la papelera
            $inscripcion->delete();
            
            return redirect()->route('admin.inscripciones.index')
                ->with('success', "Inscripción de {$clienteNombre} ({$membresiaNombre}) enviada a la papelera.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la inscripción: ' . $e->getMessage());
        }
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
        
        // Aceptar tanto 'dias' como 'dias_pausa' para compatibilidad
        $diasInput = $request->input('dias_pausa') ?? $request->input('dias');
        $razonInput = $request->input('razon_pausa') ?? $request->input('razon');
        
        // Merge para validación
        $request->merge([
            'dias' => $diasInput,
            'razon' => $razonInput
        ]);
        
        $rules = [
            'razon' => 'nullable|string|max:500',
            'indefinida' => 'nullable|boolean',
        ];
        
        // Si no es indefinida, días es requerido
        if (!$indefinida) {
            $rules['dias'] = 'required|integer|min:1|max:90';
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
                
            // Obtener días restantes guardados antes de reanudar
            $diasGuardados = $inscripcion->dias_restantes_al_pausar ?? 0;

            $inscripcion->reanudar();

            return response()->json([
                'success' => true,
                'message' => "Membresía reanudada. Estuvo pausada {$diasEnPausa} días. Se restauraron {$diasGuardados} días de membresía.",
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
                'tipo_pago' => 'nullable|in:completo,parcial', // Tipo de pago seleccionado
                'total_a_pagar' => 'nullable|numeric|min:0', // Total calculado
                'ignorar_deuda' => 'nullable|boolean', // Si se permite mejorar con deuda
            ]);

            $inscripcion->load(['cliente', 'membresia', 'pagos']);

            // Verificar que puede cambiar de plan
            if (!$inscripcion->puedeCambiarPlan()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta inscripción no puede cambiar de plan. Verifique que esté activa y no pausada.',
                ], 422);
            }
            
            // Verificar que no tiene deuda pendiente del plan actual (a menos que el admin lo ignore)
            $ignorarDeuda = $request->boolean('ignorar_deuda', false);
            $deudaAnterior = $inscripcion->monto_pendiente;
            
            if ($deudaAnterior > 0 && !$ignorarDeuda) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente tiene una deuda pendiente de $' . number_format($inscripcion->monto_pendiente, 0, ',', '.') . 
                                 ' en el plan actual. Debe pagar esta deuda antes de poder mejorar el plan.',
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
            
            // Calcular diferencia base (nuevo plan - crédito)
            $diferencia = $precioNuevoPlan - $creditoDisponible;
            
            // Si se ignoró la deuda, sumarla al total a pagar
            if ($ignorarDeuda && $deudaAnterior > 0) {
                $diferencia += $deudaAnterior;
            }
            
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
                // FIX: Ajustar fecha_vencimiento al cambiar de plan
                $inscripcion->update([
                    'id_estado' => 105, // Estado: Cambiada a otro plan
                    'fecha_vencimiento' => now()->format('Y-m-d'), // FIX: La inscripción ya no está activa
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '') 
                        . "[" . now()->format('d/m/Y H:i') . "] Cambio de plan a: {$nuevaMembresia->nombre}",
                ]);

                // 2. Crear nueva inscripción con los datos del cambio
                $observaciones = "Cambio de plan desde: {$inscripcion->membresia->nombre}";
                if ($ignorarDeuda && $deudaAnterior > 0) {
                    $observaciones .= ". Incluye deuda anterior de $" . number_format($deudaAnterior, 0, ',', '.');
                }
                
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
                    'observaciones' => $observaciones,
                    'max_pausas_permitidas' => $nuevaMembresia->max_pausas ?? 2,
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
                
                // Nota sobre deuda anterior incluida
                $mensajeDeuda = '';
                if ($ignorarDeuda && $deudaAnterior > 0) {
                    $mensajeDeuda = " Se incluyó deuda anterior de $" . number_format($deudaAnterior, 0, ',', '.') . ".";
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Plan cambiado exitosamente de {$inscripcion->membresia->nombre} a {$nuevaMembresia->nombre}." . $mensajeCredito . $mensajeDeuda,
                    'nueva_inscripcion' => [
                        'uuid' => $nuevaInscripcion->uuid,
                        'membresia' => $nuevaMembresia->nombre,
                        'fecha_vencimiento' => $fechaVencimiento->format('d/m/Y'),
                        'tipo_cambio' => $tipoCambio,
                        'diferencia' => $diferencia,
                        'deuda_incluida' => $ignorarDeuda ? $deudaAnterior : 0,
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
        Log::info('=== INICIO TRASPASAR (TRANSFERENCIA) ===', [
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
            
            // Validar que el cliente destino esté activo
            if (!$clienteDestino->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede traspasar a un cliente inactivo.',
                ], 422);
            }
            
            if (!Inscripcion::clientePuedeRecibirTraspaso($clienteDestino->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente seleccionado ya tiene una membresía activa.',
                ], 422);
            }

            $inscripcion->load(['cliente', 'membresia', 'pagos']);
            $infoTraspaso = $inscripcion->getInfoTraspaso();
            
            // Guardar datos del cliente origen antes de modificar
            $clienteOrigen = $inscripcion->cliente;
            $clienteOrigenId = $inscripcion->id_cliente;
            $clienteOrigenNombre = $clienteOrigen->nombres . ' ' . $clienteOrigen->apellido_paterno;

            DB::beginTransaction();

            try {
                // ================================================================
                // NUEVA LÓGICA: TRANSFERIR en lugar de COPIAR
                // - La inscripción cambia de dueño (id_cliente)
                // - Los pagos cambian de dueño (id_cliente)
                // - NO se crean nuevos registros
                // - Se guarda historial del traspaso
                // ================================================================

                // 1. Registrar en el historial de traspasos ANTES de modificar
                // (Guardamos inscripcion_destino_id como la misma inscripción porque no se crea nueva)
                $historial = HistorialTraspaso::create([
                    'inscripcion_origen_id' => $inscripcion->id,
                    'inscripcion_destino_id' => $inscripcion->id, // Misma inscripción, diferente dueño
                    'cliente_origen_id' => $clienteOrigenId,
                    'cliente_destino_id' => $clienteDestino->id,
                    'membresia_id' => $inscripcion->id_membresia,
                    'fecha_traspaso' => now(),
                    'motivo' => $validated['motivo_traspaso'],
                    'dias_restantes_traspasados' => $infoTraspaso['dias_restantes'],
                    'fecha_vencimiento_original' => $inscripcion->fecha_vencimiento,
                    'monto_pagado' => $infoTraspaso['monto_pagado'],
                    'deuda_transferida' => $infoTraspaso['monto_pendiente'],
                    'se_transfirio_deuda' => $infoTraspaso['tiene_deuda'],
                    'usuario_id' => auth()->id(),
                ]);

                // 2. Actualizar la inscripción: cambiar el cliente dueño
                $observacionTraspaso = "[" . now()->format('d/m/Y H:i') . "] Traspaso de: {$clienteOrigenNombre} → {$clienteDestino->nombres} {$clienteDestino->apellido_paterno}. Motivo: {$validated['motivo_traspaso']}";
                
                $inscripcion->update([
                    'id_cliente' => $clienteDestino->id, // Nuevo dueño
                    // Marcar como traspaso para tracking
                    'es_traspaso' => true,
                    'id_cliente_original' => $clienteOrigenId, // Guardamos quién era el dueño original
                    'fecha_traspaso' => now(),
                    'motivo_traspaso' => $validated['motivo_traspaso'],
                    'observaciones' => ($inscripcion->observaciones ? $inscripcion->observaciones . "\n" : '') . $observacionTraspaso,
                ]);

                // 3. Transferir todos los pagos al nuevo cliente
                foreach ($inscripcion->pagos as $pago) {
                    $observacionPago = $pago->observaciones ?? '';
                    $observacionPago .= ($observacionPago ? "\n" : '') 
                        . "[" . now()->format('d/m/Y H:i') . "] Transferido de: {$clienteOrigenNombre} → {$clienteDestino->nombres} {$clienteDestino->apellido_paterno}";
                    
                    $pago->update([
                        'id_cliente' => $clienteDestino->id, // Nuevo dueño del pago
                        'observaciones' => $observacionPago,
                    ]);
                }

                DB::commit();

                $mensajeExito = "Membresía transferida exitosamente a {$clienteDestino->nombres} {$clienteDestino->apellido_paterno}.";
                if ($infoTraspaso['tiene_deuda']) {
                    $mensajeExito .= " La deuda de $" . number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') . " fue transferida al nuevo titular.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensajeExito,
                    'nueva_inscripcion' => [
                        'uuid' => $inscripcion->uuid, // Misma inscripción
                        'cliente' => $clienteDestino->nombres . ' ' . $clienteDestino->apellido_paterno,
                        'cliente_anterior' => $clienteOrigenNombre,
                        'membresia' => $inscripcion->membresia->nombre,
                        'fecha_vencimiento' => $inscripcion->fecha_vencimiento->format('d/m/Y'),
                        'dias_restantes' => $infoTraspaso['dias_restantes'],
                        'deuda_transferida' => $infoTraspaso['monto_pendiente'],
                    ],
                    'redirect_url' => route('admin.inscripciones.show', $inscripcion),
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

    // ==========================================
    // PAPELERA (SoftDeletes)
    // ==========================================

    /**
     * Mostrar inscripciones eliminadas (papelera)
     */
    public function trashed()
    {
        $inscripciones = Inscripcion::onlyTrashed()
            ->with(['cliente', 'membresia', 'estado'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        $totalEliminadas = Inscripcion::onlyTrashed()->count();

        return view('admin.inscripciones.trashed', compact('inscripciones', 'totalEliminadas'));
    }

    /**
     * Restaurar una inscripción eliminada
     */
    public function restore($id)
    {
        $inscripcion = Inscripcion::onlyTrashed()->findOrFail($id);
        
        // Verificar que el cliente no esté eliminado
        if ($inscripcion->cliente && $inscripcion->cliente->trashed()) {
            return redirect()->route('admin.inscripciones.trashed')
                ->with('error', 'No se puede restaurar la inscripción porque el cliente está eliminado. Restaure primero al cliente.');
        }

        $inscripcion->restore();

        $clienteNombre = $inscripcion->cliente->nombres ?? 'Cliente';
        return redirect()->route('admin.inscripciones.trashed')
            ->with('success', "Inscripción de {$clienteNombre} restaurada exitosamente.");
    }

    /**
     * Eliminar permanentemente una inscripción
     */
    public function forceDelete($id)
    {
        $inscripcion = Inscripcion::onlyTrashed()->findOrFail($id);
        
        // Verificar que no tenga pagos
        if ($inscripcion->pagos()->withTrashed()->exists()) {
            return redirect()->route('admin.inscripciones.trashed')
                ->with('error', 'No se puede eliminar permanentemente. La inscripción tiene pagos asociados. Elimine primero los pagos.');
        }

        $clienteNombre = $inscripcion->cliente->nombres ?? 'Cliente';
        $inscripcion->forceDelete();

        return redirect()->route('admin.inscripciones.trashed')
            ->with('success', "Inscripción de {$clienteNombre} eliminada permanentemente.");
    }

    // ==========================================
    // RENOVACIÓN DE MEMBRESÍA
    // ==========================================

    /**
     * Mostrar formulario de renovación pre-poblado
     * 
     * @param Inscripcion $inscripcion La inscripción vencida o por vencer a renovar
     * @return \Illuminate\View\View
     */
    public function showRenovar(Inscripcion $inscripcion)
    {
        // Verificar que la inscripción sea renovable (vencida o próxima a vencer)
        $diasRestantes = $inscripcion->dias_restantes;
        
        // Permitir renovar si: vencida, o le quedan 30 días o menos
        if ($diasRestantes > 30 && $inscripcion->id_estado == 100) {
            return redirect()->route('admin.inscripciones.show', $inscripcion)
                ->with('warning', 'Esta inscripción aún tiene más de 30 días de vigencia. No es necesario renovar.');
        }

        $clientes = Cliente::where('activo', true)->orderBy('nombres')->get();
        $membresias = Membresia::where('activo', true)->orderBy('nombre')->get();
        $convenios = Convenio::where('activo', true)->get();
        $motivos = MotivoDescuento::where('activo', true)->get();
        $metodosPago = MetodoPago::where('activo', true)->get();
        $estados = Estado::where('categoria', 'inscripcion')->get();
        $estadoActiva = Estado::where('codigo', EstadosCodigo::INSCRIPCION_ACTIVA)->first();

        // Pre-cargar datos de la inscripción anterior
        $datosRenovacion = [
            'inscripcion_anterior' => $inscripcion,
            'cliente' => $inscripcion->cliente,
            'membresia' => $inscripcion->membresia,
            'convenio' => $inscripcion->convenio,
            'precio_anterior' => $inscripcion->precio_final,
            'fecha_inicio_sugerida' => $inscripcion->fecha_vencimiento->addDay()->format('Y-m-d'),
        ];

        return view('admin.inscripciones.renovar', compact(
            'inscripcion', 'clientes', 'membresias', 'convenios', 
            'motivos', 'metodosPago', 'estados', 'estadoActiva', 'datosRenovacion'
        ));
    }

    /**
     * Procesar la renovación de una membresía
     * 
     * Crea una nueva inscripción basada en la anterior y la marca como renovación.
     * 
     * @param Request $request
     * @param Inscripcion $inscripcionAnterior La inscripción que se está renovando
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renovar(Request $request, Inscripcion $inscripcionAnterior)
    {
        if (!$this->validateFormToken($request, 'inscripcion_renovar')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // Validación
        $validated = $request->validate([
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'fecha_inicio' => 'required|date',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
            'tipo_pago' => 'required|in:completo,abono,mixto,pendiente',
            'monto_abonado' => 'nullable|numeric|min:0',
            'id_metodo_pago' => 'nullable|exists:metodos_pago,id',
            'fecha_pago' => 'nullable|date',
        ]);

        $cliente = $inscripcionAnterior->cliente;
        $membresia = Membresia::findOrFail($validated['id_membresia']);
        
        // Calcular precios
        $precioBase = $this->obtenerPrecioMembresia($membresia, $validated);
        
        // VALIDACIÓN: El descuento no puede superar el precio base
        $descuentoAplicado = (float) ($validated['descuento_aplicado'] ?? 0);
        if ($descuentoAplicado > $precioBase) {
            return back()->withErrors([
                'descuento_aplicado' => 'El descuento ($' . number_format($descuentoAplicado, 0, ',', '.') . ') no puede superar el precio base ($' . number_format($precioBase, 0, ',', '.') . ').'
            ])->withInput();
        }
        
        $descuentoTotal = $this->calcularDescuentoTotal($membresia, $validated, $precioBase);
        $precioFinal = max(0, $precioBase - $descuentoTotal);

        // Calcular fecha de vencimiento
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaVencimiento = $this->calcularFechaVencimiento($fechaInicio, $membresia);

        DB::beginTransaction();
        try {
            // Crear nueva inscripción
            $nuevaInscripcion = Inscripcion::create([
                'id_cliente' => $cliente->id,
                'id_membresia' => $validated['id_membresia'],
                'id_convenio' => $validated['id_convenio'] ?? null,
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                'fecha_inscripcion' => now()->format('Y-m-d'),
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'precio_base' => $precioBase,
                'descuento_aplicado' => $descuentoTotal,
                'precio_final' => $precioFinal,
                'id_motivo_descuento' => $validated['id_motivo_descuento'] ?? null,
                'id_precio_acordado' => 1,
                'max_pausas_permitidas' => $membresia->max_pausas ?? 2,
                'observaciones' => ($validated['observaciones'] ?? '') . "\n[Renovación de inscripción #{$inscripcionAnterior->id}]",
                // Campos de renovación (usa estructura de cambio de plan)
                'es_cambio_plan' => true,
                'tipo_cambio' => 'renovacion',
                'id_inscripcion_anterior' => $inscripcionAnterior->id,
            ]);

            // Crear pago según tipo
            $tipoPago = $validated['tipo_pago'];
            if ($tipoPago === 'pendiente') {
                $this->crearPagoPendiente($nuevaInscripcion, $validated, $precioFinal);
            } elseif (isset($validated['monto_abonado']) && $validated['monto_abonado'] > 0) {
                $this->crearPagoInicial($nuevaInscripcion, $validated, $precioFinal);
            }

            // Registrar en historial
            HistorialCambio::create([
                'inscripcion_id' => $nuevaInscripcion->id,
                'tipo_cambio' => 'renovacion',
                'descripcion' => "Renovación de membresía. Inscripción anterior: #{$inscripcionAnterior->id}",
                'datos_anteriores' => json_encode([
                    'inscripcion_anterior_id' => $inscripcionAnterior->id,
                    'membresia_anterior' => $inscripcionAnterior->membresia->nombre ?? 'N/A',
                    'fecha_vencimiento_anterior' => $inscripcionAnterior->fecha_vencimiento->format('Y-m-d'),
                ]),
                'datos_nuevos' => json_encode([
                    'nueva_inscripcion_id' => $nuevaInscripcion->id,
                    'membresia' => $membresia->nombre,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'precio_final' => $precioFinal,
                ]),
                'id_usuario' => auth()->id(),
            ]);

            // Enviar notificación de renovación exitosa
            try {
                $notificacionService = app(\App\Services\NotificacionService::class);
                $notificacionService->enviarNotificacionRenovacion($nuevaInscripcion);
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar notificación de renovación: ' . $e->getMessage());
            }

            DB::commit();

            $this->invalidateFormToken($request, 'inscripcion_renovar');

            return redirect()->route('admin.inscripciones.show', $nuevaInscripcion)
                ->with('success', '¡Membresía renovada exitosamente! Nueva vigencia hasta ' . $fechaVencimiento->format('d/m/Y'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en renovación: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar la renovación: ' . $e->getMessage());
        }
    }
}
