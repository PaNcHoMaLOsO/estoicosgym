<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheMiddleware
{
    /**
     * Previene que el navegador almacene en caché las páginas protegidas.
     * Esto evita problemas de seguridad cuando el usuario presiona "atrás"
     * después de hacer logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Agregar headers para prevenir caché
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
