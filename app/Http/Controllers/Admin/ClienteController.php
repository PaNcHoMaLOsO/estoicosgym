<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Rules\RutValido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ClienteController extends Controller
{
    /**
     * Validar que no sea un doble envío usando cache en sesión
     */
    private function validateFormToken(Request $request, string $action): bool
    {
        $token = $request->input('form_submit_token');
        
        if (!$token) {
            return false;
        }
        
        // Crear clave única en cache con tiempo de vida de 10 segundos
        $userId = optional(auth('web')->user())->id ?? session()->getId();
        $cacheKey = 'form_submit_' . $userId . '_' . $action . '_' . substr($token, 0, 20);
        
        // Si el token existe en cache, es un doble envío
        if (Cache::has($cacheKey)) {
            return false;
        }
        
        // Guardar token en cache
        Cache::put($cacheKey, true, 10);
        
        return true;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mostrar solo clientes activos
        $clientes = Cliente::where('activo', true)
            ->with(['inscripciones' => function ($q) {
                $q->where('id_estado', 100); // 100 = Activa
            }])
            ->paginate(20);
        return view('admin.clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $convenios = Convenio::where('activo', true)->get();
        $membresias = Membresia::where('activo', true)->get();
        $metodos_pago = MetodoPago::all();
        
        return view('admin.clientes.create', compact('convenios', 'membresias', 'metodos_pago'));
    }

    /**
     * Store a newly created resource in storage.
     * Flujo ÚNICO: Cliente -> Convenio -> Membresía -> Pago
     */
    public function store(Request $request)
    {
        // Determinar qué tipo de flujo es
        $flujoCliente = $request->input('flujo_cliente', 'completo');

        // PASO 1: Validar datos básicos del cliente (siempre requerido)
        $validatedCliente = $request->validate([
            'run_pasaporte' => ['nullable', 'unique:clientes,run_pasaporte', new RutValido()],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'email' => 'required|email|unique:clientes',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Crear cliente (PASO 1 - Siempre se crea)
        $cliente = Cliente::create([
            ...$validatedCliente,
            'activo' => true,
        ]);

        // Validar que no sea doble envío DESPUÉS de validar datos
        // Si es doble envío, eliminar el cliente creado
        if (!$this->validateFormToken($request, 'cliente_create')) {
            $cliente->delete();
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

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
        $cliente->load(['inscripciones' => function ($q) {
            $q->with('membresia', 'estado')->latest();
        }, 'pagos' => function ($q) {
            $q->with('estado', 'metodoPago')->latest();
        }]);
        
        return view('admin.clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
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

        $validated = $request->validate([
            'run_pasaporte' => ['nullable', 'unique:clientes,run_pasaporte,' . $cliente->id, new RutValido()],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'email' => 'required|email|unique:clientes,email,' . $cliente->id,
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'id_convenio' => 'nullable|exists:convenios,id',
            'observaciones' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ]);

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
        // Validar que no tenga inscripciones activas (id_estado = 1)
        if ($cliente->inscripciones()->where('id_estado', 100)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede desactivar este cliente. Tiene inscripciones activas registradas. Por favor, venza o cancele estas inscripciones primero.');
        }

        // Validar que no tenga pagos pendientes (id_estado = 101)
        if ($cliente->pagos()->where('id_estado', 200)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede desactivar este cliente. Tiene pagos pendientes. Por favor, procese estos pagos primero.');
        }

        // Soft delete: marcar como inactivo en lugar de eliminar físicamente
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

        // Validar que no tenga inscripciones activas
        if ($cliente->inscripciones()->where('id_estado', 100)->exists()) {
            return redirect()->route('admin.clientes.edit', $cliente)
                ->with('error', 'No se puede desactivar. El cliente tiene inscripciones activas.');
        }

        // Validar que no tenga pagos pendientes
        if ($cliente->pagos()->where('id_estado', 200)->exists()) {
            return redirect()->route('admin.clientes.edit', $cliente)
                ->with('error', 'No se puede desactivar. El cliente tiene pagos pendientes.');
        }

        // Desactivar
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
