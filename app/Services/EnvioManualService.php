<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Envío de un correo suelto a un socio, elegido a mano desde el panel.
 *
 * No es lo mismo que los avisos automáticos —vencimientos, bienvenidas— que
 * viven en NotificacionService y salen solos. Esto es alguien del mesón
 * decidiendo escribirle a una persona concreta.
 *
 * QUEDA CONSTANCIA PASE LO QUE PASE. Antes, si el correo no salía, la fila de
 * la notificación se quedaba en «Pendiente» para siempre y el motivo del fallo
 * solo llegaba al navegador de quien lo intentó: al día siguiente nadie sabía
 * que ese aviso no se había mandado ni por qué.
 */
class EnvioManualService
{
    public function __construct(private readonly CorreoService $correo)
    {
    }

    /**
     * Compone el correo sin mandarlo, para poder verlo antes.
     *
     * @return array{asunto:string,contenido:string,destino:string}
     *
     * @throws ValidationException
     */
    public function componer(Cliente $cliente, TipoNotificacion $plantilla, ?string $nota = null): array
    {
        $this->exigirCorreo($cliente);

        $inscripcion = $this->ultimaInscripcion($cliente);
        $datos = $this->datosDelSocio($cliente, $inscripcion);

        $contenido = $this->rellenar($plantilla->plantilla_email, $datos);

        if ($nota !== null && trim($nota) !== '') {
            $contenido = $this->conNotaDelMeson($contenido, $nota);
        }

        $asunto = $this->rellenar($plantilla->asunto_email, $datos);

        return [
            'asunto' => $asunto,
            'contenido' => $contenido,
            'destino' => $cliente->email,
            // Lo que la plantilla pide y este servicio no sabe dar. Va a la
            // vista previa para que se vea, y enviar() lo rechaza.
            'pendientes' => $this->variablesSinRellenar($asunto . ' ' . $contenido),
        ];
    }

    /**
     * Las variables que quedaron a medio camino.
     *
     * Una plantilla puede pedir {loquesea} y este servicio no tener con qué
     * rellenarlo: entonces las llaves salen tal cual en el correo del socio.
     * Ha pasado: dos plantillas de vencimiento usan {nombre_cliente} y el envío
     * manual no la conocía.
     *
     * @return list<string>
     */
    private function variablesSinRellenar(string $texto): array
    {
        preg_match_all('/\{([a-z_]+)\}/i', $texto, $encontradas);

        return array_values(array_unique($encontradas[1]));
    }

    /**
     * Compone, guarda y manda.
     *
     * @throws ValidationException si el socio no tiene correo
     */
    public function enviar(Cliente $cliente, TipoNotificacion $plantilla, ?string $nota = null): Notificacion
    {
        $correo = $this->componer($cliente, $plantilla, $nota);

        // NO se manda a medio rellenar. Un correo que le llega al socio
        // diciendo «la membresía de {nombre_cliente} vence» es peor que uno que
        // no sale: el socio lo ve, el gimnasio no se entera, y no se puede
        // recoger. Se para aquí y se dice qué plantilla hay que arreglar.
        if ($correo['pendientes'] !== []) {
            throw ValidationException::withMessages([
                'plantilla_id' => sprintf(
                    'La plantilla «%s» usa %s y no hay con qué rellenarlo. Corrígela en Plantillas antes de mandarla.',
                    $plantilla->nombre,
                    '{' . implode('}, {', $correo['pendientes']) . '}'
                ),
            ]);
        }

        $inscripcion = $this->ultimaInscripcion($cliente);

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $plantilla->id,
            'id_cliente' => $cliente->id,
            'id_inscripcion' => $inscripcion?->id,
            'email_destino' => $correo['destino'],
            'asunto' => $correo['asunto'],
            'contenido' => $correo['contenido'],
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
            'enviado_por_user_id' => auth()->id(),
            'nota_personalizada' => $nota,
        ]);

        $notificacion->registrarLog(
            'programada',
            'Envío manual desde el panel por ' . (auth()->user()->name ?? 'alguien del mesón')
        );

        try {
            $this->correo->enviar($correo['destino'], $correo['asunto'], $correo['contenido'], $cliente->nombre_completo);
        } catch (\Throwable $e) {
            // La fila SE QUEDA, marcada como fallida y con el motivo escrito.
            // Antes se quedaba «Pendiente» para siempre y el porqué solo lo veía
            // quien estaba delante en ese momento.
            $notificacion->marcarComoFallida($e->getMessage());

            throw ValidationException::withMessages([
                'envio' => 'No se pudo enviar: ' . $e->getMessage(),
            ]);
        }

        $notificacion->marcarComoEnviada();

        return $notificacion;
    }

    /**
     * A quién se le puede escribir.
     *
     * Solo socios CON correo: los que no tienen no se pueden avisar por aquí y
     * ofrecerlos solo sirve para llegar al final y encontrarse con que no.
     */
    public function buscar(string $texto, int $cuantos = 10)
    {
        return Cliente::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) use ($texto) {
                $q->where('nombres', 'like', "%{$texto}%")
                    ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                    ->orWhere('apellido_materno', 'like', "%{$texto}%")
                    ->orWhere('run_pasaporte', 'like', "%{$texto}%")
                    ->orWhere('email', 'like', "%{$texto}%");
            })
            ->with(['inscripciones' => fn ($q) => $q->latest()->limit(1)->with('membresia')])
            ->orderBy('apellido_paterno')
            ->limit($cuantos)
            ->get()
            ->map(function (Cliente $c) {
                $i = $c->inscripciones->first();

                return [
                    'id' => $c->id,
                    'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                    'rut' => $c->run_pasaporte,
                    'email' => $c->email,
                    'plan' => $i?->membresia?->nombre,
                    'vence' => $i?->fecha_vencimiento?->format('d/m/Y'),
                    'activo' => (bool) $c->activo,
                ];
            });
    }

    /**
     * @throws ValidationException
     */
    private function exigirCorreo(Cliente $cliente): void
    {
        if (empty($cliente->email)) {
            throw ValidationException::withMessages([
                'cliente_id' => "{$cliente->nombres} no tiene correo registrado. Añádelo en su ficha antes de escribirle.",
            ]);
        }
    }

    private function ultimaInscripcion(Cliente $cliente): ?Inscripcion
    {
        return Inscripcion::where('id_cliente', $cliente->id)
            ->with(['membresia', 'pagos'])
            ->latest()
            ->first();
    }

    /**
     * Sustituye las variables de la plantilla, {nombre} y compañía.
     *
     * Lo que entra SE ESCAPA: el nombre de un socio va a parar dentro del HTML
     * del correo, y un apellido con un `<` de por medio partiría el mensaje.
     *
     * @param array<string,string> $datos
     */
    private function rellenar(?string $plantilla, array $datos): string
    {
        $buscar = [];
        $poner = [];

        foreach ($datos as $clave => $valor) {
            $buscar[] = '{' . $clave . '}';
            $poner[] = e((string) $valor);
        }

        return str_replace($buscar, $poner, (string) $plantilla);
    }

    private function conNotaDelMeson(string $contenido, string $nota): string
    {
        $bloque = '<div style="background:#fffbf0;border-left:4px solid #FFC107;padding:20px;margin:20px 0;">'
            . '<p style="margin:0;"><strong>Nota del gimnasio:</strong></p>'
            . '<p style="margin:10px 0 0 0;">' . nl2br(e($nota)) . '</p></div>';

        // Si la plantilla no trae </body> —las hay que son solo un trozo de
        // HTML— la nota va al final, que es donde se espera leerla.
        return str_contains($contenido, '</body>')
            ? str_replace('</body>', $bloque . '</body>', $contenido)
            : $contenido . $bloque;
    }

    /**
     * Lo que puede aparecer entre llaves en una plantilla.
     *
     * @return array<string,string>
     */
    private function datosDelSocio(Cliente $cliente, ?Inscripcion $inscripcion): array
    {
        $datos = [
            'nombre' => $cliente->nombre_completo,
            // Las plantillas de vencimiento la usan y NADIE la rellenaba en el
            // envio manual: el correo salia diciendo «la membresia de
            // {nombre_cliente} vence en 30 dias», con las llaves y todo. El
            // envio automatico si la rellenaba, asi que la misma plantilla se
            // veia bien por un camino y rota por el otro.
            'nombre_cliente' => $cliente->nombre_completo,
            'nombres' => $cliente->nombres,
            'apellido' => $cliente->apellido_paterno,
            'email' => $cliente->email,
            'celular' => $cliente->celular ?: 'No registrado',
            'es_menor_edad' => $cliente->es_menor_edad ? 'sí' : 'no',
            // La de tutor legal la usa. Si el socio no es menor no hay
            // apoderado, y entonces esa plantilla no es para el.
            'nombre_apoderado' => $cliente->apoderado_nombre ?: '',
        ];

        if (! $inscripcion) {
            return $datos;
        }

        $pagado = (int) $inscripcion->pagos->sum('monto_abonado');
        $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0);
        $pendiente = max(0, $total - $pagado);

        $datos += [
            // El plan pudo darse de baja: entonces no hay nombre que poner.
            'membresia' => $inscripcion->membresia?->nombre ?? 'Sin plan',
            'precio' => $this->pesos($total),
            'fecha_inicio' => $inscripcion->fecha_inicio?->format('d/m/Y') ?? '',
            'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? '',
            'dias_restantes' => (string) $this->diasQueQuedan($inscripcion),
            'monto_total' => $this->pesos($total),
            'monto_pagado' => $this->pesos($pagado),
            'total_pagado' => $this->pesos($pagado),
            'monto_pendiente' => $this->pesos($pendiente),
            'saldo_pendiente' => $this->pesos($pendiente),
        ];

        if ($inscripcion->fecha_pausa_inicio) {
            $datos['fecha_pausa'] = Carbon::parse($inscripcion->fecha_pausa_inicio)->format('d/m/Y');
        }

        if ($inscripcion->fecha_pausa_fin) {
            $fin = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
            $datos['fecha_reactivacion'] = $fin;
            $datos['fecha_activacion'] = $fin;
        }

        $ultimo = $inscripcion->pagos->sortByDesc('fecha_pago')->first();

        if ($ultimo) {
            $datos['fecha_pago'] = Carbon::parse($ultimo->fecha_pago)->format('d/m/Y');
            $datos['monto_ultimo_pago'] = $this->pesos((int) $ultimo->monto_abonado);
        }

        return $datos;
    }

    private function diasQueQuedan(Inscripcion $inscripcion): int
    {
        if (! $inscripcion->fecha_vencimiento) {
            return 0;
        }

        return max(0, (int) today()->diffInDays($inscripcion->fecha_vencimiento->startOfDay(), false));
    }

    private function pesos(int $monto): string
    {
        return number_format($monto, 0, ',', '.');
    }
}
