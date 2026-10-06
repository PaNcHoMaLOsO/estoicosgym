<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Support\Ajustes;
use App\Support\TextosLegales;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * El contrato que se firma por correo, sin papel.
 *
 * Al socio le llega un enlace. Lo abre en su celular, lee el contrato con SUS
 * datos y los de su plan, acepta los términos, firma con el dedo y le vuelve
 * una copia al correo. Lo que se guarda es el documento TAL COMO SE FIRMÓ, con
 * la firma dentro, y su huella: si alguien le cambiara una coma después, la
 * huella dejaría de cuadrar.
 *
 * Si el socio es menor de edad firma su apoderado, y el correo va a él.
 *
 * Vale como firma electrónica simple (Ley 19.799): queda quién firmó, con qué
 * RUT, cuándo, desde qué conexión y qué texto exacto aceptó.
 */
class ContratoDigitalService
{
    public const PLANTILLA_ENVIO = 'contrato_para_firmar';

    public const PLANTILLA_COPIA = 'contrato_firmado';

    public const PLANTILLAS = [self::PLANTILLA_ENVIO, self::PLANTILLA_COPIA];

    /** Lo que las dos plantillas saben rellenar además de los datos del socio. */
    public const VARIABLES = [
        'firmante' => 'Quién firma: el socio, o su apoderado si es menor',
        'enlace_contrato' => 'Su enlace personal para leer y firmar, o para ver la copia',
        'vence_enlace' => 'Hasta cuándo sirve el enlace',
        'firmado_en' => 'Cuándo se firmó (en la copia)',
        'gimnasio' => 'El nombre del gimnasio',
    ];

    /** Lo que queda escrito en el registro de correos en lugar del enlace. */
    public const ENLACE_OCULTO = '(enlace personal: no se guarda)';

    /** Una firma dibujada pesa unas decenas de KB. Más que esto no es una firma. */
    private const FIRMA_MAXIMA = 400_000;

    /**
     * Las medidas máximas del recuadro, en píxeles. El recuadro de la página
     * mide a lo más unos 770 × 180 y el navegador lo dibuja al doble: más que
     * esto no sale de ahí. Un PNG de 8000 × 8000 en blanco pesa 270 KB y al
     * abrir la copia el navegador tiene que desplegar 256 MB.
     */
    private const FIRMA_ANCHO_MAXIMO = 2000;

    private const FIRMA_ALTO_MAXIMO = 1000;

    /** Membresías que ya no rigen: un contrato no se hace sobre ellas. */
    private const TERMINADAS = [
        EstadosCodigo::INSCRIPCION_CANCELADA,
        EstadosCodigo::INSCRIPCION_CAMBIADA,
        EstadosCodigo::INSCRIPCION_TRASPASADA,
    ];

    public function __construct(
        private readonly CorreoService $correo,
        private readonly EnvioManualService $envio,
        private readonly RegistroClienteService $registro,
    ) {
    }

    /**
     * Quién firma y a qué correo va.
     *
     * @return array{tipo:string, nombre:string, rut:?string, email:?string}
     */
    public function firmante(Cliente $cliente): array
    {
        if ($cliente->es_menor_edad) {
            return [
                'tipo' => 'apoderado',
                'nombre' => (string) $cliente->apoderado_nombre,
                'rut' => $cliente->apoderado_rut,
                'email' => $cliente->apoderado_email,
            ];
        }

        return [
            'tipo' => 'socio',
            'nombre' => $cliente->nombre_completo,
            'rut' => $cliente->run_pasaporte,
            'email' => $cliente->email,
        ];
    }

    /** Lo que impide mandarle el contrato, o null. */
    public function porQueNoSePuedeEnviar(Cliente $cliente): ?string
    {
        if ($cliente->datos_borrados_en) {
            return 'Sus datos personales se borraron.';
        }

        if (! $cliente->activo || $cliente->trashed()) {
            return 'Está dado de baja. Reactívalo antes de mandarle el contrato.';
        }

        $firmante = $this->firmante($cliente);

        if ($firmante['tipo'] === 'apoderado') {
            if (blank($firmante['nombre'])) {
                return 'Es menor de edad y falta el nombre de su apoderado, que es quien firma.';
            }

            if (blank($firmante['email'])) {
                return 'Es menor de edad y falta el correo de su apoderado, que es quien firma.';
            }

            /*
             * EL CORREO DEL APODERADO NO PUEDE SER EL DEL MENOR. Si lo es, el
             * enlace le llega al propio menor, que firma «como apoderado» con
             * cualquier nombre y queda autorizado solo.
             */
            if (filled($cliente->email) && Str::lower(trim($firmante['email'])) === Str::lower(trim((string) $cliente->email))) {
                return 'El correo del apoderado es el mismo del socio, que es menor. Anota en la ficha el correo del apoderado: el contrato lo firma él.';
            }

            if (blank($firmante['rut'])) {
                return 'Es menor de edad y falta el RUT de su apoderado, que es quien firma. Anótalo en la ficha antes de mandarle el contrato.';
            }
        } elseif (blank($firmante['email'])) {
            return 'No tiene correo. Anótalo en su ficha para mandarle el contrato.';
        } elseif (blank($firmante['rut'])) {
            /*
             * SIN RUT EN LA FICHA NO HAY CON QUÉ COMPARAR. Al firmar se pide el
             * RUT y se contrasta con este; si falta, cualquiera con el enlace
             * firmaría con el nombre y el RUT que se le ocurran.
             */
            return 'Anota su RUT en la ficha antes de mandarle el contrato: al firmar se le pide, y se compara con ese.';
        }

        $plantilla = $this->plantilla(self::PLANTILLA_ENVIO);

        if (! $plantilla || ! $plantilla->activo) {
            return 'La plantilla «Contrato para firmar» está desactivada en Plantillas de correo.';
        }

        return null;
    }

    /**
     * Le manda el enlace para firmar. El enlace anterior, si había, deja de
     * servir: un solo contrato pendiente por socio.
     *
     * @throws ValidationException si no se puede, o si el correo no sale
     */
    public function enviar(Cliente $cliente): Contrato
    {
        if ($motivo = $this->porQueNoSePuedeEnviar($cliente)) {
            throw ValidationException::withMessages(['contrato' => $motivo]);
        }

        $plantilla = $this->plantilla(self::PLANTILLA_ENVIO);

        // Sin el enlace, el correo no le sirve de nada a quien lo recibe.
        if (! str_contains((string) $plantilla->plantilla_email, '{enlace_contrato}')) {
            throw ValidationException::withMessages([
                'contrato' => 'La plantilla «Contrato para firmar» no lleva {enlace_contrato}: al socio le llegaría un correo sin dónde firmar. Agrégalo en Plantillas de correo.',
            ]);
        }

        $firmante = $this->firmante($cliente);
        $token = Str::random(48);
        $dias = max(1, Ajustes::numero('reglas.dias_para_firmar'));

        /*
         * El enlace anterior se anula DESPUÉS de mandar el nuevo, no antes: si
         * el correo fallaba, el socio se quedaba sin ninguno de los dos y el
         * que tenía en su bandeja dejaba de abrir.
         *
         * EL SOCIO SE TRABA AL CREARLO: dos «Enviar contrato» a la vez creaban
         * dos y cada uno anulaba solo lo que había ANTES de los dos, así que
         * quedaban dos enlaces válidos. Con la traba se crean en orden, y al
         * final cada uno anula todo lo pendiente anterior a él.
         */
        $contrato = DB::transaction(function () use ($cliente, $firmante, $token, $dias) {
            Cliente::whereKey($cliente->getKey())->lockForUpdate()->first();

            return Contrato::create([
                'id_cliente' => $cliente->id,
                'id_inscripcion' => $this->inscripcionVigente($cliente)?->id,
                'token_hash' => hash('sha256', $token),
                'firmante_tipo' => $firmante['tipo'],
                'email_destino' => $firmante['email'],
                'vence_en' => now()->addDays($dias)->endOfDay(),
                'id_usuario' => auth()->id(),
            ]);
        });

        $enlace = self::enlace($token);
        $correo = $this->envio->componerCon($plantilla, $this->envio->variables($cliente, [
            'firmante' => $firmante['nombre'],
            'enlace_contrato' => $enlace,
            'vence_enlace' => $contrato->vence_en->format('d/m/Y'),
            'firmado_en' => '',
            'gimnasio' => $this->gimnasio(),
        ]));

        if ($correo['pendientes'] !== []) {
            $contrato->update(['anulado_en' => now(), 'error_envio' => 'La plantilla usa variables que no existen.']);

            throw ValidationException::withMessages([
                'contrato' => sprintf(
                    'La plantilla «%s» usa %s y no hay con qué rellenarlo. Corrígela en Plantillas de correo.',
                    $plantilla->nombre,
                    '{' . implode('}, {', $correo['pendientes']) . '}'
                ),
            ]);
        }

        try {
            $this->mandar($contrato, $plantilla, $firmante['email'], $firmante['nombre'], $correo, $enlace, 'Contrato para firmar');
        } catch (\Throwable $e) {
            $contrato->update(['anulado_en' => now(), 'error_envio' => Str::limit($e->getMessage(), 500)]);

            throw ValidationException::withMessages([
                'contrato' => 'No se pudo mandar el correo: ' . $e->getMessage(),
            ]);
        }

        $contrato->update(['enviado_en' => now()]);

        /*
         * Todo lo pendiente de este socio ANTERIOR a este, no solo lo que había
         * al empezar: si otro envío se coló entretanto, el más nuevo es el que
         * queda. Por id y no «todos menos este»: si el más viejo terminara de
         * mandarse después, anularía al nuevo y no quedaría ninguno.
         */
        DB::transaction(function () use ($cliente, $contrato) {
            Cliente::whereKey($cliente->getKey())->lockForUpdate()->first();

            Contrato::where('id_cliente', $cliente->id)
                ->where('id', '<', $contrato->id)
                ->whereNull('firmado_en')
                ->whereNull('anulado_en')
                ->update(['anulado_en' => now()]);
        });

        return $contrato;
    }

    /** El contrato de un enlace, o null si el enlace no es de ninguno. */
    public function buscar(string $token): ?Contrato
    {
        // Lo que no tiene la forma de nuestros enlaces ni se busca.
        if (! preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            return null;
        }

        return Contrato::with(['cliente', 'inscripcion.membresia'])
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    /**
     * Lo que se lee al abrir el enlace: el contrato con los datos del socio,
     * los términos y la política de privacidad vigentes.
     *
     * @return array<string,mixed>
     */
    public function documento(Contrato $contrato, Carbon $fecha): array
    {
        $textos = [
            'contrato' => TextosLegales::vigente('contrato'),
            'terminos' => TextosLegales::vigente('terminos'),
            'privacidad' => TextosLegales::vigente('privacidad'),
        ];

        $gimnasio = TextosLegales::datosDelGimnasio();
        $cliente = $contrato->cliente;
        $variables = $this->variables($contrato, $fecha, $textos['contrato']->version);

        // Sin su título: van dentro de un desplegable, o de un anexo, que ya
        // dice qué son.
        $terminos = TextosLegales::sinTituloPrincipal(TextosLegales::html($textos['terminos']->contenido, $gimnasio));
        $privacidad = TextosLegales::sinTituloPrincipal(TextosLegales::html($textos['privacidad']->contenido, $gimnasio));

        return [
            'textos' => $textos,
            'contrato_html' => TextosLegales::html($textos['contrato']->contenido, $variables),
            'terminos_html' => $terminos,
            'privacidad_html' => $privacidad,
            /*
             * LA HUELLA DE LO QUE SE LEYÓ. El número de versión no basta: una
             * versión que nadie ha firmado se corrige ahí mismo sin cambiar de
             * número, y el plan o el precio del socio pueden cambiar mientras
             * lee. Viaja oculta en el formulario y al firmar se compara con la
             * de ese momento. La fecha va fija: quien lee a las 23:59 y firma a
             * las 00:01 leyó lo mismo.
             */
            'lectura' => hash('sha256', implode("\n\0\n", [
                TextosLegales::html($textos['contrato']->contenido, ['fecha' => '-'] + $variables),
                $terminos,
                $privacidad,
            ])),
            'firmante' => $this->firmante($cliente),
            'socio' => $cliente->nombre_completo,
            'gimnasio' => $gimnasio,
        ];
    }

    /**
     * El contrato de un socio tal como se le haría firmar hoy: el texto
     * vigente con sus datos y los de su plan. No crea enlaces ni guarda nada.
     *
     * Es lo que se ve con «Ver e imprimir el contrato» en su ficha: para
     * leerlo con él delante, o imprimirlo y firmarlo en el mesón.
     *
     * @return array<string,mixed>
     */
    public function borrador(Cliente $cliente, Carbon $fecha): array
    {
        $contrato = (new Contrato())->forceFill(['id_cliente' => $cliente->id]);
        $contrato->setRelation('cliente', $cliente);
        $contrato->setRelation('inscripcion', null);

        return $this->documento($contrato, $fecha);
    }

    /**
     * Firma.
     *
     * @param array{nombre:string, rut:string, firma:string, consentimiento_imagen:bool, consentimiento_difusion:bool, version_contrato:int, version_terminos:int, version_privacidad:int, lectura:string} $datos
     *
     * @throws ValidationException si algo no cuadra: el RUT, la firma, o que el
     *     texto cambió mientras se leía
     */
    public function firmar(Contrato $contrato, string $token, array $datos, ?string $ip, ?string $navegador): Contrato
    {
        $cliente = $contrato->cliente;
        $firmante = $this->firmante($cliente);

        // Sin RUT en la ficha no se firma: no se manda (porQueNoSePuedeEnviar),
        // pero un enlace de antes, o una ficha a la que le borraron el RUT
        // después, no pueden quedar como puerta abierta.
        if (blank($firmante['rut'])) {
            throw ValidationException::withMessages([
                'rut' => 'El gimnasio no tiene tu RUT anotado, y sin él no se puede comprobar quién firma. Avísale al gimnasio para que lo anote y te mande el contrato de nuevo.',
            ]);
        }

        if (self::rut($firmante['rut']) !== self::rut($datos['rut'])) {
            throw ValidationException::withMessages([
                'rut' => 'Ese no es el RUT que tiene el gimnasio en la ficha. Revísalo, o avísale al gimnasio si está mal anotado.',
            ]);
        }

        $png = $this->firmaDibujada($datos['firma']);
        $fecha = now();
        $documento = $this->documento($contrato, $fecha);

        // Lo que firma tiene que ser lo que leyó. Si el gimnasio guardó una
        // versión nueva mientras tanto, se le muestra la nueva.
        foreach (['contrato', 'terminos', 'privacidad'] as $tipo) {
            if ((int) $datos["version_{$tipo}"] !== $documento['textos'][$tipo]->version) {
                throw ValidationException::withMessages([
                    'version' => 'El gimnasio actualizó el texto mientras lo leías. Revisa la versión nueva y vuelve a firmar.',
                ]);
            }
        }

        // Y aunque el número sea el mismo, el texto tiene que ser el mismo:
        // una versión sin firmas se corrige sin cambiar de número, y el plan o
        // el precio pueden haber cambiado (ver «lectura» en documento()).
        if (! hash_equals($documento['lectura'], (string) ($datos['lectura'] ?? ''))) {
            throw ValidationException::withMessages([
                'version' => 'El gimnasio actualizó el texto mientras lo leías. Revisa la versión nueva y vuelve a firmar.',
            ]);
        }

        $nombre = Str::squish($datos['nombre']);
        $rut = trim($datos['rut']);

        $contenido = view('contrato.partes.documento', $documento + [
            'firma' => 'data:image/png;base64,' . base64_encode($png),
            'nombre' => $nombre,
            'rut' => $rut,
            'fecha' => $fecha,
            'ip' => $ip,
            'email' => $contrato->email_destino,
            'consentimiento_imagen' => $datos['consentimiento_imagen'],
            'consentimiento_difusion' => $datos['consentimiento_difusion'],
            'enlace_privacidad' => TextosLegales::direccion(route('landing.privacidad', [], false)),
        ])->render();

        $firmado = DB::transaction(function () use ($contrato, $cliente, $datos, $documento, $fecha, $nombre, $rut, $ip, $navegador, $contenido) {
            $fila = Contrato::whereKey($contrato->getKey())->lockForUpdate()->first();

            // Dos pestañas, o un doble toque: el segundo llega tarde.
            if (! $fila || ! $fila->sePuedeFirmar()) {
                throw ValidationException::withMessages(['firma' => 'Este enlace ya no sirve para firmar.']);
            }

            $fila->update([
                'firmado_en' => $fecha,
                'firmante_nombre' => $nombre,
                'firmante_rut' => $rut,
                'ip' => $ip,
                'navegador' => Str::limit((string) $navegador, 250, ''),
                'version_contrato' => $documento['textos']['contrato']->version,
                'version_terminos' => $documento['textos']['terminos']->version,
                'version_privacidad' => $documento['textos']['privacidad']->version,
                'consentimiento_imagen' => $datos['consentimiento_imagen'],
                'consentimiento_difusion' => $datos['consentimiento_difusion'],
                'contenido' => $contenido,
                'huella' => hash('sha256', $contenido),
            ]);

            // La ficha queda al día, con la misma regla que la firma en papel:
            // si retiró el permiso de la foto, la foto se va.
            $this->registro->registrarContrato($cliente, [
                'contrato_version' => (string) $documento['textos']['contrato']->version,
                'contrato_firmado_en' => $fecha->toDateString(),
                'consentimiento_imagen' => $datos['consentimiento_imagen'],
                'consentimiento_difusion' => $datos['consentimiento_difusion'],
            ]);

            // La firma del apoderado ES su autorización para inscribir al menor.
            if ($fila->firmante_tipo === 'apoderado') {
                $cliente->update(['consentimiento_apoderado' => true]);
            }

            return $fila;
        });

        $this->mandarCopia($firmado, $token, $documento);

        return $firmado;
    }

    /** Que el enlace deje de servir. Uno ya firmado no se anula: es constancia. */
    public function anular(Contrato $contrato): void
    {
        if ($contrato->firmado_en) {
            throw ValidationException::withMessages([
                'contrato' => 'Ese contrato ya está firmado: no se anula, queda como constancia.',
            ]);
        }

        $contrato->update(['anulado_en' => $contrato->anulado_en ?? now()]);
    }

    /**
     * Valores de muestra para ver las dos plantillas en Plantillas de correo.
     *
     * @return array<string,string>
     */
    public function variablesDeEjemplo(Cliente $socio): array
    {
        return [
            'firmante' => $this->firmante($socio)['nombre'] ?: $socio->nombre_completo,
            'enlace_contrato' => self::enlace(str_repeat('x', 48)),
            'vence_enlace' => now()->addDays(max(1, Ajustes::numero('reglas.dias_para_firmar')))->format('d/m/Y'),
            'firmado_en' => now()->format('d/m/Y \a \l\a\s H:i'),
            'gimnasio' => $this->gimnasio(),
        ];
    }

    /**
     * El enlace personal de un contrato.
     *
     * CON LA DIRECCIÓN DE APP_URL, no con la que trae la petición: la copia se
     * manda al firmar, desde una petición del público, y quien tenga el enlace
     * podría pedirla con otra dirección (Host o X-Forwarded-Host) para que el
     * correo verdadero del gimnasio lleve a una página ajena.
     */
    public static function enlace(string $token): string
    {
        return TextosLegales::direccion(route('contrato.mostrar', $token, false));
    }

    /** «12.345.678-9», «12345678-9» y «123456789» son el mismo RUT. */
    public static function rut(?string $rut): string
    {
        return strtoupper((string) preg_replace('/[^0-9A-Za-z]/', '', (string) $rut));
    }

    /** @return array<string,string> */
    private function variables(Contrato $contrato, Carbon $fecha, int $version): array
    {
        $cliente = $contrato->cliente;
        $firmante = $this->firmante($cliente);
        $inscripcion = $this->inscripcion($contrato);
        $sinDato = 'no registrado';
        $porDefinir = 'por definir';

        return TextosLegales::datosDelGimnasio() + [
            'socio' => $cliente->nombre_completo,
            'rut_socio' => $cliente->run_pasaporte ?: $sinDato,
            'email_socio' => $cliente->email ?: $sinDato,
            'celular_socio' => $cliente->celular ?: $sinDato,
            'firmante' => $firmante['nombre'],
            'rut_firmante' => $firmante['rut'] ?: $sinDato,
            'plan' => $inscripcion?->membresia?->nombre ?? $porDefinir,
            'precio' => $inscripcion
                ? $this->pesos((int) ($inscripcion->precio_final ?? $inscripcion->precio_base))
                : $porDefinir,
            'inicio' => $inscripcion?->fecha_inicio?->format('d/m/Y') ?? $porDefinir,
            'vencimiento' => $inscripcion?->fecha_vencimiento?->format('d/m/Y') ?? $porDefinir,
            'pausas' => $this->pausas($inscripcion),
            'fecha' => $fecha->format('d/m/Y'),
            'version' => (string) $version,
        ];
    }

    /**
     * La membresía de la que habla el contrato.
     *
     * La del envío, salvo que ya no rija —se cambió de plan o se canceló—:
     * entonces la vigente, que es la que el socio está usando.
     */
    private function inscripcion(Contrato $contrato): ?Inscripcion
    {
        $propia = $contrato->inscripcion;

        if ($propia && ! in_array((int) $propia->id_estado, self::TERMINADAS, true)) {
            return $propia->loadMissing('membresia');
        }

        return $this->inscripcionVigente($contrato->cliente);
    }

    private function inscripcionVigente(Cliente $cliente): ?Inscripcion
    {
        return Inscripcion::where('id_cliente', $cliente->id)
            ->whereNotIn('id_estado', self::TERMINADAS)
            ->with('membresia')
            ->orderByDesc('fecha_vencimiento')
            ->first();
    }

    /**
     * Manda un correo y deja constancia en Notificaciones.
     *
     * El enlace NO queda en la constancia: con él, cualquiera que abra el
     * registro de correos podría firmar por el socio. Y no se reintenta solo,
     * porque el reintento lo mandaría sin el enlace; si falla, se manda otro
     * desde la ficha. Tampoco en el asunto, si alguien puso ahí el enlace.
     *
     * $anexo va en el correo pero NO en la constancia: es el contrato entero,
     * con RUT, celular y precio, y ya está guardado en su sitio (el contrato
     * firmado). Repetirlo en Notificaciones lo dejaría a la vista de quien
     * revisa los correos.
     *
     * @param array{asunto:string, contenido:string} $correo
     */
    private function mandar(Contrato $contrato, TipoNotificacion $plantilla, string $para, string $nombre, array $correo, string $enlace, string $que, string $anexo = ''): void
    {
        $tapar = fn (string $texto) => str_replace([$enlace, e($enlace)], self::ENLACE_OCULTO, $texto);

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $plantilla->id,
            'id_cliente' => $contrato->id_cliente,
            'id_inscripcion' => $contrato->id_inscripcion,
            'email_destino' => $para,
            'asunto' => $tapar($correo['asunto']),
            'contenido' => $tapar($correo['contenido']),
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
            'enviado_por_user_id' => auth()->id(),
            'max_intentos' => 1,
        ]);

        $notificacion->registrarLog('programada', $que . (auth()->user() ? ' por ' . auth()->user()->name : ''));

        try {
            $this->correo->enviar($para, $correo['asunto'], $correo['contenido'] . $anexo, $nombre);
        } catch (\Throwable $e) {
            $notificacion->marcarComoFallida($e->getMessage());

            throw $e;
        }

        $notificacion->marcarComoEnviada();
    }

    /**
     * La copia para quien firmó, completa, en su correo.
     *
     * Si no sale, el contrato queda firmado igual: la copia se puede ver en el
     * enlace, y el fallo queda anotado en Notificaciones.
     *
     * @param array<string,mixed> $documento
     */
    private function mandarCopia(Contrato $contrato, string $token, array $documento): void
    {
        $plantilla = $this->plantilla(self::PLANTILLA_COPIA);

        if (! $plantilla || ! $plantilla->activo || blank($contrato->email_destino)) {
            return;
        }

        $enlace = self::enlace($token);
        $correo = $this->envio->componerCon($plantilla, $this->envio->variables($contrato->cliente->fresh(), [
            'firmante' => (string) $contrato->firmante_nombre,
            'enlace_contrato' => $enlace,
            'vence_enlace' => '',
            'firmado_en' => $contrato->firmado_en->format('d/m/Y \a \l\a\s H:i'),
            'gimnasio' => $this->gimnasio(),
        ]));

        // No se manda a medio rellenar: el contrato ya está firmado igual.
        if ($correo['pendientes'] !== []) {
            return;
        }

        $copia = view('contrato.partes.correo', $documento + ['contrato' => $contrato])->render();

        try {
            $this->mandar($contrato, $plantilla, $contrato->email_destino, (string) $contrato->firmante_nombre, $correo, $enlace, 'Copia del contrato firmado', $copia);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * La firma que dibujó, como PNG.
     *
     * @throws ValidationException si no es una imagen de firma
     */
    private function firmaDibujada(string $dato): string
    {
        $png = preg_match('#^data:image/png;base64,([A-Za-z0-9+/]+=*)$#', $dato, $partes)
            ? base64_decode($partes[1], true)
            : false;

        $medidas = $png !== false && strlen($png) <= self::FIRMA_MAXIMA
            ? @getimagesizefromstring($png)
            : false;

        if (! $medidas || $medidas[2] !== IMAGETYPE_PNG || $medidas[0] < 100 || $medidas[1] < 40
            || $medidas[0] > self::FIRMA_ANCHO_MAXIMO || $medidas[1] > self::FIRMA_ALTO_MAXIMO) {
            throw ValidationException::withMessages([
                'firma' => 'No llegó la firma. Firma dentro del recuadro y vuelve a intentarlo.',
            ]);
        }

        if ($this->estaEnBlanco($png)) {
            throw ValidationException::withMessages([
                'firma' => 'El recuadro de la firma llegó vacío. Firma dentro del recuadro y vuelve a intentarlo.',
            ]);
        }

        return $png;
    }

    /**
     * ¿Es un recuadro sin trazo? Todos los píxeles del mismo color.
     *
     * El formulario no deja mandarlo vacío, pero eso lo revisa el navegador:
     * a mano se puede mandar un PNG en blanco y quedaría un contrato «firmado»
     * sin firma. Se achica antes de mirarlo —el recuadro llega a 2000 × 1000 y
     * mirar dos millones de píxeles uno por uno tarda— y al achicar un trazo
     * no desaparece: tiñe, aunque sea un poco, los píxeles donde cae.
     *
     * Necesita GD. Sin GD no se puede mirar y se da por buena, como antes.
     */
    private function estaEnBlanco(string $png): bool
    {
        if (! function_exists('imagecreatefromstring')) {
            return false;
        }

        $imagen = @imagecreatefromstring($png);

        if ($imagen === false) {
            return false;
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $escala = min(1, 400 / $ancho, 200 / $alto);
        $chica = imagecreatetruecolor(max(1, (int) round($ancho * $escala)), max(1, (int) round($alto * $escala)));
        // Sin mezclar: la transparencia del lienzo es parte del color a comparar.
        imagealphablending($chica, false);
        imagesavealpha($chica, true);
        imagecopyresampled($chica, $imagen, 0, 0, 0, 0, imagesx($chica), imagesy($chica), $ancho, $alto);

        $primero = imagecolorat($chica, 0, 0);

        for ($y = 0; $y < imagesy($chica); $y++) {
            for ($x = 0; $x < imagesx($chica); $x++) {
                if (imagecolorat($chica, $x, $y) !== $primero) {
                    return false;
                }
            }
        }

        return true;
    }

    private function plantilla(string $codigo): ?TipoNotificacion
    {
        return TipoNotificacion::where('codigo', $codigo)->first();
    }

    private function gimnasio(): string
    {
        return (string) (Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM');
    }

    private function pesos(int $monto): string
    {
        return '$' . number_format($monto, 0, ',', '.');
    }

    private function pausas(?Inscripcion $inscripcion): string
    {
        if (! $inscripcion) {
            return 'según el plan';
        }

        $cuantas = (int) ($inscripcion->max_pausas_permitidas ?? $inscripcion->membresia?->max_pausas ?? 0);

        return match (true) {
            $cuantas <= 0 => 'no incluye',
            $cuantas === 1 => '1 pausa',
            default => "{$cuantas} pausas",
        };
    }
}
