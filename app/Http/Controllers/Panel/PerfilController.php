<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Support\CatalogoDePermisos;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Qué puede hacer cada perfil (Configuración → Usuarios del panel).
 *
 * Antes lo que podía la recepción estaba escrito en el código: darle «ver la
 * caja del día» a quien cierra el turno necesitaba a un programador. Aquí el
 * dueño enciende y apaga, con el nombre de cada cosa en castellano
 * (CatalogoDePermisos), y queda anotado quién cambió qué.
 *
 * EL ADMINISTRADOR NO SE TOCA. Es el rol con «*»: lo puede todo, también lo
 * que se agregue mañana. Partirlo en casillas lo dejaría sin lo nuevo, y
 * quitarle algo por error podría dejar al gimnasio sin nadie que entre a
 * Configuración a arreglarlo.
 */
class PerfilController extends Controller
{
    public function index()
    {
        $ultimos = $this->ultimosCambios();

        return Inertia::render('Usuarios/Perfiles', [
            'areas' => CatalogoDePermisos::paraLaPantalla(),
            'necesita' => CatalogoDePermisos::mapaDeDependencias(),
            'perfiles' => Rol::withCount(['usuarios' => fn ($q) => $q->where('activo', true)])
                ->orderBy('id')
                ->get()
                ->map(fn (Rol $rol) => [
                    'id' => $rol->id,
                    'nombre' => $rol->nombre,
                    'descripcion' => $rol->descripcion,
                    'activo' => (bool) $rol->activo,
                    'es_admin' => self::esAdministrador($rol),
                    'cuentas' => (int) $rol->usuarios_count,
                    'permisos' => self::esAdministrador($rol)
                        ? []
                        : CatalogoDePermisos::expandir((array) ($rol->permisos ?? [])),
                    'ultimo_cambio' => $ultimos[$rol->id] ?? null,
                ])
                ->values(),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        abort_if(self::esAdministrador($rol), 403, 'El Administrador lo puede todo y no se cambia desde aquí.');

        $datos = $request->validate([
            'permisos' => 'present|array',
            // Solo lo que está en el catálogo: ni el comodín «*», ni «pagos.*»,
            // ni un nombre inventado que nadie sabría qué abre.
            'permisos.*' => ['string', Rule::in(CatalogoDePermisos::todos())],
        ], [
            'permisos.*.in' => 'Uno de los permisos no existe. Recarga la pantalla y vuelve a intentarlo.',
        ]);

        // Lo que se enciende arrastra lo que necesita para servir.
        $nuevos = CatalogoDePermisos::conLoQueNecesitan($datos['permisos']);

        /*
         * SU PROPIO PERFIL NO SE QUEDA SIN LA LLAVE. Quitándose «usuarios.editar»
         * o «usuarios.ver», quien lo guarda —y todas las cuentas con ese perfil—
         * dejaban de entrar a Perfiles y Usuarios, y nadie con ese perfil podía
         * devolvérselo. Eso lo tiene que hacer otro perfil, el Administrador.
         */
        $faltan = array_diff(['usuarios.ver', 'usuarios.editar'], CatalogoDePermisos::expandir($nuevos));

        if ((int) $request->user()?->id_rol === (int) $rol->id && $faltan !== []) {
            throw ValidationException::withMessages([
                'permisos' => 'No puedes quitarle a tu propio perfil el acceso a Usuarios y Perfiles: te quedarías fuera, tú y todos los de este perfil. Si de verdad hay que quitarlo, que lo haga un Administrador.',
            ]);
        }

        /*
         * Se guarda la lista ENTERA y no se mezcla con la de antes. Lo que el
         * rol tuviera fuera del catálogo —nombres viejos de antes de
         * «modulo.accion»— no abre ninguna ruta y no tiene casilla para
         * quitarlo; dejarlo sería guardar basura que nadie ve.
         *
         * LO DE ANTES SE LEE CON EL ROL TRABADO: dos «Guardar» a la vez
         * comparaban los dos contra la lista vieja y dejaban dos cambios
         * iguales en el registro. El segundo espera, ve que ya está y no
         * anota nada.
         */
        [$agregados, $quitados] = DB::transaction(function () use ($rol, $nuevos, $request) {
            $actual = Rol::whereKey($rol->getKey())->lockForUpdate()->firstOrFail();

            $antes = CatalogoDePermisos::expandir((array) ($actual->permisos ?? []));
            $agregados = array_values(array_diff($nuevos, $antes));
            $quitados = array_values(array_diff($antes, $nuevos));

            $actual->update(['permisos' => $nuevos]);

            if ($agregados !== [] || $quitados !== []) {
                DB::table('cambios_de_perfil')->insert([
                    'id_rol' => $actual->id,
                    'id_usuario' => $request->user()->id,
                    'agregados' => json_encode($agregados),
                    'quitados' => json_encode($quitados),
                    'created_at' => now(),
                ]);
            }

            return [$agregados, $quitados];
        });

        if ($agregados === [] && $quitados === []) {
            return back()->with('info', "No había cambios en {$rol->nombre}.");
        }

        return back()->with('success', "Listo: {$rol->nombre} ya tiene lo nuevo. Vale desde la próxima pantalla que abran.");
    }

    public static function esAdministrador(Rol $rol): bool
    {
        return in_array('*', (array) ($rol->permisos ?? []), true);
    }

    /**
     * El último cambio de cada perfil, para decirlo bajo su nombre: quien
     * entra a ver por qué recepción ya no puede algo encuentra ahí la
     * respuesta sin preguntar.
     *
     * @return array<int,array{quien:?string,cuando:string,agregados:list<string>,quitados:list<string>}>
     */
    private function ultimosCambios(): array
    {
        return DB::table('cambios_de_perfil')
            ->leftJoin('users', 'users.id', '=', 'cambios_de_perfil.id_usuario')
            ->orderByDesc('cambios_de_perfil.id')
            ->get(['cambios_de_perfil.*', 'users.name as quien'])
            ->unique('id_rol')
            ->mapWithKeys(fn ($fila) => [(int) $fila->id_rol => [
                'quien' => $fila->quien,
                'cuando' => Carbon::parse($fila->created_at)->toIso8601String(),
                'agregados' => array_map(
                    fn ($p) => CatalogoDePermisos::etiqueta($p),
                    json_decode($fila->agregados, true) ?: []
                ),
                'quitados' => array_map(
                    fn ($p) => CatalogoDePermisos::etiqueta($p),
                    json_decode($fila->quitados, true) ?: []
                ),
            ]])
            ->all();
    }
}
