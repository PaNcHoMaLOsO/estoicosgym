<?php

namespace Tests\Feature\Regresiones;

use App\Models\Convenio;
use App\Models\EntradaCanje;
use App\Models\Pago;
use Carbon\Carbon;
use Tests\CasoConCatalogos;

/**
 * El huésped del hotel que entra con su tarjeta y no paga.
 *
 * Lo que se vigila: que recepción lo anote sin crear socio ni pago, que solo
 * valgan los convenios marcados como canje, que la cuenta del mes cuadre, y
 * que lo anotado por error se pueda quitar el mismo día y no después.
 */
class EntradasPorCanjeTest extends CasoConCatalogos
{
    private function hotel(): Convenio
    {
        return Convenio::factory()->create(['activo' => true, 'canje' => true, 'nombre' => 'Hotel del Centro']);
    }

    public function test_recepcion_anota_un_huesped_sin_socio_ni_pago(): void
    {
        $hotel = $this->hotel();
        $socios = \App\Models\Cliente::count();

        $this->actingAs($this->recepcionista())
            ->post('/panel/canje', ['id_convenio' => $hotel->id, 'nombre' => 'John Smith', 'tarjeta' => '0412'])
            ->assertSessionHasNoErrors();

        $this->assertSame(['nombre' => 'John Smith', 'tarjeta' => '0412'], EntradaCanje::sole()->only(['nombre', 'tarjeta']));
        $this->assertSame($socios, \App\Models\Cliente::count());
        $this->assertSame(0, Pago::count());
    }

    public function test_un_convenio_que_no_es_de_canje_no_sirve(): void
    {
        $inacap = Convenio::factory()->create(['activo' => true, 'canje' => false]);

        $this->actingAs($this->recepcionista())
            ->post('/panel/canje', ['id_convenio' => $inacap->id, 'nombre' => 'Alguien'])
            ->assertSessionHasErrors('id_convenio');
    }

    public function test_la_cuenta_del_mes_cuadra(): void
    {
        $hotel = $this->hotel();
        $this->travelTo(Carbon::parse('2026-09-18 10:00'));

        EntradaCanje::create(['id_convenio' => $hotel->id, 'nombre' => 'Uno'])->forceFill(['created_at' => '2026-08-20 10:00'])->save();
        EntradaCanje::create(['id_convenio' => $hotel->id, 'nombre' => 'Dos'])->forceFill(['created_at' => '2026-09-02 10:00'])->save();
        EntradaCanje::create(['id_convenio' => $hotel->id, 'nombre' => 'Tres']);

        $cifras = collect($this->actingAs($this->recepcionista())->get('/panel/canje')->viewData('page')['props']['convenios'])->firstWhere('id', $hotel->id);

        $this->assertSame([1, 2, 1], [$cifras['hoy'], $cifras['mes'], $cifras['mes_pasado']]);
    }

    public function test_lo_anotado_por_error_se_quita_solo_el_mismo_dia(): void
    {
        $hotel = $this->hotel();
        $hoy = EntradaCanje::create(['id_convenio' => $hotel->id, 'nombre' => 'Error']);
        $ayer = EntradaCanje::create(['id_convenio' => $hotel->id, 'nombre' => 'Ayer']);
        $ayer->forceFill(['created_at' => now()->subDay()])->save();

        $recepcion = $this->recepcionista();
        $this->actingAs($recepcion)->delete("/panel/canje/{$hoy->uuid}");
        $this->actingAs($recepcion)->delete("/panel/canje/{$ayer->uuid}");

        $this->assertSame(['Ayer'], EntradaCanje::pluck('nombre')->all());
    }
}
