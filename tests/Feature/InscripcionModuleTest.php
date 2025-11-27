<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Inscripcion;
use App\Http\Controllers\Admin\InscripcionController;

class InscripcionModuleTest extends TestCase
{
    /**
     * Test: Módulo inscripciones carga correctamente
     * Verifica que el modelo Inscripcion exista y sea accesible
     */
    public function test_modulo_inscripciones_carga_correctamente()
    {
        $this->assertTrue(class_exists(Inscripcion::class));
        $this->assertIsObject(new Inscripcion());
    }

    /**
     * Test: Inscripcion tiene fillable correcto
     * Verifica que los campos fillable sean accesibles y completos
     */
    public function test_inscripcion_tiene_fillable_correcto()
    {
        $inscripcion = new Inscripcion();
        $fillable = $inscripcion->getFillable();
        
        $camposEsperados = [
            'uuid',
            'id_cliente',
            'id_membresia',
            'id_convenio',
            'id_precio_acordado',
            'fecha_inscripcion',
            'fecha_inicio',
            'fecha_vencimiento',
            'dia_pago',
            'precio_base',
            'descuento_aplicado',
            'precio_final',
            'id_motivo_descuento',
            'id_estado',
            'observaciones',
            'pausada',
            'dias_pausa',
            'fecha_pausa_inicio',
            'fecha_pausa_fin',
            'razon_pausa',
            'pausas_realizadas',
            'max_pausas_permitidas',
        ];
        
        foreach ($camposEsperados as $campo) {
            $this->assertContains($campo, $fillable, "Campo $campo no está en fillable");
        }
    }

    /**
     * Test: Inscripcion tiene relaciones correctas
     * Verifica que todas las relaciones estén definidas
     */
    public function test_inscripcion_tiene_relaciones_correctas()
    {
        $inscripcion = new Inscripcion();
        
        // Verificar que los métodos de relación existen
        $this->assertTrue(method_exists($inscripcion, 'cliente'));
        $this->assertTrue(method_exists($inscripcion, 'membresia'));
        $this->assertTrue(method_exists($inscripcion, 'estado'));
        $this->assertTrue(method_exists($inscripcion, 'convenio'));
        $this->assertTrue(method_exists($inscripcion, 'motivoDescuento'));
        $this->assertTrue(method_exists($inscripcion, 'pagos'));
        $this->assertTrue(method_exists($inscripcion, 'precioAcordado'));
    }

    /**
     * Test: Inscripcion modelo tiene timestamps
     * Verifica que created_at y updated_at estén configurados
     */
    public function test_inscripcion_modelo_tiene_timestamps()
    {
        $inscripcion = new Inscripcion();
        
        $this->assertTrue($inscripcion->timestamps);
        $this->assertContains('created_at', $inscripcion->getDates());
        $this->assertContains('updated_at', $inscripcion->getDates());
    }

    /**
     * Test: Inscripcion usa UUID como route key
     * Verifica que la ruta use UUID en lugar de ID
     */
    public function test_inscripcion_usa_uuid_como_ruta_key()
    {
        $inscripcion = new Inscripcion();
        
        $this->assertEquals('uuid', $inscripcion->getRouteKeyName());
    }

    /**
     * Test: Controlador inscripciones existe
     * Verifica que el controlador esté disponible
     */
    public function test_controlador_inscripciones_existe()
    {
        $this->assertTrue(class_exists(InscripcionController::class));
        $this->assertIsObject(new InscripcionController());
    }

    /**
     * Test: Inscripcion tabla está configurada
     * Verifica que la tabla sea 'inscripciones'
     */
    public function test_inscripcion_tabla_esta_configurada()
    {
        $inscripcion = new Inscripcion();
        
        $this->assertEquals('inscripciones', $inscripcion->getTable());
    }

    /**
     * Test: Inscripcion tiene métodos de pausa
     * Verifica que los métodos de gestión de pausa existan
     */
    public function test_inscripcion_tiene_metodos_de_pausa()
    {
        $inscripcion = new Inscripcion();
        
        $this->assertTrue(method_exists($inscripcion, 'pausar'));
        $this->assertTrue(method_exists($inscripcion, 'reanudar'));
        $this->assertTrue(method_exists($inscripcion, 'verificarPausaExpirada'));
        $this->assertTrue(method_exists($inscripcion, 'obtenerInfoPausa'));
        $this->assertTrue(method_exists($inscripcion, 'puedePausarse'));
        $this->assertTrue(method_exists($inscripcion, 'estaPausada'));
    }

    /**
     * Test: Inscripcion tiene métodos de pago
     * Verifica que los métodos de estado de pago existan
     */
    public function test_inscripcion_tiene_metodos_de_pago()
    {
        $inscripcion = new Inscripcion();
        
        $this->assertTrue(method_exists($inscripcion, 'obtenerEstadoPago'));
    }

    /**
     * Test: Inscripcion tiene atributos de casting
     * Verifica que los tipos de dato estén configurados
     */
    public function test_inscripcion_tiene_atributos_de_casting()
    {
        $inscripcion = new Inscripcion();
        $casts = $inscripcion->getCasts();
        
        // Verificar que los campos críticos tengan casting
        $this->assertArrayHasKey('fecha_inscripcion', $casts);
        $this->assertArrayHasKey('fecha_inicio', $casts);
        $this->assertArrayHasKey('fecha_vencimiento', $casts);
        $this->assertArrayHasKey('pausada', $casts);
        $this->assertArrayHasKey('precio_base', $casts);
        $this->assertArrayHasKey('descuento_aplicado', $casts);
        $this->assertArrayHasKey('precio_final', $casts);
    }

    /**
     * Test: Controlador tiene métodos CRUD
     * Verifica que todos los métodos CRUD existan
     */
    public function test_controlador_tiene_metodos_crud()
    {
        $controller = new InscripcionController();
        
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'create'));
        $this->assertTrue(method_exists($controller, 'store'));
        $this->assertTrue(method_exists($controller, 'show'));
        $this->assertTrue(method_exists($controller, 'edit'));
        $this->assertTrue(method_exists($controller, 'update'));
        $this->assertTrue(method_exists($controller, 'destroy'));
    }

    /**
     * Test: Controlador tiene métodos de filtrado
     * Verifica que los métodos auxiliares para filtros existan
     */
    public function test_controlador_tiene_metodos_de_filtrado()
    {
        $controller = new InscripcionController();
        
        // Usar reflection para acceder a métodos protegidos
        $reflection = new \ReflectionClass($controller);
        
        $this->assertTrue($reflection->hasMethod('aplicarFiltros'));
        $this->assertTrue($reflection->hasMethod('aplicarOrdenamiento'));
    }

    /**
     * Test: Controlador tiene métodos de cálculo
     * Verifica que los métodos de cálculo de precios existan
     */
    public function test_controlador_tiene_metodos_de_calculo()
    {
        $controller = new InscripcionController();
        
        // Usar reflection para acceder a métodos protegidos
        $reflection = new \ReflectionClass($controller);
        
        $this->assertTrue($reflection->hasMethod('obtenerPrecioMembresia'));
        $this->assertTrue($reflection->hasMethod('calcularDescuentoTotal'));
        $this->assertTrue($reflection->hasMethod('calcularFechaVencimiento'));
        $this->assertTrue($reflection->hasMethod('crearPagoInicial'));
    }
}
