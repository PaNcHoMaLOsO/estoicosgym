<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Rules\RutValido;
use App\Http\Controllers\Traits\ValidatesFormToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    use ValidatesFormToken;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Para peticiones AJAX (lazy loading)
        if ($request->ajax()) {
            return $this->getClientesJson($request);
        }
        
        // Carga inicial: primeros 100 clientes
        $clientes = Cliente::where('activo', true)
            ->with(['inscripciones' => function ($q) {
                $q->orderBy('fecha_vencimiento', 'desc');
            }, 'inscripciones.membresia'])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        // Estadísticas
        $totalClientes = Cliente::where('activo', true)->count();
        
        // Clientes con inscripción activa (estado 100)
        $clientesActivos = Cliente::where('activo', true)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 100);
            })->count();
        
        // Clientes con inscripción vencida (estado 102)
        $clientesVencidos = Cliente::where('activo', true)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 102);
            })
            ->whereDoesntHave('inscripciones', function($q) {
                $q->where('id_estado', 100);
            })->count();
        
        // Clientes con inscripción pausada (estado 101)
        $clientesPausados = Cliente::where('activo', true)
            ->whereHas('inscripciones', function($q) {
                $q->where('id_estado', 101);
            })
            ->whereDoesntHave('inscripciones', function($q) {
                $q->where('id_estado', 100);
            })->count();
        
        // Clientes sin membresía vigente (sin inscripciones O solo con canceladas/suspendidas)
        $clientesSinMembresia = Cliente::where('activo', true)
            ->where(function($query) {
                // Sin ninguna inscripción
                $query->whereDoesntHave('inscripciones')
                    // O solo con inscripciones canceladas/suspendidas (sin activa, pausada o vencida)
                    ->orWhere(function($q) {
                        $q->whereDoesntHave('inscripciones', function($sub) {
                            $sub->whereIn('id_estado', [100, 101, 102]); // Activa, Pausada, Vencida
                        });
                    });
            })
            ->count();

        // Preparar datos de clientes para JavaScript
        $clientesData = $this->prepareClientesData($clientes);

        return view('admin.clientes.index', compact(
            'clientes',
            'clientesData',
            'totalClientes', 
            'clientesActivos', 
            'clientesVencidos', 
            'clientesPausados',
            'clientesSinMembresia'
        ));
    }

    /**
     * Obtener clientes en formato JSON para lazy loading
     */
    private function getClientesJson(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = 100;

        $clientes = Cliente::where('activo', true)
            ->with(['inscripciones' => function ($q) {
                $q->orderBy('fecha_vencimiento', 'desc');
            }, 'inscripciones.membresia'])
            ->orderBy('id', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $hasMore = Cliente::where('activo', true)->count() > ($offset + $limit);

        return response()->json([
            'clientes' => $this->prepareClientesData($clientes),
            'hasMore' => $hasMore,
            'nextOffset' => $offset + $limit
        ]);
    }

    /**
     * Preparar datos de clientes para el frontend
     */
    private function prepareClientesData($clientes)
    {
        return $clientes->map(function($cliente) {
            $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first()
                ?? $cliente->inscripciones->whereNotIn('id_estado', [103, 105, 106])->first();
            
            $estadoClass = 'sin-membresia';
            $estadoTexto = 'Sin membresía';
            $membresiaTexto = '-';
            $vencimientoTexto = '-';
            
            if ($inscripcionActiva) {
                $membresiaTexto = $inscripcionActiva->membresia->nombre ?? '-';
                $vencimientoTexto = $inscripcionActiva->fecha_vencimiento 
                    ? Carbon::parse($inscripcionActiva->fecha_vencimiento)->format('d/m/Y') 
                    : '-';
                
                switch($inscripcionActiva->id_estado) {
                    case 100: $estadoClass = 'activo'; $estadoTexto = 'Activo'; break;
                    case 101: $estadoClass = 'pausado'; $estadoTexto = 'Pausado'; break;
                    case 102: $estadoClass = 'vencido'; $estadoTexto = 'Vencido'; break;
                    case 103: $estadoClass = 'cancelado'; $estadoTexto = 'Cancelado'; break;
                    case 104: $estadoClass = 'suspendido'; $estadoTexto = 'Suspendido'; break;
                    case 105: $estadoClass = 'cambiado'; $estadoTexto = 'Cambiado'; break;
                    default: $estadoClass = 'sin-membresia'; $estadoTexto = 'Sin membresía';
                }
            }
            
            return [
                'id' => $cliente->id,
                'nombres' => $cliente->nombres,
                'apellido_paterno' => $cliente->apellido_paterno,
                'run_pasaporte' => $cliente->run_pasaporte,
                'email' => $cliente->email,
                'celular' => $cliente->celular,
                'es_menor_edad' => (bool) $cliente->es_menor_edad,
                'estadoClass' => $estadoClass,
                'estadoTexto' => $estadoTexto,
                'membresiaTexto' => $membresiaTexto,
                'vencimientoTexto' => $vencimientoTexto,
                'showUrl' => route('admin.clientes.show', $cliente),
                'editUrl' => route('admin.clientes.edit', $cliente),
                'deleteUrl' => route('admin.clientes.destroy', $cliente),
            ];
        })->values()->toArray();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $convenios = Convenio::where('activo', true)->get();
        $membresias = Membresia::where('activo', true)->with(['precios' => function($q) {
            $q->where(function ($query) {
                $query->whereNull('fecha_vigencia_hasta')
                      ->orWhere('fecha_vigencia_hasta', '>=', now());
            })->orderBy('fecha_vigencia_hasta', 'desc');
        }])->get();
        $metodos_pago = MetodoPago::all();
        $motivos_descuento = \App\Models\MotivoDescuento::where('activo', true)->get();
        
        return view('admin.clientes.create', compact('convenios', 'membresias', 'metodos_pago', 'motivos_descuento'));
    }

    /**
     * Store a newly created resource in storage.
     * Flujo ÚNICO: Cliente -> Convenio -> Membresía -> Pago
     */
    public function store(Request $request)
    {
        // Validar que no sea doble envío PRIMERO
        if (!$this->validateFormToken($request, 'cliente_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // Determinar qué tipo de flujo es
        $flujoCliente = $request->input('flujo_cliente', 'completo');

        // PASO 1: Validar datos básicos del cliente (siempre requerido)
        $validatedCliente = $request->validate([
            'run_pasaporte' => ['nullable', 'unique:clientes,run_pasaporte', new RutValido()],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'email' => 'required|email|unique:clientes,email',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(10)->format('Y-m-d'),
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'observaciones' => 'nullable|string|max:500',
            // Campos de apoderado (se validan condicionalmente)
            'es_menor_edad' => 'nullable|boolean',
            'consentimiento_apoderado' => 'nullable|boolean',
            'apoderado_nombre' => 'nullable|string|max:100',
            'apoderado_rut' => ['nullable', new RutValido()],
            'apoderado_telefono' => 'nullable|string|max:20',
            'apoderado_parentesco' => 'nullable|string|max:50',
            'apoderado_observaciones' => 'nullable|string|max:500',
        ]);

        // VALIDACIÓN ESPECIAL: Si es menor de edad, campos de apoderado son obligatorios
        $esMenorEdad = $request->boolean('es_menor_edad');
        
        if ($esMenorEdad) {
            $request->validate([
                'consentimiento_apoderado' => 'accepted',
                'apoderado_nombre' => 'required|string|max:100',
                'apoderado_rut' => ['required', new RutValido()],
                'apoderado_telefono' => 'required|string|max:20',
                'apoderado_parentesco' => 'required|string|max:50',
            ], [
                'consentimiento_apoderado.accepted' => 'Debe confirmar la autorización del apoderado para menores de edad.',
                'apoderado_nombre.required' => 'El nombre del apoderado es obligatorio para menores de edad.',
                'apoderado_rut.required' => 'El RUT del apoderado es obligatorio para menores de edad.',
                'apoderado_telefono.required' => 'El teléfono del apoderado es obligatorio para menores de edad.',
                'apoderado_parentesco.required' => 'El parentesco es obligatorio para menores de edad.',
            ]);
        }

        // Crear cliente (PASO 1 - Siempre se crea)
        $cliente = Cliente::create([
            ...$validatedCliente,
            'es_menor_edad' => $esMenorEdad,
            'consentimiento_apoderado' => $esMenorEdad ? $request->boolean('consentimiento_apoderado') : false,
            'apoderado_nombre' => $esMenorEdad ? $request->input('apoderado_nombre') : null,
            'apoderado_rut' => $esMenorEdad ? $request->input('apoderado_rut') : null,
            'apoderado_telefono' => $esMenorEdad ? $request->input('apoderado_telefono') : null,
            'apoderado_parentesco' => $esMenorEdad ? $request->input('apoderado_parentesco') : null,
            'apoderado_observaciones' => $esMenorEdad ? $request->input('apoderado_observaciones') : null,
            'activo' => true,
        ]);

        // ========== CASO 1: SOLO CLIENTE ==========
        if ($flujoCliente === 'solo_cliente') {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('success', 'Cliente registrado exitosamente. Estado: REGISTRADO (sin membresía)');
        }

        // ========== CASO 2 y 3: REQUIEREN MEMBRESÍA ==========
        // Validar datos de membresía e inscripción
        $validatedMembresia = $request->validate([
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_membresia' => 'required|exists:membresias,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'descuento_manual' => 'nullable|numeric|min:0',
            'observaciones_inscripcion' => 'nullable|string|max:500',
        ]);

        // Obtener membresía y precio
        $membresia = Membresia::findOrFail($validatedMembresia['id_membresia']);
        $precioActual = PrecioMembresia::where('id_membresia', $membresia->id)
            ->where(function ($query) {
                $query->whereNull('fecha_vigencia_hasta')
                      ->orWhere('fecha_vigencia_hasta', '>=', now());
            })
            ->orderBy('fecha_vigencia_hasta', 'desc')
            ->firstOrFail();

        // Calcular precio final con descuentos
        $precioFinal = (int) $precioActual->precio_normal;
        $descuentoConvenio = 0;
        $descuentoManual = (int) ($validatedMembresia['descuento_manual'] ?? 0);

        // Si tiene convenio Y existe precio_convenio para esta membresía, aplicar descuento
        if ($validatedMembresia['id_convenio'] && $precioActual->precio_convenio) {
            $precioFinal = (int) $precioActual->precio_convenio;
            $descuentoConvenio = (int) $precioActual->precio_normal - (int) $precioActual->precio_convenio;
        }

        // Aplicar descuento manual
        $precioFinal = max(0, $precioFinal - $descuentoManual);
        $descuentoTotal = $descuentoConvenio + $descuentoManual;

        // Crear inscripción
        $fechaInicio = Carbon::parse($validatedMembresia['fecha_inicio']);
        $fechaVencimiento = $fechaInicio->clone()->addDays($membresia->duracion_dias);

        $inscripcion = Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $membresia->id,
            'id_precio_acordado' => $precioActual->id,
            'id_convenio' => $validatedMembresia['id_convenio'],
            'id_motivo_descuento' => $validatedMembresia['id_motivo_descuento'] ?? null,
            'observaciones_inscripcion' => $validatedMembresia['observaciones_inscripcion'] ?? null,
            'fecha_inscripcion' => Carbon::now(),
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'precio_base' => (int) $precioActual->precio_normal,
            'descuento_aplicado' => $descuentoTotal,
            'precio_final' => $precioFinal,
            'id_estado' => 100, // Activa
        ]);

        // ========== CASO 2: CLIENTE + MEMBRESÍA (SIN PAGO) ==========
        if ($flujoCliente === 'con_membresia') {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('success', 'Cliente + Membresía registrados. Estado: INSCRITO (pago pendiente)');
        }

        // ========== CASO 3: CLIENTE + MEMBRESÍA + PAGO (COMPLETO) ==========
        if ($flujoCliente === 'completo') {
            $validatedPago = $request->validate([
                'tipo_pago' => 'required|in:completo,parcial,pendiente,mixto',
                'monto_abonado' => 'nullable|numeric|min:0',
                'id_metodo_pago' => 'nullable|exists:metodos_pago,id',
                'fecha_pago' => 'required|date|before_or_equal:today',
            ]);

            // Validaciones según tipo de pago
            $tipoPago = $validatedPago['tipo_pago'];
            $montoAbonado = (int) ($validatedPago['monto_abonado'] ?? 0);

            // Validar según tipo de pago
            if ($tipoPago === 'completo') {
                $montoAbonado = $precioFinal;
                $request->validate(['id_metodo_pago' => 'required']);
                $estadoPago = 201; // Pagado
            } elseif ($tipoPago === 'parcial') {
                if ($montoAbonado <= 0 || $montoAbonado > $precioFinal) {
                    return back()->with('error', 'En pago parcial el monto debe ser mayor a $0 y menor al precio final.');
                }
                $request->validate(['id_metodo_pago' => 'required']);
                $estadoPago = 202; // Parcial
            } elseif ($tipoPago === 'pendiente') {
                $montoAbonado = 0;
                $estadoPago = 200; // Pendiente
            } elseif ($tipoPago === 'mixto') {
                if ($montoAbonado < 0 || $montoAbonado > $precioFinal) {
                    return back()->with('error', 'En pago mixto el monto debe estar entre $0 y el precio final.');
                }
                $request->validate(['id_metodo_pago' => 'required']);
                $estadoPago = $montoAbonado == 0 ? 200 : 202; // Pendiente o Parcial
            }

            $montoPendiente = max(0, $precioFinal - $montoAbonado);

            // Crear pago
            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $precioFinal,
                'monto_abonado' => $montoAbonado,
                'monto_pendiente' => $montoPendiente,
                'fecha_pago' => Carbon::parse($validatedPago['fecha_pago']),
                'id_metodo_pago' => $validatedPago['id_metodo_pago'],
                'id_estado' => $estadoPago,
                'tipo_pago' => $tipoPago,
                'referencia_pago' => $request->input('referencia_pago'),
                'observaciones' => $request->input('observaciones_pago'),
            ]);

            // Determinar estado final
            $estadoPagoTexto = match($tipoPago) {
                'completo' => 'PAGADO COMPLETAMENTE',
                'parcial' => 'ABONO REGISTRADO (pendiente saldo)',
                'pendiente' => 'PAGO PENDIENTE (sin pagar)',
                'mixto' => $montoAbonado > 0 ? 'ABONO REGISTRADO (mixto)' : 'PAGO PENDIENTE (mixto)',
            };

            return redirect()->route('admin.clientes.show', $cliente)
                ->with('success', "Cliente + Membresía + Pago registrados. Estado: $estadoPagoTexto");
        }

        // Fallback (no debería llegar aquí)
        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $cliente->load([
            'convenio',
            'inscripciones' => function ($q) {
                $q->with(['membresia', 'estado', 'pagos'])->latest();
            }, 
            'pagos' => function ($q) {
                $q->with(['estado', 'metodoPago', 'inscripcion.membresia'])->latest();
            }
        ]);
        
        return view('admin.clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $cliente->load('convenio');
        $convenios = Convenio::where('activo', true)->get();
        return view('admin.clientes.edit', compact('cliente', 'convenios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Validar que no sea doble envío
        if (!$this->validateFormToken($request, 'cliente_update_' . $cliente->id)) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // Reglas de validación base
        $rules = [
            'run_pasaporte' => ['nullable', 'unique:clientes,run_pasaporte,' . $cliente->id, new RutValido()],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'email' => 'required|email|unique:clientes,email,' . $cliente->id,
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(10)->format('Y-m-d'),
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'id_convenio' => 'nullable|exists:convenios,id',
            'observaciones' => 'nullable|string|max:500',
            'activo' => 'boolean',
            // Campos de apoderado (opcionales por defecto)
            'es_menor_edad' => 'nullable|boolean',
            'consentimiento_apoderado' => 'nullable',
            'apoderado_nombre' => 'nullable|string|max:255',
            'apoderado_rut' => ['nullable', new RutValido()],
            'apoderado_telefono' => 'nullable|string|max:20',
            'apoderado_parentesco' => 'nullable|string|max:100',
            'apoderado_observaciones' => 'nullable|string|max:500',
        ];

        // Mensajes personalizados
        $messages = [
            'consentimiento_apoderado.accepted' => 'Debe aceptar el consentimiento del apoderado para clientes menores de edad.',
            'apoderado_nombre.required' => 'El nombre del apoderado es obligatorio para clientes menores de edad.',
            'apoderado_rut.required' => 'El RUT del apoderado es obligatorio para clientes menores de edad.',
            'apoderado_telefono.required' => 'El teléfono del apoderado es obligatorio para clientes menores de edad.',
            'apoderado_parentesco.required' => 'El parentesco del apoderado es obligatorio para clientes menores de edad.',
        ];

        // Si es menor de edad, hacer obligatorios los campos del apoderado
        if ($request->boolean('es_menor_edad')) {
            $rules['consentimiento_apoderado'] = 'accepted';
            $rules['apoderado_nombre'] = 'required|string|max:255';
            $rules['apoderado_rut'] = ['required', new RutValido()];
            $rules['apoderado_telefono'] = 'required|string|max:20';
            $rules['apoderado_parentesco'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules, $messages);

        // Asegurar que es_menor_edad tenga un valor booleano
        $validated['es_menor_edad'] = $request->boolean('es_menor_edad');
        $validated['consentimiento_apoderado'] = $request->boolean('consentimiento_apoderado');

        // Si no es menor de edad, limpiar campos de apoderado
        if (!$validated['es_menor_edad']) {
            $validated['consentimiento_apoderado'] = false;
            $validated['apoderado_nombre'] = null;
            $validated['apoderado_rut'] = null;
            $validated['apoderado_telefono'] = null;
            $validated['apoderado_parentesco'] = null;
            $validated['apoderado_observaciones'] = null;
        }

        $cliente->update($validated);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     * Implementa soft delete: marca el cliente como inactivo en lugar de eliminarlo
     */
    public function destroy(Cliente $cliente)
    {
        // Validar que no tenga inscripciones activas o pausadas 
        // (estados que requieren cliente activo según EstadosCodigo)
        $estadosReqClienteActivo = EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO;
        if ($cliente->inscripciones()->whereIn('id_estado', $estadosReqClienteActivo)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede desactivar este cliente. Tiene inscripciones activas o pausadas. Por favor, venza o cancele estas inscripciones primero.');
        }

        // Validar que no tenga pagos pendientes o parciales
        $estadosPagoPendientes = EstadosCodigo::PAGO_PENDIENTES_COBRO;
        if ($cliente->pagos()->whereIn('id_estado', $estadosPagoPendientes)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede desactivar este cliente. Tiene pagos pendientes. Por favor, procese estos pagos primero.');
        }

        // Soft delete: marcar como inactivo (id_estado se actualiza automáticamente en boot())
        $cliente->update(['activo' => false]);

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Cliente desactivado exitosamente. Su registro y toda su información histórica se conservan en el sistema.');
    }

    /**
     * Mostrar clientes desactivados
     */
    public function showInactive()
    {
        $clientes = Cliente::where('activo', false)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        return view('admin.clientes.inactive', compact('clientes'));
    }

    /**
     * Reactivar un cliente desactivado
     */
    public function reactivate(Cliente $cliente)
    {
        // Verificar que esté desactivado
        if ($cliente->activo) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('info', 'Este cliente ya está activo.');
        }

        // Reactivar
        $cliente->update(['activo' => true]);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', "¡Cliente '{$cliente->nombres} {$cliente->apellido_paterno}' reactivado exitosamente!");
    }

    /**
     * Desactivar cliente manualmente (desde formulario de edición)
     */
    public function deactivate(Cliente $cliente)
    {
        // Verificar que esté activo
        if (!$cliente->activo) {
            return redirect()->route('admin.clientes.index')
                ->with('error', 'Este cliente ya está desactivado.');
        }

        // Validar que no tenga inscripciones activas o pausadas
        $estadosReqClienteActivo = EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO;
        if ($cliente->inscripciones()->whereIn('id_estado', $estadosReqClienteActivo)->exists()) {
            return redirect()->route('admin.clientes.edit', $cliente)
                ->with('error', 'No se puede desactivar. El cliente tiene inscripciones activas o pausadas.');
        }

        // Validar que no tenga pagos pendientes o parciales
        $estadosPagoPendientes = EstadosCodigo::PAGO_PENDIENTES_COBRO;
        if ($cliente->pagos()->whereIn('id_estado', $estadosPagoPendientes)->exists()) {
            return redirect()->route('admin.clientes.edit', $cliente)
                ->with('error', 'No se puede desactivar. El cliente tiene pagos pendientes.');
        }

        // Desactivar (id_estado se actualiza automáticamente en boot())
        $cliente->update(['activo' => false]);

        return redirect()->route('admin.clientes.index')
            ->with('success', "Cliente '{$cliente->nombres} {$cliente->apellido_paterno}' desactivado exitosamente.");
    }

    /**
     * API: Obtener precio de membresía (normal o con descuento por convenio)
     */
    public function getPrecioMembresia($membresia_id)
    {
        $convenio_id = request('convenio');
        
        // Obtener la membresía y su precio actual
        $membresia = Membresia::find($membresia_id);
        if (!$membresia) {
            return response()->json(['error' => 'Membresía no encontrada'], 404);
        }
        
        $precioActual = PrecioMembresia::where('id_membresia', $membresia_id)
            ->where(function ($query) {
                $query->whereNull('fecha_vigencia_hasta')
                      ->orWhere('fecha_vigencia_hasta', '>=', now());
            })
            ->orderBy('fecha_vigencia_hasta', 'desc')
            ->first();
        
        if (!$precioActual) {
            return response()->json(['error' => 'Precio no encontrado'], 404);
        }
        
        $precioBase = (int) $precioActual->precio_normal;
        $precioFinal = $precioBase;
        
        // Si hay convenio, aplicar descuento
        if ($convenio_id && $precioActual->precio_convenio) {
            $precioFinal = (int) $precioActual->precio_convenio;
        }
        
        return response()->json([
            'precio_base' => $precioBase,
            'precio_final' => $precioFinal,
            'duracion_dias' => (int) $membresia->duracion_dias,
            'nombre' => $membresia->nombre
        ]);
    }
}
