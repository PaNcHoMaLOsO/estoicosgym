<?php

namespace Tests\Unit;

use App\Rules\RutValido;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests para validación de RUT chileno (Algoritmo Módulo 11)
 * 
 * El dígito verificador se calcula así:
 * - Si resultado = 11 → dígito verificador = 0
 * - Si resultado = 10 → dígito verificador = K
 * - Cualquier otro → el número correspondiente
 * 
 * RUTs verificados con calculadora:
 * - 12.345.678-5 (DV numérico)
 * - 10.727.983-0 (DV cero)
 * - 10.518.381-K (DV K)
 */
class RutValidoTest extends TestCase
{
    private RutValido $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new RutValido();
    }

    // =============================================
    // TESTS: DÍGITO VERIFICADOR NUMÉRICO (1-9)
    // =============================================

    #[Test]
    public function rut_valido_con_digito_verificador_numerico(): void
    {
        // RUTs válidos con dígitos 1-9 (verificados)
        $this->assertTrue($this->rule->passes('rut', '12.345.678-5'));
        $this->assertTrue($this->rule->passes('rut', '16.958.380-3'));
        $this->assertTrue($this->rule->passes('rut', '11.111.111-1'));
        $this->assertTrue($this->rule->passes('rut', '9.876.543-3')); // DV correcto es 3
    }

    #[Test]
    public function rut_valido_sin_formato_puntos_guion(): void
    {
        // RUTs sin puntos ni guiones
        $this->assertTrue($this->rule->passes('rut', '123456785'));
        $this->assertTrue($this->rule->passes('rut', '169583803'));
    }

    // =============================================
    // TESTS: DÍGITO VERIFICADOR 0 (CERO)
    // =============================================

    #[Test]
    public function rut_valido_con_digito_verificador_cero(): void
    {
        // RUTs con DV = 0 (verificados con calculadora)
        $this->assertTrue($this->rule->passes('rut', '10.727.983-0'));
        $this->assertTrue($this->rule->passes('rut', '10.000.004-0'));
        $this->assertTrue($this->rule->passes('rut', '18.585.543-0')); // Este también da 0
    }

    #[Test]
    public function rut_con_digito_cero_sin_formato(): void
    {
        $this->assertTrue($this->rule->passes('rut', '107279830'));
        $this->assertTrue($this->rule->passes('rut', '100000040'));
    }

    // =============================================
    // TESTS: DÍGITO VERIFICADOR K
    // =============================================

    #[Test]
    public function rut_valido_con_digito_verificador_k_mayuscula(): void
    {
        // RUTs con DV = K (verificados con calculadora)
        $this->assertTrue($this->rule->passes('rut', '10.518.381-K'));
        $this->assertTrue($this->rule->passes('rut', '10.000.013-K'));
    }

    #[Test]
    public function rut_valido_con_digito_verificador_k_minuscula(): void
    {
        // K minúscula también debe ser válida
        $this->assertTrue($this->rule->passes('rut', '10.518.381-k'));
        $this->assertTrue($this->rule->passes('rut', '10.000.013-k'));
    }

    #[Test]
    public function rut_con_k_sin_formato(): void
    {
        $this->assertTrue($this->rule->passes('rut', '10518381K'));
        $this->assertTrue($this->rule->passes('rut', '10000013k'));
    }

    // =============================================
    // TESTS: CASOS INVÁLIDOS
    // =============================================

    #[Test]
    public function rut_invalido_digito_verificador_incorrecto(): void
    {
        // DV incorrecto (debería ser 5, no 6)
        $this->assertFalse($this->rule->passes('rut', '12.345.678-6'));
        
        // DV incorrecto (debería ser K, no 9)
        $this->assertFalse($this->rule->passes('rut', '10.518.381-9'));
        
        // DV incorrecto (debería ser 0, no 1)
        $this->assertFalse($this->rule->passes('rut', '10.727.983-1'));
    }

    #[Test]
    public function rut_invalido_muy_corto(): void
    {
        $this->assertFalse($this->rule->passes('rut', '123-4'));
        $this->assertFalse($this->rule->passes('rut', '1234567')); // Solo 7 caracteres
    }

    #[Test]
    public function rut_invalido_muy_largo(): void
    {
        $this->assertFalse($this->rule->passes('rut', '123.456.789.012-3'));
    }

    #[Test]
    public function rut_invalido_con_letras_en_cuerpo(): void
    {
        $this->assertFalse($this->rule->passes('rut', '12.34A.678-5'));
        $this->assertFalse($this->rule->passes('rut', 'AB.CDE.FGH-I'));
    }

    // =============================================
    // TESTS: CAMPO VACÍO (OPCIONAL)
    // =============================================

    #[Test]
    public function rut_vacio_es_valido_porque_es_opcional(): void
    {
        // El campo RUT es opcional, entonces vacío es válido
        $this->assertTrue($this->rule->passes('rut', ''));
        $this->assertTrue($this->rule->passes('rut', null));
    }

    // =============================================
    // TESTS: FORMATOS ALTERNATIVOS
    // =============================================

    #[Test]
    public function rut_con_espacios_es_limpiado_y_validado(): void
    {
        // Espacios adicionales
        $this->assertTrue($this->rule->passes('rut', ' 12.345.678-5 '));
        $this->assertTrue($this->rule->passes('rut', '12 345 678-5'));
    }

    #[Test]
    public function rut_solo_con_guion_sin_puntos(): void
    {
        $this->assertTrue($this->rule->passes('rut', '12345678-5'));
        $this->assertTrue($this->rule->passes('rut', '10518381-K'));
        $this->assertTrue($this->rule->passes('rut', '10727983-0'));
    }

    // =============================================
    // TESTS: MENSAJE DE ERROR
    // =============================================

    #[Test]
    public function mensaje_de_error_es_correcto(): void
    {
        $expectedMessage = 'El RUT ingresado no es válido. Formato correcto: 12.345.678-5 o 123456785';
        $this->assertEquals($expectedMessage, $this->rule->message());
    }

    // =============================================
    // TESTS: CASOS ESPECIALES / EDGE CASES
    // =============================================

    #[Test]
    public function rut_minimo_valido_8_digitos(): void
    {
        // RUT de 8 caracteres (7 dígitos + 1 DV)
        $this->assertTrue($this->rule->passes('rut', '1.234.567-4'));
    }

    #[Test]
    public function rut_maximo_valido_9_digitos(): void
    {
        // RUT de 9 caracteres (8 dígitos + 1 DV)
        $this->assertTrue($this->rule->passes('rut', '12.345.678-5'));
    }

    #[Test]
    public function rut_con_ceros_al_inicio(): void
    {
        // RUTs que empiezan con números bajos
        $this->assertTrue($this->rule->passes('rut', '5.126.663-3'));
        $this->assertTrue($this->rule->passes('rut', '51266633'));
    }
}
