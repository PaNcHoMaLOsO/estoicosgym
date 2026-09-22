<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\Inscripcion;
use Tests\CasoConCatalogos;

/**
 * Buscar a un socio por su RUT, escrito como sea.
 *
 * EN EL MESÓN SE TECLEA LO MÁS CORTO: «21410708», sin puntos, sin guion y sin
 * dígito verificador, que es lo que se alcanza a leer de un carnet. La ficha lo
 * tiene guardado «21.410.708-2», y comparando el texto tal cual esa búsqueda no
 * devolvía nada: el socio parecía no existir y se le abría otra ficha.
 *
 * Se prueba en las tres listas donde se busca, porque cada una tenía su propia
 * copia de la consulta y arreglar una dejaba las otras rotas.
 */
class BuscarPorRutTest extends CasoConCatalogos
{
    private function socio(): Cliente
    {
        return Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Juan Francisco',
            'apellido_paterno' => 'Hernandez',
            'apellido_materno' => 'Iturra',
            'run_pasaporte' => '21.410.708-2',
        ]);
    }

    private function encuentra(string $url, string $clave, Cliente $socio): bool
    {
        $datos = $this->actingAs($this->administrador())->get($url)->viewData('page')['props'][$clave]['data'];

        return collect($datos)->contains(
            fn (array $fila) => str_contains($fila['socio'] ?? $fila['nombre'] ?? '', 'Hernandez')
        );
    }

    /** @return list<string> */
    public static function formasDeEscribirlo(): array
    {
        return [
            'sin nada' => ['21410708'],
            'con guion y dígito' => ['21410708-2'],
            'con puntos' => ['21.410.708-2'],
            'entero sin guion' => ['214107082'],
            'con espacios' => [' 21410708 '],
        ];
    }

    #[DataProvider('formasDeEscribirlo')]
    public function test_se_encuentra_escrito_de_cualquier_forma(string $escrito): void
    {
        $socio = $this->socio();

        $encontrados = $this->actingAs($this->administrador())
            ->get('/panel/clientes?buscar='.urlencode($escrito))
            ->viewData('page')['props']['clientes']['data'];

        $this->assertCount(1, $encontrados, "No se encontró escribiendo «{$escrito}»");
        $this->assertSame((string) $socio->uuid, (string) $encontrados[0]['uuid']);
    }

    /** En inscripciones, que es donde se mira el plazo de cada membresía. */
    public function test_tambien_en_la_lista_de_inscripciones(): void
    {
        $socio = $this->socio();

        Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => today()->subDays(5),
            'fecha_vencimiento' => today()->addDays(25),
        ]);

        $this->assertTrue($this->encuentra('/panel/inscripciones?buscar=21410708', 'inscripciones', $socio));
    }

    /** Y en pagos, que es donde se responde «¿pagó o no?». */
    public function test_tambien_en_la_lista_de_pagos(): void
    {
        $socio = $this->socio();

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => today(),
            'fecha_vencimiento' => today()->addDays(30),
        ]);

        \App\Models\Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 40000,
            'monto_abonado' => 40000,
            'fecha_pago' => today(),
            'id_metodo_pago' => \App\Models\MetodoPago::value('id'),
            'id_estado' => 201,
        ]);

        $this->assertTrue($this->encuentra('/panel/pagos?buscar=21410708', 'pagos', $socio));
    }

    /**
     * BUSCAR UN NOMBRE NO PUEDE DEVOLVER A TODO EL MUNDO.
     *
     * El RUT y el celular se comparan sin puntos ni signos; hecho sin cuidado,
     * una palabra sin números se convertía en «%%» y eso lo cumple cualquiera.
     */
    public function test_buscar_un_nombre_no_devuelve_a_los_demas(): void
    {
        $this->socio();
        Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Pinto',
            'run_pasaporte' => '15.628.245-6',
        ]);

        $encontrados = $this->actingAs($this->administrador())
            ->get('/panel/clientes?buscar=hernandez')
            ->viewData('page')['props']['clientes']['data'];

        $this->assertCount(1, $encontrados);
    }

    /** El buscador del marco, el que sale en todas las pantallas. */
    public function test_el_buscador_de_arriba_tambien(): void
    {
        $socio = $this->socio();

        $socios = $this->actingAs($this->administrador())
            ->getJson('/panel/clientes/buscar?q=21410708')
            ->json('socios');

        $this->assertSame([(string) $socio->uuid], array_map(fn ($s) => (string) $s['uuid'], $socios));
    }
}
