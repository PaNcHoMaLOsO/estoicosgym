<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\JuntarFichas;
use App\Support\FichasRepetidas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Las fichas que pueden ser de la misma persona, lado a lado.
 *
 * Se ven y se deciden aquí: «es la misma, juntar» o «son personas distintas».
 * Juntar lo hace solo quien puede eliminar socios —mueve pagos y manda una
 * ficha a la papelera—; mirarlo y descartar lo puede hacer recepción, que es
 * la que conoce a la gente del mesón.
 */
class DuplicadosController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Clientes/Duplicados', [
            'grupos' => FichasRepetidas::grupos(),
            // Desde la ficha de un socio se llega con su grupo marcado.
            'enfocar' => Str::isUuid((string) $request->query('socio')) ? $request->query('socio') : null,
            'puedeJuntar' => $request->user()->puede('clientes.eliminar'),
        ]);
    }

    public function juntar(Request $request, JuntarFichas $servicio)
    {
        $datos = $request->validate([
            'queda' => 'required|uuid',
            'salen' => 'required|array|min:1',
            'salen.*' => 'uuid',
        ]);

        $queda = Cliente::where('uuid', $datos['queda'])->firstOrFail();
        $salen = Cliente::whereIn('uuid', $datos['salen'])->where('id', '!=', $queda->id)->get();

        foreach ($salen as $sale) {
            $queda = $servicio->juntar($queda, $sale);
        }

        $cuantas = $salen->count();

        return back()->with('success', $cuantas === 1
            ? "Listo: quedó una sola ficha de {$queda->nombres} {$queda->apellido_paterno}. La otra está en la papelera."
            : "Listo: {$cuantas} fichas se juntaron en la de {$queda->nombres} {$queda->apellido_paterno}.");
    }

    public function distintos(Request $request)
    {
        $datos = $request->validate([
            'socios' => 'required|array|min:2',
            'socios.*' => 'uuid',
        ]);

        $ids = Cliente::whereIn('uuid', $datos['socios'])->pluck('id')->all();

        FichasRepetidas::sonDistintos($ids, $request->user()->id);

        return back()->with('success', 'Anotado: son personas distintas. No vuelven a salir.');
    }
}
