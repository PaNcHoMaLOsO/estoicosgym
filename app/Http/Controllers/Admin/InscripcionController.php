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
use Carbon\Carbon;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
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
        
        // Filtro por cliente
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nombres', 'like', '%' . $request->cliente . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $request->cliente . '%')
                  ->orWhere('email', 'like', '%' . $request->cliente . '%');
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
        
        // Ordenamiento - solo campos de la tabla inscripciones
        $ordenar = $request->get('ordenar', 'fecha_inicio');
        $direccion = $request->get('direccion', 'desc');
        
        // Validar que el campo sea válido
        $camposValidos = ['id', 'id_cliente', 'id_membresia', 'id_estado', 'fecha_inicio', 'fecha_vencimiento', 'precio_base', 'precio_final', 'created_at'];
        if (!in_array($ordenar, $camposValidos)) {
            $ordenar = 'fecha_inicio';
        }
        
        $query->orderBy($ordenar, $direccion);
        
        $inscripciones = $query->paginate(20);
        
        // Datos para los selects de filtro
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        
        return view('admin.inscripciones.index', compact('inscripciones', 'estados', 'membresias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Clientes con membresía VENCIDA
        $clientesConMembresiaVencida = Inscripcion::where('fecha_vencimiento', '<', now())
            ->pluck('id_cliente')->unique();

        $clientes = Cliente::where('activo', true)
            ->whereIn('id', $clientesConMembresiaVencida)
            ->orderBy('nombres')
            ->get();

        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
        $metodosPago = MetodoPago::where('activo', true)->get();

        return view('admin.inscripciones.create', compact('clientes', 'estados', 'membresias', 'convenios', 'motivos', 'metodosPago'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $pagoPendiente = $request->has('pago_pendiente');

        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_estado' => 'required|exists:estados,id',
            'fecha_inicio' => 'required|date',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
            'monto_abonado' => $pagoPendiente ? 'nullable' : 'required|numeric|min:0.01',
            'id_metodo_pago' => $pagoPendiente ? 'nullable' : 'required|exists:metodos_pago,id',
            'fecha_pago' => $pagoPendiente ? 'nullable' : 'required|date',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
            'fecha_vencimiento_cuota' => 'nullable|date',
        ]);

        // Obtener membresía y precio base
        $membresia = Membresia::findOrFail($validated['id_membresia']);
        $precioBase = $membresia->precio_normal;
        
        // Calcular descuento automático del convenio (membresía mensual + convenio = $15.000)
        $descuentoConvenio = 0;
        if ($validated['id_convenio'] && $membresia->id === 1) { // id=1 es membresía mensual
            $descuentoConvenio = 15000;
        }
        
        // El descuento total es: descuento automático del convenio + descuento adicional del usuario
        $descuentoAdicional = $validated['descuento_aplicado'] ?? 0;
        $descuentoTotal = $descuentoConvenio + $descuentoAdicional;
        $precioFinal = $precioBase - $descuentoTotal;

        // Calcular fecha de vencimiento usando duracion_dias
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        
        // Usar duracion_dias si está disponible, sino calcular desde duracion_meses
        if ($membresia->duracion_dias && $membresia->duracion_dias > 0) {
            // Para Pase Diario y otros: usar duracion_dias directamente
            $fechaVencimiento = $fechaInicio->clone()->addDays($membresia->duracion_dias)->subDay();
        } else {
            // Para membresías por meses: sumar meses y restar 1 día
            $duracionMeses = $membresia->duracion_meses ?? 1;
            $fechaVencimiento = $fechaInicio->clone()->addMonths($duracionMeses)->subDay();
        }

        // Crear inscripción
        $validated['precio_base'] = $precioBase;
        $validated['precio_final'] = $precioFinal;
        $validated['descuento_aplicado'] = $descuentoTotal; // Guardar el total (convenio + adicional)
        $validated['fecha_inscripcion'] = now()->format('Y-m-d');
        $validated['fecha_vencimiento'] = $fechaVencimiento->format('Y-m-d');
        $validated['id_precio_acordado'] = 1;

        $inscripcion = Inscripcion::create($validated);

        // Solo crear pago si no es pendiente
        if (!$pagoPendiente) {
            $cantidadCuotas = $validated['cantidad_cuotas'] ?? 1;
            $montoAbonado = $validated['monto_abonado'];
            $montoCuota = $precioFinal / $cantidadCuotas;
            $idEstadoPago = $montoAbonado >= $precioFinal ? 102 : 103; // 102=Pagado, 103=Parcial

            Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $validated['id_cliente'],
                'id_membresia' => $validated['id_membresia'],
                'monto_total' => $precioFinal,
                'monto_abonado' => $montoAbonado,
                'monto_pendiente' => max(0, $precioFinal - $montoAbonado),
                'cantidad_cuotas' => $cantidadCuotas,
                'numero_cuota' => 1,
                'monto_cuota' => $montoCuota,
                'fecha_vencimiento_cuota' => $validated['fecha_vencimiento_cuota'],
                'id_estado' => $idEstadoPago,
                'id_metodo_pago' => $validated['id_metodo_pago'],
                'fecha_pago' => $validated['fecha_pago'],
            ]);
        }

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción creada exitosamente' . ($pagoPendiente ? ' - Pago pendiente de registrar' : ' con pago registrado'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'estado', 'pagos']);
        $estadoPago = $inscripcion->obtenerEstadoPago();
        return view('admin.inscripciones.show', compact('inscripcion', 'estadoPago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'estado', 'membresia', 'convenio', 'motivoDescuento']);
        $clientes = Cliente::active()->get();
        $estados = Estado::where('categoria', 'membresia')->get();
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
        return view('admin.inscripciones.edit', compact('inscripcion', 'clientes', 'estados', 'membresias', 'convenios', 'motivos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscripcion $inscripcion)
    {
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

        // Calcular precio_final
        $validated['precio_final'] = $validated['precio_base'] - ($validated['descuento_aplicado'] ?? 0);

        $inscripcion->update($validated);

        return redirect()->route('admin.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inscripcion $inscripcion)
    {
        $inscripcion->delete();

        return redirect()->route('admin.inscripciones.index')
            ->with('success', 'Inscripción eliminada exitosamente');
    }
}
