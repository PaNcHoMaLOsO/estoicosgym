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
use App\Models\PrecioMembresia;
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

        // Nombres chilenos realistas
        $nombresChilenos = [
            'Juan', 'Carlos', 'Miguel', 'Roberto', 'Fernando', 'Javier', 'Ricardo', 'Andrés',
            'Jorge', 'Francisco', 'Diego', 'Pablo', 'Raúl', 'Sergio', 'Claudio', 'Luis',
            'Marcos', 'José', 'Rodrigo', 'Alejandro', 'Felipe', 'Victor', 'Mario', 'Oscar',
            'Manuel', 'Gabriel', 'Antonio', 'Arturo', 'Iván', 'Héctor', 'Ramón', 'Ignacio',
            'María', 'Gabriela', 'Patricia', 'Rosa', 'Carmen', 'Ana', 'Laura', 'Sandra',
            'Beatriz', 'Verónica', 'Claudia', 'Lorena', 'Ximena', 'Catalina', 'Paola', 'Francisca',
        ];

        $apellidosChilenos = [
            'González', 'Muñoz', 'Martínez', 'García', 'López', 'Rodríguez', 'Hernández', 'Pérez',
            'Flores', 'Vargas', 'Castro', 'Torres', 'Silva', 'Morales', 'Ortiz', 'Jiménez',
            'Ramírez', 'Carrasco', 'Soto', 'Núñez', 'Vega', 'Ruiz', 'Acuña', 'Fuentes',
            'Rojas', 'Araya', 'Valenzuela', 'Reyes', 'Contreras', 'Moreno', 'Vidal', 'Bravo',
        ];

        // Crear 60 clientes con datos realistas
        for ($i = 0; $i < 60; $i++) {
            $cliente = Cliente::create([
                'run_pasaporte' => $this->generarRutChileno($faker),
                'nombres' => $faker->randomElement($nombresChilenos),
                'apellido_paterno' => $faker->randomElement($apellidosChilenos),
                'apellido_materno' => $faker->randomElement($apellidosChilenos),
                'celular' => $faker->numerify('+569 #### ####'),
                'email' => $faker->unique()->safeEmail(),
                'direccion' => $faker->streetAddress(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-65 years', '-18 years'),
                'contacto_emergencia' => $faker->randomElement($nombresChilenos) . ' ' . $faker->randomElement($apellidosChilenos),
                'telefono_emergencia' => $faker->numerify('+569 #### ####'),
                'id_convenio' => $faker->randomElement($convenios->pluck('id')->toArray()),
                'observaciones' => $faker->optional(0.2)->text(80),
                'activo' => $faker->boolean(85),
                'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
            ]);

            // Generar inscripciones (0 a 4 por cliente)
            $num_inscripciones = $faker->numberBetween(0, 4);
            for ($j = 0; $j < $num_inscripciones; $j++) {
                $membresia = $membresias->random();
                $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
                
                if (!$precioMembresia) {
                    continue;
                }

                $mesesAtras = $faker->numberBetween(1, 12);
                $fechaInicio = $now->copy()->subMonths($mesesAtras)->startOfDay();
                $fechaVencimiento = $fechaInicio->copy()->addDays($membresia->duracion_dias);
                
                $estado = $faker->randomElement($estados);
                $tieneDescuento = $faker->boolean(35);
                $descuentoAplicado = 0;
                
                if ($tieneDescuento) {
                    $descuentoAplicado = $faker->randomFloat(2, 5000, 20000);
                }

                $precioFinal = max(0, $precioMembresia->precio_normal - $descuentoAplicado);
                
                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_convenio' => $faker->boolean(70) ? $faker->randomElement($convenios->pluck('id')->toArray()) : null,
                    'id_precio_acordado' => $precioMembresia->id,
                    'id_estado' => $estado->id,
                    'id_motivo_descuento' => $tieneDescuento && $faker->boolean(50) ? $faker->randomElement($motivos_descuento->pluck('id')->toArray()) : null,
                    'fecha_inscripcion' => $fechaInicio,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'dia_pago' => $faker->optional(0.7)->numberBetween(1, 28),
                    'precio_base' => $precioMembresia->precio_normal,
                    'descuento_aplicado' => $descuentoAplicado,
                    'precio_final' => $precioFinal,
                    'observaciones' => $faker->optional(0.15)->text(80),
                    'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
                ]);

                // Generar pagos
                if ($estado->nombre !== 'Cancelada' && $faker->boolean(75)) {
                    $numPagos = $faker->numberBetween(1, 2);
                    $montoRestante = $precioFinal;

                    for ($k = 0; $k < $numPagos; $k++) {
                        if ($k === $numPagos - 1) {
                            $montoAbonado = $montoRestante;
                        } else {
                            $montoAbonado = $faker->randomFloat(2, $montoRestante * 0.4, $montoRestante * 0.7);
                        }

                        $fechaPago = $faker->dateTimeBetween($fechaInicio, $now);
                        $periodoInicio = Carbon::instance($fechaPago)->startOfDay();
                        $estadoPago = $estados->where('nombre', 'Pagado')->first()->id;

                        Pago::create([
                            'uuid' => \Illuminate\Support\Str::uuid(),
                            'id_inscripcion' => $inscripcion->id,
                            'id_metodo_pago_principal' => $faker->randomElement($metodos_pago->pluck('id')->toArray()),
                            'monto_abonado' => $montoAbonado,
                            'monto_pendiente' => max(0, $montoRestante - $montoAbonado),
                            'id_motivo_descuento' => null,
                            'fecha_pago' => $fechaPago,
                            'referencia_pago' => $faker->optional(0.4)->numerify('REF-########'),
                            'id_estado' => $estadoPago,
                            'cantidad_cuotas' => 1,
                            'numero_cuota' => 1,
                            'observaciones' => $faker->optional(0.1)->text(50),
                            'created_at' => $faker->dateTimeBetween('-12 months', 'now'),
                        ]);

                        $montoRestante -= $montoAbonado;
                    }
                }
            }
        }

        $this->crearClientesEspeciales($convenios, $membresias, $estados, $metodos_pago);
    }

    private function crearClientesEspeciales($convenios, $membresias, $estados, $metodos_pago)
    {
        // Cliente de prueba sin inscripciones
        Cliente::create([
            'run_pasaporte' => '10.000.001-5',
            'nombres' => 'Prueba',
            'apellido_paterno' => 'Sin',
            'apellido_materno' => 'Inscripción',
            'celular' => '+56991111111',
            'email' => 'sin.inscripcion@estoicos.test',
            'direccion' => 'Av. Paseo Colón 1000',
            'fecha_nacimiento' => Carbon::now()->subYears(30),
            'activo' => true,
        ]);

        // Cliente activo con múltiples membresías
        $clienteActivo = Cliente::create([
            'run_pasaporte' => '20.000.002-4',
            'nombres' => 'Roberto',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Martínez',
            'celular' => '+56992222222',
            'email' => 'activo@estoicos.test',
            'direccion' => 'Av. Las Condes 2500',
            'fecha_nacimiento' => Carbon::now()->subYears(35),
            'id_convenio' => $convenios->where('nombre', 'Club de Empresarios')->first()->id,
            'activo' => true,
        ]);

        // 3 inscripciones activas
        for ($i = 0; $i < 3; $i++) {
            $membresia = $membresias->skip($i)->first();
            $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
            
            if (!$precioMembresia) continue;

            Inscripcion::create([
                'id_cliente' => $clienteActivo->id,
                'id_membresia' => $membresia->id,
                'id_convenio' => $convenios->where('nombre', 'Club de Empresarios')->first()->id,
                'id_precio_acordado' => $precioMembresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'fecha_inscripcion' => now()->subMonths($i),
                'fecha_inicio' => now()->subMonths($i),
                'fecha_vencimiento' => now()->addMonths(12 - $i),
                'dia_pago' => 15,
                'precio_base' => $precioMembresia->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precioMembresia->precio_normal,
            ]);
        }

        // Cliente inactivo
        Cliente::create([
            'run_pasaporte' => '30.000.003-3',
            'nombres' => 'Inactivo',
            'apellido_paterno' => 'Usuario',
            'apellido_materno' => 'Test',
            'celular' => '+56993333333',
            'email' => 'inactivo@estoicos.test',
            'direccion' => 'Av. Providencia 1500',
            'fecha_nacimiento' => Carbon::now()->subYears(50),
            'activo' => false,
        ]);
    }

    private function generarRutChileno($faker)
    {
        $rut = $faker->numberBetween(5000000, 25000000);
        $dv = $this->calcularDvRut($rut);
        
        return sprintf('%d.%s-%s',
            intval($rut / 1000000),
            substr(sprintf('%06d', $rut % 1000000), 0, 3) . '.' . substr(sprintf('%06d', $rut % 1000000), 3),
            $dv
        );
    }

    private function calcularDvRut($rut)
    {
        $s = 0;
        $m = 2;
        
        while ($rut != 0) {
            $s += ($rut % 10) * $m;
            $rut = intval($rut / 10);
            $m++;
            if ($m > 7) {
                $m = 2;
            }
        }
        
        $dv = 11 - ($s % 11);
        
        if ($dv === 10) {
            return 'K';
        } elseif ($dv === 11) {
            return '0';
        } else {
            return (string)$dv;
        }
    }
}

