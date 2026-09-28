<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * La dirección con la que se cuentan los intentos (login, consultas, contacto).
 *
 * CON IPv6 SE CUENTA LA RED, NO LA DIRECCIÓN. Una conexión de casa o un
 * servidor arrendado trae un bloque /64 entero: millones de direcciones. Si
 * se cuenta cada una por separado, quien cambia la última parte en cada
 * intento no se frena nunca. Con IPv4 se cuenta la dirección, como siempre.
 */
class IpDelCliente
{
    public static function paraLimitar(?Request $request = null): string
    {
        $ip = (string) ($request ?? request())->ip();

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }

        $binaria = inet_pton($ip);

        return $binaria === false
            ? $ip
            : inet_ntop(substr($binaria, 0, 8) . str_repeat("\0", 8)) . '/64';
    }
}
