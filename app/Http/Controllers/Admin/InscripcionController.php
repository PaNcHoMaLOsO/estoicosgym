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
        // - Mostrar TODOS los clientes activos
        // (El filtrado de inscripciones vigentes sin pagar se hace en el frontend/JS)
        
        $clientes = Cliente::where('activo', true)
            ->orderBy('nombres')
            ->get();

        $estados = Estado::where('categoria', 'membresia')->get();
        $estadoActiva = Estado::where('codigo', 100)->first(); // Estado "Activa"
        $membresias = Membresia::all();
        $convenios = Convenio::all();
        $motivos = MotivoDescuento::all();
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
        ]);

        // Obtener datos de membresía y calcular precios
        $membresia = Membresia::findOrFail($validated['id_membresia']);
        $precioBase = $this->obtenerPrecioMembresia($membresia, $validated);
        $descuentoTotal = $this->calcularDescuentoTotal($membresia, $validated, $precioBase);
        $precioFinal = $precioBase - $descuentoTotal;

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

        // Crear pago inicial si no es pendiente
        if (!$pagoPendiente) {
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
        
        // Descuento automático del convenio (solo para mensual con convenio)
        if ($validated['id_convenio'] && $membresia->id === 4) {
            $precioMembresia = $membresia->precios()
                ->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderBy('fecha_vigencia_desde', 'desc')
                ->first();
            
            if ($precioMembresia && $precioMembresia->precio_convenio) {
                $descuentoConvenio = $precioBase - $precioMembresia->precio_convenio;
            }
        }
        
        $descuentoAdicional = $validated['descuento_aplicado'] ?? 0;
        return $descuentoConvenio + $descuentoAdicional;
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
        $idEstadoPago = $montoAbonado >= $precioFinal ? 102 : 103; // 102=Pagado, 103=Parcial

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
     * Display the specified resource.
     * 
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\View\View
     */
    public function show(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'estado', 'pagos']);
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
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Inscripcion $inscripcion
     * @return \Illuminate\Http\RedirectResponse
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
}
