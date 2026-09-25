<?php

namespace App\Http\Middleware;

use App\Support\Permisos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a quien tiene el permiso que exige la ruta.
 *
 * La tabla `roles` guardaba una lista de permisos desde el principio, pero no
 * habia nada que la leyera: las rutas solo pedian estar autenticado, asi que la
 * recepcion entraba igual a la configuracion del gimnasio y a los informes de
 * ingresos. La columna era decorativa.
 *
 * El permiso sale del NOMBRE de la ruta (ver App\Support\Permisos), asi que
 * basta con poner este middleware al grupo entero y ninguna ruta nueva se queda
 * sin proteger por olvido.
 */
class VerificaPermiso
{
    public function handle(Request $request, Closure $next, ?string $permiso = null): Response
    {
        $nombre = $request->route()?->getName();
        $permiso ??= Permisos::para($nombre);

        if ($permiso === null) {
            // CERRADO POR DEFECTO. Una ruta del panel que no está en la lista
            // de módulos quedaba abierta a cualquiera con sesión: bastaba con
            // olvidarse de clasificarla. Ahora la usa solo el administrador
            // hasta que se clasifique (`permisos:revisar` la señala).
            if ($nombre === null || str_starts_with($nombre, 'panel.') || str_starts_with($nombre, 'admin.')) {
                $permiso = '*';
            } else {
                return $next($request);
            }
        }

        $usuario = $request->user();

        if ($usuario && $usuario->puede($permiso)) {
            return $next($request);
        }

        // A quien pide JSON se le responde JSON: si no, el fetch del panel
        // recibe una pagina de error entera y la pinta como si fuera el dato
        // que esperaba.
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para esta acción.',
            ], 403);
        }

        abort(403, 'No tienes permiso para esta acción.');
    }
}
