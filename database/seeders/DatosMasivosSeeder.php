<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class DatosMasivosSeeder extends Seeder
{
    /**
     * Ejecuta el seeder para poblar la base con un millón de datos usando factories.
     */
    public function run(): void
    {
        $totalClientes = 100; // Un millón
        $inscripcionesPorCliente = 1; // Ajustable
        $pagosPorInscripcion = 1; // Ajustable

        // Asegurar que existan membresías y métodos de pago
        if (\App\Models\Membresia::count() < 5) {
            \App\Models\Membresia::factory()->count(10)->create();
        }
        if (\App\Models\MetodoPago::count() < 3) {
            \App\Models\MetodoPago::factory()->count(5)->create();
        }

        $this->command->info("Creando $totalClientes clientes...");
        Cliente::factory()->count($totalClientes)->create()->each(function ($cliente) use ($inscripcionesPorCliente, $pagosPorInscripcion) {
            Inscripcion::factory()->count($inscripcionesPorCliente)->create([
                'id_cliente' => $cliente->id,
            ])->each(function ($inscripcion) use ($pagosPorInscripcion) {
                Pago::factory()->count($pagosPorInscripcion)->create([
                    'id_inscripcion' => $inscripcion->id,
                    'id_cliente' => $inscripcion->id_cliente,
                ]);
            });
        });
        $this->command->info("Datos masivos generados correctamente.");
    }
}
