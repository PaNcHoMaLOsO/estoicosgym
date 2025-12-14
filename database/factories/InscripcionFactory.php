<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Enums\EstadosCodigo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InscripcionFactory extends Factory
{
    protected $model = Inscripcion::class;

    public function definition(): array
    {
        // Seleccionar un cliente y una membresía válidos
        $cliente = Cliente::inRandomOrder()->first() ?? Cliente::factory()->create();
        $membresia = Membresia::inRandomOrder()->first() ?? Membresia::factory()->create();

        // Duración de la membresía (en días)
        $duracion = $membresia->duracion_dias ?? 30;
        $fecha_inicio = $this->faker->dateTimeBetween('-2 months', 'now');
        $fecha_vencimiento = (clone $fecha_inicio)->modify("+{$duracion} days");

        // Estados válidos para alta/edición desde formularios
        $estadosValidos = [
            EstadosCodigo::INSCRIPCION_ACTIVA,
            EstadosCodigo::INSCRIPCION_PAUSADA,
            EstadosCodigo::INSCRIPCION_CANCELADA,
            EstadosCodigo::INSCRIPCION_VENCIDA,
        ];

        return [
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $membresia->id,
            'id_precio_acordado' => $membresia->precio_actual_id ?? 1,
            'fecha_inscripcion' => $fecha_inicio,
            'fecha_inicio' => $fecha_inicio,
            'fecha_vencimiento' => $fecha_vencimiento,
            'precio_base' => $membresia->precio ?? 20000,
            'descuento_aplicado' => 0,
            'precio_final' => $membresia->precio ?? 20000,
            'id_estado' => $this->faker->randomElement($estadosValidos),
            'observaciones' => $this->faker->optional()->sentence(),
            'pausada' => false,
            'dias_pausa' => 0,
            'dias_restantes_al_pausar' => 0,
            'fecha_pausa_inicio' => null,
            'fecha_pausa_fin' => null,
            'razon_pausa' => null,
            'pausa_indefinida' => false,
            'pausas_realizadas' => 0,
            'max_pausas_permitidas' => 2,
            'dias_compensacion' => 0,
            'id_inscripcion_anterior' => null,
            'es_cambio_plan' => false,
            'tipo_cambio' => null,
            'credito_plan_anterior' => 0,
            'precio_nuevo_plan' => null,
            'diferencia_a_pagar' => null,
            'fecha_cambio_plan' => null,
            'motivo_cambio_plan' => null,
            'es_traspaso' => false,
            'id_inscripcion_origen' => null,
            'id_cliente_original' => null,
            'fecha_traspaso' => null,
            'motivo_traspaso' => null,
        ];
    }
}
