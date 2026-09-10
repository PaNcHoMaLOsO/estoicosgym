<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Pantalla de entrada del panel.
 *
 * EL DINERO SOLO VIAJA A QUIEN PUEDE VERLO. Recepcion no recibe las cifras de
 * caja: no es que se le tapen en pantalla, es que no salen del servidor. Eso es
 * el permiso `reportes.ver`, el mismo que le cierra los informes.
 *
 * A quien SI puede verlas, la pantalla se las tapa por defecto y las enseña con
 * el ojito de la barra. Eso es otra cosa distinta: en el meson se sienta gente
 * detras de quien atiende, y una cifra de caja la lee cualquiera que pase por
 * ahi. Tapar NO es un permiso —el dato ya esta en la pagina— sino no dejarlo a
 * la vista.
 *
 * Lo demas es lo que se puede HACER hoy: la libreta del meson —las notas y lo
 * fiado—, a quien hay que llamar, quien se fue sin renovar y como va creciendo
 * el gimnasio.
 */
class ResumenController extends Controller
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    /** Cuántos meses de historia se pintan en la tendencia. */
    private const MESES = 6;

    public function __invoke(Request $request)
    {
        $hoy = Carbon::today();
        $enUnaSemana = $hoy->copy()->addDays(7);

        $porVencer = Inscripcion::query()
            ->with(['cliente:id,uuid,nombres,apellido_paterno,apellido_materno,email,celular', 'membresia:id,nombre'])
            ->where('id_estado', self::ACTIVA)
            ->whereBetween('fecha_vencimiento', [$hoy, $enUnaSemana])
            ->orderBy('fecha_vencimiento')
            ->limit(10)
            ->get();

        return Inertia::render('Resumen', [
            'cifras' => [
                'socios' => Cliente::where('activo', true)->count(),
                'al_dia' => Inscripcion::where('id_estado', self::ACTIVA)->count(),
                'pausadas' => Inscripcion::where('id_estado', self::PAUSADA)->count(),
                // Las dos que piden actuar hoy.
                'vencen_semana' => Inscripcion::where('id_estado', self::ACTIVA)
                    ->whereBetween('fecha_vencimiento', [$hoy, $enUnaSemana])
                    ->count(),
                'vencidas' => Inscripcion::where('id_estado', self::VENCIDA)->count(),
            ],

            /*
             * El dinero SOLO si esta persona puede verlo.
             *
             * No se manda y se tapa: no se manda. Recepción no tiene
             * `reportes.ver` y esta pantalla es lo primero que abre; dejarle la
             * caja del día aquí le enseñaría por la puerta de atrás justo lo que
             * el resto del sistema le cierra. El ojito es para OTRA cosa: que no
             * lo lea quien se sienta detrás de quien sí puede verlo.
             */
            'caja' => $request->user()?->puede('reportes.ver')
                ? $this->caja($hoy)
                : null,

            'notas' => $this->notas(),
            'fiados' => $this->fiados(),

            'altas' => $this->altasPorMes($hoy),
            'porPlan' => $this->repartoPorPlan(),

            'porVencer' => $porVencer->map(function (Inscripcion $i) use ($hoy) {
                $cliente = $i->cliente;

                return [
                    'uuid' => $i->uuid,
                    'socio_uuid' => $cliente?->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'membresia' => $i->membresia?->nombre,
                    'vence' => $i->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => (int) $hoy->diffInDays($i->fecha_vencimiento, false),
                    // Sin correo ni celular no hay a quien avisar: ese socio hay
                    // que buscarlo a mano y conviene que se vea desde aqui.
                    'contacto' => $cliente?->email ?: $cliente?->celular,
                ];
            }),
        ]);
    }

    /**
     * Lo que entró y lo que falta por entrar.
     *
     * Tres cifras y no diez: lo de hoy, lo del mes, y lo que se debe. Es lo que
     * se mira al abrir; el desglose está en Reportes.
     *
     * @return array<string,int>
     */
    private function caja(Carbon $hoy): array
    {
        return [
            'hoy' => (int) Pago::ingresos()
                ->whereDate('fecha_pago', $hoy)
                ->sum('monto_abonado'),

            'mes' => (int) Pago::ingresos()
                ->whereBetween('fecha_pago', [
                    $hoy->copy()->startOfMonth(),
                    $hoy->copy()->endOfMonth(),
                ])
                ->sum('monto_abonado'),

            // Lo que los socios deben de sus membresías. No incluye lo fiado
            // del mesón, que es otra libreta y se cuenta aparte.
            'por_cobrar' => (int) Pago::whereIn('id_estado', [200, 202, 203])
                ->sum('monto_pendiente'),
        ];
    }

    /**
     * El bloc del mesón: lo pendiente, y lo tachado hoy.
     *
     * @return list<array<string,mixed>>
     */
    private function notas(): array
    {
        return Nota::query()
            ->delDia()
            ->with(['autor:id,name', 'quienLaHizo:id,name'])
            // Lo pendiente primero: es lo que hay que hacer. Lo tachado se
            // queda debajo como constancia de que se hizo.
            ->orderBy('hecha')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Nota $n) => [
                'uuid' => $n->uuid,
                'texto' => $n->texto,
                'hecha' => $n->hecha,
                'autor' => $n->autor?->name,
                'cuando' => $n->created_at?->format('H:i'),
                'hecha_por' => $n->quienLaHizo?->name,
            ])
            ->all();
    }

    /**
     * Lo fiado, agrupado POR PERSONA y no línea a línea.
     *
     * Lo que se pregunta en el mesón es «¿cuánto debe Juan?», no «¿qué se llevó
     * el martes?». El detalle va dentro, para cuando alguien discute la cifra.
     *
     * @return list<array<string,mixed>>
     */
    private function fiados(): array
    {
        return Fiado::debiendo()
            ->with('cliente:id,uuid,nombres,apellido_paterno')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->claveDeCuenta())
            ->map(function ($lineas) {
                $primera = $lineas->first();

                return [
                    'clave' => $primera->claveDeCuenta(),
                    'quien' => $primera->aNombreDe(),
                    'socio_uuid' => $primera->cliente?->uuid,
                    'id_cliente' => $primera->id_cliente,
                    'nombre' => $primera->nombre,
                    'total' => (int) $lineas->sum('monto'),
                    'desde' => $lineas->min('created_at')?->format('d/m/Y'),
                    'lineas' => $lineas->map(fn (Fiado $f) => [
                        'uuid' => $f->uuid,
                        'concepto' => $f->concepto,
                        'monto' => $f->monto,
                        'cuando' => $f->created_at?->format('d/m H:i'),
                    ])->values()->all(),
                ];
            })
            // El que más debe arriba: es de quien hay que acordarse.
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Socios nuevos en cada uno de los últimos meses.
     *
     * Se pintan TODOS los meses aunque no haya altas: un hueco en la serie se
     * lee como «no se midió», y un mes que falta parece un error.
     */
    private function altasPorMes(Carbon $hoy): array
    {
        /*
         * Se cuenta mes a mes con un rango de fechas, y NO agrupando por
         * YEAR(created_at) / MONTH(created_at).
         *
         * Esas dos funciones son de MySQL y SQLite no las tiene, asi que la
         * consulta reventaba con un 500 en cuanto se ejecutaba fuera de
         * produccion —lo cazaron las pruebas, que corren sobre SQLite—. Con un
         * whereBetween la consulta vale en los dos motores, y son seis
         * COUNT diminutos: no compensa complicarlo por eso.
         */
        return collect(range(self::MESES - 1, 0))
            ->map(function (int $atras) use ($hoy) {
                $mes = $hoy->copy()->subMonths($atras);

                return [
                    'mes' => $mes->translatedFormat('M'),
                    'total' => Cliente::whereBetween('created_at', [
                        $mes->copy()->startOfMonth(),
                        $mes->copy()->endOfMonth(),
                    ])->count(),
                ];
            })
            ->all();
    }

    /** Cuántas membresías al día hay de cada plan. */
    private function repartoPorPlan(): array
    {
        return Inscripcion::query()
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->where('inscripciones.id_estado', self::ACTIVA)
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([DB::raw('membresias.nombre as nombre'), DB::raw('COUNT(*) as total')])
            ->map(fn ($f) => ['nombre' => $f->nombre, 'total' => (int) $f->total])
            ->all();
    }
}
