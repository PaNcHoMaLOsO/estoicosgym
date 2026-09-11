<?php

namespace Tests\Feature\Regresiones;

use App\Models\Rol;
use App\Models\User;
use App\Services\CorreoService;
use Illuminate\Support\Facades\Hash;
use Tests\CasoConCatalogos;

/**
 * Las cuentas del panel, manejadas desde Configuración → Usuarios.
 *
 * Lo que se vigila: que una cuenta nueva pueda entrar de verdad, que una
 * desactivada NO entre aunque sepa la clave, que nadie deje el sistema sin
 * administrador —ni se cierre la puerta a sí mismo— y que recepción no pueda
 * crearse cuentas.
 */
class UsuariosTest extends CasoConCatalogos
{
    private function cuenta(array $extra = []): User
    {
        return User::factory()->create($extra + [
            'id_rol' => 2,
            'activo' => true,
            'password' => 'clave-segura-1',
        ]);
    }

    /** Lo que manda el formulario al editar, con lo que ya tiene la cuenta. */
    private function datosDe(User $cuenta, array $cambios = []): array
    {
        return $cambios + [
            'nombre' => $cuenta->name,
            'email' => $cuenta->email,
            'id_rol' => (string) $cuenta->id_rol,
            'telefono' => $cuenta->phone,
            'activo' => (bool) $cuenta->activo,
        ];
    }

    public function test_el_administrador_crea_una_cuenta_y_esa_cuenta_entra(): void
    {
        $this->actingAs($this->administrador())->post('/panel/usuarios', [
            'nombre' => 'Camila Rojas',
            'email' => 'Camila@ProGym.cl',
            'id_rol' => '2',
            'clave' => 'clave-segura-1',
            'clave_confirmation' => 'clave-segura-1',
        ])->assertSessionHasNoErrors();

        // El correo se guarda en minúsculas: con mayúsculas no podría entrar.
        $nueva = User::where('email', 'camila@progym.cl')->firstOrFail();
        $this->assertSame(2, (int) $nueva->id_rol);
        $this->assertTrue((bool) $nueva->activo);

        $this->post('/logout');
        $this->post('/login', ['email' => 'camila@progym.cl', 'password' => 'clave-segura-1']);

        $this->assertAuthenticatedAs($nueva);
    }

    /** EL QUE IMPORTA: una cuenta desactivada no entra aunque sepa la clave. */
    public function test_una_cuenta_desactivada_no_entra(): void
    {
        $this->cuenta(['activo' => false, 'email' => 'se-fue@progym.cl']);

        $this->post('/login', ['email' => 'se-fue@progym.cl', 'password' => 'clave-segura-1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_desactivar_una_cuenta_la_deja_afuera(): void
    {
        $cuenta = $this->cuenta();

        $this->actingAs($this->administrador())
            ->put("/panel/usuarios/{$cuenta->id}", $this->datosDe($cuenta, ['activo' => false]))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $cuenta->refresh()->activo);
    }

    public function test_nadie_se_quita_su_propio_rol_ni_se_desactiva(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)
            ->put("/panel/usuarios/{$admin->id}", $this->datosDe($admin, ['id_rol' => '2']))
            ->assertSessionHasErrors('id_rol');

        $this->actingAs($admin)
            ->put("/panel/usuarios/{$admin->id}", $this->datosDe($admin, ['activo' => false]))
            ->assertSessionHasErrors('id_rol');

        $admin->refresh();
        $this->assertSame(1, (int) $admin->id_rol);
        $this->assertTrue((bool) $admin->activo);
    }

    /** Nunca sin administrador: sin él, nadie podría volver a entrar a Configuración. */
    public function test_no_se_puede_desactivar_al_unico_administrador(): void
    {
        // Quien edita maneja las cuentas, pero no es administrador.
        $encargado = Rol::create(['nombre' => 'Encargado', 'permisos' => ['usuarios.*'], 'activo' => true]);
        $quien = User::factory()->create(['id_rol' => $encargado->id, 'activo' => true]);

        User::where('id_rol', 1)->update(['activo' => false]);
        $admin = $this->administrador();

        $this->actingAs($quien)
            ->put("/panel/usuarios/{$admin->id}", $this->datosDe($admin, ['activo' => false]))
            ->assertSessionHasErrors('id_rol');

        $this->assertTrue((bool) $admin->refresh()->activo);
    }

    /** El login dice que el administrador puede apagarlo cuando el código no llega: ahora puede. */
    public function test_el_segundo_factor_se_puede_apagar(): void
    {
        $cuenta = $this->cuenta(['two_factor_enabled' => true, 'phone' => '+56912345678']);

        $this->actingAs($this->administrador())
            ->put("/panel/usuarios/{$cuenta->id}", $this->datosDe($cuenta, ['dos_factores' => false]))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $cuenta->refresh()->two_factor_enabled);
    }

    public function test_el_segundo_factor_sin_celular_se_rechaza(): void
    {
        $cuenta = $this->cuenta();

        $this->actingAs($this->administrador())
            ->put("/panel/usuarios/{$cuenta->id}", $this->datosDe($cuenta, ['telefono' => '', 'dos_factores' => true]))
            ->assertSessionHasErrors('dos_factores');
    }

    public function test_la_contrasena_se_cambia_y_la_vieja_deja_de_valer(): void
    {
        $cuenta = $this->cuenta();

        $this->actingAs($this->administrador())
            ->put("/panel/usuarios/{$cuenta->id}", $this->datosDe($cuenta, [
                'clave' => 'otra-clave-99',
                'clave_confirmation' => 'otra-clave-99',
            ]))
            ->assertSessionHasNoErrors();

        $cuenta->refresh();
        $this->assertTrue(Hash::check('otra-clave-99', $cuenta->password));
        $this->assertFalse(Hash::check('clave-segura-1', $cuenta->password));
    }

    public function test_editar_sin_contrasena_no_la_cambia(): void
    {
        $cuenta = $this->cuenta();

        $this->actingAs($this->administrador())
            ->put("/panel/usuarios/{$cuenta->id}", $this->datosDe($cuenta, ['nombre' => 'Otro nombre', 'clave' => '']))
            ->assertSessionHasNoErrors();

        $cuenta->refresh();
        $this->assertSame('Otro nombre', $cuenta->name);
        $this->assertTrue(Hash::check('clave-segura-1', $cuenta->password));
    }

    public function test_un_correo_repetido_se_rechaza(): void
    {
        $this->cuenta(['email' => 'repetido@progym.cl']);

        $this->actingAs($this->administrador())->post('/panel/usuarios', [
            'nombre' => 'Otra persona',
            'email' => 'repetido@progym.cl',
            'id_rol' => '2',
            'clave' => 'clave-segura-1',
            'clave_confirmation' => 'clave-segura-1',
        ])->assertSessionHasErrors('email');
    }

    public function test_el_enlace_de_contrasena_se_manda_por_correo(): void
    {
        $cuenta = $this->cuenta(['email' => 'camila@progym.cl']);

        $this->mock(CorreoService::class, fn ($correo) => $correo->shouldReceive('enviar')->once()->andReturn('id-del-correo'));

        $this->actingAs($this->administrador())
            ->post("/panel/usuarios/{$cuenta->id}/enlace")
            ->assertSessionHas('success');

        // El token queda guardado hasheado: el enlace del correo es la única copia.
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'camila@progym.cl']);
    }

    /** Si el correo no sale, el administrador tiene que saberlo: la persona se quedaría esperando. */
    public function test_si_el_correo_no_sale_se_dice(): void
    {
        $cuenta = $this->cuenta();

        $this->mock(CorreoService::class, fn ($correo) => $correo->shouldReceive('enviar')->once()->andThrow(new \RuntimeException('Sin conexión')));

        $this->actingAs($this->administrador())
            ->post("/panel/usuarios/{$cuenta->id}/enlace")
            ->assertSessionHas('error');
    }

    public function test_recepcion_no_maneja_las_cuentas(): void
    {
        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)->get('/panel/usuarios')->assertForbidden();

        $this->actingAs($recepcion)->post('/panel/usuarios', [
            'nombre' => 'Intruso',
            'email' => 'intruso@progym.cl',
            'id_rol' => '1',
            'clave' => 'clave-segura-1',
            'clave_confirmation' => 'clave-segura-1',
        ])->assertForbidden();

        $this->assertSame(0, User::where('email', 'intruso@progym.cl')->count());
    }
}
