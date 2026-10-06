<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use App\Services\CorreoService;
use App\Services\EnvioManualService;
use App\Services\EnvioMasivoService;
use App\Services\NotificacionService;
use App\Support\Ajustes;
use Database\Seeders\PlantillasProgymSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use RuntimeException;
use Tests\CasoConCatalogos;

/**
 * Lo que encontró la revisión de los correos.
 *
 * Diez fallos sueltos con un mismo fondo: correos que salían cuando no
 * tocaba —el interruptor apagado, el socio en la papelera, avisos de hace una
 * semana—, que no salían cuando sí —el tope del día los mataba—, o que salían
 * mal —«Bienvenido» al que renueva, «[XX%]» y {variables} sin rellenar—.
 *
 * Ningún correo sale de verdad: el de mentira anota a quién se le mandó.
 */
class RevisionDeCorreosTest extends CasoConCatalogos
{
    /** @var list<array{para:string, asunto:string}> */
    private array $mandados = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlantillasProgymSeeder::class);
    }

    /** Un correo de mentira; con `$falla`, falla siempre. */
    private function fingirCorreo(?\Throwable $falla = null): void
    {
        $doble = Mockery::mock(CorreoService::class);
        $doble->shouldReceive('enviar')->andReturnUsing(function ($para, $asunto) use ($falla) {
            if ($falla) {
                throw $falla;
            }

            $this->mandados[] = compact('para', 'asunto');

            return 'id-falso';
        });
        $doble->shouldReceive('cerrar');

        $this->app->instance(CorreoService::class, $doble);
    }

    private function inscripcion(array $socio = [], array $extra = []): Inscripcion
    {
        $cliente = Cliente::factory()->create($socio + [
            'activo' => true,
            'email' => 'socio' . uniqid() . '@correo.cl',
        ]);

        return Inscripcion::factory()->create($extra + [
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => 40000,
            'precio_final' => 40000,
            'fecha_inicio' => today()->subDays(25),
            'fecha_vencimiento' => today()->addDays(5),
        ])->fresh(['cliente', 'membresia']);
    }

    private function tipo(string $codigo): TipoNotificacion
    {
        return TipoNotificacion::where('codigo', $codigo)->firstOrFail();
    }

    /** Una fila en cola, como la dejan los avisos y los envíos. */
    private function enCola(Inscripcion $inscripcion, string $codigo, array $extra = []): Notificacion
    {
        return Notificacion::create($extra + [
            'id_tipo_notificacion' => $this->tipo($codigo)->id,
            'id_cliente' => $inscripcion->id_cliente,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => $inscripcion->cliente->email,
            'asunto' => 'Aviso',
            'contenido' => '<p>Hola</p>',
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => today(),
            'tipo_envio' => 'automatica',
        ]);
    }

    private function servicio(): NotificacionService
    {
        return app(NotificacionService::class);
    }

    // ── 1. Renovar no da la bienvenida ─────────────────────────────────────

    public function test_al_renovar_llega_el_correo_de_renovacion_y_no_el_de_bienvenida(): void
    {
        $this->fingirCorreo();
        $anterior = $this->inscripcion([
            'es_menor_edad' => true,
            'apoderado_nombre' => 'Carla Rojas',
            'apoderado_email' => 'carla@correo.cl',
        ]);

        $this->actingAs($this->administrador())
            ->post("/panel/inscripciones/{$anterior->uuid}/renovar", [
                'form_submit_token' => uniqid('t', true),
                'id_membresia' => 4,
                'fecha_inicio' => now()->addDays(6)->format('Y-m-d'),
                'tipo_pago' => 'completo',
                'monto_abonado' => 40000,
                'id_metodo_pago' => MetodoPago::first()->id,
                'fecha_pago' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasNoErrors();

        $codigos = Notificacion::with('tipoNotificacion')->get()->pluck('tipoNotificacion.codigo')->all();

        $this->assertSame([TipoNotificacion::RENOVACION], $codigos, 'Renovar manda la renovación, sin bienvenida ni constancia al tutor.');
        $this->assertCount(1, $this->mandados);
    }

    // ── 2. El interruptor apaga también lo inmediato ──────────────────────

    public function test_con_los_automaticos_apagados_no_sale_la_bienvenida_ni_se_anota_nada(): void
    {
        $this->fingirCorreo();
        Ajustes::guardar(['tareas.correos_automaticos' => '0']);

        $inscripcion = $this->inscripcion([
            'es_menor_edad' => true,
            'apoderado_email' => 'carla@correo.cl',
        ]);

        $servicio = $this->servicio();

        $this->assertFalse($servicio->enviarNotificacionBienvenida($inscripcion)['enviada']);
        $this->assertFalse($servicio->enviarNotificacionTutorLegal($inscripcion)['enviada']);
        $this->assertNull($servicio->enviarNotificacionRenovacion($inscripcion));
        $this->assertNull($servicio->crearNotificacion($this->tipo(TipoNotificacion::PAUSA_INSCRIPCION), $inscripcion));

        $this->assertSame([], $this->mandados);
        $this->assertSame(0, Notificacion::count());
    }

    public function test_con_los_automaticos_apagados_el_envio_a_mano_sigue_saliendo(): void
    {
        $this->fingirCorreo();
        Ajustes::guardar(['tareas.correos_automaticos' => '0']);

        $inscripcion = $this->inscripcion();

        app(EnvioManualService::class)->enviar($inscripcion->cliente, $this->tipo(TipoNotificacion::MEMBRESIA_POR_VENCER));

        $this->assertCount(1, $this->mandados);
    }

    // ── 3. Lo atrasado no sale de golpe ───────────────────────────────────

    public function test_un_aviso_automatico_de_hace_dias_se_cancela_en_vez_de_salir(): void
    {
        $this->fingirCorreo();
        $inscripcion = $this->inscripcion();

        $viejo = $this->enCola($inscripcion, TipoNotificacion::MEMBRESIA_POR_VENCER, ['fecha_programada' => today()->subDays(5)]);
        $justo = $this->enCola($inscripcion, TipoNotificacion::MEMBRESIA_POR_VENCER, ['fecha_programada' => today()->subDays(2)]);
        $manual = $this->enCola($inscripcion, TipoNotificacion::MEMBRESIA_POR_VENCER, [
            'fecha_programada' => today()->subDays(5),
            'tipo_envio' => 'manual',
        ]);

        $this->servicio()->enviarPendientes();

        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $viejo->fresh()->id_estado);
        $this->assertStringContainsString('pasaron más de 2 días', $viejo->fresh()->error_mensaje);
        $this->assertSame(Notificacion::ESTADO_ENVIADO, (int) $justo->fresh()->id_estado);
        $this->assertSame(Notificacion::ESTADO_ENVIADO, (int) $manual->fresh()->id_estado, 'Lo manual no caduca.');
        $this->assertCount(2, $this->mandados);
    }

    public function test_al_reactivar_se_cancela_el_aviso_de_pausa_que_no_habia_salido(): void
    {
        // Con pausas permitidas a la vista: la fábrica las sortea.
        $inscripcion = $this->inscripcion([], ['max_pausas_permitidas' => 2]);
        $this->assertTrue((bool) $inscripcion->pausar(7, 'Viaje'));

        $pausa = $this->enCola($inscripcion->fresh(), TipoNotificacion::PAUSA_INSCRIPCION);

        $this->assertTrue((bool) $inscripcion->fresh()->reanudar());

        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $pausa->fresh()->id_estado);
    }

    // ── 4. El tope del día aplaza, no mata ────────────────────────────────

    public function test_el_tope_del_dia_deja_el_correo_para_manana_sin_gastar_intentos(): void
    {
        // El servicio de verdad: el tope frena antes de tocar la red.
        Ajustes::guardar(['correo.tope_diario' => '10']);
        Cache::put('correos-enviados:' . now()->toDateString(), 10, now()->endOfDay());

        $fila = $this->enCola($this->inscripcion(), TipoNotificacion::MEMBRESIA_POR_VENCER);

        $resultado = $this->servicio()->enviarPendientes();

        $fila->refresh();
        $this->assertSame(1, $resultado['aplazadas']);
        $this->assertSame(Notificacion::ESTADO_PENDIENTE, (int) $fila->id_estado);
        $this->assertSame(0, (int) $fila->intentos);
        $this->assertSame(today()->addDay()->toDateString(), $fila->fecha_programada->toDateString());
    }

    public function test_lo_que_fallo_en_esta_corrida_no_se_reintenta_en_el_acto(): void
    {
        $doble = Mockery::mock(CorreoService::class);
        $doble->shouldReceive('enviar')->once()->andThrow(new RuntimeException('SMTP caído'));
        $doble->shouldReceive('cerrar');
        $this->app->instance(CorreoService::class, $doble);

        $fila = $this->enCola($this->inscripcion(), TipoNotificacion::MEMBRESIA_POR_VENCER);

        // Lo mismo que hace `notificaciones:enviar --todo`, una cosa tras otra.
        $servicio = $this->servicio();
        $servicio->enviarPendientes();
        $servicio->reintentarFallidas();

        $this->assertSame(1, (int) $fila->fresh()->intentos, 'Un solo intento: el reintento espera a la próxima corrida.');
    }

    public function test_lo_que_fallo_hace_rato_si_se_reintenta(): void
    {
        $this->fingirCorreo();

        $fila = $this->enCola($this->inscripcion(), TipoNotificacion::MEMBRESIA_POR_VENCER, [
            'id_estado' => Notificacion::ESTADO_FALLIDO,
            'intentos' => 1,
        ]);
        DB::table('notificaciones')->where('id', $fila->id)->update(['updated_at' => now()->subHours(2)]);

        $this->assertSame(1, $this->servicio()->reintentarFallidas()['reenviadas']);
    }

    // ── 5. Papelera y bajas ───────────────────────────────────────────────

    public function test_no_se_le_escribe_a_quien_esta_en_la_papelera_o_dado_de_baja(): void
    {
        $this->fingirCorreo();

        $papelera = $this->inscripcion();
        $enPapelera = $this->enCola($papelera, TipoNotificacion::MEMBRESIA_POR_VENCER);
        $papelera->cliente->delete();

        $baja = $this->inscripcion();
        $deBaja = $this->enCola($baja, TipoNotificacion::MEMBRESIA_POR_VENCER);
        // El de «venció» sí: la tarea nocturna da de baja justo a quien vence.
        $vencida = $this->enCola($baja, TipoNotificacion::MEMBRESIA_VENCIDA);
        // Y lo que alguien escribió a mano a un socio inactivo, también.
        $manual = $this->enCola($baja, TipoNotificacion::MEMBRESIA_POR_VENCER, ['tipo_envio' => 'manual']);
        $baja->cliente->update(['activo' => false]);

        $this->servicio()->enviarPendientes();

        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $enPapelera->fresh()->id_estado);
        $this->assertStringContainsString('papelera', $enPapelera->fresh()->error_mensaje);
        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $deBaja->fresh()->id_estado);
        $this->assertSame(Notificacion::ESTADO_ENVIADO, (int) $vencida->fresh()->id_estado);
        $this->assertSame(Notificacion::ESTADO_ENVIADO, (int) $manual->fresh()->id_estado);
        $this->assertCount(2, $this->mandados);
    }

    // ── 6. Texto de ejemplo entre corchetes ───────────────────────────────

    public function test_una_plantilla_con_texto_de_ejemplo_no_se_manda_ni_se_ofrece(): void
    {
        $this->fingirCorreo();
        $socio = $this->inscripcion()->cliente;
        $promocion = $this->tipo('promocion');

        try {
            app(EnvioManualService::class)->enviar($socio, $promocion);
            $this->fail('Una promoción con «[XX%]» no puede salir.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('[XX%]', collect($e->errors())->flatten()->first());
        }

        $this->assertSame([], $this->mandados);

        $plantillas = $this->actingAs($this->administrador())->get('/panel/notificaciones/enviar')
            ->viewData('page')['props']['plantillas'];
        $codigos = TipoNotificacion::whereIn('id', collect($plantillas)->pluck('id'))->pluck('codigo')->all();

        $this->assertNotContains('promocion', $codigos);
        $this->assertNotContains('evento', $codigos);
        $this->assertContains(TipoNotificacion::MEMBRESIA_POR_VENCER, $codigos);
    }

    public function test_los_corchetes_de_outlook_y_de_los_estilos_no_cuentan(): void
    {
        $html = '<style>a[x-apple-data-detectors]{color:inherit}</style><!--[if mso]><table><![endif]--><p>Hola</p>';

        $this->assertSame([], EnvioManualService::marcadoresSinEditar($html));
        $this->assertSame(['[NOMBRE DEL EVENTO]'], EnvioManualService::marcadoresSinEditar('<h1>[NOMBRE DEL EVENTO]</h1>'));
    }

    public function test_no_se_reenvia_un_correo_con_texto_de_ejemplo(): void
    {
        $this->fingirCorreo();
        $fila = $this->enCola($this->inscripcion(), TipoNotificacion::MEMBRESIA_POR_VENCER, [
            'contenido' => '<p>Hasta [XX%] de descuento</p>',
            'id_estado' => Notificacion::ESTADO_FALLIDO,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(EnvioManualService::class)->reenviar($fila);
        } finally {
            $this->assertSame([], $this->mandados);
        }
    }

    // ── El texto de muestra de las plantillas viejas ──────────────────────

    public function test_un_aviso_automatico_con_el_socio_de_ejemplo_no_sale(): void
    {
        $this->fingirCorreo();
        $this->tipo(TipoNotificacion::BIENVENIDA)->update([
            'plantilla_email' => '<p>Hola Juan Pérez, tu saldo es $$25.000</p>',
        ]);

        $resultado = $this->servicio()->enviarNotificacionBienvenida($this->inscripcion());

        $this->assertFalse($resultado['enviada']);
        $this->assertSame([], $this->mandados);

        $fila = Notificacion::firstOrFail();
        $this->assertSame(Notificacion::ESTADO_FALLIDO, (int) $fila->id_estado);
        $this->assertStringContainsString('texto de ejemplo «Juan Pérez»', $fila->error_mensaje);
        $this->assertSame((int) $fila->max_intentos, (int) $fila->intentos, 'Reintentarlo mandaría lo mismo.');
    }

    public function test_el_envio_a_mano_rechaza_la_plantilla_con_el_socio_de_ejemplo(): void
    {
        $this->fingirCorreo();
        $plantilla = $this->tipo(TipoNotificacion::MEMBRESIA_POR_VENCER);
        $plantilla->update(['plantilla_email' => '<p>Hola Juan Pérez</p>']);

        try {
            app(EnvioManualService::class)->enviar($this->inscripcion()->cliente, $plantilla);
            $this->fail('Salió con el nombre del ejemplo.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Juan Pérez', collect($e->errors())->flatten()->first());
        }

        $this->assertSame([], $this->mandados);
    }

    public function test_un_socio_que_se_llama_como_el_ejemplo_si_recibe_su_aviso(): void
    {
        $this->fingirCorreo();

        // La plantilla está bien: el nombre es el del socio de verdad.
        $inscripcion = $this->inscripcion(['nombres' => 'Juan', 'apellido_paterno' => 'Pérez']);

        $this->assertTrue($this->servicio()->enviarNotificacionBienvenida($inscripcion)['enviada']);
    }

    // ── 6 y 7. El envío a un grupo, sin huecos ────────────────────────────

    public function test_el_envio_a_un_grupo_rechaza_variables_desconocidas_y_texto_de_ejemplo(): void
    {
        $this->fingirCorreo();
        $this->inscripcion();
        $this->actingAs($this->administrador());

        foreach (['<p>Hola {nombre}, tu saldo es {saldo_pendiente}</p>', '<p>Hola {nombre}: [XX%] de descuento</p>'] as $mensaje) {
            try {
                app(EnvioMasivoService::class)->enviar('todos', 'Aviso', $mensaje);
                $this->fail('No puede salir a medio escribir: ' . $mensaje);
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('mensaje', $e->errors());
            }

            $this->postJson('/panel/notificaciones/masivo/vista-previa', [
                'grupo' => 'todos',
                'asunto' => 'Aviso',
                'mensaje' => $mensaje,
            ])->assertStatus(422)->assertJsonStructure(['error']);
        }

        $this->assertSame([], $this->mandados);
        $this->assertSame(0, Notificacion::count());
    }

    // ── 8. Sin correos en el registro ─────────────────────────────────────

    public function test_el_registro_no_guarda_la_direccion_del_socio(): void
    {
        $this->fingirCorreo();
        Log::spy();

        $fila = $this->enCola($this->inscripcion(['email' => 'privado@correo.cl']), TipoNotificacion::MEMBRESIA_POR_VENCER);

        $this->servicio()->enviarPendientes();

        Log::shouldHaveReceived('info')
            ->withArgs(fn ($mensaje, $contexto = []) => $mensaje === 'Notificación enviada'
                && $contexto['id'] === $fila->id
                && ! in_array('privado@correo.cl', $contexto, true))
            ->once();
    }

    // ── 9. Menores: siempre por el apoderado ──────────────────────────────

    public function test_el_correo_de_un_menor_va_al_apoderado_por_todos_los_caminos(): void
    {
        $this->fingirCorreo();

        // Sin correo propio: antes los automáticos lo saltaban.
        $inscripcion = $this->inscripcion([
            'email' => null,
            'es_menor_edad' => true,
            'apoderado_email' => 'carla@correo.cl',
        ]);

        $aviso = $this->servicio()->crearNotificacion($this->tipo(TipoNotificacion::PAUSA_INSCRIPCION), $inscripcion);
        $this->assertSame('carla@correo.cl', $aviso->email_destino);

        // Y el envío a mano, que mandaba siempre al correo del socio.
        $conCorreo = $this->inscripcion([
            'email' => 'tomas@correo.cl',
            'es_menor_edad' => true,
            'apoderado_email' => 'carla@correo.cl',
        ]);

        $manual = app(EnvioManualService::class)->enviar($conCorreo->cliente, $this->tipo(TipoNotificacion::MEMBRESIA_POR_VENCER));

        $this->assertSame('carla@correo.cl', $manual->email_destino);
        $this->assertSame(['carla@correo.cl'], array_column($this->mandados, 'para'));
    }

    // ── 10. Solo se reenvía lo que falló ──────────────────────────────────

    public function test_no_se_reenvia_un_correo_cancelado_ni_uno_pendiente(): void
    {
        $this->fingirCorreo();
        $inscripcion = $this->inscripcion();

        foreach ([Notificacion::ESTADO_CANCELADO, Notificacion::ESTADO_PENDIENTE] as $estado) {
            $fila = $this->enCola($inscripcion, TipoNotificacion::MEMBRESIA_POR_VENCER, ['id_estado' => $estado]);

            try {
                app(EnvioManualService::class)->reenviar($fila);
                $this->fail("Se reenvió uno en estado {$estado}.");
            } catch (ValidationException $e) {
                $this->assertStringContainsString('no salió', collect($e->errors())->flatten()->first());
            }

            $this->actingAs($this->administrador())
                ->post("/panel/notificaciones/{$fila->uuid}/reenviar")
                ->assertSessionHas('error');
        }

        $this->assertSame([], $this->mandados);
    }
}
