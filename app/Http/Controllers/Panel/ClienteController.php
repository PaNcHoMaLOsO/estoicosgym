<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Services\BorradoDeDatosService;
use App\Services\ContratoDigitalService;
use App\Services\RegistroClienteService;
use App\Support\Ajustes;
use App\Support\TextosLegales;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        /*
         * Los dados de baja se ven aparte, no mezclados.
         *
         * El listado ensena solo a quien esta activo, que es a quien se
         * atiende. Pero si no hubiera forma de ver a los otros, dar de baja a
         * alguien lo haria desaparecer del panel entero y no habria manera de
         * reactivarlo salvo sabiendose la URL de su ficha.
         */
        $verBajas = $request->boolean('bajas');

        $clientes = Cliente::query()
            ->where('activo', ! $verBajas)
            // Una ficha con los datos borrados ya no es nadie a quien atender:
            // sus pagos siguen en los informes, pero aquí no aparece.
            ->whereNull('datos_borrados_en')
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
                    'foto' => $cliente->urlDeFoto(),
                    'email' => $cliente->email,
                    'celular' => $cliente->celular,
                    'membresia' => $inscripcion?->membresia?->nombre,
                    'id_estado' => $inscripcion?->id_estado,
                    'vence' => $inscripcion?->fecha_vencimiento?->format('d/m/Y'),
                ];
            });

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filtros' => ['buscar' => $busqueda, 'bajas' => $verBajas],
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
    public function store(Request $request, RegistroClienteService $registro, ContratoDigitalService $contratos)
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

        $mensaje = $resultado['mensaje'];
        $problema = null;

        // El contrato por correo, si se pidió. Que no salga NO deshace el alta:
        // el socio queda registrado y el contrato se manda después desde su ficha.
        if ($request->boolean('enviar_contrato')) {
            try {
                $contrato = $contratos->enviar($resultado['cliente']);
                $mensaje .= " Le mandamos el contrato para firmar a {$contrato->email_destino}.";
            } catch (ValidationException $e) {
                $problema = 'El contrato no salió: ' . collect($e->errors())->flatten()->first() . ' Mándalo desde su ficha.';
            }
        }

        return redirect()->route('panel.clientes.index')
            ->with('success', $mensaje)
            ->with('error', $problema);
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
        if ($cerrada = $this->fichaCerrada($cliente)) {
            return $cerrada;
        }

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
        if ($cerrada = $this->fichaCerrada($cliente)) {
            return $cerrada;
        }

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
     * Constancia del contrato firmado y de lo que el socio autorizo.
     *
     * SE FIRMA EN PAPEL. Esto solo anota que firmo, que dia y que version, mas
     * los dos permisos —foto interna y difusion— que van separados porque un
     * consentimiento sirve para una finalidad y no para la de al lado.
     */
    public function contrato(Request $request, Cliente $cliente, RegistroClienteService $registro)
    {
        if ($cerrada = $this->fichaCerrada($cliente)) {
            return $cerrada;
        }

        $datos = $request->validate([
            'contrato_version' => ['nullable', 'string', 'max:20'],
            // Una firma con fecha futura es un dedazo, no un contrato.
            'contrato_firmado_en' => ['nullable', 'date', 'before_or_equal:today'],
            'consentimiento_imagen' => ['boolean'],
            'consentimiento_difusion' => ['boolean'],
        ], [
            'contrato_firmado_en.before_or_equal' => 'La fecha de la firma no puede ser futura.',
        ]);

        // Si se anota la fecha pero no la version, se toma la que se esta
        // haciendo firmar hoy: es lo que acaba de pasar en el meson.
        if (($datos['contrato_firmado_en'] ?? null) && empty($datos['contrato_version'])) {
            $datos['contrato_version'] = (string) TextosLegales::vigente('contrato')->version;
        }

        $registro->registrarContrato($cliente, $datos + [
            'contrato_version' => null,
            'contrato_firmado_en' => null,
            'consentimiento_imagen' => false,
            'consentimiento_difusion' => false,
        ]);

        return back()->with('success', 'Contrato y permisos guardados.');
    }

    /**
     * La foto del socio: ponerla, cambiarla o quitarla.
     *
     * VA POR SU CUENTA y no dentro de la edicion de la ficha. Mandar un archivo
     * obliga a enviar el formulario como multipart, e Inertia no sabe hacer eso
     * con un PUT: habria que falsear el metodo en todo el formulario de edicion
     * —veinte campos— por un campo que casi nunca se toca. Ademas se pone donde
     * tiene sentido: en la ficha, con la persona delante.
     */
    public function foto(Request $request, Cliente $cliente, RegistroClienteService $registro)
    {
        if ($cerrada = $this->fichaCerrada($cliente)) {
            return $cerrada;
        }

        $quitar = $request->boolean('quitar');

        $request->validate(
            ['foto_perfil' => RegistroClienteService::REGLAS_FOTO],
            RegistroClienteService::MENSAJES_FOTO
        );

        // Sin archivo y sin querer quitarla no hay nada que hacer. Se avisa en
        // vez de callar: un «guardado» sin cambios es peor que un error.
        if (! $quitar && ! $request->hasFile('foto_perfil')) {
            return back()->with('error', 'No llegó ninguna foto.');
        }

        $registro->cambiarFoto($cliente, $request->file('foto_perfil'), $quitar);

        return back()->with('success', $quitar ? 'Foto quitada.' : 'Foto guardada.');
    }

    /**
     * Lo deja fuera de la lista sin borrar nada.
     *
     * Es para quien dejó de venir: sigue estando su ficha, su historial y sus
     * pagos, pero no aparece al inscribir ni al cobrar.
     */
    public function desactivar(Cliente $cliente)
    {
        if (! $cliente->activo) {
            return back()->with('error', 'Ese socio ya estaba desactivado.');
        }

        if ($motivo = $this->porQueSigueEnActivo($cliente)) {
            return back()->with('error', $motivo);
        }

        $cliente->update(['activo' => false]);

        return back()->with(
            'success',
            "{$cliente->nombres} {$cliente->apellido_paterno} queda desactivado. Su ficha y su historial siguen ahí."
        );
    }

    public function reactivar(Cliente $cliente)
    {
        if ($cliente->datos_borrados_en) {
            return back()->with('error', 'Sus datos personales se borraron: esa ficha ya no es de nadie. Si vuelve, se inscribe como socio nuevo.');
        }

        if ($cliente->activo) {
            return back()->with('error', 'Ese socio ya estaba activo.');
        }

        $cliente->update(['activo' => true]);

        return back()->with(
            'success',
            "{$cliente->nombres} {$cliente->apellido_paterno} vuelve a estar activo."
        );
    }

    /**
     * A la papelera, de donde se puede sacar.
     *
     * NO se borra la foto. El de Blade la borraba del disco antes del borrado
     * suave, así que restaurar al socio devolvía la ficha pero no la foto: se
     * perdía para siempre en una acción que se presenta como reversible.
     */
    public function eliminar(Cliente $cliente)
    {
        if ($motivo = $this->porQueSigueEnActivo($cliente)) {
            return back()->with('error', $motivo);
        }

        $nombre = trim("{$cliente->nombres} {$cliente->apellido_paterno}");
        $cliente->delete();

        return redirect()->route('panel.clientes.index')->with(
            'success',
            "{$nombre} está en la papelera. Se puede recuperar desde ahí."
        );
    }

    /**
     * Borra sus datos personales (Ley 21.719) y deja sus pagos en las cuentas.
     *
     * Se confirma escribiendo BORRAR: no se deshace, y un clic de más en el
     * mesón no puede costarle a nadie su historial.
     */
    public function borrarDatos(Request $request, Cliente $cliente, BorradoDeDatosService $borrado)
    {
        $request->merge(['confirmacion' => mb_strtoupper(trim((string) $request->input('confirmacion')))]);

        $datos = $request->validate([
            'motivo' => ['required', Rule::in(array_keys(BorradoDeDatosService::MOTIVOS))],
            'confirmacion' => ['required', 'in:BORRAR'],
        ], [
            'motivo.required' => 'Elige por qué se borran.',
            'confirmacion.required' => 'Escribe BORRAR para confirmar.',
            'confirmacion.in' => 'Escribe BORRAR para confirmar.',
        ]);

        try {
            $borrado->borrar($cliente, $request->user()?->id, $datos['motivo']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()
            ->route('panel.clientes.show', $cliente->uuid)
            ->with('success', 'Se borraron sus datos personales. Sus membresías y pagos siguen en las cuentas, sin nombre.');
    }

    /**
     * A una ficha con los datos borrados no se le vuelve a poner nada.
     *
     * Editarla, ponerle foto o anotarle un contrato le devolvería un nombre a
     * pagos que ya no son de nadie.
     */
    private function fichaCerrada(Cliente $cliente)
    {
        return $cliente->datos_borrados_en
            ? redirect()->route('panel.clientes.show', $cliente->uuid)
                ->with('error', 'Sus datos personales se borraron: esta ficha ya no se puede cambiar.')
            : null;
    }

    /**
     * Lo que impide darlo de baja, o null.
     *
     * Las dos razones son de dinero y de acceso: una membresía viva le deja
     * entrar al gimnasio, y un saldo sin cobrar desaparecería del listado de
     * «por cobrar» en cuanto el socio dejara de estar activo.
     */
    private function porQueSigueEnActivo(Cliente $cliente): ?string
    {
        $vigente = $cliente->inscripciones()
            ->whereIn('id_estado', EstadosCodigo::INSCRIPCION_REQUIERE_CLIENTE_ACTIVO)
            ->exists();

        if ($vigente) {
            return 'Tiene una membresía vigente o pausada. Espera a que venza, o cancélala primero.';
        }

        $debe = $cliente->pagos()
            ->whereIn('id_estado', EstadosCodigo::PAGO_PENDIENTES_COBRO)
            ->exists();

        if ($debe) {
            return 'Tiene pagos pendientes. Cóbralos o cancélalos antes de darlo de baja.';
        }

        return null;
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
            // Para poder ofrecer el enlace solo cuando hay alguno.
            'bajas' => Cliente::where('activo', false)->whereNull('datos_borrados_en')->count(),
        ];
    }
}
