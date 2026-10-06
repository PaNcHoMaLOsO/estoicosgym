<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * El código corto de un pago o de una membresía: los primeros 8 caracteres de
 * su uuid, como #E5E42D49.
 *
 * Es para nombrarlos («revisa el pago #E5E42D49»), anotarlo en una boleta o un
 * WhatsApp, y encontrarlo después escribiéndolo en el buscador de la lista. No
 * es un número nuevo: sale del uuid que ya tienen, así que no hay nada que
 * numerar ni que se pueda repetir.
 */
class CodigoCorto
{
    public static function de(?string $uuid): ?string
    {
        return $uuid ? '#' . strtoupper(substr($uuid, 0, 8)) : null;
    }

    /** Lo escrito, si tiene forma de código (con o sin #), en minúsculas. */
    public static function leer(string $texto): ?string
    {
        return preg_match('/^#?([0-9a-f]{8})$/i', trim($texto), $m) ? strtolower($m[1]) : null;
    }

    /**
     * Busca por el socio O por el código. Las dos a la vez porque un RUT sin
     * puntos ni guion («12345678») también tiene forma de código.
     */
    public static function oPorSocio(Builder $consulta, string $busqueda): Builder
    {
        $codigo = self::leer($busqueda);

        return $consulta->where(function (Builder $q) use ($busqueda, $codigo) {
            $q->whereHas('cliente', fn ($c) => BusquedaDeSocio::aplicar($c, $busqueda));

            if ($codigo) {
                // Como texto: en PostgreSQL la columna es de tipo uuid y LIKE
                // no se le aplica directo.
                $q->orWhereRaw('CAST(' . $q->getModel()->qualifyColumn('uuid') . ' AS TEXT) LIKE ?', [$codigo . '%']);
            }
        });
    }
}
