<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Falla;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Support\IpDelCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\CasoConCatalogos;

/**
 * Lo que salió en la revisión de seguridad del 28 de septiembre de 2026:
 * permisos de recepción, páginas públicas y configuración.
 */
class RevisionDeSeguridadDosTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    // ---------- La dirección con que se cuenta ----------

    public function test_con_ipv6_se_cuenta_la_red_y_con_ipv4_la_direccion(): void
    {
        $pedido = fn (string $ip) => Request::create('/', 'GET', server: ['REMOTE_ADDR' => $ip]);

        $this->assertSame('2800:150:1:2::/64', IpDelCliente::paraLimitar($pedido('2800:150:1:2::1')));
        $this->assertSame('2800:150:1:2::/64', IpDelCliente::paraLimitar($pedido('2800:150:1:2:aaaa:bbbb:cccc:dddd')));
        $this->assertSame('190.100.1.2', IpDelCliente::paraLimitar($pedido('190.100.1.2')));
    }

    // ---------- Páginas públicas ----------

    public function test_el_contacto_no_gasta_mas_de_treinta_correos_al_dia(): void
    {
        $enviados = 0;
        $this->mock(\App\Services\CorreoService::class, function ($doble) use (&$enviados) {
            $doble->shouldReceive('enviar')->andReturnUsing(function () use (&$enviados) {
                $enviados++;

                return true;
            });
        });

        RateLimiter::increment('contacto:del-dia', 86400, 30);

        $this->withServerVariables(['REMOTE_ADDR' => '10.8.0.1'])
            ->post('/contacto', [
                'nombre' => 'Pedro Soto',
                'email' => 'pedro.soto@gmail.com', // El formulario mira que el dominio exista.
                'mensaje' => 'Quiero saber los horarios de la sala.',
            ])
            ->assertSessionHas('success');

        $this->assertSame(0, $enviados);
    }

    public function test_las_fallas_del_navegador_tienen_tope_por_persona(): void
    {
        $usuario = $this->recepcionista();
        RateLimiter::increment('fallas-navegador:' . $usuario->id, 86400, 200);

        $this->actingAs($usuario)
            ->postJson('/fallas/navegador', ['mensaje' => 'Algo se rompió'])
            ->assertNoContent();

        $this->assertSame(0, Falla::count());
    }

    public function test_el_disco_privado_no_se_sirve_por_storage(): void
    {
        $this->assertFalse(Route::has('storage.local'));
    }

    public function test_el_panel_lleva_politica_de_contenidos_y_de_permisos(): void
    {
        $this->actingAs($this->administrador())->get('/panel')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'")
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    // ---------- Permisos de recepción ----------

    public function test_recepcion_no_manda_correos_a_grupos_ni_baja_la_lista(): void
    {
        $recepcion = $this->recepcionista();

        $this->actingAs($recepcion)
            ->post('/panel/notificaciones/masivo/destinatarios', ['grupo' => 'todos'])
            ->assertForbidden();

        $this->actingAs($recepcion)
            ->post('/panel/notificaciones/masivo', ['grupo' => 'todos', 'asunto' => 'Hola', 'mensaje' => 'Hola'])
            ->assertForbidden();
    }

    public function test_recepcion_no_crea_talleres(): void
    {
        $this->actingAs($this->recepcionista())
            ->post('/panel/talleres', ['nombre' => 'Clases', 'precio_hora' => 1])
            ->assertForbidden();
    }

    public function test_recepcion_no_ve_lo_que_entro_en_pagos(): void
    {
        $this->actingAs($this->recepcionista())->get('/panel/pagos')
            ->assertOk()
            ->assertInertia(fn ($pagina) => $pagina
                ->missing('resumen.recaudado_mes')
                ->missing('resumen.recaudado_hoy')
                ->has('resumen.por_cobrar'));

        $this->actingAs($this->administrador())->get('/panel/pagos')
            ->assertInertia(fn ($pagina) => $pagina->has('resumen.recaudado_mes'));
    }

    private function membresia(): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => 3,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'fecha_inicio' => '2026-03-01',
            'fecha_vencimiento' => '2026-05-30',
            'precio_base' => 100000,
            'descuento_aplicado' => 0,
            'precio_final' => 100000,
        ]);
    }

    private function corregir($usuario, Inscripcion $inscripcion, array $datos)
    {
        return $this->actingAs($usuario)->put("/panel/inscripciones/{$inscripcion->uuid}", array_merge([
            'form_submit_token' => uniqid('t', true),
            'fecha_inicio' => '2026-03-01',
            'fecha_vencimiento' => '2026-05-30',
            'precio_base' => 100000,
        ], $datos));
    }

    public function test_recepcion_corrige_fechas_pero_no_el_precio_ni_el_largo(): void
    {
        $recepcion = $this->recepcionista();
        $inscripcion = $this->membresia();

        $this->corregir($recepcion, $inscripcion, ['precio_base' => 1000])
            ->assertSessionHasErrors('precio_base');

        $this->corregir($recepcion, $inscripcion, ['fecha_vencimiento' => '2036-12-31'])
            ->assertSessionHasErrors('fecha_vencimiento');

        $this->corregir($recepcion, $inscripcion, ['fecha_inicio' => '2026-04-01', 'fecha_vencimiento' => '2026-06-30'])
            ->assertSessionHasNoErrors();

        $inscripcion->refresh();
        $this->assertSame(100000, (int) $inscripcion->precio_final);
        $this->assertSame('2026-04-01', $inscripcion->fecha_inicio->format('Y-m-d'));
    }

    public function test_corregir_una_membresia_queda_en_el_historial(): void
    {
        $admin = $this->administrador();
        $inscripcion = $this->membresia();

        $this->corregir($admin, $inscripcion, ['precio_base' => 80000])->assertSessionHasNoErrors();

        $cambio = HistorialCambio::where('inscripcion_id', $inscripcion->id)->sole();
        $this->assertSame('correccion', $cambio->tipo_cambio);
        $this->assertSame($admin->id, $cambio->usuario_id);
        $this->assertSame(100000, $cambio->detalles['antes']['precio_final']);
        $this->assertSame(80000, $cambio->detalles['despues']['precio_final']);
    }
}
