<?php

namespace Tests\Feature\Regresiones;

use App\Models\ContenidoWeb;
use Tests\CasoConCatalogos;

/**
 * El arriendo por horas para universidades e institutos, en su propia página.
 */
class ArriendoParaInstitucionesTest extends CasoConCatalogos
{
    public function test_la_pagina_de_arriendo_tiene_su_titulo_y_el_formulario(): void
    {
        $this->get('/arriendo-por-horas')
            ->assertOk()
            ->assertSee('<title>Arriendo de gimnasio por horas para clases', false)
            ->assertSee('La sala se comparte con los socios')
            ->assertSee('id="instituciones"', false)
            ->assertSee('"@type":"Service"', false);
    }

    public function test_sin_logos_ni_fotos_no_hay_franjas_vacias(): void
    {
        $this->get('/arriendo-por-horas')
            ->assertDontSee('Ya entrenan aquí')
            ->assertDontSee('El espacio');
    }

    public function test_salen_las_instituciones_y_las_fotos_que_estan_en_la_web(): void
    {
        ContenidoWeb::create(['tipo' => 'institucion', 'titulo' => 'IP Virginio Gómez', 'activo' => true, 'orden' => 1]);
        ContenidoWeb::create(['tipo' => 'institucion', 'titulo' => 'Oculta', 'activo' => false, 'orden' => 2]);
        ContenidoWeb::create(['tipo' => 'arriendo', 'titulo' => 'Grupo en la sala de máquinas', 'imagen' => 'web/x.webp', 'activo' => true, 'orden' => 1]);

        $this->get('/arriendo-por-horas')
            ->assertSee('Ya entrenan aquí')
            ->assertSee('IP Virginio Gómez')
            ->assertDontSee('Oculta')
            ->assertSee('alt="Grupo en la sala de máquinas"', false)
            ->assertSee('Ya entrenan aquí IP Virginio Gómez', false);
    }

    public function test_convenios_enlaza_al_arriendo_y_el_sitemap_lo_lleva(): void
    {
        $this->get('/convenios')->assertOk()->assertSee(route('landing.arriendo'), false);

        $this->get('/sitemap.xml')->assertSee(route('landing.arriendo'), false);
    }

    public function test_se_anota_una_institucion_sin_logo_desde_el_panel(): void
    {
        $this->actingAs($this->administrador())->get('/panel/web/institucion')->assertOk();
        $this->actingAs($this->administrador())->get('/panel/web/arriendo')->assertOk();

        $this->actingAs($this->administrador())
            ->post('/panel/web/institucion', ['titulo' => 'Santo Tomás', 'activo' => true])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contenidos_web', ['tipo' => 'institucion', 'titulo' => 'Santo Tomás']);
    }

    public function test_recepcion_no_toca_el_arriendo(): void
    {
        $this->actingAs($this->recepcionista())
            ->post('/panel/web/institucion', ['titulo' => 'Otra', 'activo' => true])
            ->assertForbidden();
    }
}
