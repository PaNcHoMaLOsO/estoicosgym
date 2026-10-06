<?php

namespace App\Http\Middleware;

use App\Support\PaginasWeb;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Una página de la web apagada en Configuración → Páginas que se ven.
 *
 * Para el público no existe (404, y Google la saca). Quien entró al panel la
 * sigue viendo, con la franja que avisa que está apagada: así se revisa antes
 * de encenderla.
 */
class PaginaEncendida
{
    public function handle(Request $request, Closure $next, string $pagina): Response
    {
        if (PaginasWeb::encendida($pagina)) {
            return $next($request);
        }

        abort_unless($request->user(), 404);

        // En la petición y no compartido con todas las vistas: así no se
        // arrastra a la página siguiente.
        $request->attributes->set('paginaApagada', PaginasWeb::PAGINAS[$pagina][0] ?? $pagina);

        $respuesta = $next($request);
        // Que nadie la guarde ni la indexe mientras está apagada.
        $respuesta->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $respuesta->headers->set('Cache-Control', 'private, no-store');

        return $respuesta;
    }
}
