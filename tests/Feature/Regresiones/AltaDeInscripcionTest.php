<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Regresiones del alta de inscripción.
 *
 * El de «no paga ahora» es el peor de todos los que aparecieron en esta
 * pantalla: no fallaba a veces, fallaba SIEMPRE, y encima dejaba basura en la
 * base. Nada de esto se ve leyendo el código; hay que ejecutarlo.
 */
class AltaDeInscripcionTest extends CasoConCatalogos
{
    /** Mensual: $40.000, y con convenio $25.000. */
    private const PLAN_MENSUAL = 4;

    private const ESTADO_ACTIVA = 100;
    private const ESTADO_PAGO_PENDIENTE = 200;
    private const ESTADO_PAGO_PAGADO = 201;
    private const ESTADO_PAGO_PARCIAL = 202;

    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true]);
    }

    /** @return array<string,mixed> */
    private function formulario(Cliente $cliente, array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_cliente' => $cliente->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'monto_abonado' => 40000,
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    private function inscribir(array $datos)
    {
        return $this->actingAs($this->administrador())
            ->post('/panel/inscripciones', $datos);
    }

    /**
     * EL BUG GORDO.
     *
     * `crearPagoPendiente` escribía `fecha_pago => null` sobre una columna que
     * es NOT NULL, así que elegir «no paga ahora» reventaba con un 500 SIEMPRE.
     * Y como la inscripción se creaba ANTES del pago y no había transacción, la
     * inscripción quedaba guardada sin ningún pago detrás: invisible para
     * «por cobrar» y para los informes de caja.
     *
     * Quien atendía veía el error, volvía a intentarlo, y entonces le saltaba
     * «este cliente ya tiene una inscripción Activa» sin haber creado ninguna.
     */
    public function test_inscribir_sin_pago_inicial_deja_la_deuda_registrada(): void
    {
        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'tipo_pago' => 'pendiente',
            'monto_abonado' => null,
            'id_metodo_pago' => null,
        ]));

        $respuesta->assertRedirect();
        $respuesta->assertSessionHasNoErrors();

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();

        // La deuda TIENE que existir como fila: sin ella el socio no aparece
        // en «por cobrar» y nadie se acuerda de cobrarle.
        $pago = Pago::where('id_inscripcion', $inscripcion->id)->firstOrFail();

        $this->assertSame(self::ESTADO_PAGO_PENDIENTE, (int) $pago->id_estado);
        $this->assertSame('pendiente', $pago->tipo_pago);
        $this->assertEquals(0, $pago->monto_abonado);
        $this->assertEquals(40000, $pago->monto_pendiente);
        $this->assertNotNull($pago->fecha_pago, 'fecha_pago es NOT NULL: si va vacía la fila entera se rechaza.');

        // Sin método: no ha pagado. Poner «Efectivo» por defecto metía en los
        // informes efectivo que nadie entregó.
        $this->assertNull($pago->id_metodo_pago);
    }

    /**
     * Una inscripción y su pago se guardan JUNTOS o no se guarda nada.
     *
     * Se fuerza el fallo por el único camino que queda: un método de pago que
     * no existe. Antes la inscripción sobrevivía al error del pago.
     */
    public function test_si_el_pago_falla_no_queda_una_inscripcion_huerfana(): void
    {
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'id_metodo_pago' => 99999,
        ]));

        $this->assertSame(
            0,
            Inscripcion::where('id_cliente', $socio->id)->count(),
            'Un pago rechazado no puede dejar la inscripción creada.'
        );
    }

    /**
     * `obtenerPrecioMembresia` terminaba en `?? 0`: un plan al que se le olvidó
     * cargar el precio inscribía GRATIS y sin decir nada. No se notaba hasta
     * cuadrar la caja a fin de mes.
     */
    public function test_un_plan_sin_precio_vigente_no_inscribe_gratis(): void
    {
        $sinPrecio = Membresia::create([
            'nombre' => 'Plan recién creado',
            'duracion_meses' => 1,
            'duracion_dias' => 0,
            'activo' => true,
        ]);

        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'id_membresia' => $sinPrecio->id,
        ]));

        $respuesta->assertSessionHasErrors('id_membresia');
        $this->assertSame(0, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /**
     * El formulario viejo mandaba `id_estado` y la validación aceptaba
     * cualquier código de la tabla: se podía crear una inscripción que nacía
     * «Cancelada» o «Vencida», que no significa nada. Ahora el servidor no lee
     * ese campo.
     */
    public function test_una_inscripcion_nueva_nace_activa_aunque_le_manden_otro_estado(): void
    {
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'id_estado' => 103, // Cancelada
        ]));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertSame(self::ESTADO_ACTIVA, (int) $inscripcion->id_estado);
    }

    /** Dos membresías vigentes a la vez para el mismo socio no tienen sentido. */
    public function test_no_se_inscribe_a_quien_ya_tiene_membresia_vigente(): void
    {
        $socio = $this->socio();

        Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_estado' => self::ESTADO_ACTIVA,
        ]);

        $respuesta = $this->inscribir($this->formulario($socio));

        $respuesta->assertSessionHasErrors('id_cliente');
        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /**
     * AL QUE VUELVE SE LE VENDE EL PLAN Y QUEDA ACTIVO, sin pasar por su ficha.
     *
     * Antes se rechazaba: había que reactivarlo primero. Con la persona
     * esperando en el mesón, lo más rápido era crearlo de nuevo, y así nacían
     * los duplicados. Casi todos los que vinieron de las planillas están de baja.
     */
    public function test_inscribir_a_uno_de_baja_lo_reactiva(): void
    {
        $socio = $this->socio(['activo' => false]);

        $respuesta = $this->inscribir($this->formulario($socio));

        $respuesta->assertSessionHasNoErrors();
        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
        $this->assertTrue((bool) $socio->fresh()->activo);
    }

    /** Lo que sigue sin poder volver es quien pidió borrar sus datos. */
    public function test_no_se_inscribe_a_quien_se_le_borraron_los_datos(): void
    {
        $socio = $this->socio(['activo' => false, 'datos_borrados_en' => now()]);

        $respuesta = $this->inscribir($this->formulario($socio));

        $respuesta->assertSessionHasErrors('id_cliente');
        $this->assertSame(0, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /**
     * Cobrar el total exacto y llamarlo «abono» dejaba un pago en estado
     * Parcial con saldo cero, que después salía en «por cobrar» debiendo nada.
     */
    public function test_un_abono_por_el_total_se_rechaza(): void
    {
        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'tipo_pago' => 'abono',
            'monto_abonado' => 40000, // el precio entero
        ]));

        $respuesta->assertSessionHasErrors('monto_abonado');
    }

    public function test_un_abono_parcial_deja_el_saldo_a_la_vista(): void
    {
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'tipo_pago' => 'abono',
            'monto_abonado' => 15000,
        ]));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();
        $pago = Pago::where('id_inscripcion', $inscripcion->id)->firstOrFail();

        $this->assertSame(self::ESTADO_PAGO_PARCIAL, (int) $pago->id_estado);
        // El enum de la columna no tiene 'abono': el suyo se llama 'parcial'.
        $this->assertSame('parcial', $pago->tipo_pago);
        $this->assertEquals(25000, $pago->monto_pendiente);
    }

    public function test_pagar_el_plan_completo_lo_deja_al_dia(): void
    {
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();
        $pago = Pago::where('id_inscripcion', $inscripcion->id)->firstOrFail();

        $this->assertSame(self::ESTADO_PAGO_PAGADO, (int) $pago->id_estado);
        $this->assertEquals(0, $pago->monto_pendiente);
        $this->assertEquals(40000, $inscripcion->precio_final);
    }

    /** El convenio rebaja el precio del plan, no lo cobra aparte. */
    public function test_el_convenio_rebaja_el_precio(): void
    {
        $convenio = Convenio::create([
            'nombre' => 'Empresa X',
            'tipo' => 'empresa',
            'activo' => true,
        ]);

        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'id_convenio' => $convenio->id,
            'monto_abonado' => 25000,
        ]));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();

        $this->assertEquals(40000, $inscripcion->precio_base);
        $this->assertEquals(15000, $inscripcion->descuento_aplicado);
        $this->assertEquals(25000, $inscripcion->precio_final);
    }

    public function test_el_descuento_no_puede_superar_el_precio(): void
    {
        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'descuento_aplicado' => 90000, // el plan vale 40.000
        ]));

        $respuesta->assertSessionHasErrors('descuento_aplicado');
        $this->assertSame(0, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /** No se puede cobrar más de lo que vale. */
    public function test_no_se_cobra_mas_que_el_precio_final(): void
    {
        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'monto_abonado' => 60000,
        ]));

        $respuesta->assertSessionHasErrors('monto_abonado');
    }

    /** Un reparto entre dos métodos son dos filas, una por método. */
    public function test_el_pago_mixto_deja_una_fila_por_metodo(): void
    {
        $metodos = MetodoPago::orderBy('id')->take(2)->get();
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'tipo_pago' => 'mixto',
            'monto_abonado' => null,
            'id_metodo_pago' => null,
            'detalle_pagos_mixto' => json_encode([
                ['id_metodo_pago' => $metodos[0]->id, 'monto' => 30000, 'metodo_nombre' => $metodos[0]->nombre],
                ['id_metodo_pago' => $metodos[1]->id, 'monto' => 10000, 'metodo_nombre' => $metodos[1]->nombre],
            ]),
        ]));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();
        $pagos = Pago::where('id_inscripcion', $inscripcion->id)->orderBy('id')->get();

        $this->assertCount(2, $pagos);
        $this->assertSame('mixto', $pagos[0]->tipo_pago);
        $this->assertEquals(40000, $pagos->sum('monto_abonado'));

        // Las dos filas suman el precio, así que la membresía queda pagada.
        $this->assertSame(self::ESTADO_PAGO_PAGADO, (int) $pagos[0]->id_estado);
    }

    public function test_el_pago_mixto_no_puede_pasarse_del_total(): void
    {
        $metodos = MetodoPago::orderBy('id')->take(2)->get();
        $socio = $this->socio();

        $respuesta = $this->inscribir($this->formulario($socio, [
            'tipo_pago' => 'mixto',
            'detalle_pagos_mixto' => json_encode([
                ['id_metodo_pago' => $metodos[0]->id, 'monto' => 30000],
                ['id_metodo_pago' => $metodos[1]->id, 'monto' => 30000],
            ]),
        ]));

        $respuesta->assertSessionHasErrors('detalle_pagos_mixto');
        $this->assertSame(0, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /**
     * El vencimiento es el ÚLTIMO día que sirve, no el día siguiente: un plan
     * de un mes que empieza el 1 vence el último día del mes, no el 1 del
     * siguiente, o se estarían regalando 24 horas en cada venta.
     */
    public function test_el_vencimiento_es_el_ultimo_dia_util(): void
    {
        $socio = $this->socio();

        $this->inscribir($this->formulario($socio, [
            'fecha_inicio' => '2026-03-01',
            'fecha_pago' => '2026-03-01',
        ]));

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();

        // Mensual está sembrado con duracion_dias = 30, que manda sobre los meses.
        $this->assertSame('2026-03-30', $inscripcion->fecha_vencimiento->format('Y-m-d'));
    }

    /**
     * Un doble clic no inscribe dos veces.
     *
     * Aquí quien lo impide NO es el turno anti-duplicado sino la regla de «este
     * socio ya tiene una membresía vigente», que se comprueba antes: en cuanto
     * la primera se guarda, la segunda no tiene por dónde pasar. El turno sigue
     * puesto por si acaso, y donde de verdad hace falta —cobrar, que sí admite
     * dos pagos seguidos al mismo socio— se comprueba en RegistroDePagosTest.
     */
    public function test_un_doble_clic_no_inscribe_dos_veces(): void
    {
        $socio = $this->socio();
        $datos = $this->formulario($socio);

        $this->inscribir($datos);
        $segundo = $this->inscribir($datos);

        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
        $segundo->assertSessionHasErrors('id_cliente');
    }
}
