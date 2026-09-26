<?php

namespace Tests\Feature\Regresiones;

use App\Models\Falla;
use App\Support\RegistroDeFallas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\CasoConCatalogos;

/**
 * El registro de fallas: lo que sale mal queda a la vista en el panel, una
 * vez por falla y con cuántas veces pasó, sin claves.
 */
class RegistroDeFallasTest extends CasoConCatalogos
{
    public function test_una_excepcion_queda_registrada_con_su_lugar(): void
    {
        Route::middleware('web')->get('/prueba-que-revienta', function () {
            throw new \RuntimeException('Se rompió el cálculo 42');
        });

        $this->get('/prueba-que-revienta')->assertStatus(500);

        $falla = Falla::sole();
        $this->assertSame('servidor', $falla->origen);
        $this->assertSame(\RuntimeException::class, $falla->tipo);
        $this->assertSame('Se rompió el cálculo 42', $falla->mensaje);
        $this->assertStringContainsString('tests/Feature/Regresiones/RegistroDeFallasTest.php:', $falla->lugar);
        $this->assertSame('/prueba-que-revienta', $falla->url);
    }

    /** La misma falla con otros números es una fila con su contador, no dos. */
    public function test_la_misma_falla_se_cuenta_no_se_repite(): void
    {
        Log::error('No salió el correo del pago 1532');
        Log::error('No salió el correo del pago 88');

        $this->assertSame(1, Falla::count());
        $this->assertSame(2, Falla::first()->veces);
    }

    /** Los avisos y lo informativo no son fallas. */
    public function test_solo_se_anotan_los_errores(): void
    {
        Log::info('Todo bien');
        Log::warning('Algo raro');

        $this->assertSame(0, Falla::count());
    }

    public function test_las_claves_no_se_guardan(): void
    {
        Log::error('Falló el correo', ['smtp_clave' => 'abcd efgh', 'password' => 'secreta', 'para' => 'socio@correo.cl']);

        $contexto = Falla::first()->contexto;
        $this->assertSame('(oculto)', $contexto['smtp_clave']);
        $this->assertSame('(oculto)', $contexto['password']);
        $this->assertSame('socio@correo.cl', $contexto['para']);
    }

    /** Resuelta y vuelve a pasar: se reabre sola. */
    public function test_una_resuelta_que_vuelve_se_reabre(): void
    {
        Log::error('Falla que vuelve');
        $falla = Falla::first();

        $this->actingAs($this->administrador())->patch("/panel/fallas/{$falla->id}/resolver");
        $this->assertNotNull($falla->fresh()->resuelta_en);

        Log::error('Falla que vuelve');
        $this->assertNull($falla->fresh()->resuelta_en);
        $this->assertSame(2, $falla->fresh()->veces);
    }

    public function test_el_navegador_avisa_una_pantalla_rota(): void
    {
        $this->actingAs($this->recepcionista())
            ->postJson('/fallas/navegador', [
                'mensaje' => "Cannot read properties of undefined (reading 'nombre')",
                'tipo' => 'TypeError',
                'archivo' => 'https://algo.trycloudflare.com/build/assets/Ficha-abc.js',
                'linea' => 12,
                'pantalla' => 'https://algo.trycloudflare.com/panel/clientes/123',
            ])
            ->assertNoContent();

        $falla = Falla::sole();
        $this->assertSame('navegador', $falla->origen);
        $this->assertSame('/build/assets/Ficha-abc.js:12', $falla->lugar);
        $this->assertSame('/panel/clientes/123', $falla->url);
    }

    public function test_sin_sesion_no_se_puede_avisar(): void
    {
        $this->postJson('/fallas/navegador', ['mensaje' => 'x'])->assertUnauthorized();
        $this->assertSame(0, Falla::count());
    }

    /** Los mensajes pueden traer datos de socios: el registro es del administrador. */
    public function test_recepcion_no_ve_el_registro(): void
    {
        $this->actingAs($this->recepcionista())->get('/panel/fallas')->assertForbidden();
        $this->actingAs($this->administrador())->get('/panel/fallas')->assertOk();
    }

    public function test_se_borran_las_viejas(): void
    {
        Log::error('Vieja');
        Falla::query()->update(['ultima_vez' => now()->subDays(100)]);
        Log::error('Nueva');

        $this->assertSame(1, RegistroDeFallas::limpiar());
        $this->assertSame('Nueva', Falla::sole()->mensaje);
    }
}
