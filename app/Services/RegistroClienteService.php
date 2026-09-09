<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Rules\RutValido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Alta de un socio, con su inscripcion y su primer pago.
 *
 * POR QUE ESTO NO VIVE EN UN CONTROLADOR. El alta la piden dos paneles —el de
 * Blade en /admin y el de React en /panel— y son casi trescientas lineas entre
 * validaciones, precios y una transaccion de tres tablas. Copiarlas en el
 * segundo panel habria dejado dos versiones que se separan a la primera
 * correccion que se haga solo en una.
 *
 * Los tres flujos son acumulativos:
 *   solo_cliente    -> solo la ficha del socio;
 *   con_membresia   -> ficha + inscripcion, sin cobrar todavia;
 *   completo        -> ficha + inscripcion + pago.
 */
class RegistroClienteService
{
    /** Codigos de la tabla `estados` (la columna guarda el CODIGO, no el id). */
    private const INSCRIPCION_ACTIVA = 100;
    private const PAGO_PENDIENTE = 200;
    private const PAGO_PAGADO = 201;
    private const PAGO_PARCIAL = 202;

    /**
     * Valida la peticion entera y devuelve ya resuelto lo que hace falta para
     * crear los registros: precios calculados, estado del pago y montos.
     *
     * Lanza ValidationException, que Blade e Inertia saben pintar cada uno a su
     * manera sin que este servicio sepa cual de los dos le esta llamando.
     */
    public function validar(Request $request): array
    {
        $flujo = $request->input('flujo_cliente', 'completo');

        $cliente = $request->validate($this->reglasCliente(), $this->mensajesCliente());

        $esMenor = $request->boolean('es_menor_edad');

        if ($esMenor) {
            $request->validate($this->reglasApoderado(), $this->mensajesApoderado());
        }

        $datos = [
            'flujo' => $flujo,
            'cliente' => $cliente,
            'es_menor' => $esMenor,
            'apoderado' => $esMenor ? [
                'consentimiento_apoderado' => $request->boolean('consentimiento_apoderado'),
                'apoderado_nombre' => $request->input('apoderado_nombre'),
                'apoderado_rut' => $request->input('apoderado_rut'),
                'apoderado_email' => $request->input('apoderado_email'),
                'apoderado_telefono' => $request->input('apoderado_telefono'),
                'apoderado_parentesco' => $request->input('apoderado_parentesco'),
                'apoderado_observaciones' => $request->input('apoderado_observaciones'),
            ] : [],
        ];

        if ($flujo === 'solo_cliente') {
            return $datos;
        }

        $datos += $this->validarMembresia($request);

        if ($flujo === 'completo') {
            $datos += $this->validarPago($request, $datos['precio_final']);
        }

        return $datos;
    }

    /**
     * Crea los registros en UNA transaccion y devuelve el socio y el aviso.
     *
     * Todo junto y no en tres pasos: si el pago fallara despues de haber
     * guardado la inscripcion, el socio quedaria con una membresia activa que
     * nadie cobro.
     */
    public function registrar(array $datos, ?UploadedFile $foto = null): array
    {
        return DB::transaction(function () use ($datos, $foto) {
            $cliente = Cliente::create([
                ...$datos['cliente'],
                'es_menor_edad' => $datos['es_menor'],
                'consentimiento_apoderado' => $datos['apoderado']['consentimiento_apoderado'] ?? false,
                'apoderado_nombre' => $datos['apoderado']['apoderado_nombre'] ?? null,
                'apoderado_rut' => $datos['apoderado']['apoderado_rut'] ?? null,
                'apoderado_email' => $datos['apoderado']['apoderado_email'] ?? null,
                'apoderado_telefono' => $datos['apoderado']['apoderado_telefono'] ?? null,
                'apoderado_parentesco' => $datos['apoderado']['apoderado_parentesco'] ?? null,
                'apoderado_observaciones' => $datos['apoderado']['apoderado_observaciones'] ?? null,
                'foto_perfil' => $foto?->store('clientes', 'public'),
                'activo' => true,
            ]);

            if ($datos['flujo'] === 'solo_cliente') {
                return ['cliente' => $cliente, 'mensaje' => 'Cliente registrado exitosamente.'];
            }

            $inscripcion = $this->crearInscripcion($cliente, $datos);

            if ($datos['flujo'] === 'con_membresia') {
                return [
                    'cliente' => $cliente,
                    'mensaje' => 'Cliente y membresía registrados. Pago pendiente.',
                ];
            }

            $this->crearPago($cliente, $inscripcion, $datos);

            $estadoTexto = match ($datos['tipo_pago']) {
                'completo' => 'Pagado completamente',
                'parcial' => 'Abono registrado',
                'pendiente' => 'Pago pendiente',
                'mixto' => $datos['monto_abonado'] > 0 ? 'Abono registrado' : 'Pago pendiente',
            };

            return [
                'cliente' => $cliente,
                'mensaje' => "Registro completo. Estado: {$estadoTexto}",
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Validacion
    // ─────────────────────────────────────────────────────────────────────

    private function reglasCliente(): array
    {
        return [
            'run_pasaporte' => ['nullable', 'unique:clientes,run_pasaporte', new RutValido()],
            'nombres' => ['required', 'string', 'max:50', ...$this->reglasDeNombre('El nombre')],
            'apellido_paterno' => ['required', 'string', 'max:50', ...$this->reglasDeNombre('El apellido')],
            'apellido_materno' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]*$/'],
            'celular' => ['required', 'string', 'regex:/^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('clientes', 'email')],
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => [
                'nullable', 'date',
                'before_or_equal:' . now()->subYears(14)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(110)->format('Y-m-d'),
            ],
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => ['nullable', 'string', 'regex:/^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/'],
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /** Letras y espacios, y sin espacios dobles: «Juan  Pérez» es un desliz. */
    private function reglasDeNombre(string $etiqueta): array
    {
        return [
            'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/',
            function ($attribute, $value, $fail) use ($etiqueta) {
                if (preg_match('/\s{2,}/', $value)) {
                    $fail("{$etiqueta} no debe tener espacios dobles.");
                }
            },
        ];
    }

    private function mensajesCliente(): array
    {
        return [
            'nombres.regex' => 'El nombre solo debe contener letras y espacios.',
            'apellido_paterno.regex' => 'El apellido solo debe contener letras y espacios.',
            'apellido_materno.regex' => 'El apellido materno solo debe contener letras y espacios.',
            'fecha_nacimiento.before_or_equal' => 'El cliente debe tener al menos 14 años.',
            'fecha_nacimiento.after_or_equal' => 'La fecha de nacimiento no es válida.',
            'celular.regex' => 'Formato de celular inválido. Use: +56 9 1234 5678',
            'email.unique' => 'Este correo ya está registrado en otro cliente.',
        ];
    }

    private function reglasApoderado(): array
    {
        return [
            'consentimiento_apoderado' => 'accepted',
            'apoderado_nombre' => 'required|string|max:100',
            'apoderado_rut' => ['required', new RutValido()],
            'apoderado_email' => 'required|email:rfc|max:100',
            'apoderado_telefono' => 'required|string|max:20',
            'apoderado_parentesco' => 'required|string|max:50',
        ];
    }

    private function mensajesApoderado(): array
    {
        return [
            'consentimiento_apoderado.accepted' => 'Debe confirmar la autorización del apoderado.',
            'apoderado_nombre.required' => 'El nombre del apoderado es obligatorio.',
            'apoderado_rut.required' => 'El RUT del apoderado es obligatorio.',
            'apoderado_email.required' => 'El email del apoderado es obligatorio.',
            'apoderado_email.email' => 'El email del apoderado no es válido.',
            'apoderado_telefono.required' => 'El teléfono del apoderado es obligatorio.',
            'apoderado_parentesco.required' => 'El parentesco es obligatorio.',
        ];
    }

    /** Valida la membresia elegida y deja el precio ya calculado. */
    private function validarMembresia(Request $request): array
    {
        $datos = $request->validate([
            'id_convenio' => 'nullable|exists:convenios,id',
            'id_membresia' => 'required|exists:membresias,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'descuento_manual' => 'nullable|numeric|min:0',
            'observaciones_inscripcion' => 'nullable|string|max:500',
        ], [
            'id_membresia.required' => 'Debe seleccionar una membresía.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
        ]);

        $membresia = Membresia::findOrFail($datos['id_membresia']);

        $precio = PrecioMembresia::where('id_membresia', $membresia->id)
            ->where(function ($q) {
                $q->whereNull('fecha_vigencia_hasta')
                    ->orWhere('fecha_vigencia_hasta', '>=', now());
            })
            ->orderBy('fecha_vigencia_hasta', 'desc')
            ->firstOrFail();

        $precioBase = (int) $precio->precio_normal;
        $descuentoConvenio = 0;
        $descuentoManual = (int) ($datos['descuento_manual'] ?? 0);

        // Con convenio manda el precio de convenio, y la diferencia con el
        // normal se registra como descuento para que quede a la vista.
        if (! empty($datos['id_convenio']) && $precio->precio_convenio) {
            $precioBase = (int) $precio->precio_convenio;
            $descuentoConvenio = (int) $precio->precio_normal - (int) $precio->precio_convenio;
        }

        if ($descuentoManual > $precioBase) {
            // Antes esto volvia como aviso suelto arriba del formulario; ahora
            // sale pegado al campo del descuento, que es donde esta el error.
            throw ValidationException::withMessages([
                'descuento_manual' => "El descuento (\${$descuentoManual}) no puede superar el precio (\${$precioBase}).",
            ]);
        }

        return [
            'membresia' => $membresia,
            'precio' => $precio,
            'inscripcion' => $datos,
            'precio_final' => max(0, $precioBase - $descuentoManual),
            'descuento_total' => $descuentoConvenio + $descuentoManual,
        ];
    }

    /** Valida el pago y resuelve monto y estado segun el tipo elegido. */
    private function validarPago(Request $request, int $precioFinal): array
    {
        $datos = $request->validate([
            'tipo_pago' => 'required|in:completo,parcial,pendiente,mixto',
            'monto_abonado' => 'nullable|numeric|min:0',
            'id_metodo_pago' => 'nullable|exists:metodos_pago,id',
            'fecha_pago' => 'required|date|before_or_equal:today',
        ], [
            'tipo_pago.required' => 'Debe seleccionar un tipo de pago.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ]);

        $tipo = $datos['tipo_pago'];
        $abonado = (int) ($datos['monto_abonado'] ?? 0);
        $metodo = $request->input('id_metodo_pago');

        $exigirMetodo = function () use ($metodo) {
            if (! $metodo) {
                throw ValidationException::withMessages([
                    'id_metodo_pago' => 'Debe seleccionar un método de pago.',
                ]);
            }
        };

        // Cada tipo fija su propio monto y su estado. Se escribe plano y no con
        // un match: dos de los cuatro casos REESCRIBEN el monto, y eso dentro de
        // una expresion se lee mal y se rompe facil.
        if ($tipo === 'completo') {
            $exigirMetodo();
            $abonado = $precioFinal;
            $estado = self::PAGO_PAGADO;
        } elseif ($tipo === 'parcial') {
            if ($abonado <= 0 || $abonado >= $precioFinal) {
                throw ValidationException::withMessages([
                    'monto_abonado' => 'En pago parcial, el monto debe ser mayor a $0 y menor al precio total.',
                ]);
            }
            $exigirMetodo();
            $estado = self::PAGO_PARCIAL;
        } elseif ($tipo === 'pendiente') {
            $abonado = 0;
            $estado = self::PAGO_PENDIENTE;
        } else { // mixto
            if ($abonado < 0 || $abonado > $precioFinal) {
                throw ValidationException::withMessages([
                    'monto_abonado' => 'El monto no es válido para pago mixto.',
                ]);
            }
            if ($abonado > 0) {
                $exigirMetodo();
            }

            // Un mixto que cubre el TOTAL queda Pagado, no Parcial. «Mixto»
            // dice que el dinero entro por dos vias, no que falte plata: pagar
            // la mitad en efectivo y la mitad con tarjeta se marcaba como
            // Parcial, y el gimnasio creia que le debian lo que ya cobro.
            $estado = match (true) {
                $abonado <= 0 => self::PAGO_PENDIENTE,
                $abonado >= $precioFinal => self::PAGO_PAGADO,
                default => self::PAGO_PARCIAL,
            };
        }

        return [
            'pago' => $datos,
            'tipo_pago' => $tipo,
            'monto_abonado' => $abonado,
            'estado_pago' => $estado,
            'referencia_pago' => $request->input('referencia_pago'),
            'observaciones_pago' => $request->input('observaciones_pago'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Creacion
    // ─────────────────────────────────────────────────────────────────────

    private function crearInscripcion(Cliente $cliente, array $datos): Inscripcion
    {
        $inicio = Carbon::parse($datos['inscripcion']['fecha_inicio']);

        return Inscripcion::create([
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $datos['membresia']->id,
            'id_precio_acordado' => $datos['precio']->id,
            'id_convenio' => $datos['inscripcion']['id_convenio'] ?? null,
            'id_motivo_descuento' => $datos['inscripcion']['id_motivo_descuento'] ?? null,
            'observaciones' => $datos['inscripcion']['observaciones_inscripcion'] ?? null,
            'fecha_inscripcion' => Carbon::now(),
            'fecha_inicio' => $inicio,
            'fecha_vencimiento' => $inicio->clone()->addDays($datos['membresia']->duracion_dias),
            'precio_base' => (int) $datos['precio']->precio_normal,
            'descuento_aplicado' => $datos['descuento_total'],
            'precio_final' => $datos['precio_final'],
            'id_estado' => self::INSCRIPCION_ACTIVA,
        ]);
    }

    private function crearPago(Cliente $cliente, Inscripcion $inscripcion, array $datos): Pago
    {
        return Pago::create([
            'uuid' => Str::uuid(),
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => $datos['precio_final'],
            'monto_abonado' => $datos['monto_abonado'],
            'monto_pendiente' => max(0, $datos['precio_final'] - $datos['monto_abonado']),
            'fecha_pago' => Carbon::parse($datos['pago']['fecha_pago']),
            'id_metodo_pago' => $datos['pago']['id_metodo_pago'] ?? null,
            'id_estado' => $datos['estado_pago'],
            'tipo_pago' => $datos['tipo_pago'],
            'referencia_pago' => $datos['referencia_pago'],
            'observaciones' => $datos['observaciones_pago'],
        ]);
    }
}
