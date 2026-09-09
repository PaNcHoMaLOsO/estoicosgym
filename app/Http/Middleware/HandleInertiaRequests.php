<?php

namespace App\Http\Middleware;

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
        ];
    }
}
