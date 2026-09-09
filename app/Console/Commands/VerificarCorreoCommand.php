<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

/**
 * Comprueba la configuracion SMTP SIN enviar ningun correo.
 *
 * Existe aparte de `test:email` a proposito: aquel manda un mensaje de verdad,
 * y para saber si la clave quedo bien puesta no hace falta molestar a nadie ni
 * gastar cuota. Solo abre la conexion, autentica y cuelga.
 */
class VerificarCorreoCommand extends Command
{
    protected $signature = 'correo:verificar';

    protected $description = 'Verifica la conexion y la clave SMTP sin enviar correos';

    public function handle(): int
    {
        $host = (string) config('mail.mailers.smtp.host');
        $puerto = (int) config('mail.mailers.smtp.port');
        $usuario = (string) config('mail.mailers.smtp.username');
        $clave = (string) config('mail.mailers.smtp.password');

        $this->line("Servidor : {$host}:{$puerto}");
        $this->line("Cuenta   : {$usuario}");

        if ($clave === '' || str_starts_with($clave, 'AQUI_TU_')) {
            $this->newLine();
            $this->error('MAIL_PASSWORD sigue con el valor de ejemplo.');
            $this->line('Pon la contrasena de aplicacion de 16 caracteres en el .env y repite.');

            return self::FAILURE;
        }

        // Gmail entrega la clave en 4 bloques de 4 separados por espacios y hay
        // que pegarla SIN ellos: con espacios la autenticacion falla y el error
        // que devuelve el servidor no dice por que.
        if (preg_match('/\s/', $clave)) {
            $this->newLine();
            $this->error('La clave tiene espacios. Gmail la muestra en bloques de 4, pero se pega junta.');

            return self::FAILURE;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $puerto;
        $mail->SMTPAuth = true;
        $mail->Username = $usuario;
        $mail->Password = $clave;
        $mail->SMTPSecure = $puerto === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout = 15;

        $this->newLine();

        try {
            if ($mail->smtpConnect()) {
                $mail->smtpClose();

                $this->info('Conexion y autenticacion correctas. El envio de correos esta operativo.');

                return self::SUCCESS;
            }
        } catch (Throwable $e) {
            $this->error('No se pudo autenticar: ' . $e->getMessage());

            if ($mail->ErrorInfo !== '') {
                $this->line('Detalle: ' . $mail->ErrorInfo);
            }

            $this->newLine();
            $this->line('Si la clave es correcta, revisa que la cuenta tenga la verificacion');
            $this->line('en 2 pasos activada: sin ella Google no emite contrasenas de aplicacion.');

            return self::FAILURE;
        }

        $this->error('No se pudo conectar con el servidor.');

        return self::FAILURE;
    }
}
