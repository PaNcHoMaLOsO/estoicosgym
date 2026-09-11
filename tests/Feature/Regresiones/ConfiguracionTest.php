<?php

namespace Tests\Feature\Regresiones;

use App\Models\Convenio;
use App\Support\Ajustes;
use App\Support\Programador;
use Illuminate\Support\Facades\Cache;
use Tests\CasoConCatalogos;

/**
 * Configuración como un solo lugar: una portada con lo que falta y un menú que
 * marca lo pendiente, en todas sus pantallas.
 *
 * Lo que se vigila: que la portada diga lo que de verdad falta —y deje de
 * decirlo cuando se arregla—, que el menú sepa qué marcar también dentro de un
 * catálogo, y que las tareas automáticas que nunca corrieron se vean en vez de
 * pasar calladas.
 */
class ConfiguracionTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
        Cache::forget('programador:latido');
    }

    private function props(string $url): array
    {
        return $this->actingAs($this->administrador())
            ->get($url)
            ->assertOk()
            ->viewData('page')['props'];
    }

    private function punto(string $clave): array
    {
        return collect($this->props('/panel/configuracion')['puntos'])->firstWhere('clave', $clave);
    }

    public function test_la_portada_dice_que_faltan_los_datos_del_gimnasio_y_deja_de_decirlo(): void
    {
        $punto = $this->punto('gimnasio');

        $this->assertSame('falta', $punto['estado']);
        $this->assertStringContainsString('la dirección, el teléfono y el correo', $punto['detalle']);

        $this->actingAs($this->administrador())->put('/panel/configuracion', [
            'gimnasio.direccion' => 'Colón 123',
            'gimnasio.telefono' => '+56 43 212 3456',
            'gimnasio.email' => 'contacto@progym.cl',
        ])->assertSessionHasNoErrors();

        Ajustes::olvidar();

        $this->assertSame('ok', $this->punto('gimnasio')['estado']);
    }

    /** Las tareas que nunca corrieron se ven: en este gimnasio no corrieron nunca y nadie lo supo. */
    public function test_las_tareas_que_nunca_corrieron_se_marcan(): void
    {
        $punto = $this->punto('tareas');

        $this->assertSame('falta', $punto['estado']);
        $this->assertStringContainsString('Nunca han corrido', $punto['detalle']);

        Programador::latir();

        $this->assertSame('ok', $this->punto('tareas')['estado']);
    }

    public function test_un_convenio_de_la_web_sin_logo_se_marca(): void
    {
        Convenio::create([
            'nombre' => 'UCSC',
            'tipo' => 'institucion_educativa',
            'activo' => true,
            'mostrar_en_web' => true,
        ]);

        $punto = $this->punto('convenios');

        $this->assertSame('falta', $punto['estado']);
        $this->assertStringContainsString('no tiene logo', $punto['detalle']);
    }

    /** El menú marca lo pendiente en todas las pantallas de Configuración, catálogos incluidos. */
    public function test_el_menu_marca_lo_pendiente_tambien_dentro_de_un_catalogo(): void
    {
        $avisos = $this->props('/panel/membresias')['configuracion']['avisos'];

        $this->assertArrayHasKey('/panel/configuracion/gimnasio', $avisos);
        $this->assertArrayHasKey('/panel/configuracion/tareas', $avisos);
    }

    /** Fuera de Configuración no se calcula: son varias cuentas que el mesón no necesita. */
    public function test_fuera_de_configuracion_no_se_calcula(): void
    {
        $this->assertNull($this->props('/panel/clientes')['configuracion']);
    }

    public function test_la_pagina_de_tareas_dice_como_activarlas(): void
    {
        $extra = $this->props('/panel/configuracion/tareas')['extra'];

        $this->assertFalse($extra['tareas']['corriendo']);
        $this->assertStringContainsString('schtasks /Create', $extra['tareas']['comando']);
        $this->assertStringContainsString('schedule:run', $extra['tareas']['comando']);
        $this->assertCount(3, $extra['tareas']['tareas']);
        $this->assertIsBool($extra['correoConfigurado']);
    }

    public function test_la_pagina_de_google_muestra_como_sale_la_portada(): void
    {
        $vista = $this->props('/panel/configuracion/web')['extra']['vistaGoogle'];

        $this->assertSame('PRO GYM | Gimnasio en Los Ángeles, Biobío', $vista['titulo']);
        $this->assertStringContainsString('Gimnasio en Los Ángeles', $vista['descripcionAutomatica']);
    }
}
