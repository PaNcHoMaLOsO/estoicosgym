<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use Carbon\Carbon;
use Tests\CasoConCatalogos;

/**
 * Las cifras de la caja: lo que se debe, lo que entró por cada método, y la hora
 * con que se anota todo.
 *
 * Lo que se vigila: que «por cobrar» cuente cada membresía una vez —el resumen,
 * Pagos y Reportes daban tres cifras distintas y ninguna era la deuda—, que un
 * pago mixto se reparta entre sus dos métodos, que un pago que vuelve de la
 * papelera recalcule el saldo, y que el sistema viva en la hora de Chile.
 */
class DeudaYCajaTest extends CasoConCatalogos
{
    private function membresia(int $precio, int $estado = 100): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => Cliente::factory()->create(['activo' => true])->id,
            'id_membresia' => 4,
            'id_estado' => $estado,
            'precio_base' => $precio,
            'precio_final' => $precio,
        ]);
    }

    private function abonar(Inscripcion $inscripcion, int $monto, array $extra = []): Pago
    {
        $pago = Pago::create($extra + [
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $inscripcion->id_cliente,
            'monto_total' => $inscripcion->precio_final,
            'monto_abonado' => $monto,
            'monto_pendiente' => 0,
            'id_estado' => 201,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::orderBy('id')->first()->id,
            'fecha_pago' => today()->format('Y-m-d'),
        ]);

        $inscripcion->recalcularSusPagos();

        return $pago;
    }

    /** Lo que dicen las tres pantallas que muestran «por cobrar». */
    private function porCobrarEnPantalla(): array
    {
        $admin = $this->administrador();

        return [
            'resumen' => $this->actingAs($admin)->get('/panel')->viewData('page')['props']['caja']['por_cobrar'],
            'pagos' => $this->actingAs($admin)->get('/panel/pagos')->viewData('page')['props']['resumen']['por_cobrar'],
            'reportes' => $this->actingAs($admin)->get('/panel/reportes')->viewData('page')['props']['cifras']['por_cobrar'],
        ];
    }

    /**
     * EL QUE IMPORTA: se cuenta cada membresía una vez.
     *
     * Una de 40.000 con dos abonos de 20.000 y 10.000 debe 10.000; sumar lo que
     * quedaba después de cada pago daba 30.000. Y la que no pagó nada debe su
     * precio entero, aunque no tenga ninguna fila de pago.
     */
    public function test_por_cobrar_cuenta_cada_membresia_una_vez(): void
    {
        $abonada = $this->membresia(40000);
        $this->abonar($abonada, 20000);
        $this->abonar($abonada, 10000);

        $this->membresia(30000);                  // sin ningún pago
        $this->membresia(50000, estado: 103);     // cancelada: no se sale a cobrar

        $this->assertSame(
            ['resumen' => 40000, 'pagos' => 40000, 'reportes' => 40000],
            $this->porCobrarEnPantalla()
        );
    }

    /** El informe de pendientes lista cada membresía una vez, con lo que de verdad debe. */
    public function test_pendientes_tiene_una_fila_por_membresia(): void
    {
        $abonada = $this->membresia(40000);
        $this->abonar($abonada, 20000);
        $this->abonar($abonada, 10000);
        $sinPagos = $this->membresia(30000);

        $props = $this->actingAs($this->administrador())
            ->get('/panel/reportes/pendientes')
            ->viewData('page')['props'];

        $this->assertSame(40000, $props['total']);
        $this->assertSame(
            [(string) $sinPagos->uuid => 30000, (string) $abonada->uuid => 10000],
            collect($props['pagos'])->pluck('pendiente', 'uuid')->all()
        );
    }

    /** Un pago mixto se reparte: 20.000 en efectivo y 10.000 por transferencia no son 30.000 en efectivo. */
    public function test_un_pago_mixto_se_reparte_entre_sus_metodos(): void
    {
        [$uno, $dos] = MetodoPago::orderBy('id')->take(2)->get()->all();

        $inscripcion = $this->membresia(45000);
        $this->abonar($inscripcion, 30000, [
            'tipo_pago' => 'mixto',
            'id_metodo_pago' => $uno->id,
            'id_metodo_pago2' => $dos->id,
            'monto_metodo1' => 20000,
            'monto_metodo2' => 10000,
        ]);
        $this->abonar($inscripcion, 15000, ['id_metodo_pago' => $uno->id]);

        $porMetodo = collect(
            $this->actingAs($this->administrador())
                ->get('/panel/reportes/ingresos')
                ->viewData('page')['props']['porMetodo']
        )->pluck('total', 'nombre');

        $this->assertSame(35000, $porMetodo[$uno->nombre]);
        $this->assertSame(10000, $porMetodo[$dos->nombre]);
    }

    /** Un pago que vuelve de la papelera recalcula el saldo de los demás. */
    public function test_recuperar_un_pago_recalcula_el_saldo(): void
    {
        $inscripcion = $this->membresia(40000);
        $primero = $this->abonar($inscripcion, 20000);
        $segundo = $this->abonar($inscripcion, 20000);

        $segundo->delete();
        $inscripcion->recalcularSusPagos();
        $this->assertSame(202, (int) $primero->refresh()->id_estado);

        $this->actingAs($this->administrador())
            ->patch("/panel/papelera/pagos/{$segundo->id}/restaurar")
            ->assertSessionHasNoErrors();

        $this->assertSame([201, 20000], [(int) $primero->refresh()->id_estado, (int) $primero->monto_pendiente]);
        $this->assertSame([201, 0], [(int) $segundo->refresh()->id_estado, (int) $segundo->monto_pendiente]);
    }

    /**
     * EL OTRO QUE IMPORTA: el sistema vive en la hora de Chile.
     *
     * Con UTC, a las 22:30 de Los Ángeles ya era el día siguiente: inscribir a
     * alguien con la fecha de hoy se rechazaba por «fecha pasada».
     */
    public function test_a_las_diez_y_media_de_la_noche_todavia_es_hoy(): void
    {
        $this->travelTo(Carbon::parse('2026-09-11 22:30', 'America/Santiago'));

        $this->assertSame('2026-09-11', today()->toDateString());

        $this->actingAs($this->administrador())->post('/panel/clientes', [
            'flujo_cliente' => 'con_membresia',
            'run_pasaporte' => '11.111.111-1',
            'nombres' => 'Camila',
            'apellido_paterno' => 'Rojas',
            'celular' => '+56912345674',
            'email' => 'camila@progym.cl',
            'fecha_nacimiento' => '1990-05-10',
            'id_membresia' => 4,
            'fecha_inicio' => '2026-09-11',
        ])->assertSessionHasNoErrors();
    }
}
