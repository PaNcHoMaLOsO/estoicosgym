<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'run_pasaporte' => $this->faker->unique()->numerify('##.###.###-#'),
            'nombres' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'celular' => '+56' . $this->faker->numerify('#########'),
            'direccion' => $this->faker->address(),
            'fecha_nacimiento' => $this->faker->dateOfBirth(),
            'contacto_emergencia' => $this->faker->name(),
            'telefono_emergencia' => '+56' . $this->faker->numerify('#########'),
            'observaciones' => $this->faker->sentence(),
            'activo' => true,
        ];
    }
}
