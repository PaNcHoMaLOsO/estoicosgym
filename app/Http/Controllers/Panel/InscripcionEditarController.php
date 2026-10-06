<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Models\MotivoDescuento;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Corregir los datos de una membresía ya vendida.
 *
 * Para el error de tecleo: la fecha de inicio del mes pasado en vez de la de
 * este, un precio mal puesto. Sin esto no había forma de arreglarlo desde el
 * panel, y una fecha de inicio equivocada corre todo el periodo.
 *
 * LO QUE NO SE PUEDE CAMBIAR AQUÍ, aunque el formulario de Blade sí lo dejaba:
 *
 * - EL SOCIO. Mover una membresía a otra persona deja sus pagos apuntando a la
 *   primera, y el socio nuevo aparece con una membresía que nadie le cobró. Eso
 *   es un traspaso y tiene su propia pantalla, que mueve las dos cosas.
 * - EL ESTADO. Pausar, reanudar y cancelar llevan cuentas —días compensados,
 *   pausas gastadas, la fecha de vencimiento nueva— que un desplegable de
 *   estados se salta enteras.
 * - EL PLAN. Cambiar de plan recalcula el precio y acredita lo pagado; también
 *   tiene su pantalla.
 */
class InscripcionEditarController extends Controller
{
    use ValidatesFormToken;

    public function edit(Inscripcion $inscripcion)
    {
        $inscripcion->load(['cliente', 'membresia', 'convenio']);

        $socio = $inscripcion->cliente;

        return Inertia::render('Inscripciones/Editar', [
            'inscripcion' => [
                'uuid' => $inscripcion->uuid,
                'socio' => $socio
                    ? trim("{$socio->nombres} {$socio->apellido_paterno} {$socio->apellido_materno}")
                    : 'Socio eliminado',
                'plan' => $inscripcion->membresia?->nombre,
                'convenio' => $inscripcion->convenio?->nombre,
                'fecha_inicio' => $inscripcion->fecha_inicio?->format('Y-m-d'),
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('Y-m-d'),
                'precio_base' => (int) $inscripcion->precio_base,
                'descuento_aplicado' => (int) $inscripcion->descuento_aplicado,
                'precio_final' => (int) $inscripcion->precio_final,
                'id_motivo_descuento' => $inscripcion->id_motivo_descuento,
                'observaciones' => $inscripcion->observaciones,
                // Lo que ya se cobró: el precio no puede bajar de ahí sin dejar
                // al socio con saldo a favor.
                'cobrado' => (int) $inscripcion->pagos()->sum('monto_abonado'),
            ],
            'motivos' => MotivoDescuento::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'formToken' => (string) Str::uuid(),
            'puedeCambiarPrecio' => request()->user()->puede('configuracion.editar'),
        ]);
    }

    public function update(Request $request, Inscripcion $inscripcion)
    {
        /*
         * LAS OBSERVACIONES TAMBIÉN LAS ESCRIBE EL SISTEMA: cada pausa vencida,
         * cambio de plan o traspaso agrega su línea (la columna es text). Con
         * un tope de 500, una membresía con historia ya no se podía editar en
         * NADA —ni una fecha— porque el texto que viene de vuelta lo superaba.
         * El tope es holgado y nunca menor que lo que ya tiene guardado.
         */
        $topeObservaciones = max(5000, mb_strlen((string) $inscripcion->observaciones));

        $datos = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'precio_base' => 'required|integer|min:0|max:99999999',
            'descuento_aplicado' => 'nullable|integer|min:0|max:99999999',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => "nullable|string|max:{$topeObservaciones}",
        ], [
            'fecha_vencimiento.after' => 'Una membresía tiene que vencer después de empezar.',
            'precio_base.required' => 'Indica cuánto vale.',
        ]);

        $descuento = (int) ($datos['descuento_aplicado'] ?? 0);

        /*
         * EL PRECIO LO CORRIGE QUIEN FIJA LOS PRECIOS. Recepción corrige el
         * error de tecleo en las fechas, pero no el precio ni el descuento, ni
         * alarga o acorta el periodo: con eso se podía dar por pagada una
         * membresía de $100.000 y estirarla diez años sin dejar rastro.
         */
        if (! $request->user()->puede('configuracion.editar')) {
            $diasAntes = $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento);
            $diasAhora = \Illuminate\Support\Carbon::parse($datos['fecha_inicio'])->diffInDays(\Illuminate\Support\Carbon::parse($datos['fecha_vencimiento']));

            if ((int) $datos['precio_base'] !== (int) $inscripcion->precio_base
                || $descuento !== (int) $inscripcion->descuento_aplicado
                || (int) ($datos['id_motivo_descuento'] ?? 0) !== (int) ($inscripcion->id_motivo_descuento ?? 0)) {
                throw ValidationException::withMessages([
                    'precio_base' => 'El precio y el descuento los corrige un administrador.',
                ]);
            }

            if ((int) round($diasAntes) !== (int) round($diasAhora)) {
                throw ValidationException::withMessages([
                    'fecha_vencimiento' => 'Puedes mover las fechas, pero el periodo tiene que durar lo mismo. Para alargarlo, pídeselo a un administrador.',
                ]);
            }
        }

        if ($descuento > $datos['precio_base']) {
            throw ValidationException::withMessages([
                'descuento_aplicado' => 'El descuento no puede superar el precio.',
            ]);
        }

        $final = $datos['precio_base'] - $descuento;
        $cobrado = (int) $inscripcion->pagos()->sum('monto_abonado');

        /*
         * El precio no puede bajar de lo que YA SE COBRÓ.
         *
         * Si se cobraron 40.000 y el precio se corrige a 25.000, el socio queda
         * con 15.000 a favor que nadie le va a devolver y que ningún informe
         * sabe contar. Primero se corrige o se anula el pago de más.
         */
        if ($final < $cobrado) {
            throw ValidationException::withMessages([
                'precio_base' => sprintf(
                    'Ya se cobraron $%s. Corrige o anula los pagos antes de bajar el precio por debajo de esa cifra.',
                    number_format($cobrado, 0, ',', '.')
                ),
            ]);
        }

        if (! $this->validateFormToken($request, 'inscripcion_editar_' . $inscripcion->id)) {
            return back()->with('error', 'Ese cambio ya se guardó.');
        }

        $antes = [
            'fecha_inicio' => $inscripcion->fecha_inicio?->format('Y-m-d'),
            'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('Y-m-d'),
            'precio_final' => (int) $inscripcion->precio_final,
        ];

        DB::transaction(function () use ($inscripcion, $datos, $descuento, $final, $antes) {
            // array_merge y no «+»: con «+» manda lo que llegó del formulario,
            // y un descuento en blanco llegaba como null a una columna que no
            // lo acepta (la membresía no se podía corregir).
            $inscripcion->update(array_merge($datos, [
                'descuento_aplicado' => $descuento,
                'precio_final' => $final,
            ]));

            // Cambiar el precio mueve el saldo de todos sus pagos.
            $inscripcion->recalcularSusPagos();

            // Quién corrigió qué, en el historial.
            $despues = [
                'fecha_inicio' => $inscripcion->fecha_inicio?->format('Y-m-d'),
                'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('Y-m-d'),
                'precio_final' => (int) $inscripcion->precio_final,
            ];

            if ($antes !== $despues) {
                HistorialCambio::create([
                    'tipo_cambio' => 'correccion',
                    'entidad' => 'inscripcion',
                    'entidad_id' => $inscripcion->id,
                    'cliente_id' => $inscripcion->id_cliente,
                    'inscripcion_id' => $inscripcion->id,
                    // El estado no cambia al corregir: el mismo antes y después.
                    'estado_anterior' => $inscripcion->id_estado,
                    'estado_nuevo' => $inscripcion->id_estado,
                    'detalles' => ['antes' => $antes, 'despues' => $despues],
                    'motivo' => $datos['observaciones'] ?? null,
                    'usuario_id' => auth()->id(),
                ]);
            }
        });

        return redirect()
            ->route('panel.inscripciones.show', $inscripcion->uuid)
            ->with('success', 'Membresía corregida.');
    }

    /**
     * Cancela una membresía vigente o pausada: el socio la tuvo y deja de tenerla.
     *
     * La baja del socio y el borrado de sus datos decían «cancélala primero»,
     * pero nada escribía el estado Cancelada y no había botón: la única salida
     * era esperar a que venciera, o borrarla, que es para la que no debió
     * existir. Esto es lo que faltaba.
     *
     * SUS PAGOS NO SE TOCAN. Son lo que de verdad se cobró y siguen en la caja
     * y en los informes. Lo que se dejó de deber, se deja de deber: una
     * cancelada no está en Inscripcion::ESTADOS_CON_DEUDA y su pago pendiente
     * ya no cuenta como cobro por hacer (Pago::scopePendientesDeCobro).
     *
     * El motivo es obligatorio: es lo único que explicará, meses después, por
     * qué un socio que pagó se quedó sin plan.
     */
    public function cancelar(Request $request, Inscripcion $inscripcion)
    {
        $datos = $request->validate([
            'motivo' => 'required|string|min:3|max:500',
        ], [
            'motivo.required' => 'Escribe por qué se cancela: queda en el historial.',
            'motivo.min' => 'Escribe por qué se cancela: queda en el historial.',
        ]);

        $abiertas = [EstadosCodigo::INSCRIPCION_ACTIVA, EstadosCodigo::INSCRIPCION_PAUSADA];

        $cancelada = DB::transaction(function () use ($inscripcion, $datos, $abiertas) {
            // Trabada y mirada otra vez: un doble clic no la cancela dos veces
            // ni deja dos líneas en el historial.
            $actual = Inscripcion::whereKey($inscripcion->getKey())->lockForUpdate()->first();

            if (! $actual || ! in_array((int) $actual->id_estado, $abiertas, true)) {
                return false;
            }

            $antes = (int) $actual->id_estado;
            $motivo = trim($datos['motivo']);

            $actual->update([
                'id_estado' => EstadosCodigo::INSCRIPCION_CANCELADA,
                // Una cancelada no está en pausa: si quedara la marca, las
                // listas de pausados la seguirían contando.
                'pausada' => false,
                'observaciones' => ($actual->observaciones ? $actual->observaciones . "\n" : '')
                    . '[' . now()->format('d/m/Y H:i') . "] Cancelada. Motivo: {$motivo}",
            ]);

            HistorialCambio::registrarCambioEstadoInscripcion(
                $actual,
                $antes,
                EstadosCodigo::INSCRIPCION_CANCELADA,
                $motivo,
            );

            // Los avisos que quedaban por salir hablan de una membresía que ya
            // no tiene: «tu plan vence el viernes» a quien lo canceló ayer.
            Notificacion::where('id_inscripcion', $actual->id)
                ->where('id_estado', Notificacion::ESTADO_PENDIENTE)
                ->get()
                ->each(fn (Notificacion $aviso) => $aviso->cancelar('Membresía cancelada'));

            return true;
        });

        if (! $cancelada) {
            return back()->with('error', 'Solo se cancela una membresía vigente o pausada. Esta ya no lo está.');
        }

        return redirect()
            ->route('panel.inscripciones.show', $inscripcion->uuid)
            ->with('success', 'Membresía cancelada. Sus pagos siguen registrados.');
    }

    /**
     * Manda una membresía vendida a la papelera.
     *
     * Es para la que no debería existir: la que se apuntó dos veces, o la del
     * socio equivocado recién creada. NO es para cancelar una membresía real
     * —eso es un estado, y el socio la tuvo— ni para corregirla, que se hace
     * arriba.
     *
     * SI SE COBRÓ ALGO, NO SE BORRA. La inscripción se iría a la papelera y el
     * pago se quedaría fuera, apuntando a una membresía que ya no se lista:
     * el dinero seguiría contando en los informes y nadie sabría de qué era.
     * Primero se anula el pago —que tiene su pantalla y recalcula el saldo— y
     * después se borra esto. En ese orden las cuentas cuadran en cada paso.
     */
    public function eliminar(Inscripcion $inscripcion)
    {
        $cobrado = (int) $inscripcion->pagos()->sum('monto_abonado');

        if ($cobrado > 0) {
            return back()->with('error', sprintf(
                'No se puede borrar: esta membresía tiene $%s cobrados. Anula primero sus pagos.',
                number_format($cobrado, 0, ',', '.')
            ));
        }

        $socio = $inscripcion->cliente;

        /*
         * SUS PAGOS EN CERO SE VAN CON ELLA.
         *
         * Una membresía vendida «sin pagar» lleva un pago pendiente de $0. Se
         * quedaba fuera de la papelera, sin membresía que listar, y seguía
         * contando como pendiente: el socio ya no podía darse de baja ni
         * borrar sus datos por una deuda que nadie podía ver ni cobrar.
         * Llevan la misma hora de borrado que la membresía: así la papelera
         * sabe cuáles devolver si se recupera (PapeleraController).
         */
        DB::transaction(function () use ($inscripcion) {
            $inscripcion->delete();

            $inscripcion->pagos()->update([
                'deleted_at' => $inscripcion->deleted_at,
                'updated_at' => now(),
            ]);
        });

        // Con el socio en la papelera no hay ficha a la que volver: a la lista.
        return ($socio ? redirect()->route('panel.clientes.show', $socio->uuid) : redirect()->route('panel.inscripciones.index'))
            ->with('success', 'Membresía borrada. Está en la papelera por si hay que recuperarla.');
    }
}
