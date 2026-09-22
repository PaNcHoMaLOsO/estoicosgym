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
     * Qué ajuste apaga cada pantalla.
     *
     * @var array<string,string>
     */
    private const AJUSTES = [
        'caja' => 'privacidad.ocultar_caja',
        'pendientes' => 'privacidad.ocultar_pendientes',
    ];

    public function handle(Request $request, Closure $next, string $que = 'caja'): Response
    {
        $ajuste = self::AJUSTES[$que] ?? self::AJUSTES['caja'];

        if (! Ajustes::activo($ajuste)) {
            return $next($request);
        }

        // Se dice DÓNDE se vuelve a encender: una pantalla que deja de abrirse
        // sin explicar por qué parece que el sistema se rompió.
        return redirect('/panel')->with(
            'info',
            $que === 'pendientes'
                ? 'Lo que deben de sus membresías está escondido. Se vuelve a ver en Configuración → El dinero en pantalla.'
                : 'Las cifras de caja están escondidas. Se vuelven a ver en Configuración → El dinero en pantalla.'
        );
    }
}
