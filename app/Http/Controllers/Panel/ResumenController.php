<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Pantalla de entrada del panel.
 *
 * AQUI NO HAY DINERO, a proposito. Es la primera pantalla que se abre y la ve
 * todo el mundo, incluida recepcion —que por permisos NO entra a los informes de
 * ingresos—: dejar «recaudado hoy» en la portada le enseñaba por la puerta de
 * atras justo lo que el resto del sistema le tapa. La plata vive en Reportes,
 * detras del permiso `reportes.ver`.
 *
 * Lo que queda es lo que se puede HACER hoy: a quien hay que llamar, quien se
 * fue sin renovar y como va creciendo el gimnasio.
 */
class ResumenController extends Controller
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    /** Cuántos meses de historia se pintan en la tendencia. */
    private const MESES = 6;

    public function __invoke()
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
     * Socios nuevos en cada uno de los últimos meses.
     *
     * Se pintan TODOS los meses aunque no haya altas: un hueco en la serie se
     * lee como «no se midió», y un mes que falta parece un error.
     */
    private function altasPorMes(Carbon $hoy): array
    {
        $desde = $hoy->copy()->subMonths(self::MESES - 1)->startOfMonth();

        $conteo = Cliente::query()
            ->where('created_at', '>=', $desde)
            ->selectRaw('YEAR(created_at) as anio, MONTH(created_at) as mes, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($f) => "{$f->anio}-{$f->mes}");

        return collect(range(self::MESES - 1, 0))
            ->map(function (int $atras) use ($hoy, $conteo) {
                $mes = $hoy->copy()->subMonths($atras);
                $clave = "{$mes->year}-{$mes->month}";

                return [
                    'mes' => $mes->translatedFormat('M'),
                    'total' => (int) ($conteo[$clave]->total ?? 0),
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
