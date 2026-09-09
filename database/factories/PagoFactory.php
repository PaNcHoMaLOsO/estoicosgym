<?php

namespace Database\Factories;

use App\Enums\EstadosCodigo;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Pagos de prueba COHERENTES entre si.
 *
 * Antes el tipo de pago, el estado y el monto se sorteaban por separado, y
 * salian filas que se contradicen solas: «Pagado» debiendo dinero, «Pendiente»
 * con un abono hecho, «completo» con saldo por cobrar. Con eso el panel enseña
 * cifras que no cuadran y no hay forma de saber si el fallo es del sistema.
 *
 * El orden correcto es causal: primero QUE clase de pago es, de ahi el monto, y
 * el estado sale del monto. Es el mismo camino que sigue RegistroClienteService
 * cuando cobra de verdad.
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return $this->atributosDe(
            Inscripcion::inRandomOrder()->first() ?? Inscripcion::factory()->create()
        );
    }

    /**
     * Pago de UNA inscripcion concreta.
     *
     * Existe porque pasar `['id_inscripcion' => $x]` a create() NO basta: los
     * valores sueltos se aplican DESPUES de definition(), asi que el monto ya
     * venia calculado sobre otra inscripcion cualquiera y el pago terminaba con
     * el precio de un plan que no era el suyo.
     */
    public function paraInscripcion(Inscripcion $inscripcion): static
    {
        return $this->state(fn () => $this->atributosDe($inscripcion));
    }

    private function atributosDe(Inscripcion $inscripcion): array
    {
        $metodoPago = MetodoPago::inRandomOrder()->first() ?? MetodoPago::factory()->create();

        $total = (int) ($inscripcion->precio_final ?? 20000);

        $tipo = $this->faker->randomElement(['completo', 'parcial', 'pendiente', 'mixto']);

        $abonado = match ($tipo) {
            'completo' => $total,
            'parcial' => $this->faker->numberBetween(1, max(1, $total - 1)),
            'pendiente' => 0,
            'mixto' => $this->faker->numberBetween(0, $total),
        };

        $pendiente = max(0, $total - $abonado);

        $estado = match (true) {
            $abonado <= 0 => EstadosCodigo::PAGO_PENDIENTE,
            $pendiente <= 0 => EstadosCodigo::PAGO_PAGADO,
            default => EstadosCodigo::PAGO_PARCIAL,
        };

        // NUNCA en el futuro. Se sorteaba entre el inicio y el vencimiento de la
        // inscripcion, asi que una membresia anual daba pagos fechados hasta un
        // año por delante: «Recaudado hoy» salia en cero mientras el grueso del
        // dinero estaba registrado en 2027.
        $desde = $inscripcion->fecha_inicio ?? now()->subMonths(2);
        $hasta = min(
            $inscripcion->fecha_vencimiento ?? now(),
            now(),
        );

        $fechaPago = $hasta > $desde
            ? $this->faker->dateTimeBetween($desde, $hasta)
            : $desde;

        return [
            'uuid' => Str::uuid(),
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $total,
            'monto_abonado' => $abonado,
            'monto_pendiente' => $pendiente,
            'fecha_pago' => $fechaPago,
            'id_metodo_pago' => $metodoPago->id,
            'referencia_pago' => $this->faker->optional()->bothify('REF-####-????'),
            'cantidad_cuotas' => 1,
            'numero_cuota' => 1,
            'monto_cuota' => $total,
            'periodo_inicio' => $inscripcion->fecha_inicio,
            'periodo_fin' => $inscripcion->fecha_vencimiento,
            'id_estado' => $estado,
            'tipo_pago' => $tipo,
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
