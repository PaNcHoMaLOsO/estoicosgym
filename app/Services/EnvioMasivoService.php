<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Support\Ajustes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Un mismo correo a un grupo de socios.
 *
 * TOPE DURO Y CUENTA A LA VISTA. Un envío a «todos» son cientos de correos, y
 * el de Blade los mandaba uno detrás de otro dentro de la propia petición web:
 * con más de un puñado, PHP corta por tiempo a mitad de la lista y nadie sabe
 * quién recibió el correo y quién no. Aquí hay un tope, se dice cuántos van a
 * salir ANTES de mandarlos, y al terminar se dice cuántos salieron de verdad.
 */
class EnvioMasivoService
{
    /**
     * Cuántos caben en un envío.
     *
     * No es un capricho: los correos salen uno a uno en la misma petición, y
     * pasado ese número el servidor corta antes de terminar. Con un gimnasio
     * más grande esto tiene que irse a una cola.
     *
     * Sale de Configuración por si el servidor de otro aguanta más, pero
     * subirlo sin saberlo es como quitarle el tope: la lista se corta igual,
     * solo que más tarde.
     */
    public static function tope(): int
    {
        return Ajustes::numero('correo.tope_masivo');
    }

    public function __construct(private readonly CorreoService $correo)
    {
    }

    /**
     * Los grupos a los que se puede escribir, y a quién incluye cada uno.
     *
     * @return array<string,array{titulo:string,explicacion:string}>
     */
    public static function grupos(): array
    {
        return [
            'por_vencer_7' => [
                'titulo' => 'Se les acaba esta semana',
                'explicacion' => 'Membresía activa que vence en 7 días o menos.',
            ],
            'por_vencer_15' => [
                'titulo' => 'Se les acaba en dos semanas',
                'explicacion' => 'Membresía activa que vence en 15 días o menos.',
            ],
            'vencidas' => [
                'titulo' => 'Se les acabó',
                'explicacion' => 'Su última membresía está vencida.',
            ],
            'deben' => [
                'titulo' => 'Deben dinero',
                'explicacion' => 'Tienen algún pago pendiente o a medias.',
            ],
            'activas' => [
                'titulo' => 'Al día',
                'explicacion' => 'Membresía vigente ahora mismo.',
            ],
            'todos' => [
                'titulo' => 'Todos los socios activos',
                'explicacion' => 'Todos los que están de alta y tienen correo.',
            ],
        ];
    }

    /**
     * Quiénes recibirían el correo.
     *
     * Solo socios de alta y CON correo: a los demás no se les puede escribir, y
     * contarlos infla la cifra que se le enseña a quien va a pulsar «Enviar».
     */
    public function destinatarios(string $grupo, ?int $idMembresia = null): Collection
    {
        $consulta = Cliente::query()
            ->where('activo', true)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $this->acotar($consulta, $grupo, $idMembresia);

        return $consulta
            ->orderBy('apellido_paterno')
            ->get(['id', 'uuid', 'nombres', 'apellido_paterno', 'apellido_materno', 'email']);
    }

    /**
     * Manda el correo a todos, y cuenta lo que pasó con cada uno.
     *
     * @return array{enviados:int,fallidos:int,motivos:list<string>}
     *
     * @throws ValidationException
     */
    public function enviar(string $grupo, string $asunto, string $mensaje, ?int $idMembresia = null): array
    {
        $socios = $this->destinatarios($grupo, $idMembresia);

        if ($socios->isEmpty()) {
            throw ValidationException::withMessages([
                'grupo' => 'Ese grupo no tiene a nadie con correo ahora mismo.',
            ]);
        }

        $tope = self::tope();

        if ($socios->count() > $tope) {
            throw ValidationException::withMessages([
                'grupo' => sprintf(
                    'Ese grupo son %d socios y de una vez caben %d. Elige un grupo más pequeño.',
                    $socios->count(),
                    $tope
                ),
            ]);
        }

        $plantilla = $this->plantillaDeAvisosSueltos();

        $enviados = 0;
        $fallidos = 0;
        $motivos = [];

        foreach ($socios as $socio) {
            $correo = $this->personalizar($socio, $asunto, $mensaje);

            $notificacion = Notificacion::create([
                'id_tipo_notificacion' => $plantilla->id,
                'id_cliente' => $socio->id,
                'id_inscripcion' => $this->ultimaInscripcion($socio)?->id,
                'email_destino' => $socio->email,
                'asunto' => $correo['asunto'],
                'contenido' => $correo['mensaje'],
                'id_estado' => Notificacion::ESTADO_PENDIENTE,
                'fecha_programada' => today(),
                'tipo_envio' => 'manual',
                'enviado_por_user_id' => auth()->id(),
            ]);

            try {
                $this->correo->enviar($socio->email, $correo['asunto'], $correo['mensaje']);
                $notificacion->marcarComoEnviada();
                $enviados++;
            } catch (\Throwable $e) {
                /*
                 * Un fallo NO para el envío.
                 *
                 * Una dirección mal escrita entre ciento cincuenta no puede
                 * dejar sin su correo a los otros ciento cuarenta y nueve. Se
                 * anota en su fila, se cuenta, y al final se dice cuántos
                 * fallaron y por qué.
                 */
                $notificacion->marcarComoFallida($e->getMessage());
                $fallidos++;

                $nombre = trim("{$socio->nombres} {$socio->apellido_paterno}");
                $motivos[] = "{$nombre}: {$e->getMessage()}";
            }
        }

        return [
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            // Los primeros cinco: la lista entera no cabe en un aviso, y los
            // demás están en el listado de notificaciones con su motivo.
            'motivos' => array_slice($motivos, 0, 5),
        ];
    }

    /**
     * El correo con el nombre de cada socio puesto.
     *
     * @return array{asunto:string,mensaje:string}
     */
    public function personalizar(Cliente $socio, string $asunto, string $mensaje): array
    {
        $inscripcion = $this->ultimaInscripcion($socio);

        // SE ESCAPA lo que viene del socio: su nombre va a parar dentro del
        // HTML del correo, y un apellido con un «<» partiría el mensaje.
        $datos = [
            '{nombre}' => e($socio->nombre_completo),
            '{nombres}' => e($socio->nombres),
            '{email}' => e($socio->email),
            '{membresia}' => e($inscripcion?->membresia?->nombre ?? 'Sin plan'),
            '{fecha_vencimiento}' => $inscripcion?->fecha_vencimiento?->format('d/m/Y') ?? '',
        ];

        return [
            'asunto' => str_replace(array_keys($datos), array_values($datos), $asunto),
            'mensaje' => str_replace(array_keys($datos), array_values($datos), $mensaje),
        ];
    }

    /** Lo que se puede escribir entre llaves en un aviso suelto. */
    public static function variables(): array
    {
        return [
            'nombre' => 'Nombre completo del socio',
            'nombres' => 'Solo el nombre de pila',
            'email' => 'Su correo',
            'membresia' => 'El plan que tiene',
            'fecha_vencimiento' => 'Cuándo se le acaba',
        ];
    }

    private function acotar(Builder $consulta, string $grupo, ?int $idMembresia): void
    {
        $conInscripcion = fn (callable $filtro) => $consulta->whereHas('inscripciones', $filtro);

        match ($grupo) {
            'por_vencer_7' => $conInscripcion(fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
                ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(7)])),

            'por_vencer_15' => $conInscripcion(fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
                ->whereBetween('fecha_vencimiento', [Carbon::today(), Carbon::today()->addDays(15)])),

            'vencidas' => $conInscripcion(fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_VENCIDA)),

            'deben' => $consulta->whereHas('pagos', fn ($q) => $q
                ->whereIn('id_estado', EstadosCodigo::PAGO_PENDIENTES_COBRO)),

            'activas' => $conInscripcion(fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)),

            // «Todos» ya está acotado a los de alta con correo.
            'todos' => null,

            default => throw ValidationException::withMessages([
                'grupo' => 'Ese grupo no existe.',
            ]),
        };

        if ($idMembresia) {
            $consulta->whereHas('inscripciones', fn ($q) => $q
                ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)
                ->where('id_membresia', $idMembresia));
        }
    }

    private function ultimaInscripcion(Cliente $socio): ?Inscripcion
    {
        return Inscripcion::where('id_cliente', $socio->id)
            ->with('membresia')
            ->latest()
            ->first();
    }

    /**
     * Bajo qué tipo se anotan estos avisos.
     *
     * Van todos bajo el mismo para que se puedan encontrar juntos en el
     * historial, y para no ensuciar el catálogo de plantillas con una entrada
     * por cada comunicado.
     */
    private function plantillaDeAvisosSueltos(): TipoNotificacion
    {
        return TipoNotificacion::firstOrCreate(
            ['codigo' => 'aviso_suelto'],
            [
                'nombre' => 'Aviso suelto',
                'descripcion' => 'Comunicados escritos a mano y mandados a un grupo',
                // Se escribe entero cada vez, así que la plantilla no se usa.
                'asunto_email' => 'Aviso de PRO GYM',
                'plantilla_email' => '<p>Aviso de PRO GYM</p>',
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => true,
            ]
        );
    }
}
