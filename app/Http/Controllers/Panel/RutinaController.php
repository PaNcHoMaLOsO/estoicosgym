<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\Rutina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Las rutinas de la sala, editables desde el panel.
 *
 * Antes solo se cambiaban desde el código (rutinas:ejemplos): el entrenador no
 * podía corregir una serie ni sacar una máquina que no hay. Aquí se editan
 * enteras —días, ejercicios, series, la variante «si está ocupada»—, se
 * duplican para armar otra variante y se apagan sin borrarlas.
 */
class RutinaController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracion/Rutinas', [
            'rutinas' => Rutina::withCount('dias')
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Rutina $r) => [
                    'uuid' => $r->uuid,
                    'nombre' => $r->nombre,
                    'objetivo' => $r->objetivo,
                    'nivel' => Rutina::NIVELES[$r->nivel] ?? $r->nivel,
                    'dias' => $r->dias_por_semana,
                    'activa' => (bool) $r->activa,
                    'ver' => route('landing.rutina', ['objetivo' => $r->objetivo, 'nivel' => $r->nivel, 'dias' => $r->dias_por_semana]),
                ]),
            'objetivos' => Rutina::OBJETIVOS,
            'ejercicios' => Ejercicio::count(),
        ]);
    }

    public function create()
    {
        return $this->editor(null);
    }

    public function edit(Rutina $rutina)
    {
        return $this->editor($rutina->load('dias.ejercicios'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $rutina = DB::transaction(fn () => $this->guardar(new Rutina(['orden' => (int) Rutina::max('orden') + 1]), $datos));

        return redirect()->route('panel.rutinas.edit', $rutina->uuid)->with('success', "«{$rutina->nombre}» creada.");
    }

    public function update(Request $request, Rutina $rutina)
    {
        $datos = $this->validar($request);
        DB::transaction(fn () => $this->guardar($rutina, $datos));

        return back()->with('success', "«{$rutina->nombre}» guardada.");
    }

    /** Una copia para armar otra variante: con más días, otro nivel… */
    public function duplicar(Rutina $rutina)
    {
        $copia = DB::transaction(function () use ($rutina) {
            $nueva = $rutina->replicate(['uuid']);
            $nueva->nombre = mb_substr($rutina->nombre . ' (copia)', 0, 80);
            $nueva->activa = false;
            $nueva->orden = (int) Rutina::max('orden') + 1;
            $nueva->save();

            foreach ($rutina->dias()->with('ejercicios')->get() as $dia) {
                $nuevoDia = $nueva->dias()->create($dia->only(['numero', 'titulo', 'foco']));

                foreach ($dia->ejercicios as $linea) {
                    $nuevoDia->ejercicios()->create($linea->only(['id_ejercicio', 'id_alternativa', 'series', 'repeticiones', 'descanso_seg', 'nota', 'orden']));
                }
            }

            return $nueva;
        });

        return redirect()->route('panel.rutinas.edit', $copia->uuid)
            ->with('success', 'Copia creada, apagada: cámbiale lo que haga falta y préndela.');
    }

    public function alternar(Rutina $rutina)
    {
        $rutina->update(['activa' => ! $rutina->activa]);

        return back()->with('success', $rutina->activa ? "«{$rutina->nombre}» sale en la web." : "«{$rutina->nombre}» ya no sale en la web.");
    }

    public function destroy(Rutina $rutina)
    {
        $rutina->delete();

        return redirect()->route('panel.rutinas.index')->with('success', "«{$rutina->nombre}» eliminada.");
    }

    private function editor(?Rutina $rutina)
    {
        return Inertia::render('Configuracion/RutinaEditar', [
            'rutina' => $rutina ? [
                'uuid' => $rutina->uuid,
                'nombre' => $rutina->nombre,
                'objetivo' => $rutina->objetivo,
                'nivel' => $rutina->nivel,
                'descripcion' => $rutina->descripcion ?? '',
                'activa' => (bool) $rutina->activa,
                'ver' => route('landing.rutina', ['objetivo' => $rutina->objetivo, 'nivel' => $rutina->nivel, 'dias' => $rutina->dias_por_semana]),
                'dias' => $rutina->dias->map(fn ($d) => [
                    'titulo' => $d->titulo,
                    'foco' => $d->foco ?? '',
                    'ejercicios' => $d->ejercicios->map(fn ($l) => [
                        'id_ejercicio' => $l->id_ejercicio,
                        'id_alternativa' => $l->id_alternativa ?? '',
                        'series' => $l->series,
                        'repeticiones' => $l->repeticiones,
                        'descanso_seg' => $l->descanso_seg,
                        'nota' => $l->nota ?? '',
                    ])->values(),
                ])->values(),
            ] : null,
            'objetivos' => Rutina::OBJETIVOS,
            'niveles' => Rutina::NIVELES,
            'zonas' => Ejercicio::ZONAS,
            'ejercicios' => Ejercicio::where('activo', true)
                ->orderBy('zona')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'zona', 'equipo']),
        ]);
    }

    /** @return array<string,mixed> */
    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:80',
            'objetivo' => ['required', Rule::in(array_keys(Rutina::OBJETIVOS))],
            'nivel' => ['required', Rule::in(array_keys(Rutina::NIVELES))],
            'descripcion' => 'nullable|string|max:300',
            'activa' => 'boolean',
            'dias' => 'required|array|min:1|max:7',
            'dias.*.titulo' => 'required|string|max:60',
            'dias.*.foco' => 'nullable|string|max:120',
            'dias.*.ejercicios' => 'required|array|min:1|max:15',
            'dias.*.ejercicios.*.id_ejercicio' => 'required|exists:ejercicios,id',
            'dias.*.ejercicios.*.id_alternativa' => 'nullable|exists:ejercicios,id',
            'dias.*.ejercicios.*.series' => 'required|integer|min:1|max:10',
            'dias.*.ejercicios.*.repeticiones' => 'required|string|max:40',
            'dias.*.ejercicios.*.descanso_seg' => 'required|integer|min:0|max:600',
            'dias.*.ejercicios.*.nota' => 'nullable|string|max:200',
        ], [
            'dias.required' => 'La rutina necesita al menos un día.',
            'dias.*.titulo.required' => 'Cada día necesita un título (por ejemplo «Tren superior»).',
            'dias.*.ejercicios.required' => 'Cada día necesita al menos un ejercicio.',
            'dias.*.ejercicios.*.id_ejercicio.required' => 'Falta elegir un ejercicio.',
            'dias.*.ejercicios.*.repeticiones.required' => 'Faltan las repeticiones de un ejercicio.',
        ]);
    }

    /**
     * Guarda la rutina y REHACE sus días: se editan todos juntos en una
     * pantalla, y rehacerlos es más simple y más seguro que casar cada línea
     * con la que había.
     */
    private function guardar(Rutina $rutina, array $datos): Rutina
    {
        $rutina->fill([
            'nombre' => trim($datos['nombre']),
            'objetivo' => $datos['objetivo'],
            'nivel' => $datos['nivel'],
            'descripcion' => filled($datos['descripcion'] ?? null) ? trim($datos['descripcion']) : null,
            'activa' => (bool) ($datos['activa'] ?? true),
            // Los días de la semana son los días que tiene: no se escribe aparte.
            'dias_por_semana' => count($datos['dias']),
        ])->save();

        $rutina->dias()->delete();

        foreach (array_values($datos['dias']) as $n => $dia) {
            $nuevo = $rutina->dias()->create([
                'numero' => $n + 1,
                'titulo' => trim($dia['titulo']),
                'foco' => filled($dia['foco'] ?? null) ? trim($dia['foco']) : null,
            ]);

            foreach (array_values($dia['ejercicios']) as $j => $linea) {
                $nuevo->ejercicios()->create([
                    'id_ejercicio' => $linea['id_ejercicio'],
                    'id_alternativa' => ($linea['id_alternativa'] ?? null) ?: null,
                    'series' => $linea['series'],
                    'repeticiones' => trim($linea['repeticiones']),
                    'descanso_seg' => $linea['descanso_seg'],
                    'nota' => filled($linea['nota'] ?? null) ? trim($linea['nota']) : null,
                    'orden' => $j + 1,
                ]);
            }
        }

        return $rutina->refresh();
    }
}
