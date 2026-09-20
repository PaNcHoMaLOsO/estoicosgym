<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Tests\CasoConCatalogos;

/**
 * El buscador de socios del marco del panel.
 *
 * En el mesón todo empieza por un nombre o un RUT. El único buscador estaba en
 * Resumen, y desde cualquier otra pantalla había que volver al inicio para
 * encontrar a la persona. Este responde desde donde se esté, y dice de una vez
 * lo que se quiere saber antes de abrir la ficha: qué plan tiene y si debe.
 */
class BuscadorDelMarcoTest extends CasoConCatalogos
{
    private function buscar(string $texto): array
    {
        return $this->actingAs($this->administrador())
            ->getJson('/panel/clientes/buscar?q=' . urlencode($texto))
            ->assertOk()
            ->json('socios');
    }

    public function test_encuentra_por_nombre_y_apellido_juntos(): void
    {
        Cliente::factory()->create(['nombres' => 'Camila', 'apellido_paterno' => 'Rojas', 'apellido_materno' => 'Soto']);
        Cliente::factory()->create(['nombres' => 'Camilo', 'apellido_paterno' => 'Vera', 'apellido_materno' => 'Paz']);

        // Nombre y apellido están en columnas distintas: cada palabra calza en la suya.
        $socios = $this->buscar('camila rojas');

        $this->assertCount(1, $socios);
        $this->assertSame('Camila Rojas Soto', $socios[0]['nombre']);
    }

    public function test_dice_el_plan_y_lo_que_debe(): void
    {
        $cliente = Cliente::factory()->create(['nombres' => 'Pedro', 'apellido_paterno' => 'Deudor', 'activo' => true]);
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
            'monto_abonado' => 15000,
            'monto_pendiente' => 25000,
            'id_estado' => 202,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
        ]);

        $socio = $this->buscar('deudor')[0];

        $this->assertSame((string) $cliente->uuid, $socio['uuid']);
        $this->assertNotNull($socio['plan']);
        $this->assertSame(25000, $socio['debe']);
    }

    public function test_con_una_letra_no_busca(): void
    {
        Cliente::factory()->create(['nombres' => 'Ana']);

        $this->assertSame([], $this->buscar('a'));
    }

    /** «buscar» no puede caer en la ruta de la ficha como si fuera un uuid. */
    public function test_sin_sesion_no_responde(): void
    {
        $this->getJson('/panel/clientes/buscar?q=ana')->assertUnauthorized();
    }

    /**
     * El RUT se encuentra escrito como sea.
     *
     * En la base hay fichas con puntos y fichas sin ellos, y en el mesón se
     * teclea de las dos formas. El alta de socio usa este mismo buscador para
     * avisar «esta persona ya está registrada»: si no encuentra, se crea una
     * ficha repetida y el historial del socio queda partido en dos.
     */
    public function test_encuentra_el_rut_con_puntos_y_sin_puntos(): void
    {
        \App\Models\Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Pinto',
            'apellido_materno' => '',
            'run_pasaporte' => '12.345.678-5',
        ]);

        $admin = $this->administrador();

        foreach (['12.345.678-5', '123456785', '12345678'] as $comoSeEscribe) {
            $nombres = collect(
                $this->actingAs($admin)->getJson('/panel/clientes/buscar?q=' . urlencode($comoSeEscribe))->json('socios')
            )->pluck('nombre');

            $this->assertContains('Rosa Pinto', $nombres, "No se encontró escribiendo «{$comoSeEscribe}».");
        }
    }
}
