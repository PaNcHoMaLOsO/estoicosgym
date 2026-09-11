<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Las tareas automáticas: a qué hora corren y si de verdad están corriendo.
 *
 * Todo lo de routes/console.php depende de que Windows ejecute
 * `php artisan schedule:run` cada minuto. Si nadie registró esa tarea el
 * sistema no se entera: los vencimientos no se marcan y los avisos no salen,
 * sin un solo error a la vista. Así estuvo este gimnasio desde el principio.
 *
 * Por eso cada vuelta deja un LATIDO, cada tarea anota cuándo terminó bien, y
 * Configuración dice las dos cosas en vez de suponerlas.
 */
class Programador
{
    private const LATIDO = 'programador:latido';

    /** Las tareas que se muestran, con el ajuste que decide su hora. */
    public const TAREAS = [
        'revision' => ['nombre' => 'Revisar vencimientos y pagos', 'ajuste' => 'tareas.hora_revision'],
        'avisos' => ['nombre' => 'Mandar los avisos por correo', 'ajuste' => 'tareas.hora_avisos'],
        'reintento' => ['nombre' => 'Reintentar los correos que fallaron', 'ajuste' => 'tareas.hora_reintento'],
    ];

    public static function latir(): void
    {
        Cache::forever(self::LATIDO, now()->toIso8601String());
    }

    public static function registrar(string $tarea): void
    {
        Cache::forever("programador:tarea:{$tarea}", now()->toIso8601String());
    }

    public static function ultimoLatido(): ?Carbon
    {
        return self::fecha(Cache::get(self::LATIDO));
    }

    public static function ultimaVez(string $tarea): ?Carbon
    {
        return self::fecha(Cache::get("programador:tarea:{$tarea}"));
    }

    /**
     * ¿Está corriendo?
     *
     * El latido es de cada minuto: si el último tiene más de diez, Windows dejó
     * de llamar —o nunca llamó—.
     */
    public static function corriendo(): bool
    {
        $ultimo = self::ultimoLatido();

        return $ultimo !== null && $ultimo->greaterThan(now()->subMinutes(10));
    }

    /**
     * La hora de un ajuste lista para dailyAt(), corrida unos minutos si hace
     * falta ir en orden detrás de otra tarea.
     *
     * Con `rescue` porque routes/console.php se carga en CUALQUIER comando
     * —también en `migrate` con la base vacía—, y ahí la tabla de ajustes
     * todavía no existe. Sin él, instalar el sistema desde cero fallaría.
     */
    public static function hora(string $ajuste, int $masMinutos = 0): string
    {
        $defecto = (string) Ajustes::definiciones()[$ajuste]['defecto'];
        $hora = (string) rescue(fn () => Ajustes::obtener($ajuste), $defecto, false);

        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            $hora = $defecto;
        }

        return $masMinutos === 0
            ? $hora
            : Carbon::createFromFormat('H:i', $hora)->addMinutes($masMinutos)->format('H:i');
    }

    /**
     * Lo que se pinta en Configuración.
     *
     * @return array<string,mixed>
     */
    public static function estado(): array
    {
        return [
            'corriendo' => self::corriendo(),
            'ultimo_latido' => self::ultimoLatido()?->toIso8601String(),
            'tareas' => collect(self::TAREAS)
                ->map(fn (array $tarea, string $clave) => [
                    'clave' => $clave,
                    'nombre' => $tarea['nombre'],
                    'hora' => self::hora($tarea['ajuste']),
                    'ultima' => self::ultimaVez($clave)?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'comando' => self::comandoParaWindows(),
        ];
    }

    /**
     * La orden que registra la tarea en Windows, con las rutas de ESTE equipo.
     *
     * php-win.exe y no php.exe: es el mismo PHP, pero sin abrir una ventana
     * negra cada minuto delante de quien atiende el mesón.
     */
    public static function comandoParaWindows(): string
    {
        $php = PHP_BINARY;

        if (preg_match('/php(-cgi)?\.exe$/i', $php)) {
            $sinVentana = dirname($php) . DIRECTORY_SEPARATOR . 'php-win.exe';

            if (is_file($sinVentana)) {
                $php = $sinVentana;
            }
        } elseif (! preg_match('/php-win\.exe$/i', $php)) {
            // Detrás de Apache PHP_BINARY no es el PHP de consola: se deja el
            // nombre a secas y que lo encuentre Windows.
            $php = 'php';
        }

        return 'schtasks /Create /TN "PRO GYM - tareas automaticas" /SC MINUTE /MO 1 /TR "\"'
            . $php . '\" \"' . base_path('artisan') . '\" schedule:run" /F';
    }

    private static function fecha(mixed $valor): ?Carbon
    {
        if (! is_string($valor) || $valor === '') {
            return null;
        }

        return rescue(fn () => Carbon::parse($valor), null, false);
    }
}
