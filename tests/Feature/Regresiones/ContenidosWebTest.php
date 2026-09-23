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

    /**
     * BORRAR ES BORRAR: se va la fila y se va el archivo.
     *
     * Ocultar deja la foto en la lista y en el disco, y una galería que solo
     * crece acaba siendo imposible de ordenar: al final había doce fotos y
     * ninguna manera de sacar la que no se quería.
     */
    public function test_eliminar_una_foto_se_lleva_su_archivo(): void
    {
        Storage::disk('public')->put('web/sala.jpg', 'foto');
        $foto = ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'Sala', 'imagen' => 'web/sala.jpg', 'activo' => true]);

        $this->admin()->delete("/panel/web/contenido/{$foto->uuid}")->assertSessionHasNoErrors();

        $this->assertSame(0, ContenidoWeb::where('tipo', 'foto')->count());
        Storage::disk('public')->assertMissing('web/sala.jpg');
    }

    /**
     * EL ORDEN SE LLEVA SOLO: lo nuevo va al final, sin preguntar el número.
     *
     * Pedirlo a mano dejó la galería con dos fotos en el puesto 12 y ninguna
     * en el 11, y entonces quién sale antes lo decide el desempate y no quien
     * lo escribió.
     */
    public function test_lo_nuevo_se_pone_al_final_solo(): void
    {
        foreach (['¿Abren los domingos?', '¿Hay estacionamiento?', '¿Tienen casilleros?'] as $pregunta) {
            $this->admin()->post('/panel/web/pregunta', ['titulo' => $pregunta, 'texto' => 'Sí.'])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame([1, 2, 3], ContenidoWeb::where('tipo', 'pregunta')->orderBy('orden')->pluck('orden')->all());
    }

    /** Y se ordena con flechas: «esta va antes que esa», sin pensar en números. */
    public function test_subir_una_fila_la_cambia_de_puesto(): void
    {
        $primera = ContenidoWeb::create(['tipo' => 'pregunta', 'titulo' => 'Primera', 'texto' => 'a', 'orden' => 1, 'activo' => true]);
        $segunda = ContenidoWeb::create(['tipo' => 'pregunta', 'titulo' => 'Segunda', 'texto' => 'b', 'orden' => 2, 'activo' => true]);

        $this->admin()->post("/panel/web/contenido/{$segunda->uuid}/mover", ['hacia' => 'arriba'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $segunda->refresh()->orden);
        $this->assertSame(2, $primera->refresh()->orden);

        // La primera de la lista ya no sube más: no hay a dónde.
        $this->admin()->post("/panel/web/contenido/{$segunda->uuid}/mover", ['hacia' => 'arriba']);
        $this->assertSame(1, $segunda->refresh()->orden);
    }

    /** Al borrar una del medio, las de abajo corren: no queda el hueco. */
    public function test_borrar_una_no_deja_hueco_en_el_orden(): void
    {
        foreach ([1, 2, 3] as $puesto) {
            ContenidoWeb::create(['tipo' => 'pregunta', 'titulo' => "Pregunta {$puesto}", 'texto' => 'a', 'orden' => $puesto, 'activo' => true]);
        }

        $delMedio = ContenidoWeb::where('titulo', 'Pregunta 2')->firstOrFail();

        $this->admin()->delete("/panel/web/contenido/{$delMedio->uuid}")->assertSessionHasNoErrors();

        $this->assertSame([1, 2], ContenidoWeb::where('tipo', 'pregunta')->orderBy('orden')->pluck('orden')->all());
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
