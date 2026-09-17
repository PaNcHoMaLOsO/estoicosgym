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
use App\Services\RegistroInscripcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Alta de inscripción desde el panel nuevo.
 *
 * Va aparte de Panel\InscripcionController —que lista— porque no comparten
 * nada: uno pagina y este calcula precios y cobra.
 *
 * Los cálculos y las validaciones están en RegistroInscripcionService, el mismo
 * que usa la renovación, para que las dos pantallas no se separen.
 */
class InscripcionCrearController extends Controller
{
    /** Cuántos socios devuelve cada búsqueda. */
    private const RESULTADOS = 15;

    use ValidatesFormToken;

    public function create(Request $request)
    {
        return Inertia::render('Inscripciones/Crear', [
            /*
             * NO va la lista de socios.
             *
             * La pantalla vieja cargaba TODOS los clientes activos y además,
             * por cada uno, todas sus inscripciones, para después filtrar en el
             * navegador. Con el gimnasio en marcha eso son miles de filas para
             * elegir una. Aquí se busca y solo viaja lo que se escribe.
             */
            'preseleccionado' => $this->preseleccionado($request->query('cliente')),
            'membresias' => $this->planesCobrables(),
            'convenios' => Convenio::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'motivos' => MotivoDescuento::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'metodosPago' => MetodoPago::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'requiere_comprobante']),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    /**
     * Busca a quién inscribir.
     *
     * Devuelve solo socios QUE SE PUEDEN INSCRIBIR HOY: activos y sin membresía
     * vigente. Ofrecer a alguien que ya está inscrito es el error que esta
     * pantalla tiene que hacer difícil, porque el formulario lo rechaza al
     * final, después de haberlo rellenado entero.
     */
    public function buscar(Request $request)
    {
        $texto = trim((string) $request->query('q', ''));

        // Con una letra saldría medio padrón y no serviría para elegir.
        if (mb_strlen($texto) < 2) {
            return response()->json(['clientes' => []]);
        }

        $clientes = $this->inscribibles()
            ->where(function ($q) use ($texto) {
                $q->where('nombres', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('run_pasaporte', 'like', "%{$texto}%")
                    ->orWhere('email', 'like', "%{$texto}%");
            })
            ->orderBy('apellido_paterno')
            ->limit(self::RESULTADOS)
            ->get()
            ->map(fn (Cliente $c) => $this->resumir($c));

        return response()->json(['clientes' => $clientes]);
    }

    public function store(Request $request, RegistroInscripcionService $registro)
    {
        // Validar PRIMERO: si se reservara el turno antes, un formulario
        // rechazado lo dejaría pillado y al corregirlo no se podría reenviar.
        $resultado = $registro->validar($request);

        if (! $this->validateFormToken($request, 'inscripcion_create')) {
            return back()->with('error', 'Esta inscripción ya se registró. Búscala en el listado antes de repetirla.');
        }

        try {
            $inscripcion = $registro->registrar($resultado);
        } catch (\Throwable $e) {
            Log::error('Error al inscribir desde el panel: ' . $e->getMessage());
            $this->releaseFormToken($request, 'inscripcion_create');

            return back()->withInput()->with('error', 'No se pudo crear la inscripción. Inténtalo nuevamente.');
        }

        $socio = $resultado['cliente'];
        $nombre = trim("{$socio->nombres} {$socio->apellido_paterno}");

        // El mensaje NO concuerda en genero con el nombre: la ficha del socio
        // no guarda ese dato, y adivinarlo por el nombre se equivoca con
        // cualquiera cuyo nombre no siga la regla. Se habla de la membresia.
        $plan = $resultado['membresia']->nombre;

        return redirect()->route('panel.inscripciones.show', $inscripcion->uuid)->with(
            'success',
            $resultado['abonos'] === []
                ? "Membresía {$plan} creada para {$nombre}. El pago queda pendiente de cobro."
                : "Membresía {$plan} creada para {$nombre}, con su pago registrado."
        );
    }

    /**
     * El socio con el que se llega desde su ficha.
     *
     * Devuelve null si ese socio no se puede inscribir: es mejor que la
     * pantalla abra vacía y se busque, a que abra con alguien preseleccionado
     * que el formulario va a rechazar al guardar.
     */
    private function preseleccionado(?string $uuid): ?array
    {
        if (! $uuid) {
            return null;
        }

        $cliente = $this->inscribibles()->where('uuid', $uuid)->first();

        return $cliente ? $this->resumir($cliente) : null;
    }

    /** Socios activos que hoy no tienen una membresía vigente ni pausada. */
    private function inscribibles()
    {
        return Cliente::query()
            ->where('activo', true)
            ->whereDoesntHave('inscripciones', fn ($q) => $q->whereIn('id_estado', [
                EstadosCodigo::INSCRIPCION_ACTIVA,
                EstadosCodigo::INSCRIPCION_PAUSADA,
            ]));
    }

    /** @return array<string,mixed> */
    private function resumir(Cliente $cliente): array
    {
        return [
            'id' => $cliente->id,
            'uuid' => $cliente->uuid,
            'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
            'rut' => $cliente->run_pasaporte,
            'email' => $cliente->email,
            'celular' => $cliente->celular,
            'menor' => (bool) $cliente->es_menor_edad,
        ];
    }

    /**
     * Los planes que se pueden vender hoy, con su precio ya resuelto.
     *
     * SE DEJAN FUERA los que no tienen precio vigente. Antes salían igual y se
     * inscribía a la gente en $0 sin que nadie se enterara hasta cuadrar la
     * caja; ahora el servicio los rechaza, así que tampoco tiene sentido
     * ofrecerlos.
     *
     * El precio viaja al navegador para poder ir mostrando el total mientras se
     * elige, sin una consulta por cada clic. Igual se recalcula en el servidor
     * al guardar: lo que llega del formulario no decide cuánto se cobra.
     *
     * @return list<array<string,mixed>>
     */
    private function planesCobrables(): array
    {
        return Membresia::with(['precios' => fn ($q) => $q
            ->where('activo', true)
            ->where('fecha_vigencia_desde', '<=', now())
            ->orderByDesc('fecha_vigencia_desde')])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Membresia $m) {
                $precio = $m->precios->first();

                if (! $precio) {
                    return null;
                }

                return [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'duracion' => $this->duracion($m),
                    'precio' => (int) round($precio->precio_normal),
                    'precio_convenio' => $precio->precio_convenio
                        ? (int) round($precio->precio_convenio)
                        : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function duracion(Membresia $membresia): string
    {
        if ($membresia->duracion_dias > 0) {
            // El Pase Diario dura 1 y quedaba escrito «1 días».
            return $membresia->duracion_dias === 1 ? '1 día' : "{$membresia->duracion_dias} días";
        }

        $meses = $membresia->duracion_meses ?? 1;

        return $meses === 1 ? '1 mes' : "{$meses} meses";
    }
}
