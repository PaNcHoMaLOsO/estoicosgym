<?php

namespace Database\Factories;

use App\Models\Membresia;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembresiaFactory extends Factory
{
    protected $model = Membresia::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(2, true),
            'duracion_meses' => $this->faker->numberBetween(1, 12),
            'duracion_dias' => $this->faker->numberBetween(1, 365),
            'descripcion' => $this->faker->sentence(),
            'activo' => true,
        ];
    }
}
