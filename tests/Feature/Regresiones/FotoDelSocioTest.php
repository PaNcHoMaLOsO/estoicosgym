<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * La foto del socio.
 *
 * Sirve para reconocer a quien llega al mesón sin preguntarle el RUT. Es
 * OPCIONAL: sin ella la ficha funciona igual, con las iniciales.
 *
 * Lo que se vigila aquí son las tres formas de perderla sin querer: que una
 * edición corriente de la ficha la borre, que reemplazarla deje el archivo
 * viejo tirado en el disco, y que dar de baja a alguien se lleve por delante
 * una foto que la papelera después no puede devolver.
 */
class FotoDelSocioTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disco de mentira: las pruebas no escriben en storage/app/public.
        Storage::fake('public');
    }

    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true]);
    }

    private function subir(Cliente $socio, UploadedFile $archivo)
    {
        return $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/foto", ['foto_perfil' => $archivo]);
    }

    // ---------- Poner, cambiar y quitar ----------

    public function test_se_le_pone_una_foto(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'))
            ->assertSessionHasNoErrors();

        $ruta = $socio->refresh()->foto_perfil;

        $this->assertNotNull($ruta);
        Storage::disk('public')->assertExists($ruta);
    }

    /**
     * Reemplazarla BORRA la anterior.
     *
     * Si no, cada cambio deja un archivo más en el disco: son caras de
     * personas, no basura que pueda quedarse ahí de sobra.
     */
    public function test_cambiarla_borra_la_anterior(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('vieja.jpg'));
        $vieja = $socio->refresh()->foto_perfil;

        $this->subir($socio, UploadedFile::fake()->image('nueva.jpg'));
        $nueva = $socio->refresh()->foto_perfil;

        $this->assertNotSame($vieja, $nueva);
        Storage::disk('public')->assertMissing($vieja);
        Storage::disk('public')->assertExists($nueva);
    }

    public function test_se_le_quita_la_foto_y_el_archivo_se_va(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'));
        $ruta = $socio->refresh()->foto_perfil;

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/foto", ['quitar' => true])
            ->assertSessionHasNoErrors();

        $this->assertNull($socio->refresh()->foto_perfil);
        Storage::disk('public')->assertMissing($ruta);
    }

    // ---------- Lo que no se acepta ----------

    public function test_un_archivo_que_no_es_imagen_se_rechaza(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->create('contrato.pdf', 20, 'application/pdf'))
            ->assertSessionHasErrors('foto_perfil');

        $this->assertNull($socio->refresh()->foto_perfil);
    }

    public function test_una_foto_de_mas_de_dos_megas_se_rechaza(): void
    {
        $socio = $this->socio();

        // 3 MB: lo que pesa una foto cualquiera de un teléfono actual.
        $this->subir($socio, UploadedFile::fake()->image('enorme.jpg')->size(3072))
            ->assertSessionHasErrors('foto_perfil');

        $this->assertNull($socio->refresh()->foto_perfil);
    }

    /** Sin archivo y sin querer quitarla no hay nada que hacer: se dice. */
    public function test_sin_archivo_no_se_calla(): void
    {
        $socio = $this->socio();

        $this->actingAs($this->administrador())
            ->post("/panel/clientes/{$socio->uuid}/foto", [])
            ->assertSessionHas('error');
    }

    // ---------- Las tres formas de perderla sin querer ----------

    /**
     * EL QUE IMPORTA: corregir la ficha no borra la foto.
     *
     * Un <input type="file"> vacío se manda igual, y `validated()` devuelve la
     * clave siempre que venga en la petición —aunque venga vacía—. Si
     * 'foto_perfil' se colara entre los campos de la ficha, guardar la
     * corrección de un teléfono haría `update(['foto_perfil' => null])` y el
     * socio se quedaría sin cara sin que nadie atara una cosa con la otra. Es
     * lo que mandaba el panel de Blade en cada edición.
     *
     * Por eso la petición de esta prueba lleva el campo vacío a propósito.
     */
    public function test_corregir_la_ficha_no_borra_la_foto(): void
    {
        $socio = $this->socio(['celular' => '+56912345678']);

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'));
        $ruta = $socio->refresh()->foto_perfil;

        $this->actingAs($this->administrador())
            ->put("/panel/clientes/{$socio->uuid}", [
                'run_pasaporte' => $socio->run_pasaporte,
                'nombres' => $socio->nombres,
                'apellido_paterno' => $socio->apellido_paterno,
                'apellido_materno' => $socio->apellido_materno,
                'celular' => '+56987654321',
                'email' => $socio->email,
                // El campo vacío, como lo manda el formulario de Blade.
                'foto_perfil' => null,
            ])
            ->assertSessionHasNoErrors();

        $socio->refresh();

        // El modelo guarda el celular sin el +56: nueve dígitos y ya.
        $this->assertSame('987654321', $socio->celular);
        $this->assertSame($ruta, $socio->foto_perfil);
        Storage::disk('public')->assertExists($ruta);
    }

    /**
     * Dar de baja NO borra la foto.
     *
     * La baja se deshace desde la papelera. Si el archivo se fuera con ella, el
     * socio volvería sin cara y sin forma de recuperarla: es exactamente lo que
     * hacía el panel de Blade.
     */
    public function test_dar_de_baja_no_borra_la_foto(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'));
        $ruta = $socio->refresh()->foto_perfil;

        $this->actingAs($this->administrador())
            ->patch("/panel/clientes/{$socio->uuid}/desactivar");

        Storage::disk('public')->assertExists($ruta);
        $this->assertSame($ruta, $socio->refresh()->foto_perfil);
    }

    // ---------- Donde se ve ----------

    public function test_la_ficha_trae_la_direccion_de_la_foto(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'));

        $props = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertNotNull($props['cliente']['foto']);
        $this->assertStringContainsString('storage/clientes/', $props['cliente']['foto']);
    }

    /** Y quien no tiene foto no rompe nada: sale en null y la pantalla pinta iniciales. */
    public function test_sin_foto_la_ficha_trae_null(): void
    {
        $socio = $this->socio();

        $props = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertNull($props['cliente']['foto']);
    }

    public function test_el_listado_tambien_trae_la_foto(): void
    {
        $socio = $this->socio();

        $this->subir($socio, UploadedFile::fake()->image('camila.jpg'));

        $props = $this->actingAs($this->administrador())
            ->get('/panel/clientes')
            ->viewData('page')['props'];

        $fila = collect($props['clientes']['data'])->firstWhere('uuid', $socio->uuid);

        $this->assertNotNull($fila['foto']);
    }
}
