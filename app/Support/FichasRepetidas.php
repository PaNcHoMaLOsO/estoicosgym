<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Las fichas que pueden ser de la misma persona.
 *
 * El alta ya no deja repetir un RUT, pero las planillas se cargaron antes y
 * traían fichas sin RUT: la misma persona quedó con una ficha sin RUT y otra
 * con él. Se sospecha por:
 *
 *  · el mismo RUT (no debería pasar, pero si pasa es seguro);
 *  · el mismo nombre y apellido paterno;
 *  · el mismo celular (los ocho últimos dígitos).
 *
 * Es una sospecha: dos personas se pueden llamar igual. Por eso nada se junta
 * solo; se muestra, alguien decide, y lo que se marca como «personas
 * distintas» no vuelve a salir.
 */
class FichasRepetidas
{
    /**
     * Los grupos de fichas sospechosas, lo más seguro primero.
     *
     * @return list<array{clave:string, porque:string, fichas:list<array<string,mixed>>}>
     */
    public static function grupos(): array
    {
        $socios = Cliente::query()
            ->whereNull('datos_borrados_en')
            ->get(['id', 'uuid', 'run_pasaporte', 'nombres', 'apellido_paterno', 'apellido_materno', 'celular', 'email', 'activo', 'created_at']);

        $distintos = self::paresDistintos();
        $grupos = [];
        $vistos = [];

        foreach (self::criterios() as $porque => $clave) {
            foreach ($socios->groupBy($clave) as $valor => $grupo) {
                if ($valor === '' || $grupo->count() < 2) {
                    continue;
                }

                // Fuera los pares que ya se revisaron: queda en el grupo quien
                // tenga al menos un compañero sin revisar.
                $porId = $grupo->keyBy('id');
                $ids = $grupo->pluck('id')->sort()->values();
                $quedan = $ids->filter(fn ($id) => $ids->contains(
                    fn ($otro) => $otro !== $id
                        && ! isset($distintos[min($id, $otro) . '-' . max($id, $otro)])
                        && self::puedenSerLaMisma($porId[$id], $porId[$otro])
                ))->values();

                if ($quedan->count() < 2) {
                    continue;
                }

                // El mismo grupo por nombre y por celular sale una vez.
                $claveGrupo = $quedan->implode('-');

                if (isset($vistos[$claveGrupo])) {
                    continue;
                }

                $vistos[$claveGrupo] = true;
                $fichas = $grupo->whereIn('id', $quedan->all());
                $ruts = $fichas->map(fn (Cliente $c) => strtoupper(preg_replace('/[^0-9kK]/', '', (string) $c->run_pasaporte)))
                    ->filter()
                    ->unique();

                $grupos[] = [
                    'clave' => $claveGrupo,
                    'porque' => $porque,
                    // Con dos RUT distintos lo más probable es que sean dos
                    // personas que se llaman igual: se muestran aparte.
                    'probable' => $ruts->count() <= 1,
                    'fichas' => $fichas->map(fn (Cliente $c) => self::ficha($c))->values()->all(),
                ];
            }
        }

        // Lo probable primero.
        usort($grupos, fn ($a, $b) => $b['probable'] <=> $a['probable']);

        return $grupos;
    }

    /** Las otras fichas que pueden ser de esta persona, para avisar en su ficha. */
    public static function de(Cliente $cliente): array
    {
        return collect(self::grupos())
            ->filter(fn (array $g) => $g['probable'] && collect($g['fichas'])->contains('id', $cliente->id))
            ->flatMap(fn (array $g) => collect($g['fichas'])
                ->where('id', '!=', $cliente->id)
                ->map(fn (array $f) => $f + ['porque' => $g['porque']]))
            ->unique('id')
            ->values()
            ->all();
    }

    /** Marca que las fichas son de personas distintas: no vuelven a salir juntas. */
    public static function sonDistintos(array $ids, ?int $usuario): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        foreach ($ids as $i => $a) {
            foreach (array_slice($ids, $i + 1) as $b) {
                DB::table('socios_distintos')->insertOrIgnore([
                    'id_a' => min($a, $b),
                    'id_b' => max($a, $b),
                    'id_usuario' => $usuario,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /** Por qué se sospecha → con qué se agrupa. */
    private static function criterios(): array
    {
        return [
            'Mismo RUT' => fn (Cliente $c) => strtoupper(preg_replace('/[^0-9kK]/', '', (string) $c->run_pasaporte)),
            'Mismo nombre' => function (Cliente $c) {
                $nombre = self::texto($c->nombres);
                $apellido = self::texto($c->apellido_paterno);

                return $nombre === '' || $apellido === '' ? '' : "{$nombre}|{$apellido}";
            },
            'Mismo celular' => function (Cliente $c) {
                $digitos = preg_replace('/\D/', '', (string) $c->celular);

                return strlen($digitos) >= 8 ? substr($digitos, -8) : '';
            },
        ];
    }

    /**
     * Dos fichas con el mismo nombre y apellido pero con el segundo apellido
     * distinto son dos personas: «Muñoz Castillo» no es «Muñoz Pérez». Si a
     * una le falta —las planillas casi nunca lo traían—, pueden ser la misma.
     */
    private static function puedenSerLaMisma(Cliente $a, Cliente $b): bool
    {
        $maternoA = self::texto($a->apellido_materno);
        $maternoB = self::texto($b->apellido_materno);

        return $maternoA === '' || $maternoB === '' || $maternoA === $maternoB;
    }

    private static function texto(?string $valor): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtolower(Str::ascii((string) $valor))));
    }

    /** @return array<string,true> «3-17» por cada par ya revisado. */
    private static function paresDistintos(): array
    {
        return DB::table('socios_distintos')->get(['id_a', 'id_b'])
            ->mapWithKeys(fn ($p) => ["{$p->id_a}-{$p->id_b}" => true])
            ->all();
    }

    /** Lo que se compara lado a lado. */
    private static function ficha(Cliente $c): array
    {
        $membresias = Inscripcion::where('id_cliente', $c->id)
            ->orderByDesc('fecha_vencimiento')
            ->get(['fecha_vencimiento']);

        return [
            'id' => $c->id,
            'uuid' => $c->uuid,
            'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
            'rut' => $c->run_pasaporte,
            'celular' => $c->celular,
            'email' => $c->email,
            'activo' => (bool) $c->activo,
            'membresias' => $membresias->count(),
            'ultima_vence' => $membresias->first()?->fecha_vencimiento?->format('d/m/Y'),
            'pagos' => DB::table('pagos')->where('id_cliente', $c->id)->count(),
            'fiado' => DB::table('fiados')->where('id_cliente', $c->id)->count(),
            'registrada' => $c->created_at?->format('d/m/Y'),
        ];
    }
}
