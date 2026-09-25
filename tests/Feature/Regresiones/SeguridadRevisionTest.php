<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Tests\CasoConCatalogos;

/**
 * Lo que salió en la revisión de seguridad del 25 de septiembre de 2026.
 */
class SeguridadRevisionTest extends CasoConCatalogos
{
    /** Una ruta nueva del panel que nadie clasificó queda cerrada, no abierta. */
    public function test_una_ruta_del_panel_sin_clasificar_queda_cerrada(): void
    {
        Route::middleware(['web', 'auth', 'puede'])
            ->get('/panel/prueba-sin-clasificar', fn () => 'adentro')
            ->name('panel.inventado.index');
        Route::getRoutes()->refreshNameLookups();

        $this->actingAs($this->recepcionista())->get('/panel/prueba-sin-clasificar')->assertForbidden();
        $this->actingAs($this->administrador())->get('/panel/prueba-sin-clasificar')->assertOk();
    }

    /** El panel y el login no se pueden meter dentro de un marco ajeno. */
    public function test_el_login_y_el_panel_llevan_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($this->administrador())->get('/panel')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    /** Probar claves contra una cuenta se frena aunque cambie la IP. */
    public function test_el_login_se_frena_por_cuenta(): void
    {
        RateLimiter::clear('cuenta:alguien@progym.cl');

        for ($i = 0; $i < 20; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$i}"])
                ->post('/login', ['email' => 'alguien@progym.cl', 'password' => "mala{$i}"]);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.99'])
            ->post('/login', ['email' => 'Alguien@progym.cl', 'password' => 'otra'])
            ->assertStatus(429);
    }

    /** Recuperar la clave saca de las sesiones y anula el «recordarme». */
    public function test_recuperar_la_clave_anula_el_recordarme(): void
    {
        $usuario = User::factory()->create(['email' => 'camila@progym.cl', 'activo' => true, 'remember_token' => 'viejo']);
        $token = 'un-token-de-prueba';

        \DB::table('password_reset_tokens')->insert([
            'email' => 'camila@progym.cl',
            'token' => \Hash::make($token),
            'created_at' => now(),
        ]);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'camila@progym.cl',
            'password' => 'clave-nueva-segura-1',
            'password_confirmation' => 'clave-nueva-segura-1',
        ])->assertRedirect(route('login'));

        $this->assertNotSame('viejo', $usuario->fresh()->remember_token);
    }
}
