<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\Institucion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Taller;
use App\Support\IngresosDelNegocio;
use Illuminate\Support\Carbon;
use Tests\CasoConCatalogos;

/**
 * La plata de los talleres: que lo que el colegio ya pagó no se pierda de la
 * caja por el camino, y que las cifras del informe digan lo mismo que la Caja.
 *
 * Cada prueba es un error que existió: el pago anotado con fecha de mañana,
 * el mes pagado que se reabría y borraba el ingreso, el taller en la papelera
 * que se llevaba lo que ya había pagado, y «Ingresos del mes» contando solo
 * las membresías.
 */
class DineroDeTalleresTest extends CasoConCatalogos
{
    private function taller(): Taller
    {
        $institucion = Institucion::create(['nombre' => 'Colegio Hispano Americano']);

        return Taller::create([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'precio_hora' => 30000,
            'activo' => true,
        ]);
    }

    /** Julio, cerrado: 20 horas a $30.000. */
    private function cobro(Taller $taller, array $cambios = []): CobroTaller
    {
        return $taller->cobros()->create($cambios + [
            'periodo' => '2026-07', 'horas' => 20, 'precio_hora' => 30000,
            'total' => 600000, 'neto' => 504202, 'iva' => 95798,
        ]);
    }

    private function caja(): array
    {
        return $this->actingAs($this->administrador())->get('/panel/caja')->viewData('page')['props'];
    }

    // ------------------------------------------------ la fecha de pago

    /**
     * A LAS 22:00 DEL 28 EN CHILE, EN GREENWICH YA ES EL 29. El botón «Marcar
     * pagada» mandaba esa fecha y el pago no aparecía en la caja de hoy. El
     * botón ya manda la de Chile; el servidor además rechaza una fecha futura.
     */
    public function test_no_se_puede_anotar_un_pago_con_fecha_de_manana(): void
    {
        $this->travelTo(Carbon::parse('2026-09-28 22:00', 'America/Santiago'));
        $cobro = $this->cobro($this->taller());

        $this->actingAs($this->administrador())
            ->patch("/panel/talleres/cobros/{$cobro->uuid}", ['pagado_en' => '2026-09-29'])
            ->assertSessionHasErrors('pagado_en');
        $this->assertNull($cobro->fresh()->pagado_en);

        $this->actingAs($this->administrador())
            ->patch("/panel/talleres/cobros/{$cobro->uuid}", ['pagado_en' => '2026-09-28'])
            ->assertSessionHasNoErrors();
        $this->assertSame('2026-09-28', $cobro->fresh()->pagado_en->toDateString());
        $this->assertSame(600000, $this->caja()['caja']['mes']['talleres']);
    }

    // ------------------------------------------------ reabrir un mes

    /**
     * REABRIR UN MES PAGADO BORRABA EL INGRESO: el cobro se iba con la fecha
     * de pago, la caja perdía los $600.000 y el mes volvía a «por cobrar».
     */
    public function test_un_mes_ya_pagado_no_se_reabre(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));
        $cobro = $this->cobro($this->taller(), ['pagado_en' => '2026-09-08']);

        $this->actingAs($this->administrador())
            ->delete("/panel/talleres/cobros/{$cobro->uuid}")
            ->assertSessionHas('error');

        $this->assertNotNull($cobro->fresh(), 'Se borró un cobro que ya estaba pagado.');
        $this->assertSame(600000, $this->caja()['caja']['mes']['talleres']);
    }

    /** Sin la fecha de pago sí se reabre: es la salida cuando hay que rehacerlo. */
    public function test_quitando_la_fecha_de_pago_se_puede_reabrir(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));
        $cobro = $this->cobro($this->taller(), ['pagado_en' => '2026-09-08']);

        $this->actingAs($this->administrador())
            ->patch("/panel/talleres/cobros/{$cobro->uuid}", ['pagado_en' => null])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->administrador())
            ->delete("/panel/talleres/cobros/{$cobro->uuid}")
            ->assertSessionHas('success');

        $this->assertNull($cobro->fresh());
    }

    // ------------------------------------------------ la papelera

    /**
     * MANDAR EL TALLER A LA PAPELERA NO SACA DEL CAJÓN LO QUE YA PAGÓ.
     * Lo que se deja de contar es lo que falta cobrar; lo pagado sigue en la
     * caja, en el IVA del mes y en el informe del año.
     */
    public function test_lo_pagado_sigue_en_la_caja_aunque_el_taller_este_en_la_papelera(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));
        $taller = $this->taller();
        $this->cobro($taller, ['pagado_en' => '2026-09-08']);
        $this->cobro($taller, ['periodo' => '2026-08', 'horas' => 10, 'total' => 300000, 'neto' => 252101, 'iva' => 47899]);

        $this->actingAs($this->administrador())->delete("/panel/talleres/{$taller->uuid}");
        $this->assertSoftDeleted('talleres', ['id' => $taller->id]);

        $caja = $this->caja();
        $this->assertSame(600000, $caja['caja']['mes']['talleres']);
        $this->assertSame(600000, collect($caja['porDia'])->firstWhere('mes', '8')['partes']['talleres']);
        $this->assertSame(95798, $caja['talleres']['iva_mes']);
        // Lo que no pagó sí se va con el taller.
        $this->assertSame(0, $caja['talleres']['por_cobrar']);
        $this->assertSame(0, $caja['talleres']['facturas']);

        $informe = $this->actingAs($this->administrador())
            ->get('/panel/reportes/ingresos?anio=2026')
            ->viewData('page')['props'];
        $this->assertSame(600000, $informe['totalesPorFuente']['talleres']);
        $this->assertSame('Colegio Hispano Americano', $informe['porInstitucion'][0]['nombre']);
        $this->assertSame(600000, $informe['porInstitucion'][0]['total']);
    }

    // ------------------------------------------------ el IVA

    /**
     * EL DESGLOSE ELIGE EL NETO DEL SII CUANDO EXISTE. Se probó buscar otro
     * neto alrededor de total / 1,19 y no hay mejora posible: si existe un neto
     * con neto + round(neto × 0,19) = total, el redondeo ya lo encuentra.
     */
    public function test_el_desglose_da_el_neto_del_sii_cuando_existe(): void
    {
        $sii = fn (int $neto) => $neto + (int) round($neto * CobroTaller::IVA);
        $descuadrados = [];

        for ($total = 1; $total <= 200000; $total++) {
            $neto = CobroTaller::desglosar($total)['neto'];

            foreach ([$neto - 2, $neto - 1, $neto, $neto + 1, $neto + 2] as $candidato) {
                if ($sii($candidato) === $total && $candidato !== $neto) {
                    $descuadrados[] = "{$total}: el SII tiene {$candidato}, el desglose dio {$neto}";
                }
            }
        }

        $this->assertSame([], array_slice($descuadrados, 0, 5));
        $this->assertSame(['neto' => 504202, 'iva' => 95798], CobroTaller::desglosar(600000));
    }

    /**
     * $180.000 NO TIENE NETO EXACTO: con 151.260 el SII daría $179.999 y con
     * 151.261, $180.001. Se queda el total acordado y el IVA absorbe el peso;
     * el total no se toca, que es lo que se le dijo al colegio.
     */
    public function test_un_total_sin_neto_exacto_conserva_el_total(): void
    {
        $this->assertSame(179999, 151260 + (int) round(151260 * 0.19));
        $this->assertSame(180001, 151261 + (int) round(151261 * 0.19));

        $desglose = CobroTaller::desglosar(180000);
        $this->assertSame(['neto' => 151261, 'iva' => 28739], $desglose);
        $this->assertSame(180000, $desglose['neto'] + $desglose['iva']);
    }

    // ------------------------------------------------ los informes

    /** «Ingresos del mes» de Reportes dice lo mismo que la Caja. */
    public function test_ingresos_del_mes_cuenta_membresias_talleres_y_meson(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => 4, 'id_estado' => 100,
            'precio_base' => 40000, 'precio_final' => 40000,
        ]);
        Pago::create([
            'id_inscripcion' => $inscripcion->id, 'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => 40000, 'monto_abonado' => 40000, 'monto_pendiente' => 0,
            'id_estado' => 201, 'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => '2026-09-05',
        ]);
        $this->cobro($this->taller(), ['pagado_en' => '2026-09-08']);
        Fiado::create([
            'id_cliente' => Cliente::factory()->create()->id, 'concepto' => 'Barrita', 'monto' => 2000,
            'pagado' => true, 'pagado_en' => now(), 'id_usuario' => $this->administrador()->id,
        ]);

        $cifras = $this->actingAs($this->administrador())->get('/panel/reportes')->viewData('page')['props']['cifras'];

        $this->assertSame(642000, $cifras['ingresos_mes']);
        $this->assertSame($this->caja()['caja']['mes']['total'], $cifras['ingresos_mes']);
    }

    /** El reparto por medio del informe del año incluye lo cobrado del fiado. */
    public function test_el_informe_del_anio_reparte_tambien_el_meson_por_medio(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $efectivo = MetodoPago::where('nombre', 'like', '%fectivo%')->firstOrFail();
        Fiado::create([
            'id_cliente' => Cliente::factory()->create()->id, 'concepto' => 'Barrita', 'monto' => 2000,
            'pagado' => true, 'pagado_en' => now(), 'id_usuario' => $this->administrador()->id,
            'id_metodo_pago' => $efectivo->id,
        ]);

        $medios = collect($this->actingAs($this->administrador())
            ->get('/panel/reportes/ingresos?anio=2026')
            ->viewData('page')['props']['porMetodo'])->keyBy('nombre');

        $this->assertSame(2000, $medios[$efectivo->nombre]['total'] ?? null);
        // Y el de la Caja, que sale de la misma cuenta.
        $this->assertSame(IngresosDelNegocio::porMetodo(Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')), $this->caja()['porMetodo']);
    }
}
