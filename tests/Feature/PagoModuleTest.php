<?php

namespace Tests\Feature;

use App\Models\Pago;
use Tests\TestCase;

class PagoModuleTest extends TestCase
{
    /** @test */
    public function modulo_pagos_carga_correctamente()
    {
        // Verificar que el modelo Pago existe
        $this->assertTrue(class_exists(Pago::class));
        
        // Verificar que tiene UUID
        $pago = new Pago();
        $this->assertTrue(method_exists($pago, 'getRouteKeyName'));
        
        // Verificar que usa UUID como route key
        $this->assertEquals('uuid', $pago->getRouteKeyName());
    }

    /** @test */
    public function pago_tiene_fillable_correcto()
    {
        $pago = new Pago();
        
        // Verificar que los campos principales están en fillable
        $fillable = $pago->getFillable();
        $this->assertContains('monto_abonado', $fillable);
        $this->assertContains('monto_pendiente', $fillable);
        $this->assertContains('fecha_pago', $fillable);
        $this->assertContains('id_metodo_pago_principal', $fillable);
        $this->assertContains('id_estado', $fillable);
    }

    /** @test */
    public function pago_tiene_relaciones_correctas()
    {
        $pago = new Pago();
        
        // Verificar que tiene métodos de relación
        $this->assertTrue(method_exists($pago, 'inscripcion'));
        $this->assertTrue(method_exists($pago, 'metodoPagoPrincipal'));
        $this->assertTrue(method_exists($pago, 'estado'));
        $this->assertTrue(method_exists($pago, 'motivoDescuento'));
    }

    /** @test */
    public function pago_modelo_tiene_timestamps()
    {
        $pago = new Pago();
        $this->assertTrue($pago->timestamps);
    }

    /** @test */
    public function controlador_pagos_existe()
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\Admin\PagoController::class));
    }

    /** @test */
    public function pago_tabla_esta_configurada()
    {
        $pago = new Pago();
        $this->assertEquals('pagos', $pago->getTable());
    }

    /** @test */
    public function pago_usa_uuid_como_ruta_key()
    {
        $pago = new Pago();
        $routeKey = $pago->getRouteKeyName();
        $this->assertEquals('uuid', $routeKey);
    }
}
