<?php

namespace App\Support;

use App\Models\Inscripcion;
use Illuminate\Support\Carbon;

/**
 * Las conversaciones de WhatsApp del panel. POR AHORA, UNA MAQUETA.
 *
 * Aquí no se manda ni se recibe nada: son conversaciones de mentira armadas
 * con socios DE VERDAD —los que tienen la membresía por vencer o recién
 * vencida—, para poder juzgar si la pantalla sirve antes de decidir por dónde
 * se conecta WhatsApp: la API oficial de Meta (número aparte, verificación de
 * la empresa, centavos por mensaje) o la vía no oficial (el número de siempre,
 * sin costo, pero pueden bloquearlo).
 *
 * Vive aquí y no dentro de una pantalla porque la piden DOS —el resumen, en su
 * columna lateral, y la pantalla propia de WhatsApp— y con dos copias la
 * primera corrección las dejaría distintas.
 */
class ChatDeWhatsapp
{
    /** Cuántas conversaciones de prueba se arman. */
    public const CUANTAS = 8;

    /**
     * Conversaciones de mentira con socios de verdad.
     *
     * Con socios reales porque lo que hay que juzgar es si con ESTA gente y
     * ESTOS mensajes el mesón trabaja mejor; con nombres inventados, la
     * pantalla se ve bonita y no dice nada.
     *
     * @return list<array<string,mixed>>
     */
    public static function conversaciones(): array
    {
        $hoy = Carbon::today();

        // Los que tienen algo de qué hablar: su plan vence pronto o ya venció.
        $inscripciones = Inscripcion::query()
            ->sinPases()
            ->whereIn('id_estado', [100, 102])
            ->whereBetween('fecha_vencimiento', [$hoy->copy()->subDays(20), $hoy->copy()->addDays(10)])
            ->whereHas('cliente', fn ($q) => $q->where('activo', true)->whereNotNull('celular'))
            ->with(['cliente:id,uuid,nombres,apellido_paterno,celular', 'membresia:id,nombre'])
            ->orderBy('fecha_vencimiento')
            ->limit(self::CUANTAS)
            ->get();

        return $inscripciones->values()->map(function (Inscripcion $i, int $n) use ($hoy) {
            $cliente = $i->cliente;
            $dias = (int) $hoy->diffInDays($i->fecha_vencimiento, false);
            $vencida = $dias < 0;

            $mio = $vencida
                ? "Hola {$cliente->nombres}, tu {$i->membresia?->nombre} venció el " . $i->fecha_vencimiento->format('d/m') . '. ¿Te esperamos esta semana?'
                : "Hola {$cliente->nombres}, tu {$i->membresia?->nombre} vence el " . $i->fecha_vencimiento->format('d/m') . '. Cuando quieras la renovamos en el mesón.';

            // Una de cada tres contesta: así se ve cómo queda una conversación
            // con respuesta y una que quedó esperando.
            $respuesta = $n % 3 === 0
                ? ($vencida ? 'Ya, paso mañana a renovar' : 'Gracias, la renuevo el viernes')
                : null;

            return [
                'id' => $n + 1,
                'socio_uuid' => $cliente->uuid,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}"),
                'celular' => $cliente->celular,
                'motivo' => $vencida ? 'Venció hace ' . abs($dias) . ' días' : "Vence en {$dias} días",
                'urgente' => $vencida,
                'mensajes' => array_values(array_filter([
                    [
                        'mio' => true,
                        'texto' => $mio,
                        'hora' => $hoy->copy()->subDays($n)->setTime(9, 12)->format('d/m H:i'),
                        'estado' => 'leído',
                    ],
                    $respuesta ? [
                        'mio' => false,
                        'texto' => $respuesta,
                        'hora' => $hoy->copy()->subDays($n)->setTime(9, 40)->format('d/m H:i'),
                        'estado' => null,
                    ] : null,
                ])),
                'sin_responder' => $respuesta === null,
            ];
        })->all();
    }

    /**
     * Lo que se escribe una y otra vez, listo para meter en el cuadro.
     *
     * Es el ahorro de verdad: en el mesón se manda veinte veces al mes el mismo
     * «tu mensualidad vence». Con la API oficial, además, estos textos son las
     * plantillas que Meta tiene que aprobar para poder escribir primero.
     *
     * @return list<array<string,string>>
     */
    public static function plantillas(): array
    {
        $gimnasio = Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM';

        return [
            [
                'nombre' => 'Vence pronto',
                'texto' => "Hola {nombre}, te escribimos de {$gimnasio}: tu {plan} vence el {vence}. Cuando quieras la renovamos en el mesón.",
            ],
            [
                'nombre' => 'Ya venció',
                'texto' => "Hola {nombre}, tu {plan} venció el {vence}. Te echamos de menos: pásate cuando puedas y la dejamos al día.",
            ],
            [
                'nombre' => 'Queda debiendo',
                'texto' => "Hola {nombre}, te quedó un saldo pendiente de tu {plan}. Lo puedes pagar en el mesón cuando vengas.",
            ],
        ];
    }
}
