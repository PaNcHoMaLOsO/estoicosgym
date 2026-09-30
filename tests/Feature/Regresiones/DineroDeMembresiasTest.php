<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\CasoConCatalogos;

/**
 * Errores de dinero y fechas en las membresías.
 *
 * Cinco agujeros por los que entraba plata a la fecha equivocada, se cobraba
 * dos veces o no se podía cobrar lo que se debía:
 *
 *  - la fecha de pago se aceptaba en el futuro (el navegador proponía la de
 *    mañana desde las 21:00, por calcularla en UTC);
 *  - la baja de la noche dejaba al deudor fuera de Cobrar para siempre;
 *  - una membresía pausada se podía renovar y después «reanudar», y el socio
 *    quedaba con dos vigentes;
 *  - se podía renovar una vieja teniendo otra vigente;
 *  - una cortesía de $0 quedaba con el pago «Pendiente» tras la revisión.
 */
class DineroDeMembresiasTest extends CasoConCatalogos
{
    /** Mensual: $40.000. */
    private const PLAN_MENSUAL = 4;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        config(['mail.default' => 'array']);
    }

    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true, 'email' => null]);
    }

    private function membresia(Cliente $socio, array $extra = []): Inscripcion
    {
        return Inscripcion::factory()->create($extra + [
            'id_cliente' => $socio->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => now()->subDays(25),
            'fecha_vencimiento' => now()->addDays(5),
            'precio_base' => 40000,
            'precio_final' => 40000,
            'descuento_aplicado' => 0,
        ]);
    }

    private function pausada(Cliente $socio, array $extra = []): Inscripcion
    {
        return $this->membresia($socio, $extra + [
            'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
            'pausada' => true,
            'pausa_indefinida' => false,
            'dias_pausa' => 7,
            'dias_restantes_al_pausar' => 20,
            'fecha_pausa_inicio' => now()->startOfDay()->subDays(2),
            'fecha_pausa_fin' => now()->startOfDay()->addDays(5),
            'pausas_realizadas' => 1,
        ]);
    }

    /** @return array<string,mixed> */
    private function cobro(array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    private function renovar(Inscripcion $inscripcion, array $extra = [])
    {
        return $this->actingAs($this->administrador())
            ->post("/panel/inscripciones/{$inscripcion->uuid}/renovar", $this->cobro($extra));
    }

    /** @return array<string,bool> */
    private function puede(Inscripcion $inscripcion): array
    {
        return $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->viewData('page')['props']['puede'];
    }

    // ------------------------------------------------------------------
    // 1. La fecha de pago no puede ser futura
    // ------------------------------------------------------------------

    public function test_la_app_cuenta_los_dias_en_hora_de_chile(): void
    {
        $this->assertSame('America/Santiago', config('app.timezone'));
    }

    /**
     * A las 22:30 en Chile ya es mañana en Greenwich. El formulario proponía
     * esa fecha, y el servidor la aceptaba: el pago caía en la caja del día
     * siguiente. Ahora «mañana» se rechaza y «hoy» es el de Chile.
     */
    public function test_inscribir_rechaza_una_fecha_de_pago_futura(): void
    {
        // Las 22:30 de un día que viene, no de una fecha fija: el precio
        // cargado vale desde hoy y un viaje al pasado lo dejaba sin precio.
        $hoy = now('America/Santiago')->addDays(2)->format('Y-m-d');
        $this->travelTo(Carbon::parse("{$hoy} 22:30", 'America/Santiago'));

        $socio = $this->socio();
        $mananaEnGreenwich = now()->utc()->format('Y-m-d');
        $this->assertNotSame($hoy, $mananaEnGreenwich);

        $this->actingAs($this->administrador())
            ->post('/panel/inscripciones', $this->cobro([
                'id_cliente' => $socio->id,
                'fecha_pago' => $mananaEnGreenwich,
            ]))
            ->assertSessionHasErrors('fecha_pago');

        $this->assertSame(0, Inscripcion::count());

        $this->actingAs($this->administrador())
            ->post('/panel/inscripciones', $this->cobro([
                'id_cliente' => $socio->id,
                'fecha_pago' => $hoy,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame($hoy, Pago::firstOrFail()->fecha_pago->format('Y-m-d'));
    }

    /** Lo mismo en el abono y en el pago repartido, y al renovar. */
    public function test_abono_mixto_y_renovacion_tampoco_aceptan_fecha_futura(): void
    {
        $manana = now()->addDay()->format('Y-m-d');
        $metodos = MetodoPago::orderBy('id')->pluck('id');

        $this->actingAs($this->administrador())
            ->post('/panel/inscripciones', $this->cobro([
                'id_cliente' => $this->socio()->id,
                'tipo_pago' => 'abono',
                'monto_abonado' => 10000,
                'fecha_pago' => $manana,
            ]))
            ->assertSessionHasErrors('fecha_pago');

        $this->actingAs($this->administrador())
            ->post('/panel/inscripciones', $this->cobro([
                'id_cliente' => $this->socio()->id,
                'tipo_pago' => 'mixto',
                'detalle_pagos_mixto' => json_encode([
                    ['id_metodo_pago' => $metodos[0], 'monto' => 20000],
                    ['id_metodo_pago' => $metodos[1], 'monto' => 20000],
                ]),
                'fecha_pago' => $manana,
            ]))
            ->assertSessionHasErrors('fecha_pago');

        $anterior = $this->membresia($this->socio());
        $this->renovar($anterior, [
            'fecha_inicio' => now()->addDays(6)->format('Y-m-d'),
            'fecha_pago' => $manana,
        ])->assertSessionHasErrors('fecha_pago');

        $this->assertSame(1, Inscripcion::count());
    }

    // ------------------------------------------------------------------
    // 2. La deuda de quien la revisión dio de baja se sigue cobrando
    // ------------------------------------------------------------------

    public function test_al_socio_dado_de_baja_por_la_revision_se_le_cobra_lo_que_debe(): void
    {
        $socio = $this->socio(['nombres' => 'Deudor']);
        $inscripcion = $this->membresia($socio, [
            'fecha_inicio' => now()->subDays(40),
            'fecha_vencimiento' => now()->subDays(10),
        ]);
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 40000,
            'monto_abonado' => 10000,
            'monto_pendiente' => 30000,
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->subDays(40)->format('Y-m-d'),
        ]);

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();
        $this->artisan('pagos:sincronizar-estados')->assertSuccessful();
        $this->artisan('clientes:desactivar-vencidos')->assertSuccessful();

        $this->assertFalse((bool) $socio->fresh()->activo, 'La revisión lo da de baja: eso no cambia.');

        $encontradas = $this->actingAs($this->administrador())
            ->getJson('/panel/pagos/buscar?q=Deudor')
            ->json('inscripciones');

        $this->assertCount(1, $encontradas, 'El buscador de Cobrar tiene que encontrar al que debe.');
        $this->assertSame(30000, $encontradas[0]['pendiente']);

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', [
                'form_submit_token' => uniqid('t', true),
                'id_inscripcion' => $inscripcion->id,
                'tipo_pago' => 'completo',
                'id_metodo_pago' => MetodoPago::first()->id,
                'fecha_pago' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(40000, (int) $inscripcion->pagos()->sum('monto_abonado'));
        // Saldar lo que debía no lo reactiva ni le crea otra membresía.
        $this->assertFalse((bool) $socio->fresh()->activo);
        $this->assertSame(1, Inscripcion::count());
        $this->assertSame(EstadosCodigo::INSCRIPCION_VENCIDA, (int) $inscripcion->fresh()->id_estado);
    }

    /** Quien no debe nada sigue sin aparecer en Cobrar. */
    public function test_el_dado_de_baja_sin_deuda_no_aparece_en_cobrar(): void
    {
        $socio = $this->socio(['nombres' => 'Aldia', 'activo' => false]);
        $inscripcion = $this->membresia($socio, [
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'fecha_vencimiento' => now()->subDays(10),
        ]);
        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 40000,
            'monto_abonado' => 40000,
            'monto_pendiente' => 0,
            'id_estado' => EstadosCodigo::PAGO_PAGADO,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->subDays(40)->format('Y-m-d'),
        ]);

        $this->assertCount(0, $this->actingAs($this->administrador())
            ->getJson('/panel/pagos/buscar?q=Aldia')
            ->json('inscripciones'));
    }

    // ------------------------------------------------------------------
    // 3. Una pausada no se renueva; una renovada no se reanuda
    // ------------------------------------------------------------------

    public function test_una_membresia_pausada_no_se_renueva(): void
    {
        $pausada = $this->pausada($this->socio());

        $this->renovar($pausada)->assertSessionHas('error', 'Esta membresía está pausada. Reanúdala antes de renovar.');

        $this->assertSame(1, Inscripcion::count());
        $this->assertFalse($this->puede($pausada)['renovar']);
        $this->assertTrue($this->puede($pausada)['reanudar']);
    }

    /**
     * Las que se renovaron en pausa antes del arreglo quedaron Vencidas con la
     * marca de pausa y los días guardados. «Reanudar» las revivía al lado de la
     * nueva: no se ofrece y el servidor lo rechaza.
     */
    public function test_una_pausada_que_ya_se_renovo_no_se_reanuda(): void
    {
        $socio = $this->socio();
        $vieja = $this->pausada($socio, ['id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA]);
        $nueva = $this->membresia($socio, [
            'id_inscripcion_anterior' => $vieja->id,
            'fecha_inicio' => now(),
            'fecha_vencimiento' => now()->addDays(30),
        ]);

        $this->assertFalse($vieja->fresh()->reanudar());
        $this->assertFalse($this->puede($vieja)['reanudar']);

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$vieja->uuid}/reanudar")
            ->assertStatus(422);

        $this->assertSame(EstadosCodigo::INSCRIPCION_VENCIDA, (int) $vieja->fresh()->id_estado);
        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)
            ->whereIn('id_estado', [EstadosCodigo::INSCRIPCION_ACTIVA, EstadosCodigo::INSCRIPCION_PAUSADA])
            ->count());
        $this->assertSame(EstadosCodigo::INSCRIPCION_ACTIVA, (int) $nueva->fresh()->id_estado);
    }

    /** Una pausada con sucesora tampoco, aunque siga marcada 101. */
    public function test_una_pausada_con_sucesora_no_se_reanuda_aunque_siga_en_pausa(): void
    {
        $socio = $this->socio();
        $vieja = $this->pausada($socio);
        $this->membresia($socio, ['id_inscripcion_anterior' => $vieja->id]);

        $this->assertNotNull($vieja->fresh()->porQueNoSePuedeReanudar());
        $this->assertFalse($vieja->fresh()->reanudar());
    }

    /** Al cerrar la anterior no queda rastro de pausa: Reanudar no tiene de dónde tirar. */
    public function test_renovar_limpia_la_pausa_que_arrastraba_la_anterior(): void
    {
        $socio = $this->socio();
        // Vencida con la marca puesta: lo que dejó el código de antes.
        $vieja = $this->pausada($socio, [
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'fecha_vencimiento' => now()->subDays(3),
        ]);

        $this->renovar($vieja)->assertSessionHasNoErrors()->assertSessionMissing('error');

        $vieja->refresh();
        $this->assertSame(EstadosCodigo::INSCRIPCION_VENCIDA, (int) $vieja->id_estado);
        $this->assertFalse((bool) $vieja->pausada);
        $this->assertNull($vieja->dias_restantes_al_pausar);
        $this->assertNull($vieja->fecha_pausa_fin);
    }

    // ------------------------------------------------------------------
    // 4. No se renueva una vieja teniendo otra vigente
    // ------------------------------------------------------------------

    public function test_no_se_renueva_una_vieja_si_el_socio_tiene_otra_vigente(): void
    {
        $socio = $this->socio();
        $vieja = $this->membresia($socio, [
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'fecha_inicio' => now()->subDays(200),
            'fecha_vencimiento' => now()->subDays(170),
        ]);
        $vigente = $this->membresia($socio);

        $this->renovar($vieja)->assertSessionHas('error');

        $this->assertSame(2, Inscripcion::count(), 'Se habría cobrado dos veces el mismo mes.');
        $this->assertFalse($this->puede($vieja)['renovar']);

        // La vigente sí: es la que toca renovar.
        $this->assertTrue($this->puede($vigente)['renovar']);
    }

    public function test_tampoco_si_la_otra_esta_pausada(): void
    {
        $socio = $this->socio();
        $vieja = $this->membresia($socio, [
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'fecha_vencimiento' => now()->subDays(40),
        ]);
        $this->pausada($socio);

        $this->renovar($vieja)->assertSessionHas('error');
        $this->assertSame(2, Inscripcion::count());
    }

    // ------------------------------------------------------------------
    // 5. Una cortesía de $0 queda pagada, no pendiente
    // ------------------------------------------------------------------

    public function test_una_membresia_de_cero_pesos_queda_pagada_tras_la_revision(): void
    {
        $socio = $this->socio();
        $cortesia = $this->membresia($socio, [
            'precio_final' => 0,
            'descuento_aplicado' => 40000,
        ]);
        // Uno como lo dejaba la revisión de antes: «Pendiente» sin deber nada.
        $pago = Pago::create([
            'id_inscripcion' => $cortesia->id,
            'id_cliente' => $socio->id,
            'monto_total' => 0,
            'monto_abonado' => 0,
            'monto_pendiente' => 0,
            'id_estado' => EstadosCodigo::PAGO_PENDIENTE,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $this->artisan('pagos:sincronizar-estados')->assertSuccessful();

        $this->assertSame(EstadosCodigo::PAGO_PAGADO, (int) $pago->fresh()->id_estado);

        // Y la corrida siguiente no lo vuelve a tocar.
        $this->assertSame(0, $cortesia->fresh()->recalcularSusPagos(false));
    }

    /** Lo que no es cortesía sigue igual: sin cobrar nada, pendiente. */
    public function test_sin_cobrar_nada_una_membresia_con_precio_sigue_pendiente(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio);
        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 40000,
            'monto_abonado' => 0,
            'monto_pendiente' => 40000,
            'id_estado' => EstadosCodigo::PAGO_PAGADO,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $inscripcion->recalcularSusPagos();

        $this->assertSame(EstadosCodigo::PAGO_PENDIENTE, (int) $pago->fresh()->id_estado);
    }
}
