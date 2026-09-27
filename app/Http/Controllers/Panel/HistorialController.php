<?php

namespace App\Http\Controllers\Panel;

use App\Support\BusquedaDeSocio;
use Illuminate\Http\Request;
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
    /**
     * Como se lee cada tipo de cambio.
     *
     * El valor guardado es el de la columna —«cambio_estado_inscripcion»— y
     * enseñarlo poniendole la primera en mayuscula daba «Renovacion» sin
     * tilde y «Cambio estado inscripcion» en una sola linea.
     */
    private const COMO_SE_LLAMA = [
        'pausa' => 'Pausa',
        'reanudacion' => 'Reanudación',
        'cambio_plan' => 'Cambio de plan',
        'renovacion' => 'Renovación',
        'traspaso' => 'Traspaso',
        'inscripcion' => 'Alta',
        'cambio_estado_inscripcion' => 'Cambio de estado',
        'cambio_estado_cliente' => 'Cambio de estado del socio',
        'cancelacion_inscripcion' => 'Cancelación',
        'suspension' => 'Suspensión',
        'vencimiento' => 'Vencimiento',
    ];

    /**
     * BUSCAR, FILTRAR Y VER MÁS. Eran los últimos 100 movimientos sin más:
     * para saber cuándo se pausó la membresía de alguien había que bajar la
     * lista a ojo, y lo que pasó antes de esos 100 no se veía nunca.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $tipo = (string) $request->query('tipo', '');
        $tipo = $tipo === 'traspaso' || isset(self::COMO_SE_LLAMA[$tipo]) ? $tipo : '';
        // Cuántos se ven: 100, y «Ver más» los va doblando hasta 1.600.
        $cuantos = min(1600, max(100, (int) $request->query('cuantos', 100)));
        $delSocio = fn ($q) => BusquedaDeSocio::aplicar($q, $buscar);

        // Los codigos de estado se resuelven a nombre AQUI y de una sola vez:
        // la fila guarda el codigo (100, 101…) y en pantalla eso no dice nada.
        $estados = Estado::pluck('nombre', 'codigo');

        $cambios = HistorialCambio::query()
            ->with(['cliente', 'usuario'])
            ->when($buscar !== '', fn ($q) => $q->whereHas('cliente', $delSocio))
            ->when($tipo === 'traspaso', fn ($q) => $q->whereRaw('1 = 0'))
            ->when($tipo !== '' && $tipo !== 'traspaso', fn ($q) => $q->where('tipo_cambio', $tipo))
            ->orderByDesc('fecha_cambio')
            ->limit($cuantos + 1)
            ->get()
            ->map(fn (HistorialCambio $c) => [
                'id' => "cambio-{$c->id}",
                'cuando' => $c->fecha_cambio ?? $c->created_at,
                'clase' => 'cambio',
                'titulo' => self::COMO_SE_LLAMA[$c->tipo_cambio] ?? 'Cambio',
                'socio' => $c->cliente
                    ? trim("{$c->cliente->nombres} {$c->cliente->apellido_paterno}")
                    : null,
                // El uuid para poder llegar a su ficha: de ahi sale todo lo
                // demas —sus membresias, sus pagos—, que es lo que se viene a
                // mirar despues de leer que paso.
                'socio_uuid' => $c->cliente?->uuid,
                'detalle' => $c->motivo ?: $this->resumirDetalles($c->detalles),
                'de' => $estados[$c->estado_anterior] ?? $c->estado_anterior,
                'a' => $estados[$c->estado_nuevo] ?? $c->estado_nuevo,
                'usuario' => $c->usuario?->name,
            ]);

        $traspasos = HistorialTraspaso::query()
            ->with(['clienteOrigen', 'clienteDestino', 'usuario'])
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('clienteOrigen', $delSocio)
                ->orWhereHas('clienteDestino', $delSocio)))
            ->when($tipo !== '' && $tipo !== 'traspaso', fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('fecha_traspaso')
            ->limit($cuantos + 1)
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
                // En un traspaso los dos extremos son personas, y se llega a
                // las dos fichas desde aqui.
                'de_uuid' => $t->clienteOrigen?->uuid,
                'a_uuid' => $t->clienteDestino?->uuid,
                'usuario' => $t->usuario?->name,
            ]);

        // Se ordena despues de unir: cada consulta viene ordenada por su cuenta
        // y concatenarlas sin mas dejaria los traspasos todos al final.
        $todos = $cambios->concat($traspasos)->sortByDesc('cuando');
        $hayMas = $todos->count() > $cuantos;

        $movimientos = $todos
            ->take($cuantos)
            ->map(fn (array $m) => [
                ...$m,
                'cuando' => $m['cuando']?->format('d/m/Y H:i'),
            ])
            ->values();

        return Inertia::render('Historial/Index', [
            'movimientos' => $movimientos,
            'filtros' => ['buscar' => $buscar, 'tipo' => $tipo, 'cuantos' => $cuantos],
            'hayMas' => $hayMas,
            'tipos' => collect(self::COMO_SE_LLAMA)
                ->reject(fn ($nombre, $clave) => $clave === 'traspaso')
                ->map(fn ($nombre, $clave) => ['valor' => $clave, 'etiqueta' => $nombre])
                ->values()
                ->push(['valor' => 'traspaso', 'etiqueta' => 'Traspaso'])
                ->sortBy('etiqueta')
                ->values(),
        ]);
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
