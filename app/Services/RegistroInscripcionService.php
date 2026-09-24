<?php

namespace App\Services;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Support\PrecioAcordado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Alta de una inscripción: el socio, el plan, el precio y el primer pago.
 *
 * Vive aparte del controlador porque lo usan el alta y la renovación, y porque
 * son tres cosas distintas metidas en una: calcular cuánto sale, crear la
 * inscripción y anotar cómo se pagó. Antes estaban las tres en un método de 345
 * líneas del controlador de Blade.
 *
 * TODO SE GUARDA JUNTO O NO SE GUARDA NADA. Antes no: la inscripción se creaba
 * primero y el pago después, sin transacción. Si el pago fallaba —y con «pago
 * pendiente» fallaba SIEMPRE, porque escribía fecha_pago en NULL sobre una
 * columna NOT NULL— quedaba una inscripción sin ningún pago detrás. En pantalla
 * salía un error, así que se volvía a intentar, y ahí saltaba «este cliente ya
 * tiene una inscripción Activa»: la primera sí se había guardado. Esa
 * inscripción huérfana no aparece en ningún informe de caja.
 */
class RegistroInscripcionService
{
    /** Formas de pagar la primera cuota. */
    public const FORMAS = ['completo', 'abono', 'mixto', 'pendiente'];

    /**
     * Revisa el formulario y deja calculado todo lo que hay que guardar.
     *
     * No escribe nada. Devuelve el paquete que espera registrar().
     *
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    public function validar(Request $request, bool $exigirQuePuedaInscribirse = true): array
    {
        $forma = (string) $request->input('tipo_pago', 'completo');

        $datos = $request->validate($this->reglas($forma), $this->mensajes());

        $cliente = Cliente::find($datos['id_cliente']);

        // Al renovar NO se comprueba: el socio tiene una membresia vigente
        // justamente porque la esta renovando.
        if ($exigirQuePuedaInscribirse) {
            $this->exigirClienteInscribible($cliente);
        } elseif (! $cliente) {
            throw ValidationException::withMessages(['id_cliente' => 'Ese socio no existe.']);
        }

        $membresia = Membresia::findOrFail($datos['id_membresia']);
        $precio = $this->precioVigente($membresia);

        $base = (int) round($precio->precio_normal);
        $descuentoManual = (int) round((float) ($datos['descuento_aplicado'] ?? 0));

        if ($descuentoManual > $base) {
            throw ValidationException::withMessages([
                'descuento_aplicado' => sprintf(
                    'El descuento (%s) no puede superar el precio del plan (%s).',
                    $this->pesos($descuentoManual),
                    $this->pesos($base)
                ),
            ]);
        }

        $descuento = $this->descuentoTotal($precio, $datos, $base, $descuentoManual);
        $final = max(0, $base - $descuento);

        $abonos = $this->abonos($request, $forma, $final);

        $inicio = Carbon::parse($datos['fecha_inicio']);

        return [
            'cliente' => $cliente,
            'membresia' => $membresia,
            'forma' => $forma,
            'inscripcion' => [
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresia->id,
                'id_convenio' => $datos['id_convenio'] ?? null,
                'id_motivo_descuento' => $datos['id_motivo_descuento'] ?? null,
                'id_precio_acordado' => $precio->id,
                // Una inscripción nace ACTIVA y punto. El formulario viejo
                // dejaba elegir cualquier estado de la tabla, incluidos
                // «Vencida» y «Cancelada»: nacer cancelada no significa nada.
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                'fecha_inscripcion' => now()->format('Y-m-d'),
                'fecha_inicio' => $inicio->format('Y-m-d'),
                'fecha_vencimiento' => $this->vencimiento($inicio, $membresia)->format('Y-m-d'),
                'precio_base' => $base,
                'descuento_aplicado' => $descuento,
                'precio_final' => $final,
                'max_pausas_permitidas' => $membresia->max_pausas ?? 2,
                'observaciones' => $datos['observaciones'] ?? null,
            ],
            'fecha_pago' => $datos['fecha_pago'] ?? now()->format('Y-m-d'),
            'abonos' => $abonos,
            'final' => $final,
        ];
    }

    /**
     * Lo mismo, pero encadenando con la membresía que termina.
     *
     * El socio ya está: no se elige ni se comprueba que pueda inscribirse —tiene
     * una membresía, por eso la está renovando—. El resto del cálculo es
     * idéntico, así que se reutiliza entero.
     *
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    public function validarRenovacion(Request $request, Inscripcion $anterior): array
    {
        $cliente = $anterior->cliente;

        if (! $cliente) {
            throw ValidationException::withMessages([
                'id_cliente' => 'Esa membresía no tiene socio: no se puede renovar.',
            ]);
        }

        // El formulario de renovar no pregunta a quién: se sabe. Se inyecta para
        // que las reglas de validar() encuentren el campo que esperan.
        $request->merge(['id_cliente' => $cliente->id]);

        $resultado = $this->validar($request, exigirQuePuedaInscribirse: false);

        $resultado['inscripcion'] += [
            'es_cambio_plan' => true,
            'tipo_cambio' => 'renovacion',
            'id_inscripcion_anterior' => $anterior->id,
        ];

        $resultado['anterior'] = $anterior;

        return $resultado;
    }

    /**
     * Guarda la inscripción y sus pagos en una sola transacción.
     *
     * @param array<string,mixed> $resultado lo que devolvió validar()
     */
    public function registrar(array $resultado): Inscripcion
    {
        $inscripcion = DB::transaction(function () use ($resultado) {
            $inscripcion = Inscripcion::create($resultado['inscripcion']);

            // Quien vuelve y paga está activo otra vez: lo reactiva la venta,
            // no un paso aparte que se olvida.
            $socio = $resultado['cliente'] ?? null;
            if ($socio instanceof Cliente && ! $socio->activo) {
                $socio->update(['activo' => true]);
            }

            foreach ($this->filasDePago($inscripcion, $resultado) as $fila) {
                Pago::create($fila);
            }

            if (isset($resultado['anterior'])) {
                $this->cerrarLaAnterior($resultado['anterior'], $inscripcion);
            }

            return $inscripcion;
        });

        $this->avisarAlSocio($inscripcion);

        return $inscripcion;
    }

    /**
     * La membresía que se renueva DEJA DE ESTAR VIGENTE.
     *
     * Renovar no la cerraba: creaba la nueva y dejaba la vieja tal cual. Como se
     * puede renovar hasta 30 días antes de que venza, el socio se quedaba con
     * DOS membresías activas a la vez —contadas dos veces en el panel y en los
     * informes— y encima la comprobación de «ya tiene una membresía activa»
     * impedía volver a inscribirlo más adelante.
     *
     * Se marca Vencida y no Cancelada: su periodo terminó, no se anuló. Cancelar
     * es otra cosa y se hace a mano.
     */
    private function cerrarLaAnterior(Inscripcion $anterior, Inscripcion $nueva): void
    {
        // El ayudante del modelo, que sabe qué columnas tiene la tabla.
        // Escribirlo a mano era justo lo que fallaba: se ponían cuatro que no
        // existen y se dejaban sin poner cuatro que son NOT NULL.
        //
        // ANTES de cerrarla: el historial anota de qué estado venía, y si se
        // cerrara primero anotaría «de Vencida a Activa», que no dice nada.
        HistorialCambio::registrarRenovacion($anterior, $nueva);

        $anterior->update(['id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA]);
    }

    /**
     * Las filas de la tabla pagos que corresponden a esta alta.
     *
     * Siempre hay al menos una, INCLUSO cuando el socio no paga nada: esa fila
     * en estado Pendiente es lo que después hace que aparezca en «por cobrar».
     * Sin ella la deuda no existe para el sistema.
     *
     * @param array<string,mixed> $resultado
     * @return list<array<string,mixed>>
     */
    private function filasDePago(Inscripcion $inscripcion, array $resultado): array
    {
        $final = $resultado['final'];
        $abonos = $resultado['abonos'];
        $comun = [
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $final,
            'periodo_inicio' => $inscripcion->fecha_inicio->format('Y-m-d'),
            'periodo_fin' => $inscripcion->fecha_vencimiento->format('Y-m-d'),
        ];

        if ($abonos === []) {
            return [$comun + [
                'monto_abonado' => 0,
                'monto_pendiente' => $final,
                'id_estado' => EstadosCodigo::PAGO_PENDIENTE,
                'tipo_pago' => 'pendiente',
                'id_metodo_pago' => null,
                // NO va NULL: la columna es NOT NULL y la fila entera se
                // rechazaba. Se anota el día en que se generó la deuda, que es
                // el dato que de verdad hace falta para saber cuánto lleva sin
                // pagar; el día en que pague se registra en su propio pago.
                'fecha_pago' => $resultado['fecha_pago'],
                'observaciones' => 'Inscripción sin pago inicial',
            ]];
        }

        $total = array_sum(array_column($abonos, 'monto'));
        $estado = $total >= $final
            ? EstadosCodigo::PAGO_PAGADO
            : EstadosCodigo::PAGO_PARCIAL;

        $filas = [];
        $restante = $final;

        foreach ($abonos as $abono) {
            $restante -= $abono['monto'];

            $filas[] = $comun + [
                'monto_abonado' => $abono['monto'],
                'monto_pendiente' => max(0, $restante),
                'id_estado' => $estado,
                // La base guarda «parcial»; la pantalla dice «abono». Son la
                // misma cosa con dos nombres, y el enum solo acepta el suyo.
                'tipo_pago' => $resultado['forma'] === 'abono' ? 'parcial' : $resultado['forma'],
                'id_metodo_pago' => $abono['id_metodo_pago'],
                'fecha_pago' => $resultado['fecha_pago'],
                'observaciones' => $abono['observaciones'] ?? null,
            ];
        }

        return $filas;
    }

    /**
     * Cuánto se abona y con qué método, según la forma de pago elegida.
     *
     * Lista vacía = no paga nada ahora.
     *
     * @return list<array{monto:int,id_metodo_pago:int|null,observaciones?:string}>
     *
     * @throws ValidationException
     */
    private function abonos(Request $request, string $forma, int $final): array
    {
        if ($forma === 'pendiente') {
            return [];
        }

        if ($forma === 'mixto') {
            return $this->abonosMixtos($request, $final);
        }

        /*
         * «PAGA EL PLAN COMPLETO» ES EL TOTAL, NO LO QUE SE ESCRIBA.
         *
         * Se leía del campo monto: si alguien escribía menos —o el precio
         * cambiaba por un convenio después de escribirlo—, quedaba un pago
         * llamado «completo» con saldo pendiente, que los informes leían como
         * pagado y «por cobrar» como deuda a la vez.
         */
        $monto = $forma === 'completo'
            ? $final
            : (int) round((float) $request->input('monto_abonado', 0));

        if ($monto > $final) {
            throw ValidationException::withMessages([
                'monto_abonado' => sprintf(
                    'No se puede cobrar %s por una inscripción de %s.',
                    $this->pesos($monto),
                    $this->pesos($final)
                ),
            ]);
        }

        // Cobrar el total exacto y llamarlo «abono» deja un pago parcial sin
        // saldo, que después aparece en «por cobrar» debiendo cero.
        if ($forma === 'abono' && $monto >= $final && $final > 0) {
            throw ValidationException::withMessages([
                'monto_abonado' => 'Un abono tiene que ser menor que el total. Si paga todo, elige «Paga el plan completo».',
            ]);
        }

        return [[
            'monto' => $monto,
            'id_metodo_pago' => (int) $request->input('id_metodo_pago'),
        ]];
    }

    /**
     * Reparto entre dos o más métodos.
     *
     * @return list<array{monto:int,id_metodo_pago:int|null,observaciones?:string}>
     *
     * @throws ValidationException
     */
    private function abonosMixtos(Request $request, int $final): array
    {
        $detalle = json_decode((string) $request->input('detalle_pagos_mixto', '[]'), true);

        if (! is_array($detalle) || $detalle === []) {
            throw ValidationException::withMessages([
                'detalle_pagos_mixto' => 'Indica con qué métodos se reparte el pago.',
            ]);
        }

        $abonos = [];
        $suma = 0;

        foreach ($detalle as $i => $parte) {
            $monto = (int) round((float) ($parte['monto'] ?? 0));
            $metodo = (int) ($parte['id_metodo_pago'] ?? 0);

            if ($monto <= 0 || $metodo <= 0) {
                throw ValidationException::withMessages([
                    'detalle_pagos_mixto' => 'Cada parte del pago necesita un monto mayor que cero y un método.',
                ]);
            }

            $suma += $monto;
            $abonos[] = [
                'monto' => $monto,
                'id_metodo_pago' => $metodo,
                'observaciones' => 'Pago mixto - ' . ($parte['metodo_nombre'] ?? 'parte ' . ($i + 1)),
            ];
        }

        if ($suma > $final) {
            throw ValidationException::withMessages([
                'detalle_pagos_mixto' => sprintf(
                    'Las partes suman %s y la inscripción vale %s.',
                    $this->pesos($suma),
                    $this->pesos($final)
                ),
            ]);
        }

        return $abonos;
    }

    /**
     * El precio con el que se cobra hoy este plan.
     *
     * @throws ValidationException si el plan no tiene ninguno vigente
     */
    private function precioVigente(Membresia $membresia)
    {
        $precio = $membresia->precios()
            ->where('activo', true)
            ->where('fecha_vigencia_desde', '<=', now())
            ->orderByDesc('fecha_vigencia_desde')
            ->first();

        // Antes esto era `$precio->precio_normal ?? 0`: un plan al que se le
        // olvidó cargar el precio inscribía a la gente GRATIS y sin avisar.
        if (! $precio) {
            throw ValidationException::withMessages([
                'id_membresia' => "El plan «{$membresia->nombre}» no tiene un precio vigente cargado. Config. → Membresías.",
            ]);
        }

        return $precio;
    }

    /**
     * Descuento del convenio más el que se escriba a mano.
     *
     * El precio con convenio sale de PrecioAcordado: primero el que ese
     * convenio negoció para este plan —el club que paga 15.000 la mensualidad—
     * y si no tiene, el «con convenio» general del plan.
     */
    private function descuentoTotal($precio, array $datos, int $base, int $manual): int
    {
        $porConvenio = max(0, $base - PrecioAcordado::para($precio, $datos['id_convenio'] ?? null));

        return min($base, $porConvenio + $manual);
    }

    /** El último día que la membresía sigue sirviendo. */
    private function vencimiento(Carbon $inicio, Membresia $membresia): Carbon
    {
        return $membresia->vencimientoDesde($inicio);
    }

    /**
     * @throws ValidationException
     */
    private function exigirClienteInscribible(?Cliente $cliente): void
    {
        if (! $cliente) {
            throw ValidationException::withMessages(['id_cliente' => 'Ese socio no existe.']);
        }

        /*
         * UNO DE BAJA SÍ SE PUEDE INSCRIBIR: venderle un plan lo reactiva.
         *
         * Antes había que ir a su ficha, reactivarlo, volver y recién ahí
         * inscribirlo. En el mesón, con la persona esperando, lo más rápido era
         * crearlo de nuevo, y así nacían los duplicados. Casi todos los socios
         * que vinieron de las planillas están de baja: es el caso de todos los
         * días. Lo que sigue sin poder volver es quien pidió borrar sus datos.
         */
        if ($cliente->datos_borrados_en) {
            throw ValidationException::withMessages([
                'id_cliente' => 'A ese socio se le borraron los datos personales: no se puede volver a inscribir.',
            ]);
        }

        $vigente = Inscripcion::where('id_cliente', $cliente->id)
            ->whereIn('id_estado', [
                EstadosCodigo::INSCRIPCION_ACTIVA,
                EstadosCodigo::INSCRIPCION_PAUSADA,
            ])
            ->first();

        if ($vigente) {
            $estado = $vigente->id_estado === EstadosCodigo::INSCRIPCION_ACTIVA ? 'activa' : 'pausada';

            throw ValidationException::withMessages([
                'id_cliente' => "Este socio ya tiene una membresía {$estado}. Usa Renovar o espera a que venza.",
            ]);
        }
    }

    /** @return array<string,mixed> */
    private function reglas(string $forma): array
    {
        $reglas = [
            'id_cliente' => 'required|exists:clientes,id',
            'id_membresia' => 'required|exists:membresias,id',
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'fecha_inicio' => 'required|date',
            'descuento_aplicado' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'tipo_pago' => 'required|in:' . implode(',', self::FORMAS),
        ];

        if ($forma === 'mixto') {
            $reglas['detalle_pagos_mixto'] = 'required|string';
            $reglas['fecha_pago'] = 'required|date';
        } elseif ($forma === 'completo') {
            // «Todo» no pregunta cuánto: es el total, y lo pone abonos().
            $reglas['id_metodo_pago'] = 'required|exists:metodos_pago,id';
            $reglas['fecha_pago'] = 'required|date';
        } elseif ($forma !== 'pendiente') {
            $reglas['monto_abonado'] = 'required|numeric|min:1';
            $reglas['id_metodo_pago'] = 'required|exists:metodos_pago,id';
            $reglas['fecha_pago'] = 'required|date';
        }

        return $reglas;
    }

    /** @return array<string,string> */
    private function mensajes(): array
    {
        return [
            'id_cliente.required' => 'Elige a quién se va a inscribir.',
            'id_membresia.required' => 'Elige el plan.',
            'fecha_inicio.required' => 'Indica desde cuándo corre la membresía.',
            'monto_abonado.required' => 'Indica cuánto paga.',
            'monto_abonado.min' => 'El monto tiene que ser mayor que cero.',
            'id_metodo_pago.required' => 'Indica cómo paga.',
        ];
    }

    /**
     * Bienvenida al socio y, si es menor, aviso al apoderado.
     *
     * Fuera de la transacción y a prueba de fallos: que el correo no salga no
     * puede tumbar una inscripción que ya está pagada y guardada.
     */
    private function avisarAlSocio(Inscripcion $inscripcion): void
    {
        try {
            $notificaciones = app(NotificacionService::class);
            $notificaciones->enviarNotificacionBienvenida($inscripcion);

            $cliente = $inscripcion->cliente;

            if ($cliente && $cliente->es_menor_edad && ! empty($cliente->apoderado_email)) {
                $notificaciones->enviarNotificacionTutorLegal($inscripcion);
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo avisar del alta #' . $inscripcion->id . ': ' . $e->getMessage());
        }
    }

    private function pesos(int $monto): string
    {
        return '$' . number_format($monto, 0, ',', '.');
    }
}
