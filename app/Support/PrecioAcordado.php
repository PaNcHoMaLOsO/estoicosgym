<?php

namespace App\Support;

use App\Models\ConvenioPrecio;
use App\Models\PrecioMembresia;

/**
 * Cuánto vale un plan para esta persona: UNA sola regla, en un solo sitio.
 *
 * El orden es siempre el mismo, del trato más particular al más general:
 *
 *   1. El precio que ESE convenio negoció para ESE plan (el club de básquetbol
 *      que paga 15.000 la mensualidad).
 *   2. El «precio con convenio» del plan, para quien trae un convenio sin trato
 *      propio.
 *   3. El precio normal.
 *
 * Vive aquí porque lo preguntan cuatro sitios —el alta de socio, la inscripción
 * nueva, la renovación y el cobro— y con cuatro copias bastaba con corregir una
 * para que el gimnasio cobrara dos cifras distintas por lo mismo.
 */
class PrecioAcordado
{
    /**
     * @param PrecioMembresia $precio el precio vigente del plan
     * @param int|string|null $idConvenio el convenio elegido, si hay alguno
     */
    public static function para(PrecioMembresia $precio, mixed $idConvenio): int
    {
        $normal = (int) round($precio->precio_normal);

        if (empty($idConvenio)) {
            return $normal;
        }

        $propio = ConvenioPrecio::where('id_convenio', $idConvenio)
            ->where('id_membresia', $precio->id_membresia)
            ->value('precio');

        if ($propio !== null) {
            return (int) $propio;
        }

        return $precio->precio_convenio ? (int) round($precio->precio_convenio) : $normal;
    }

    /**
     * Lo que cada convenio paga por cada plan, para las pantallas.
     *
     * Las pantallas enseñan el precio MIENTRAS se elige, antes de guardar: sin
     * esto tendrían que preguntar al servidor cada vez que se cambia de
     * convenio, con la persona esperando delante.
     *
     * @return array<int,array<int,int>> [id_convenio][id_membresia] => precio
     */
    public static function porConvenio(): array
    {
        $mapa = [];

        foreach (ConvenioPrecio::all() as $fila) {
            $mapa[(int) $fila->id_convenio][(int) $fila->id_membresia] = (int) $fila->precio;
        }

        return $mapa;
    }
}
