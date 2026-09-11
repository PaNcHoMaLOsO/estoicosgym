<?php

namespace Tests\Feature\Regresiones;

use App\Models\User;
use Tests\CasoConCatalogos;

/**
 * Adónde se llega al entrar al sistema.
 *
 * Entrar llevaba a /dashboard, el tablero viejo de AdminLTE —todavía con el
 * nombre de antes y sus cifras calculadas a la manera vieja—, y no al Resumen
 * del panel de PRO GYM.
 */
class EntradaAlPanelTest extends CasoConCatalogos
{
    public function test_entrar_lleva_al_panel(): void
    {
        User::factory()->create([
            'email' => 'recepcion@progym.cl',
            'password' => 'clave-segura-1',
            'id_rol' => 2,
            'activo' => true,
        ]);

        $this->post('/login', ['email' => 'recepcion@progym.cl', 'password' => 'clave-segura-1'])
            ->assertRedirect('/panel');
    }

    /** Un marcador guardado del tablero viejo también termina en el panel. */
    public function test_el_tablero_viejo_lleva_al_panel(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/dashboard')
            ->assertRedirect('/panel');
    }

    public function test_recepcion_puede_abrir_el_panel(): void
    {
        $this->actingAs($this->recepcionista())->get('/panel')->assertOk();
    }
}
