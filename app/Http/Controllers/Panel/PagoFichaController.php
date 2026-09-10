<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Inertia\Inertia;

/**
 * Ficha de un pago.
 *
 * Es el comprobante: qué se cobró, a quién, por qué vía y qué quedó debiendo.
 * Se abre sobre todo para resolver una duda concreta —«¿esto se pagó?»,
 * «¿con qué tarjeta?»—, así que lo primero que se ve es el monto y el estado.
 */
class PagoFichaController extends Controller
{
    public function __invoke(Pago $pago)
    {
        $pago->load([
            'cliente:id,uuid,nombres,apellido_paterno,apellido_materno,run_pasaporte,email,celular',
            'inscripcion:id,uuid,id_membresia,fecha_inicio,fecha_vencimiento,id_estado',
            'inscripcion.membresia:id,nombre',
            'metodoPago:id,nombre,requiere_comprobante',
            'metodoPago2:id,nombre',
        ]);

        $cliente = $pago->cliente;
        $inscripcion = $pago->inscripcion;
        $esMixto = $pago->tipo_pago === 'mixto';

        return Inertia::render('Pagos/Ficha', [
            'pago' => [
                'uuid' => $pago->uuid,
                'fecha' => $pago->fecha_pago?->format('d/m/Y'),
                'registrado' => $pago->created_at?->format('d/m/Y H:i'),
                'id_estado' => $pago->id_estado,
                // La UI dice «Abono»; en la base ese caso se guarda como
                // 'parcial', que es el valor del enum de la tabla.
                'tipo' => $pago->tipo_pago === 'parcial' ? 'Abono' : ucfirst((string) $pago->tipo_pago),
                'total' => (int) $pago->monto_total,
                'abonado' => (int) $pago->monto_abonado,
                'pendiente' => (int) $pago->monto_pendiente,
                'referencia' => $pago->referencia_pago,
                'observaciones' => $pago->observaciones,
                'cuotas' => (int) $pago->cantidad_cuotas,
                'periodo_inicio' => $pago->periodo_inicio?->format('d/m/Y'),
                'periodo_fin' => $pago->periodo_fin?->format('d/m/Y'),
            ],

            /*
             * En un pago mixto el dinero entró por DOS vías y hay que poder
             * cuadrar cada una con su caja. En el resto sobra la segunda fila,
             * así que se manda una lista y la pantalla no decide nada.
             */
            'metodos' => $esMixto
                ? array_values(array_filter([
                    $pago->metodoPago ? [
                        'nombre' => $pago->metodoPago->nombre,
                        'monto' => (int) $pago->monto_metodo1,
                    ] : null,
                    $pago->metodoPago2 ? [
                        'nombre' => $pago->metodoPago2->nombre,
                        'monto' => (int) $pago->monto_metodo2,
                    ] : null,
                ]))
                : ($pago->metodoPago ? [[
                    'nombre' => $pago->metodoPago->nombre,
                    'monto' => (int) $pago->monto_abonado,
                ]] : []),

            'requiere_comprobante' => (bool) $pago->metodoPago?->requiere_comprobante,

            'socio' => $cliente ? [
                'uuid' => $cliente->uuid,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                'rut' => $cliente->run_pasaporte,
                'email' => $cliente->email,
                'celular' => $cliente->celular,
            ] : null,

            'inscripcion' => $inscripcion ? [
                'uuid' => $inscripcion->uuid,
                'membresia' => $inscripcion->membresia?->nombre,
                'id_estado' => $inscripcion->id_estado,
                'inicio' => $inscripcion->fecha_inicio?->format('d/m/Y'),
                'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
            ] : null,

            // Los demás cobros de la misma membresía: es lo que se necesita para
            // entender por qué queda saldo, sin salir de la pantalla.
            'otrosPagos' => $inscripcion
                ? Pago::where('id_inscripcion', $inscripcion->id)
                    ->where('id', '!=', $pago->id)
                    ->with('metodoPago:id,nombre')
                    ->orderByDesc('fecha_pago')
                    ->get()
                    ->map(fn (Pago $p) => [
                        'uuid' => $p->uuid,
                        'fecha' => $p->fecha_pago?->format('d/m/Y'),
                        'metodo' => $p->metodoPago?->nombre,
                        'id_estado' => $p->id_estado,
                        'abonado' => (int) $p->monto_abonado,
                    ])
                : collect(),
        ]);
    }
}
