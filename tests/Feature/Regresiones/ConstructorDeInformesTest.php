<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Services\ConstructorInformes;
use Illuminate\Support\Facades\DB;
use Tests\CasoConCatalogos;

/**
 * Regresiones del constructor de informes.
 *
 * La lista de columnas del catálogo es una LISTA BLANCA. Antes no lo era: lo que
 * llegaba en la URL entraba tal cual en la consulta, y el propio formulario
 * ofrecía un filtro «Género» sobre una columna que no existe en la tabla.
 */
class ConstructorDeInformesTest extends CasoConCatalogos
{
    private function pagoDe(int $abonado, int $estado = 201): Pago
    {
        $cliente = Cliente::factory()->create(['activo' => true]);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_estado' => 100,
        ]);

        return Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => $abonado,
            'monto_abonado' => $abonado,
            'monto_pendiente' => 0,
            'id_estado' => $estado,
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    private function ver(string $modulo, array $parametros = [])
    {
        return $this->actingAs($this->administrador())
            ->getJson("/panel/reportes/constructor/{$modulo}/ver?" . http_build_query($parametros));
    }

    /**
     * EL BUG.
     *
     * `aplicarFiltros` tomaba la CLAVE del filtro de la URL y la metía como
     * columna sin comprobar nada. El formulario tenía un desplegable «Género» y
     * esa columna no existe en `clientes`, así que usarlo devolvía un 500 con
     * el SQL en pantalla. Cualquier clave inventada hacía lo mismo.
     */
    public function test_un_filtro_por_una_columna_que_no_existe_no_revienta(): void
    {
        Cliente::factory()->count(2)->create(['activo' => true]);

        $respuesta = $this->ver('clientes', [
            'columnas' => ['nombres'],
            'filtros' => ['genero' => 'Masculino', 'loquesea' => '1'],
        ]);

        $respuesta->assertOk();
        // El filtro se descarta entero: no se inventa un resultado vacío.
        $this->assertCount(2, $respuesta->json('filas'));
    }

    /**
     * La consulta pedía `select *`: un listado de nombres se llevaba al
     * navegador el RUT del apoderado, el teléfono de emergencia y todo lo demás
     * de cada socio.
     */
    public function test_solo_se_piden_a_la_base_las_columnas_elegidas(): void
    {
        Cliente::factory()->create(['activo' => true]);

        $consultas = [];
        DB::listen(function ($q) use (&$consultas) {
            $consultas[] = $q->sql;
        });

        app(ConstructorInformes::class)->ejecutar('clientes', [
            'columnas' => ['nombres', 'email'],
        ]);

        $sobreClientes = array_filter($consultas, fn ($sql) => str_contains($sql, 'from "clientes"'));
        $this->assertNotEmpty($sobreClientes, 'No se consultó la tabla clientes.');

        $sql = reset($sobreClientes);

        $this->assertStringNotContainsString('select *', $sql);
        $this->assertStringContainsString('"nombres"', $sql);
        // El apoderado NO se pide: nadie lo eligió.
        $this->assertStringNotContainsString('apoderado_rut', $sql);
    }

    /**
     * Una columna que sale de una relación necesita su clave foránea en el
     * SELECT. Si se recorta el SELECT y se olvida, la relación no encuentra con
     * qué emparejar y la columna sale vacía sin dar ningún error.
     */
    public function test_las_columnas_que_salen_de_una_relacion_se_resuelven(): void
    {
        $pago = $this->pagoDe(50000);

        $respuesta = $this->ver('pagos', [
            'columnas' => ['socio', 'metodo', 'monto_abonado'],
        ]);

        $fila = $respuesta->json('filas.0');

        $this->assertNotNull($fila['socio'], 'El nombre del socio no se resolvió.');
        $this->assertSame($pago->cliente->nombres . ' ' . $pago->cliente->apellido_paterno, $fila['socio']);
        $this->assertSame(MetodoPago::first()->nombre, $fila['metodo']);
    }

    /** La dirección del orden viene de la URL y Eloquent solo acepta asc o desc. */
    public function test_una_direccion_de_orden_inventada_no_revienta(): void
    {
        Cliente::factory()->create(['activo' => true]);

        $this->ver('clientes', [
            'columnas' => ['nombres'],
            'orden' => 'nombres',
            'direccion' => '; drop table clientes',
        ])->assertOk();

        $this->assertDatabaseCount('clientes', 1);
    }

    /** Por una columna derivada no se puede ordenar: no existe en la tabla. */
    public function test_ordenar_por_una_columna_derivada_no_revienta(): void
    {
        $this->pagoDe(1000);

        $this->ver('pagos', [
            'columnas' => ['socio'],
            'orden' => 'socio',
        ])->assertOk();
    }

    /**
     * «Sin límite» se traía todo a memoria. Ahora hay tope, y cuando recorta
     * TIENE que decirlo: si no, la tabla se lee como si fuera todo lo que hay.
     */
    public function test_cuando_el_limite_recorta_el_informe_lo_avisa(): void
    {
        Cliente::factory()->count(4)->create(['activo' => true]);

        $respuesta = $this->ver('clientes', [
            'columnas' => ['nombres'],
            'limite' => 2,
        ]);

        $this->assertCount(2, $respuesta->json('filas'));
        $this->assertTrue($respuesta->json('recortado'));
    }

    public function test_cuando_cabe_entero_no_dice_que_recorto(): void
    {
        Cliente::factory()->count(2)->create(['activo' => true]);

        $respuesta = $this->ver('clientes', [
            'columnas' => ['nombres'],
            'limite' => 50,
        ]);

        $this->assertFalse($respuesta->json('recortado'));
    }

    /** Un límite absurdo no puede saltarse el tope. */
    public function test_un_limite_enorme_se_queda_en_el_tope(): void
    {
        Cliente::factory()->count(3)->create(['activo' => true]);

        $respuesta = $this->ver('clientes', [
            'columnas' => ['nombres'],
            'limite' => 999999999,
        ]);

        $respuesta->assertOk();
        $this->assertFalse($respuesta->json('recortado'));
    }

    /** Suma solo lo que es dinero, y solo de las filas que se enseñan. */
    public function test_los_totales_suman_las_columnas_de_dinero(): void
    {
        $this->pagoDe(30000);
        $this->pagoDe(20000);

        $respuesta = $this->ver('pagos', [
            'columnas' => ['socio', 'monto_abonado'],
        ]);

        $this->assertSame(50000, $respuesta->json('totales.monto_abonado'));
        // «socio» es texto: no se suma.
        $this->assertArrayNotHasKey('socio', $respuesta->json('totales'));
    }

    /**
     * Los códigos se enseñan con su nombre, y con EL MISMO que ofrece el
     * filtro: el desplegable decía «Abono» y la celda de al lado «Parcial»
     * para el mismo valor, que se lee como si fueran dos cosas distintas.
     */
    public function test_el_estado_se_enseña_con_el_mismo_nombre_que_usa_el_filtro(): void
    {
        $this->pagoDe(1000, 202);

        $respuesta = $this->ver('pagos', [
            'columnas' => ['id_estado'],
        ]);

        $enElFiltro = $respuesta->json('filas.0.id_estado');

        $this->assertSame('Abono', $enElFiltro);
        $this->assertSame(
            ConstructorInformes::ESTADOS_PAGO[202],
            $enElFiltro,
            'La celda tiene que decir lo mismo que el desplegable del filtro.'
        );
    }

    /** Una columna inventada se descarta y el informe sale igual. */
    public function test_una_columna_que_no_esta_en_el_catalogo_se_descarta(): void
    {
        Cliente::factory()->create(['activo' => true]);

        $respuesta = $this->ver('clientes', [
            'columnas' => ['nombres', 'password', 'remember_token'],
        ]);

        $columnas = array_column($respuesta->json('columnas'), 'clave');

        $this->assertSame(['nombres'], $columnas);
    }

    public function test_un_modulo_que_no_existe_da_404(): void
    {
        $this->ver('inventado')->assertNotFound();
    }

    /** El CSV lleva las cabeceras con el título de cada columna. */
    public function test_el_csv_sale_con_cabecera_y_filas(): void
    {
        $this->pagoDe(15000);

        $respuesta = $this->actingAs($this->administrador())
            ->get('/panel/reportes/constructor/pagos/csv?' . http_build_query([
                'columnas' => ['socio', 'monto_abonado'],
            ]));

        $respuesta->assertOk();
        $respuesta->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $respuesta->streamedContent();

        $this->assertStringContainsString('Socio;Abonado', $csv);
        $this->assertStringContainsString('15000', $csv);
    }

    /** El catálogo que viaja al navegador no puede llevar cosas del servidor. */
    public function test_el_catalogo_de_pantalla_no_expone_los_modelos(): void
    {
        $catalogo = app(ConstructorInformes::class)->catalogoParaPantalla();

        foreach ($catalogo as $modulo) {
            $this->assertArrayNotHasKey('modelo', $modulo);
            $this->assertArrayNotHasKey('relaciones', $modulo);
        }
    }


    /**
     * Un texto que empieza por «=» no llega al CSV como fórmula.
     *
     * Excel ejecuta lo que empieza por = + - @ al abrir el archivo: un socio
     * anotado con un nombre así corría una fórmula en el computador del gimnasio.
     */
    public function test_el_csv_no_deja_pasar_formulas(): void
    {
        Cliente::factory()->create(['nombres' => '=1+1', 'activo' => true]);

        $csv = $this->actingAs($this->administrador())
            ->get('/panel/reportes/constructor/clientes/csv?' . http_build_query(['columnas' => ['nombres']]))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertDoesNotMatchRegularExpression('/^=1\+1/m', $csv);
    }

    /** Un filtro de texto que llega como lista —?filtros[nombres][]=a— no revienta el informe. */
    public function test_un_filtro_de_texto_como_lista_no_revienta(): void
    {
        Cliente::factory()->create(['activo' => true]);

        $this->ver('clientes', [
            'columnas' => ['nombres'],
            'filtros' => ['nombres' => ['a', 'b']],
        ])->assertOk();
    }
}
