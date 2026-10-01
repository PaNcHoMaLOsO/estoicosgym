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
 * ejercicios y las rutinas se buscan por nombre, y lo que ya está no se toca.
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

        // Agregados después: más variedad para las rutinas de glúteo, express y torso.
        'Press de pecho con mancuernas' => ['pecho', 'mancuernas', 'Banco plano. Baja las mancuernas a los lados del pecho y súbelas sin chocarlas.'],
        'Cruce de poleas' => ['pecho', 'maquina', 'Un paso adelante, codos apenas doblados. Junta las manos frente al pecho y vuelve despacio.'],
        'Remo en máquina' => ['espalda', 'maquina', 'Pecho apoyado en el respaldo. Tira de las manillas juntando los omóplatos.'],
        'Hiperextensiones' => ['espalda', 'maquina', 'Cadera en el apoyo. Baja con la espalda recta y sube hasta quedar en línea, sin pasarte.'],
        'Sentadilla búlgara' => ['piernas', 'mancuernas', 'Pie de atrás sobre un banco. Baja recto hasta que el muslo de adelante quede paralelo.'],
        'Abductores en máquina' => ['piernas', 'maquina', 'Espalda apoyada. Abre las piernas contra el acolchado y vuelve sin soltar el peso.'],
        'Patada de glúteo en polea' => ['piernas', 'maquina', 'Tobillera en la polea baja. Lleva la pierna atrás apretando el glúteo, sin arquear la espalda.'],
        'Aperturas invertidas con mancuernas' => ['hombros', 'mancuernas', 'Inclinado adelante, espalda recta. Abre los brazos hacia los lados juntando los omóplatos.'],
        'Curl en polea' => ['brazos', 'maquina', 'Codos pegados al cuerpo. Sube la barra de la polea sin mover los codos y baja completo.'],
        'Extensión de tríceps sobre la cabeza' => ['brazos', 'mancuernas', 'Sentado con respaldo, una mancuerna con las dos manos. Bájala detrás de la cabeza y estira.'],
        'Escaladores' => ['core', 'propio_peso', 'En posición de flexión, lleva las rodillas al pecho alternando, sin subir la cadera.'],
        'Swing con pesa rusa' => ['cuerpo_completo', 'mancuernas', 'La fuerza sale de la cadera, no de los brazos. Espalda recta y la pesa hasta la altura del pecho.'],
        'Escaladora' => ['cardio', 'cardio', 'Pasos completos, espalda derecha. Apóyate lo mínimo en las barras.'],

        // Agregados para dar más variedad (2026-10-01).
        'Press declinado con barra' => ['pecho', 'barra', 'Banco declinado y pies trabados. Baja la barra a la parte baja del pecho y empuja.'],
        'Press inclinado en máquina' => ['pecho', 'maquina', 'Respaldo inclinado, empuja hacia arriba y adelante sin despegar la espalda.'],
        'Press inclinado con barra' => ['pecho', 'barra', 'Banco a unos 30°. Baja la barra a la parte alta del pecho, codos un poco cerrados.'],
        'Aperturas con mancuernas' => ['pecho', 'mancuernas', 'Codos apenas doblados. Abre los brazos hasta sentir el pecho estirado y vuelve juntándolos arriba.'],
        'Cruce de poleas bajo' => ['pecho', 'maquina', 'Poleas abajo. Sube las manos juntándolas a la altura del pecho, sin mover los codos.'],
        'Flexiones con pies elevados' => ['pecho', 'propio_peso', 'Pies en un banco, cuerpo recto. Baja el pecho entre las manos y empuja.'],
        'Fondos en paralelas' => ['pecho', 'propio_peso', 'Inclina el tronco adelante y baja hasta que los codos queden en 90°. Usa la máquina asistida si cuesta.'],
        'Jalón con agarre cerrado' => ['espalda', 'maquina', 'Agarre en V, pecho arriba. Tira hacia el pecho llevando los codos pegados al cuerpo.'],
        'Jalón con brazos rectos' => ['espalda', 'maquina', 'De pie frente a la polea, brazos casi rectos. Lleva la barra hacia los muslos con la espalda.'],
        'Pullover con mancuerna' => ['espalda', 'mancuernas', 'Espalda alta en el banco, una mancuerna con las dos manos. Bájala detrás de la cabeza y vuelve sobre el pecho.'],
        'Remo en T' => ['espalda', 'barra', 'Pecho apoyado o espalda recta, tira la barra hacia el abdomen juntando los omóplatos.'],
        'Remo invertido en barra' => ['espalda', 'propio_peso', 'Bajo una barra baja, cuerpo recto. Tira el pecho hacia la barra.'],
        'Peso muerto con barra' => ['espalda', 'barra', 'Barra pegada a las piernas, espalda recta. Empuja el suelo con los pies y estira la cadera arriba.'],
        'Encogimientos con mancuernas' => ['espalda', 'mancuernas', 'Brazos estirados. Sube los hombros hacia las orejas, aguanta un segundo y baja.'],
        'Sentadilla en máquina Smith' => ['piernas', 'maquina', 'Pies un poco adelante de la barra. Baja hasta los 90° con el pecho arriba.'],
        'Sentadilla hack' => ['piernas', 'maquina', 'Espalda pegada al respaldo, baja controlado y empuja con todo el pie.'],
        'Sentadilla sumo con mancuerna' => ['piernas', 'mancuernas', 'Pies abiertos y puntas afuera. Baja con la mancuerna colgando entre las piernas.'],
        'Prensa a una pierna' => ['piernas', 'maquina', 'Un pie al centro de la plataforma. Baja sin que la rodilla se vaya hacia adentro.'],
        'Zancadas caminando' => ['piernas', 'mancuernas', 'Pasos largos, rodilla de atrás cerca del suelo, tronco derecho.'],
        'Curl femoral sentado' => ['piernas', 'maquina', 'Rodillas alineadas con la máquina. Lleva los talones hacia abajo y atrás, vuelve despacio.'],
        'Peso muerto a una pierna' => ['piernas', 'mancuernas', 'Una mancuerna, inclínate llevando la pierna libre atrás, espalda recta.'],
        'Aductores en máquina' => ['piernas', 'maquina', 'Junta las piernas contra los cojines sin chocarlas, vuelve controlado.'],
        'Elevación de talones sentado' => ['piernas', 'maquina', 'Rodillas bajo el cojín. Sube en puntas lo más alto que puedas y baja completo.'],
        'Hip thrust en máquina' => ['piernas', 'maquina', 'Espalda alta en el respaldo. Sube la cadera apretando glúteos, mentón al pecho.'],
        'Press militar con barra' => ['hombros', 'barra', 'De pie, abdomen firme. Empuja la barra desde la clavícula hasta arriba de la cabeza.'],
        'Press Arnold' => ['hombros', 'mancuernas', 'Sentado. Empieza con las palmas hacia ti y gíralas mientras empujas hacia arriba.'],
        'Elevaciones frontales' => ['hombros', 'mancuernas', 'Brazos casi rectos. Sube las mancuernas al frente hasta la altura de los hombros.'],
        'Elevaciones laterales en polea' => ['hombros', 'maquina', 'Polea baja al lado contrario. Sube el brazo hacia el costado hasta el hombro.'],
        'Pájaros en máquina' => ['hombros', 'maquina', 'Pecho contra el respaldo de la máquina de aperturas. Abre los brazos hacia atrás.'],
        'Remo al mentón' => ['hombros', 'barra', 'Agarre a lo ancho de hombros. Sube la barra hasta el pecho llevando los codos arriba.'],
        'Curl predicador' => ['brazos', 'maquina', 'Brazos apoyados en el cojín. Sube sin despegar los codos y baja casi hasta estirar.'],
        'Curl concentrado' => ['brazos', 'mancuernas', 'Sentado, codo apoyado en la parte interna del muslo. Sube la mancuerna sin mover el brazo.'],
        'Curl inclinado con mancuernas' => ['brazos', 'mancuernas', 'Banco inclinado, brazos colgando. Sube alternando sin adelantar los codos.'],
        'Patada de tríceps' => ['brazos', 'mancuernas', 'Inclinado, codo pegado al cuerpo. Estira el brazo hacia atrás y vuelve.'],
        'Extensión de tríceps con cuerda' => ['brazos', 'maquina', 'Codos pegados. Baja la cuerda separando las manos al final.'],
        'Press cerrado con barra' => ['brazos', 'barra', 'Manos a lo ancho de hombros, codos pegados. Baja la barra al pecho y empuja.'],
        'Rueda abdominal' => ['core', 'propio_peso', 'De rodillas, avanza con la rueda sin arquear la espalda y vuelve con el abdomen.'],
        'Elevación de rodillas colgado' => ['core', 'propio_peso', 'Colgado de la barra o en la silla romana. Sube las rodillas al pecho sin balancearte.'],
        'Giros rusos' => ['core', 'propio_peso', 'Sentado, tronco inclinado atrás. Gira de lado a lado tocando el suelo.'],
        'Crunch en máquina' => ['core', 'maquina', 'Lleva el pecho hacia las rodillas encogiendo el abdomen, sin tirar con los brazos.'],
        'Trote en trotadora' => ['cardio', 'cardio', 'Ritmo en que puedas hablar entrecortado. Brazos sueltos, sin agarrarte.'],
        'Salto de cuerda' => ['cardio', 'propio_peso', 'Saltos bajos en la punta de los pies, muñecas giran la cuerda.'],
    ];

    /**
     * Qué trabaja cada uno, para el mapa muscular: [principal, [secundarios]].
     * Van todos los del catálogo. Las claves son las de Ejercicio::MUSCULOS.
     *
     * @var array<string,array{0:string,1:list<string>}>
     */
    public const MUSCULOS = [
        'Press de pecho en máquina' => ['pecho', ['triceps', 'hombros']],
        'Press de banca con barra' => ['pecho', ['triceps', 'hombros']],
        'Press inclinado con mancuernas' => ['pecho', ['hombros', 'triceps']],
        'Aperturas en máquina' => ['pecho', ['hombros']],
        'Flexiones de brazos' => ['pecho', ['triceps', 'hombros', 'abdomen']],

        'Jalón al pecho' => ['espalda', ['biceps']],
        'Remo sentado en polea' => ['espalda', ['biceps', 'hombros']],
        'Remo con mancuerna a una mano' => ['espalda', ['biceps']],
        'Remo con barra' => ['espalda', ['biceps', 'lumbar']],
        'Dominadas asistidas' => ['espalda', ['biceps']],

        'Prensa de piernas' => ['cuadriceps', ['gluteos']],
        'Sentadilla goblet' => ['cuadriceps', ['gluteos', 'abdomen']],
        'Sentadilla con barra' => ['cuadriceps', ['gluteos', 'lumbar']],
        'Extensión de cuádriceps' => ['cuadriceps', []],
        'Curl femoral' => ['isquios', []],
        'Zancadas con mancuernas' => ['cuadriceps', ['gluteos']],
        'Peso muerto rumano con mancuernas' => ['isquios', ['gluteos', 'lumbar']],
        'Peso muerto rumano con barra' => ['isquios', ['gluteos', 'lumbar']],
        'Hip thrust' => ['gluteos', ['isquios']],
        'Puente de glúteos' => ['gluteos', ['isquios']],
        'Elevación de talones' => ['pantorrillas', []],
        'Subida al cajón' => ['cuadriceps', ['gluteos']],

        'Press de hombros en máquina' => ['hombros', ['triceps']],
        'Press de hombros con mancuernas' => ['hombros', ['triceps']],
        'Elevaciones laterales' => ['hombros', []],
        'Face pull en polea' => ['hombros', ['espalda']],

        'Curl de bíceps con mancuernas' => ['biceps', []],
        'Curl martillo' => ['biceps', []],
        'Curl con barra' => ['biceps', []],
        'Extensión de tríceps en polea' => ['triceps', []],
        'Fondos entre bancos' => ['triceps', ['pecho', 'hombros']],
        'Press francés con mancuerna' => ['triceps', []],

        'Plancha' => ['abdomen', ['hombros']],
        'Plancha lateral' => ['abdomen', ['gluteos']],
        'Dead bug' => ['abdomen', []],
        'Crunch en polea' => ['abdomen', []],
        'Elevación de piernas' => ['abdomen', []],
        'Pallof press' => ['abdomen', []],

        'Caminata inclinada en trotadora' => ['gluteos', ['cuadriceps', 'pantorrillas']],
        'Bicicleta estática' => ['cuadriceps', ['gluteos', 'pantorrillas']],
        'Elíptica' => ['cuadriceps', ['gluteos', 'hombros']],
        'Intervalos en bicicleta' => ['cuadriceps', ['gluteos', 'pantorrillas']],
        'Remo ergómetro' => ['espalda', ['cuadriceps', 'biceps']],
        'Burpees' => ['cuadriceps', ['pecho', 'hombros', 'abdomen']],
        'Paseo del granjero' => ['abdomen', ['espalda', 'hombros']],

        'Press de pecho con mancuernas' => ['pecho', ['triceps', 'hombros']],
        'Cruce de poleas' => ['pecho', ['hombros']],
        'Remo en máquina' => ['espalda', ['biceps']],
        'Hiperextensiones' => ['lumbar', ['gluteos', 'isquios']],
        'Sentadilla búlgara' => ['cuadriceps', ['gluteos']],
        'Abductores en máquina' => ['gluteos', []],
        'Patada de glúteo en polea' => ['gluteos', ['isquios']],
        'Aperturas invertidas con mancuernas' => ['hombros', ['espalda']],
        'Curl en polea' => ['biceps', []],
        'Extensión de tríceps sobre la cabeza' => ['triceps', []],
        'Escaladores' => ['abdomen', ['hombros', 'cuadriceps']],
        'Swing con pesa rusa' => ['gluteos', ['isquios', 'lumbar', 'hombros']],
        'Escaladora' => ['gluteos', ['cuadriceps', 'pantorrillas']],

        // Agregados para dar más variedad (2026-10-01).
        'Press declinado con barra' => ['pecho', ['triceps']],
        'Press inclinado en máquina' => ['pecho', ['hombros', 'triceps']],
        'Press inclinado con barra' => ['pecho', ['hombros', 'triceps']],
        'Aperturas con mancuernas' => ['pecho', []],
        'Cruce de poleas bajo' => ['pecho', []],
        'Flexiones con pies elevados' => ['pecho', ['hombros', 'triceps']],
        'Fondos en paralelas' => ['pecho', ['triceps', 'hombros']],
        'Jalón con agarre cerrado' => ['espalda', ['biceps']],
        'Jalón con brazos rectos' => ['espalda', []],
        'Pullover con mancuerna' => ['espalda', ['pecho']],
        'Remo en T' => ['espalda', ['biceps', 'lumbar']],
        'Remo invertido en barra' => ['espalda', ['biceps']],
        'Peso muerto con barra' => ['lumbar', ['gluteos', 'isquios', 'espalda']],
        'Encogimientos con mancuernas' => ['espalda', []],
        'Sentadilla en máquina Smith' => ['cuadriceps', ['gluteos']],
        'Sentadilla hack' => ['cuadriceps', ['gluteos']],
        'Sentadilla sumo con mancuerna' => ['gluteos', ['cuadriceps']],
        'Prensa a una pierna' => ['cuadriceps', ['gluteos']],
        'Zancadas caminando' => ['gluteos', ['cuadriceps']],
        'Curl femoral sentado' => ['isquios', []],
        'Peso muerto a una pierna' => ['isquios', ['gluteos']],
        'Aductores en máquina' => ['gluteos', []],
        'Elevación de talones sentado' => ['pantorrillas', []],
        'Hip thrust en máquina' => ['gluteos', ['isquios']],
        'Press militar con barra' => ['hombros', ['triceps']],
        'Press Arnold' => ['hombros', ['triceps']],
        'Elevaciones frontales' => ['hombros', []],
        'Elevaciones laterales en polea' => ['hombros', []],
        'Pájaros en máquina' => ['hombros', ['espalda']],
        'Remo al mentón' => ['hombros', ['espalda']],
        'Curl predicador' => ['biceps', []],
        'Curl concentrado' => ['biceps', []],
        'Curl inclinado con mancuernas' => ['biceps', []],
        'Patada de tríceps' => ['triceps', []],
        'Extensión de tríceps con cuerda' => ['triceps', []],
        'Press cerrado con barra' => ['triceps', ['pecho']],
        'Rueda abdominal' => ['abdomen', []],
        'Elevación de rodillas colgado' => ['abdomen', []],
        'Giros rusos' => ['abdomen', []],
        'Crunch en máquina' => ['abdomen', []],
        'Trote en trotadora' => ['cuadriceps', ['pantorrillas']],
        'Salto de cuerda' => ['pantorrillas', ['cuadriceps']],
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
        'Press de pecho con mancuernas' => 'Press de pecho en máquina',
        'Cruce de poleas' => 'Aperturas en máquina',
        'Remo en máquina' => 'Remo con mancuerna a una mano',
        'Hiperextensiones' => 'Puente de glúteos',
        'Sentadilla búlgara' => 'Prensa de piernas',
        'Abductores en máquina' => 'Patada de glúteo en polea',
        'Patada de glúteo en polea' => 'Puente de glúteos',
        'Curl en polea' => 'Curl de bíceps con mancuernas',
        'Extensión de tríceps sobre la cabeza' => 'Extensión de tríceps en polea',
        'Swing con pesa rusa' => 'Peso muerto rumano con mancuernas',
        'Escaladores' => 'Plancha',
        'Escaladora' => 'Elíptica',
        'Burpees' => 'Escaladores',

        // Agregados para dar más variedad (2026-10-01).
        'Press declinado con barra' => 'Press de banca con barra',
        'Press inclinado en máquina' => 'Press inclinado con mancuernas',
        'Press inclinado con barra' => 'Press inclinado en máquina',
        'Aperturas con mancuernas' => 'Aperturas en máquina',
        'Cruce de poleas bajo' => 'Cruce de poleas',
        'Flexiones con pies elevados' => 'Flexiones de brazos',
        'Fondos en paralelas' => 'Press declinado con barra',
        'Jalón con agarre cerrado' => 'Jalón al pecho',
        'Jalón con brazos rectos' => 'Pullover con mancuerna',
        'Pullover con mancuerna' => 'Jalón con agarre cerrado',
        'Remo en T' => 'Remo con barra',
        'Remo invertido en barra' => 'Remo sentado en polea',
        'Peso muerto con barra' => 'Peso muerto rumano con barra',
        'Encogimientos con mancuernas' => 'Face pull en polea',
        'Sentadilla en máquina Smith' => 'Prensa de piernas',
        'Sentadilla hack' => 'Sentadilla en máquina Smith',
        'Sentadilla sumo con mancuerna' => 'Sentadilla goblet',
        'Prensa a una pierna' => 'Prensa de piernas',
        'Zancadas caminando' => 'Zancadas con mancuernas',
        'Curl femoral sentado' => 'Curl femoral',
        'Peso muerto a una pierna' => 'Peso muerto rumano con mancuernas',
        'Aductores en máquina' => 'Abductores en máquina',
        'Elevación de talones sentado' => 'Elevación de talones',
        'Hip thrust en máquina' => 'Hip thrust',
        'Press militar con barra' => 'Press de hombros con mancuernas',
        'Press Arnold' => 'Press de hombros en máquina',
        'Elevaciones frontales' => 'Elevaciones laterales',
        'Elevaciones laterales en polea' => 'Elevaciones laterales',
        'Pájaros en máquina' => 'Aperturas invertidas con mancuernas',
        'Remo al mentón' => 'Elevaciones laterales',
        'Curl predicador' => 'Curl con barra',
        'Curl concentrado' => 'Curl de bíceps con mancuernas',
        'Curl inclinado con mancuernas' => 'Curl concentrado',
        'Patada de tríceps' => 'Extensión de tríceps en polea',
        'Extensión de tríceps con cuerda' => 'Extensión de tríceps en polea',
        'Press cerrado con barra' => 'Fondos entre bancos',
        'Rueda abdominal' => 'Plancha',
        'Elevación de rodillas colgado' => 'Elevación de piernas',
        'Giros rusos' => 'Pallof press',
        'Crunch en máquina' => 'Crunch en polea',
        'Trote en trotadora' => 'Caminata inclinada en trotadora',
        'Salto de cuerda' => 'Escaladores',
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

        // Torso y pierna con máquinas: para aprender o para volver.
        $torsoMaquinas = fn (int $s, string $r, int $d) => ['Tren superior', 'Pecho, espalda y hombros con máquinas', [
            ['Press de pecho en máquina', $s, $r, $d],
            ['Jalón al pecho', $s, $r, $d],
            ['Remo en máquina', $s, $r, $d],
            ['Press de hombros en máquina', 2, $r, $d],
            ['Curl en polea', 2, '12', 45],
            ['Extensión de tríceps en polea', 2, '12', 45],
        ]];
        $piernaMaquinas = fn (int $s, string $r, int $d) => ['Tren inferior', 'Piernas y glúteos con máquinas', [
            ['Prensa de piernas', $s, $r, $d + 15],
            ['Curl femoral', $s, $r, $d],
            ['Extensión de cuádriceps', 2, $r, $d],
            ['Abductores en máquina', 2, '15', 45],
            ['Elevación de talones', 2, '15', 45],
            ['Plancha', 3, '20 a 30 segundos', 45],
        ]];
        $torsoLibre = fn (int $s, string $r, int $d) => ['Tren superior', 'Pecho, espalda y hombros con peso libre', [
            ['Press de pecho con mancuernas', $s, $r, $d],
            ['Remo con mancuerna a una mano', $s, $r . ' por brazo', $d],
            ['Press de hombros con mancuernas', $s, $r, $d],
            ['Jalón al pecho', 2, '12', 60],
            ['Aperturas invertidas con mancuernas', 2, '15', 45],
            ['Curl martillo', 2, '12', 45],
        ]];
        $piernaLibre = fn (int $s, string $r, int $d) => ['Tren inferior', 'Piernas y glúteos con peso libre', [
            ['Sentadilla goblet', $s, $r, $d + 15],
            ['Peso muerto rumano con mancuernas', $s, $r, $d],
            ['Zancadas con mancuernas', 2, $r . ' por pierna', $d],
            ['Hip thrust', 2, '12', 60],
            ['Elevación de talones', 2, '15', 45],
            ['Dead bug', 3, '8 por lado', 45],
        ]];
        // Un día suave: cardio largo y zona media.
        $cardioYZona = fn (string $min) => ['Cardio y zona media', 'Suave, a ritmo en que puedas conversar', [
            ['Elíptica', 1, $min, 0],
            ['Dead bug', 3, '8 por lado', 45],
            ['Plancha lateral', 3, '20 a 30 segundos por lado', 30],
            ['Pallof press', 2, '10 por lado', 30],
        ]];
        // Fuerza con los básicos de barra, para quien ya los maneja.
        $basicosA = fn (int $s, string $r, int $d) => ['Día A', 'Sentadilla y empuje', [
            ['Sentadilla con barra', $s, $r, $d],
            ['Press de banca con barra', $s, $r, $d],
            ['Remo con barra', 3, '6 a 8', 120],
            ['Face pull en polea', 3, '15', 45],
            ['Plancha', 3, '40 segundos', 45],
        ]];
        $basicosB = fn (int $s, string $r, int $d) => ['Día B', 'Cadera y tirón', [
            ['Peso muerto rumano con barra', $s, $r, $d],
            ['Dominadas asistidas', $s, $r, $d],
            ['Press de hombros con mancuernas', 3, '8 a 10', 90],
            ['Hip thrust', 3, '8 a 10', 90],
            ['Curl con barra', 2, '10', 60],
        ]];
        // Circuitos para bajar de peso: uno tras otro, descanso al cerrar la vuelta.
        $circuitoMaquinas = fn (string $cardio) => ['Circuito con máquinas', '3 vueltas, 90 s entre vueltas', [
            ['Prensa de piernas', 3, '15', 0],
            ['Press de pecho en máquina', 3, '15', 0],
            ['Remo en máquina', 3, '15', 0],
            ['Abductores en máquina', 3, '15', 0],
            ['Plancha', 3, '30 segundos', 90],
            ['Bicicleta estática', 1, $cardio, 0],
        ]];
        $circuitoLibre = fn (string $cardio) => ['Circuito con peso libre', '3 vueltas, 90 s entre vueltas', [
            ['Sentadilla goblet', 3, '15', 0],
            ['Remo con mancuerna a una mano', 3, '12 por brazo', 0],
            ['Swing con pesa rusa', 3, '15', 0],
            ['Flexiones de brazos', 3, 'las que salgan', 0],
            ['Escaladores', 3, '30 segundos', 90],
            ['Caminata inclinada en trotadora', 1, $cardio, 0],
        ]];
        // Glúteo y pierna.
        $gluteo = fn (int $s, int $d) => ['Glúteo', 'Cadera y glúteos', [
            ['Hip thrust', $s, '8 a 10', $d],
            ['Peso muerto rumano con mancuernas', 3, '10', $d],
            ['Sentadilla búlgara', 3, '10 por pierna', 60],
            ['Patada de glúteo en polea', 3, '12 por pierna', 45],
            ['Abductores en máquina', 3, '15 a 20', 45],
        ]];
        $pierna = fn (int $s, int $d) => ['Pierna', 'Cuádriceps e isquios', [
            ['Prensa de piernas', $s, '10 a 12', $d],
            ['Curl femoral', 3, '12', 60],
            ['Zancadas con mancuernas', 3, '10 por pierna', 60],
            ['Puente de glúteos', 3, '15', 45],
            ['Elevación de talones', 3, '15', 45],
        ]];
        $torsoGluteo = ['Torso', 'Espalda, pecho y hombros', [
            ['Jalón al pecho', 3, '10 a 12', 75],
            ['Press de pecho con mancuernas', 3, '10', 75],
            ['Remo en máquina', 3, '10 a 12', 75],
            ['Elevaciones laterales', 3, '12 a 15', 45],
            ['Aperturas invertidas con mancuernas', 2, '15', 45],
            ['Dead bug', 3, '8 por lado', 45],
        ]];
        // Express: de a pares, uno seguido del otro. Cabe en 30 minutos.
        $expressA = ['Express A', 'De a pares: haz los dos seguidos y descansa', [
            ['Prensa de piernas', 3, '12', 0],
            ['Jalón al pecho', 3, '12', 60],
            ['Press de pecho en máquina', 3, '12', 0],
            ['Remo en máquina', 3, '12', 60],
            ['Plancha', 2, '30 segundos', 30],
        ]];
        $expressB = ['Express B', 'De a pares: haz los dos seguidos y descansa', [
            ['Sentadilla goblet', 3, '12', 0],
            ['Remo con mancuerna a una mano', 3, '10 por brazo', 60],
            ['Press de hombros con mancuernas', 3, '10', 0],
            ['Hip thrust', 3, '12', 60],
            ['Escaladores', 2, '30 segundos', 30],
        ]];
        $expressC = ['Express C', 'Circuito corto y cardio', [
            ['Swing con pesa rusa', 3, '15', 0],
            ['Flexiones de brazos', 3, 'las que salgan', 0],
            ['Curl femoral', 3, '12', 60],
            ['Intervalos en bicicleta', 1, '5 rondas', 0, '30 segundos fuerte, 90 suave.'],
        ]];

        // Un grupo por día, con máquinas (para quien empieza) o con más
        // series y peso libre (para quien ya entrena).
        $pechoMaquinas = ['Pecho', 'Pecho con máquinas', [
            ['Press de pecho en máquina', 3, '12 a 15', 75],
            ['Aperturas en máquina', 2, '12 a 15', 60],
            ['Press inclinado con mancuernas', 2, '12 a 15', 60],
            ['Cruce de poleas', 2, '12 a 15', 60],
            ['Plancha', 3, '20 a 30 segundos', 45],
        ]];
        $espaldaMaquinas = ['Espalda', 'Espalda con máquinas', [
            ['Jalón al pecho', 3, '12 a 15', 75],
            ['Remo en máquina', 3, '12 a 15', 75],
            ['Remo sentado en polea', 2, '12 a 15', 60],
            ['Hiperextensiones', 2, '12', 60],
            ['Dead bug', 3, '8 por lado', 45],
        ]];
        $piernasMaquinas = ['Piernas', 'Piernas y glúteos con máquinas', [
            ['Prensa de piernas', 3, '12 a 15', 90],
            ['Curl femoral', 2, '12 a 15', 60],
            ['Extensión de cuádriceps', 2, '12 a 15', 60],
            ['Abductores en máquina', 2, '15', 45],
            ['Elevación de talones', 2, '15', 45],
        ]];
        $hombrosBrazosMaquinas = ['Hombros y brazos', 'Hombros, bíceps y tríceps', [
            ['Press de hombros en máquina', 3, '12 a 15', 75],
            ['Elevaciones laterales', 2, '12 a 15', 45],
            ['Face pull en polea', 2, '15', 45],
            ['Curl en polea', 2, '12', 45],
            ['Extensión de tríceps en polea', 2, '12', 45],
        ]];
        $pechoIntermedio = ['Pecho', 'Pecho y tríceps', [
            ['Press de pecho con mancuernas', 4, '8 a 10', 90],
            ['Press inclinado con mancuernas', 3, '10 a 12', 75],
            ['Press de pecho en máquina', 3, '10 a 12', 75],
            ['Cruce de poleas', 3, '12', 60],
            ['Fondos entre bancos', 3, '10 a 12', 60],
        ]];
        $espaldaIntermedio = ['Espalda', 'Espalda y bíceps', [
            ['Jalón al pecho', 4, '8 a 10', 90],
            ['Remo con mancuerna a una mano', 3, '10 por brazo', 75],
            ['Remo sentado en polea', 3, '10 a 12', 75],
            ['Remo en máquina', 3, '12', 60],
            ['Hiperextensiones', 3, '12', 60],
        ]];
        $piernasIntermedio = ['Piernas', 'Cuádriceps, isquios y pantorrillas', [
            ['Prensa de piernas', 4, '8 a 10', 120],
            ['Peso muerto rumano con mancuernas', 3, '10', 90],
            ['Zancadas con mancuernas', 3, '10 por pierna', 60],
            ['Curl femoral', 3, '12', 60],
            ['Elevación de talones', 3, '15', 45],
        ]];
        $hombrosBrazosIntermedio = ['Hombros y brazos', 'Hombros, bíceps y tríceps', [
            ['Press de hombros con mancuernas', 4, '8 a 10', 90],
            ['Elevaciones laterales', 3, '12 a 15', 45],
            ['Aperturas invertidas con mancuernas', 3, '15', 45],
            ['Curl de bíceps con mancuernas', 3, '10 a 12', 60],
            ['Extensión de tríceps en polea', 3, '10 a 12', 60],
            ['Curl martillo', 2, '12', 45],
        ]];

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

            // ================================================================
            // AGREGADAS DESPUÉS: cada objetivo y nivel con 2, 3 y 4 días, más
            // glúteo y pierna, y las express de 30 minutos.
            // ================================================================

            // ---------- Estoy empezando ----------
            [
                'nombre' => 'Aprender la sala · 4 días',
                'objetivo' => 'empezar', 'nivel' => 'nunca', 'dias' => 4,
                'descripcion' => 'Arriba y abajo por separado, todo con máquinas. Pesos livianos las dos primeras semanas: primero la técnica.',
                'dias_de' => [
                    $torsoMaquinas(2, '12 a 15', 60),
                    $piernaMaquinas(2, '12 a 15', 60),
                    self::conCardio($torsoMaquinas(2, '12 a 15', 60), $cardioSuave('10 minutos')),
                    self::conCardio($piernaMaquinas(2, '12 a 15', 60), $cardioSuave('10 minutos')),
                ],
            ],
            [
                'nombre' => 'Base completa · 2 días',
                'objetivo' => 'empezar', 'nivel' => 'algo', 'dias' => 2,
                'descripcion' => 'Dos días de cuerpo completo, con máquinas y peso libre. Deja al menos un día entre sesiones.',
                'dias_de' => [
                    self::conCardio($cuerpoA(3, '10 a 12', 75), $cardioSuave('10 minutos')),
                    self::conCardio($cuerpoC(3, '10 a 12', 75), ['Bicicleta estática', 1, '10 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Vuelta al gimnasio · 2 días',
                'objetivo' => 'empezar', 'nivel' => 'hace_tiempo', 'dias' => 2,
                'descripcion' => 'Para quien entrenaba y lo dejó. Las primeras semanas usa menos peso del que movías antes: el cuerpo se acuerda rápido.',
                'dias_de' => [
                    self::conCardio($cuerpoA(3, '10 a 12', 90), $cardioSuave('10 minutos')),
                    self::conCardio($cuerpoB(3, '10 a 12', 90), ['Elíptica', 1, '10 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Vuelta al gimnasio · 3 días',
                'objetivo' => 'empezar', 'nivel' => 'hace_tiempo', 'dias' => 3,
                'descripcion' => 'Cuerpo completo tres veces por semana para retomar. Sube el peso de a poco, semana a semana.',
                'dias_de' => [$cuerpoA(3, '10 a 12', 90), $cuerpoB(3, '10 a 12', 90), $cuerpoC(3, '10', 90)],
            ],
            [
                'nombre' => 'Vuelta al gimnasio · 4 días',
                'objetivo' => 'empezar', 'nivel' => 'hace_tiempo', 'dias' => 4,
                'descripcion' => 'Torso y pierna dos veces por semana: un día con máquinas y otro con peso libre.',
                'dias_de' => [
                    $torsoMaquinas(3, '10 a 12', 75),
                    $piernaMaquinas(3, '10 a 12', 75),
                    $torsoLibre(3, '10', 75),
                    $piernaLibre(3, '10', 75),
                ],
            ],

            // ---------- Bajar de peso ----------
            [
                'nombre' => 'Quema y tonifica · 2 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'nunca', 'dias' => 2,
                'descripcion' => 'Fuerza con máquinas y cardio al final. Suma caminatas los otros días: todo movimiento cuenta.',
                'dias_de' => [
                    self::conCardio($cuerpoA(2, '12 a 15', 45), ['Bicicleta estática', 1, '15 minutos', 0]),
                    self::conCardio($cuerpoB(2, '12 a 15', 45), ['Elíptica', 1, '15 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Quema y tonifica · 4 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'nunca', 'dias' => 4,
                'descripcion' => 'Dos días de fuerza con máquinas y dos de cardio suave con zona media. Alterna: fuerza, cardio, fuerza, cardio.',
                'dias_de' => [
                    self::conCardio($cuerpoA(2, '12 a 15', 45), ['Bicicleta estática', 1, '10 minutos', 0]),
                    $cardioYZona('25 minutos'),
                    self::conCardio($cuerpoB(2, '12 a 15', 45), $cardioSuave('10 minutos')),
                    $cardioYZona('30 minutos'),
                ],
            ],
            [
                'nombre' => 'Quema en circuito · 2 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'algo', 'dias' => 2,
                'descripcion' => 'Circuitos: un ejercicio tras otro y 90 segundos de descanso al cerrar la vuelta. Da 3 vueltas y termina con cardio.',
                'dias_de' => [$circuitoMaquinas('15 minutos'), $circuitoLibre('15 minutos')],
            ],
            [
                'nombre' => 'Definición · 2 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'hace_tiempo', 'dias' => 2,
                'descripcion' => 'Pesado al comienzo para no perder fuerza y un circuito corto al final para sumar gasto.',
                'dias_de' => [
                    self::conCardio($basicosA(4, '6 a 8', 120), ['Intervalos en bicicleta', 1, '8 rondas', 0, '30 segundos fuerte, 90 suave.']),
                    self::conCardio($basicosB(4, '6 a 8', 120), ['Remo ergómetro', 1, '10 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Definición · 3 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'hace_tiempo', 'dias' => 3,
                'descripcion' => 'Dos días pesados y uno de circuito metabólico. Cuida la comida: es lo que más pesa para bajar.',
                'dias_de' => [
                    self::conCardio($basicosA(4, '6 a 8', 120), ['Intervalos en bicicleta', 1, '8 rondas', 0, '30 segundos fuerte, 90 suave.']),
                    $circuitoLibre('15 minutos'),
                    self::conCardio($basicosB(4, '6 a 8', 120), $cardioSuave('15 minutos')),
                ],
            ],
            [
                'nombre' => 'Definición torso y pierna · 4 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'hace_tiempo', 'dias' => 4,
                'descripcion' => 'Torso y pierna dos veces por semana, con poco descanso en los accesorios y cardio al final.',
                'dias_de' => [
                    self::conCardio($torsoLibre(4, '8 a 10', 75), ['Intervalos en bicicleta', 1, '8 rondas', 0, '30 segundos fuerte, 90 suave.']),
                    self::conCardio($piernaLibre(4, '8 a 10', 75), $cardioSuave('15 minutos')),
                    self::conCardio($torsoMaquinas(4, '12', 45), ['Remo ergómetro', 1, '10 minutos', 0]),
                    self::conCardio($piernaMaquinas(4, '12', 45), ['Escaladora', 1, '10 minutos', 0]),
                ],
            ],

            // ---------- Ganar fuerza y músculo ----------
            [
                'nombre' => 'Fuerza desde cero · 3 días',
                'objetivo' => 'fuerza', 'nivel' => 'nunca', 'dias' => 3,
                'descripcion' => 'Cuerpo completo tres días. Anota lo que levantas y cada semana intenta sumar una repetición o un poco de peso.',
                'dias_de' => [$cuerpoA(3, '8 a 10', 90), $cuerpoB(3, '8 a 10', 90), $cuerpoC(3, '8 a 10', 90)],
            ],
            [
                'nombre' => 'Fuerza desde cero · 4 días',
                'objetivo' => 'fuerza', 'nivel' => 'nunca', 'dias' => 4,
                'descripcion' => 'Torso y pierna con máquinas, dos veces cada uno. Las máquinas guían el movimiento y dejan subir el peso con seguridad.',
                'dias_de' => [
                    $torsoMaquinas(3, '8 a 10', 90),
                    $piernaMaquinas(3, '8 a 10', 90),
                    $torsoMaquinas(3, '10 a 12', 75),
                    $piernaMaquinas(3, '10 a 12', 75),
                ],
            ],
            [
                'nombre' => 'Fuerza cuerpo completo · 2 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 2,
                'descripcion' => 'Dos días con los básicos. Descansa lo indicado: la fuerza se gana levantando bien, no rápido.',
                'dias_de' => [$basicosA(3, '8', 120), $basicosB(3, '8', 120)],
            ],
            [
                'nombre' => 'Fuerza básicos · 2 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 2,
                'descripcion' => 'Pocos ejercicios y pesados. Calienta con 2 o 3 series livianas antes de las de trabajo.',
                'dias_de' => [$basicosA(5, '5', 180), $basicosB(4, '5 a 6', 150)],
            ],
            [
                'nombre' => 'Empuje, tirón y pierna · 3 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 3,
                'descripcion' => 'Un día para empujar, uno para tirar y uno de pierna. Cada zona una vez por semana, con volumen alto.',
                'dias_de' => [
                    ['Empuje', 'Pecho, hombros y tríceps', [
                        ['Press de banca con barra', 4, '6 a 8', 150],
                        ['Press de hombros con mancuernas', 3, '8 a 10', 90],
                        ['Press inclinado con mancuernas', 3, '10', 90],
                        ['Cruce de poleas', 3, '12 a 15', 60],
                        ['Elevaciones laterales', 3, '15', 45],
                        ['Extensión de tríceps sobre la cabeza', 3, '12', 45],
                    ]],
                    ['Tirón', 'Espalda y bíceps', [
                        ['Dominadas asistidas', 4, '6 a 8', 120],
                        ['Remo con barra', 4, '8', 120],
                        ['Remo en máquina', 3, '10 a 12', 75],
                        ['Aperturas invertidas con mancuernas', 3, '15', 45],
                        ['Curl con barra', 3, '10', 60],
                        ['Curl en polea', 2, '12', 45],
                    ]],
                    ['Pierna', 'Piernas y glúteos', [
                        ['Sentadilla con barra', 4, '6 a 8', 150],
                        ['Peso muerto rumano con barra', 3, '8', 120],
                        ['Prensa de piernas', 3, '10 a 12', 90],
                        ['Curl femoral', 3, '12', 60],
                        ['Elevación de talones', 4, '12', 45],
                    ]],
                ],
            ],

            // ---------- Mantenerme ----------
            [
                'nombre' => 'Moverse y mantenerse · 2 días',
                'objetivo' => 'mantener', 'nivel' => 'nunca', 'dias' => 2,
                'descripcion' => 'Lo justo para sentirse bien: cuerpo completo con máquinas y un poco de cardio.',
                'dias_de' => [
                    self::conCardio($cuerpoA(2, '12 a 15', 60), ['Bicicleta estática', 1, '10 minutos', 0]),
                    self::conCardio($cuerpoB(2, '12 a 15', 60), ['Elíptica', 1, '10 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Moverse y mantenerse · 3 días',
                'objetivo' => 'mantener', 'nivel' => 'nunca', 'dias' => 3,
                'descripcion' => 'Dos días de cuerpo completo y uno de cardio con zona media.',
                'dias_de' => [
                    $cuerpoA(2, '12 a 15', 60),
                    $cardioYZona('20 minutos'),
                    $cuerpoB(2, '12 a 15', 60),
                ],
            ],
            [
                'nombre' => 'Moverse y mantenerse · 4 días',
                'objetivo' => 'mantener', 'nivel' => 'nunca', 'dias' => 4,
                'descripcion' => 'Fuerza y cardio intercalados, sin días muy pesados.',
                'dias_de' => [
                    $cuerpoA(2, '12 a 15', 60),
                    $cardioYZona('20 minutos'),
                    $cuerpoB(2, '12 a 15', 60),
                    $cardioYZona('25 minutos'),
                ],
            ],
            [
                'nombre' => 'Mantenerse · 4 días',
                'objetivo' => 'mantener', 'nivel' => 'algo', 'dias' => 4,
                'descripcion' => 'Torso y pierna dos veces por semana, sin llegar al límite. Cardio al final si te queda tiempo.',
                'dias_de' => [
                    $torsoMaquinas(3, '10 a 12', 75),
                    self::conCardio($piernaMaquinas(3, '10 a 12', 75), $cardioSuave('10 minutos')),
                    $torsoLibre(3, '10 a 12', 75),
                    self::conCardio($piernaLibre(3, '10 a 12', 75), ['Elíptica', 1, '10 minutos', 0]),
                ],
            ],
            [
                'nombre' => 'Mantenerse activo · 2 días',
                'objetivo' => 'mantener', 'nivel' => 'hace_tiempo', 'dias' => 2,
                'descripcion' => 'Dos días con los básicos para no perder fuerza, y cardio al final.',
                'dias_de' => [
                    self::conCardio($basicosA(3, '8', 120), ['Remo ergómetro', 1, '10 minutos', 0]),
                    self::conCardio($basicosB(3, '8', 120), $cardioSuave('15 minutos')),
                ],
            ],
            [
                'nombre' => 'Mantenerse activo · 3 días',
                'objetivo' => 'mantener', 'nivel' => 'hace_tiempo', 'dias' => 3,
                'descripcion' => 'Dos días de fuerza y uno de circuito. Variado para no aburrirse.',
                'dias_de' => [$basicosA(3, '8', 120), $circuitoLibre('10 minutos'), $basicosB(3, '8', 120)],
            ],

            // ---------- Glúteo y pierna ----------
            [
                'nombre' => 'Glúteo y pierna · 3 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Dos días para glúteo y pierna, y uno de torso para no dejarlo de lado.',
                'dias_de' => [$gluteo(4, 90), $torsoGluteo, $pierna(4, 90)],
            ],
            [
                'nombre' => 'Glúteo y pierna · 4 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 4,
                'descripcion' => 'Glúteo dos veces por semana, un día de pierna y uno de torso. Sube el peso del hip thrust cada semana que puedas.',
                'dias_de' => [$gluteo(4, 120), $torsoGluteo, $pierna(4, 120), $gluteo(3, 90)],
            ],

            // ---------- Express de 30 minutos ----------
            [
                'nombre' => 'Express 30 minutos · 2 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'algo', 'dias' => 2,
                'descripcion' => 'Para cuando hay poco tiempo: ejercicios de a pares, casi sin pausas. Entras, entrenas y sales en media hora.',
                'dias_de' => [$expressA, $expressB],
            ],
            [
                'nombre' => 'Express 30 minutos · 3 días',
                'objetivo' => 'mantener', 'nivel' => 'algo', 'dias' => 3,
                'descripcion' => 'Tres sesiones cortas de cuerpo completo. Mejor media hora tres veces que una hora que no llega.',
                'dias_de' => [$expressA, $expressB, $expressC],
            ],

            // ---------- Por grupos: 5 y 6 días ----------
            // Un grupo por día, para quien viene casi todos los días y quiere
            // saber qué toca hoy. Cierra la semana un día de cuerpo completo o de glúteo.
            [
                'nombre' => 'Grupos con máquinas · 5 días',
                'objetivo' => 'empezar', 'nivel' => 'nunca', 'dias' => 5,
                'descripcion' => 'Un grupo por día, casi todo con máquinas. Pesos livianos y buena técnica: con cinco días, cada músculo descansa casi una semana.',
                'dias_de' => [$pechoMaquinas, $espaldaMaquinas, $piernasMaquinas, $hombrosBrazosMaquinas,
                    self::conCardio($cuerpoA(2, '12 a 15', 60), $cardioSuave('10 minutos'))],
            ],
            [
                'nombre' => 'Grupos con máquinas · 6 días',
                'objetivo' => 'empezar', 'nivel' => 'nunca', 'dias' => 6,
                'descripcion' => 'Los cinco días por grupo y uno suave de cardio y zona media a mitad de semana.',
                'dias_de' => [$pechoMaquinas, $espaldaMaquinas, $piernasMaquinas, $cardioYZona('20 minutos'), $hombrosBrazosMaquinas,
                    self::conCardio($cuerpoA(2, '12 a 15', 60), $cardioSuave('10 minutos'))],
            ],
            [
                'nombre' => 'División por grupos · 5 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 5,
                'descripcion' => 'Pecho, espalda, piernas, hombros y brazos, y un día de cuerpo completo. Cada grupo con más series y algo más de peso.',
                'dias_de' => [$pechoIntermedio, $espaldaIntermedio, $piernasIntermedio, $hombrosBrazosIntermedio, $cuerpoC(3, '10 a 12', 75)],
            ],
            [
                'nombre' => 'División por grupos · 6 días',
                'objetivo' => 'fuerza', 'nivel' => 'algo', 'dias' => 6,
                'descripcion' => 'Los cinco grupos y un día de glúteo. Si un día no puedes venir, sigue con el que toca.',
                'dias_de' => [$pechoIntermedio, $espaldaIntermedio, $piernasIntermedio, $hombrosBrazosIntermedio, $gluteo(3, 90), $cuerpoC(3, '10 a 12', 75)],
            ],
            [
                'nombre' => 'División por grupos avanzada · 5 días',
                'objetivo' => 'fuerza', 'nivel' => 'hace_tiempo', 'dias' => 5,
                'descripcion' => 'Un grupo por día, con los básicos de barra al comienzo, pesados, y los accesorios después.',
                'dias_de' => [
                    ['Pecho', 'Pecho y tríceps', [
                        ['Press de banca con barra', 5, '6 a 8', 150],
                        ['Press inclinado con mancuernas', 4, '8 a 10', 90],
                        ['Press de pecho con mancuernas', 3, '8 a 10', 90],
                        ['Cruce de poleas', 3, '10 a 12', 60],
                        ['Fondos entre bancos', 3, '10', 60],
                    ]],
                    ['Espalda', 'Espalda y bíceps', [
                        ['Remo con barra', 5, '6 a 8', 120],
                        ['Dominadas asistidas', 4, '6 a 8', 120],
                        ['Remo con mancuerna a una mano', 4, '8 por brazo', 90],
                        ['Jalón al pecho', 3, '10', 75],
                        ['Hiperextensiones', 3, '10', 60],
                    ]],
                    ['Piernas', 'Cuádriceps, isquios y pantorrillas', [
                        ['Sentadilla con barra', 5, '6 a 8', 150],
                        ['Peso muerto rumano con barra', 4, '8', 120],
                        ['Prensa de piernas', 4, '8 a 10', 90],
                        ['Curl femoral', 3, '10', 60],
                        ['Elevación de talones', 4, '10 a 12', 45],
                    ]],
                    ['Hombros y brazos', 'Hombros, bíceps y tríceps', [
                        ['Press de hombros con mancuernas', 4, '6 a 8', 120],
                        ['Elevaciones laterales', 4, '10 a 12', 45],
                        ['Face pull en polea', 3, '12', 45],
                        ['Curl con barra', 4, '8 a 10', 60],
                        ['Press francés con mancuerna', 4, '8 a 10', 60],
                        ['Curl martillo', 3, '10', 45],
                    ]],
                    $gluteo(4, 120),
                ],
            ],
            [
                'nombre' => 'Quema por grupos · 5 días',
                'objetivo' => 'bajar_grasa', 'nivel' => 'algo', 'dias' => 5,
                'descripcion' => 'Fuerza con poco descanso y cardio al final, más un día de circuito. El quinto es suave: cardio largo y zona media.',
                'dias_de' => [
                    self::conCardio($torsoMaquinas(3, '12 a 15', 45), ['Bicicleta estática', 1, '15 minutos', 0]),
                    self::conCardio($piernaMaquinas(3, '12 a 15', 45), $cardioSuave('15 minutos')),
                    $circuitoLibre('15 minutos'),
                    self::conCardio($torsoLibre(3, '12 a 15', 45), ['Elíptica', 1, '15 minutos', 0]),
                    $cardioYZona('30 minutos'),
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
     * Carga lo que falta del catálogo y de las rutinas. Devuelve [ejercicios
     * del catálogo, rutinas del catálogo, rutinas nuevas].
     *
     * NO PISA NADA: un ejercicio o una rutina que ya está (por nombre) queda
     * como el gimnasio lo dejó, aunque lo haya editado en el panel. Solo se
     * crean los que faltan, y a los ejercicios sin mapa muscular se les completa.
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function cargar(): array
    {
        return DB::transaction(function () {
            $ids = [];
            $orden = (int) Ejercicio::max('orden');

            foreach (self::EJERCICIOS as $nombre => [$zona, $equipo, $indicacion]) {
                $ejercicio = Ejercicio::firstOrNew(['nombre' => $nombre]);

                if (! $ejercicio->exists) {
                    $ejercicio->fill(['zona' => $zona, 'equipo' => $equipo, 'indicacion' => $indicacion, 'activo' => true, 'orden' => ++$orden]);
                }

                if ($ejercicio->musculos === null && isset(self::MUSCULOS[$nombre])) {
                    $ejercicio->musculos = ['principal' => self::MUSCULOS[$nombre][0], 'secundarios' => self::MUSCULOS[$nombre][1]];
                }

                $ejercicio->save();
                $ids[$nombre] = $ejercicio->id;
            }

            $nuevas = 0;
            $ordenRutina = (int) Rutina::max('orden');

            foreach (self::rutinas() as $datos) {
                if (Rutina::where('nombre', $datos['nombre'])->exists()) {
                    continue;
                }

                $nuevas++;
                $rutina = Rutina::create([
                    'nombre' => $datos['nombre'],
                    'objetivo' => $datos['objetivo'],
                    'nivel' => $datos['nivel'],
                    'dias_por_semana' => $datos['dias'],
                    'descripcion' => $datos['descripcion'],
                    'activa' => true,
                    'orden' => ++$ordenRutina,
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

            return [count($ids), count(self::rutinas()), $nuevas];
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
