<?php

namespace App\Http\Middleware;

use App\Support\Ajustes;
use App\Support\EstadoDeConfiguracion;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'id_rol' => $request->user()->id_rol,
                    'rol' => $request->user()->rol?->nombre,
                    // Los permisos viajan al panel para NO ENSENAR lo que no se
                    // puede usar. Es cosmetica: quien manda es el middleware,
                    // que revisa cada peticion aunque el enlace no se pinte.
                    'permisos' => $request->user()->permisos(),
                ] : null,
            ],

            // Los mensajes viajan como props compartidas y los pinta el Layout
            // una sola vez. En Blade cada vista tenia que acordarse de mostrar
            // su propio bloque y 9 de 14 se olvidaban, asi que las
            // confirmaciones se perdian en silencio.
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            /*
             * SI EL DINERO SE ENSEÑA O NO. Va compartido porque lo mira medio
             * panel —el menú, las cifras tapadas, los avisos de quién debe— y
             * pasarlo pantalla por pantalla obligaría a acordarse en cada
             * controlador nuevo; el que se olvidara enseñaría las cifras.
             *
             * Los ajustes viven en caché, así que esto no es una consulta por
             * página.
             */
            'privado' => fn () => [
                'sin_montos' => Ajustes::activo('privacidad.ocultar_montos'),
                'sin_caja' => Ajustes::activo('privacidad.ocultar_caja'),
                'sin_fiado' => Ajustes::activo('privacidad.ocultar_fiado'),
                'sin_pendientes' => Ajustes::activo('privacidad.ocultar_pendientes'),
            ],

            // El menú de Configuración marca las secciones con algo pendiente.
            // Solo se calcula ahí adentro y para quien la puede ver: son varias
            // cuentas, y no hace falta pagarlas en cada pantalla del mesón.
            'configuracion' => fn () => $this->configuracion($request),
        ];
    }

    /** @return array{avisos: array<string,string>}|null */
    private function configuracion(Request $request): ?array
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->puede('configuracion.ver') || ! $request->routeIs(
            'panel.configuracion.*',
            'panel.membresias.*',
            'panel.convenios.*',
            'panel.metodos-pago.*',
            'panel.motivos-descuento.*',
            'panel.notificaciones.plantillas*',
            'panel.web.*',
            'panel.especialistas.*',
            'panel.usuarios.*',
            'panel.papelera.*',
        )) {
            return null;
        }

        return ['avisos' => EstadoDeConfiguracion::avisosDelMenu()];
    }
}
