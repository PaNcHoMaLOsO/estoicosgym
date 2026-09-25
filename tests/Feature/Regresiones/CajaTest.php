<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Carbon\Carbon;
use Tests\CasoConCatalogos;

/**
 * La caja: con qué se comparan las cifras y a quién hay que cobrarle.
 *
 * Lo que se vigila: que el mes se compare contra el mes pasado HASTA EL MISMO
 * DÍA —contra el mes entero, un mes recién empezado siempre parecería un
 * desastre—, que la deuda se parta entre quien sigue viniendo y quien ya se
 * fue, y que un pago mixto se reparta entre sus dos medios también aquí.
 */
class CajaTest extends CasoConCatalogos
{
    private function membresia(int $precio, int $estado = 100): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'precio_base' => $precio,
            'precio_final' => $precio,
        ]);
    }

    private function cobrar(Inscripcion $inscripcion, int $monto, string $fecha, array $extra = []): void
    {
        Pago::create($extra + [
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_final,
            'monto_abonado' => $monto,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => $fecha,
        ]);

        $inscripcion->recalcularSusPagos();
    }

    private function caja(): array
    {
        return $this->actingAs($this->administrador())->get('/panel/caja')->viewData('page')['props'];
    }

    /** EL QUE IMPORTA: el mes se compara con el mes pasado a la misma altura. */
    public function test_el_mes_se_compara_hasta_el_mismo_dia_del_mes_pasado(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $inscripcion = $this->membresia(500000);
        $this->cobrar($inscripcion, 100000, '2026-09-05');   // este mes
        $this->cobrar($inscripcion, 50000, '2026-08-03');    // mes pasado, antes del 10
        $this->cobrar($inscripcion, 70000, '2026-08-25');    // mes pasado, DESPUÉS del 10

        $caja = $this->caja()['caja'];

        $this->assertSame(100000, $caja['mes']['total']);
        $this->assertSame(50000, $caja['mes_pasado']['total'], 'Se coló lo cobrado después del día 10 del mes pasado.');
        $this->assertSame(10, $caja['dia_del_mes']);
    }

    public function test_lo_de_hoy_se_compara_con_ayer(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $inscripcion = $this->membresia(500000);
        $this->cobrar($inscripcion, 30000, '2026-09-10');
        $this->cobrar($inscripcion, 20000, '2026-09-09');

        $caja = $this->caja()['caja'];

        $this->assertSame([30000, 20000], [$caja['hoy']['total'], $caja['ayer']['total']]);
    }

    /** La deuda, partida entre quien sigue viniendo y quien ya se fue. */
    public function test_la_deuda_se_parte_segun_a_quien_hay_que_cobrarle(): void
    {
        $vigente = $this->membresia(40000);
        $this->cobrar($vigente, 10000, today()->format('Y-m-d'));   // debe 30.000
        $this->membresia(25000, estado: 102);                        // se fue debiendo 25.000
        $this->membresia(50000, estado: 103);                        // cancelada: no se cobra

        $deuda = $this->caja()['deuda'];

        $this->assertSame(['total' => 30000, 'cuantas' => 1], $deuda['vigente']);
        $this->assertSame(['total' => 25000, 'cuantas' => 1], $deuda['vencida']);
        // Las dos partes suman lo mismo que la cifra de arriba: si no, una de
        // las dos pantallas estaría mintiendo.
        $this->assertSame(
            $this->caja()['caja']['por_cobrar'],
            $deuda['vigente']['total'] + $deuda['vencida']['total']
        );
    }

    /** Un pago mixto se reparte entre sus dos medios, igual que en los informes. */
    public function test_un_pago_mixto_se_reparte_entre_sus_medios(): void
    {
        [$uno, $dos] = MetodoPago::orderBy('id')->take(2)->get()->all();

        $inscripcion = $this->membresia(45000);
        $this->cobrar($inscripcion, 30000, today()->format('Y-m-d'), [
            'tipo_pago' => 'mixto',
            'id_metodo_pago' => $uno->id,
            'id_metodo_pago2' => $dos->id,
            'monto_metodo1' => 20000,
            'monto_metodo2' => 10000,
        ]);

        $porMetodo = collect($this->caja()['porMetodo'])->pluck('total', 'nombre');

        $this->assertSame(20000, $porMetodo[$uno->nombre]);
        $this->assertSame(10000, $porMetodo[$dos->nombre]);
    }

    /** El mes entero, día por día: los que faltan van marcados, no recortados. */
    public function test_el_mes_va_dia_por_dia_y_marca_los_que_faltan(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $this->cobrar($this->membresia(60000), 15000, '2026-09-03');

        $porDia = collect($this->caja()['porDia']);

        $this->assertCount(30, $porDia, 'Septiembre tiene 30 días.');
        $this->assertSame(15000, $porDia->firstWhere('mes', '3')['total']);
        $this->assertFalse($porDia->firstWhere('mes', '10')['futuro']);
        $this->assertTrue($porDia->firstWhere('mes', '11')['futuro']);
    }
    /**
     * LO IMPORTADO NO SE CUENTA COMO EFECTIVO.
     *
     * Las planillas viejas no decían cómo pagó cada socio, y al traerlas hubo
     * que ponerles un medio: quedaron 1.770 pagos diciendo «efectivo» por
     * $73.953.000. Eso hacía que el informe de ingresos por medio de pago
     * afirmara una cifra falsa con toda seguridad, y una cifra falsa es peor
     * que una que falta: nadie la revisa porque parece completa.
     *
     * El medio «Sin registrar» existe y está apagado —no se ofrece al cobrar,
     * porque de hoy en adelante sí se sabe— pero sigue nombrándose en los
     * informes.
     */
    public function test_el_medio_sin_registrar_existe_apagado_y_se_nombra_en_los_informes(): void
    {
        $sinRegistrar = MetodoPago::withoutGlobalScopes()->where('nombre', 'Sin registrar')->firstOrFail();

        $this->assertFalse((bool) $sinRegistrar->activo);

        $this->cobrar($this->membresia(30000), 30000, today()->toDateString(), [
            'id_metodo_pago' => $sinRegistrar->id,
        ]);

        $porMedio = \App\Support\IngresosPorMetodo::en(fn ($q) => $q->whereDate('pagos.fecha_pago', today()));

        $this->assertSame('Sin registrar', $porMedio->first()['nombre']);
        $this->assertSame(30000, $porMedio->first()['total']);
    }
    /**
     * LA CAJA CUENTA LAS TRES FUENTES, por separado y juntas.
     *
     * Antes solo sumaba las membresías: lo que pagó el colegio por el arriendo
     * y lo cobrado del fiado del mesón no salía en ninguna cifra, y «entró
     * este mes» decía menos de lo que entró. Lo que sigue debiéndose —el fiado
     * sin cobrar, la factura sin pagar— no es un ingreso y no cuenta.
     */
    public function test_la_caja_suma_membresias_talleres_y_meson(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $this->cobrar($this->membresia(40000), 40000, '2026-09-05');

        $institucion = \App\Models\Institucion::create(['nombre' => 'Colegio']);
        $taller = \App\Models\Taller::create(['id_institucion' => $institucion->id, 'nombre' => 'Clases', 'precio_hora' => 30000]);
        $taller->cobros()->create(['periodo' => '2026-08', 'horas' => 20, 'precio_hora' => 30000, 'total' => 600000, 'neto' => 504202, 'iva' => 95798, 'pagado_en' => '2026-09-08']);
        // Facturada y sin pagar: se debe, no entró.
        $taller->cobros()->create(['periodo' => '2026-07', 'horas' => 10, 'precio_hora' => 30000, 'total' => 300000, 'neto' => 252101, 'iva' => 47899]);

        $socio = Cliente::factory()->create();
        $quien = $this->administrador()->id;
        \App\Models\Fiado::create(['id_cliente' => $socio->id, 'concepto' => 'Barrita', 'monto' => 2000, 'pagado' => true, 'pagado_en' => now(), 'id_usuario' => $quien]);
        \App\Models\Fiado::create(['id_cliente' => $socio->id, 'concepto' => 'Agua', 'monto' => 1000, 'pagado' => false, 'id_usuario' => $quien]);

        $props = $this->caja();
        $mes = $props['caja']['mes'];

        $this->assertSame(
            ['membresias' => 40000, 'talleres' => 600000, 'meson' => 2000, 'total' => 642000],
            $mes
        );
        $this->assertSame(2000, $props['caja']['hoy']['meson']);
        $this->assertSame(300000, $props['talleres']['por_cobrar']);

        // El gráfico mes a mes trae las mismas partes.
        $this->assertSame(642000, end($props['porMes'])['total']);
    }

    /**
     * Sin el IVA de los talleres: el IVA de la factura al colegio es del SII.
     * Los talleres cuentan su neto; membresías y mesón no cambian.
     */
    public function test_la_caja_sin_el_iva_de_los_talleres(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $this->cobrar($this->membresia(40000), 40000, '2026-09-05');

        $institucion = \App\Models\Institucion::create(['nombre' => 'Colegio']);
        $taller = \App\Models\Taller::create(['id_institucion' => $institucion->id, 'nombre' => 'Clases', 'precio_hora' => 30000]);
        $taller->cobros()->create(['periodo' => '2026-08', 'horas' => 20, 'precio_hora' => 30000, 'total' => 600000, 'neto' => 504202, 'iva' => 95798, 'pagado_en' => '2026-09-08']);
        $taller->cobros()->create(['periodo' => '2026-07', 'horas' => 10, 'precio_hora' => 30000, 'total' => 300000, 'neto' => 252101, 'iva' => 47899]);

        $props = $this->actingAs($this->administrador())->get('/panel/caja?iva=sin')->viewData('page')['props'];

        $this->assertTrue($props['sinIva']);
        $this->assertSame(['membresias' => 40000, 'talleres' => 504202, 'meson' => 0, 'total' => 544202], $props['caja']['mes']);
        $this->assertSame(252101, $props['talleres']['por_cobrar']);
        $this->assertSame(95798, $props['talleres']['iva_mes']);
        $this->assertSame(544202, end($props['porMes'])['total']);
        $this->assertSame(504202, collect($props['porDia'])->firstWhere('mes', '8')['partes']['talleres']);

        // Con IVA, como siempre.
        $this->assertSame(600000, $this->caja()['caja']['mes']['talleres']);
    }

    /** Lo cobrado del fiado cuenta en «Con qué pagan», con su medio. */
    public function test_el_fiado_cobrado_sale_con_su_medio(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $efectivo = MetodoPago::where('nombre', 'like', '%fectivo%')->first();
        $socio = Cliente::factory()->create();
        $quien = $this->administrador()->id;
        \App\Models\Fiado::create(['id_cliente' => $socio->id, 'concepto' => 'Barrita', 'monto' => 2000, 'pagado' => true, 'pagado_en' => now(), 'id_usuario' => $quien, 'id_metodo_pago' => $efectivo->id]);
        \App\Models\Fiado::create(['id_cliente' => $socio->id, 'concepto' => 'Agua', 'monto' => 1000, 'pagado' => true, 'pagado_en' => now()->subDay(), 'id_usuario' => $quien]);

        $medios = collect($this->caja()['porMetodo'])->keyBy('nombre');

        $this->assertSame(2000, $medios[$efectivo->nombre]['total']);
        $this->assertSame(1000, $medios['Sin anotar']['total']);
    }
    /**
     * EL INFORME DEL AÑO CUENTA LO MISMO QUE LA CAJA, partido igual, y cada
     * parte con su detalle: el colegio que pagó, el producto del mesón.
     */
    public function test_el_informe_de_ingresos_parte_el_anio_por_fuente(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 12:00', 'America/Santiago'));

        $this->cobrar($this->membresia(40000), 40000, '2026-03-05');

        $institucion = \App\Models\Institucion::create(['nombre' => 'Colegio Hispano Americano']);
        $taller = \App\Models\Taller::create(['id_institucion' => $institucion->id, 'nombre' => 'Clases', 'precio_hora' => 30000]);
        $taller->cobros()->create(['periodo' => '2026-07', 'horas' => 20, 'precio_hora' => 30000, 'total' => 600000, 'neto' => 504202, 'iva' => 95798, 'pagado_en' => '2026-08-02']);

        \App\Models\Fiado::create([
            'id_cliente' => Cliente::factory()->create()->id, 'concepto' => 'Barrita', 'monto' => 2000,
            'pagado' => true, 'pagado_en' => now(), 'id_usuario' => $this->administrador()->id,
        ]);

        $props = $this->actingAs($this->administrador())
            ->get('/panel/reportes/ingresos?anio=2026')
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(642000, $props['total']);
        $this->assertSame(['membresias' => 40000, 'talleres' => 600000, 'meson' => 2000], $props['totalesPorFuente']);
        $this->assertSame(600000, $props['meses'][7]['partes']['talleres']);
        $this->assertSame('Colegio Hispano Americano', $props['porInstitucion'][0]['nombre']);
        $this->assertSame('Barrita', $props['porConcepto'][0]['nombre']);
    }
}
