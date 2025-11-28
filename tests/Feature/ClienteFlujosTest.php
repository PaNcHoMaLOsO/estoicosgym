<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Convenio;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @property Membresia $membresia
 * @property MetodoPago $metodoPago
 * @property Convenio $convenio
 */
class ClienteFlujosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Deshabilitar foreign keys para los tests
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys=OFF');
        
        // Crear rol y usuario de prueba
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
            'nombre' => 'Membresía Test',
            'duracion_meses' => 1,
            'duracion_dias' => 30,
            'activo' => true,
        ]);

        $this->metodoPago = MetodoPago::factory()->create([
            'nombre' => 'Efectivo',
            'activo' => true,
        ]);

        $this->convenio = Convenio::factory()->create([
            'nombre' => 'Descuento 10%',
            'descuento_porcentaje' => 10,
            'activo' => true,
        ]);
    }

    /**
     * Flujo 1: Crear SOLO CLIENTE (sin membresía ni pago)
     */
    public function test_flujo_1_solo_cliente()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'run_pasaporte' => '12.345.678-1',
            'nombres' => 'Juan',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'celular' => '+56912345678',
            'email' => 'juan1@example.com',
            'direccion' => 'Calle Principal 123',
            'fecha_nacimiento' => '1990-05-15',
            'contacto_emergencia' => 'María García',
            'telefono_emergencia' => '+56987654321',
            'observaciones' => 'Cliente de prueba',
            'flujo_cliente' => 'solo_cliente',
        ]);

        $response->assertRedirect();
    }

    /**
     * Flujo 2: Crear CLIENTE + MEMBRESÍA (sin pago)
     */
    public function test_flujo_2_cliente_con_membresia()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'run_pasaporte' => '12.345.678-2',
            'nombres' => 'Juan',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'celular' => '+56912345678',
            'email' => 'juan2@example.com',
            'direccion' => 'Calle Principal 123',
            'fecha_nacimiento' => '1990-05-15',
            'contacto_emergencia' => 'María García',
            'telefono_emergencia' => '+56987654321',
            'observaciones' => 'Cliente de prueba',
            
            'id_membresia' => $this->membresia->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'id_convenio' => null,
            
            'flujo_cliente' => 'con_membresia',
        ]);

        $response->assertRedirect();
    }

    /**
     * Flujo 3: Crear CLIENTE + MEMBRESÍA + PAGO
     */
    public function test_flujo_3_cliente_completo()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'run_pasaporte' => '12.345.678-3',
            'nombres' => 'Juan',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'celular' => '+56912345678',
            'email' => 'juan3@example.com',
            'direccion' => 'Calle Principal 123',
            'fecha_nacimiento' => '1990-05-15',
            'contacto_emergencia' => 'María García',
            'telefono_emergencia' => '+56987654321',
            'observaciones' => 'Cliente de prueba',
            
            'id_membresia' => $this->membresia->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'id_convenio' => null,
            
            'monto_abonado' => '50000',
            'id_metodo_pago' => $this->metodoPago->id,
            'fecha_pago' => now()->format('Y-m-d'),
            
            'flujo_cliente' => 'completo',
        ]);

        $response->assertRedirect();
    }

    /**
     * Flujo 4: Crear CLIENTE con CONVENIO (descuento en membresía)
     */
    public function test_flujo_4_cliente_con_convenio()
    {
        $response = $this->post(route('admin.clientes.store'), [
            'run_pasaporte' => '12.345.678-4',
            'nombres' => 'Juan',
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'celular' => '+56912345678',
            'email' => 'juan4@example.com',
            'direccion' => 'Calle Principal 123',
            'fecha_nacimiento' => '1990-05-15',
            'contacto_emergencia' => 'María García',
            'telefono_emergencia' => '+56987654321',
            'observaciones' => 'Cliente con convenio',
            
            'id_membresia' => $this->membresia->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'id_convenio' => $this->convenio->id,
            
            'monto_abonado' => '45000',
            'id_metodo_pago' => $this->metodoPago->id,
            'fecha_pago' => now()->format('Y-m-d'),
            
            'flujo_cliente' => 'completo',
        ]);

        $response->assertRedirect();
    }
}
