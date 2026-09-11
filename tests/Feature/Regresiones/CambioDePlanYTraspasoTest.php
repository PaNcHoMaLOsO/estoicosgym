<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\HistorialTraspaso;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use Tests\CasoConCatalogos;

/**
 * Las dos operaciones que MUEVEN dinero entre membresías: cambiar de plan y
 * traspasar a otra persona.
 *
 * Son las más peligrosas del sistema porque el error no se ve al hacerlas: se
 * ve semanas después, cuando alguien aparece debiendo lo que ya pagó o cuando
 * los números de un socio no cuadran con lo que recuerda haber pagado.
 */
class CambioDePlanYTraspasoTest extends CasoConCatalogos
{
    /**
     * Los planes con precio vigente, del más barato al más caro.
     *
     * Se buscan en el catálogo en vez de escribir ids a mano: si el seeder
     * cambia sus precios, la prueba sigue midiendo lo mismo.
     *
     * @return list<array{0:Membresia,1:int}>
     */
    private function planesConPrecio(): array
    {
        return Membresia::where('activo', true)
            ->with(['precios' => fn ($q) => $q->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now())
                ->orderByDesc('fecha_vigencia_desde')])
            ->get()
            ->filter(fn (Membresia $m) => $m->precios->isNotEmpty())
            ->map(fn (Membresia $m) => [$m, (int) $m->precios->first()->precio_normal])
            ->sortBy(fn (array $p) => $p[1])
            ->values()
            ->all();
    }

    private function membresiaPagada(Cliente $socio, Membresia $plan, int $precio, int $abonado): Inscripcion
    {
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => $plan->id,
            'id_estado' => 100,
            'precio_base' => $precio,
            'descuento_aplicado' => 0,
            'precio_final' => $precio,
            'fecha_inicio' => now()->subDays(5),
            'fecha_vencimiento' => now()->addDays(25),
            'pausada' => false,
        ]);

        if ($abonado > 0) {
            Pago::factory()->create([
                'id_cliente' => $socio->id,
                'id_inscripcion' => $inscripcion->id,
                'monto_total' => $precio,
                'monto_abonado' => $abonado,
                'monto_pendiente' => max(0, $precio - $abonado),
                'id_estado' => $abonado >= $precio ? 201 : 202,
            ]);
        }

        return $inscripcion;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Cambio de plan
    // ─────────────────────────────────────────────────────────────────────

    /**
     * EL QUE IMPORTA: después de subir de plan con crédito, no debe nada.
     *
     * Pagó el plan barato entero. Sube al caro, se le reconoce lo pagado y paga
     * la diferencia. En total pagó exactamente el plan caro: la ficha tiene que
     * decir que no debe nada, no que debe otra vez lo que ya pagó.
     */
    public function test_subir_de_plan_con_credito_no_deja_una_deuda_falsa(): void
    {
        [[$barato, $precioBarato], [$caro, $precioCaro]] = [
            $this->planesConPrecio()[0],
            collect($this->planesConPrecio())->last(),
        ];
        $this->assertGreaterThan($precioBarato, $precioCaro, 'El seeder necesita dos planes con precios distintos.');

        $socio = Cliente::factory()->create(['activo' => true]);
        $vieja = $this->membresiaPagada($socio, $barato, $precioBarato, $precioBarato);

        $diferencia = $precioCaro - $precioBarato;

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$vieja->uuid}/cambiar-plan", [
                'id_membresia_actual' => $barato->id,
                'id_membresia_nueva' => $caro->id,
                'id_metodo_pago' => 1,
                'aplicar_credito' => true,
                'tipo_pago' => 'completo',
                'monto_abonado' => $diferencia,
            ])
            ->assertOk();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->firstOrFail();

        // Lo que de verdad pagó, en las dos membresías juntas.
        $pagadoEnTotal = (int) Pago::where('id_cliente', $socio->id)->sum('monto_abonado');
        $this->assertSame($precioCaro, $pagadoEnTotal, 'Pagó el plan caro entero, ni un peso más ni menos.');

        $this->assertSame(
            0,
            (int) $nueva->obtenerEstadoPago()['pendiente'],
            'La membresía nueva dice que se debe dinero que ya se pagó.'
        );

        // Y lo mismo en la pantalla, que es donde lo ve quien cobra.
        $ficha = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertSame(0, (int) $ficha['resumen']['debe'], 'La ficha manda a cobrarle otra vez.');
    }

    /** El plan más barato y el más caro, con sus precios. */
    private function extremos(): array
    {
        $planes = $this->planesConPrecio();
        [$barato, $precioBarato] = $planes[0];
        [$caro, $precioCaro] = end($planes);

        $this->assertGreaterThan($precioBarato, $precioCaro, 'El seeder necesita dos planes con precios distintos.');

        return [$barato, $precioBarato, $caro, $precioCaro];
    }

    private function subirDePlan(Inscripcion $vieja, Membresia $nuevo, array $extra)
    {
        return $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$vieja->uuid}/cambiar-plan", $extra + [
                'id_membresia_actual' => $vieja->id_membresia,
                'id_membresia_nueva' => $nuevo->id,
                'id_metodo_pago' => 1,
            ]);
    }

    /**
     * Con deuda: se cobra lo que muestra la pantalla, y en total paga lo mismo
     * que quien pagó el plan viejo entero antes de subir.
     *
     * La pantalla del cambio enseña «precio nuevo menos lo pagado». El servidor
     * le sumaba ENCIMA la deuda del plan viejo, que ya estaba dentro de esa
     * cuenta —el crédito solo cubre lo pagado—: se cobraba dos veces, y el
     * pago quedaba «parcial» por una cifra que nadie vio.
     */
    public function test_con_deuda_se_cobra_lo_que_muestra_la_pantalla(): void
    {
        [$barato, $precioBarato, $caro, $precioCaro] = $this->extremos();

        $socio = Cliente::factory()->create(['activo' => true]);
        $pagadoDelViejo = intdiv($precioBarato, 2);
        $vieja = $this->membresiaPagada($socio, $barato, $precioBarato, $pagadoDelViejo);

        // Exactamente lo que enseña la pantalla del cambio.
        $loQueMuestra = $precioCaro - $pagadoDelViejo;

        $this->subirDePlan($vieja, $caro, [
            'aplicar_credito' => true,
            'ignorar_deuda' => true,
            'tipo_pago' => 'completo',
            'monto_abonado' => $loQueMuestra,
        ])->assertOk();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->firstOrFail();

        $this->assertSame(0, (int) $nueva->obtenerEstadoPago()['pendiente']);

        // Ningún pago queda «debiendo» una cifra fantasma que sume en por cobrar.
        $this->assertSame(0, (int) Pago::where('id_inscripcion', $nueva->id)->sum('monto_pendiente'));

        // Y en total, lo mismo que si hubiera saldado el viejo antes de subir.
        $this->assertSame($precioCaro, (int) Pago::where('id_cliente', $socio->id)->sum('monto_abonado'));
    }

    /** Si no paga nada al subir, queda debiendo la diferencia: ni más ni menos. */
    public function test_subir_sin_pagar_ahora_deja_debiendo_solo_la_diferencia(): void
    {
        [$barato, $precioBarato, $caro, $precioCaro] = $this->extremos();

        $socio = Cliente::factory()->create(['activo' => true]);
        $vieja = $this->membresiaPagada($socio, $barato, $precioBarato, $precioBarato);

        $this->subirDePlan($vieja, $caro, [
            'aplicar_credito' => true,
            'monto_abonado' => 0,
        ])->assertOk();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->firstOrFail();
        $diferencia = $precioCaro - $precioBarato;

        $this->assertSame($diferencia, (int) $nueva->obtenerEstadoPago()['pendiente']);

        $ficha = $this->actingAs($this->administrador())
            ->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertSame($diferencia, (int) $ficha['resumen']['debe']);
    }

    /**
     * Base menos descuento tiene que dar el final.
     *
     * Es lo que vuelve a calcular la pantalla de corrección. Si no cuadrara,
     * corregir cualquier otra cosa de la membresía nueva —una fecha— haría
     * reaparecer la deuda.
     */
    public function test_el_precio_de_la_membresia_nueva_cuadra(): void
    {
        [$barato, $precioBarato, $caro] = $this->extremos();

        $socio = Cliente::factory()->create(['activo' => true]);
        $vieja = $this->membresiaPagada($socio, $barato, $precioBarato, $precioBarato);

        $this->subirDePlan($vieja, $caro, ['aplicar_credito' => true, 'monto_abonado' => 0])->assertOk();

        $nueva = Inscripcion::where('id_inscripcion_anterior', $vieja->id)->firstOrFail();

        $this->assertSame(
            (int) $nueva->precio_base - (int) $nueva->descuento_aplicado,
            (int) $nueva->precio_final
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Traspaso
    // ─────────────────────────────────────────────────────────────────────

    /** Lo básico: la membresía y sus pagos cambian de dueño, y queda escrito. */
    public function test_el_traspaso_mueve_la_membresia_y_sus_pagos(): void
    {
        [$plan, $precio] = $this->planesConPrecio()[0];

        $origen = Cliente::factory()->create(['activo' => true]);
        $destino = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaPagada($origen, $plan, $precio, $precio);

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/traspasar", [
                'id_cliente_destino' => $destino->id,
                'motivo_traspaso' => 'Se muda de ciudad y se la regala a su hermana',
            ])
            ->assertOk();

        $this->assertSame($destino->id, $inscripcion->refresh()->id_cliente);
        $this->assertSame(0, Pago::where('id_cliente', $origen->id)->count());
        $this->assertSame(1, Pago::where('id_cliente', $destino->id)->count());
        $this->assertSame(1, HistorialTraspaso::where('cliente_origen_id', $origen->id)->count());
    }

    /**
     * Con deuda y sin marcar la casilla, NO se traspasa.
     *
     * Quien recibe una membresía tiene que saber que viene con deuda antes de
     * aceptarla, no enterarse cuando se la cobran.
     */
    public function test_con_deuda_y_sin_casilla_no_se_traspasa(): void
    {
        [$plan, $precio] = $this->planesConPrecio()[0];

        $origen = Cliente::factory()->create(['activo' => true]);
        $destino = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaPagada($origen, $plan, $precio, intdiv($precio, 2));

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/traspasar", [
                'id_cliente_destino' => $destino->id,
                'motivo_traspaso' => 'Cambio de titular',
            ])
            ->assertStatus(422);

        $this->assertSame($origen->id, $inscripcion->refresh()->id_cliente);
    }

    /**
     * Con la casilla marcada se traspasa, y el aviso DICE que la deuda se fue
     * con la membresía.
     *
     * Es el único momento en que quien está en el mesón puede decírselo al
     * nuevo titular. Si el aviso calla, el nuevo titular se entera cuando se
     * la cobran.
     */
    public function test_el_aviso_dice_que_la_deuda_se_fue_con_la_membresia(): void
    {
        [$plan, $precio] = $this->planesConPrecio()[0];

        $origen = Cliente::factory()->create(['activo' => true]);
        $destino = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaPagada($origen, $plan, $precio, intdiv($precio, 2));

        $respuesta = $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/traspasar", [
                'id_cliente_destino' => $destino->id,
                'motivo_traspaso' => 'Cambio de titular',
                'ignorar_deuda' => true,
            ])
            ->assertOk();

        $this->assertStringContainsString('deuda', $respuesta->json('message'));
    }

    /**
     * Un correo pendiente sobre esa membresía no puede irle al titular viejo.
     *
     * Estaba escrito con su nombre y a su correo, sobre una membresía que ya no
     * es suya. Si sale igual, le avisa de un vencimiento que no le toca, y al
     * titular nuevo —el que sí tiene que renovar— no le llega nada con su
     * nombre.
     */
    public function test_un_correo_pendiente_no_le_llega_al_titular_viejo(): void
    {
        [$plan, $precio] = $this->planesConPrecio()[0];

        $origen = Cliente::factory()->create(['activo' => true, 'email' => 'origen@progym.cl']);
        $destino = Cliente::factory()->create(['activo' => true, 'email' => 'destino@progym.cl']);
        $inscripcion = $this->membresiaPagada($origen, $plan, $precio, $precio);

        // La base de pruebas no trae plantillas sembradas: se crea la del aviso.
        $tipo = TipoNotificacion::firstOrCreate(
            ['codigo' => 'membresia_por_vencer'],
            [
                'nombre' => 'Membresía por vencer',
                'descripcion' => 'Aviso de vencimiento',
                'asunto_email' => 'Tu membresía vence pronto',
                'plantilla_email' => '<p>Tu membresía vence pronto</p>',
                'dias_anticipacion' => 7,
                'activo' => true,
                'enviar_email' => true,
                'es_manual' => false,
            ]
        );

        $pendiente = Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => $origen->id,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => 'origen@progym.cl',
            'asunto' => 'Tu membresía vence pronto',
            'contenido' => 'Hola, tu membresía vence en 7 días.',
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/traspasar", [
                'id_cliente_destino' => $destino->id,
                'motivo_traspaso' => 'Cambio de titular',
            ])
            ->assertOk();

        $pendiente->refresh();

        $this->assertFalse(
            $pendiente->id_estado === Notificacion::ESTADO_PENDIENTE
                && $pendiente->email_destino === 'origen@progym.cl',
            'El aviso sobre la membresía traspasada sigue esperando para salir hacia el titular viejo.'
        );
    }
}
