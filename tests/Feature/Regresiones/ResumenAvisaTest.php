<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\TipoNotificacion;
use Tests\CasoConCatalogos;

/**
 * Dos cosas que el Resumen dice y que no son plata:
 *
 * - LOS AVISOS QUE NO SALIERON. En el mesón se da por avisado al socio; si el
 *   correo falló y nadie lo ve, se le trata como a alguien que ya sabía.
 * - LOS PLANES QUE EMPIEZAN PRONTO. La persona que llega el lunes por primera
 *   vez: conviene saber quién es antes de que entre.
 */
class ResumenAvisaTest extends CasoConCatalogos
{
    private function resumen(): array
    {
        return $this->actingAs($this->administrador())->get('/panel')->assertOk()->viewData('page')['props'];
    }

    private function correo(int $estado, string $para = 'socio@correo.cl'): Notificacion
    {
        $tipo = TipoNotificacion::first() ?? TipoNotificacion::create([
            'codigo' => 'prueba', 'nombre' => 'Prueba', 'asunto_email' => 'Prueba', 'plantilla_email' => '<p>x</p>', 'activo' => true,
        ]);

        return Notificacion::create([
            'id_tipo_notificacion' => $tipo->id,
            'id_cliente' => Cliente::factory()->create()->id,
            'email_destino' => $para,
            'asunto' => 'Tu membresía vence pronto',
            'contenido' => '<p>Hola</p>',
            'id_estado' => $estado,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
        ]);
    }

    public function test_cuenta_los_avisos_que_no_salieron(): void
    {
        $this->correo(602);
        $this->correo(602);
        $this->correo(Notificacion::ESTADO_ENVIADO);

        $this->assertSame(2, $this->resumen()['avisosFallidos']);
    }

    public function test_notificaciones_deja_ver_solo_las_fallidas(): void
    {
        $this->correo(602, 'fallo@correo.cl');
        $this->correo(Notificacion::ESTADO_ENVIADO, 'salio@correo.cl');

        $props = $this->actingAs($this->administrador())
            ->get('/panel/notificaciones?estado=fallidas')
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(['fallo@correo.cl'], collect($props['notificaciones']['data'])->pluck('email')->all());
        $this->assertSame('fallidas', $props['filtros']['estado']);

        // Y buscando dentro de las fallidas no se cuelan las que sí salieron.
        $props = $this->actingAs($this->administrador())
            ->get('/panel/notificaciones?estado=fallidas&buscar=membres')
            ->viewData('page')['props'];

        $this->assertSame(['fallo@correo.cl'], collect($props['notificaciones']['data'])->pluck('email')->all());
    }

    public function test_lista_los_planes_que_empiezan_pronto(): void
    {
        $inscribir = fn (string $nombre, $inicio) => Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['nombres' => $nombre, 'apellido_paterno' => 'Prueba', 'apellido_materno' => ''])->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => $inicio,
            'fecha_vencimiento' => now()->addMonths(2),
        ]);

        $inscribir('Lunes', today()->addDays(3));
        $inscribir('YaEmpezo', today()->subDay());
        $inscribir('Hoy', today());
        $inscribir('MuyLejos', today()->addDays(40));

        $porEmpezar = $this->resumen()['porEmpezar'];

        $this->assertCount(1, $porEmpezar);
        $this->assertStringStartsWith('Lunes', $porEmpezar[0]['socio']);
        $this->assertSame(3, $porEmpezar[0]['dias']);
        $this->assertSame(today()->addDays(3)->format('d/m/Y'), $porEmpezar[0]['fecha']);
    }

    /**
     * LO FIADO SE VE EN EL RESUMEN, no solo en su pantalla.
     *
     * Es lo que se cobra con la persona delante. Escondido en otra pantalla, la
     * cuenta de alguien que pasa todos los días se quedaba semanas sin cobrar.
     */
    public function test_el_resumen_dice_quien_debe_del_meson(): void
    {
        $socio = \App\Models\Cliente::factory()->create(['activo' => true, 'nombres' => 'Rosa', 'apellido_paterno' => 'Pinto']);

        \App\Models\Fiado::create([
            'id_cliente' => $socio->id,
            'concepto' => 'Barra de proteína',
            'monto' => 2500,
            'id_usuario' => $this->administrador()->id,
        ]);
        \App\Models\Fiado::create([
            'nombre' => 'El de la moto',
            'concepto' => 'Bebida',
            'monto' => 1500,
            'id_usuario' => $this->administrador()->id,
        ]);

        $fiado = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props']['fiado'];

        $this->assertSame(4000, $fiado['total']);
        $this->assertSame(2, $fiado['personas']);
        // Con el uuid del socio para poder abrir su ficha desde ahí.
        $this->assertContains(
            (string) $socio->uuid,
            collect($fiado['cuentas'])->pluck('socio_uuid')->map(fn ($u) => (string) $u)->all()
        );
    }

    /** Sin nada fiado, el resumen lo dice en cero y no revienta. */
    public function test_sin_fiado_el_resumen_sigue_abriendo(): void
    {
        $fiado = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props']['fiado'];

        $this->assertSame(['total' => 0, 'personas' => 0], ['total' => $fiado['total'], 'personas' => $fiado['personas']]);
        $this->assertSame([], $fiado['cuentas']);
    }
    /**
     * LOS CUMPLEAÑOS SALEN EN EL RESUMEN, y solo los de la semana.
     *
     * La fecha se pide al inscribirse y no se usaba para nada. Aquí se prueba
     * lo que puede salir mal: que aparezca uno de dentro de dos meses, que se
     * cuele un socio dado de baja, o que el de hoy no salga como «hoy».
     */
    public function test_el_resumen_dice_quien_cumple_anos_esta_semana(): void
    {
        $hoy = \Illuminate\Support\Carbon::today();

        $deHoy = \App\Models\Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Rocío',
            'fecha_nacimiento' => $hoy->copy()->subYears(30),
        ]);
        $enTresDias = \App\Models\Cliente::factory()->create([
            'activo' => true,
            'fecha_nacimiento' => $hoy->copy()->addDays(3)->subYears(41),
        ]);
        \App\Models\Cliente::factory()->create([
            'activo' => true,
            'fecha_nacimiento' => $hoy->copy()->addDays(40)->subYears(25),
        ]);
        \App\Models\Cliente::factory()->create([
            'activo' => false,
            'fecha_nacimiento' => $hoy->copy()->subYears(22),
        ]);

        $cumples = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props']['cumpleanos'];

        $this->assertSame(
            [(string) $deHoy->uuid, (string) $enTresDias->uuid],
            array_map(fn ($c) => (string) $c['uuid'], $cumples),
        );
        $this->assertSame(0, $cumples[0]['dias']);
        $this->assertSame(30, $cumples[0]['edad']);
        $this->assertSame(3, $cumples[1]['dias']);
    }

    /**
     * EL SALTO DE AÑO. Quien nació un 2 de enero cumple dentro de días cuando
     * estamos a fin de diciembre: comparando fechas a secas quedaría fuera por
     * un año entero y nunca se le saludaría.
     */
    public function test_el_cumpleanos_de_enero_sale_estando_en_diciembre(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 12, 30));

        $socio = \App\Models\Cliente::factory()->create([
            'activo' => true,
            'fecha_nacimiento' => '1990-01-02',
        ]);

        $cumples = $this->actingAs($this->administrador())->get('/panel')->viewData('page')['props']['cumpleanos'];

        $this->assertSame([(string) $socio->uuid], array_map(fn ($c) => (string) $c['uuid'], $cumples));
        $this->assertSame(3, $cumples[0]['dias']);
        $this->assertSame(37, $cumples[0]['edad']);
    }
}
