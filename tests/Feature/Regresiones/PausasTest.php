<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use Tests\CasoConCatalogos;

/**
 * Pausar y reanudar: los días de la membresía.
 *
 * Una pausa congela lo que le queda al socio y se lo devuelve al reanudar. El
 * error aquí no se ve en pantalla: son días de más en una fecha de vencimiento
 * que nadie recalcula a mano.
 */
class PausasTest extends CasoConCatalogos
{
    /** Pausada hace $haceDias, por $duracion días, con $quedaban días por delante al pausar. */
    private function pausada(int $haceDias, int $duracion, int $quedaban): Inscripcion
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inicioPausa = now()->startOfDay()->subDays($haceDias);

        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 101,
            'pausada' => true,
            'pausa_indefinida' => false,
            'dias_pausa' => $duracion,
            'dias_restantes_al_pausar' => $quedaban,
            'fecha_pausa_inicio' => $inicioPausa,
            'fecha_pausa_fin' => $inicioPausa->copy()->addDays($duracion),
            // La fecha que tenía al pausar.
            'fecha_vencimiento' => $inicioPausa->copy()->addDays($quedaban),
            'pausas_realizadas' => 1,
        ]);
    }

    /**
     * EL QUE IMPORTA: la tarea nocturna reanuda desde el fin de la pausa.
     *
     * Pausa de 7 días que terminó hace dos semanas y nadie reanudó. El socio
     * tiene que quedar con sus 20 días contados desde que acabó la pausa —lo
     * que pidió—, no desde hoy: eso le regalaría las dos semanas de retraso.
     */
    public function test_la_tarea_nocturna_no_regala_los_dias_de_retraso(): void
    {
        $inscripcion = $this->pausada(haceDias: 21, duracion: 7, quedaban: 20);
        $finDeLaPausa = $inscripcion->fecha_pausa_fin->copy();

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();

        $inscripcion->refresh();

        $this->assertFalse((bool) $inscripcion->pausada);
        $this->assertSame(
            $finDeLaPausa->copy()->addDays(20)->toDateString(),
            $inscripcion->fecha_vencimiento->toDateString(),
            'Se le regalaron los días que la pausa estuvo terminada sin reanudar.'
        );
    }

    /** Reanudar ANTES de tiempo cuenta desde hoy: la pausa se corta ahí. */
    public function test_reanudar_antes_de_tiempo_cuenta_desde_hoy(): void
    {
        $inscripcion = $this->pausada(haceDias: 2, duracion: 10, quedaban: 20);

        $inscripcion->reanudar();

        $this->assertSame(
            now()->startOfDay()->addDays(20)->toDateString(),
            $inscripcion->refresh()->fecha_vencimiento->toDateString()
        );
    }

    /** Y a mano, tarde, lo mismo que la tarea: no se regalan días. */
    public function test_reanudar_a_mano_tarde_tampoco_regala_dias(): void
    {
        $inscripcion = $this->pausada(haceDias: 30, duracion: 7, quedaban: 20);
        $finDeLaPausa = $inscripcion->fecha_pausa_fin->copy();

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/reanudar")
            ->assertOk();

        $this->assertSame(
            $finDeLaPausa->copy()->addDays(20)->toDateString(),
            $inscripcion->refresh()->fecha_vencimiento->toDateString()
        );
    }

    /** Una pausa indefinida no tiene fin: reanudar cuenta desde hoy. */
    public function test_una_pausa_indefinida_cuenta_desde_hoy(): void
    {
        $inscripcion = $this->pausada(haceDias: 15, duracion: 0, quedaban: 20);
        $inscripcion->update(['pausa_indefinida' => true, 'fecha_pausa_fin' => null]);

        $inscripcion->refresh()->reanudar();

        $this->assertSame(
            now()->startOfDay()->addDays(20)->toDateString(),
            $inscripcion->refresh()->fecha_vencimiento->toDateString()
        );
    }

    /**
     * El aviso del botón dice los días de verdad.
     *
     * Contaba hasta hoy: de una pausa de 7 días reanudada a las tres semanas
     * decía «estuvo pausada 30 días», mientras el historial —y los días que se
     * devuelven— cuentan 7.
     */
    public function test_el_aviso_de_reanudar_tarde_cuenta_los_dias_de_la_pausa(): void
    {
        $inscripcion = $this->pausada(haceDias: 30, duracion: 7, quedaban: 20);

        $mensaje = $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/reanudar")
            ->assertOk()
            ->json('message');

        $this->assertStringContainsString('Estuvo pausada 7 días', $mensaje);
        $this->assertStringNotContainsString('30 días', $mensaje);
    }
}
