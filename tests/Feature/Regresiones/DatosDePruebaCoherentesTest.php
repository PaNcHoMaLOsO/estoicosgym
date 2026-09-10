<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use Tests\CasoConCatalogos;

/**
 * Los datos de prueba no pueden contradecirse a si mismos.
 *
 * Las factories sorteaban el estado aparte de los montos y de las fechas, y de
 * cien registros salian 32 pagos «Pagado» que debian dinero, 37 «Pendiente» con
 * un abono hecho y 27 inscripciones «Pausada» sin fecha de pausa. Con datos asi
 * no se distingue un fallo del sistema de un disparate del generador, y el
 * panel enseña cifras que no significan nada.
 */
class DatosDePruebaCoherentesTest extends CasoConCatalogos
{
    private const ACTIVA = 100;
    private const PAUSADA = 101;
    private const VENCIDA = 102;

    private const PAGO_PENDIENTE = 200;
    private const PAGO_PAGADO = 201;
    private const PAGO_PARCIAL = 202;

    /**
     * El precio sale de precios_membresias.
     *
     * La factory leia $membresia->precio, una columna que NO existe, asi que el
     * `?? 20000` se aplicaba siempre: un Pase Diario de $5.000 y un Anual de
     * $250.000 quedaban los dos en $20.000 y toda cifra de ingresos era ficcion.
     */
    public function test_la_inscripcion_cobra_el_precio_real_de_su_plan(): void
    {
        Cliente::factory()->count(5)->create();

        foreach (Inscripcion::factory()->count(15)->create() as $inscripcion) {
            $precio = PrecioMembresia::where('id_membresia', $inscripcion->id_membresia)
                ->where('activo', true)
                ->value('precio_normal');

            $this->assertEquals(
                (int) $precio,
                (int) $inscripcion->precio_final,
                "La inscripción no cobra el precio de su plan (membresía {$inscripcion->id_membresia})."
            );
        }

        // Y no todos valen lo mismo: si el precio volviera a quedar fijo, esto
        // lo delata aunque por casualidad coincidiera con uno de los planes.
        $this->assertGreaterThan(
            1,
            Inscripcion::distinct()->count('precio_final'),
            'Todas las inscripciones cuestan igual; el precio no se está leyendo del plan.'
        );
    }

    public function test_cada_plan_conserva_sus_pausas_permitidas(): void
    {
        Cliente::factory()->count(5)->create();

        foreach (Inscripcion::factory()->count(15)->create() as $inscripcion) {
            $delPlan = Membresia::find($inscripcion->id_membresia)->max_pausas;

            $this->assertSame(
                (int) $delPlan,
                (int) $inscripcion->max_pausas_permitidas,
                'Las pausas permitidas no coinciden con las del plan.'
            );
        }
    }

    /** Ninguna «Activa» puede tener el vencimiento ya pasado, ni al reves. */
    public function test_el_estado_concuerda_con_las_fechas(): void
    {
        Cliente::factory()->count(10)->create();

        foreach (Inscripcion::factory()->count(30)->create() as $inscripcion) {
            if ((int) $inscripcion->id_estado === self::ACTIVA) {
                $this->assertTrue(
                    $inscripcion->fecha_vencimiento >= now()->startOfDay(),
                    'Una inscripción Activa no puede estar vencida.'
                );
            }

            if ((int) $inscripcion->id_estado === self::VENCIDA) {
                $this->assertTrue(
                    $inscripcion->fecha_vencimiento < now(),
                    'Una inscripción Vencida no puede vencer en el futuro.'
                );
            }
        }
    }

    /** Pausada de verdad: con su indicador, su fecha y su motivo. */
    public function test_una_pausada_tiene_todos_sus_campos(): void
    {
        Cliente::factory()->count(10)->create();

        $pausadas = Inscripcion::factory()->count(30)->pausada()->create()
            ->where('id_estado', self::PAUSADA);

        $this->assertGreaterThan(0, $pausadas->count(), 'No se generó ninguna pausada.');

        foreach ($pausadas as $inscripcion) {
            $this->assertTrue((bool) $inscripcion->pausada, 'Estado Pausada con el indicador en false.');
            $this->assertNotNull($inscripcion->fecha_pausa_inicio, 'Pausada sin fecha de inicio.');
            $this->assertNotNull($inscripcion->razon_pausa, 'Pausada sin motivo.');
            $this->assertLessThanOrEqual(
                (int) $inscripcion->max_pausas_permitidas,
                (int) $inscripcion->pausas_realizadas,
                'Usó más pausas de las que permite su plan.'
            );
        }
    }

    /** El estado del pago sale del monto, no de un sorteo aparte. */
    public function test_el_estado_del_pago_concuerda_con_lo_abonado(): void
    {
        Cliente::factory()->count(10)->create();
        Inscripcion::factory()->count(10)->create();

        foreach (Pago::factory()->count(30)->create() as $pago) {
            $estado = (int) $pago->id_estado;
            $abonado = (int) $pago->monto_abonado;
            $pendiente = (int) $pago->monto_pendiente;

            match ($estado) {
                self::PAGO_PAGADO => $this->assertSame(0, $pendiente, 'Marcado Pagado pero debe dinero.'),
                self::PAGO_PENDIENTE => $this->assertSame(0, $abonado, 'Marcado Pendiente pero pagó algo.'),
                self::PAGO_PARCIAL => $this->assertTrue(
                    $abonado > 0 && $pendiente > 0,
                    'Marcado Parcial sin ser ni una cosa ni la otra.'
                ),
                default => $this->fail("Estado de pago inesperado: {$estado}."),
            };
        }
    }

    /** Ningún pago puede estar fechado en el futuro. */
    public function test_ningun_pago_se_fecha_en_el_futuro(): void
    {
        Cliente::factory()->count(10)->create();
        Inscripcion::factory()->count(10)->create();

        foreach (Pago::factory()->count(30)->create() as $pago) {
            $this->assertTrue(
                $pago->fecha_pago <= now()->endOfDay(),
                "Pago fechado en {$pago->fecha_pago}, que aún no ha llegado."
            );
        }
    }

    /**
     * El pago cobra lo que cuesta SU inscripción.
     *
     * Pasar ['id_inscripcion' => $x] a la factory no bastaba: los valores
     * sueltos se aplican despues de definition(), asi que el monto venia
     * calculado sobre otra inscripcion cualquiera.
     */
    public function test_el_pago_cobra_el_precio_de_su_propia_inscripcion(): void
    {
        Cliente::factory()->count(5)->create();

        foreach (Inscripcion::factory()->count(10)->create() as $inscripcion) {
            $pago = Pago::factory()->paraInscripcion($inscripcion)->create([
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
            ]);

            $this->assertEquals(
                (int) $inscripcion->precio_final,
                (int) $pago->monto_total,
                'El pago cobra un total que no es el de su inscripción.'
            );
        }
    }
}
