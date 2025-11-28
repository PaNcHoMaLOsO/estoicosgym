<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Factories\Factory;

class MetodoPagoFactory extends Factory
{
    protected $model = MetodoPago::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'activo' => true,
        ];
    }
}
