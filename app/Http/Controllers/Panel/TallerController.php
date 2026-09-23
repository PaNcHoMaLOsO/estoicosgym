<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CobroTaller;
use App\Models\HoraTaller;
use App\Models\Institucion;
use App\Models\Taller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Talleres y arriendos: la otra mitad del negocio.
 *
 * EL GIMNASIO LE ARRIENDA LA SALA A UN COLEGIO y le factura por hora a fin de
 * mes. Eso vivía fuera del sistema: el horario en un Excel, el calendario de
 * Windows abierto al lado para contar qué días tocaba clase, y la cifra escrita
 * a mano en la cotización. Contar a mano una vez al mes es justo donde se
 * pierde una hora, y una hora son treinta mil pesos.
 *
 * Aquí el horario propone las clases del mes, se corrige lo que no hubo
 * —feriados, suspensiones— y al cerrar queda la cuenta hecha: horas, neto, IVA
 * y total, que es lo que hay que escribir en la factura.
 *
 * NO EMITE FACTURAS ELECTRÓNICAS: eso lo timbra el SII a través de un
 * proveedor. Lo que hace es que la cifra no se cuente a mano y que quede
 * anotado qué se cobró, cuándo se emitió y si lo pagaron.
 */
class TallerController extends Controller
{
    public function index(Request $request)
    {
        $mes = $this->mes($request->query('periodo'));

        $talleres = Taller::with('institucion')
            ->withSum(['horas as horas_del_mes' => fn ($q) => $q
                ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])], 'horas')
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Taller $t) => [
                'uuid' => $t->uuid,
                'nombre' => $t->nombre,
                'institucion' => $t->institucion?->nombre,
                'precio_hora' => $t->precio_hora,
                'activo' => $t->activo,
                'horas' => (float) ($t->horas_del_mes ?? 0),
                'total' => (int) round(($t->horas_del_mes ?? 0) * $t->precio_hora),
                // Si el mes ya se cerró, no hay nada que hacer con él.
                'cerrado' => $t->cobros()->where('periodo', $mes->format('Y-m'))->exists(),
            ])
            ->all();

        return Inertia::render('Talleres/Index', [
            'periodo' => $mes->format('Y-m'),
            'mesLegible' => $mes->translatedFormat('F \d\e Y'),
            'talleres' => $talleres,
            'instituciones' => Institucion::orderBy('nombre')->get(['uuid', 'nombre', 'rut'])->all(),
            // Lo facturado y sin pagar: es plata que ya salió en una factura y
            // que nadie está mirando en el panel.
            'porCobrar' => $this->porCobrar(),
        ]);
    }

    /** La ficha del taller: el mes, sus clases y lo que se le cobrará. */
    public function show(Request $request, Taller $taller)
    {
        $mes = $this->mes($request->query('periodo'));
        $periodo = $mes->format('Y-m');

        $horas = $taller->horas()
            ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderBy('fecha')
            ->get();

        $cobro = $taller->cobros()->where('periodo', $periodo)->first();
        $total = (int) round($horas->sum('horas') * $taller->precio_hora);

        return Inertia::render('Talleres/Ficha', [
            'taller' => [
                'uuid' => $taller->uuid,
                'nombre' => $taller->nombre,
                'descripcion_factura' => $taller->descripcion_factura,
                'precio_hora' => $taller->precio_hora,
                'horario' => $taller->horario ?? [],
                'activo' => $taller->activo,
                'institucion' => $taller->institucion?->only([
                    'nombre', 'rut', 'giro', 'direccion', 'comuna',
                    'contacto_nombre', 'contacto_email', 'contacto_telefono',
                ]),
            ],
            'periodo' => $periodo,
            'mesLegible' => $mes->translatedFormat('F \d\e Y'),
            'horas' => $horas->map(fn (HoraTaller $h) => [
                'uuid' => $h->uuid,
                'fecha' => $h->fecha->format('d/m/Y'),
                'dia' => $h->fecha->translatedFormat('D'),
                'horas' => $h->horas,
                'detalle' => $h->detalle,
                'cobrada' => $h->estaCobrada(),
            ])->all(),
            // Las clases del horario que todavía no están anotadas: se ofrecen
            // para no tener que teclear catorce fechas a mano.
            'propuestas' => $this->propuestas($taller, $mes, $horas),
            'cuenta' => [
                'horas' => (float) $horas->sum('horas'),
                'total' => $total,
                ...CobroTaller::desglosar($total),
            ],
            'cobro' => $cobro ? $this->comoSeLee($cobro) : null,
            'historial' => $taller->cobros()
                ->orderByDesc('periodo')
                ->limit(24)
                ->get()
                ->map(fn (CobroTaller $c) => $this->comoSeLee($c))
                ->all(),
        ]);
    }

    // ------------------------------------------------------- el taller

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'institucion_uuid' => 'nullable|string|exists:instituciones,uuid',
            'institucion_nombre' => 'required_without:institucion_uuid|nullable|string|max:160',
            'institucion_rut' => 'nullable|string|max:20',
            'nombre' => 'required|string|max:160',
            'descripcion_factura' => 'nullable|string|max:200',
            'precio_hora' => 'required|integer|min:1|max:9999999',
        ], [
            'institucion_nombre.required_without' => 'Di a quién se le factura.',
            'precio_hora.required' => 'Pon cuánto se cobra la hora.',
        ]);

        $taller = DB::transaction(function () use ($datos) {
            // `?? null`: cuando no se manda, la clave no llega en los datos
            // validados y leerla a secas revienta.
            $institucion = ($datos['institucion_uuid'] ?? null)
                ? Institucion::where('uuid', $datos['institucion_uuid'])->firstOrFail()
                : Institucion::create([
                    'nombre' => trim($datos['institucion_nombre']),
                    'rut' => $datos['institucion_rut'] ?? null,
                ]);

            return Taller::create([
                'id_institucion' => $institucion->id,
                'nombre' => trim($datos['nombre']),
                'descripcion_factura' => $datos['descripcion_factura'] ?? null,
                'precio_hora' => $datos['precio_hora'],
                'activo' => true,
            ]);
        });

        return redirect()
            ->route('panel.talleres.show', $taller->uuid)
            ->with('success', "Taller «{$taller->nombre}» creado. Ponle el horario para que proponga las clases.");
    }

    public function actualizar(Request $request, Taller $taller)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:160',
            'descripcion_factura' => 'nullable|string|max:200',
            'precio_hora' => 'required|integer|min:1|max:9999999',
            'activo' => 'boolean',
            'horario' => 'nullable|array',
            'horario.*' => 'array',
            'horario.*.*' => 'array:0,1',
        ]);

        $taller->update([
            'nombre' => trim($datos['nombre']),
            'descripcion_factura' => $datos['descripcion_factura'] ?? null,
            'precio_hora' => $datos['precio_hora'],
            'activo' => (bool) ($datos['activo'] ?? true),
            'horario' => $this->horarioLimpio($datos['horario'] ?? []),
        ]);

        return back()->with('success', 'Taller actualizado.');
    }

    // -------------------------------------------------------- las horas

    /** Anota una clase suelta. */
    public function anotarHora(Request $request, Taller $taller)
    {
        $datos = $request->validate([
            'fecha' => 'required|date',
            'horas' => 'required|numeric|min:0.25|max:24',
            'detalle' => 'nullable|string|max:160',
        ], [
            'horas.min' => 'Menos de un cuarto de hora no se cobra.',
        ]);

        $this->abortSiEstaCerrado($taller, Carbon::parse($datos['fecha']));

        $taller->horas()->create([
            'fecha' => $datos['fecha'],
            'horas' => $datos['horas'],
            'detalle' => $datos['detalle'] ?? null,
            'id_usuario' => $request->user()->id,
        ]);

        return back()->with('success', 'Clase anotada.');
    }

    /**
     * Anota de una vez las clases del horario que faltan del mes.
     *
     * ES LO QUE AHORRA LA HORA DE TRABAJO: catorce fechas tecleadas a mano, una
     * por una, mirando el calendario. Solo se anotan las que faltan, así que
     * pulsarlo dos veces no duplica nada.
     */
    public function anotarMes(Request $request, Taller $taller)
    {
        $mes = $this->mes($request->input('periodo'));
        $this->abortSiEstaCerrado($taller, $mes);

        $yaEstan = $taller->horas()
            ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->get();

        $nuevas = $this->propuestas($taller, $mes, $yaEstan);

        foreach ($nuevas as $clase) {
            $taller->horas()->create([
                'fecha' => $clase['fecha'],
                'horas' => $clase['horas'],
                'detalle' => $clase['detalle'],
                'id_usuario' => $request->user()->id,
            ]);
        }

        if ($nuevas === []) {
            return back()->with('info', 'Ya estaban anotadas todas las clases del horario.');
        }

        return back()->with('success', count($nuevas).' clases anotadas. Quita las que no se hicieron.');
    }

    /** Quita una clase: la que no se hizo no se cobra. */
    public function borrarHora(HoraTaller $hora)
    {
        if ($hora->estaCobrada()) {
            return back()->with('error', 'Esa clase ya está en un cobro cerrado. Reabre el mes para tocarla.');
        }

        $hora->delete();

        return back()->with('success', 'Clase quitada.');
    }

    // -------------------------------------------------------- el cobro

    /**
     * Cierra el mes: deja la cuenta hecha para escribir la factura.
     *
     * Y ENGANCHA LAS HORAS AL COBRO, que es lo que impide que alguien añada una
     * clase de julio después de haber facturado julio y la factura deje de
     * cuadrar con el sistema.
     */
    public function cerrar(Request $request, Taller $taller)
    {
        $mes = $this->mes($request->input('periodo'));
        $periodo = $mes->format('Y-m');

        if ($taller->cobros()->where('periodo', $periodo)->exists()) {
            return back()->with('error', 'Ese mes ya estaba cerrado.');
        }

        $horas = $taller->horas()
            ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->get();

        if ($horas->isEmpty()) {
            throw ValidationException::withMessages([
                'periodo' => 'No hay ninguna clase anotada en ese mes.',
            ]);
        }

        $total = (int) round($horas->sum('horas') * $taller->precio_hora);
        $desglose = CobroTaller::desglosar($total);

        DB::transaction(function () use ($taller, $periodo, $horas, $total, $desglose, $request) {
            $cobro = $taller->cobros()->create([
                'periodo' => $periodo,
                'horas' => $horas->sum('horas'),
                // El precio que tenía ESE mes: si mañana sube la hora, la
                // factura de julio tiene que seguir diciendo lo que decía.
                'precio_hora' => $taller->precio_hora,
                'total' => $total,
                'neto' => $desglose['neto'],
                'iva' => $desglose['iva'],
                'id_usuario' => $request->user()->id,
            ]);

            HoraTaller::whereIn('id', $horas->pluck('id'))->update(['id_cobro' => $cobro->id]);
        });

        return back()->with('success', 'Mes cerrado. Ya tienes la cuenta para la factura.');
    }

    /** El folio de la factura, cuándo se emitió y cuándo la pagaron. */
    public function actualizarCobro(Request $request, CobroTaller $cobro)
    {
        $datos = $request->validate([
            'folio' => 'nullable|string|max:30',
            'emitido_en' => 'nullable|date',
            'pagado_en' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $cobro->update($datos);

        return back()->with('success', 'Cobro actualizado.');
    }

    /**
     * Reabre un mes cerrado.
     *
     * Hace falta: se cierra julio y aparece una clase que no estaba anotada. Al
     * reabrir, las horas vuelven a quedar sueltas y se puede corregir. El folio
     * se pierde con el cobro a propósito: si ya se emitió la factura, lo que
     * corresponde es una nota de crédito, no cambiar el número por detrás.
     */
    public function reabrir(CobroTaller $cobro)
    {
        DB::transaction(function () use ($cobro) {
            $cobro->horas()->update(['id_cobro' => null]);
            $cobro->delete();
        });

        return back()->with('success', 'Mes reabierto. Corrige las clases y vuelve a cerrarlo.');
    }

    // ------------------------------------------------------------ apoyo

    /** El mes pedido, o el que corre. */
    private function mes(?string $periodo): Carbon
    {
        if ($periodo && preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            return Carbon::createFromFormat('Y-m-d', $periodo.'-01')->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    /**
     * Las clases del horario que todavía no están anotadas ese mes.
     *
     * @param  \Illuminate\Support\Collection<int,HoraTaller>  $yaEstan
     * @return list<array{fecha:string, horas:float, detalle:string}>
     */
    private function propuestas(Taller $taller, Carbon $mes, $yaEstan): array
    {
        $puestas = $yaEstan->map(fn (HoraTaller $h) => $h->fecha->toDateString().'|'.$h->detalle)->all();

        return array_values(array_filter(
            $taller->clasesDe($mes),
            fn (array $clase) => ! in_array($clase['fecha'].'|'.$clase['detalle'], $puestas, true)
        ));
    }

    private function abortSiEstaCerrado(Taller $taller, Carbon $fecha): void
    {
        $cerrado = $taller->cobros()->where('periodo', $fecha->format('Y-m'))->exists();

        if ($cerrado) {
            throw ValidationException::withMessages([
                'fecha' => 'Ese mes ya está cerrado. Reabre el cobro para poder tocarlo.',
            ]);
        }
    }

    /**
     * El horario, sin los días vacíos.
     *
     * Un día con los dos campos en blanco no es un día sin clases: es un día
     * que alguien empezó a escribir y dejó a medias, y guardado tal cual haría
     * que el mes propusiera clases de cero horas.
     *
     * @param  array<string,mixed>  $bruto
     * @return array<string,list<array{0:string,1:string}>>
     */
    private function horarioLimpio(array $bruto): array
    {
        $limpio = [];

        foreach (Taller::DIAS as $dia) {
            foreach ($bruto[$dia] ?? [] as $tramo) {
                $desde = trim((string) ($tramo[0] ?? ''));
                $hasta = trim((string) ($tramo[1] ?? ''));

                if ($desde !== '' && $hasta !== '' && $hasta > $desde) {
                    $limpio[$dia][] = [$desde, $hasta];
                }
            }
        }

        return $limpio;
    }

    /** @return array<string,mixed> */
    private function comoSeLee(CobroTaller $cobro): array
    {
        return [
            'uuid' => $cobro->uuid,
            'periodo' => $cobro->periodo,
            'mes' => Carbon::createFromFormat('Y-m-d', $cobro->periodo.'-01')->translatedFormat('F \d\e Y'),
            'horas' => $cobro->horas,
            'precio_hora' => $cobro->precio_hora,
            'total' => $cobro->total,
            'neto' => $cobro->neto,
            'iva' => $cobro->iva,
            'folio' => $cobro->folio,
            'emitido_en' => $cobro->emitido_en?->format('Y-m-d'),
            'pagado_en' => $cobro->pagado_en?->format('Y-m-d'),
            'observaciones' => $cobro->observaciones,
        ];
    }

    /**
     * Lo facturado y todavía sin pagar.
     *
     * @return array{total:int, cuantos:int}
     */
    private function porCobrar(): array
    {
        $pendientes = CobroTaller::whereNull('pagado_en')->get(['total']);

        return ['total' => (int) $pendientes->sum('total'), 'cuantos' => $pendientes->count()];
    }
}
