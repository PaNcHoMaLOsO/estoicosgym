<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Ajustes;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * El buscador de socios que va en el marco del panel, en TODAS las pantallas.
 *
 * En el mesón todo empieza por un nombre o un RUT: llega alguien y hay que
 * saber si está al día, cobrarle o renovarle. El único buscador estaba en
 * Resumen, así que desde cualquier otra pantalla había que volver al inicio
 * para encontrar a la persona. Este responde desde donde se esté y lleva
 * directo a la ficha, que es desde donde se hace todo lo demás.
 *
 * Devuelve lo justo para reconocer a la persona y saber cómo está sin abrir
 * nada: su plan, cuándo vence y si debe.
 */
class BuscarSocioController extends Controller
{
    private const RESULTADOS = 8;

    private const ACTIVA = 100;

    public function __invoke(Request $request): JsonResponse
    {
        $texto = trim((string) $request->query('q', ''));

        // Con una letra saldría medio padrón y no serviría para elegir.
        if (mb_strlen($texto) < 2) {
            return response()->json(['socios' => []]);
        }

        // «juan perez» tiene que encontrar a Juan Pérez aunque nombre y apellido
        // estén en columnas distintas: cada palabra debe calzar en alguna.
        $palabras = array_slice(preg_split('/\s+/', $texto), 0, 4);

        $socios = Cliente::query()
            ->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    // El RUT se busca SIN puntos ni guion a los dos lados: en
                    // la base hay fichas viejas escritas de las dos formas, y
                    // quien teclea 12345678 tiene que encontrar a 12.345.678-9.
                    $sinFormato = preg_replace('/[^0-9kK]/', '', $palabra);

                    $q->where(function ($q) use ($palabra, $sinFormato) {
                        // Sin mirar mayúsculas ni tildes, también en PostgreSQL.
                        $q->whereParecido('nombres', "%{$palabra}%")
                            ->orWhereParecido('apellido_paterno', "%{$palabra}%")
                            ->orWhereParecido('apellido_materno', "%{$palabra}%")
                            ->orWhereParecido('run_pasaporte', "%{$palabra}%")
                            ->orWhereParecido('celular', "%{$palabra}%");

                        if ($sinFormato !== '') {
                            $q->orWhereRaw(
                                "replace(replace(replace(run_pasaporte, '.', ''), '-', ''), ' ', '') like ?",
                                ["%{$sinFormato}%"]
                            )->orWhereRaw(
                                "replace(replace(celular, ' ', ''), '+', '') like ?",
                                ["%{$sinFormato}%"]
                            );
                        }
                    });
                }
            })
            ->with(['inscripciones' => fn ($q) => $q
                ->with('membresia:id,nombre')
                ->withSum('pagos as abonado', 'monto_abonado')
                ->orderByDesc('fecha_vencimiento')])
            // Los activos primero: a quien se busca casi siempre es socio hoy.
            ->orderByDesc('activo')
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->limit(self::RESULTADOS)
            ->get()
            ->map(function (Cliente $c) {
                $vigente = $c->inscripciones->first(fn ($i) => (int) $i->id_estado === self::ACTIVA);
                $debe = (int) $c->inscripciones->sum(
                    fn ($i) => max(0, (int) ($i->precio_final ?? $i->precio_base) - (int) ($i->abonado ?? 0))
                );

                return [
                    'uuid' => $c->uuid,
                    'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                    'rut' => $c->run_pasaporte,
                    'activo' => (bool) $c->activo,
                    'plan' => $vigente?->membresia?->nombre,
                    'vence' => $vigente?->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => $vigente?->fecha_vencimiento
                        ? (int) now()->startOfDay()->diffInDays($vigente->fecha_vencimiento, false)
                        : null,
                    // En cero cuando se pidió esconder quién debe: el
                    // buscador sale en todas las pantallas, y es el sitio por
                    // donde una cifra escondida se asomaría igual.
                    'debe' => Ajustes::activo('privacidad.ocultar_pendientes') ? 0 : $debe,
                ];
            });

        return response()->json(['socios' => $socios]);
    }
}
