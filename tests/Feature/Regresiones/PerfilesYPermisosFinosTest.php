<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\TipoNotificacion;
use App\Models\User;
use App\Services\CorreoService;
use App\Support\Ajustes;
use App\Support\CatalogoDePermisos;
use App\Support\Permisos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Qué puede hacer cada perfil, y los tres permisos finos de la recepción.
 *
 *  · La pantalla de perfiles: solo la abre y la guarda quien cambia cuentas,
 *    el Administrador no se toca, no entra un permiso inventado ni el «*», y
 *    lo que se enciende arrastra lo que necesita.
 *  · La caja del día: lo de hoy y nada más. Lo que no se ve no se tapa: NO
 *    LLEGA, y eso es lo que se comprueba en las props.
 *  · Corregir sus cobros de hoy: los suyos y de hoy; ni los de ayer, ni los de
 *    otro turno, ni anular.
 *  · Escribirle a un socio: ahora la recepción puede.
 */
class PerfilesYPermisosFinosTest extends CasoConCatalogos
{
    private const PRECIO = 30000;

    // ===== El catálogo =====

    /**
     * Toda ruta del panel pide un permiso que tiene casilla. Si mañana se
     * agrega una con un permiso nuevo y nadie lo pone en CatalogoDePermisos,
     * no se le podría dar a nadie desde la pantalla, y esta prueba lo dice.
     */
    public function test_todo_permiso_que_pide_una_ruta_esta_en_el_catalogo(): void
    {
        $usados = [];

        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();

            if (! $nombre || ! preg_match('/^(admin|panel)\./', $nombre)) {
                continue;
            }

            foreach (array_merge([Permisos::para($nombre)], Permisos::tambien($nombre)) as $permiso) {
                if ($permiso !== null) {
                    $usados[$permiso] = $nombre;
                }
            }
        }

        $faltan = array_diff(array_keys($usados), CatalogoDePermisos::todos());
        $this->assertSame([], array_values($faltan), 'Hay permisos que piden las rutas y no tienen casilla en Perfiles.');

        // Y al revés: una casilla que ninguna ruta mira no hace nada.
        $sobran = array_diff(CatalogoDePermisos::todos(), array_keys($usados));
        $this->assertSame([], array_values($sobran), 'Hay casillas en Perfiles que ninguna ruta pide.');
    }

    public function test_lo_de_recepcion_esta_todo_en_el_catalogo(): void
    {
        foreach (\Database\Seeders\RolesSeeder::PERMISOS_RECEPCION as $permiso) {
            $this->assertTrue(CatalogoDePermisos::existe($permiso), "«{$permiso}» no tiene casilla.");
        }
    }

    // ===== La pantalla de perfiles =====

    public function test_el_administrador_la_abre_y_el_suyo_no_se_edita(): void
    {
        $this->actingAs($this->administrador())
            ->get('/panel/usuarios/perfiles')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Usuarios/Perfiles')
                ->where('perfiles.0.nombre', 'Administrador')
                ->where('perfiles.0.es_admin', true)
                ->where('perfiles.1.nombre', 'Recepcionista')
                ->where('perfiles.1.es_admin', false)
                ->where('perfiles.1.permisos', fn ($lista) => collect($lista)->contains('caja.hoy')));

        $this->actingAs($this->administrador())
            ->put('/panel/usuarios/perfiles/1', ['permisos' => ['clientes.ver']])
            ->assertForbidden();

        $this->assertSame(['*'], Rol::find(1)->permisos);
    }

    public function test_recepcion_no_entra(): void
    {
        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)->get('/panel/usuarios/perfiles')->assertForbidden();
        $this->actingAs($recepcion)
            ->put('/panel/usuarios/perfiles/2', ['permisos' => ['usuarios.editar']])
            ->assertForbidden();

        $this->assertNotContains('usuarios.editar', Rol::find(2)->permisos);
    }

    public function test_guarda_lo_marcado_con_lo_que_necesita_y_anota_quien(): void
    {
        $admin = $this->administrador();

        // Solo «Corregir sus cobros de hoy»: arrastra «Cobrar» y «Ver pagos».
        $this->actingAs($admin)
            ->put('/panel/usuarios/perfiles/2', ['permisos' => ['pagos.corregir_hoy', 'clientes.ver']])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertEqualsCanonicalizing(
            ['clientes.ver', 'pagos.ver', 'pagos.crear', 'pagos.corregir_hoy'],
            Rol::find(2)->permisos
        );

        $cambio = DB::table('cambios_de_perfil')->where('id_rol', 2)->first();
        $this->assertSame($admin->id, (int) $cambio->id_usuario);
        $this->assertContains('caja.hoy', json_decode($cambio->quitados, true));
        $this->assertSame([], json_decode($cambio->agregados, true));

        // Y lo nuevo vale en la próxima petición: ya no ve la caja del día.
        $this->actingAs($this->recepcionista())->get('/panel/caja/hoy')->assertForbidden();
    }

    public function test_no_acepta_permisos_inventados_ni_el_comodin(): void
    {
        $antes = Rol::find(2)->permisos;

        foreach (['*', 'pagos.*', 'borrar.todo'] as $raro) {
            $this->actingAs($this->administrador())
                ->put('/panel/usuarios/perfiles/2', ['permisos' => ['clientes.ver', $raro]])
                ->assertSessionHasErrors('permisos.1');
        }

        $this->assertSame($antes, Rol::find(2)->permisos);
    }

    /** Dar «Usuarios» se puede, pero solo a propósito: nada lo enciende solo. */
    public function test_usuarios_editar_solo_llega_si_se_marca(): void
    {
        $todoMenosUsuarios = array_values(array_filter(
            CatalogoDePermisos::todos(),
            fn ($p) => ! str_starts_with($p, 'usuarios.')
        ));

        $this->assertNotContains('usuarios.editar', CatalogoDePermisos::conLoQueNecesitan($todoMenosUsuarios));

        $this->actingAs($this->administrador())
            ->put('/panel/usuarios/perfiles/2', ['permisos' => ['usuarios.editar']])
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(['usuarios.ver', 'usuarios.editar'], Rol::find(2)->permisos);
    }

    // ===== La caja del día =====

    private function pagoDe(?User $quien, string $fecha, int $monto = self::PRECIO): Pago
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => self::PRECIO,
            'precio_final' => self::PRECIO,
        ]);

        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => self::PRECIO,
            'monto_abonado' => $monto,
            'monto_pendiente' => self::PRECIO - $monto,
            'id_estado' => $monto >= self::PRECIO ? EstadosCodigo::PAGO_PAGADO : EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => $monto >= self::PRECIO ? 'completo' : 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => $fecha,
            'id_usuario' => $quien?->id,
        ]);
    }

    public function test_recepcion_ve_solo_lo_de_hoy(): void
    {
        $recepcion = $this->recepcionista();
        $this->pagoDe($recepcion, now()->toDateString(), 30000);
        $this->pagoDe(null, now()->subDay()->toDateString(), 30000);
        $this->pagoDe(null, now()->subMonth()->toDateString(), 30000);

        $this->actingAs($recepcion)
            ->get('/panel/caja/hoy')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('CajaDelDia')
                ->where('entradas.membresias', 30000)
                ->where('entradas.total', 30000)
                ->has('cobros.membresias', 1)
                ->where('cobros.membresias.0.monto', 30000)
                ->where('cobros.membresias.0.quien', $recepcion->name)
                // Lo que no es de hoy no llega, ni tapado.
                ->missing('caja')
                ->missing('deuda')
                ->missing('fiado')
                ->missing('talleres')
                ->missing('porMes')
                ->missing('porDia')
                ->missing('porPlan')
                ->missing('altas'));

        // La caja completa la sigue sin ver: se la manda a la de hoy.
        $this->actingAs($recepcion)->get('/panel/caja')->assertRedirect('/panel/caja/hoy');
    }

    public function test_el_administrador_sigue_viendo_la_caja_completa(): void
    {
        $this->pagoDe(null, now()->toDateString());

        $this->actingAs($this->administrador())
            ->get('/panel/caja')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Caja')->has('deuda')->has('porMes')->where('caja.hoy.membresias', self::PRECIO));
    }

    public function test_cerrar_la_caja_tambien_cierra_la_del_dia(): void
    {
        Ajustes::guardar(['privacidad.ocultar_caja' => '1']);
        Ajustes::olvidar();

        $this->actingAs($this->recepcionista())->get('/panel/caja/hoy')->assertRedirect('/panel');
    }

    public function test_sin_caja_hoy_no_entra(): void
    {
        $sinCaja = Rol::create([
            'nombre' => 'Profesor',
            'permisos' => ['clientes.ver'],
            'activo' => true,
        ]);
        $profe = User::factory()->create(['id_rol' => $sinCaja->id, 'activo' => true]);

        $this->actingAs($profe)->get('/panel/caja/hoy')->assertForbidden();
        $this->actingAs($profe)->get('/panel/caja')->assertForbidden();
    }

    // ===== Corregir sus cobros de hoy =====

    private function corregir(User $quien, Pago $pago, array $cambios = [])
    {
        return $this->actingAs($quien)->put("/panel/pagos/{$pago->uuid}", array_merge([
            'form_submit_token' => uniqid('t', true),
            'monto_abonado' => (int) $pago->monto_abonado,
            'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
            'id_metodo_pago' => $pago->id_metodo_pago,
        ], $cambios));
    }

    public function test_corrige_su_propio_pago_de_hoy(): void
    {
        $recepcion = $this->recepcionista();
        $pago = $this->pagoDe($recepcion, now()->toDateString(), 20000);

        $this->actingAs($recepcion)
            ->get("/panel/pagos/{$pago->uuid}")
            ->assertInertia(fn ($p) => $p->where('puede.corregir', true));

        $this->actingAs($recepcion)
            ->get("/panel/pagos/{$pago->uuid}/editar")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('pago.solo_hoy', true));

        $this->corregir($recepcion, $pago, ['monto_abonado' => 2000])->assertSessionHasNoErrors();

        $this->assertEquals(2000, $pago->fresh()->monto_abonado);
    }

    public function test_no_le_cambia_la_fecha_a_otro_dia(): void
    {
        $recepcion = $this->recepcionista();
        $pago = $this->pagoDe($recepcion, now()->toDateString(), 20000);

        $this->corregir($recepcion, $pago, ['fecha_pago' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors('fecha_pago');

        $this->assertTrue($pago->fresh()->fecha_pago->isToday());
    }

    public function test_no_corrige_el_de_ayer_ni_el_de_otro(): void
    {
        $recepcion = $this->recepcionista();
        $otra = $this->recepcionista();

        $deAyer = $this->pagoDe($recepcion, now()->subDay()->toDateString(), 20000);
        $deOtra = $this->pagoDe($otra, now()->toDateString(), 20000);
        $sinAutor = $this->pagoDe(null, now()->toDateString(), 20000);

        foreach ([$deAyer, $deOtra, $sinAutor] as $pago) {
            $this->actingAs($recepcion)->get("/panel/pagos/{$pago->uuid}/editar")->assertForbidden();
            $this->corregir($recepcion, $pago, ['monto_abonado' => 1000])->assertForbidden();
            $this->assertEquals(20000, $pago->fresh()->monto_abonado);

            $this->actingAs($recepcion)
                ->get("/panel/pagos/{$pago->uuid}")
                ->assertInertia(fn ($p) => $p->where('puede.corregir', false));
        }
    }

    public function test_no_anula_ni_siquiera_el_suyo(): void
    {
        $recepcion = $this->recepcionista();
        $pago = $this->pagoDe($recepcion, now()->toDateString());

        $this->actingAs($recepcion)->delete("/panel/pagos/{$pago->uuid}")->assertForbidden();

        $this->assertNotSoftDeleted('pagos', ['id' => $pago->id]);
    }

    public function test_el_administrador_corrige_cualquiera(): void
    {
        $pago = $this->pagoDe($this->recepcionista(), now()->subWeek()->toDateString(), 20000);
        $admin = $this->administrador();

        $this->actingAs($admin)
            ->get("/panel/pagos/{$pago->uuid}")
            ->assertInertia(fn ($p) => $p->where('puede.corregir', true));

        $this->corregir($admin, $pago, ['monto_abonado' => 2000])->assertSessionHasNoErrors();
        $this->assertEquals(2000, $pago->fresh()->monto_abonado);
    }

    /** El pago anota solo quién lo registró, venga del camino que venga. */
    public function test_el_cobro_anota_quien_lo_hizo(): void
    {
        $recepcion = $this->recepcionista();
        $this->actingAs($recepcion);

        $pago = $this->pagoDe(null, now()->toDateString());

        $this->assertSame($recepcion->id, (int) $pago->fresh()->id_usuario);
    }

    // ===== Escribirle a un socio =====

    public function test_recepcion_escribe_a_un_socio(): void
    {
        $doble = Mockery::mock(CorreoService::class);
        $doble->shouldReceive('enviar')->once()->andReturn('smtp');
        $this->app->instance(CorreoService::class, $doble);

        $socio = Cliente::factory()->create(['activo' => true, 'email' => 'socio' . uniqid() . '@progym.cl']);
        Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
        ]);
        $plantilla = TipoNotificacion::create([
            'codigo' => 'prueba_' . uniqid(),
            'nombre' => 'Aviso de prueba',
            'descripcion' => 'Para las pruebas',
            'asunto_email' => 'Hola {nombre}',
            'plantilla_email' => '<html><body><p>Hola.</p></body></html>',
            'activo' => true,
        ]);

        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)->get('/panel/notificaciones/enviar')->assertOk();

        $this->actingAs($recepcion)
            ->post('/panel/notificaciones/enviar', [
                'form_submit_token' => uniqid('t', true),
                'cliente_id' => $socio->id,
                'plantilla_id' => $plantilla->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notificaciones', [
            'id_cliente' => $socio->id,
            'id_estado' => EstadosCodigo::NOTIFICACION_ENVIADA,
        ]);

        // A un grupo, no: eso sigue siendo del dueño.
        $this->actingAs($recepcion)->get('/panel/notificaciones/masivo')->assertForbidden();
    }

    // ===== La migración =====

    /** Sobre una recepción ya existente: agrega lo nuevo, no quita nada, y dos veces da lo mismo. */
    public function test_la_migracion_agrega_sin_quitar_y_se_puede_repetir(): void
    {
        DB::table('roles')->where('id', 2)->update([
            'permisos' => json_encode(['clientes.ver', 'pagos.ver', 'pagos.crear', 'algo.viejo']),
        ]);

        $migracion = require database_path('migrations/2026_10_03_110000_recepcion_cuadra_la_caja_y_corrige_sus_cobros.php');
        $migracion->up();
        $migracion->up();

        $this->assertEqualsCanonicalizing(
            ['clientes.ver', 'pagos.ver', 'pagos.crear', 'algo.viejo', 'notificaciones.crear', 'caja.hoy', 'pagos.corregir_hoy'],
            Rol::find(2)->permisos
        );
        $this->assertSame(['*'], Rol::find(1)->permisos);
    }
}
