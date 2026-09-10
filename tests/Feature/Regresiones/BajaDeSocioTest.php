<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * Dar de baja a un socio, reactivarlo y mandarlo a la papelera.
 *
 * Las dos reglas que se comprueban aquí son de dinero y de acceso: una
 * membresía viva le deja entrar al gimnasio, y un saldo sin cobrar
 * desaparecería del listado de «por cobrar» en cuanto el socio dejara de estar
 * activo. Por eso ninguna de las dos permite darlo de baja.
 */
class BajaDeSocioTest extends CasoConCatalogos
{
    private function socio(array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + ['activo' => true]);
    }

    private function conMembresia(Cliente $socio, int $estado): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
        ]);
    }

    private function conDeuda(Cliente $socio): Pago
    {
        $inscripcion = $this->conMembresia($socio, EstadosCodigo::INSCRIPCION_VENCIDA);

        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 40000,
            'monto_abonado' => 10000,
            'monto_pendiente' => 30000,
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    private function darDeBaja(Cliente $socio)
    {
        return $this->actingAs($this->administrador())
            ->patch("/panel/clientes/{$socio->uuid}/desactivar");
    }

    public function test_un_socio_sin_nada_pendiente_se_da_de_baja(): void
    {
        $socio = $this->socio();

        $this->darDeBaja($socio)->assertSessionHasNoErrors();

        $this->assertFalse((bool) $socio->fresh()->activo);
    }

    /** Dar de baja NO borra nada: la ficha y el historial siguen ahí. */
    public function test_dar_de_baja_no_borra_la_ficha(): void
    {
        $socio = $this->socio();
        $this->conMembresia($socio, EstadosCodigo::INSCRIPCION_VENCIDA);

        $this->darDeBaja($socio);

        $this->assertNotNull($socio->fresh(), 'La ficha desapareció al dar de baja.');
        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
    }

    /** Una membresía viva le deja entrar al gimnasio. */
    public function test_no_se_da_de_baja_a_quien_tiene_membresia_vigente(): void
    {
        $socio = $this->socio();
        $this->conMembresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA);

        $this->darDeBaja($socio)->assertSessionHas('error');

        $this->assertTrue((bool) $socio->fresh()->activo);
    }

    public function test_no_se_da_de_baja_a_quien_tiene_la_membresia_pausada(): void
    {
        $socio = $this->socio();
        $this->conMembresia($socio, EstadosCodigo::INSCRIPCION_PAUSADA);

        $this->darDeBaja($socio)->assertSessionHas('error');

        $this->assertTrue((bool) $socio->fresh()->activo);
    }

    /**
     * Un saldo sin cobrar desaparecería del listado de «por cobrar» en cuanto
     * el socio dejara de estar activo: la deuda seguiría existiendo pero nadie
     * volvería a verla.
     */
    public function test_no_se_da_de_baja_a_quien_debe_dinero(): void
    {
        $socio = $this->socio();
        $this->conDeuda($socio);

        $this->darDeBaja($socio)->assertSessionHas('error');

        $this->assertTrue((bool) $socio->fresh()->activo);
    }

    public function test_reactivar_lo_devuelve_al_listado(): void
    {
        $socio = $this->socio(['activo' => false]);

        $this->actingAs($this->administrador())
            ->patch("/panel/clientes/{$socio->uuid}/reactivar")
            ->assertSessionHasNoErrors();

        $this->assertTrue((bool) $socio->fresh()->activo);
    }

    public function test_dar_de_baja_a_quien_ya_estaba_de_baja_avisa(): void
    {
        $socio = $this->socio(['activo' => false]);

        $this->darDeBaja($socio)->assertSessionHas('error');
    }

    /** Eliminar manda a la papelera, no al vacío. */
    public function test_eliminar_manda_a_la_papelera(): void
    {
        $socio = $this->socio();

        $this->actingAs($this->administrador())
            ->delete("/panel/clientes/{$socio->uuid}")
            ->assertSessionHasNoErrors();

        $this->assertNull(Cliente::find($socio->id));
        $this->assertNotNull(Cliente::withTrashed()->find($socio->id));
    }

    /**
     * EL BUG QUE ESTO EVITA.
     *
     * El de Blade borraba la foto del disco ANTES del borrado suave, así que
     * restaurar al socio devolvía la ficha pero no la foto: se perdía para
     * siempre en una acción que se presenta como reversible.
     */
    public function test_eliminar_no_borra_la_foto(): void
    {
        $socio = $this->socio(['foto_perfil' => 'fotos/socio.jpg']);

        $this->actingAs($this->administrador())->delete("/panel/clientes/{$socio->uuid}");

        $this->assertSame(
            'fotos/socio.jpg',
            Cliente::withTrashed()->find($socio->id)->foto_perfil,
            'La foto se perdió: el borrado a papelera tiene que poder deshacerse entero.'
        );
    }

    /** Las mismas reglas que para dar de baja: no se va quien debe. */
    public function test_no_se_elimina_a_quien_tiene_membresia_vigente(): void
    {
        $socio = $this->socio();
        $this->conMembresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA);

        $this->actingAs($this->administrador())
            ->delete("/panel/clientes/{$socio->uuid}")
            ->assertSessionHas('error');

        $this->assertNotNull(Cliente::find($socio->id));
    }

    /** Y lo eliminado aparece en la papelera, listo para volver. */
    public function test_lo_eliminado_se_puede_recuperar(): void
    {
        $socio = $this->socio(['nombres' => 'Recuperable']);

        $this->actingAs($this->administrador())->delete("/panel/clientes/{$socio->uuid}");

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/clientes/{$socio->id}/restaurar");

        $this->assertNotNull(Cliente::find($socio->id));
    }
}
