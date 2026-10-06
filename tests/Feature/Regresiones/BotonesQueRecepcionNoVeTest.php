<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\CotizacionTaller;
use App\Models\Inscripcion;
use App\Models\Institucion;
use App\Models\Taller;
use App\Support\Permisos;
use Database\Seeders\RolesSeeder;
use Tests\CasoConCatalogos;

/**
 * A recepción no se le pintan botones que el servidor le va a rechazar.
 *
 * Talleres le enseñaba «Nuevo», el horario editable, «Cerrar el mes», «Marcar
 * pagada», «Reabrir» y la papelera; una cotización, todo el formulario; y en
 * Notificaciones, «Escribir a un socio», «Aviso a un grupo» y «Editar» en las
 * plantillas. Al pulsar, un 403. El servidor ya decidía bien: lo que fallaba
 * era la pantalla.
 *
 * Las pantallas deciden con `puede(auth, 'permiso')` sobre la lista que manda
 * HandleInertiaRequests, o con banderas `puede` que arma el controlador. Aquí
 * se vigilan las dos cosas, y además que el permiso que mira cada pantalla sea
 * el MISMO que exige su ruta: si mañana una ruta cambia de permiso y la
 * pantalla no, el botón vuelve a aparecer para nada.
 */
class BotonesQueRecepcionNoVeTest extends CasoConCatalogos
{
    /**
     * Ruta => permiso que la pantalla consulta antes de pintar su botón.
     *
     * Si una de estas falla, hay que cambiar el permiso en la pantalla (el
     * archivo .jsx que se indica) a la vez que en la ruta.
     */
    private const PANTALLA_Y_RUTA = [
        // Talleres/Index.jsx, Talleres/Ficha.jsx, Talleres/Cotizacion.jsx
        'panel.talleres.store' => 'pagos.editar',
        'panel.talleres.update' => 'pagos.editar',
        'panel.talleres.cerrar' => 'pagos.editar',
        'panel.talleres.cobros.update' => 'pagos.editar',
        'panel.talleres.instituciones.update' => 'pagos.editar',
        'panel.talleres.cotizaciones.update' => 'pagos.editar',
        'panel.talleres.cotizaciones.refrescar' => 'pagos.editar',
        'panel.talleres.destroy' => 'pagos.eliminar',
        'panel.talleres.horas.destroy' => 'pagos.eliminar',
        'panel.talleres.cobros.destroy' => 'pagos.eliminar',
        'panel.talleres.cotizaciones.destroy' => 'pagos.eliminar',
        'panel.talleres.horas.store' => 'pagos.crear',
        'panel.talleres.horas.mes' => 'pagos.crear',
        'panel.talleres.cotizaciones.store' => 'pagos.crear',
        // Notificaciones/Index.jsx y Notificaciones/Plantillas.jsx
        'panel.notificaciones.crear' => 'notificaciones.crear',
        'panel.notificaciones.crear-masivo' => 'notificaciones.editar',
        'panel.notificaciones.reenviar' => 'notificaciones.enviar',
        'panel.notificaciones.cancelar' => 'notificaciones.enviar',
        'panel.notificaciones.plantillas.actualizar' => 'configuracion.editar',
        'panel.notificaciones.plantillas.preview' => 'configuracion.editar',
        // Pagos/Editar.jsx (anular) y los «Nuevo» de los listados. Corregir
        // no va aquí: lo decide el servidor con `puede.corregir`.
        'panel.pagos.destroy' => 'pagos.eliminar',
        'panel.pagos.create' => 'pagos.crear',
        'panel.clientes.create' => 'clientes.crear',
        'panel.inscripciones.create' => 'inscripciones.crear',
        // Canje.jsx
        'panel.canje.store' => 'clientes.crear',
        'panel.canje.anular' => 'clientes.editar',
        'panel.convenios.index' => 'configuracion.ver',
        // Clientes/Crear.jsx: «Restaurar su ficha» saca de la papelera
        'panel.papelera.restore' => 'configuracion.editar',
        // Clientes/Ficha.jsx
        'panel.inscripciones.renovar' => 'inscripciones.gestionar',
        'panel.clientes.borrar-datos' => 'clientes.eliminar',
        // Layout.jsx: la entrada «Caja» de quien solo ve lo de hoy
        'panel.caja.hoy' => 'caja.hoy',
        // Usuarios/Index.jsx: «Qué puede cada perfil»
        'panel.usuarios.perfiles' => 'usuarios.editar',
    ];

    public function test_cada_pantalla_mira_el_mismo_permiso_que_su_ruta(): void
    {
        foreach (self::PANTALLA_Y_RUTA as $ruta => $permiso) {
            $this->assertSame($permiso, Permisos::para($ruta), "La pantalla mira «{$permiso}» para {$ruta}, pero la ruta exige otro.");
        }
    }

    /**
     * Lo que las pantallas leen para decidir: la lista de permisos que viaja
     * en `auth.user.permisos`. A recepción le llega lo suyo y nada más.
     */
    public function test_a_recepcion_le_llegan_sus_permisos_y_no_los_que_no_tiene(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/talleres')
            ->assertOk()
            ->assertInertia(function ($pagina) {
                $permisos = $pagina->toArray()['props']['auth']['user']['permisos'];

                // Lo que SÍ hace: anotar clases, cotizar, cobrar, dar de alta,
                // escribirle a un socio, cuadrar la caja del día y corregir
                // sus propios cobros de hoy.
                foreach (['pagos.ver', 'pagos.crear', 'pagos.corregir_hoy', 'caja.hoy', 'clientes.crear', 'clientes.editar', 'inscripciones.crear', 'inscripciones.gestionar', 'notificaciones.crear', 'notificaciones.enviar'] as $suyo) {
                    $this->assertContains($suyo, $permisos);
                }

                // Lo que no: con cualquiera de estos, un botón le chocaría.
                foreach (['pagos.editar', 'pagos.eliminar', 'clientes.eliminar', 'inscripciones.eliminar', 'notificaciones.editar', 'reportes.ver', 'configuracion.ver', 'configuracion.editar', 'usuarios.ver', 'usuarios.editar', '*'] as $ajeno) {
                    $this->assertNotContains($ajeno, $permisos);
                }

                $this->assertEqualsCanonicalizing(RolesSeeder::PERMISOS_RECEPCION, $permisos);
            });
    }

    /** Y el administrador recibe el comodín, que es lo que enciende todo. */
    public function test_al_administrador_le_llega_el_comodin(): void
    {
        $this->actingAs($this->administrador())
            ->get('/panel/talleres')
            ->assertOk()
            ->assertInertia(fn ($pagina) => $this->assertContains('*', $pagina->toArray()['props']['auth']['user']['permisos']));
    }

    /**
     * Las pantallas de talleres que recepción sí abre siguen abriendo: lo que
     * cambió es qué se pinta, no quién entra.
     */
    public function test_recepcion_abre_talleres_y_cotizaciones_pero_no_los_cambia(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio de prueba', 'rut' => '65.154.436-K']);
        $taller = Taller::create([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'precio_hora' => 30000,
            'horario' => ['lunes' => [['15:30', '16:30']]],
            'activo' => true,
        ]);

        // Cotizar es de recepción.
        $this->actingAs($this->recepcionista())
            ->post("/panel/talleres/{$taller->uuid}/cotizaciones", ['periodo' => '2026-07'])
            ->assertRedirect();
        $cotizacion = CotizacionTaller::firstOrFail();

        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)->get("/panel/talleres/{$taller->uuid}?periodo=2026-07")->assertOk();
        $this->actingAs($recepcion)->get("/panel/talleres/cotizaciones/{$cotizacion->uuid}")->assertOk();

        // Lo que la pantalla ya no le ofrece, el servidor tampoco se lo deja.
        $this->actingAs($recepcion)->patch("/panel/talleres/{$taller->uuid}", ['nombre' => 'Otro'])->assertForbidden();
        $this->actingAs($recepcion)->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07'])->assertForbidden();
        $this->actingAs($recepcion)->patch("/panel/talleres/cotizaciones/{$cotizacion->uuid}", [])->assertForbidden();
        $this->actingAs($recepcion)->delete("/panel/talleres/cotizaciones/{$cotizacion->uuid}")->assertForbidden();
        $this->actingAs($recepcion)->delete("/panel/talleres/{$taller->uuid}")->assertForbidden();
    }

    /**
     * Las banderas que arma el servidor: la ficha de una membresía no le
     * ofrece a recepción ni borrarla ni cancelarla.
     */
    public function test_la_ficha_de_la_membresia_no_le_ofrece_borrar_ni_cancelar(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 30000,
            'precio_final' => 30000,
            'fecha_vencimiento' => now()->addMonth(),
        ]);

        $this->actingAs($this->recepcionista())
            ->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertOk()
            ->assertInertia(fn ($pagina) => $pagina
                ->where('puede.borrar', false)
                ->where('puede.cancelar', false));

        // Al administrador sí, con la misma membresía: no es que no se pueda.
        $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertInertia(fn ($pagina) => $pagina
                ->where('puede.borrar', true)
                ->where('puede.cancelar', true));
    }

    /** Juntar fichas repetidas manda una a la papelera: no es de recepción. */
    public function test_los_duplicados_no_le_ofrecen_juntar(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/clientes/duplicados')
            ->assertOk()
            ->assertInertia(fn ($pagina) => $pagina->where('puedeJuntar', false));

        $this->actingAs($this->administrador())
            ->get('/panel/clientes/duplicados')
            ->assertInertia(fn ($pagina) => $pagina->where('puedeJuntar', true));
    }
}
