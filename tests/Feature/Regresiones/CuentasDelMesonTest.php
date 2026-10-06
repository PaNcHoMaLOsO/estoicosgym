<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\MetodoPago;
use App\Models\User;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * Lo que las pantallas del fiado le dicen a quien atiende.
 *
 * El aviso de deshacer un cobro, el plazo para insistir y el nombre de quien
 * debe: tres cifras que el servidor calculaba bien y la pantalla contaba mal.
 */
class CuentasDelMesonTest extends CasoConCatalogos
{
    private $admin = null;

    private function como()
    {
        return $this->actingAs($this->admin ??= $this->administrador());
    }

    private function efectivo(): int
    {
        return (int) MetodoPago::where('nombre', 'like', '%fectivo%')->value('id');
    }

    private function fiar(array $datos): void
    {
        $this->como()->post('/panel/fiados', $datos + ['concepto' => 'Bebida'])
            ->assertSessionHasNoErrors();
    }

    /**
     * Deshacer un cobro reabre TODAS sus líneas, y el aviso enseñaba el monto
     * de la fila pulsada: se confirmaba $1.500 y volvían a deberse $4.000.
     */
    public function test_lo_cobrado_dice_cuanto_vuelve_si_se_deshace(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'monto' => 1500]);
        // Otra cuenta cobrada en el mismo segundo no se suma: no se reabre.
        $this->fiar(['nombre' => 'Visita', 'monto' => 900]);

        $this->travelTo(now()->startOfSecond());
        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id, 'id_metodo_pago' => $this->efectivo()]);
        $this->como()->post('/panel/fiados/saldar', ['nombre' => 'Visita', 'id_metodo_pago' => $this->efectivo()]);

        $cobrado = collect($this->como()->get('/panel/fiados')->assertOk()->viewData('page')['props']['cobrado']);

        // Un renglón por cobro, con su total: lo que vuelve si se deshace.
        $delSocio = $cobrado->where('total', 4000)->first();
        $this->assertNotNull($delSocio);
        $this->assertCount(2, $delSocio['lineas']);
        $this->assertNotNull($cobrado->where('total', 900)->first());

        // Y es lo que de verdad vuelve.
        $this->como()->patch("/panel/fiados/{$delSocio['uuid']}/reabrir");
        $this->assertSame(4000, (int) Fiado::debiendo()->sum('monto'));
    }

    /** El resumen marca lo viejo con el plazo de Configuración → Mesón, no con 14. */
    public function test_el_resumen_usa_el_plazo_configurado_para_insistir(): void
    {
        Ajustes::guardar(['meson.dias_fiado_viejo' => 5]);
        $this->fiar(['nombre' => 'Visita', 'monto' => 900]);

        $fiado = $this->como()->get('/panel')->assertOk()->viewData('page')['props']['fiado'];

        $this->assertSame(5, $fiado['dias_para_insistir']);
    }

    /**
     * Un socio en la papelera sigue debiendo lo que se llevó: su cuenta sale con
     * su nombre, sin enlace a una ficha que no abre, y con una llave propia.
     */
    public function test_la_cuenta_de_un_socio_eliminado_conserva_su_nombre(): void
    {
        $uno = Cliente::factory()->create(['nombres' => 'Ana', 'apellido_paterno' => 'Rojas']);
        $otro = Cliente::factory()->create(['nombres' => 'Luis', 'apellido_paterno' => 'Soto']);
        $this->fiar(['id_cliente' => $uno->id, 'monto' => 1000]);
        $this->fiar(['id_cliente' => $otro->id, 'monto' => 2000]);
        $uno->delete();
        $otro->delete();

        Ajustes::guardar(['privacidad.ocultar_nombres' => false]);
        $cuentas = collect($this->como()->get('/panel')->assertOk()->viewData('page')['props']['fiado']['cuentas']);

        $this->assertCount(2, $cuentas->pluck('clave')->unique());
        $this->assertNotContains('Sin nombre', $cuentas->pluck('quien'));
        $this->assertSame([null, null], $cuentas->pluck('socio_uuid')->all());

        $pantalla = collect($this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas']);
        $this->assertEqualsCanonicalizing(['Ana Rojas', 'Luis Soto'], $pantalla->pluck('quien')->all());
        $this->assertSame([null, null], $pantalla->pluck('socio_uuid')->all());
    }
}
