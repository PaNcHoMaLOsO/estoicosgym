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
        // EL ORDEN SE PONE SOLO: lo nuevo va al final, que es donde uno espera
        // que caiga. Pedir el número a mano dejaba huecos y repetidos —dos
        // fotos con el 12 y ninguna con el 11—, y entonces el orden de la web
        // lo decidía el desempate y no quien lo escribió.
        $contenido = ContenidoWeb::create($this->validar($request, $tipo, true) + [
            'tipo' => $tipo,
            'orden' => (int) ContenidoWeb::where('tipo', $tipo)->max('orden') + 1,
        ]);
        $this->ponerImagen($contenido, $request);
        $this->renumerar($tipo);

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

    /**
     * Lo borra del todo, con su archivo.
     *
     * OCULTAR NO BASTA: una foto que ya no se quiere seguiría ocupando sitio en
     * la lista y en el disco. Y el archivo se va con ella, o el servidor se
     * llena de fotos que no mira nadie.
     */
    public function eliminar(ContenidoWeb $contenido)
    {
        if ($contenido->imagen) {
            Storage::disk('public')->delete($contenido->imagen);
        }

        $contenido->delete();
        // Sin esto quedaría un hueco en la cuenta: 1, 2, 4, 5…
        $this->renumerar($contenido->tipo);

        return back()->with('success', 'Eliminado. Ya no está en la web.');
    }

    /**
     * Sube o baja un puesto.
     *
     * ES LA ÚNICA MANERA DE ORDENAR QUE NO OBLIGA A PENSAR EN NÚMEROS: «esta
     * foto va antes que esa» se dice con una flecha. Se renumera primero para
     * que no haya dos con el mismo puesto, que es como estaba la galería.
     */
    public function mover(Request $request, ContenidoWeb $contenido)
    {
        $this->renumerar($contenido->tipo);
        $contenido->refresh();

        $arriba = $request->input('hacia') !== 'abajo';

        $vecino = ContenidoWeb::where('tipo', $contenido->tipo)
            ->when(
                $arriba,
                fn ($q) => $q->where('orden', '<', $contenido->orden)->orderByDesc('orden'),
                fn ($q) => $q->where('orden', '>', $contenido->orden)->orderBy('orden')
            )
            ->first();

        if (! $vecino) {
            return back();
        }

        $puesto = $contenido->orden;
        $contenido->update(['orden' => $vecino->orden]);
        $vecino->update(['orden' => $puesto]);

        return back();
    }

    /**
     * Ordena la galería sola.
     *
     * ORDENAR DIEZ FOTOS A FLECHAZOS ES UN TRABAJO QUE NADIE HACE: se suben
     * cuando se sacan y quedan en el orden en que se subieron, que no es
     * ninguno. Dos reglas, que son las que usaría cualquiera a ojo:
     *
     *  · Las panorámicas primero. Son las que enseñan la sala entera, y la
     *    galería de la web empieza por ahí: quien entra quiere ver el local,
     *    no un primer plano de una mancuerna.
     *  · Después, sin dos parecidas seguidas. Se compara el color medio de
     *    cada foto —las del salón rojo se parecen entre ellas, las de la sala
     *    azul entre ellas— y se va eligiendo cada vez la que más se diferencia
     *    de la anterior. Tres rojas seguidas parecen la misma foto repetida.
     *
     * NO SUSTITUYE A LAS FLECHAS: deja un orden razonable de una vez, y quien
     * quiera una foto concreta arriba la sube a mano y ahí se queda.
     */
    public function ordenar(string $tipo)
    {
        // Solo las fotos: en un servicio o una pregunta el orden lo decide lo
        // que dice, no cómo se ve.
        abort_unless($tipo === 'foto', 404);

        $fotos = ContenidoWeb::where('tipo', 'foto')
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn (ContenidoWeb $foto) => ['foto' => $foto] + $this->comoSeVe($foto));

        // Apaisada: más ancha que alta con holgura. Las de teléfono, que son
        // verticales, no entran aquí.
        $panoramicas = $fotos->filter(fn (array $f) => $f['proporcion'] >= 1.25)->values()->all();
        $pendientes = $fotos->filter(fn (array $f) => $f['proporcion'] < 1.25)->values()->all();

        $orden = $panoramicas;

        if ($orden === [] && $pendientes !== []) {
            $orden[] = array_shift($pendientes);
        }

        while ($pendientes !== []) {
            $anterior = end($orden);
            $lejana = 0;

            foreach ($pendientes as $i => $candidata) {
                if ($this->distancia($anterior['color'], $candidata['color'])
                    > $this->distancia($anterior['color'], $pendientes[$lejana]['color'])) {
                    $lejana = $i;
                }
            }

            $orden[] = $pendientes[$lejana];
            unset($pendientes[$lejana]);
            $pendientes = array_values($pendientes);
        }

        foreach ($orden as $i => $puesto) {
            $puesto['foto']->update(['orden' => $i + 1]);
        }

        return back()->with('success', count($orden) === 0
            ? 'No hay fotos que ordenar.'
            : 'Ordenadas: las panorámicas primero y sin dos parecidas seguidas. Con las flechas las mueves a tu gusto.');
    }

    /**
     * Cómo se ve una foto: su proporción y su color medio.
     *
     * El color se saca de una copia de 8×8 píxeles —no hace falta más para
     * saber si una foto es roja o azul— y así ordenar cuarenta fotos no se
     * convierte en cuarenta imágenes enteras cargadas en memoria.
     *
     * @return array{proporcion:float, color:array{0:int,1:int,2:int}}
     */
    private function comoSeVe(ContenidoWeb $foto): array
    {
        $gris = ['proporcion' => 1.0, 'color' => [128, 128, 128]];
        $ruta = $foto->imagen ? Storage::disk('public')->path($foto->imagen) : null;

        if (! $ruta || ! is_file($ruta) || ! function_exists('imagecreatefromstring')) {
            return $gris;
        }

        $medidas = @getimagesize($ruta);
        $imagen = @imagecreatefromstring((string) file_get_contents($ruta));

        if (! $medidas || ! $imagen) {
            return $gris;
        }

        $chica = imagescale($imagen, 8, 8);
        $suma = [0, 0, 0];

        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $punto = imagecolorsforindex($chica, imagecolorat($chica, $x, $y));
                $suma[0] += $punto['red'];
                $suma[1] += $punto['green'];
                $suma[2] += $punto['blue'];
            }
        }

        imagedestroy($chica);
        imagedestroy($imagen);

        return [
            'proporcion' => $medidas[1] > 0 ? $medidas[0] / $medidas[1] : 1.0,
            'color' => [(int) ($suma[0] / 64), (int) ($suma[1] / 64), (int) ($suma[2] / 64)],
        ];
    }

    /**
     * Cuánto se diferencian dos colores.
     *
     * @param  array{0:int,1:int,2:int}  $uno
     * @param  array{0:int,1:int,2:int}  $otro
     */
    private function distancia(array $uno, array $otro): float
    {
        return sqrt(
            ($uno[0] - $otro[0]) ** 2
            + ($uno[1] - $otro[1]) ** 2
            + ($uno[2] - $otro[2]) ** 2
        );
    }

    /**
     * Deja los puestos en 1, 2, 3… sin huecos ni repetidos.
     *
     * Se llama después de crear, de borrar y antes de mover: así el número que
     * se ve en la lista es el puesto de verdad y no una etiqueta suelta.
     */
    private function renumerar(string $tipo): void
    {
        ContenidoWeb::where('tipo', $tipo)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->each(function (ContenidoWeb $contenido, int $i) {
                if ($contenido->orden !== $i + 1) {
                    $contenido->update(['orden' => $i + 1]);
                }
            });
    }

    /** @return array<string,mixed> */
    private function validar(Request $request, string $tipo, bool $creando): array
    {
        $largoTitulo = ['servicio' => 60, 'foto' => 150, 'pregunta' => 150, 'testimonio' => 60][$tipo];
        $largoTexto = ['servicio' => 200, 'foto' => 300, 'pregunta' => 1000, 'testimonio' => 400][$tipo];

        $reglas = [
            'titulo' => ['required', 'string', "max:{$largoTitulo}"],
            'texto' => [$tipo === 'foto' ? 'nullable' : 'required', 'string', "max:{$largoTexto}"],
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
