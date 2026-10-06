<?php

namespace App\Http\Controllers\Panel;

use App\Support\Ajustes;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\FiadoRegistro;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Support\BusquedaDeSocio;

/**
 * La libreta de lo fiado en el mesón.
 *
 * Se apunta lo que alguien se lleva, se le va sumando cada vez, y cuando paga
 * se salda su cuenta. Si trae menos de lo que debe, abona: se cobra lo más
 * viejo primero y lo demás sigue debiéndose.
 */
class FiadoController extends Controller
{
    use ValidatesFormToken;

    /**
     * La pantalla propia de lo fiado.
     *
     * En el resumen hay un resumen: quién debe y cuánto, para el vistazo del
     * mesón. Aquí está lo demás —lo ya cobrado, mes a mes, con quién lo cobró—
     * porque eso no se mira todos los días pero hace falta cuando alguien
     * discute una cifra o hay que cuadrar el mes.
     *
     * Lo cobrado entra a la caja como «Mesón», el día que se cobró y con el
     * medio con que se pagó. Lo que se sigue debiendo no es ingreso.
     */
    public function index(Request $request)
    {
        $hoy = Carbon::today();
        $mes = $this->mes($request->texto('mes'));

        $cobradoEsteMes = (int) Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()])
            ->sum('monto');

        $puedeAdministrar = (bool) $request->user()?->puede('clientes.eliminar');

        return Inertia::render('Fiados', [
            // Con qué se cobra lo fiado (ConfirmarDinero los lee de aquí). Solo
            // en las pantallas donde se cobra: compartidos, se consultaban en
            // todas.
            'medios_de_pago' => \App\Models\MetodoPago::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'cuentas' => $this->cuentasPendientes(),
            'cobrado' => $this->loYaCobrado($mes),
            'mes' => $mes->format('Y-m'),
            // Los últimos doce meses, para elegir cuál mirar en «Ya pagado».
            'meses' => collect(range(0, 11))->map(fn (int $i) => [
                'valor' => $hoy->copy()->startOfMonth()->subMonthsNoOverflow($i)->format('Y-m'),
                'etiqueta' => ucfirst($hoy->copy()->startOfMonth()->subMonthsNoOverflow($i)->locale('es')->isoFormat('MMMM YYYY')),
            ])->all(),
            'cifras' => [
                'se_debe' => (int) Fiado::debiendo()->sum('monto'),
                'personas' => Fiado::debiendo()->get()->groupBy(fn (Fiado $f) => $f->claveDeCuenta())->count(),
                'cobrado_mes' => $cobradoEsteMes,
                // Lo cobrado HOY: es lo que se cuadra al cerrar el turno, y
                // hasta ahora había que sumarlo a ojo de la lista de pagados.
                'cobrado_hoy' => (int) Fiado::where('pagado', true)
                    ->whereBetween('pagado_en', [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()])
                    ->sum('monto'),
                'anotado_hoy' => (int) Fiado::whereBetween('created_at', [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()])->sum('monto'),
            ],
            // Desde Configuración → Mesón: a partir de cuántos días se insiste.
            'diasParaInsistir' => Ajustes::numero('meson.dias_fiado_viejo'),
            // Lo quitado, los cobros deshechos y las cuentas pasadas a un
            // socio. Solo para quien puede quitar y deshacer cualquier cobro:
            // es el control de lo que hace el mesón.
            'registro' => $puedeAdministrar ? $this->registro() : null,
        ]);
    }

    /** Anota una cosa más en la cuenta de alguien. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
            // Solo para quien no es socio: el del socio sale de su ficha.
            'celular' => 'nullable|string|max:20',
            'concepto' => 'required|string|max:120',
            'monto' => 'required|integer|min:1|max:9999999',
            'pasar_tope' => 'nullable|boolean',
        ], [
            'concepto.required' => 'Apunta qué se llevó.',
            'monto.required' => 'Apunta cuánto es.',
            'monto.min' => 'El monto tiene que ser mayor que cero.',
        ]);

        // Uno de los dos, o no se sabe de quién es la cuenta.
        if (empty($datos['id_cliente']) && trim((string) ($datos['nombre'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'nombre' => 'Di de quién es: elige al socio o escribe un nombre.',
            ]);
        }

        $idCliente = $datos['id_cliente'] ?? null;
        $nombre = $idCliente ? null : trim($datos['nombre']);

        /*
         * EL TOPE, si se puso uno en Configuración → Mesón. No se prohíbe: se
         * avisa con la cifra y hay que confirmarlo. A veces fiarle más a
         * alguien conocido está bien; lo que no puede pasar es que nadie se
         * entere de que ya debe $40.000.
         */
        $tope = Ajustes::numero('meson.tope_fiado');

        if ($tope > 0 && empty($datos['pasar_tope'])) {
            $debe = (int) Fiado::debiendo()->deLaCuenta($idCliente, $nombre)->sum('monto');

            if ($debe + (int) $datos['monto'] > $tope) {
                throw ValidationException::withMessages([
                    'tope' => sprintf(
                        'Con esto debería $%s, más que el tope de $%s. Si igual corresponde, confírmalo.',
                        number_format($debe + (int) $datos['monto'], 0, ',', '.'),
                        number_format($tope, 0, ',', '.')
                    ),
                ]);
            }
        }

        $celular = $idCliente ? null : (preg_replace('/[^0-9+]/', '', (string) ($datos['celular'] ?? '')) ?: null);

        // EL MISMO FORMULARIO DOS VECES (doble clic, reintento de la red) se
        // apunta una sola. Se reserva aquí, ya validado: un error de dato no
        // deja el turno pillado.
        if (! $this->reservarTokenDelFormulario($request, 'fiado_anotar')) {
            return back();
        }

        Fiado::create([
            'id_cliente' => $idCliente,
            // El nombre a mano solo se guarda cuando NO hay socio: con socio, el
            // nombre sale de su ficha y una copia aquí se quedaría vieja el día
            // que se corrija.
            'nombre' => $nombre,
            'celular' => $celular,
            'concepto' => trim($datos['concepto']),
            'monto' => $datos['monto'],
            'id_usuario' => $request->user()->id,
        ]);

        // El celular nuevo vale para toda su cuenta: se escribe una vez y ya
        // sirve para recordarle lo que debía de antes.
        if ($celular) {
            Fiado::debiendo()->deLaCuenta(null, $nombre)->whereNull('celular')->update(['celular' => $celular]);
        }

        return back();
    }

    /**
     * Cobra la cuenta de una persona: entera, o lo que trae (un abono).
     *
     * Entera por defecto: quien paga en el mesón suele pagar lo que debe, y
     * marcar línea a línea es la forma de dejarse una sin querer. Pero si trae
     * $5.000 y debe $8.000, se le reciben: se pagan las cosas más viejas
     * primero y, si la plata alcanza para parte de una, esa se parte en lo
     * pagado y lo que queda. Así la caja recibe justo lo que entró y la deuda
     * sigue diciendo qué se llevó.
     */
    public function saldar(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
            // CON QUÉ SE PAGÓ. Sin esto la caja sabía cuánto entró del mesón
            // pero no cuánto había en efectivo en el cajón.
            'id_metodo_pago' => ['required', Rule::exists('metodos_pago', 'id')->where('activo', true)->whereNull('deleted_at')],
            // Las líneas que se vieron al pulsar «Pagó». Se salda eso y nada
            // más: lo que se cobra es el total que tenía delante quien cobró.
            'lineas' => 'nullable|array|max:500',
            'lineas.*' => 'uuid',
            // Lo que trae, si no es todo.
            'monto' => 'nullable|integer|min:1|max:99999999',
            // Lo que debía según la pantalla al pulsar «Pagó». Con un abono es
            // lo que separa un segundo cobro legítimo de un doble envío.
            'debe_visto' => 'nullable|integer|min:0|max:999999999',
        ], [
            'id_metodo_pago.required' => 'Elige con qué pagó.',
            'id_metodo_pago.exists' => 'Ese medio de pago no está disponible.',
            'monto.min' => 'El monto tiene que ser mayor que cero.',
        ]);

        /*
         * DE QUIÉN SON LAS LÍNEAS, CON EL MISMO CRITERIO DE LA PANTALLA: se
         * filtra como agrupa claveDeCuenta() y, si la pantalla manda las líneas
         * que mostraba, se cobran esas. Lo apuntado entre abrir la pantalla y
         * pulsar no se da por pagado sin que se haya cobrado.
         */
        $pendientes = Fiado::debiendo()
            ->deLaCuenta($datos['id_cliente'] ?? null, $datos['nombre'] ?? null)
            ->when(! empty($datos['lineas']), fn ($q) => $q->whereIn('uuid', $datos['lineas']))
            ->get();

        if ($pendientes->isEmpty()) {
            return back()->with('error', 'Esa cuenta ya estaba saldada.');
        }

        // UNA SOLA MARCA DE TIEMPO PARA TODO EL COBRO: es lo que agrupa las
        // líneas de un mismo gesto, y por ahí reabrir() lo deshace entero.
        $momento = now();
        $usuario = $request->user()->id;
        $medio = (int) $datos['id_metodo_pago'];

        /*
         * LAS LÍNEAS SE TRABAN Y SE VUELVEN A MIRAR: dos «Pagó» a la vez —un
         * doble clic, la ficha y esta pantalla abiertas— cobraban dos veces
         * las mismas líneas. Ahora el segundo espera y solo toca lo que siga
         * debiéndose, o nada.
         */
        $resultado = DB::transaction(function () use ($pendientes, $momento, $usuario, $medio, $datos) {
            $siguenDebiendo = Fiado::whereKey($pendientes->modelKeys())
                ->where('pagado', false)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $total = (int) $siguenDebiendo->sum('monto');
            $trae = isset($datos['monto']) ? (int) $datos['monto'] : $total;

            /*
             * UN ABONO REPETIDO SE COBRABA DOS VECES: el doble clic, o la ficha
             * y la libreta abiertas a la vez. Las líneas siguen debiéndose
             * después del primero (con menos), así que el segundo pasaba. Se
             * compara con lo que debía cuando se pulsó: si ya no es eso, alguien
             * cobró o apuntó entretanto y hay que mirarlo antes de cobrar.
             */
            if (isset($datos['monto'], $datos['debe_visto']) && (int) $datos['debe_visto'] !== $total) {
                throw ValidationException::withMessages([
                    'monto' => 'La cuenta cambió mientras cobrabas: revisa y vuelve a intentar.',
                ]);
            }

            if ($trae > $total) {
                throw ValidationException::withMessages([
                    'monto' => 'Debe $' . number_format($total, 0, ',', '.') . ': no se le puede cobrar más que eso.',
                ]);
            }

            $cobrado = 0;
            $resto = $trae;
            $pagar = fn (Fiado $f) => $f->update([
                'pagado' => true,
                'pagado_en' => $momento,
                'id_usuario_cobro' => $usuario,
                'id_metodo_pago' => $medio,
            ]);

            foreach ($siguenDebiendo as $fiado) {
                if ($resto <= 0) {
                    break;
                }

                if ($fiado->monto <= $resto) {
                    $pagar($fiado);
                    $resto -= $fiado->monto;
                    $cobrado += $fiado->monto;

                    continue;
                }

                // Alcanza para parte de esta: lo pagado sale como su propia
                // línea y la original se queda con lo que falta.
                $parte = $fiado->replicate(['uuid', 'pagado', 'pagado_en', 'id_usuario_cobro', 'id_metodo_pago']);
                $parte->monto = $resto;
                $parte->concepto = mb_substr($fiado->concepto . ' (abono)', 0, 120);
                $parte->created_at = $fiado->created_at;
                $parte->save();
                $pagar($parte);

                $fiado->update(['monto' => $fiado->monto - $resto]);
                $cobrado += $resto;
                $resto = 0;
            }

            return ['cobrado' => $cobrado, 'queda' => $total - $cobrado, 'quien' => $siguenDebiendo->first()?->aNombreDe()];
        });

        if ($resultado['cobrado'] === 0) {
            return back()->with('error', 'Esa cuenta ya estaba saldada.');
        }

        $plata = fn (int $monto) => '$' . number_format($monto, 0, ',', '.');

        return back()->with('success', $resultado['queda'] > 0
            ? sprintf('%s abonó %s. Le quedan %s.', $resultado['quien'], $plata($resultado['cobrado']), $plata($resultado['queda']))
            : sprintf('%s pagó %s. Cuenta saldada.', $resultado['quien'], $plata($resultado['cobrado'])));
    }

    /**
     * Vuelve a abrir una cuenta que se dio por pagada sin serlo.
     *
     * Hace falta: «Pagó» es un botón y equivocarse de fila es un clic. Se
     * reabre lo que se saldó EN ESE MISMO COBRO —no todo su histórico—.
     *
     * Pero un cobro de OTRO DÍA ya está en la caja de ese día y en el informe
     * del mes: deshacerlo los cambia hacia atrás. Eso solo lo hace quien
     * administra, y queda anotado. El mesón deshace lo de hoy, que es donde
     * de verdad ocurre el error de fila.
     */
    public function reabrir(Request $request, Fiado $fiado)
    {
        if (! $fiado->pagado) {
            return back()->with('error', 'Esa cuenta ya estaba abierta.');
        }

        if (! $fiado->pagado_en?->isToday() && ! $request->user()?->puede('clientes.eliminar')) {
            return back()->with('error', 'Ese cobro es de otro día y ya está en la caja de ese día. Solo un administrador puede deshacerlo.');
        }

        /*
         * SE TRABA Y SE VUELVE A MIRAR DENTRO: dos «Deshacer» a la vez leían
         * los dos «está pagada» y dejaban dos anotaciones de «reabierto» para
         * un solo cobro. El segundo espera, la encuentra abierta y no hace nada.
         */
        $total = DB::transaction(function () use ($fiado, $request) {
            $actual = Fiado::whereKey($fiado->getKey())->lockForUpdate()->first();

            if (! $actual?->pagado) {
                return null;
            }

            // Las líneas que se cobraron a la vez que esta: un cobro es un
            // gesto, y deshacerlo tiene que deshacer el gesto entero.
            $delMismoCobro = Fiado::where('pagado', true)
                ->where('pagado_en', $actual->pagado_en)
                ->deLaCuenta($actual->id_cliente, $actual->nombre)
                ->lockForUpdate()
                ->get();

            $total = (int) $delMismoCobro->sum('monto');

            foreach ($delMismoCobro as $linea) {
                $linea->update([
                    'pagado' => false,
                    'pagado_en' => null,
                    'id_usuario_cobro' => null,
                    'id_metodo_pago' => null,
                ]);
            }

            FiadoRegistro::anotar(
                'reabierto',
                $actual,
                'Cobro del ' . $actual->pagado_en->format('d/m/Y H:i') . ': ' . $delMismoCobro->pluck('concepto')->implode(', '),
                $total,
                $request->user()?->id,
            );

            return $total;
        });

        if ($total === null) {
            return back()->with('error', 'Esa cuenta ya estaba abierta.');
        }

        return back()->with(
            'success',
            sprintf('%s vuelve a deber $%s.', $fiado->aNombreDe(), number_format($total, 0, ',', '.'))
        );
    }

    /**
     * Quita una línea apuntada por error.
     *
     * No se borra del todo: se esconde y queda anotado quién la quitó, qué
     * era y cuánto. Un error de tecleo se quita igual que antes, pero una
     * deuda real no puede desaparecer sin que se sepa.
     */
    public function destroy(Request $request, Fiado $fiado)
    {
        if ($fiado->pagado) {
            return back()->with('error', 'Eso ya se cobró: no se borra.');
        }

        // Trabada y mirada otra vez: dos «Quitar» a la vez dejaban dos
        // anotaciones de «quitado» para una sola línea.
        DB::transaction(function () use ($fiado, $request) {
            $actual = Fiado::whereKey($fiado->getKey())->lockForUpdate()->first();

            if (! $actual || $actual->pagado) {
                return;
            }

            $actual->update(['id_usuario_quito' => $request->user()?->id]);
            $actual->delete();

            FiadoRegistro::anotar(
                'quitado',
                $actual,
                $actual->concepto . ' (anotado el ' . $actual->created_at?->format('d/m/Y') . ')',
                (int) $actual->monto,
                $request->user()?->id,
            );
        });

        return back();
    }

    /**
     * Pasa la cuenta de un nombre suelto a un socio.
     *
     * «Pedro» se anotó como visita y después se inscribió: lo que debía (y lo
     * que ya pagó) pasa a su ficha, donde se ve y se cobra con lo demás.
     */
    public function asignar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'id_cliente' => ['required', Rule::exists('clientes', 'id')->whereNull('deleted_at')->whereNull('datos_borrados_en')],
        ], [
            'id_cliente.required' => 'Elige al socio.',
            'id_cliente.exists' => 'Ese socio no está disponible.',
        ]);

        $socio = Cliente::findOrFail($datos['id_cliente']);

        /*
         * Las líneas se buscan YA TRABADAS: dos «Pasar a su ficha» a la vez
         * encontraban las dos la cuenta y dejaban dos anotaciones. El segundo
         * espera y ya no encuentra nada a ese nombre.
         */
        $asignadas = DB::transaction(function () use ($datos, $socio, $request) {
            $lineas = Fiado::deLaCuenta(null, $datos['nombre'])->lockForUpdate()->get();

            if ($lineas->isEmpty()) {
                return false;
            }

            /*
             * A NOMBRE DEL SOCIO, no del nombre suelto: anotado con la línea de
             * antes quedaba sin id_cliente y con su nombre en el detalle, y al
             * borrar sus datos (que limpia por id_cliente) el nombre sobrevivía.
             * El detalle no repite el nombre: la anotación ya es de su ficha.
             */
            FiadoRegistro::anotar(
                'asignado',
                (clone $lineas->first())->forceFill(['id_cliente' => $socio->id]),
                "Pasó a una ficha de socio ({$lineas->count()} cosas)",
                (int) $lineas->where('pagado', false)->sum('monto'),
                $request->user()?->id,
            );

            Fiado::whereKey($lineas->modelKeys())->update([
                'id_cliente' => $socio->id,
                'nombre' => null,
                'celular' => null,
            ]);

            return true;
        });

        if (! $asignadas) {
            return back()->with('error', 'No hay nada anotado a ese nombre.');
        }

        return back()->with('success', 'La cuenta de «' . trim($datos['nombre']) . '» ahora está en la ficha de ' . trim("{$socio->nombres} {$socio->apellido_paterno}") . '.');
    }

    /**
     * Las cuentas que siguen abiertas, agrupadas por persona.
     *
     * La misma forma que usa el resumen, para que las dos pantallas cuenten lo
     * mismo.
     *
     * @return list<array<string,mixed>>
     */
    private function cuentasPendientes(): array
    {
        return Fiado::debiendo()
            // El autor va cargado: cada linea dice quien la apunto, y pedirlo
            // por fila seria una consulta por cada cosa que alguien se llevo.
            ->with(['cliente:id,uuid,nombres,apellido_paterno,celular,foto_perfil,deleted_at', 'autor:id,name'])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->claveDeCuenta())
            ->map(function ($lineas) {
                $primera = $lineas->first();
                $desde = $lineas->min('created_at');

                return [
                    'clave' => $primera->claveDeCuenta(),
                    'quien' => $primera->aNombreDe(),
                    // Sin enlace si el socio está en la papelera: su ficha no abre.
                    'socio_uuid' => $primera->cliente?->trashed() ? null : $primera->cliente?->uuid,
                    'foto' => $primera->cliente?->urlDeFoto(),
                    // El del socio, o el que se anotó para quien no lo es (el
                    // más reciente): sin celular no hay cómo recordárselo.
                    'celular' => $primera->cliente?->celular
                        ?? $lineas->sortByDesc('id')->first(fn (Fiado $f) => $f->celular)?->celular,
                    'id_cliente' => $primera->id_cliente,
                    'nombre' => $primera->nombre,
                    'total' => (int) $lineas->sum('monto'),
                    'desde' => $desde?->format('d/m/Y'),
                    'dias' => (int) $desde?->startOfDay()->diffInDays(today()),
                    'lineas' => $lineas->map(fn (Fiado $f) => [
                        'uuid' => $f->uuid,
                        'concepto' => $f->concepto,
                        'monto' => $f->monto,
                        'cuando' => $f->created_at?->format('d/m/Y H:i'),
                        'apunto' => $f->autor?->name,
                    ])->values()->all(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Lo cobrado en un mes, UN RENGLÓN POR COBRO.
     *
     * Antes era una fila por cosa y solo las cien últimas: un cobro de cinco
     * cosas ocupaba cinco filas y lo de hace dos meses no se encontraba. Ahora
     * se elige el mes y cada cobro dice todo lo que pagó, con cuánto y cómo.
     *
     * @return list<array<string,mixed>>
     */
    private function loYaCobrado(Carbon $mes): array
    {
        $hoy = today();

        return Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->with(['cliente:id,uuid,nombres,apellido_paterno,deleted_at', 'metodoPago:id,nombre', 'cobrador:id,name'])
            ->orderByDesc('pagado_en')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->pagado_en?->toDateTimeString() . '|' . $f->claveDeCuenta())
            ->map(function (Collection $lineas) use ($hoy) {
                $primera = $lineas->first();

                return [
                    // La de cualquier línea sirve para deshacer: reabrir() toma
                    // todo el cobro.
                    'uuid' => $primera->uuid,
                    'quien' => $primera->aNombreDe(),
                    'socio_uuid' => $primera->cliente?->trashed() ? null : $primera->cliente?->uuid,
                    'cuando' => $primera->pagado_en?->format('d/m/Y H:i'),
                    'de_hoy' => (bool) $primera->pagado_en?->isSameDay($hoy),
                    'medio' => $primera->metodoPago?->nombre,
                    'cobro' => $primera->cobrador?->name,
                    'total' => (int) $lineas->sum('monto'),
                    'lineas' => $lineas->map(fn (Fiado $f) => ['concepto' => $f->concepto, 'monto' => (int) $f->monto])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /** @return list<array<string,mixed>> */
    private function registro(): array
    {
        return FiadoRegistro::with(['cliente:id,nombres,apellido_paterno,deleted_at', 'usuario:id,name'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (FiadoRegistro $r) => [
                'id' => $r->id,
                'cuando' => $r->created_at?->format('d/m/Y H:i'),
                'que' => FiadoRegistro::ACCIONES[$r->accion] ?? $r->accion,
                'accion' => $r->accion,
                'quien' => $r->aNombreDe(),
                'detalle' => $r->detalle,
                'monto' => $r->monto,
                'usuario' => $r->usuario?->name,
            ])
            ->all();
    }

    /** El mes pedido (AAAA-MM), o el actual si no viene o no tiene forma. */
    private function mes(string $texto): Carbon
    {
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $texto, $m)) {
            $mes = Carbon::create((int) $m[1], (int) $m[2], 1)->startOfMonth();

            // Ni futuro ni antes de que existiera la libreta.
            if ($mes->lessThanOrEqualTo(today()) && $mes->year >= 2020) {
                return $mes;
            }
        }

        return today()->startOfMonth();
    }

    /**
     * Lo que se pone de un toque al anotar: primero la lista de precios de
     * Configuración → Mesón, después lo que más se fía con el precio de la
     * última vez.
     *
     * TECLEAR «BARRA DE PROTEÍNA» Y «2500» VEINTE VECES AL MES es la razón por
     * la que las cosas acaban sin apuntarse. Con la lista, además, la misma
     * cosa cuesta lo mismo la apunte quien la apunte.
     */
    public function frecuentes()
    {
        $lista = self::listaDePrecios();
        $enLista = array_map(fn (array $p) => mb_strtolower($p['concepto']), $lista);

        // Por `id` y no por fecha: dos cosas apuntadas en el mismo segundo
        // empatan en `created_at` y «la última vez» dependía del orden.
        $frecuentes = Fiado::query()
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'concepto', 'monto'])
            // Los abonos son un trozo de otra cosa, no algo que se venda.
            ->reject(fn (Fiado $f) => str_ends_with($f->concepto, ' (abono)'))
            ->groupBy(fn (Fiado $f) => mb_strtolower(trim($f->concepto)))
            ->reject(fn ($lineas, $clave) => in_array($clave, $enLista, true))
            ->map(fn ($lineas) => [
                'concepto' => $lineas->first()->concepto,
                'monto' => (int) $lineas->first()->monto,
                'veces' => $lineas->count(),
            ])
            ->filter(fn (array $f) => $f['veces'] >= 2)
            ->sortByDesc('veces')
            ->values()
            ->all();

        return response()->json([
            'frecuentes' => array_slice([...$lista, ...$frecuentes], 0, 8),
        ]);
    }

    /**
     * La lista de Configuración → Mesón: «Barra de proteína = 2500», una por
     * línea. También vale con dos puntos o solo un espacio antes del precio,
     * y con el precio escrito con puntos o con $.
     *
     * @return list<array{concepto:string,monto:int,veces:null}>
     */
    public static function listaDePrecios(): array
    {
        $lista = [];

        foreach (preg_split('/\R/', (string) Ajustes::obtener('meson.precios')) as $linea) {
            if (preg_match('/^\s*(.+?)\s*(?:[=:]\s*|\s)\$?\s*([\d.]+)\s*$/u', $linea, $m)) {
                $monto = (int) str_replace('.', '', $m[2]);

                if ($monto > 0 && $monto <= 9999999) {
                    $lista[] = ['concepto' => mb_substr(trim($m[1]), 0, 120), 'monto' => $monto, 'veces' => null];
                }
            }
        }

        return $lista;
    }

    /**
     * Busca a quién apuntarle.
     *
     * Salen TODOS los socios, incluidos los dados de baja: quien se llevó una
     * bebida ayer y hoy ya no está de alta sigue debiendo esa bebida.
     */
    public function buscar(Request $request)
    {
        $texto = trim($request->texto('q', ''));

        if (mb_strlen($texto) < 2) {
            return response()->json(['clientes' => []]);
        }

        $clientes = BusquedaDeSocio::aplicar(Cliente::query(), $texto)
            ->whereNull('datos_borrados_en')
            ->orderBy('apellido_paterno')
            ->limit(10)
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno', 'run_pasaporte', 'activo']);

        return response()->json([
            'clientes' => $clientes->map(fn (Cliente $c) => [
                'id' => $c->id,
                'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                'rut' => $c->run_pasaporte,
                'activo' => (bool) $c->activo,
            ]),
        ]);
    }
}
