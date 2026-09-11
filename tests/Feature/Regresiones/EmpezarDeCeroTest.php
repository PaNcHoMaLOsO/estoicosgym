<?php

namespace Tests\Feature\Regresiones;

use App\Console\Commands\EmpezarDeCero;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Fiado;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Nota;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\CasoConCatalogos;

/**
 * Dejar el sistema en cero antes de empezar con socios de verdad.
 *
 * Lo que se vigila: que sin --confirmar no se borre nada, que con él se vayan
 * los socios y todo lo que cuelga de ellos pero no la configuración, que antes
 * quede un respaldo que se pueda volver a importar, y que en producción no se
 * pueda correr por un descuido.
 */
class EmpezarDeCeroTest extends CasoConCatalogos
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    /** Un socio con todo lo que puede colgar de él. */
    private function sembrar(): void
    {
        $admin = $this->administrador();

        Storage::disk('public')->put('clientes/camila.jpg', 'foto');
        $cliente = Cliente::factory()->create(['activo' => true, 'foto_perfil' => 'clientes/camila.jpg']);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => 40000,
            'monto_abonado' => 40000,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
        ]);

        Fiado::create(['id_cliente' => $cliente->id, 'concepto' => 'Agua', 'monto' => 1000, 'id_usuario' => $admin->id]);
        Nota::create(['texto' => 'Llamar a Camila', 'id_usuario' => $admin->id]);
    }

    public function test_sin_confirmar_solo_cuenta(): void
    {
        $this->sembrar();

        $this->artisan('datos:empezar-de-cero')
            ->expectsOutputToContain('No se borró nada')
            ->assertSuccessful();

        $this->assertSame(1, Cliente::count());
        $this->assertSame(1, Pago::count());
    }

    /** EL QUE IMPORTA: se van los movimientos y la configuración queda como estaba. */
    public function test_borra_los_movimientos_y_deja_la_configuracion(): void
    {
        $this->sembrar();

        $configuracion = [
            'planes' => Membresia::count(),
            'metodos' => MetodoPago::count(),
            'convenios' => Convenio::count(),
            'usuarios' => User::count(),
        ];

        $this->artisan('datos:empezar-de-cero', ['--confirmar' => true])->assertSuccessful();

        foreach (EmpezarDeCero::TABLAS as $tabla) {
            $this->assertSame(0, DB::table($tabla)->count(), "Quedaron filas en {$tabla}.");
        }

        $this->assertSame($configuracion, [
            'planes' => Membresia::count(),
            'metodos' => MetodoPago::count(),
            'convenios' => Convenio::count(),
            'usuarios' => User::count(),
        ]);

        Storage::disk('public')->assertMissing('clientes/camila.jpg');

        // La numeración empieza de nuevo: el primer socio de verdad es el 1.
        $this->assertSame(1, Cliente::factory()->create()->id);
    }

    /** Antes de borrar queda un respaldo, y se puede volver a importar. */
    public function test_antes_de_borrar_guarda_un_respaldo_que_se_puede_importar(): void
    {
        $this->sembrar();

        $this->artisan('datos:empezar-de-cero', ['--confirmar' => true])->assertSuccessful();

        $archivos = collect(Storage::disk('local')->allFiles('respaldos'));
        $sql = $archivos->first(fn (string $a) => str_ends_with($a, 'datos.sql'));

        $this->assertNotNull($sql, 'No quedó el respaldo.');
        $this->assertTrue($archivos->contains(fn (string $a) => str_ends_with($a, 'fotos/camila.jpg')), 'No quedó la foto.');

        $contenido = Storage::disk('local')->get($sql);
        $this->assertStringContainsString('INSERT INTO `clientes`', $contenido);

        // Se importa y vuelve todo. La base de las pruebas no entiende el
        // SET FOREIGN_KEY_CHECKS de MySQL: se quita solo para probar.
        DB::unprepared(preg_replace('/^SET FOREIGN_KEY_CHECKS=\d;$/m', '', $contenido));

        $this->assertSame(1, Cliente::count());
        $this->assertSame(1, Inscripcion::count());
        $this->assertSame(40000, (int) Pago::first()->monto_abonado);
        $this->assertSame(1, Fiado::count());
    }

    /** Con socios de verdad no se puede correr por un descuido. */
    public function test_en_produccion_pide_un_permiso_mas(): void
    {
        $this->sembrar();
        $this->app['env'] = 'production';

        $this->artisan('datos:empezar-de-cero', ['--confirmar' => true])->assertFailed();

        $this->assertSame(1, Cliente::count());
    }
}
