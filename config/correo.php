<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Por dónde salen los correos
    |--------------------------------------------------------------------------
    |
    | 'smtp'   → PHPMailer contra el servidor de MAIL_HOST (Gmail hoy).
    | 'resend' → la API de Resend.
    |
    | El sistema entero envía a través de App\Services\CorreoService, así que
    | cambiar esto cambia el envío de todo: notificaciones automáticas, avisos
    | manuales y los códigos de verificación.
    |
    */

    'transporte' => env('MAIL_TRANSPORTE', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Respaldo
    |--------------------------------------------------------------------------
    |
    | Si el principal falla, se reintenta por aquí. Vacío = sin respaldo, y el
    | fallo se propaga como siempre.
    |
    | Para qué sirve: el SMTP de Gmail corta a los 500 correos diarios y depende
    | de una cuenta con verificación en dos pasos. Un aviso de vencimiento que no
    | sale es un socio que no renueva, así que conviene tener una segunda vía.
    |
    | El respaldo NO se usa cuando el correo del destinatario está mal escrito:
    | eso fallaría igual por los dos lados.
    |
    */

    'respaldo' => env('MAIL_TRANSPORTE_RESPALDO'),

    /*
    |--------------------------------------------------------------------------
    | Resend
    |--------------------------------------------------------------------------
    |
    | La clave se saca de https://resend.com/api-keys y el remitente tiene que
    | ser de un dominio verificado en su panel: con una dirección @gmail.com
    | Resend rechaza el envío.
    |
    */

    'resend' => [
        'key' => env('RESEND_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Espera máxima, en segundos
    |--------------------------------------------------------------------------
    |
    | PHPMailer espera 300 s por defecto: un servidor que no contesta dejaba
    | colgada la petición —y al usuario mirando la pantalla— cinco minutos.
    |
    */

    'timeout' => (int) env('MAIL_TIMEOUT', 15),

];
