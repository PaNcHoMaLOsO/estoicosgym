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

    /**
     * Achica un logo en su mismo archivo y formato, sin perder la transparencia.
     *
     * UN LOGO SE VE A 150 PÍXELES Y SE SUBE A 3.000: el de un convenio pesaba
     * 728 KB para ocupar el alto de un dedo en la cinta de la portada, y con
     * eso solo la portada doblaba su peso en un teléfono. Se deja en $maximo
     * por su lado más largo, que sobra para una pantalla de doble densidad.
     *
     * En el MISMO archivo: nadie tiene que cambiar a dónde apunta, y las fotos
     * que viajan en el repositorio siguen llamándose igual.
     *
     * Solo se guarda si de verdad pesa menos. Devuelve true si lo cambió.
     */
    public static function achicar(string $ruta, int $maximo = 480): bool
    {
        $medidas = is_file($ruta) ? @getimagesize($ruta) : false;
        $tipo = $medidas[2] ?? null;

        if (! $medidas || ! function_exists('imagecreatefromstring')
            || ! in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)
            || max($medidas[0], $medidas[1]) <= $maximo) {
            return false;
        }

        $original = (string) file_get_contents($ruta);
        $imagen = @imagecreatefromstring($original);

        if (! $imagen) {
            return false;
        }

        if (! imageistruecolor($imagen)) {
            imagepalettetotruecolor($imagen);
        }

        $escala = $maximo / max($medidas[0], $medidas[1]);
        $ancho = max(1, (int) round($medidas[0] * $escala));
        $alto = max(1, (int) round($medidas[1] * $escala));

        $chica = imagecreatetruecolor($ancho, $alto);
        imagealphablending($chica, false);
        imagesavealpha($chica, true);
        imagefill($chica, 0, 0, imagecolorallocatealpha($chica, 0, 0, 0, 127));
        imagecopyresampled($chica, $imagen, 0, 0, 0, 0, $ancho, $alto, $medidas[0], $medidas[1]);

        ob_start();
        match ($tipo) {
            IMAGETYPE_PNG => imagepng($chica, null, 9),
            IMAGETYPE_JPEG => imagejpeg($chica, null, 88),
            IMAGETYPE_WEBP => imagewebp($chica, null, 90),
        };
        $bytes = (string) ob_get_clean();

        if ($bytes === '' || strlen($bytes) >= strlen($original)) {
            return false;
        }

        return file_put_contents($ruta, $bytes) !== false;
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
