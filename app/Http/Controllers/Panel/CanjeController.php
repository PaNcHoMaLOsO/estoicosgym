<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\EntradaCanje;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Entradas por canje: el huésped del hotel que llega con su tarjeta.
 *
 * No paga y no es socio: se anota su nombre y su tarjeta, y listo. La cuenta
 * del mes sirve para saber cuánto se está usando cada canje.
 */
class CanjeController extends Controller
{
    /** Cuántas entradas se listan: las del día y las anteriores más recientes. */
    private const EN_LISTA = 50;

    public function index()
    {
        $hoy = Carbon::today();
        $mes = [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfDay()];
        $mesPasado = [$hoy->copy()->subMonthNoOverflow()->startOfMonth(), $hoy->copy()->subMonthNoOverflow()->endOfMonth()];

        $convenios = Convenio::where('canje', true)->where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Canje', [
            'convenios' => $convenios->map(fn (Convenio $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'hoy' => EntradaCanje::where('id_convenio', $c->id)->whereDate('created_at', $hoy)->count(),
                'mes' => EntradaCanje::where('id_convenio', $c->id)->whereBetween('created_at', $mes)->count(),
                'mes_pasado' => EntradaCanje::where('id_convenio', $c->id)->whereBetween('created_at', $mesPasado)->count(),
            ])->values(),
            'entradas' => EntradaCanje::with(['convenio:id,nombre', 'autor:id,name'])
                ->latest('created_at')
                ->latest('id')
                ->limit(self::EN_LISTA)
                ->get()
                ->map(fn (EntradaCanje $e) => [
                    'uuid' => (string) $e->uuid,
                    'nombre' => $e->nombre,
                    'tarjeta' => $e->tarjeta,
                    'convenio' => $e->convenio?->nombre,
                    'cuando' => $e->created_at->isToday()
                        ? 'Hoy ' . $e->created_at->format('H:i')
                        : $e->created_at->format('d-m-Y H:i'),
                    'es_de_hoy' => $e->created_at->isToday(),
                    'anoto' => $e->autor?->name,
                ]),
        ]);
    }

    /** Anota una entrada. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_convenio' => ['required', Rule::exists('convenios', 'id')->where('canje', true)->where('activo', true)],
            'nombre' => 'required|string|max:100',
            'tarjeta' => 'nullable|string|max:50',
        ], [
            'id_convenio.required' => 'Elige de dónde viene.',
            'id_convenio.exists' => 'Ese convenio no es de canje.',
            'nombre.required' => 'Anota su nombre.',
        ]);

        $entrada = EntradaCanje::create([
            'id_convenio' => $datos['id_convenio'],
            'nombre' => trim($datos['nombre']),
            'tarjeta' => trim((string) ($datos['tarjeta'] ?? '')) ?: null,
            'id_usuario' => $request->user()->id,
        ]);

        return back()->with('success', "Entrada de {$entrada->nombre} anotada.");
    }

    /**
     * Quita una entrada anotada por error.
     *
     * Solo las de HOY: la de ayer ya es parte de la cuenta del mes, y borrarla
     * después cambiaría una cifra que alguien pudo haber mirado.
     */
    public function anular(EntradaCanje $entrada)
    {
        if (! $entrada->created_at->isToday()) {
            return back()->with('error', 'Solo se pueden quitar las entradas de hoy.');
        }

        $entrada->delete();

        return back()->with('success', 'Entrada quitada.');
    }
}
