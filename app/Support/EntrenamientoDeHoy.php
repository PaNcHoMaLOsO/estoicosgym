<?php

namespace App\Support;

use App\Models\Ejercicio;
use App\Models\Rutina;

/**
 * «Tu entrenamiento de hoy»: lo que sale de las cuatro preguntas de /rutina.
 *
 * Con los días y el nivel se busca la rutina de siempre (RutinaSugerida::buscar,
 * con el objetivo que corresponde al nivel). De esa rutina se toma el DÍA que
 * mejor calza con lo que quiere entrenar hoy y que menos pisa lo que entrenó
 * los últimos días, mirando los músculos de cada ejercicio.
 *
 * Si ningún día calza (pide piernas y su rutina es de cuerpo completo), el día
 * se arma con el catálogo de la sala: 5 o 6 ejercicios del grupo pedido, los
 * que mueven más músculos primero y los accesorios después, con series y
 * repeticiones según el nivel.
 *
 * Antes va el calentamiento con banda y después los estiramientos de lo que
 * trabajó (CalentamientoYEstiramiento). «Estirar y movilidad» es un día
 * liviano entero de eso.
 *
 * No guarda nada: las respuestas van en la dirección.
 */
class EntrenamientoDeHoy
{
    public const DIAS = [2, 3, 4, 5, 6];

    /** Las mismas claves de Rutina::NIVELES, dichas como se pregunta. */
    public const NIVELES = [
        'nunca' => 'Estoy empezando',
        'algo' => 'Ya entreno hace un tiempo',
        'hace_tiempo' => 'Entreno hace años',
    ];

    public const GRUPOS = [
        'pecho' => 'Pecho',
        'espalda' => 'Espalda',
        'piernas' => 'Piernas',
        'hombros_brazos' => 'Hombros y brazos',
        // Las combinaciones clásicas de la sala.
        'pecho_triceps' => 'Pecho y tríceps',
        'espalda_biceps' => 'Espalda y bíceps',
        'piernas_gluteos' => 'Piernas y glúteos',
        'hombros_abdomen' => 'Hombros y abdomen',
        // Todo lo de arriba en un día (la mitad de torso y pierna), cargado
        // hacia el pecho o hacia la espalda: así dos días de torso en la semana
        // no son el mismo.
        'torso_pecho' => 'Torso, más pecho',
        'torso_espalda' => 'Torso, más espalda',
        'cuerpo_completo' => 'Cuerpo completo',
        'cardio' => 'Cardio',
        // Un día liviano: movilidad con banda y estiramientos.
        'movilidad' => 'Estirar y movilidad',
    ];

    /** Cómo se muestran las opciones, para que la lista larga se lea. */
    public const SECCIONES = [
        'Divididos' => ['pecho', 'espalda', 'piernas', 'hombros_brazos'],
        'Combinados' => ['pecho_triceps', 'espalda_biceps', 'piernas_gluteos', 'hombros_abdomen'],
        'Completos' => ['torso_pecho', 'torso_espalda', 'cuerpo_completo'],
        'Otros' => ['cardio', 'movilidad'],
    ];

    /**
     * Lo que se puede marcar en «¿qué entrenaste estos últimos días?». Estirar
     * no cansa: no se pregunta.
     */
    public const HICE = [
        'pecho' => 'Pecho',
        'espalda' => 'Espalda',
        'piernas' => 'Piernas',
        'hombros_brazos' => 'Hombros y brazos',
        'pecho_triceps' => 'Pecho y tríceps',
        'espalda_biceps' => 'Espalda y bíceps',
        'piernas_gluteos' => 'Piernas y glúteos',
        'hombros_abdomen' => 'Hombros y abdomen',
        'torso_pecho' => 'Torso, más pecho',
        'torso_espalda' => 'Torso, más espalda',
        'cuerpo_completo' => 'Cuerpo completo',
        'cardio' => 'Cardio',
        'nada' => 'Nada, descansé',
    ];

    /** Los músculos de cada grupo, para su mini mapa. */
    public const MUSCULOS = [
        'pecho' => ['pecho'],
        'espalda' => ['espalda', 'lumbar'],
        'piernas' => ['cuadriceps', 'isquios', 'gluteos', 'pantorrillas'],
        'hombros_brazos' => ['hombros', 'biceps', 'triceps'],
        'pecho_triceps' => ['pecho', 'triceps'],
        'espalda_biceps' => ['espalda', 'biceps'],
        'piernas_gluteos' => ['gluteos', 'cuadriceps', 'isquios'],
        'hombros_abdomen' => ['hombros', 'abdomen'],
        'torso_pecho' => ['pecho', 'hombros', 'triceps'],
        'torso_espalda' => ['espalda', 'lumbar', 'biceps'],
        'cuerpo_completo' => ['pecho', 'espalda', 'hombros', 'cuadriceps', 'isquios', 'gluteos', 'abdomen'],
        'cardio' => [],
        'movilidad' => [],
    ];

    /**
     * Lo que de verdad cansa cada grupo: lo que conviene dejar descansar si
     * lo entrenó estos días. El torso cansa todo lo de arriba.
     */
    public const CARGA = [
        'pecho' => ['pecho'],
        'espalda' => ['espalda', 'lumbar'],
        'piernas' => ['cuadriceps', 'isquios', 'gluteos', 'pantorrillas'],
        'hombros_brazos' => ['hombros', 'biceps', 'triceps'],
        'pecho_triceps' => ['pecho', 'triceps'],
        'espalda_biceps' => ['espalda', 'lumbar', 'biceps'],
        'piernas_gluteos' => ['cuadriceps', 'isquios', 'gluteos'],
        'hombros_abdomen' => ['hombros', 'abdomen'],
        'torso_pecho' => ['pecho', 'espalda', 'lumbar', 'hombros', 'biceps', 'triceps'],
        'torso_espalda' => ['pecho', 'espalda', 'lumbar', 'hombros', 'biceps', 'triceps'],
        'cuerpo_completo' => ['pecho', 'espalda', 'lumbar', 'hombros', 'biceps', 'triceps', 'abdomen', 'cuadriceps', 'isquios', 'gluteos', 'pantorrillas'],
        'cardio' => [],
        'movilidad' => [],
    ];

    /**
     * La vuelta de la semana según los días: hoy toca lo que sigue a lo último
     * que entrenó. Con pocos días, cuerpo completo; con 4, torso y pierna se
     * turnan; con 5, uno por grupo; con 6, las combinaciones.
     */
    public const VUELTAS = [
        2 => ['cuerpo_completo'],
        3 => ['cuerpo_completo'],
        4 => ['torso_pecho', 'piernas', 'torso_espalda', 'piernas_gluteos'],
        5 => ['pecho', 'espalda', 'piernas', 'hombros_brazos'],
        6 => ['pecho_triceps', 'espalda_biceps', 'piernas_gluteos', 'hombros_abdomen'],
    ];

    /** Si lo de la vuelta choca con lo ya hecho, lo primero de aquí que no choque. */
    private const RESPALDO = [
        'espalda_biceps', 'pecho_triceps', 'piernas_gluteos', 'hombros_abdomen',
        'espalda', 'pecho', 'piernas', 'hombros_brazos', 'torso_espalda', 'torso_pecho',
        'cardio', 'movilidad',
    ];

    /** El objetivo que se busca según el nivel. */
    public const OBJETIVO_POR_NIVEL = [
        'nunca' => 'empezar',
        'algo' => 'fuerza',
        'hace_tiempo' => 'fuerza',
    ];

    /** [series, repeticiones, descanso] de los básicos y de los accesorios, por nivel. */
    private const DOSIS = [
        'nunca' => ['basico' => [3, '12 a 15', 75], 'accesorio' => [2, '12 a 15', 60]],
        'algo' => ['basico' => [4, '8 a 10', 90], 'accesorio' => [3, '10 a 12', 60]],
        'hace_tiempo' => ['basico' => [4, '6 a 8', 120], 'accesorio' => [4, '8 a 10', 75]],
    ];

    /**
     * Los que trabajan un músculo solo, aunque ayuden otros: van después de
     * los básicos y con menos peso.
     */
    private const ACCESORIOS = '/Pullover|Encogimientos|Pájaros|brazos rectos|Aperturas|Cruce de poleas|Elevaciones laterales|Face pull|Curl|Extensión|Press francés|Abductores|Patada|Elevación de talones|Hiperextensiones/u';

    /** Cuántos ejercicios lleva un día armado con el catálogo. */
    private const CUANTOS = ['pecho' => 5, 'espalda' => 5, 'piernas' => 6, 'hombros_brazos' => 6, 'torso_pecho' => 6, 'torso_espalda' => 6];

    /**
     * Lo que marcó en «estos últimos días», desde la dirección: ?hice[]=…
     * (varios) o el ?ayer=… de antes (QR y enlaces viejos). Una lista vacía
     * es «nada, descansé»; null, que no contestó.
     *
     * @return list<string>|null
     */
    public static function hechos(mixed $hice, mixed $ayer = null): ?array
    {
        $valores = match (true) {
            is_array($hice) => $hice,
            is_string($hice) && $hice !== '' => [$hice],
            is_string($ayer) && $ayer !== '' => [$ayer],
            default => [],
        };
        $validos = array_values(array_unique(array_filter($valores, fn ($v) => is_string($v) && isset(self::HICE[$v]))));

        if ($validos === []) {
            return null;
        }

        // «Nada» junto con algo más: vale lo otro.
        return array_values(array_diff($validos, ['nada']));
    }

    /** @param list<string>|null $hechos */
    public static function respondido(int $dias, string $nivel, ?array $hechos): bool
    {
        return in_array($dias, self::DIAS, true) && isset(self::NIVELES[$nivel]) && $hechos !== null;
    }

    /** «nada», «pecho» o una lista: siempre una lista de lo hecho. */
    private static function comoLista(array|string $hice): array
    {
        return array_values(array_diff((array) $hice, ['nada', '']));
    }

    /**
     * Si hoy conviene dejar descansar ese grupo: lo marcó, o cansa algún
     * músculo de lo que marcó. El cardio choca solo con el cardio, y
     * estirar con nada.
     *
     * @param list<string> $hechos
     */
    public static function choca(string $grupo, array $hechos): bool
    {
        if (in_array($grupo, $hechos, true)) {
            return true;
        }

        $cansados = array_merge([], ...array_map(fn ($h) => self::CARGA[$h] ?? [], $hechos));

        return array_intersect(self::CARGA[$grupo] ?? [], $cansados) !== [];
    }

    /**
     * Qué conviene hoy: lo que sigue en la vuelta de sus días después de lo
     * último que entrenó, sin nada que choque con lo de estos días. Si todo
     * choca, otra combinación; y si ya entrenó de todo, cardio o estirar.
     *
     * (La página hace la misma cuenta en JavaScript con reglas().)
     */
    public static function sugerencia(array|string $hice, int $dias): string
    {
        $hechos = self::comoLista($hice);
        $vuelta = self::VUELTAS[$dias] ?? self::VUELTAS[3];

        $ultimo = -1;
        foreach ($vuelta as $i => $g) {
            if (in_array($g, $hechos, true)) {
                $ultimo = $i;
            }
        }

        for ($i = 1; $i <= count($vuelta); $i++) {
            $g = $vuelta[($ultimo + $i) % count($vuelta)];

            if (! self::choca($g, $hechos)) {
                return $g;
            }
        }

        foreach (self::RESPALDO as $g) {
            if (! self::choca($g, $hechos)) {
                return $g;
            }
        }

        return 'movilidad';
    }

    /** Lo que necesita el JavaScript de la página para sugerir sin volver a preguntar. */
    public static function reglas(): array
    {
        return ['vueltas' => self::VUELTAS, 'respaldo' => self::RESPALDO, 'carga' => self::CARGA];
    }

    /**
     * El entrenamiento de hoy, listo para mostrar.
     *
     * $hice: lo que entrenó estos últimos días (una lista, o «nada»).
     *
     * @return array{grupo:string, titulo:string, foco:?string, lineas:list<array<string,mixed>>, principales:list<string>, secundarios:list<string>, rutina:?Rutina, dia:?int, calentamiento:list<array<string,mixed>>, estiramientos:list<array<string,mixed>>, movilidad:list<array<string,mixed>>, minutos:?int}
     */
    public static function armar(int $dias, string $nivel, array|string $hice, string $hoy): array
    {
        if ($hoy === 'movilidad') {
            return self::diaDeMovilidad();
        }

        $hechos = self::comoLista($hice);
        $rutina = RutinaSugerida::buscar(self::OBJETIVO_POR_NIVEL[$nivel] ?? 'empezar', $nivel, $dias);
        $elegido = $rutina ? self::elegirDia(RutinaSugerida::dias($rutina), $hoy, $hechos) : null;

        if ($elegido) {
            $lineas = $elegido['lineas'];
            $titulo = $elegido['titulo'];
            $foco = $elegido['foco'];
            $numero = $elegido['numero'];
        } else {
            $lineas = self::desdeElCatalogo($hoy, $nivel);
            $titulo = self::GRUPOS[$hoy];
            $foco = null;
            $numero = null;
        }

        $lineas = self::conOpciones($lineas);

        $principales = array_values(array_unique(array_filter(array_column($lineas, 'principal'))));
        $secundarios = array_values(array_diff(array_unique(array_merge([], ...array_column($lineas, 'secundarios'))), $principales));

        return [
            'grupo' => $hoy,
            'titulo' => $titulo,
            'foco' => $foco,
            'lineas' => $lineas,
            'principales' => $principales,
            'secundarios' => $secundarios,
            'rutina' => $rutina,
            'dia' => $numero,
            'calentamiento' => [CalentamientoYEstiramiento::CARDIO_SUAVE, ...CalentamientoYEstiramiento::calentamiento($principales, $secundarios, $hoy)],
            'estiramientos' => CalentamientoYEstiramiento::estiramientos($principales, $secundarios),
            'movilidad' => [],
            'minutos' => null,
        ];
    }

    /** «Estirar y movilidad»: sin pesas, de los pies a la cabeza. */
    private static function diaDeMovilidad(): array
    {
        $m = CalentamientoYEstiramiento::rutinaDeMovilidad();
        $musculos = array_values(array_unique(array_merge(...array_column([...$m['movilidad'], ...$m['estiramientos']], 'musculos'))));

        return [
            'grupo' => 'movilidad',
            'titulo' => self::GRUPOS['movilidad'],
            'foco' => null,
            'lineas' => [],
            'principales' => $musculos,
            'secundarios' => [],
            'rutina' => null,
            'dia' => null,
            'calentamiento' => [CalentamientoYEstiramiento::CARDIO_SUAVE],
            'estiramientos' => $m['estiramientos'],
            'movilidad' => $m['movilidad'],
            'minutos' => $m['minutos'],
        ];
    }

    /**
     * El día de la rutina que mejor calza con hoy y menos pisa ayer, o null si
     * ninguno trabaja de verdad lo que pidió.
     *
     * @param list<array<string,mixed>> $dias
     */
    public static function elegirDia(array $dias, string $hoy, array|string $hice): ?array
    {
        $hechos = self::comoLista($hice);

        if ($hoy === 'movilidad') {
            return null;
        }

        $mejor = null;
        $mejorPuntaje = -INF;

        foreach ($dias as $dia) {
            $perfil = self::perfil($dia['lineas']);
            $calza = self::calza($perfil, $hoy);

            if ($calza < ($hoy === 'cuerpo_completo' ? 0.75 : 0.5)) {
                continue;
            }

            $puntaje = $calza - self::pisa($perfil, $hechos, $hoy);

            if ($puntaje > $mejorPuntaje) {
                $mejor = $dia;
                $mejorPuntaje = $puntaje;
            }
        }

        // Un día que es casi todo lo ya hecho no sirve aunque traiga algo de hoy.
        return $mejorPuntaje > 0.25 ? $mejor : null;
    }

    /** De qué grupo es un ejercicio: pecho, espalda, piernas, hombros_brazos, core o cardio. */
    public static function grupoDe(?string $principal, ?string $zona): ?string
    {
        if ($zona === 'cardio') {
            return 'cardio';
        }

        return match ($principal) {
            'pecho' => 'pecho',
            'espalda', 'lumbar' => 'espalda',
            'cuadriceps', 'isquios', 'gluteos', 'pantorrillas' => 'piernas',
            'hombros', 'biceps', 'triceps' => 'hombros_brazos',
            'abdomen' => 'core',
            default => match ($zona) {
                'pecho', 'espalda', 'piernas' => $zona,
                'hombros', 'brazos' => 'hombros_brazos',
                'core' => 'core',
                default => null,
            },
        };
    }

    /**
     * Cuántos ejercicios de cada grupo trae un día.
     *
     * @param list<array<string,mixed>> $lineas
     * @return array<string,int|float>
     */
    private static function perfil(array $lineas): array
    {
        $perfil = ['pecho' => 0, 'espalda' => 0, 'piernas' => 0, 'hombros_brazos' => 0, 'core' => 0, 'cardio' => 0, 'total' => 0];
        // El mismo peso por músculo principal (solo los de fuerza), para las
        // combinaciones y para ver qué pisa de lo ya hecho.
        $musculos = [];
        $primero = true;

        foreach ($lineas as $l) {
            $grupo = self::grupoDe($l['principal'] ?? null, $l['zona'] ?? null);
            $perfil['total']++;

            if ($grupo) {
                // El primer ejercicio de fuerza dice de qué es el día: en un
                // día de empuje (pecho, hombros y tríceps a partes iguales)
                // manda el press de pecho con que empieza.
                $deFuerza = ! in_array($grupo, ['core', 'cardio'], true);
                $peso = $deFuerza && $primero ? 1.5 : 1;
                $perfil[$grupo] += $peso;
                $primero = $primero && ! $deFuerza;

                if ($deFuerza && ! empty($l['principal'])) {
                    $musculos[$l['principal']] = ($musculos[$l['principal']] ?? 0) + $peso;
                }
            }
        }

        $perfil['musculos'] = $musculos;

        $perfil['fuerza'] = $perfil['pecho'] + $perfil['espalda'] + $perfil['piernas'] + $perfil['hombros_brazos'];

        return $perfil;
    }

    /** Qué tanto trabaja el día lo que pidió, de 0 a 1. */
    private static function calza(array $p, string $hoy): float
    {
        if ($p['total'] === 0) {
            return 0;
        }

        if ($hoy === 'cardio') {
            return ($p['cardio'] + $p['core'] * 0.5) / $p['total'];
        }

        if ($hoy === 'cuerpo_completo') {
            $cubre = count(array_filter([$p['pecho'], $p['espalda'], $p['piernas'], $p['hombros_brazos']]));

            return $p['piernas'] > 0 && $p['pecho'] + $p['espalda'] > 0 ? $cubre / 4 : 0;
        }

        // Torso: pecho y espalda los dos, casi nada de pierna, y más de lo
        // que pidió que de lo otro.
        if ($hoy === 'torso_pecho' || $hoy === 'torso_espalda') {
            [$mas, $menos] = $hoy === 'torso_pecho' ? ['pecho', 'espalda'] : ['espalda', 'pecho'];
            $arriba = $p['pecho'] + $p['espalda'] + $p['hombros_brazos'];

            return $p['fuerza'] > 0 && $p[$menos] > 0 && $p[$mas] >= 1.5 * $p[$menos] && $p['piernas'] / $p['fuerza'] < 0.25
                ? $arriba / $p['fuerza']
                : 0;
        }

        // Las combinaciones: el grupo grande manda en el día y el chico
        // aparece como principal de algún ejercicio.
        $m = fn (string $musculo) => $p['musculos'][$musculo] ?? 0;
        $manda = fn (string $grupo) => $p[$grupo] >= max($p['pecho'], $p['espalda'], $p['piernas'], $p['hombros_brazos']);

        if ($p['fuerza'] <= 0) {
            return 0;
        }

        return match ($hoy) {
            // El grupo chico, con dos ejercicios por lo menos: si no, es un día del grande.
            'pecho_triceps' => $manda('pecho') && $m('triceps') >= 2 ? min(1, ($p['pecho'] + $m('triceps')) / $p['fuerza']) : 0,
            'espalda_biceps' => $manda('espalda') && $m('biceps') >= 2 ? min(1, ($p['espalda'] + $m('biceps')) / $p['fuerza']) : 0,
            // Dos de glúteo por lo menos: si no, es un día de piernas común.
            'piernas_gluteos' => $manda('piernas') && $m('gluteos') >= 2 ? $p['piernas'] / $p['fuerza'] : 0,
            'hombros_abdomen' => $m('hombros') >= 2 && $p['core'] >= 2 && $m('hombros') >= max($m('pecho'), $m('espalda'), $p['piernas'])
                ? ($m('hombros') + $p['core']) / ($p['fuerza'] + $p['core'])
                : 0,
            'movilidad' => 0,
            // Lo que pidió tiene que ser lo que más trabaja el día.
            default => $manda($hoy) ? $p[$hoy] / $p['fuerza'] : 0,
        };
    }

    /**
     * Qué tanto vuelve a cargar lo hecho estos días, de 0 a 1: la parte de
     * los ejercicios de fuerza cuyo músculo principal ya se cansó (a medias
     * si fue en un día de cuerpo completo). Si hoy pide justo algo que ya
     * hizo, eso no se cuenta: lo eligió a sabiendas.
     *
     * @param list<string> $hechos
     */
    private static function pisa(array $p, array $hechos, string $hoy): float
    {
        if ($p['total'] === 0 || $hechos === []) {
            return 0;
        }

        $cansados = [];

        foreach ($hechos as $h) {
            foreach (self::CARGA[$h] ?? [] as $musculo) {
                $cansados[$musculo] = max($cansados[$musculo] ?? 0, $h === 'cuerpo_completo' ? 0.5 : 1);
            }
        }

        if (in_array($hoy, $hechos, true)) {
            $cansados = array_diff_key($cansados, array_flip(self::CARGA[$hoy] ?? []));
        }

        $pisa = 0;

        if ($p['fuerza'] > 0) {
            $suma = 0;

            foreach ($p['musculos'] as $musculo => $peso) {
                $suma += $peso * ($cansados[$musculo] ?? 0);
            }

            $pisa = min(1, $suma / $p['fuerza']);
        }

        if (in_array('cardio', $hechos, true) && $hoy !== 'cardio') {
            $pisa = max($pisa, $p['cardio'] / $p['total']);
        }

        return $pisa;
    }

    /**
     * Un día armado con los ejercicios activos de la sala.
     *
     * @return list<array<string,mixed>>
     */
    public static function desdeElCatalogo(string $grupo, string $nivel): array
    {
        $todos = Ejercicio::where('activo', true)->orderBy('orden')->orderBy('nombre')->get()
            ->map(fn (Ejercicio $e) => [
                'e' => $e,
                'grupo' => self::grupoDe($e->grupos()['principal'], $e->zona),
                'basico' => $e->grupos()['secundarios'] !== [] && ! preg_match(self::ACCESORIOS, $e->nombre),
            ] + $e->grupos());

        $de = fn (string $g) => self::ordenar($todos->where('grupo', $g)->values()->all(), $nivel);

        // Zona media: solo los de abdomen, y no dos planchas.
        $zonaMedia = function (int $cuantos) use ($de) {
            $elegidos = [];

            foreach ($de('core') as $c) {
                $tipo = strtok($c['e']->nombre, ' ');

                if ($c['e']->zona === 'core' && ! isset($elegidos[$tipo]) && count($elegidos) < $cuantos) {
                    $elegidos[$tipo] = $c;
                }
            }

            return array_values($elegidos);
        };

        if ($grupo === 'cardio') {
            $elegidos = [...array_slice(self::variados($de('cardio'), 2), 0, 2), ...$zonaMedia(3)];
        } elseif ($grupo === 'cuerpo_completo') {
            // Uno de cada parte: piernas, pecho, espalda, la parte de atrás de
            // la pierna, hombros y zona media.
            $piernas = self::variados($de('piernas'), 2);
            $elegidos = array_values(array_filter([
                $piernas[0] ?? null,
                $de('pecho')[0] ?? null,
                $de('espalda')[0] ?? null,
                $piernas[1] ?? null,
                $de('hombros_brazos')[0] ?? null,
                $zonaMedia(1)[0] ?? null,
            ]));
        } elseif ($grupo === 'torso_pecho' || $grupo === 'torso_espalda') {
            // Tres de lo que pidió, uno de lo otro y dos de hombros y brazos:
            // con más pecho, hombro y tríceps; con más espalda, bíceps.
            [$mas, $menos] = $grupo === 'torso_pecho' ? ['pecho', 'espalda'] : ['espalda', 'pecho'];
            $fuerte = self::variados(array_values(array_filter($de($mas), fn ($c) => $c['basico'])), 3);
            $otro = self::variados(array_values(array_filter($de($menos), fn ($c) => $c['basico'])), 1);
            $brazos = array_values(array_filter($de('hombros_brazos'), fn ($c) => $grupo === 'torso_pecho'
                ? array_intersect([$c['principal']], ['hombros', 'triceps']) !== []
                : $c['principal'] === 'biceps'));
            $elegidos = array_values(array_filter([
                ...$fuerte,
                $otro[0] ?? null,
                ...self::variados($brazos, 2),
            ]));
        } elseif (in_array($grupo, ['pecho_triceps', 'espalda_biceps', 'piernas_gluteos', 'hombros_abdomen'], true)) {
            $elegidos = self::combinacion($grupo, $de, $zonaMedia, $nivel);
        } else {
            // Dos o tres básicos sin repetir músculo, y los accesorios hasta completar.
            $cuantos = self::CUANTOS[$grupo] ?? 5;
            // Sin los de cuerpo completo (el swing, los burpees): piden más técnica.
            $candidatos = array_values(array_filter($de($grupo), fn ($c) => $c['e']->zona !== 'cuerpo_completo'));
            $basicos = array_values(array_filter($candidatos, fn ($c) => $c['basico']));
            $accesorios = array_values(array_filter($candidatos, fn ($c) => ! $c['basico']));
            $elegidos = self::variados($basicos, $grupo === 'hombros_brazos' ? 2 : 3);
            $elegidos = [...$elegidos, ...self::variados($accesorios, $cuantos - count($elegidos))];

            foreach ($basicos as $c) {
                if (count($elegidos) < $cuantos && ! in_array($c, $elegidos, true)) {
                    $elegidos[] = $c;
                }
            }

            $elegidos = self::ordenar($elegidos, $nivel);
        }

        $basicos = 0;
        $cardios = 0;

        return array_map(function (array $c) use ($nivel, &$basicos, &$cardios) {
            $e = $c['e'];
            $basico = $c['basico'];
            $dosis = self::DOSIS[$nivel] ?? self::DOSIS['algo'];
            [$series, $reps, $descanso] = $dosis[$basico ? 'basico' : 'accesorio'];
            $nota = $e->indicacion;

            if ($c['grupo'] === 'cardio') {
                // El segundo, más corto: es para terminar.
                $minutos = $cardios++ ? 10 : (['nunca' => 15, 'algo' => 20, 'hace_tiempo' => 25][$nivel] ?? 20);
                [$series, $reps, $descanso] = [1, "{$minutos} minutos", 0];

                if (str_contains($e->nombre, 'Intervalos')) {
                    $reps = (['nunca' => 6, 'algo' => 8, 'hace_tiempo' => 10][$nivel] ?? 8) . ' rondas';
                }
            } elseif ($c['grupo'] === 'core') {
                [$series, $descanso] = [3, 45];
                $reps = match (true) {
                    str_contains($e->nombre, 'Plancha') => ['nunca' => '20 a 30 segundos', 'algo' => '30 a 45 segundos', 'hace_tiempo' => '45 a 60 segundos'][$nivel] ?? '30 segundos',
                    str_contains($e->nombre, 'Escaladores') => '30 segundos',
                    in_array($e->nombre, ['Dead bug', 'Pallof press'], true) => '10 por lado',
                    $e->nombre === 'Paseo del granjero' => '30 metros',
                    default => '12 a 15',
                };
            } elseif ($basico && $basicos++ === 0 && $nivel === 'hace_tiempo') {
                // El primer básico del que entrena hace años, con una serie más.
                [$series, $descanso] = [5, 150];
            }

            $reps .= self::porLado($e->nombre);
            $alternativa = RutinasDeEjemplo::ALTERNATIVAS[$e->nombre] ?? null;

            return [
                'nombre' => $e->nombre,
                'dosis' => RutinaSugerida::dosis($series, $reps),
                'corta' => RutinaSugerida::corta($series, $reps),
                'series' => $series,
                'repeticiones' => $reps,
                'descanso' => $descanso,
                'nota' => $nota,
                'alternativa' => $alternativa && Ejercicio::where('activo', true)->where('nombre', $alternativa)->exists() ? $alternativa : null,
                'imagen' => $e->urlDeImagen(),
                'principal' => $c['principal'],
                'secundarios' => $c['secundarios'],
                'zona' => $e->zona,
            ];
        }, $elegidos);
    }

    /**
     * Las combinaciones clásicas, del grupo grande al chico:
     * pecho y tríceps, 3 + 2; espalda y bíceps, 3 + 2; piernas y glúteos,
     * cuádriceps, glúteo, isquios, otro de cuádriceps y otro de glúteo;
     * hombros y abdomen, 3 de hombro y 2 o 3 de abdomen.
     *
     * @param callable(string):list<array<string,mixed>> $de
     * @param callable(int):list<array<string,mixed>> $zonaMedia
     * @return list<array<string,mixed>>
     */
    private static function combinacion(string $grupo, callable $de, callable $zonaMedia, string $nivel): array
    {
        // Sin los de cuerpo completo (el swing, los burpees): piden más técnica.
        $del = fn (string $g, string $musculo) => array_values(array_filter($de($g),
            fn ($c) => $c['principal'] === $musculo && $c['e']->zona !== 'cuerpo_completo'));
        $basicos = fn (array $lista) => array_values(array_filter($lista, fn ($c) => $c['basico']));
        $accesorios = fn (array $lista) => array_values(array_filter($lista, fn ($c) => ! $c['basico']));

        // Tres del grupo grande, uno por hueco: «basico», «accesorio» o un
        // patrón del nombre. Lo que falte, con lo que haya.
        $tres = function (array $lista, array $huecos) {
            $elegidos = [];

            foreach ($huecos as $hueco) {
                foreach ($lista as $c) {
                    $sirve = match ($hueco) {
                        'basico' => $c['basico'],
                        'accesorio' => ! $c['basico'],
                        default => (bool) preg_match($hueco, $c['e']->nombre),
                    };

                    if ($sirve && ! in_array($c, $elegidos, true)) {
                        $elegidos[] = $c;
                        break;
                    }
                }
            }

            foreach ($lista as $c) {
                if (count($elegidos) < 3 && ! in_array($c, $elegidos, true)) {
                    $elegidos[] = $c;
                }
            }

            return $elegidos;
        };

        // Del chico, uno de cada tipo si hay: un básico y un accesorio.
        $dos = function (array $lista) use ($basicos, $accesorios) {
            $elegidos = array_values(array_filter([$basicos($lista)[0] ?? null, $accesorios($lista)[0] ?? null]));

            foreach ($lista as $c) {
                if (count($elegidos) < 2 && ! in_array($c, $elegidos, true)) {
                    $elegidos[] = $c;
                }
            }

            return $elegidos;
        };

        return match ($grupo) {
            'pecho_triceps' => [...$tres($del('pecho', 'pecho'), ['basico', 'basico', 'accesorio']), ...$dos($del('hombros_brazos', 'triceps'))],
            // Un jalón (o dominadas) y dos remos.
            'espalda_biceps' => [...$tres($del('espalda', 'espalda'), ['/Jalón al pecho|Dominadas|Jalón con agarre/u', '/^Remo/u', '/^Remo/u']), ...$dos($del('hombros_brazos', 'biceps'))],
            'piernas_gluteos' => (function () use ($del, $basicos, $accesorios) {
                $cuad = $del('piernas', 'cuadriceps');
                $gluteo = $del('piernas', 'gluteos');
                $isquios = $del('piernas', 'isquios');

                return array_values(array_filter([
                    $basicos($cuad)[0] ?? $cuad[0] ?? null,
                    $basicos($gluteo)[0] ?? $gluteo[0] ?? null,
                    $basicos($isquios)[0] ?? $isquios[0] ?? null,
                    $basicos($cuad)[1] ?? null,
                    $accesorios($gluteo)[0] ?? $basicos($gluteo)[1] ?? null,
                ]));
            })(),
            'hombros_abdomen' => [
                // Un press, una elevación lateral y uno para la parte de atrás.
                ...$tres($del('hombros_brazos', 'hombros'), ['basico', '/Elevaciones laterales/u', '/Face pull|Pájaros|invertidas/u']),
                ...$zonaMedia($nivel === 'nunca' ? 2 : 3),
            ],
        };
    }

    /**
     * OTRAS OPCIONES PARA CADA EJERCICIO: hasta tres de la sala que trabajan
     * el mismo músculo, empezando por su variante. Si la máquina está ocupada
     * o el ejercicio no le acomoda, tiene con qué cambiarlo sin preguntar.
     *
     * @param list<array<string,mixed>> $lineas
     * @return list<array<string,mixed>>
     */
    public static function conOpciones(array $lineas): array
    {
        // Sin los de cardio ni los de cuerpo completo: el remo ergómetro no
        // reemplaza un remo con mancuerna.
        $catalogo = Ejercicio::where('activo', true)->whereNotIn('zona', ['cardio', 'cuerpo_completo'])
            ->orderBy('orden')->orderBy('nombre')->get();
        $enElDia = array_column($lineas, 'nombre');
        // Lo ya ofrecido en otro ejercicio no se repite: tres de espalda
        // seguidos mostraban las mismas tres opciones.
        $ofrecidos = [];

        return array_map(function (array $l) use ($catalogo, $enElDia, &$ofrecidos) {
            if (! $l['principal'] || in_array($l['zona'], ['cardio', 'core'], true)) {
                return $l + ['opciones' => []];
            }

            $todos = $catalogo
                ->filter(fn (Ejercicio $e) => ! in_array($e->nombre, $enElDia, true)
                    && ($e->grupos()['principal'] ?? null) === $l['principal'])
                // La variante primero, después los que mueven los mismos
                // músculos, y entre iguales el que toca hoy.
                ->sortBy(fn (Ejercicio $e) => [
                    $e->nombre === $l['alternativa'] ? 0 : 1,
                    -count(array_intersect($e->grupos()['secundarios'], $l['secundarios'])),
                    self::delDia($e->nombre),
                ])
                ->values();

            // Primero los que no se ofrecieron arriba; si no alcanzan para dos,
            // se repite alguno antes que dejarlo sin opciones.
            $nuevos = $todos->reject(fn (Ejercicio $e) => in_array($e->nombre, $ofrecidos, true))->take(2);
            $parecidos = $nuevos->concat($todos->diff($nuevos))->take(max(2, $nuevos->count()))
                ->take(3)
                ->map(fn (Ejercicio $e) => [
                    'nombre' => $e->nombre,
                    'imagen' => $e->urlDeImagen(),
                    'principal' => $e->grupos()['principal'],
                    'secundarios' => $e->grupos()['secundarios'],
                    'zona' => $e->zona,
                ])
                ->values()
                ->all();

            array_push($ofrecidos, ...array_column($parecidos, 'nombre'));

            return $l + ['opciones' => $parecidos];
        }, $lineas);
    }

    /**
     * Un número que cambia cada día para cada ejercicio: entre ejercicios
     * igual de buenos, hoy sale uno y mañana otro, sin azar (el mismo día
     * siempre muestra lo mismo, también al volver atrás).
     */
    public static function delDia(string $nombre): int
    {
        return crc32(now()->toDateString() . '|' . $nombre);
    }

    /** « por pierna» o « por brazo» para los que se hacen de a un lado. */
    private static function porLado(string $nombre): string
    {
        return match (true) {
            str_contains($nombre, 'una mano') => ' por brazo',
            (bool) preg_match('/Zancadas|búlgara|Subida al cajón|Patada de glúteo/u', $nombre) => ' por pierna',
            default => '',
        };
    }

    /**
     * Los que mueven más músculos primero; entre iguales, el equipo que mejor
     * le queda al nivel (máquinas para quien empieza, barra para quien entrena
     * hace años) y después el orden de la sala.
     *
     * @param list<array<string,mixed>> $candidatos
     * @return list<array<string,mixed>>
     */
    private static function ordenar(array $candidatos, string $nivel): array
    {
        $equipo = [
            'nunca' => ['maquina' => 0, 'mancuernas' => 1, 'propio_peso' => 2, 'cardio' => 2, 'barra' => 3],
            'algo' => ['maquina' => 0, 'mancuernas' => 0, 'barra' => 1, 'propio_peso' => 2, 'cardio' => 2],
            'hace_tiempo' => ['barra' => 0, 'mancuernas' => 1, 'maquina' => 2, 'propio_peso' => 3, 'cardio' => 2],
        ][$nivel] ?? [];

        // Entre iguales, el que toca hoy: así no salen siempre los mismos.
        usort($candidatos, fn ($a, $b) => [(int) $b['basico'], $equipo[$a['e']->equipo] ?? 2, self::delDia($a['e']->nombre)]
            <=> [(int) $a['basico'], $equipo[$b['e']->equipo] ?? 2, self::delDia($b['e']->nombre)]);

        return $candidatos;
    }

    /**
     * Hasta $cuantos sin repetir músculo mientras se pueda: una vuelta con el
     * mejor de cada músculo, otra con el segundo, y así.
     *
     * @param list<array<string,mixed>> $ordenados
     * @return list<array<string,mixed>>
     */
    private static function variados(array $ordenados, int $cuantos): array
    {
        $porMusculo = [];

        foreach ($ordenados as $c) {
            $porMusculo[$c['principal'] ?? '-'][] = $c;
        }

        $elegidos = [];

        for ($vuelta = 0; count($elegidos) < $cuantos && $vuelta < count($ordenados); $vuelta++) {
            foreach ($porMusculo as $lista) {
                if (isset($lista[$vuelta]) && count($elegidos) < $cuantos) {
                    $elegidos[] = $lista[$vuelta];
                }
            }
        }

        return $elegidos;
    }
}
