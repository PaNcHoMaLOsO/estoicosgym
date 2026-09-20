<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Services\RegistroPagoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\CasoConCatalogos;

/**
 * Regresiones del registro de pagos.
 *
 * Las tres cosas que se comprueban aqui rompieron de verdad, y ninguna daba la
 * cara al leer el codigo: hacia falta ejecutarlo.
 */
class RegistroDePagosTest extends CasoConCatalogos
{
    private const ESTADO_PAGADO = 201;
    private const ESTADO_PARCIAL = 202;

    private function inscripcionDe(int $precio = 100000): Inscripcion
    {
        $cliente = Cliente::factory()->create(['activo' => true]);

        return Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_estado' => 100,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'fecha_inicio' => now()->subDays(5),
            'fecha_vencimiento' => now()->addMonths(3),
        ]);
    }

    private function datosDePago(Inscripcion $inscripcion, array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'abono',
            'monto_abonado' => 20000,
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    /**
     * La UI ofrece «Abono» y la columna `tipo_pago` de la tabla es un enum que
     * NO tiene ese valor: los suyos son completo, parcial, pendiente y mixto.
     * Guardar 'abono' reventaba con un error 1265 de MySQL y el pago parcial
     * estaba roto por completo.
     *
     * Sobre SQLite el enum no existe y esto no reventaria, pero la prueba mira
     * el valor GUARDADO, que es lo que de verdad importa: si alguien vuelve a
     * escribir 'abono', se ve aqui.
     */
    public function test_un_abono_se_guarda_como_parcial_y_no_como_abono(): void
    {
        $inscripcion = $this->inscripcionDe();

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', $this->datosDePago($inscripcion));

        $pago = Pago::latest('id')->first();

        $this->assertNotNull($pago, 'No se registró el pago.');
        $this->assertSame('parcial', $pago->tipo_pago);
        $this->assertSame(self::ESTADO_PARCIAL, (int) $pago->id_estado);
    }

    /**
     * Un pago mixto que cubre el total queda PAGADO, no parcial.
     *
     * «Mixto» dice que el dinero entro por dos vias, no que falte plata. Pagar
     * mitad en efectivo y mitad con tarjeta se marcaba como Parcial, y el
     * gimnasio creia que le debian lo que ya habia cobrado.
     */
    public function test_un_mixto_que_cubre_el_total_queda_pagado(): void
    {
        $this->assertSame(
            self::ESTADO_PAGADO,
            $this->estadoDeUnMixto(precio: 50000, abonado: 50000),
            'Un mixto que cubre el total no puede quedar como Parcial.'
        );
    }

    public function test_un_mixto_que_no_cubre_el_total_queda_parcial(): void
    {
        $this->assertSame(
            self::ESTADO_PARCIAL,
            $this->estadoDeUnMixto(precio: 50000, abonado: 20000)
        );
    }

    /**
     * Un mixto sin dinero no es un mixto: se rechaza. Antes quedaba «pendiente»,
     * pero para no cobrar nada ya existe «Nada todavía», y aceptar un reparto
     * vacío era justo lo que dejaba guardar la mitad de un pago sin avisar.
     */
    public function test_un_mixto_sin_dinero_encima_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        $this->estadoDeUnMixto(precio: 50000, abonado: 0);
    }

    /**
     * Dos envios identicos crean UN pago.
     *
     * La proteccion anterior preguntaba Cache::has() y solo marcaba el token al
     * terminar: con un doble clic las dos peticiones veian la via libre y las
     * dos cobraban. Ahora el turno se reserva de forma atomica.
     */
    public function test_el_mismo_envio_repetido_crea_un_solo_pago(): void
    {
        $inscripcion = $this->inscripcionDe();
        $usuario = $this->administrador();

        // MISMO token en los dos: es lo que manda un formulario al reenviarse.
        $datos = $this->datosDePago($inscripcion, ['form_submit_token' => 'token-repetido']);

        $this->actingAs($usuario)->post('/panel/pagos/registrar', $datos);
        $this->actingAs($usuario)->post('/panel/pagos/registrar', $datos);

        $this->assertSame(
            1,
            Pago::where('id_inscripcion', $inscripcion->id)->count(),
            'El envío repetido creó un pago duplicado.'
        );
    }

    /**
     * Un envio rechazado por validacion se puede corregir y reenviar.
     *
     * Es la otra cara de la moneda: si el turno se reservara ANTES de validar,
     * el formulario rechazado lo dejaria pillado y quien corrige el dato se
     * quedaria sin poder guardar.
     */
    public function test_tras_un_error_de_validacion_se_puede_reenviar(): void
    {
        $inscripcion = $this->inscripcionDe();
        $usuario = $this->administrador();

        // Por debajo del minimo de 1000 que exige el servicio de pagos.
        $this->actingAs($usuario)->post('/panel/pagos/registrar', $this->datosDePago($inscripcion, [
            'form_submit_token' => 'token-reintento',
            'monto_abonado' => 1,
        ]));

        $this->assertSame(0, Pago::where('id_inscripcion', $inscripcion->id)->count());

        // Mismo token, monto corregido: TIENE que guardar.
        $this->actingAs($usuario)->post('/panel/pagos/registrar', $this->datosDePago($inscripcion, [
            'form_submit_token' => 'token-reintento',
            'monto_abonado' => 20000,
        ]));

        $this->assertSame(
            1,
            Pago::where('id_inscripcion', $inscripcion->id)->count(),
            'Tras corregir el dato, el reenvío quedó bloqueado.'
        );
    }

    /**
     * Un cobro completo salda el total.
     *
     * Todos los cobros entran por aqui: el panel viejo de Blade, que tenia su
     * propia copia de las reglas, ya no existe. Si manana se corrige una regla,
     * se corrige en un solo lugar (RegistroPagoService).
     */
    public function test_el_panel_nuevo_registra_un_cobro_completo(): void
    {
        $inscripcion = $this->inscripcionDe(50000);

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', $this->datosDePago($inscripcion, [
                'tipo_pago' => 'completo',
            ]));

        $pago = Pago::latest('id')->first();

        $this->assertNotNull($pago, 'El panel no registró el cobro.');
        $this->assertSame(50000, (int) $pago->monto_abonado, 'Un cobro completo debe saldar el total.');
        $this->assertSame(0, (int) $pago->monto_pendiente);
        $this->assertSame(self::ESTADO_PAGADO, (int) $pago->id_estado);
    }

    /** Un mixto que suma el saldo exacto queda Pagado, tambien desde el panel. */
    public function test_el_panel_nuevo_registra_un_mixto_que_salda_la_deuda(): void
    {
        $inscripcion = $this->inscripcionDe(60000);
        $metodos = MetodoPago::take(2)->pluck('id');

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', [
                'form_submit_token' => uniqid('mix', true),
                'id_inscripcion' => $inscripcion->id,
                'tipo_pago' => 'mixto',
                'id_metodo_pago1' => $metodos[0],
                'id_metodo_pago2' => $metodos[1],
                'monto_metodo1' => 40000,
                'monto_metodo2' => 20000,
                'fecha_pago' => now()->format('Y-m-d'),
            ]);

        $pago = Pago::latest('id')->first();

        $this->assertNotNull($pago, 'No se registró el pago mixto.');
        $this->assertSame(self::ESTADO_PAGADO, (int) $pago->id_estado);
        $this->assertSame(0, (int) $pago->monto_pendiente);
    }

    /** Los dos montos de un mixto tienen que sumar el saldo, ni mas ni menos. */
    public function test_un_mixto_que_no_suma_el_saldo_se_rechaza(): void
    {
        $inscripcion = $this->inscripcionDe(60000);
        $metodos = MetodoPago::take(2)->pluck('id');

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', [
                'form_submit_token' => uniqid('mix', true),
                'id_inscripcion' => $inscripcion->id,
                'tipo_pago' => 'mixto',
                'id_metodo_pago1' => $metodos[0],
                'id_metodo_pago2' => $metodos[1],
                'monto_metodo1' => 1000,
                'monto_metodo2' => 1000,
                'fecha_pago' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('monto_metodo1');

        $this->assertSame(0, Pago::count());
    }

    /** No se cobra sobre una membresia cancelada: el dinero iria a la nada. */
    public function test_no_se_cobra_sobre_una_inscripcion_cancelada(): void
    {
        $inscripcion = $this->inscripcionDe();
        $inscripcion->update(['id_estado' => 103]);

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', $this->datosDePago($inscripcion))
            ->assertSessionHasErrors('id_inscripcion');

        $this->assertSame(0, Pago::count());
    }

    /** Ni a un socio dado de baja. */
    public function test_no_se_cobra_a_un_socio_inactivo(): void
    {
        $inscripcion = $this->inscripcionDe();
        $inscripcion->cliente->update(['activo' => false]);

        $this->actingAs($this->administrador())
            ->post('/panel/pagos/registrar', $this->datosDePago($inscripcion))
            ->assertSessionHasErrors('id_inscripcion');

        $this->assertSame(0, Pago::count());
    }

    /**
     * Dos cobros que se cruzan no pueden pasarse del precio.
     *
     * Las dos peticiones leen el saldo ANTES de que ninguna escriba —dos cajas a
     * la vez, o el mismo formulario abierto en dos pestañas, cada una con SU
     * token— y las dos lo daban por bueno: entre ambas entraba el doble de lo
     * que valía la membresía. El corte al duplicado no lo ve, porque mira el
     * mismo monto en la misma fecha, y el recálculo de la noche tampoco lo
     * arregla, porque los dos cobros son de verdad: el socio queda con plata a
     * favor que nadie le va a devolver.
     *
     * Se llama al servicio directo para poder cruzarlos: por HTTP las peticiones
     * van una detrás de otra y la segunda ya vería el cobro de la primera, que
     * es justo lo que aquí no puede pasar.
     */
    public function test_dos_cobros_que_se_cruzan_no_se_pasan_del_precio(): void
    {
        $inscripcion = $this->inscripcionDe(50000);
        $servicio = app(RegistroPagoService::class);

        $peticion = fn () => Request::create('/', 'POST', [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        // Las dos validan mientras la membresía todavía debe los 50.000.
        $primera = $servicio->validar($peticion());
        $segunda = $servicio->validar($peticion());

        $servicio->registrar($primera);

        try {
            $servicio->registrar($segunda);
            $this->fail('El segundo cobro entró: la membresía quedó cobrada dos veces.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('monto_abonado', $e->errors());
        }

        $this->assertSame(
            50000,
            (int) Pago::where('id_inscripcion', $inscripcion->id)->sum('monto_abonado'),
            'Entre los dos cobros se pasaron del precio de la membresía.'
        );
    }

    /**
     * Estado que el servicio le pone a un pago mixto.
     *
     * Se llama al metodo privado con reflexion en vez de dar el alta completa:
     * lo que se comprueba es la REGLA —cuanto entro frente a cuanto costaba—, y
     * montar un cliente con su inscripcion alrededor solo añadiria formas de
     * que la prueba falle por algo que no es lo que mira.
     */
    private function estadoDeUnMixto(int $precio, int $abonado): int
    {
        $servicio = app(\App\Services\RegistroClienteService::class);

        // El mixto llega como sus PARTES, cada una con su medio, no como un
        // monto suelto: aqui se reparte lo abonado en dos mitades.
        [$uno, $dos] = MetodoPago::orderBy('id')->take(2)->get()->all();
        $mitad = intdiv($abonado, 2);

        $peticion = \Illuminate\Http\Request::create('/', 'POST', [
            'tipo_pago' => 'mixto',
            'detalle_pagos_mixto' => json_encode($abonado > 0 ? [
                ['id_metodo_pago' => $uno->id, 'monto' => $mitad],
                ['id_metodo_pago' => $dos->id, 'monto' => $abonado - $mitad],
            ] : []),
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $metodo = new \ReflectionMethod($servicio, 'validarPago');
        $metodo->setAccessible(true);

        return (int) $metodo->invoke($servicio, $peticion, $precio)['estado_pago'];
    }
}
