<?php

namespace App\Support;

use App\Models\Ejercicio;
use App\Models\Rutina;
use App\Models\RutinaEjercicio;

/**
 * Las rutinas de la sala para la web: la lista, cada una con sus ejercicios, y
 * los ejercicios del gimnasio.
 *
 * Los QR impresos llevan tres respuestas en la dirección (qué busca, cuánto
 * lleva entrenando y cuántos días puede venir): con ellas se busca una de las
 * rutinas cargadas y se abre su página.
 *
 * SIEMPRE SALE ALGUNA mientras haya rutinas. Quien contestó y recibe «no hay
 * nada para ti» queda tan perdido como antes de escanear: si no hay una exacta
 * se afloja primero el nivel, después los días, y la pantalla dice de qué es la
 * que le tocó.
 */
class RutinaSugerida
{
    /**
     * Los días listos para mostrar: cada ejercicio con su foto (si el
     * gimnasio subió una) y los músculos que trabaja, para el mapa muscular.
     * Cada día junta además todo lo que trabaja, para su mapa de arriba.
     *
     * @return list<array<string,mixed>>
     */
    public static function dias(Rutina $rutina): array
    {
        return $rutina->dias->map(function ($dia) {
            $lineas = $dia->ejercicios->map(fn (RutinaEjercicio $l) => self::linea($l))->values()->all();
            $principales = array_values(array_unique(array_filter(array_column($lineas, 'principal'))));
            $secundarios = array_values(array_diff(array_unique(array_merge(...array_column($lineas, 'secundarios'))), $principales));

            return [
                'numero' => (int) $dia->numero,
                'titulo' => $dia->titulo,
                'foco' => $dia->foco,
                'lineas' => $lineas,
                'principales' => $principales,
                'secundarios' => $secundarios,
            ];
        })->all();
    }

    /** @return array<string,mixed> */
    public static function linea(RutinaEjercicio $linea): array
    {
        $ejercicio = $linea->ejercicio;
        $grupos = $ejercicio?->grupos() ?? ['principal' => null, 'secundarios' => []];

        return [
            'nombre' => $ejercicio->nombre ?? 'Ejercicio',
            'dosis' => self::dosis((int) $linea->series, (string) $linea->repeticiones),
            'corta' => self::corta((int) $linea->series, (string) $linea->repeticiones),
            'series' => (int) $linea->series,
            'repeticiones' => (string) $linea->repeticiones,
            'descanso' => (int) $linea->descanso_seg,
            'nota' => $linea->nota ?: ($ejercicio->indicacion ?? null),
            'alternativa' => $linea->alternativa?->nombre,
            'imagen' => $ejercicio?->urlDeImagen(),
            'principal' => $grupos['principal'],
            'secundarios' => $grupos['secundarios'],
            'zona' => $ejercicio?->zona,
        ];
    }

    /** «3 × 10-12», para leer de lejos. Lo que no es un número va tal cual. */
    public static function corta(int $series, string $repeticiones): string
    {
        $repeticiones = preg_replace('/^(\d+) a (\d+)/u', '$1-$2', trim($repeticiones));

        return $series <= 1 ? $repeticiones : "{$series} × {$repeticiones}";
    }

    /**
     * «3 series × 10 a 12 repeticiones». Si lo de las repeticiones no es un
     * número («30 segundos», «15 minutos», «10 por pierna») va tal cual, y con
     * una sola serie no se dice «1 serie ×».
     */
    public static function dosis(int $series, string $repeticiones): string
    {
        $repeticiones = trim($repeticiones);
        $cuantas = preg_match('/^\d+( a \d+)?$/u', $repeticiones) ? "{$repeticiones} repeticiones" : $repeticiones;

        if ($series <= 1) {
            return $cuantas;
        }

        return "{$series} series × {$cuantas}";
    }

    /**
     * Las rutinas activas por objetivo, para la lista de /rutina. Con un
     * objetivo, solo esas.
     *
     * @return array<string, array{nombre:string, rutinas: list<array<string,mixed>>}>
     */
    public static function porObjetivo(?string $objetivo = null): array
    {
        $rutinas = Rutina::where('activa', true)
            ->when($objetivo, fn ($q) => $q->where('objetivo', $objetivo))
            ->orderBy('dias_por_semana')
            ->orderBy('orden')
            ->get()
            ->groupBy('objetivo');
        $nivel = fn (Rutina $r) => (int) array_search($r->nivel, array_keys(Rutina::NIVELES), true);

        $grupos = [];

        foreach (Rutina::OBJETIVOS as $clave => $nombre) {
            if (! isset($rutinas[$clave])) {
                continue;
            }

            $grupos[$clave] = [
                'nombre' => $nombre,
                'rutinas' => $rutinas[$clave]->sortBy([
                    fn (Rutina $a, Rutina $b) => $nivel($a) <=> $nivel($b),
                    fn (Rutina $a, Rutina $b) => $a->dias_por_semana <=> $b->dias_por_semana,
                ])->values()->map(fn (Rutina $r) => [
                    'nombre' => $r->nombre,
                    'nivel' => Rutina::NIVELES[$r->nivel] ?? $r->nivel,
                    'dias' => $r->dias_por_semana,
                    'url' => route('landing.rutina.ver', $r->slug),
                ])->all(),
            ];
        }

        return $grupos;
    }

    /**
     * Los ejercicios activos del gimnasio por grupo, en el orden del panel:
     * pecho, espalda, piernas, hombros, brazos, abdomen, cardio y cuerpo
     * completo. Cada uno con su foto o su mapa muscular.
     *
     * @return array<string, array{nombre:string, ejercicios: list<array<string,mixed>>}>
     */
    public static function ejerciciosPorGrupo(): array
    {
        $todos = Ejercicio::where('activo', true)->orderBy('orden')->orderBy('nombre')->get()->groupBy('zona');
        $nombres = ['core' => 'Abdomen'] + Ejercicio::ZONAS;
        $grupos = [];

        foreach (array_keys(Ejercicio::ZONAS) as $zona) {
            if (! isset($todos[$zona])) {
                continue;
            }

            $grupos[$zona] = [
                'nombre' => $nombres[$zona],
                'ejercicios' => $todos[$zona]->map(fn (Ejercicio $e) => [
                    'nombre' => $e->nombre,
                    'equipo' => Ejercicio::EQUIPOS[$e->equipo] ?? null,
                    'nota' => $e->indicacion,
                    'imagen' => $e->urlDeImagen(),
                    'principal' => $e->grupos()['principal'],
                    'secundarios' => $e->grupos()['secundarios'],
                ])->values()->all(),
            ];
        }

        return $grupos;
    }

    /** Cómo seguir subiendo: una línea según el objetivo y el nivel. */
    public static function comoProgresar(Rutina $rutina): string
    {
        if ($rutina->nivel === 'nunca') {
            return 'Las dos primeras semanas aprende la técnica. Después, cuando hagas todas las repeticiones bien, sube un poco el peso.';
        }

        return match ($rutina->objetivo) {
            'fuerza' => 'Cuando hagas todas las repeticiones con buena técnica, sube 2,5 kg o una repetición.',
            'bajar_grasa' => 'Cada semana intenta descansar un poco menos o sumar unos minutos de cardio.',
            'mantener' => 'Si todo se siente fácil, sube un poco el peso o una repetición.',
            default => 'Cuando hagas todas las repeticiones con buena técnica, sube al peso siguiente.',
        };
    }

    public static function buscar(string $objetivo, string $nivel, int $dias): ?Rutina
    {
        $con = fn (array $filtros) => Rutina::where('activa', true)
            ->where($filtros)
            ->with(['dias.ejercicios.ejercicio', 'dias.ejercicios.alternativa'])
            ->orderBy('orden')
            ->first();

        return $con(['objetivo' => $objetivo, 'nivel' => $nivel, 'dias_por_semana' => $dias])
            ?? $con(['objetivo' => $objetivo, 'dias_por_semana' => $dias])
            ?? $con(['objetivo' => $objetivo, 'nivel' => $nivel])
            ?? $con(['objetivo' => $objetivo])
            ?? $con(['nivel' => $nivel, 'dias_por_semana' => $dias])
            ?? Rutina::where('activa', true)->with(['dias.ejercicios.ejercicio', 'dias.ejercicios.alternativa'])->orderBy('orden')->first();
    }

    /**
     * Las otras rutinas del mismo objetivo: la misma idea con más o menos
     * días, o para otro nivel. Quien puede venir un día más, o ya se le quedó
     * corta la suya, pasa a la otra desde abajo de la rutina.
     *
     * @return list<array{nombre:string, nivel:string, dias:int, url:string}>
     */
    public static function variantes(Rutina $actual): array
    {
        return Rutina::where('activa', true)
            ->where('objetivo', $actual->objetivo)
            ->whereKeyNot($actual->getKey())
            ->orderBy('dias_por_semana')
            ->orderBy('orden')
            ->get()
            ->map(fn (Rutina $r) => [
                'nombre' => $r->nombre,
                'nivel' => Rutina::NIVELES[$r->nivel] ?? $r->nivel,
                'dias' => $r->dias_por_semana,
                'url' => route('landing.rutina.ver', $r->slug),
            ])
            ->all();
    }

    /** Si lo que respondió tiene sentido: si no, se le vuelven a hacer las preguntas. */
    public static function respondido(string $objetivo, string $nivel, int $dias): bool
    {
        return isset(Rutina::OBJETIVOS[$objetivo])
            && isset(Rutina::NIVELES[$nivel])
            && $dias >= 2
            && $dias <= 6;
    }
}
