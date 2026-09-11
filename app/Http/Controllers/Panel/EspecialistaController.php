<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Especialista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
    public function index()
    {
        $especialistas = Especialista::orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Especialista $e) => [
                'uuid' => $e->uuid,
                'nombre' => $e->nombre,
                'especialidad' => $e->especialidad,
                'descripcion' => $e->descripcion,
                'foto_url' => $e->urlDeFoto(),
                // Se enseñan como se escriben, no como se guardan.
                'whatsapp' => $e->whatsapp ? $this->comoSeLee($e->whatsapp) : '',
                'instagram' => $e->instagram ? '@' . $e->instagram : '',
                'orden' => $e->orden,
                'activo' => (bool) $e->activo,
            ]);

        return Inertia::render('Configuracion/Especialistas', ['especialistas' => $especialistas]);
    }

    public function store(Request $request)
    {
        $especialista = Especialista::create($this->validar($request));
        $this->ponerFoto($especialista, $request);

        return back()->with('success', $especialista->activo
            ? "«{$especialista->nombre}» ya aparece en la web."
            : "«{$especialista->nombre}» quedó guardado, sin mostrarse en la web.");
    }

    public function update(Request $request, Especialista $especialista)
    {
        $especialista->update($this->validar($request));
        $this->ponerFoto($especialista, $request);

        return back()->with('success', 'Especialista actualizado.');
    }

    /** @return array<string,mixed> */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'especialidad' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:300',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'orden' => 'nullable|integer|min:0|max:999',
            'activo' => 'boolean',
            // Sin SVG: puede llevar código, y se ejecutaría al abrirlo desde la web.
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'quitar_foto' => 'boolean',
        ], [
            'foto.image' => 'Ese archivo no es una imagen.',
            'foto.mimes' => 'La foto tiene que ser JPG, PNG o WEBP.',
            'foto.max' => 'La foto no puede pesar más de 2 MB.',
        ]);

        return [
            'nombre' => trim($datos['nombre']),
            'especialidad' => trim($datos['especialidad']),
            'descripcion' => $datos['descripcion'] ?? null,
            'whatsapp' => $this->whatsapp($datos['whatsapp'] ?? null),
            'instagram' => $this->instagram($datos['instagram'] ?? null),
            'orden' => (int) ($datos['orden'] ?? 0),
            'activo' => (bool) ($datos['activo'] ?? true),
        ];
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
            $especialista->update(['foto' => $request->file('foto')->store('especialistas', 'public')]);
        } elseif ($request->boolean('quitar_foto')) {
            $especialista->update(['foto' => null]);
        } else {
            return;
        }

        if ($anterior && $anterior !== $especialista->foto) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /** 56912345678 → «9 1234 5678». */
    private function comoSeLee(string $whatsapp): string
    {
        $nueve = substr($whatsapp, -9);

        return substr($nueve, 0, 1) . ' ' . substr($nueve, 1, 4) . ' ' . substr($nueve, 5);
    }
}
