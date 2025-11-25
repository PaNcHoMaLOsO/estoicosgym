<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Carbon\Carbon;

class ClientesInscripcionesPagosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 10 clientes
        for ($i = 1; $i <= 10; $i++) {
            $cliente = Cliente::create([
                'run_pasaporte' => '1234567' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'nombres' => 'Cliente ' . $i,
                'apellido_paterno' => 'Prueba',
                'apellido_materno' => 'Test',
                'celular' => '912345' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => 'cliente' . $i . '@test.com',
                'direccion' => 'Calle ' . $i . ', número ' . ($i * 100),
                'fecha_nacimiento' => Carbon::now()->subYears(25 + $i)->toDateString(),
                'activo' => true,
            ]);

            // Crear 2 inscripciones por cliente
            for ($j = 1; $j <= 2; $j++) {
                $precioId = ($j === 1) ? 1 : 2; // Usar diferentes precios
                
                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $j,
                    'id_precio_acordado' => $precioId,
                    'fecha_inscripcion' => Carbon::now()->subMonths(6 - $j)->toDateString(),
                    'fecha_inicio' => Carbon::now()->subMonths(6 - $j)->toDateString(),
                    'fecha_vencimiento' => Carbon::now()->addMonths($j)->toDateString(),
                    'dia_pago' => 15,
                    'precio_base' => 50000 + ($j * 10000),
                    'descuento_aplicado' => $j === 1 ? 5000 : 0,
                    'precio_final' => ($j === 1) ? 45000 : 60000,
                    'id_motivo_descuento' => null,
                    'id_estado' => 1, // Activa
                    'observaciones' => 'Cliente de prueba ' . $i,
                ]);

                // Crear 3 pagos por inscripción
                for ($k = 1; $k <= 3; $k++) {
                    Pago::create([
                        'id_inscripcion' => $inscripcion->id,
                        'id_cliente' => $cliente->id,
                        'monto_total' => $inscripcion->precio_final,
                        'monto_abonado' => $inscripcion->precio_final,
                        'monto_pendiente' => 0,
                        'descuento_aplicado' => 0,
                        'id_motivo_descuento' => null,
                        'fecha_pago' => Carbon::now()->subDays(10 - $k)->toDateString(),
                        'periodo_inicio' => Carbon::now()->subMonths(3 - $k)->toDateString(),
                        'periodo_fin' => Carbon::now()->subMonths(2 - $k)->toDateString(),
                        'id_metodo_pago' => ($k % 3) + 1, // Alternar métodos de pago
                        'referencia_pago' => 'REF-' . $cliente->id . '-' . $inscripcion->id . '-' . $k,
                        'id_estado' => 2, // Pagado
                        'observaciones' => 'Pago ' . $k . ' de cliente ' . $i,
                    ]);
                }
            }
        }
    }
}
