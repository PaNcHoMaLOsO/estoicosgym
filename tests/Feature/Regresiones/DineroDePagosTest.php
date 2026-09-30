<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Services\ConstructorInformes;
use App\Support\EvolucionDelNegocio;
use App\Support\IngresosPorMetodo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\CasoConCatalogos;

/**
 * Plata que se contaba dos veces, en negativo o nunca.
 *
 * Cada prueba es un caso que pasó en el panel: un pago anulado que volvía de la
 * papelera sobre una membresía ya cobrada otra vez, un mixto corregido que
 * dejaba la transferencia en −$17.000, un «Pagó» que dejaba a «juan» debiendo,
 * un socio que renovaba sin cortes y salía como baja, y un informe de pagos que
 * sumaba el precio de la membresía una vez por cada abono.
 */
class DineroDePagosTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        config(['mail.default' => 'array']);
    }

    private function socio(): Cliente
    {
        return Cliente::factory()->create(['activo' => true, 'email' => null]);
    }

    private function membresia(Cliente $socio, int $precio): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id, 'id_membresia' => 4, 'id_estado' => 100,
            'precio_base' => $precio, 'precio_final' => $precio, 'descuento_aplicado' => 0,
        ]);
    }

    private function efectivo(): MetodoPago
    {
        return MetodoPago::orderBy('id')->first();
    }

    private function otroMedio(): MetodoPago
    {
        return MetodoPago::orderBy('id')->skip(1)->first();
    }

    private function pago(Inscripcion $ins, int $monto, array $extra = []): Pago
    {
        return Pago::create($extra + [
            'id_inscripcion' => $ins->id, 'id_cliente' => $ins->id_cliente,
            'monto_total' => (int) $ins->precio_final, 'monto_abonado' => $monto,
            'monto_pendiente' => max(0, (int) $ins->precio_final - $monto),
            'id_estado' => 201, 'tipo_pago' => 'completo', 'id_metodo_pago' => $this->efectivo()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    // ── 1. Papelera ────────────────────────────────────────────────────────

    public function test_no_se_restaura_un_pago_si_la_membresia_ya_se_volvio_a_cobrar(): void
    {
        $admin = $this->administrador();
        $ins = $this->membresia($this->socio(), 25000);
        $primero = $this->pago($ins, 25000, ['fecha_pago' => now()->subDays(2)->format('Y-m-d')]);

        $this->actingAs($admin)->delete("/panel/pagos/{$primero->uuid}");

        $this->actingAs($admin)->post('/panel/pagos/registrar', [
            'form_submit_token' => uniqid('t', true),
            'id_inscripcion' => $ins->id, 'tipo_pago' => 'completo',
            'id_metodo_pago' => $this->efectivo()->id, 'fecha_pago' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->patch("/panel/papelera/pagos/{$primero->id}/restaurar")
            ->assertSessionHas('error');

        $this->assertSoftDeleted($primero);
        $this->assertSame(25000, (int) $ins->pagos()->sum('monto_abonado'));
    }

    public function test_se_restaura_un_pago_que_todavia_cabe_en_su_membresia(): void
    {
        $admin = $this->administrador();
        $ins = $this->membresia($this->socio(), 25000);
        $pago = $this->pago($ins, 25000);

        $this->actingAs($admin)->delete("/panel/pagos/{$pago->uuid}");
        $this->actingAs($admin)->patch("/panel/papelera/pagos/{$pago->id}/restaurar")
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted($pago);
        $this->assertSame(0, (int) $pago->fresh()->monto_pendiente);
    }

    public function test_no_se_restaura_un_pago_cuya_membresia_esta_en_la_papelera(): void
    {
        $ins = $this->membresia($this->socio(), 25000);
        $pago = $this->pago($ins, 25000);
        $pago->delete();
        $ins->delete();

        $this->actingAs($this->administrador())->patch("/panel/papelera/pagos/{$pago->id}/restaurar")
            ->assertSessionHas('error');

        $this->assertSoftDeleted($pago);
        $this->assertSame(0, (int) Pago::ingresos()->sum('monto_abonado'));
    }

    // ── 2. Pago mixto corregido ───────────────────────────────────────────

    private function mixto(): Pago
    {
        $ins = $this->membresia($this->socio(), 30000);

        return $this->pago($ins, 30000, [
            'tipo_pago' => 'mixto',
            'id_metodo_pago2' => $this->otroMedio()->id,
            'monto_metodo1' => 20000, 'monto_metodo2' => 10000,
        ]);
    }

    public function test_corregir_un_mixto_sin_repartir_el_monto_se_rechaza(): void
    {
        $pago = $this->mixto();

        $this->actingAs($this->administrador())->put("/panel/pagos/{$pago->uuid}", [
            'form_submit_token' => uniqid('t', true),
            'monto_abonado' => 3000,
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => $this->efectivo()->id,
        ])->assertSessionHasErrors('id_metodo_pago2');

        $this->assertSame(30000, (int) $pago->fresh()->monto_abonado);
    }

    public function test_corregir_un_mixto_con_partes_que_no_cuadran_se_rechaza(): void
    {
        $pago = $this->mixto();

        $this->actingAs($this->administrador())->put("/panel/pagos/{$pago->uuid}", [
            'form_submit_token' => uniqid('t', true),
            'monto_abonado' => 3000,
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => $this->efectivo()->id,
            'id_metodo_pago2' => $this->otroMedio()->id,
            'monto_metodo1' => 20000, 'monto_metodo2' => 1000,
        ])->assertSessionHasErrors('monto_metodo1');
    }

    public function test_corregir_un_mixto_guarda_el_reparto_nuevo(): void
    {
        $pago = $this->mixto();

        $this->actingAs($this->administrador())->put("/panel/pagos/{$pago->uuid}", [
            'form_submit_token' => uniqid('t', true),
            'monto_abonado' => 3000,
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => $this->efectivo()->id,
            'id_metodo_pago2' => $this->otroMedio()->id,
            'monto_metodo1' => 2000, 'monto_metodo2' => 1000,
        ])->assertSessionHasNoErrors();

        $porMedio = IngresosPorMetodo::en(fn ($q) => $q)->pluck('total', 'nombre')->all();
        $this->assertSame([$this->efectivo()->nombre => 2000, $this->otroMedio()->nombre => 1000], $porMedio);

        $metodos = $this->actingAs($this->administrador())->get("/panel/pagos/{$pago->uuid}")
            ->viewData('page')['props']['metodos'];
        $this->assertSame([2000, 1000], array_column($metodos, 'monto'));
    }

    public function test_un_mixto_se_puede_convertir_en_pago_de_un_solo_medio(): void
    {
        $pago = $this->mixto();

        $this->actingAs($this->administrador())->put("/panel/pagos/{$pago->uuid}", [
            'form_submit_token' => uniqid('t', true),
            'monto_abonado' => 3000,
            'fecha_pago' => now()->format('Y-m-d'),
            'id_metodo_pago' => $this->efectivo()->id,
            'como_simple' => true,
        ])->assertSessionHasNoErrors();

        $pago->refresh();
        $this->assertNull($pago->id_metodo_pago2);
        $this->assertNull($pago->monto_metodo1);
        $this->assertSame('parcial', $pago->tipo_pago);

        $porMedio = IngresosPorMetodo::en(fn ($q) => $q)->pluck('total', 'nombre')->all();
        $this->assertSame([$this->efectivo()->nombre => 3000], $porMedio);
    }

    /** Una fila que ya quedó mal guardada antes del arreglo. */
    public function test_un_reparto_viejo_descuadrado_no_saca_medios_en_negativo(): void
    {
        $pago = $this->mixto();
        $pago->forceFill(['monto_abonado' => 3000])->saveQuietly();

        $porMedio = IngresosPorMetodo::en(fn ($q) => $q)->pluck('total', 'nombre')->all();
        $this->assertSame(3000, array_sum($porMedio));
        $this->assertGreaterThanOrEqual(0, min($porMedio));

        $metodos = $this->actingAs($this->administrador())->get("/panel/pagos/{$pago->uuid}")
            ->viewData('page')['props']['metodos'];
        $this->assertSame([3000, 0], array_column($metodos, 'monto'));
    }

    // ── 3. Fiado con el nombre escrito distinto ─────────────────────────────

    /** @return array<string,mixed> la cuenta tal como la ve la pantalla */
    private function cuentaDeJuan(): array
    {
        $admin = $this->administrador();
        $this->actingAs($admin)->post('/panel/fiados', ['nombre' => 'Juan', 'concepto' => 'Bebida', 'monto' => 1500]);
        $this->actingAs($admin)->post('/panel/fiados', ['nombre' => 'juan', 'concepto' => 'Barra', 'monto' => 2500]);

        $cuentas = $this->actingAs($admin)->get('/panel/fiados')->viewData('page')['props']['cuentas'];
        $this->assertCount(1, $cuentas);
        $this->assertSame(4000, $cuentas[0]['total']);

        return $cuentas[0];
    }

    public function test_pago_salda_las_lineas_que_muestra_la_pantalla(): void
    {
        $cuenta = $this->cuentaDeJuan();

        // Mientras se cobra, alguien apunta otra cosa a Juan: esa no se cobró.
        Fiado::create(['nombre' => 'JUAN', 'concepto' => 'Agua', 'monto' => 800, 'id_usuario' => $this->administrador()->id]);

        $this->actingAs($this->administrador())->post('/panel/fiados/saldar', [
            'id_cliente' => null,
            'nombre' => $cuenta['nombre'],
            'id_metodo_pago' => $this->efectivo()->id,
            'lineas' => array_column($cuenta['lineas'], 'uuid'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(800, (int) Fiado::debiendo()->sum('monto'));
    }

    public function test_pago_sin_lineas_salda_la_cuenta_como_la_agrupa_la_pantalla(): void
    {
        $cuenta = $this->cuentaDeJuan();

        $this->actingAs($this->administrador())->post('/panel/fiados/saldar', [
            'id_cliente' => null,
            'nombre' => $cuenta['nombre'],
            'id_metodo_pago' => $this->efectivo()->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, (int) Fiado::debiendo()->sum('monto'));
    }

    public function test_deshacer_el_cobro_reabre_toda_la_cuenta(): void
    {
        $cuenta = $this->cuentaDeJuan();
        $admin = $this->administrador();

        $this->actingAs($admin)->post('/panel/fiados/saldar', [
            'id_cliente' => null,
            'nombre' => $cuenta['nombre'],
            'id_metodo_pago' => $this->efectivo()->id,
            'lineas' => array_column($cuenta['lineas'], 'uuid'),
        ]);

        $cualquiera = Fiado::where('nombre', 'juan')->first();
        $this->actingAs($admin)->patch("/panel/fiados/{$cualquiera->uuid}/reabrir")->assertSessionHasNoErrors();

        $this->assertSame(4000, (int) Fiado::debiendo()->sum('monto'));
    }

    // ── 4. «Se fueron» con renovaciones sin cortes ───────────────────────

    public function test_quien_renueva_sin_cortes_no_cuenta_como_baja(): void
    {
        $this->travelTo(Carbon::create(2027, 3, 1));
        $socio = $this->socio();

        // Abril mensual, mayo trimestral, agosto trimestral: cada una empieza
        // al día siguiente de que vence la anterior. Se va en noviembre.
        foreach ([['2026-04-11', '2026-05-10'], ['2026-05-11', '2026-08-10'], ['2026-08-11', '2026-11-10']] as [$desde, $hasta]) {
            Inscripcion::factory()->create([
                'id_cliente' => $socio->id, 'id_membresia' => 4, 'id_estado' => 100,
                'precio_base' => 40000, 'precio_final' => 40000,
                'fecha_inicio' => Carbon::parse($desde), 'fecha_vencimiento' => Carbon::parse($hasta),
            ]);
        }

        $meses = collect((new EvolucionDelNegocio(12))->porMes())->keyBy('clave');

        $this->assertSame(0, $meses['2026-05']['se_fueron']);
        $this->assertSame(0, $meses['2026-08']['se_fueron']);
        $this->assertSame(1, $meses['2026-11']['se_fueron']);
        $this->assertSame(1, $meses['2026-05']['activos']);
    }

    // ── 5. Informe de pagos ────────────────────────────────────────────

    public function test_el_informe_de_pagos_no_suma_el_precio_una_vez_por_abono(): void
    {
        $ins = $this->membresia($this->socio(), 40000);
        $this->pago($ins, 20000, ['tipo_pago' => 'parcial']);
        $this->pago($ins, 20000, ['tipo_pago' => 'parcial']);
        $ins->recalcularSusPagos();

        $informe = app(ConstructorInformes::class)->ejecutar('pagos', [
            'columnas' => ['monto_abonado', 'monto_pendiente', 'monto_total'],
            'limite' => 10,
        ]);

        $this->assertSame(['monto_abonado' => 40000], $informe['totales']);
    }
}
