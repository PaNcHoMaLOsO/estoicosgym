<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Carbon;

/**
 * El último que se ingresó: el socio, la membresía, el pago.
 *
 * Siempre a la vista arriba de cada lista: después de registrar a alguien
 * se mira que haya quedado, y al empezar el turno se ve qué fue lo último que
 * se hizo en el mesón.
 */
class UltimoIngresado
{
    public static function socio(): ?array
    {
        $c = Cliente::whereNull('datos_borrados_en')->latest('created_at')->latest('id')->first();

        return $c ? [
            'que' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
            'detalle' => $c->run_pasaporte,
            'href' => "/panel/clientes/{$c->uuid}",
            ...self::cuando($c->created_at),
        ] : null;
    }

    public static function membresia(): ?array
    {
        $i = Inscripcion::with(['cliente', 'membresia'])->latest('created_at')->latest('id')->first();

        return $i ? [
            'que' => $i->cliente ? trim("{$i->cliente->nombres} {$i->cliente->apellido_paterno}") : 'Socio eliminado',
            'detalle' => $i->membresia?->nombre,
            'href' => "/panel/inscripciones/{$i->uuid}",
            ...self::cuando($i->created_at),
        ] : null;
    }

    public static function pago(): ?array
    {
        $p = Pago::with(['cliente', 'metodoPago'])->latest('created_at')->latest('id')->first();

        return $p ? [
            'que' => $p->cliente ? trim("{$p->cliente->nombres} {$p->cliente->apellido_paterno}") : 'Socio eliminado',
            'detalle' => '$' . number_format((int) $p->monto_abonado, 0, ',', '.') . ($p->metodoPago ? " · {$p->metodoPago->nombre}" : ''),
            'href' => "/panel/pagos/{$p->uuid}",
            'monto' => (int) $p->monto_abonado,
            ...self::cuando($p->created_at),
        ] : null;
    }

    /** «hace 5 minutos» y la fecha exacta al pasar el mouse. */
    private static function cuando(?Carbon $fecha): array
    {
        return [
            'hace' => $fecha?->diffForHumans(),
            'cuando' => $fecha?->format('d/m/Y H:i'),
        ];
    }
}
