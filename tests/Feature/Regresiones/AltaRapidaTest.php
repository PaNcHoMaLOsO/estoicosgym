<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use Tests\CasoConCatalogos;

/**
 * El alta rápida del mesón: lo mínimo para inscribir a alguien.
 *
 * Lo que se vigila: que el correo no sea obligatorio, que un extranjero pueda
 * dejar su celular con su código de país y su pasaporte en vez de RUT, y que un
 * celular chileno mal escrito se siga rechazando.
 */
class AltaRapidaTest extends CasoConCatalogos
{
    private function alta(array $datos)
    {
        return $this->actingAs($this->administrador())->post('/panel/clientes', $datos + [
            'flujo_cliente' => 'solo_cliente',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '+56 9 1234 5674',
        ]);
    }

    public function test_se_inscribe_sin_correo(): void
    {
        $this->alta(['run_pasaporte' => '11.111.111-1', 'email' => ''])->assertSessionHasNoErrors();

        $this->assertNull(Cliente::where('run_pasaporte', '11.111.111-1')->value('email'));
    }

    public function test_dos_socios_sin_correo_no_chocan(): void
    {
        $this->alta(['run_pasaporte' => '11.111.111-1'])->assertSessionHasNoErrors();
        $this->alta(['run_pasaporte' => '22.222.222-2', 'nombres' => 'Pedro'])->assertSessionHasNoErrors();

        $this->assertSame(2, Cliente::whereNull('email')->count());
    }

    public function test_un_extranjero_deja_su_celular_y_su_pasaporte(): void
    {
        $this->alta([
            'tipo_documento' => 'pasaporte',
            'run_pasaporte' => 'AB1234567',
            'celular' => '+54 9 11 2345 6789',
        ])->assertSessionHasNoErrors();

        $this->assertSame('+5491123456789', Cliente::where('run_pasaporte', 'AB1234567')->value('celular'));
    }

    public function test_un_pasaporte_no_pasa_por_rut(): void
    {
        $this->alta(['run_pasaporte' => 'AB1234567'])->assertSessionHasErrors('run_pasaporte');
    }

    public function test_un_celular_chileno_mal_escrito_se_rechaza(): void
    {
        $this->alta(['celular' => '+56 9 1234'])->assertSessionHasErrors('celular');
        $this->alta(['celular' => '+56 8 1234 5678'])->assertSessionHasErrors('celular');
    }

    /**
     * El alta con pago mixto guarda las DOS partes, cada una con su medio.
     *
     * La pantalla ofrecía «mixto» pero mandaba un solo monto y un solo medio:
     * mitad en efectivo y mitad con tarjeta quedaba entera en efectivo, y la
     * caja del día no cuadraba con lo que había en el cajón.
     */
    public function test_el_alta_con_pago_mixto_guarda_cada_parte_con_su_medio(): void
    {
        $plan = \App\Models\Membresia::where('activo', true)->whereHas('precios', fn ($q) => $q->where('activo', true))->firstOrFail();
        $precio = (int) $plan->precios()->where('activo', true)->latest('fecha_vigencia_desde')->value('precio_normal');
        [$uno, $dos] = \App\Models\MetodoPago::where('activo', true)->orderBy('id')->take(2)->get()->all();
        $mitad = intdiv($precio, 2);

        $this->alta([
            'flujo_cliente' => 'completo',
            'run_pasaporte' => '11.111.111-1',
            'id_membresia' => $plan->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_pago' => now()->toDateString(),
            'tipo_pago' => 'mixto',
            'detalle_pagos_mixto' => json_encode([
                ['id_metodo_pago' => $uno->id, 'monto' => $mitad],
                ['id_metodo_pago' => $dos->id, 'monto' => $precio - $mitad],
            ]),
        ])->assertSessionHasNoErrors();

        $pagos = Cliente::where('run_pasaporte', '11.111.111-1')->firstOrFail()->pagos()->orderBy('id')->get();

        $this->assertCount(2, $pagos);
        $this->assertSame([$uno->id, $dos->id], $pagos->pluck('id_metodo_pago')->all());
        $this->assertSame($precio, (int) $pagos->sum('monto_abonado'));
        $this->assertSame(0, (int) $pagos->last()->monto_pendiente);
        // Entró todo: queda Pagado, no Parcial.
        $this->assertSame([201, 201], $pagos->pluck('id_estado')->map(fn ($e) => (int) $e)->all());
    }

    /** Y un mixto sin sus partes se rechaza en vez de guardarse a medias. */
    public function test_un_mixto_sin_partes_se_rechaza(): void
    {
        $plan = \App\Models\Membresia::where('activo', true)->whereHas('precios', fn ($q) => $q->where('activo', true))->firstOrFail();

        $this->alta([
            'flujo_cliente' => 'completo',
            'run_pasaporte' => '11.111.111-1',
            'id_membresia' => $plan->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_pago' => now()->toDateString(),
            'tipo_pago' => 'mixto',
            'monto_abonado' => 10000,
        ])->assertSessionHasErrors('detalle_pagos_mixto');

        $this->assertSame(0, Cliente::where('run_pasaporte', '11.111.111-1')->count());
    }
}
