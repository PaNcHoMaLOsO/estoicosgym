<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Los ajustes del gimnasio, con sus valores por defecto.
 *
 * LO QUE ESTABA ESCRITO A MANO Y AHORA SE PUEDE CAMBIAR: el nombre del gimnasio
 * —que iba dentro de las plantillas de correo—, «se puede renovar 30 días
 * antes», «una nota lleva 3 días sin hacerse», «un fiado lleva 14 días sin
 * cobrarse» y el tope del envío masivo. Ninguno de los cinco lo podía tocar
 * nadie del gimnasio sin abrir el código.
 *
 * SE LEEN MUCHÍSIMO —el nombre del gimnasio sale en cada correo— así que van a
 * caché. Se olvida entera al guardar cualquiera: son diez filas, y mantener una
 * entrada por clave sería más código del que ahorra.
 */
class Ajustes
{
    private const CACHE = 'ajustes:todos';

    /**
     * Qué se puede ajustar, qué significa y qué vale si nadie lo tocó.
     *
     * Los valores por defecto son los que estaban escritos a mano en el código,
     * para que estrenar esto no cambie el comportamiento de nada.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function definiciones(): array
    {
        return [
            // ---- Quién es el gimnasio ----
            'gimnasio.nombre' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Nombre',
                'ayuda' => 'Sale en los correos que reciben los socios.',
                'tipo' => 'texto',
                'defecto' => 'PRO GYM',
            ],
            'gimnasio.direccion' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Dirección',
                'ayuda' => 'Para los correos y los comprobantes.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.telefono' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Teléfono',
                'ayuda' => 'El que se le da al socio para llamar.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.email' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Correo de contacto',
                'ayuda' => 'A dónde responde el socio si contesta un aviso.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.horario' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Horario',
                'ayuda' => 'Tal cual se quiere que se lea. Ej: «Lun a Vie 7:00–22:00 · Sáb 9:00–14:00».',
                'tipo' => 'texto',
                'defecto' => '',
            ],

            // ---- Cómo funciona el gimnasio ----
            'reglas.dias_para_renovar' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Renovar como mucho antes de',
                'ayuda' => 'Días antes del vencimiento en que ya se puede renovar. Antes de eso, renovar cortaría la membresía en curso y el socio perdería los días que le quedan.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 120,
                'defecto' => 30,
                'unidad' => 'días',
            ],
            'reglas.version_contrato' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Versión del contrato',
                'ayuda' => 'La que se le hace firmar hoy a quien se inscribe. Súbela cada vez que cambie el texto: es lo que permite saber después qué firmó cada socio, y quién firmó una versión vieja.',
                'tipo' => 'texto',
                'defecto' => '1',
            ],
            'reglas.dias_aviso_vencimiento' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Avisar del vencimiento',
                'ayuda' => 'Con cuántos días de antelación se le manda el aviso al socio.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 60,
                'defecto' => 7,
                'unidad' => 'días antes',
            ],

            // ---- El mesón ----
            'meson.dias_nota_vieja' => [
                'grupo' => 'meson',
                'etiqueta' => 'Marcar una nota sin hacer',
                'ayuda' => 'A los cuántos días una nota pendiente se marca para que alguien decida. Las notas NO se borran solas.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 30,
                'defecto' => 3,
                'unidad' => 'días',
            ],
            'meson.dias_fiado_viejo' => [
                'grupo' => 'meson',
                'etiqueta' => 'Marcar un fiado sin cobrar',
                'ayuda' => 'A los cuántos días una cuenta del mesón sale marcada. Una cuenta vieja no se cobra sola.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 90,
                'defecto' => 14,
                'unidad' => 'días',
            ],

            // ---- Correo ----
            'correo.tope_masivo' => [
                'grupo' => 'correo',
                'etiqueta' => 'Máximo por envío a un grupo',
                'ayuda' => 'Los correos salen uno a uno dentro de la petición: pasado cierto número el servidor corta a mitad de la lista y nadie sabe a quién le llegó. Súbelo solo si el servidor aguanta.',
                'tipo' => 'numero',
                'min' => 10,
                'max' => 500,
                'defecto' => 150,
                'unidad' => 'socios',
            ],

            // ---- La web publica ----
            'web.ciudad' => [
                'grupo' => 'web',
                'etiqueta' => 'Ciudad',
                'ayuda' => 'La que la gente escribe en Google: «gimnasio en Los Ángeles». Va en el título de la página y en la ficha que lee Google.',
                'tipo' => 'texto',
                'defecto' => 'Los Ángeles',
            ],
            'web.region' => [
                'grupo' => 'web',
                'etiqueta' => 'Región',
                'ayuda' => 'Para que Google no la confunda con Los Ángeles de California.',
                'tipo' => 'texto',
                'defecto' => 'Biobío',
            ],
            'web.google_maps' => [
                'grupo' => 'web',
                'etiqueta' => 'Enlace de Google Maps',
                'ayuda' => 'El de la ficha del gimnasio en Google Maps (Compartir → Copiar enlace). Sale como botón «Cómo llegar».',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.instagram' => [
                'grupo' => 'web',
                'etiqueta' => 'Instagram',
                'ayuda' => 'El enlace completo del perfil. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.facebook' => [
                'grupo' => 'web',
                'etiqueta' => 'Facebook',
                'ayuda' => 'El enlace completo de la página. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.google_analytics' => [
                'grupo' => 'web',
                'etiqueta' => 'Google Analytics',
                'ayuda' => 'El ID de medición, con la forma G-XXXXXXXXXX. Vacío = no se mide nada y no aparece el aviso de cookies.',
                'tipo' => 'texto',
                'patron' => '/^G-[A-Z0-9]{4,12}$/',
                'mensaje' => 'El ID de Google Analytics tiene la forma G-XXXXXXXXXX.',
                'defecto' => '',
            ],
            'web.search_console' => [
                'grupo' => 'web',
                'etiqueta' => 'Verificación de Search Console',
                'ayuda' => 'Solo el código de la etiqueta «google-site-verification», sin comillas ni el resto.',
                'tipo' => 'texto',
                'patron' => '/^[A-Za-z0-9_-]{10,100}$/',
                'mensaje' => 'Pega solo el código, sin comillas ni la etiqueta completa.',
                'defecto' => '',
            ],
            'web.whatsapp' => [
                'grupo' => 'web',
                'etiqueta' => 'WhatsApp del gimnasio',
                'ayuda' => 'Sale como un botón verde flotante en todas las páginas. Vacío = no aparece.',
                'tipo' => 'texto',
                'patron' => '/^(\+?56)?\s?9\s?[0-9]{4}\s?[0-9]{4}$/',
                'mensaje' => 'Tiene que ser un celular chileno: 9 1234 5678.',
                'defecto' => '',
            ],

            // ---- La portada ----
            'portada.titulo_1' => [
                'grupo' => 'portada',
                'etiqueta' => 'Título, primera línea',
                'ayuda' => 'En letras grandes y blancas.',
                'tipo' => 'texto',
                'defecto' => 'TRANSFORMA',
            ],
            'portada.titulo_2' => [
                'grupo' => 'portada',
                'etiqueta' => 'Título, segunda línea',
                'ayuda' => 'En letras plateadas con brillo, debajo de la primera.',
                'tipo' => 'texto',
                'defecto' => 'TU CUERPO',
            ],
            'portada.subtitulo' => [
                'grupo' => 'portada',
                'etiqueta' => 'Texto de bienvenida',
                'ayuda' => 'Una o dos frases debajo del título.',
                'tipo' => 'texto',
                'defecto' => 'Musculación, cardio y un equipo que te orienta desde el primer día. Elige tu plan y empieza hoy.',
            ],
            'portada.aviso' => [
                'grupo' => 'portada',
                'etiqueta' => 'Aviso destacado',
                'ayuda' => 'Una franja roja arriba de todas las páginas: «Este sábado cerramos a las 14:00». Vacío = no hay aviso.',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'portada.aviso_desde' => [
                'grupo' => 'portada',
                'etiqueta' => 'El aviso aparece desde',
                'ayuda' => 'Vacío = desde ya.',
                'tipo' => 'fecha',
                'defecto' => '',
            ],
            'portada.aviso_hasta' => [
                'grupo' => 'portada',
                'etiqueta' => 'El aviso aparece hasta',
                'ayuda' => 'Incluido ese día. Después se va solo.',
                'tipo' => 'fecha',
                'defecto' => '',
            ],

            // ---- El horario ----
            'horario.lunes' => [
                'grupo' => 'horario',
                'etiqueta' => 'Lunes',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.martes' => [
                'grupo' => 'horario',
                'etiqueta' => 'Martes',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.miercoles' => [
                'grupo' => 'horario',
                'etiqueta' => 'Miércoles',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.jueves' => [
                'grupo' => 'horario',
                'etiqueta' => 'Jueves',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.viernes' => [
                'grupo' => 'horario',
                'etiqueta' => 'Viernes',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.sabado' => [
                'grupo' => 'horario',
                'etiqueta' => 'Sábado',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.domingo' => [
                'grupo' => 'horario',
                'etiqueta' => 'Domingo',
                'ayuda' => 'Ej: 07:00-22:00. Con pausa: 07:00-13:00, 16:00-22:00. Vacío = cerrado.',
                'tipo' => 'texto',
                'patron' => '/^(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])(\s*,\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9]))?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ],
            'horario.nota' => [
                'grupo' => 'horario',
                'etiqueta' => 'Nota',
                'ayuda' => 'Ej: «Festivos de 9:00 a 14:00».',
                'tipo' => 'texto',
                'defecto' => '',
            ],
        ];
    }

    /** Cómo se llama cada grupo en la pantalla. */
    public static function grupos(): array
    {
        return [
            'gimnasio' => ['titulo' => 'El gimnasio', 'descripcion' => 'Lo que ve el socio en los correos'],
            'reglas' => ['titulo' => 'Reglas', 'descripcion' => 'Cómo se comportan las membresías'],
            'meson' => ['titulo' => 'Mesón', 'descripcion' => 'Las notas y lo fiado'],
            'correo' => ['titulo' => 'Correo', 'descripcion' => 'Límites de los envíos'],
            'web' => ['titulo' => 'Web', 'descripcion' => 'La página pública: cómo la encuentra Google y cómo se mide'],
            'portada' => ['titulo' => 'Portada', 'descripcion' => 'Lo primero que se lee en la página, y un aviso con fecha'],
            'horario' => ['titulo' => 'Horario', 'descripcion' => 'Día por día. Sale en la página y lo lee Google'],
        ];
    }

    /** El valor de un ajuste, o su defecto si nadie lo tocó. */
    public static function obtener(string $clave): mixed
    {
        $definicion = self::definiciones()[$clave] ?? null;

        if (! $definicion) {
            return null;
        }

        $guardado = self::todos()[$clave] ?? null;

        if ($guardado === null || $guardado === '') {
            return $definicion['defecto'];
        }

        return $definicion['tipo'] === 'numero' ? (int) $guardado : $guardado;
    }

    /** Atajo para los que son números: siempre devuelve un entero usable. */
    public static function numero(string $clave): int
    {
        return (int) self::obtener($clave);
    }

    /**
     * Guarda unos cuantos de golpe.
     *
     * Lo que no esté en las definiciones SE DESCARTA: los valores llegan de un
     * formulario, y una clave inventada acabaría en la tabla ocupando sitio y
     * sin que nada la lea nunca.
     *
     * @param array<string,mixed> $valores
     */
    public static function guardar(array $valores): void
    {
        $conocidos = self::definiciones();

        foreach ($valores as $clave => $valor) {
            if (! isset($conocidos[$clave])) {
                continue;
            }

            DB::table('ajustes')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => (string) $valor, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget(self::CACHE);
    }

    /**
     * Todo lo guardado, de una vez.
     *
     * @return array<string,string>
     */
    private static function todos(): array
    {
        return Cache::rememberForever(
            self::CACHE,
            fn () => DB::table('ajustes')->pluck('valor', 'clave')->all()
        );
    }

    /** Para las pruebas y para después de guardar. */
    public static function olvidar(): void
    {
        Cache::forget(self::CACHE);
    }
}
