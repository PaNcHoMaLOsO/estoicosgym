<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * EL DOBLE CLIC, EN LO QUE NO TENÍA PROTECCIÓN.
 *
 * Lo que mueve plata (pagos, inscripciones, renovaciones, fiados, canjes) ya
 * tiene su token por formulario (ValidatesFormToken). Pero quedaban quince
 * acciones que crean algo sin nada que las cuide —un convenio, un plan, una
 * clase, un profesional, un usuario, el contrato por correo— y un doble clic
 * las hacía dos veces.
 *
 * Se pone ruta por ruta ('una-vez' en routes/web.php), solo en esas: si llega
 * OTRA VEZ lo mismo —misma persona, misma dirección, mismos datos— dentro de
 * unos segundos, el segundo envío no se hace. No va en todo el panel: en un
 * fiado, dos «agua $1.000» seguidos son dos aguas de verdad, y pausar,
 * reanudar y volver a pausar es una secuencia válida; esas acciones tienen
 * su propia regla.
 */
class EvitaDobleEnvio
{
    /** Segundos en que el mismo envío se toma por repetido. */
    public const VENTANA = 3;

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) || ! $request->user()) {
            return $next($request);
        }

        $clave = 'doble-envio:' . sha1(implode('|', [
            $request->user()->getAuthIdentifier(),
            $request->method(),
            $request->path(),
            $this->huella($request),
        ]));

        if (! Cache::add($clave, true, self::VENTANA)) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => 'Eso ya se estaba guardando: no se hizo dos veces.'], 409);
            }

            return back()->with('info', 'Eso ya se estaba guardando: no se hizo dos veces.');
        }

        $respuesta = $next($request);

        // Si no se guardó nada (un error de validación), se puede reenviar al
        // tiro: quien corrige el dato no tiene que esperar.
        if ($respuesta->getStatusCode() === 422 || ($request->hasSession() && $request->session()->has('errors'))) {
            Cache::forget($clave);
        }

        return $respuesta;
    }

    /** Los datos enviados, sin lo que cambia en cada envío aunque sea lo mismo. */
    private function huella(Request $request): string
    {
        $datos = $request->except(['_token', '_method']);
        ksort($datos);

        $archivos = collect($request->allFiles())
            ->flatten()
            ->map(fn ($f) => $f->getClientOriginalName() . ':' . $f->getSize())
            ->sort()
            ->implode(',');

        return json_encode($datos) . '#' . $archivos;
    }
}
