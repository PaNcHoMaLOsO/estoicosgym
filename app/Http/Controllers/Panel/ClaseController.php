<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Clase;
use App\Support\FotoLiviana;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Las clases del gimnasio (judo, lucha olímpica…), para la página «Clases»
 * de la web. Viven en Configuración → Página web.
 *
 * Se trabaja como los contenidos de la web: una lista con flechas para
 * ordenar, un formulario encima y ocultar sin borrar.
 */
class ClaseController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracion/Clases', [
            'clases' => Clase::orderBy('orden')->orderBy('id')->get()->map(fn (Clase $c) => [
                'uuid' => $c->uuid,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'profesor' => $c->profesor,
                'para_quien' => $c->para_quien,
                'precio_mensual' => $c->precio_mensual,
                'imagen_url' => $c->urlDeImagen(),
                'horario' => $c->horarioOrdenado(),
                'horario_texto' => $c->horarioEnUnaLinea(),
                'color' => $c->color,
                'activo' => (bool) $c->activo,
            ]),
            'dias' => collect(Clase::DIAS)->map(fn (string $nombre, string $valor) => ['valor' => $valor, 'etiqueta' => $nombre])->values(),
            'colores' => collect(Clase::COLORES)->map(fn (array $c, string $valor) => ['valor' => $valor] + $c)->values(),
            'ver' => route('landing.clases'),
        ]);
    }

    public function store(Request $request)
    {
        // Lo nueva va al final, como en los contenidos de la web: el orden se
        // cambia con las flechas, no escribiendo números.
        $clase = Clase::create($this->validar($request, true) + [
            'orden' => (int) Clase::max('orden') + 1,
        ]);
        $this->ponerImagen($clase, $request);

        return back()->with('success', $clase->activo ? 'Clase guardada. Ya sale en la web.' : 'Clase guardada, oculta.');
    }

    public function update(Request $request, Clase $clase)
    {
        $clase->update($this->validar($request, false));
        $this->ponerImagen($clase, $request);

        return back()->with('success', 'Cambios guardados.');
    }

    public function alternar(Clase $clase)
    {
        $clase->update(['activo' => ! $clase->activo]);

        return back()->with('success', $clase->activo ? "«{$clase->nombre}» vuelve a la web." : "«{$clase->nombre}» ya no sale en la web.");
    }

    /** Sube o baja un puesto, igual que los contenidos de la web. */
    public function mover(Request $request, Clase $clase)
    {
        $this->renumerar();
        $clase->refresh();

        $arriba = $request->input('hacia') !== 'abajo';
        $vecina = Clase::query()
            ->when(
                $arriba,
                fn ($q) => $q->where('orden', '<', $clase->orden)->orderByDesc('orden'),
                fn ($q) => $q->where('orden', '>', $clase->orden)->orderBy('orden')
            )
            ->first();

        if ($vecina) {
            $puesto = $clase->orden;
            $clase->update(['orden' => $vecina->orden]);
            $vecina->update(['orden' => $puesto]);
        }

        return back();
    }

    /** Borrarla del todo, con su foto: una foto sin clase no la mira nadie. */
    public function destroy(Clase $clase)
    {
        if ($clase->imagen) {
            Storage::disk('public')->delete($clase->imagen);
        }

        $clase->delete();
        $this->renumerar();

        return back()->with('success', 'Clase eliminada.');
    }

    /** Deja los puestos en 1, 2, 3… sin huecos ni repetidos. */
    private function renumerar(): void
    {
        Clase::orderBy('orden')->orderBy('id')->get()->each(function (Clase $clase, int $i) {
            if ($clase->orden !== $i + 1) {
                $clase->update(['orden' => $i + 1]);
            }
        });
    }

    /** @return array<string,mixed> */
    private function validar(Request $request, bool $creando): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'profesor' => ['nullable', 'string', 'max:100'],
            'para_quien' => ['nullable', 'string', 'max:100'],
            'precio_mensual' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'color' => ['required', Rule::in(array_keys(Clase::COLORES))],
            'activo' => 'boolean',
            // Con foto de teléfono: hasta 8 MB, que igual se achica. Sin SVG,
            // que puede llevar código.
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'horario' => ['required', 'array', 'min:1', 'max:21'],
            'horario.*.dia' => ['required', Rule::in(array_keys(Clase::DIAS))],
            'horario.*.desde' => ['required', 'date_format:H:i'],
            // «after» compara las dos horas del mismo bloque: 20:30 a 19:00 no es un horario.
            'horario.*.hasta' => ['required', 'date_format:H:i', 'after:horario.*.desde'],
        ], [
            'nombre.required' => 'Escribe el nombre de la clase.',
            'color.required' => 'Elige un color.',
            'color.in' => 'Elige uno de los colores.',
            'precio_mensual.integer' => 'El precio va en pesos, sin decimales.',
            'imagen.image' => 'Ese archivo no es una imagen.',
            'imagen.mimes' => 'La foto tiene que ser JPG, PNG o WEBP.',
            'imagen.max' => 'La foto no puede pesar más de 8 MB.',
            'horario.required' => 'Agrega al menos un día con su horario.',
            'horario.min' => 'Agrega al menos un día con su horario.',
            'horario.*.dia.required' => 'Elige el día.',
            'horario.*.dia.in' => 'Elige un día de la lista.',
            'horario.*.desde.required' => 'Falta la hora de inicio.',
            'horario.*.desde.date_format' => 'La hora va como 19:00.',
            'horario.*.hasta.required' => 'Falta la hora de término.',
            'horario.*.hasta.date_format' => 'La hora va como 20:30.',
            'horario.*.hasta.after' => 'La hora de término tiene que ser después de la de inicio.',
        ]);

        $limpio = fn (?string $texto) => ($texto = trim((string) $texto)) === '' ? null : $texto;

        return [
            'nombre' => trim($datos['nombre']),
            'descripcion' => $limpio($datos['descripcion'] ?? null),
            'profesor' => $limpio($datos['profesor'] ?? null),
            'para_quien' => $limpio($datos['para_quien'] ?? null),
            // Vacío o 0 es «sin precio»: la web dice «Consulta el valor».
            'precio_mensual' => ! empty($datos['precio_mensual']) ? (int) $datos['precio_mensual'] : null,
            'color' => $datos['color'],
            'activo' => (bool) ($datos['activo'] ?? true),
            'horario' => collect($datos['horario'])
                ->map(fn (array $b) => ['dia' => $b['dia'], 'desde' => $b['desde'], 'hasta' => $b['hasta']])
                ->values()
                ->all(),
        ];
    }

    /**
     * La foto va aparte: editar el nombre sin volver a elegirla no la borra.
     * La anterior se borra solo al reemplazarla.
     */
    private function ponerImagen(Clase $clase, Request $request): void
    {
        if (! $request->hasFile('imagen')) {
            return;
        }

        $anterior = $clase->imagen;
        $clase->update(['imagen' => $this->guardarFoto($request->file('imagen'))]);

        if ($anterior && $anterior !== $clase->imagen) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /**
     * Achicada, derecha y en WebP (ver App\Support\FotoLiviana), como las
     * fotos de la galería. Sale en una tarjeta: 1.200 de lado sobran.
     */
    private function guardarFoto(UploadedFile $archivo): string
    {
        $liviana = FotoLiviana::desde((string) file_get_contents($archivo->getRealPath()), 1200, 80, $archivo->getRealPath());

        if (! $liviana) {
            return $archivo->store('web/clases', 'public');
        }

        $ruta = 'web/clases/' . Str::random(32) . '.' . $liviana['extension'];
        Storage::disk('public')->put($ruta, $liviana['bytes']);

        return $ruta;
    }
}
