<?php

namespace Database\Factories;

use App\Models\Convenio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConvenioFactory extends Factory
{
    protected $model = Convenio::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Descuento ' . $this->faker->numberBetween(1, 50) . '%',
            'tipo' => $this->faker->randomElement(['institucion_educativa', 'empresa', 'organizacion', 'otro']),
            'descuento_porcentaje' => $this->faker->numberBetween(1, 50),
            'descuento_monto' => 0,
            'descripcion' => $this->faker->sentence(),
            'activo' => true,
        ];
    }
}
