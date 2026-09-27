<?php

namespace App\Support;

use App\Models\Ejercicio;
use App\Models\Rutina;
use Illuminate\Support\Facades\DB;

/**
 * Las rutinas de la sala: el catálogo de ejercicios y las variantes.
 *
 * Son guías generales, iguales para todos, para quien lee el QR y no sabe qué
 * hacer: cuatro objetivos, tres niveles y de 2 a 6 días. No son planes
 * personales. Antes de publicarlas conviene que las revise el entrenador de la
 * sala y ajuste lo que no calce con las máquinas que hay.
 *
 * CRITERIOS con que están armadas:
 *
 *  · Quien nunca entrenó hace cuerpo completo, casi todo en máquinas —la
 *    máquina guía el movimiento—, 2 o 3 series y repeticiones altas.
 *  · Fuerza sube de peso con menos repeticiones y más descanso; bajar de peso
 *    usa circuitos con poco descanso y cardio al final.
 *  · Cada línea con máquina trae una ALTERNATIVA por si está ocupada.
 *  · Descansos: 45–60 s en circuitos y accesorios, 90–120 s en los básicos
 *    pesados.
 *
 * Se cargan con `php artisan rutinas:ejemplos`. Se puede volver a correr: los
 * ejercicios se buscan por nombre y las rutinas se rehacen enteras.
 */
class RutinasDeEjemplo
{
    /** nombre => [zona, equipo, indicación] */
    public const EJERCICIOS = [
        // Pecho
        'Press de pecho en máquina' => ['pecho', 'maquina', 'Espalda pegada al respaldo y codos un poco bajo los hombros. Empuja sin trabar los codos.'],
        'Press de banca con barra' => ['pecho', 'barra', 'Pies firmes, omóplatos juntos. Baja la barra al medio del pecho y empuja. Pide a alguien que te asista.'],
        'Press inclinado con mancuernas' => ['pecho', 'mancuernas', 'Banco a unos 30°. Baja las mancuernas a los lados del pecho y súbelas juntándolas arriba.'],
        'Aperturas en máquina' => ['pecho', 'maquina', 'Codos apenas doblados y fijos. Junta los brazos adelante sin chocar las manillas.'],
        'Flexiones de brazos' => ['pecho', 'propio_peso', 'Cuerpo en línea de cabeza a talones. Si cuesta, apoya las rodillas o las manos en un banco.'],

        // Espalda
        'Jalón al pecho' => ['espalda', 'maquina', 'Pecho arriba. Tira la barra hacia la clavícula llevando los codos hacia abajo, sin balancearte.'],
        'Remo sentado en polea' => ['espalda', 'maquina', 'Espalda recta. Tira hacia el ombligo juntando los omóplatos; vuelve despacio.'],
        'Remo con mancuerna a una mano' => ['espalda', 'mancuernas', 'Rodilla y mano en el banco. Sube la mancuerna hacia la cadera, sin girar el tronco.'],
        'Remo con barra' => ['espalda', 'barra', 'Inclínate con la espalda recta y rodillas suaves. Lleva la barra al ombligo.'],
        'Dominadas asistidas' => ['espalda', 'maquina', 'Agarre un poco más ancho que los hombros. Sube hasta pasar la barbilla y baja completo.'],

        // Piernas
        'Prensa de piernas' => ['piernas', 'maquina', 'Pies al ancho de los hombros. Baja hasta que las rodillas lleguen cerca del pecho sin despegar la espalda baja.'],
        'Sentadilla goblet' => ['piernas', 'mancuernas', 'Mancuerna pegada al pecho. Baja como sentándote, con el pecho arriba y las rodillas hacia afuera.'],
        'Sentadilla con barra' => ['piernas', 'barra', 'Barra sobre la espalda alta. Baja con control hasta que los muslos queden paralelos y sube empujando el suelo.'],
        'Extensión de cuádriceps' => ['piernas', 'maquina', 'Ajusta el respaldo para que la rodilla calce con el eje. Sube, aprieta un segundo y baja despacio.'],
        'Curl femoral' => ['piernas', 'maquina', 'Cadera pegada al asiento o al banco. Dobla las rodillas llevando el rodillo a los glúteos.'],
        'Zancadas con mancuernas' => ['piernas', 'mancuernas', 'Paso largo; baja hasta que la rodilla de atrás casi toque el suelo. Alterna piernas.'],
        'Peso muerto rumano con mancuernas' => ['piernas', 'mancuernas', 'Rodillas suaves. Lleva la cadera atrás bajando las mancuernas pegadas a las piernas; espalda recta.'],
        'Peso muerto rumano con barra' => ['piernas', 'barra', 'Igual que con mancuernas: cadera atrás, barra pegada a las piernas, espalda recta.'],
        'Hip thrust' => ['piernas', 'barra', 'Espalda alta en el banco. Sube la cadera apretando los glúteos hasta quedar en línea recta.'],
        'Puente de glúteos' => ['piernas', 'propio_peso', 'Acostado, pies apoyados. Sube la cadera apretando los glúteos y baja despacio.'],
        'Elevación de talones' => ['piernas', 'maquina', 'Baja el talón hasta estirar y sube lo más alto posible. Pausa arriba.'],
        'Subida al cajón' => ['piernas', 'mancuernas', 'Pie completo en el cajón. Sube empujando con esa pierna, sin impulsarte con la de abajo.'],

        // Hombros
        'Press de hombros en máquina' => ['hombros', 'maquina', 'Espalda pegada al respaldo. Empuja hacia arriba sin arquear la espalda baja.'],
        'Press de hombros con mancuernas' => ['hombros', 'mancuernas', 'Sentado con respaldo. Sube las mancuernas sobre la cabeza y baja hasta la altura de las orejas.'],
        'Elevaciones laterales' => ['hombros', 'mancuernas', 'Sube los brazos por los lados hasta la altura de los hombros, codos apenas doblados. Sin impulso.'],
        'Face pull en polea' => ['hombros', 'maquina', 'Cuerda a la altura de la cara. Tira hacia los ojos abriendo los codos. Cuida los hombros.'],

        // Brazos
        'Curl de bíceps con mancuernas' => ['brazos', 'mancuernas', 'Codos pegados al cuerpo. Sube girando la palma hacia arriba y baja completo.'],
        'Curl martillo' => ['brazos', 'mancuernas', 'Palmas mirándose. Sube sin mover los codos.'],
        'Curl con barra' => ['brazos', 'barra', 'Codos quietos a los lados. Sube sin balancear la espalda.'],
        'Extensión de tríceps en polea' => ['brazos', 'maquina', 'Codos pegados al cuerpo. Estira los brazos hacia abajo y vuelve hasta 90°.'],
        'Fondos entre bancos' => ['brazos', 'propio_peso', 'Manos en el banco detrás de ti. Baja doblando los codos hacia atrás y sube.'],
        'Press francés con mancuerna' => ['brazos', 'mancuernas', 'Acostado, mancuerna sobre la frente con las dos manos. Dobla solo los codos.'],

        // Core
        'Plancha' => ['core', 'propio_peso', 'Antebrazos en el suelo, cuerpo recto. Aprieta abdomen y glúteos; no dejes caer la cadera.'],
        'Plancha lateral' => ['core', 'propio_peso', 'Apoyado en un antebrazo, cadera arriba y cuerpo en línea. Cambia de lado.'],
        'Dead bug' => ['core', 'propio_peso', 'Boca arriba, brazos y piernas arriba. Estira brazo y pierna contrarios sin despegar la espalda baja.'],
        'Crunch en polea' => ['core', 'maquina', 'De rodillas frente a la polea. Encorva la espalda llevando los codos a los muslos.'],
        'Elevación de piernas' => ['core', 'propio_peso', 'Acostado o colgado. Sube las piernas sin arquear la espalda y baja despacio.'],
        'Pallof press' => ['core', 'maquina', 'De lado a la polea. Empuja las manos al frente sin dejar que el cuerpo gire.'],

        // Cardio y cuerpo completo
        'Caminata inclinada en trotadora' => ['cardio', 'cardio', 'Inclinación 8 a 12 y paso rápido, sin sujetarte de las barras.'],
        'Bicicleta estática' => ['cardio', 'cardio', 'Ritmo en que puedas hablar entrecortado.'],
        'Elíptica' => ['cardio', 'cardio', 'Ritmo constante, espalda derecha, empuja también con los brazos.'],
        'Intervalos en bicicleta' => ['cardio', 'cardio', '30 segundos fuerte y 90 suave. Repite.'],
        'Remo ergómetro' => ['cardio', 'cardio', 'Empuja primero con las piernas, después tira con los brazos. Espalda recta.'],
        'Burpees' => ['cuerpo_completo', 'propio_peso', 'Agáchate, lleva los pies atrás, vuelve y salta. Si cuesta, sin salto ni flexión.'],
        'Paseo del granjero' => ['cuerpo_completo', 'mancuernas', 'Una mancuerna pesada en cada mano. Camina erguido, hombros atrás, pasos cortos.'],
    ];

    /**
     * Si la máquina está ocupada, esto.
     *
     * @var array<string,string>
     */
    public const ALTERNATIVAS = [
        'Press de pecho en máquina' => 'Flexiones de brazos',
        'Press de banca con barra' => 'Press de pecho en máquina',
        'Aperturas en máquina' => 'Press inclinado con mancuernas',
        'Jalón al pecho' => 'Remo con mancuerna a una mano',
        'Dominadas asistidas' => 'Jalón al pecho',
        'Remo sentado en polea' => 'Remo con mancuerna a una mano',
        'Remo con barra' => 'Remo sentado en polea',
        'Prensa de piernas' => 'Sentadilla goblet',
        'Sentadilla con barra' => 'Prensa de piernas',
        'Extensión de cuádriceps' => 'Zancadas con mancuernas',
        'Curl femoral' => 'Peso muerto rumano con mancuernas',
        'Hip thrust' => 'Puente de glúteos',
        'Elevación de talones' => 'Subida al cajón',
        'Press de hombros en máquina' => 'Press de hombros con mancuernas',
        'Face pull en polea' => 'Elevaciones laterales',
        'Extensión de tríceps en polea' => 'Fondos entre bancos',
        'Crunch en polea' => 'Dead bug',
        'Pallof press' => 'Plancha lateral',
        'Caminata inclinada en trotadora' => 'Elíptica',
        'Bicicleta estática' => 'Elíptica',
        'Elíptica' => 'Bicicleta estática',
        'Intervalos en bicicleta' => 'Remo ergómetro',
        'Remo ergómetro' => 'Intervalos en bicicleta',
    ];

    /**
     * Las rutinas. Cada día: [título, foco, [[ejercicio, series, reps, descanso, nota?], …]].
     *
     * @return list<array<string,mixed>>
     */
    public static function rutinas(): array
    {
        // Días que se repiten entre rutinas, con su esquema de series.
        $cuerpoA = fn (int $s, string $r, int $d) => ['Cuerpo completo A', 'Todo el cuerpo con máquinas', [
            ['Prensa de piernas', $s, $r, $d],
            ['Press de pecho en máquina', $s, $r, $d],
            ['Jalón al pecho', $s, $r, $d],
            ['Curl femoral', $s, $r, $d],
            ['Press de hombros en máquina', 2, $r, $d],
            ['Plancha', 3, '20 a 30 segundos', 45],
        ]];
        $cuerpoB = fn (int $s, string $r, int $d) => ['Cuerpo completo B', 'Todo el cuerpo, otra variante', [
            ['Sentadilla goblet', $s, $r, $d],
            ['Remo sentado en polea', $s, $r, $d],
            ['Press inclinado con mancuernas', $s, $r, $d],
            ['Puente de glúteos', $s, $r, $d],
            ['Elevaciones laterales', 2, '12 a 15', 45],
            ['Dead bug', 3, '8 por lado', 45],
        ]];
        $cuerpoC = fn (int $s, string $r, int $d) => ['Cuerpo completo C', 'Piernas y espalda con peso libre', [
            ['Zancadas con mancuernas', $s, $r . ' por pierna', $d],
            ['Remo con mancuerna a una mano', $s, $r . ' por brazo', $d],
            ['Flexiones de brazos', $s, 'las que salgan con buena técnica', $d],
            ['Peso muerto rumano con mancuernas', $s, $r, $d],
            ['Curl de bíceps con mancuernas', 2, '12', 45],
            ['Extensión de tríceps en polea', 2, '12', 45],
        ]];
        $cardioSuave = fn (string $min) => ['Caminata inclinada en trotadora', 1, $min, 0, 'Al final, a ritmo que puedas mantener.'];

        return [
            // ---------- Estoy empezando ----------
            [
                'nombre' => 'Primeros pasos · 2 días',
                'objetivo' => 'empezar', 'nivel' => 'nunca', 'dias' => 2,
                'descripcion' => 'Para aprender los movimientos básicos con máquinas, que guían el gesto. Deja al menos un día entre sesiones. Las dos primeras semanas usa pesos livianos: lo que importa es la técnica.',
                'dias_de' => [$cuerpoA(2, '12 a 15', 60), $cuerpoB(2, '12 a 15', 60)],
            ],
            [
                'nombre' => 'Primeros pasos · 3 días',
                'objetivo' => 'empezar', 'nivel' => 'nunca', 'dias' => 3,
                'descripcion' => 'Tres días de cuerpo completo, por ejemplo lunes, miércoles y viernes. Cuando termines las series con facilidad, sube un poco el peso.',
                'dias_de' => [$cuerpoA(2, '12 a 15', 60), $cuerpoB(2, '12 a 15', 60), $cuerpoC(2, '10 a 12', 60)],
            ],
            [
                'nombre' => 'Base completa · 3 días',
                'objetivo' => 'empezar', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Si ya conoces las máquinas: cuerpo completo con una serie más y un poco más de peso. Termina cada sesión con 10 minutos de cardio suave.',
                'dias_de' => [
                    self::conCardio($cuerpoA(3, '10 a 12', 75), $cardioSuave('10 minutos')),
                    self::conCardio($cuerpoB(3, '10 a 12', 75), $cardioSuave('10 minutos')),
                    self::conCardio($cuerpoC(3, '10 a 12', 75), $cardioSuave('10 minutos')),
                ],
            ],
            [
                'nombre' => 'Primeros pasos · 4 días',
                'objetivo' => 'empezar', 'nivel' => 'algo', 'dias' => 4,
                'descripcion' => 'Parte de arriba y parte de abajo por separado, dos veces por semana cada una. Ideal lunes, martes, jueves y viernes.',
                'dias_de' => [
                    ['Tren superior', 'Pecho, espalda y hombros', [
                        ['Press de pecho en máquina', 3, '10 a 12', 75],
                        ['Jalón al pecho', 3, '10 a 12', 75],
                        ['Press de hombros en máquina', 2, '10 a 12', 60],
                        ['Remo sentado en polea', 3, '10 a 12', 75],
                        ['Curl de bíceps con mancuernas', 2, '12', 45],
                        ['Extensión de tríceps en polea', 2, '12', 45],
                    ]],
                    ['Tren inferior', 'Piernas, glúteos y zona media', [
                        ['Prensa de piernas', 3, '10 a 12', 90],
                        ['Curl femoral', 3, '10 a 12', 60],
                        ['Extensión de cuádriceps', 2, '12 a 15', 60],
                        ['Puente de glúteos', 3, '12 a 15', 60],
                        ['Elevación de talones', 2, '15', 45],
                        ['Plancha', 3, '30 segundos', 45],
                    ]],
                    ['Tren superior', 'Misma zona, peso libre', [
                        ['Press inclinado con mancuernas', 3, '10 a 12', 75],
                        ['Remo con mancuerna a una mano', 3, '10 por brazo', 60],
                        ['Elevaciones laterales', 3, '12 a 15', 45],
                        ['Face pull en polea', 2, '15', 45],
                        ['Curl martillo', 2, '12', 45],
                        ['Fondos entre bancos', 2, '10 a 12', 45],
                    ]],
                    ['Tren inferior', 'Piernas con peso libre', [
                        ['Sentadilla goblet', 3, '10 a 12', 90],
                        ['Peso muerto rumano con mancuernas', 3, '10 a 12', 75],
                        ['Zancadas con mancuernas', 2, '10 por pierna', 60],
                        ['Elevación de talones', 2, '15', 45],
                        ['Dead bug', 3, '8 por lado', 45],
                    ]],
                ],
            ],

            // ---------- Bajar de peso ----------
            [
                'nombre' => 'Quema y tonifica · 3 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'nunca', 'dias' => 3,
                'descripcion' => 'Fuerza con máquinas y cardio al final. Bajar de peso depende sobre todo de la alimentación: el entrenamiento ayuda a que lo que se pierda sea grasa y no músculo.',
                'dias_de' => [
                    self::conCardio($cuerpoA(2, '12 a 15', 45), ['Bicicleta estática', 1, '15 minutos', 0]),
                    self::conCardio($cuerpoB(2, '12 a 15', 45), ['Elíptica', 1, '15 minutos', 0]),
                    self::conCardio($cuerpoA(2, '12 a 15', 45), $cardioSuave('20 minutos')),
                ],
            ],
            [
                'nombre' => 'Quema en circuito · 3 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Circuitos: haz un ejercicio tras otro sin descansar y descansa 90 segundos al terminar la vuelta. Da 3 vueltas. Después, cardio.',
                'dias_de' => [
                    ['Circuito A', '3 vueltas, 90 s entre vueltas', [
                        ['Sentadilla goblet', 3, '15', 0],
                        ['Flexiones de brazos', 3, '10 a 15', 0],
                        ['Remo sentado en polea', 3, '15', 0],
                        ['Zancadas con mancuernas', 3, '10 por pierna', 0],
                        ['Plancha', 3, '30 segundos', 90],
                        ['Intervalos en bicicleta', 1, '8 rondas', 0, '30 segundos fuerte, 90 suave.'],
                    ]],
                    ['Circuito B', '3 vueltas, 90 s entre vueltas', [
                        ['Prensa de piernas', 3, '15', 0],
                        ['Jalón al pecho', 3, '15', 0],
                        ['Press de hombros con mancuernas', 3, '12', 0],
                        ['Puente de glúteos', 3, '15', 0],
                        ['Burpees', 3, '8', 90],
                        ['Caminata inclinada en trotadora', 1, '15 minutos', 0],
                    ]],
                    ['Circuito C', '3 vueltas, 90 s entre vueltas', [
                        ['Peso muerto rumano con mancuernas', 3, '12', 0],
                        ['Press de pecho en máquina', 3, '15', 0],
                        ['Remo con mancuerna a una mano', 3, '12 por brazo', 0],
                        ['Subida al cajón', 3, '10 por pierna', 0],
                        ['Paseo del granjero', 3, '30 metros', 90],
                        ['Remo ergómetro', 1, '10 minutos', 0],
                    ]],
                ],
            ],
            [
                'nombre' => 'Quema torso y pierna · 4 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'algo', 'dias' => 4,
                'descripcion' => 'Arriba y abajo por separado, con poco descanso y cardio al final de cada sesión.',
                'dias_de' => [
                    self::conCardio(['Torso', 'Pecho, espalda y brazos', [
                        ['Press de pecho en máquina', 3, '12 a 15', 45],
                        ['Jalón al pecho', 3, '12 a 15', 45],
                        ['Press de hombros en máquina', 3, '12', 45],
                        ['Remo sentado en polea', 3, '12 a 15', 45],
                        ['Curl de bíceps con mancuernas', 2, '15', 30],
                        ['Extensión de tríceps en polea', 2, '15', 30],
                    ]], ['Elíptica', 1, '15 minutos', 0]),
                    self::conCardio(['Pierna', 'Piernas y glúteos', [
                        ['Prensa de piernas', 3, '15', 60],
                        ['Zancadas con mancuernas', 3, '12 por pierna', 45],
                        ['Curl femoral', 3, '12 a 15', 45],
                        ['Hip thrust', 3, '12', 60],
                        ['Plancha', 3, '40 segundos', 30],
                    ]], $cardioSuave('15 minutos')),
                    self::conCardio(['Torso', 'Peso libre', [
                        ['Press inclinado con mancuernas', 3, '12', 45],
                        ['Remo con mancuerna a una mano', 3, '12 por brazo', 45],
                        ['Elevaciones laterales', 3, '15', 30],
                        ['Flexiones de brazos', 2, 'las que salgan', 45],
                        ['Face pull en polea', 2, '15', 30],
                    ]], ['Intervalos en bicicleta', 1, '8 rondas', 0, '30 segundos fuerte, 90 suave.']),
                    self::conCardio(['Pierna', 'Peso libre y zona media', [
                        ['Sentadilla goblet', 3, '15', 60],
                        ['Peso muerto rumano con mancuernas', 3, '12', 60],
                        ['Subida al cajón', 3, '10 por pierna', 45],
                        ['Elevación de talones', 3, '15', 30],
                        ['Dead bug', 3, '10 por lado', 30],
                    ]], $cardioSuave('15 minutos')),
                ],
            ],
            [
                'nombre' => 'Definición · 5 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'hace_tiempo', 'dias' => 5,
                'descripcion' => 'Para quien ya entrena: mantener la fuerza con pesos exigentes y sumar gasto con cardio y circuitos. Un día a la semana, solo cardio.',
                'dias_de' => [
                    ['Empuje', 'Pecho, hombros y tríceps', [
                        ['Press de banca con barra', 4, '6 a 8', 120],
                        ['Press inclinado con mancuernas', 3, '10', 90],
                        ['Press de hombros con mancuernas', 3, '10', 90],
                        ['Elevaciones laterales', 3, '15', 45],
                        ['Extensión de tríceps en polea', 3, '12', 45],
                        ['Intervalos en bicicleta', 1, '10 rondas', 0],
                    ]],
                    ['Tirón', 'Espalda y bíceps', [
                        ['Dominadas asistidas', 4, '6 a 10', 90],
                        ['Remo con barra', 3, '8 a 10', 90],
                        ['Remo sentado en polea', 3, '12', 60],
                        ['Face pull en polea', 3, '15', 45],
                        ['Curl con barra', 3, '10', 45],
                        ['Remo ergómetro', 1, '12 minutos', 0],
                    ]],
                    ['Pierna', 'Piernas y glúteos', [
                        ['Sentadilla con barra', 4, '6 a 8', 120],
                        ['Peso muerto rumano con barra', 3, '8 a 10', 90],
                        ['Zancadas con mancuernas', 3, '10 por pierna', 60],
                        ['Curl femoral', 3, '12', 45],
                        ['Elevación de talones', 3, '15', 30],
                    ]],
                    ['Circuito metabólico', '4 vueltas, 90 s entre vueltas', [
                        ['Burpees', 4, '10', 0],
                        ['Sentadilla goblet', 4, '15', 0],
                        ['Remo con mancuerna a una mano', 4, '12 por brazo', 0],
                        ['Paseo del granjero', 4, '40 metros', 0],
                        ['Plancha', 4, '45 segundos', 90],
                    ]],
                    ['Cardio y zona media', 'Largo y suave', [
                        ['Caminata inclinada en trotadora', 1, '35 a 45 minutos', 0],
                        ['Plancha lateral', 3, '30 segundos por lado', 30],
                        ['Elevación de piernas', 3, '12', 45],
                        ['Pallof press', 3, '10 por lado', 30],
                    ]],
                ],
            ],

            // ---------- Ganar fuerza y músculo ----------
            [
                'nombre' => 'Fuerza desde cero · 2 días',
                'objetivo' => 'fuerza', 'nivel' => 'nunca', 'dias' => 2,
                'descripcion' => 'Cuerpo completo con máquinas y un poco más de peso que en las rutinas de inicio. Anota lo que levantas: cada semana intenta sumar una repetición o un poco de peso.',
                'dias_de' => [$cuerpoA(3, '8 a 10', 90), $cuerpoB(3, '8 a 10', 90)],
            ],
            [
                'nombre' => 'Fuerza cuerpo completo · 3 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Tres sesiones de cuerpo completo con los básicos. Descansa lo indicado en los ejercicios pesados: la fuerza se gana levantando bien, no rápido.',
                'dias_de' => [
                    ['Día A', 'Sentadilla y empuje', [
                        ['Sentadilla con barra', 4, '6 a 8', 120],
                        ['Press de banca con barra', 4, '6 a 8', 120],
                        ['Remo sentado en polea', 3, '10', 90],
                        ['Elevaciones laterales', 3, '12', 45],
                        ['Plancha', 3, '40 segundos', 45],
                    ]],
                    ['Día B', 'Cadera y tirón', [
                        ['Peso muerto rumano con barra', 4, '6 a 8', 120],
                        ['Dominadas asistidas', 4, '6 a 8', 90],
                        ['Press de hombros con mancuernas', 3, '8 a 10', 90],
                        ['Zancadas con mancuernas', 3, '10 por pierna', 60],
                        ['Curl de bíceps con mancuernas', 2, '10 a 12', 45],
                    ]],
                    ['Día C', 'Volumen', [
                        ['Prensa de piernas', 3, '10 a 12', 90],
                        ['Press inclinado con mancuernas', 3, '10', 90],
                        ['Remo con mancuerna a una mano', 3, '10 por brazo', 60],
                        ['Hip thrust', 3, '10', 90],
                        ['Extensión de tríceps en polea', 3, '12', 45],
                        ['Crunch en polea', 3, '12', 45],
                    ]],
                ],
            ],
            [
                'nombre' => 'Torso y pierna · 4 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 4,
                'descripcion' => 'El clásico de cuatro días: dos de torso y dos de pierna. Un día pesado y uno con más repeticiones para cada zona.',
                'dias_de' => [
                    ['Torso pesado', 'Pocas repeticiones', [
                        ['Press de banca con barra', 4, '6 a 8', 120],
                        ['Remo con barra', 4, '6 a 8', 120],
                        ['Press de hombros con mancuernas', 3, '8', 90],
                        ['Dominadas asistidas', 3, '8', 90],
                        ['Curl con barra', 2, '10', 60],
                        ['Extensión de tríceps en polea', 2, '10', 60],
                    ]],
                    ['Pierna pesada', 'Pocas repeticiones', [
                        ['Sentadilla con barra', 4, '6 a 8', 150],
                        ['Peso muerto rumano con barra', 3, '8', 120],
                        ['Prensa de piernas', 3, '10', 90],
                        ['Curl femoral', 3, '10', 60],
                        ['Elevación de talones', 3, '12', 45],
                    ]],
                    ['Torso volumen', 'Más repeticiones', [
                        ['Press inclinado con mancuernas', 3, '10 a 12', 90],
                        ['Jalón al pecho', 3, '10 a 12', 90],
                        ['Aperturas en máquina', 3, '12 a 15', 60],
                        ['Remo sentado en polea', 3, '12', 60],
                        ['Elevaciones laterales', 3, '15', 45],
                        ['Curl martillo', 3, '12', 45],
                    ]],
                    ['Pierna volumen', 'Más repeticiones', [
                        ['Prensa de piernas', 4, '12', 90],
                        ['Hip thrust', 3, '10 a 12', 90],
                        ['Zancadas con mancuernas', 3, '12 por pierna', 60],
                        ['Extensión de cuádriceps', 3, '15', 45],
                        ['Crunch en polea', 3, '12 a 15', 45],
                    ]],
                ],
            ],
            [
                'nombre' => 'Fuerza torso y pierna · 4 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 4,
                'descripcion' => 'Para quien ya maneja los básicos: series más pesadas y descansos largos. Calienta con 2 o 3 series livianas antes de las series de trabajo.',
                'dias_de' => [
                    ['Torso fuerza', '4 a 6 repeticiones', [
                        ['Press de banca con barra', 5, '4 a 6', 180],
                        ['Remo con barra', 4, '6', 150],
                        ['Press de hombros con mancuernas', 3, '6 a 8', 120],
                        ['Dominadas asistidas', 3, '6 a 8', 120],
                        ['Press francés con mancuerna', 3, '10', 60],
                    ]],
                    ['Pierna fuerza', '4 a 6 repeticiones', [
                        ['Sentadilla con barra', 5, '4 a 6', 180],
                        ['Peso muerto rumano con barra', 4, '6', 150],
                        ['Zancadas con mancuernas', 3, '8 por pierna', 90],
                        ['Elevación de talones', 4, '10', 60],
                        ['Elevación de piernas', 3, '12', 45],
                    ]],
                    ['Torso hipertrofia', '8 a 12 repeticiones', [
                        ['Press inclinado con mancuernas', 4, '8 a 10', 90],
                        ['Jalón al pecho', 4, '10', 90],
                        ['Aperturas en máquina', 3, '12', 60],
                        ['Remo sentado en polea', 3, '12', 60],
                        ['Elevaciones laterales', 4, '12 a 15', 45],
                        ['Curl con barra', 3, '10', 60],
                        ['Extensión de tríceps en polea', 3, '12', 45],
                    ]],
                    ['Pierna hipertrofia', '8 a 12 repeticiones', [
                        ['Prensa de piernas', 4, '10 a 12', 120],
                        ['Hip thrust', 4, '8 a 10', 90],
                        ['Curl femoral', 3, '12', 60],
                        ['Extensión de cuádriceps', 3, '12 a 15', 60],
                        ['Pallof press', 3, '10 por lado', 45],
                    ]],
                ],
            ],
            [
                'nombre' => 'Empuje, tirón y pierna · 5 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 5,
                'descripcion' => 'Empuje, tirón y pierna, y después torso y pierna. Cada zona se entrena casi dos veces por semana.',
                'dias_de' => [
                    ['Empuje', 'Pecho, hombros y tríceps', [
                        ['Press de banca con barra', 4, '6 a 8', 150],
                        ['Press de hombros con mancuernas', 3, '8 a 10', 90],
                        ['Press inclinado con mancuernas', 3, '10', 90],
                        ['Elevaciones laterales', 3, '15', 45],
                        ['Extensión de tríceps en polea', 3, '12', 45],
                    ]],
                    ['Tirón', 'Espalda y bíceps', [
                        ['Dominadas asistidas', 4, '6 a 8', 120],
                        ['Remo con barra', 4, '8', 120],
                        ['Remo sentado en polea', 3, '12', 60],
                        ['Face pull en polea', 3, '15', 45],
                        ['Curl con barra', 3, '10', 60],
                    ]],
                    ['Pierna', 'Cuádriceps y glúteos', [
                        ['Sentadilla con barra', 4, '6 a 8', 150],
                        ['Prensa de piernas', 3, '10 a 12', 120],
                        ['Zancadas con mancuernas', 3, '10 por pierna', 60],
                        ['Extensión de cuádriceps', 3, '15', 45],
                        ['Elevación de talones', 4, '12', 45],
                    ]],
                    ['Torso', 'Volumen', [
                        ['Press inclinado con mancuernas', 3, '10 a 12', 90],
                        ['Jalón al pecho', 3, '10 a 12', 90],
                        ['Aperturas en máquina', 3, '12 a 15', 60],
                        ['Remo con mancuerna a una mano', 3, '12 por brazo', 60],
                        ['Curl martillo', 3, '12', 45],
                        ['Press francés con mancuerna', 3, '12', 45],
                    ]],
                    ['Pierna', 'Isquios y glúteos', [
                        ['Peso muerto rumano con barra', 4, '8', 150],
                        ['Hip thrust', 4, '10', 90],
                        ['Curl femoral', 3, '12', 60],
                        ['Subida al cajón', 3, '10 por pierna', 60],
                        ['Crunch en polea', 3, '15', 45],
                    ]],
                ],
            ],
            [
                'nombre' => 'Empuje, tirón y pierna · 6 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 6,
                'descripcion' => 'Empuje, tirón y pierna dos veces por semana. Solo si duermes bien y puedes descansar: seis días exigentes cansan.',
                'dias_de' => [
                    ['Empuje pesado', 'Pocas repeticiones', [
                        ['Press de banca con barra', 5, '5', 180],
                        ['Press de hombros con mancuernas', 3, '8', 120],
                        ['Press inclinado con mancuernas', 3, '8 a 10', 90],
                        ['Extensión de tríceps en polea', 3, '10', 60],
                    ]],
                    ['Tirón pesado', 'Pocas repeticiones', [
                        ['Remo con barra', 5, '5', 180],
                        ['Dominadas asistidas', 4, '6', 120],
                        ['Face pull en polea', 3, '15', 45],
                        ['Curl con barra', 3, '8', 60],
                    ]],
                    ['Pierna pesada', 'Pocas repeticiones', [
                        ['Sentadilla con barra', 5, '5', 180],
                        ['Peso muerto rumano con barra', 3, '6 a 8', 150],
                        ['Elevación de talones', 4, '10', 60],
                        ['Plancha', 3, '45 segundos', 45],
                    ]],
                    ['Empuje volumen', 'Más repeticiones', [
                        ['Press de pecho en máquina', 4, '10 a 12', 90],
                        ['Aperturas en máquina', 3, '12 a 15', 60],
                        ['Elevaciones laterales', 4, '15', 45],
                        ['Fondos entre bancos', 3, '12 a 15', 45],
                    ]],
                    ['Tirón volumen', 'Más repeticiones', [
                        ['Jalón al pecho', 4, '10 a 12', 90],
                        ['Remo sentado en polea', 4, '12', 60],
                        ['Remo con mancuerna a una mano', 3, '12 por brazo', 60],
                        ['Curl martillo', 3, '12', 45],
                    ]],
                    ['Pierna volumen', 'Más repeticiones', [
                        ['Prensa de piernas', 4, '12', 120],
                        ['Hip thrust', 3, '12', 90],
                        ['Curl femoral', 3, '12 a 15', 60],
                        ['Extensión de cuádriceps', 3, '15', 45],
                        ['Elevación de piernas', 3, '12', 45],
                    ]],
                ],
            ],

            // ---------- Mantenerme ----------
            [
                'nombre' => 'Mantenerse · 2 días',
                'objetivo' => 'mantener', 'nivel' => 'algo', 'dias' => 2,
                'descripcion' => 'Con dos días bien hechos se mantiene la fuerza y el estado físico. Cuerpo completo y un poco de cardio.',
                'dias_de' => [
                    self::conCardio($cuerpoA(3, '10 a 12', 75), ['Elíptica', 1, '15 minutos', 0]),
                    self::conCardio($cuerpoC(3, '10 a 12', 75), ['Bicicleta estática', 1, '15 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Mantenerse · 3 días',
                'objetivo' => 'mantener', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Tres días de cuerpo completo combinando máquinas y peso libre. El tercer día, más cardio.',
                'dias_de' => [
                    $cuerpoA(3, '10 a 12', 75),
                    $cuerpoC(3, '10 a 12', 75),
                    self::conCardio($cuerpoB(2, '12 a 15', 60), $cardioSuave('20 minutos')),
                ],
            ],
            [
                'nombre' => 'Mantenerse activo · 4 días',
                'objetivo' => 'mantener', 'nivel' => 'hace_tiempo', 'dias' => 4,
                'descripcion' => 'Dos días de fuerza de cuerpo completo, uno de circuito y uno de cardio largo. Variado para no aburrirse.',
                'dias_de' => [
                    ['Fuerza A', 'Básicos', [
                        ['Sentadilla con barra', 3, '8', 120],
                        ['Press de banca con barra', 3, '8', 120],
                        ['Remo con barra', 3, '8 a 10', 90],
                        ['Elevaciones laterales', 3, '12', 45],
                        ['Plancha', 3, '45 segundos', 45],
                    ]],
                    ['Circuito', '3 vueltas, 90 s entre vueltas', [
                        ['Burpees', 3, '10', 0],
                        ['Zancadas con mancuernas', 3, '10 por pierna', 0],
                        ['Flexiones de brazos', 3, '12', 0],
                        ['Remo con mancuerna a una mano', 3, '12 por brazo', 0],
                        ['Paseo del granjero', 3, '40 metros', 90],
                    ]],
                    ['Fuerza B', 'Básicos', [
                        ['Peso muerto rumano con barra', 3, '8', 120],
                        ['Press de hombros con mancuernas', 3, '8 a 10', 90],
                        ['Dominadas asistidas', 3, '8', 90],
                        ['Hip thrust', 3, '10', 90],
                        ['Pallof press', 3, '10 por lado', 45],
                    ]],
                    ['Cardio', 'Largo y suave', [
                        ['Remo ergómetro', 1, '15 minutos', 0],
                        ['Caminata inclinada en trotadora', 1, '20 minutos', 0],
                        ['Plancha lateral', 3, '30 segundos por lado', 30],
                    ]],
                ],
            ],
        ];
    }

    /** Un día con cardio al final. */
    private static function conCardio(array $dia, array $cardio): array
    {
        $dia[2][] = $cardio;

        return $dia;
    }

    /**
     * Carga el catálogo y las rutinas. Devuelve [ejercicios, rutinas].
     *
     * @return array{0:int,1:int}
     */
    public static function cargar(): array
    {
        return DB::transaction(function () {
            $ids = [];
            $orden = 0;

            foreach (self::EJERCICIOS as $nombre => [$zona, $equipo, $indicacion]) {
                $ejercicio = Ejercicio::firstOrNew(['nombre' => $nombre]);
                $ejercicio->fill(['zona' => $zona, 'equipo' => $equipo, 'indicacion' => $indicacion, 'activo' => true, 'orden' => ++$orden])->save();
                $ids[$nombre] = $ejercicio->id;
            }

            foreach (self::rutinas() as $i => $datos) {
                // Se rehace entera: sus días y ejercicios salen de aquí.
                Rutina::where('nombre', $datos['nombre'])->each(fn (Rutina $r) => $r->delete());

                $rutina = Rutina::create([
                    'nombre' => $datos['nombre'],
                    'objetivo' => $datos['objetivo'],
                    'nivel' => $datos['nivel'],
                    'dias_por_semana' => $datos['dias'],
                    'descripcion' => $datos['descripcion'],
                    'activa' => true,
                    'orden' => $i + 1,
                ]);

                foreach ($datos['dias_de'] as $n => [$titulo, $foco, $lineas]) {
                    $dia = $rutina->dias()->create(['numero' => $n + 1, 'titulo' => $titulo, 'foco' => $foco]);

                    foreach ($lineas as $j => $linea) {
                        [$nombre, $series, $reps, $descanso] = $linea;
                        $alternativa = self::ALTERNATIVAS[$nombre] ?? null;

                        $dia->ejercicios()->create([
                            'id_ejercicio' => $ids[$nombre] ?? throw new \RuntimeException("Falta el ejercicio «{$nombre}» en el catálogo."),
                            'id_alternativa' => $alternativa ? $ids[$alternativa] : null,
                            'series' => $series,
                            'repeticiones' => $reps,
                            'descanso_seg' => $descanso,
                            'nota' => $linea[4] ?? null,
                            'orden' => $j + 1,
                        ]);
                    }
                }
            }

            return [count($ids), count(self::rutinas())];
        });
    }

    /** Quita las rutinas de ejemplo (por nombre). Los ejercicios quedan. */
    public static function quitar(): int
    {
        $nombres = array_column(self::rutinas(), 'nombre');
        $cuantas = Rutina::whereIn('nombre', $nombres)->count();
        Rutina::whereIn('nombre', $nombres)->each(fn (Rutina $r) => $r->delete());

        return $cuantas;
    }
}
