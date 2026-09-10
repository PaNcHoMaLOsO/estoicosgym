<?php

namespace Tests\Feature\Regresiones;

use App\Services\CorreoService;
use RuntimeException;
use Tests\TestCase;

/**
 * Regresiones del envío de correo.
 */
class EnvioDeCorreoTest extends TestCase
{
    /**
     * No se envía a los dominios reservados de la RFC 2606.
     *
     * La base se puebla con socios de prueba cuyos correos son @example.net.
     * Bastaba con generar las notificaciones automáticas y lanzar el envío para
     * disparar decenas de rebotes desde la cuenta real del gimnasio, y los
     * rebotes son lo que hace que Gmail acabe mandando tus correos a spam.
     */
    public function test_no_envia_a_direcciones_de_ejemplo(): void
    {
        $correo = app(CorreoService::class);

        $reservadas = [
            'socio@example.com',
            'socio@example.net',
            'socio@example.org',
            'algo@servidor.test',
            'algo@servidor.invalid',
            'algo@maquina.localhost',
        ];

        foreach ($reservadas as $direccion) {
            try {
                $correo->enviar($direccion, 'Asunto', '<p>Cuerpo</p>');
                $this->fail("Se intentó enviar a {$direccion}, que no existe.");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString(
                    'dirección de ejemplo',
                    $e->getMessage(),
                    "«{$direccion}» debía rechazarse por ser reservada."
                );
            }
        }
    }

    /**
     * Una dirección normal SÍ se intenta enviar.
     *
     * Sin esto, el guardia de arriba podría estar bloqueándolo todo y las
     * pruebas seguirían en verde.
     */
    public function test_una_direccion_real_si_se_intenta_enviar(): void
    {
        config(['correo.transporte' => 'smtp', 'correo.respaldo' => null]);

        try {
            app(CorreoService::class)->enviar('socio@gmail.com', 'Asunto', '<p>Cuerpo</p>');
        } catch (RuntimeException $e) {
            // Sin credenciales el envío falla, y eso está bien: lo que importa
            // es que NO lo rechace por la dirección, sino al hablar con el SMTP.
            $this->assertStringNotContainsString('dirección de ejemplo', $e->getMessage());

            return;
        }

        $this->addToAssertionCount(1);
    }

    /** Un transporte mal escrito en el .env avisa en vez de fallar callado. */
    public function test_un_transporte_desconocido_lo_dice_claro(): void
    {
        config(['correo.transporte' => 'palomas']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('palomas');

        app(CorreoService::class)->enviar('socio@gmail.com', 'Asunto', '<p>Cuerpo</p>');
    }
}
