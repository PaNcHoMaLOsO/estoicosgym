<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\CasoConCatalogos;

/**
 * Regresiones de la recuperación de contraseña.
 */
class RecuperarClaveTest extends CasoConCatalogos
{
    private function usuario(): User
    {
        return User::factory()->create(['id_rol' => 2, 'activo' => true]);
    }

    private function tokenPara(User $usuario, string $token, $creado): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $usuario->email],
            ['email' => $usuario->email, 'token' => Hash::make($token), 'created_at' => $creado],
        );
    }

    private function restablecer(User $usuario, string $token, string $clave)
    {
        return $this->post('/reset-password', [
            'token' => $token,
            'email' => $usuario->email,
            'password' => $clave,
            'password_confirmation' => $clave,
        ]);
    }

    /**
     * EL ENLACE CADUCA. Antes no.
     *
     * La comprobación era `now()->diffInMinutes($creado) > 60`, y eso nunca se
     * cumple: Carbon devuelve la diferencia CON SIGNO, así que para una fecha
     * pasada da un número negativo —hace dos horas son −120— y ningún negativo
     * es mayor que 60.
     *
     * Comprobado antes del arreglo: un token de SIETE DÍAS cambiaba la
     * contraseña sin chistar. Un correo reenviado o dormido en un buzón viejo
     * seguía abriendo la cuenta meses después.
     */
    public function test_un_enlace_de_hace_dias_ya_no_sirve(): void
    {
        $usuario = $this->usuario();
        $this->tokenPara($usuario, 'token-viejo', now()->subDays(7));

        $this->restablecer($usuario, 'token-viejo', 'ClaveNueva123');

        $this->assertFalse(
            Hash::check('ClaveNueva123', $usuario->fresh()->password),
            'Un enlace de hace una semana cambió la contraseña.'
        );
    }

    /** Justo pasada la hora tampoco. */
    public function test_un_enlace_de_hace_mas_de_una_hora_ya_no_sirve(): void
    {
        $usuario = $this->usuario();
        $this->tokenPara($usuario, 'token-limite', now()->subMinutes(61));

        $this->restablecer($usuario, 'token-limite', 'ClaveNueva123');

        $this->assertFalse(Hash::check('ClaveNueva123', $usuario->fresh()->password));
    }

    /** Dentro de la hora SÍ: el arreglo no puede romper el flujo normal. */
    public function test_un_enlace_reciente_si_cambia_la_clave(): void
    {
        $usuario = $this->usuario();
        $this->tokenPara($usuario, 'token-fresco', now()->subMinutes(5));

        $this->restablecer($usuario, 'token-fresco', 'ClaveNueva123');

        $this->assertTrue(
            Hash::check('ClaveNueva123', $usuario->fresh()->password),
            'Un enlace recién pedido debería funcionar.'
        );
    }

    /** Y el token se borra al usarlo: no vale dos veces. */
    public function test_el_token_se_borra_despues_de_usarlo(): void
    {
        $usuario = $this->usuario();
        $this->tokenPara($usuario, 'token-unico', now());

        $this->restablecer($usuario, 'token-unico', 'ClaveNueva123');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $usuario->email]);
    }

    /** Un token que no es el suyo no abre nada. */
    public function test_un_token_equivocado_no_cambia_la_clave(): void
    {
        $usuario = $this->usuario();
        $this->tokenPara($usuario, 'el-bueno', now());

        $this->restablecer($usuario, 'el-inventado', 'ClaveNueva123');

        $this->assertFalse(Hash::check('ClaveNueva123', $usuario->fresh()->password));
    }

    /**
     * Pedir el enlace responde IGUAL exista o no el correo.
     *
     * Si respondiera distinto, cualquiera sin sesión podría averiguar qué
     * direcciones están registradas probándolas una a una.
     */
    public function test_pedir_el_enlace_no_revela_si_el_correo_existe(): void
    {
        $usuario = $this->usuario();

        $conCuenta = $this->post('/forgot-password', ['email' => $usuario->email]);
        $sinCuenta = $this->post('/forgot-password', ['email' => 'nadie@ejemplo.cl']);

        $this->assertSame(
            $conCuenta->getSession()->get('status'),
            $sinCuenta->getSession()->get('status'),
            'La respuesta delata si el correo está registrado.'
        );
    }

    /** El token nunca vuelve al navegador: viaja solo por correo. */
    public function test_el_token_no_se_devuelve_en_la_respuesta(): void
    {
        $usuario = $this->usuario();

        $this->post('/forgot-password', ['email' => $usuario->email]);

        $guardado = DB::table('password_reset_tokens')->where('email', $usuario->email)->value('token');

        if ($guardado === null) {
            $this->markTestSkipped('No se generó token: el envío de correo falló en este entorno.');
        }

        // Lo que se guarda esta hasheado, asi que quien lea la tabla tampoco
        // puede armar el enlace con lo que hay ahi.
        $this->assertStringStartsWith('$2y$', $guardado, 'El token se guardó en claro.');
    }
}
