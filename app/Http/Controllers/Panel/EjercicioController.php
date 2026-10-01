<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\RutinaEjercicio;
use App\Support\FotoLiviana;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * El catálogo de ejercicios de la sala: cada máquina o movimiento, una vez,
 * con su indicación corta, su foto y los músculos que trabaja. Las rutinas lo
 * usan; corregir una indicación aquí la corrige en todas.
 */
class EjercicioController extends Controller
{
    /** Un GIF más liviano que esto se guarda tal cual, para que siga moviéndose. */
    private const GIF_TAL_CUAL = 2 * 1024 * 1024;

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
                'imagen_url' => $e->urlDeImagen(),
                'musculo_principal' => $e->grupos()['principal'] ?? '',
                'musculos_secundarios' => $e->grupos()['secundarios'],
                'usos' => (int) ($usos[$e->id] ?? 0),
            ]),
            'zonas' => Ejercicio::ZONAS,
            'musculos' => Ejercicio::MUSCULOS,
            'equipos' => Ejercicio::EQUIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $ejercicio = Ejercicio::create($this->validar($request) + ['orden' => (int) Ejercicio::max('orden') + 1]);
        $this->guardarImagen($request, $ejercicio);

        return back()->with('success', "«{$ejercicio->nombre}» agregado.");
    }

    public function update(Request $request, Ejercicio $ejercicio)
    {
        $ejercicio->update($this->validar($request, $ejercicio));
        $this->guardarImagen($request, $ejercicio);

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
        $musculos = array_keys(Ejercicio::MUSCULOS);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('ejercicios', 'nombre')->ignore($actual?->id)],
            'zona' => ['required', Rule::in(array_keys(Ejercicio::ZONAS))],
            'equipo' => ['required', Rule::in(array_keys(Ejercicio::EQUIPOS))],
            'indicacion' => 'nullable|string|max:300',
            'activo' => 'boolean',
            // El mapa muscular que se ve mientras no haya foto.
            'musculo_principal' => ['nullable', Rule::in($musculos)],
            'musculos_secundarios' => 'nullable|array',
            'musculos_secundarios.*' => [Rule::in($musculos)],
            // Una foto de la máquina o un GIF corto del movimiento.
            'imagen' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'quitar_imagen' => 'boolean',
        ], [
            'nombre.unique' => 'Ya hay un ejercicio con ese nombre.',
            'imagen.mimes' => 'La imagen tiene que ser JPG, PNG, WEBP o GIF.',
            'imagen.max' => 'La imagen no puede pesar más de 4 MB.',
        ]);

        $principal = filled($datos['musculo_principal'] ?? null) ? $datos['musculo_principal'] : null;
        $secundarios = array_values(array_diff(array_intersect($musculos, $datos['musculos_secundarios'] ?? []), [$principal]));

        return [
            'nombre' => trim($datos['nombre']),
            'zona' => $datos['zona'],
            'equipo' => $datos['equipo'],
            'indicacion' => filled($datos['indicacion'] ?? null) ? trim($datos['indicacion']) : null,
            'activo' => (bool) ($datos['activo'] ?? true),
            'musculos' => $principal || $secundarios ? ['principal' => $principal, 'secundarios' => $secundarios] : null,
        ];
    }

    /**
     * La imagen solo se toca si llega un archivo o si se pide quitarla: editar
     * el nombre no puede dejarla vacía. La anterior se borra del disco.
     */
    private function guardarImagen(Request $request, Ejercicio $ejercicio): void
    {
        $anterior = $ejercicio->imagen;

        if ($request->hasFile('imagen')) {
            $ejercicio->update(['imagen' => $this->liviana($request->file('imagen'))]);
        } elseif ($request->boolean('quitar_imagen')) {
            $ejercicio->update(['imagen' => null]);
        } else {
            return;
        }

        if ($anterior && $anterior !== $ejercicio->imagen) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /**
     * Las fotos se achican y pasan a WebP (App\Support\FotoLiviana): en la
     * rutina salen chicas, al lado del nombre. Un GIF liviano va tal cual,
     * porque achicarlo lo deja quieto; uno pesado se achica como foto.
     */
    private function liviana(UploadedFile $archivo): string
    {
        $esGif = strtolower($archivo->getClientOriginalExtension()) === 'gif' || $archivo->getMimeType() === 'image/gif';

        if ($esGif && $archivo->getSize() < self::GIF_TAL_CUAL) {
            return $archivo->store('ejercicios', 'public');
        }

        $liviana = FotoLiviana::desde((string) file_get_contents($archivo->getRealPath()), 900, 80, $archivo->getRealPath());

        if (! $liviana) {
            return $archivo->store('ejercicios', 'public');
        }

        $ruta = 'ejercicios/' . Str::random(32) . '.' . $liviana['extension'];
        Storage::disk('public')->put($ruta, $liviana['bytes']);

        return $ruta;
    }
}
