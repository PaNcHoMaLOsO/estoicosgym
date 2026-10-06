<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * El ancho y el alto de una imagen de la web, para escribirlos en la página.
 *
 * Con width y height el navegador guarda el hueco de la foto antes de que
 * llegue, y la página no salta mientras carga (Google lo mide: CLS). Y al
 * compartir, WhatsApp y Facebook arman la vista previa sin tener que bajar
 * la foto para medirla.
 *
 * Se leen del archivo y se recuerdan: la llave lleva la fecha del archivo,
 * así que una foto reemplazada con el mismo nombre se vuelve a medir.
 */
final class MedidasDeImagen
{
    private const CACHE = 'medidas-de-imagenes';

    /** @return array{0:int,1:int}|null */
    public static function de(?string $url): ?array
    {
        $ruta = self::rutaLocal($url);

        if ($ruta === null || ! is_file($ruta)) {
            return null;
        }

        /*
         * TODAS EN UNA SOLA LLAVE. Cada foto tenía su propia llave, y como el
         * caché vive en la base, la página de clases hacía diecisiete consultas
         * solo para leer medidas. Ahora es una lectura por petición (memo()),
         * por archivo y con su fecha: una foto reemplazada se vuelve a medir.
         */
        $fecha = filemtime($ruta);
        $todas = Cache::memo()->get(self::CACHE, []);

        if (isset($todas[$ruta]) && $todas[$ruta]['fecha'] === $fecha) {
            return $todas[$ruta]['medidas'];
        }

        $medidas = @getimagesize($ruta);
        $medidas = $medidas && $medidas[0] > 0 && $medidas[1] > 0 ? [(int) $medidas[0], (int) $medidas[1]] : null;

        $todas[$ruta] = ['fecha' => $fecha, 'medidas' => $medidas];
        Cache::memo()->forever(self::CACHE, $todas);

        return $medidas;
    }

    /** El archivo en disco de una dirección de la propia web, o null. */
    private static function rutaLocal(?string $url): ?string
    {
        $camino = rawurldecode((string) parse_url((string) $url, PHP_URL_PATH));

        if ($camino === '' || str_contains($camino, '..')) {
            return null;
        }

        if (str_starts_with($camino, '/storage/')) {
            return \Illuminate\Support\Facades\Storage::disk('public')->path(substr($camino, strlen('/storage/')));
        }

        return public_path(ltrim($camino, '/'));
    }
}
