<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\ConvenioPrecio;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\PrecioMembresia;
use Tests\CasoConCatalogos;

/**
 * Cada convenio puede tener su propio precio para un plan.
 *
 * Los clubes deportivos lo negocian: uno paga 10.000 la mensualidad y otro
 * 15.000, por venir tres veces por semana. El «precio con convenio» del plan es
 * uno solo para todos y no da para eso; sin esta tabla había que escribir un
 * descuento a mano en cada inscripción, y un descuento a mano no lo comprueba
 * nadie.
 *
 * Lo que se vigila: que mande el precio del convenio, que sin trato propio siga
 * mandando el general, que la diferencia quede registrada como descuento —y no
 * escondida— y que el precio salga del catálogo y NO de lo que mande el
 * navegador.
 */
class PrecioPorConvenioTest extends CasoConCatalogos
{
    /** El plan mensual del catálogo de pruebas: 40.000, y 25.000 con convenio. */
    private const MENSUAL = 4;

    private function convenio(string $nombre, ?int $precio = null): Convenio
    {
        $convenio = Convenio::factory()->create(['activo' => true, 'nombre' => $nombre, 'tipo' => 'club_deportivo']);

        if ($precio !== null) {
            ConvenioPrecio::create([
                'id_convenio' => $convenio->id,
                'id_membresia' => self::MENSUAL,
                'precio' => $precio,
                'condicion' => '3 veces por semana',
            ]);
        }

        return $convenio;
    }

    private function inscribir(Convenio $convenio, array $extra = []): Inscripcion
    {
        $this->actingAs($this->administrador())->post('/panel/clientes', $extra + [
            'flujo_cliente' => 'con_membresia',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '+56 9 1234 5674',
            'run_pasaporte' => '11.111.111-1',
            'id_membresia' => self::MENSUAL,
            'id_convenio' => $convenio->id,
            'fecha_inicio' => today()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        return Inscripcion::latest('id')->firstOrFail();
    }

    /** EL QUE IMPORTA: se cobra lo que ese club negoció, no el precio general. */
    public function test_el_club_paga_el_precio_que_negocio(): void
    {
        $inscripcion = $this->inscribir($this->convenio('Club de Básquetbol', 15000));

        $this->assertSame(15000, (int) $inscripcion->precio_final);
        // La rebaja queda escrita, no escondida: 40.000 menos 15.000.
        $this->assertSame(25000, (int) $inscripcion->descuento_aplicado);
    }

    /** Dos clubes, dos precios: no se pisan entre ellos. */
    public function test_cada_club_paga_lo_suyo(): void
    {
        $futbol = $this->convenio('Club de Fútbol', 10000);
        $basquet = $this->convenio('Club de Básquetbol', 15000);

        $uno = $this->inscribir($futbol);
        $otro = $this->inscribir($basquet, ['run_pasaporte' => '22.222.222-2', 'nombres' => 'Pedro']);

        $this->assertSame([10000, 15000], [(int) $uno->precio_final, (int) $otro->precio_final]);
    }

    /** Sin trato propio sigue mandando el «precio con convenio» del plan. */
    public function test_sin_trato_propio_manda_el_precio_con_convenio_del_plan(): void
    {
        $inscripcion = $this->inscribir($this->convenio('INACAP'));

        $this->assertSame(25000, (int) $inscripcion->precio_final);
    }

    /** Sin convenio, el precio normal. */
    public function test_sin_convenio_se_paga_el_precio_normal(): void
    {
        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'con_membresia',
            'nombres' => 'Ana',
            'apellido_paterno' => 'Soto',
            'celular' => '+56 9 1234 5674',
            'run_pasaporte' => '33.333.333-3',
            'id_membresia' => self::MENSUAL,
            'fecha_inicio' => today()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(40000, (int) Inscripcion::latest('id')->firstOrFail()->precio_final);
    }

    /**
     * EL PRECIO NO LLEGA DEL NAVEGADOR.
     *
     * Quien mande un precio inventado en la petición no cambia lo que se cobra:
     * sale del catálogo. Si no, cualquiera podría inscribirse en $1.
     */
    public function test_el_precio_lo_pone_el_catalogo_y_no_la_peticion(): void
    {
        $inscripcion = $this->inscribir($this->convenio('Club de Fútbol', 10000), [
            'precio_final' => 1,
            'precio_base' => 1,
        ]);

        $this->assertSame(10000, (int) $inscripcion->precio_final);
    }

    /** Se guardan desde la ficha del convenio, y vaciar el precio borra el trato. */
    public function test_los_precios_se_guardan_y_se_quitan_desde_la_ficha(): void
    {
        $convenio = $this->convenio('Club de Fútbol');
        $admin = $this->administrador();

        $this->actingAs($admin)->put("/panel/convenios/{$convenio->uuid}/precios", [
            'precios' => [
                ['id_membresia' => self::MENSUAL, 'precio' => 12000, 'condicion' => '3 veces por semana'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(12000, (int) ConvenioPrecio::where('id_convenio', $convenio->id)->value('precio'));

        // Vacío no es cero: significa «paga el precio general», y la fila se va.
        $this->actingAs($admin)->put("/panel/convenios/{$convenio->uuid}/precios", [
            'precios' => [
                ['id_membresia' => self::MENSUAL, 'precio' => null, 'condicion' => ''],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, ConvenioPrecio::where('id_convenio', $convenio->id)->count());
    }

    /** Recepción no cambia precios: eso es configuración. */
    public function test_recepcion_no_toca_los_precios(): void
    {
        $convenio = $this->convenio('Club de Fútbol');

        $this->actingAs($this->recepcionista())
            ->put("/panel/convenios/{$convenio->uuid}/precios", [
                'precios' => [['id_membresia' => self::MENSUAL, 'precio' => 1000]],
            ])
            ->assertForbidden();

        $this->assertSame(0, ConvenioPrecio::count());
    }

    /** El catálogo de pruebas es el que dice esta prueba: si cambia, se entera. */
    public function test_el_plan_de_referencia_cuesta_lo_que_se_supone(): void
    {
        $precio = PrecioMembresia::where('id_membresia', self::MENSUAL)->where('activo', true)->firstOrFail();

        $this->assertSame([40000, 25000], [(int) $precio->precio_normal, (int) $precio->precio_convenio]);
    }
}
