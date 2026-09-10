<?php

namespace Tests\Feature\Regresiones;

use App\Http\Controllers\Panel\PlantillaController;
use App\Models\Cliente;
use App\Models\TipoNotificacion;
use App\Services\EnvioManualService;
use Tests\CasoConCatalogos;

/**
 * Los textos de los correos que manda el gimnasio.
 *
 * Una plantilla que usa una variable inexistente le manda al socio un correo
 * con las llaves puestas: «la membresía de {nombre_cliente} vence en 30 días».
 * Ha pasado. El sitio para darse cuenta es al escribirla, no la bandeja de
 * entrada de la persona.
 */
class PlantillasDeCorreoTest extends CasoConCatalogos
{
    private function plantilla(array $extra = []): TipoNotificacion
    {
        return TipoNotificacion::create(array_merge([
            'codigo' => 'prueba_' . uniqid(),
            'nombre' => 'Plantilla ' . uniqid(),
            'asunto_email' => 'Hola {nombre}',
            'plantilla_email' => '<p>Tu plan {membresia} vence el {fecha_vencimiento}.</p>',
            'activo' => true,
        ], $extra));
    }

    private function guardar(TipoNotificacion $plantilla, array $datos)
    {
        return $this->actingAs($this->administrador())
            ->put("/panel/notificaciones/plantillas/{$plantilla->id}", array_merge([
                'nombre' => $plantilla->nombre,
                'asunto_email' => $plantilla->asunto_email,
                'plantilla_email' => $plantilla->plantilla_email,
                'dias_anticipacion' => 0,
                'activo' => true,
                'enviar_email' => true,
            ], $datos));
    }

    public function test_se_corrige_el_texto_de_un_correo(): void
    {
        $plantilla = $this->plantilla();

        $this->guardar($plantilla, [
            'asunto_email' => 'Se te acaba, {nombre}',
            'plantilla_email' => '<p>Quedan {dias_restantes} días.</p>',
        ])->assertSessionHasNoErrors();

        $plantilla->refresh();

        $this->assertSame('Se te acaba, {nombre}', $plantilla->asunto_email);
        $this->assertStringContainsString('{dias_restantes}', $plantilla->plantilla_email);
    }

    /**
     * EL BUG QUE ESTO EVITA.
     *
     * Escribir {nombre_del_socio} en vez de {nombre} hace que el correo le
     * llegue a la persona con las llaves puestas, y nadie del gimnasio se
     * entera.
     */
    public function test_no_se_guarda_una_plantilla_con_una_variable_que_no_existe(): void
    {
        $plantilla = $this->plantilla();
        $antes = $plantilla->asunto_email;

        $this->guardar($plantilla, ['asunto_email' => 'Hola {nombre_del_socio}'])
            ->assertSessionHasErrors('plantilla_email');

        $this->assertSame($antes, $plantilla->fresh()->asunto_email);
    }

    /** También se mira el cuerpo, no solo el asunto. */
    public function test_tambien_se_revisa_el_cuerpo_del_correo(): void
    {
        $plantilla = $this->plantilla();

        $this->guardar($plantilla, ['plantilla_email' => '<p>Debes {lo_que_debe}</p>'])
            ->assertSessionHasErrors('plantilla_email');
    }

    /** Las variables de verdad se aceptan todas. */
    public function test_las_variables_conocidas_se_aceptan(): void
    {
        $plantilla = $this->plantilla();

        $todas = collect(array_keys(PlantillaController::VARIABLES))
            ->map(fn (string $v) => '{' . $v . '}')
            ->implode(' ');

        $this->guardar($plantilla, ['plantilla_email' => "<p>{$todas}</p>"])
            ->assertSessionHasNoErrors();
    }

    /**
     * La lista que ofrece la pantalla tiene que ser EXACTAMENTE la que el
     * servicio sabe rellenar. Si sobrara una, la pantalla ofrecería algo que
     * después sale en blanco; si faltara, se rechazaría una plantilla correcta.
     */
    public function test_la_lista_de_variables_coincide_con_lo_que_se_rellena(): void
    {
        $socio = Cliente::factory()->create(['email' => 'x@progym.cl']);

        $plantilla = $this->plantilla([
            'asunto_email' => 'x',
            'plantilla_email' => collect(array_keys(PlantillaController::VARIABLES))
                ->map(fn (string $v) => '{' . $v . '}')
                ->implode(' '),
        ]);

        $correo = app(EnvioManualService::class)->componer($socio, $plantilla);

        $this->assertSame(
            [],
            $correo['pendientes'],
            'La pantalla ofrece variables que el servicio no sabe rellenar: ' . implode(', ', $correo['pendientes'])
        );
    }

    /** El listado marca las plantillas que hoy están mandando correos rotos. */
    public function test_el_listado_marca_las_plantillas_rotas(): void
    {
        // Se escribe directo en la base: por la pantalla ya no se puede meter.
        $rota = $this->plantilla(['asunto_email' => 'Hola {esto_no_existe}']);
        $buena = $this->plantilla();

        $respuesta = $this->actingAs($this->administrador())
            ->get('/panel/notificaciones/plantillas');

        $plantillas = collect($respuesta->viewData('page')['props']['plantillas']);

        $this->assertSame(
            ['esto_no_existe'],
            $plantillas->firstWhere('id', $rota->id)['rotas']
        );
        $this->assertSame([], $plantillas->firstWhere('id', $buena->id)['rotas']);
    }

    public function test_no_se_repiten_los_nombres_de_plantilla(): void
    {
        $otra = $this->plantilla(['nombre' => 'Ya usado']);
        $plantilla = $this->plantilla();

        $this->guardar($plantilla, ['nombre' => 'Ya usado'])
            ->assertSessionHasErrors('nombre');
    }

    /** La vista previa compone con un socio de verdad. */
    public function test_la_vista_previa_usa_datos_reales(): void
    {
        Cliente::factory()->create([
            'email' => 'real@progym.cl',
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Muñoz',
        ]);

        $plantilla = $this->plantilla(['asunto_email' => 'Hola {nombre}']);

        $respuesta = $this->actingAs($this->administrador())
            ->getJson("/panel/notificaciones/plantillas/{$plantilla->id}/vista-previa");

        $respuesta->assertOk();
        $this->assertStringContainsString('Rosa', $respuesta->json('asunto'));
        $this->assertStringNotContainsString('{nombre}', $respuesta->json('asunto'));
    }
}
