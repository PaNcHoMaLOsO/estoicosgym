<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Pagos del panel nuevo (Inertia + React).
 *
 * Convive con Admin\PagoController mientras dure la migracion.
 */
class PagoController extends Controller
{
    /** Codigos de la tabla `estados` para la categoria pago. */
    private const PAGADO = 201;
    private const PARCIAL = 202;

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');

        $pagos = Pago::query()
            ->with(['cliente', 'metodoPago'])
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($q) use ($busqueda) {
                    $q->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                        ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                        ->orWhere('run_pasaporte', 'like', "%{$busqueda}%");
                });
            })
            ->when(is_numeric($estado), fn ($q) => $q->where('id_estado', (int) $estado))
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Pago $pago) {
                $cliente = $pago->cliente;

                return [
                    'uuid' => $pago->uuid,
                    'socio' => $cliente
                        ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}")
                        : 'Socio eliminado',
                    'fecha' => $pago->fecha_pago?->format('d/m/Y'),
                    'metodo' => $pago->metodoPago?->nombre,
                    // La UI muestra «Abono»; en la base ese caso se guarda como
                    // 'parcial', que es el valor del enum de la tabla pagos.
                    'tipo' => $pago->tipo_pago === 'parcial' ? 'Abono' : ucfirst((string) $pago->tipo_pago),
                    'id_estado' => $pago->id_estado,
                    'total' => (int) $pago->monto_total,
                    'abonado' => (int) $pago->monto_abonado,
                    'pendiente' => (int) $pago->monto_pendiente,
                ];
            });

        $hoy = Carbon::today();

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'filtros' => ['buscar' => $busqueda, 'estado' => $estado],
            'resumen' => [
                'recaudado_hoy' => (int) Pago::whereDate('fecha_pago', $hoy)->sum('monto_abonado'),
                'recaudado_mes' => (int) Pago::whereYear('fecha_pago', $hoy->year)
                    ->whereMonth('fecha_pago', $hoy->month)
                    ->sum('monto_abonado'),
                // Lo que queda por cobrar es la cifra que mueve a actuar.
                'por_cobrar' => (int) Pago::where('id_estado', self::PARCIAL)->sum('monto_pendiente'),
                'completados' => Pago::where('id_estado', self::PAGADO)->count(),
            ],
        ]);
    }
}
