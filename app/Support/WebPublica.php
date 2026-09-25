<?php

namespace App\Support;

use App\Models\Membresia;

/**
 * Lo que dice la página de inicio en Google: el título y la descripción.
 *
 * Aparte porque lo usan dos: la web, que lo pone en su <head>, y
 * Configuración, que enseña cómo se va a ver en los resultados antes de
 * guardar. Si cada uno lo armara por su cuenta, la vista previa mentiría el
 * día que cambie uno solo.
 */
class WebPublica
{
    /** «PRO GYM | Gimnasio en Los Ángeles, Biobío». */
    public static function tituloDeInicio(): string
    {
        $nombre = Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM';
        $ciudad = trim((string) Ajustes::obtener('web.ciudad'));
        $region = trim((string) Ajustes::obtener('web.region'));

        return $nombre . ($ciudad ? " | Gimnasio en {$ciudad}" . ($region ? ", {$region}" : '') : '');
    }

    /** La que se arma sola: dónde está, qué hay y desde cuánto. */
    public static function descripcionAutomatica(?int $desde): string
    {
        $ciudad = trim((string) Ajustes::obtener('web.ciudad'));
        $donde = $ciudad ? "Gimnasio en {$ciudad}" : 'Gimnasio';

        return "{$donde}: musculación, cardio y orientación en sala."
            . ($desde !== null ? ' Planes desde ' . self::pesos($desde) . '.' : '')
            . ' Revisa los precios y consulta tu membresía en línea.';
    }

    /** La que se usa: la escrita en Configuración o, si no hay, la automática. */
    public static function descripcion(?int $desde): string
    {
        $escrita = trim((string) Ajustes::obtener('web.descripcion'));

        return $escrita !== '' ? $escrita : self::descripcionAutomatica($desde);
    }

    /**
     * La mensualidad más barata que se anuncia en la web hoy.
     *
     * Con el precio VIGENTE de cada plan, igual que la página de planes: un
     * precio viejo que sigue activo en la tabla no cuenta.
     */
    public static function precioDesde(): ?int
    {
        // Igual que la web: solo los planes que salen en ella y sin los pases
        // de días. Contándolos, la vista previa de Google en Configuración
        // decía un «desde» que la página no decía.
        $precio = Membresia::where('activo', true)
            ->where('en_la_web', true)
            ->where('duracion_meses', '>=', 1)
            ->with(['precios' => fn ($q) => $q->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderByDesc('fecha_vigencia_desde')])
            ->get()
            ->map(fn (Membresia $m) => $m->precios->first()?->precio_normal)
            ->filter(fn ($p) => $p !== null)
            ->min();

        return $precio === null ? null : (int) $precio;
    }

    public static function pesos(int $monto): string
    {
        return '$' . number_format($monto, 0, ',', '.');
    }
}
