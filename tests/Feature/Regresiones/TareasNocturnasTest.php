<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Lo que el sistema hace solo, de noche, sin que nadie mire.
 *
 * Son los errores más caros de encontrar: no fallan delante de nadie. Un socio
 * que pagó ayer amanece dado de baja, desaparece del listado, y la persona del
 * mesón no tiene cómo saber por qué.
 */
class TareasNocturnasTest extends CasoConCatalogos
{
    private function membresia(Cliente $socio, int $estado, int $diasParaVencer): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'pausada' => $estado === 101,
            'fecha_inicio' => now()->addDays($diasParaVencer)->subMonth(),
            'fecha_vencimiento' => now()->addDays($diasParaVencer),
        ]);
    }

    private function desactivarVencidos(): void
    {
        $this->artisan('clientes:desactivar-vencidos')->assertSuccessful();
    }

    /** Lo que el comando SÍ tiene que hacer: dar de baja a quien solo tiene una vencida. */
    public function test_quien_solo_tiene_una_vencida_queda_de_baja(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->membresia($socio, 102, -10);

        $this->desactivarVencidos();

        $this->assertFalse($socio->refresh()->activo);
    }

    /**
     * EL QUE IMPORTA: quien renovó no amanece dado de baja.
     *
     * Al renovar, la membresía anterior queda «vencida» —es lo correcto: se
     * acabó—. El comando tomaba cualquier vencida y desactivaba al socio sin
     * mirar si tenía otra vigente, así que quien acababa de pagar desaparecía
     * del listado esa misma noche. Y al reactivarlo a mano, la noche siguiente
     * lo volvía a dar de baja.
     */
    public function test_quien_renovo_no_amanece_dado_de_baja(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->membresia($socio, 102, -3);   // la de antes, ya terminada
        $this->membresia($socio, 100, 27);   // la que acaba de pagar

        $this->desactivarVencidos();

        $this->assertTrue($socio->refresh()->activo, 'Pagó su renovación y el sistema lo dio de baja de noche.');
    }

    /** Una membresía en pausa sigue siendo suya: tampoco se le da de baja. */
    public function test_quien_tiene_una_pausada_no_queda_de_baja(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->membresia($socio, 102, -40);
        $this->membresia($socio, 101, 15);

        $this->desactivarVencidos();

        $this->assertTrue($socio->refresh()->activo);
    }

    /** Y a la noche siguiente sigue igual: no es cuestión de suerte con el orden. */
    public function test_tampoco_la_noche_siguiente(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->membresia($socio, 102, -3);
        $this->membresia($socio, 100, 27);

        $this->desactivarVencidos();
        $this->travel(1)->days();
        $this->desactivarVencidos();

        $this->assertTrue($socio->refresh()->activo);
    }


    // ---------- La revisión de los pagos ----------

    private function pago(Inscripcion $inscripcion, int $abonado, int $pendiente, int $estado, $fecha): Pago
    {
        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_final,
            'monto_abonado' => $abonado,
            'monto_pendiente' => $pendiente,
            'id_estado' => $estado,
            'tipo_pago' => $pendiente > 0 ? 'parcial' : 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => $fecha->format('Y-m-d'),
        ]);
    }

    private function membresiaDe40Mil(): Inscripcion
    {
        $inscripcion = $this->membresia(Cliente::factory()->create(['activo' => true]), 100, 10);
        $inscripcion->update(['precio_base' => 40000, 'precio_final' => 40000]);

        return $inscripcion;
    }

    /**
     * EL QUE IMPORTA: la revisión de pagos corre, y deja cada pago como lo dejaría el modelo.
     *
     * Se caía en su primera consulta —pedía una columna que no existe— y, de
     * haber seguido, marcaba «vencido» un pago parcial: los informes de
     * ingresos no lo habrían contado aunque la plata ya estaba cobrada.
     */
    public function test_la_revision_de_pagos_corre_y_cuadra_lo_descuadrado(): void
    {
        $inscripcion = $this->membresiaDe40Mil();

        // Tocado a mano: dice «vencido» y que no queda nada por cobrar.
        $primero = $this->pago($inscripcion, 20000, 0, 203, now()->subDays(5));
        $segundo = $this->pago($inscripcion, 10000, 10000, 202, now()->subDay());

        $this->artisan('pagos:sincronizar-estados')->assertSuccessful();

        $this->assertSame([202, 20000], [(int) $primero->refresh()->id_estado, (int) $primero->monto_pendiente]);
        $this->assertSame([202, 10000], [(int) $segundo->refresh()->id_estado, (int) $segundo->monto_pendiente]);
    }

    /** Lo que ya cuadra no se toca: pasa todas las noches por todas las membresías. */
    public function test_la_revision_de_pagos_no_toca_lo_que_cuadra(): void
    {
        $pago = $this->pago($this->membresiaDe40Mil(), 40000, 0, 201, now()->subDay());
        $antes = $pago->refresh()->updated_at;

        $this->travel(5)->minutes();
        $this->artisan('pagos:sincronizar-estados')->assertSuccessful();

        $this->assertEquals($antes, $pago->refresh()->updated_at);
    }

    public function test_la_revision_de_pagos_de_prueba_no_guarda_nada(): void
    {
        $pago = $this->pago($this->membresiaDe40Mil(), 20000, 0, 203, now()->subDay());

        $this->artisan('pagos:sincronizar-estados', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(203, (int) $pago->refresh()->id_estado);
    }

    /**
     * Un socio en la papelera no tumba la revisión de membresías.
     *
     * Leer su nombre sin mirar reventaba la tarea con el primero que apareciera:
     * las membresías que venían detrás no se marcaban nunca como vencidas.
     */
    public function test_un_socio_en_la_papelera_no_tumba_la_revision_de_membresias(): void
    {
        $borrado = Cliente::factory()->create(['activo' => true]);
        $deBorrado = $this->membresia($borrado, 100, -3);
        $borrado->delete();

        $deOtro = $this->membresia(Cliente::factory()->create(['activo' => true]), 100, -2);

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();

        $this->assertSame(102, (int) $deBorrado->refresh()->id_estado);
        $this->assertSame(102, (int) $deOtro->refresh()->id_estado);
    }
}
