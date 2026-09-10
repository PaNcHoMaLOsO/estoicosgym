<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Nota;
use App\Services\EnvioMasivoService;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * Los ajustes del gimnasio.
 *
 * Lo que se comprueba aquí es que de verdad SIRVAN: que cambiar un número en
 * Configuración cambie lo que hace el sistema. Un ajuste que se guarda pero que
 * nadie lee es peor que no tenerlo, porque quien lo cambia se queda creyendo
 * que hizo algo.
 */
class AjustesTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        // La caché vive entre pruebas y arrastraría los valores de la anterior.
        Ajustes::olvidar();
    }

    private function guardar(array $valores)
    {
        return $this->actingAs($this->administrador())
            ->put('/panel/configuracion', $valores);
    }

    public function test_un_ajuste_sin_tocar_vale_su_defecto(): void
    {
        $this->assertSame(30, Ajustes::numero('reglas.dias_para_renovar'));
        $this->assertSame('PRO GYM', Ajustes::obtener('gimnasio.nombre'));
    }

    public function test_se_guarda_y_se_lee(): void
    {
        $this->guardar(['gimnasio.nombre' => 'Gimnasio Los Ángeles'])
            ->assertSessionHasNoErrors();

        Ajustes::olvidar();

        $this->assertSame('Gimnasio Los Ángeles', Ajustes::obtener('gimnasio.nombre'));
    }

    /** Una clave inventada no acaba en la tabla ocupando sitio. */
    public function test_una_clave_que_no_existe_se_descarta(): void
    {
        $this->guardar(['gimnasio.nombre' => 'Algo', 'inventado.loquesea' => 'x']);

        $this->assertDatabaseMissing('ajustes', ['clave' => 'inventado.loquesea']);
    }

    public function test_un_numero_fuera_de_rango_se_rechaza(): void
    {
        $this->guardar(['reglas.dias_para_renovar' => 9999])
            ->assertSessionHasErrors('reglas.dias_para_renovar');

        Ajustes::olvidar();
        $this->assertSame(30, Ajustes::numero('reglas.dias_para_renovar'));
    }

    /**
     * EL QUE IMPORTA: que el ajuste cambie el comportamiento.
     *
     * «Se puede renovar 30 días antes» estaba escrito dentro del controlador.
     * Si se guarda el ajuste pero el controlador sigue leyendo su número, quien
     * lo cambia se queda creyendo que hizo algo.
     */
    public function test_cambiar_los_dias_para_renovar_cambia_lo_que_deja_renovar(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        // Le quedan 45 días: con el defecto de 30, todavía no se puede renovar.
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_vencimiento' => now()->addDays(45),
        ]);

        $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}/renovar")
            ->assertRedirect();

        $this->guardar(['reglas.dias_para_renovar' => 60]);
        Ajustes::olvidar();

        $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}/renovar")
            ->assertOk();
    }

    /** Lo mismo con los días que tarda una nota en marcarse. */
    public function test_cambiar_los_dias_de_una_nota_cambia_cuando_se_marca(): void
    {
        $admin = $this->administrador();

        $nota = Nota::create(['texto' => 'Algo', 'id_usuario' => $admin->id]);
        $nota->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();

        // Con el defecto de 3, una de 5 días ya está marcada.
        $this->assertTrue($nota->refresh()->estaVieja());

        $this->guardar(['meson.dias_nota_vieja' => 10]);
        Ajustes::olvidar();

        $this->assertFalse($nota->refresh()->estaVieja());
    }

    /** Y con el tope del envío masivo. */
    public function test_cambiar_el_tope_del_envio_cambia_cuantos_caben(): void
    {
        $this->assertSame(150, EnvioMasivoService::tope());

        $this->guardar(['correo.tope_masivo' => 40]);
        Ajustes::olvidar();

        $this->assertSame(40, EnvioMasivoService::tope());
    }

    // ---------- La pantalla ----------

    public function test_la_pantalla_trae_los_ajustes_agrupados(): void
    {
        $props = $this->actingAs($this->administrador())
            ->get('/panel/configuracion')
            ->viewData('page')['props'];

        $claves = collect($props['grupos'])->pluck('clave');

        $this->assertTrue($claves->contains('gimnasio'));
        $this->assertTrue($claves->contains('reglas'));
        $this->assertTrue($claves->contains('meson'));
    }

    /**
     * La pantalla avisa de lo que impide trabajar.
     *
     * Un plan activo sin precio no se puede vender —el alta lo rechaza— y eso
     * hay que verlo aquí, que es donde se arregla, y no con el socio delante.
     */
    public function test_la_pantalla_avisa_de_un_plan_sin_precio(): void
    {
        Membresia::create([
            'nombre' => 'Plan a medias',
            'duracion_meses' => 1,
            'duracion_dias' => 0,
            'activo' => true,
        ]);

        $catalogos = collect(
            $this->actingAs($this->administrador())
                ->get('/panel/configuracion')
                ->viewData('page')['props']['catalogos']
        );

        $planes = $catalogos->firstWhere('titulo', 'Planes');

        $this->assertNotNull($planes['aviso']);
        $this->assertStringContainsString('no tiene precio', $planes['aviso']);
    }

    /** Y la cuenta de cada catálogo, para saber si falta algo. */
    public function test_la_pantalla_cuenta_lo_que_hay_en_cada_catalogo(): void
    {
        $catalogos = collect(
            $this->actingAs($this->administrador())
                ->get('/panel/configuracion')
                ->viewData('page')['props']['catalogos']
        );

        $metodos = $catalogos->firstWhere('titulo', 'Métodos de pago');

        $this->assertSame(3, $metodos['activos']);
        $this->assertNull($metodos['aviso']);
    }

    /** Recepción no entra a la configuración: es lo que define lo que se cobra. */
    public function test_recepcion_no_entra_a_la_configuracion(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/configuracion')
            ->assertForbidden();
    }
}
