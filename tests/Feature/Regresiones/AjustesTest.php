<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Nota;
use App\Services\EnvioMasivoService;
use App\Support\Ajustes;
use Illuminate\Support\Facades\File;
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

    /** Un punto de la portada de Configuración. */
    private function punto(string $clave): array
    {
        return collect(
            $this->actingAs($this->administrador())
                ->get('/panel/configuracion')
                ->viewData('page')['props']['puntos']
        )->firstWhere('clave', $clave);
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

    public function test_una_hora_mal_escrita_se_rechaza(): void
    {
        $this->guardar(['tareas.hora_avisos' => '8 de la mañana'])
            ->assertSessionHasErrors('tareas.hora_avisos');
    }

    public function test_un_correo_de_contacto_sin_arroba_se_rechaza(): void
    {
        $this->guardar(['gimnasio.email' => 'contacto.progym.cl'])
            ->assertSessionHasErrors('gimnasio.email');
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

    /**
     * Y con los días de un fiado sin cobrar.
     *
     * Estaban escritos en la pantalla de Fiado —un 14 fijo— mientras el ajuste
     * de Configuración no lo leía nadie.
     */
    public function test_cambiar_los_dias_de_un_fiado_cambia_la_pantalla(): void
    {
        $dias = fn () => $this->actingAs($this->administrador())
            ->get('/panel/fiados')
            ->viewData('page')['props']['diasParaInsistir'];

        $this->assertSame(14, $dias());

        $this->guardar(['meson.dias_fiado_viejo' => 5]);
        Ajustes::olvidar();

        $this->assertSame(5, $dias());
    }

    /** Y con el tope del envío masivo. */
    public function test_cambiar_el_tope_del_envio_cambia_cuantos_caben(): void
    {
        $this->assertSame(150, EnvioMasivoService::tope());

        $this->guardar(['correo.tope_masivo' => 40]);
        Ajustes::olvidar();

        $this->assertSame(40, EnvioMasivoService::tope());
    }

    /**
     * EL CANARIO: no queda ningún ajuste que nadie lea.
     *
     * «Avisar del vencimiento, 7 días» estuvo en la pantalla sin que ningún
     * código lo mirara: los avisos usan los días de su plantilla. Esta prueba
     * busca cada clave en el código —fuera de su propia definición y de la
     * portada de Configuración, que solo mira si está puesta— y falla si alguna
     * no la usa nadie.
     */
    public function test_no_queda_ningun_ajuste_que_nadie_lea(): void
    {
        $excluidos = ['Support/Ajustes.php', 'Support/EstadoDeConfiguracion.php'];

        $codigo = collect(File::allFiles(app_path()))
            ->merge(File::allFiles(resource_path('views')))
            ->map(fn ($archivo) => str_replace('\\', '/', $archivo->getPathname()))
            ->push(str_replace('\\', '/', base_path('routes/console.php')))
            ->reject(fn (string $ruta) => collect($excluidos)->contains(fn ($fin) => str_ends_with($ruta, $fin)))
            ->map(fn (string $ruta) => file_get_contents($ruta))
            ->implode("\n");

        $sinUso = collect(array_keys(Ajustes::definiciones()))
            // Los días del horario se leen de a uno con «horario.{$dia}».
            ->reject(fn (string $clave) => str_starts_with($clave, 'horario.') && $clave !== 'horario.nota')
            ->reject(fn (string $clave) => str_contains($codigo, "'{$clave}'"))
            ->values()
            ->all();

        $this->assertSame([], $sinUso, 'Ajustes que no lee nadie: ' . implode(', ', $sinUso));
    }

    // ---------- La pantalla ----------

    /** Cada tema tiene su dirección: «atrás» vuelve al tema anterior y no fuera de Configuración. */
    public function test_cada_tema_abre_en_su_propia_direccion(): void
    {
        $admin = $this->administrador();

        foreach (array_keys(Ajustes::grupos()) as $grupo) {
            $props = $this->actingAs($admin)
                ->get("/panel/configuracion/{$grupo}")
                ->assertOk()
                ->viewData('page')['props'];

            $this->assertSame($grupo, $props['grupo']['clave']);
            $this->assertNotEmpty($props['grupo']['ajustes'], "El tema «{$grupo}» no trae ningún ajuste.");
        }
    }

    public function test_un_tema_que_no_existe_no_abre(): void
    {
        $this->actingAs($this->administrador())
            ->get('/panel/configuracion/inventado')
            ->assertNotFound();
    }

    /**
     * La portada avisa de lo que impide trabajar.
     *
     * Un plan activo sin precio no se puede vender —el alta lo rechaza— y eso
     * hay que verlo aquí, que es donde se arregla, y no con el socio delante.
     */
    public function test_la_portada_avisa_de_un_plan_sin_precio(): void
    {
        Membresia::create([
            'nombre' => 'Plan a medias',
            'duracion_meses' => 1,
            'duracion_dias' => 0,
            'activo' => true,
        ]);

        $planes = $this->punto('planes');

        $this->assertSame('falta', $planes['estado']);
        $this->assertStringContainsString('no tiene precio', $planes['detalle']);
        $this->assertSame('/panel/membresias', $planes['href']);
    }

    /** Y cuenta lo que hay, para saber si falta algo. */
    public function test_la_portada_cuenta_los_metodos_de_pago(): void
    {
        $metodos = $this->punto('metodos');

        $this->assertSame('ok', $metodos['estado']);
        $this->assertStringContainsString('3 formas de pago', $metodos['detalle']);
    }

    /** Recepción no entra a la configuración: es lo que define lo que se cobra. */
    public function test_recepcion_no_entra_a_la_configuracion(): void
    {
        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)->get('/panel/configuracion')->assertForbidden();
        $this->actingAs($recepcion)->get('/panel/configuracion/gimnasio')->assertForbidden();
    }
}
