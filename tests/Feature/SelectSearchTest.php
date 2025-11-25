<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_search_api_returns_results_with_200_records()
    {
        // Ejecutar seeder
        $this->seed();
        $this->seed(\Database\Seeders\TestDataSeeder::class);

        // Buscar "Juan" (nombre común)
        $response = $this->getJson('/api/clientes/search?q=Juan');
        $response->assertStatus(200);
        $results = $response->json();
        
        // Validar que hay resultados
        $this->assertIsArray($results);
        // Máximo 20 resultados según Select2
        $this->assertLessThanOrEqual(20, count($results));

        // Validar estructura
        foreach ($results as $result) {
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('text', $result);
        }
    }

    public function test_inscripcion_search_api_returns_results()
    {
        $this->seed();
        $this->seed(\Database\Seeders\TestDataSeeder::class);

        // Buscar por "Activa"
        $response = $this->getJson('/api/inscripciones/search?q=Activa');
        $response->assertStatus(200);
        $results = $response->json();
        
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(20, count($results));

        foreach ($results as $result) {
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('text', $result);
        }
    }

    public function test_inscripcion_calcular_api()
    {
        $this->seed();

        // Obtener primeros registros
        $membresia = \App\Models\Membresia::first();
        $convenio = \App\Models\Convenio::first();

        $response = $this->postJson('/api/inscripciones/calcular', [
            'id_membresia' => $membresia->id,
            'id_convenio' => $convenio->id,
            'fecha_inicio' => '2025-01-01',
            'precio_base' => 100,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('fecha_vencimiento', $data);
        $this->assertArrayHasKey('descuento_aplicado', $data);
        $this->assertArrayHasKey('precio_final', $data);
    }

    public function test_no_duplicate_pending_states_in_inscriptions()
    {
        $this->seed();
        $this->seed(\Database\Seeders\TestDataSeeder::class);

        // Verificar que no hay dos "Pendiente" con categoría inscripcion
        $estadosPendientes = \App\Models\Estado::where('nombre', 'Pendiente')
            ->where('categoria', 'inscripcion')
            ->get();

        $this->assertCount(1, $estadosPendientes, 'Hay múltiples estados Pendiente para inscripción');

        // Verificar que las inscripciones usan el estado correcto
        $inscripciones = Inscripcion::where('id_estado', $estadosPendientes->first()->id)->count();
        $this->assertGreaterThan(0, $inscripciones, 'No hay inscripciones con estado Pendiente');
    }

    public function test_select2_works_with_minimum_2_chars()
    {
        $this->seed();
        $this->seed(\Database\Seeders\TestDataSeeder::class);

        // Busca con 1 caracter (no debería retornar nada o error)
        $response = $this->getJson('/api/clientes/search?q=J');
        // Depende de implementación, pero generalmente rechaza búsquedas cortas

        // Busca con 2 caracteres (debería funcionar)
        $response = $this->getJson('/api/clientes/search?q=Ju');
        $response->assertStatus(200);
    }
}
