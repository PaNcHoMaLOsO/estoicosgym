<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Services\RegistroClienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Clientes del panel nuevo (Inertia + React).
 *
 * Convive con Admin\ClienteController, que sigue sirviendo las vistas Blade
 * mientras dure la migracion. Cuando /panel cubra todo, /admin se redirige aqui
 * y aquel se borra.
 */
class ClienteController extends Controller
{
    use ValidatesFormToken;

    /**
     * La busqueda y la paginacion se hacen EN LA BASE y no en el navegador.
     *
     * La pantalla Blade traia 100 filas de golpe y filtraba con jQuery: con
     * 2.000 socios eso son 2.000 filas viajando en cada carga para enseñar 25.
     */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        $clientes = Cliente::query()
            ->where('activo', true)
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('celular', 'like', "%{$busqueda}%");
                });
            })
            // Solo la inscripcion mas reciente: es la unica que se pinta en la
            // fila, y traerlas todas multiplicaba las consultas por socio.
            ->with(['inscripciones' => fn ($q) => $q->latest('fecha_vencimiento')->with('membresia')->limit(1)])
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Cliente $cliente) {
                $inscripcion = $cliente->inscripciones->first();

                return [
                    'uuid' => $cliente->uuid,
                    'run_pasaporte' => $cliente->run_pasaporte,
                    'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                    'email' => $cliente->email,
                    'celular' => $cliente->celular,
                    'membresia' => $inscripcion?->membresia?->nombre,
                    'id_estado' => $inscripcion?->id_estado,
                    'vence' => $inscripcion?->fecha_vencimiento?->format('d/m/Y'),
                ];
            });

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filtros' => ['buscar' => $busqueda],
            'resumen' => $this->resumen(),
        ]);
    }

    /**
     * Formulario de alta.
     *
     * Los catalogos viajan como props: el formulario los necesita ya cargados
     * para pintar los desplegables, y pedirlos por separado al abrir la
     * pantalla habria sido una segunda vuelta al servidor para nada.
     */
    public function create()
    {
        return Inertia::render('Clientes/Crear', [
            'membresias' => Membresia::where('activo', true)
                ->with(['precios' => fn ($q) => $q->where('activo', true)])
                ->orderBy('nombre')
                ->get()
                ->map(fn (Membresia $m) => [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'dias' => $m->duracion_dias,
                    'precio' => (int) ($m->precios->first()->precio_normal ?? 0),
                    'precio_convenio' => (int) ($m->precios->first()->precio_convenio ?? 0),
                ]),
            'convenios' => Convenio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'motivos' => MotivoDescuento::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'metodosPago' => MetodoPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            // Mismo token anti-doble-envio que usa el panel Blade.
            'formToken' => (string) Str::uuid(),
        ]);
    }

    /**
     * Alta del socio.
     *
     * La logica esta en RegistroClienteService, el MISMO que usa el panel
     * Blade: validaciones, calculo de precios y la transaccion de las tres
     * tablas. Aqui solo se decide a donde volver.
     */
    public function store(Request $request, RegistroClienteService $registro)
    {
        // Validar PRIMERO. Si se reservase el turno antes, un formulario con un
        // error de dato lo dejaria pillado y al corregirlo no se podria enviar.
        $datos = $registro->validar($request);

        if (! $this->validateFormToken($request, 'cliente_create')) {
            return back()->with('error', 'Este registro ya se envió. Revisa la lista antes de repetirlo.');
        }

        try {
            $resultado = $registro->registrar($datos, $request->file('foto_perfil'));
        } catch (\Throwable $e) {
            report($e);
            // No se creo nada: se devuelve el turno para poder reintentar.
            $this->releaseFormToken($request, 'cliente_create');

            return back()->withInput()->with('error', 'Error al procesar el registro. Por favor intente nuevamente.');
        }

        return redirect()->route('panel.clientes.index')->with('success', $resultado['mensaje']);
    }

    /**
     * La ficha de un socio, para corregirla.
     *
     * Solo sus datos: la membresía y los pagos NO se tocan aquí, tienen sus
     * propias pantallas —renovar, cobrar— con sus propias reglas. Esto es para
     * arreglar un teléfono mal escrito, no para cambiar lo que se cobró.
     */
    public function edit(Cliente $cliente)
    {
        return Inertia::render('Clientes/Editar', [
            'cliente' => [
                'uuid' => $cliente->uuid,
                'run_pasaporte' => $cliente->run_pasaporte,
                'nombres' => $cliente->nombres,
                'apellido_paterno' => $cliente->apellido_paterno,
                'apellido_materno' => $cliente->apellido_materno,
                'celular' => $cliente->celular,
                'email' => $cliente->email,
                'direccion' => $cliente->direccion,
                'fecha_nacimiento' => $cliente->fecha_nacimiento?->format('Y-m-d'),
                'contacto_emergencia' => $cliente->contacto_emergencia,
                'telefono_emergencia' => $cliente->telefono_emergencia,
                'observaciones' => $cliente->observaciones,
                'es_menor_edad' => (bool) $cliente->es_menor_edad,
                'consentimiento_apoderado' => (bool) $cliente->consentimiento_apoderado,
                'apoderado_nombre' => $cliente->apoderado_nombre,
                'apoderado_rut' => $cliente->apoderado_rut,
                'apoderado_email' => $cliente->apoderado_email,
                'apoderado_telefono' => $cliente->apoderado_telefono,
                'apoderado_parentesco' => $cliente->apoderado_parentesco,
                'apoderado_observaciones' => $cliente->apoderado_observaciones,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
            ],
        ]);
    }

    public function update(Request $request, Cliente $cliente, RegistroClienteService $registro)
    {
        $datos = $registro->validarEdicion($request, $cliente);

        try {
            $registro->actualizar($cliente, $datos);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'No se pudieron guardar los cambios. Inténtalo nuevamente.');
        }

        return redirect()
            ->route('panel.clientes.show', $cliente->uuid)
            ->with('success', 'Ficha actualizada.');
    }

    /**
     * Cuatro cifras, no diez: son las que se miran al abrir.
     *
     * Los codigos son los de la tabla estados (100 activa, 101 pausada,
     * 102 vencida); id_estado guarda el CODIGO, no el id de la fila.
     */
    private function resumen(): array
    {
        $conEstado = fn (int $codigo) => Cliente::where('activo', true)
            ->whereHas('inscripciones', fn ($q) => $q->where('id_estado', $codigo))
            ->count();

        return [
            'total' => Cliente::where('activo', true)->count(),
            'activos' => $conEstado(100),
            'pausados' => $conEstado(101),
            'vencidos' => $conEstado(102),
        ];
    }
}
