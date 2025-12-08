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
        
        // Paginación con límite inicial
        $perPage = $request->get('per_page', 50);
        $pagos = $query->paginate($perPage);
        
        // Si es petición AJAX, devolver JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'pagos' => $this->preparePagosData($pagos->items()),
                'pagination' => [
                    'current_page' => $pagos->currentPage(),
                    'last_page' => $pagos->lastPage(),
                    'per_page' => $pagos->perPage(),
                    'total' => $pagos->total(),
                    'from' => $pagos->firstItem(),
                    'to' => $pagos->lastItem(),
                ]
            ]);
        }
        
        $metodos_pago = MetodoPago::all();
        $estados = Estado::where('categoria', 'pago')->get();
        $totalEliminados = Pago::onlyTrashed()->count();
        
        // Preparar datos iniciales para JavaScript
        $pagosData = $this->preparePagosData($pagos->items());
        $totalPagos = $pagos->total();
        
        // Calcular estadísticas reales desde la base de datos (no solo los paginados)
        $estadisticas = $this->calcularEstadisticas();
        
        return view('admin.pagos.index', compact('pagos', 'pagosData', 'totalPagos', 'metodos_pago', 'estados', 'totalEliminados', 'estadisticas'));
    }

    /**
     * Obtener pagos en formato JSON para lazy loading
     */
    public function getPagosJson(Request $request)
    {
        $query = Pago::with(['cliente', 'inscripcion.cliente', 'inscripcion.membresia', 'metodoPago', 'estado']);
        
        // Filtro por inscripción
        if ($request->filled('id_inscripcion')) {
            $query->where('id_inscripcion', $request->id_inscripcion);
        }
        
        // Ordenamiento
        $ordenar = $request->get('ordenar', 'fecha_pago');
        $direccion = $request->get('direccion', 'desc');
        
        $camposValidos = ['id', 'id_inscripcion', 'id_cliente', 'monto_total', 'monto_abonado', 'fecha_pago', 'id_metodo_pago', 'id_estado', 'created_at'];
        if (!in_array($ordenar, $camposValidos)) {
            $ordenar = 'fecha_pago';
        }
        
        $query->orderBy($ordenar, $direccion);
        
        $perPage = $request->get('per_page', 50);
        $pagos = $query->paginate($perPage);
        
        return response()->json([
            'pagos' => $this->preparePagosData($pagos->items()),
            'pagination' => [
                'current_page' => $pagos->currentPage(),
                'last_page' => $pagos->lastPage(),
                'per_page' => $pagos->perPage(),
                'total' => $pagos->total(),
                'from' => $pagos->firstItem(),
                'to' => $pagos->lastItem(),
            ]
        ]);
    }

    /**
     * Preparar datos de pagos para JavaScript
     */
    private function preparePagosData($pagos)
    {
        return collect($pagos)->map(function($pago) {
            $total = $pago->monto_total ?? 0;
            $abonado = $pago->monto_abonado ?? 0;
            $pendiente = $pago->monto_pendiente ?? 0;
            $porcentaje = $total > 0 ? ($abonado / $total) * 100 : 0;
            
            // NOTA: id_estado contiene directamente el código del estado (200, 201, etc.)
            // en lugar del id de la tabla estados
            $estadoCodigo = $pago->id_estado ?? 200;
            $estadoPago = match($estadoCodigo) {
                200 => 'pendiente',
                201 => 'pagado',
                202 => 'parcial',
                203 => 'vencido',
                204 => 'cancelado',
                205 => 'traspasado',
                default => 'pendiente'
            };
            
            $estadoTexto = match($estadoCodigo) {
                200 => 'Pendiente',
                201 => 'Pagado',
                202 => 'Parcial',
                203 => 'Vencido',
                204 => 'Cancelado',
                205 => 'Traspasado',
                default => 'Pendiente'
            };
            
            $estadoIcono = match($estadoCodigo) {
                200 => 'fa-clock',
                201 => 'fa-check-circle',
                202 => 'fa-adjust',
                203 => 'fa-exclamation-triangle',
                204 => 'fa-times-circle',
                205 => 'fa-exchange-alt',
                default => 'fa-clock'
            };
            
            // Cliente actual de la inscripción (dueño actual de la membresía)
            $clienteActual = $pago->inscripcion?->cliente;
            // Cliente original que pagó (puede ser diferente si hubo traspaso)
            $clienteOriginal = $pago->cliente;
            // Detectar si es un traspaso
            $esTraspaso = $clienteActual && $clienteOriginal && $clienteActual->id !== $clienteOriginal->id;
            // Usar cliente actual si existe, sino el original
            $clienteMostrar = $clienteActual ?? $clienteOriginal;
            
            // Método de pago icono
            $metodoCodigo = $pago->metodoPago?->codigo ?? '';
            $metodoIcono = match($metodoCodigo) {
                'efectivo' => 'fa-money-bill-wave',
                'tarjeta' => 'fa-credit-card',
                'transferencia' => 'fa-university',
                default => 'fa-wallet'
            };
            
            return [
                'id' => $pago->id,
                'uuid' => $pago->uuid ?? $pago->id,
                'cliente_nombre' => $clienteMostrar ? ($clienteMostrar->nombres . ' ' . $clienteMostrar->apellido_paterno) : 'Sin cliente',
                'cliente_iniciales' => strtoupper(substr($clienteMostrar?->nombres ?? 'N', 0, 1) . substr($clienteMostrar?->apellido_paterno ?? 'A', 0, 1)),
                'cliente_original_nombre' => $clienteOriginal ? ($clienteOriginal->nombres . ' ' . $clienteOriginal->apellido_paterno) : '',
                'es_traspaso' => $esTraspaso,
                'membresia_nombre' => $pago->inscripcion?->membresia?->nombre ?? 'Sin membresía',
                'fecha_pago' => $pago->fecha_pago?->format('d/m/Y') ?? 'N/A',
                'referencia_pago' => $pago->referencia_pago ?? '',
                'monto_total' => $total,
                'monto_total_formatted' => '$' . number_format($total, 0, ',', '.'),
                'monto_abonado' => $abonado,
                'monto_abonado_formatted' => '$' . number_format($abonado, 0, ',', '.'),
                'monto_pendiente' => $pendiente,
                'monto_pendiente_formatted' => '$' . number_format($pendiente, 0, ',', '.'),
                'porcentaje' => round($porcentaje, 1),
                'estado_pago' => $estadoPago,
                'estado_texto' => $estadoTexto,
                'estado_icono' => $estadoIcono,
                'metodo_nombre' => $pago->metodoPago?->nombre ?? 'N/A',
                'metodo_icono' => $metodoIcono,
                'show_url' => route('admin.pagos.show', $pago->uuid ?? $pago->id),
                'edit_url' => route('admin.pagos.edit', $pago->uuid ?? $pago->id),
                'delete_url' => route('admin.pagos.destroy', $pago->uuid ?? $pago->id),
            ];
        })->values()->toArray();
    }

    /**
     * Calcular estadísticas reales de pagos desde la base de datos
     */
    private function calcularEstadisticas(): array
    {
        // NOTA: Los pagos usan el código del estado directamente en id_estado
        // (200=Pendiente, 201=Pagado, 202=Parcial, 203=Vencido, 204=Cancelado, 205=Traspasado)
        
        return [
            'pagados' => Pago::where('id_estado', 201)->count(),
            'parciales' => Pago::where('id_estado', 202)->count(),
            'pendientes' => Pago::where('id_estado', 200)->count(),
            'vencidos' => Pago::where('id_estado', 203)->count(),
        ];
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

        // 📧 ENVIAR NOTIFICACIÓN SI EL PAGO ESTÁ COMPLETO
        if ($idEstado == 201) { // Pago completado
            try {
                $notificacionService = app(\App\Services\NotificacionService::class);
                $inscripcion->load(['cliente', 'membresia', 'pagos']);
                
                // Usar crearNotificacion para que use la plantilla HTML con datos dinámicos
                $tipoNotificacion = \App\Models\TipoNotificacion::where('codigo', \App\Models\TipoNotificacion::PAGO_COMPLETADO)
                    ->where('activo', true)
                    ->first();
                    
                if ($tipoNotificacion && $inscripcion->cliente->email) {
                    $notificacionService->crearNotificacion($tipoNotificacion, $inscripcion);
                    \Illuminate\Support\Facades\Log::info("Notificación de pago completado programada para inscripción #{$inscripcion->id}");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error al programar notificación de pago completado: " . $e->getMessage());
                // No interrumpir el flujo si falla el envío del email
            }
        }

        // Invalidar token para prevenir doble envío
        $this->invalidateFormToken($request, 'pago_create');

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

        // Invalidar token para prevenir doble envío
        $this->invalidateFormToken($request, 'pago_update_' . $pago->id);

        return redirect()->route('admin.pagos.show', $pago)
            ->with('success', 'Pago actualizado exitosamente. El estado se asignó automáticamente: ' . $pago->estado->nombre);
    }

    /**
     * Remove the specified resource from storage.
     * Usa SoftDelete - el pago va a la papelera y puede restaurarse.
     */
    public function destroy(Pago $pago)
    {
        try {
            // Cargar relaciones necesarias para el mensaje
            $pago->load(['inscripcion.cliente', 'metodoPago']);
            
            $clienteNombre = $pago->inscripcion->cliente->nombre_completo ?? 'Cliente';
            $monto = number_format($pago->monto_abonado, 0, ',', '.');
            
            // Soft delete - el pago va a la papelera
            $pago->delete();
            
            return redirect()->route('admin.pagos.index')
                ->with('success', "Pago de \${$monto} de {$clienteNombre} enviado a la papelera.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Método legacy - deshabilitado
     * Se mantiene comentado por referencia histórica
     */
    /*
    private function destroyLegacy(Pago $pago)
    {
        $pago->load('inscripcion');
        
        if ($pago->id_estado == 205) {
            return redirect()->route('admin.pagos.show', $pago)
                ->with('error', 'No se puede eliminar un pago traspasado.');
        }
        
        if ($pago->inscripcion && $pago->inscripcion->id_estado == 100) {
            $totalPagos = $pago->inscripcion->pagos()->count();
            if ($totalPagos <= 1) {
                return redirect()->route('admin.pagos.show', $pago)
                    ->with('error', 'No se puede eliminar el único pago de una inscripción activa.');
            }
        }
        
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

    // ==========================================
    // PAPELERA (SoftDeletes)
    // ==========================================

    /**
     * Mostrar pagos eliminados (papelera)
     */
    public function trashed()
    {
        $pagos = Pago::onlyTrashed()
            ->with(['cliente', 'inscripcion', 'metodoPago', 'estado'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        $totalEliminados = Pago::onlyTrashed()->count();

        return view('admin.pagos.trashed', compact('pagos', 'totalEliminados'));
    }

    /**
     * Restaurar un pago eliminado
     */
    public function restore($id)
    {
        $pago = Pago::onlyTrashed()->findOrFail($id);
        
        // Verificar que la inscripción no esté eliminada
        if ($pago->inscripcion && $pago->inscripcion->trashed()) {
            return redirect()->route('admin.pagos.trashed')
                ->with('error', 'No se puede restaurar el pago porque la inscripción está eliminada. Restaure primero la inscripción.');
        }

        $pago->restore();

        return redirect()->route('admin.pagos.trashed')
            ->with('success', "Pago de \${$pago->monto_abonado} restaurado exitosamente.");
    }

    /**
     * Eliminar permanentemente un pago
     */
    public function forceDelete($id)
    {
        $pago = Pago::onlyTrashed()->findOrFail($id);
        $monto = $pago->monto_abonado;
        $pago->forceDelete();

        return redirect()->route('admin.pagos.trashed')
            ->with('success', "Pago de \${$monto} eliminado permanentemente.");
    }
}
