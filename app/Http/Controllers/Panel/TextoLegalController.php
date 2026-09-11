<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\TextoLegal;
use App\Support\TextosLegales;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * El contrato, los términos y condiciones y la política de privacidad, en
 * Configuración.
 *
 * El parámetro se llama {texto} y no {tipo}: {tipo} ya lo usa la página web
 * para sus servicios, fotos y preguntas.
 */
class TextoLegalController extends Controller
{
    public function show(string $texto)
    {
        $vigente = TextosLegales::vigente($texto);

        return Inertia::render('Configuracion/TextosLegales', [
            'tipo' => $texto,
            'tipos' => collect(TextosLegales::TIPOS)
                ->map(fn (array $t, string $clave) => ['clave' => $clave, 'titulo' => $t['titulo']])
                ->values(),
            'titulo' => TextosLegales::TIPOS[$texto]['titulo'],
            'descripcion' => TextosLegales::TIPOS[$texto]['descripcion'],
            'texto' => [
                'version' => $vigente->version,
                'contenido' => $vigente->contenido,
                'guardado' => $vigente->updated_at?->format('d/m/Y H:i'),
                'por' => $vigente->usuario?->name,
                'base' => TextosLegales::esElTextoBase($vigente),
                'firmas' => TextosLegales::firmas($vigente),
            ],
            'variables' => TextosLegales::variables($texto),
            'publica' => match ($texto) {
                'terminos' => route('landing.terminos'),
                'privacidad' => route('landing.privacidad'),
                default => null,
            },
            // Las versiones anteriores: son lo que firmó cada uno.
            'historial' => TextoLegal::where('tipo', $texto)
                ->with('usuario:id,name')
                ->orderByDesc('version')
                ->limit(20)
                ->get()
                ->map(fn (TextoLegal $t) => [
                    'version' => $t->version,
                    'guardado' => $t->updated_at?->format('d/m/Y H:i'),
                    'por' => $t->usuario?->name,
                    'firmas' => TextosLegales::firmas($t),
                ]),
        ]);
    }

    public function update(Request $request, string $texto)
    {
        $datos = $request->validate([
            'contenido' => ['required', 'string', 'max:100000'],
        ], [
            'contenido.required' => 'El texto no puede quedar vacío.',
        ]);

        // Una {variable} mal escrita saldría con las llaves puestas en el
        // contrato de un socio. El sitio para verlo es este.
        $sueltas = TextosLegales::variablesDesconocidas($texto, $datos['contenido']);

        if ($sueltas !== []) {
            throw ValidationException::withMessages([
                'contenido' => sprintf(
                    'No se conocen %s. Usa solo las de la lista, o saldrán con las llaves puestas.',
                    '{' . implode('}, {', $sueltas) . '}'
                ),
            ]);
        }

        [$guardado, $como] = TextosLegales::guardar($texto, $datos['contenido'], $request->user()->id);

        return back()->with('success', match ($como) {
            'nueva' => "Guardado como versión {$guardado->version}. La anterior ya la habían firmado y queda tal cual: lo que firmó cada uno no cambia.",
            'misma' => "Guardado. Sigue siendo la versión {$guardado->version}, porque nadie la había firmado todavía.",
            default => 'No había cambios. Queda marcado como revisado.',
        });
    }

    /** Cómo se ve, con datos de muestra, antes de guardarlo. */
    public function vistaPrevia(Request $request, string $texto)
    {
        $contenido = (string) $request->input('contenido', '');
        $variables = $texto === 'contrato'
            ? TextosLegales::datosDelGimnasio() + TextosLegales::ejemploDelContrato()
            : TextosLegales::datosDelGimnasio();

        return response()->json([
            'html' => TextosLegales::html($contenido, $variables),
            'desconocidas' => TextosLegales::variablesDesconocidas($texto, $contenido),
        ]);
    }
}
