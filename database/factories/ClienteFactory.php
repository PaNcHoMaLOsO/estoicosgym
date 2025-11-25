<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'run_pasaporte' => $this->faker->numerify('##.###.###-#'),
            'nombres' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'celular' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->address(),
            'fecha_nacimiento' => $this->faker->date('Y-m-d', '-30 years'),
            'contacto_emergencia' => $this->faker->name(),
            'telefono_emergencia' => $this->faker->phoneNumber(),
            'id_convenio' => null,
            'observaciones' => $this->faker->optional()->sentence(),
            'activo' => true,
        ];
    }
}
