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
        // Validar que no sea doble envío
        if (!$this->validateFormToken($request, 'cliente_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // PASO 1: Validar datos básicos del cliente
        $validated = $request->validate([
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
            // PASO 2: Convenio (opcional)
            'id_convenio' => 'nullable|exists:convenios,id',
            // PASO 3: Membresía
            'id_membresia' => 'required|exists:membresias,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            // PASO 4: Pago
            'monto_abonado' => 'required|numeric|min:0.01',
            'id_metodo_pago' => 'required|exists:metodos_pago,id',
            'fecha_pago' => 'required|date|before_or_equal:today',
        ]);

        // Crear cliente
        $cliente = Cliente::create([
            ...$validated,
            'activo' => true,
        ]);

        // Obtener membresía y precio
        $membresia = Membresia::findOrFail($validated['id_membresia']);
        $precioActual = PrecioMembresia::where('id_membresia', $membresia->id)
            ->whereNull('fecha_vigencia_hasta')
            ->firstOrFail();

        // Calcular precio final con descuento si aplica
        $precioFinal = $precioActual->precio_normal;
        $descuento = 0;

        // Si tiene convenio Y existe precio_convenio para esta membresía, aplicar descuento
        if ($cliente->id_convenio && $precioActual->precio_convenio) {
            $precioFinal = $precioActual->precio_convenio;
            $descuento = $precioActual->precio_normal - $precioActual->precio_convenio;
        }

        // Crear inscripción
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaVencimiento = $fechaInicio->clone()->addDays($membresia->duracion_dias);

        $inscripcion = Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $membresia->id,
            'id_precio_acordado' => $precioActual->id,
            'id_convenio' => $cliente->id_convenio,
            'id_motivo_descuento' => null,
            'fecha_inscripcion' => Carbon::now(),
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'precio_base' => $precioActual->precio_normal,
            'descuento_aplicado' => $descuento,
            'precio_final' => $precioFinal,
            'id_estado' => 100, // Activa
        ]);

        // Crear pago
        Pago::create([
            'uuid' => Str::uuid(),
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => $precioFinal,
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => max(0, $precioFinal - $validated['monto_abonado']),
            'fecha_pago' => Carbon::parse($validated['fecha_pago']),
            'periodo_inicio' => $fechaInicio,
            'periodo_fin' => $fechaVencimiento,
            'id_metodo_pago' => $validated['id_metodo_pago'],
            'id_estado' => $validated['monto_abonado'] >= $precioFinal ? 201 : 200, // Pagado(201) o Pendiente(200)
            'cantidad_cuotas' => 1,
            'numero_cuota' => 1,
            'monto_cuota' => $precioFinal,
        ]);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente registrado exitosamente con membresía y pago.');
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
                ->with('error', 'Este cliente ya está activo.');
        }

        // Reactivar
        $cliente->update(['activo' => true]);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente reactivado exitosamente. Ahora aparecerá en el listado de clientes activos.');
    }

    /**
     * API: Obtener precio de membresía (normal o con descuento por convenio)
     */
    public function getPrecioMembresia($membresia_id)
    {
        $convenio_id = request('convenio');
        
        // Obtener el precio actual de la membresía
        $precioActual = PrecioMembresia::where('id_membresia', $membresia_id)
            ->whereNull('fecha_vigencia_hasta')
            ->orWhere('fecha_vigencia_hasta', '>=', now())
            ->first();
        
        if (!$precioActual) {
            return response()->json(['error' => 'Precio no encontrado'], 404);
        }
        
        $precioNormal = $precioActual->precio_normal;
        $precioFinal = $precioNormal;
        $descuento = 0;
        
        // Si hay convenio y existe precio_convenio, aplicar descuento
        if ($convenio_id && $precioActual->precio_convenio) {
            $precioFinal = $precioActual->precio_convenio;
            $descuento = $precioNormal - $precioFinal;
        }
        
        return response()->json([
            'precio_normal' => $precioNormal,
            'precio_convenio' => $precioActual->precio_convenio,
            'precio_final' => $precioFinal,
            'descuento' => $descuento,
            'tiene_descuento' => $descuento > 0
        ]);
    }
}
