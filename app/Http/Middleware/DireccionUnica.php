<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Una sola dirección para cada página de la web pública.
 *
 * «www.progymlosangeles.cl/planes», «progymlosangeles.cl/index.php/planes» y
 * «progymlosangeles.cl/planes» son la misma página, pero para Google son tres
 * y se reparten lo que vale cada una. Aquí las dos primeras se mandan a la de
 * APP_URL con un 301 (cambio permanente: Google pasa todo a la nueva).
 *
 * Solo en producción: en el equipo del mesón y en las pruebas la dirección es
 * la que sea (127.0.0.1, un túnel...). Y solo lo que se lee (GET): un 301
 * sobre un formulario enviado perdería lo escrito.
 */
class DireccionUnica
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production') || ! $request->isMethodCacheable()) {
            return $next($request);
        }

        $base = rtrim((string) config('app.url'), '/');
        $host = parse_url($base, PHP_URL_HOST);
        $conIndex = str_starts_with($request->getRequestUri(), '/index.php');

        if (! $host || ($request->getHost() === $host && ! $conIndex)) {
            return $next($request);
        }

        $camino = '/' . ltrim($request->getPathInfo(), '/');
        $consulta = $request->getQueryString();

        return redirect()->away($base . ($camino === '/' ? '/' : $camino) . ($consulta ? '?' . $consulta : ''), 301);
    }
}
