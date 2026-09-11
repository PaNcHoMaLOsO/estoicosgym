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
 * cobrarse», el tope del envío masivo y las horas de las tareas automáticas.
 * Nada de eso lo podía tocar nadie del gimnasio sin abrir el código.
 *
 * UN AJUSTE QUE NADIE LEE NO SE DEFINE. «Avisar del vencimiento, 7 días» vivió
 * aquí sin que ningún código lo mirara —los avisos usan los días de su
 * plantilla de correo— y quien lo cambiaba se quedaba creyendo que hizo algo.
 * Lo mismo el horario en texto libre, que reemplazó el horario día por día.
 *
 * SE LEEN MUCHÍSIMO —el nombre del gimnasio sale en cada correo— así que van a
 * caché. Se olvida entera al guardar cualquiera: son pocas filas, y mantener
 * una entrada por clave sería más código del que ahorra.
 *
 * Los tipos: «texto» (una línea, hasta `largo`), «area» (varias líneas),
 * «numero» (entero entre `min` y `max`), «fecha» (AAAA-MM-DD) y «hora» (HH:MM).
 * `seccion` agrupa los campos de un tema largo bajo un subtítulo.
 */
class Ajustes
{
    private const CACHE = 'ajustes:todos';

    /** Un tramo de horario: 07:00-22:00. */
    private const TRAMO = '(([01]?[0-9]|2[0-3]):[0-5][0-9])\s*-\s*(([01]?[0-9]|2[0-3]):[0-5][0-9])';

    private const DIAS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

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
        static $definiciones = null;

        return $definiciones ??= [
            // ---- Quién es el gimnasio ----
            'gimnasio.nombre' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Nombre',
                'ayuda' => 'Sale en los correos, en la página web y en Google.',
                'tipo' => 'texto',
                'largo' => 80,
                'defecto' => 'PRO GYM',
            ],
            'gimnasio.direccion' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Dirección',
                'ayuda' => 'Calle y número, como en Google Maps. Sale en los correos, en la web y en la ficha de Google.',
                'ejemplo' => 'Colón 123',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.telefono' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Teléfono',
                'ayuda' => 'El que se le da al socio para llamar. Sale en la web y en Google.',
                'ejemplo' => '+56 43 212 3456',
                'tipo' => 'texto',
                'largo' => 30,
                'defecto' => '',
            ],
            'gimnasio.email' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Correo de contacto',
                'ayuda' => 'A dónde responde el socio si contesta un aviso, y a dónde llegan los mensajes de la web.',
                'ejemplo' => 'contacto@progym.cl',
                'tipo' => 'texto',
                'patron' => '/^[^@\s]+@[^@\s]+\.[^@\s]+$/',
                'mensaje' => 'Revisa el correo: le falta la @ o el punto.',
                'defecto' => '',
            ],

            // ---- El horario, día por día ----
            ...self::horario(),

            // ---- Cómo funcionan las membresías ----
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
                'largo' => 20,
                'defecto' => '1',
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
                'ayuda' => 'A los cuántos días una cuenta del mesón sale marcada para insistir. Una cuenta vieja no se cobra sola.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 90,
                'defecto' => 14,
                'unidad' => 'días',
            ],

            // ---- Lo automático ----
            'tareas.hora_revision' => [
                'grupo' => 'tareas',
                'seccion' => 'A qué hora',
                'etiqueta' => 'Revisar vencimientos y pagos',
                'ayuda' => 'Marca las membresías vencidas, pone al día los pagos y desactiva a quien quedó sin membresía. Tiene que ser una hora en que el computador del mesón esté prendido.',
                'tipo' => 'hora',
                'defecto' => '01:00',
            ],
            'tareas.hora_avisos' => [
                'grupo' => 'tareas',
                'seccion' => 'A qué hora',
                'etiqueta' => 'Mandar los avisos por correo',
                'ayuda' => 'Los de «tu membresía vence pronto» y «venció», y los envíos programados para ese día.',
                'tipo' => 'hora',
                'defecto' => '08:00',
            ],
            'tareas.hora_reintento' => [
                'grupo' => 'tareas',
                'seccion' => 'A qué hora',
                'etiqueta' => 'Reintentar los que fallaron',
                'ayuda' => 'Una segunda oportunidad para los correos que no salieron en la mañana.',
                'tipo' => 'hora',
                'defecto' => '14:00',
            ],
            'correo.tope_masivo' => [
                'grupo' => 'tareas',
                'seccion' => 'Envíos a un grupo',
                'etiqueta' => 'Máximo por envío a un grupo',
                'ayuda' => 'Los correos salen uno a uno dentro de la petición: pasado cierto número el servidor corta a mitad de la lista y nadie sabe a quién le llegó. Súbelo solo si el servidor aguanta.',
                'tipo' => 'numero',
                'min' => 10,
                'max' => 500,
                'defecto' => 150,
                'unidad' => 'socios',
            ],

            // ---- La portada de la web ----
            'portada.titulo_1' => [
                'grupo' => 'portada',
                'seccion' => 'El título',
                'etiqueta' => 'Primera línea',
                'ayuda' => 'En letras grandes y blancas.',
                'tipo' => 'texto',
                'largo' => 30,
                'defecto' => 'TRANSFORMA',
            ],
            'portada.titulo_2' => [
                'grupo' => 'portada',
                'seccion' => 'El título',
                'etiqueta' => 'Segunda línea',
                'ayuda' => 'En letras plateadas con brillo, debajo de la primera.',
                'tipo' => 'texto',
                'largo' => 30,
                'defecto' => 'TU CUERPO',
            ],
            'portada.subtitulo' => [
                'grupo' => 'portada',
                'seccion' => 'El título',
                'etiqueta' => 'Texto de bienvenida',
                'ayuda' => 'Una o dos frases debajo del título.',
                'tipo' => 'area',
                'largo' => 220,
                'defecto' => 'Musculación, cardio y un equipo que te orienta desde el primer día. Elige tu plan y empieza hoy.',
            ],
            'portada.aviso' => [
                'grupo' => 'portada',
                'seccion' => 'Aviso destacado',
                'etiqueta' => 'Aviso',
                'ayuda' => 'Una franja roja arriba de todas las páginas: «Este sábado cerramos a las 14:00». Vacío = no hay aviso.',
                'tipo' => 'texto',
                'largo' => 160,
                'defecto' => '',
            ],
            'portada.aviso_desde' => [
                'grupo' => 'portada',
                'seccion' => 'Aviso destacado',
                'etiqueta' => 'Aparece desde',
                'ayuda' => 'Vacío = desde ya.',
                'tipo' => 'fecha',
                'defecto' => '',
            ],
            'portada.aviso_hasta' => [
                'grupo' => 'portada',
                'seccion' => 'Aviso destacado',
                'etiqueta' => 'Aparece hasta',
                'ayuda' => 'Incluido ese día. Después se va solo.',
                'tipo' => 'fecha',
                'defecto' => '',
            ],

            // ---- Google y las redes ----
            'web.ciudad' => [
                'grupo' => 'web',
                'seccion' => 'Dónde está',
                'etiqueta' => 'Ciudad',
                'ayuda' => 'La que la gente escribe en Google: «gimnasio en Los Ángeles». Va en el título de la página y en la ficha que lee Google.',
                'tipo' => 'texto',
                'defecto' => 'Los Ángeles',
            ],
            'web.region' => [
                'grupo' => 'web',
                'seccion' => 'Dónde está',
                'etiqueta' => 'Región',
                'ayuda' => 'Para que Google no la confunda con Los Ángeles de California.',
                'tipo' => 'texto',
                'defecto' => 'Biobío',
            ],
            'web.comunas' => [
                'grupo' => 'web',
                'seccion' => 'Dónde está',
                'etiqueta' => 'Comunas cercanas',
                'ayuda' => 'Separadas por coma. Le dice a Google que también atiendes a quien vive ahí.',
                'ejemplo' => 'Nacimiento, Mulchén, Santa Bárbara',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'web.coordenadas' => [
                'grupo' => 'web',
                'seccion' => 'Dónde está',
                'etiqueta' => 'Ubicación exacta',
                'ayuda' => 'En Google Maps, clic derecho sobre el gimnasio: son los números de arriba del menú. Con ellos Google lo ubica en el mapa sin adivinar.',
                'ejemplo' => '-37.46973, -72.35366',
                'tipo' => 'texto',
                'patron' => '/^-?\d{1,2}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/',
                'mensaje' => 'Pégalas como salen en Google Maps: -37.46973, -72.35366.',
                'defecto' => '',
            ],
            'web.google_maps' => [
                'grupo' => 'web',
                'seccion' => 'Dónde está',
                'etiqueta' => 'Enlace de Google Maps',
                'ayuda' => 'El de la ficha del gimnasio en Google Maps (Compartir → Copiar enlace). Sale como botón «Cómo llegar».',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.descripcion' => [
                'grupo' => 'web',
                'seccion' => 'Cómo se ve en Google',
                'etiqueta' => 'Descripción para Google',
                'ayuda' => 'El texto que sale bajo el título en los resultados. Google muestra unos 155 caracteres. Vacío = se arma solo con la ciudad y los precios.',
                'tipo' => 'area',
                'largo' => 300,
                'defecto' => '',
            ],
            'web.resenas' => [
                'grupo' => 'web',
                'seccion' => 'Cómo se ve en Google',
                'etiqueta' => 'Enlace para dejar una reseña',
                'ayuda' => 'En tu Perfil de Empresa de Google: «Pedir reseñas» → copiar el enlace. Sale como botón en la web. Las reseñas son lo que más ayuda a salir primero en el mapa.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.whatsapp' => [
                'grupo' => 'web',
                'seccion' => 'Redes y contacto',
                'etiqueta' => 'WhatsApp del gimnasio',
                'ayuda' => 'Sale como un botón verde flotante en todas las páginas. Vacío = no aparece.',
                'ejemplo' => '9 1234 5678',
                'tipo' => 'texto',
                'patron' => '/^(\+?56)?\s?9\s?[0-9]{4}\s?[0-9]{4}$/',
                'mensaje' => 'Tiene que ser un celular chileno: 9 1234 5678.',
                'defecto' => '',
            ],
            'web.instagram' => [
                'grupo' => 'web',
                'seccion' => 'Redes y contacto',
                'etiqueta' => 'Instagram',
                'ayuda' => 'El enlace completo del perfil. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.facebook' => [
                'grupo' => 'web',
                'seccion' => 'Redes y contacto',
                'etiqueta' => 'Facebook',
                'ayuda' => 'El enlace completo de la página. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.tiktok' => [
                'grupo' => 'web',
                'seccion' => 'Redes y contacto',
                'etiqueta' => 'TikTok',
                'ayuda' => 'El enlace completo del perfil. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.youtube' => [
                'grupo' => 'web',
                'seccion' => 'Redes y contacto',
                'etiqueta' => 'YouTube',
                'ayuda' => 'El enlace completo del canal. Vacío = no se muestra.',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'web.google_analytics' => [
                'grupo' => 'web',
                'seccion' => 'Medición y verificación',
                'etiqueta' => 'Google Analytics',
                'ayuda' => 'El ID de medición, con la forma G-XXXXXXXXXX. Vacío = no se mide nada y no aparece el aviso de cookies.',
                'tipo' => 'texto',
                'patron' => '/^G-[A-Z0-9]{4,12}$/',
                'mensaje' => 'El ID de Google Analytics tiene la forma G-XXXXXXXXXX.',
                'defecto' => '',
            ],
            'web.search_console' => [
                'grupo' => 'web',
                'seccion' => 'Medición y verificación',
                'etiqueta' => 'Verificación de Search Console',
                'ayuda' => 'Solo el código de la etiqueta «google-site-verification», sin comillas ni el resto.',
                'tipo' => 'texto',
                'patron' => '/^[A-Za-z0-9_-]{10,100}$/',
                'mensaje' => 'Pega solo el código, sin comillas ni la etiqueta completa.',
                'defecto' => '',
            ],
            'web.bing' => [
                'grupo' => 'web',
                'seccion' => 'Medición y verificación',
                'etiqueta' => 'Verificación de Bing',
                'ayuda' => 'Opcional. El código de la etiqueta «msvalidate.01» de Bing Webmaster Tools, sin comillas.',
                'tipo' => 'texto',
                'patron' => '/^[A-Za-z0-9]{16,64}$/',
                'mensaje' => 'Pega solo el código, sin comillas ni la etiqueta completa.',
                'defecto' => '',
            ],
        ];
    }

    /**
     * Cómo se llama cada tema y qué hay adentro. El orden es el de la pantalla.
     *
     * @return array<string,array{titulo:string, descripcion:string}>
     */
    public static function grupos(): array
    {
        return [
            'gimnasio' => [
                'titulo' => 'Datos del gimnasio',
                'descripcion' => 'Cómo se llama, dónde está y cómo se le escribe. Sale en los correos, en la página web y en Google.',
            ],
            'horario' => [
                'titulo' => 'Horario',
                'descripcion' => 'Día por día: 07:00-22:00, o con pausa al mediodía: 07:00-13:00, 16:00-22:00. Vacío = cerrado. Sale en la página web y lo lee Google.',
            ],
            'reglas' => [
                'titulo' => 'Reglas de las membresías',
                'descripcion' => 'Cuándo se puede renovar y qué contrato firma quien se inscribe.',
            ],
            'meson' => [
                'titulo' => 'Mesón',
                'descripcion' => 'Cuándo se marcan las notas y lo fiado que llevan tiempo esperando.',
            ],
            'tareas' => [
                'titulo' => 'Correos y tareas automáticas',
                'descripcion' => 'A qué hora corre lo automático y cuántos correos salen de una vez.',
            ],
            'portada' => [
                'titulo' => 'Portada y aviso',
                'descripcion' => 'Lo primero que se lee en la página web, y un aviso con fecha que se quita solo.',
            ],
            'web' => [
                'titulo' => 'Google y redes',
                'descripcion' => 'Cómo encuentra Google al gimnasio, sus redes sociales y cómo se miden las visitas.',
            ],
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
     * Los siete días y la nota del horario.
     *
     * @return array<string,array<string,mixed>>
     */
    private static function horario(): array
    {
        $horario = [];

        foreach (self::DIAS as $clave => $nombre) {
            $horario["horario.{$clave}"] = [
                'grupo' => 'horario',
                'etiqueta' => $nombre,
                'ejemplo' => '07:00-22:00',
                'tipo' => 'texto',
                'patron' => '/^' . self::TRAMO . '(\s*,\s*' . self::TRAMO . ')?$/',
                'mensaje' => 'Escríbelo como 07:00-22:00, o con pausa: 07:00-13:00, 16:00-22:00.',
                'defecto' => '',
            ];
        }

        $horario['horario.nota'] = [
            'grupo' => 'horario',
            'etiqueta' => 'Nota',
            'ayuda' => 'Sale debajo del horario.',
            'ejemplo' => 'Festivos de 9:00 a 14:00',
            'tipo' => 'texto',
            'largo' => 120,
            'defecto' => '',
        ];

        return $horario;
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
