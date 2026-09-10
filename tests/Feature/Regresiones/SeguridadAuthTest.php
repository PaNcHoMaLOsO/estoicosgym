<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\CasoConCatalogos;

/**
 * Regresiones de seguridad en la autenticación.
 *
 * Los tres agujeros que se cubren aquí estaban abiertos para cualquier
 * visitante ANÓNIMO y ninguno daba error: el sistema respondía de más.
 *
 * Estas pruebas corren en entorno «testing», que NO es local ni development,
 * así que ven exactamente lo que vería producción: sin el atajo de desarrollo
 * que muestra el enlace en pantalla.
 */
class SeguridadAuthTest extends CasoConCatalogos
{
    private function usuario(string $email = 'jefe@progym.cl'): User
    {
        return User::factory()->create(['email' => $email, 'activo' => true]);
    }

    /**
     * El token de recuperación NO vuelve al navegador.
     *
     * Antes se devolvía en el mensaje de pantalla, así que cualquiera que
     * supiera el correo del admin reseteaba su clave sin entrar a su buzón.
     */
    public function test_el_token_de_recuperacion_no_se_muestra_en_pantalla(): void
    {
        $this->usuario('jefe@progym.cl');

        $respuesta = $this->post('/forgot-password', ['email' => 'jefe@progym.cl']);

        $token = DB::table('password_reset_tokens')->where('email', 'jefe@progym.cl')->value('token');

        $this->assertNotNull($token, 'Debió generarse un token para enviar por correo.');

        // El mensaje flash no puede traer el token ni el enlace de reseteo.
        $status = (string) session('status');
        $this->assertStringNotContainsString('reset-password', $status);
        $this->assertStringNotContainsString('token', strtolower($status));
    }

    /** El «olvidé mi contraseña» responde igual exista o no el correo. */
    public function test_recuperacion_no_revela_si_el_correo_existe(): void
    {
        $this->usuario('existe@progym.cl');

        $conCuenta = $this->post('/forgot-password', ['email' => 'existe@progym.cl']);
        $sinCuenta = $this->post('/forgot-password', ['email' => 'fantasma@progym.cl']);

        $this->assertSame(
            (string) $conCuenta->baseResponse->getSession()->get('status'),
            (string) $sinCuenta->baseResponse->getSession()->get('status'),
            'El mensaje difiere entre un correo registrado y uno que no: eso los delata.'
        );

        // Y para el que no existe, no se crea ningún token.
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'fantasma@progym.cl']);
    }

    /**
     * resend-2fa no deja adivinar qué usuarios existen.
     *
     * Antes aceptaba `user_id` del formulario y respondía «Usuario no
     * encontrado» o «no tiene teléfono» según el caso: enumeración pura, sin
     * necesidad de estar autenticado.
     */
    public function test_resend_2fa_no_enumera_usuarios(): void
    {
        $this->usuario();

        $existente = $this->postJson('/resend-2fa', ['user_id' => 1]);
        $inexistente = $this->postJson('/resend-2fa', ['user_id' => 99999]);

        $existente->assertOk();
        $inexistente->assertOk();

        $this->assertSame(
            $existente->json('message'),
            $inexistente->json('message'),
            'La respuesta cambia según el id: eso permite enumerar usuarios.'
        );
    }

    /**
     * El reseteo completo funciona con un token válido.
     *
     * Cerrar las fugas no puede dejar inservible el flujo legítimo: el token
     * generado tiene que servir para cambiar la clave.
     */
    public function test_el_reseteo_con_token_valido_cambia_la_clave(): void
    {
        $usuario = $this->usuario('cambio@progym.cl');

        $token = \Illuminate\Support\Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => 'cambio@progym.cl',
            'token' => bcrypt($token),
            'created_at' => now(),
        ]);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'cambio@progym.cl',
            'password' => 'claveNueva123',
            'password_confirmation' => 'claveNueva123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('claveNueva123', $usuario->fresh()->password),
            'La contraseña no se actualizó con un token válido.'
        );

        // El token se consume: no puede reutilizarse.
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'cambio@progym.cl']);
    }

    /** El login corta la fuerza bruta: tras varios intentos, 429. */
    public function test_el_login_frena_el_martilleo(): void
    {
        $this->usuario('objetivo@progym.cl');

        $bloqueado = false;

        for ($i = 0; $i < 12; $i++) {
            $respuesta = $this->post('/login', [
                'email' => 'objetivo@progym.cl',
                'password' => "malísima{$i}",
            ]);

            if ($respuesta->status() === 429) {
                $bloqueado = true;
                break;
            }
        }

        $this->assertTrue($bloqueado, 'El login aceptó 12 intentos seguidos sin frenar.');
    }
}
