<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ContenidoWeb;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * La página web, manejada desde el panel.
 *
 * Los contenidos que se escriben aquí —servicios, fotos, preguntas y
 * testimonios—. Se ven dentro de Configuración, en «Página web», junto a lo
 * que vive en otra parte: especialistas, portada y horario.
 */
class ContenidoWebController extends Controller
{
    /** Los contenidos de un tipo. */
    public function show(string $tipo)
    {
        $filas = ContenidoWeb::where('tipo', $tipo)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn (ContenidoWeb $c) => [
                'uuid' => $c->uuid,
                'titulo' => $c->titulo,
                'texto' => $c->texto,
                'icono' => $c->icono,
                'imagen_url' => $c->urlDeImagen(),
                'con_permiso' => (bool) $c->con_permiso,
                'orden' => $c->orden,
                'activo' => (bool) $c->activo,
            ]);

        return Inertia::render('Web/Contenidos', [
            'tipo' => $tipo,
            'datos' => ContenidoWeb::TIPOS[$tipo],
            'filas' => $filas,
            'iconos' => collect(ContenidoWeb::ICONOS)
                ->map(fn (string $etiqueta, string $valor) => ['valor' => $valor, 'etiqueta' => $etiqueta])
                ->values(),
        ]);
    }

    public function store(Request $request, string $tipo)
    {
        $contenido = ContenidoWeb::create($this->validar($request, $tipo, true) + ['tipo' => $tipo]);
        $this->ponerImagen($contenido, $request);

        return back()->with('success', $contenido->activo
            ? 'Guardado. Ya aparece en la web.'
            : 'Guardado, sin mostrarse en la web.');
    }

    public function update(Request $request, ContenidoWeb $contenido)
    {
        $contenido->update($this->validar($request, $contenido->tipo, false));
        $this->ponerImagen($contenido, $request);

        return back()->with('success', 'Cambios guardados.');
    }

    /** @return array<string,mixed> */
    private function validar(Request $request, string $tipo, bool $creando): array
    {
        $largoTitulo = ['servicio' => 60, 'foto' => 150, 'pregunta' => 150, 'testimonio' => 60][$tipo];
        $largoTexto = ['servicio' => 200, 'foto' => 300, 'pregunta' => 1000, 'testimonio' => 400][$tipo];

        $reglas = [
            'titulo' => ['required', 'string', "max:{$largoTitulo}"],
            'texto' => [$tipo === 'foto' ? 'nullable' : 'required', 'string', "max:{$largoTexto}"],
            'orden' => 'nullable|integer|min:0|max:999',
            'activo' => 'boolean',
        ];

        if ($tipo === 'servicio') {
            $reglas['icono'] = ['required', Rule::in(array_keys(ContenidoWeb::ICONOS))];
        }

        if ($tipo === 'foto') {
            // Fotos de teléfono: hasta 8 MB, que igual se achican al guardarlas.
            // Sin SVG: puede llevar código que se ejecutaría desde la web.
            $reglas['imagen'] = [$creando ? 'required' : 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'];
        }

        if ($tipo === 'testimonio') {
            // Una opinión con nombre es un dato personal: sin permiso no se publica.
            $reglas['con_permiso'] = 'accepted';
        }

        $datos = $request->validate($reglas, [
            'titulo.required' => match ($tipo) {
                'foto' => 'Describe qué muestra la foto: lo lee Google y quien no puede verla.',
                'pregunta' => 'Escribe la pregunta.',
                'testimonio' => 'Escribe el nombre como quiere que aparezca, por ejemplo «Camila R.».',
                default => 'Ponle un nombre al servicio.',
            },
            'texto.required' => match ($tipo) {
                'pregunta' => 'Escribe la respuesta.',
                'testimonio' => 'Escribe lo que dijo.',
                default => 'Escribe una línea sobre el servicio.',
            },
            'imagen.required' => 'Elige la foto.',
            'imagen.image' => 'Ese archivo no es una imagen.',
            'imagen.mimes' => 'La foto tiene que ser JPG, PNG o WEBP.',
            'imagen.max' => 'La foto no puede pesar más de 8 MB.',
            'con_permiso.accepted' => 'Sin el permiso de la persona no se puede publicar su opinión.',
            'icono.in' => 'Elige uno de los íconos de la lista.',
        ]);

        return [
            'titulo' => trim($datos['titulo']),
            'texto' => isset($datos['texto']) ? trim($datos['texto']) : null,
            'icono' => $datos['icono'] ?? null,
            'con_permiso' => $tipo === 'testimonio',
            'orden' => (int) ($datos['orden'] ?? 0),
            'activo' => (bool) ($datos['activo'] ?? true),
        ];
    }

    /**
     * La foto va APARTE de los demás datos: editar la descripción sin volver a
     * elegir la foto no la borra. La anterior se borra solo al reemplazarla.
     */
    private function ponerImagen(ContenidoWeb $contenido, Request $request): void
    {
        if (! $request->hasFile('imagen')) {
            return;
        }

        $anterior = $contenido->imagen;
        $contenido->update(['imagen' => $this->guardarFoto($request->file('imagen'))]);

        if ($anterior && $anterior !== $contenido->imagen) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /**
     * Guarda la foto achicada y derecha.
     *
     * Una foto de teléfono pesa 4 a 8 MB y mide 4.000 píxeles: diez así
     * harían la página lentísima, y Google cuenta la velocidad. Se deja en
     * 1.600 de ancho, que sobra para una pantalla. Y se endereza: el teléfono
     * guarda la foto de lado y apunta en un dato aparte hacia dónde girarla,
     * y ese dato se pierde al procesarla.
     */
    private function guardarFoto(UploadedFile $archivo): string
    {
        $imagen = function_exists('imagecreatefromstring')
            ? @imagecreatefromstring((string) file_get_contents($archivo->getRealPath()))
            : false;

        if (! $imagen) {
            return $archivo->store('web', 'public');
        }

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($archivo->getRealPath());
            $giro = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($giro !== 0) {
                $imagen = imagerotate($imagen, $giro, 0);
            }
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $maximo = 1600;

        if ($ancho > $maximo) {
            $nuevoAlto = (int) round($alto * $maximo / $ancho);
            $chica = imagecreatetruecolor($maximo, $nuevoAlto);
            imagecopyresampled($chica, $imagen, 0, 0, 0, 0, $maximo, $nuevoAlto, $ancho, $alto);
            imagedestroy($imagen);
            $imagen = $chica;
        }

        ob_start();
        imagejpeg($imagen, null, 82);
        $bytes = (string) ob_get_clean();
        imagedestroy($imagen);

        $ruta = 'web/' . Str::random(32) . '.jpg';
        Storage::disk('public')->put($ruta, $bytes);

        return $ruta;
    }
}
