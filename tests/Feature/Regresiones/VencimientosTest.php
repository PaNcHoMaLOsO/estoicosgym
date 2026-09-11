<?php

namespace Tests\Feature\Regresiones;

use App\Models\Inscripcion;
use App\Models\Membresia;
use Carbon\Carbon;
use Tests\CasoConCatalogos;

/**
 * Cuándo vence una membresía: una sola regla para todo lo que la crea.
 *
 * Lo que se vigila: que registrar a un socio nuevo con su membresía dé la
 * misma fecha que inscribirlo después —antes daba un día de más, un pase
 * diario de dos días, y un plan de solo meses que vencía el mismo día en que
 * empezaba—, y que los días que quedan se cuenten de día a día y no según la
 * hora en que se miran.
 */
class VencimientosTest extends CasoConCatalogos
{
    public function test_la_regla_del_vencimiento(): void
    {
        $inicio = Carbon::parse('2026-09-11 15:30');

        // Mensual, 30 días: el primero cuenta.
        $this->assertSame('2026-10-10', Membresia::find(4)->vencimientoDesde($inicio)->toDateString());
        // Pase diario: vale solo ese día.
        $this->assertSame('2026-09-11', Membresia::find(5)->vencimientoDesde($inicio)->toDateString());

        // Un plan cargado solo en meses —el formulario lo permite—.
        $soloMeses = new Membresia(['duracion_meses' => 1, 'duracion_dias' => 0]);
        $this->assertSame('2026-10-10', $soloMeses->vencimientoDesde($inicio)->toDateString());

        // La fecha de inicio que se le pasa no se toca.
        $this->assertSame('2026-09-11 15:30', $inicio->format('Y-m-d H:i'));
    }

    /**
     * EL QUE IMPORTA: el socio nuevo sale con la misma fecha que cualquier inscripción.
     *
     * El alta de socio con membresía llevaba su propia cuenta —inicio + días—:
     * un Mensual de 30 días vencía un día después que el mismo plan inscrito
     * desde la ficha, y un pase diario valía dos días.
     */
    public function test_un_socio_nuevo_vence_igual_que_una_inscripcion(): void
    {
        $casos = [
            4 => ['vence' => today()->addDays(29), 'rut' => '11.111.111-1', 'celular' => '+56912345674'],
            5 => ['vence' => today(), 'rut' => '22.222.222-2', 'celular' => '+56912345675'],
        ];

        foreach ($casos as $plan => $caso) {
            $this->actingAs($this->administrador())->post('/panel/clientes', [
                'flujo_cliente' => 'con_membresia',
                'run_pasaporte' => $caso['rut'],
                'nombres' => 'Camila',
                'apellido_paterno' => 'Rojas',
                'celular' => $caso['celular'],
                'email' => "camila{$plan}@progym.cl",
                'fecha_nacimiento' => '1990-05-10',
                'id_membresia' => $plan,
                'fecha_inicio' => today()->toDateString(),
            ])->assertSessionHasNoErrors();

            $inscripcion = Inscripcion::where('id_membresia', $plan)->latest('id')->firstOrFail();

            $this->assertSame(
                $caso['vence']->toDateString(),
                $inscripcion->fecha_vencimiento->toDateString(),
                "El plan {$plan} de un socio nuevo no vence cuando debe."
            );
        }
    }

    /** Los días que quedan se cuentan de día a día, no según la hora en que se mira. */
    public function test_los_dias_que_quedan_no_dependen_de_la_hora(): void
    {
        $this->travelTo(today()->setTime(15, 0));

        $venceEn = fn (int $dias) => Inscripcion::factory()->create([
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 0,
            'precio_final' => 0,
            'fecha_inicio' => today()->subDays(10),
            'fecha_vencimiento' => today()->addDays($dias),
        ]);

        $manana = $venceEn(1);

        $this->assertSame(1, $manana->dias_restantes);
        $this->assertSame(0, $venceEn(0)->dias_restantes);
        $this->assertSame(-1, $venceEn(-1)->dias_restantes);

        // Con «0 días», una membresía que vence mañana ya no se podía traspasar.
        $this->assertTrue($manana->puedeTraspasarse());
    }
}
