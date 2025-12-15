<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        $nombres = [
            'Juan', 'Pedro', 'Camila', 'Javiera', 'Matías', 'Sofía', 'Valentina', 'Ignacio', 'Fernanda', 'Cristóbal',
            'Francisco', 'Antonia', 'Martina', 'Vicente', 'Josefa', 'Tomás', 'Catalina', 'Diego', 'Daniela', 'Sebastián',
            'Paula', 'Felipe', 'Gabriela', 'Andrés', 'María', 'Benjamín', 'Trinidad', 'Agustín', 'Florencia', 'Emilia',
            'Lucas', 'Isidora', 'Joaquín', 'Amanda', 'Maximiliano', 'Jose', 'Constanza', 'Alejandro', 'Valeria', 'Manuel',
            'Renata', 'Simón', 'Pablo', 'Francisca', 'Rafael', 'Josefina', 'Samuel', 'Anaís', 'Leonardo', 'Bárbara'
        ];
        $apellidos = [
            'González', 'Muñoz', 'Rojas', 'Díaz', 'Pérez', 'Soto', 'Contreras', 'Silva', 'Martínez', 'Sepúlveda',
            'Morales', 'Rodríguez', 'López', 'Fuentes', 'Hernández', 'Torres', 'Araya', 'Flores', 'Espinoza', 'Valenzuela',
            'Castillo', 'Tapia', 'Reyes', 'Gutiérrez', 'Castro', 'Pizarro', 'Álvarez', 'Vásquez', 'Sanhueza', 'Carrasco',
            'Fernández', 'Ramírez', 'Herrera', 'Molina', 'Vega', 'Campos', 'Jara', 'Vergara', 'Rivera', 'Figueroa',
            'Cornejo', 'Salazar', 'Miranda', 'Cárdenas', 'Riquelme', 'Bravo', 'Cortés', 'Saavedra', 'Navarro', 'Ortega'
        ];
        return [
            'run_pasaporte' => $this->generarRutChileno(),
            'nombres' => $this->faker->randomElement($nombres),
            'apellido_paterno' => $this->faker->randomElement($apellidos),
            'apellido_materno' => $this->faker->randomElement($apellidos),
            'email' => $this->faker->unique()->safeEmail(),
            'celular' => '+56' . $this->faker->numerify('#########'),
            'direccion' => $this->faker->address(),
            'fecha_nacimiento' => $this->faker->date(),
            'contacto_emergencia' => $this->faker->randomElement($nombres) . ' ' . $this->faker->randomElement($apellidos),
            'telefono_emergencia' => '+56' . $this->faker->numerify('#########'),
            'observaciones' => $this->faker->sentence(),
            'activo' => true,
        ];
    }
    public function generarRutChileno(): string
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
