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

        // Crear PrecioMembresia para esta membresía
        \App\Models\PrecioMembresia::create([
            'id_membresia' => $this->membresia->id,
            'precio_normal' => 100000,
            'precio_convenio' => 90000,
            'fecha_vigencia_desde' => now(),
            'fecha_vigencia_hasta' => null,
        ]);
    }

    public function test_flujo_1_solo_cliente()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            'run_pasaporte' => '7.882.382-4',
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
            'run_pasaporte' => '7.882.382-5',
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
            'run_pasaporte' => '7.882.382-1',
            'nombres' => '',
            'apellido_paterno' => 'User',
            'celular' => '+56912345678',
            'email' => 'testnom@example.com',
            'flujo_cliente' => 'solo_cliente',
        ]);

        $response->assertSessionHasErrors('nombres');
    }

    public function test_flujo_2_con_membresia()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            // RUT es opcional, lo omitimos para evitar validación
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Rodriguez',
            'celular' => '+56912345679',
            'email' => 'carlos@example.com',
            'flujo_cliente' => 'con_membresia',
            'id_membresia' => $this->membresia->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'id_convenio' => null,
        ]);

        $response->assertRedirect();
        
        // Debug
        if ($response->exception) {
            throw $response->exception;
        }
    }

    public function test_flujo_3_completo()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'form_submit_token' => $this->getFormToken(),
            // RUT es opcional, lo omitimos para evitar validación
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Martinez',
            'celular' => '+56912345680',
            'email' => 'pedro@example.com',
            'flujo_cliente' => 'completo',
            'id_membresia' => $this->membresia->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'id_convenio' => null,
            'monto_abonado' => 100000,
            'id_metodo_pago' => $this->metodoPago->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        
        // Verificar que cliente fue creado
        $this->assertDatabaseHas('clientes', [
            'email' => 'pedro@example.com',
            'activo' => true,
        ]);
        
        // Verificar que inscripción fue creada
        $cliente = Cliente::where('email', 'pedro@example.com')->first();
        $this->assertDatabaseHas('inscripciones', [
            'id_cliente' => $cliente->id,
            'id_membresia' => $this->membresia->id,
            'id_estado' => 100, // Activa
        ]);
        
        // Verificar que pago fue creado
        $this->assertDatabaseHas('pagos', [
            'id_cliente' => $cliente->id,
            'monto_abonado' => 100000,
            'id_metodo_pago' => $this->metodoPago->id,
        ]);
    }
}
