<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Support\EvolucionDelNegocio;
use Illuminate\Support\Carbon;
use Tests\CasoConCatalogos;

/**
 * El informe de cómo va el negocio.
 *
 * ES EL ÚNICO QUE MIRA EL TIEMPO, y por eso es el que más fácil miente: contar
 * como socio nuevo al que renueva, dar por perdido al que pagó tres días tarde,
 * o contar dos veces al que compró dos planes el mismo mes. Cada una de esas
 * cuentas hace que el gimnasio parezca lo que no es, así que cada una tiene su
 * prueba.
 */
class EvolucionDelNegocioTest extends CasoConCatalogos
{
    private function membresia(Cliente $socio, string $desde, string $hasta, int $precio = 40000, int $plan = 4): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => $plan,
            'id_estado' => 100,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'fecha_inicio' => Carbon::parse($desde),
            'fecha_vencimiento' => Carbon::parse($hasta),
        ]);
    }

    private function socio(string $nombre = 'Socio'): Cliente
    {
        return Cliente::factory()->create(['activo' => true, 'nombres' => $nombre]);
    }

    /** @return array<string,mixed> */
    private function mes(string $clave, int $meses = 12): array
    {
        $filas = collect((new EvolucionDelNegocio($meses))->porMes());

        return $filas->firstWhere('clave', $clave) ?? [];
    }

    /**
     * QUIEN RENUEVA NO ES UN SOCIO NUEVO.
     *
     * Contando cualquier membresía que empiece, el socio de siempre que renueva
     * cada mes sale doce veces al año como alta y el gimnasio parece crecer sin
     * parar mientras no entra nadie.
     */
    public function test_la_segunda_membresia_es_renovacion_y_no_alta(): void
    {
        $this->travelTo(Carbon::create(2026, 6, 15));

        $socio = $this->socio('Rosa');
        $this->membresia($socio, '2026-04-01', '2026-04-30');
        $this->membresia($socio, '2026-05-01', '2026-05-31');

        $abril = $this->mes('2026-04');
        $mayo = $this->mes('2026-05');

        $this->assertSame(1, $abril['altas']);
        $this->assertSame(0, $abril['renovaciones']);

        $this->assertSame(0, $mayo['altas'], 'Renovar no puede contar como socio nuevo.');
        $this->assertSame(1, $mayo['renovaciones']);
    }

    /**
     * LOS ACTIVOS SON PERSONAS, NO MEMBRESÍAS. Quien compra dos planes a la vez
     * —pasa: el socio que paga el suyo y el de su pareja— es un socio.
     */
    public function test_los_activos_se_cuentan_por_persona(): void
    {
        $this->travelTo(Carbon::create(2026, 6, 15));

        $socio = $this->socio('Doble');
        $this->membresia($socio, '2026-06-01', '2026-06-30');
        $this->membresia($socio, '2026-06-02', '2026-07-02', 40000, 3);

        $this->assertSame(1, $this->mes('2026-06')['activos']);
    }

    /**
     * EL QUE RENUEVA TARDE NO SE FUE.
     *
     * En el mesón se renueva tres días después sin que a nadie le parezca raro.
     * Sin plazo de gracia, esa persona aparecería como perdida y recuperada el
     * mismo mes, y las bajas saldrían tres veces más altas de lo real.
     */
    public function test_renovar_unos_dias_tarde_no_cuenta_como_baja(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 15));

        $puntual = $this->socio('Puntual');
        $this->membresia($puntual, '2026-05-01', '2026-05-31');
        $this->membresia($puntual, '2026-06-10', '2026-07-10');

        $ido = $this->socio('Ido');
        $this->membresia($ido, '2026-05-01', '2026-05-31');

        // Solo el que no volvió a comprar nada cuenta como baja de mayo.
        $this->assertSame(1, $this->mes('2026-05')['se_fueron']);
    }

    /**
     * LOS ÚLTIMOS MESES NO SE DAN POR CERRADOS: esa gente todavía puede volver,
     * y contarla ya como perdida pintaría una caída que no ocurrió.
     */
    public function test_el_mes_en_curso_no_cuenta_bajas(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 15));

        $socio = $this->socio('Reciente');
        $this->membresia($socio, '2026-08-20', '2026-09-05');

        $this->assertSame(0, $this->mes('2026-09')['se_fueron']);
    }

    /** Los pases diarios no son socios que se ganen ni que se pierdan. */
    public function test_los_pases_no_entran_en_la_cuenta(): void
    {
        $this->travelTo(Carbon::create(2026, 6, 15));

        $dePaso = $this->socio('De paso');
        // El plan 5 es el pase diario.
        $this->membresia($dePaso, '2026-06-02', '2026-06-02', 5000, 5);

        $junio = $this->mes('2026-06');

        $this->assertSame(0, $junio['altas']);
        $this->assertSame(0, $junio['activos']);
    }

    /** Vuelve a comprar: quién compró una segunda vez, de los que ya pudieron. */
    public function test_la_retencion_mira_quien_compro_dos_veces(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 15));

        $vuelve = $this->socio('Vuelve');
        $this->membresia($vuelve, '2026-01-01', '2026-01-31');
        $this->membresia($vuelve, '2026-02-01', '2026-02-28');

        $noVuelve = $this->socio('No vuelve');
        $this->membresia($noVuelve, '2026-01-01', '2026-01-31');

        // Este entró la semana pasada: todavía no ha tenido ocasión de volver,
        // así que no puede hundir el porcentaje.
        $recien = $this->socio('Recién');
        $this->membresia($recien, '2026-09-10', '2026-10-10');

        $retencion = (new EvolucionDelNegocio(12))->retencion();

        $this->assertSame(2, $retencion['nuevos']);
        $this->assertSame(1, $retencion['volvieron']);
        $this->assertSame(50, $retencion['porcentaje']);
    }

    /** La pantalla se abre y trae lo suyo. */
    public function test_la_pantalla_responde(): void
    {
        $socio = $this->socio('Alguien');
        $this->membresia($socio, today()->subDays(10)->toDateString(), today()->addDays(20)->toDateString());

        $props = $this->actingAs($this->administrador())
            ->get('/panel/reportes/negocio')
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertCount(24, $props['porMes']);
        $this->assertArrayHasKey('porcentaje', $props['retencion']);
        $this->assertArrayHasKey('avisar', $props['importadas']);
    }

    /** Recepción no ve los informes: esto es el negocio, no el mesón. */
    public function test_recepcion_no_entra(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/reportes/negocio')
            ->assertForbidden();
    }
}
