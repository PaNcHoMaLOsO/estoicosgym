<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Models\Pago;
use App\Support\IngresosDelNegocio;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * La caja del día: lo que entró HOY, para cuadrar el cajón al cerrar el turno.
 *
 * Es para quien tiene «Ver la caja del día» (`caja.hoy`) y no «Ver la caja
 * completa» (`reportes.ver`). Quien cierra el turno necesita saber cuánto
 * tendría que haber en efectivo y cuánto entró por transferencia, y qué cobros
 * lo forman para encontrar el que no cuadra. No necesita, y no recibe, cuánto
 * factura el gimnasio en el mes, cómo va contra el mes pasado ni quién debe:
 * esas cifras NO SE MANDAN, no se tapan. Lo de esconder en pantalla es aparte.
 *
 * Los mismos ajustes de privacidad que la caja completa: si se pidió cerrar
 * la Caja, la ruta lleva el mismo `sin-dinero:caja`; y las cifras se tapan con
 * el mismo ojito, que la pantalla resuelve con <Reservado>.
 */
class CajaDelDiaController extends Controller
{
    public function __invoke(Request $request)
    {
        $hoy = Carbon::today();
        $finDelDia = $hoy->copy()->endOfDay();

        return Inertia::render('CajaDelDia', [
            'fecha' => $hoy->translatedFormat('l j \d\e F'),
            'fuentes' => IngresosDelNegocio::FUENTES,
            // Por fuente y en total, con el IVA de los talleres incluido: es lo
            // que entró de verdad, y lo que se cuadra contra la cuenta.
            'entradas' => IngresosDelNegocio::entre($hoy, $hoy),
            // Membresías y mesón, que son lo que pasa por el cajón.
            'porMetodo' => IngresosDelNegocio::porMetodo($hoy, $hoy),
            'cobros' => [
                'membresias' => $this->pagosDeHoy($hoy, $finDelDia),
                'meson' => $this->mesonDeHoy($hoy, $finDelDia),
                'talleres' => $this->talleresDeHoy($hoy),
            ],
        ]);
    }

    /** Los cobros de membresías con fecha de hoy, el último primero. */
    private function pagosDeHoy(Carbon $hoy, Carbon $fin): array
    {
        return Pago::ingresos()
            ->whereBetween('fecha_pago', [$hoy, $fin])
            ->with([
                'cliente:id,nombres,apellido_paterno',
                'inscripcion:id,id_membresia',
                'inscripcion.membresia:id,nombre',
                'metodoPago:id,nombre',
                'metodoPago2:id,nombre',
                'usuario:id,name',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Pago $p) {
                $repartido = PagoEditarController::esRepartido($p);
                $abonado = (int) $p->monto_abonado;
                // El reparto se lee igual que en la ficha del pago: la primera
                // parte acotada al monto y la segunda por diferencia.
                $primera = $repartido ? min(max((int) $p->monto_metodo1, 0), $abonado) : $abonado;

                return [
                    'uuid' => $p->uuid,
                    'hora' => $p->created_at?->format('H:i'),
                    'socio' => $p->cliente
                        ? trim("{$p->cliente->nombres} {$p->cliente->apellido_paterno}")
                        : 'Socio eliminado',
                    'detalle' => $p->inscripcion?->membresia?->nombre,
                    'medios' => array_values(array_filter([
                        $p->metodoPago ? ['nombre' => $p->metodoPago->nombre, 'monto' => $primera] : null,
                        $repartido && $p->metodoPago2 ? ['nombre' => $p->metodoPago2->nombre, 'monto' => $abonado - $primera] : null,
                    ])),
                    'monto' => $abonado,
                    'quien' => $p->usuario?->name,
                ];
            })
            ->all();
    }

    /**
     * Lo fiado que se cobró hoy, una fila por cobro y no por línea: cobrar la
     * cuenta de alguien salda varias líneas de una vez, y en el cajón entró
     * una sola vez.
     */
    private function mesonDeHoy(Carbon $hoy, Carbon $fin): array
    {
        return Fiado::where('pagado', true)
            ->whereBetween('pagado_en', [$hoy, $fin])
            ->with(['cliente:id,nombres,apellido_paterno', 'metodoPago:id,nombre', 'cobrador:id,name'])
            ->orderByDesc('pagado_en')
            ->get()
            ->groupBy(fn (Fiado $f) => $f->pagado_en?->toDateTimeString() . '|' . $f->claveDeCuenta())
            ->map(function ($lineas) {
                $primera = $lineas->first();

                return [
                    'hora' => $primera->pagado_en?->format('H:i'),
                    'nombre' => $primera->aNombreDe(),
                    'detalle' => $lineas->count() === 1
                        ? $primera->concepto
                        : $lineas->count() . ' cosas',
                    'medio' => $primera->metodoPago?->nombre ?? 'Sin anotar',
                    'monto' => (int) $lineas->sum('monto'),
                    'quien' => $primera->cobrador?->name,
                ];
            })
            ->values()
            ->all();
    }

    /** Lo que pagaron hoy colegios y empresas. Llega por transferencia. */
    private function talleresDeHoy(Carbon $hoy): array
    {
        return CobroTaller::whereNotNull('pagado_en')
            // Igual que IngresosDelNegocio, para que la lista sume lo mismo que
            // la cifra de arriba. Con los talleres de la papelera: esa factura
            // se pagó igual, y la relación ya los trae.
            ->whereBetween('pagado_en', [$hoy->toDateString(), $hoy->toDateString()])
            ->with('taller:id,nombre')
            ->get()
            ->map(fn (CobroTaller $c) => [
                'nombre' => $c->taller?->nombre ?? 'Taller borrado',
                'detalle' => $c->folio ? "Factura {$c->folio}" : null,
                'monto' => (int) $c->total,
            ])
            ->all();
    }
}
