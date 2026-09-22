<?php

namespace App\Http\Middleware;

use App\Support\Ajustes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra las pantallas que solo son dinero, cuando se pidió esconderlo.
 *
 * ESTO NO ES UN PERMISO Y NO PRETENDE SERLO. Quien tenga el permiso de
 * informes lo sigue teniendo: lo que se apagó aquí se vuelve a encender en
 * Configuración → El dinero en pantalla, y quien puede entrar ahí puede
 * hacerlo. Es una decisión del dueño sobre lo que quiere tener delante, no una
 * barrera contra nadie.
 *
 * Hace falta porque tapar las cifras no alcanza en estas pantallas: Caja
 * entera y el informe de ingresos NO SON OTRA COSA que dinero. Dejarlas
 * abiertas con todos los números en puntitos sería enseñar una pantalla vacía
 * y hacerle perder el tiempo a quien la abra.
 */
class EscondeElDinero
{
    /**
     * @param string $que 'dinero' para las cifras del negocio, 'deudas' para
     *                    las listas de quién debe.
     */
    public function handle(Request $request, Closure $next, string $que = 'dinero'): Response
    {
        $ajuste = $que === 'deudas' ? 'privacidad.ocultar_deudas' : 'privacidad.ocultar_dinero';

        if (! Ajustes::activo($ajuste)) {
            return $next($request);
        }

        // Se dice DÓNDE se vuelve a encender: una pantalla que deja de abrirse
        // sin explicar por qué parece que el sistema se rompió.
        return redirect('/panel')->with(
            'info',
            $que === 'deudas'
                ? 'Las listas de quién debe están escondidas. Se vuelven a ver en Configuración → El dinero en pantalla.'
                : 'Las cifras de dinero están escondidas. Se vuelven a ver en Configuración → El dinero en pantalla.'
        );
    }
}
