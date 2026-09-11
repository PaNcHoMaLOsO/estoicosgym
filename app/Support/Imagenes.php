<?php

namespace App\Support;

/**
 * Arreglos de imágenes que se suben desde el panel.
 */
class Imagenes
{
    /**
     * Quita el margen blanco o transparente que traen muchos logos.
     *
     * Un logo de 500x500 con la marca chica al medio se veía diminuto en su
     * recuadro: el espacio lo ocupaba el blanco de alrededor, no la marca. Se
     * recorta hasta donde empieza el dibujo y se guarda en el mismo archivo,
     * en el mismo formato.
     *
     * A MANO Y NO CON imagecropauto(): esa compara contra UN color exacto, y
     * el blanco de un JPEG nunca es exacto —queda un borde de 254, 253...—.
     * Aquí cuenta como fondo lo casi blanco y lo casi transparente.
     *
     * Devuelve true si recortó algo. Sin GD, o si todo es fondo —un logo
     * blanco sobre transparente—, no toca nada.
     */
    public static function recortarBordes(string $ruta): bool
    {
        $tipo = is_file($ruta) ? (@getimagesize($ruta)[2] ?? null) : null;

        if (! function_exists('imagecreatefromstring')
            || ! in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
            return false;
        }

        $imagen = @imagecreatefromstring((string) file_get_contents($ruta));

        if (! $imagen) {
            return false;
        }

        // Una imagen de paleta no guarda el color de cada punto: se pasa a color real.
        if (! imageistruecolor($imagen)) {
            imagepalettetotruecolor($imagen);
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);

        $arriba = 0;
        while ($arriba < $alto && self::filaDeFondo($imagen, $arriba, $ancho)) {
            $arriba++;
        }

        // Todo es fondo: recortar dejaría el logo en nada.
        if ($arriba === $alto) {
            return false;
        }

        $abajo = $alto - 1;
        while ($abajo > $arriba && self::filaDeFondo($imagen, $abajo, $ancho)) {
            $abajo--;
        }

        $izquierda = 0;
        while ($izquierda < $ancho - 1 && self::columnaDeFondo($imagen, $izquierda, $arriba, $abajo)) {
            $izquierda++;
        }

        $derecha = $ancho - 1;
        while ($derecha > $izquierda && self::columnaDeFondo($imagen, $derecha, $arriba, $abajo)) {
            $derecha--;
        }

        if ($arriba === 0 && $izquierda === 0 && $abajo === $alto - 1 && $derecha === $ancho - 1) {
            return false;
        }

        $recortada = imagecrop($imagen, [
            'x' => $izquierda,
            'y' => $arriba,
            'width' => $derecha - $izquierda + 1,
            'height' => $abajo - $arriba + 1,
        ]);

        if (! $recortada) {
            return false;
        }

        imagealphablending($recortada, false);
        imagesavealpha($recortada, true);

        return match ($tipo) {
            IMAGETYPE_PNG => imagepng($recortada, $ruta, 9),
            IMAGETYPE_JPEG => imagejpeg($recortada, $ruta, 90),
            IMAGETYPE_WEBP => imagewebp($recortada, $ruta, 90),
        };
    }

    /** Casi transparente, o casi blanco. */
    private static function esFondo(int $color): bool
    {
        if ((($color >> 24) & 0x7F) > 110) {
            return true;
        }

        return (($color >> 16) & 0xFF) > 235
            && (($color >> 8) & 0xFF) > 235
            && ($color & 0xFF) > 235;
    }

    private static function filaDeFondo(\GdImage $imagen, int $y, int $ancho): bool
    {
        for ($x = 0; $x < $ancho; $x++) {
            if (! self::esFondo(imagecolorat($imagen, $x, $y))) {
                return false;
            }
        }

        return true;
    }

    private static function columnaDeFondo(\GdImage $imagen, int $x, int $desde, int $hasta): bool
    {
        for ($y = $desde; $y <= $hasta; $y++) {
            if (! self::esFondo(imagecolorat($imagen, $x, $y))) {
                return false;
            }
        }

        return true;
    }
}
