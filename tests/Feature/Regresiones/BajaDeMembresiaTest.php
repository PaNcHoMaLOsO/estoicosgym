<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Borrar una membresía vendida.
 *
 * Es para la que NO DEBERÍA EXISTIR: la apuntada dos veces, la del socio
 * equivocado recién creada. No es cancelar —eso es un estado, y el socio la
 * tuvo— ni corregirla, que tiene su pantalla.
 *
 * Lo que se vigila aquí es que no deje dinero suelto: si se cobró algo, la
 * inscripción se iría a la papelera y el pago se quedaría fuera, apuntando a
 * una membresía que ya no se lista. El dinero seguiría contando en los
 * informes y nadie sabría de qué era.
 */
class BajaDeMembresiaTest extends CasoConCatalogos
{
    private function membresiaDe(Cliente $socio, int $precio = 30000): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'fecha_vencimiento' => now()->addMonth(),
        ]);
    }

    private function borrar(Inscripcion $inscripcion)
    {
        return $this->actingAs($this->administrador())
            ->delete("/panel/inscripciones/{$inscripcion->uuid}");
    }

    public function test_una_membresia_sin_cobrar_se_va_a_la_papelera(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        $this->borrar($inscripcion)->assertSessionHasNoErrors();

        $this->assertSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }

    /** Y desde la papelera se puede recuperar, que es de lo que se trata. */
    public function test_se_puede_recuperar_desde_la_papelera(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        $this->borrar($inscripcion);

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/inscripciones/{$inscripcion->id}/restaurar")
            ->assertSessionHasNoErrors();

        $this->assertNotSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }

    /**
     * EL QUE IMPORTA: con dinero cobrado no se borra.
     *
     * El pago se quedaría apuntando a una membresía que ya no se lista, y
     * seguiría sumando en los informes sin que nadie pueda decir de qué era.
     */
    public function test_con_un_pago_encima_no_se_borra(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 30000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
        ]);

        $this->borrar($inscripcion)->assertSessionHas('error');

        $this->assertNotSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }

    /** Un abono parcial tampoco: es dinero igual. */
    public function test_un_abono_parcial_tampoco_deja_borrarla(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 30000,
            'monto_abonado' => 5000,
            'monto_pendiente' => 25000,
            'id_estado' => 202,
        ]);

        $this->borrar($inscripcion)->assertSessionHas('error');

        $this->assertNotSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }

    /**
     * Un pago pendiente SIN abonar sí deja borrarla.
     *
     * No se recibió nada: es la membresía que se apuntó y nunca se cobró, que
     * es justo el caso para el que existe esto.
     */
    public function test_un_pago_pendiente_sin_abonar_si_deja_borrarla(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 30000,
            'monto_abonado' => 0,
            'monto_pendiente' => 30000,
            'id_estado' => 200,
        ]);

        $this->borrar($inscripcion)->assertSessionHasNoErrors();

        $this->assertSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }

    /** La ficha no ofrece el botón si hay dinero: un botón que falla enseña a desconfiar. */
    public function test_la_ficha_no_ofrece_borrar_si_hay_dinero(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        $puede = fn () => $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->viewData('page')['props']['puede'];

        $this->assertTrue($puede()['borrar']);

        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 30000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
        ]);

        $this->assertFalse($puede()['borrar']);
    }

    /** Recepción no borra membresías vendidas: eso es de quien lleva las cuentas. */
    public function test_recepcion_no_puede_borrarla(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = $this->membresiaDe($socio);

        $this->actingAs($this->recepcionista())
            ->delete("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('inscripciones', ['id' => $inscripcion->id]);
    }
}
