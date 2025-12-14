<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Enums\EstadosCodigo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        // Seleccionar una inscripción válida
        $inscripcion = Inscripcion::inRandomOrder()->first() ?? Inscripcion::factory()->create();
        $metodoPago = MetodoPago::inRandomOrder()->first() ?? MetodoPago::factory()->create();

        // Estados válidos para pagos desde formularios/controladores
        $estadosValidos = [
            EstadosCodigo::PAGO_PENDIENTE,
            EstadosCodigo::PAGO_PAGADO,
            EstadosCodigo::PAGO_PARCIAL,
        ];

        // Monto y fechas coherentes
        $monto_total = $inscripcion->precio_final ?? 20000;
        $monto_abonado = $this->faker->randomFloat(0, 0, $monto_total);
        $monto_pendiente = max(0, $monto_total - $monto_abonado);
        $fecha_pago = $this->faker->dateTimeBetween($inscripcion->fecha_inicio, $inscripcion->fecha_vencimiento);

        return [
            'uuid' => Str::uuid(),
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $monto_total,
            'monto_abonado' => $monto_abonado,
            'monto_pendiente' => $monto_pendiente,
            'fecha_pago' => $fecha_pago,
            'id_metodo_pago' => $metodoPago->id,
            'referencia_pago' => $this->faker->optional()->bothify('REF-####-????'),
            'cantidad_cuotas' => 1,
            'numero_cuota' => 1,
            'monto_cuota' => $monto_total,
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
            'id_estado' => $this->faker->randomElement($estadosValidos),
            'tipo_pago' => $this->faker->randomElement(['completo', 'parcial', 'pendiente', 'mixto']),
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
