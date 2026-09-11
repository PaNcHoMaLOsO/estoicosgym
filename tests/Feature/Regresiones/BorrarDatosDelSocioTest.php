<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\LogNotificacion;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Borrar los datos personales de un socio (Ley 21.719) sin descuadrar las
 * cuentas.
 *
 * Lo que se vigila: que se vaya todo lo que dice quién era, que sus membresías
 * y pagos sigan con sus montos, que no se pueda mientras use el gimnasio o
 * deba plata, y que después la ficha no se pueda volver a llenar.
 */
class BorrarDatosDelSocioTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * Un ex socio con todo lo que puede tener encima: foto, una membresía
     * vencida y pagada, un correo que se le mandó y un contrato firmado.
     *
     * @return array<string,mixed>
     */
    private function exSocio(): array
    {
        Storage::disk('public')->put('clientes/camila.jpg', 'foto');

        $cliente = Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Soto',
            'run_pasaporte' => '12.345.678-5',
            'email' => 'camila@correo.cl',
            'celular' => '912345678',
            'direccion' => 'Colón 123',
            'observaciones' => 'Lesión de rodilla',
            'foto_perfil' => 'clientes/camila.jpg',
            'consentimiento_imagen' => true,
        ]);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'precio_base' => 40000,
            'precio_final' => 40000,
            'observaciones' => 'Le hicimos precio por su hermana',
            'fecha_inicio' => now()->subMonths(2),
            'fecha_vencimiento' => now()->subMonth(),
        ]);

        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => 40000,
            'monto_abonado' => 40000,
            'monto_pendiente' => 0,
            'id_estado' => EstadosCodigo::PAGO_PAGADO,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->subMonths(2)->format('Y-m-d'),
            'observaciones' => 'Transferencia de Camila Rojas',
        ]);

        $correo = Notificacion::create([
            'id_tipo_notificacion' => TipoNotificacion::first()->id,
            'id_cliente' => $cliente->id,
            'email_destino' => 'camila@correo.cl',
            'asunto' => 'Hola Camila',
            'contenido' => '<p>Hola Camila Rojas</p>',
            'id_estado' => Notificacion::ESTADO_ENVIADO,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
        ]);
        $correo->registrarLog('enviada', 'Enviado a camila@correo.cl');

        $contrato = Contrato::create([
            'id_cliente' => $cliente->id,
            'token_hash' => hash('sha256', 'un-enlace'),
            'firmante_tipo' => 'socio',
            'email_destino' => 'camila@correo.cl',
            'vence_en' => now()->subMonths(2)->addDays(7),
            'firmado_en' => now()->subMonths(2),
            'firmante_nombre' => 'Camila Rojas Soto',
            'firmante_rut' => '12.345.678-5',
            'ip' => '10.0.0.1',
            'contenido' => '<p>Contrato de Camila Rojas</p>',
            'huella' => hash('sha256', '<p>Contrato de Camila Rojas</p>'),
        ]);

        return compact('cliente', 'inscripcion', 'pago', 'correo', 'contrato');
    }

    private function borrar(Cliente $cliente, array $datos = [], $usuario = null)
    {
        return $this->actingAs($usuario ?? $this->administrador())
            ->post("/panel/clientes/{$cliente->uuid}/borrar-datos", $datos + [
                'motivo' => 'solicitud',
                'confirmacion' => 'BORRAR',
            ]);
    }

    /** EL QUE IMPORTA: se va lo personal y las cuentas quedan igual. */
    public function test_se_va_lo_personal_y_las_cuentas_quedan_igual(): void
    {
        ['cliente' => $cliente, 'inscripcion' => $inscripcion, 'pago' => $pago, 'correo' => $correo, 'contrato' => $contrato] = $this->exSocio();
        $ingresos = (int) Pago::ingresos()->sum('monto_abonado');

        $this->borrar($cliente)->assertSessionHas('success');

        $cliente->refresh();
        $this->assertSame("Socio Borrado #{$cliente->id}", $cliente->nombre_completo);

        foreach (['run_pasaporte', 'email', 'celular', 'direccion', 'observaciones', 'foto_perfil'] as $campo) {
            $this->assertNull($cliente->{$campo}, "Quedó {$campo}.");
        }

        $this->assertFalse((bool) $cliente->activo);
        $this->assertNotNull($cliente->datos_borrados_en);
        $this->assertSame('solicitud', $cliente->datos_borrados_motivo);
        Storage::disk('public')->assertMissing('clientes/camila.jpg');

        // Las cuentas: la membresía y el pago siguen, con sus montos...
        $this->assertSame(40000, (int) $inscripcion->fresh()->precio_final);
        $this->assertSame(40000, (int) $pago->fresh()->monto_abonado);
        $this->assertSame($ingresos, (int) Pago::ingresos()->sum('monto_abonado'));

        // ...pero sin lo que alguien escribió a mano.
        $this->assertNull($inscripcion->fresh()->observaciones);
        $this->assertNull($pago->fresh()->observaciones);

        // Los correos que se le mandaron se van enteros.
        $this->assertNull(Notificacion::find($correo->id));
        $this->assertSame(0, LogNotificacion::where('id_notificacion', $correo->id)->count());

        // Del contrato queda la huella, sin el documento ni la firma.
        $contrato->refresh();
        $this->assertNull($contrato->contenido);
        $this->assertNull($contrato->firmante_nombre);
        $this->assertNull($contrato->firmante_rut);
        $this->assertNull($contrato->ip);
        $this->assertNotNull($contrato->huella);
        $this->assertSame('borrado', $contrato->estado());
    }

    /** Mientras use el gimnasio, el gimnasio necesita saber quién es. */
    public function test_no_se_puede_con_una_membresia_vigente(): void
    {
        $cliente = Cliente::factory()->create(['activo' => true]);
        Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
        ]);

        $this->borrar($cliente)->assertSessionHas('error');

        $this->assertNull($cliente->fresh()->datos_borrados_en);
    }

    /**
     * Ni si debe plata de una membresía vencida.
     *
     * Una membresía que nunca se pagó no tiene ni una fila en pagos: mirar
     * solo los pagos pendientes la dejaba pasar, y se borraba a un deudor.
     */
    public function test_no_se_puede_si_debe_una_membresia(): void
    {
        $cliente = Cliente::factory()->create(['activo' => false]);
        Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);

        $this->borrar($cliente)->assertSessionHas('error');

        $this->assertNull($cliente->fresh()->datos_borrados_en);
    }

    /** Ni si debe algo del mesón. */
    public function test_no_se_puede_si_debe_del_meson(): void
    {
        $cliente = Cliente::factory()->create(['activo' => false]);
        Fiado::create([
            'id_cliente' => $cliente->id,
            'concepto' => 'Bebida',
            'monto' => 1500,
            'id_usuario' => $this->administrador()->id,
        ]);

        $this->borrar($cliente)->assertSessionHas('error');

        $this->assertNull($cliente->fresh()->datos_borrados_en);
    }

    /** No se deshace: se confirma escribiendo la palabra. */
    public function test_hay_que_escribir_borrar_para_confirmar(): void
    {
        ['cliente' => $cliente] = $this->exSocio();

        $this->borrar($cliente, ['confirmacion' => 'sí'])->assertSessionHasErrors('confirmacion');

        $this->assertNull($cliente->fresh()->datos_borrados_en);
    }

    /** Recepción da de baja, pero borrar para siempre no le toca. */
    public function test_recepcion_no_puede(): void
    {
        ['cliente' => $cliente] = $this->exSocio();

        $this->borrar($cliente, [], $this->recepcionista())->assertForbidden();

        $this->assertNull($cliente->fresh()->datos_borrados_en);
    }

    /**
     * Después, la ficha no se vuelve a llenar.
     *
     * Editarla, reactivarla o mandarle un contrato le pondría otra vez un
     * nombre a pagos que ya no son de nadie.
     */
    public function test_despues_la_ficha_no_se_edita_ni_se_reactiva(): void
    {
        ['cliente' => $cliente] = $this->exSocio();
        $admin = $this->administrador();
        $this->borrar($cliente, [], $admin);

        $this->actingAs($admin)
            ->get("/panel/clientes/{$cliente->uuid}/editar")
            ->assertRedirect("/panel/clientes/{$cliente->uuid}");

        $this->actingAs($admin)
            ->patch("/panel/clientes/{$cliente->uuid}/reactivar")
            ->assertSessionHas('error');

        $this->assertFalse((bool) $cliente->fresh()->activo);

        $this->actingAs($admin)
            ->post("/panel/clientes/{$cliente->uuid}/contrato/enviar")
            ->assertSessionHas('error');

        // Ni se le arma un contrato: sería «Socio Borrado #N» firmando algo.
        $this->actingAs($admin)
            ->get("/panel/clientes/{$cliente->uuid}/contrato/ver")
            ->assertRedirect("/panel/clientes/{$cliente->uuid}");

        $this->assertSame(1, Contrato::where('id_cliente', $cliente->id)->count());
    }

    /** Y deja de aparecer entre los socios, también entre los dados de baja. */
    public function test_no_aparece_en_el_listado_de_socios(): void
    {
        ['cliente' => $cliente] = $this->exSocio();
        $admin = $this->administrador();
        $this->borrar($cliente, [], $admin);

        $bajas = $this->actingAs($admin)->get('/panel/clientes?bajas=1')->viewData('page')['props']['clientes']['data'];

        $this->assertNotContains($cliente->uuid, array_column($bajas, 'uuid'));
    }

    /** Su ficha se sigue abriendo —desde un pago antiguo—, y dice qué pasó. */
    public function test_su_ficha_dice_que_se_borraron(): void
    {
        ['cliente' => $cliente] = $this->exSocio();
        $admin = $this->administrador();
        $this->borrar($cliente, [], $admin);

        $props = $this->actingAs($admin)->get("/panel/clientes/{$cliente->uuid}")->assertOk()->viewData('page')['props'];

        $this->assertNotNull($props['cliente']['datos_borrados']);
        $this->assertSame(40000, $props['resumen']['pagado']);
    }
}
