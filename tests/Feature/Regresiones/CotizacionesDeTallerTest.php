<?php

namespace Tests\Feature\Regresiones;

use App\Models\CotizacionTaller;
use App\Models\Institucion;
use App\Models\Taller;
use Tests\CasoConCatalogos;

/**
 * Las cotizaciones de un taller: el papel que se le manda al colegio.
 *
 * LO QUE SE VIGILA ES QUE LA CUENTA SIGA A LAS CLASES. Una cotización de
 * colegio no es un número cerrado: se suspende una semana, cae un feriado, y el
 * total tiene que bajar solo. Si se quedara pegado, se mandaría un papel que
 * cobra clases que no van a existir —y eso se descubre al facturar, cuando ya
 * está mandado—.
 */
class CotizacionesDeTallerTest extends CasoConCatalogos
{
    private function taller(): Taller
    {
        $institucion = Institucion::create([
            'nombre' => 'Corporación Educacional Colegio Hispano Americano',
            'rut' => '65.154.436-K',
            'giro' => 'Educación',
        ]);

        return Taller::create([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'descripcion_factura' => 'Uso instalaciones deportivas para clase grupal',
            'precio_hora' => 30000,
            'horario' => [
                'lunes' => [['15:30', '16:30']],
                'viernes' => [['13:30', '14:30'], ['14:30', '15:30']],
            ],
            'activo' => true,
        ]);
    }

    /**
     * COTIZAR UN MES TRAE SUS CLASES Y HACE LA CUENTA.
     *
     * Es justo el trabajo que se hacía a mano: contar los lunes y los viernes
     * del mes con el calendario al lado y multiplicar.
     */
    public function test_cotizar_un_mes_trae_las_clases_del_horario(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07'])
            ->assertSessionHasNoErrors();

        $cotizacion = CotizacionTaller::firstOrFail();

        // Julio de 2026: 4 lunes de 1 hora y 5 viernes de 2 horas.
        $this->assertSame(14.0, $cotizacion->horas);
        $this->assertSame(420000, $cotizacion->total);
        $this->assertSame(352941, $cotizacion->neto);
        $this->assertSame(67059, $cotizacion->iva);
        $this->assertSame(420000, $cotizacion->neto + $cotizacion->iva);
        $this->assertCount(14, $cotizacion->detalle);
        $this->assertSame('2026-07', $cotizacion->periodo);
    }

    /**
     * SUSPENDER UNA CLASE BAJA EL TOTAL, que es para lo que existe esto.
     *
     * Y la clase quitada NO se borra: se guarda destildada, porque el papel que
     * dice «el 3 de julio no hay clase» es el que explica por qué el mes sale
     * más barato.
     */
    public function test_quitar_una_clase_rehace_la_cuenta(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07']);

        $cotizacion = CotizacionTaller::firstOrFail();
        $lineas = $cotizacion->detalle;
        $lineas[0]['incluida'] = false;

        $this->actingAs($this->administrador())
            ->patch("/panel/talleres/cotizaciones/{$cotizacion->uuid}", [
                'numero' => $cotizacion->numero,
                'fecha' => '2026-06-28',
                'valido_hasta' => '2026-07-28',
                'descripcion' => $cotizacion->descripcion,
                'precio_hora' => 30000,
                'estado' => 'enviada',
                'detalle' => $lineas,
            ])
            ->assertSessionHasNoErrors();

        $cotizacion->refresh();

        $this->assertSame(13.0, $cotizacion->horas);
        $this->assertSame(390000, $cotizacion->total);
        // La quitada sigue ahí, para que salga tachada en el papel.
        $this->assertCount(14, $cotizacion->detalle);
        $this->assertCount(1, $cotizacion->lineasQuitadas());
    }

    /**
     * EL NÚMERO NO SE REPITE. Va en el papel: dos cotizaciones que se llaman
     * igual y no hay manera de saber cuál aceptó el colegio.
     */
    public function test_no_se_repite_el_numero(): void
    {
        $taller = $this->taller();

        foreach (['2026-07', '2026-08'] as $periodo) {
            $this->actingAs($this->administrador())
                ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => $periodo]);
        }

        [$primera, $segunda] = CotizacionTaller::orderBy('numero')->get()->all();

        $this->assertSame($primera->numero + 1, $segunda->numero);

        $this->actingAs($this->administrador())
            ->patch("/panel/talleres/cotizaciones/{$segunda->uuid}", [
                'numero' => $primera->numero,
                'fecha' => '2026-07-01',
                'valido_hasta' => '2026-08-01',
                'descripcion' => 'Uso instalaciones',
                'precio_hora' => 30000,
                'estado' => 'borrador',
                'detalle' => $segunda->detalle,
            ])
            ->assertSessionHasErrors('numero');
    }

    /**
     * REFRESCAR AÑADE LO QUE FALTA Y RESPETA LO QUITADO.
     *
     * Pasa cuando el colegio corre las clases: se cambia el horario y la
     * cotización del mes quedó con las de antes. Lo que ya se destildó tiene
     * que seguir destildado, o se recupera solo el feriado que se había quitado.
     */
    public function test_refrescar_respeta_lo_que_se_habia_quitado(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07']);

        $cotizacion = CotizacionTaller::firstOrFail();
        $lineas = $cotizacion->detalle;
        $lineas[0]['incluida'] = false;
        $cotizacion->rehacerLaCuenta($lineas);
        $cotizacion->save();

        // El colegio añade los miércoles.
        $taller->update(['horario' => [...$taller->horario, 'miercoles' => [['15:00', '16:00']]]]);

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/cotizaciones/{$cotizacion->uuid}/refrescar")
            ->assertSessionHasNoErrors();

        $cotizacion->refresh();

        // Los 5 miércoles de julio de 2026 se suman; el lunes quitado sigue fuera.
        $this->assertCount(19, $cotizacion->detalle);
        $this->assertCount(1, $cotizacion->lineasQuitadas());
        $this->assertSame(18.0, $cotizacion->horas);
    }

    /** El papel se puede sacar: es como se manda. */
    public function test_la_cotizacion_se_imprime(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07']);

        $cotizacion = CotizacionTaller::firstOrFail();

        $this->actingAs($this->administrador())
            ->get("/panel/talleres/cotizaciones/{$cotizacion->uuid}/imprimir")
            ->assertOk()
            ->assertSee('COTIZACIÓN', false)
            ->assertSee('Colegio Hispano Americano', false)
            ->assertSee('65.154.436-K');
    }

    /**
     * RECEPCIÓN COTIZA, y es a propósito: es quien atiende cuando el colegio
     * llama preguntando cuánto sale el mes. Cotizar no cobra nada; cerrar el
     * mes, que sí es emitir un cobro, le sigue estando vedado.
     */
    public function test_recepcion_puede_cotizar(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->recepcionista())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, CotizacionTaller::count());
    }
}
