<?php

namespace App\Support;

use App\Models\Falla;
use Illuminate\Log\Events\MessageLogged;
use Throwable;

/**
 * Anota las fallas del sistema en la base, para verlas desde el panel.
 *
 * ESCUCHA TODO LO QUE SE REGISTRA COMO ERROR: las excepciones que Laravel
 * informa y los Log::error que el código ya escribe —un correo que no salió,
 * la revisión del día que falló—. Así no hay que acordarse de avisar en cada
 * sitio: lo que ya iba al archivo de registro llega también aquí.
 *
 * La misma falla repetida es UNA fila con su contador: se agrupa por una
 * huella hecha del tipo, el lugar y el mensaje sin números. Si una falla que
 * se dio por resuelta vuelve a pasar, se reabre sola.
 *
 * Nunca revienta: si la base no responde, la falla se queda solo en el archivo
 * de siempre. Y no guarda claves: lo que se llame clave, contraseña, token o
 * similar se reemplaza antes de guardar.
 */
class RegistroDeFallas
{
    private const NIVELES = ['error', 'critical', 'alert', 'emergency'];

    private const SENSIBLE = '/pass|clave|contrase|token|secret|api[_-]?key|authorization|cookie|smtp/i';

    /** Anotando: si anotar falla y eso se registra, no se vuelve a entrar. */
    private static bool $anotando = false;

    public static function delLog(MessageLogged $evento): void
    {
        if (! in_array($evento->level, self::NIVELES, true)) {
            return;
        }

        $excepcion = $evento->context['exception'] ?? null;
        $contexto = $evento->context;
        unset($contexto['exception']);

        if ($excepcion instanceof Throwable) {
            self::guardar([
                'origen' => 'servidor',
                'nivel' => $evento->level,
                'tipo' => get_class($excepcion),
                'mensaje' => $excepcion->getMessage() ?: $evento->message,
                'lugar' => self::ruta($excepcion->getFile()) . ':' . $excepcion->getLine(),
                'traza' => self::traza($excepcion),
                'contexto' => $contexto,
            ]);

            return;
        }

        // Un Log::error escrito a mano: el lugar es quien lo escribió.
        $quien = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 40))
            ->first(fn ($paso) => isset($paso['file'])
                && str_contains(str_replace('\\', '/', $paso['file']), '/app/')
                && ! str_contains($paso['file'], 'RegistroDeFallas'));

        self::guardar([
            'origen' => 'servidor',
            'nivel' => $evento->level,
            'tipo' => null,
            'mensaje' => $evento->message,
            'lugar' => $quien ? self::ruta($quien['file']) . ':' . $quien['line'] : null,
            'traza' => null,
            'contexto' => $contexto,
        ]);
    }

    /** Una falla del panel en el navegador: una pantalla que se rompió. */
    public static function delNavegador(array $datos): void
    {
        self::guardar([
            'origen' => 'navegador',
            'nivel' => 'error',
            'tipo' => mb_substr((string) ($datos['tipo'] ?? 'Error'), 0, 200),
            'mensaje' => mb_substr((string) ($datos['mensaje'] ?? ''), 0, 2000),
            'lugar' => isset($datos['archivo']) ? mb_substr(self::sinDominio((string) $datos['archivo']) . ':' . (int) ($datos['linea'] ?? 0), 0, 300) : null,
            'traza' => isset($datos['traza']) ? mb_substr((string) $datos['traza'], 0, 8000) : null,
            'contexto' => array_filter([
                'pantalla' => isset($datos['pantalla']) ? mb_substr(self::sinDominio((string) $datos['pantalla']), 0, 300) : null,
                'navegador' => mb_substr((string) request()->userAgent(), 0, 300),
            ]),
            // La dirección es la de la pantalla que falló, no la de este aviso.
            'url' => isset($datos['pantalla']) ? mb_substr(self::sinDominio((string) $datos['pantalla']), 0, 500) : null,
            'metodo' => 'GET',
        ]);
    }

    /** Borra las fallas que no se repiten hace más de 90 días. */
    public static function limpiar(int $dias = 90): int
    {
        return Falla::where('ultima_vez', '<', now()->subDays($dias))->delete();
    }

    private static function guardar(array $datos): void
    {
        if (self::$anotando) {
            return;
        }

        self::$anotando = true;

        try {
            $peticion = app()->runningInConsole() && ! app()->runningUnitTests() ? null : request();
            $mensaje = mb_substr(trim((string) $datos['mensaje']), 0, 4000) ?: '(sin mensaje)';

            $datos['url'] ??= $peticion ? mb_substr('/' . ltrim($peticion->path(), '/'), 0, 500) : 'consola';
            $datos['metodo'] ??= $peticion?->method();
            $datos['contexto'] = self::sinSecretos($datos['contexto'] ?? []) ?: null;

            $huella = hash('sha256', implode('|', [
                $datos['origen'],
                $datos['tipo'] ?? '',
                $datos['lugar'] ?? '',
                self::sinNumeros($mensaje),
            ]));

            $ahora = now();
            $falla = Falla::where('huella', $huella)->first();
            $usuario = auth()->id();

            if ($falla) {
                $falla->update([
                    'veces' => $falla->veces + 1,
                    'ultima_vez' => $ahora,
                    // Se dio por resuelta y volvió: se reabre.
                    'resuelta_en' => null,
                    'mensaje' => $mensaje,
                    'url' => $datos['url'],
                    'metodo' => $datos['metodo'],
                    'id_usuario' => $usuario,
                    'traza' => $datos['traza'] ?? $falla->traza,
                    'contexto' => $datos['contexto'],
                ]);
            } else {
                Falla::create($datos + [
                    'huella' => $huella,
                    'mensaje' => $mensaje,
                    'id_usuario' => $usuario,
                    'veces' => 1,
                    'primera_vez' => $ahora,
                    'ultima_vez' => $ahora,
                ]);
            }
        } catch (Throwable) {
            // Sin base no hay registro en el panel: queda el del archivo.
        } finally {
            self::$anotando = false;
        }
    }

    /** «Pago 1532 de 20000» y «Pago 88 de 5000» son la misma falla. */
    private static function sinNumeros(string $mensaje): string
    {
        $mensaje = preg_replace('/[\w.+-]+@[\w-]+\.[\w.]+/', '@', $mensaje);
        $mensaje = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', 'uuid', $mensaje);

        return mb_substr(preg_replace('/\d+/', '#', $mensaje), 0, 500);
    }

    private static function sinSecretos(mixed $valor, int $profundidad = 0): mixed
    {
        if (! is_array($valor)) {
            if (is_object($valor)) {
                return method_exists($valor, '__toString') ? mb_substr((string) $valor, 0, 500) : get_class($valor);
            }

            return is_string($valor) ? mb_substr($valor, 0, 1000) : $valor;
        }

        if ($profundidad > 3) {
            return '(…)';
        }

        $limpio = [];

        foreach (array_slice($valor, 0, 30, true) as $clave => $dato) {
            $limpio[$clave] = is_string($clave) && preg_match(self::SENSIBLE, $clave)
                ? '(oculto)'
                : self::sinSecretos($dato, $profundidad + 1);
        }

        return $limpio;
    }

    private static function traza(Throwable $e): string
    {
        return mb_substr(str_replace(
            [str_replace('\\', '/', base_path()) . '/', base_path() . DIRECTORY_SEPARATOR],
            '',
            str_replace('\\', '/', $e->getTraceAsString())
        ), 0, 8000);
    }

    private static function ruta(string $archivo): string
    {
        $archivo = str_replace('\\', '/', $archivo);
        $base = str_replace('\\', '/', base_path()) . '/';

        return str_starts_with($archivo, $base) ? substr($archivo, strlen($base)) : $archivo;
    }

    /** «https://…/build/assets/app.js» → «/build/assets/app.js». */
    private static function sinDominio(string $url): string
    {
        $partes = parse_url($url);

        return ($partes['path'] ?? $url) . (isset($partes['query']) ? '?' . $partes['query'] : '');
    }
}
