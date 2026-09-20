<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * El chat de WhatsApp dentro del panel. POR AHORA, UNA MAQUETA.
 *
 * Aquí no se manda ni se recibe nada: las conversaciones son de mentira,
 * armadas con socios de verdad para poder juzgar si la pantalla sirve antes de
 * decidir por dónde se conecta WhatsApp. Esa decisión no es de programación:
 *
 *   - Por la API oficial de Meta, el chat vive aquí dentro y se paga por
 *     mensaje (en Chile, centavos), pero pide un número que nunca haya estado
 *     en WhatsApp normal y verificar la empresa.
 *   - Por la vía no oficial se usa el número de siempre y no se paga nada, pero
 *     va contra los términos de WhatsApp y pueden bloquear ese número, que es
 *     por el que escriben los socios.
 *
 * Mientras eso no se decida, esta pantalla deja claro en todo momento que no
 * manda nada: una maqueta que parezca funcionar es peor que no tenerla, porque
 * alguien escribiría un mensaje creyendo que sale.
 */
class WhatsappController extends Controller
{
    /** Cuántas conversaciones de prueba se arman. */
    private const CUANTAS = 8;

    public function __invoke()
    {
        return Inertia::render('Whatsapp', [
            'conversaciones' => $this->conversaciones(),
            'plantillas' => $this->plantillas(),
            'esMaqueta' => true,
        ]);
    }

    /**
     * Conversaciones de mentira con socios de verdad.
     *
     * Con socios reales porque lo que hay que juzgar es si con ESTA gente y
     * ESTOS mensajes el mesón trabaja mejor; con nombres inventados, la
     * pantalla se ve bonita y no dice nada.
     *
     * @return list<array<string,mixed>>
     */
    private function conversaciones(): array
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
    private function plantillas(): array
    {
        $gimnasio = \App\Support\Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM';

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
