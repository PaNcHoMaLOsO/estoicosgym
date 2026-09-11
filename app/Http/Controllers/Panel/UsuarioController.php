<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use App\Support\EnlaceDeClave;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Las cuentas del panel: quién entra y con qué rol.
 *
 * Antes solo se podían crear desde el código —el sembrado deja un
 * administrador y una recepción—, así que sumar a alguien al mesón, o sacar a
 * quien se fue, necesitaba a un programador. Y el segundo factor no se podía
 * apagar desde ninguna parte, aunque el login le dice al usuario que el
 * administrador puede hacerlo.
 *
 * NO SE BORRAN: se desactivan. Lo que hizo cada cuenta —un fiado, un pago—
 * queda a su nombre, y una cuenta borrada dejaría esas fichas sin autor.
 */
class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $ultimaVez = $this->ultimaVezDeCadaUno();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => User::with('rol')
                ->orderByDesc('activo')
                ->orderBy('name')
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'nombre' => $u->name,
                    'email' => $u->email,
                    'telefono' => $u->phone,
                    'id_rol' => $u->id_rol,
                    'rol' => $u->rol?->nombre,
                    'es_admin' => $this->esAdministrador($u),
                    'activo' => (bool) $u->activo,
                    'dos_factores' => (bool) $u->two_factor_enabled,
                    'ultima_vez' => isset($ultimaVez[$u->id])
                        ? Carbon::createFromTimestamp($ultimaVez[$u->id])->toIso8601String()
                        : null,
                    'soy_yo' => $u->id === $request->user()->id,
                ]),
            'roles' => Rol::where('activo', true)
                ->orderBy('id')
                ->get()
                ->map(fn (Rol $r) => [
                    'valor' => (string) $r->id,
                    'etiqueta' => $r->nombre,
                    'descripcion' => $r->descripcion,
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'id_rol' => ['required', Rule::exists('roles', 'id')->where('activo', true)],
            'telefono' => 'nullable|string|max:20',
            'clave' => 'required|string|min:8|confirmed',
        ], $this->mensajes());

        $usuario = User::create([
            'name' => trim($datos['nombre']),
            'email' => mb_strtolower(trim($datos['email'])),
            'id_rol' => (int) $datos['id_rol'],
            'phone' => $datos['telefono'] ?? null ?: null,
            'password' => $datos['clave'],
            'activo' => true,
        ]);

        return back()->with('success', "Listo: {$usuario->name} ya puede entrar con {$usuario->email} y la contraseña que pusiste.");
    }

    public function update(Request $request, User $usuario)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuario->id)],
            'id_rol' => ['required', Rule::exists('roles', 'id')->where('activo', true)],
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
            'dos_factores' => 'boolean',
            'clave' => 'nullable|string|min:8|confirmed',
        ], $this->mensajes());

        $idRol = (int) $datos['id_rol'];
        $activo = (bool) ($datos['activo'] ?? $usuario->activo);
        $dosFactores = (bool) ($datos['dos_factores'] ?? $usuario->two_factor_enabled);
        $telefono = trim((string) ($datos['telefono'] ?? '')) ?: null;
        $soyYo = $usuario->id === $request->user()->id;

        // Uno mismo no se cierra la puerta: quitarse el rol o desactivarse por
        // error dejaría la cuenta afuera sin nadie que la vuelva a abrir.
        if ($soyYo && (! $activo || $idRol !== (int) $usuario->id_rol)) {
            throw ValidationException::withMessages([
                'id_rol' => 'Tu propia cuenta no la puedes desactivar ni cambiarle el rol: pídeselo a otro administrador.',
            ]);
        }

        // Nunca sin administrador: sin él, nadie podría volver a entrar a
        // Configuración ni a esta pantalla.
        $eraAdministrador = $usuario->activo && $this->esAdministrador($usuario);
        $siguieAdministrador = $activo && $this->rolesAdministradores()->contains($idRol);

        if ($eraAdministrador && ! $siguieAdministrador && $this->otrosAdministradoresActivos($usuario) === 0) {
            throw ValidationException::withMessages([
                'id_rol' => 'Es el único administrador activo: sin él nadie podría entrar a Configuración.',
            ]);
        }

        if ($dosFactores && ! $telefono) {
            throw ValidationException::withMessages([
                'dos_factores' => 'Para el segundo factor hace falta el celular: ahí llega el código.',
            ]);
        }

        $cambios = [
            'name' => trim($datos['nombre']),
            'email' => mb_strtolower(trim($datos['email'])),
            'id_rol' => $idRol,
            'phone' => $telefono,
            'activo' => $activo,
            'two_factor_enabled' => $dosFactores,
        ];

        $claveNueva = ! empty($datos['clave']);

        if ($claveNueva) {
            $cambios['password'] = $datos['clave'];
        }

        $usuario->update($cambios);

        // Desactivada o con contraseña nueva: afuera de donde tuviera la sesión
        // abierta. Si no, la cuenta de alguien que se fue seguiría adentro en
        // el computador donde quedó abierta.
        if (! $activo || $claveNueva) {
            $this->cerrarSesiones($usuario, $request);
        }

        return back()->with('success', match (true) {
            ! $activo => "{$usuario->name} ya no puede entrar al panel.",
            $claveNueva => 'Guardado. La contraseña nueva ya vale.',
            default => 'Guardado.',
        });
    }

    /**
     * Le manda a la cuenta el enlace para poner su contraseña.
     *
     * Así el administrador no tiene que inventar una y dictarla: la persona la
     * elige ella misma desde su correo.
     */
    public function enlace(User $usuario)
    {
        // Una cuenta desactivada no entra aunque tenga contraseña nueva:
        // mandarle el enlace solo haría creer que ya puede.
        if (! $usuario->activo) {
            return back()->with('error', "La cuenta de {$usuario->name} está desactivada: actívala primero.");
        }

        try {
            EnlaceDeClave::mandar($usuario, EnlaceDeClave::crear($usuario));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo mandar el correo. Revisa que el correo del gimnasio esté configurado.');
        }

        return back()->with('success', "Le mandamos a {$usuario->email} un enlace para poner su contraseña. Vale por una hora.");
    }

    /** @return array<string,string> */
    private function mensajes(): array
    {
        return [
            'nombre.required' => 'Escribe el nombre.',
            'email.required' => 'Escribe el correo: con él entra al panel.',
            'email.email' => 'Ese correo no es válido.',
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'id_rol.required' => 'Elige el rol.',
            'id_rol.exists' => 'Elige uno de los roles de la lista.',
            'clave.required' => 'Pon una contraseña para que pueda entrar.',
            'clave.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'clave.confirmed' => 'Las dos contraseñas no coinciden.',
        ];
    }

    private function esAdministrador(User $usuario): bool
    {
        return in_array('*', (array) ($usuario->rol?->permisos ?? []), true);
    }

    /** Los roles que lo pueden todo. */
    private function rolesAdministradores(): Collection
    {
        return Rol::all()
            ->filter(fn (Rol $r) => in_array('*', (array) ($r->permisos ?? []), true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    private function otrosAdministradoresActivos(User $usuario): int
    {
        return User::where('activo', true)
            ->where('id', '!=', $usuario->id)
            ->whereIn('id_rol', $this->rolesAdministradores())
            ->count();
    }

    /**
     * Cuándo se vio a cada uno por última vez, sacado de sus sesiones.
     *
     * @return array<int,int> id => marca de tiempo
     */
    private function ultimaVezDeCadaUno(): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return DB::table(config('session.table', 'sessions'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->selectRaw('user_id, max(last_activity) as ultima')
            ->pluck('ultima', 'user_id')
            ->map(fn ($marca) => (int) $marca)
            ->all();
    }

    /** Cierra sus otras sesiones y anula su «recordarme». */
    private function cerrarSesiones(User $usuario, Request $request): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $usuario->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $usuario->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
    }
}
