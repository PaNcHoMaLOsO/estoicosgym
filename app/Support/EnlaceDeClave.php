<?php

namespace App\Support;

use App\Models\User;
use App\Services\CorreoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * El enlace para poner o cambiar la contraseña de una cuenta del panel.
 *
 * Lo usan dos puertas: «¿Olvidaste tu contraseña?» y el administrador desde
 * Configuración → Usuarios. Vive aparte para que las dos hagan lo mismo —un
 * token nuevo, guardado hasheado, que caduca a la hora— y ninguna quede más
 * débil que la otra.
 */
class EnlaceDeClave
{
    /**
     * Crea un enlace nuevo; el anterior deja de valer.
     *
     * El token viaja EN CLARO en el enlace y se guarda hasheado: quien lea la
     * tabla no puede armar el enlace con lo que hay ahí.
     */
    public static function crear(User $usuario): string
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $usuario->email],
            ['email' => $usuario->email, 'token' => bcrypt($token), 'created_at' => now()]
        );

        return route('password.reset', ['token' => $token, 'email' => $usuario->email]);
    }

    /**
     * Lo manda por correo.
     *
     * Si el correo no sale, LANZA: quien llama decide qué decir. La pantalla de
     * «olvidé mi contraseña» responde lo mismo pase lo que pase; el
     * administrador, en cambio, necesita saber que no llegó.
     */
    public static function mandar(User $usuario, string $enlace): void
    {
        $salio = app(CorreoService::class)->enviar(
            $usuario->email,
            'Restablece tu contraseña · ' . (Ajustes::obtener('gimnasio.nombre') ?: 'PRO GYM'),
            view('emails.reset-password', ['enlace' => $enlace, 'nombre' => $usuario->name])->render(),
            $usuario->name,
        );

        if ($salio === false) {
            throw new \RuntimeException("El correo para {$usuario->email} no salió.");
        }
    }
}
