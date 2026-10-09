<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Las especialidades de los especialistas, juntas: una página por cada una.
 *
 * La especialidad se escribe a mano en el panel, así que la misma sale de
 * varias formas: «Kinesiólogo», «kinesiologa», «Nutricionista - Preparador
 * Fisico». Aquí se parte donde hay un guion, una barra, una coma o una «y»
 * (quien hace dos cosas cuenta en las dos) y se compara sin mayúsculas, sin tildes y sin
 * el femenino de las terminaciones más comunes: «Kinesióloga» y «Kinesiólogo»
 * son la misma página.
 */
final class Especialidades
{
    /**
     * Las especialidades de un texto: [clave => cómo se lee].
     *
     * @return array<string,string>
     */
    public static function de(?string $especialidad): array
    {
        $partes = preg_split('/\s+[-–]\s+|\s*[\/,;|]\s*/u', trim((string) $especialidad)) ?: [];
        $salida = [];

        // «Entrenador personal: estética y funcionalidad» es entrenador
        // personal; lo de después de los dos puntos es el detalle. Y «judoka y
        // preparador físico» son dos: cada uno con su página.
        $partes = array_merge(...array_map(
            fn (string $p) => preg_split('/\s+[ye]\s+/u', preg_replace('/\s*:.*$/u', '', $p)) ?: [],
            $partes
        ));

        foreach ($partes as $parte) {
            $parte = trim($parte);
            $clave = self::clave($parte);

            if ($clave !== '') {
                $salida[$clave] ??= $parte;
            }
        }

        return $salida;
    }

    /**
     * Los especialistas agrupados por especialidad, en orden alfabético.
     *
     * El nombre de cada grupo es el que ya coincide con la clave (el
     * masculino, si alguien lo escribió así), y si no, el primero que salga,
     * con mayúscula inicial.
     *
     * @param  list<array<string,mixed>>  $especialistas  Con 'especialidad'.
     * @return array<string, array{slug:string, nombre:string, especialistas:list<array<string,mixed>>}>
     */
    public static function agrupar(array $especialistas): array
    {
        $grupos = [];

        foreach ($especialistas as $e) {
            foreach (self::de($e['especialidad'] ?? null) as $clave => $nombre) {
                $grupos[$clave] ??= ['slug' => $clave, 'formas' => [], 'especialistas' => []];
                $grupos[$clave]['formas'][] = $nombre;
                $grupos[$clave]['especialistas'][] = $e;
            }
        }

        foreach ($grupos as $clave => &$g) {
            $exacta = collect($g['formas'])->first(fn (string $f) => Str::slug($f) === $clave);
            $g['nombre'] = Str::ucfirst($exacta ?? $g['formas'][0]);
            unset($g['formas']);
        }
        unset($g);

        ksort($grupos);

        return $grupos;
    }

    /**
     * CÓMO LO BUSCA LA GENTE EN CHILE, cuando no es como se escribe en el
     * panel. Sale de las sugerencias de Google desde Chile (8-oct-2026): se
     * busca «personal trainer los angeles chile» y «masajes los angeles
     * chile», no «entrenador personal» ni «masajista». El título de la página
     * de esa especialidad lleva las dos formas.
     */
    public const COMO_SE_BUSCA = [
        'entrenador-personal' => 'Personal trainer y entrenador personal',
        'personal-trainer' => 'Personal trainer y entrenador personal',
        'masajista' => 'Masajes y masajista',
        'masoterapeuta' => 'Masajes y masoterapia',
        'quiromasajista' => 'Masajes y quiromasaje',
        'kinesiologo' => 'Kinesiólogo',
        'nutricionista' => 'Nutricionista',
    ];

    /** El nombre para el título: como se busca, o como se escribió. */
    public static function comoSeBusca(string $clave, string $nombre): string
    {
        return self::COMO_SE_BUSCA[$clave] ?? $nombre;
    }

    /** «Kinesióloga» → «kinesiologo»; «Preparadora Física» → «preparador-fisico». */
    public static function clave(string $texto): string
    {
        $palabras = explode('-', Str::slug($texto));

        return implode('-', array_filter(array_map(
            fn (string $p) => (string) preg_replace(['/ologa$/', '/ora$/', '/ica$/'], ['ologo', 'or', 'ico'], $p),
            $palabras
        )));
    }
}
