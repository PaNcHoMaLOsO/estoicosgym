<?php

namespace Tests\Unit;

use App\Support\Collage;
use PHPUnit\Framework\TestCase;

class CollageTest extends TestCase
{
    /** Las columnas terminan casi parejas, y no se pierde ni se repite ninguna foto. */
    public function test_reparte_parejo_y_sin_perder_fotos(): void
    {
        $medidas = [[1600, 900], [1600, 900], [1200, 1600], [1280, 1600], [900, 1600], [900, 1600], [1280, 1600],
            [900, 1600], [1280, 1600], [900, 1600], [1600, 1143], [1600, 900], [1600, 900]];
        $fotos = array_map(fn ($m) => ['medidas' => $m], $medidas);

        foreach ([2, 3, 4] as $n) {
            $columnas = Collage::columnas($fotos, $n);
            $this->assertCount($n, $columnas);

            $posiciones = array_merge(...array_map(fn ($c) => array_column($c, 0), $columnas));
            sort($posiciones);
            $this->assertSame(range(0, count($fotos) - 1), $posiciones);

            $altos = array_map(fn ($c) => array_sum(array_map(fn ($f) => $f[1]['medidas'][1] / $f[1]['medidas'][0], $c)), $columnas);
            // Nunca más de media foto vertical de diferencia.
            $this->assertLessThan(0.9, max($altos) - min($altos), "Con {$n} columnas");

            // Dentro de cada columna, en el orden de la lista.
            foreach ($columnas as $c) {
                $orden = array_column($c, 0);
                $ordenado = $orden;
                sort($ordenado);
                $this->assertSame($ordenado, $orden);
            }
        }
    }

    public function test_sin_fotos_o_sin_medidas_no_falla(): void
    {
        $this->assertSame([[], [], []], Collage::columnas([], 3));
        $this->assertCount(2, array_merge(...Collage::columnas([['medidas' => null], []], 2)));
    }
}
