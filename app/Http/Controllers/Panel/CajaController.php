<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Support\IngresosDelNegocio;
use App\Support\IngresosPorMetodo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * La caja: lo que entró, lo que se debe y cómo va el gimnasio.
 *
 * SALIÓ DEL RESUMEN A PROPÓSITO. El resumen es lo primero que abre quien
 * atiende el mesón, y ahí tiene que estar lo que se HACE: a quién llamar, las
 * notas del día, los atajos. La plata es para quien lleva el negocio, y aquí la
 * ve solo quien tiene `reportes.ver`; recepción no entra. Lo decide la ruta, no
 * la pantalla: a recepción no se le tapa la cifra, no le llega.
 *
 * Aun así las cifras salen TAPADAS y se destapan con el ojito de la barra: en
 * el mesón se sienta gente detrás de quien mira la pantalla.
 *
 * TODA CIFRA VA CON CON QUÉ COMPARARLA. «Entró $2.070.034 este mes» no dice si
 * el mes va bien o mal; «un 12% más que a esta altura del mes pasado» sí. Un
 * número solo obliga a acordarse del anterior, y nadie se acuerda.
 */
class CajaController extends Controller
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    /** Cuántos meses de historia se pintan en las tendencias. */
    private const MESES = 6;

    public function __invoke()
    {
        $hoy = Carbon::today();
        $mes = [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()];

        return Inertia::render('Caja', [
            /*
             * CADA CIFRA VIENE PARTIDA en membresías, talleres y mesón, y con
             * su total. Antes solo contaba las membresías: lo del colegio y lo
             * cobrado del fiado no salía en ninguna parte, y «entró este mes»
             * decía menos de lo que entró.
             */
            'caja' => [
                'hoy' => $this->entre($hoy, $hoy),
                'ayer' => $this->entre($hoy->copy()->subDay(), $hoy->copy()->subDay()),

                'mes' => $this->entre(...$mes),
                // El mes pasado HASTA EL MISMO DÍA, no entero: comparar cinco
                // días contra treinta diría siempre que el mes va peor.
                'mes_pasado' => $this->mesPasadoALaFecha($hoy),
                'dia_del_mes' => $hoy->day,

                // Lo que los socios deben de sus membresías, contado membresía
                // por membresía. No incluye lo fiado del mesón, que es otra
                // libreta y va aparte, abajo.
                'por_cobrar' => Inscripcion::porCobrar(),
            ],

            // Lo que se debe, partido en dos: lo de quien sigue viniendo se
            // cobra en el mostrador cualquier día de estos; lo de quien ya se
            // fue hay que salir a buscarlo, y son gestiones distintas.
            'deuda' => $this->deudaPorEstado(),

            // Lo fiado del mesón, en total. El detalle por persona y el botón
            // de cobrar están en la pantalla de Fiado, que es donde se trabaja.
            'fiado' => [
                'total' => (int) Fiado::debiendo()->sum('monto'),
                'personas' => Fiado::debiendo()->get()
                    ->groupBy(fn (Fiado $f) => $f->claveDeCuenta())
                    ->count(),
            ],

            // Lo facturado a colegios y empresas que todavía no pagan. Es
            // plata que ya salió en una factura y que nadie estaba mirando.
            'talleres' => [
                'por_cobrar' => (int) CobroTaller::whereNull('pagado_en')->sum('total'),
                'facturas' => CobroTaller::whereNull('pagado_en')->count(),
            ],

            'fuentes' => IngresosDelNegocio::FUENTES,
            'porDia' => $this->porDiaDelMes($hoy),
            'porMes' => $this->porMes($hoy),
            // Solo las membresías: los talleres se pagan por transferencia a
            // la cuenta del gimnasio y el fiado no anota con qué se cobró.
            'porMetodo' => IngresosPorMetodo::en(fn ($q) => $q->whereBetween('fecha_pago', $mes))->all(),
            'altas' => $this->altasPorMes($hoy),
            'porPlan' => $this->repartoPorPlan(),
        ]);
    }

    /**
     * Lo que entró entre dos días, ambos incluidos: por fuente y en total.
     *
     * @return array{membresias:int, talleres:int, meson:int, total:int}
     */
    private function entre(Carbon $desde, Carbon $hasta): array
    {
        return IngresosDelNegocio::entre($desde, $hasta);
    }

    /**
     * El mes pasado hasta el mismo día del mes.
     *
     * El 31 de marzo no existe en febrero: `subMonthNoOverflow` deja el último
     * día del mes en vez de saltar al 2 de marzo y comparar contra un periodo
     * de treinta y un días.
     */
    private function mesPasadoALaFecha(Carbon $hoy): array
    {
        $mismoDia = $hoy->copy()->subMonthNoOverflow();

        return $this->entre($mismoDia->copy()->startOfMonth(), $mismoDia);
    }

    /**
     * Lo que entró cada día de este mes.
     *
     * Los días que faltan del mes también van, en cero: la forma de la barra
     * dice en qué parte del mes se cobra —los primeros días, cuando pagan las
     * mensualidades— y con el mes recortado esa forma no se vería.
     */
    private function porDiaDelMes(Carbon $hoy): array
    {
        return collect(IngresosDelNegocio::porDia($hoy))
            ->map(fn (array $partes, string $fecha) => [
                'mes' => (string) Carbon::parse($fecha)->day,
                'total' => $partes['total'],
                'partes' => $partes,
                'futuro' => Carbon::parse($fecha)->gt($hoy),
            ])
            ->values()
            ->all();
    }

    /** Lo que entró en cada uno de los últimos meses. */
    private function porMes(Carbon $hoy): array
    {
        return collect(range(self::MESES - 1, 0))
            ->map(function (int $atras) use ($hoy) {
                $mes = $hoy->copy()->subMonthsNoOverflow($atras);

                $partes = $this->entre($mes->copy()->startOfMonth(), $mes->copy()->endOfMonth());

                return [
                    'mes' => $mes->translatedFormat('M'),
                    'mes_largo' => $mes->translatedFormat('F Y'),
                    'total' => $partes['total'],
                    'partes' => $partes,
                ];
            })
            ->all();
    }

    /**
     * Lo que se debe, según si el socio sigue viniendo o ya se fue.
     *
     * La deuda se cuenta MEMBRESÍA POR MEMBRESÍA, igual que «por cobrar»: la
     * columna `monto_pendiente` de cada pago guarda lo que faltaba después de
     * ese pago, y sumarla cuenta dos veces a quien abonó dos veces.
     */
    private function deudaPorEstado(): array
    {
        $porEstado = Inscripcion::conDeuda()->groupBy(fn (Inscripcion $i) => (int) $i->id_estado);
        $sumar = fn (array $estados) => (int) collect($estados)
            ->sum(fn (int $estado) => ($porEstado[$estado] ?? collect())->sum(fn (Inscripcion $i) => $i->deuda));
        $contar = fn (array $estados) => (int) collect($estados)
            ->sum(fn (int $estado) => ($porEstado[$estado] ?? collect())->count());

        $alDia = [self::ACTIVA, self::PAUSADA];

        return [
            'vigente' => ['total' => $sumar($alDia), 'cuantas' => $contar($alDia)],
            'vencida' => ['total' => $sumar([self::VENCIDA]), 'cuantas' => $contar([self::VENCIDA])],
        ];
    }

    /**
     * Socios nuevos en cada uno de los últimos meses.
     *
     * Se pintan TODOS los meses aunque no haya altas: un hueco en la serie se
     * lee como «no se midió», y un mes que falta parece un error.
     *
     * Se cuenta mes a mes con un rango de fechas y NO agrupando por
     * YEAR()/MONTH(): esas dos funciones son de MySQL y SQLite no las tiene, así
     * que la consulta reventaba en cuanto corría fuera de producción.
     */
    private function altasPorMes(Carbon $hoy): array
    {
        return collect(range(self::MESES - 1, 0))
            ->map(function (int $atras) use ($hoy) {
                $mes = $hoy->copy()->subMonthsNoOverflow($atras);

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

    /** Cuántas membresías al día hay de cada plan, y cuánto valen. */
    private function repartoPorPlan(): array
    {
        return Inscripcion::query()
            ->join('membresias', 'inscripciones.id_membresia', '=', 'membresias.id')
            ->where('inscripciones.id_estado', self::ACTIVA)
            ->groupBy('membresias.id', 'membresias.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                DB::raw('membresias.nombre as nombre'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(inscripciones.precio_final) as valor'),
            ])
            ->map(fn ($f) => [
                'nombre' => $f->nombre,
                'total' => (int) $f->total,
                'valor' => (int) $f->valor,
            ])
            ->all();
    }
}
