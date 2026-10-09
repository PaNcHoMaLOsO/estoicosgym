<?php

namespace App\Support;

/**
 * La imagen que sale al pegar un enlace de la web en WhatsApp o Facebook.
 *
 * Las fotos de la web se guardan en WebP (FotoLiviana), que los navegadores
 * abren sin problema pero no todas las apps al armar la vista previa de un
 * enlace: el enlace podía salir sin foto. Para eso se usa una copia en JPG,
 * hecha una sola vez y guardada al lado (storage/app/public/compartir). Si no
 * se puede hacer, va la original.
 */
final class ImagenParaCompartir
{
    public static function de(string $url): string
    {
        $prefijo = rtrim(asset('storage'), '/') . '/';

        if (! str_starts_with($url, $prefijo) || ! str_ends_with(strtolower($url), '.webp') || ! function_exists('imagecreatefromwebp')) {
            return $url;
        }

        $relativa = substr($url, strlen($prefijo));
        $origen = storage_path('app/public/' . $relativa);

        // Nada que suba por la dirección: solo archivos de verdad de storage.
        if (str_contains($relativa, '..') || ! is_file($origen)) {
            return $url;
        }

        $nombre = 'compartir/' . md5($relativa . '|' . filemtime($origen)) . '.jpg';
        $destino = storage_path('app/public/' . $nombre);

        if (! is_file($destino)) {
            $imagen = @imagecreatefromwebp($origen);

            if (! $imagen) {
                return $url;
            }

            @mkdir(dirname($destino), 0755, true);
            $listo = @imagejpeg($imagen, $destino, 85);
            imagedestroy($imagen);

            if (! $listo) {
                return $url;
            }
        }

        return asset('storage/' . $nombre);
    }
}
