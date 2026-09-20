<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Pantalla de entrada del panel: lo que hay que HACER hoy.
 *
 * ES PARA QUIEN ATIENDE EL MESÓN. Lleva los atajos a lo que se hace con el
 * socio delante, las notas del día y las dos listas que piden levantar el
 * teléfono: a quién le vence esta semana y quién se fue sin renovar.
 *
 * SIN PLATA. La caja del día, lo que se debe, lo fiado y cómo va el gimnasio
 * están en Caja, que solo abre quien ve los informes. Antes vivían aquí y la
 * primera pantalla de quien atiende enseñaba las cifras del negocio.
 */
class ResumenController extends Controller
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    /** Hasta cuántos días atrás se mira quién se fue sin renovar. */
    private const DIAS_SIN_RENOVAR = 30;

    /** Cuántos socios se listan en cada lista: lo que cabe sin bajar. */
    private const EN_LISTA = 8;

    /** Un correo que se intentó y no salió. Código de la categoría `notificacion`. */
    private const AVISO_FALLIDO = 602;

    /** Hasta cuántos días adelante se mira qué planes están por empezar. */
    private const DIAS_POR_EMPEZAR = 14;

    public function __invoke()
    {
        $hoy = Carbon::today();
        $enUnaSemana = $hoy->copy()->addDays(7);

        // Todo lo de aquí habla de MENSUALIDADES. Un pase diario vence al día
        // siguiente: contarlo llenaba «vencen» y «se fueron» de gente de paso.
        $porVencer = Inscripcion::query()
            ->sinPases()
            ->where('id_estado', self::ACTIVA)
            ->whereBetween('fecha_vencimiento', [$hoy, $enUnaSemana]);

        $sinRenovar = $this->sinRenovar($hoy);

        return Inertia::render('Resumen', [
            'cifras' => [
                // Socios son los que tienen o tuvieron una mensualidad, o los
                // recién dados de alta. Quien solo compró pases no lo es.
                'socios' => Cliente::where('activo', true)
                    ->where(fn (Builder $q) => $q
                        ->whereHas('inscripciones', fn (Builder $q) => $q->sinPases())
                        ->orWhereDoesntHave('inscripciones'))
                    ->count(),
                'al_dia' => Inscripcion::sinPases()->where('id_estado', self::ACTIVA)->count(),
                'pausadas' => Inscripcion::sinPases()->where('id_estado', self::PAUSADA)->count(),
                'vencen_semana' => (clone $porVencer)->count(),
                'sin_renovar' => (clone $sinRenovar)->count(),
                'dias_sin_renovar' => self::DIAS_SIN_RENOVAR,
            ],

            'notas' => $this->notas(),

            /*
             * LOS AVISOS QUE NO SALIERON. El sistema le escribe al socio cuando
             * se le acerca el vencimiento; si ese correo falla y nadie se entera,
             * en el mesón se da por avisado a alguien que no lo fue. Aquí solo se
             * cuenta: reintentarlos se hace en Notificaciones, y el enlace solo
             * se le ofrece a quien puede entrar ahí.
             */
            'avisosFallidos' => Notificacion::where('id_estado', self::AVISO_FALLIDO)->count(),

            /*
             * LOS PLANES QUE EMPIEZAN PRONTO: inscripciones ya hechas cuyo primer
             * día todavía no llega. Es la persona que aparece el lunes por
             * primera vez, y conviene saber su nombre antes de que entre.
             */
            'porEmpezar' => Inscripcion::query()
                ->sinPases()
                ->whereIn('id_estado', [self::ACTIVA, self::PAUSADA])
                ->whereBetween('fecha_inicio', [$hoy->copy()->addDay(), $hoy->copy()->addDays(self::DIAS_POR_EMPEZAR)])
                ->with(['cliente:id,uuid,nombres,apellido_paterno,apellido_materno,email,celular', 'membresia:id,nombre'])
                ->orderBy('fecha_inicio')
                ->limit(self::EN_LISTA)
                ->get()
                ->map(fn (Inscripcion $i) => [
                    ...$this->fila($i, (int) $hoy->diffInDays($i->fecha_inicio, false)),
                    'fecha' => $i->fecha_inicio?->format('d/m/Y'),
                ])
                ->all(),

            'porVencer' => $porVencer
                ->with(['cliente:id,uuid,nombres,apellido_paterno,apellido_materno,email,celular', 'membresia:id,nombre'])
                ->orderBy('fecha_vencimiento')
                ->limit(self::EN_LISTA)
                ->get()
                ->map(fn (Inscripcion $i) => $this->fila($i, (int) $hoy->diffInDays($i->fecha_vencimiento, false)))
                ->all(),

            'sinRenovar' => $sinRenovar
                ->with(['cliente:id,uuid,nombres,apellido_paterno,apellido_materno,email,celular', 'membresia:id,nombre'])
                // El que se fue hace menos, arriba: es al que todavía se le
                // puede convencer de volver.
                ->orderByDesc('fecha_vencimiento')
                ->limit(self::EN_LISTA)
                ->get()
                ->map(fn (Inscripcion $i) => $this->fila($i, (int) $i->fecha_vencimiento->copy()->startOfDay()->diffInDays($hoy)))
                ->all(),
        ]);
    }

    /**
     * Las membresías de socios que se fueron sin renovar, en los últimos días.
     *
     * «SIN RENOVAR» ES QUE EL SOCIO NO TENGA NADA VIGENTE, no que la membresía
     * esté vencida. Al renovar, la membresía anterior se cierra como vencida:
     * contando solo el estado, quien renovó salía aquí como que se estaba
     * yendo, y el número se inflaba justo con los socios que sí se quedaron.
     */
    private function sinRenovar(Carbon $hoy): Builder
    {
        return Inscripcion::query()
            ->sinPases()
            ->where('id_estado', self::VENCIDA)
            ->where('fecha_vencimiento', '>=', $hoy->copy()->subDays(self::DIAS_SIN_RENOVAR))
            // Un pase comprado después no es renovar: sigue sin mensualidad.
            ->whereDoesntHave('cliente.inscripciones', fn (Builder $q) => $q
                ->sinPases()
                ->whereIn('id_estado', [self::ACTIVA, self::PAUSADA]));
    }

    /**
     * Una fila de las listas de llamar: quién, qué plan, cuándo y cómo avisarle.
     *
     * @return array<string,mixed>
     */
    private function fila(Inscripcion $i, int $dias): array
    {
        $cliente = $i->cliente;

        return [
            'uuid' => $i->uuid,
            'socio_uuid' => $cliente?->uuid,
            'socio' => $cliente
                ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                : 'Socio eliminado',
            'membresia' => $i->membresia?->nombre,
            'fecha' => $i->fecha_vencimiento?->format('d/m/Y'),
            'dias' => $dias,
            // Sin correo ni celular no hay a quién avisar: ese socio hay que
            // buscarlo a mano y conviene que se vea desde aquí.
            'celular' => $cliente?->celular,
            'email' => $cliente?->email,
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
                // Cuántos días lleva ahí. Las notas NO se borran solas —una
                // tarea pendiente que desaparece sola es lo peor que puede
                // pasar—, pero una que lleva una semana sin que nadie la toque
                // se enseña distinta para que alguien decida: se hace o se quita.
                'dias' => (int) $n->created_at?->startOfDay()->diffInDays(today()),
                'vieja' => $n->estaVieja(),
            ])
            ->all();
    }
}
