<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Convenio;
use App\Models\PrecioMembresia;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatosRealistasSeeder extends Seeder
{
    // Nombres chilenos reales
    private array $nombresHombre = [
        'Sebastián', 'Matías', 'Nicolás', 'Benjamín', 'Lucas', 'Martín', 'Vicente', 'Agustín',
        'José', 'Diego', 'Felipe', 'Tomás', 'Gabriel', 'Joaquín', 'Maximiliano', 'Francisco',
        'Cristóbal', 'Ignacio', 'Alejandro', 'Daniel', 'Pablo', 'Eduardo', 'Rodrigo', 'Carlos',
        'Andrés', 'Fernando', 'Jorge', 'Luis', 'Miguel', 'Ricardo', 'Sergio', 'Gonzalo'
    ];

    private array $nombresMujer = [
        'Sofía', 'Martina', 'Florencia', 'Valentina', 'Isidora', 'Agustina', 'Antonella', 'Emilia',
        'Catalina', 'Fernanda', 'Constanza', 'Javiera', 'Francisca', 'Camila', 'María José', 'Daniela',
        'Paulina', 'Carolina', 'Andrea', 'Gabriela', 'Natalia', 'Patricia', 'Claudia', 'Verónica',
        'Lorena', 'Paola', 'Marcela', 'Sandra', 'Alejandra', 'Macarena', 'Bárbara', 'Trinidad'
    ];

    private array $apellidos = [
        'González', 'Muñoz', 'Rojas', 'Díaz', 'Pérez', 'Soto', 'Contreras', 'Silva',
        'Martínez', 'Sepúlveda', 'Morales', 'Rodríguez', 'López', 'Fuentes', 'Hernández', 'García',
        'Garrido', 'Bravo', 'Reyes', 'Núñez', 'Jara', 'Vera', 'Torres', 'Araya',
        'Espinoza', 'Valenzuela', 'Tapia', 'Figueroa', 'Flores', 'Sandoval', 'Vega', 'Castillo',
        'Pizarro', 'Leiva', 'Medina', 'Vargas', 'Campos', 'Henríquez', 'Carrasco', 'Guzmán'
    ];

    private array $comunas = [
        'Santiago', 'Providencia', 'Las Condes', 'Ñuñoa', 'La Florida', 'Maipú', 'Puente Alto',
        'San Bernardo', 'Vitacura', 'La Reina', 'Peñalolén', 'Macul', 'San Miguel', 'La Cisterna',
        'Estación Central', 'Quinta Normal', 'Recoleta', 'Independencia', 'Conchalí', 'Renca'
    ];

    public function run(): void
    {
        $this->command->info('🏋️ Generando datos realistas para EstóicosGym...');

        // Obtener datos base
        $membresias = Membresia::where('activo', true)->get();
        $metodosPago = MetodoPago::where('activo', true)->get();
        $convenios = Convenio::where('activo', true)->get();

        if ($membresias->isEmpty()) {
            $this->command->error('No hay membresías activas. Ejecuta primero los seeders base.');
            return;
        }

        $totalClientes = 50;
        $clientesCreados = 0;
        $inscripcionesCreadas = 0;
        $pagosCreados = 0;

        $this->command->info("📝 Creando {$totalClientes} clientes con inscripciones y pagos...");

        for ($i = 0; $i < $totalClientes; $i++) {
            // Determinar género
            $esMujer = fake()->boolean(45);
            $nombres = $esMujer ? $this->nombresMujer : $this->nombresHombre;

            // Generar RUT chileno válido
            $rut = $this->generarRutChileno();

            // Crear cliente con campos correctos del modelo
            $cliente = Cliente::create([
                'uuid' => Str::uuid(),
                'run_pasaporte' => $rut,
                'nombres' => fake()->randomElement($nombres),
                'apellido_paterno' => fake()->randomElement($this->apellidos),
                'apellido_materno' => fake()->randomElement($this->apellidos),
                'email' => fake()->unique()->safeEmail(),
                'celular' => '+569' . fake()->numerify('########'),
                'fecha_nacimiento' => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
                'direccion' => fake()->streetAddress() . ', ' . fake()->randomElement($this->comunas),
                'contacto_emergencia' => fake()->randomElement($this->nombresHombre) . ' ' . fake()->randomElement($this->apellidos),
                'telefono_emergencia' => '+569' . fake()->numerify('########'),
                'activo' => true,
                'id_estado' => 400, // Estado activo cliente
                'observaciones' => fake()->boolean(20) ? fake()->sentence() : null,
            ]);

            $clientesCreados++;

            // Decidir tipo de cliente
            $tipoCliente = fake()->randomElement(['nuevo', 'activo', 'vencido', 'historico']);
            
            // Crear inscripciones según tipo
            $numInscripciones = match($tipoCliente) {
                'nuevo' => 1,
                'activo' => fake()->numberBetween(1, 3),
                'vencido' => fake()->numberBetween(1, 2),
                'historico' => fake()->numberBetween(2, 5),
            };

            $fechaBase = match($tipoCliente) {
                'nuevo' => Carbon::now()->subDays(fake()->numberBetween(1, 15)),
                'activo' => Carbon::now()->subMonths(fake()->numberBetween(1, 6)),
                'vencido' => Carbon::now()->subMonths(fake()->numberBetween(2, 8)),
                'historico' => Carbon::now()->subYears(fake()->numberBetween(1, 2)),
            };

            for ($j = 0; $j < $numInscripciones; $j++) {
                $membresia = $membresias->random();
                $convenio = fake()->boolean(30) && $convenios->isNotEmpty() ? $convenios->random() : null;
                
                // Calcular fechas - las inscripciones van hacia el PASADO, no futuro
                // La primera inscripción (j=0) es la más antigua
                $mesesAtras = ($numInscripciones - 1 - $j) * ($membresia->duracion_meses ?: 1);
                $fechaInicio = $fechaBase->copy()->subMonths($mesesAtras);
                $fechaVencimiento = $fechaInicio->copy()->addMonths($membresia->duracion_meses ?: 0)->addDays($membresia->duracion_dias ?: 0);
                
                // Limitar fecha de vencimiento a máximo 1 año en el futuro
                $maxFechaVencimiento = Carbon::now()->addYear();
                if ($fechaVencimiento->gt($maxFechaVencimiento)) {
                    $fechaVencimiento = $maxFechaVencimiento;
                }
                
                // Determinar estado según fechas y tipo
                $estado = $this->determinarEstadoInscripcion($fechaVencimiento, $tipoCliente, $j, $numInscripciones);
                
                // Obtener precio
                $precioMembresia = PrecioMembresia::where('id_membresia', $membresia->id)
                    ->where('activo', true)
                    ->first();
                
                $precioBase = $precioMembresia ? (float)$precioMembresia->precio_normal : 25000;
                
                // Aplicar descuento de convenio
                $descuento = 0;
                if ($convenio) {
                    // Primero verificar porcentaje, luego monto fijo
                    if ($convenio->descuento_porcentaje > 0) {
                        $descuento = $precioBase * (float)$convenio->descuento_porcentaje / 100;
                    } elseif ($convenio->descuento_monto > 0) {
                        $descuento = (float)$convenio->descuento_monto;
                    }
                }
                
                $precioFinal = max(0, $precioBase - $descuento);

                $inscripcion = Inscripcion::create([
                    'uuid' => Str::uuid(),
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresia->id,
                    'id_convenio' => $convenio?->id,
                    'id_precio_acordado' => $precioMembresia?->id ?? 1,
                    'fecha_inscripcion' => $fechaInicio,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'precio_base' => $precioBase,
                    'descuento_aplicado' => $descuento,
                    'precio_final' => $precioFinal,
                    'id_estado' => $estado,
                    'observaciones' => fake()->boolean(15) ? fake()->sentence() : null,
                ]);

                $inscripcionesCreadas++;

                // Crear pagos para esta inscripción
                $pagosCreados += $this->crearPagosInscripcion($inscripcion, $metodosPago, $estado);
            }

            // Actualizar estado activo del cliente
            if ($tipoCliente === 'vencido' || $tipoCliente === 'historico') {
                $cliente->update(['activo' => fake()->boolean(30)]);
            }
        }

        $this->command->info("✅ Datos creados exitosamente:");
        $this->command->info("   👥 Clientes: {$clientesCreados}");
        $this->command->info("   📋 Inscripciones: {$inscripcionesCreadas}");
        $this->command->info("   💰 Pagos: {$pagosCreados}");
    }

    private function determinarEstadoInscripcion(Carbon $fechaVencimiento, string $tipoCliente, int $index, int $total): int
    {
        // Si es la última inscripción del cliente
        $esUltima = $index === $total - 1;

        if (!$esUltima) {
            // Inscripciones anteriores están finalizadas
            return 103; // Finalizada
        }

        // Estado de la inscripción actual
        return match($tipoCliente) {
            'nuevo', 'activo' => $fechaVencimiento->isFuture() ? 100 : 102, // Activa o Vencida
            'vencido' => 102, // Vencida
            'historico' => fake()->randomElement([102, 103, 104]), // Vencida, Finalizada o Cancelada
        };
    }

    private function crearPagosInscripcion(Inscripcion $inscripcion, $metodosPago, int $estadoInscripcion): int
    {
        $pagosCreados = 0;
        $montoTotal = $inscripcion->precio_final;
        
        if ($montoTotal <= 0) {
            return 0;
        }

        // Decidir si pago único o en cuotas
        $enCuotas = fake()->boolean(25) && $montoTotal >= 30000;
        $numCuotas = $enCuotas ? fake()->numberBetween(2, 3) : 1;
        $montoPorCuota = ceil($montoTotal / $numCuotas);

        for ($i = 0; $i < $numCuotas; $i++) {
            $montoEstaCuota = ($i === $numCuotas - 1) 
                ? $montoTotal - ($montoPorCuota * ($numCuotas - 1)) 
                : $montoPorCuota;

            $fechaPago = $inscripcion->fecha_inicio->copy()->addDays($i * 15);
            
            // Determinar estado del pago
            $estadoPago = $this->determinarEstadoPago($fechaPago, $estadoInscripcion, $i, $numCuotas);
            
            $montoAbonado = match($estadoPago) {
                201 => $montoEstaCuota, // Pagado completo
                202 => (int)($montoEstaCuota * fake()->randomFloat(2, 0.3, 0.8)), // Parcial
                200 => 0, // Pendiente
                default => $montoEstaCuota,
            };

            $metodoPago = $metodosPago->random();

            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $inscripcion->id_cliente,
                'monto_total' => $montoEstaCuota,
                'monto_abonado' => $montoAbonado,
                'monto_pendiente' => $montoEstaCuota - $montoAbonado,
                'fecha_pago' => $estadoPago !== 200 ? $fechaPago : Carbon::now(),
                'id_metodo_pago' => $metodoPago->id,
                'id_estado' => $estadoPago,
                'cantidad_cuotas' => $numCuotas,
                'numero_cuota' => $i + 1,
                'monto_cuota' => $montoEstaCuota,
                'observaciones' => fake()->boolean(10) ? 'Pago cuota ' . ($i + 1) : null,
            ]);

            $pagosCreados++;
        }

        return $pagosCreados;
    }

    private function determinarEstadoPago(Carbon $fechaPago, int $estadoInscripcion, int $index, int $total): int
    {
        // Si la inscripción está activa, probablemente los pagos están al día
        if ($estadoInscripcion === 100) {
            if ($index < $total - 1) {
                return 201; // Pagos anteriores pagados
            }
            return fake()->randomElement([201, 201, 201, 202]); // 75% pagado, 25% parcial
        }

        // Si está vencida, puede haber pagos pendientes
        if ($estadoInscripcion === 102) {
            return fake()->randomElement([201, 201, 202, 200]); // Variado
        }

        // Finalizada o cancelada - todos pagados o anulados
        return fake()->boolean(90) ? 201 : 203; // 90% pagado, 10% anulado
    }

    private function generarRutChileno(): string
    {
        // Generar número base (entre 5 y 25 millones para RUTs realistas)
        $numero = fake()->numberBetween(5000000, 25000000);
        
        // Calcular dígito verificador
        $dv = $this->calcularDigitoVerificador($numero);
        
        // Formatear con puntos y guión
        $numeroFormateado = number_format($numero, 0, '', '.');
        
        return $numeroFormateado . '-' . $dv;
    }

    private function calcularDigitoVerificador(int $numero): string
    {
        $suma = 0;
        $multiplicador = 2;
        
        while ($numero > 0) {
            $suma += ($numero % 10) * $multiplicador;
            $numero = (int)($numero / 10);
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        
        $resto = $suma % 11;
        $dv = 11 - $resto;
        
        if ($dv === 11) return '0';
        if ($dv === 10) return 'K';
        return (string)$dv;
    }
}
