<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Registro de un pago sobre una inscripcion existente.
 *
 * POR QUE ESTO NO VIVE EN UN CONTROLADOR. Lo pedian los dos paneles —el de Blade
 * en /admin, ya borrado, y el de React en /panel— y son mas de doscientas lineas
 * entre validaciones, calculo de saldos y tres formas de cobrar. Copiarlas en el
 * segundo panel habria dejado dos versiones que se separan a la primera
 * correccion que se haga solo en una, que es justo lo que ya paso con el valor
 * 'abono'.
 *
 * TODO ERROR SALE COMO ValidationException. Antes se mezclaban `withErrors()` y
 * `with('error')`, asi que segun cual saltara el aviso aparecia al lado del
 * campo o como un cartel arriba, sin criterio. Con una sola forma, Blade e
 * Inertia lo pintan cada uno a su manera sin que el servicio sepa quien llama.
 */
class RegistroPagoService
{
    /** Codigos de la tabla `estados` (la columna guarda el CODIGO, no el id). */
    private const PAGO_PAGADO = 201;
    private const PAGO_PARCIAL = 202;

    /** Lo minimo que se acepta como abono. */
    private const ABONO_MINIMO = 1000;

    /**
     * Valida la peticion y devuelve los datos del pago ya resueltos.
     *
     * @return array{datos: array<string,mixed>, inscripcion: Inscripcion, tipo: string, completa: bool}
     */
    public function validar(Request $request): array
    {
        $tipo = (string) $request->input('tipo_pago', 'abono');

        $validado = $request->validate($this->reglas($tipo), $this->mensajes());

        $inscripcion = Inscripcion::with('cliente')->findOrFail($validado['id_inscripcion']);

        $this->exigirInscripcionCobrable($inscripcion);

        $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base);
        $yaPagado = (int) $inscripcion->pagos()->sum('monto_abonado');
        $pendiente = $total - $yaPagado;

        if ($pendiente <= 0) {
            throw ValidationException::withMessages([
                'id_inscripcion' => 'Esta inscripción ya está pagada completamente.',
            ]);
        }

        $abonado = $this->montoSegunTipo($request, $tipo, $pendiente);

        $this->exigirQueNoSeaUnDuplicadoReciente($inscripcion, $abonado, $validado['fecha_pago']);

        $saldo = $pendiente - $abonado;
        $cuotas = max(1, (int) ($validado['cantidad_cuotas'] ?? 1));

        $datos = [
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            // El total es SIEMPRE el precio de la inscripcion, no el saldo que
            // quedaba: si no, un segundo abono diria que la membresia costo lo
            // que faltaba por pagar.
            'monto_total' => $total,
            'monto_abonado' => $abonado,
            'monto_pendiente' => $saldo,
            'cantidad_cuotas' => $cuotas,
            'numero_cuota' => 1,
            'monto_cuota' => intdiv($abonado, $cuotas),
            'fecha_pago' => $validado['fecha_pago'],
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
            // La UI dice «abono»; el enum de la tabla `pagos` no tiene ese
            // valor y su equivalente es 'parcial'. Guardar 'abono' reventaba
            // con un 1265 de MySQL y dejaba el pago parcial inservible.
            'tipo_pago' => $tipo === 'abono' ? 'parcial' : $tipo,
            'referencia_pago' => $validado['referencia_pago'] ?? null,
            'observaciones' => $validado['observaciones'] ?? null,
            'id_estado' => $saldo <= 0 ? self::PAGO_PAGADO : self::PAGO_PARCIAL,
        ];

        if ($tipo === 'mixto') {
            $datos['id_metodo_pago'] = $validado['id_metodo_pago1'];
            $datos['id_metodo_pago2'] = $validado['id_metodo_pago2'];
            $datos['monto_metodo1'] = (int) $request->input('monto_metodo1');
            $datos['monto_metodo2'] = (int) $request->input('monto_metodo2');
        } else {
            $datos['id_metodo_pago'] = $validado['id_metodo_pago'];
        }

        return [
            'datos' => $datos,
            'inscripcion' => $inscripcion,
            'tipo' => $tipo,
            'completa' => $saldo <= 0,
        ];
    }

    /**
     * Crea el pago y, si con el queda saldada la membresia, programa el aviso
     * al socio.
     *
     * EL SALDO SE VUELVE A MIRAR AQUI DENTRO, CON LA FILA TRABADA. Entre validar
     * y escribir puede haberse cobrado por otra caja: las dos peticiones leian
     * el mismo saldo, las dos lo daban por bueno y entre las dos entraba mas
     * plata que lo que valia la membresia. El turno del formulario no lo
     * atrapaba —son dos envios distintos, cada uno con su token— ni el corte al
     * duplicado, que solo mira el mismo monto en la misma fecha. Y el recalculo
     * de la noche tampoco lo arregla: los dos cobros son de verdad, y el socio
     * queda con plata a favor que nadie le va a devolver.
     *
     * Al trabar la inscripcion, el segundo cobro espera al primero y ve lo que
     * de verdad quedaba por pagar.
     */
    public function registrar(array $resultado): Pago
    {
        $pago = DB::transaction(function () use ($resultado) {
            $inscripcion = Inscripcion::whereKey($resultado['inscripcion']->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $datos = $resultado['datos'];
            $abonado = (int) $datos['monto_abonado'];
            $total = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base);
            $pendiente = $total - (int) $inscripcion->pagos()->sum('monto_abonado');

            if ($abonado > $pendiente) {
                throw ValidationException::withMessages([
                    'monto_abonado' => $pendiente > 0
                        ? sprintf(
                            'Mientras se llenaba este formulario se cobró sobre esta membresía: ahora el saldo es $%s. Revisa el monto antes de guardar.',
                            number_format($pendiente, 0, ',', '.'),
                        )
                        : 'Mientras se llenaba este formulario esta membresía quedó pagada por completo. No queda saldo que cobrar.',
                ]);
            }

            // El saldo y el estado se escriben con lo que hay AHORA, no con lo
            // que se leyo al validar.
            $saldo = $pendiente - $abonado;

            return Pago::create([
                'monto_total' => $total,
                'monto_pendiente' => $saldo,
                'id_estado' => $saldo <= 0 ? self::PAGO_PAGADO : self::PAGO_PARCIAL,
            ] + $datos);
        });

        // El aviso, ya con el cobro escrito y confirmado: si se mandara dentro
        // de la transaccion y esta se cayera, el socio tendria el correo de un
        // pago que no existe.
        if ((int) $pago->monto_pendiente <= 0) {
            $this->avisarPagoCompletado($resultado['inscripcion']);
        }

        return $pago;
    }

    /** @return array<string,mixed> */
    private function reglas(string $tipo): array
    {
        $reglas = [
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'tipo_pago' => 'required|in:abono,completo,mixto',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
            'cantidad_cuotas' => 'nullable|integer|min:1|max:12',
        ];

        if ($tipo === 'mixto') {
            // En mixto el dinero entra por dos vias, y tienen que ser distintas:
            // dos veces «efectivo» no es un pago mixto, es uno solo partido.
            $reglas['id_metodo_pago1'] = 'required|exists:metodos_pago,id';
            $reglas['id_metodo_pago2'] = 'required|exists:metodos_pago,id|different:id_metodo_pago1';
            $reglas['monto_metodo1'] = 'required|integer|min:1';
            $reglas['monto_metodo2'] = 'required|integer|min:1';

            return $reglas;
        }

        $reglas['id_metodo_pago'] = 'required|exists:metodos_pago,id';

        return $reglas;
    }

    /** @return array<string,string> */
    private function mensajes(): array
    {
        return [
            'id_inscripcion.required' => 'Debe indicar sobre qué inscripción se cobra.',
            'tipo_pago.in' => 'El tipo de pago debe ser abono, completo o mixto.',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'id_metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'id_metodo_pago1.required' => 'Debe seleccionar el primer método de pago.',
            'id_metodo_pago2.required' => 'Debe seleccionar el segundo método de pago.',
            'id_metodo_pago2.different' => 'Los dos métodos de pago deben ser distintos.',
        ];
    }

    /**
     * No se cobra sobre una membresia que ya termino ni a un socio dado de baja:
     * el dinero entraria a una inscripcion que nadie va a usar.
     */
    private function exigirInscripcionCobrable(Inscripcion $inscripcion): void
    {
        if (in_array($inscripcion->id_estado, EstadosCodigo::INSCRIPCION_FINALIZADOS)) {
            $estado = EstadosCodigo::getNombre($inscripcion->id_estado);

            throw ValidationException::withMessages([
                'id_inscripcion' => "No se puede cobrar sobre una inscripción {$estado}.",
            ]);
        }

        if ($inscripcion->cliente && ! $inscripcion->cliente->activo) {
            throw ValidationException::withMessages([
                'id_inscripcion' => 'No se puede cobrar a un socio dado de baja.',
            ]);
        }
    }

    /** Monto que entra segun la forma de cobro, ya validado contra el saldo. */
    private function montoSegunTipo(Request $request, string $tipo, int $pendiente): int
    {
        if ($tipo === 'completo') {
            return $pendiente;
        }

        if ($tipo === 'abono') {
            $abonado = (int) $request->input('monto_abonado', 0);

            if ($abonado < self::ABONO_MINIMO || $abonado > $pendiente) {
                throw ValidationException::withMessages([
                    'monto_abonado' => sprintf(
                        'El abono debe estar entre $%s y $%s, que es el saldo pendiente.',
                        number_format(self::ABONO_MINIMO, 0, ',', '.'),
                        number_format($pendiente, 0, ',', '.'),
                    ),
                ]);
            }

            return $abonado;
        }

        // Mixto: los dos montos tienen que sumar EXACTAMENTE el saldo. Si
        // sumaran de menos seria un abono, y de mas habria que devolver vuelto.
        $uno = (int) $request->input('monto_metodo1', 0);
        $dos = (int) $request->input('monto_metodo2', 0);

        if ($uno <= 0 || $dos <= 0) {
            throw ValidationException::withMessages([
                'monto_metodo1' => 'Los dos montos deben ser mayores que cero.',
            ]);
        }

        if ($uno + $dos !== $pendiente) {
            throw ValidationException::withMessages([
                'monto_metodo1' => sprintf(
                    'Los dos montos deben sumar exactamente $%s, que es el saldo pendiente.',
                    number_format($pendiente, 0, ',', '.'),
                ),
            ]);
        }

        return $uno + $dos;
    }

    /**
     * Corta el pago repetido que llega tarde.
     *
     * La reserva de turno del formulario para el doble clic; esto para el caso
     * lento: quien no vio la confirmacion, volvio atras y cobro otra vez. Mismo
     * monto, misma fecha y misma inscripcion en cinco minutos es, casi siempre,
     * el mismo cobro dos veces.
     */
    private function exigirQueNoSeaUnDuplicadoReciente(Inscripcion $inscripcion, int $abonado, string $fecha): void
    {
        if ($abonado <= 0) {
            return;
        }

        $repetido = Pago::where('id_inscripcion', $inscripcion->id)
            ->where('monto_abonado', $abonado)
            ->whereDate('fecha_pago', $fecha)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($repetido) {
            throw ValidationException::withMessages([
                'monto_abonado' => 'Hace menos de cinco minutos se registró un cobro idéntico sobre esta inscripción. Revísalo en el listado antes de repetirlo.',
            ]);
        }
    }

    /**
     * El aviso al socio no puede tumbar el cobro: el dinero ya entro y la fila
     * ya esta escrita. Si el correo falla, se registra y se sigue.
     */
    private function avisarPagoCompletado(Inscripcion $inscripcion): void
    {
        try {
            $tipo = TipoNotificacion::where('codigo', TipoNotificacion::PAGO_COMPLETADO)
                ->where('activo', true)
                ->first();

            if (! $tipo || ! $inscripcion->cliente?->email) {
                return;
            }

            $inscripcion->load(['cliente', 'membresia', 'pagos']);

            app(NotificacionService::class)->crearNotificacion($tipo, $inscripcion);
        } catch (\Throwable $e) {
            Log::error('No se pudo programar el aviso de pago completado: ' . $e->getMessage(), [
                'inscripcion' => $inscripcion->id,
            ]);
        }
    }
}
