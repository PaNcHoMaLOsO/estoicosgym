<?php

namespace Tests\Feature\Regresiones;

use App\Support\Ajustes;
use App\Support\Programador;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\CasoConCatalogos;

/**
 * Las tareas automáticas: si corren, y a qué hora.
 *
 * Lo que se vigila: que el sistema distinga «corriendo» de «nunca corrió» —en
 * este gimnasio nunca corrieron y nadie lo supo—, y que las horas salgan de
 * Configuración y aguanten un valor roto sin dejar al programador sin hora.
 */
class ProgramadorTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Ajustes::olvidar();
        Cache::forget('programador:latido');
    }

    public function test_sin_latido_no_esta_corriendo(): void
    {
        $this->assertNull(Programador::ultimoLatido());
        $this->assertFalse(Programador::corriendo());
    }

    public function test_el_latido_se_apaga_a_los_diez_minutos(): void
    {
        Programador::latir();
        $this->assertTrue(Programador::corriendo());

        $this->travel(11)->minutes();

        $this->assertFalse(Programador::corriendo());
    }

    public function test_la_hora_sale_de_configuracion(): void
    {
        $this->assertSame('01:00', Programador::hora('tareas.hora_revision'));

        Ajustes::guardar(['tareas.hora_revision' => '06:30']);

        $this->assertSame('06:30', Programador::hora('tareas.hora_revision'));
        // Los pasos que van detrás, corridos en orden.
        $this->assertSame('06:50', Programador::hora('tareas.hora_revision', 20));
    }

    /** Un valor roto en la tabla no deja al programador sin hora: vuelve a la de fábrica. */
    public function test_una_hora_rota_vuelve_a_la_de_fabrica(): void
    {
        DB::table('ajustes')->insert([
            'clave' => 'tareas.hora_avisos',
            'valor' => '25:99',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Ajustes::olvidar();

        $this->assertSame('08:00', Programador::hora('tareas.hora_avisos'));
    }

    /** El programador tiene el latido, y cada tarea a la hora que dice Configuración. */
    public function test_el_programador_usa_esas_horas(): void
    {
        $this->app->make(Kernel::class)->bootstrap();
        $eventos = collect($this->app->make(Schedule::class)->events());

        $this->assertTrue($eventos->contains(fn ($e) => $e->description === 'latido-del-programador'));

        $pagos = $eventos->first(fn ($e) => str_contains((string) $e->command, 'pagos:sincronizar-estados'));
        [$hora, $minuto] = explode(':', Programador::hora('tareas.hora_revision', 10));

        $this->assertSame((int) $minuto . ' ' . (int) $hora . ' * * *', $pagos->expression);
    }
}
