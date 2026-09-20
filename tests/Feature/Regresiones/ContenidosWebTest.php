<?php

namespace Tests\Feature\Regresiones;

use App\Models\ContenidoWeb;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Lo que se escribe en Página web: servicios, fotos, preguntas y testimonios.
 *
 * Lo que se vigila: que un testimonio no se publique sin el permiso de la
 * persona, que una foto de teléfono se achique antes de ir a la página, que
 * corregir el texto de una foto no la borre, y que recepción no toque la web.
 */
class ContenidosWebTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function admin()
    {
        return $this->actingAs($this->administrador());
    }

    public function test_la_seccion_se_abre_desde_el_panel(): void
    {
        foreach (array_keys(ContenidoWeb::TIPOS) as $tipo) {
            $this->admin()->get("/panel/web/{$tipo}")->assertOk();
        }
    }

    public function test_un_tipo_que_no_existe_no_abre(): void
    {
        $this->admin()->get('/panel/web/banner')->assertNotFound();
    }

    public function test_recepcion_no_toca_la_pagina_web(): void
    {
        $this->actingAs($this->recepcionista())->get('/panel/web/servicio')->assertForbidden();

        $this->actingAs($this->recepcionista())->post('/panel/web/pregunta', [
            'titulo' => '¿Abren los domingos?',
            'texto' => 'No.',
        ])->assertForbidden();

        $this->assertSame(0, ContenidoWeb::where('tipo', 'pregunta')->count());
    }

    public function test_se_crea_una_pregunta_pero_no_sale_en_la_web(): void
    {
        $this->admin()->post('/panel/web/pregunta', [
            'titulo' => '¿Abren los domingos?',
            'texto' => 'Sí, de 10:00 a 14:00.',
            'activo' => true,
        ])->assertSessionHasNoErrors();

        // Se guarda, pero la lista de preguntas se quitó de la web pública el
        // 2026-09-18 por decisión del dueño: ni en Contacto ni en la portada.
        $this->assertSame(1, ContenidoWeb::where('tipo', 'pregunta')->count());
        $this->get('/contacto')->assertOk()->assertDontSee('¿Abren los domingos?');
        $this->get('/')->assertOk()->assertDontSee('¿Abren los domingos?');
    }

    /** Una opinión con nombre es un dato personal: sin el permiso de la persona no se publica. */
    public function test_un_testimonio_sin_permiso_se_rechaza(): void
    {
        $this->admin()->post('/panel/web/testimonio', [
            'titulo' => 'Camila R.',
            'texto' => 'El mejor gimnasio de Los Ángeles.',
        ])->assertSessionHasErrors('con_permiso');

        $this->assertSame(0, ContenidoWeb::where('tipo', 'testimonio')->count());

        $this->admin()->post('/panel/web/testimonio', [
            'titulo' => 'Camila R.',
            'texto' => 'El mejor gimnasio de Los Ángeles.',
            'con_permiso' => true,
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('El mejor gimnasio de Los Ángeles.');
    }

    /** El ícono termina dentro de un atributo de la página: solo vale uno de la lista. */
    public function test_un_icono_que_no_es_de_la_lista_se_rechaza(): void
    {
        $this->admin()->post('/panel/web/servicio', [
            'titulo' => 'Spinning',
            'texto' => 'Clases de bicicleta.',
            'icono' => 'x" onmouseover="alert(1)',
        ])->assertSessionHasErrors('icono');
    }

    /** Una foto de teléfono se achica: diez de 4.000 píxeles harían la página lentísima. */
    public function test_una_foto_grande_se_achica_al_guardarla(): void
    {
        $this->admin()->post('/panel/web/foto', [
            'titulo' => 'Sala de máquinas',
            'imagen' => UploadedFile::fake()->image('sala.jpg', 2000, 1500),
        ])->assertSessionHasNoErrors();

        $foto = ContenidoWeb::where('tipo', 'foto')->firstOrFail();

        $this->assertSame([1600, 1200], array_slice(getimagesize(Storage::disk('public')->path($foto->imagen)), 0, 2));
        $this->get('/el-gimnasio')->assertOk()->assertSee('alt="Sala de máquinas"', false);
    }

    public function test_una_foto_sin_archivo_no_se_crea(): void
    {
        $this->admin()->post('/panel/web/foto', ['titulo' => 'Sala'])->assertSessionHasErrors('imagen');

        $this->assertSame(0, ContenidoWeb::where('tipo', 'foto')->count());
    }

    /**
     * EL QUE IMPORTA: corregir la descripción sin volver a elegir la foto no la borra.
     *
     * Es el mismo error que tuvo la foto de los socios.
     */
    public function test_editar_la_descripcion_no_borra_la_foto(): void
    {
        Storage::disk('public')->put('web/sala.jpg', 'foto');
        $foto = ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'Sala', 'imagen' => 'web/sala.jpg', 'activo' => true]);

        $this->admin()->put("/panel/web/contenido/{$foto->uuid}", [
            'titulo' => 'Sala de máquinas',
            'imagen' => null,
        ])->assertSessionHasNoErrors();

        $foto->refresh();

        $this->assertSame('Sala de máquinas', $foto->titulo);
        $this->assertSame('web/sala.jpg', $foto->imagen);
        Storage::disk('public')->assertExists('web/sala.jpg');
    }

    /** Ocultar va por el mismo interruptor que los catálogos, y lo oculto no sale. */
    public function test_ocultar_un_contenido_lo_saca_de_la_web(): void
    {
        $pregunta = ContenidoWeb::create(['tipo' => 'pregunta', 'titulo' => '¿Abren los domingos?', 'texto' => 'No.', 'activo' => true]);

        $this->admin()->patch("/panel/catalogos/contenidos/{$pregunta->uuid}/alternar")
            ->assertSessionHasNoErrors();

        $this->assertFalse($pregunta->refresh()->activo);

        // El aviso del panel —«... ya no se ofrecerá»— quedaría para la página siguiente.
        $this->flushSession();
        $this->get('/contacto')->assertOk()->assertDontSee('¿Abren los domingos?');
    }
}
