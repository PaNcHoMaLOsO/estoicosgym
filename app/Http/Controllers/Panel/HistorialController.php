<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\HistorialCambio;
use App\Models\HistorialTraspaso;
use Inertia\Inertia;

/**
 * Historial de movimientos del panel nuevo (Inertia + React).
 *
 * Une los cambios de estado y los traspasos en UNA sola linea de tiempo. Son
 * dos tablas distintas pero para quien atiende son lo mismo: «que le paso a
 * este socio y cuando». Tenerlas en dos pestañas obligaba a mirar dos veces
 * para reconstruir un dia.
 */
class HistorialController extends Controller
{
    public function index()
    {
        // Los codigos de estado se resuelven a nombre AQUI y de una sola vez:
        // la fila guarda el codigo (100, 101…) y en pantalla eso no dice nada.
        $estados = Estado::pluck('nombre', 'codigo');

        $cambios = HistorialCambio::query()
            ->with(['cliente', 'usuario'])
            ->orderByDesc('fecha_cambio')
            ->limit(100)
            ->get()
            ->map(fn (HistorialCambio $c) => [
                'id' => "cambio-{$c->id}",
                'cuando' => $c->fecha_cambio ?? $c->created_at,
                'clase' => 'cambio',
                'titulo' => $c->tipo_cambio ? ucfirst(str_replace('_', ' ', $c->tipo_cambio)) : 'Cambio',
                'socio' => $c->cliente
                    ? trim("{$c->cliente->nombres} {$c->cliente->apellido_paterno}")
                    : null,
                'detalle' => $c->motivo ?: $this->resumirDetalles($c->detalles),
                'de' => $estados[$c->estado_anterior] ?? $c->estado_anterior,
                'a' => $estados[$c->estado_nuevo] ?? $c->estado_nuevo,
                'usuario' => $c->usuario?->name,
            ]);

        $traspasos = HistorialTraspaso::query()
            ->with(['clienteOrigen', 'clienteDestino', 'usuario'])
            ->orderByDesc('fecha_traspaso')
            ->limit(100)
            ->get()
            ->map(fn (HistorialTraspaso $t) => [
                'id' => "traspaso-{$t->id}",
                'cuando' => $t->fecha_traspaso ?? $t->created_at,
                'clase' => 'traspaso',
                'titulo' => 'Traspaso de membresía',
                'socio' => null,
                'detalle' => $t->motivo,
                'de' => $t->clienteOrigen
                    ? trim("{$t->clienteOrigen->nombres} {$t->clienteOrigen->apellido_paterno}")
                    : null,
                'a' => $t->clienteDestino
                    ? trim("{$t->clienteDestino->nombres} {$t->clienteDestino->apellido_paterno}")
                    : null,
                'usuario' => $t->usuario?->name,
            ]);

        // Se ordena despues de unir: cada consulta viene ordenada por su cuenta
        // y concatenarlas sin mas dejaria los traspasos todos al final.
        $movimientos = $cambios
            ->concat($traspasos)
            ->sortByDesc('cuando')
            ->take(100)
            ->map(fn (array $m) => [
                ...$m,
                'cuando' => $m['cuando']?->format('d/m/Y H:i'),
            ])
            ->values();

        return Inertia::render('Historial/Index', ['movimientos' => $movimientos]);
    }

    /**
     * `detalles` viene casteado a array, y mandarlo tal cual a React reventaba
     * la pantalla entera («Objects are not valid as a React child»). Se resume
     * a una linea legible y se dejan fuera las claves sin valor, que son la
     * mayoria: de {"dias_pausa":null,"dias_compensados":351} solo importa la
     * segunda.
     */
    private function resumirDetalles(mixed $detalles): ?string
    {
        if (! is_array($detalles)) {
            return is_string($detalles) && $detalles !== '' ? $detalles : null;
        }

        $partes = [];

        foreach ($detalles as $clave => $valor) {
            if ($valor === null || $valor === '' || $valor === false || is_array($valor)) {
                continue;
            }

            $etiqueta = ucfirst(str_replace('_', ' ', (string) $clave));
            $partes[] = $etiqueta . ': ' . (is_bool($valor) ? 'sí' : $valor);
        }

        return $partes === [] ? null : implode(' · ', $partes);
    }
}
