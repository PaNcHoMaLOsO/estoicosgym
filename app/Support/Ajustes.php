<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
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
            // Con lo que se factura y se cotiza. No es lo mismo que el
            // nombre: el socio conoce «PRO GYM» y el colegio recibe un papel a
            // nombre de la sociedad, con su RUT.
            'gimnasio.razon_social' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Razón social',
                'ayuda' => 'El nombre con el que se factura. Sale en las cotizaciones de talleres y arriendos.',
                'ejemplo' => 'Progym SpA',
                'tipo' => 'texto',
                'largo' => 120,
                'defecto' => '',
            ],
            'gimnasio.rut' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'RUT de la empresa',
                'ayuda' => 'El de la sociedad, no el tuyo. Sale en las cotizaciones.',
                'ejemplo' => '77.490.649-5',
                'tipo' => 'texto',
                'largo' => 20,
                'defecto' => '',
            ],
            'gimnasio.direccion' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Dirección',
                'ayuda' => 'Calle y número, como en Google Maps. Sale en los correos, en la web y en la ficha de Google.',
                'ejemplo' => 'Colón 123',
                'tipo' => 'texto',
                'defecto' => '',
            ],
            'gimnasio.comuna' => [
                'grupo' => 'gimnasio',
                'etiqueta' => 'Comuna y región',
                'ayuda' => 'Va debajo de la dirección en las cotizaciones.',
                'ejemplo' => 'Los Ángeles, Biobío',
                'tipo' => 'texto',
                'largo' => 80,
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
            // La versión del contrato ya no se escribe aquí: la lleva el propio
            // texto (Configuración → Contrato), y sube sola al cambiar uno que
            // alguien ya firmó.
            'reglas.dias_para_firmar' => [
                'grupo' => 'reglas',
                'etiqueta' => 'Plazo para firmar el contrato por correo',
                'ayuda' => 'Cuántos días sirve el enlace que le llega al socio. Si vence sin firmar, se le manda otro desde su ficha.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 60,
                'defecto' => 7,
                'unidad' => 'días',
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

            /*
             * ============ EL DINERO EN PANTALLA ============
             *
             * QUÉ SE VE, NO QUIÉN PUEDE. Los permisos deciden a qué datos llega
             * cada usuario —recepción no recibe los ingresos y punto—; esto es
             * otra cosa: el dueño puede no querer las cifras del negocio en
             * pantalla, en ningún computador, mientras atiende o mientras hay
             * gente al otro lado del mesón.
             *
             * Va aparte del ojo de la barra de arriba, que tapa y destapa por
             * computador y para un rato. Esto es la decisión de fondo, vale
             * para todo el panel y se queda hasta que se cambie aquí.
             *
             * NO SE TOCA LO QUE HAY QUE COBRAR. En la pantalla de cobrar, la de
             * inscribir y la de anotar un fiado el precio sigue a la vista: sin
             * él no se puede atender, y esconderlo no dejaría un panel discreto
             * sino uno roto.
             *
             * SON CUATRO Y NO UNO. Esconder los importes y esconder quién debe
             * son decisiones distintas —se puede no querer ver la caja y sí
             * saber a quién cobrarle—, y dentro de cada una hay pantallas que
             * se tapan y pantallas que se cierran. Un interruptor único
             * obligaba a tragarse las cuatro cosas por querer una.
             */
            'privacidad.ocultar_montos' => [
                'grupo' => 'privacidad',
                'seccion' => 'Las cifras',
                'etiqueta' => 'Tapar los importes',
                'ayuda' => 'Los totales, lo recaudado y los saldos salen tapados (••••) en todo el panel, y el ojo de la barra de arriba deja de destaparlos. Cobrar, inscribir y anotar siguen enseñando el precio: sin eso no se puede atender.',
                'tipo' => 'si_no',
                'defecto' => '0',
            ],
            'privacidad.ocultar_caja' => [
                'grupo' => 'privacidad',
                'seccion' => 'Las cifras',
                'etiqueta' => 'Cerrar Caja y el informe de ingresos',
                'ayuda' => 'Esas dos pantallas no son otra cosa que cifras del negocio: tapadas quedarían en blanco, así que dejan de abrirse y Caja sale del menú. Quien entre volverá al resumen con el aviso de dónde se enciende otra vez.',
                'tipo' => 'si_no',
                'defecto' => '0',
            ],

            'privacidad.ocultar_fiado' => [
                'grupo' => 'privacidad',
                'seccion' => 'Quién debe',
                'etiqueta' => 'Esconder lo fiado del mesón',
                'ayuda' => 'Quita el panel de lo fiado del resumen, el aviso de la ficha del socio y su cifra en Caja. La pantalla de Fiado sigue abierta, porque es donde se cobra: sin ella la deuda no tendría forma de saldarse.',
                'tipo' => 'si_no',
                'defecto' => '0',
            ],
            'privacidad.ocultar_pendientes' => [
                'grupo' => 'privacidad',
                'seccion' => 'Quién debe',
                'etiqueta' => 'Esconder lo que deben de sus membresías',
                'ayuda' => 'Quita de todo el panel lo que dice si alguien debe: la columna de pago y el filtro «con deuda» de las listas, la cifra «Debe» y las columnas de pendiente en las fichas, «por cobrar» en Caja y en Pagos, lo que el buscador marca en rojo, y cierra el informe de pendientes. Cobrar sigue pudiéndose, pero el botón no dice cuánto.',
                'tipo' => 'si_no',
                'defecto' => '0',
            ],

            /*
              * LOS NOMBRES DEL RESUMEN.
              *
              * El resumen es la pantalla que está puesta todo el día en el
              * mesón, y sus listas son de personas: a quién se le vence la
              * membresía, quién debe una barrita, quién cumple años. Quien
              * espera su turno al otro lado del mostrador las lee enteras.
              *
              * Abreviado, quien atiende sigue sabiendo de quién habla —y la
              * foto está al lado— pero desde dos metros ya no se lee el nombre
              * completo de nadie. El nombre entero sigue en su ficha, a un clic.
              */
            'privacidad.ocultar_nombres' => [
                'grupo' => 'privacidad',
                'seccion' => 'Los nombres',
                'etiqueta' => 'Abreviar los nombres en el resumen',
                'ayuda' => 'En las listas del resumen los socios salen como «Camila R.» en vez de con su nombre completo. Es la pantalla que se ve desde el otro lado del mesón. En su ficha y en las demás pantallas el nombre sigue entero.',
                'tipo' => 'si_no',
                'defecto' => '0',
            ],

            // ---- Lo automático ----
            /*
             * EL INTERRUPTOR DE LOS CORREOS AUTOMÁTICOS.
             *
             * Apagado, el gimnasio sigue funcionando entero: lo que se corta son
             * los avisos que el sistema manda SOLO —«tu membresía vence
             * pronto», «venció»— y los envíos programados. Escribirle a un socio
             * desde su ficha o mandar un correo a un grupo a mano sigue
             * andando, porque eso lo decide una persona en ese momento.
             *
             * Hace falta un apagado de verdad para estrenar el sistema con datos
             * reales sin que a nadie le llegue un correo de prueba, y para
             * cortar en seco si algo sale mal un domingo.
             */
            /*
             * ============ EL CORREO DE SALIDA ============
             *
             * La cuenta desde la que escribe el gimnasio, cambiable desde el
             * panel. Antes vivía solo en un archivo del equipo: cambiar de
             * correo —o renovar la clave de aplicación de Gmail, que caduca—
             * obligaba a abrir el servidor, y eso no lo puede hacer quien lleva
             * el gimnasio.
             *
             * LA CLAVE SE GUARDA CIFRADA y NUNCA vuelve al navegador: la
             * pantalla solo dice si hay una guardada. Dejar el campo vacío
             * mantiene la que ya estaba; para quitarla del todo se escribe
             * «BORRAR», que es más difícil de hacer sin querer que un campo que
             * se vacía al recargar.
             */
            'correo.remitente' => [
                'grupo' => 'correo',
                'seccion' => 'Desde qué correo se escribe',
                'etiqueta' => 'Dirección',
                'ayuda' => 'La que ven los socios en «De:». Con SMTP tiene que ser la misma cuenta que se usa para conectarse, o el servidor rechaza el envío.',
                'ejemplo' => 'contacto@progym.cl',
                'tipo' => 'texto',
                'largo' => 120,
                'patron' => '/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i',
                'mensaje' => 'Revisa el correo: le falta la @ o el punto.',
                // De fábrica, lo que ya está puesto en el equipo: así la
                // pantalla no arranca vacía haciendo creer que no hay correo.
                'defecto' => (string) config('mail.from.address', ''),
            ],
            'correo.nombre_remitente' => [
                'grupo' => 'correo',
                'seccion' => 'Desde qué correo se escribe',
                'etiqueta' => 'A nombre de',
                'ayuda' => 'El nombre que aparece antes de la dirección.',
                'ejemplo' => 'PRO GYM',
                'tipo' => 'texto',
                'largo' => 60,
                'defecto' => (string) config('mail.from.name', ''),
            ],
            'correo.transporte' => [
                'grupo' => 'correo',
                'seccion' => 'Desde qué correo se escribe',
                'etiqueta' => 'Por dónde salen',
                'ayuda' => 'SMTP usa la cuenta de correo del gimnasio (Gmail corta a los 500 diarios). Resend es un servicio aparte y necesita un dominio propio verificado.',
                'tipo' => 'opciones',
                'opciones' => [
                    'smtp' => 'Servidor de correo (SMTP)',
                    'resend' => 'API de Resend',
                ],
                'defecto' => 'smtp',
            ],
            'correo.respaldo' => [
                'grupo' => 'correo',
                'seccion' => 'Desde qué correo se escribe',
                'etiqueta' => 'Si falla, reintentar por',
                'ayuda' => 'Un aviso de vencimiento que no sale es un socio que no renueva. El respaldo solo se usa si el primero falla, nunca si el correo del socio está mal escrito.',
                'tipo' => 'opciones',
                'opciones' => [
                    '' => 'Nada: se da por perdido',
                    'smtp' => 'Servidor de correo (SMTP)',
                    'resend' => 'API de Resend',
                ],
                'defecto' => '',
            ],

            'correo.smtp_host' => [
                'grupo' => 'correo',
                'seccion' => 'Servidor de correo (SMTP)',
                'etiqueta' => 'Servidor',
                'ayuda' => 'Para Gmail: smtp.gmail.com. Para Outlook: smtp.office365.com.',
                'ejemplo' => 'smtp.gmail.com',
                'tipo' => 'texto',
                'largo' => 120,
                'defecto' => (string) config('mail.mailers.smtp.host', ''),
            ],
            'correo.smtp_puerto' => [
                'grupo' => 'correo',
                'seccion' => 'Servidor de correo (SMTP)',
                'etiqueta' => 'Puerto',
                'ayuda' => '587 con TLS es lo normal. 465 es para SSL.',
                'tipo' => 'numero',
                'min' => 1,
                'max' => 65535,
                'defecto' => (int) config('mail.mailers.smtp.port', 587),
            ],
            'correo.smtp_usuario' => [
                'grupo' => 'correo',
                'seccion' => 'Servidor de correo (SMTP)',
                'etiqueta' => 'Usuario',
                'ayuda' => 'Casi siempre la dirección de correo completa.',
                'ejemplo' => 'contacto@progym.cl',
                'tipo' => 'texto',
                'largo' => 120,
                'defecto' => (string) config('mail.mailers.smtp.username', ''),
            ],
            'correo.smtp_clave' => [
                'grupo' => 'correo',
                'seccion' => 'Servidor de correo (SMTP)',
                'etiqueta' => 'Contraseña',
                'ayuda' => 'Con Gmail NO es la contraseña de la cuenta: es una «contraseña de aplicación» de 16 letras, que se saca en la configuración de seguridad de Google con la verificación en dos pasos activada.',
                'tipo' => 'secreto',
                'largo' => 255,
                'defecto' => '',
            ],

            'correo.resend_clave' => [
                'grupo' => 'correo',
                'seccion' => 'API de Resend',
                'etiqueta' => 'Clave de Resend',
                'ayuda' => 'Se saca en resend.com/api-keys. El remitente tiene que ser de un dominio verificado ahí: con una dirección @gmail.com, Resend rechaza el envío.',
                'tipo' => 'secreto',
                'largo' => 255,
                'defecto' => '',
            ],

            'tareas.correos_automaticos' => [
                'grupo' => 'tareas',
                'seccion' => 'Correos automáticos',
                'etiqueta' => 'Mandar correos automáticos',
                'ayuda' => 'Apagado, el sistema no manda ningún aviso solo. Escribirle a un socio desde su ficha o a un grupo a mano sigue funcionando.',
                'tipo' => 'si_no',
                'defecto' => '1',
            ],
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
            // La tienda de suplementos es OTRO negocio con su propia web. Aquí
            // solo se enlaza: sin dirección, el apartado no sale en la página.
            'tienda.url' => [
                'grupo' => 'web',
                'seccion' => 'Tienda de suplementos',
                'etiqueta' => 'Dirección de la tienda',
                'ayuda' => 'El enlace completo de la tienda. Vacío = el apartado no aparece en la web.',
                'ejemplo' => 'https://estoicossuplementos.cl/',
                'tipo' => 'texto',
                'formato' => 'url',
                'defecto' => '',
            ],
            'tienda.titulo' => [
                'grupo' => 'web',
                'seccion' => 'Tienda de suplementos',
                'etiqueta' => 'Nombre de la tienda',
                'ayuda' => 'El título del apartado en la página.',
                'tipo' => 'texto',
                'defecto' => 'Estoicos Suplementos',
            ],
            'tienda.texto' => [
                'grupo' => 'web',
                'seccion' => 'Tienda de suplementos',
                'etiqueta' => 'Qué se dice de la tienda',
                'ayuda' => 'Un par de frases. Sale bajo el título, antes del botón.',
                'tipo' => 'area',
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
                'descripcion' => 'Cuándo se puede renovar y cuánto dura el enlace para firmar el contrato por correo.',
            ],
            'meson' => [
                'titulo' => 'Mesón',
                'descripcion' => 'Cuándo se marcan las notas y lo fiado que llevan tiempo esperando.',
            ],
            'privacidad' => [
                'titulo' => 'Lo que se ve en pantalla',
                'descripcion' => 'Qué se enseña y qué no: los importes por un lado, las deudas por otro y los nombres de los socios por otro. No cambia quién puede entrar a cada sitio —eso son los permisos de cada usuario—, solo lo que hay a la vista.',
            ],
            'correo' => [
                'titulo' => 'Cuenta de correo',
                'descripcion' => 'Desde qué cuenta escribe el gimnasio, y por dónde salen los correos. Se cambia aquí, sin tocar el servidor.',
            ],
            'tareas' => [
                'titulo' => 'Avisos automáticos',
                'descripcion' => 'Qué hace el sistema solo: a qué hora revisa los vencimientos, cuándo manda los avisos y cuántos salen de una vez.',
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

        /*
         * Los secretos se guardan cifrados con la llave de la aplicación: una
         * contraseña de correo en texto plano en la base es una cuenta
         * regalada a quien consiga una copia del respaldo.
         *
         * Si no se puede descifrar —porque cambió APP_KEY— vale más devolver
         * vacío que el texto cifrado: con vacío el correo deja de salir y se
         * ve; con la porquería cifrada, el servidor rechazaría el acceso y
         * nadie sabría por qué.
         */
        if (($definicion['tipo'] ?? null) === 'secreto') {
            try {
                return Crypt::decryptString($guardado);
            } catch (\Throwable) {
                return $definicion['defecto'];
            }
        }

        return $definicion['tipo'] === 'numero' ? (int) $guardado : $guardado;
    }

    /**
     * Atajo para los de encendido/apagado.
     *
     * Solo un «0» apaga. Un ajuste que nunca se guardó vale lo que diga su
     * valor de fábrica, y para los correos automáticos ese valor es encendido:
     * un sistema que deja de avisar porque a nadie se le ocurrió encenderlo es
     * peor que uno que avisa de más.
     */
    public static function activo(string $clave): bool
    {
        return (string) self::obtener($clave) !== '0';
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

            if (($conocidos[$clave]['tipo'] ?? null) === 'secreto') {
                $escrito = trim((string) $valor);

                // Vacío = no se tocó: la pantalla nunca recibe la clave
                // guardada, así que un campo en blanco significa «déjala como
                // está», no «bórrala». Para quitarla se escribe BORRAR.
                if ($escrito === '') {
                    continue;
                }

                // Gmail enseña la contraseña de aplicación en cuatro bloques
                // —«abcd efgh ijkl mnop»— y así es como se copia. Con los
                // espacios la conexión falla sin decir por qué, y quien la
                // pegó bien cree que la llave no sirve. Ninguna clave de estas
                // lleva espacios de verdad, así que se quitan al guardar.
                if ($clave === 'correo.smtp_clave') {
                    $escrito = preg_replace('/\s+/', '', $escrito);
                }

                $valor = $escrito === 'BORRAR' ? '' : Crypt::encryptString($escrito);
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
