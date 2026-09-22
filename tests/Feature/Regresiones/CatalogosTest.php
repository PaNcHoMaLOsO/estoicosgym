<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use App\Models\Pago;
use App\Models\PrecioMembresia;
use Tests\CasoConCatalogos;

/**
 * Alta y edición de los cuatro catálogos desde el panel.
 *
 * Lo que más importa aquí es el precio de un plan: no se pisa, se cierra el
 * que estaba y se abre otro. Si se sobrescribiera, una subida de precio
 * reescribiría hacia atrás lo que pagó cada socio.
 */
class CatalogosTest extends CasoConCatalogos
{
    private function comoAdmin()
    {
        return $this->actingAs($this->administrador());
    }

    /** @return array<string,mixed> */
    private function plan(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Quincenal',
            'descripcion' => 'Dos semanas',
            'duracion_meses' => 0,
            'duracion_dias' => 15,
            'max_pausas' => 1,
            'precio' => 22000,
            'activo' => true,
        ], $extra);
    }

    public function test_se_crea_un_plan_con_su_precio(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan())
            ->assertSessionHasNoErrors();

        $plan = Membresia::where('nombre', 'Quincenal')->firstOrFail();

        $this->assertSame(15, (int) $plan->duracion_dias);

        $precio = $plan->precios()->where('activo', true)->firstOrFail();
        $this->assertEquals(22000, $precio->precio_normal);
    }

    /**
     * Un plan sin precio no se puede vender: el alta de inscripción lo rechaza.
     * Crearlo a medias solo sirve para que el formulario falle más tarde.
     */
    public function test_no_se_crea_un_plan_sin_precio(): void
    {
        $datos = $this->plan();
        unset($datos['precio']);

        $this->comoAdmin()->post('/panel/membresias', $datos)
            ->assertSessionHasErrors('precio');

        $this->assertSame(0, Membresia::where('nombre', 'Quincenal')->count());
    }

    /** Un plan que dura cero vence el mismo día en que se compra. */
    public function test_no_se_crea_un_plan_que_no_dura_nada(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan([
            'duracion_meses' => 0,
            'duracion_dias' => 0,
        ]))->assertSessionHasErrors('duracion_dias');
    }

    /** El precio de convenio es una REBAJA: por encima del normal no lo es. */
    public function test_el_precio_con_convenio_no_puede_superar_al_normal(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan([
            'precio' => 20000,
            'precio_convenio' => 30000,
        ]))->assertSessionHasErrors('precio_convenio');
    }

    /**
     * EL QUE IMPORTA.
     *
     * Cambiar el precio de un plan NO puede pisar el anterior: las
     * inscripciones viejas apuntan a ese precio, y sobrescribirlo reescribiría
     * hacia atrás lo que se cobró de verdad.
     */
    public function test_subir_el_precio_no_reescribe_el_anterior(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan());
        $plan = Membresia::where('nombre', 'Quincenal')->firstOrFail();
        $viejo = $plan->precios()->where('activo', true)->firstOrFail();

        $this->comoAdmin()->put("/panel/membresias/{$plan->uuid}", $this->plan(['precio' => 30000]))
            ->assertSessionHasNoErrors();

        // El de antes sigue ahí, con su cifra intacta, solo que cerrado.
        $viejo->refresh();
        $this->assertEquals(22000, $viejo->precio_normal);
        $this->assertFalse((bool) $viejo->activo);
        $this->assertNotNull($viejo->fecha_vigencia_hasta);

        $this->assertSame(2, $plan->precios()->count());
        $this->assertEquals(30000, $plan->precios()->where('activo', true)->firstOrFail()->precio_normal);
    }

    /**
     * El tramo cerrado no puede terminar ANTES de empezar: si el precio se
     * corrige el mismo día en que se creó y se cerrara «ayer», quedaría del
     * revés.
     */
    public function test_el_tramo_de_precio_cerrado_no_queda_del_reves(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan());
        $plan = Membresia::where('nombre', 'Quincenal')->firstOrFail();

        $this->comoAdmin()->put("/panel/membresias/{$plan->uuid}", $this->plan(['precio' => 25000]));

        $cerrado = $plan->precios()->where('activo', false)->firstOrFail();

        $this->assertTrue(
            $cerrado->fecha_vigencia_hasta >= $cerrado->fecha_vigencia_desde,
            'Un tramo de precio no puede terminar antes de empezar.'
        );
    }

    /** Guardar sin tocar el precio no abre un tramo nuevo cada vez. */
    public function test_guardar_con_el_mismo_precio_no_duplica_el_tramo(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan());
        $plan = Membresia::where('nombre', 'Quincenal')->firstOrFail();

        $this->comoAdmin()->put("/panel/membresias/{$plan->uuid}", $this->plan(['nombre' => 'Quincenal Plus']));

        $this->assertSame(1, $plan->precios()->count());
    }

    public function test_no_se_repiten_los_nombres_de_plan(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan());

        $this->comoAdmin()->post('/panel/membresias', $this->plan())
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Membresia::where('nombre', 'Quincenal')->count());
    }

    public function test_se_crea_y_se_edita_un_metodo_de_pago(): void
    {
        $this->comoAdmin()->post('/panel/metodos-pago', [
            'nombre' => 'Webpay',
            'descripcion' => 'Pago en línea',
            'requiere_comprobante' => true,
        ])->assertSessionHasNoErrors();

        $metodo = MetodoPago::where('nombre', 'Webpay')->firstOrFail();
        $this->assertTrue((bool) $metodo->requiere_comprobante);

        $this->comoAdmin()->put("/panel/metodos-pago/{$metodo->id}", [
            'nombre' => 'Webpay Plus',
            'requiere_comprobante' => false,
        ])->assertSessionHasNoErrors();

        $metodo->refresh();
        $this->assertSame('Webpay Plus', $metodo->nombre);
        $this->assertFalse((bool) $metodo->requiere_comprobante);
    }

    public function test_se_crea_un_motivo_de_descuento(): void
    {
        $this->comoAdmin()->post('/panel/motivos-descuento', [
            'nombre' => 'Hermano de socio',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('motivos_descuento', [
            'nombre' => 'Hermano de socio',
            'activo' => true,
        ]);
    }

    public function test_se_crea_un_convenio(): void
    {
        $this->comoAdmin()->post('/panel/convenios', [
            'nombre' => 'Empresa Z',
            'tipo' => 'empresa',
            'descuento_porcentaje' => 15,
            'contacto_email' => 'rrhh@empresaz.cl',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('convenios', ['nombre' => 'Empresa Z', 'tipo' => 'empresa']);
    }

    public function test_un_convenio_necesita_un_tipo_valido(): void
    {
        $this->comoAdmin()->post('/panel/convenios', [
            'nombre' => 'Empresa Z',
            'tipo' => 'inventado',
        ])->assertSessionHasErrors('tipo');
    }

    /**
     * Desactivar deja de ofrecerlo SIN tocar lo que ya lo usaba: un método
     * borrado dejaría los pagos viejos sin decir con qué se cobraron, y esas
     * cifras desaparecerían de los informes de años anteriores.
     */
    public function test_desactivar_un_metodo_no_toca_los_pagos_que_lo_usaron(): void
    {
        $metodo = MetodoPago::first();
        $cliente = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create(['id_cliente' => $cliente->id, 'id_estado' => 100]);

        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => 10000,
            'monto_abonado' => 10000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => $metodo->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $this->comoAdmin()
            ->patch("/panel/catalogos/metodos-pago/{$metodo->id}/alternar")
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $metodo->fresh()->activo);
        $this->assertSame($metodo->id, (int) $pago->fresh()->id_metodo_pago);
    }

    public function test_alternar_vuelve_a_activarlo(): void
    {
        $motivo = MotivoDescuento::create(['nombre' => 'Prueba', 'activo' => true]);

        $this->comoAdmin()->patch("/panel/catalogos/motivos-descuento/{$motivo->id}/alternar");
        $this->assertFalse((bool) $motivo->fresh()->activo);

        $this->comoAdmin()->patch("/panel/catalogos/motivos-descuento/{$motivo->id}/alternar");
        $this->assertTrue((bool) $motivo->fresh()->activo);
    }

    /**
     * Seis modelos declaraban `$incrementing = false` sobre una clave que en la
     * base SÍ es AUTO_INCREMENT. Eso le dice a Eloquent que no recoja el id
     * después de insertar: la fila se escribía bien, pero el objeto que devolvía
     * create() salía sin id, y sobre esa instancia ->fresh(), ->refresh() y
     * cualquier relación no encontraban nada.
     */
    public function test_crear_una_fila_devuelve_un_modelo_con_su_id(): void
    {
        $creados = [
            MotivoDescuento::create(['nombre' => 'Con id']),
            MetodoPago::create(['nombre' => 'Con id']),
            PrecioMembresia::create([
                'id_membresia' => 4,
                'precio_normal' => 1000,
                'fecha_vigencia_desde' => now()->format('Y-m-d'),
                'activo' => true,
            ]),
        ];

        foreach ($creados as $fila) {
            $quien = class_basename($fila);

            $this->assertNotNull($fila->id, "{$quien}::create() devolvió un modelo sin id.");
            $this->assertNotNull($fila->fresh(), "Un {$quien} recién creado no se encuentra a sí mismo.");
        }
    }

    public function test_alternar_un_catalogo_que_no_existe_da_404(): void
    {
        $this->comoAdmin()->patch('/panel/catalogos/inventado/1/alternar')->assertNotFound();
    }

    /** Un plan recién creado se puede vender de inmediato. */
    public function test_un_plan_recien_creado_ya_se_puede_vender(): void
    {
        $this->comoAdmin()->post('/panel/membresias', $this->plan());
        $plan = Membresia::where('nombre', 'Quincenal')->firstOrFail();

        $socio = Cliente::factory()->create(['activo' => true]);

        $this->comoAdmin()->post('/panel/inscripciones', [
            'form_submit_token' => uniqid('t', true),
            'id_cliente' => $socio->id,
            'id_membresia' => $plan->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'monto_abonado' => 22000,
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $inscripcion = Inscripcion::where('id_cliente', $socio->id)->firstOrFail();
        $this->assertEquals(22000, $inscripcion->precio_final);

        // La inscripción apunta al tramo de precio con el que se vendió.
        $this->assertSame(
            (int) PrecioMembresia::where('id_membresia', $plan->id)->where('activo', true)->value('id'),
            (int) $inscripcion->id_precio_acordado
        );
    }
    // ---------- Días de regalo ----------

    /**
     * LOS DÍAS QUE EL GIMNASIO DA DE MÁS.
     *
     * Al anual se le regalaban unos días por pagar todo junto, y eso se
     * arreglaba escribiendo el vencimiento a mano: no quedaba dicho en ninguna
     * parte, así que dependía de quién atendiera. Ahora es del plan, y se suma
     * solo en cada venta.
     */
    public function test_los_dias_de_regalo_se_suman_al_vencimiento(): void
    {
        $plan = \App\Models\Membresia::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'nombre' => 'Anual con regalo',
            'duracion_meses' => 12,
            'duracion_dias' => 0,
            'dias_regalo' => 5,
            'max_pausas' => 1,
            'activo' => true,
        ]);

        $inicio = \Illuminate\Support\Carbon::create(2026, 1, 1);

        // Un año —hasta el 31 de diciembre, que es el último día que sirve— y
        // encima los cinco de regalo.
        $this->assertSame('2027-01-05', $plan->vencimientoDesde($inicio)->format('Y-m-d'));
    }

    /** Sin regalo, el vencimiento es el de siempre. */
    public function test_sin_dias_de_regalo_nada_cambia(): void
    {
        $plan = \App\Models\Membresia::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'nombre' => 'Mensual de 31',
            'duracion_meses' => 1,
            'duracion_dias' => 31,
            'max_pausas' => 1,
            'activo' => true,
        ]);

        // 31 días contando el primero: del 22 de septiembre al 22 de octubre.
        $this->assertSame(
            '2026-10-22',
            $plan->vencimientoDesde(\Illuminate\Support\Carbon::create(2026, 9, 22))->format('Y-m-d')
        );
        $this->assertSame(0, (int) $plan->dias_regalo);
    }

    /** No son la duración: sesenta días de regalo ya serían otro plan. */
    public function test_el_regalo_tiene_tope(): void
    {
        $respuesta = $this->actingAs($this->administrador())->post('/panel/membresias', [
            'nombre' => 'Plan generoso',
            'duracion_meses' => 1,
            'duracion_dias' => 0,
            'dias_regalo' => 200,
            'max_pausas' => 1,
            'precio' => 40000,
        ]);

        $respuesta->assertSessionHasErrors('dias_regalo');
    }
}
