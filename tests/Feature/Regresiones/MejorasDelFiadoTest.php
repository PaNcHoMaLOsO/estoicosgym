<?php

namespace Tests\Feature\Regresiones;

use App\Http\Controllers\Panel\FiadoController;
use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\FiadoRegistro;
use App\Models\MetodoPago;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * Lo que se le sumó a la libreta del mesón: abonar, el tope, el celular de
 * quien no es socio, pasar una cuenta a un socio, «Ya pagado» por mes, la
 * lista de precios, y el control de lo que se quita o se deshace.
 */
class MejorasDelFiadoTest extends CasoConCatalogos
{
    private $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
    }

    private function como($usuario = null)
    {
        return $this->actingAs($usuario ?? ($this->admin ??= $this->administrador()));
    }

    private function efectivo(): int
    {
        return (int) MetodoPago::where('nombre', 'like', '%fectivo%')->value('id');
    }

    private function fiar(array $datos)
    {
        return $this->como()->post('/panel/fiados', $datos + ['concepto' => 'Bebida']);
    }

    // ---------- Abonar ----------

    public function test_abona_lo_mas_viejo_primero_y_parte_la_linea_que_no_alcanza(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'concepto' => 'Proteína', 'monto' => 2500]);
        $this->travel(1)->minutes();
        $this->fiar(['id_cliente' => $socio->id, 'concepto' => 'Barra', 'monto' => 1500]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id, 'id_metodo_pago' => $this->efectivo(), 'monto' => 3000])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'abonó $3.000') && str_contains($m, 'quedan $1.000'));

        // Entró justo lo que trajo, y lo que queda sigue diciendo qué se llevó.
        $this->assertSame(3000, (int) Fiado::where('pagado', true)->sum('monto'));
        $this->assertSame(1000, (int) Fiado::debiendo()->sum('monto'));
        $this->assertSame('Barra', Fiado::debiendo()->value('concepto'));
        $this->assertTrue(Fiado::where('pagado', true)->where('concepto', 'Barra (abono)')->where('monto', 500)->exists());
        $this->assertTrue(Fiado::where('pagado', true)->where('concepto', 'Proteína')->exists());
    }

    public function test_no_se_cobra_mas_de_lo_que_debe(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);

        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo(), 'monto' => 5000])
            ->assertSessionHasErrors('monto');

        $this->assertSame(2000, (int) Fiado::debiendo()->sum('monto'));
    }

    public function test_sin_monto_salda_todo_como_antes(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);
        $this->fiar(['nombre' => 'Visita', 'monto' => 700]);

        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()])
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Cuenta saldada'));

        $this->assertSame(0, Fiado::debiendo()->count());
    }

    // ---------- Deshacer un cobro ----------

    public function test_recepcion_deshace_lo_de_hoy_pero_no_lo_de_otro_dia(): void
    {
        $recepcion = $this->recepcionista();
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()]);
        $linea = Fiado::where('pagado', true)->firstOrFail();

        // De hoy: sí.
        $this->como($recepcion)->patch("/panel/fiados/{$linea->uuid}/reabrir")->assertSessionHas('success');
        $this->assertSame(2000, (int) Fiado::debiendo()->sum('monto'));

        // Cobrado ayer: ya está en la caja de ayer.
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()]);
        Fiado::where('pagado', true)->update(['pagado_en' => now()->subDay()]);

        $this->como($recepcion)->patch("/panel/fiados/{$linea->uuid}/reabrir")
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Solo un administrador'));
        $this->assertSame(0, Fiado::debiendo()->count());

        // El administrador sí, y queda en el registro.
        $this->como()->patch("/panel/fiados/{$linea->uuid}/reabrir")->assertSessionHas('success');
        $this->assertSame(2000, (int) Fiado::debiendo()->sum('monto'));
        $this->assertSame(2, FiadoRegistro::where('accion', 'reabierto')->count());
    }

    // ---------- Quitar ----------

    public function test_quitar_esconde_la_linea_y_deja_quien_lo_hizo(): void
    {
        $this->fiar(['nombre' => 'Visita', 'concepto' => 'Agua', 'monto' => 1000]);
        $linea = Fiado::firstOrFail();

        $this->como()->delete("/panel/fiados/{$linea->uuid}");

        $this->assertSame(0, Fiado::debiendo()->count());
        $this->assertSoftDeleted($linea);
        $this->assertSame($this->admin->id, Fiado::withTrashed()->find($linea->id)->id_usuario_quito);

        $registro = FiadoRegistro::where('accion', 'quitado')->firstOrFail();
        $this->assertSame(1000, $registro->monto);
        $this->assertStringContainsString('Agua', $registro->detalle);
        $this->assertSame('Visita', $registro->aNombreDe());
    }

    public function test_el_registro_lo_ve_solo_quien_administra(): void
    {
        $this->assertIsArray($this->como()->get('/panel/fiados')->viewData('page')['props']['registro']);
        $this->assertNull($this->como($this->recepcionista())->get('/panel/fiados')->viewData('page')['props']['registro']);
    }

    // ---------- Tope ----------

    public function test_el_tope_avisa_y_se_puede_confirmar(): void
    {
        Ajustes::guardar(['meson.tope_fiado' => 5000]);

        $this->fiar(['nombre' => 'Visita', 'monto' => 4000])->assertSessionHasNoErrors();
        $this->fiar(['nombre' => 'Visita', 'monto' => 2000])->assertSessionHasErrors('tope');
        $this->assertSame(4000, (int) Fiado::debiendo()->sum('monto'));

        $this->fiar(['nombre' => 'Visita', 'monto' => 2000, 'pasar_tope' => true])->assertSessionHasNoErrors();
        $this->assertSame(6000, (int) Fiado::debiendo()->sum('monto'));
    }

    public function test_sin_tope_no_avisa(): void
    {
        $this->fiar(['nombre' => 'Visita', 'monto' => 900000])->assertSessionHasNoErrors();
    }

    // ---------- Celular de quien no es socio ----------

    public function test_el_celular_de_una_visita_sirve_para_recordarle_toda_su_cuenta(): void
    {
        $this->fiar(['nombre' => 'Pedro', 'monto' => 1000]);
        $this->fiar(['nombre' => 'pedro', 'monto' => 500, 'celular' => '+56 9 1234 5678']);

        $cuenta = $this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas'][0];

        $this->assertSame('+56912345678', $cuenta['celular']);
        $this->assertSame(2, Fiado::where('celular', '+56912345678')->count());
    }

    // ---------- Pasar a un socio ----------

    public function test_la_cuenta_de_una_visita_pasa_a_un_socio(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Pedro', 'apellido_paterno' => 'Soto']);
        $this->fiar(['nombre' => 'Pedro', 'monto' => 1000]);
        $this->fiar(['nombre' => 'Pedro', 'monto' => 800]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Pedro', 'id_metodo_pago' => $this->efectivo(), 'monto' => 1000]);

        $this->como()->post('/panel/fiados/asignar', ['nombre' => 'pedro ', 'id_cliente' => $socio->id])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(0, Fiado::whereNull('id_cliente')->count());
        $this->assertSame(800, (int) Fiado::debiendo()->where('id_cliente', $socio->id)->sum('monto'));
        $this->assertSame(1000, (int) Fiado::where('pagado', true)->where('id_cliente', $socio->id)->sum('monto'));
        $this->assertSame(1, FiadoRegistro::where('accion', 'asignado')->count());
    }

    // ---------- Ya pagado, por mes ----------

    public function test_ya_pagado_se_mira_mes_a_mes_y_por_cobro(): void
    {
        $this->fiar(['nombre' => 'Visita', 'concepto' => 'Agua', 'monto' => 1000]);
        $this->fiar(['nombre' => 'Visita', 'concepto' => 'Barra', 'monto' => 2000]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()]);
        $pasado = now()->subMonthNoOverflow()->startOfMonth()->addDays(3);
        Fiado::query()->update(['pagado_en' => $pasado]);

        $this->assertSame([], $this->como()->get('/panel/fiados')->viewData('page')['props']['cobrado']);

        $props = $this->como()->get('/panel/fiados?mes=' . $pasado->format('Y-m'))->viewData('page')['props'];
        $this->assertCount(1, $props['cobrado']);
        $this->assertSame(3000, $props['cobrado'][0]['total']);
        $this->assertSame(['Agua', 'Barra'], array_column($props['cobrado'][0]['lineas'], 'concepto'));
        $this->assertFalse($props['cobrado'][0]['de_hoy']);

        // Un mes sin forma o futuro cae en el actual.
        $this->assertSame(now()->format('Y-m'), $this->como()->get('/panel/fiados?mes=2999-01')->viewData('page')['props']['mes']);
    }

    // ---------- Lista de precios ----------

    public function test_la_lista_de_precios_sale_primero_al_anotar(): void
    {
        Ajustes::guardar(['meson.precios' => "Barra de proteína = 2.500\nAgua: 1000\nlínea sin precio\nBebida $1.200"]);

        $this->assertSame([
            ['concepto' => 'Barra de proteína', 'monto' => 2500, 'veces' => null],
            ['concepto' => 'Agua', 'monto' => 1000, 'veces' => null],
            ['concepto' => 'Bebida', 'monto' => 1200, 'veces' => null],
        ], FiadoController::listaDePrecios());

        // Lo más fiado va después, sin repetir lo de la lista.
        $this->fiar(['nombre' => 'A', 'concepto' => 'Agua', 'monto' => 900]);
        $this->fiar(['nombre' => 'B', 'concepto' => 'agua', 'monto' => 900]);
        $this->fiar(['nombre' => 'A', 'concepto' => 'Candado', 'monto' => 3000]);
        $this->fiar(['nombre' => 'B', 'concepto' => 'Candado', 'monto' => 3000]);

        $frecuentes = $this->como()->getJson('/panel/fiados/frecuentes')->json('frecuentes');

        $this->assertSame(['Barra de proteína', 'Agua', 'Bebida', 'Candado'], array_column($frecuentes, 'concepto'));
        $this->assertSame(1000, $frecuentes[1]['monto']);
    }
}
