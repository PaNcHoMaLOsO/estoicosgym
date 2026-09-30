<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ValidatesFormToken;
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
                // Un pago repartido entre dos medios se corrige con sus dos
                // partes a la vista: si no, cambiar el monto dejaba el reparto
                // viejo guardado y la caja sacaba un medio en negativo.
                'repartido' => self::esRepartido($pago),
                'id_metodo_pago2' => $pago->id_metodo_pago2,
                'monto_metodo1' => self::esRepartido($pago) ? (int) $pago->monto_metodo1 : null,
                'monto_metodo2' => self::esRepartido($pago)
                    ? (int) $pago->monto_abonado - (int) $pago->monto_metodo1
                    : null,
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

        $datos += $this->reparto($request, $pago, (int) $datos['monto_abonado']);

        if (! $this->validateFormToken($request, 'pago_editar_' . $pago->id)) {
            return back()->with('error', 'Ese cambio ya se guardó.');
        }

        DB::transaction(function () use ($pago, $datos) {
            $pago->update($datos);

            $pago->inscripcion?->recalcularSusPagos();
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

            $inscripcion?->recalcularSusPagos();
        });

        return redirect()
            ->route('panel.pagos.index')
            ->with('success', 'Pago anulado. Está en la papelera por si hay que recuperarlo.');
    }

    /**
     * Un pago mixto de verdad: el dinero entró por dos medios y se guardó
     * cuánto por cada uno. Las partes de un mixto hecho al inscribir se
     * guardan como pagos sueltos de un solo medio —dicen «mixto» pero no
     * tienen segundo medio— y se corrigen como cualquier otro.
     */
    public static function esRepartido(Pago $pago): bool
    {
        return $pago->id_metodo_pago2 !== null && $pago->monto_metodo1 !== null;
    }

    /**
     * Qué se guarda del segundo medio al corregir.
     *
     * EL REPARTO TIENE QUE SEGUIR CUADRANDO CON EL MONTO. Antes solo se
     * guardaba el monto: un mixto de $20.000 en efectivo + $10.000 por
     * transferencia corregido a $3.000 seguía diciendo «$20.000 en efectivo»,
     * y la caja sacaba la transferencia como $3.000 − $20.000 = −$17.000.
     *
     * Así que un mixto se corrige con sus dos montos, que tienen que sumar el
     * total, o se convierte en un pago de un solo medio a propósito
     * («como_simple»), nunca por omisión.
     *
     * @return array<string,mixed>
     */
    private function reparto(Request $request, Pago $pago, int $monto): array
    {
        if (! self::esRepartido($pago)) {
            return [];
        }

        if ($request->boolean('como_simple')) {
            $precio = (int) ($pago->inscripcion?->precio_final ?? $pago->inscripcion?->precio_base ?? $pago->monto_total);

            return [
                'id_metodo_pago2' => null,
                'monto_metodo1' => null,
                'monto_metodo2' => null,
                'tipo_pago' => $monto >= $precio ? 'completo' : 'parcial',
            ];
        }

        $partes = $request->validate([
            'id_metodo_pago2' => 'required|exists:metodos_pago,id|different:id_metodo_pago',
            'monto_metodo1' => 'required|integer|min:1',
            'monto_metodo2' => 'required|integer|min:1',
        ], [
            'id_metodo_pago2.required' => 'Indica el segundo medio, o conviértelo en pago de un solo medio.',
            'id_metodo_pago2.different' => 'Los dos medios tienen que ser distintos: dos veces el mismo es un pago de un solo medio.',
            'monto_metodo1.required' => 'Indica cuánto entró por cada medio.',
            'monto_metodo2.required' => 'Indica cuánto entró por cada medio.',
            'monto_metodo1.min' => 'Cada parte tiene que ser mayor que cero.',
            'monto_metodo2.min' => 'Cada parte tiene que ser mayor que cero.',
        ]);

        if ((int) $partes['monto_metodo1'] + (int) $partes['monto_metodo2'] !== $monto) {
            throw ValidationException::withMessages([
                'monto_metodo1' => sprintf(
                    'Las dos partes suman $%s y el pago es de $%s: tienen que cuadrar.',
                    number_format((int) $partes['monto_metodo1'] + (int) $partes['monto_metodo2'], 0, ',', '.'),
                    number_format($monto, 0, ',', '.'),
                ),
            ]);
        }

        return [
            'id_metodo_pago2' => (int) $partes['id_metodo_pago2'],
            'monto_metodo1' => (int) $partes['monto_metodo1'],
            'monto_metodo2' => (int) $partes['monto_metodo2'],
        ];
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
}
