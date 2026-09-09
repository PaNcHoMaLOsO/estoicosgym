<?php

namespace App\Services\Correo;

/**
 * Por donde sale un correo.
 *
 * Hay dos: el SMTP de siempre (PHPMailer) y la API de Resend. Se separan detras
 * de esta interfaz para que CorreoService —y las siete partes del sistema que
 * lo llaman— no sepan ni les importe cual esta puesto, y para poder tener uno
 * de respaldo cuando el principal no contesta.
 */
interface Transporte
{
    /**
     * Envía un correo y devuelve el identificador que da el proveedor.
     *
     * @throws \RuntimeException si el envío falla.
     */
    public function enviar(string $para, string $asunto, string $html, ?string $nombreDestino = null): string;

    /**
     * Suelta lo que tenga abierto (la conexión SMTP, por ejemplo).
     *
     * Se llama al terminar una tanda. Quien envie de uno en uno puede no
     * llamarlo: el transporte tiene que quedar igual de correcto.
     */
    public function cerrar(): void;

    /**
     * Comprueba que el transporte puede enviar, SIN mandar nada.
     *
     * Devuelve null si todo esta bien, o el motivo del fallo.
     */
    public function comprobar(): ?string;

    /** Nombre corto para los registros: «SMTP (smtp.gmail.com)», «Resend». */
    public function descripcion(): string;
}
