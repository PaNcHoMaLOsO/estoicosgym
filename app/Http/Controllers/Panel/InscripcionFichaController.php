<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Ficha de una inscripción.
 *
 * Responde tres preguntas, en este orden: cuánto le queda de vigencia, cuánto
 * debe y qué le ha pasado. Las acciones —pausar, renovar, cambiar de plan,
 * traspasar— siguen en Blade, y aquí se ofrecen solo las que de verdad se
 * pueden hacer ahora mismo: enseñar «Pausar» en una membresía que ya gastó sus
 * pausas es mandar al usuario contra un error.
 */
class InscripcionFichaController extends Controller
{
    /** Estados en los que la inscripción ya terminó su vida. */
    private const FINALIZADAS = [103, 105, 106];

    public function __invoke(Inscripcion $inscripcion)
    {
        $inscripcion->load([
            'cliente:id,uuid,nombres,apellido_paterno,apellido_materno,run_pasaporte,email,celular',
            'membresia:id,nombre,duracion_dias,max_pausas',
            'convenio:id,nombre',
            'motivoDescuento:id,nombre',
        ]);

        $pago = $inscripcion->obtenerEstadoPago();
        $hoy = Carbon::today();
        $cliente = $inscripcion->cliente;

        $finalizada = in_array((int) $inscripcion->id_estado, self::FINALIZADAS, true);

        return Inertia::render('Inscripciones/Ficha', [
            'inscripcion' => [
                'uuid' => $inscripcion->uuid,
                'id_estado' => $inscripcion->id_estado,
                'membresia' => $inscripcion->membresia?->nombre,
                'inicio' => $inscripcion->fecha_inicio?->format('d/m/Y'),
                'vence' => $inscripcion->fecha_vencimiento?->format('d/m/Y'),
                'dias' => $inscripcion->fecha_vencimiento
                    ? (int) $hoy->diffInDays($inscripcion->fecha_vencimiento, false)
                    : null,
                'convenio' => $inscripcion->convenio?->nombre,
                'motivo_descuento' => $inscripcion->motivoDescuento?->nombre,
                'descuento' => (int) $inscripcion->descuento_aplicado,
                'observaciones' => $inscripcion->observaciones,
            ],

            'socio' => $cliente ? [
                'uuid' => $cliente->uuid,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                'rut' => $cliente->run_pasaporte,
                'email' => $cliente->email,
                'celular' => $cliente->celular,
            ] : null,

            'pago' => [
                'total' => (int) $pago['monto_total'],
                'abonado' => (int) $pago['total_abonado'],
                'pendiente' => (int) $pago['pendiente'],
                'porcentaje' => (int) round($pago['porcentaje_pagado']),
                'estado' => $pago['estado'],
            ],

            'pausa' => [
                'pausada' => (bool) $inscripcion->pausada,
                'usadas' => (int) $inscripcion->pausas_realizadas,
                'permitidas' => (int) $inscripcion->max_pausas_permitidas,
                'disponibles' => max(0, (int) $inscripcion->max_pausas_permitidas - (int) $inscripcion->pausas_realizadas),
                'desde' => $inscripcion->fecha_pausa_inicio?->format('d/m/Y'),
                'hasta' => $inscripcion->fecha_pausa_fin?->format('d/m/Y'),
                'razon' => $inscripcion->razon_pausa,
            ],

            /*
             * Solo se ofrece lo que de verdad se puede hacer.
             *
             * Se pregunta al modelo, que es quien manda: si la pantalla lo
             * decidiera por su cuenta acabaria enseñando botones que el servidor
             * rechaza, y el usuario aprende a desconfiar de lo que ve.
             */
            'puede' => [
                'pausar' => ! $finalizada && $inscripcion->puedePausarse(),
                'reanudar' => (bool) $inscripcion->pausada,
                'cobrar' => ! $finalizada && $pago['pendiente'] > 0,
                'renovar' => ! $finalizada,
                'traspasar' => $inscripcion->puedeTraspasarse(true),
                // Borrar es para la membresia que no deberia existir —la
                // apuntada dos veces—, no para cancelar una real. Con dinero
                // cobrado no se ofrece: el pago se quedaria suelto, apuntando a
                // una membresia que ya no se lista. Primero se anula el pago.
                'borrar' => (int) $pago['total_abonado'] === 0,
            ],

            'pagos' => $inscripcion->pagos()
                ->with('metodoPago:id,nombre')
                ->orderByDesc('fecha_pago')
                ->get()
                ->map(fn (Pago $p) => [
                    'uuid' => $p->uuid,
                    'fecha' => $p->fecha_pago?->format('d/m/Y'),
                    'metodo' => $p->metodoPago?->nombre,
                    'tipo' => $p->tipo_pago === 'parcial' ? 'Abono' : ucfirst((string) $p->tipo_pago),
                    'id_estado' => $p->id_estado,
                    'abonado' => (int) $p->monto_abonado,
                ]),

            'movimientos' => HistorialCambio::query()
                ->where('inscripcion_id', $inscripcion->id)
                ->with('usuario:id,name')
                ->orderByDesc('fecha_cambio')
                ->limit(20)
                ->get()
                ->map(fn (HistorialCambio $c) => [
                    'id' => $c->id,
                    'que' => $c->tipo_cambio ? ucfirst(str_replace('_', ' ', $c->tipo_cambio)) : 'Cambio',
                    'cuando' => ($c->fecha_cambio ?? $c->created_at)?->format('d/m/Y H:i'),
                    'quien' => $c->usuario?->name,
                    'motivo' => $c->motivo,
                ]),
        ]);
    }
}
