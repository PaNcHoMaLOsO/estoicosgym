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
        // Con la ciudad en el texto alternativo cuando el título no la dice.
        $this->get('/el-gimnasio')->assertOk()->assertSee('alt="Sala de máquinas, gimnasio en Los Ángeles"', false);
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

    /**
     * LA GALERÍA SE ORDENA SOLA: las panorámicas primero.
     *
     * Son las que enseñan la sala entera, y es por donde empieza a mirar quien
     * entra a la web. Ordenar diez fotos a flechazos no lo hace nadie, así que
     * sin esto la galería queda en el orden en que se subieron, que no es
     * ninguno.
     */
    public function test_la_galeria_se_ordena_sola_con_las_panoramicas_primero(): void
    {
        $this->admin()->post('/panel/web/foto', [
            'titulo' => 'Vertical',
            'imagen' => UploadedFile::fake()->image('alta.jpg', 600, 900),
        ]);
        $this->admin()->post('/panel/web/foto', [
            'titulo' => 'Otra vertical',
            'imagen' => UploadedFile::fake()->image('alta2.jpg', 600, 900),
        ]);
        $this->admin()->post('/panel/web/foto', [
            'titulo' => 'Panorámica',
            'imagen' => UploadedFile::fake()->image('ancha.jpg', 1600, 900),
        ]);

        $this->admin()->post('/panel/web/foto/ordenar')->assertSessionHasNoErrors();

        $puestos = ContenidoWeb::where('tipo', 'foto')->orderBy('orden')->pluck('titulo', 'orden')->all();

        $this->assertSame('Panorámica', $puestos[1]);
        $this->assertSame([1, 2, 3], array_keys($puestos));
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
    /**
     * LA FOTO VERTICAL DEL CELULAR TAMBIÉN SE ACHICA, y queda en WebP.
     *
     * Se achicaba solo a lo ancho: una de 1152 × 2048 pasaba entera y pesaba
     * 400 KB. La galería completa eran más de 3 MB.
     */
    public function test_una_foto_vertical_se_achica_por_el_lado_largo_y_en_webp(): void
    {
        $this->admin()->post('/panel/web/foto', [
            'titulo' => 'Sala de máquinas',
            'imagen' => UploadedFile::fake()->image('vertical.jpg', 1152, 2048),
        ])->assertSessionHasNoErrors();

        $foto = ContenidoWeb::where('tipo', 'foto')->firstOrFail();
        [$ancho, $alto, $tipo] = getimagesize(Storage::disk('public')->path($foto->imagen));

        $this->assertSame(1600, $alto);
        $this->assertSame(900, $ancho);
        $this->assertSame(IMAGETYPE_WEBP, $tipo);
    }

    /** Y el comando aligera las que ya estaban, sin borrar la original si no se pide. */
    public function test_aligerar_las_fotos_viejas_conserva_la_original(): void
    {
        $vieja = UploadedFile::fake()->image('vieja.jpg', 1152, 2048);
        Storage::disk('public')->putFileAs('web', $vieja, 'vieja.jpg');
        $foto = ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'Sala', 'imagen' => 'web/vieja.jpg', 'activo' => true]);

        $this->artisan('web:aligerar-fotos --confirmar')->assertSuccessful();

        $foto->refresh();

        $this->assertStringEndsWith('.webp', $foto->imagen);
        Storage::disk('public')->assertExists($foto->imagen);
        // La original se queda: viaja en git, y borrarla dejaría al servidor
        // sin foto antes de que alcance a correr esto.
        Storage::disk('public')->assertExists('web/vieja.jpg');
    }

    /** Instagram: la publicación sale en la portada con su enlace limpio, sin la cola que identifica a quien lo copió. */
    public function test_una_publicacion_de_instagram_sale_en_la_portada_con_su_enlace_limpio(): void
    {
        $this->admin()->post('/panel/web/instagram', [
            'titulo' => 'Nueva zona de peso libre',
            'enlace' => 'https://www.instagram.com/progym/reel/DeNRm8cvp2J/?igsh=abc123',
            'imagen' => UploadedFile::fake()->image('reel.jpg', 720, 1280),
        ])->assertSessionHasNoErrors();

        $publicacion = ContenidoWeb::where('tipo', 'instagram')->firstOrFail();
        $this->assertSame('https://www.instagram.com/reel/DeNRm8cvp2J/', $publicacion->enlace);
        $this->assertTrue($publicacion->esReel());

        $this->get('/')->assertOk()
            ->assertSee('Nueva zona de peso libre')
            ->assertSee('href="https://www.instagram.com/reel/DeNRm8cvp2J/"', false);
    }

    /** El enlace termina en un href de la web pública: solo vale el de una publicación de Instagram. */
    public function test_un_enlace_que_no_es_de_una_publicacion_se_rechaza(): void
    {
        foreach (['javascript:alert(1)', 'https://instagram.com.malo.cl/p/ABC/', 'https://www.instagram.com/progym/', 'https://tiktok.com/p/ABC/'] as $enlace) {
            $this->admin()->post('/panel/web/instagram', [
                'titulo' => 'Algo',
                'enlace' => $enlace,
                'imagen' => UploadedFile::fake()->image('foto.jpg', 800, 1000),
            ])->assertSessionHasErrors('enlace');
        }

        $this->admin()->post('/panel/web/instagram', [
            'titulo' => 'Sin foto',
            'enlace' => 'https://www.instagram.com/p/ABC123/',
        ])->assertSessionHasErrors('imagen');

        $this->assertSame(0, ContenidoWeb::where('tipo', 'instagram')->count());
    }

    /** Lo recién publicado es lo primero que se quiere ver: en Instagram lo nuevo va adelante, no al final. */
    public function test_la_publicacion_nueva_queda_primera(): void
    {
        foreach (['Primera', 'Segunda', 'Tercera'] as $i => $titulo) {
            $this->admin()->post('/panel/web/instagram', [
                'titulo' => $titulo,
                'enlace' => "instagram.com/p/POST{$i}/",
                'imagen' => UploadedFile::fake()->image('foto.jpg', 800, 1000),
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(
            ['Tercera' => 1, 'Segunda' => 2, 'Primera' => 3],
            ContenidoWeb::where('tipo', 'instagram')->orderBy('orden')->pluck('orden', 'titulo')->all()
        );

        $this->get('/')->assertOk()->assertSeeInOrder(['Tercera', 'Segunda', 'Primera']);
    }

    /** Sin publicaciones no se pinta el bloque: un título sin nada debajo se ve roto. */
    public function test_sin_publicaciones_no_hay_bloque_de_instagram(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="instagram"', false);
    }

    /** Lo que responde Instagram: una foto y un reel, la más nueva primero. */
    private function instagramResponde(): void
    {
        $archivo = UploadedFile::fake()->image('ig.jpg', 800, 1000);
        $foto = (string) file_get_contents($archivo->getRealPath());

        \Illuminate\Support\Facades\Http::fake([
            'graph.instagram.com/me/media*' => \Illuminate\Support\Facades\Http::response(['data' => [
                [
                    'id' => '111', 'media_type' => 'IMAGE', 'media_url' => 'https://scontent.cdninstagram.com/a.jpg',
                    'permalink' => 'https://www.instagram.com/p/NUEVA1/', 'timestamp' => '2026-10-13T12:00:00+0000',
                    'caption' => "🎃 En Pro Gym hasta los monstruos entrenan… ¿y tú cuál eres? 👻\n\nComenta tu número 👇 #gimnasio",
                ],
                [
                    'id' => '222', 'media_type' => 'VIDEO', 'thumbnail_url' => 'https://scontent.cdninstagram.com/b.jpg',
                    'permalink' => 'https://www.instagram.com/reel/REEL22/', 'timestamp' => '2026-10-06T12:00:00+0000',
                    'caption' => '',
                ],
            ]]),
            'graph.instagram.com/refresh_access_token*' => \Illuminate\Support\Facades\Http::response(['access_token' => 'llave-renovada']),
            'scontent.cdninstagram.com/*' => \Illuminate\Support\Facades\Http::response($foto, 200, ['Content-Type' => 'image/jpeg']),
        ]);
    }

    /** Conectado, las publicaciones entran solas: con su foto copiada aquí, su primera frase sin emojis y la más nueva primero. */
    public function test_instagram_trae_solas_las_publicaciones_nuevas(): void
    {
        \App\Support\Ajustes::guardar(['web.instagram_token' => 'llave-de-prueba']);
        $this->instagramResponde();

        // Una cargada a mano de antes: queda detrás de las que vienen de Instagram.
        ContenidoWeb::create(['tipo' => 'instagram', 'titulo' => 'A mano', 'imagen' => 'web/a-mano.jpg', 'enlace' => 'https://www.instagram.com/p/VIEJA0/', 'orden' => 1, 'activo' => true]);

        $this->admin()->post('/panel/web/instagram/traer')->assertSessionHas('success');

        $filas = ContenidoWeb::where('tipo', 'instagram')->orderBy('orden')->get();
        $this->assertSame(['En Pro Gym hasta los monstruos entrenan… ¿y tú cuál eres?', 'Publicación del 6 de octubre', 'A mano'], $filas->pluck('titulo')->all());
        $this->assertSame('https://www.instagram.com/reel/REEL22/', $filas[1]->enlace);
        Storage::disk('public')->assertExists($filas[0]->imagen);
        // La llave se renovó sola y sigue guardada cifrada.
        $this->assertSame('llave-renovada', \App\Support\Ajustes::obtener('web.instagram_token'));
        $this->assertStringNotContainsString('llave', (string) \Illuminate\Support\Facades\DB::table('ajustes')->where('clave', 'web.instagram_token')->value('valor'));

        // Otra vuelta no duplica, ni deshace lo que se corrigió u ocultó a mano.
        $filas[0]->update(['titulo' => 'Corregido a mano', 'activo' => false]);
        $this->artisan('instagram:traer')->assertSuccessful();

        $this->assertSame(3, ContenidoWeb::where('tipo', 'instagram')->count());
        $this->assertSame('Corregido a mano', $filas[0]->fresh()->titulo);
        $this->assertFalse($filas[0]->fresh()->activo);
    }

    /** Una publicación eliminada a mano no vuelve a entrar en la vuelta siguiente. */
    public function test_una_publicacion_eliminada_no_vuelve_sola(): void
    {
        \App\Support\Ajustes::guardar(['web.instagram_token' => 'llave-de-prueba']);
        $this->instagramResponde();
        $this->artisan('instagram:traer')->assertSuccessful();

        $this->admin()->delete('/panel/web/contenido/' . ContenidoWeb::where('enlace', 'https://www.instagram.com/p/NUEVA1/')->firstOrFail()->uuid);
        $this->artisan('instagram:traer')->assertSuccessful();

        $this->assertSame(['https://www.instagram.com/reel/REEL22/'], ContenidoWeb::where('tipo', 'instagram')->pluck('enlace')->all());
    }

    /** Sin llave no se llama a nadie; con una llave vencida se dice qué hacer y la portada sigue en pie. */
    public function test_instagram_sin_llave_o_con_llave_vencida_no_rompe_nada(): void
    {
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response(['error' => ['message' => 'Error validating access token']], 400)]);

        $this->artisan('instagram:traer')->assertSuccessful();
        $this->get('/')->assertOk();
        \Illuminate\Support\Facades\Http::assertNothingSent();

        \App\Support\Ajustes::guardar(['web.instagram_token' => 'vencida']);
        $this->admin()->post('/panel/web/instagram/traer')->assertSessionHas('error');
        $this->get('/')->assertOk();
        $this->actingAs($this->recepcionista())->post('/panel/web/instagram/traer')->assertForbidden();
    }
}
