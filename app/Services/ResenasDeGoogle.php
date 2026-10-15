<?php

namespace App\Services;

use App\Support\Ajustes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Las reseñas de la ficha del gimnasio en Google Maps, solas en la portada.
 *
 * POR LA API OFICIAL (Places API), con una llave de Google Cloud que se pega
 * en Configuración → Página web. Leer la ficha «a mano» no sirve: Google la
 * arma con JavaScript y bloquea a los servidores.
 *
 * LA FICHA SE BUSCA SOLA con el nombre y la dirección del gimnasio la primera
 * vez, y queda anotada. Google entrega la nota, cuántas reseñas hay y hasta
 * cinco reseñas («las más relevantes»): de esas se muestran las de 4 y 5
 * estrellas que traen texto. Se vuelven a pedir cada 12 horas, que son unas 60
 * consultas al mes: dentro de lo gratis de Google.
 *
 * Los testimonios cargados a mano siguen saliendo, detrás de las de Google.
 */
class ResenasDeGoogle
{
    private const HORAS_ENTRE_VUELTAS = 12;

    /** Se muestran las de esta nota o más. */
    public const NOTA_MINIMA = 4;

    public static function conectado(): bool
    {
        return (string) Ajustes::obtener('web.google_llave') !== '';
    }

    /**
     * Lo último que se trajo, o null si nunca se trajo nada.
     *
     * @return array{nombre:?string, direccion:?string, nota:?float, total:int, enlace:?string, resenas:list<array<string,mixed>>, cuando:string}|null
     */
    public static function guardadas(): ?array
    {
        return self::conectado() ? Cache::get('resenas.google') : null;
    }

    /**
     * Las que van en la portada: con texto y de buena nota, con el formato de
     * los testimonios (titulo = quién, texto = lo que dijo).
     *
     * @return list<array<string,mixed>>
     */
    public static function paraLaPortada(): array
    {
        $g = self::guardadas();

        return collect($g['resenas'] ?? [])
            ->filter(fn (array $r) => $r['nota'] >= self::NOTA_MINIMA && mb_strlen($r['texto']) >= 15)
            ->map(fn (array $r) => [
                'titulo' => $r['autor'],
                'texto' => self::recortar($r['texto']),
                'nota' => $r['nota'],
                'cuando' => $r['cuando'],
                'enlace' => $r['enlace'],
                'google' => true,
            ])
            ->values()
            ->all();
    }

    /** @return array{cuando:string, bien:bool, mensaje:string}|null */
    public static function ultimaVuelta(): ?array
    {
        return Cache::get('resenas.ultima');
    }

    /** Si ya toca mirar, mira después de responder: nadie espera a Google. */
    public static function siToca(): void
    {
        if (! self::conectado() || ! Cache::add('resenas.revisado', 1, now()->addHours(self::HORAS_ENTRE_VUELTAS))) {
            return;
        }

        app()->terminating(function () {
            try {
                self::traer();
            } catch (\Throwable) {
                // Queda anotado en ultimaVuelta(); la portada no se entera.
            }
        });
    }

    /**
     * Pide la nota y las reseñas.
     *
     * @return int cuántas reseñas van a la portada
     *
     * @throws RuntimeException con un mensaje que se le puede mostrar al dueño
     */
    public static function traer(): int
    {
        try {
            $g = self::traerDeVerdad();
            $enPortada = count(self::paraLaPortada());
            self::anotar(true, sprintf(
                '%s: %s estrellas con %d %s; %d en la portada.',
                $g['nombre'] ?? 'La ficha',
                $g['nota'] !== null ? number_format($g['nota'], 1, ',', '') : 'sin',
                $g['total'],
                $g['total'] === 1 ? 'reseña' : 'reseñas',
                $enPortada,
            ));

            return $enPortada;
        } catch (RuntimeException $e) {
            self::anotar(false, $e->getMessage());

            throw $e;
        } catch (\Throwable $e) {
            report($e);
            self::anotar(false, 'No se pudo conectar con Google.');

            throw new RuntimeException('No se pudo conectar con Google. Prueba de nuevo en un rato.');
        }
    }

    /** @return array<string,mixed> */
    private static function traerDeVerdad(): array
    {
        $llave = (string) Ajustes::obtener('web.google_llave');

        if ($llave === '') {
            throw new RuntimeException('Falta la llave de Google: se pega en Configuración → Página web.');
        }

        $lugar = self::lugar($llave);

        $respuesta = Http::timeout(15)
            ->withHeaders([
                'X-Goog-Api-Key' => $llave,
                'X-Goog-FieldMask' => 'displayName,formattedAddress,rating,userRatingCount,reviews,googleMapsUri',
            ])
            ->get('https://places.googleapis.com/v1/places/' . rawurlencode($lugar), ['languageCode' => 'es']);

        if ($respuesta->failed()) {
            if ($respuesta->status() === 404) {
                // La ficha cambió: la próxima vez se busca de nuevo.
                Cache::forget('resenas.lugar');
            }

            throw self::errorDe($respuesta->status());
        }

        $datos = [
            'nombre' => $respuesta->json('displayName.text'),
            'direccion' => $respuesta->json('formattedAddress'),
            'nota' => is_numeric($respuesta->json('rating')) ? round((float) $respuesta->json('rating'), 1) : null,
            'total' => (int) $respuesta->json('userRatingCount', 0),
            'enlace' => self::https($respuesta->json('googleMapsUri')),
            'resenas' => collect((array) $respuesta->json('reviews', []))
                ->map(fn ($r) => [
                    'autor' => trim((string) data_get($r, 'authorAttribution.displayName', '')) ?: 'Alguien en Google',
                    'nota' => (int) data_get($r, 'rating', 0),
                    // El texto tal como lo escribió; si no, la traducción de Google.
                    'texto' => trim((string) (data_get($r, 'originalText.text') ?: data_get($r, 'text.text', ''))),
                    'cuando' => (string) data_get($r, 'relativePublishTimeDescription', ''),
                    'enlace' => self::https(data_get($r, 'googleMapsUri')) ?? self::https(data_get($r, 'authorAttribution.uri')),
                ])
                ->values()
                ->all(),
            'cuando' => now()->toIso8601String(),
        ];

        Cache::forever('resenas.google', $datos);

        return $datos;
    }

    /** El id de la ficha: anotado, o se busca con el nombre y la dirección del gimnasio. */
    private static function lugar(string $llave): string
    {
        $anotado = Cache::get('resenas.lugar');

        if (is_string($anotado) && $anotado !== '') {
            return $anotado;
        }

        $busqueda = trim(implode(' ', array_filter([
            Ajustes::obtener('gimnasio.nombre'),
            Ajustes::obtener('gimnasio.direccion'),
            Ajustes::obtener('web.ciudad') ?: Ajustes::obtener('gimnasio.comuna'),
        ])));

        $respuesta = Http::timeout(15)
            ->withHeaders([
                'X-Goog-Api-Key' => $llave,
                'X-Goog-FieldMask' => 'places.id',
            ])
            ->post('https://places.googleapis.com/v1/places:searchText', [
                'textQuery' => $busqueda,
                'languageCode' => 'es',
                'pageSize' => 1,
            ]);

        if ($respuesta->failed()) {
            throw self::errorDe($respuesta->status());
        }

        $id = $respuesta->json('places.0.id');

        if (! is_string($id) || $id === '') {
            throw new RuntimeException("Google no encontró la ficha buscando «{$busqueda}». Revisa el nombre y la dirección en Configuración → Gimnasio.");
        }

        Cache::forever('resenas.lugar', $id);

        return $id;
    }

    private static function errorDe(int $estado): RuntimeException
    {
        return new RuntimeException(match (true) {
            in_array($estado, [400, 401, 403], true) => 'Google rechazó la llave: revisa que sea la correcta y que en Google Cloud esté activada «Places API (New)».',
            $estado === 429 => 'Se acabaron las consultas del día en Google. Se intenta de nuevo más tarde.',
            default => 'Google no respondió. Prueba de nuevo en un rato.',
        });
    }

    /** Solo enlaces https: van en un href. */
    private static function https(mixed $url): ?string
    {
        return is_string($url) && preg_match('#^https://#', $url) ? $url : null;
    }

    /** En la portada caben unas cuatro líneas; el resto se lee en Google. */
    private static function recortar(string $texto): string
    {
        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));

        if (mb_strlen($texto) <= 260) {
            return $texto;
        }

        $corte = mb_substr($texto, 0, 258);

        return rtrim(mb_substr($corte, 0, mb_strrpos($corte, ' ') ?: 258), ' ,;:.') . '…';
    }

    private static function anotar(bool $bien, string $mensaje): void
    {
        Cache::forever('resenas.ultima', [
            'cuando' => now()->toIso8601String(),
            'bien' => $bien,
            'mensaje' => $mensaje,
        ]);
    }
}
