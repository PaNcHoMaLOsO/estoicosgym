<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionesNegocioTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $cliente;
    private $membresia;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario admin
        $this->user = User::factory()->create();
        
        // Crear estados necesarios
        \DB::table('estados')->insert([
            ['codigo' => 100, 'nombre' => 'Activa', 'categoria' => 'membresia'],
            ['codigo' => 101, 'nombre' => 'Pausada', 'categoria' => 'membresia'],
            ['codigo' => 102, 'nombre' => 'Vencida', 'categoria' => 'membresia'],
            ['codigo' => 103, 'nombre' => 'Cancelada', 'categoria' => 'membresia'],
            ['codigo' => 105, 'nombre' => 'Cambiada', 'categoria' => 'membresia'],
            ['codigo' => 106, 'nombre' => 'Traspasada', 'categoria' => 'membresia'],
            ['codigo' => 200, 'nombre' => 'Pendiente', 'categoria' => 'pago'],
            ['codigo' => 201, 'nombre' => 'Pagado', 'categoria' => 'pago'],
            ['codigo' => 202, 'nombre' => 'Parcial', 'categoria' => 'pago'],
            ['codigo' => 205, 'nombre' => 'Traspasado', 'categoria' => 'pago'],
        ]);
        
        // Crear método de pago
        \DB::table('metodos_pago')->insert([
            ['id' => 1, 'nombre' => 'Efectivo', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Tarjeta', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Crear membresía de prueba
        $this->membresia = Membresia::factory()->create([
            'nombre' => 'Plan Mensual',
            'duracion_dias' => 30,
            'activo' => true,
        ]);
        
        // Crear precio para la membresía
        \DB::table('precios_membresia')->insert([
            'id_membresia' => $this->membresia->id,
            'precio_normal' => 30000,
            'fecha_vigencia_desde' => now()->subMonth(),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Crear cliente de prueba
        $this->cliente = Cliente::factory()->create([
            'activo' => true,
            'email' => 'test@example.com',
        ]);
    }

    // =====================================================
    // TESTS DE PAGOS
    // =====================================================

    /** @test */
    public function no_permite_pago_mayor_al_saldo_pendiente()
    {
        $this->actingAs($this->user);
        
        // Crear inscripción con precio 30000
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        // Crear pago parcial de 20000
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $this->cliente->id,
            'monto_total' => 30000,
            'monto_abonado' => 20000,
            'monto_pendiente' => 10000,
            'id_estado' => 202,
            'id_metodo_pago' => 1,
            'fecha_pago' => now(),
        ]);
        
        // Intentar pagar 15000 cuando solo quedan 10000
        $response = $this->post(route('admin.pagos.store'), [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'abono',
            'monto_abonado' => 15000, // Mayor al saldo pendiente (10000)
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => 1,
            'form_submit_token' => uniqid(),
        ]);
        
        $response->assertSessionHasErrors('monto_abonado');
    }

    /** @test */
    public function no_permite_pago_en_inscripcion_cancelada()
    {
        $this->actingAs($this->user);
        
        // Crear inscripción cancelada
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 103, // Cancelada
        ]);
        
        $response = $this->post(route('admin.pagos.store'), [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => 1,
            'form_submit_token' => uniqid(),
        ]);
        
        $response->assertSessionHas('error');
    }

    /** @test */
    public function no_permite_pago_en_inscripcion_traspasada()
    {
        $this->actingAs($this->user);
        
        // Crear inscripción traspasada
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->subDay(), // Ya expirada por traspaso
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 106, // Traspasada
        ]);
        
        $response = $this->post(route('admin.pagos.store'), [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => 1,
            'form_submit_token' => uniqid(),
        ]);
        
        $response->assertSessionHas('error');
    }

    /** @test */
    public function pago_mixto_debe_sumar_exactamente_el_saldo()
    {
        $this->actingAs($this->user);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        // Intentar pago mixto con suma incorrecta
        $response = $this->post(route('admin.pagos.store'), [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'mixto',
            'id_metodo_pago1' => 1,
            'id_metodo_pago2' => 2,
            'monto_metodo1' => 15000,
            'monto_metodo2' => 10000, // Total 25000, debería ser 30000
            'fecha_pago' => now()->format('Y-m-d'),
            'form_submit_token' => uniqid(),
        ]);
        
        $response->assertSessionHasErrors('monto_metodo1');
    }

    // =====================================================
    // TESTS DE INSCRIPCIONES
    // =====================================================

    /** @test */
    public function no_permite_pausar_inscripcion_ya_pausada()
    {
        $this->actingAs($this->user);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 101, // Ya pausada
            'pausada' => true,
        ]);
        
        $response = $this->post(route('admin.inscripciones.pausar', $inscripcion), [
            'dias' => 7,
        ]);
        
        $response->assertStatus(422);
    }

    /** @test */
    public function no_permite_pausar_si_excede_maximo_pausas()
    {
        $this->actingAs($this->user);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
            'max_pausas_permitidas' => 2,
            'pausas_realizadas' => 2, // Ya usó todas sus pausas
        ]);
        
        $response = $this->post(route('admin.inscripciones.pausar', $inscripcion), [
            'dias' => 7,
        ]);
        
        $response->assertStatus(422);
    }

    // =====================================================
    // TESTS DE TRASPASOS
    // =====================================================

    /** @test */
    public function no_permite_traspasar_al_mismo_cliente()
    {
        $this->actingAs($this->user);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        // Crear pago para la inscripción
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $this->cliente->id,
            'monto_total' => 30000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'id_metodo_pago' => 1,
            'fecha_pago' => now(),
        ]);
        
        $response = $this->post(route('admin.inscripciones.traspasar', $inscripcion), [
            'id_cliente_destino' => $this->cliente->id, // Mismo cliente
            'motivo_traspaso' => 'Test traspaso',
        ]);
        
        $response->assertStatus(422);
        $response->assertJson(['message' => 'No puedes traspasar la membresía al mismo cliente.']);
    }

    /** @test */
    public function traspaso_ajusta_fecha_vencimiento_original()
    {
        $this->actingAs($this->user);
        
        $clienteDestino = Cliente::factory()->create(['activo' => true]);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        // Crear pago completo
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $this->cliente->id,
            'monto_total' => 30000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'id_metodo_pago' => 1,
            'fecha_pago' => now(),
        ]);
        
        $response = $this->post(route('admin.inscripciones.traspasar', $inscripcion), [
            'id_cliente_destino' => $clienteDestino->id,
            'motivo_traspaso' => 'Test traspaso',
        ]);
        
        $response->assertStatus(200);
        
        // Verificar que la inscripción original tiene fecha ajustada
        $inscripcion->refresh();
        $this->assertEquals(106, $inscripcion->id_estado); // Traspasada
        $this->assertEquals(now()->format('Y-m-d'), $inscripcion->fecha_vencimiento->format('Y-m-d'));
    }

    /** @test */
    public function traspaso_marca_pagos_originales_sin_saldo_pendiente()
    {
        $this->actingAs($this->user);
        
        $clienteDestino = Cliente::factory()->create(['activo' => true]);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        // Crear pago parcial
        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $this->cliente->id,
            'monto_total' => 30000,
            'monto_abonado' => 20000,
            'monto_pendiente' => 10000,
            'id_estado' => 202,
            'id_metodo_pago' => 1,
            'fecha_pago' => now(),
        ]);
        
        $response = $this->post(route('admin.inscripciones.traspasar', $inscripcion), [
            'id_cliente_destino' => $clienteDestino->id,
            'motivo_traspaso' => 'Test traspaso con deuda',
            'ignorar_deuda' => true,
            'transferir_deuda' => true,
        ]);
        
        $response->assertStatus(200);
        
        // Verificar que el pago original quedó sin saldo pendiente
        $pago->refresh();
        $this->assertEquals(205, $pago->id_estado); // Traspasado
        $this->assertEquals(0, $pago->monto_pendiente);
        $this->assertEquals($pago->monto_total, $pago->monto_abonado);
    }

    // =====================================================
    // TESTS DE CLIENTES
    // =====================================================

    /** @test */
    public function no_permite_crear_cliente_con_rut_duplicado()
    {
        $this->actingAs($this->user);
        
        // El cliente de setUp ya tiene un RUT
        $rutExistente = $this->cliente->run_pasaporte;
        
        $response = $this->post(route('admin.clientes.store'), [
            'run_pasaporte' => $rutExistente, // RUT duplicado
            'nombres' => 'Nuevo',
            'apellido_paterno' => 'Cliente',
            'celular' => '912345678',
            'email' => 'nuevo@test.com',
        ]);
        
        $response->assertSessionHasErrors('run_pasaporte');
    }

    /** @test */
    public function no_permite_pago_para_cliente_inactivo()
    {
        $this->actingAs($this->user);
        
        // Desactivar cliente
        $this->cliente->update(['activo' => false]);
        
        $inscripcion = Inscripcion::create([
            'id_cliente' => $this->cliente->id,
            'id_membresia' => $this->membresia->id,
            'fecha_inscripcion' => now(),
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'precio_base' => 30000,
            'precio_final' => 30000,
            'id_estado' => 100,
        ]);
        
        $response = $this->post(route('admin.pagos.store'), [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => 1,
            'form_submit_token' => uniqid(),
        ]);
        
        $response->assertSessionHas('error');
    }
}
