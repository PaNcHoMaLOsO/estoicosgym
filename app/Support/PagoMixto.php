<?php

namespace App\Support;

use App\Enums\EstadosCodigo;
use App\Models\MetodoPago;
use Illuminate\Validation\ValidationException;

/**
 * Un pago repartido entre DOS medios: la única forma que tiene en este sistema.
 *
 * HABÍA DOS FORMAS DE GUARDAR LO MISMO. Cobrar dejaba una fila con los dos
 * medios y cuánto por cada uno; inscribir, renovar y el alta dejaban una fila
 * por parte, aceptaban tres o cuatro partes y hasta «efectivo + efectivo». La
 * Caja, los informes, la corrección y la ficha del pago entienden la primera;
 * la segunda la leían como pagos sueltos que decían «mixto» sin serlo, y el
 * filtro por medio del listado no encontraba la segunda mitad.
 *
 * Ahora los tres caminos pasan por aquí: exactamente dos partes, dos medios
 * distintos y activos, cada monto mayor que cero y entre los dos no más de lo
 * que se cobra. Si las reglas cambian, cambian para todos a la vez.
 */
final class PagoMixto
{
    /**
     * Las dos partes que manda la pantalla en `detalle_pagos_mixto`, ya revisadas.
     *
     * @param string|null $json  lista de {id_metodo_pago, monto}
     * @param int $tope          lo más que pueden sumar: el precio o el saldo
     * @return array{id_metodo_pago:int,id_metodo_pago2:int,monto_metodo1:int,monto_metodo2:int,monto:int}
     *
     * @throws ValidationException
     */
    public static function desdeDetalle(?string $json, int $tope, string $campo = 'detalle_pagos_mixto'): array
    {
        $detalle = json_decode((string) $json, true);

        // Tres partes no se recortan a dos en silencio: la tercera es plata que
        // entró y que no quedaría anotada en ningún lado.
        if (! is_array($detalle) || count($detalle) !== 2 || ! array_is_list($detalle)) {
            throw ValidationException::withMessages([
                $campo => 'Un pago mixto se reparte entre exactamente dos medios: indica cuáles y cuánto por cada uno.',
            ]);
        }

        [$uno, $dos] = array_map(fn ($parte) => is_array($parte) ? $parte : [], $detalle);

        return self::repartir(
            (int) ($uno['id_metodo_pago'] ?? 0),
            (int) ($dos['id_metodo_pago'] ?? 0),
            (int) round((float) ($uno['monto'] ?? 0)),
            (int) round((float) ($dos['monto'] ?? 0)),
            $tope,
            $campo,
        );
    }

    /**
     * Las reglas del reparto, sea cual sea la forma en que llegaron las partes.
     *
     * @return array{id_metodo_pago:int,id_metodo_pago2:int,monto_metodo1:int,monto_metodo2:int,monto:int}
     *
     * @throws ValidationException
     */
    public static function repartir(int $metodo1, int $metodo2, int $monto1, int $monto2, int $tope, string $campo = 'detalle_pagos_mixto'): array
    {
        if ($monto1 <= 0 || $monto2 <= 0) {
            throw ValidationException::withMessages([
                $campo => 'Cada uno de los dos medios necesita un monto mayor que cero.',
            ]);
        }

        // Dos veces el mismo medio no es un mixto: es un pago de un solo medio
        // partido en dos, y la caja de ese medio lo cuenta igual.
        if ($metodo1 === $metodo2) {
            throw ValidationException::withMessages([
                $campo => 'Los dos medios tienen que ser distintos. Si todo entra por el mismo, elige otra forma de pago.',
            ]);
        }

        // Activos: un medio dado de baja no aparece en la pantalla, así que
        // solo llega si alguien lo manda a mano o la página quedó vieja.
        $activos = MetodoPago::whereIn('id', [$metodo1, $metodo2])->where('activo', true)->count();

        if ($activos !== 2) {
            throw ValidationException::withMessages([
                $campo => 'Elige los dos medios de pago entre los que están en uso.',
            ]);
        }

        $suma = $monto1 + $monto2;

        if ($suma > $tope) {
            throw ValidationException::withMessages([
                $campo => sprintf(
                    'Los dos medios suman %s y lo que se cobra es %s.',
                    self::pesos($suma),
                    self::pesos($tope),
                ),
            ]);
        }

        return [
            'id_metodo_pago' => $metodo1,
            'id_metodo_pago2' => $metodo2,
            'monto_metodo1' => $monto1,
            'monto_metodo2' => $monto2,
            'monto' => $suma,
        ];
    }

    /**
     * Las columnas de la fila de `pagos` que guardan el reparto.
     *
     * @param array{id_metodo_pago:int,id_metodo_pago2:int,monto_metodo1:int,monto_metodo2:int} $reparto
     * @return array<string,int|string>
     */
    public static function columnas(array $reparto): array
    {
        return [
            'tipo_pago' => 'mixto',
            'id_metodo_pago' => $reparto['id_metodo_pago'],
            'id_metodo_pago2' => $reparto['id_metodo_pago2'],
            'monto_metodo1' => $reparto['monto_metodo1'],
            'monto_metodo2' => $reparto['monto_metodo2'],
        ];
    }

    /**
     * Pagado si cubre lo que se debía; si no, Parcial. «Mixto» dice por dónde
     * entró la plata, no si falta: un mixto que salda queda Pagado.
     */
    public static function estado(int $abonado, int $total): int
    {
        return $abonado >= $total ? EstadosCodigo::PAGO_PAGADO : EstadosCodigo::PAGO_PARCIAL;
    }

    private static function pesos(int $monto): string
    {
        return '$' . number_format($monto, 0, ',', '.');
    }
}
