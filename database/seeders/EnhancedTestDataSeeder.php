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
            'Elena', 'Francisca', 'Sofía', 'Marta', 'Susana', 'Alejandra', 'Constanza', 'Eugenia',
        ];

        $apellidosChilenos = [
            'González', 'Muñoz', 'Martínez', 'García', 'López', 'Rodríguez', 'Hernández', 'Pérez',
            'Flores', 'Vargas', 'Castro', 'Torres', 'Silva', 'Morales', 'Ortiz', 'Jiménez',
            'Ramírez', 'Carrasco', 'Soto', 'Núñez', 'Vega', 'Ruiz', 'Acuña', 'Fuentes',
            'Rojas', 'Araya', 'Valenzuela', 'Reyes', 'Contreras', 'Moreno', 'Vidal', 'Bravo',
            'Díaz', 'Medina', 'Parra', 'Romero', 'Aguilar', 'Miranda', 'Sánchez', 'Valdez',
        ];

        // Crear 100 clientes con datos realistas y casos diversos
        for ($i = 0; $i < 100; $i++) {
            $cliente = Cliente::create([
                'run_pasaporte' => $this->generarRutChileno($faker),
                'nombres' => $faker->randomElement($nombresChilenos),
                'apellido_paterno' => $faker->randomElement($apellidosChilenos),
                'apellido_materno' => $faker->randomElement($apellidosChilenos),
                'celular' => $faker->numerify('+569 #### ####'),
                'email' => $faker->unique()->safeEmail(),
                'direccion' => $faker->streetAddress(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-70 years', '-16 years'),
                'contacto_emergencia' => $faker->randomElement($nombresChilenos) . ' ' . $faker->randomElement($apellidosChilenos),
                'telefono_emergencia' => $faker->numerify('+569 #### ####'),
                'id_convenio' => $faker->boolean(40) ? $faker->randomElement($convenios->pluck('id')->toArray()) : null,
                'observaciones' => $faker->optional(0.3)->text(80),
                'activo' => $faker->boolean(88),
                'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
            ]);

            // Generar inscripciones (0 a 5 por cliente para variar más)
            $num_inscripciones = $faker->numberBetween(0, 5);
            for ($j = 0; $j < $num_inscripciones; $j++) {
                $membresia = $membresias->random();
                $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
                
                if (!$precioMembresia) {
                    continue;
                }

                $mesesAtras = $faker->numberBetween(1, 18);
                $fechaInicio = $now->copy()->subMonths($mesesAtras)->startOfDay();
                $fechaVencimiento = $fechaInicio->copy()->addDays($membresia->duracion_dias);
                
                $estado = $faker->randomElement($estados);
                $tieneDescuento = $faker->boolean(45);
                $descuentoAplicado = 0;
                
                if ($tieneDescuento) {
                    $descuentoAplicado = $faker->randomFloat(2, 5000, 35000);
                }

                $precioFinal = max(0, $precioMembresia->precio_normal - $descuentoAplicado);
                
                $inscripcion = Inscripcion::create([
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_convenio' => $faker->boolean(50) ? $faker->randomElement($convenios->pluck('id')->toArray()) : null,
                    'id_precio_acordado' => $precioMembresia->id,
                    'id_estado' => $estado->id,
                    'id_motivo_descuento' => $tieneDescuento && $faker->boolean(60) ? $faker->randomElement($motivos_descuento->pluck('id')->toArray()) : null,
                    'fecha_inscripcion' => $fechaInicio,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'dia_pago' => $faker->optional(0.75)->numberBetween(1, 28),
                    'precio_base' => $precioMembresia->precio_normal,
                    'descuento_aplicado' => $descuentoAplicado,
                    'precio_final' => $precioFinal,
                    'observaciones' => $faker->optional(0.2)->text(80),
                    'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
                ]);

                // Generar pagos con múltiples casos de uso
                $this->generarPagos($inscripcion, $estado, $precioFinal, $fechaInicio, $now, $faker, $estados, $metodos_pago);
            }
        }

        $this->crearClientesEspeciales($convenios, $membresias, $estados, $metodos_pago);
        $this->crearCasosEspecificos($convenios, $membresias, $estados, $metodos_pago, $motivos_descuento);
    }

    private function generarPagos($inscripcion, $estado, $precioFinal, $fechaInicio, $now, $faker, $estados, $metodos_pago)
    {
        // Casos de pago diferentes
        $rand = $faker->numberBetween(1, 10);

        // Caso 1: Sin pagos (20%)
        if ($rand === 1 || $rand === 2) {
            return;
        }

        // Caso 2: 100% Pagado (30%)
        if ($rand === 3 || $rand === 4 || $rand === 5) {
            $fechaPago = $faker->dateTimeBetween($fechaInicio, $now);
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_metodo_pago_principal' => $faker->randomElement($metodos_pago->pluck('id')->toArray()),
                'monto_abonado' => $precioFinal,
                'monto_pendiente' => 0,
                'fecha_pago' => $fechaPago,
                'referencia_pago' => $faker->optional(0.6)->numerify('REF-########'),
                'id_estado' => $estados->where('nombre', 'Pagado')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
                'observaciones' => $faker->optional(0.1)->text(50),
                'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
            ]);
            return;
        }

        // Caso 3: Pago parcial con abono (20%)
        if ($rand === 6 || $rand === 7) {
            $montoAbonado = $faker->randomFloat(2, $precioFinal * 0.3, $precioFinal * 0.8);
            $fechaPago = $faker->dateTimeBetween($fechaInicio, $now);
            
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_metodo_pago_principal' => $faker->randomElement($metodos_pago->pluck('id')->toArray()),
                'monto_abonado' => $montoAbonado,
                'monto_pendiente' => max(0, $precioFinal - $montoAbonado),
                'fecha_pago' => $fechaPago,
                'referencia_pago' => $faker->optional(0.7)->numerify('REF-########'),
                'id_estado' => $estados->where('nombre', 'Parcial')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
                'observaciones' => $faker->optional(0.15)->text(50),
                'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
            ]);
            return;
        }

        // Caso 4: Múltiples cuotas (20%)
        if ($rand === 8 || $rand === 9) {
            $cantidadCuotas = $faker->randomElement([2, 3, 4, 5, 6]);
            $montoPorCuota = $precioFinal / $cantidadCuotas;
            $grupoPago = \Illuminate\Support\Str::uuid();
            
            for ($cuota = 1; $cuota <= $cantidadCuotas; $cuota++) {
                $monto = ($cuota === $cantidadCuotas) 
                    ? $precioFinal - (($cantidadCuotas - 1) * $montoPorCuota)
                    : $montoPorCuota;
                
                $fechaPago = $faker->dateTimeBetween($fechaInicio, $now);
                $estadoPago = $faker->randomElement([
                    $estados->where('nombre', 'Pagado')->first()->id,
                    $estados->where('nombre', 'Pendiente')->first()->id,
                ]);
                
                Pago::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'id_inscripcion' => $inscripcion->id,
                    'id_metodo_pago_principal' => $faker->randomElement($metodos_pago->pluck('id')->toArray()),
                    'monto_abonado' => $monto,
                    'monto_pendiente' => $estadoPago === $estados->where('nombre', 'Pagado')->first()->id ? 0 : $monto,
                    'fecha_pago' => $fechaPago,
                    'referencia_pago' => $faker->optional(0.5)->numerify('CUOTA-#-' . $cuota),
                    'id_estado' => $estadoPago,
                    'es_plan_cuotas' => true,
                    'cantidad_cuotas' => $cantidadCuotas,
                    'numero_cuota' => $cuota,
                    'monto_cuota' => $monto,
                    'fecha_vencimiento_cuota' => $faker->dateTimeBetween($now, $now->copy()->addMonths(6)),
                    'grupo_pago' => $grupoPago,
                    'observaciones' => "Cuota {$cuota} de {$cantidadCuotas}",
                    'created_at' => $faker->dateTimeBetween('-18 months', 'now'),
                ]);
            }
            return;
        }

        // Caso 5: Pendiente de pago (10%)
        if ($rand === 10) {
            // No se crea pago
            return;
        }
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
            'id_convenio' => $convenios->where('nombre', 'Club de Empresarios')->first()->id ?? null,
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
                'id_convenio' => $convenios->where('nombre', 'Club de Empresarios')->first()->id ?? null,
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

    private function crearCasosEspecificos($convenios, $membresias, $estados, $metodos_pago, $motivos_descuento)
    {
        $faker = Faker::create('es_ES');
        $now = Carbon::now();

        // CASO 1: Cliente corporativo con muchos clientes
        $clienteCorporativo = Cliente::create([
            'run_pasaporte' => '40.000.004-2',
            'nombres' => 'Corporativo',
            'apellido_paterno' => 'Premium',
            'apellido_materno' => 'Test',
            'celular' => '+56994444444',
            'email' => 'corporativo@estoicos.test',
            'direccion' => 'Av. Andrés Bello 3500',
            'fecha_nacimiento' => Carbon::now()->subYears(45),
            'id_convenio' => $convenios->first()->id,
            'observaciones' => 'Cliente corporativo con descuentos especiales',
            'activo' => true,
        ]);

        // 4 inscripciones corporativas pagadas
        for ($i = 0; $i < 4; $i++) {
            $membresia = $membresias->skip($i)->first();
            $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
            
            if (!$precioMembresia) continue;

            $inscripcion = Inscripcion::create([
                'id_cliente' => $clienteCorporativo->id,
                'id_membresia' => $membresia->id,
                'id_convenio' => $convenios->first()->id,
                'id_precio_acordado' => $precioMembresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'id_motivo_descuento' => $motivos_descuento->first()->id,
                'fecha_inscripcion' => now()->subMonths(6),
                'fecha_inicio' => now()->subMonths(6),
                'fecha_vencimiento' => now()->addMonths(6),
                'dia_pago' => 10,
                'precio_base' => $precioMembresia->precio_normal,
                'descuento_aplicado' => $precioMembresia->precio_normal * 0.15, // 15% descuento
                'precio_final' => $precioMembresia->precio_normal * 0.85,
            ]);

            // Pago completo
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_metodo_pago_principal' => $metodos_pago->where('codigo', 'transferencia')->first()->id,
                'monto_abonado' => $inscripcion->precio_final,
                'monto_pendiente' => 0,
                'fecha_pago' => $now->subMonths(5),
                'referencia_pago' => 'CORP-' . sprintf('%04d', $i + 1),
                'id_estado' => $estados->where('nombre', 'Pagado')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
            ]);
        }

        // CASO 2: Cliente con plan de cuotas pendientes
        $clienteCuotas = Cliente::create([
            'run_pasaporte' => '50.000.005-1',
            'nombres' => 'Cuotas',
            'apellido_paterno' => 'Mensual',
            'apellido_materno' => 'Test',
            'celular' => '+56995555555',
            'email' => 'cuotas@estoicos.test',
            'direccion' => 'Av. Costanera 4500',
            'fecha_nacimiento' => Carbon::now()->subYears(28),
            'activo' => true,
        ]);

        $membresiaCuotas = $membresias->first();
        $precioCuotas = PrecioMembresia::where('id_membresia', $membresiaCuotas->id)->latest()->first();
        
        if ($precioCuotas) {
            $inscripcionCuotas = Inscripcion::create([
                'id_cliente' => $clienteCuotas->id,
                'id_membresia' => $membresiaCuotas->id,
                'id_precio_acordado' => $precioCuotas->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'fecha_inscripcion' => now()->subMonths(3),
                'fecha_inicio' => now()->subMonths(3),
                'fecha_vencimiento' => now()->addMonths(9),
                'dia_pago' => 20,
                'precio_base' => $precioCuotas->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precioCuotas->precio_normal,
            ]);

            // 6 cuotas: 3 pagadas, 3 pendientes
            $grupoCuotas = \Illuminate\Support\Str::uuid();
            $montoPorCuota = $precioCuotas->precio_normal / 6;

            for ($cuota = 1; $cuota <= 6; $cuota++) {
                $monto = ($cuota === 6) 
                    ? $precioCuotas->precio_normal - (5 * $montoPorCuota)
                    : $montoPorCuota;

                $estaCuota = $cuota <= 3 ? 'Pagado' : 'Pendiente';
                $fechaPago = $cuota <= 3 ? $now->subMonths(4 - $cuota) : $now->addMonths($cuota - 3);

                Pago::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'id_inscripcion' => $inscripcionCuotas->id,
                    'id_metodo_pago_principal' => $metodos_pago->random()->id,
                    'monto_abonado' => $monto,
                    'monto_pendiente' => $estaCuota === 'Pendiente' ? $monto : 0,
                    'fecha_pago' => $fechaPago,
                    'referencia_pago' => 'CUOTA-' . sprintf('%02d', $cuota),
                    'id_estado' => $estados->where('nombre', $estaCuota)->first()->id,
                    'es_plan_cuotas' => true,
                    'cantidad_cuotas' => 6,
                    'numero_cuota' => $cuota,
                    'monto_cuota' => $monto,
                    'fecha_vencimiento_cuota' => $now->copy()->addMonths($cuota),
                    'grupo_pago' => $grupoCuotas,
                    'observaciones' => "Cuota {$cuota}/6",
                ]);
            }
        }

        // CASO 3: Cliente con múltiples métodos de pago
        $clienteMetodos = Cliente::create([
            'run_pasaporte' => '60.000.006-0',
            'nombres' => 'Mixto',
            'apellido_paterno' => 'Métodos',
            'apellido_materno' => 'Test',
            'celular' => '+56996666666',
            'email' => 'metodos@estoicos.test',
            'direccion' => 'Av. Moneda 5500',
            'fecha_nacimiento' => Carbon::now()->subYears(32),
            'activo' => true,
        ]);

        // 3 inscripciones con diferentes métodos de pago
        $metodosDisponibles = $metodos_pago->pluck('id')->toArray();
        
        for ($i = 0; $i < 3; $i++) {
            $membresia = $membresias->skip($i)->first();
            $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
            
            if (!$precioMembresia) continue;

            $inscripcion = Inscripcion::create([
                'id_cliente' => $clienteMetodos->id,
                'id_membresia' => $membresia->id,
                'id_precio_acordado' => $precioMembresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'fecha_inscripcion' => now()->subMonths(2),
                'fecha_inicio' => now()->subMonths(2),
                'fecha_vencimiento' => now()->addMonths(10),
                'dia_pago' => 5 + ($i * 10),
                'precio_base' => $precioMembresia->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precioMembresia->precio_normal,
            ]);

            // Pago con método diferente para cada uno
            $metodoId = $metodosDisponibles[$i % count($metodosDisponibles)];
            
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_metodo_pago_principal' => $metodoId,
                'monto_abonado' => $inscripcion->precio_final,
                'monto_pendiente' => 0,
                'fecha_pago' => $now->subMonths(1),
                'referencia_pago' => 'MTD-' . sprintf('%04d', $i + 1),
                'id_estado' => $estados->where('nombre', 'Pagado')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
            ]);
        }

        // CASO 4: Cliente con descuentos variados
        $clienteDescuentos = Cliente::create([
            'run_pasaporte' => '70.000.007-9',
            'nombres' => 'Descuentos',
            'apellido_paterno' => 'Especial',
            'apellido_materno' => 'Test',
            'celular' => '+56997777777',
            'email' => 'descuentos@estoicos.test',
            'direccion' => 'Av. Lastarria 6500',
            'fecha_nacimiento' => Carbon::now()->subYears(55),
            'id_convenio' => $convenios->first()->id,
            'observaciones' => 'Cliente con beneficios y descuentos',
            'activo' => true,
        ]);

        // 3 inscripciones con diferentes tipos de descuento
        $motivosDescuentoArray = $motivos_descuento->pluck('id')->toArray();
        
        for ($i = 0; $i < 3; $i++) {
            $membresia = $membresias->skip($i)->first();
            $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
            
            if (!$precioMembresia) continue;

            $descuentoPorcentaje = [0.10, 0.20, 0.25][$i];
            $descuentoMonto = $precioMembresia->precio_normal * $descuentoPorcentaje;

            $inscripcion = Inscripcion::create([
                'id_cliente' => $clienteDescuentos->id,
                'id_membresia' => $membresia->id,
                'id_convenio' => $convenios->first()->id,
                'id_precio_acordado' => $precioMembresia->id,
                'id_estado' => $estados->where('nombre', 'Activa')->first()->id,
                'id_motivo_descuento' => $motivosDescuentoArray[$i % count($motivosDescuentoArray)],
                'fecha_inscripcion' => now()->subMonths(4),
                'fecha_inicio' => now()->subMonths(4),
                'fecha_vencimiento' => now()->addMonths(8),
                'dia_pago' => 25,
                'precio_base' => $precioMembresia->precio_normal,
                'descuento_aplicado' => $descuentoMonto,
                'precio_final' => $precioMembresia->precio_normal - $descuentoMonto,
            ]);

            // Pago parcial
            $pagoMonto = $inscripcion->precio_final * 0.6;
            
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_metodo_pago_principal' => $metodos_pago->random()->id,
                'monto_abonado' => $pagoMonto,
                'monto_pendiente' => $inscripcion->precio_final - $pagoMonto,
                'fecha_pago' => $now->subMonths(3),
                'referencia_pago' => 'DESC-' . sprintf('%04d', $i + 1),
                'id_estado' => $estados->where('nombre', 'Parcial')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
            ]);
        }

        // CASO 5: Cliente antiguo con inscripción vencida
        $clienteVencido = Cliente::create([
            'run_pasaporte' => '80.000.008-8',
            'nombres' => 'Vencido',
            'apellido_paterno' => 'Antiguo',
            'apellido_materno' => 'Test',
            'celular' => '+56998888888',
            'email' => 'vencido@estoicos.test',
            'direccion' => 'Av. República 7500',
            'fecha_nacimiento' => Carbon::now()->subYears(42),
            'activo' => true,
        ]);

        $membresia = $membresias->last();
        $precio = PrecioMembresia::where('id_membresia', $membresia->id)->latest()->first();
        
        if ($precio) {
            $inscripcionVencida = Inscripcion::create([
                'id_cliente' => $clienteVencido->id,
                'id_membresia' => $membresia->id,
                'id_precio_acordado' => $precio->id,
                'id_estado' => $estados->where('nombre', 'Vencida')->first()->id,
                'fecha_inscripcion' => now()->subMonths(15),
                'fecha_inicio' => now()->subMonths(15),
                'fecha_vencimiento' => now()->subMonths(3), // Vencida hace 3 meses
                'dia_pago' => 1,
                'precio_base' => $precio->precio_normal,
                'descuento_aplicado' => 0,
                'precio_final' => $precio->precio_normal,
            ]);

            // Pago 80%
            Pago::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_inscripcion' => $inscripcionVencida->id,
                'id_metodo_pago_principal' => $metodos_pago->first()->id,
                'monto_abonado' => $precio->precio_normal * 0.8,
                'monto_pendiente' => $precio->precio_normal * 0.2,
                'fecha_pago' => $now->subMonths(8),
                'referencia_pago' => 'VENC-001',
                'id_estado' => $estados->where('nombre', 'Parcial')->first()->id,
                'cantidad_cuotas' => 1,
                'numero_cuota' => 1,
            ]);
        }
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

