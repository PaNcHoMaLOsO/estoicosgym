<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Convenio;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteFlujosTest extends TestCase
{
    use RefreshDatabase;

    protected $membresia;
    protected $metodoPago;
    protected $convenio;

    protected function getFormToken()
    {
        return \Illuminate\Support\Str::random(40);
    }

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=OFF');

        $rol = Rol::create(['nombre' => 'Admin']);
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'id_rol' => $rol?->id ?? 1,
        ]);
        $this->actingAs($user);

        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=ON');

        $this->membresia = Membresia::factory()->create([
            'nombre' => 'Membresia Test',
            'duracion_meses' => 1,
            'duracion_dias' => 30,
            'activo' => true,
        ]);

        $this->metodoPago = MetodoPago::factory()->create([
            'nombre' => 'Efectivo',
            'activo' => true,
        ]);

        $this->convenio = Convenio::factory()->create([
            'nombre' => 'Descuento',
            'descuento_porcentaje' => 10,
            'activo' => true,
        ]);
    }

    public function test_flujo_1_solo_cliente()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            'run_pasaporte' => '12.345.678-1',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Garcia',
            'celular' => '+56912345678',
            'email' => 'juan1@example.com',
            'flujo_cliente' => 'solo_cliente',
        ]);

        $response->assertRedirect();
    }

    public function test_validacion_email_requerido()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            'run_pasaporte' => '12.345.678-5',
            'nombres' => 'Test',
            'apellido_paterno' => 'User',
            'celular' => '+56912345678',
            'email' => '',
            'flujo_cliente' => 'solo_cliente',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_validacion_nombres_requerido()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            'run_pasaporte' => '12.345.678-1',
            'nombres' => '',
            'apellido_paterno' => 'User',
            'celular' => '+56912345678',
            'email' => 'testnom@example.com',
            'flujo_cliente' => 'solo_cliente',
        ]);

        $response->assertSessionHasErrors('nombres');
    }
}
