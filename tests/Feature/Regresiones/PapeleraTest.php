<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MotivoDescuento;
use Tests\CasoConCatalogos;

/**
 * Lo que se borró y todavía se puede recuperar.
 *
 * Una sola pantalla para los siete tipos: quien la abre no viene a mirar «la
 * papelera de convenios», viene a buscar algo que borró hace un rato.
 */
class PapeleraTest extends CasoConCatalogos
{
    private function abrir()
    {
        return $this->actingAs($this->administrador())->get('/panel/papelera');
    }

    /** @return array<int,array<string,mixed>> */
    private function grupos($respuesta): array
    {
        return $respuesta->viewData('page')['props']['grupos'];
    }

    public function test_lo_borrado_aparece_en_la_papelera(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Borrada']);
        $socio->delete();

        $grupos = $this->grupos($this->abrir());

        $clientes = collect($grupos)->firstWhere('clave', 'clientes');

        $this->assertNotNull($clientes, 'El grupo de socios no aparece.');
        $this->assertSame(1, $clientes['cuantos']);
        $this->assertStringContainsString('Borrada', $clientes['filas'][0]['que']);
    }

    /** Lo que no se ha borrado NO sale: la papelera no es un listado más. */
    public function test_lo_que_sigue_vivo_no_aparece(): void
    {
        Cliente::factory()->count(3)->create(['activo' => true]);

        $this->assertSame([], $this->grupos($this->abrir()));
    }

    public function test_restaurar_lo_devuelve_a_su_sitio(): void
    {
        $motivo = MotivoDescuento::create(['nombre' => 'Se borró sin querer']);
        $motivo->delete();

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/motivos-descuento/{$motivo->id}/restaurar")
            ->assertSessionHasNoErrors();

        $this->assertNull($motivo->fresh()->deleted_at);
        $this->assertSame(1, MotivoDescuento::where('nombre', 'Se borró sin querer')->count());
    }

    public function test_restaurar_lo_saca_de_la_papelera(): void
    {
        $plan = Membresia::create([
            'nombre' => 'Plan borrado',
            'duracion_meses' => 1,
            'duracion_dias' => 0,
            'activo' => true,
        ]);
        $plan->delete();

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/membresias/{$plan->id}/restaurar");

        $this->assertSame([], $this->grupos($this->abrir()));
    }

    /** Los siete tipos caben en la misma pantalla, agrupados. */
    public function test_agrupa_por_tipo(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $socio->delete();

        $motivo = MotivoDescuento::create(['nombre' => 'Otro']);
        $motivo->delete();

        $claves = collect($this->grupos($this->abrir()))->pluck('clave')->all();

        $this->assertContains('clientes', $claves);
        $this->assertContains('motivos-descuento', $claves);
    }

    /**
     * La membresía borrada dice de quién era, aunque el socio no esté: sin eso,
     * la fila diría solo «membresía» y no se podría reconocer cuál es.
     */
    public function test_una_membresia_borrada_dice_de_quien_era(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Aurora']);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
        ]);
        $inscripcion->delete();

        $grupo = collect($this->grupos($this->abrir()))->firstWhere('clave', 'inscripciones');

        $this->assertStringContainsString('Aurora', $grupo['filas'][0]['que']);
        $this->assertStringContainsString('Mensual', $grupo['filas'][0]['detalle']);
    }

    public function test_un_tipo_que_no_existe_da_404(): void
    {
        $this->actingAs($this->administrador())
            ->patch('/panel/papelera/inventado/1/restaurar')
            ->assertNotFound();
    }

    /** Restaurar algo que no está borrado tampoco existe para esta pantalla. */
    public function test_restaurar_algo_que_no_estaba_borrado_da_404(): void
    {
        $motivo = MotivoDescuento::create(['nombre' => 'Vivito']);

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/motivos-descuento/{$motivo->id}/restaurar")
            ->assertNotFound();
    }

    /**
     * NO hay «eliminar del todo». Un socio con inscripciones o un pago de una
     * caja de hace tres años están referenciados por otras filas, y quitarlos
     * deja huecos en sitios que nadie mira hasta que cuadran mal las cuentas.
     *
     * «Borrar sus datos» SÍ existe y no es lo mismo: vacía el nombre, el RUT y
     * el contacto de la persona y deja la fila en su sitio, para que sus pagos
     * sigan cuadrando a nombre de «Socio Borrado».
     */
    public function test_no_existe_ninguna_ruta_para_borrar_del_todo(): void
    {
        $rutas = collect(app('router')->getRoutes())
            ->map(fn ($r) => $r->getName())
            ->filter(fn (?string $n) => $n && str_starts_with($n, 'panel.papelera.'))
            ->values()
            ->all();

        $this->assertSame(
            ['panel.papelera.index', 'panel.papelera.borrar-datos', 'panel.papelera.restore'],
            $rutas
        );

        // Y lo que hay detrás tampoco borra filas: ni un forceDelete.
        $this->assertStringNotContainsString(
            'forceDelete',
            file_get_contents(app_path('Http/Controllers/Panel/PapeleraController.php'))
        );
    }
}
