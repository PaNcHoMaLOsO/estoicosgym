<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use Tests\CasoConCatalogos;

/**
 * Cobrar, renovar e inscribir desde la ficha del socio vuelven a esa ficha.
 *
 * Se hacen en una ventana sobre la ficha, con el socio delante. Terminar en la
 * pantalla del pago o de la membresía recién creada obligaba a buscar otra vez
 * a la misma persona para seguir atendiéndola.
 *
 * El destino va como uuid del socio y NO como dirección: una dirección venida
 * del navegador dejaría mandar a cualquier sitio a quien apriete Guardar.
 */
class VolverAlSocioTest extends CasoConCatalogos
{
    private function socio(): Cliente
    {
        return Cliente::factory()->create(['activo' => true]);
    }

    private function membresia(Cliente $cliente, int $precio = 40000): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => $precio,
            'precio_final' => $precio,
            'fecha_inicio' => today(),
            'fecha_vencimiento' => today()->addDays(30),
        ]);
    }

    public function test_cobrar_desde_la_ficha_vuelve_a_la_ficha(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio);

        $this->actingAs($this->administrador())->post('/panel/pagos/registrar', [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
            'volver' => (string) $socio->uuid,
        ])->assertRedirect("/panel/clientes/{$socio->uuid}");
    }

    public function test_sin_pedirlo_se_sigue_yendo_al_pago(): void
    {
        $inscripcion = $this->membresia($this->socio());

        $this->actingAs($this->administrador())->post('/panel/pagos/registrar', [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
        ])->assertRedirectContains('/panel/pagos/');
    }

    /** Un uuid que no es de nadie no manda a ninguna parte rara. */
    public function test_un_destino_inventado_se_ignora(): void
    {
        $inscripcion = $this->membresia($this->socio());

        $this->actingAs($this->administrador())->post('/panel/pagos/registrar', [
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
            'volver' => 'https://otro-sitio.cl/panel',
        ])->assertRedirectContains('/panel/pagos/');
    }

    public function test_renovar_desde_la_ficha_vuelve_a_la_ficha(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio);

        $this->actingAs($this->administrador())
            ->post("/panel/inscripciones/{$inscripcion->uuid}/renovar", [
                'id_membresia' => 4,
                'fecha_inicio' => today()->addDays(31)->format('Y-m-d'),
                'tipo_pago' => 'pendiente',
                'volver' => (string) $socio->uuid,
            ])
            ->assertRedirect("/panel/clientes/{$socio->uuid}");
    }

    /** La pantalla recibe a dónde volver, que es lo que manda de vuelta al guardar. */
    public function test_la_pantalla_sabe_a_donde_volver(): void
    {
        $socio = $this->socio();
        $inscripcion = $this->membresia($socio);

        $props = $this->actingAs($this->administrador())
            ->get("/panel/pagos/cobrar?inscripcion={$inscripcion->uuid}&volver={$socio->uuid}")
            ->viewData('page')['props'];

        $this->assertSame((string) $socio->uuid, $props['volverA']);
    }
}
