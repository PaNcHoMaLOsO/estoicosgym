<?php

namespace App\Http\Controllers\Panel;

use App\Support\Ajustes;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fiado;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * La libreta de lo fiado en el mesón.
 *
 * Se apunta lo que alguien se lleva, se le va sumando cada vez, y cuando paga
 * se salda su cuenta entera de un golpe: nadie paga una bebida de la semana
 * pasada y deja a deber la de ayer.
 */
class FiadoController extends Controller
{
    /**
     * La pantalla propia de lo fiado.
     *
     * En el resumen hay un resumen: quién debe y cuánto, para el vistazo del
     * mesón. Aquí está lo demás —lo ya cobrado, con fecha y con quién lo cobró—
     * porque eso no se mira todos los días pero hace falta cuando alguien
     * discute una cifra o hay que cuadrar el mes.
     *
     * ESTO NO ES CAJA DEL GIMNASIO. Lo cobrado aquí no aparece en los ingresos
     * ni en ningún informe de membresías: es la libreta del mesón, y su cuenta
     * se lleva aparte a propósito.
     */
    public function index(Request $request)
    {
        $hoy = Carbon::today();

        $cobradoEsteMes = (int) Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()])
            ->sum('monto');

        return Inertia::render('Fiados', [
            'cuentas' => $this->cuentasPendientes(),
            'cobrado' => $this->loYaCobrado(),
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
            // Estaba escrito en la pantalla, y el ajuste no lo leía nadie.
            'diasParaInsistir' => Ajustes::numero('meson.dias_fiado_viejo'),
        ]);
    }

    /** Anota una cosa más en la cuenta de alguien. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
            'concepto' => 'required|string|max:120',
            'monto' => 'required|integer|min:1|max:9999999',
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

        Fiado::create([
            'id_cliente' => $datos['id_cliente'] ?? null,
            // El nombre a mano solo se guarda cuando NO hay socio: con socio, el
            // nombre sale de su ficha y una copia aquí se quedaría vieja el día
            // que se corrija.
            'nombre' => empty($datos['id_cliente']) ? trim($datos['nombre']) : null,
            'concepto' => trim($datos['concepto']),
            'monto' => $datos['monto'],
            'id_usuario' => $request->user()->id,
        ]);

        return back();
    }

    /**
     * Salda la cuenta ENTERA de una persona.
     *
     * De golpe y no línea a línea: quien paga en el mesón paga lo que debe, no
     * la bebida del martes. Marcarlas de una en una es la forma de dejarse una
     * sin querer y que esa persona arrastre $1.500 para siempre.
     */
    public function saldar(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'nombre' => 'nullable|string|max:100',
        ]);

        $pendientes = Fiado::debiendo()
            ->when(
                ! empty($datos['id_cliente']),
                fn ($q) => $q->where('id_cliente', $datos['id_cliente']),
                fn ($q) => $q->whereNull('id_cliente')->where('nombre', $datos['nombre'] ?? '')
            )
            ->get();

        if ($pendientes->isEmpty()) {
            return back()->with('error', 'Esa cuenta ya estaba saldada.');
        }

        $total = $pendientes->sum('monto');

        // UNA SOLA MARCA DE TIEMPO PARA TODO EL COBRO. `pagado_en` es lo que
        // agrupa las líneas de un mismo gesto, y es por ahí por donde reabrir()
        // lo deshace entero. Pidiendo la hora dentro del bucle, un cobro que
        // cruzara el cambio de segundo —la columna guarda segundos— quedaba
        // partido en dos marcas: deshacerlo reabría solo una parte y el resto de
        // la deuda se quedaba dada por pagada sin que nadie la volviera a ver.
        $momento = now();

        DB::transaction(function () use ($pendientes, $request, $momento) {
            foreach ($pendientes as $fiado) {
                $fiado->update([
                    'pagado' => true,
                    'pagado_en' => $momento,
                    'id_usuario_cobro' => $request->user()->id,
                ]);
            }
        });

        $quien = $pendientes->first()->aNombreDe();

        return back()->with(
            'success',
            sprintf('%s pagó $%s. Cuenta saldada.', $quien, number_format($total, 0, ',', '.'))
        );
    }

    /**
     * Vuelve a abrir una cuenta que se dio por pagada sin serlo.
     *
     * Hace falta. «Pagó» es un botón y equivocarse de fila es un clic: sin esto,
     * la deuda de esa persona desaparece y la única forma de recuperarla es
     * apuntársela otra vez a mano, inventando conceptos y montos que ya nadie
     * recuerda. Se reabre lo que se saldó EN ESE MISMO COBRO —no todo su
     * histórico— mirando la marca de tiempo del pago.
     */
    public function reabrir(Request $request, Fiado $fiado)
    {
        if (! $fiado->pagado) {
            return back()->with('error', 'Esa cuenta ya estaba abierta.');
        }

        // Las líneas que se cobraron a la vez que esta: un cobro es un gesto,
        // y deshacerlo tiene que deshacer el gesto entero.
        $delMismoCobro = Fiado::where('pagado', true)
            ->where('pagado_en', $fiado->pagado_en)
            ->when(
                $fiado->id_cliente,
                fn ($q) => $q->where('id_cliente', $fiado->id_cliente),
                fn ($q) => $q->whereNull('id_cliente')->where('nombre', $fiado->nombre)
            )
            ->get();

        DB::transaction(function () use ($delMismoCobro) {
            foreach ($delMismoCobro as $linea) {
                $linea->update([
                    'pagado' => false,
                    'pagado_en' => null,
                    'id_usuario_cobro' => null,
                ]);
            }
        });

        $total = $delMismoCobro->sum('monto');

        return back()->with(
            'success',
            sprintf(
                '%s vuelve a deber $%s.',
                $fiado->aNombreDe(),
                number_format($total, 0, ',', '.')
            )
        );
    }

    /**
     * Quita una línea apuntada por error.
     *
     * Se borra de verdad: un «bebida $1.500» que nunca ocurrió no es un dato
     * que recuperar, es un error de tecleo. Lo que se cobró de verdad se queda
     * marcado como pagado y ahí sigue.
     */
    public function destroy(Fiado $fiado)
    {
        if ($fiado->pagado) {
            return back()->with('error', 'Eso ya se cobró: no se borra.');
        }

        $fiado->delete();

        return back();
    }

    /**
     * Las cuentas que siguen abiertas, agrupadas por persona.
     *
     * La misma forma que usa el resumen, para que las dos pantallas cuenten lo
     * mismo: si cada una agrupara a su manera, la portada podría decir «Juan
     * debe $4.000» y esta otra repartirlo en dos deudores.
     *
     * @return list<array<string,mixed>>
     */
    private function cuentasPendientes(): array
    {
        return Fiado::debiendo()
            // El autor va cargado: cada linea dice quien la apunto, y pedirlo
            // por fila seria una consulta por cada cosa que alguien se llevo.
            ->with(['cliente:id,uuid,nombres,apellido_paterno,celular,foto_perfil', 'autor:id,name'])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->claveDeCuenta())
            ->map(function ($lineas) {
                $primera = $lineas->first();
                $desde = $lineas->min('created_at');

                return [
                    'clave' => $primera->claveDeCuenta(),
                    'quien' => $primera->aNombreDe(),
                    'socio_uuid' => $primera->cliente?->uuid,
                    // La cara, para reconocer a quién hay que cobrarle, y el
                    // celular, para poder recordárselo sin buscar su ficha.
                    'foto' => $primera->cliente?->urlDeFoto(),
                    'celular' => $primera->cliente?->celular,
                    'id_cliente' => $primera->id_cliente,
                    'nombre' => $primera->nombre,
                    'total' => (int) $lineas->sum('monto'),
                    'desde' => $desde?->format('d/m/Y'),
                    // Cuantos dias lleva debiendo: una cuenta de hace tres
                    // semanas no se cobra sola, y conviene que se note.
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
     * Lo ya cobrado, lo más reciente primero.
     *
     * Se guarda y se enseña porque es lo que responde «¿pero yo no pagué eso?»
     * tres semanas después. Se corta en 100: más allá, quien lo busque va a
     * mirar la fecha concreta, no a bajar la lista.
     *
     * @return list<array<string,mixed>>
     */
    private function loYaCobrado(): array
    {
        return Fiado::where('pagado', true)
            ->with(['cliente:id,uuid,nombres,apellido_paterno', 'autor:id,name'])
            ->orderByDesc('pagado_en')
            ->limit(100)
            ->get()
            ->map(fn (Fiado $f) => [
                'uuid' => $f->uuid,
                'quien' => $f->aNombreDe(),
                'socio_uuid' => $f->cliente?->uuid,
                'concepto' => $f->concepto,
                'monto' => $f->monto,
                'cuando' => $f->pagado_en?->format('d/m/Y H:i'),
                'apunto' => $f->autor?->name,
            ])
            ->all();
    }

    /**
     * Lo que más se fía, con lo que costó la última vez.
     *
     * TECLEAR «BARRA DE PROTEÍNA» Y «2500» VEINTE VECES AL MES es la razón por
     * la que las cosas acaban sin apuntarse. En el mesón se venden siempre las
     * mismas cinco cosas: aquí salen para dejarlas puestas de un toque.
     *
     * El precio es el de la última vez que se apuntó esa misma cosa, no un
     * catálogo: no hay lista de precios en el sistema, y el último es el que
     * más se parece al de hoy. Se puede corregir antes de guardar.
     */
    public function frecuentes()
    {
        // Se mira lo último y no todo el histórico: lo que se vendía hace un
        // año no es lo que hay hoy en el mostrador.
        // Por `id` y no por fecha: dos cosas apuntadas en el mismo segundo
        // empatan en `created_at` —la columna no guarda milésimas— y entonces
        // «la última vez» dependía del orden en que la base las devolviera.
        $ultimos = Fiado::query()
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'concepto', 'monto']);

        $frecuentes = $ultimos
            ->groupBy(fn (Fiado $f) => mb_strtolower(trim($f->concepto)))
            ->map(fn ($lineas) => [
                // El nombre tal como se escribió la última vez: así se respeta
                // la mayúscula y la tilde de quien lo apuntó bien.
                'concepto' => $lineas->first()->concepto,
                'monto' => (int) $lineas->first()->monto,
                'veces' => $lineas->count(),
            ])
            ->filter(fn (array $f) => $f['veces'] >= 2)
            ->sortByDesc('veces')
            ->take(6)
            ->values()
            ->all();

        return response()->json(['frecuentes' => $frecuentes]);
    }

    /**
     * Busca a quién apuntarle.
     *
     * Salen TODOS los socios, incluidos los dados de baja: quien se llevó una
     * bebida ayer y hoy ya no está de alta sigue debiendo esa bebida.
     */
    public function buscar(Request $request)
    {
        $texto = trim((string) $request->query('q', ''));

        if (mb_strlen($texto) < 2) {
            return response()->json(['clientes' => []]);
        }

        $clientes = Cliente::query()
            ->where(function ($q) use ($texto) {
                $q->where('nombres', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('run_pasaporte', 'like', "%{$texto}%");
            })
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
