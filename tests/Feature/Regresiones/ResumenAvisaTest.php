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
}
