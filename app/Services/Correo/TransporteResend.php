<?php

namespace App\Services\Correo;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Salida por la API de Resend.
 *
 * Se habla por HTTP con el cliente que ya trae Laravel en vez de sumar el
 * paquete oficial: es una sola llamada POST y una dependencia menos que
 * mantener al dia.
 *
 * POR QUE INTERESA COMO RESPALDO. El SMTP de Gmail depende de una cuenta con
 * verificacion en dos pasos y una clave de aplicacion, y corta por volumen —500
 * correos al dia— y por conexiones. Resend autentica con una clave y no
 * necesita mantener una sesion abierta, asi que cuando el SMTP se cae o se
 * queda sin cuota, los avisos de vencimiento siguen saliendo.
 *
 * OJO CON EL REMITENTE: Resend solo acepta enviar desde un dominio verificado
 * en su panel. Con MAIL_FROM_ADDRESS en @gmail.com rechazara el envio; hace
 * falta un correo del dominio del gimnasio.
 */
class TransporteResend implements Transporte
{
    private const URL = 'https://api.resend.com/emails';

    public function __construct(
        private readonly string $clave,
        private readonly string $remitente,
        private readonly string $nombreRemitente,
        private readonly int $timeout = 15,
    ) {}

    public function enviar(string $para, string $asunto, string $html, ?string $nombreDestino = null): string
    {
        if ($this->clave === '') {
            throw new RuntimeException('Falta RESEND_API_KEY.');
        }

        try {
            $respuesta = Http::withToken($this->clave)
                ->timeout($this->timeout)
                // Un fallo de red se reintenta solo; un rechazo del servidor no,
                // porque volver a mandarlo daria el mismo rechazo.
                ->retry(2, 300, throw: false)
                ->post(self::URL, [
                    'from' => $this->remitenteConNombre(),
                    'to' => [$para],
                    'subject' => $asunto,
                    'html' => $html,
                    'text' => trim(strip_tags($html)),
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Error al enviar correo por Resend: ' . $e->getMessage(), previous: $e);
        }

        if ($respuesta->failed()) {
            $detalle = $respuesta->json('message') ?? $respuesta->body();

            throw new RuntimeException("Resend respondió {$respuesta->status()}: {$detalle}");
        }

        return (string) ($respuesta->json('id') ?? '');
    }

    /**
     * Resend no ofrece un «ping», asi que se consulta el listado de dominios:
     * responde 200 con una clave valida y 401 con una que no lo es, y no manda
     * ningun correo.
     */
    public function comprobar(): ?string
    {
        if ($this->clave === '') {
            return 'Falta RESEND_API_KEY en el .env.';
        }

        try {
            $respuesta = Http::withToken($this->clave)
                ->timeout($this->timeout)
                ->get('https://api.resend.com/domains');
        } catch (Throwable $e) {
            return 'No se pudo contactar con Resend: ' . $e->getMessage();
        }

        if ($respuesta->status() === 401) {
            return 'Resend rechazó la clave (401). Revisa RESEND_API_KEY.';
        }

        if ($respuesta->failed()) {
            return "Resend respondió {$respuesta->status()}.";
        }

        // El remitente tiene que ser de un dominio verificado o el envio se
        // rechaza en el momento, no al configurarlo.
        $dominios = collect($respuesta->json('data') ?? [])
            ->where('status', 'verified')
            ->pluck('name');

        if ($dominios->isEmpty()) {
            return 'La clave sirve, pero no hay ningún dominio verificado en Resend todavía.';
        }

        $dominioRemitente = substr(strrchr($this->remitente, '@') ?: '', 1);

        if ($dominioRemitente !== '' && ! $dominios->contains($dominioRemitente)) {
            return sprintf(
                'El remitente es @%s y en Resend solo están verificados: %s.',
                $dominioRemitente,
                $dominios->implode(', '),
            );
        }

        return null;
    }

    public function descripcion(): string
    {
        return 'Resend (API)';
    }

    /** Nada que soltar: cada envío es una petición HTTP independiente. */
    public function cerrar(): void {}

    private function remitenteConNombre(): string
    {
        return $this->nombreRemitente !== ''
            ? "{$this->nombreRemitente} <{$this->remitente}>"
            : $this->remitente;
    }
}
