<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\HistorialCambio;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\CasoConCatalogos;

/**
 * Pausas y vencimientos: lo que se rompía en los bordes.
 *
 * Doble clic, dos pestañas, la pausa que termina sola, el cambio de horario de
 * Chile, la pausada que la ficha daba por «vencida» y la que la web pública
 * daba por «sin membresía». Cada prueba es un caso que fallaba.
 */
class PausasSinErroresTest extends CasoConCatalogos
{
    /** Activa, del plan Mensual (1 pausa), que vence en $quedan días. */
    private function activa(int $quedan, array $mas = []): Inscripcion
    {
        $socio = Cliente::factory()->create(['activo' => true, 'email' => 'socio@correo.cl']);

        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => today()->subDays(30 - $quedan),
            'fecha_vencimiento' => today()->addDays($quedan),
            'pausada' => false,
            'pausas_realizadas' => 0,
            'max_pausas_permitidas' => 1,
            'precio_base' => 40000,
            'descuento_aplicado' => 0,
            'precio_final' => 40000,
            ...$mas,
        ]);
    }

    /** Pausada hace $haceDias por $duracion días, con $quedaban días guardados. */
    private function pausada(int $haceDias, int $duracion, int $quedaban): Inscripcion
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $inicio = today()->subDays($haceDias);

        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 101,
            'pausada' => true,
            'pausa_indefinida' => false,
            'dias_pausa' => $duracion,
            'dias_restantes_al_pausar' => $quedaban,
            'fecha_pausa_inicio' => $inicio,
            'fecha_pausa_fin' => $inicio->copy()->addDays($duracion),
            // La fecha que tenía al pausar: queda atrás mientras dura la pausa.
            'fecha_vencimiento' => $inicio->copy()->addDays($quedaban),
            'pausas_realizadas' => 1,
            'max_pausas_permitidas' => 2,
            'precio_base' => 40000,
            'descuento_aplicado' => 0,
            'precio_final' => 40000,
        ]);
    }

    // ---------- Doble clic y dos pestañas ----------

    /**
     * Dos pestañas abiertas con la misma membresía activa: las dos pulsan
     * «Pausar». La segunda llega con la membresía vieja en la mano y pausaba
     * otra vez: dos filas de historial y los días guardados reescritos.
     */
    public function test_pausar_desde_dos_pestanas_pausa_una_sola_vez(): void
    {
        $inscripcion = $this->activa(quedan: 20, mas: ['max_pausas_permitidas' => 3]);
        $otraPestana = Inscripcion::find($inscripcion->id);

        $this->assertTrue($inscripcion->pausar(7, 'Viaje'));
        $this->assertFalse($otraPestana->pausar(7, 'Viaje'), 'La segunda pestaña volvió a pausar la membresía ya pausada.');

        $inscripcion->refresh();
        $this->assertSame(1, $inscripcion->pausas_realizadas);
        $this->assertSame(1, HistorialCambio::where('inscripcion_id', $inscripcion->id)->where('tipo_cambio', 'pausa')->count());
    }

    /** Lo mismo al reanudar: una sola reanudación, una sola fila. */
    public function test_reanudar_desde_dos_pestanas_reanuda_una_sola_vez(): void
    {
        $inscripcion = $this->pausada(haceDias: 3, duracion: 10, quedaban: 20);
        $otraPestana = Inscripcion::find($inscripcion->id);

        $this->assertTrue($inscripcion->reanudar());
        $this->assertFalse($otraPestana->reanudar(), 'La segunda pestaña volvió a reanudar la membresía.');

        $this->assertSame(1, HistorialCambio::where('inscripcion_id', $inscripcion->id)->where('tipo_cambio', 'reanudacion')->count());
        $this->assertSame(today()->addDays(20)->toDateString(), $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    /** Y la pantalla no dice «pausada» si no pausó nada. */
    public function test_el_segundo_clic_de_pausar_no_responde_exito(): void
    {
        $inscripcion = $this->activa(quedan: 20, mas: ['max_pausas_permitidas' => 3]);
        $admin = $this->administrador();

        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/pausar", ['dias_pausa' => 7])->assertOk();
        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/pausar", ['dias_pausa' => 7])->assertStatus(422);
        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/reanudar")->assertOk();
        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/reanudar")->assertStatus(422);

        $this->assertSame(1, $inscripcion->refresh()->pausas_realizadas);
    }

    // ---------- El historial ----------

    /**
     * El historial de la pausa guardaba los días y la fecha de fin vacíos:
     * pausar() los mandaba con un nombre y registrarPausa() los leía con otro.
     */
    public function test_el_historial_de_la_pausa_guarda_dias_y_fin(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
        $inscripcion = $this->activa(quedan: 20);

        $inscripcion->pausar(7, 'Viaje');

        $fila = HistorialCambio::where('inscripcion_id', $inscripcion->id)->where('tipo_cambio', 'pausa')->sole();
        $this->assertSame(7, $fila->detalles['dias_pausa']);
        $this->assertSame('2026-10-12', $fila->detalles['fecha_fin_prevista']);
    }

    // ---------- La pausa que termina sola ----------

    /**
     * La pausa de 7 días termina el día 7: ese día el socio vuelve. La revisión
     * solo reanudaba las que terminaron ANTES de hoy, y el socio pasaba su
     * primer día de vuelta todavía «en pausa».
     */
    public function test_la_pausa_que_termina_hoy_se_reanuda_hoy(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
        $inscripcion = $this->activa(quedan: 20);
        $inscripcion->pausar(7, 'Viaje');

        $this->travelTo(Carbon::parse('2026-10-12 08:00'));
        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();

        $inscripcion->refresh();
        $this->assertSame(100, (int) $inscripcion->id_estado, 'El día en que termina la pausa sigue pausada.');
        $this->assertSame('2026-11-01', $inscripcion->fecha_vencimiento->toDateString());
    }

    /**
     * Una pausa olvidada que, al reanudarse desde su fin, ya venció: tiene que
     * quedar Vencida en la misma revisión, no un día entero «Activa» con la
     * fecha en el pasado (las vencidas se marcaban ANTES de reanudar).
     */
    public function test_la_pausa_olvidada_que_ya_vencio_queda_vencida_en_la_misma_revision(): void
    {
        $inscripcion = $this->pausada(haceDias: 40, duracion: 7, quedaban: 3);

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();

        $inscripcion->refresh();
        $this->assertFalse((bool) $inscripcion->pausada);
        $this->assertSame(102, (int) $inscripcion->id_estado);
    }

    // ---------- Qué se puede pausar ----------

    /** Vencida en la fecha pero todavía «Activa» porque la revisión no pasó. */
    public function test_no_se_pausa_una_que_ya_vencio(): void
    {
        $inscripcion = $this->activa(quedan: -2);

        $this->assertFalse($inscripcion->pausar(7, 'Viaje'));
        $this->assertSame(100, (int) $inscripcion->refresh()->id_estado);
    }

    /**
     * Pausar el último día: le queda ese día. Se guardaban 0 días, y al
     * reanudar el vencimiento se quedaba en la fecha vieja, ya pasada.
     */
    public function test_pausar_el_ultimo_dia_le_guarda_ese_dia(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
        $inscripcion = $this->activa(quedan: 0);
        $this->assertTrue($inscripcion->pausar(7, 'Lesión'));

        $this->travelTo(Carbon::parse('2026-10-08 10:00'));
        $inscripcion->refresh()->reanudar();

        $this->assertSame('2026-10-08', $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    /** Con un día por delante, al reanudar le queda ese día. */
    public function test_pausar_la_que_vence_manana(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
        $inscripcion = $this->activa(quedan: 1);
        $inscripcion->pausar(7, 'Lesión');

        $this->travelTo(Carbon::parse('2026-10-12 10:00'));
        $inscripcion->refresh()->reanudar();

        $this->assertSame('2026-10-13', $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    /** El plan Mensual permite una pausa: la segunda se rechaza. */
    public function test_no_se_pausa_mas_veces_que_las_del_plan(): void
    {
        $inscripcion = $this->activa(quedan: 20);
        $admin = $this->administrador();

        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/pausar", ['dias_pausa' => 3])->assertOk();
        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/reanudar")->assertOk();
        $this->actingAs($admin)->postJson("/panel/inscripciones/{$inscripcion->uuid}/pausar", ['dias_pausa' => 3])->assertStatus(422);

        $this->assertSame(1, $inscripcion->refresh()->pausas_realizadas);
    }

    /** Pausar y reanudar el mismo día no mueve el vencimiento. */
    public function test_pausar_y_reanudar_el_mismo_dia_no_cambia_nada(): void
    {
        $inscripcion = $this->activa(quedan: 12);
        $antes = $inscripcion->fecha_vencimiento->toDateString();

        $inscripcion->pausar(10, 'Error');
        $inscripcion->refresh()->reanudar();

        $this->assertSame($antes, $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    // ---------- El calendario ----------

    /**
     * El domingo en que Chile adelanta la hora, ese día empieza a la 01:00 y
     * dura 23 horas. Contar los días por horas daba uno menos: 9,96 → 9. El
     * socio que pausaba ese domingo perdía un día, y la ficha decía «vence hoy»
     * a quien le quedaba uno.
     */
    public function test_el_cambio_de_hora_de_septiembre_no_se_come_un_dia(): void
    {
        $this->travelTo(Carbon::parse('2026-09-06 12:00'));
        $inscripcion = $this->activa(quedan: 10);
        $this->assertSame('2026-09-16', $inscripcion->fecha_vencimiento->toDateString());

        $this->assertSame(10, $inscripcion->dias_restantes);

        $inscripcion->pausar(5, 'Viaje');
        $this->assertSame(10, $inscripcion->refresh()->dias_restantes_al_pausar);
    }

    /** Y vencida ayer, el lunes después del cambio, es vencida y no «vence hoy». */
    public function test_vencida_el_domingo_del_cambio_de_hora_no_vence_hoy_el_lunes(): void
    {
        $this->travelTo(Carbon::parse('2026-09-06 12:00'));
        $inscripcion = $this->activa(quedan: 0);

        $this->travelTo(Carbon::parse('2026-09-07 09:00'));

        $this->assertSame(-1, $inscripcion->refresh()->dias_restantes);
    }

    /** En abril el día dura 25 horas: tampoco se regala ni se quita nada. */
    public function test_el_cambio_de_hora_de_abril(): void
    {
        $this->travelTo(Carbon::parse('2027-04-03 12:00'));
        $inscripcion = $this->activa(quedan: 10);
        $inscripcion->pausar(5, 'Viaje');
        $this->assertSame(10, $inscripcion->refresh()->dias_restantes_al_pausar);

        $this->travelTo(Carbon::parse('2027-04-08 12:00'));
        $inscripcion->reanudar();

        $this->assertSame('2027-04-18', $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    /** Una pausa que cruza el 29 de febrero y el cambio de mes. */
    public function test_la_pausa_que_cruza_el_29_de_febrero(): void
    {
        $this->travelTo(Carbon::parse('2028-02-27 10:00'));
        $inscripcion = $this->activa(quedan: 4); // vence el 2 de marzo
        $inscripcion->pausar(5, 'Viaje');
        $this->assertSame(4, $inscripcion->refresh()->dias_restantes_al_pausar);

        $this->travelTo(Carbon::parse('2028-03-05 10:00'));
        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();

        // La pausa terminaba el 3 de marzo: 3 + 4 = 7 de marzo.
        $this->assertSame('2028-03-07', $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    // ---------- Pausada no es vencida ----------

    /**
     * Mientras dura la pausa la fecha de vencimiento vieja queda atrás. La
     * ficha restaba contra esa fecha y decía «Venció hace 10 días» de una
     * membresía en pausa con 20 días guardados.
     */
    public function test_la_ficha_de_una_pausada_no_dice_vencio(): void
    {
        $inscripcion = $this->pausada(haceDias: 30, duracion: 60, quedaban: 20);

        $props = $this->actingAs($this->administrador())
            ->get("/panel/inscripciones/{$inscripcion->uuid}")
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(20, $props['inscripcion']['dias']);
        $this->assertSame(20, $inscripcion->dias_restantes);
        $this->assertFalse($inscripcion->esta_vencida);
    }

    /** Se puede traspasar una pausada aunque la fecha vieja haya pasado. */
    public function test_se_puede_traspasar_una_pausada_con_la_fecha_vieja_pasada(): void
    {
        $inscripcion = $this->pausada(haceDias: 30, duracion: 60, quedaban: 20);

        $this->assertTrue($inscripcion->puedeTraspasarse(true));
    }

    /**
     * Quien tiene una membresía en pausa ya tiene una: no puede recibir otra
     * por traspaso. Se miraba la fecha vieja y se lo daba por libre.
     */
    public function test_quien_tiene_una_pausada_no_recibe_un_traspaso(): void
    {
        $pausada = $this->pausada(haceDias: 30, duracion: 60, quedaban: 20);

        $this->assertFalse(Inscripcion::clientePuedeRecibirTraspaso($pausada->id_cliente));
    }

    /** Ni se desactiva al socio en pausa, aunque tenga una vieja vencida. */
    public function test_al_socio_en_pausa_no_se_le_da_de_baja(): void
    {
        $pausada = $this->pausada(haceDias: 30, duracion: 60, quedaban: 20);
        Inscripcion::factory()->create([
            'id_cliente' => $pausada->id_cliente,
            'id_membresia' => 4,
            'id_estado' => 102,
            'fecha_inicio' => today()->subDays(90),
            'fecha_vencimiento' => today()->subDays(60),
        ]);

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();
        $this->artisan('clientes:desactivar-vencidos')->assertSuccessful();

        $this->assertSame(101, (int) $pausada->refresh()->id_estado);
        $this->assertTrue((bool) Cliente::find($pausada->id_cliente)->activo);
    }

    /**
     * «Mi membresía» en la web: el socio en pausa leía «Sin membresía activa»
     * y «Estás al día» aunque debiera.
     */
    public function test_mi_membresia_muestra_la_pausa_y_lo_que_debe(): void
    {
        Cache::flush();
        $pausada = $this->pausada(haceDias: 30, duracion: 60, quedaban: 20);
        $pausada->cliente->update(['run_pasaporte' => '12345678-5', 'celular' => '912345678', 'nombres' => 'Camila']);

        $datos = $this->postJson('/consultar-membresia', ['tipo' => 'rut', 'rut' => '12.345.678-5', 'digitos' => '5678'])
            ->assertOk()
            ->json('data');

        $this->assertSame('Mensual', $datos['membresia']);
        $this->assertStringContainsString('Pausada', $datos['estado']);
        $this->assertSame(20, $datos['dias_restantes']);
        $this->assertSame(40000, $datos['saldo']);
    }

    // ---------- Los avisos ----------

    /**
     * Un aviso de vencimiento que quedó esperando —o que falló y se reintenta
     * cada noche— salía igual después de pausar: «tu membresía vence en 7
     * días» a quien la tiene congelada.
     */
    public function test_al_pausar_se_cancelan_los_avisos_de_vencimiento(): void
    {
        $inscripcion = $this->activa(quedan: 7);
        $tipo = TipoNotificacion::firstOrCreate(['codigo' => 'membresia_por_vencer'], [
            'nombre' => 'Membresía por vencer', 'asunto_email' => 'Vence pronto',
            'plantilla_email' => '<p>x</p>', 'dias_anticipacion' => 7, 'activo' => true,
        ]);
        $aviso = fn (int $estado) => Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => $inscripcion->id_cliente,
            'id_inscripcion' => $inscripcion->id,
            'email_destino' => 'socio@correo.cl',
            'asunto' => 'Vence pronto',
            'contenido' => '<p>Hola</p>',
            'id_estado' => $estado,
            'fecha_programada' => today(),
        ]);
        $pendiente = $aviso(Notificacion::ESTADO_PENDIENTE);
        $fallido = $aviso(Notificacion::ESTADO_FALLIDO);

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/pausar", ['dias_pausa' => 7])
            ->assertOk();

        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $pendiente->refresh()->id_estado);
        $this->assertSame(Notificacion::ESTADO_CANCELADO, (int) $fallido->refresh()->id_estado);
    }

    // ---------- Otras acciones sobre una pausada ----------

    public function test_una_pausada_no_cambia_de_plan(): void
    {
        $inscripcion = $this->pausada(haceDias: 3, duracion: 10, quedaban: 20);

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$inscripcion->uuid}/cambiar-plan", [
                'id_membresia_nueva' => 1,
                'id_metodo_pago' => 1,
            ])
            ->assertStatus(422);

        $this->assertSame(101, (int) $inscripcion->refresh()->id_estado);
    }

    /**
     * Borrada y restaurada desde la papelera vuelve con su pausa intacta, y la
     * revisión la reanuda desde el fin de la pausa como a cualquier otra.
     */
    public function test_una_pausada_borrada_y_restaurada_conserva_su_pausa(): void
    {
        $inscripcion = $this->pausada(haceDias: 3, duracion: 5, quedaban: 20);
        $fin = $inscripcion->fecha_pausa_fin->copy();
        $admin = $this->administrador();

        $this->actingAs($admin)->delete("/panel/inscripciones/{$inscripcion->uuid}");
        $this->assertSoftDeleted($inscripcion);

        $this->travel(5)->days();
        $this->actingAs($admin)->patch("/panel/papelera/inscripciones/{$inscripcion->id}/restaurar");

        $inscripcion->refresh();
        $this->assertSame(101, (int) $inscripcion->id_estado);
        $this->assertSame(20, $inscripcion->dias_restantes_al_pausar);

        $this->artisan('inscripciones:actualizar-estados')->assertSuccessful();
        $this->assertSame($fin->copy()->addDays(20)->toDateString(), $inscripcion->refresh()->fecha_vencimiento->toDateString());
    }

    /**
     * Si mientras estaba en la papelera el socio sacó otra membresía, la
     * pausada restaurada no se puede reanudar: quedaría con dos vigentes.
     */
    public function test_no_se_reanuda_si_el_socio_ya_tiene_otra_vigente(): void
    {
        $inscripcion = $this->pausada(haceDias: 3, duracion: 10, quedaban: 20);
        Inscripcion::factory()->create([
            'id_cliente' => $inscripcion->id_cliente,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => today(),
            'fecha_vencimiento' => today()->addDays(29),
            'pausada' => false,
        ]);

        $this->assertNotNull($inscripcion->porQueNoSePuedeReanudar());
        $this->assertFalse($inscripcion->reanudar());
    }

    /** Pero un pase diario comprado durante la pausa no la bloquea. */
    public function test_un_pase_diario_no_bloquea_reanudar(): void
    {
        $inscripcion = $this->pausada(haceDias: 3, duracion: 10, quedaban: 20);
        Inscripcion::factory()->create([
            'id_cliente' => $inscripcion->id_cliente,
            'id_membresia' => 5, // Pase Diario
            'id_estado' => 100,
            'fecha_inicio' => today(),
            'fecha_vencimiento' => today(),
            'pausada' => false,
        ]);

        $this->assertTrue($inscripcion->reanudar());
    }
}
