<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\BusquedaDeSocio;

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
    public function componer(Cliente $cliente, TipoNotificacion $plantilla, ?string $nota = null, array $extra = []): array
    {
        $this->exigirCorreo($cliente);

        $inscripcion = $this->ultimaInscripcion($cliente);
        // Lo que traiga quien llama —el enlace de un contrato, por ejemplo— se
        // suma a lo del socio.
        $datos = array_merge($this->datosDelSocio($cliente, $inscripcion), $extra);

        $contenido = $this->rellenar($plantilla->plantilla_email, $datos);

        if ($nota !== null && trim($nota) !== '') {
            $contenido = $this->conNotaDelMeson($contenido, $nota);
        }

        $asunto = $this->rellenar($plantilla->asunto_email, $datos);

        return [
            'asunto' => $asunto,
            'contenido' => $contenido,
            // La misma regla que los avisos automáticos: el menor con
            // apoderado recibe por el apoderado.
            'destino' => $cliente->correoParaAvisos(),
            // Lo que la plantilla pide y este servicio no sabe dar. Va a la
            // vista previa para que se vea, y enviar() lo rechaza.
            'pendientes' => $this->variablesSinRellenar($asunto . ' ' . $contenido),
            // Lo mismo con el texto de ejemplo entre corchetes: [XX%].
            'marcadores' => self::marcadoresSinEditar($asunto . ' ' . $contenido),
            'muestras' => self::muestrasDeEjemplo($plantilla),
        ];
    }

    /**
     * Las variables de un socio, más las que traiga quien llama.
     *
     * Para los correos que no salen de aquí pero usan las mismas plantillas:
     * el contrato por firmar suma su enlace a lo de siempre.
     *
     * @param array<string,string> $extra
     * @return array<string,string>
     */
    public function variables(Cliente $cliente, array $extra = []): array
    {
        return array_merge($this->datosDelSocio($cliente, $this->ultimaInscripcion($cliente)), $extra);
    }

    /**
     * Las mismas variables, pero de UNA inscripción concreta.
     *
     * Los avisos automáticos hablan de la inscripción que los provocó —la que
     * vence, la que se pagó, la que se pausó—, que no siempre es la última del
     * socio: el aviso de «vence pronto» de un plan que ya se renovó por
     * adelantado tiene que hablar del que vence, no del nuevo.
     *
     * @param array<string,string> $extra
     * @return array<string,string>
     */
    public function variablesDeInscripcion(Inscripcion $inscripcion, array $extra = []): array
    {
        // Los pagos se leen de nuevo: el aviso de «pago completado» se arma
        // justo después de anotar el pago, y la relación cargada antes no lo
        // traería.
        $inscripcion->load(['membresia', 'pagos.metodoPago']);

        return array_merge($this->datosDelSocio($inscripcion->cliente, $inscripcion), $extra);
    }

    /**
     * Rellena una plantilla con variables ya armadas.
     *
     * @param array<string,string> $datos
     * @return array{asunto:string, contenido:string, pendientes:list<string>}
     */
    public function componerCon(TipoNotificacion $plantilla, array $datos): array
    {
        $asunto = $this->rellenar($plantilla->asunto_email, $datos);
        $contenido = $this->rellenar($plantilla->plantilla_email, $datos);

        return [
            'asunto' => $asunto,
            'contenido' => $contenido,
            'pendientes' => $this->variablesSinRellenar($asunto . ' ' . $contenido),
            'muestras' => self::muestrasDeEjemplo($plantilla),
        ];
    }

    /**
     * El texto de muestra de las plantillas viejas que sigue en la plantilla.
     *
     * Las plantillas de antes traían un socio inventado —«Juan Pérez»,
     * «$$25.000»— que el motor viejo cambiaba a mano por los datos de verdad.
     * El de ahora solo rellena {variables}: una plantilla editada con ese
     * texto, o una de fábrica que aún no se actualizó en el servidor, le
     * llegaría al socio con el nombre y el monto del ejemplo.
     *
     * Se mira la PLANTILLA, no el correo ya rellenado: un socio puede llamarse
     * Juan Pérez de verdad, y su vencimiento puede caer el 06/03/2026.
     *
     * @return list<string>
     */
    public static function muestrasDeEjemplo(?TipoNotificacion $plantilla): array
    {
        if (! $plantilla) {
            return [];
        }

        $texto = $plantilla->asunto_email . ' ' . $plantilla->plantilla_email;

        return array_values(array_filter(
            \App\Support\PlantillasDeFabrica::MUESTRAS,
            fn (string $muestra) => str_contains($texto, $muestra)
        ));
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
    public static function variablesSinRellenar(string $texto): array
    {
        preg_match_all('/\{([a-z_]+)\}/i', $texto, $encontradas);

        return array_values(array_unique($encontradas[1]));
    }

    /**
     * El texto de ejemplo entre corchetes que nadie cambió.
     *
     * Las plantillas de promoción, anuncio, evento y horario especial vienen
     * con huecos como «[XX%]» o «[NOMBRE DEL EVENTO]» para escribirlos antes
     * de mandar. No son variables —nada los rellena— y se mandaban tal cual:
     * el socio recibía «[XX%] de descuento».
     *
     * Se ignoran los comentarios HTML y los estilos: «<!--[if mso]>» y
     * «a[x-apple-data-detectors]» llevan corchetes y no son huecos.
     *
     * @return list<string>
     */
    public static function marcadoresSinEditar(string $texto): array
    {
        $texto = preg_replace(['/<!--.*?-->/s', '/<style\b.*?<\/style>/is'], '', $texto) ?? $texto;

        preg_match_all('/\[[^\]\n]{3,}\]/u', $texto, $encontrados);

        return array_values(array_unique($encontrados[0]));
    }

    /**
     * Rechaza un correo que todavía tiene huecos por llenar: variables que
     * nadie sabe rellenar o texto de ejemplo entre corchetes.
     *
     * @param array{pendientes?:list<string>, marcadores?:list<string>} $correo
     *
     * @throws ValidationException
     */
    public static function exigirCompleto(array $correo, string $campo, string $cual): void
    {
        if (($correo['pendientes'] ?? []) !== []) {
            throw ValidationException::withMessages([
                $campo => sprintf(
                    '%s usa %s y no hay con qué rellenarlo. Corrígelo antes de mandarlo.',
                    $cual,
                    '{' . implode('}, {', $correo['pendientes']) . '}'
                ),
            ]);
        }

        if (($correo['muestras'] ?? []) !== []) {
            throw ValidationException::withMessages([
                $campo => sprintf(
                    '%s todavía tiene el texto de ejemplo «%s»: edita la plantilla en Configuración → Plantillas de correo antes de mandarlo.',
                    $cual,
                    implode('», «', array_slice($correo['muestras'], 0, 3))
                ),
            ]);
        }

        if (($correo['marcadores'] ?? []) !== []) {
            throw ValidationException::withMessages([
                $campo => sprintf(
                    '%s todavía tiene texto de ejemplo por cambiar: %s. Edítalo antes de mandarlo (las plantillas, en Configuración → Plantillas de correo).',
                    $cual,
                    implode(', ', array_slice($correo['marcadores'], 0, 3)) . (count($correo['marcadores']) > 3 ? '…' : '')
                ),
            ]);
        }
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

        // Ni con el texto de ejemplo de la plantilla sin cambiar.
        self::exigirCompleto(
            ['marcadores' => $correo['marcadores'], 'muestras' => $correo['muestras']],
            'plantilla_id',
            "La plantilla «{$plantilla->nombre}»"
        );

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
        } catch (\App\Services\Correo\TopeDelDiaAlcanzado $e) {
            // No falló: hoy ya no caben más. Queda para mañana sin gastar un
            // intento, y se dice así para que nadie lo vuelva a mandar.
            $notificacion->aplazarParaManana($e->getMessage());

            throw ValidationException::withMessages([
                'envio' => $e->getMessage() . ' Este quedó programado para mañana.',
            ]);
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
     * Vuelve a intentar UN correo que no salió.
     *
     * Solo ese. El reenvío del panel viejo ponía esta notificación en pendiente
     * y a continuación llamaba a «enviar todas las pendientes»: reintentar un
     * correo fallido disparaba de golpe todos los demás que estuvieran en cola,
     * que es lo último que quiere quien solo intentaba arreglar uno. Y después
     * decía «reenviada correctamente» sin haber mirado si esta había salido.
     *
     * @throws ValidationException
     */
    public function reenviar(Notificacion $notificacion): Notificacion
    {
        if (empty($notificacion->email_destino)) {
            throw ValidationException::withMessages([
                'envio' => 'Esa notificación no tiene destinatario.',
            ]);
        }

        // El del contrato quedó guardado SIN su enlace, a propósito: reenviarlo
        // mandaría un correo para firmar sin dónde firmar.
        if (in_array($notificacion->tipoNotificacion?->codigo, ContratoDigitalService::PLANTILLAS, true)) {
            throw ValidationException::withMessages([
                'envio' => 'Los contratos se vuelven a mandar desde la ficha del socio: cada envío lleva un enlace nuevo.',
            ]);
        }

        // Un aviso automático que no salió porque su plantilla pedía algo que
        // no se sabe rellenar se guarda tal cual, con las llaves. Reenviarlo
        // le mandaría al socio justo el correo roto que se paró a propósito.
        $sueltas = self::variablesSinRellenar($notificacion->asunto . ' ' . $notificacion->contenido);

        if ($sueltas !== []) {
            throw ValidationException::withMessages([
                'envio' => 'Ese correo quedó con {' . implode('}, {', $sueltas) . '} sin rellenar. Corrige la plantilla en Configuración → Plantillas de correo; el próximo aviso saldrá bien.',
            ]);
        }

        self::exigirCompleto(
            [
                'marcadores' => self::marcadoresSinEditar($notificacion->asunto . ' ' . $notificacion->contenido),
                // Lo guardado ya viene rellenado y no se puede separar el
                // ejemplo de un dato real, así que se mira su plantilla: si
                // todavía lleva el texto de muestra, el correo también.
                'muestras' => array_values(array_filter(
                    self::muestrasDeEjemplo($notificacion->tipoNotificacion),
                    fn (string $m) => str_contains($notificacion->asunto . ' ' . $notificacion->contenido, $m)
                )),
            ],
            'envio',
            'Ese correo'
        );

        /*
         * UN SOLO ENVIO AUNQUE LLEGUEN DOS. El estado se miraba sin bloquear:
         * con un doble clic las dos peticiones lo veian «fallida», las dos
         * mandaban y el socio recibia el correo dos veces. Ahora se bloquea la
         * fila y se vuelve a mirar el estado DENTRO de la transaccion; la
         * segunda espera a que la primera termine y se encuentra «enviada».
         *
         * El fallo del servidor de correo se devuelve en vez de lanzarse dentro:
         * una excepcion ahi desharia la marca de «fallida», que es justo la
         * constancia que tiene que quedar.
         */
        $fallo = DB::transaction(function () use ($notificacion) {
            $actual = Notificacion::whereKey($notificacion->getKey())->lockForUpdate()->first();

            if (! $actual || (int) $actual->id_estado === Notificacion::ESTADO_ENVIADO) {
                throw ValidationException::withMessages([
                    'envio' => 'Ese correo ya se envió.',
                ]);
            }

            /*
             * SOLO SE REENVÍA LO QUE FALLÓ. Mirando únicamente «ya enviado»,
             * se podía reenviar uno CANCELADO —que alguien paró a propósito,
             * o que se canceló porque el socio se fue a la papelera— o uno
             * pendiente que igual iba a salir en la tanda: dos correos.
             */
            if ((int) $actual->id_estado !== Notificacion::ESTADO_FALLIDO) {
                throw ValidationException::withMessages([
                    'envio' => 'Solo se puede reenviar un correo que no salió.',
                ]);
            }

            $notificacion->setRawAttributes($actual->getAttributes(), true);
            $notificacion->registrarLog('reintentando', 'Reenvío manual desde el panel');

            try {
                $this->correo->enviar(
                    $notificacion->email_destino,
                    $notificacion->asunto,
                    $notificacion->contenido
                );
            } catch (\App\Services\Correo\TopeDelDiaAlcanzado $e) {
                $notificacion->aplazarParaManana($e->getMessage());

                return $e->getMessage() . ' Quedó programado para mañana.';
            } catch (\Throwable $e) {
                $notificacion->marcarComoFallida($e->getMessage());

                return $e->getMessage();
            }

            $notificacion->marcarComoEnviada();

            return null;
        });

        if ($fallo !== null) {
            throw ValidationException::withMessages([
                'envio' => 'Tampoco salió esta vez: ' . $fallo,
            ]);
        }

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
            ->conCorreoParaAvisos()
            // La misma búsqueda que el resto del panel: el RUT se compara sin
            // puntos ni guion, porque nadie los teclea.
            ->where(fn ($q) => BusquedaDeSocio::aplicar($q, $texto))
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
                    'email' => $c->correoParaAvisos(),
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
        if ($cliente->correoParaAvisos() === null) {
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
        /*
         * TODAS las variables se definen SIEMPRE, aunque no haya con qué
         * llenarlas.
         *
         * Una variable sin valor y una variable que no existe son cosas
         * distintas: la primera es «este socio no tiene plan», la segunda es
         * «alguien escribió mal la plantilla». Si las que no aplican se
         * quedaran sin definir, el aviso de plantilla rota saltaría con una
         * plantilla correcta cada vez que el socio no tuviera inscripción.
         */
        $datos = array_fill_keys([
            'membresia', 'precio', 'fecha_inicio', 'fecha_vencimiento', 'dias_restantes',
            'monto_total', 'monto_pagado', 'total_pagado', 'monto_pendiente', 'saldo_pendiente',
            'tipo_pago', 'fecha_pago', 'monto_ultimo_pago', 'metodo_pago', 'fecha_registro',
            'fecha_pausa', 'fecha_reactivacion', 'fecha_activacion', 'motivo_pausa',
        ], '');

        // Los del gimnasio salen de Configuración: antes iban escritos dentro
        // de cada plantilla —teléfono, correo, Instagram, el enlace del mapa— y
        // cambiar el teléfono obligaba a corregir trece correos a mano.
        $datos += self::datosDelGimnasio();

        $datos += [
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
            // La constancia al tutor legal identifica a los dos con su RUT.
            'rut_apoderado' => $cliente->apoderado_rut ?: 'No registrado',
            'rut' => $cliente->run_pasaporte ?: 'No registrado',
            'fecha_nacimiento' => $cliente->fecha_nacimiento
                ? Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y')
                : 'No registrada',
            // El de Configuración → Página web; vacío si no hay.
            'enlace_resena' => (string) \App\Support\Ajustes::obtener('web.resenas'),
        ];

        if (! $inscripcion) {
            return $datos;
        }

        $pagado = (int) $inscripcion->pagos->sum('monto_abonado');
        $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0);
        $pendiente = max(0, $total - $pagado);

        // array_merge y no `+=`: los valores de arriba estan puestos en blanco
        // a proposito y `+=` no pisa lo que ya existe, asi que se quedarian.
        $datos = array_merge($datos, [
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
            'tipo_pago' => match (true) {
                $pendiente === 0 => 'Completo',
                $pagado === 0 => 'Pendiente',
                default => 'Parcial',
            },
            'fecha_registro' => $inscripcion->created_at?->format('d/m/Y H:i') ?? '',
        ]);

        if ($inscripcion->fecha_pausa_inicio) {
            $datos['fecha_pausa'] = Carbon::parse($inscripcion->fecha_pausa_inicio)->format('d/m/Y');
            $datos['motivo_pausa'] = $inscripcion->razon_pausa ?: 'Sin indicar';
            // Una pausa indefinida no tiene día de vuelta: en blanco, el correo
            // diría «Reactivación estimada:» y nada más.
            $datos['fecha_reactivacion'] = 'Cuando la reactives';
        }

        if ($inscripcion->fecha_pausa_fin) {
            $fin = Carbon::parse($inscripcion->fecha_pausa_fin)->format('d/m/Y');
            $datos['fecha_reactivacion'] = $fin;
            $datos['fecha_activacion'] = $fin;
        }

        // El último por fecha y, el mismo día, el último anotado.
        $ultimo = $inscripcion->pagos->sortBy([['fecha_pago', 'desc'], ['id', 'desc']])->first();

        if ($ultimo) {
            $datos['fecha_pago'] = Carbon::parse($ultimo->fecha_pago)->format('d/m/Y');
            $datos['monto_ultimo_pago'] = $this->pesos((int) $ultimo->monto_abonado);
            $datos['metodo_pago'] = $ultimo->metodoPago?->nombre ?? 'No especificado';
        }

        return $datos;
    }

    /**
     * Los datos del gimnasio que van en los correos, desde Configuración.
     *
     * Cada uno con el respaldo que tiene sentido: sin correo de contacto, el
     * de salida —es a donde el socio va a responder de todas formas—; sin
     * teléfono, el WhatsApp; sin enlace de Maps, una búsqueda de la dirección.
     * Lo que no está en ningún lado queda vacío: inventar un dato de contacto
     * es peor que no darlo.
     *
     * @return array<string,string>
     */
    public static function datosDelGimnasio(): array
    {
        $texto = fn (string $clave) => trim((string) \App\Support\Ajustes::obtener($clave));

        $telefono = $texto('gimnasio.telefono') ?: $texto('web.whatsapp');
        $direccion = implode(', ', array_filter([$texto('gimnasio.direccion'), $texto('gimnasio.comuna')]));
        $mapa = $texto('web.google_maps')
            ?: ($direccion !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($direccion) : '');

        return [
            'gimnasio' => $texto('gimnasio.nombre') ?: 'PRO GYM',
            'ciudad' => $texto('web.ciudad'),
            'telefono_gimnasio' => $telefono,
            // Para el botón «Llámanos»: el enlace tel: no admite espacios.
            'telefono_enlace' => preg_replace('/[^0-9+]/', '', $telefono),
            'email_gimnasio' => $texto('gimnasio.email') ?: $texto('correo.remitente'),
            'direccion_gimnasio' => $direccion,
            'instagram' => $texto('web.instagram'),
            'enlace_mapa' => $mapa,
            'horario' => self::horarioEnUnaLinea(),
        ];
    }

    /**
     * El horario de Configuración → Horario en una sola línea.
     *
     * Los días seguidos con las mismas horas se juntan: «Lunes a viernes:
     * 07:00-22:00 · Sábado: 09:00-14:00 · Domingo: cerrado». Va en una línea
     * porque las variables se rellenan con texto, no con HTML.
     */
    private static function horarioEnUnaLinea(): string
    {
        $dias = [
            'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves',
            'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
        ];

        $horas = [];

        foreach ($dias as $clave => $nombre) {
            $valor = preg_replace('/\s*-\s*/', '-', trim((string) \App\Support\Ajustes::obtener("horario.{$clave}")));
            $horas[$nombre] = preg_replace('/\s*,\s*/', ', ', $valor);
        }

        if (implode('', $horas) === '') {
            return 'Consulta el horario en recepción';
        }

        $tramos = [];

        foreach ($horas as $nombre => $valor) {
            $ultimo = array_key_last($tramos);

            if ($ultimo !== null && $tramos[$ultimo]['horas'] === $valor) {
                $tramos[$ultimo]['hasta'] = $nombre;
            } else {
                $tramos[] = ['desde' => $nombre, 'hasta' => null, 'horas' => $valor];
            }
        }

        $partes = array_map(function (array $t) {
            $dias = $t['hasta'] ? $t['desde'] . ' a ' . mb_strtolower($t['hasta']) : $t['desde'];

            return $dias . ': ' . ($t['horas'] === '' ? 'cerrado' : $t['horas']);
        }, $tramos);

        $nota = trim((string) \App\Support\Ajustes::obtener('horario.nota'));

        return implode(' · ', $nota !== '' ? [...$partes, $nota] : $partes);
    }

    private function diasQueQuedan(Inscripcion $inscripcion): int
    {
        if (! $inscripcion->fecha_vencimiento) {
            return 0;
        }

        // Por fechas y entero, como Inscripcion::diasEntre: diffInDays()
        // cuenta horas y el domingo del cambio de hora salía un día de menos.
        return max(0, Inscripcion::diasEntre(today(), $inscripcion->fecha_vencimiento));
    }

    private function pesos(int $monto): string
    {
        return number_format($monto, 0, ',', '.');
    }
}
