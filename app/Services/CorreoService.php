<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use RuntimeException;

/**
 * Servicio central de envío de correos vía PHPMailer (SMTP).
 *
 * Todo el sistema envía correos a través de este servicio. La configuración
 * SMTP se lee de config/mail.php (y por tanto de las variables MAIL_* del .env),
 * de modo que sigue funcionando aunque la config esté cacheada (config:cache).
 */
class CorreoService
{
    /**
     * Envía un correo HTML a un destinatario.
     *
     * @param  string       $para          Email del destinatario.
     * @param  string       $asunto        Asunto del correo.
     * @param  string       $html          Cuerpo del correo en HTML.
     * @param  string|null  $nombreDestino Nombre del destinatario (opcional).
     * @return string                      Message-ID generado por PHPMailer.
     *
     * @throws RuntimeException Si el envío falla.
     */
    public function enviar(string $para, string $asunto, string $html, ?string $nombreDestino = null): string
    {
        $mail = new PHPMailer(true);

        try {
            // ── Transporte SMTP ──────────────────────────────────────────
            $mail->isSMTP();
            $mail->Host       = (string) config('mail.mailers.smtp.host');
            $mail->Port       = (int) config('mail.mailers.smtp.port');
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) config('mail.mailers.smtp.username');
            $mail->Password   = (string) config('mail.mailers.smtp.password');
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;
            $mail->Encoding   = PHPMailer::ENCODING_BASE64;

            // Cifrado: 465 = SSL/SMTPS, resto (587, 25) = STARTTLS.
            $mail->SMTPSecure = $mail->Port === 465
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            // ── Remitente y destinatario ─────────────────────────────────
            $mail->setFrom(
                (string) config('mail.from.address'),
                (string) config('mail.from.name')
            );
            $mail->addAddress($para, (string) $nombreDestino);

            // ── Contenido ────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $html;
            $mail->AltBody = trim(strip_tags($html));

            $mail->send();

            return $mail->getLastMessageID();
        } catch (PHPMailerException $e) {
            // Re-lanzamos con el detalle de PHPMailer para que el llamador
            // lo registre/maneje como ya lo hace con cualquier \Exception.
            throw new RuntimeException(
                'Error al enviar correo: ' . $mail->ErrorInfo,
                previous: $e
            );
        }
    }
}
