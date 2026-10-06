<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoNotificacion;
use App\Services\ContratoDigitalService;
use App\Services\EnvioManualService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Los textos de los correos que manda el gimnasio.
 *
 * Cada plantilla lleva {variables} entre llaves que se cambian por los datos de
 * cada socio al mandarla. La lista de las que se saben rellenar viaja a la
 * pantalla: escribir una que no exista hace que el correo le llegue al socio
 * con las llaves puestas, y eso ha pasado de verdad.
 */
class PlantillaController extends Controller
{
    /**
     * Las variables que el sistema sabe rellenar, con lo que significan.
     *
     * Es la MISMA lista que usa EnvioManualService al componer. Si aquí sobrara
     * una, la pantalla ofrecería algo que después sale en blanco.
     */
    public const VARIABLES = [
        'nombre' => 'Nombre completo del socio',
        'nombre_cliente' => 'Lo mismo. La usan las plantillas de vencimiento',
        'nombres' => 'Solo el nombre de pila',
        'apellido' => 'Solo el apellido paterno',
        'email' => 'Su correo',
        'celular' => 'Su celular',
        'es_menor_edad' => '«sí» o «no»',
        'nombre_apoderado' => 'El apoderado, si es menor',
        'rut_apoderado' => 'El RUT del apoderado',
        'rut' => 'El RUT o pasaporte del socio',
        'fecha_nacimiento' => 'Su fecha de nacimiento',
        'membresia' => 'El plan que tiene contratado',
        'precio' => 'Lo que cuesta su plan',
        'fecha_inicio' => 'Desde cuándo corre',
        'fecha_vencimiento' => 'Cuándo se le acaba',
        'dias_restantes' => 'Cuántos días le quedan',
        'monto_total' => 'El total de su membresía',
        'monto_pagado' => 'Lo que lleva pagado',
        'total_pagado' => 'Lo mismo, con otro nombre',
        'monto_pendiente' => 'Lo que debe',
        'saldo_pendiente' => 'Lo mismo, con otro nombre',
        'tipo_pago' => '«Completo», «Parcial» o «Pendiente»',
        'metodo_pago' => 'Con qué pagó la última vez',
        'fecha_registro' => 'Cuándo se anotó la inscripción, con la hora',
        'fecha_pago' => 'Cuándo pagó por última vez',
        'monto_ultimo_pago' => 'Cuánto pagó esa vez',
        'fecha_pausa' => 'Desde cuándo está pausada',
        'fecha_reactivacion' => 'Cuándo vuelve',
        'fecha_activacion' => 'Cuándo vuelve a estar activa',
        'motivo_pausa' => 'Por qué se pausó',
        // Los del gimnasio, de Configuración → Datos del gimnasio, Horario y
        // Google y redes. Antes iban escritos a mano dentro de cada plantilla.
        'gimnasio' => 'El nombre del gimnasio',
        'ciudad' => 'La ciudad del gimnasio',
        'telefono_gimnasio' => 'El teléfono del gimnasio (o el WhatsApp)',
        'telefono_enlace' => 'El mismo, sin espacios, para un enlace tel:',
        'email_gimnasio' => 'El correo de contacto',
        'direccion_gimnasio' => 'La dirección y la comuna',
        'instagram' => 'El enlace al Instagram',
        'enlace_mapa' => 'El enlace de Google Maps',
        'horario' => 'El horario de la semana, en una línea',
        'enlace_resena' => 'El enlace para dejar una reseña en Google (Configuración → Página web)',
    ];

    public function index()
    {
        return Inertia::render('Notificaciones/Plantillas', [
            'plantillas' => TipoNotificacion::withCount('notificaciones')
                ->orderBy('nombre')
                ->get()
                ->map(fn (TipoNotificacion $t) => [
                    'id' => $t->id,
                    'codigo' => $t->codigo,
                    'nombre' => $t->nombre,
                    'descripcion' => $t->descripcion,
                    'asunto_email' => $t->asunto_email,
                    'plantilla_email' => $t->plantilla_email,
                    'dias_anticipacion' => (int) $t->dias_anticipacion,
                    'activo' => (bool) $t->activo,
                    'enviar_email' => (bool) $t->enviar_email,
                    'usos' => $t->notificaciones_count,
                    // Las que usa y no se saben rellenar: salen con las llaves
                    // puestas en el correo del socio.
                    'rotas' => $this->variablesQueNadieRellena($t),
                    // Las del contrato saben rellenar además su enlace.
                    'extras' => $this->extras($t->codigo),
                ]),
            'variables' => self::VARIABLES,
        ]);
    }

    public function update(Request $request, TipoNotificacion $tipoNotificacion)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('tipo_notificaciones', 'nombre')->ignore($tipoNotificacion->id)],
            'descripcion' => 'nullable|string|max:500',
            'asunto_email' => 'required|string|max:255',
            'plantilla_email' => 'required|string',
            'dias_anticipacion' => 'required|integer|min:0|max:60',
            'activo' => 'boolean',
            'enviar_email' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay una plantilla con ese nombre.',
            'plantilla_email.required' => 'El correo no puede quedar vacío.',
        ]);

        /*
         * NO SE GUARDA con variables que no existen.
         *
         * Se escribe {nombre_del_socio} en vez de {nombre} y el correo le llega
         * a la persona con las llaves puestas. El sitio para darse cuenta es
         * este, no la bandeja del socio.
         */
        $rotas = $this->variablesSueltas($datos['asunto_email'] . ' ' . $datos['plantilla_email'], $tipoNotificacion->codigo);

        if ($rotas !== []) {
            throw ValidationException::withMessages([
                'plantilla_email' => sprintf(
                    'No se conocen %s. Usa solo las de la lista, o el correo saldrá con las llaves puestas.',
                    '{' . implode('}, {', $rotas) . '}'
                ),
            ]);
        }

        $tipoNotificacion->update($datos + [
            'activo' => $request->boolean('activo'),
            'enviar_email' => $request->boolean('enviar_email'),
        ]);

        return back()->with('success', "Plantilla «{$tipoNotificacion->nombre}» guardada.");
    }

    /**
     * Cómo queda con datos de verdad.
     *
     * Se compone contra un socio real —el primero que haya— porque una
     * plantilla se lee distinta con «{nombre}» que con «Pablo Bravo Castro», y
     * los fallos de redacción solo se ven así.
     */
    public function vistaPrevia(Request $request, TipoNotificacion $tipoNotificacion, EnvioManualService $envio)
    {
        /*
         * LO QUE ESTA EN EL EDITOR, NO LO GUARDADO. Antes se componia la
         * plantilla tal como estaba en la base: se cambiaba el texto, se pulsaba
         * «Ver con datos reales» y salia el de antes, que es justo lo que no se
         * queria mirar. Ahora el editor manda su asunto y su texto, se validan
         * con las mismas reglas que al guardar y se componen sobre una copia en
         * memoria: aqui NO se guarda nada. Sin ellos (GET), lo guardado.
         */
        if ($request->isMethod('post')) {
            $borrador = $request->validate([
                'asunto_email' => 'required|string|max:255',
                'plantilla_email' => 'required|string',
            ], [
                'plantilla_email.required' => 'El correo no puede quedar vacío.',
            ]);

            $tipoNotificacion = (clone $tipoNotificacion)->forceFill($borrador);
        }

        /*
         * Con datos de un socio de verdad solo si quien mira puede ver socios:
         * si no, una plantilla con {rut} {fecha_nacimiento} {celular} serviría
         * para leer los datos de alguien sin tener permiso para su ficha. Sin
         * ese permiso se usa un socio de ejemplo, que no existe.
         */
        $socio = $request->user()?->puede('clientes.ver')
            ? Cliente::whereNotNull('email')->where('email', '!=', '')->first()
            : new Cliente([
                'nombres' => 'Camila',
                'apellido_paterno' => 'Rojas',
                'apellido_materno' => 'Soto',
                'email' => 'camila.rojas@example.com',
                'celular' => '+56 9 1234 5678',
                'run_pasaporte' => '12.345.678-5',
                'fecha_nacimiento' => '1995-04-12',
            ]);

        if (! $socio) {
            return response()->json([
                'error' => 'No hay ningún socio con correo con el que probar la plantilla.',
            ], 422);
        }

        // Las del contrato llevan un enlace que solo existe al mandarlas: se
        // muestran con uno de muestra.
        $extra = in_array($tipoNotificacion->codigo, ContratoDigitalService::PLANTILLAS, true)
            ? app(ContratoDigitalService::class)->variablesDeEjemplo($socio)
            : [];

        return response()->json(
            $envio->componer($socio, $tipoNotificacion, null, $extra) + ['socio' => $socio->nombre_completo]
        );
    }

    /**
     * Lo que saben rellenar además las dos plantillas del contrato.
     *
     * @return array<string,string>
     */
    private function extras(?string $codigo): array
    {
        return in_array($codigo, ContratoDigitalService::PLANTILLAS, true) ? ContratoDigitalService::VARIABLES : [];
    }

    /** @return list<string> */
    private function variablesQueNadieRellena(TipoNotificacion $tipo): array
    {
        return $this->variablesSueltas($tipo->asunto_email . ' ' . $tipo->plantilla_email, $tipo->codigo);
    }

    /** @return list<string> */
    private function variablesSueltas(string $texto, ?string $codigo = null): array
    {
        preg_match_all('/\{([a-z_]+)\}/i', $texto, $encontradas);

        return array_values(array_diff(
            array_unique($encontradas[1]),
            [...array_keys(self::VARIABLES), ...array_keys($this->extras($codigo))]
        ));
    }
}
