<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\RutinaEjercicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * El catálogo de ejercicios de la sala: cada máquina o movimiento, una vez,
 * con su indicación corta. Las rutinas lo usan; corregir una indicación aquí
 * la corrige en todas.
 */
class EjercicioController extends Controller
{
    public function index()
    {
        $usos = RutinaEjercicio::selectRaw('id_ejercicio, count(*) as veces')->groupBy('id_ejercicio')->pluck('veces', 'id_ejercicio');

        return Inertia::render('Configuracion/Ejercicios', [
            'ejercicios' => Ejercicio::orderBy('zona')->orderBy('nombre')->get()->map(fn (Ejercicio $e) => [
                'uuid' => $e->uuid,
                'nombre' => $e->nombre,
                'zona' => $e->zona,
                'equipo' => $e->equipo,
                'indicacion' => $e->indicacion ?? '',
                'activo' => (bool) $e->activo,
                'usos' => (int) ($usos[$e->id] ?? 0),
            ]),
            'zonas' => Ejercicio::ZONAS,
            'equipos' => Ejercicio::EQUIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $ejercicio = Ejercicio::create($this->validar($request) + ['orden' => (int) Ejercicio::max('orden') + 1]);

        return back()->with('success', "«{$ejercicio->nombre}» agregado.");
    }

    public function update(Request $request, Ejercicio $ejercicio)
    {
        $ejercicio->update($this->validar($request, $ejercicio));

        return back()->with('success', "«{$ejercicio->nombre}» guardado.");
    }

    /** Apagado no se ofrece al armar rutinas; las que ya lo usan siguen igual. */
    public function alternar(Ejercicio $ejercicio)
    {
        $ejercicio->update(['activo' => ! $ejercicio->activo]);

        return back();
    }

    /** @return array<string,mixed> */
    private function validar(Request $request, ?Ejercicio $actual = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('ejercicios', 'nombre')->ignore($actual?->id)],
            'zona' => ['required', Rule::in(array_keys(Ejercicio::ZONAS))],
            'equipo' => ['required', Rule::in(array_keys(Ejercicio::EQUIPOS))],
            'indicacion' => 'nullable|string|max:300',
            'activo' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un ejercicio con ese nombre.',
        ]);

        return [
            'nombre' => trim($datos['nombre']),
            'zona' => $datos['zona'],
            'equipo' => $datos['equipo'],
            'indicacion' => filled($datos['indicacion'] ?? null) ? trim($datos['indicacion']) : null,
            'activo' => (bool) ($datos['activo'] ?? true),
        ];
    }
}
