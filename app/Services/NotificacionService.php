<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Models\LogNotificacion;
use App\Services\Correo\TopeDelDiaAlcanzado;
use App\Support\Ajustes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Cuántos días puede esperar un aviso automático antes de darse por viejo.
     *
     * Si el computador del mesón pasa apagado una semana, al prenderlo salían
     * de golpe todos los «tu membresía vence en 3 días» de membresías que ya
     * vencieron. Dos días dan margen a un fin de semana o a un día de tope.
     */
    public const DIAS_QUE_ESPERA_UN_AVISO = 2;

    /**
     * Cuánto tiene que pasar desde el último intento para volver a probar.
     *
     * `--todo` manda las pendientes y en seguida reintenta las fallidas: sin
     * esta espera, lo que acababa de fallar se reintentaba en el mismo minuto
     * —contra el mismo servidor caído— y gastaba sus intentos en una sola
     * corrida. Con media hora, la tanda de la mañana reintenta lo de ayer y lo
     * de esta mañana espera a la próxima.
     */
    public const MINUTOS_ENTRE_REINTENTOS = 30;

    private CorreoService $correo;

    public function __construct(?CorreoService $correo = null)
    {
        $this->correo = $correo ?? new CorreoService();
    }

    /**
     * Programa notificaciones para membresías próximas a vencer
     */
    public function programarNotificacionesPorVencer(): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_POR_VENCER)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado o inactivo'];
        }

        $diasAnticipacion = $tipoNotificacion->dias_anticipacion;
        $fechaObjetivo = Carbon::today()->addDays($diasAnticipacion);

        // Buscar inscripciones que vencen en X días
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100) // Activa
            ->whereDate('fecha_vencimiento', $fechaObjetivo)
            // Con la misma regla que crearNotificacion(): el menor que solo
            // tiene el correo del apoderado también recibe el aviso.
            ->whereHas('cliente', fn ($q) => $q->where('activo', true)->conCorreoParaAvisos())
            ->get();

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            // Verificar si ya existe una notificación para esta inscripción y tipo
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_ENVIADO])
                ->exists();

            if ($existe) {
                continue;
            }

            $this->crearNotificacion($tipoNotificacion, $inscripcion);
            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de membresías por vencer"
        ];
    }

    /**
     * Programa notificaciones para membresías vencidas hoy
     */
    public function programarNotificacionesVencidas(): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::MEMBRESIA_VENCIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado o inactivo'];
        }

        // Buscar inscripciones que vencen hoy
        $inscripciones = Inscripcion::with(['cliente', 'membresia'])
            ->where('id_estado', 100) // Aún activa (se marcará como vencida después)
            ->whereDate('fecha_vencimiento', Carbon::today())
            // Con la misma regla que crearNotificacion(): el menor que solo
            // tiene el correo del apoderado también recibe el aviso.
            ->whereHas('cliente', fn ($q) => $q->where('activo', true)->conCorreoParaAvisos())
            ->get();

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_ENVIADO])
                ->exists();

            if ($existe) {
                continue;
            }

            $this->crearNotificacion($tipoNotificacion, $inscripcion);
            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de membresías vencidas"
        ];
    }

    /*
     * ============ UN SOLO MOTOR PARA TODOS LOS AVISOS ============
     *
     * Antes cada aviso automático leía un HTML de storage/app/test_emails —una
     * carpeta que no va en el repositorio, así que en el servidor no existía y
     * los correos fallaban con «Plantilla no encontrada»— y le cambiaba a mano
     * el TEXTO DE MUESTRA: «Juan Pérez» por el nombre, «Trimestral» por el plan,
     * «$$25.000» por el saldo. Lo que se corregía en Configuración → Plantillas
     * de correo no llegaba nunca a esos avisos, y una muestra que no coincidía
     * con la búsqueda se colaba tal cual en el correo del socio.
     *
     * Ahora todos salen de la plantilla guardada en la base, rellenada con las
     * MISMAS variables que el envío manual (EnvioManualService). Lo que se ve
     * en la vista previa de la pantalla es lo que le llega al socio.
     */

    private ?EnvioManualService $envio = null;

    private function envio(): EnvioManualService
    {
        return $this->envio ??= new EnvioManualService($this->correo);
    }

    /**
     * Compone un aviso con su plantilla y los datos de esa inscripción.
     *
     * @param array<string,string> $extra lo que solo se sabe en el momento del aviso
     * @return array{asunto:string, contenido:string, pendientes:list<string>}
     */
    public function componer(TipoNotificacion $tipo, Inscripcion $inscripcion, array $extra = []): array
    {
        return $this->envio()->componerCon(
            $tipo,
            $this->envio()->variablesDeInscripcion($inscripcion, $extra + $this->extrasDelMomento($tipo))
        );
    }

    /**
     * Lo que no está guardado en ningún lado y depende de cuándo sale el aviso.
     *
     * La reactivación borra las fechas de la pausa antes de avisar, así que la
     * fecha de vuelta es hoy: es el día en que se reactivó.
     *
     * @return array<string,string>
     */
    private function extrasDelMomento(TipoNotificacion $tipo): array
    {
        return match ($tipo->codigo) {
            TipoNotificacion::ACTIVACION_INSCRIPCION => ['fecha_activacion' => Carbon::today()->format('d/m/Y')],
            default => [],
        };
    }

    /**
     * Anota el aviso. Si la plantilla quedó a medio rellenar, NO sale.
     *
     * Igual que el envío manual: un correo que le llega al socio diciendo
     * «tu plan {membresia} vence» es peor que uno que no sale. Pero aquí no hay
     * nadie delante a quien avisar, así que la fila se guarda FALLIDA, con el
     * motivo escrito y sin reintentos —reintentarla mandaría lo mismo—, y se ve
     * en la lista de notificaciones con qué plantilla hay que arreglar.
     *
     * @param array{asunto:string, contenido:string, pendientes:list<string>} $correo
     */
    private function anotar(
        TipoNotificacion $tipo,
        Inscripcion $inscripcion,
        string $destino,
        array $correo,
        Carbon $fecha,
        string $detalle
    ): Notificacion {
        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => $inscripcion->id_cliente,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $destino,
            'asunto' => $correo['asunto'],
            'contenido' => $correo['contenido'],
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => $fecha,
        ]);

        $muestras = $correo['muestras'] ?? [];

        if ($correo['pendientes'] === [] && $muestras === []) {
            $notificacion->registrarLog('programada', $detalle);

            return $notificacion;
        }

        /*
         * El texto de muestra de las plantillas viejas —«Juan Pérez»,
         * «$$25.000»— se trata igual que una variable sin rellenar: el motor
         * de antes lo cambiaba a mano por los datos del socio, el de ahora no,
         * y saldría tal cual con el nombre y el monto inventados.
         */
        $motivo = $correo['pendientes'] !== []
            ? sprintf(
                'No se envió: la plantilla «%s» usa %s y no hay con qué rellenarlo. Corrígela en Configuración → Plantillas de correo.',
                $tipo->nombre,
                '{' . implode('}, {', $correo['pendientes']) . '}'
            )
            : sprintf(
                'No se envió: la plantilla «%s» todavía tiene el texto de ejemplo «%s». Edítala en Configuración → Plantillas de correo.',
                $tipo->nombre,
                implode('», «', array_slice($muestras, 0, 3))
            );

        $notificacion->update([
            'id_estado' => Notificacion::ESTADO_FALLIDO,
            'error_mensaje' => $motivo,
            'intentos' => DB::raw('max_intentos'),
        ]);
        $notificacion->registrarLog('fallida', $motivo);

        Log::warning('Aviso automático sin enviar por una plantilla incompleta', [
            'tipo' => $tipo->codigo,
            'inscripcion' => $inscripcion->id,
            'variables' => $correo['pendientes'],
            'muestras' => $muestras,
        ]);

        return $notificacion->refresh();
    }

    /**
     * Manda en el momento un aviso recién anotado (bienvenida, tutor legal,
     * renovación). Si el correo no sale, la fila queda fallida con el motivo y
     * la tanda de reintentos la vuelve a probar.
     *
     * @return array{enviada:bool, mensaje:string, notificacion_id:int}
     */
    private function mandarYa(Notificacion $notificacion, string $queEs): array
    {
        if ((int) $notificacion->id_estado === Notificacion::ESTADO_FALLIDO) {
            return [
                'enviada' => false,
                'mensaje' => $notificacion->error_mensaje,
                'notificacion_id' => $notificacion->id,
            ];
        }

        try {
            $this->correo->enviar($notificacion->email_destino, $notificacion->asunto, $notificacion->contenido);
        } catch (TopeDelDiaAlcanzado $e) {
            // No falló: hoy ya no caben más. Sale mañana con la tanda diaria.
            $notificacion->aplazarParaManana($e->getMessage());

            return [
                'enviada' => false,
                'mensaje' => $e->getMessage(),
                'notificacion_id' => $notificacion->id,
            ];
        } catch (\Throwable $e) {
            $notificacion->marcarComoFallida($e->getMessage());

            return [
                'enviada' => false,
                'mensaje' => 'Error al enviar: ' . $e->getMessage(),
                'notificacion_id' => $notificacion->id,
            ];
        }

        $notificacion->marcarComoEnviada();

        return [
            'enviada' => true,
            'mensaje' => "{$queEs} enviada",
            'notificacion_id' => $notificacion->id,
        ];
    }

    /**
     * Crea una notificación para una inscripción
     */
    public function crearNotificacion(TipoNotificacion $tipo, Inscripcion $inscripcion): ?Notificacion
    {
        // Con el interruptor apagado no se anota nada: una fila pendiente la
        // mandaría la tanda del día en cuanto alguien lo volviera a prender.
        if (! self::automaticosEncendidos()) {
            return null;
        }

        $cliente = $inscripcion->cliente;

        // Si es menor de edad y tiene correo de apoderado, el aviso es para él.
        $destino = $cliente?->correoParaAvisos();

        if ($destino === null) {
            return null;
        }

        $detalle = $destino !== $cliente->email
            ? "Notificación programada para apoderado: {$destino}"
            : 'Notificación programada automáticamente';

        return $this->anotar($tipo, $inscripcion, (string) $destino, $this->componer($tipo, $inscripcion), Carbon::today(), $detalle);
    }

    /**
     * Envía las notificaciones pendientes
     *
     * Con `$soloManuales`, solo las que alguien escribió y programó a mano
     * (tipo_envio = manual): es lo que sigue saliendo con los correos
     * automáticos apagados.
     */
    public function enviarPendientes(bool $soloManuales = false): array
    {
        $canceladas = $this->cancelarAvisosViejos();

        $notificaciones = Notificacion::paraEnviarHoy()
            ->when($soloManuales, fn ($q) => $q->manuales())
            ->with(['cliente' => fn ($q) => $q->withTrashed(), 'tipoNotificacion'])
            ->get();

        $enviadas = 0;
        $fallidas = 0;
        $aplazadas = 0;

        foreach ($notificaciones as $notificacion) {
            if ($motivo = $this->porQueNoSeLeEscribe($notificacion)) {
                $notificacion->cancelar($motivo);
                $canceladas++;

                continue;
            }

            try {
                $notificacion->registrarLog('enviando', 'Iniciando envío de correo');

                // Enviar usando PHPMailer (SMTP configurado en las variables MAIL_* del .env)
                $this->correo->enviar(
                    $notificacion->email_destino,
                    $notificacion->asunto,
                    $notificacion->contenido
                );

                // Registrar en log_notificaciones
                LogNotificacion::create([
                    'id_notificacion' => $notificacion->id,
                    'accion' => 'enviada',
                    'detalle' => 'Correo enviado correctamente',
                ]);

                $notificacion->marcarComoEnviada();
                $enviadas++;

                // Solo identificadores: el registro lo lee cualquiera con
                // acceso al servidor, y el correo es un dato personal.
                Log::info("Notificación enviada", [
                    'id' => $notificacion->id,
                    'cliente' => $notificacion->id_cliente,
                    'tipo' => $notificacion->tipoNotificacion->codigo ?? 'N/A',
                ]);

            } catch (TopeDelDiaAlcanzado $e) {
                // El tope no es un fallo: sale mañana sin gastar un intento.
                $notificacion->aplazarParaManana($e->getMessage());
                $aplazadas++;
            } catch (\Exception $e) {
                // Registrar error en log_notificaciones
                LogNotificacion::create([
                    'id_notificacion' => $notificacion->id,
                    'accion' => 'fallida',
                    'detalle' => 'Error: ' . $e->getMessage(),
                ]);

                $notificacion->marcarComoFallida($e->getMessage());
                $fallidas++;

                Log::error("Error al enviar notificación", [
                    'id' => $notificacion->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Se suelta la sesion SMTP al terminar la tanda. La conexion se reusa
        // entre correos —abrir una por cada uno hacia que Gmail cortara— pero
        // dejarla abierta despues no sirve de nada.
        $this->correo->cerrar();
        
        return [
            'enviadas' => $enviadas,
            'fallidas' => $fallidas,
            'aplazadas' => $aplazadas,
            'canceladas' => $canceladas,
            'total' => $notificaciones->count(),
            'mensaje' => "Enviadas: {$enviadas}, Fallidas: {$fallidas}"
        ];
    }

    /**
     * Reintenta enviar notificaciones fallidas (ver `$soloManuales` arriba)
     *
     * Solo las que fallaron hace más de MINUTOS_ENTRE_REINTENTOS: lo que
     * falló en esta misma corrida no se vuelve a probar en el acto.
     */
    public function reintentarFallidas(bool $soloManuales = false): array
    {
        $this->cancelarAvisosViejos();

        $notificaciones = Notificacion::fallidas()
            ->when($soloManuales, fn ($q) => $q->manuales())
            ->where('intentos', '<', \DB::raw('max_intentos'))
            ->where('updated_at', '<', now()->subMinutes(self::MINUTOS_ENTRE_REINTENTOS))
            ->with(['cliente' => fn ($q) => $q->withTrashed(), 'tipoNotificacion'])
            ->get();

        $reenviadas = 0;
        $fallidasNuevamente = 0;

        foreach ($notificaciones as $notificacion) {
            if ($motivo = $this->porQueNoSeLeEscribe($notificacion)) {
                $notificacion->cancelar($motivo);

                continue;
            }

            try {
                $notificacion->registrarLog('reintentando', "Reintento #{$notificacion->intentos}");
                $notificacion->update(['id_estado' => Notificacion::ESTADO_PENDIENTE]);

                $this->correo->enviar(
                    $notificacion->email_destino,
                    $notificacion->asunto,
                    $notificacion->contenido
                );

                $notificacion->marcarComoEnviada();
                $reenviadas++;

            } catch (TopeDelDiaAlcanzado $e) {
                $notificacion->aplazarParaManana($e->getMessage());
            } catch (\Exception $e) {
                $notificacion->marcarComoFallida($e->getMessage());
                $fallidasNuevamente++;
            }
        }

        // Misma razon que en la tanda anterior: la sesion SMTP se reusa
        // durante el bucle y se suelta al acabar.
        $this->correo->cerrar();
        
        return [
            'reenviadas' => $reenviadas,
            'fallidas' => $fallidasNuevamente,
            'mensaje' => "Reenviadas: {$reenviadas}, Fallidas nuevamente: {$fallidasNuevamente}"
        ];
    }

    /**
     * ¿Está prendido el interruptor de Configuración → Avisos automáticos?
     *
     * Lo miran TODOS los avisos que salen solos, no solo la tanda diaria: la
     * bienvenida, la del tutor legal y la de renovación salían en el momento
     * aunque estuviera apagado, y el interruptor promete que «el sistema no
     * manda ningún aviso solo». Lo escrito a mano no pasa por aquí.
     */
    public static function automaticosEncendidos(): bool
    {
        return Ajustes::activo('tareas.correos_automaticos');
    }

    /**
     * Cancela los avisos automáticos que ya llegan tarde.
     *
     * Un «vence en 3 días» que sale una semana después dice algo falso. Se
     * cancelan con el motivo escrito, en vez de borrarlos, para que en el
     * listado se vea que existieron y por qué no salieron. Los manuales no se
     * tocan: alguien los escribió y eligió el día.
     */
    private function cancelarAvisosViejos(): int
    {
        // Como texto y sin hora: la columna es DATE, y así la comparación da
        // lo mismo en MySQL, PostgreSQL y SQLite (ver scopeParaEnviarHoy).
        $limite = today()->subDays(self::DIAS_QUE_ESPERA_UN_AVISO)->toDateString();

        $viejas = Notificacion::automaticas()
            ->whereIn('id_estado', [Notificacion::ESTADO_PENDIENTE, Notificacion::ESTADO_FALLIDO])
            ->where('intentos', '<', DB::raw('max_intentos'))
            ->where('fecha_programada', '<', $limite)
            ->get();

        foreach ($viejas as $vieja) {
            $vieja->cancelar(sprintf(
                'No se envió: era para el %s y pasaron más de %d días. Atrasado, el aviso diría algo que ya no es cierto.',
                $vieja->fecha_programada?->format('d/m/Y'),
                self::DIAS_QUE_ESPERA_UN_AVISO
            ));
        }

        return $viejas->count();
    }

    /**
     * Por qué ya no se le manda a este socio lo que tenía en cola, o null.
     *
     * La fila guarda la dirección del día en que se anotó, y entre medio el
     * socio pudo irse a la papelera, quedar sin datos o darse de baja.
     *
     * - Papelera o datos borrados: nunca. Se lo sacó del sistema a propósito.
     * - Dado de baja: los automáticos no, SALVO el de «tu membresía venció».
     *   La tarea nocturna da de baja justo a quien se le venció el plan, y si
     *   ese aviso se atrasa un día (tope, equipo apagado) el socio ya amanece
     *   inactivo: es el único aviso que tiene sentido para alguien de baja,
     *   porque lo invita a volver.
     * - Los manuales a alguien de baja sí salen: escribirle a un socio
     *   inactivo desde su ficha es algo que se hace a sabiendas.
     */
    private function porQueNoSeLeEscribe(Notificacion $notificacion): ?string
    {
        if (! $notificacion->id_cliente) {
            return null;
        }

        $cliente = $notificacion->cliente;

        if (! $cliente) {
            return 'No se envió: el socio ya no existe.';
        }

        if ($cliente->trashed()) {
            return 'No se envió: el socio está en la papelera.';
        }

        if ($cliente->datos_borrados_en) {
            return 'No se envió: se borraron los datos del socio.';
        }

        if (! $cliente->activo
            && $notificacion->esAutomatica()
            && $notificacion->tipoNotificacion?->codigo !== TipoNotificacion::MEMBRESIA_VENCIDA) {
            return 'No se envió: el socio está dado de baja.';
        }

        return null;
    }

    /**
     * Envía notificación de bienvenida a un cliente nuevo
     */
    public function enviarBienvenida(Inscripcion $inscripcion): ?Notificacion
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return null;
        }

        // crearNotificacion() ya mira el interruptor y a quién se le escribe.
        return $this->crearNotificacion($tipoNotificacion, $inscripcion);
    }

    /**
     * Obtiene estadísticas de notificaciones
     */
    public function obtenerEstadisticas(): array
    {
        return [
            'pendientes' => Notificacion::pendientes()->count(),
            'enviadas_hoy' => Notificacion::enviadas()
                ->whereDate('fecha_envio', Carbon::today())
                ->count(),
            'enviadas_mes' => Notificacion::enviadas()
                ->whereMonth('fecha_envio', Carbon::now()->month)
                ->whereYear('fecha_envio', Carbon::now()->year)
                ->count(),
            'fallidas' => Notificacion::fallidas()->count(),
            'total' => Notificacion::count(),
        ];
    }

    /**
     * Envía notificación de renovación exitosa
     *
     * @param Inscripcion $inscripcion La nueva inscripción (renovada)
     */
    public function enviarNotificacionRenovacion(Inscripcion $inscripcion): ?Notificacion
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::RENOVACION)
            ->where('activo', true)
            ->first();

        $destino = $inscripcion->cliente?->correoParaAvisos();

        if (!$tipoNotificacion || $destino === null || ! self::automaticosEncendidos()) {
            return null;
        }

        $notificacion = $this->anotar(
            $tipoNotificacion,
            $inscripcion,
            $destino,
            $this->componer($tipoNotificacion, $inscripcion),
            Carbon::today(),
            'Notificación de renovación programada'
        );

        // Si no sale ahora queda fallida y la tanda de reintentos la recoge.
        $this->mandarYa($notificacion, 'Notificación de renovación');

        return $notificacion->refresh();
    }

    /**
     * Programa notificaciones de pago pendiente
     *
     * @param int $diasVencimiento Días desde que venció el pago
     * @return array
     */
    public function programarNotificacionesPagoPendiente(int $diasVencimiento = 7): array
    {
        $tipoNotificacion = TipoNotificacion::where('codigo', TipoNotificacion::PAGO_PENDIENTE)
            ->where('activo', true)
            ->first();

        if (!$tipoNotificacion) {
            return ['programadas' => 0, 'mensaje' => 'Tipo de notificación no encontrado'];
        }

        // Buscar inscripciones activas con pagos pendientes hace X días
        $inscripciones = Inscripcion::with(['cliente', 'membresia', 'pagos'])
            ->where('id_estado', 100) // Activa
            // Con la misma regla que crearNotificacion(): el menor que solo
            // tiene el correo del apoderado también recibe el aviso.
            ->whereHas('cliente', fn ($q) => $q->where('activo', true)->conCorreoParaAvisos())
            ->get()
            ->filter(fn ($inscripcion) => $inscripcion->obtenerEstadoPago()['pendiente'] > 0);

        $programadas = 0;

        foreach ($inscripciones as $inscripcion) {
            // Verificar si ya se envió notificación reciente
            $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
                ->where('id_tipo_notificacion', $tipoNotificacion->id)
                ->where('fecha_programada', '>=', Carbon::today()->subDays($diasVencimiento))
                ->exists();

            if ($existe) {
                continue;
            }

            $this->crearNotificacion($tipoNotificacion, $inscripcion);
            $programadas++;
        }

        return [
            'programadas' => $programadas,
            'mensaje' => "Se programaron {$programadas} notificaciones de pago pendiente"
        ];
    }

    /**
     * Envía notificación de confirmación al tutor legal cuando se registra un menor
     */
    public function enviarNotificacionTutorLegal(Inscripcion $inscripcion): array
    {
        if (! self::automaticosEncendidos()) {
            return ['enviada' => false, 'mensaje' => 'Los correos automáticos están apagados en Configuración'];
        }

        $inscripcion->load(['cliente', 'membresia']);
        $cliente = $inscripcion->cliente;

        // Verificar que es menor de edad y tiene datos del tutor
        if (!$cliente->es_menor_edad || empty($cliente->apoderado_email)) {
            return [
                'enviada' => false,
                'mensaje' => 'Cliente no es menor de edad o no tiene email de apoderado'
            ];
        }

        $tipoTutor = TipoNotificacion::where('codigo', 'confirmacion_tutor_legal')
            ->where('activo', true)
            ->first();

        // Sin su plantilla no hay qué mandar: antes se caía a la de
        // «notificación manual» con el texto de muestra adentro.
        if (!$tipoTutor) {
            return [
                'enviada' => false,
                'mensaje' => 'La plantilla de confirmación de tutor legal no existe o está desactivada'
            ];
        }

        $notificacion = $this->anotar(
            $tipoTutor,
            $inscripcion,
            $cliente->apoderado_email,
            $this->componer($tipoTutor, $inscripcion),
            Carbon::now(),
            "Confirmación para el tutor legal: {$cliente->apoderado_email}"
        );

        return $this->mandarYa($notificacion, 'Notificación al tutor legal');
    }

    /**
     * Envía notificación de bienvenida automática
     */
    public function enviarNotificacionBienvenida(Inscripcion $inscripcion): array
    {
        // Buscar tipo de notificación de bienvenida
        $tipoBienvenida = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
            ->where('activo', true)
            ->first();

        if (!$tipoBienvenida) {
            return [
                'enviada' => false,
                'mensaje' => 'Tipo de notificación de bienvenida no encontrado o inactivo'
            ];
        }

        if (! self::automaticosEncendidos()) {
            return ['enviada' => false, 'mensaje' => 'Los correos automáticos están apagados en Configuración'];
        }

        // Cargar relaciones necesarias
        $inscripcion->load(['cliente', 'membresia']);
        $cliente = $inscripcion->cliente;
        $destino = $cliente?->correoParaAvisos();

        // Validar que haya a quién escribirle (el apoderado, si es menor)
        if ($destino === null) {
            return [
                'enviada' => false,
                'mensaje' => 'Cliente sin email registrado'
            ];
        }

        // Verificar si ya existe una notificación de bienvenida para esta inscripción
        $existe = Notificacion::where('id_inscripcion', $inscripcion->id)
            ->where('id_tipo_notificacion', $tipoBienvenida->id)
            ->exists();

        if ($existe) {
            return [
                'enviada' => false,
                'mensaje' => 'Ya existe una notificación de bienvenida para esta inscripción'
            ];
        }

        $notificacion = $this->anotar(
            $tipoBienvenida,
            $inscripcion,
            $destino,
            $this->componer($tipoBienvenida, $inscripcion),
            Carbon::now(),
            'Bienvenida programada al inscribirse'
        );

        return $this->mandarYa($notificacion, 'Notificación de bienvenida');
    }
}
