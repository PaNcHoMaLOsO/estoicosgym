<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\Estado;
use App\Models\MetodoPago;
use App\Models\MotivoDescuento;
use Faker\Factory as Faker;
use Carbon\Carbon;

class EnhancedTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $now = Carbon::now();

        // Obtener datos base
        $convenios = Convenio::all();
        $membresias = Membresia::all();
        $estados = Estado::all();
        $metodos_pago = MetodoPago::all();
        $motivos_descuento = MotivoDescuento::all();

        // Crear 50 clientes con diferentes tipos
        for ($i = 0; $i < 50; $i++) {
            $cliente = Cliente::create([
                'run_pasaporte' => $faker->unique()->numerify('##.###.###-#'),
                'nombres' => $faker->firstName(),
                'apellido_paterno' => $faker->lastName(),
                'apellido_materno' => $faker->lastName(),
                'celular' => $faker->numerify('+569 #### ####'),
                'email' => $faker->unique()->safeEmail(),
                'direccion' => $faker->address(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'id_convenio' => $faker->randomElement($convenios->pluck('id')->toArray()),
                'observaciones' => $faker->optional(0.3)->text(100),
                'activo' => $faker->boolean(80),
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);

            // Generar inscripciones para cada cliente (0 a 5)
            $num_inscripciones = $faker->numberBetween(0, 5);
            for ($j = 0; $j < $num_inscripciones; $j++) {
                $membresia = $faker->randomElement($membresias);
                $convenio = $faker->randomElement($convenios);
                $estado = $faker->randomElement($estados);
                
                // Obtener precio acordado
                $precio_membresia = $membresia->precios()->latest()->first();
                if (!$precio_membresia) {
                    continue;
                }
                
                $fecha_inicio = $faker->dateTimeBetween('-12 months', 'now');
                $fecha_vencimiento = Carbon::instance($fecha_inicio)->addDays($membresia->duracion_dias);
                
                $precio_base = $precio_membresia->precio_normal;
                $descuento_aplicado = 0;
                
                // Aplicar descuentos aleatorios
                if ($faker->boolean(40)) {
                    $descuento_aplicado = $faker->numberBetween(5, 30);
                }
                
                $precio_final = $precio_base - ($precio_base * $descuento_aplicado / 100);
                
                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_convenio' => $faker->boolean(60) ? $convenio->id : null,
                    'id_precio_acordado' => $precio_membresia->id,
                    'id_estado' => $estado->id,
                    'id_motivo_descuento' => $faker->boolean(30) ? $faker->randomElement($motivos_descuento->pluck('id')->toArray()) : null,
                    'fecha_inscripcion' => $faker->dateTimeBetween('-12 months', 'now'),
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'precio_base' => $precio_base,
                    'descuento_aplicado' => $descuento_aplicado,
                    'precio_final' => $precio_final,
                    'observaciones' => $faker->optional(0.2)->text(100),
                    'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
                ]);

                // Generar pagos para inscripciones activas o pagadas
                if ($estado->nombre != 'Cancelada' && $faker->boolean(70)) {
                    $num_pagos = $faker->numberBetween(1, 3);
                    $monto_restante = $precio_final;
                    
                    for ($k = 0; $k < $num_pagos; $k++) {
                        if ($k == $num_pagos - 1) {
                            $monto = $monto_restante;
                        } else {
                            $monto = $faker->randomFloat(2, $monto_restante * 0.1, $monto_restante * 0.6);
                            $monto_restante -= $monto;
                        }
                        
                        $fecha_pago = $faker->dateTimeBetween($fecha_inicio, $now);
                        $periodo_inicio = Carbon::instance($fecha_pago);
                        $estado_pago = $k == $num_pagos - 1 && $monto_restante <= 0 
                            ? $estados->where('nombre', 'Pagado')->first()?->id 
                            : $estados->where('nombre', 'Pendiente')->first()?->id;
                        
                        Pago::create([
                            'id_inscripcion' => $inscripcion->id,
                            'id_cliente' => $cliente->id,
                            'id_metodo_pago' => $faker->randomElement($metodos_pago->pluck('id')->toArray()),
                            'monto_total' => $precio_final,
                            'monto_abonado' => $monto,
                            'monto_pendiente' => max(0, $monto_restante - $monto),
                            'descuento_aplicado' => 0,
                            'id_motivo_descuento' => null,
                            'fecha_pago' => $fecha_pago,
                            'periodo_inicio' => $periodo_inicio->copy(),
                            'periodo_fin' => $periodo_inicio->copy()->addDays(30),
                            'referencia_pago' => $faker->optional(0.5)->numerify('REF-########'),
                            'id_estado' => $estado_pago,
                            'observaciones' => $faker->optional(0.1)->text(50),
                            'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
                        ]);
                    }
                }
            }
        }

        // Crear casos especiales para testing
        $this->crearClientesEspeciales($faker, $convenios, $membresias, $estados, $metodos_pago, $motivos_descuento);
    }

    private function crearClientesEspeciales($faker, $convenios, $membresias, $estados, $metodos_pago, $motivos_descuento)
    {
        // 1. Cliente sin inscripciones
        Cliente::create([
            'run_pasaporte' => '99.999.999-9',
            'nombres' => 'Sin',
            'apellido_paterno' => 'Inscripción',
            'apellido_materno' => 'Test',
            'celular' => '+569 9999 9999',
            'email' => 'sin.inscripcion@test.com',
            'direccion' => 'Dirección Test 1',
            'fecha_nacimiento' => Carbon::now()->subYears(30),
            'activo' => true,
        ]);

        // 2. Cliente con múltiples inscripciones activas
        $cliente_activo = Cliente::create([
            'run_pasaporte' => '88.888.888-8',
            'nombres' => 'Muy',
            'apellido_paterno' => 'Activo',
            'apellido_materno' => 'Test',
            'celular' => '+569 8888 8888',
            'email' => 'muy.activo@test.com',
            'direccion' => 'Dirección Test 2',
            'fecha_nacimiento' => Carbon::now()->subYears(25),
            'activo' => true,
        ]);

        for ($i = 0; $i < 4; $i++) {
            $membresia = $membresias->random();
            $precio_membresia = $membresia->precios()->latest()->first();
            
            if (!$precio_membresia) {
                continue;
            }
            
            $fecha_inicio = Carbon::now()->subMonths($i);
            
            Inscripcion::create([
                'id_cliente' => $cliente_activo->id,
                'id_membresia' => $membresia->id,
                'id_convenio' => $convenios->random()->id,
                'id_precio_acordado' => $precio_membresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'fecha_inscripcion' => $fecha_inicio,
                'fecha_inicio' => $fecha_inicio,
                'fecha_vencimiento' => $fecha_inicio->copy()->addDays($membresia->duracion_dias),
                'precio_base' => $precio_membresia->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio_membresia->precio_normal,
            ]);
        }

        // 3. Cliente con inscripción vencida
        $cliente_vencido = Cliente::create([
            'run_pasaporte' => '77.777.777-7',
            'nombres' => 'Vencido',
            'apellido_paterno' => 'Test',
            'apellido_materno' => 'Cliente',
            'celular' => '+569 7777 7777',
            'email' => 'vencido@test.com',
            'direccion' => 'Dirección Test 3',
            'fecha_nacimiento' => Carbon::now()->subYears(35),
            'activo' => true,
        ]);

        $membresia = $membresias->first();
        $precio_membresia = $membresia->precios()->latest()->first();
        
        if ($precio_membresia) {
            Inscripcion::create([
                'id_cliente' => $cliente_vencido->id,
                'id_membresia' => $membresia->id,
                'id_precio_acordado' => $precio_membresia->id,
                'id_estado' => $estados->where('nombre', 'Cancelada')->first()->id,
                'fecha_inscripcion' => Carbon::now()->subMonths(6),
                'fecha_inicio' => Carbon::now()->subMonths(6),
                'fecha_vencimiento' => Carbon::now()->subMonths(2),
                'precio_base' => $precio_membresia->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio_membresia->precio_normal,
            ]);
        }

        // 4. Cliente sin convenio
        $cliente_sin_convenio = Cliente::create([
            'run_pasaporte' => '66.666.666-6',
            'nombres' => 'Sin',
            'apellido_paterno' => 'Convenio',
            'apellido_materno' => 'Test',
            'celular' => '+569 6666 6666',
            'email' => 'sin.convenio@test.com',
            'direccion' => 'Dirección Test 4',
            'fecha_nacimiento' => Carbon::now()->subYears(28),
            'id_convenio' => null,
            'activo' => true,
        ]);

        $membresia = $membresias->random();
        $precio_membresia = $membresia->precios()->latest()->first();
        
        if ($precio_membresia) {
            Inscripcion::create([
                'id_cliente' => $cliente_sin_convenio->id,
                'id_membresia' => $membresia->id,
                'id_precio_acordado' => $precio_membresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'fecha_inscripcion' => Carbon::now()->subMonths(1),
                'fecha_inicio' => Carbon::now()->subMonths(1),
                'fecha_vencimiento' => Carbon::now()->addMonths(11),
                'precio_base' => $precio_membresia->precio_normal,
                'descuento_aplicado' => 15,
                'precio_final' => $precio_membresia->precio_normal * 0.85,
            ]);
        }

        // 5. Cliente inactivo
        Cliente::create([
            'run_pasaporte' => '55.555.555-5',
            'nombres' => 'Inactivo',
            'apellido_paterno' => 'Test',
            'apellido_materno' => 'Usuario',
            'celular' => '+569 5555 5555',
            'email' => 'inactivo@test.com',
            'direccion' => 'Dirección Test 5',
            'fecha_nacimiento' => Carbon::now()->subYears(40),
            'activo' => false,
        ]);
    }
}
