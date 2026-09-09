<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Pantalla de entrada del panel nuevo.
 *
 * Ensena lo que se necesita saber al abrir la persiana, no todo lo que se puede
 * contar: cuantos socios estan al dia, que vence esta semana y cuanto se
 * recaudo hoy.
 */
class ResumenController extends Controller
{
    public function __invoke()
    {
        $hoy = Carbon::today();

        return Inertia::render('Resumen', [
            'cifras' => [
                'socios' => Cliente::where('activo', true)->count(),
                'activas' => Inscripcion::where('id_estado', 100)->count(),
                'pausadas' => Inscripcion::where('id_estado', 101)->count(),
                // Lo que exige actuar esta semana: es la unica cifra accionable
                // de las cuatro, por eso va la ultima y se pinta distinta.
                'vencen_semana' => Inscripcion::where('id_estado', 100)
                    ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(7)])
                    ->count(),
            ],
            'recaudado_hoy' => (int) Pago::whereDate('fecha_pago', $hoy)->sum('monto_abonado'),
        ]);
    }
}
