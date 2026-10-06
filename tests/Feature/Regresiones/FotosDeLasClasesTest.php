<?php

namespace Tests\Feature\Regresiones;

use App\Models\Clase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * La galería de cada clase: además de la foto principal, hasta ocho más que
 * salen en su página.
 */
class FotosDeLasClasesTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function clase(array $datos = []): Clase
    {
        return Clase::create($datos + [
            'nombre' => 'Judo',
            'color' => 'azul',
            'horario' => [['dia' => 'lunes', 'desde' => '19:00', 'hasta' => '20:30']],
            'activo' => true,
            'orden' => 1,
        ]);
    }

    private function guardar(Clase $clase, array $extra)
    {
        return $this->actingAs($this->administrador())->post("/panel/clases/{$clase->uuid}", $extra + [
            '_method' => 'put',
            'nombre' => $clase->nombre,
            'color' => $clase->color,
            'activo' => true,
            'horario' => $clase->horario,
        ]);
    }

    private function foto(string $nombre = 'foto.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($nombre, 800, 600);
    }

    public function test_se_suben_varias_y_salen_en_su_pagina(): void
    {
        $clase = $this->clase();

        $this->guardar($clase, ['fotos_nuevas' => [$this->foto('a.jpg'), $this->foto('b.jpg'), $this->foto('c.jpg')]])
            ->assertSessionHasNoErrors();

        $rutas = $clase->fresh()->rutasDeFotos();
        $this->assertCount(3, $rutas);
        foreach ($rutas as $ruta) {
            Storage::disk('public')->assertExists($ruta);
        }

        $this->get('/clases/judo')
            ->assertOk()
            ->assertSee('>Fotos</h2>', false)
            ->assertSee('Clase de Judo en', false)
            ->assertSee('foto 3', false);
    }

    public function test_sin_galeria_no_hay_franja_vacia(): void
    {
        $this->clase();

        $this->get('/clases/judo')->assertOk()->assertDontSee('>Fotos</h2>', false);
    }

    public function test_se_quita_una_y_se_borra_el_archivo(): void
    {
        $clase = $this->clase();
        $this->guardar($clase, ['fotos_nuevas' => [$this->foto(), $this->foto()]]);
        [$primera, $segunda] = $clase->fresh()->rutasDeFotos();

        // Una ruta que no es de la clase se ignora: llega del navegador.
        Storage::disk('public')->put('web/ajena.webp', 'x');

        $this->guardar($clase, ['fotos_quitar' => [$primera, 'web/ajena.webp']])->assertSessionHasNoErrors();

        $this->assertSame([$segunda], $clase->fresh()->rutasDeFotos());
        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists('web/ajena.webp');
    }

    public function test_no_pasa_del_tope(): void
    {
        $clase = $this->clase();
        $this->guardar($clase, ['fotos_nuevas' => array_map(fn () => $this->foto(), range(1, 6))])->assertSessionHasNoErrors();

        $this->guardar($clase, ['fotos_nuevas' => array_map(fn () => $this->foto(), range(1, 3))])
            ->assertSessionHasErrors('fotos_nuevas');

        $this->assertCount(6, $clase->fresh()->rutasDeFotos());

        // Quitando dos, caben las tres.
        $quitar = array_slice($clase->fresh()->rutasDeFotos(), 0, 2);
        $this->guardar($clase, ['fotos_quitar' => $quitar, 'fotos_nuevas' => array_map(fn () => $this->foto(), range(1, 3))])
            ->assertSessionHasNoErrors();
        $this->assertCount(Clase::MAX_FOTOS - 1, $clase->fresh()->rutasDeFotos());
    }

    public function test_al_borrar_la_clase_se_van_sus_fotos(): void
    {
        $clase = $this->clase();
        $this->guardar($clase, ['fotos_nuevas' => [$this->foto()]]);
        $ruta = $clase->fresh()->rutasDeFotos()[0];

        $this->actingAs($this->administrador())->delete("/panel/clases/{$clase->uuid}")->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_el_panel_las_muestra_para_editarlas(): void
    {
        $clase = $this->clase();
        $this->guardar($clase, ['fotos_nuevas' => [$this->foto()]]);

        $props = $this->actingAs($this->administrador())->get('/panel/clases')->assertOk()->viewData('page')['props'];

        $this->assertSame(Clase::MAX_FOTOS, $props['maxFotos']);
        $this->assertCount(1, $props['clases'][0]['fotos']);
        $this->assertArrayHasKey('ruta', $props['clases'][0]['fotos'][0]);
    }
}
