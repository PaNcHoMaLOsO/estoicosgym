<?php

namespace Database\Seeders;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder para crear datos de prueba realistas
 * Incluye escenarios variados para testing de consistencia
 */
class DatosPruebaRealistasSeeder extends Seeder
{
    // Nombres chilenos realistas
    private array $nombresHombres = [
        'Sebastián', 'Matías', 'Nicolás', 'Benjamín', 'Lucas', 'Martín', 'Tomás', 'Joaquín',
        'Diego', 'Felipe', 'Vicente', 'Gabriel', 'Agustín', 'Maximiliano', 'José', 'Daniel',
        'Francisco', 'Cristóbal', 'Ignacio', 'Andrés', 'Pablo', 'Rodrigo', 'Alejandro', 'Carlos'
    ];

    private array $nombresMujeres = [
        'Sofía', 'Martina', 'Florencia', 'Valentina', 'Isidora', 'Agustina', 'Catalina', 'Emilia',
        'Josefa', 'Antonella', 'Maite', 'Trinidad', 'Amanda', 'Fernanda', 'Camila', 'Isabella',
        'Francisca', 'Antonia', 'Constanza', 'Javiera', 'María José', 'Daniela', 'Paulina', 'Carolina'
    ];

    private array $apellidos = [
        'González', 'Muñoz', 'Rojas', 'Díaz', 'Pérez', 'Soto', 'Contreras', 'Silva',
        'Martínez', 'Sepúlveda', 'Morales', 'Rodríguez', 'López', 'Fuentes', 'Hernández', 'García',
        'Vargas', 'Castillo', 'Tapia', 'Reyes', 'Araya', 'Espinoza', 'Guerrero', 'Bravo',
        'Vera', 'Núñez', 'Carrasco', 'Jara', 'Torres', 'Figueroa', 'Vega', 'Campos'
    ];

    private array $calles = [
        'Av. O\'Higgins', 'Av. Libertador', 'Calle Principal', 'Pasaje Las Flores',
        'Av. Colón', 'Calle Lautaro', 'Pasaje Los Aromos', 'Av. Alemania',
        'Calle Caupolicán', 'Av. Ercilla', 'Pasaje El Sol', 'Calle Tucapel'
    ];

    // Precios por membresía (id_membresia => [id_precio, precio, duracion_dias])
    private array $membresias = [
        1 => ['id_precio' => 1, 'precio' => 299000, 'dias' => 360, 'nombre' => 'Anual'],
        2 => ['id_precio' => 2, 'precio' => 170000, 'dias' => 180, 'nombre' => 'Semestral'],
        3 => ['id_precio' => 3, 'precio' => 99000, 'dias' => 90, 'nombre' => 'Trimestral'],
        4 => ['id_precio' => 4, 'precio' => 40000, 'dias' => 30, 'nombre' => 'Mensual'],
        5 => ['id_precio' => 5, 'precio' => 8000, 'dias' => 1, 'nombre' => 'Pase Diario'],
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║     CREANDO DATOS DE PRUEBA REALISTAS                        ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 1: Clientes con membresías activas y pagos completos (15)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📗 Creando clientes con membresías activas y pagos completos...');
        $this->crearClientesActivosPagados(15);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 2: Clientes con membresías activas y pagos parciales (8)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📙 Creando clientes con pagos parciales...');
        $this->crearClientesPagosParciales(8);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 3: Clientes con membresías vencidas (10)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📕 Creando clientes con membresías vencidas...');
        $this->crearClientesMembresiaVencida(10);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 4: Clientes con membresías pausadas (5)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📘 Creando clientes con membresías pausadas...');
        $this->crearClientesMembresiaPausada(5);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 5: Clientes nuevos sin pagar aún (5)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📓 Creando clientes nuevos sin pagar...');
        $this->crearClientesNuevosSinPagar(5);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 6: Clientes con múltiples inscripciones (historial) (5)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📔 Creando clientes con historial de membresías...');
        $this->crearClientesConHistorial(5);

        // ═══════════════════════════════════════════════════════════════
        // GRUPO 7: Clientes inactivos (3)
        // ═══════════════════════════════════════════════════════════════
        $this->command->comment('📒 Creando clientes inactivos...');
        $this->crearClientesInactivos(3);

        $this->command->info('');
        $this->command->info('✅ Datos de prueba creados exitosamente');
        $this->command->info('');
    }

    /**
     * Grupo 1: Clientes activos con todo pagado
     */
    private function crearClientesActivosPagados(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            $membresiaId = $this->randomMembresia([1, 2, 3, 4]); // Sin pase diario
            $membresia = $this->membresias[$membresiaId];
            
            $fechaInicio = Carbon::now()->subDays(rand(5, $membresia['dias'] - 10));
            $fechaVencimiento = $fechaInicio->copy()->addDays($membresia['dias']);

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            ]);

            // Pago completo
            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => $membresia['precio'],
                'monto_pendiente' => 0,
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => rand(1, 3),
                'id_estado' => EstadosCodigo::PAGO_PAGADO,
                'tipo_pago' => 'completo',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes activos con pagos completos");
    }

    /**
     * Grupo 2: Clientes con pagos parciales
     */
    private function crearClientesPagosParciales(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            $membresiaId = $this->randomMembresia([1, 2, 3]); // Solo membresías caras
            $membresia = $this->membresias[$membresiaId];
            
            $fechaInicio = Carbon::now()->subDays(rand(5, 30));
            $fechaVencimiento = $fechaInicio->copy()->addDays($membresia['dias']);

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            ]);

            // Pago parcial (50-80%)
            $porcentaje = rand(50, 80) / 100;
            $abonado = round($membresia['precio'] * $porcentaje);
            $pendiente = $membresia['precio'] - $abonado;

            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => $abonado,
                'monto_pendiente' => $pendiente,
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => rand(1, 3),
                'id_estado' => EstadosCodigo::PAGO_PARCIAL,
                'tipo_pago' => 'parcial',
                'observaciones' => 'Pago inicial, saldo pendiente',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes con pagos parciales");
    }

    /**
     * Grupo 3: Clientes con membresías vencidas
     */
    private function crearClientesMembresiaVencida(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            $membresiaId = $this->randomMembresia([3, 4]); // Trimestrales y mensuales
            $membresia = $this->membresias[$membresiaId];
            
            // Fechas en el pasado
            $diasVencida = rand(5, 60);
            $fechaVencimiento = Carbon::now()->subDays($diasVencida);
            $fechaInicio = $fechaVencimiento->copy()->subDays($membresia['dias']);

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_VENCIDA,
            ]);

            // Algunos pagados, algunos no
            $pagado = rand(0, 1);
            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => $pagado ? $membresia['precio'] : 0,
                'monto_pendiente' => $pagado ? 0 : $membresia['precio'],
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => rand(1, 3),
                'id_estado' => $pagado ? EstadosCodigo::PAGO_PAGADO : EstadosCodigo::PAGO_PENDIENTE,
                'tipo_pago' => 'completo',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes con membresías vencidas");
    }

    /**
     * Grupo 4: Clientes con membresías pausadas
     */
    private function crearClientesMembresiaPausada(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            $membresiaId = $this->randomMembresia([1, 2, 3]);
            $membresia = $this->membresias[$membresiaId];
            
            $fechaInicio = Carbon::now()->subDays(rand(30, 60));
            $fechaVencimiento = $fechaInicio->copy()->addDays($membresia['dias']);
            $fechaPausa = Carbon::now()->subDays(rand(5, 15));

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
                'pausada' => true,
                'dias_pausa' => 15,
                'fecha_pausa_inicio' => $fechaPausa,
                'fecha_pausa_fin' => $fechaPausa->copy()->addDays(15),
                'razon_pausa' => $this->randomRazonPausa(),
                'pausas_realizadas' => 1,
                'max_pausas_permitidas' => 2,
            ]);

            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => $membresia['precio'],
                'monto_pendiente' => 0,
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => rand(1, 3),
                'id_estado' => EstadosCodigo::PAGO_PAGADO,
                'tipo_pago' => 'completo',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes con membresías pausadas");
    }

    /**
     * Grupo 5: Clientes nuevos sin pagar
     */
    private function crearClientesNuevosSinPagar(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            $membresiaId = $this->randomMembresia([3, 4]);
            $membresia = $this->membresias[$membresiaId];
            
            $fechaInicio = Carbon::now()->subDays(rand(1, 5));
            $fechaVencimiento = $fechaInicio->copy()->addDays($membresia['dias']);

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            ]);

            // Sin pago - pendiente
            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => 0,
                'monto_pendiente' => $membresia['precio'],
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => 1,
                'id_estado' => EstadosCodigo::PAGO_PENDIENTE,
                'tipo_pago' => 'pendiente',
                'observaciones' => 'Pendiente de pago',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes nuevos sin pagar");
    }

    /**
     * Grupo 6: Clientes con historial de membresías
     */
    private function crearClientesConHistorial(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente();
            
            // Crear 2-4 inscripciones históricas
            $cantidadInscripciones = rand(2, 4);
            $fechaBase = Carbon::now()->subMonths($cantidadInscripciones * 3);

            for ($j = 0; $j < $cantidadInscripciones; $j++) {
                $membresiaId = $this->randomMembresia([3, 4]);
                $membresia = $this->membresias[$membresiaId];
                
                $fechaInicio = $fechaBase->copy()->addMonths($j * 3);
                $fechaVencimiento = $fechaInicio->copy()->addDays($membresia['dias']);
                
                $esUltima = ($j == $cantidadInscripciones - 1);
                $estaVencida = $fechaVencimiento->isPast();

                $estado = $esUltima && !$estaVencida 
                    ? EstadosCodigo::INSCRIPCION_ACTIVA 
                    : EstadosCodigo::INSCRIPCION_VENCIDA;

                $inscripcion = Inscripcion::create([
                    'uuid' => Str::uuid(),
                    'id_cliente' => $cliente->id,
                    'id_membresia' => $membresiaId,
                    'id_precio_acordado' => $membresia['id_precio'],
                    'fecha_inscripcion' => $fechaInicio,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'precio_base' => $membresia['precio'],
                    'descuento_aplicado' => 0,
                    'precio_final' => $membresia['precio'],
                    'id_estado' => $estado,
                ]);

                Pago::create([
                    'uuid' => Str::uuid(),
                    'id_inscripcion' => $inscripcion->id,
                    'id_cliente' => $cliente->id,
                    'monto_total' => $membresia['precio'],
                    'monto_abonado' => $membresia['precio'],
                    'monto_pendiente' => 0,
                    'fecha_pago' => $fechaInicio,
                    'id_metodo_pago' => rand(1, 3),
                    'id_estado' => EstadosCodigo::PAGO_PAGADO,
                    'tipo_pago' => 'completo',
                ]);
            }
        }
        $this->command->info("   ✓ {$cantidad} clientes con historial de membresías");
    }

    /**
     * Grupo 7: Clientes inactivos
     */
    private function crearClientesInactivos(int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearCliente(false); // activo = false
            
            $membresiaId = $this->randomMembresia([4]);
            $membresia = $this->membresias[$membresiaId];
            
            // Membresía antigua cancelada
            $fechaVencimiento = Carbon::now()->subMonths(rand(3, 12));
            $fechaInicio = $fechaVencimiento->copy()->subDays($membresia['dias']);

            $inscripcion = Inscripcion::create([
                'uuid' => Str::uuid(),
                'id_cliente' => $cliente->id,
                'id_membresia' => $membresiaId,
                'id_precio_acordado' => $membresia['id_precio'],
                'fecha_inscripcion' => $fechaInicio,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'precio_base' => $membresia['precio'],
                'descuento_aplicado' => 0,
                'precio_final' => $membresia['precio'],
                'id_estado' => EstadosCodigo::INSCRIPCION_CANCELADA,
            ]);

            Pago::create([
                'uuid' => Str::uuid(),
                'id_inscripcion' => $inscripcion->id,
                'id_cliente' => $cliente->id,
                'monto_total' => $membresia['precio'],
                'monto_abonado' => $membresia['precio'],
                'monto_pendiente' => 0,
                'fecha_pago' => $fechaInicio,
                'id_metodo_pago' => rand(1, 3),
                'id_estado' => EstadosCodigo::PAGO_PAGADO,
                'tipo_pago' => 'completo',
            ]);
        }
        $this->command->info("   ✓ {$cantidad} clientes inactivos");
    }

    /**
     * Crear un cliente con datos realistas chilenos
     */
    private function crearCliente(bool $activo = true): Cliente
    {
        $esHombre = rand(0, 1);
        $nombres = $esHombre ? $this->nombresHombres : $this->nombresMujeres;
        
        $nombre = $nombres[array_rand($nombres)];
        $apellidoPaterno = $this->apellidos[array_rand($this->apellidos)];
        $apellidoMaterno = $this->apellidos[array_rand($this->apellidos)];
        
        // RUT chileno fake
        $rut = rand(8, 25) . '.' . rand(100, 999) . '.' . rand(100, 999) . '-' . rand(0, 9);
        
        // Email basado en nombre (SIN TILDES para emails válidos)
        $emailBase = $this->normalizarTextoEmail($nombre) . '.' . $this->normalizarTextoEmail($apellidoPaterno);
        $email = strtolower($emailBase) . rand(1, 99) . '@' . ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com'][rand(0, 3)];
        
        // Celular chileno
        $celular = '+569' . rand(10000000, 99999999);
        
        // Dirección
        $direccion = $this->calles[array_rand($this->calles)] . ' ' . rand(100, 2000) . ', Los Ángeles';
        
        // Fecha nacimiento (18-60 años)
        $edad = rand(18, 60);
        $fechaNacimiento = Carbon::now()->subYears($edad)->subDays(rand(0, 365));

        return Cliente::create([
            'uuid' => Str::uuid(),
            'run_pasaporte' => $rut,
            'nombres' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'celular' => $celular,
            'email' => $email,
            'direccion' => $direccion,
            'fecha_nacimiento' => $fechaNacimiento,
            'contacto_emergencia' => $this->nombresHombres[array_rand($this->nombresHombres)] . ' ' . $this->apellidos[array_rand($this->apellidos)],
            'telefono_emergencia' => '+569' . rand(10000000, 99999999),
            'activo' => $activo,
            'id_estado' => $activo ? EstadosCodigo::CLIENTE_ACTIVO : EstadosCodigo::CLIENTE_CANCELADO,
        ]);
    }
    
    /**
     * Normalizar texto para emails (quitar tildes y caracteres especiales)
     */
    private function normalizarTextoEmail(string $texto): string
    {
        $tildes = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', ' '];
        $sinTildes = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n', ''];
        return str_replace($tildes, $sinTildes, $texto);
    }

    private function randomMembresia(array $opciones): int
    {
        return $opciones[array_rand($opciones)];
    }

    private function randomRazonPausa(): string
    {
        $razones = [
            'Viaje de trabajo',
            'Vacaciones',
            'Lesión temporal',
            'Motivos personales',
            'Estudios',
            'Mudanza temporal',
        ];
        return $razones[array_rand($razones)];
    }
}
