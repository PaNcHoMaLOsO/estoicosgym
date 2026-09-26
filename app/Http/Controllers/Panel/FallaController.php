<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Falla;
use App\Support\RegistroDeFallas;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * El registro de fallas del sistema: qué salió mal, dónde y cuántas veces.
 *
 * Solo para quien administra: los mensajes pueden llevar datos de socios y
 * rutas del código.
 */
class FallaController extends Controller
{
    public function index(Request $request)
    {
        $estado = in_array($request->query('estado'), ['abiertas', 'resueltas', 'todas'], true)
            ? $request->query('estado')
            : 'abiertas';
        $origen = in_array($request->query('origen'), ['servidor', 'navegador'], true) ? $request->query('origen') : null;

        $fallas = Falla::query()
            ->with('usuario:id,name')
            ->when($estado === 'abiertas', fn ($q) => $q->whereNull('resuelta_en'))
            ->when($estado === 'resueltas', fn ($q) => $q->whereNotNull('resuelta_en'))
            ->when($origen, fn ($q) => $q->where('origen', $origen))
            ->orderByDesc('ultima_vez')
            ->paginate(40)
            ->withQueryString()
            ->through(fn (Falla $f) => [
                'id' => $f->id,
                'origen' => $f->origen,
                'nivel' => $f->nivel,
                'tipo' => $f->tipo ? class_basename($f->tipo) : null,
                'tipo_completo' => $f->tipo,
                'mensaje' => $f->mensaje,
                'lugar' => $f->lugar,
                'metodo' => $f->metodo,
                'url' => $f->url,
                'usuario' => $f->usuario?->name,
                'traza' => $f->traza,
                'contexto' => $f->contexto,
                'veces' => $f->veces,
                'primera_vez' => $f->primera_vez?->format('d/m/Y H:i'),
                'ultima_vez' => $f->ultima_vez?->format('d/m/Y H:i'),
                'hace' => $f->ultima_vez?->diffForHumans(),
                'resuelta' => $f->resuelta_en !== null,
            ]);

        return Inertia::render('Configuracion/Fallas', [
            'fallas' => $fallas,
            'filtros' => ['estado' => $estado, 'origen' => $origen],
            'cuentas' => [
                'abiertas' => Falla::whereNull('resuelta_en')->count(),
                'hoy' => Falla::whereNull('resuelta_en')->where('ultima_vez', '>=', now()->startOfDay())->count(),
                'resueltas' => Falla::whereNotNull('resuelta_en')->count(),
            ],
        ]);
    }

    /** Se dio por arreglada. Si vuelve a pasar, se reabre sola. */
    public function resolver(Falla $falla)
    {
        $falla->update(['resuelta_en' => $falla->resuelta_en ? null : now()]);

        return back()->with('success', $falla->resuelta_en ? 'Marcada como resuelta.' : 'Vuelve a estar abierta.');
    }

    public function destroy(Falla $falla)
    {
        $falla->delete();

        return back()->with('success', 'Falla borrada del registro.');
    }

    /** Una pantalla del panel que se rompió en el navegador. */
    public function navegador(Request $request)
    {
        $datos = $request->validate([
            'mensaje' => 'required|string|max:2000',
            'tipo' => 'nullable|string|max:200',
            'archivo' => 'nullable|string|max:500',
            'linea' => 'nullable|integer',
            'traza' => 'nullable|string|max:8000',
            'pantalla' => 'nullable|string|max:500',
        ]);

        RegistroDeFallas::delNavegador($datos);

        return response()->noContent();
    }
}
