<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\PrecioMembresia;
use App\Rules\RutValido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
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
     * Soporta dos flujos:
     * 1. save_cliente: Solo registro del cliente
     * 2. save_completo: Registro + Inscripción + Pago en una transacción
     */
    public function store(Request $request)
    {
        // Validar datos básicos del cliente
        $validated = $request->validate([
            'run_pasaporte' => ['required', 'unique:clientes', new RutValido()],
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'email' => 'required|email|unique:clientes',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20|regex:/^\+?[\d\s\-()]{9,}$/',
            'id_convenio' => 'nullable|exists:convenios,id',
            'observaciones' => 'nullable|string|max:500',
            'action' => 'required|in:save_cliente,save_completo',
        ]);

        // Crear cliente
        $cliente = Cliente::create([
            ...$validated,
            'activo' => true,
        ]);

        // Flujo 1: Solo guardar cliente
        if ($request->input('action') === 'save_cliente') {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('success', 'Cliente registrado exitosamente. Puedes crear su inscripción más tarde.');
        }

        // Flujo 2: Registro + Inscripción + Pago
        $this->validarYCrearInscripcionConPago($request, $cliente);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente registrado con inscripción y pago exitosamente');
    }

    /**
     * Validar y crear inscripción con pago
     */
    private function validarYCrearInscripcionConPago(Request $request, Cliente $cliente)
    {
        // Validar datos de inscripción
        $datosInscripcion = $request->validate([
            'id_membresia' => 'required|exists:membresias,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'id_convenio_inscripcion' => 'nullable|exists:convenios,id',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
        ]);

        // Validar datos de pago
        $datosPago = $request->validate([
            'monto_abonado' => 'required|numeric|min:0.01',
            'id_metodo_pago' => 'required|exists:metodos_pago,id',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
        ]);

        $membresia = Membresia::findOrFail($datosInscripcion['id_membresia']);
        $precioActual = PrecioMembresia::where('id_membresia', $membresia->id)
            ->whereNull('fecha_vigencia_hasta')
            ->firstOrFail();

        $fechaInicio = Carbon::parse($datosInscripcion['fecha_inicio']);
        $fechaVencimiento = $fechaInicio->clone()->addDays($membresia->duracion_dias);
        $descuento = $datosInscripcion['descuento_aplicado'] ?? 0;
        $precioFinal = $precioActual->precio_normal - $descuento;

        // Crear inscripción
        $inscripcion = Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $membresia->id,
            'id_precio_acordado' => $precioActual->id,
            'id_convenio' => $datosInscripcion['id_convenio_inscripcion'],
            'id_motivo_descuento' => $datosInscripcion['id_motivo_descuento'],
            'fecha_inscripcion' => Carbon::now(),
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'precio_base' => $precioActual->precio_normal,
            'descuento_aplicado' => $descuento,
            'precio_final' => $precioFinal,
            'id_estado' => 100, // Activa
        ]);

        // Crear pago
        $cantidadCuotas = $datosPago['cantidad_cuotas'] ?? 1;
        $montoPorCuota = $precioFinal / $cantidadCuotas;

        Pago::create([
            'uuid' => Str::uuid(),
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => $precioFinal,
            'monto_abonado' => $datosPago['monto_abonado'],
            'monto_pendiente' => max(0, $precioFinal - $datosPago['monto_abonado']),
            'fecha_pago' => Carbon::parse($datosPago['fecha_pago']),
            'periodo_inicio' => $fechaInicio,
            'periodo_fin' => $fechaVencimiento,
            'id_metodo_pago' => $datosPago['id_metodo_pago'],
            'id_estado' => $datosPago['monto_abonado'] >= $precioFinal ? 201 : 200, // Pagado(201) o Pendiente(200)
            'cantidad_cuotas' => $cantidadCuotas,
            'numero_cuota' => 1,
            'monto_cuota' => $montoPorCuota,
        ]);
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
        $validated = $request->validate([
            'run_pasaporte' => ['required', 'unique:clientes,run_pasaporte,' . $cliente->id, new RutValido()],
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
}
