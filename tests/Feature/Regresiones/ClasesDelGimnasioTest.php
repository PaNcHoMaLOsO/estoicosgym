<?php

namespace Tests\Feature\Regresiones;

use App\Models\Clase;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * Las clases del gimnasio (judo, lucha…) y su página en la web.
 *
 * Abiertas a cualquiera y con mensualidad; no son los talleres. Sin clases
 * activas la página no existe y el menú no la enlaza.
 */
class ClasesDelGimnasioTest extends CasoConCatalogos
{
    private function clase(array $datos = []): Clase
    {
        return Clase::create($datos + [
            'nombre' => 'Judo',
            'para_quien' => 'Niños desde 8 años',
            'profesor' => 'Pedro Soto',
            'precio_mensual' => 25000,
            'color' => 'azul',
            'horario' => [
                ['dia' => 'miercoles', 'desde' => '19:00', 'hasta' => '20:30'],
                ['dia' => 'lunes', 'desde' => '19:00', 'hasta' => '20:30'],
            ],
            'activo' => true,
            'orden' => 1,
        ]);
    }

    public function test_sin_clases_no_hay_pagina_ni_enlace(): void
    {
        $this->get('/clases')->assertNotFound();

        $this->get('/planes')->assertOk()->assertDontSee(route('landing.clases'), false);
        $this->get('/sitemap.xml')->assertDontSee(route('landing.clases'), false);
    }

    public function test_con_clases_se_ven_el_calendario_el_precio_y_el_whatsapp(): void
    {
        Ajustes::guardar(['web.whatsapp' => '+56 9 8765 4321']);
        $this->clase();

        $this->get('/clases')
            ->assertOk()
            ->assertSee('<title>Clase de Judo', false)
            ->assertSee('id="calendario"', false)
            ->assertSee('19:00 a 20:30')
            // Los días con la misma hora, juntos y en orden de la semana.
            ->assertSee('Lun y Mié · 19:00 a 20:30')
            ->assertSee('$25.000')
            ->assertSee('al mes')
            ->assertSee('Abiertas a todos, no hace falta ser socio.')
            ->assertSee('https://wa.me/56987654321?text=' . rawurlencode('Hola, quiero inscribirme en la clase de Judo'), false)
            ->assertSee('"unitText":"MONTH"', false)
            ->assertSee('"priceCurrency":"CLP"', false);

        $this->get('/planes')->assertSee(route('landing.clases'), false);
        $this->get('/sitemap.xml')->assertSee(route('landing.clases'), false);
    }

    public function test_sin_precio_dice_consulta_el_valor(): void
    {
        $this->clase(['nombre' => 'Kickboxing', 'precio_mensual' => null]);

        $this->get('/clases')->assertOk()->assertSee('Consulta el valor')->assertDontSee('"offers"', false);
    }

    public function test_una_clase_oculta_no_sale(): void
    {
        $this->clase();
        $this->clase(['nombre' => 'Esgrima secreta', 'activo' => false, 'orden' => 2]);

        $this->get('/clases')->assertOk()->assertSee('Judo')->assertDontSee('Esgrima secreta');
    }

    public function test_solo_ocultas_es_como_no_tener(): void
    {
        $this->clase(['activo' => false]);

        $this->get('/clases')->assertNotFound();
        $this->get('/planes')->assertDontSee(route('landing.clases'), false);
    }

    public function test_el_administrador_crea_una_clase_con_su_horario(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)->get('/panel/clases')->assertOk();

        $this->actingAs($admin)
            ->post('/panel/clases', [
                'nombre' => 'Lucha olímpica',
                'para_quien' => 'Adultos, todo nivel',
                'precio_mensual' => 25000,
                'color' => 'verde',
                'activo' => true,
                'horario' => [
                    ['dia' => 'martes', 'desde' => '19:30', 'hasta' => '21:00'],
                    ['dia' => 'jueves', 'desde' => '19:30', 'hasta' => '21:00'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $clase = Clase::where('nombre', 'Lucha olímpica')->firstOrFail();
        $this->assertCount(2, $clase->horario);
        $this->assertSame('Mar y Jue · 19:30 a 21:00', $clase->horarioEnUnaLinea());
        $this->assertSame(25000, $clase->precio_mensual);

        $this->get('/clases')->assertOk()->assertSee('Lucha olímpica');
    }

    public function test_un_horario_al_reves_o_vacio_se_rechaza(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)
            ->post('/panel/clases', [
                'nombre' => 'Boxeo',
                'color' => 'rojo',
                'horario' => [['dia' => 'lunes', 'desde' => '20:30', 'hasta' => '19:00']],
            ])
            ->assertSessionHasErrors('horario.0.hasta');

        $this->actingAs($admin)
            ->post('/panel/clases', [
                'nombre' => 'Boxeo',
                'color' => 'rojo',
                'horario' => [['dia' => 'lunes', 'desde' => '20:30', 'hasta' => '20:30']],
            ])
            ->assertSessionHasErrors('horario.0.hasta');

        $this->actingAs($admin)
            ->post('/panel/clases', ['nombre' => 'Boxeo', 'color' => 'rojo', 'horario' => []])
            ->assertSessionHasErrors('horario');

        $this->assertDatabaseMissing('clases', ['nombre' => 'Boxeo']);
    }

    public function test_recepcion_no_toca_las_clases(): void
    {
        $recepcion = $this->recepcionista();
        $clase = $this->clase();

        $this->actingAs($recepcion)->get('/panel/clases')->assertForbidden();
        $this->actingAs($recepcion)
            ->post('/panel/clases', ['nombre' => 'Otra', 'color' => 'rojo', 'horario' => [['dia' => 'lunes', 'desde' => '10:00', 'hasta' => '11:00']]])
            ->assertForbidden();
        $this->actingAs($recepcion)->delete("/panel/clases/{$clase->uuid}")->assertForbidden();

        $this->assertDatabaseHas('clases', ['uuid' => $clase->uuid]);
    }
}
