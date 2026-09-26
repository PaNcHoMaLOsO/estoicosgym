<?php

namespace App\Support;

use App\Models\Inscripcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Cómo le va al gimnasio a lo largo del tiempo.
 *
 * LOS INFORMES QUE HABÍA SON FOTOS: cuánto entró este mes, cuántas membresías
 * hay vigentes hoy, a quién hay que llamar esta semana. Ninguno contesta las
 * preguntas del negocio, que son de película y no de foto: ¿entra más gente de
 * la que se va? ¿el que se inscribe vuelve el mes siguiente? ¿cuánto dura un
 * socio? Sin eso, un gimnasio que pierde diez socios al mes y gana ocho se ve
 * exactamente igual que uno que crece, porque las dos fotos enseñan gente
 * entrenando.
 *
 * SE CALCULA EN PHP Y DE UNA SOLA CONSULTA. Son cuatro años de membresías
 * —menos de dos mil filas—, y en memoria se puede preguntar cosas que en SQL
 * pedirían una consulta por mes y por definición: «activo al cierre» mira si
 * el periodo cubre ese día, «alta» mira si fue la primera del socio, «se fue»
 * mira si después no compró nada. Además, así funciona igual en MySQL y en
 * SQLite —las pruebas— sin escribir dos veces cada cuenta.
 *
 * SIN PASES: quien compra un pase diario no es un socio que se gana ni que se
 * pierda, y contarlos convertiría la curva en el ruido de la gente de paso.
 */
class EvolucionDelNegocio
{
    /** Cuántos días se esperan antes de dar a alguien por ido. */
    public const DIAS_DE_GRACIA = 45;

    /** @var Collection<int,object> */
    private Collection $membresias;

    /** Las membresías de cada socio, para no recorrerlas todas por cada uno. */
    private Collection $porSocio;

    public function __construct(private readonly int $meses = 24)
    {
        $this->membresias = Inscripcion::query()
            ->sinPases()
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_inicio')
            ->get(['id_cliente', 'id_convenio', 'id_membresia', 'fecha_inicio', 'fecha_vencimiento', 'precio_final'])
            ->map(fn (Inscripcion $i) => (object) [
                'socio' => (int) $i->id_cliente,
                'convenio' => $i->id_convenio ? (int) $i->id_convenio : null,
                'plan' => (int) $i->id_membresia,
                'desde' => Carbon::parse($i->fecha_inicio)->startOfDay(),
                'hasta' => Carbon::parse($i->fecha_vencimiento)->endOfDay(),
                'precio' => (int) $i->precio_final,
            ])
            ->values();

        $this->porSocio = $this->membresias->groupBy('socio');
    }

    /**
     * Mes a mes: quién entró, quién renovó, cuántos quedaron y quién se fue.
     *
     * @return list<array<string,mixed>>
     */
    public function porMes(): array
    {
        $primeraDe = $this->primeraDeCadaSocio();
        $desde = Carbon::today()->startOfMonth()->subMonths($this->meses - 1);

        $filas = [];

        for ($i = 0; $i < $this->meses; $i++) {
            $mes = $desde->copy()->addMonths($i);
            $fin = $mes->copy()->endOfMonth();

            $delMes = $this->membresias->filter(fn ($m) => $m->desde->between($mes, $fin));

            // ALTA es la primera membresía de esa persona, no cualquiera que
            // empiece: sin esto, el socio de siempre que renueva cada mes se
            // contaba como uno nuevo doce veces al año.
            $altas = $delMes->filter(fn ($m) => $primeraDe[$m->socio]->equalTo($m->desde))
                ->pluck('socio')->unique();

            $renovaciones = $delMes->reject(fn ($m) => $primeraDe[$m->socio]->equalTo($m->desde));

            $activos = $this->membresias
                ->filter(fn ($m) => $m->desde->lte($fin) && $m->hasta->gte($fin))
                ->pluck('socio')->unique();

            $filas[] = [
                'mes' => $mes->translatedFormat('M Y'),
                'clave' => $mes->format('Y-m'),
                'altas' => $altas->count(),
                'renovaciones' => $renovaciones->count(),
                'activos' => $activos->count(),
                'se_fueron' => $this->seFueronEn($mes, $fin),
                'ingresos' => (int) $delMes->sum('precio'),
                // El mes que corre todavía no terminó: su columna se lee
                // distinta, porque le faltan días por pasar.
                'en_curso' => $mes->isSameMonth(Carbon::today()),
            ];
        }

        return $filas;
    }

    /**
     * Quién dejó de venir en ese mes.
     *
     * SE LE VENCIÓ Y NO VOLVIÓ A COMPRAR en los 45 días siguientes. El plazo
     * hace falta: sin él, quien renueva tres días tarde —que es lo normal en un
     * mesón— aparecería como perdido y recuperado el mismo mes, y la cuenta de
     * bajas sería tres veces la real. Por lo mismo, el último mes y medio no se
     * puede dar por cerrado: esa gente todavía puede volver.
     */
    private function seFueronEn(Carbon $inicio, Carbon $fin): int
    {
        $limite = Carbon::today()->subDays(self::DIAS_DE_GRACIA);

        if ($fin->gt($limite)) {
            return 0;
        }

        return $this->membresias
            ->filter(fn ($m) => $m->hasta->between($inicio, $fin))
            ->pluck('socio')
            ->unique()
            ->filter(function (int $socio) use ($fin) {
                $gracia = $fin->copy()->addDays(self::DIAS_DE_GRACIA);

                // Solo las suyas: antes se recorrían las mil setecientas por
                // cada socio y cada mes, y el informe tardaba medio segundo.
                return ! $this->porSocio[$socio]->contains(
                    fn ($m) => $m->desde->gt($fin) && $m->desde->lte($gracia)
                );
            })
            ->count();
    }

    /**
     * Si el que entra se queda.
     *
     * ES LA PREGUNTA CARA. Conseguir un socio nuevo cuesta plata —cartel,
     * Instagram, el descuento del primer mes—; que vuelva al segundo mes no
     * cuesta nada más que atenderlo bien. Un gimnasio con el 30% de segunda
     * compra tiene que reponer siete de cada diez socios TODOS los meses solo
     * para no achicarse.
     *
     * Se miran los que entraron hace más de dos meses: los de la semana pasada
     * todavía no han tenido ocasión de renovar, y meterlos hundiría el número.
     *
     * @return array<string,mixed>
     */
    public function retencion(): array
    {
        $primeraDe = $this->primeraDeCadaSocio();
        $corte = Carbon::today()->subMonths(2);

        $nuevos = collect($primeraDe)->filter(fn (Carbon $cuando) => $cuando->lte($corte));

        $porSocio = $this->membresias->groupBy('socio');

        $volvieron = $nuevos->filter(fn (Carbon $cuando, int $socio) => $porSocio[$socio]->count() > 1);

        // Cuántas compras hace un socio, y cuánto tiempo pasa entre la primera
        // y el final de la última: eso es lo que dura de verdad.
        $meses = $porSocio->map(function (Collection $suyas) {
            $primera = $suyas->min('desde');
            $ultima = $suyas->max('hasta');

            return max(1, (int) round($primera->diffInDays($ultima) / 30));
        });

        return [
            'nuevos' => $nuevos->count(),
            'volvieron' => $volvieron->count(),
            'porcentaje' => $nuevos->count() > 0 ? (int) round($volvieron->count() * 100 / $nuevos->count()) : 0,
            'membresias_por_socio' => $porSocio->count() > 0
                ? round($this->membresias->count() / $porSocio->count(), 1)
                : 0,
            'meses_de_vida' => $meses->count() > 0 ? (int) round($meses->median()) : 0,
            'socios' => $porSocio->count(),
        ];
    }

    /**
     * Cuánto de esto viene de las planillas viejas.
     *
     * IMPORTA PARA LEER LA RETENCIÓN. En las planillas la renovación se
     * escribía ENCIMA de la fila del socio —se le cambiaban las fechas—, así
     * que un socio de tres años quedó como una sola membresía. Con esos datos,
     * «vuelve a comprar» sale casi en cero y no porque la gente no vuelva.
     *
     * La cifra se vuelve verdad con lo que se registre desde ahora, y hasta
     * entonces la pantalla tiene que decirlo en vez de dejar que alguien tome
     * una decisión con un número que no significa eso.
     *
     * @return array{cuantas:int, de:int, avisar:bool}
     */
    public function importadas(): array
    {
        $total = Inscripcion::sinPases()->count();
        $importadas = Inscripcion::sinPases()->where('observaciones', 'like', 'Importado de%')->count();

        return [
            'cuantas' => $importadas,
            'de' => $total,
            // Con la mitad o más viniendo de las planillas, la retención que
            // sale es la de las planillas, no la del gimnasio.
            'avisar' => $total > 0 && $importadas >= $total / 2,
        ];
    }

    /**
     * De dónde sale el negocio: cuánto y cuántos socios pone cada convenio.
     *
     * Un convenio que trae tres socios al año no vale lo que cuesta mantenerlo,
     * y eso no se puede decidir sin contarlo. «Sin convenio» va en la lista a
     * propósito: es la referencia contra la que se comparan los demás.
     *
     * @return list<array<string,mixed>>
     */
    public function porConvenio(int $mesesAtras = 12): array
    {
        $desde = Carbon::today()->subMonths($mesesAtras)->startOfMonth();
        $nombres = \App\Models\Convenio::pluck('nombre', 'id');

        return $this->membresias
            ->filter(fn ($m) => $m->desde->gte($desde))
            ->groupBy(fn ($m) => $m->convenio ?? 0)
            ->map(fn (Collection $suyas, $id) => [
                'nombre' => $id ? ($nombres[$id] ?? 'Convenio borrado') : 'Sin convenio',
                'total' => (int) $suyas->sum('precio'),
                'cantidad' => $suyas->pluck('socio')->unique()->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Cuándo compró cada socio su primera membresía.
     *
     * @return array<int,Carbon>
     */
    private function primeraDeCadaSocio(): array
    {
        return $this->membresias
            ->groupBy('socio')
            ->map(fn (Collection $suyas) => $suyas->min('desde'))
            ->all();
    }
}
