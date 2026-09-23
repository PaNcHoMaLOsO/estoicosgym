<?php

namespace Tests\Feature\Regresiones;

use App\Models\CobroTaller;
use App\Models\Institucion;
use App\Models\Taller;
use Illuminate\Support\Carbon;
use Tests\CasoConCatalogos;

/**
 * Talleres y arriendos: las horas que se le facturan a un colegio.
 *
 * LO QUE SE VIGILA ES LA CUENTA. Esto termina en una factura del SII, así que
 * un peso de diferencia entre lo que dice el panel y lo que dice el documento
 * es un problema de verdad. Y lo segundo, que un mes ya facturado no se pueda
 * mover por detrás: si se anota una clase más en julio después de haber
 * emitido la factura de julio, el sistema y el papel dejan de coincidir.
 */
class TalleresTest extends CasoConCatalogos
{
    private function taller(array $cambios = []): Taller
    {
        $institucion = Institucion::create([
            'nombre' => 'Colegio de prueba',
            'rut' => '65.154.436-K',
        ]);

        return Taller::create(array_merge([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'descripcion_factura' => 'Uso instalaciones para clase grupal',
            'precio_hora' => 30000,
            'horario' => [
                'lunes' => [['15:30', '16:30']],
                'viernes' => [['13:30', '14:30'], ['14:30', '15:30']],
            ],
            'activo' => true,
        ], $cambios));
    }

    /**
     * LA CUENTA TIENE QUE DAR LO MISMO QUE LA FACTURA.
     *
     * Esta es la de julio de 2026, que existe en papel: 20 horas a $30.000 con
     * IVA incluido son $600.000, y el SII los parte en $504.202 de neto y
     * $95.798 de IVA. Se parte del total y no del neto a propósito: sumando el
     * IVA sobre el neto redondeado, el total se va un peso y la factura deja de
     * cuadrar con lo que se le dijo al colegio.
     */
    public function test_el_desglose_cuadra_con_la_factura_del_sii(): void
    {
        $this->assertSame(['neto' => 504202, 'iva' => 95798], CobroTaller::desglosar(600000));
        $this->assertSame(600000, 504202 + 95798);
    }

    /**
     * EL HORARIO PROPONE LAS CLASES DEL MES.
     *
     * Es lo que ahorra el trabajo: antes se contaban a mano con el calendario
     * de Windows abierto al lado, una vez al mes y por cada taller.
     */
    public function test_el_horario_propone_las_clases_del_mes(): void
    {
        $taller = $this->taller();

        // Julio de 2026: 4 lunes (1 hora) y 5 viernes (2 horas cada uno).
        $clases = $taller->clasesDe(Carbon::create(2026, 7, 1));

        $this->assertSame(14, count($clases));
        $this->assertSame(14.0, collect($clases)->sum('horas'));
    }

    /** Y se anotan de una vez, sin teclear catorce fechas. */
    public function test_se_anotan_las_clases_del_horario_de_un_golpe(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07'])
            ->assertSessionHasNoErrors();

        $this->assertSame(14, $taller->horas()->count());

        // Pulsarlo otra vez no duplica: solo anota las que faltan.
        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        $this->assertSame(14, $taller->horas()->count());
    }

    /** Lo que no se hizo no se cobra: la clase del feriado se quita. */
    public function test_una_clase_que_no_se_hizo_se_quita(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        $hora = $taller->horas()->first();

        $this->actingAs($this->administrador())
            ->delete("/panel/talleres/horas/{$hora->uuid}")
            ->assertSessionHasNoErrors();

        $this->assertSame(13, $taller->horas()->count());
    }

    /** Al cerrar el mes queda la cuenta hecha, con el precio de ese mes. */
    public function test_cerrar_el_mes_deja_la_cuenta_para_la_factura(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07'])
            ->assertSessionHasNoErrors();

        $cobro = $taller->cobros()->firstOrFail();

        $this->assertSame('2026-07', $cobro->periodo);
        $this->assertSame(14.0, $cobro->horas);
        $this->assertSame(420000, $cobro->total);
        $this->assertSame(352941, $cobro->neto);
        $this->assertSame(67059, $cobro->iva);
        $this->assertSame(420000, $cobro->neto + $cobro->iva);

        // Y el precio queda congelado: si mañana sube la hora, la factura de
        // julio tiene que seguir diciendo lo que decía.
        $taller->update(['precio_hora' => 35000]);
        $this->assertSame(30000, $cobro->fresh()->precio_hora);
    }

    /**
     * UN MES CERRADO NO SE TOCA POR DETRÁS. Anotar una clase más en julio
     * después de emitir la factura de julio deja el sistema y el papel
     * diciendo cosas distintas.
     */
    public function test_no_se_anotan_horas_en_un_mes_ya_cerrado(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);
        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07']);

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas", [
                'fecha' => '2026-07-20',
                'horas' => 2,
            ])
            ->assertSessionHasErrors('fecha');

        $this->assertSame(14, $taller->horas()->count());
    }

    /** Y el mismo mes no se cierra dos veces. */
    public function test_un_mes_no_se_cobra_dos_veces(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        foreach ([1, 2] as $vez) {
            $this->actingAs($this->administrador())
                ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07']);
        }

        $this->assertSame(1, $taller->cobros()->count());
    }

    /**
     * REABRIR HACE FALTA: se cierra julio y aparece una clase que no estaba.
     * Al reabrir, las horas vuelven a quedar sueltas y se puede corregir.
     */
    public function test_se_puede_reabrir_un_mes(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);
        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07']);

        $cobro = $taller->cobros()->firstOrFail();

        $this->actingAs($this->administrador())
            ->delete("/panel/talleres/cobros/{$cobro->uuid}")
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $taller->cobros()->count());
        // Las horas siguen ahí y otra vez sueltas.
        $this->assertSame(14, $taller->horas()->whereNull('id_cobro')->count());
    }

    /** Un mes sin ninguna clase no se cierra: no hay nada que facturar. */
    public function test_no_se_cierra_un_mes_sin_clases(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07'])
            ->assertSessionHasErrors('periodo');
    }

    /** Se crea el taller y la institución de una vez, no en dos pantallas. */
    public function test_se_crea_el_taller_con_su_institucion(): void
    {
        $this->actingAs($this->administrador())->post('/panel/talleres', [
            'institucion_nombre' => 'Corporación Educacional Hispanoamericano',
            'institucion_rut' => '65.154.436-K',
            'nombre' => 'Clases grupales',
            'descripcion_factura' => 'Uso instalaciones para clase grupal',
            'precio_hora' => 30000,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('instituciones', ['rut' => '65.154.436-K']);
        $this->assertDatabaseHas('talleres', ['nombre' => 'Clases grupales', 'precio_hora' => 30000]);
    }

    /** El horario a medio escribir no se guarda: propondría clases de cero horas. */
    public function test_un_tramo_incompleto_no_se_guarda(): void
    {
        $taller = $this->taller(['horario' => []]);

        $this->actingAs($this->administrador())->patch("/panel/talleres/{$taller->uuid}", [
            'nombre' => $taller->nombre,
            'precio_hora' => 30000,
            'horario' => [
                'lunes' => [['15:00', '16:00'], ['17:00', '']],
                'martes' => [['', '']],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['lunes' => [['15:00', '16:00']]], $taller->fresh()->horario);
    }

    /**
     * RECEPCIÓN SÍ ENTRA, y es a propósito.
     *
     * Quien está en el mesón es quien ve pasar las clases y sabe cuál se
     * suspendió: si no pudiera anotarlas, habría que contárselo a alguien para
     * que las escribiera, que es justo el paso donde se pierden. Va con el
     * permiso de cobrar, no con el de los informes.
     */
    public function test_recepcion_puede_anotar_las_horas(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->recepcionista())->get('/panel/talleres')->assertOk();

        $this->actingAs($this->recepcionista())
            ->post("/panel/talleres/{$taller->uuid}/horas", [
                'fecha' => '2026-07-20',
                'horas' => 2,
                'detalle' => '15:00 a 17:00',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $taller->horas()->count());
    }

    /**
     * PERO NO CIERRA EL MES. Anotar horas es trabajo de mesón; cerrar el mes es
     * emitir un cobro, y eso lo decide quien factura.
     */
    public function test_recepcion_no_cierra_el_mes(): void
    {
        $taller = $this->taller();

        $this->actingAs($this->administrador())
            ->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        $this->actingAs($this->recepcionista())
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07'])
            ->assertForbidden();

        $this->assertSame(0, $taller->cobros()->count());
    }
}
