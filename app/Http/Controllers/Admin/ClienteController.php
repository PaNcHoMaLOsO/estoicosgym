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
use App\Services\RegistroClienteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

        // Total de clientes eliminados (SoftDelete)
        $totalEliminados = Cliente::onlyTrashed()->count();

        // Preparar datos de clientes para JavaScript
        $clientesData = $this->prepareClientesData($clientes);

        return view('admin.clientes.index', compact(
            'clientes',
            'clientesData',
            'totalClientes', 
            'clientesActivos', 
            'clientesVencidos', 
            'clientesPausados',
            'clientesSinMembresia',
            'totalEliminados'
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
                'foto_perfil' => $cliente->foto_perfil
                    ? asset('storage/' . $cliente->foto_perfil)
                    : null,
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
    public function store(Request $request, RegistroClienteService $registro)
    {
        // Validar que no sea doble envío
        if (!$this->validateFormToken($request, 'cliente_create')) {
            return back()->with('error', 'Formulario duplicado. Por favor, intente nuevamente.');
        }

        // Las validaciones, los precios y la transaccion viven en el servicio:
        // el panel de React hace esta misma alta y no puede haber dos copias de
        // trescientas lineas que se separen a la primera correccion.
        try {
            $datos = $registro->validar($request);
            $resultado = $registro->registrar($datos, $request->file('foto_perfil'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al crear cliente: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Error al procesar el registro. Por favor intente nuevamente.');
        }

        $this->invalidateFormToken($request, 'cliente_create');

        return redirect()->route('admin.clientes.show', $resultado['cliente'])
            ->with('success', $resultado['mensaje']);
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

        // Reglas de validación mejoradas
        $rules = [
            // RUT con validación personalizada
            'run_pasaporte' => ['nullable', Rule::unique('clientes', 'run_pasaporte')->ignore($cliente->id), new RutValido()],
            
            // NOMBRES - Solo letras, sin espacios dobles, máx 50 caracteres
            'nombres' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/',
                function ($attribute, $value, $fail) {
                    if (preg_match('/\s{2,}/', $value)) {
                        $fail('El nombre no debe tener espacios dobles.');
                    }
                },
            ],
            
            'apellido_paterno' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/',
                function ($attribute, $value, $fail) {
                    if (preg_match('/\s{2,}/', $value)) {
                        $fail('El apellido no debe tener espacios dobles.');
                    }
                },
            ],
            
            'apellido_materno' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]*$/',
            ],
            
            // CELULAR - Formato chileno (9 dígitos empezando con 9, permite espacios)
            'celular' => [
                'required',
                'string',
                'regex:/^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/',
            ],
            
            // EMAIL - Formato válido, único excepto el actual
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($cliente->id),
            ],
            
            'direccion' => 'nullable|string|max:500',
            
            // FECHA NACIMIENTO - Mínimo 14 años, máximo 110 años
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before_or_equal:' . now()->subYears(14)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(110)->format('Y-m-d'),
            ],
            
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => ['nullable', 'string', 'regex:/^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/'],
            'id_convenio' => 'nullable|exists:convenios,id',
            'observaciones' => 'nullable|string|max:500',
            'activo' => 'boolean',
            
            // Campos de apoderado (opcionales por defecto)
            'es_menor_edad' => 'nullable|boolean',
            'consentimiento_apoderado' => 'nullable',
            'apoderado_nombre' => 'nullable|string|max:255',
            'apoderado_rut' => ['nullable', new RutValido()],
            'apoderado_email' => 'nullable|email:rfc|max:100',
            'apoderado_telefono' => 'nullable|string|max:20',
            'apoderado_parentesco' => 'nullable|string|max:100',
            'apoderado_observaciones' => 'nullable|string|max:500',
        ];

        // Mensajes personalizados
        $messages = [
            'nombres.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombres.max' => 'El nombre no debe exceder 50 caracteres.',
            'apellido_paterno.regex' => 'El apellido solo debe contener letras y espacios.',
            'apellido_paterno.max' => 'El apellido no debe exceder 50 caracteres.',
            'apellido_materno.regex' => 'El apellido materno solo debe contener letras y espacios.',
            'fecha_nacimiento.before_or_equal' => 'El cliente debe tener al menos 14 años.',
            'fecha_nacimiento.after_or_equal' => 'La fecha de nacimiento no es válida (máximo 110 años).',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado en otro cliente.',
            'celular.regex' => 'Formato de celular inválido. Use: 912345678 o +56912345678',
            'telefono_emergencia.regex' => 'Formato de teléfono de emergencia inválido.',
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
            $rules['apoderado_email'] = 'required|email:rfc|max:100';
            $rules['apoderado_telefono'] = 'required|string|max:20';
            $rules['apoderado_parentesco'] = 'required|string|max:100';
            
            $messages['apoderado_email.required'] = 'El email del apoderado es obligatorio.';
            $messages['apoderado_email.email'] = 'El email del apoderado no es válido.';
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
            $validated['apoderado_email'] = null;
            $validated['apoderado_telefono'] = null;
            $validated['apoderado_parentesco'] = null;
            $validated['apoderado_observaciones'] = null;
        }

        $cliente->update($validated);

        // Actualizar foto de perfil si se envió una nueva
        if ($request->hasFile('foto_perfil')) {
            // Borrar la anterior
            if ($cliente->foto_perfil) {
                \Storage::disk('public')->delete($cliente->foto_perfil);
            }
            $cliente->foto_perfil = $request->file('foto_perfil')
                ->store('clientes', 'public');
            $cliente->save();
        }

        // Eliminar foto si el usuario la quitó
        if ($request->input('eliminar_foto') === '1' && $cliente->foto_perfil) {
            \Storage::disk('public')->delete($cliente->foto_perfil);
            $cliente->foto_perfil = null;
            $cliente->save();
        }

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     * Implementa SoftDelete: envía el cliente a la papelera
     */
    public function destroy(Cliente $cliente)
    {
        // Validar que no tenga inscripciones activas o pausadas 
        $estadosReqClienteActivo = EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO;
        if ($cliente->inscripciones()->whereIn('id_estado', $estadosReqClienteActivo)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede eliminar este cliente. Tiene inscripciones activas o pausadas. Por favor, venza o cancele estas inscripciones primero.');
        }

        // Validar que no tenga pagos pendientes o parciales
        $estadosPagoPendientes = EstadosCodigo::PAGO_PENDIENTES_COBRO;
        if ($cliente->pagos()->whereIn('id_estado', $estadosPagoPendientes)->exists()) {
            return redirect()->route('admin.clientes.show', $cliente)
                ->with('error', 'No se puede eliminar este cliente. Tiene pagos pendientes. Por favor, procese estos pagos primero.');
        }

        $nombreCliente = $cliente->nombres . ' ' . $cliente->apellido_paterno;

        // Borrar foto de perfil si existe
        if ($cliente->foto_perfil) {
            \Storage::disk('public')->delete($cliente->foto_perfil);
        }

        // SoftDelete: enviar a papelera
        $cliente->delete();

        return redirect()->route('admin.clientes.index')
            ->with('success', "Cliente '{$nombreCliente}' enviado a la papelera. Puede restaurarlo desde la papelera si lo necesita.");
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

    // ==========================================
    // PAPELERA (SoftDeletes)
    // ==========================================

    /**
     * Mostrar clientes eliminados (papelera)
     */
    public function trashed()
    {
        $clientes = Cliente::onlyTrashed()
            ->with(['inscripciones' => function ($q) {
                $q->withTrashed();
            }])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        $totalEliminados = Cliente::onlyTrashed()->count();

        return view('admin.clientes.trashed', compact('clientes', 'totalEliminados'));
    }

    /**
     * Restaurar un cliente eliminado
     */
    public function restore($id)
    {
        $cliente = Cliente::onlyTrashed()->findOrFail($id);
        $cliente->restore();

        return redirect()->route('admin.clientes.trashed')
            ->with('success', "Cliente '{$cliente->nombres} {$cliente->apellido_paterno}' restaurado exitosamente.");
    }

    /**
     * Eliminar permanentemente un cliente
     */
    public function forceDelete($id)
    {
        $cliente = Cliente::onlyTrashed()->findOrFail($id);
        
        // Verificar que no tenga inscripciones (ni eliminadas)
        if ($cliente->inscripciones()->withTrashed()->exists()) {
            return redirect()->route('admin.clientes.trashed')
                ->with('error', 'No se puede eliminar permanentemente. El cliente tiene inscripciones asociadas. Elimine primero las inscripciones.');
        }

        $nombreCliente = "{$cliente->nombres} {$cliente->apellido_paterno}";
        $cliente->forceDelete();

        return redirect()->route('admin.clientes.trashed')
            ->with('success', "Cliente '{$nombreCliente}' eliminado permanentemente.");
    }
}
