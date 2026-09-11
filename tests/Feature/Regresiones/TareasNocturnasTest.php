<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
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
}
