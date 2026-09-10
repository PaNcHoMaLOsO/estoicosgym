<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use App\Models\VerificationCode;
use Tests\CasoConCatalogos;

/**
 * Regresiones de la verificación en dos pasos.
 *
 * Los dos fallos de aquí convivían y se tapaban entre sí: el segundo factor se
 * saltaba solo cuando el envío fallaba, y por eso nadie llegaba nunca a la
 * línea que lo dejaba inservible.
 */
class SegundoFactorTest extends CasoConCatalogos
{
    private function usuarioCon2fa(): User
    {
        return User::factory()->create([
            'id_rol' => 2,
            'activo' => true,
            'two_factor_enabled' => true,
            'phone' => '+56912345678',
            'two_factor_channel' => 'whatsapp',
        ]);
    }

    /**
     * SI EL CÓDIGO NO SALE, NO SE ENTRA.
     *
     * El login hacía Auth::login() cuando el envío fallaba, «para no dejar
     * fuera al usuario». Eso convertía el segundo factor en un adorno: bastaba
     * con que el canal se cayera para que la clave sola abriera la puerta. Y no
     * era un caso raro: sin ningún canal configurado el envío falla SIEMPRE, así
     * que todo el que activaba 2FA entraba sin él creyéndose protegido.
     *
     * En el entorno de pruebas no hay canal, así que el envío falla igual que
     * en una instalación recién hecha.
     */
    public function test_si_el_codigo_no_se_puede_enviar_no_se_inicia_sesion(): void
    {
        $usuario = $this->usuarioCon2fa();

        $respuesta = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        // assertGuest() recibe el nombre del guard, no un mensaje: el motivo va
        // en la comprobación de abajo, que sí lo admite.
        $this->assertFalse(
            auth()->check(),
            'El segundo factor se saltó: la sesión quedó iniciada sin verificar.'
        );

        $respuesta->assertSessionHasErrors('email');
    }

    /** Y tampoco queda a medias: sin sesión pendiente que alguien pueda cerrar. */
    public function test_un_envio_fallido_no_deja_la_sesion_a_medias(): void
    {
        $usuario = $this->usuarioCon2fa();

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        $this->assertNull(session('2fa_user_id'), 'Quedó una sesión de 2FA pendiente tras fallar el envío.');
    }

    /** Quien no usa 2FA entra como siempre: el arreglo no puede estorbarle. */
    public function test_sin_segundo_factor_el_login_sigue_funcionando(): void
    {
        $usuario = User::factory()->create([
            'id_rol' => 1,
            'activo' => true,
            'two_factor_enabled' => false,
        ]);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($usuario);
    }

    /**
     * Dar por bueno un código correcto no puede reventar.
     *
     * markAsUsed() escribía en `verified_at`, una columna que la migración
     * original nunca creó: verificar un código CORRECTO terminaba en un 1054 de
     * MySQL y devolvía un 500. Nadie con 2FA podía completar el acceso, y no se
     * notaba porque el login se lo saltaba antes de llegar aquí.
     */
    public function test_verificar_un_codigo_correcto_lo_marca_como_usado(): void
    {
        $usuario = $this->usuarioCon2fa();
        $codigo = VerificationCode::createFor($usuario, 'login');

        $verificado = VerificationCode::verify($usuario, $codigo->code, 'login');

        $this->assertNotNull($verificado, 'Un código correcto debe darse por bueno.');
        $this->assertTrue((bool) $verificado->fresh()->is_used);
        $this->assertNotNull($verificado->fresh()->verified_at, 'No quedó constancia de cuándo se verificó.');
    }

    /** El mismo código no sirve dos veces. */
    public function test_un_codigo_ya_usado_no_vale_de_nuevo(): void
    {
        $usuario = $this->usuarioCon2fa();
        $codigo = VerificationCode::createFor($usuario, 'login');

        VerificationCode::verify($usuario, $codigo->code, 'login');

        $this->assertNull(
            VerificationCode::verify($usuario, $codigo->code, 'login'),
            'Un código usado se aceptó por segunda vez.'
        );
    }

    /** Un código caducado tampoco. */
    public function test_un_codigo_caducado_no_vale(): void
    {
        $usuario = $this->usuarioCon2fa();
        $codigo = VerificationCode::createFor($usuario, 'login');
        $codigo->update(['expires_at' => now()->subMinute()]);

        $this->assertNull(VerificationCode::verify($usuario, $codigo->code, 'login'));
    }

    /** Pedir uno nuevo invalida el anterior: solo vale el último enviado. */
    public function test_pedir_un_codigo_nuevo_invalida_el_anterior(): void
    {
        $usuario = $this->usuarioCon2fa();

        $primero = VerificationCode::createFor($usuario, 'login');
        VerificationCode::createFor($usuario, 'login');

        $this->assertNull(
            VerificationCode::verify($usuario, $primero->code, 'login'),
            'El código antiguo siguió sirviendo después de pedir otro.'
        );
    }

    /** El código de otro usuario no abre mi sesión. */
    public function test_el_codigo_de_otro_usuario_no_sirve(): void
    {
        $mio = $this->usuarioCon2fa();
        $ajeno = $this->usuarioCon2fa();

        $codigoAjeno = VerificationCode::createFor($ajeno, 'login');

        $this->assertNull(VerificationCode::verify($mio, $codigoAjeno->code, 'login'));
    }
}
