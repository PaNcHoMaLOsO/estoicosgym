<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Especialista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Los especialistas que aparecen en la web.
 *
 * Es un catálogo más —se crea, se edita y se oculta; no se borra— y usa el
 * mismo formulario que los otros cuatro. Lo propio es la foto y los dos
 * enlaces, que se limpian aquí antes de guardarse.
 */
class EspecialistaController extends Controller
{
    /**
     * Los especialistas, SOLOS.
     *
     * Iban en la misma lista que los embajadores y al dueño no le acomodaba:
     * son cosas distintas —uno es un profesional al que se le escribe, el otro
     * un socio que representa al gimnasio—, salen en sitios distintos de la
     * web y se llenan con datos distintos. Comparten tabla, no pantalla.
     */
    public function index()
    {
        return $this->lista('especialista');
    }

    /** Los embajadores, en su propia pantalla. */
    public function embajadores()
    {
        return $this->lista('embajador');
    }

    private function lista(string $tipo)
    {
        $especialistas = Especialista::where('tipo', $tipo)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Especialista $e) => [
                'uuid' => $e->uuid,
                'tipo' => $e->tipo,
                'nombre' => $e->nombre,
                'especialidad' => $e->especialidad,
                'descripcion' => $e->descripcion,
                'temas' => $e->temas ?? [],
                'modalidad' => $e->modalidad ?? '',
                // Para abrir su perfil desde el formulario.
                'perfil_url' => $e->tipo === 'especialista' && $e->slug ? route('landing.especialista', $e->slug) : null,
                'foto_url' => $e->urlDeFoto(),
                // Se enseñan como se escriben, no como se guardan.
                'whatsapp' => $e->whatsapp ? $this->comoSeLee($e->whatsapp) : '',
                'instagram' => $e->instagram ? '@' . $e->instagram : '',
                'orden' => $e->orden,
                'activo' => (bool) $e->activo,
            ]);

        return Inertia::render('Configuracion/Especialistas', [
            'especialistas' => $especialistas,
            'tipo' => $tipo,
        ]);
    }

    public function store(Request $request)
    {
        $fila = $this->validar($request);
        $tipo = $fila['tipo'] ?? 'especialista';

        // EL ORDEN SE PONE SOLO: lo nuevo va al final DE SU LISTA. Escrito a
        // mano, los números se repetían y saltaban —embajadores 1, 2, 4, 6 y
        // especialistas 3, 5, 7—, porque se contaba entre las dos listas.
        $especialista = Especialista::create($fila + [
            'orden' => (int) Especialista::where('tipo', $tipo)->max('orden') + 1,
        ]);
        $this->ponerFoto($especialista, $request);
        $this->renumerar($especialista->tipo);

        return back()->with('success', $especialista->activo
            ? "«{$especialista->nombre}» ya aparece en la web."
            : "«{$especialista->nombre}» quedó guardado, sin mostrarse en la web.");
    }

    public function update(Request $request, Especialista $especialista)
    {
        $especialista->update($this->validar($request));
        $this->ponerFoto($especialista, $request);

        return back()->with('success', "«{$especialista->nombre}» actualizado.");
    }

    /**
     * Lo borra del todo, con su foto.
     *
     * OCULTAR NO BASTABA: quien dejó de trabajar con el gimnasio —o un ejemplo
     * cargado para probar— seguía en la lista para siempre. Borrar se lleva
     * también la foto, que es la cara de una persona y no tiene por qué
     * quedarse en el servidor cuando ya no sale en ninguna parte.
     */
    public function eliminar(Especialista $especialista)
    {
        if ($especialista->foto) {
            Storage::disk('public')->delete($especialista->foto);
        }

        $especialista->delete();
        // Sin esto quedaría un hueco en la cuenta: 1, 2, 4…
        $this->renumerar($especialista->tipo);

        return back()->with('success', "«{$especialista->nombre}» eliminado.");
    }

    /**
     * Sube o baja un puesto dentro de su lista.
     *
     * Ordenar con flechas y no con números: «este va antes que ese». Se
     * renumera antes para que no haya dos en el mismo puesto.
     */
    public function mover(Request $request, Especialista $especialista)
    {
        $this->renumerar($especialista->tipo);
        $especialista->refresh();

        $arriba = $request->input('hacia') !== 'abajo';

        $vecino = Especialista::where('tipo', $especialista->tipo)
            ->when(
                $arriba,
                fn ($q) => $q->where('orden', '<', $especialista->orden)->orderByDesc('orden'),
                fn ($q) => $q->where('orden', '>', $especialista->orden)->orderBy('orden')
            )
            ->first();

        if ($vecino) {
            $puesto = $especialista->orden;
            $especialista->update(['orden' => $vecino->orden]);
            $vecino->update(['orden' => $puesto]);
        }

        return back();
    }

    /**
     * Deja los puestos de UNA lista en 1, 2, 3… sin huecos ni repetidos.
     *
     * Por tipo: los especialistas y los embajadores comparten tabla pero no
     * lista, y numerarlos juntos es justo lo que dejaba saltos en las dos.
     */
    private function renumerar(string $tipo): void
    {
        Especialista::where('tipo', $tipo)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->each(function (Especialista $especialista, int $i) {
                if ((int) $especialista->orden !== $i + 1) {
                    $especialista->update(['orden' => $i + 1]);
                }
            });
    }

    /** @return array<string,mixed> */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            // Especialista o embajador: decide dónde sale en la web.
            'tipo' => 'nullable|in:' . implode(',', array_keys(Especialista::TIPOS)),
            'nombre' => 'required|string|max:100',
            'especialidad' => 'required|string|max:100',
            // En su perfil: una presentación, no una línea.
            'descripcion' => 'nullable|string|max:1200',
            'temas' => 'nullable|array|max:8',
            'temas.*' => 'nullable|string|max:40',
            'modalidad' => 'nullable|in:' . implode(',', array_keys(Especialista::MODALIDADES)),
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'activo' => 'boolean',
            // Sin SVG: puede llevar código, y se ejecutaría al abrirlo desde la web.
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'quitar_foto' => 'boolean',
        ], [
            'foto.image' => 'Ese archivo no es una imagen.',
            'foto.mimes' => 'La foto tiene que ser JPG, PNG o WEBP.',
            'foto.max' => 'La foto no puede pesar más de 2 MB.',
        ]);

        $fila = [
            'nombre' => trim($datos['nombre']),
            'especialidad' => trim($datos['especialidad']),
            'descripcion' => filled($datos['descripcion'] ?? null) ? trim($datos['descripcion']) : null,
            // Sin repetidos ni vacíos, con la primera en mayúscula.
            'temas' => collect($datos['temas'] ?? [])
                ->map(fn ($t) => Str::ucfirst(trim((string) $t)))
                ->filter()
                ->unique(fn ($t) => mb_strtolower($t))
                ->values()
                ->all() ?: null,
            'modalidad' => ($datos['modalidad'] ?? null) ?: null,
            'whatsapp' => $this->whatsapp($datos['whatsapp'] ?? null),
            'instagram' => $this->instagram($datos['instagram'] ?? null),
            'activo' => (bool) ($datos['activo'] ?? true),
        ];

        // El tipo SOLO se toca si llega. Al crear, sin él queda especialista
        // (lo pone la base); al editar, se respeta el que tenía: poniéndolo por
        // defecto, una edición sin ese campo volvería especialista a un embajador.
        if (! empty($datos['tipo'])) {
            $fila['tipo'] = $datos['tipo'];
        }

        return $fila;
    }

    /** «9 1234 5678», «+56 9 1234 5678» o «56912345678» → 56912345678. */
    private function whatsapp(?string $valor): ?string
    {
        $digitos = preg_replace('/[^0-9]/', '', (string) $valor);

        if ($digitos === '') {
            return null;
        }

        if (strlen($digitos) === 9 && str_starts_with($digitos, '9')) {
            $digitos = '56' . $digitos;
        }

        if (! preg_match('/^569[0-9]{8}$/', $digitos)) {
            throw ValidationException::withMessages([
                'whatsapp' => 'Tiene que ser un celular chileno: 9 1234 5678.',
            ]);
        }

        return $digitos;
    }

    /**
     * «@usuario», «usuario» o el enlace del perfil → usuario.
     *
     * Se guarda SOLO el usuario y el enlace lo arma el modelo: lo que termina
     * en la página pública nunca es un enlace escrito a mano. Por eso algo
     * como «javascript:…» no pasa: no tiene forma de usuario de Instagram.
     */
    private function instagram(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('#^(?:https?://)?(?:www\.)?instagram\.com/([^/?\#]+)#i', $valor, $partes)) {
            $valor = $partes[1];
        }

        $usuario = ltrim($valor, '@');

        if (! preg_match('/^[A-Za-z0-9._]{1,30}$/', $usuario)) {
            throw ValidationException::withMessages([
                'instagram' => 'Escribe el usuario (@usuario) o el enlace del perfil.',
            ]);
        }

        return $usuario;
    }

    /**
     * La foto va APARTE de los demás datos.
     *
     * Si fuera un campo más, editar el nombre sin volver a subir la foto la
     * dejaría vacía. Solo se toca si llega un archivo o si se pide quitarla, y
     * la vieja se borra después de guardar la nueva.
     */
    private function ponerFoto(Especialista $especialista, Request $request): void
    {
        $anterior = $especialista->foto;

        if ($request->hasFile('foto')) {
            $especialista->update(['foto' => self::guardarLiviana($request->file('foto'), 'especialistas', 1200)]);
        } elseif ($request->boolean('quitar_foto')) {
            $especialista->update(['foto' => null]);
        } else {
            return;
        }

        if ($anterior && $anterior !== $especialista->foto) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /**
     * La foto, liviana: en la web sale en un panel, no a pantalla completa.
     * Si GD no la sabe leer, va tal cual.
     */
    private static function guardarLiviana(\Illuminate\Http\UploadedFile $archivo, string $carpeta, int $maximo): string
    {
        $liviana = \App\Support\FotoLiviana::desde((string) file_get_contents($archivo->getRealPath()), $maximo, 80, $archivo->getRealPath());

        if (! $liviana) {
            return $archivo->store($carpeta, 'public');
        }

        $ruta = $carpeta . '/' . \Illuminate\Support\Str::random(40) . '.' . $liviana['extension'];
        Storage::disk('public')->put($ruta, $liviana['bytes']);

        return $ruta;
    }

    /** 56912345678 → «9 1234 5678». */
    private function comoSeLee(string $whatsapp): string
    {
        $nueve = substr($whatsapp, -9);

        return substr($nueve, 0, 1) . ' ' . substr($nueve, 1, 4) . ' ' . substr($nueve, 5);
    }
}
