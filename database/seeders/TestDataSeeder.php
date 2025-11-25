<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the database with 200+ test records for performance testing.
     */
    public function run(): void
    {
        // Crear 200+ clientes
        $clientes = Cliente::factory(220)->create();

        // Estados válidos
        $estadosPendiente = \App\Models\Estado::where('nombre', 'Pendiente')
            ->where('categoria', 'inscripcion')
            ->first();
        $estadosActiva = \App\Models\Estado::where('nombre', 'Activa')
            ->where('categoria', 'inscripcion')
            ->first();
        $estadosCancelada = \App\Models\Estado::where('nombre', 'Cancelada')
            ->where('categoria', 'inscripcion')
            ->first();

        $estadosInscripciones = [$estadosPendiente, $estadosActiva, $estadosCancelada];

        // Membresias
        $membresias = \App\Models\Membresia::all();

        // Convenios
        $convenios = \App\Models\Convenio::all();

        // Motivos de descuento
        $motivos = \App\Models\MotivoDescuento::all();

        // Métodos de pago
        $metodosPago = \App\Models\MetodoPago::all();

        // Estados para pagos
        $estadosPagoPendiente = \App\Models\Estado::where('nombre', 'Pendiente')
            ->where('categoria', 'pago')
            ->first();
        $estadosPagoRealizado = \App\Models\Estado::where('nombre', 'Realizado')
            ->where('categoria', 'pago')
            ->first();

        $estadosPagos = array_filter([$estadosPagoPendiente, $estadosPagoRealizado]);

        // Crear 200+ inscripciones
        foreach ($clientes as $cliente) {
            for ($i = 0; $i < 2; $i++) {
                $membresia = $membresias->random();
                $convenio = rand(0, 1) ? $convenios->random() : null;
                $estado = collect($estadosInscripciones)->random();
                $fechaInicio = Carbon::now()->addDays(rand(-300, 100));
                $fechaVencimiento = $fechaInicio->clone()->addMonths($membresia->duracion_meses);

                $precioMembresia = $membresia->precios()->latest()->first();
                $precioBase = $precioMembresia?->precio ?? 100;
                $descuentoAplicado = 0;

                if ($convenio && $convenio->descuento_porcentaje > 0) {
                    $descuentoAplicado = ($precioBase * $convenio->descuento_porcentaje) / 100;
                } elseif ($convenio && $convenio->descuento_monto > 0) {
                    $descuentoAplicado = $convenio->descuento_monto;
                }

                $precioFinal = $precioBase - $descuentoAplicado;

                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_convenio' => $convenio?->id,
                    'id_precio_acordado' => $precioMembresia?->id,
                    'id_estado' => $estado->id,
                    'id_motivo_descuento' => $motivos->isNotEmpty() ? $motivos->random()->id : null,
                    'fecha_inscripcion' => Carbon::now(),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'precio_base' => $precioBase,
                    'descuento_aplicado' => $descuentoAplicado,
                    'precio_final' => $precioFinal,
                    'observaciones' => 'Inscripción de prueba generada automáticamente',
                ]);

                // Crear pagos para inscripciones activas
                if ($estado->nombre === 'Activa' && rand(0, 1) && !empty($estadosPagos) && $metodosPago->isNotEmpty()) {
                    for ($j = 0; $j < rand(1, 3); $j++) {
                        $estadoPago = collect($estadosPagos)->random();
                        $fechaPago = $fechaInicio->clone()->addDays(rand(0, 30));
                        $montoAbonado = $precioFinal / rand(1, 3);

                        if ($estadoPago) {
                            Pago::create([
                                'id_inscripcion' => $inscripcion->id,
                                'id_cliente' => $cliente->id,
                                'id_metodo_pago' => $metodosPago->random()->id,
                                'id_estado' => $estadoPago->id,
                                'fecha_pago' => $fechaPago,
                                'monto_total' => $precioFinal,
                                'monto_abonado' => $montoAbonado,
                                'monto_pendiente' => max(0, $precioFinal - $montoAbonado),
                                'referencia_pago' => 'REF-' . str_pad($inscripcion->id, 6, '0', STR_PAD_LEFT),
                                'periodo_inicio' => $fechaInicio,
                                'periodo_fin' => $fechaVencimiento,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('✅ Datos de prueba creados: ' . $clientes->count() . ' clientes y ' . Inscripcion::count() . ' inscripciones');
    }
}
