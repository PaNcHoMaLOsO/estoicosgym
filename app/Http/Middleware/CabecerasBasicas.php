<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las cabeceras de seguridad mínimas para TODO: el panel, el login y la
 * recuperación de clave.
 *
 * La web pública ya tenía las suyas (SecurityHeaders, con su política de
 * contenidos); el panel no llevaba ninguna, y cualquier página podía cargarlo
 * dentro de un marco invisible y hacer que alguien pulsara «Eliminar» creyendo
 * pulsar otra cosa. La política de contenidos es la mínima (sin marcos, sin
 * <object>, formularios solo hacia aquí) y la de permisos deja la cámara para
 * la foto del socio y nada más.
 *
 * No pisa lo que ya puso otro middleware.
 */
class CabecerasBasicas
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        foreach ([
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'",
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=()',
        ] as $cabecera => $valor) {
            if (! $respuesta->headers->has($cabecera)) {
                $respuesta->headers->set($cabecera, $valor);
            }
        }

        return $respuesta;
    }
}
