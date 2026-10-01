<?php

namespace App\Support;

/**
 * El antes y el después de «Tu entrenamiento de hoy»: el calentamiento con
 * banda elástica (la sala tiene bandas) y los estiramientos del final, más la
 * rutina entera de «Estirar y movilidad» para los días livianos.
 *
 * Son datos fijos, sin tabla: cada uno con cómo se hace en una línea, la
 * dosis, si usa banda y los músculos que mueve (las claves de
 * Ejercicio::MUSCULOS, para su mini mapa). Se eligen según los músculos que
 * trabaja el día y, entre iguales, cambian con la fecha (delDia).
 */
final class CalentamientoYEstiramiento
{
    /** Los músculos de arriba y de abajo, para repartir el calentamiento. */
    private const ARRIBA = ['pecho', 'espalda', 'hombros', 'biceps', 'triceps'];

    private const ABAJO = ['cuadriceps', 'isquios', 'gluteos', 'pantorrillas', 'lumbar'];

    /**
     * Con banda, antes de entrenar. [cómo, dosis, zona, músculos, de base].
     * Los «de base» preparan hombros o cadera para cualquier día de esa zona.
     */
    public const CALENTAMIENTO = [
        'Separación de banda al pecho' => ['Brazos estirados al frente: abre la banda hasta el pecho juntando los omóplatos.', '2 × 15', 'arriba', ['espalda', 'hombros'], true],
        'Dislocaciones de hombro con banda' => ['Banda tomada ancha y brazos rectos: pásala por sobre la cabeza hasta atrás y vuelve.', '2 × 10', 'arriba', ['hombros', 'pecho'], true],
        'Rotación externa con banda' => ['Codo pegado al cuerpo y doblado en 90°: gira el antebrazo hacia afuera sin mover el codo.', '2 × 10 por lado', 'arriba', ['hombros'], true],
        'Face pull con banda' => ['Banda amarrada a la altura de la cara: tira hacia los ojos abriendo los codos.', '2 × 15', 'arriba', ['hombros', 'espalda'], true],
        'Press con banda al frente' => ['Banda por detrás de la espalda: empuja al frente como un press y vuelve lento.', '2 × 15', 'arriba', ['pecho', 'triceps'], false],
        'Curl con banda' => ['Pisa la banda y sube las manos doblando los codos, sin balancearte.', '2 × 15', 'arriba', ['biceps'], false],
        'Extensión de tríceps con banda' => ['Banda amarrada arriba y codos pegados: estira los brazos hacia abajo.', '2 × 15', 'arriba', ['triceps'], false],
        'Caminata lateral con banda' => ['Banda sobre las rodillas y medio agachado: da pasos al lado sin juntar los pies.', '2 × 10 por lado', 'abajo', ['gluteos'], true],
        'Puente de glúteo con banda' => ['Acostado, banda sobre las rodillas: sube la cadera empujando las rodillas hacia afuera.', '2 × 15', 'abajo', ['gluteos', 'isquios'], true],
        'Sentadilla con banda sobre las rodillas' => ['Baja lento empujando las rodillas contra la banda, sin que se junten.', '2 × 15', 'abajo', ['cuadriceps', 'gluteos'], true],
        'Buenos días con banda' => ['Pisa la banda y pásala por la nuca: inclínate con la espalda recta y sube apretando el glúteo.', '2 × 15', 'abajo', ['isquios', 'lumbar'], true],
        'Almeja con banda' => ['De lado, rodillas dobladas y banda sobre ellas: abre la rodilla de arriba sin girar la cadera.', '2 × 10 por lado', 'abajo', ['gluteos'], false],
        'Pallof con banda' => ['Banda amarrada a un lado, a la altura del pecho: estira los brazos al frente sin dejar que te gire.', '2 × 10 por lado', 'centro', ['abdomen'], false],
    ];

    /** Movilidad sin banda, para el día liviano. [cómo, dosis, músculos]. */
    private const MOVILIDAD = [
        'Círculos de cadera' => ['De pie, manos en la cintura: dibuja círculos grandes con la cadera hacia un lado y al otro.', '10 por lado', ['gluteos', 'lumbar']],
        'Gato y camello' => ['En cuatro apoyos: redondea la espalda mirando al ombligo y después arquéala mirando al frente, lento.', '2 × 8', ['espalda', 'lumbar']],
    ];

    /**
     * Para el final. [cómo, con banda, por lado, músculos, zona del cuerpo].
     * Las zonas van de los pies a la cabeza.
     */
    public const ESTIRAMIENTOS = [
        'Pantorrilla en la pared' => ['Manos en la pared y una pierna atrás, estirada y con el talón en el suelo: inclínate adelante.', false, true, ['pantorrillas'], 'pantorrilla'],
        'Isquios acostado con banda' => ['Boca arriba, banda en la planta del pie: sube la pierna recta hasta sentir el tirón atrás del muslo.', true, true, ['isquios'], 'isquios'],
        'Isquios de pie' => ['Talón sobre un banco bajo y pierna recta: inclínate adelante con la espalda derecha.', false, true, ['isquios'], 'isquios'],
        'Cuádriceps de pie' => ['Apoyado en la pared, toma el pie por detrás y llévalo al glúteo con las rodillas juntas.', false, true, ['cuadriceps'], 'cuadriceps'],
        'Flexor de cadera en zancada' => ['Rodilla de atrás en el suelo: empuja la cadera adelante apretando el glúteo.', false, true, ['cuadriceps'], 'cadera'],
        'Glúteo en figura 4' => ['Acostado, cruza el tobillo sobre la otra rodilla y acerca las piernas al pecho.', false, true, ['gluteos'], 'gluteo'],
        'Postura del niño' => ['De rodillas, siéntate en los talones y estira los brazos adelante en el suelo.', false, false, ['espalda', 'lumbar'], 'espalda'],
        'Dorsal colgado en la barra' => ['Cuélgate de la barra con los brazos estirados y suelta el peso del cuerpo.', false, false, ['espalda'], 'espalda'],
        'Dorsal con banda' => ['Banda amarrada arriba, tómala con una mano y lleva la cadera atrás hasta estirar el costado.', true, true, ['espalda'], 'espalda'],
        'Cobra' => ['Boca abajo, apoya las manos y sube el pecho dejando la cadera en el suelo.', false, false, ['abdomen'], 'abdomen'],
        'Pecho con banda detrás' => ['Toma la banda detrás de la espalda con los brazos estirados y abre el pecho subiendo un poco las manos.', true, false, ['pecho', 'hombros'], 'pecho'],
        'Pecho en la pared' => ['Antebrazo en la pared a la altura del hombro: gira el cuerpo hacia el otro lado.', false, true, ['pecho'], 'pecho'],
        'Hombro cruzado' => ['Cruza el brazo estirado por delante del pecho y apriétalo con el otro.', false, true, ['hombros'], 'hombro'],
        'Tríceps detrás de la cabeza' => ['Lleva la mano a la espalda por sobre la cabeza y empuja suave el codo con la otra.', false, true, ['triceps'], 'brazo'],
        'Bíceps en la pared' => ['Palma en la pared con el brazo estirado atrás: gira el cuerpo hacia afuera.', false, true, ['biceps'], 'brazo'],
    ];

    /** Las zonas, de los pies a la cabeza. */
    private const DE_PIES_A_CABEZA = ['pantorrilla', 'isquios', 'cuadriceps', 'cadera', 'gluteo', 'espalda', 'abdomen', 'pecho', 'hombro', 'brazo'];

    /** Lo primero del calentamiento, igual para todos. */
    public const CARDIO_SUAVE = [
        'nombre' => 'Cardio suave',
        'como' => 'Bicicleta, trotadora o elíptica, a un ritmo en que puedas conversar.',
        'dosis' => '5 minutos',
        'banda' => false,
        'musculos' => [],
    ];

    /**
     * El calentamiento con banda para lo de hoy: 4 ejercicios (3 los días de
     * cardio), repartidos entre arriba y abajo si el día trabaja los dos.
     *
     * @param list<string> $principales
     * @param list<string> $secundarios
     * @return list<array<string,mixed>>
     */
    public static function calentamiento(array $principales, array $secundarios, string $grupo): array
    {
        // El cardio mueve las piernas aunque no tenga músculos marcados.
        if ($principales === []) {
            $principales = ['cuadriceps', 'gluteos', 'pantorrillas'];
        }

        $cuantos = $grupo === 'cardio' ? 3 : 4;
        $arriba = array_intersect($principales, self::ARRIBA) !== [];
        $abajo = array_intersect($principales, self::ABAJO) !== [];

        $cupos = match (true) {
            $arriba && $abajo => ['abajo' => intdiv($cuantos + 1, 2), 'arriba' => intdiv($cuantos, 2)],
            $arriba => ['arriba' => $cuantos],
            default => ['abajo' => $cuantos],
        };

        // Si el día trabaja abdomen, uno de zona media en vez del último (no
        // el día de cardio: su zona media ya trae el Pallof).
        if (in_array('abdomen', $principales, true) && $grupo !== 'cardio') {
            $cupos[array_key_last($cupos)]--;
            $cupos['centro'] = 1;
        }

        $puntaje = fn (array $musculos, bool $base) => ($base ? 1.5 : 0)
            + 2 * count(array_intersect($musculos, $principales))
            + count(array_intersect($musculos, $secundarios));

        $elegidos = [];

        foreach ($cupos as $zona => $cupo) {
            $candidatos = array_filter(self::CALENTAMIENTO, fn ($c) => $c[2] === $zona, ARRAY_FILTER_USE_BOTH);
            uksort($candidatos, fn ($a, $b) => [-$puntaje(self::CALENTAMIENTO[$a][3], self::CALENTAMIENTO[$a][4]), EntrenamientoDeHoy::delDia($a)]
                <=> [-$puntaje(self::CALENTAMIENTO[$b][3], self::CALENTAMIENTO[$b][4]), EntrenamientoDeHoy::delDia($b)]);

            foreach (array_slice(array_keys($candidatos), 0, max(0, $cupo)) as $nombre) {
                $elegidos[] = self::deCalentamiento($nombre);
            }
        }

        return $elegidos;
    }

    /**
     * De 3 a 5 estiramientos para los músculos de hoy: primero los que más
     * trabajó, uno por músculo, y ordenados de los pies a la cabeza.
     *
     * @param list<string> $principales
     * @param list<string> $secundarios
     * @return list<array<string,mixed>>
     */
    public static function estiramientos(array $principales, array $secundarios): array
    {
        if ($principales === []) {
            $principales = ['cuadriceps', 'isquios', 'pantorrillas'];
        }

        $puntaje = fn (array $musculos) => 2 * count(array_intersect($musculos, $principales))
            + count(array_intersect($musculos, $secundarios));

        $elegidos = [];
        $cubiertos = [];

        foreach ([...$principales, ...$secundarios] as $musculo) {
            if (count($elegidos) >= 5 || in_array($musculo, $cubiertos, true)) {
                continue;
            }

            $candidatos = array_filter(array_keys(self::ESTIRAMIENTOS), fn ($n) => in_array($musculo, self::ESTIRAMIENTOS[$n][3], true) && ! isset($elegidos[$n]));
            usort($candidatos, fn ($a, $b) => [-$puntaje(self::ESTIRAMIENTOS[$a][3]), EntrenamientoDeHoy::delDia($a)]
                <=> [-$puntaje(self::ESTIRAMIENTOS[$b][3]), EntrenamientoDeHoy::delDia($b)]);

            if ($candidatos !== []) {
                $elegidos[$candidatos[0]] = true;
                array_push($cubiertos, ...self::ESTIRAMIENTOS[$candidatos[0]][3]);
            }
        }

        // Al menos tres: se completa con algo de la misma zona.
        $rellenos = array_intersect($principales, self::ARRIBA) !== []
            ? ['Hombro cruzado', 'Dorsal con banda', 'Pecho en la pared', 'Postura del niño']
            : ['Postura del niño', 'Cuádriceps de pie', 'Isquios acostado con banda', 'Glúteo en figura 4'];

        foreach ($rellenos as $relleno) {
            if (count($elegidos) < 3 && ! array_intersect(self::ESTIRAMIENTOS[$relleno][3], $cubiertos)) {
                $elegidos[$relleno] = true;
                array_push($cubiertos, ...self::ESTIRAMIENTOS[$relleno][3]);
            }
        }

        return self::dePiesACabeza(array_keys($elegidos));
    }

    /**
     * «Estirar y movilidad»: cardio suave, movilidad (con y sin banda) y un
     * estiramiento por zona, de los pies a la cabeza. Unos 20 minutos.
     *
     * @return array{movilidad:list<array<string,mixed>>, estiramientos:list<array<string,mixed>>, minutos:int}
     */
    public static function rutinaDeMovilidad(): array
    {
        $porDia = fn (array $nombres) => collect($nombres)->sortBy(fn ($n) => EntrenamientoDeHoy::delDia($n))->values()->all();

        $abajo = $porDia(['Puente de glúteo con banda', 'Buenos días con banda'])[0];
        $arriba = array_slice($porDia(['Dislocaciones de hombro con banda', 'Separación de banda al pecho', 'Rotación externa con banda']), 0, 2);

        $movilidad = [
            self::deMovilidad('Círculos de cadera'),
            self::deCalentamiento($abajo),
            self::deMovilidad('Gato y camello'),
            ...array_map(fn ($n) => self::deCalentamiento($n), $arriba),
        ];

        // Uno por zona; en las que hay más de uno, cambia con la fecha.
        $estiramientos = [];

        foreach (self::DE_PIES_A_CABEZA as $zona) {
            if ($zona === 'abdomen') {
                continue;
            }

            $deLaZona = array_keys(array_filter(self::ESTIRAMIENTOS, fn ($e) => $e[4] === $zona));
            $estiramientos[] = $porDia($deLaZona)[0];
        }

        $estiramientos = self::dePiesACabeza($estiramientos);

        // Algo más de un minuto por ejercicio de movilidad; los estiramientos,
        // medio minuto (o uno si es por lado).
        $minutos = 5 + 1.2 * count($movilidad)
            + array_sum(array_map(fn ($e) => $e['lado'] ? 1 : 0.5, $estiramientos));

        return [
            'movilidad' => $movilidad,
            'estiramientos' => $estiramientos,
            'minutos' => (int) (round($minutos / 5) * 5),
        ];
    }

    /**
     * @param list<string> $nombres
     * @return list<array<string,mixed>>
     */
    private static function dePiesACabeza(array $nombres): array
    {
        usort($nombres, fn ($a, $b) => array_search(self::ESTIRAMIENTOS[$a][4], self::DE_PIES_A_CABEZA, true)
            <=> array_search(self::ESTIRAMIENTOS[$b][4], self::DE_PIES_A_CABEZA, true));

        return array_map(function (string $nombre) {
            [$como, $banda, $lado, $musculos] = self::ESTIRAMIENTOS[$nombre];

            return [
                'nombre' => $nombre,
                'como' => $como,
                'dosis' => $lado ? '30 segundos por lado' : '30 segundos',
                'banda' => $banda,
                'lado' => $lado,
                'musculos' => $musculos,
            ];
        }, $nombres);
    }

    /** @return array<string,mixed> */
    private static function deCalentamiento(string $nombre): array
    {
        [$como, $dosis, , $musculos] = self::CALENTAMIENTO[$nombre];

        return ['nombre' => $nombre, 'como' => $como, 'dosis' => $dosis, 'banda' => true, 'musculos' => $musculos];
    }

    /** @return array<string,mixed> */
    private static function deMovilidad(string $nombre): array
    {
        [$como, $dosis, $musculos] = self::MOVILIDAD[$nombre];

        return ['nombre' => $nombre, 'como' => $como, 'dosis' => $dosis, 'banda' => false, 'musculos' => $musculos];
    }
}
