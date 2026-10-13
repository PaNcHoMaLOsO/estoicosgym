<?php

namespace App\Services;

use App\Models\ContenidoWeb;
use App\Support\Ajustes;
use App\Support\FotoLiviana;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Trae solas las últimas publicaciones del Instagram del gimnasio.
 *
 * POR LA API OFICIAL, con la llave de la cuenta que se pega en Configuración →
 * Página web. Leer el perfil público «a mano» no sirve: Instagram le pone un
 * muro de inicio de sesión a los servidores y funciona un día sí y otro no.
 *
 * LA FOTO SE COPIA AQUÍ: las direcciones que entrega Instagram vencen a los
 * pocos días, y una portada con seis recuadros rotos es peor que una sin
 * Instagram.
 *
 * LO QUE YA ESTÁ NO SE TOCA: si alguien corrigió el texto de una publicación u
 * ocultó una, la siguiente vuelta no se lo deshace. Solo entran las nuevas, y
 * quedan primeras.
 */
class InstagramDelGimnasio
{
    /** Cada cuánto mira si hay algo nuevo. */
    private const HORAS_ENTRE_VUELTAS = 3;

    /** La llave dura 60 días y se renueva sola: con una vuelta por semana nunca vence. */
    private const DIAS_PARA_RENOVAR = 7;

    public static function conectado(): bool
    {
        return (string) Ajustes::obtener('web.instagram_token') !== '';
    }

    /**
     * La última vuelta: cuándo fue y cómo salió.
     *
     * @return array{cuando:string, bien:bool, mensaje:string}|null
     */
    public static function ultimaVuelta(): ?array
    {
        return Cache::get('instagram.ultima');
    }

    /**
     * Si ya toca mirar, mira —después de responder, para que nadie espere—.
     *
     * Se llama desde la portada: así se actualiza aunque el programador del
     * computador esté apagado. Una vez cada tres horas, entre todos los que
     * entren.
     */
    public static function siToca(): void
    {
        if (! self::conectado() || ! Cache::add('instagram.revisado', 1, now()->addHours(self::HORAS_ENTRE_VUELTAS))) {
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
     * Trae las publicaciones nuevas.
     *
     * @return int cuántas entraron
     *
     * @throws RuntimeException con un mensaje que se le puede mostrar al dueño
     */
    public static function traer(): int
    {
        try {
            $nuevas = self::traerDeVerdad();
            self::anotar(true, $nuevas === 0
                ? 'Sin publicaciones nuevas.'
                : ($nuevas === 1 ? 'Entró 1 publicación nueva.' : "Entraron {$nuevas} publicaciones nuevas."));

            return $nuevas;
        } catch (RuntimeException $e) {
            self::anotar(false, $e->getMessage());

            throw $e;
        } catch (\Throwable $e) {
            report($e);
            self::anotar(false, 'No se pudo conectar con Instagram.');

            throw new RuntimeException('No se pudo conectar con Instagram. Prueba de nuevo en un rato.');
        }
    }

    private static function traerDeVerdad(): int
    {
        $llave = (string) Ajustes::obtener('web.instagram_token');

        if ($llave === '') {
            throw new RuntimeException('Falta la llave de Instagram: se pega en Configuración → Página web.');
        }

        $respuesta = Http::timeout(15)->get('https://graph.instagram.com/me/media', [
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp',
            'limit' => ContenidoWeb::PUBLICACIONES_EN_PORTADA,
            'access_token' => $llave,
        ]);

        if ($respuesta->failed()) {
            throw new RuntimeException($respuesta->status() === 400 || $respuesta->status() === 401
                ? 'Instagram rechazó la llave: venció o se quitó el permiso. Hay que sacar una nueva y pegarla en Configuración → Página web.'
                : 'Instagram no respondió. Prueba de nuevo en un rato.');
        }

        $descartadas = (array) Cache::get('instagram.descartadas', []);
        $enOrden = [];
        $nuevas = 0;

        foreach ((array) $respuesta->json('data', []) as $publicacion) {
            $enlace = ContenidoWeb::enlaceDeInstagram((string) ($publicacion['permalink'] ?? ''));

            // Las que el dueño eliminó a mano no vuelven a entrar solas.
            if ($enlace === null || in_array($enlace, $descartadas, true)) {
                continue;
            }

            $fila = ContenidoWeb::where('tipo', 'instagram')->where('enlace', $enlace)->first();

            if (! $fila) {
                $foto = self::copiarFoto($publicacion);

                if ($foto === null) {
                    continue;
                }

                $fila = ContenidoWeb::create([
                    'tipo' => 'instagram',
                    'titulo' => self::titulo($publicacion),
                    'imagen' => $foto,
                    'enlace' => $enlace,
                    'orden' => 0,
                    'activo' => true,
                ]);
                $nuevas++;
            }

            $enOrden[] = $fila->id;
        }

        self::ordenar($enOrden);
        self::renovarLlave($llave);

        return $nuevas;
    }

    /** Las de Instagram en su orden —la más nueva primero— y detrás las que se cargaron a mano. */
    private static function ordenar(array $enOrden): void
    {
        $puesto = 1;

        foreach ($enOrden as $id) {
            ContenidoWeb::whereKey($id)->update(['orden' => $puesto++]);
        }

        ContenidoWeb::where('tipo', 'instagram')
            ->whereNotIn('id', $enOrden)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->each(function (ContenidoWeb $fila) use (&$puesto) {
                $fila->update(['orden' => $puesto++]);
            });
    }

    /** La foto de la publicación (o la portada del video), achicada y guardada aquí. */
    private static function copiarFoto(array $publicacion): ?string
    {
        $direccion = ($publicacion['media_type'] ?? '') === 'VIDEO'
            ? ($publicacion['thumbnail_url'] ?? null)
            : ($publicacion['media_url'] ?? null);

        if (! is_string($direccion) || ! preg_match('#^https://#', $direccion)) {
            return null;
        }

        $descarga = Http::timeout(20)->get($direccion);
        $bytes = $descarga->successful() ? $descarga->body() : '';

        // Tiene que ser una imagen de verdad: se guarda en una carpeta pública.
        if ($bytes === '' || @getimagesizefromstring($bytes) === false) {
            return null;
        }

        $liviana = FotoLiviana::desde($bytes, 1200, 84);
        $ruta = 'web/instagram-' . preg_replace('/\D/', '', (string) ($publicacion['id'] ?? '')) . '-' . substr(md5($direccion), 0, 8);

        if ($liviana) {
            $ruta .= '.' . $liviana['extension'];
            Storage::disk('public')->put($ruta, $liviana['bytes']);
        } else {
            $ruta .= '.jpg';
            Storage::disk('public')->put($ruta, $bytes);
        }

        return $ruta;
    }

    /**
     * La primera frase del texto, limpia: sin emojis ni etiquetas.
     *
     * Bajo la foto caben dos líneas; el texto entero, con sus veinte hashtags,
     * se lee en Instagram.
     */
    public static function titulo(array $publicacion): string
    {
        $primera = strtok(trim((string) ($publicacion['caption'] ?? '')), "\r\n") ?: '';
        $primera = preg_replace('/[#@][\p{L}\p{N}_.]+/u', '', $primera);
        $primera = preg_replace('/[\p{Extended_Pictographic}\x{FE0F}\x{200D}\x{20E3}\x{1F1E6}-\x{1F1FF}\x{1F3FB}-\x{1F3FF}]/u', '', (string) $primera);
        $primera = trim((string) preg_replace('/\s+/u', ' ', (string) $primera), " \t-–—·|:");

        if (mb_strlen($primera) > 150) {
            $primera = rtrim(mb_substr($primera, 0, mb_strrpos(mb_substr($primera, 0, 148), ' ') ?: 148), ' ,;:') . '…';
        }

        if (mb_strlen($primera) >= 3) {
            return $primera;
        }

        $fecha = isset($publicacion['timestamp']) ? Carbon::parse($publicacion['timestamp']) : now();

        return 'Publicación del ' . $fecha->locale('es')->isoFormat('D [de] MMMM');
    }

    /** La llave vence a los 60 días si nadie la renueva: se renueva sola una vez por semana. */
    private static function renovarLlave(string $llave): void
    {
        if (! Cache::add('instagram.llave_renovada', 1, now()->addDays(self::DIAS_PARA_RENOVAR))) {
            return;
        }

        try {
            $nueva = Http::timeout(15)->get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $llave,
            ])->json('access_token');

            if (is_string($nueva) && $nueva !== '') {
                Ajustes::guardar(['web.instagram_token' => $nueva]);
            } else {
                Cache::forget('instagram.llave_renovada');
            }
        } catch (\Throwable) {
            // Se intenta en la próxima vuelta: la de ahora sigue sirviendo.
            Cache::forget('instagram.llave_renovada');
        }
    }

    /** Una publicación eliminada a mano no vuelve a entrar sola. */
    public static function descartar(?string $enlace): void
    {
        if ($enlace) {
            $lista = (array) Cache::get('instagram.descartadas', []);
            $lista[] = $enlace;
            Cache::forever('instagram.descartadas', array_slice(array_values(array_unique($lista)), -200));
        }
    }

    private static function anotar(bool $bien, string $mensaje): void
    {
        Cache::forever('instagram.ultima', [
            'cuando' => now()->toIso8601String(),
            'bien' => $bien,
            'mensaje' => $mensaje,
        ]);
    }
}
