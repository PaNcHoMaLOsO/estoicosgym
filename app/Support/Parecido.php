<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Buscar texto sin fijarse en mayúsculas ni tildes, en MySQL y en PostgreSQL.
 *
 * EN MYSQL ESTO SALÍA GRATIS: la comparación de la base no distingue
 * mayúsculas ni tildes, así que «hernandez» encontraba a «Hernández». En
 * PostgreSQL —el del servidor— LIKE distingue las dos cosas, y el buscador del
 * mesón dejaba de encontrar a medio padrón: quien escribe rápido no pone
 * mayúsculas ni tildes, y el socio «no existía». Se le creaba otra ficha.
 *
 * En PostgreSQL se usa ILIKE, que no mira mayúsculas, y unaccent, que quita
 * las tildes, si la extensión está instalada (la instala una migración; si el
 * servidor no deja, se busca igual sin mirar mayúsculas y las tildes cuentan).
 *
 * Se usa como `->whereParecido('nombres', "%juan%")` y
 * `->orWhereParecido(...)`: lo registra AppServiceProvider.
 */
class Parecido
{
    private static ?bool $unaccent = null;

    public static function registrar(): void
    {
        Builder::macro('whereParecido', function (string $columna, string $valor, string $boolean = 'and') {
            /** @var Builder $this */
            if ($this->getConnection()->getDriverName() !== 'pgsql') {
                return $this->where($columna, 'like', $valor, $boolean);
            }

            $campo = 'CAST(' . $this->getGrammar()->wrap($columna) . ' AS TEXT)';

            return Parecido::conUnaccent()
                ? $this->whereRaw("unaccent({$campo}) ILIKE unaccent(?)", [$valor], $boolean)
                : $this->whereRaw("{$campo} ILIKE ?", [$valor], $boolean);
        });

        Builder::macro('orWhereParecido', function (string $columna, string $valor) {
            /** @var Builder $this */
            return $this->whereParecido($columna, $valor, 'or');
        });
    }

    /** ¿Tiene el PostgreSQL la extensión que quita las tildes? Se pregunta una vez. */
    public static function conUnaccent(): bool
    {
        if (self::$unaccent !== null) {
            return self::$unaccent;
        }

        try {
            return self::$unaccent = DB::table('pg_extension')->where('extname', 'unaccent')->exists();
        } catch (\Throwable) {
            return self::$unaccent = false;
        }
    }
}
