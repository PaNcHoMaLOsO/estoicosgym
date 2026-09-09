<?php

namespace Database\Factories;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\PrecioMembresia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Inscripciones de prueba COHERENTES entre si.
 *
 * Antes el estado se sorteaba aparte de las fechas y de los campos de pausa, y
 * salian filas que no pueden existir: «Pausada» sin fecha de pausa y con el
 * indicador `pausada` en false, o «Activa» con el vencimiento ya pasado. Con
 * datos asi no se puede distinguir un fallo del sistema de un disparate del
 * generador, y el panel enseña cifras que no significan nada.
 *
 * Aqui el estado se DEDUCE de los datos, no al reves.
 */
class InscripcionFactory extends Factory
{
    protected $model = Inscripcion::class;

    public function definition(): array
    {
        $cliente = Cliente::inRandomOrder()->first() ?? Cliente::factory()->create();
        $membresia = Membresia::inRandomOrder()->first() ?? Membresia::factory()->create();

        $duracion = $membresia->duracion_dias ?: 30;
        $inicio = $this->faker->dateTimeBetween('-2 months', 'now');
        $vencimiento = (clone $inicio)->modify("+{$duracion} days");

        // EL PRECIO VIVE EN precios_membresias, no en membresias. Aqui se leia
        // $membresia->precio, una columna que no existe, asi que el `?? 20000`
        // se aplicaba SIEMPRE: un Pase Diario de $5.000 y un Anual de $250.000
        // quedaban los dos registrados en $20.000 y toda cifra de ingresos del
        // panel era mentira.
        $precio = PrecioMembresia::where('id_membresia', $membresia->id)
            ->where('activo', true)
            ->first();

        $precioFinal = (int) ($precio->precio_normal ?? 20000);

        $vencida = $vencimiento < now();

        // Una de cada seis se cancela; el resto sigue las fechas. La pausa no se
        // sortea aqui: exige rellenar cinco campos a la vez y para eso esta el
        // estado pausada() de abajo.
        $estado = match (true) {
            $this->faker->boolean(15) => EstadosCodigo::INSCRIPCION_CANCELADA,
            $vencida => EstadosCodigo::INSCRIPCION_VENCIDA,
            default => EstadosCodigo::INSCRIPCION_ACTIVA,
        };

        return [
            'uuid' => Str::uuid(),
            'id_cliente' => $cliente->id,
            'id_membresia' => $membresia->id,
            'id_precio_acordado' => $precio?->id,
            'fecha_inscripcion' => $inicio,
            'fecha_inicio' => $inicio,
            'fecha_vencimiento' => $vencimiento,
            'precio_base' => $precioFinal,
            'descuento_aplicado' => 0,
            'precio_final' => $precioFinal,
            'id_estado' => $estado,
            'observaciones' => $this->faker->optional()->sentence(),
            'pausada' => false,
            'dias_pausa' => 0,
            'dias_restantes_al_pausar' => 0,
            'fecha_pausa_inicio' => null,
            'fecha_pausa_fin' => null,
            'razon_pausa' => null,
            'pausa_indefinida' => false,
            'pausas_realizadas' => 0,
            // Cada plan permite las suyas: el Pase Diario ninguna y el Anual
            // tres. Estaba fijo en 2 para todos.
            'max_pausas_permitidas' => $membresia->max_pausas ?? 0,
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

    /**
     * Inscripcion pausada, con TODOS los campos que eso implica.
     *
     * Se pausa solo lo que esta vigente, y solo si el plan lo permite: pausar
     * un Pase Diario o una membresia ya vencida no es un caso de prueba, es un
     * registro imposible.
     */
    public function pausada(): static
    {
        return $this->state(function (array $atributos) {
            if ($atributos['id_estado'] !== EstadosCodigo::INSCRIPCION_ACTIVA
                || ($atributos['max_pausas_permitidas'] ?? 0) < 1) {
                return [];
            }

            $desde = $this->faker->dateTimeBetween('-10 days', 'now');
            $dias = $this->faker->randomElement([7, 14, 30]);

            return [
                'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
                'pausada' => true,
                'dias_pausa' => $dias,
                'fecha_pausa_inicio' => $desde,
                'fecha_pausa_fin' => (clone $desde)->modify("+{$dias} days"),
                'dias_restantes_al_pausar' => max(
                    0,
                    (int) $desde->diff($atributos['fecha_vencimiento'])->days,
                ),
                'razon_pausa' => $this->faker->randomElement([
                    'Viaje', 'Lesión', 'Motivos laborales', 'Enfermedad',
                ]),
                'pausas_realizadas' => 1,
            ];
        });
    }
}
