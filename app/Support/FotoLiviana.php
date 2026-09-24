<?php

namespace App\Support;

/**
 * Una foto lista para la web: derecha, del tamaño justo y liviana.
 *
 * LAS FOTOS PESABAN 400 KB CADA UNA. Se achicaban solo a lo ancho, y las del
 * celular son verticales: una de 1152 × 2048 pasaba entera. Y se guardaban en
 * JPG. La galería de El gimnasio eran más de 3 MB, que en un celular con datos
 * es la diferencia entre que la página cargue o que la cierren.
 *
 *  · Se achica por el lado MÁS LARGO, sea ancha o alta.
 *  · Se guarda en WebP, que a la misma calidad pesa bastante menos que el JPG
 *    y lo abren todos los navegadores de hoy. Si el PHP no sabe hacer WebP,
 *    JPG progresivo, que carga de a poco en vez de a franjas.
 *  · Se endereza: el teléfono guarda la foto de lado y apunta en un dato
 *    aparte hacia dónde girarla, y ese dato se pierde al procesarla.
 *  · La transparencia se respeta: un logo en PNG no queda con fondo negro.
 */
class FotoLiviana
{
    /**
     * @return array{bytes:string, extension:string}|null  null si no es una imagen que GD sepa leer
     */
    public static function desde(string $contenido, int $maximo = 1600, int $calidad = 80, ?string $rutaOriginal = null): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $imagen = @imagecreatefromstring($contenido);

        if (! $imagen) {
            return null;
        }

        if ($rutaOriginal && function_exists('exif_read_data')) {
            $exif = @exif_read_data($rutaOriginal);
            $giro = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($giro !== 0) {
                $imagen = imagerotate($imagen, $giro, 0);
            }
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $lado = max($ancho, $alto);

        if ($lado > $maximo) {
            $escala = $maximo / $lado;
            $nuevoAncho = max(1, (int) round($ancho * $escala));
            $nuevoAlto = max(1, (int) round($alto * $escala));

            $chica = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            imagealphablending($chica, false);
            imagesavealpha($chica, true);
            imagecopyresampled($chica, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            imagedestroy($imagen);
            $imagen = $chica;
        } else {
            imagealphablending($imagen, false);
            imagesavealpha($imagen, true);
        }

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($imagen, null, $calidad);
            $extension = 'webp';
        } else {
            imageinterlace($imagen, true);
            imagejpeg($imagen, null, $calidad);
            $extension = 'jpg';
        }

        $bytes = (string) ob_get_clean();
        imagedestroy($imagen);

        return ['bytes' => $bytes, 'extension' => $extension];
    }
}
