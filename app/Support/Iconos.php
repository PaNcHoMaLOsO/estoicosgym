<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Los íconos de la web pública, dibujados en la misma página.
 *
 * Antes venía Font Awesome entero desde su CDN: unos 290 KB de hoja y fuentes
 * que frenaban el primer dibujo de cada página para usar unos cuarenta
 * íconos. Ahora cada ícono es un SVG copiado del paquete de Font Awesome Free
 * (resources/svg/iconos; íconos con licencia CC BY 4.0, el aviso va dentro de
 * cada archivo) y se escribe en línea: sin pedir nada a nadie.
 *
 * Se usa desde Blade con <x-icono nombre="whatsapp" class="text-xl" />. El
 * tamaño lo da la letra, igual que antes: `text-xl` agranda el ícono.
 */
final class Iconos
{
    /**
     * Los nombres viejos de Font Awesome que siguen escritos en la base de
     * datos (los íconos de los servicios) o en las vistas, y cómo se llama
     * hoy el archivo.
     */
    private const OTROS_NOMBRES = [
        'check-circle' => 'circle-check',
        'exclamation-circle' => 'circle-exclamation',
        'times' => 'xmark',
        'map-marker-alt' => 'location-dot',
        'phone-alt' => 'phone',
        'search' => 'magnifying-glass',
        'mobile-alt' => 'mobile-screen-button',
        'fist-raised' => 'hand-fist',
        'user-friends' => 'user-group',
        'heartbeat' => 'heart-pulse',
        'running' => 'person-running',
        'apple-alt' => 'apple-whole',
        'parking' => 'square-parking',
        'home' => 'house',
    ];

    /** @var array<string, array{0:int,1:int,2:string}|null> */
    private static array $leidos = [];

    /**
     * El SVG de un ícono, listo para escribir en la página.
     *
     * Acepta también la forma de antes («fab fa-instagram»). Un ícono que no
     * existe no rompe la página: no se dibuja nada.
     */
    public static function svg(string $nombre, string $clase = ''): HtmlString
    {
        $nombre = self::nombre($nombre);
        $datos = self::$leidos[$nombre] ??= self::leer($nombre);

        if ($datos === null) {
            return new HtmlString('');
        }

        [$ancho, $alto, $trazos] = $datos;
        $proporcion = rtrim(rtrim(number_format($ancho / $alto, 4, '.', ''), '0'), '.');

        return new HtmlString(
            '<svg class="icono' . ($clase !== '' ? ' ' . e($clase) : '') . '" xmlns="http://www.w3.org/2000/svg"'
            . ' viewBox="0 0 ' . $ancho . ' ' . $alto . '" width="' . $proporcion . 'em" height="1em"'
            . ' fill="currentColor" aria-hidden="true" focusable="false">' . $trazos . '</svg>'
        );
    }

    public static function existe(string $nombre): bool
    {
        $nombre = self::nombre($nombre);

        return (self::$leidos[$nombre] ??= self::leer($nombre)) !== null;
    }

    private static function nombre(string $nombre): string
    {
        $nombre = (string) preg_replace('/^(?:fa[bsr]?\s+)?fa-/', '', trim($nombre));

        return self::OTROS_NOMBRES[$nombre] ?? $nombre;
    }

    /** @return array{0:int,1:int,2:string}|null */
    private static function leer(string $nombre): ?array
    {
        if (! preg_match('/^[a-z0-9-]+$/', $nombre)) {
            return null;
        }

        $ruta = resource_path("svg/iconos/{$nombre}.svg");
        $svg = is_file($ruta) ? (string) file_get_contents($ruta) : '';

        if (! preg_match('/viewBox="0 0 (\d+) (\d+)"/', $svg, $caja)
            || ! preg_match_all('#<path\b[^>]*/>#', $svg, $trazos)) {
            return null;
        }

        return [(int) $caja[1], (int) $caja[2], implode('', $trazos[0])];
    }
}
