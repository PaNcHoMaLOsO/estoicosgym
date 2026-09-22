<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Fiado;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * Esconder el dinero del panel.
 *
 * ES UNA DECISIÓN DEL DUEÑO, NO UN PERMISO. Quien tiene el permiso de informes
 * lo sigue teniendo: esto se apaga y se enciende en Configuración, y quien
 * entra ahí puede volver a encenderlo. Lo que resuelve es otra cosa —no querer
 * las cifras del negocio delante mientras se atiende— y por eso vale para todo
 * el panel y no para un usuario.
 *
 * Lo que se prueba aquí es que no se escape ninguna: una sola pantalla que se
 * olvide de mirar el ajuste deja el dinero a la vista, y entonces el ajuste no
 * sirve de nada.
 */
class DineroEnPantallaTest extends CasoConCatalogos
{
    private function esconder(array $valores): void
    {
        Ajustes::guardar($valores);
        Ajustes::olvidar();
    }

    /** Sin tocar nada, el panel enseña el dinero como siempre. */
    public function test_por_defecto_no_se_esconde_nada(): void
    {
        $respuesta = $this->actingAs($this->administrador())->get('/panel/caja');

        $respuesta->assertOk();

        $privado = $respuesta->viewData('page')['props']['privado'];
        $this->assertSame(
            ['sin_montos' => false, 'sin_caja' => false, 'sin_fiado' => false, 'sin_pendientes' => false],
            $privado,
        );
    }

    /**
     * CAJA DEJA DE ABRIRSE. La pantalla entera son cifras del negocio: dejarla
     * abierta con todos los números en puntitos sería enseñar una pantalla
     * vacía y hacerle perder el tiempo a quien la abra.
     */
    public function test_con_la_caja_escondida_no_se_abre(): void
    {
        $this->esconder(['privacidad.ocultar_caja' => '1']);

        $this->actingAs($this->administrador())
            ->get('/panel/caja')
            ->assertRedirect('/panel');

        $this->actingAs($this->administrador())
            ->get('/panel/reportes/ingresos')
            ->assertRedirect('/panel');
    }

    /** Y el panel lo dice, para que no parezca que el sistema se rompió. */
    public function test_al_devolver_al_resumen_se_explica_por_que(): void
    {
        $this->esconder(['privacidad.ocultar_caja' => '1']);

        $this->actingAs($this->administrador())
            ->get('/panel/caja')
            ->assertSessionHas('info', fn (string $aviso) => str_contains($aviso, 'Configuración'));
    }

    /** El resto del panel sigue abriéndose: no es un permiso, es lo que se ve. */
    public function test_lo_demas_del_panel_sigue_funcionando(): void
    {
        $this->esconder([
            'privacidad.ocultar_montos' => '1',
            'privacidad.ocultar_caja' => '1',
        ]);

        $this->actingAs($this->administrador())->get('/panel')->assertOk();
        $this->actingAs($this->administrador())->get('/panel/pagos')->assertOk();
        // Cobrar sigue en pie: sin el precio a la vista no se puede atender.
        $this->actingAs($this->administrador())->get('/panel/pagos/cobrar')->assertOk();
    }

    /** Las pantallas saben que hay que tapar: viaja en las props compartidas. */
    public function test_el_aviso_de_tapar_llega_a_todas_las_pantallas(): void
    {
        $this->esconder(['privacidad.ocultar_montos' => '1']);

        $privado = $this->actingAs($this->administrador())->get('/panel/clientes')
            ->viewData('page')['props']['privado'];

        $this->assertTrue($privado['sin_montos']);
        // Tapar los importes no cierra la caja: son dos interruptores.
        $this->assertFalse($privado['sin_caja']);
    }

    // ---------- Quién debe ----------

    /**
     * LO FIADO NI SE MANDA. Un panel que dijera «nadie debe nada» sin ser
     * verdad sería peor que no tenerlo: en el mesón se cobraría de menos.
     */
    public function test_con_las_deudas_escondidas_el_resumen_no_trae_lo_fiado(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        Fiado::create([
            'id_cliente' => $socio->id,
            'concepto' => 'Bebida',
            'monto' => 1500,
            'id_usuario' => $this->administrador()->id,
        ]);

        $this->esconder(['privacidad.ocultar_fiado' => '1']);

        $props = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props'];

        $this->assertNull($props['fiado']);
    }

    /** Ni el aviso de la ficha del socio. */
    public function test_la_ficha_no_avisa_de_lo_fiado_cuando_esta_escondido(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        Fiado::create([
            'id_cliente' => $socio->id,
            'concepto' => 'Bebida',
            'monto' => 1500,
            'id_usuario' => $this->administrador()->id,
        ]);

        $this->esconder(['privacidad.ocultar_fiado' => '1']);

        $props = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertNull($props['fiado']);
    }

    /**
     * EL BUSCADOR ES EL AGUJERO FÁCIL: sale en todas las pantallas y marca en
     * rojo lo que debe cada socio. Sin esto, la deuda escondida se asomaría
     * ahí cada vez que alguien busca un nombre.
     */
    public function test_el_buscador_no_dice_cuanto_debe(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Rosalinda']);

        $this->esconder(['privacidad.ocultar_pendientes' => '1']);

        $encontrado = $this->actingAs($this->administrador())
            ->getJson('/panel/clientes/buscar?q=Rosalinda')
            ->json('socios.0');

        $this->assertSame(0, $encontrado['debe']);
    }

    /** El informe de pendientes es una lista de deudores: tampoco se abre. */
    public function test_el_informe_de_pendientes_no_se_abre(): void
    {
        $this->esconder(['privacidad.ocultar_pendientes' => '1']);

        $this->actingAs($this->administrador())
            ->get('/panel/reportes/pendientes')
            ->assertRedirect('/panel');
    }

    /**
     * FIADO SIGUE ABIERTO. Es donde se cobra: cerrándolo, la deuda que ya está
     * anotada no tendría forma de saldarse y se quedaría ahí para siempre.
     */
    public function test_la_pantalla_de_fiado_sigue_abriendose(): void
    {
        $this->esconder(['privacidad.ocultar_fiado' => '1']);

        $this->actingAs($this->administrador())->get('/panel/fiados')->assertOk();
    }

    /**
     * LOS CUATRO SON INDEPENDIENTES. Se puede no querer ver la caja y seguir
     * queriendo saber a quién cobrarle, o al revés: por eso son cuatro y no un
     * interruptor que obligue a tragarse las cuatro cosas por querer una.
     */
    public function test_cada_interruptor_va_por_su_cuenta(): void
    {
        $this->esconder(['privacidad.ocultar_fiado' => '1']);

        // Lo fiado escondido no cierra la caja ni tapa los importes.
        $this->actingAs($this->administrador())->get('/panel/caja')->assertOk();
        $this->actingAs($this->administrador())->get('/panel/reportes/pendientes')->assertOk();

        $privado = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props']['privado'];
        $this->assertFalse($privado['sin_montos']);
    }

    /** Y al revés: cerrar la caja no esconde a quien debe del mesón. */
    public function test_cerrar_la_caja_no_esconde_lo_fiado(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        Fiado::create([
            'id_cliente' => $socio->id,
            'concepto' => 'Bebida',
            'monto' => 1500,
            'id_usuario' => $this->administrador()->id,
        ]);

        $this->esconder(['privacidad.ocultar_caja' => '1']);

        $props = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props'];

        $this->assertNotNull($props['fiado']);
        $this->assertSame(1500, $props['fiado']['total']);
    }
}
