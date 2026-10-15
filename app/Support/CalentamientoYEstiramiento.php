<?php

namespace App\Support;

/**
 * El antes y el después de «Tu entrenamiento de hoy»: el calentamiento, los
 * estiramientos del final y la rutina entera de «Estirar y movilidad» para
 * los días livianos.
 *
 * EL CALENTAMIENTO ES BÁSICO (15-oct-2026): primero movilidad articular, las
 * rotaciones de siempre de cuello a tobillos, igual para todos; después unos
 * pocos ejercicios dinámicos según lo que toca (arriba, abajo o los dos).
 * Antes eran 5 minutos de cardio y ejercicios con banda elegidos músculo por
 * músculo: el dueño lo quería simple, sin material, como se calienta en la
 * sala.
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

    /** Movilidad sin banda, para el día liviano. [cómo, dosis, músculos]. */
    private const MOVILIDAD = [
        'Círculos de cadera' => ['De pie, manos en la cintura: dibuja círculos grandes con la cadera hacia un lado y al otro.', '10 por lado', ['gluteos', 'lumbar']],
        'Gato y camello' => ['En cuatro apoyos: redondea la espalda mirando al ombligo y después arquéala mirando al frente, lento.', '2 × 8', ['espalda', 'lumbar']],
    ];

    /**
     * MOVILIDAD ARTICULAR: cada articulación en círculos, de arriba abajo.
     * Igual para todos y todos los días. [cómo, dosis, músculos].
     */
    public const ARTICULAR = [
        'Cuello' => ['Gira la cabeza lento hacia un lado y al otro; después lleva la oreja hacia el hombro.', '5 por lado', []],
        'Hombros' => ['Brazos sueltos: círculos con los hombros hacia atrás y después hacia adelante.', '10 y 10', ['hombros']],
        'Brazos' => ['Brazos estirados a los lados: círculos chicos que se van agrandando, hacia adelante y hacia atrás.', '10 y 10', ['hombros', 'pecho']],
        'Codos y muñecas' => ['Gira los antebrazos doblando los codos; después las muñecas, con los dedos entrelazados.', '10 por lado', ['biceps', 'triceps']],
        'Tronco' => ['Pies firmes al ancho de la cadera: gira el tronco a un lado y al otro, dejando que los brazos sigan.', '10 por lado', ['espalda', 'abdomen']],
        'Cadera' => ['Manos en la cintura: círculos grandes con la cadera hacia un lado y al otro.', '10 por lado', ['gluteos', 'lumbar']],
        'Rodillas' => ['Pies juntos y manos en las rodillas: círculos lentos hacia un lado y al otro.', '10 por lado', ['cuadriceps']],
        'Tobillos' => ['Punta del pie apoyada en el suelo: gira el tobillo hacia un lado y al otro.', '10 por lado', ['pantorrillas']],
    ];

    /**
     * DINÁMICOS: movimientos sin peso que suben las pulsaciones y preparan lo
     * que viene. [cómo, dosis, zona (general, arriba o abajo), músculos].
     */
    public const DINAMICO = [
        'Trote en el lugar' => ['Suave, subiendo un poco las rodillas y moviendo los brazos.', '1 minuto', 'general', ['cuadriceps', 'pantorrillas']],
        'Saltos de tijera' => ['Abre piernas y brazos a la vez con un salto suave y vuelve. Sin saltar, dando pasos al lado.', '30 segundos', 'general', ['pantorrillas', 'hombros']],
        'Sentadilla sin peso' => ['Baja lento como sentándote y sube; cada vez un poco más profundo.', '10', 'abajo', ['cuadriceps', 'gluteos']],
        'Zancada con giro' => ['Paso largo adelante y gira el tronco hacia la pierna de adelante. Vuelve y cambia de pierna.', '6 por lado', 'abajo', ['cuadriceps', 'gluteos']],
        'Balanceo de pierna' => ['Apoyado en la pared: balancea la pierna estirada adelante y atrás, y después de lado a lado.', '10 por lado', 'abajo', ['isquios', 'gluteos']],
        'Rodillas al pecho caminando' => ['Camina y en cada paso abraza una rodilla hacia el pecho, de pie bien derecho.', '10 pasos', 'abajo', ['gluteos', 'isquios']],
        'Puente de glúteos sin peso' => ['Acostado, pies apoyados: sube la cadera apretando los glúteos y baja lento.', '10', 'abajo', ['gluteos', 'isquios']],
        'Abrazos dinámicos' => ['Abre los brazos bien atrás y crúzalos adelante como en un abrazo, cambiando cuál va arriba.', '10', 'arriba', ['pecho', 'espalda', 'hombros']],
        'Rotación de espalda en cuatro apoyos' => ['En cuatro apoyos, una mano en la nuca: gira el codo hacia el techo siguiéndolo con la mirada.', '8 por lado', 'arriba', ['espalda']],
        'Flexiones inclinadas' => ['Manos en un banco y cuerpo recto: flexiones lentas y fáciles, sin cansarte.', '8', 'arriba', ['pecho', 'triceps']],
        'Brazos en Y y en T' => ['Inclinado adelante con la espalda recta: sube los brazos sin peso en Y y después en T, juntando los omóplatos.', '8 y 8', 'arriba', ['hombros', 'espalda']],
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

    /**
     * El calentamiento de hoy: toda la movilidad articular y 4 dinámicos (uno
     * general y tres de lo que toca; arriba y abajo repartidos si el día
     * trabaja los dos). Cada uno lleva su 'tipo': articular o dinamico.
     *
     * @param list<string> $principales
     * @param list<string> $secundarios
     * @return list<array<string,mixed>>
     */
    public static function calentamiento(array $principales, array $secundarios, string $grupo): array
    {
        if ($principales === []) {
            $principales = ['cuadriceps', 'gluteos', 'pantorrillas'];
        }

        $arriba = array_intersect($principales, self::ARRIBA) !== [];
        $abajo = array_intersect($principales, self::ABAJO) !== [];
        $cupos = match (true) {
            $arriba && $abajo => ['abajo' => 2, 'arriba' => 1],
            $arriba => ['arriba' => 3],
            default => ['abajo' => 3],
        };

        $puntaje = fn (array $musculos) => 2 * count(array_intersect($musculos, $principales)) + count(array_intersect($musculos, $secundarios));
        $elegir = function (string $zona, int $cuantos) use ($puntaje) {
            $nombres = array_keys(array_filter(self::DINAMICO, fn ($d) => $d[2] === $zona));
            usort($nombres, fn ($a, $b) => [-$puntaje(self::DINAMICO[$a][3]), EntrenamientoDeHoy::delDia($a)]
                <=> [-$puntaje(self::DINAMICO[$b][3]), EntrenamientoDeHoy::delDia($b)]);

            return array_slice($nombres, 0, $cuantos);
        };

        // El general primero: sube las pulsaciones.
        $dinamicos = $elegir('general', 1);

        foreach ($cupos as $zona => $cuantos) {
            array_push($dinamicos, ...$elegir($zona, $cuantos));
        }

        return [
            ...array_map(fn ($n) => self::deArticular($n), array_keys(self::ARTICULAR)),
            ...array_map(fn ($n) => self::deDinamico($n), $dinamicos),
        ];
    }

    /** Solo la movilidad articular: el calentamiento del día de estirar. @return list<array<string,mixed>> */
    public static function articular(): array
    {
        return array_map(fn ($n) => self::deArticular($n), array_keys(self::ARTICULAR));
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
     * «Estirar y movilidad»: la movilidad articular de calentamiento, lo
     * dinámico más tranquilo y un estiramiento por zona, de los pies a la
     * cabeza. Unos 20 minutos.
     *
     * @return array{movilidad:list<array<string,mixed>>, estiramientos:list<array<string,mixed>>, minutos:int}
     */
    public static function rutinaDeMovilidad(): array
    {
        $porDia = fn (array $nombres) => collect($nombres)->sortBy(fn ($n) => EntrenamientoDeHoy::delDia($n))->values()->all();

        // Lo dinámico más tranquilo, de abajo arriba: sin saltos ni pulsaciones.
        $movilidad = [
            self::deDinamico('Balanceo de pierna'),
            self::deDinamico('Zancada con giro'),
            self::deMovilidad('Gato y camello'),
            self::deDinamico('Rotación de espalda en cuatro apoyos'),
            self::deDinamico($porDia(['Abrazos dinámicos', 'Brazos en Y y en T'])[0]),
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
    private static function deArticular(string $nombre): array
    {
        [$como, $dosis, $musculos] = self::ARTICULAR[$nombre];

        return ['nombre' => $nombre, 'como' => $como, 'dosis' => $dosis, 'banda' => false, 'musculos' => $musculos, 'tipo' => 'articular'];
    }

    /** @return array<string,mixed> */
    private static function deDinamico(string $nombre): array
    {
        [$como, $dosis, , $musculos] = self::DINAMICO[$nombre];

        return ['nombre' => $nombre, 'como' => $como, 'dosis' => $dosis, 'banda' => false, 'musculos' => $musculos, 'tipo' => 'dinamico'];
    }

    /** @return array<string,mixed> */
    private static function deMovilidad(string $nombre): array
    {
        [$como, $dosis, $musculos] = self::MOVILIDAD[$nombre];

        return ['nombre' => $nombre, 'como' => $como, 'dosis' => $dosis, 'banda' => false, 'musculos' => $musculos];
    }
}
