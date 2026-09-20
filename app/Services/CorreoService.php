<?php

namespace App\Services;

use App\Services\Correo\Transporte;
use App\Services\Correo\TransporteResend;
use App\Services\Correo\TransporteSmtp;
use App\Support\Ajustes;
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

    /**
     * Por dónde sale el correo.
     *
     * MANDA LO QUE DIGA CONFIGURACIÓN, y si nadie lo eligió, el archivo del
     * equipo. Elegirlo desde el panel es lo que permite cambiar de vía cuando
     * Gmail corta los 500 del día, sin tocar archivos ni reiniciar nada.
     *
     * Las CLAVES no se eligen aquí: siguen en el archivo del equipo. Por eso
     * una vía sin credenciales no se usa aunque esté elegida; si no, el panel
     * podría dejar al gimnasio sin avisos con un clic.
     */
    public function nombrePrincipal(): string
    {
        $elegido = $this->elegidoEnConfiguracion('correo.transporte');

        if ($elegido !== '' && $this->tieneCredenciales($elegido)) {
            return $elegido;
        }

        return (string) config('correo.transporte', 'smtp');
    }

    public function nombreRespaldo(): ?string
    {
        $elegido = $this->elegidoEnConfiguracion('correo.respaldo');

        if ($elegido !== '') {
            return $this->tieneCredenciales($elegido) ? $elegido : null;
        }

        $nombre = config('correo.respaldo');

        return $nombre ? (string) $nombre : null;
    }

    /**
     * Lo elegido en Configuración, o vacío si no se puede preguntar.
     *
     * El ajuste vive en la base de datos, y esto corre también donde no la hay
     * —una orden de consola suelta, una prueba—. Si no se puede leer, manda el
     * archivo del equipo: quedarse sin mandar correos porque no se pudo
     * consultar una preferencia sería el peor de los dos males.
     */
    private function elegidoEnConfiguracion(string $clave): string
    {
        return trim($this->deConfiguracion($clave));
    }

    /** ¿Esa vía tiene con qué conectarse? No dice si la clave sirve. */
    public function tieneCredenciales(string $nombre): bool
    {
        $hay = fn (string $clave, $delArchivo) => trim((string) $this->deConfiguracion($clave)) !== ''
            || trim((string) $delArchivo) !== '';

        return match ($nombre) {
            'resend' => $hay('correo.resend_clave', config('correo.resend.key')),
            'smtp' => $hay('correo.smtp_host', config('mail.mailers.smtp.host'))
                && $hay('correo.smtp_usuario', config('mail.mailers.smtp.username'))
                && $hay('correo.smtp_clave', config('mail.mailers.smtp.password')),
            default => false,
        };
    }

    /** Un ajuste del panel, o vacío si aquí no se puede preguntar. */
    private function deConfiguracion(string $clave): string
    {
        try {
            return (string) Ajustes::obtener($clave);
        } catch (\Throwable) {
            return '';
        }
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
        /*
         * LO QUE DIGA EL PANEL MANDA, y el archivo del equipo queda de
         * respaldo. Cambiar la cuenta de correo —o renovar la contraseña de
         * aplicación de Gmail, que caduca— tiene que poder hacerlo quien lleva
         * el gimnasio, sin entrar al servidor.
         */
        $delPanel = fn (string $clave, $siNo) => trim((string) $this->deConfiguracion($clave)) !== ''
            ? $this->deConfiguracion($clave)
            : $siNo;

        $timeout = (int) config('correo.timeout', 15);
        $remitente = (string) $delPanel('correo.remitente', config('mail.from.address'));
        $nombreRemitente = (string) $delPanel('correo.nombre_remitente', config('mail.from.name'));

        return match ($nombre) {
            'smtp' => new TransporteSmtp(
                host: (string) $delPanel('correo.smtp_host', config('mail.mailers.smtp.host')),
                puerto: (int) $delPanel('correo.smtp_puerto', config('mail.mailers.smtp.port')),
                usuario: (string) $delPanel('correo.smtp_usuario', config('mail.mailers.smtp.username')),
                clave: (string) $delPanel('correo.smtp_clave', config('mail.mailers.smtp.password')),
                remitente: $remitente,
                nombreRemitente: $nombreRemitente,
                timeout: $timeout,
            ),
            'resend' => new TransporteResend(
                clave: (string) $delPanel('correo.resend_clave', config('correo.resend.key')),
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
