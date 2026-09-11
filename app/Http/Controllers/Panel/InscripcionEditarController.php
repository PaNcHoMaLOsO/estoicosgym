<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Inscripcion;
use App\Models\MotivoDescuento;
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
        ]);
    }

    public function update(Request $request, Inscripcion $inscripcion)
    {
        $datos = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_inicio',
            'precio_base' => 'required|integer|min:0|max:99999999',
            'descuento_aplicado' => 'nullable|integer|min:0|max:99999999',
            'id_motivo_descuento' => 'nullable|exists:motivos_descuento,id',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'fecha_vencimiento.after' => 'Una membresía tiene que vencer después de empezar.',
            'precio_base.required' => 'Indica cuánto vale.',
        ]);

        $descuento = (int) ($datos['descuento_aplicado'] ?? 0);

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

        DB::transaction(function () use ($inscripcion, $datos, $descuento, $final) {
            $inscripcion->update($datos + [
                'descuento_aplicado' => $descuento,
                'precio_final' => $final,
            ]);

            // Cambiar el precio mueve el saldo de todos sus pagos.
            $inscripcion->recalcularSusPagos();
        });

        return redirect()
            ->route('panel.inscripciones.show', $inscripcion->uuid)
            ->with('success', 'Membresía corregida.');
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

        $inscripcion->delete();

        return redirect()
            ->route('panel.clientes.show', $socio?->uuid)
            ->with('success', 'Membresía borrada. Está en la papelera por si hay que recuperarla.');
    }
}
