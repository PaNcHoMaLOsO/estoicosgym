<?php

namespace App\Http\Controllers\Panel;

use App\Enums\EstadosCodigo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Corregir o anular un pago ya registrado.
 *
 * Es para el error de tecleo: 40.000 donde iba 4.000, la tarjeta donde iba el
 * efectivo, la fecha de ayer donde iba la de hoy. Sin esto no había forma de
 * arreglarlo desde el panel, y un monto mal escrito descuadra la caja del día y
 * el saldo del socio a la vez.
 *
 * AL TOCAR UN PAGO SE RECALCULAN TODOS LOS DE ESA MEMBRESÍA. El saldo que
 * guarda cada fila es el que quedaba después de ella, así que cambiar uno
 * invalida a los que vienen detrás: el de Blade actualizaba solo el editado y
 * los demás se quedaban diciendo lo de antes.
 */
class PagoEditarController extends Controller
{
    use ValidatesFormToken;

    public function edit(Pago $pago)
    {
        $pago->load(['inscripcion.cliente', 'inscripcion.membresia', 'metodoPago']);

        $inscripcion = $pago->inscripcion;
        $socio = $inscripcion?->cliente;

        return Inertia::render('Pagos/Editar', [
            'pago' => [
                'uuid' => $pago->uuid,
                'monto_abonado' => (int) $pago->monto_abonado,
                'fecha_pago' => $pago->fecha_pago?->format('Y-m-d'),
                'id_metodo_pago' => $pago->id_metodo_pago,
                'referencia_pago' => $pago->referencia_pago,
                'observaciones' => $pago->observaciones,
                'socio' => $socio ? trim("{$socio->nombres} {$socio->apellido_paterno}") : 'Socio eliminado',
                'plan' => $inscripcion?->membresia?->nombre,
                'total' => (int) ($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0),
                // Cuanto se puede poner como maximo: el precio menos lo que se
                // cobro en los OTROS pagos de esta membresia.
                'tope' => $this->loQueCabe($pago),
            ],
            'metodosPago' => MetodoPago::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'requiere_comprobante']),
            'formToken' => (string) Str::uuid(),
        ]);
    }

    public function update(Request $request, Pago $pago)
    {
        $datos = $request->validate([
            'monto_abonado' => 'required|integer|min:1|max:999999999',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'id_metodo_pago' => 'required|exists:metodos_pago,id',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'monto_abonado.required' => 'Indica cuánto se cobró.',
            'monto_abonado.min' => 'El monto tiene que ser mayor que cero.',
            'fecha_pago.before_or_equal' => 'Un pago no puede tener fecha futura.',
        ]);

        $tope = $this->loQueCabe($pago);

        if ($datos['monto_abonado'] > $tope) {
            throw ValidationException::withMessages([
                'monto_abonado' => sprintf(
                    'Como mucho $%s: es lo que queda del precio descontando los otros pagos de esta membresía.',
                    number_format($tope, 0, ',', '.')
                ),
            ]);
        }

        if (! $this->validateFormToken($request, 'pago_editar_' . $pago->id)) {
            return back()->with('error', 'Ese cambio ya se guardó.');
        }

        DB::transaction(function () use ($pago, $datos) {
            $pago->update($datos);

            $this->recalcularLaMembresia($pago->inscripcion);
        });

        return redirect()
            ->route('panel.pagos.show', $pago->uuid)
            ->with('success', 'Pago corregido.');
    }

    /**
     * Anula un pago que no debió registrarse.
     *
     * Va a la papelera, no al vacío: un cobro anulado por error se recupera
     * desde ahí, y mientras tanto la caja del día ya no lo cuenta.
     */
    public function eliminar(Pago $pago)
    {
        $inscripcion = $pago->inscripcion;

        DB::transaction(function () use ($pago, $inscripcion) {
            $pago->delete();

            $this->recalcularLaMembresia($inscripcion);
        });

        return redirect()
            ->route('panel.pagos.index')
            ->with('success', 'Pago anulado. Está en la papelera por si hay que recuperarlo.');
    }

    /**
     * El máximo que puede valer ESTE pago.
     *
     * El precio de la membresía menos lo que se cobró en los otros: entre todos
     * no pueden sumar más de lo que vale, o el socio saldría con saldo a favor
     * que nadie le va a devolver.
     */
    private function loQueCabe(Pago $pago): int
    {
        $inscripcion = $pago->inscripcion;

        if (! $inscripcion) {
            return (int) $pago->monto_total;
        }

        $precio = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0);

        $otros = (int) $inscripcion->pagos()
            ->where('id', '!=', $pago->id)
            ->sum('monto_abonado');

        return max(0, $precio - $otros);
    }

    /**
     * Vuelve a escribir el saldo y el estado de TODOS los pagos de la membresía.
     *
     * El saldo de cada fila es lo que quedaba por pagar después de ella, así que
     * cambiar o quitar un pago invalida a todos los que vengan detrás. El de
     * Blade actualizaba solo el editado, y los demás se quedaban diciendo un
     * saldo que ya no era: en la ficha del socio se veían tres pagos que no
     * cuadraban entre sí.
     */
    private function recalcularLaMembresia(?Inscripcion $inscripcion): void
    {
        if (! $inscripcion) {
            return;
        }

        $precio = (int) ($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0);

        $pagos = $inscripcion->pagos()->orderBy('fecha_pago')->orderBy('id')->get();
        $total = (int) $pagos->sum('monto_abonado');

        // El estado es de la membresía entera, no de cada pago suelto: o está
        // saldada o no lo está.
        $estado = match (true) {
            $total <= 0 => EstadosCodigo::PAGO_PENDIENTE,
            $total >= $precio => EstadosCodigo::PAGO_PAGADO,
            default => EstadosCodigo::PAGO_PARCIAL,
        };

        $restante = $precio;

        foreach ($pagos as $pago) {
            $restante -= (int) $pago->monto_abonado;

            $pago->update([
                'monto_total' => $precio,
                'monto_pendiente' => max(0, $restante),
                'id_estado' => $estado,
            ]);
        }
    }
}
