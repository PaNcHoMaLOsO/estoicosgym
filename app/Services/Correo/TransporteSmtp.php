<?php

namespace App\Services\Correo;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Salida por SMTP con PHPMailer.
 *
 * REUSA LA CONEXION. Antes se creaba un PHPMailer nuevo en cada envio, asi que
 * una tanda de cien notificaciones abria y cerraba cien sesiones SMTP: cada una
 * son varios viajes de ida y vuelta mas el saludo TLS, y Gmail acaba cortando
 * por exceso de conexiones. Con SMTPKeepAlive la sesion se mantiene y los
 * correos salen uno detras de otro por el mismo canal; `cerrar()` la suelta al
 * final de la tanda.
 */
class TransporteSmtp implements Transporte
{
    private ?PHPMailer $mail = null;

    public function __construct(
        private readonly string $host,
        private readonly int $puerto,
        private readonly string $usuario,
        private readonly string $clave,
        private readonly string $remitente,
        private readonly string $nombreRemitente,
        private readonly int $timeout = 15,
        // A dónde llega la respuesta si el socio pulsa «Responder». Puede no
        // ser la cuenta que envía: se manda desde una y se atiende en otra.
        private readonly ?string $responderA = null,
    ) {}

    public function enviar(string $para, string $asunto, string $html, ?string $nombreDestino = null): string
    {
        $mail = $this->conexion();

        try {
            // Los destinatarios del envio anterior siguen puestos: sin esto, el
            // segundo correo de una tanda le llegaria tambien al primero.
            $mail->clearAddresses();
            $mail->addAddress($para, (string) $nombreDestino);

            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->AltBody = trim(strip_tags($html));

            $mail->send();

            return $mail->getLastMessageID();
        } catch (PHPMailerException $e) {
            // Si la sesion quedo en mal estado, la siguiente llamada abre otra.
            $this->cerrar();

            throw new RuntimeException('Error al enviar correo: ' . $mail->ErrorInfo, previous: $e);
        }
    }

    public function comprobar(): ?string
    {
        if ($this->clave === '' || str_starts_with($this->clave, 'AQUI_TU_')) {
            return 'MAIL_PASSWORD sigue con el valor de ejemplo.';
        }

        // Gmail entrega la clave de aplicacion en cuatro bloques de cuatro y hay
        // que pegarla SIN los espacios: con ellos la autenticacion falla y el
        // servidor no dice por que.
        if (preg_match('/\s/', $this->clave)) {
            return 'La clave tiene espacios. Gmail la muestra en bloques de 4, pero se pega junta.';
        }

        try {
            $mail = $this->conexion();

            if (! $mail->smtpConnect()) {
                return 'No se pudo conectar con el servidor.';
            }

            $mail->smtpClose();

            return null;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function descripcion(): string
    {
        return "SMTP ({$this->host}:{$this->puerto})";
    }

    public function cerrar(): void
    {
        $this->mail?->smtpClose();
        $this->mail = null;
    }

    private function conexion(): PHPMailer
    {
        if ($this->mail !== null) {
            return $this->mail;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->host;
        $mail->Port = $this->puerto;
        $mail->SMTPAuth = true;
        $mail->Username = $this->usuario;
        $mail->Password = $this->clave;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;

        // Sin esto PHPMailer espera hasta 300 s. Un SMTP que no contesta dejaba
        // colgada la peticion entera —y con ella al usuario— cinco minutos.
        $mail->Timeout = $this->timeout;

        // 465 es SSL directo; 587 y 25 negocian TLS despues del saludo.
        $mail->SMTPSecure = $this->puerto === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->SMTPKeepAlive = true;

        $mail->setFrom($this->remitente, $this->nombreRemitente);

        if ($this->responderA && strcasecmp($this->responderA, $this->remitente) !== 0) {
            $mail->addReplyTo($this->responderA, $this->nombreRemitente);
        }

        $mail->isHTML(true);

        return $this->mail = $mail;
    }

    public function __destruct()
    {
        $this->cerrar();
    }
}
