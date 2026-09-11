<?php

namespace Tests\Feature\Regresiones;

use App\Support\Permisos;
use Illuminate\Support\Facades\Route;
use Tests\CasoConCatalogos;

/**
 * Los permisos por rol se aplican de verdad.
 *
 * La tabla `roles` guardaba una lista de permisos desde el primer dia y no
 * habia nada que la leyera: recepcion entraba igual a la configuracion del
 * gimnasio y a los informes de ingresos. Un agujero de permisos no falla,
 * simplemente deja pasar, asi que sin estas pruebas volveria a abrirse sin que
 * nadie lo note.
 */
class PermisosPorRolTest extends CasoConCatalogos
{
    public function test_recepcion_no_entra_a_la_configuracion(): void
    {
        $vetadas = [
            '/panel/membresias',
            '/panel/convenios',
            '/panel/metodos-pago',
            '/panel/motivos-descuento',
            '/panel/configuracion',
            '/panel/usuarios',
        ];

        foreach ($vetadas as $ruta) {
            $this->actingAs($this->recepcionista())
                ->get($ruta)
                ->assertForbidden();
        }
    }

    public function test_recepcion_no_ve_los_informes_de_ingresos(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/reportes/ingresos')
            ->assertForbidden();
    }

    public function test_recepcion_si_hace_su_trabajo_de_meson(): void
    {
        $suyas = [
            '/panel',
            '/panel/clientes',
            '/panel/clientes/crear',
            '/panel/inscripciones',
            '/panel/pagos',
            '/panel/historial',
            '/panel/notificaciones',
        ];

        foreach ($suyas as $ruta) {
            $this->actingAs($this->recepcionista())
                ->get($ruta)
                ->assertOk();
        }
    }

    public function test_recepcion_no_borra_socios(): void
    {
        $cliente = \App\Models\Cliente::factory()->create();

        $this->actingAs($this->recepcionista())
            ->delete("/panel/clientes/{$cliente->uuid}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_el_administrador_llega_a_todo(): void
    {
        $todas = [
            '/panel',
            '/panel/membresias',
            '/panel/convenios',
            '/panel/reportes',
            '/panel/clientes',
        ];

        foreach ($todas as $ruta) {
            $this->actingAs($this->administrador())
                ->get($ruta)
                ->assertOk();
        }
    }

    /**
     * Ninguna ruta del panel puede quedarse sin permiso asignado.
     *
     * Es la prueba que sostiene a las demas: si manana se agrega un modulo y
     * nadie lo clasifica, esto lo saca a la luz en vez de dejarlo abierto.
     */
    public function test_toda_ruta_del_panel_exige_un_permiso(): void
    {
        $sinClasificar = [];

        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();

            if (! $nombre || ! preg_match('/^(admin|panel)\./', $nombre)) {
                continue;
            }

            if (Permisos::para($nombre) === null) {
                $sinClasificar[] = $nombre;
            }
        }

        $this->assertSame([], $sinClasificar, 'Hay rutas del panel que no exigen ningún permiso.');
    }

    /**
     * El panel viejo ya no existe: ni para mirar ni para cobrar.
     *
     * Calculaba saldos y fechas a su manera y se entraba escribiendo la
     * dirección; un pago hecho ahí podía descuadrar lo del panel nuevo.
     */
    public function test_el_panel_viejo_ya_no_existe(): void
    {
        $admin = $this->administrador();

        $this->actingAs($admin)->get('/admin/clientes')->assertNotFound();
        $this->actingAs($admin)->get('/admin/pagos/create')->assertNotFound();
        $this->actingAs($admin)->post('/admin/pagos', [])->assertNotFound();
    }
}
