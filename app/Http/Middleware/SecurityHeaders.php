<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de seguridad para páginas públicas
 * Implementa headers de seguridad recomendados por OWASP
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ===== HEADERS DE SEGURIDAD OWASP =====

        // 1. Content-Security-Policy (CSP)
        // Previene XSS, clickjacking, inyección de código
        // Con `npm run dev` los estilos llegan del servidor de Vite, que no es
        // «self»: sin sumarlo, la política los bloquea y la web sale sin estilos.
        $servidor = $this->servidorDeVite();
        $vite = $servidor ? ' ' . $servidor : '';
        $viteEnVivo = $servidor ? ' ' . $servidor . ' ' . preg_replace('#^http#', 'ws', $servidor) : '';

        $csp = implode('; ', [
            "default-src 'self'",
            // Google Analytics: solo se carga si hay un ID en Configuracion -> Web.
            // Tailwind ya no viene de su CDN: la web usa su hoja compilada.
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.googletagmanager.com{$vite}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com{$vite}",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com{$viteEnVivo}",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // 2. X-Content-Type-Options
        // Previene MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. X-Frame-Options
        // Previene clickjacking (respaldo para navegadores antiguos)
        $response->headers->set('X-Frame-Options', 'DENY');

        // 4. X-XSS-Protection
        // Activa filtro XSS del navegador (legacy, pero útil)
        // «0» es lo recomendado hoy: el filtro viejo de los navegadores abría
        // más agujeros de los que tapaba.
        $response->headers->set('X-XSS-Protection', '0');

        // 5. Referrer-Policy
        // Controla información enviada en el header Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 6. Permissions-Policy (antes Feature-Policy)
        // Deshabilita APIs innecesarias
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

        // 7. Strict-Transport-Security (HSTS)
        // Fuerza HTTPS - Solo en producción
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // 8. Cache-Control: lo decide la web pública, no el panel.
        $response->headers->set('Cache-Control', $this->cacheSegun($request));
        // NoCacheMiddleware (del grupo web) lo respeta y no lo pisa.
        $request->attributes->set(self::CACHE_DECIDIDO, true);

        return $response;
    }

    public const CACHE_DECIDIDO = 'cache-control-decidido';

    /**
     * CUÁNTO SE PUEDE GUARDAR CADA COSA.
     *
     * Antes todo salía con no-store, como el panel: el navegador no guardaba
     * ni la portada y al volver atrás la pedía entera otra vez. Ahora:
     *
     *  · robots.txt y el mapa del sitio: iguales para todos, una hora en
     *    cualquier caché (también en la de Cloudflare).
     *  · Lo que es de una persona (su membresía, su contrato) y lo que se
     *    envía (formularios): no se guarda en ninguna parte.
     *  · Las demás páginas: solo en el navegador de quien las mira, y
     *    preguntando antes de usarlas. Llevan el formulario con su llave de
     *    sesión, así que no pueden ir a una caché compartida.
     */
    private function cacheSegun(Request $request): string
    {
        if ($request->routeIs('landing.robots', 'landing.sitemap')) {
            return 'public, max-age=3600';
        }

        if (! $request->isMethodCacheable() || $request->routeIs('landing.membresia', 'contrato.*')) {
            return 'no-store, no-cache, must-revalidate, max-age=0';
        }

        return 'private, no-cache';
    }

    /** La dirección del servidor de Vite si está corriendo (`npm run dev`), o null. */
    private function servidorDeVite(): ?string
    {
        $archivo = public_path('hot');

        if (! is_file($archivo)) {
            return null;
        }

        $origen = rtrim(trim((string) file_get_contents($archivo)), '/');

        return preg_match('#^https?://#', $origen) ? $origen : null;
    }
}
