<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Ficha de un socio.
 *
 * Va aparte de Panel\ClienteController —que lista y da de alta— porque la ficha
 * no comparte nada con esos dos: no pagina ni valida, reune el historial de una
 * sola persona.
 */
class ClienteFichaController extends Controller
{
    private const ACTIVA = 100;

    public function __invoke(Cliente $cliente)
    {
        $cliente->load([
            'convenio:id,nombre,tipo,descuento_porcentaje,descuento_monto',
        ]);

        $inscripciones = $cliente->inscripciones()
            ->with('membresia:id,nombre')
            // El saldo de cada inscripcion en UNA consulta: pedirlo dentro del
            // bucle serian tantas consultas como membresias tenga el socio.
            ->withSum('pagos as abonado', 'monto_abonado')
            ->orderByDesc('fecha_vencimiento')
            ->get();

        $hoy = Carbon::today();

        return Inertia::render('Clientes/Ficha', [
            'cliente' => [
                'uuid' => $cliente->uuid,
                'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}"),
                'rut' => $cliente->run_pasaporte,
                'email' => $cliente->email,
                'celular' => $cliente->celular,
                'direccion' => $cliente->direccion,
                'nacimiento' => $cliente->fecha_nacimiento?->format('d/m/Y'),
                'edad' => $cliente->fecha_nacimiento
                    ? (int) $cliente->fecha_nacimiento->diffInYears($hoy)
                    : null,
                'contacto_emergencia' => $cliente->contacto_emergencia,
                'telefono_emergencia' => $cliente->telefono_emergencia,
                'observaciones' => $cliente->observaciones,
                'convenio' => $cliente->convenio?->nombre,
                'activo' => (bool) $cliente->activo,
                'desde' => $cliente->created_at?->format('d/m/Y'),
                // El apoderado solo aparece si el socio es menor: en un adulto
                // serian cuatro filas vacias ocupando la mitad de la ficha.
                'menor' => (bool) $cliente->es_menor_edad,
                'apoderado' => $cliente->es_menor_edad ? [
                    'nombre' => $cliente->apoderado_nombre,
                    'rut' => $cliente->apoderado_rut,
                    'email' => $cliente->apoderado_email,
                    'telefono' => $cliente->apoderado_telefono,
                    'parentesco' => $cliente->apoderado_parentesco,
                    'consentimiento' => (bool) $cliente->consentimiento_apoderado,
                ] : null,
            ],

            'inscripciones' => $inscripciones->map(function (Inscripcion $i) use ($hoy) {
                $total = (int) ($i->precio_final ?? $i->precio_base);
                $abonado = (int) ($i->abonado ?? 0);

                return [
                    'uuid' => $i->uuid,
                    'membresia' => $i->membresia?->nombre,
                    'id_estado' => $i->id_estado,
                    'inicio' => $i->fecha_inicio?->format('d/m/Y'),
                    'vence' => $i->fecha_vencimiento?->format('d/m/Y'),
                    'dias' => $i->fecha_vencimiento
                        ? (int) $hoy->diffInDays($i->fecha_vencimiento, false)
                        : null,
                    'total' => $total,
                    'abonado' => $abonado,
                    'pendiente' => max(0, $total - $abonado),
                    'vigente' => (int) $i->id_estado === self::ACTIVA,
                ];
            }),

            'pagos' => $cliente->pagos()
                ->with('metodoPago:id,nombre')
                ->orderByDesc('fecha_pago')
                ->limit(10)
                ->get()
                ->map(fn (Pago $p) => [
                    'uuid' => $p->uuid,
                    'fecha' => $p->fecha_pago?->format('d/m/Y'),
                    'metodo' => $p->metodoPago?->nombre,
                    'id_estado' => $p->id_estado,
                    'abonado' => (int) $p->monto_abonado,
                    'pendiente' => (int) $p->monto_pendiente,
                ]),

            'resumen' => [
                'inscripciones' => $inscripciones->count(),
                'pagado' => (int) $cliente->pagos()->ingresos()->sum('monto_abonado'),
                // Lo que debe hoy, sumando todas sus inscripciones vivas.
                'debe' => (int) $inscripciones
                    ->reject(fn (Inscripcion $i) => in_array((int) $i->id_estado, [103, 105, 106], true))
                    ->sum(fn (Inscripcion $i) => max(
                        0,
                        (int) ($i->precio_final ?? $i->precio_base) - (int) ($i->abonado ?? 0),
                    )),
            ],
        ]);
    }
}
