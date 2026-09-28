<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\RateLimiter;
use Tests\CasoConCatalogos;

/**
 * Lo que salió en la revisión de acceso y sesión del 28 de septiembre de 2026.
 */
class AccesoYSesionTest extends CasoConCatalogos
{
    /** En modo desarrollo, el código solo se ve sentado en este equipo, no por el túnel. */
    public function test_el_codigo_no_se_ve_desde_fuera_de_este_equipo(): void
    {
        $this->app['env'] = 'local';

        $this->app['request']->headers->set('HOST', 'algo.trycloudflare.com');
        $this->assertFalse(TwoFactorService::codigoEnPantalla());

        $this->app['request']->headers->set('HOST', '127.0.0.1');
        $this->assertTrue(TwoFactorService::codigoEnPantalla());
    }

    /** Cinco códigos malos y hay que volver a poner la clave. */
    public function test_cinco_codigos_malos_vuelven_al_login(): void
    {
        $usuario = User::factory()->create(['id_rol' => 1, 'activo' => true]);

        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.3.{$i}.1"])
                ->withSession(['2fa_user_id' => $usuario->id])
                ->post('/verify-2fa', ['code' => '000000']);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.4.0.1'])
            ->withSession(['2fa_user_id' => $usuario->id])
            ->post('/verify-2fa', ['code' => '000000'])
            ->assertRedirect(route('login'))
            ->assertSessionMissing('2fa_user_id');
    }

    /** Activar el segundo factor saca de las otras sesiones y anula el «recordarme». */
    public function test_activar_el_segundo_factor_cierra_las_otras_sesiones(): void
    {
        $admin = $this->administrador();
        $usuario = User::factory()->create(['id_rol' => 2, 'activo' => true, 'phone' => '+56911112222']);
        $recordarme = $usuario->remember_token;

        $this->actingAs($admin)->put(route('panel.usuarios.update', $usuario), [
            'nombre' => $usuario->name,
            'email' => $usuario->email,
            'id_rol' => 2,
            'activo' => true,
            'telefono' => '+56911112222',
            'dos_factores' => true,
        ]);

        $this->assertTrue((bool) $usuario->fresh()->two_factor_enabled, 'No se activó el segundo factor.');
        $this->assertNotSame($recordarme, $usuario->fresh()->remember_token);
    }

    /** Tres enlaces por hora por correo: al cuarto ya no sale otro. */
    public function test_olvide_mi_clave_manda_tres_por_hora(): void
    {
        $usuario = User::factory()->create(['id_rol' => 2, 'activo' => true]);

        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.5.{$i}.1"])
                ->post('/forgot-password', ['email' => $usuario->email])
                ->assertSessionHas('status');
        }

        $this->assertSame(3, RateLimiter::attempts('clave-olvidada:' . mb_strtolower($usuario->email)));
    }

    /** «Recordarme» dura un mes, no más de un año. */
    public function test_recordarme_dura_un_mes(): void
    {
        $duracion = (new \ReflectionProperty(auth()->guard('web'), 'rememberDuration'))->getValue(auth()->guard('web'));

        $this->assertSame(60 * 24 * 30, $duracion);
    }
}
