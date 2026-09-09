<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Database\Seeder;

/**
 * Datos de prueba con volumen.
 *
 * Cada socio recibe UNA inscripcion y esa inscripcion UN pago, encadenados de
 * forma explicita. Antes se pasaba `['id_inscripcion' => $x]` a la factory del
 * pago, y eso no basta: los valores sueltos se aplican DESPUES de definition(),
 * asi que el monto ya venia calculado sobre otra inscripcion cualquiera y el
 * pago quedaba con el precio de un plan que no era el suyo.
 */
class DatosMasivosSeeder extends Seeder
{
    public function run(): void
    {
        $totalClientes = 100;

        if (Membresia::count() < 5) {
            Membresia::factory()->count(10)->create();
        }

        if (MetodoPago::count() < 3) {
            MetodoPago::factory()->count(5)->create();
        }

        $this->command->info("Creando {$totalClientes} clientes con su inscripción y su pago...");

        $pausadas = 0;

        Cliente::factory()->count($totalClientes)->create()->each(function (Cliente $cliente) use (&$pausadas) {
            $inscripcion = Inscripcion::factory()
                // Una de cada cinco vigentes se deja pausada, y la factory se
                // encarga de dejar consistentes los cinco campos que eso toca.
                ->when(fake()->boolean(20), fn ($f) => $f->pausada())
                ->create(['id_cliente' => $cliente->id]);

            if ($inscripcion->pausada) {
                $pausadas++;
            }

            Pago::factory()
                ->paraInscripcion($inscripcion)
                ->create([
                    'id_inscripcion' => $inscripcion->id,
                    'id_cliente' => $cliente->id,
                ]);
        });

        $this->command->info("Listo. Inscripciones pausadas: {$pausadas}");
    }
}
