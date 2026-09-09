<?php

namespace App\Services;

use App\Services\Correo\Transporte;
use App\Services\Correo\TransporteResend;
use App\Services\Correo\TransporteSmtp;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio central de envío de correos.
 *
 * Todo el sistema envía por aquí: las notificaciones automáticas, los avisos
 * manuales del panel y los códigos de verificación. Por eso la firma de
 * enviar() no cambia aunque por debajo cambie el transporte.
 *
 * QUE HAY DEBAJO. Un transporte SMTP (PHPMailer, que ya venía instalado) y otro
 * contra la API de Resend. Cual se usa lo dice config/correo.php, y se puede
 * declarar un respaldo: si el principal falla, se reintenta por el otro antes
 * de dar el envío por perdido.
 *
 * POR QUE UN RESPALDO. El SMTP de Gmail corta a los 500 correos diarios y
 * depende de una cuenta con verificación en dos pasos y una clave de
 * aplicación. Un aviso de vencimiento que no sale es un socio que no renueva.
 */
class CorreoService
{
    private ?Transporte $principal = null;
    private ?Transporte $respaldo = null;

    /**
     * Envía un correo HTML a un destinatario.
     *
     * @param  string       $para          Email del destinatario.
     * @param  string       $asunto        Asunto del correo.
     * @param  string       $html          Cuerpo del correo en HTML.
     * @param  string|null  $nombreDestino Nombre del destinatario (opcional).
     * @return string                      Identificador que devuelve el proveedor.
     *
     * @throws RuntimeException Si el envío falla por todas las vías.
     */
    public function enviar(string $para, string $asunto, string $html, ?string $nombreDestino = null): string
    {
        if ($this->esDireccionReservada($para)) {
            throw new RuntimeException(
                "«{$para}» es una dirección de ejemplo y no existe. No se envía para no acumular rebotes."
            );
        }

        try {
            return $this->principal()->enviar($para, $asunto, $html, $nombreDestino);
        } catch (RuntimeException $e) {
            $respaldo = $this->respaldo();

            if ($respaldo === null) {
                throw $e;
            }

            // Se deja constancia del primer fallo aunque el respaldo funcione:
            // si no, un SMTP caído durante días pasa inadvertido porque los
            // correos «siguen saliendo».
            Log::warning('Falló el envío por {via}; se reintenta por el respaldo.', [
                'via' => $this->principal()->descripcion(),
                'error' => $e->getMessage(),
                'destinatario' => $para,
            ]);

            return $respaldo->enviar($para, $asunto, $html, $nombreDestino);
        }
    }

    /**
     * Suelta lo que los transportes tengan abierto.
     *
     * Se llama al terminar una tanda de envíos. Quien mande de uno en uno no
     * necesita llamarlo.
     */
    public function cerrar(): void
    {
        $this->principal?->cerrar();
        $this->respaldo?->cerrar();
    }

    /** Comprueba una vía SIN enviar nada. Devuelve null si está bien. */
    public function comprobar(?string $nombre = null): ?string
    {
        return $this->crear($nombre ?? $this->nombrePrincipal())->comprobar();
    }

    public function descripcion(?string $nombre = null): string
    {
        return $this->crear($nombre ?? $this->nombrePrincipal())->descripcion();
    }

    public function nombrePrincipal(): string
    {
        return (string) config('correo.transporte', 'smtp');
    }

    public function nombreRespaldo(): ?string
    {
        $nombre = config('correo.respaldo');

        return $nombre ? (string) $nombre : null;
    }

    /**
     * ¿Es una dirección que por definición no existe?
     *
     * Los dominios y extensiones de la RFC 2606 están reservados justo para
     * ejemplos y pruebas: nadie tiene un buzón ahí y todo lo que se mande
     * rebota.
     *
     * Importa porque la base se puebla con datos de prueba cuyos socios llevan
     * correos @example.net. Bastaba con generar las notificaciones automáticas y
     * lanzar el envío para disparar decenas de rebotes desde la cuenta real del
     * gimnasio, y los rebotes son lo que hace que Gmail acabe mandando tus
     * correos a spam.
     */
    private function esDireccionReservada(string $direccion): bool
    {
        $dominio = strtolower(substr(strrchr($direccion, '@') ?: '', 1));

        if ($dominio === '') {
            return false;
        }

        if (in_array($dominio, ['example.com', 'example.net', 'example.org'], true)) {
            return true;
        }

        foreach (['.test', '.example', '.invalid', '.localhost'] as $extension) {
            if (str_ends_with($dominio, $extension)) {
                return true;
            }
        }

        return false;
    }

    private function principal(): Transporte
    {
        return $this->principal ??= $this->crear($this->nombrePrincipal());
    }

    private function respaldo(): ?Transporte
    {
        $nombre = $this->nombreRespaldo();

        if ($nombre === null || $nombre === $this->nombrePrincipal()) {
            return null;
        }

        return $this->respaldo ??= $this->crear($nombre);
    }

    private function crear(string $nombre): Transporte
    {
        $timeout = (int) config('correo.timeout', 15);
        $remitente = (string) config('mail.from.address');
        $nombreRemitente = (string) config('mail.from.name');

        return match ($nombre) {
            'smtp' => new TransporteSmtp(
                host: (string) config('mail.mailers.smtp.host'),
                puerto: (int) config('mail.mailers.smtp.port'),
                usuario: (string) config('mail.mailers.smtp.username'),
                clave: (string) config('mail.mailers.smtp.password'),
                remitente: $remitente,
                nombreRemitente: $nombreRemitente,
                timeout: $timeout,
            ),
            'resend' => new TransporteResend(
                clave: (string) config('correo.resend.key'),
                remitente: $remitente,
                nombreRemitente: $nombreRemitente,
                timeout: $timeout,
            ),
            default => throw new InvalidArgumentException(
                "Transporte de correo desconocido: «{$nombre}». Usa 'smtp' o 'resend' en MAIL_TRANSPORTE."
            ),
        };
    }
}
