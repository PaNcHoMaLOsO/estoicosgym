<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use App\Rules\RutValido;
use App\Support\Ajustes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    /**
     * Que se acepta como foto de perfil.
     *
     * 2 MB y solo jpeg/png/webp, lo mismo que exigia el panel de Blade: una
     * foto de meson no necesita mas y la del telefono de alguien pesa cuatro
     * veces eso. `image` a secas no basta —deja pasar formatos que el navegador
     * no siempre pinta—, asi que la lista va escrita.
     */
    public const REGLAS_FOTO = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'];

    /** Lo que se le dice a quien sube algo que no vale. */
    public const MENSAJES_FOTO = [
        'foto_perfil.image' => 'Ese archivo no es una imagen.',
        'foto_perfil.mimes' => 'La foto tiene que ser JPG, PNG o WEBP.',
        'foto_perfil.max' => 'La foto no puede pesar más de 2 MB.',
    ];

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

        /*
         * La foto se comprueba APARTE y no dentro de reglasCliente().
         *
         * Metida ahi volveria dentro de $cliente, y `validated()` devuelve la
         * clave siempre que venga en la peticion —aunque venga vacia—. El panel
         * de Blade manda su <input type="file"> este o no elegido, asi que
         * `actualizar()` recibiria 'foto_perfil' => null y borraria la foto
         * cada vez que alguien corrigiera un telefono. Aqui solo se valida;
         * guardarla es cosa de `registrar()` y de `cambiarFoto()`.
         */
        $request->validate(['foto_perfil' => self::REGLAS_FOTO], self::MENSAJES_FOTO);

        $request->validate([
            'contrato_firmado_en' => ['nullable', 'date', 'before_or_equal:today'],
            'consentimiento_imagen' => ['boolean'],
            'consentimiento_difusion' => ['boolean'],
        ], ['contrato_firmado_en.before_or_equal' => 'La fecha de la firma no puede ser futura.']);

        $esMenor = $request->boolean('es_menor_edad');

        if ($esMenor) {
            $request->validate($this->reglasApoderado(), $this->mensajesApoderado());
        }

        $firmado = $request->input('contrato_firmado_en') ?: null;

        $datos = [
            'flujo' => $flujo,
            'cliente' => $cliente,
            'es_menor' => $esMenor,
            'contrato' => [
                // La version se toma de Ajustes y no del formulario: es la que
                // se esta haciendo firmar hoy, y escribirla a mano en el alta
                // seria una ocasion mas de teclear mal un numero.
                'contrato_version' => $firmado
                    ? (string) Ajustes::obtener('reglas.version_contrato')
                    : null,
                'contrato_firmado_en' => $firmado,
                'consentimiento_imagen' => $request->boolean('consentimiento_imagen'),
                'consentimiento_difusion' => $request->boolean('consentimiento_difusion'),
            ],
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
     * Revisa los datos de una ficha que YA existe.
     *
     * Solo la ficha: al editar no se toca la membresia ni los pagos, que tienen
     * sus propias pantallas —renovar, cobrar— con sus propias reglas. Aqui se
     * corrige un telefono mal escrito, no se cambia lo que se cobro.
     *
     * @return array<string,mixed>
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validarEdicion(Request $request, Cliente $cliente): array
    {
        $datos = $request->validate(
            $this->reglasCliente($cliente),
            $this->mensajesCliente()
        );

        $esMenor = $request->boolean('es_menor_edad');

        if ($esMenor) {
            $request->validate($this->reglasApoderado(), $this->mensajesApoderado());
        }

        return [
            'cliente' => $datos,
            'es_menor' => $esMenor,
            // Al dejar de ser menor los datos del apoderado se BORRAN, no se
            // quedan escondidos: si vuelve a marcarse como menor por error,
            // aparecerian los de antes como si siguieran valiendo.
            'apoderado' => $esMenor ? [
                'consentimiento_apoderado' => $request->boolean('consentimiento_apoderado'),
                'apoderado_nombre' => $request->input('apoderado_nombre'),
                'apoderado_rut' => $request->input('apoderado_rut'),
                'apoderado_email' => $request->input('apoderado_email'),
                'apoderado_telefono' => $request->input('apoderado_telefono'),
                'apoderado_parentesco' => $request->input('apoderado_parentesco'),
                'apoderado_observaciones' => $request->input('apoderado_observaciones'),
            ] : [
                'consentimiento_apoderado' => false,
                'apoderado_nombre' => null,
                'apoderado_rut' => null,
                'apoderado_email' => null,
                'apoderado_telefono' => null,
                'apoderado_parentesco' => null,
                'apoderado_observaciones' => null,
            ],
        ];
    }

    /**
     * Guarda los cambios de una ficha.
     *
     * @param array<string,mixed> $datos lo que devolvio validarEdicion()
     */
    public function actualizar(Cliente $cliente, array $datos): Cliente
    {
        $campos = $datos['cliente'];

        // LA FOTO NO SE TOCA AQUI, pase lo que pase. Un <input type="file">
        // vacio llega igual en la peticion, y si se colara en estos campos la
        // ficha se guardaria con 'foto_perfil' => null: corregir un telefono
        // dejaria al socio sin cara. Se pone y se quita en `cambiarFoto()`.
        unset($campos['foto_perfil']);

        $cliente->update(
            $campos
            + ['es_menor_edad' => $datos['es_menor']]
            + $datos['apoderado']
        );

        return $cliente->refresh();
    }

    /**
     * Deja constancia del contrato firmado y de lo que el socio autorizo.
     *
     * EL CONTRATO SE FIRMA EN PAPEL: aqui solo queda que firmo, que dia y QUE
     * VERSION. La version es lo que hace falta el dia que cambie el texto, para
     * poder saber cual acepto cada uno en vez de «alguna de las dos».
     *
     * @param array<string,mixed> $datos version, fecha y los dos permisos
     */
    public function registrarContrato(Cliente $cliente, array $datos): Cliente
    {
        $teniaPermiso = (bool) $cliente->consentimiento_imagen;

        $cliente->update([
            'contrato_version' => $datos['contrato_version'] ?: null,
            'contrato_firmado_en' => $datos['contrato_firmado_en'] ?: null,
            'consentimiento_imagen' => $datos['consentimiento_imagen'],
            'consentimiento_difusion' => $datos['consentimiento_difusion'],
        ]);

        /*
         * RETIRAR EL PERMISO SE LLEVA LA FOTO.
         *
         * El contrato dice que el socio puede retirarlo cuando quiera. Si al
         * desmarcar la casilla la foto siguiera en el disco, esa frase seria
         * mentira: quedaria guardada la cara de alguien que ya dijo que no.
         * Se borra aqui y no se deja para que alguien se acuerde despues.
         */
        if ($teniaPermiso && ! $cliente->consentimiento_imagen && $cliente->foto_perfil) {
            $this->cambiarFoto($cliente, null, quitar: true);
        }

        return $cliente->refresh();
    }

    /**
     * Pone, cambia o quita la foto de un socio.
     *
     * EL ARCHIVO VIEJO SE BORRA al reemplazarlo o al quitarlo. Son caras de
     * personas: cuando alguien pide que se vaya la suya, tiene que irse del
     * disco y no quedarse ahi de sobra. Lo que NO se borra es la foto de quien
     * se da de baja, porque esa baja se deshace desde la papelera y el socio
     * volveria sin cara.
     */
    public function cambiarFoto(Cliente $cliente, ?UploadedFile $nueva, bool $quitar = false): Cliente
    {
        $anterior = $cliente->foto_perfil;

        if ($quitar) {
            $cliente->update(['foto_perfil' => null]);
        } elseif ($nueva) {
            $cliente->update(['foto_perfil' => $nueva->store('clientes', 'public')]);
        } else {
            return $cliente;
        }

        // El borrado va DESPUES de guardar, no antes: si el update fallara,
        // borrar primero dejaria la ficha apuntando a un archivo que ya no esta.
        if ($anterior && $anterior !== $cliente->foto_perfil) {
            Storage::disk('public')->delete($anterior);
        }

        return $cliente->refresh();
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
                ...($datos['contrato'] ?? []),
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

    /**
     * @param ?Cliente $actual el socio que se esta editando, si es una edicion
     */
    private function reglasCliente(?Cliente $actual = null): array
    {
        return [
            // Al editar, su propio RUT y su propio correo NO cuentan como
            // repetidos: sin esto, guardar una ficha sin tocar esos campos
            // se rechazaria a si misma.
            'run_pasaporte' => [
                'nullable',
                Rule::unique('clientes', 'run_pasaporte')->ignore($actual?->id),
                new RutValido(),
            ],
            'nombres' => ['required', 'string', 'max:50', ...$this->reglasDeNombre('El nombre')],
            'apellido_paterno' => ['required', 'string', 'max:50', ...$this->reglasDeNombre('El apellido')],
            'apellido_materno' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]*$/'],
            'celular' => ['required', 'string', 'regex:/^(\+?56)?[\s]?9[\s]?[0-9]{4}[\s]?[0-9]{4}$/'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                Rule::unique('clientes', 'email')->ignore($actual?->id),
            ],
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
            // La misma regla que inscribir y renovar: ver Membresia::vencimientoDesde().
            'fecha_vencimiento' => $datos['membresia']->vencimientoDesde($inicio),
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
