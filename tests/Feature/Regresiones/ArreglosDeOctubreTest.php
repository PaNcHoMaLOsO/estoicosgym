<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use App\Models\User;
use App\Services\CorreoService;
use App\Services\NotificacionService;
use App\Support\Ajustes;
use App\Support\BusquedaDeSocio;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Una tanda de defectos reportados juntos: cada prueba nombra el suyo.
 */
class ArreglosDeOctubreTest extends CasoConCatalogos
{
    private function socio(string $nombres, string $paterno, array $extra = []): Cliente
    {
        return Cliente::factory()->create($extra + [
            'activo' => true,
            'nombres' => $nombres,
            'apellido_paterno' => $paterno,
            'apellido_materno' => 'Soto',
        ]);
    }

    private function membresia(Cliente $socio, int $estado, int $diasParaVencer, array $extra = []): Inscripcion
    {
        return Inscripcion::factory()->create($extra + [
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'fecha_inicio' => today()->addDays($diasParaVencer)->subMonth(),
            'fecha_vencimiento' => today()->addDays($diasParaVencer),
        ]);
    }

    /** «juan perez» es Juan Pérez, no todos los Juan y todos los Pérez. */
    public function test_buscar_varias_palabras_exige_todas(): void
    {
        $juanPerez = $this->socio('Juan', 'Perez');
        $this->socio('Juan', 'González');
        $this->socio('Pedro', 'Perez');

        $ids = BusquedaDeSocio::aplicar(Cliente::query(), 'juan perez')->pluck('id')->all();

        $this->assertSame([$juanPerez->id], $ids);

        // En otro orden, lo mismo.
        $ids = BusquedaDeSocio::aplicar(Cliente::query(), 'perez juan')->pluck('id')->all();
        $this->assertSame([$juanPerez->id], $ids);
    }

    /** El RUT entero sigue encontrándose aunque tenga puntos y guion. */
    public function test_el_rut_entero_sigue_encontrando(): void
    {
        $socio = $this->socio('Ana', 'Rojas', ['run_pasaporte' => '21.410.708-2']);
        $this->socio('Luis', 'Rojas', ['run_pasaporte' => '11.111.111-1']);

        $ids = BusquedaDeSocio::aplicar(Cliente::query(), '21.410.708-2')->pluck('id')->all();

        $this->assertSame([$socio->id], $ids);
    }

    /** La deuda calculada en la base da lo mismo que la de cada membresía. */
    public function test_la_deuda_en_sql_cuadra_con_la_de_cada_membresia(): void
    {
        $socio = $this->socio('Ana', 'Rojas');

        $debe = $this->membresia($socio, 100, 10, ['precio_base' => 30000, 'precio_final' => 30000]);
        $pagada = $this->membresia($socio, 102, -40, ['precio_base' => 20000, 'precio_final' => 20000]);
        $anulada = $this->membresia($socio, 102, -80, ['precio_base' => 15000, 'precio_final' => 15000]);

        Pago::factory()->create(['id_inscripcion' => $debe->id, 'monto_abonado' => 10000]);
        Pago::factory()->create(['id_inscripcion' => $pagada->id, 'monto_abonado' => 20000]);
        // Un pago anulado no cuenta: esa membresía sigue debiendo entera.
        Pago::factory()->create(['id_inscripcion' => $anulada->id, 'monto_abonado' => 15000])->delete();

        $conDeuda = Inscripcion::conDeuda();

        $this->assertEqualsCanonicalizing([$debe->id, $anulada->id], $conDeuda->pluck('id')->all());
        $this->assertSame(20000 + 15000, Inscripcion::porCobrar());
        $this->assertSame(Inscripcion::porCobrar(), (int) $conDeuda->sum(fn ($i) => $i->deuda));
    }

    /** Con los automáticos apagados, un correo a un grupo programado sale igual. */
    public function test_apagados_salen_los_programados_a_mano(): void
    {
        Ajustes::guardar(['tareas.correos_automaticos' => '0']);

        $doble = Mockery::mock(CorreoService::class);
        $doble->shouldReceive('enviar')->andReturn('smtp');
        $doble->shouldReceive('cerrar');
        $this->app->instance(CorreoService::class, $doble);
        $this->app->instance(NotificacionService::class, new NotificacionService($doble));
        // La orden ya se armó al arrancar, con el correo de verdad: se vuelve
        // a armar para que tome el doble.
        $this->app[\Illuminate\Contracts\Console\Kernel::class]->setArtisan(null);

        $socio = $this->socio('Ana', 'Rojas', ['email' => 'ana@progym.cl']);
        $tipo = TipoNotificacion::query()->value('id')
            ?? TipoNotificacion::create(['codigo' => 'prueba', 'nombre' => 'Prueba', 'asunto_email' => 'x', 'plantilla_email' => 'x'])->id;

        $datos = [
            'id_tipo_notificacion' => $tipo,
            'id_cliente' => $socio->id,
            'email_destino' => $socio->email,
            'asunto' => 'Cerramos el lunes',
            'contenido' => 'Hola',
            'id_estado' => Notificacion::ESTADO_PENDIENTE,
            'fecha_programada' => today(),
        ];

        $manual = Notificacion::create($datos + ['tipo_envio' => 'manual']);
        $automatica = Notificacion::create($datos + ['tipo_envio' => 'automatica']);

        $this->artisan('notificaciones:enviar --todo')->assertSuccessful();

        $this->assertSame(Notificacion::ESTADO_ENVIADO, (int) $manual->fresh()->id_estado, (string) $manual->fresh()->error_mensaje);
        $this->assertSame(Notificacion::ESTADO_PENDIENTE, (int) $automatica->fresh()->id_estado);
    }

    /** Socio en la papelera: «Renovar» no se ofrece ni revienta. */
    public function test_no_se_renueva_la_de_un_socio_en_la_papelera(): void
    {
        $socio = $this->socio('Ana', 'Rojas');
        $vencida = $this->membresia($socio, 102, -5);
        $socio->delete();

        $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$vencida->uuid}/renovar")
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** Un correo repetido con otras mayúsculas es un aviso, no un 500. */
    public function test_correo_repetido_con_mayusculas_avisa(): void
    {
        User::factory()->create(['email' => 'ana@progym.cl']);

        $this->actingAs($this->administrador())
            ->post('/panel/usuarios', [
                'nombre' => 'Ana',
                'email' => 'Ana@ProGym.cl',
                'id_rol' => 2,
                'clave' => 'clave-segura-1',
                'clave_confirmation' => 'clave-segura-1',
            ])
            ->assertSessionHasErrors('email');
    }

    /** Observaciones largas, escritas por el sistema, no traban la edición. */
    public function test_observaciones_largas_no_traban_la_edicion(): void
    {
        $socio = $this->socio('Ana', 'Rojas');
        $insc = $this->membresia($socio, 100, 20, [
            'precio_base' => 30000,
            'precio_final' => 30000,
            'descuento_aplicado' => 0,
            'observaciones' => str_repeat('Pausa vencida el 01/01/2026. ', 30),
        ]);

        $this->actingAs($this->administrador())
            ->put("/panel/inscripciones/{$insc->uuid}", [
                'fecha_inicio' => $insc->fecha_inicio->toDateString(),
                'fecha_vencimiento' => $insc->fecha_vencimiento->addDay()->toDateString(),
                'precio_base' => 30000,
                'descuento_aplicado' => 0,
                'observaciones' => $insc->observaciones,
                'form_submit_token' => uniqid('t', true),
            ])
            ->assertSessionDoesntHaveErrors('observaciones');
    }

    /** Restaurar una activa no deja al socio con dos vigentes. */
    public function test_restaurar_una_activa_no_duplica_la_vigente(): void
    {
        $socio = $this->socio('Ana', 'Rojas');
        $borrada = $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA, 15);
        $borrada->delete();
        $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA, 25);

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/inscripciones/{$borrada->id}/restaurar")
            ->assertSessionHas('error');

        $this->assertTrue($borrada->fresh()->trashed());
    }

    /** Sin otra vigente, se restaura como siempre. */
    public function test_restaurar_una_activa_sin_otra_vigente(): void
    {
        $socio = $this->socio('Ana', 'Rojas');
        $borrada = $this->membresia($socio, EstadosCodigo::INSCRIPCION_ACTIVA, 15);
        $borrada->delete();

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/inscripciones/{$borrada->id}/restaurar")
            ->assertSessionHas('success');

        $this->assertFalse($borrada->fresh()->trashed());
    }

    /** Un plan en la papelera no deja en blanco el nombre de lo vendido. */
    public function test_plan_en_la_papelera_conserva_el_nombre(): void
    {
        $insc = $this->membresia($this->socio('Ana', 'Rojas'), 102, -5);
        $nombre = $insc->membresia->nombre;

        $insc->membresia->delete();

        $this->assertSame($nombre, $insc->fresh()->membresia?->nombre);
    }
}
