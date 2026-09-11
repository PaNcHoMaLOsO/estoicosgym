<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Models\PrecioMembresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Alta, edición y baja de los cuatro catálogos: planes, convenios, métodos de
 * pago y motivos de descuento.
 *
 * Van juntos porque son la misma pantalla cuatro veces —un nombre, una
 * descripción, un interruptor de activo— y separarlos serían cuatro
 * controladores repitiendo lo mismo. Lo único propio de cada uno son sus campos
 * y sus reglas, que están abajo en un sitio por catálogo.
 *
 * NADA SE BORRA DE VERDAD. Un plan o un método que ya se usó está referenciado
 * por inscripciones y pagos: borrarlo dejaría fichas apuntando al vacío, y en
 * los informes de años anteriores desaparecerían las cifras. Se desactiva, que
 * es lo que de verdad se quiere: que no vuelva a ofrecerse.
 */
class CatalogoController extends Controller
{
    public function guardarMembresia(Request $request)
    {
        $datos = $this->validarMembresia($request);

        // El plan y su precio se guardan JUNTOS. Un plan sin precio vigente no
        // se puede vender —el alta lo rechaza—, así que crearlo a medias solo
        // sirve para que el formulario de inscripción falle más tarde.
        $membresia = DB::transaction(function () use ($datos) {
            $membresia = Membresia::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'duracion_meses' => $datos['duracion_meses'],
                'duracion_dias' => $datos['duracion_dias'],
                'max_pausas' => $datos['max_pausas'],
                'activo' => $datos['activo'],
            ]);

            $this->ponerPrecio($membresia, $datos);

            return $membresia;
        });

        return redirect()
            ->route('panel.membresias.show', $membresia->uuid)
            ->with('success', "Plan «{$membresia->nombre}» creado.");
    }

    public function actualizarMembresia(Request $request, Membresia $membresia)
    {
        $datos = $this->validarMembresia($request, $membresia);

        DB::transaction(function () use ($membresia, $datos) {
            $membresia->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'duracion_meses' => $datos['duracion_meses'],
                'duracion_dias' => $datos['duracion_dias'],
                'max_pausas' => $datos['max_pausas'],
                'activo' => $datos['activo'],
            ]);

            $this->ponerPrecio($membresia, $datos);
        });

        return back()->with('success', 'Plan actualizado.');
    }

    public function guardarConvenio(Request $request)
    {
        $convenio = Convenio::create($this->validarConvenio($request));
        $this->ponerLogo($convenio, $request);

        return redirect()
            ->route('panel.convenios.show', $convenio->uuid)
            ->with('success', "Convenio «{$convenio->nombre}» creado.");
    }

    public function actualizarConvenio(Request $request, Convenio $convenio)
    {
        $convenio->update($this->validarConvenio($request, $convenio));
        $this->ponerLogo($convenio, $request);

        return back()->with('success', 'Convenio actualizado.');
    }

    public function guardarMetodoPago(Request $request)
    {
        $metodo = MetodoPago::create($this->validarMetodoPago($request));

        return back()->with('success', "Método «{$metodo->nombre}» creado.");
    }

    public function actualizarMetodoPago(Request $request, MetodoPago $metodoPago)
    {
        $metodoPago->update($this->validarMetodoPago($request, $metodoPago));

        return back()->with('success', 'Método actualizado.');
    }

    public function guardarMotivo(Request $request)
    {
        $motivo = MotivoDescuento::create($this->validarMotivo($request));

        return back()->with('success', "Motivo «{$motivo->nombre}» creado.");
    }

    public function actualizarMotivo(Request $request, MotivoDescuento $motivoDescuento)
    {
        $motivoDescuento->update($this->validarMotivo($request, $motivoDescuento));

        return back()->with('success', 'Motivo actualizado.');
    }

    /**
     * Deja de ofrecerlo, sin borrarlo.
     *
     * El botón dice «desactivar» y no «eliminar» a propósito: lo que se quiere
     * es que no aparezca al inscribir o al cobrar, no hacer desaparecer las
     * inscripciones y los pagos que ya lo usaron.
     */
    public function alternar(Request $request, string $catalogo, string $id)
    {
        $modelos = [
            'membresias' => Membresia::class,
            'convenios' => Convenio::class,
            'metodos-pago' => MetodoPago::class,
            'motivos-descuento' => MotivoDescuento::class,
            'especialistas' => \App\Models\Especialista::class,
        ];

        abort_unless(isset($modelos[$catalogo]), 404);

        $fila = $modelos[$catalogo]::where(
            in_array($catalogo, ['membresias', 'convenios', 'especialistas'], true) ? 'uuid' : 'id',
            $id
        )->firstOrFail();

        $fila->update(['activo' => ! $fila->activo]);

        return back()->with(
            'success',
            $fila->activo
                ? "«{$fila->nombre}» vuelve a estar disponible."
                : "«{$fila->nombre}» ya no se ofrecerá. Lo que ya lo usaba no cambia."
        );
    }

    /**
     * Escribe el precio del plan, si cambió.
     *
     * Los precios NO se pisan: se cierra el que estaba y se abre otro. El
     * histórico tiene que seguir contando lo que se cobró de verdad, o una
     * subida de precio reescribiría hacia atrás lo que pagó cada socio.
     *
     * @param array<string,mixed> $datos
     */
    private function ponerPrecio(Membresia $membresia, array $datos): void
    {
        $vigente = $membresia->precios()
            ->where('activo', true)
            ->orderByDesc('fecha_vigencia_desde')
            ->first();

        $nuevoNormal = (float) $datos['precio'];
        $nuevoConvenio = $datos['precio_convenio'] !== null ? (float) $datos['precio_convenio'] : null;

        if ($vigente
            && (float) $vigente->precio_normal === $nuevoNormal
            && ($vigente->precio_convenio === null ? null : (float) $vigente->precio_convenio) === $nuevoConvenio
        ) {
            return;
        }

        if ($vigente) {
            // Se cierra HOY, no ayer: si el precio se corrige el mismo día en
            // que se creó, «hasta» caería antes que «desde» y el tramo quedaría
            // del revés. Cuál manda no depende de las fechas sino de `activo`,
            // que es lo que mira la consulta al vender.
            $vigente->update([
                'activo' => false,
                'fecha_vigencia_hasta' => now()->format('Y-m-d'),
            ]);
        }

        PrecioMembresia::create([
            'id_membresia' => $membresia->id,
            'precio_normal' => $nuevoNormal,
            'precio_convenio' => $nuevoConvenio,
            'fecha_vigencia_desde' => now()->format('Y-m-d'),
            'activo' => true,
        ]);
    }

    /** @return array<string,mixed> */
    private function validarMembresia(Request $request, ?Membresia $actual = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('membresias', 'nombre')->ignore($actual?->id)],
            'descripcion' => 'nullable|string|max:500',
            'duracion_meses' => 'required|integer|min:0|max:120',
            'duracion_dias' => 'required|integer|min:0|max:3650',
            'max_pausas' => 'required|integer|min:0|max:12',
            'precio' => 'required|numeric|min:0|max:99999999',
            'precio_convenio' => 'nullable|numeric|min:0|max:99999999',
            'activo' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un plan con ese nombre.',
            'precio.required' => 'Indica cuánto cuesta. Un plan sin precio no se puede vender.',
        ]);

        // Una duración de cero por los dos lados es un plan que vence el mismo
        // día en que se compra.
        if ((int) $datos['duracion_meses'] === 0 && (int) $datos['duracion_dias'] === 0) {
            throw ValidationException::withMessages([
                'duracion_dias' => 'El plan tiene que durar algo: pon los meses o los días.',
            ]);
        }

        // El precio de convenio es una REBAJA: por encima del normal no lo es.
        if (isset($datos['precio_convenio']) && $datos['precio_convenio'] > $datos['precio']) {
            throw ValidationException::withMessages([
                'precio_convenio' => 'El precio con convenio no puede ser mayor que el normal.',
            ]);
        }

        $datos['activo'] = (bool) ($datos['activo'] ?? true);
        $datos['precio_convenio'] = $datos['precio_convenio'] ?? null;

        return $datos;
    }

    /** @return array<string,mixed> */
    private function validarConvenio(Request $request, ?Convenio $actual = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('convenios', 'nombre')->ignore($actual?->id)],
            'tipo' => 'required|in:institucion_educativa,empresa,organizacion,otro',
            'descripcion' => 'nullable|string|max:500',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'descuento_monto' => 'nullable|numeric|min:0|max:99999999',
            'contacto_nombre' => 'nullable|string|max:100',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:100',
            // La pagina publica. El logo sin SVG: puede llevar codigo y se
            // ejecutaria al abrir el archivo desde la web.
            'mostrar_en_web' => 'boolean',
            'requisito_web' => 'nullable|string|max:150',
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'quitar_logo' => 'boolean',
            'activo' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un convenio con ese nombre.',
            'logo.image' => 'Ese archivo no es una imagen.',
            'logo.mimes' => 'El logo tiene que ser PNG, JPG o WEBP.',
            'logo.max' => 'El logo no puede pesar más de 2 MB.',
        ]);

        $datos['descuento_porcentaje'] = $datos['descuento_porcentaje'] ?? 0;
        $datos['descuento_monto'] = $datos['descuento_monto'] ?? 0;

        // Si no llega, no se toca: un formulario que no lo conozca no puede
        // esconder de la web un convenio que otro marco.
        if (array_key_exists('mostrar_en_web', $datos)) {
            $datos['mostrar_en_web'] = (bool) $datos['mostrar_en_web'];
        }

        // El logo NO va con los demas datos: lo pone ponerLogo().
        unset($datos['logo'], $datos['quitar_logo']);
        $datos['activo'] = (bool) ($datos['activo'] ?? true);

        return $datos;
    }

    /** @return array<string,mixed> */
    private function validarMetodoPago(Request $request, ?MetodoPago $actual = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('metodos_pago', 'nombre')->ignore($actual?->id)],
            'descripcion' => 'nullable|string|max:500',
            'requiere_comprobante' => 'boolean',
            'activo' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un método de pago con ese nombre.',
        ]);

        $datos['requiere_comprobante'] = (bool) ($datos['requiere_comprobante'] ?? false);
        $datos['activo'] = (bool) ($datos['activo'] ?? true);

        return $datos;
    }

    /** @return array<string,mixed> */
    private function validarMotivo(Request $request, ?MotivoDescuento $actual = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('motivos_descuento', 'nombre')->ignore($actual?->id)],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un motivo con ese nombre.',
        ]);

        $datos['activo'] = (bool) ($datos['activo'] ?? true);

        return $datos;
    }

    /**
     * El logo del convenio, aparte de los demas datos.
     *
     * Aparte a proposito: si fuera un campo mas, editar el nombre sin volver a
     * subir el logo lo dejaria vacio. Solo se toca si llega un archivo nuevo o
     * si se pide quitarlo, y el archivo viejo se borra DESPUES de guardar.
     */
    private function ponerLogo(Convenio $convenio, Request $request): void
    {
        $anterior = $convenio->logo;

        if ($request->hasFile('logo')) {
            $convenio->update(['logo' => $request->file('logo')->store('convenios', 'public')]);
        } elseif ($request->boolean('quitar_logo')) {
            $convenio->update(['logo' => null]);
        } else {
            return;
        }

        if ($anterior && $anterior !== $convenio->logo) {
            Storage::disk('public')->delete($anterior);
        }
    }
}
