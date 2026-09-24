<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use Tests\CasoConCatalogos;

/**
 * Anotar el celular desde las listas del Resumen, sin abrir la ficha.
 *
 * A quien salía «sin contacto» había que abrirle la ficha, editar y volver
 * para poder escribirle. Ahora el número se pide ahí mismo y queda guardado.
 */
class AnotarCelularTest extends CasoConCatalogos
{
    public function test_guarda_el_celular_y_lo_devuelve(): void
    {
        $socio = Cliente::factory()->create(['celular' => null]);

        $this->actingAs($this->administrador())
            ->patchJson("/panel/clientes/{$socio->uuid}/celular", ['celular' => '+56 9 1234 5678'])
            ->assertOk()
            // La ficha lo guarda normalizado (solo dígitos, sin el +56): así lo
            // devuelve, que es como lo espera el enlace de WhatsApp.
            ->assertJson(['celular' => '912345678']);

        $this->assertSame('912345678', $socio->fresh()->celular);
    }

    public function test_un_numero_mal_escrito_se_rechaza(): void
    {
        $socio = Cliente::factory()->create(['celular' => null]);

        $this->actingAs($this->administrador())
            ->patchJson("/panel/clientes/{$socio->uuid}/celular", ['celular' => '12345'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('celular');

        $this->assertNull($socio->fresh()->celular);
    }
}
