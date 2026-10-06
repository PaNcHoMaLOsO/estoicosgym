<?php

namespace App\Support;

/**
 * Reparte las fotos de un collage en columnas parejas.
 *
 * Las columnas terminan casi a la misma altura —sin una larga y otra que
 * acaba a la mitad—. El alto de cada foto sale de sus medidas; sin medidas
 * se cuenta como una vertical común.
 */
class Collage
{
    /**
     * @param  array<int, array{medidas?: array{0:int,1:int}|null}>  $fotos
     * @return list<list<array{0:int, 1:array}>>  por columna, [posición original, foto]
     */
    public static function columnas(array $fotos, int $columnas): array
    {
        $fotos = array_values($fotos);
        // El alto de cada una, en anchos de columna, más la separación.
        $alto = array_map(function (array $foto) {
            $m = $foto['medidas'] ?? null;

            return ($m && $m[0] > 0 ? $m[1] / $m[0] : 1.25) + 0.05;
        }, $fotos);

        // Primera pasada: cada una a la columna más corta, en orden.
        $donde = [];
        $suma = array_fill(0, $columnas, 0.0);
        foreach ($alto as $i => $a) {
            $k = array_keys($suma, min($suma))[0];
            $donde[$i] = $k;
            $suma[$k] += $a;
        }

        // Después se emparejan: mover una foto o cambiar dos entre columnas
        // mientras eso acorte la diferencia entre la más alta y la más baja.
        // Con una pasada sola puede quedar una columna una foto entera más
        // larga que otra.
        $rango = fn (array $s) => max($s) - min($s);
        for ($vuelta = 0; $vuelta < 200; $vuelta++) {
            $mejor = $rango($suma);
            $cambio = null;
            foreach ($alto as $i => $ai) {
                for ($k = 0; $k < $columnas; $k++) {
                    if ($k === $donde[$i]) {
                        continue;
                    }
                    $s = $suma;
                    $s[$donde[$i]] -= $ai;
                    $s[$k] += $ai;
                    if ($rango($s) < $mejor - 1e-9) {
                        [$mejor, $cambio] = [$rango($s), [[$i, $k]]];
                    }
                    foreach ($alto as $j => $aj) {
                        if ($donde[$j] !== $k) {
                            continue;
                        }
                        $s = $suma;
                        $s[$donde[$i]] += $aj - $ai;
                        $s[$k] += $ai - $aj;
                        if ($rango($s) < $mejor - 1e-9) {
                            [$mejor, $cambio] = [$rango($s), [[$i, $k], [$j, $donde[$i]]]];
                        }
                    }
                }
            }
            if ($cambio === null) {
                break;
            }
            foreach ($cambio as [$i, $k]) {
                $suma[$donde[$i]] -= $alto[$i];
                $suma[$k] += $alto[$i];
                $donde[$i] = $k;
            }
        }

        // Dentro de cada columna, en el orden de la lista.
        $grupos = array_fill(0, $columnas, []);
        foreach ($fotos as $i => $foto) {
            $grupos[$donde[$i]][] = [$i, $foto];
        }

        return $grupos;
    }
}
