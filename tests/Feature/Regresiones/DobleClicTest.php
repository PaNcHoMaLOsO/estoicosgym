<?php

namespace Tests\Feature\Regresiones;

use App\Models\Convenio;
use App\Models\Especialista;
use Tests\CasoConCatalogos;

/**
 * El doble clic en lo que no tenía token propio (9-oct-2026): un convenio, un
 * plan, un profesional, un usuario… El mismo envío dos veces seguidas se hace
 * una sola vez (EvitaDobleEnvio, 'una-vez' en las rutas). Lo que mueve plata ya
 * lo cuida su token: ver DoblesEnviosTest.
 */
class DobleClicTest extends CasoConCatalogos
{
    private ?\App\Models\User $quien = null;

    /** Siempre la misma persona: el doble clic es de alguien, no de dos. */
    private function admin()
    {
        return $this->actingAs($this->quien ??= $this->administrador());
    }

    /** Un profesional no tiene nombre único: el doble clic lo creaba dos veces (el segundo «camila-rojas-2»). */
    public function test_el_doble_clic_crea_una_sola_vez(): void
    {
        $datos = ['nombre' => 'Camila Rojas', 'especialidad' => 'Nutricionista', 'activo' => true];

        $this->admin()->post('/panel/especialistas', $datos)->assertSessionHasNoErrors();
        $this->admin()->post('/panel/especialistas', $datos)->assertRedirect()->assertSessionHas('info');

        $this->assertSame(1, Especialista::where('nombre', 'Camila Rojas')->count());

        // Otro distinto, al tiro, sí pasa.
        $this->admin()->post('/panel/especialistas', ['nombre' => 'Diego Soto'] + $datos)->assertSessionHasNoErrors();
        $this->assertSame(2, Especialista::count());
    }

    public function test_con_un_error_se_puede_reenviar_al_tiro(): void
    {
        $malo = ['nombre' => '', 'especialidad' => 'Nutricionista', 'activo' => true];

        $this->admin()->post('/panel/especialistas', $malo)->assertSessionHasErrors('nombre');
        // El mismo envío con el error: vuelve a validar, no dice «ya se estaba guardando».
        $this->admin()->post('/panel/especialistas', $malo)->assertSessionHasErrors('nombre')->assertSessionMissing('info');

        $this->admin()->post('/panel/especialistas', ['nombre' => 'Camila Rojas'] + $malo)->assertSessionHasNoErrors();
        $this->assertSame(1, Especialista::count());
    }

    public function test_pasada_la_ventana_se_puede_volver_a_crear(): void
    {
        $datos = ['nombre' => 'Bomberos', 'tipo' => 'organizacion', 'activo' => true];

        $this->admin()->post('/panel/convenios', $datos);
        $this->travel(\App\Http\Middleware\EvitaDobleEnvio::VENTANA + 1)->seconds();
        $this->admin()->post('/panel/convenios', $datos)->assertSessionMissing('info');
    }
}
