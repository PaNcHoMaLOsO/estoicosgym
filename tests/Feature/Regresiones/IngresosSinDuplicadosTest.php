<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\CobroTaller;
use App\Models\Fiado;
use App\Models\HistorialTraspaso;
use App\Models\Inscripcion;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Taller;
use App\Services\RegistroInscripcionService;
use App\Services\RegistroPagoService;
use App\Support\IngresosDelNegocio;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\CasoConCatalogos;

/**
 * Que el mismo dinero no entre dos veces.
 *
 * Hay dos maneras de duplicar un cobro y cada una se frena en un sitio:
 *
 *  · El MISMO envío dos veces (doble clic, el navegador que reintenta): lo
 *    frena el token del formulario, que se reserva de forma atómica.
 *  · DOS envíos distintos a la vez (dos pestañas, dos cajas): cada uno trae su
 *    token, así que el turno no los frena. Los dos pasaban las comprobaciones
 *    —«ya tiene membresía», «ya se renovó», «cobro idéntico»— ANTES de que
 *    ninguno escribiera. Eso se frena trabando la fila y volviendo a mirar
 *    dentro de la transacción.
 *
 * SQLite —el de las pruebas— no traba filas, así que la carrera se reproduce
 * a mano: o se valida dos veces antes de escribir ninguna (lo que hacen dos
 * peticiones que coinciden en el aire), o se escribe «lo de la otra caja»
 * justo cuando la petición abre su transacción, que es el instante en que, en
 * PostgreSQL, se quedaría esperando la traba.
 */
class IngresosSinDuplicadosTest extends CasoConCatalogos
{
    /** Mensual: $40.000. */
    private const PLAN_MENSUAL = 4;

    private function socio(): Cliente
    {
        return Cliente::factory()->create(['activo' => true]);
    }

    private function membresia(Cliente $socio, int $precio = 40000, int $diasQueQuedan = 25): Inscripcion
    {
        return Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => $precio,
            'descuento_aplicado' => 0,
            'precio_final' => $precio,
            'fecha_inicio' => now()->subDays(5),
            'fecha_vencimiento' => now()->addDays($diasQueQuedan),
            'pausada' => false,
        ]);
    }

    private function cobrado(Inscripcion $inscripcion, int $monto): Pago
    {
        return Pago::factory()->paraInscripcion($inscripcion)->create([
            'monto_total' => (int) $inscripcion->precio_final,
            'monto_abonado' => $monto,
            'monto_pendiente' => max(0, (int) $inscripcion->precio_final - $monto),
            'id_estado' => $monto >= (int) $inscripcion->precio_final ? 201 : 202,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'id_metodo_pago2' => null,
            'monto_metodo1' => null,
            'monto_metodo2' => null,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    /** Lo que hace «la otra caja» justo cuando esta petición abre su transacción. */
    private function alAbrirLaTransaccion(callable $otraCaja): void
    {
        $this->unaVez(TransactionBeginning::class, $otraCaja);
    }

    /**
     * Lo que hace «la otra caja» justo después de que esta petición lea la
     * fila: lo que tiene en memoria queda viejo, como en la carrera de verdad,
     * y lo de la otra caja queda escrito FUERA de esta transacción, así que un
     * rechazo no se lo lleva por delante.
     */
    private function alLeer(string $modelo, callable $otraCaja): void
    {
        $this->unaVez("eloquent.retrieved: {$modelo}", $otraCaja);
    }

    private function unaVez(string $evento, callable $otraCaja): void
    {
        $hecho = false;

        Event::listen($evento, function () use (&$hecho, $otraCaja) {
            if ($hecho) {
                return;
            }

            $hecho = true;
            $otraCaja();
        });
    }

    private function abono(Inscripcion $inscripcion, array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_inscripcion' => $inscripcion->id,
            'tipo_pago' => 'abono',
            'monto_abonado' => 10000,
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    private function alta(Cliente $socio, array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_cliente' => $socio->id,
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    private function renovacion(array $extra = []): array
    {
        return array_merge([
            'form_submit_token' => uniqid('t', true),
            'id_membresia' => self::PLAN_MENSUAL,
            'fecha_inicio' => now()->addDays(6)->format('Y-m-d'),
            'tipo_pago' => 'completo',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ], $extra);
    }

    // ───────────────────────────────────────── el mismo envío, dos veces

    /** Doble clic en «Registrar pago», «Inscribir» y «Renovar»: una fila cada uno. */
    public function test_el_mismo_envio_dos_veces_cobra_una_sola_vez(): void
    {
        $admin = $this->administrador();

        $deuda = $this->membresia($this->socio(), diasQueQuedan: 25);
        $abono = $this->abono($deuda);
        $this->actingAs($admin)->post('/panel/pagos/registrar', $abono);
        $this->actingAs($admin)->post('/panel/pagos/registrar', $abono);
        $this->assertSame(1, Pago::where('id_inscripcion', $deuda->id)->count(), 'El abono repetido se cobró dos veces.');

        $nuevo = $this->socio();
        $alta = $this->alta($nuevo);
        $this->actingAs($admin)->post('/panel/inscripciones', $alta);
        $this->actingAs($admin)->post('/panel/inscripciones', $alta);
        $this->assertSame(1, Inscripcion::where('id_cliente', $nuevo->id)->count(), 'El alta repetida creó dos membresías.');
        $this->assertSame(1, Pago::where('id_cliente', $nuevo->id)->count());

        $porVencer = $this->membresia($this->socio(), diasQueQuedan: 5);
        $renovar = $this->renovacion();
        $this->actingAs($admin)->post("/panel/inscripciones/{$porVencer->uuid}/renovar", $renovar);
        $this->actingAs($admin)->post("/panel/inscripciones/{$porVencer->uuid}/renovar", $renovar);
        $this->assertSame(1, Inscripcion::where('id_inscripcion_anterior', $porVencer->id)->count(), 'La renovación repetida creó dos membresías.');
    }

    /** Dos envíos seguidos con tokens DISTINTOS (dos pestañas): tampoco. */
    public function test_dos_pestanas_seguidas_cobran_una_sola_vez(): void
    {
        $admin = $this->administrador();

        $deuda = $this->membresia($this->socio());
        $this->actingAs($admin)->post('/panel/pagos/registrar', $this->abono($deuda));
        $this->actingAs($admin)->post('/panel/pagos/registrar', $this->abono($deuda))
            ->assertSessionHasErrors('monto_abonado');
        $this->assertSame(1, Pago::where('id_inscripcion', $deuda->id)->count());

        $nuevo = $this->socio();
        $this->actingAs($admin)->post('/panel/inscripciones', $this->alta($nuevo));
        $this->actingAs($admin)->post('/panel/inscripciones', $this->alta($nuevo))
            ->assertSessionHasErrors('id_cliente');
        $this->assertSame(1, Inscripcion::where('id_cliente', $nuevo->id)->count());

        $porVencer = $this->membresia($this->socio(), diasQueQuedan: 5);
        $this->actingAs($admin)->post("/panel/inscripciones/{$porVencer->uuid}/renovar", $this->renovacion());
        $this->actingAs($admin)->post("/panel/inscripciones/{$porVencer->uuid}/renovar", $this->renovacion());
        $this->assertSame(1, Inscripcion::where('id_inscripcion_anterior', $porVencer->id)->count());
    }

    // ───────────────────────────────────────── dos envíos a la vez

    /**
     * Dos abonos iguales que coinciden en el aire.
     *
     * El corte al «cobro idéntico en cinco minutos» vivía solo en validar(),
     * fuera de la traba: las dos peticiones lo pasaban antes de que ninguna
     * escribiera, y como $10.000 cabía dos veces en el saldo de $40.000, el
     * tope tampoco las frenaba. Quedaban $20.000 cobrados por $10.000 recibidos.
     */
    public function test_dos_abonos_iguales_a_la_vez_entran_una_vez(): void
    {
        $deuda = $this->membresia($this->socio());
        $servicio = app(RegistroPagoService::class);

        $primera = $servicio->validar(Request::create('/', 'POST', $this->abono($deuda)));
        $segunda = $servicio->validar(Request::create('/', 'POST', $this->abono($deuda)));

        $servicio->registrar($primera);

        try {
            $servicio->registrar($segunda);
            $this->fail('El segundo abono idéntico se registró: el socio queda con $10.000 cobrados de más.');
        } catch (ValidationException) {
            // Lo esperado.
        }

        $this->assertSame(10000, (int) Pago::where('id_inscripcion', $deuda->id)->sum('monto_abonado'));
    }

    /**
     * Dos pestañas inscribiendo al mismo socio a la vez.
     *
     * La comprobación «ya tiene una membresía activa» se hacía al validar: las
     * dos la pasaban y el socio quedaba con dos membresías activas y dos cobros
     * del mismo mes.
     */
    public function test_dos_altas_del_mismo_socio_a_la_vez_dejan_una_membresia(): void
    {
        $socio = $this->socio();
        $servicio = app(RegistroInscripcionService::class);

        $primera = $servicio->validar(Request::create('/', 'POST', $this->alta($socio)));
        $segunda = $servicio->validar(Request::create('/', 'POST', $this->alta($socio)));

        $servicio->registrar($primera);

        try {
            $servicio->registrar($segunda);
            $this->fail('Se creó una segunda membresía activa con su cobro.');
        } catch (ValidationException) {
            // Lo esperado.
        }

        $this->assertSame(1, Inscripcion::where('id_cliente', $socio->id)->count());
        $this->assertSame(40000, (int) Pago::where('id_cliente', $socio->id)->sum('monto_abonado'));
    }

    /** Y lo mismo al renovar: una renovación, un cobro. */
    public function test_dos_renovaciones_a_la_vez_dejan_una_sola(): void
    {
        $vieja = $this->membresia($this->socio(), diasQueQuedan: 5);
        $servicio = app(RegistroInscripcionService::class);

        $primera = $servicio->validarRenovacion(Request::create('/', 'POST', $this->renovacion()), $vieja);
        $segunda = $servicio->validarRenovacion(Request::create('/', 'POST', $this->renovacion()), $vieja->fresh());

        $servicio->registrar($primera);

        try {
            $servicio->registrar($segunda);
            $this->fail('Se renovó dos veces la misma membresía.');
        } catch (ValidationException) {
            // Lo esperado.
        }

        $this->assertSame(1, Inscripcion::where('id_inscripcion_anterior', $vieja->id)->count());
        $this->assertSame(1, Inscripcion::where('id_cliente', $vieja->id_cliente)
            ->where('id_estado', EstadosCodigo::INSCRIPCION_ACTIVA)->count());
    }

    /**
     * Doble clic en «Cambiar plan»: una membresía nueva y un cobro.
     *
     * Esa pantalla no lleva token, y la comprobación de que la membresía sigue
     * activa se hacía fuera de la transacción: la segunda petición, si
     * coincidía, creaba otra membresía nueva y cobraba otra vez la diferencia.
     */
    public function test_cambiar_de_plan_dos_veces_a_la_vez_cobra_una_diferencia(): void
    {
        $socio = $this->socio();
        $vieja = $this->membresia($socio);
        $this->cobrado($vieja, 40000);

        $caro = Membresia::where('activo', true)->where('id', '!=', self::PLAN_MENSUAL)->get()
            ->first(fn (Membresia $m) => (int) $m->precios()->where('activo', true)->value('precio_normal') > 40000);
        $this->assertNotNull($caro, 'El catálogo necesita un plan más caro que el mensual.');

        // La otra pestaña hace el cambio justo después de que esta lea la
        // membresía: esta la sigue viendo activa.
        $this->alLeer(Inscripcion::class, function () use ($vieja, $socio, $caro) {
            Inscripcion::whereKey($vieja->id)->update(['id_estado' => EstadosCodigo::INSCRIPCION_CAMBIADA]);
            Inscripcion::factory()->create([
                'id_cliente' => $socio->id,
                'id_membresia' => $caro->id,
                'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
                'id_inscripcion_anterior' => $vieja->id,
                'es_cambio_plan' => true,
            ]);
        });

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$vieja->uuid}/cambiar-plan", [
                'id_membresia_actual' => $vieja->id_membresia,
                'id_membresia_nueva' => $caro->id,
                'id_metodo_pago' => MetodoPago::first()->id,
                'aplicar_credito' => true,
                'monto_abonado' => 999999,
            ])
            ->assertStatus(422);

        $this->assertSame(1, Inscripcion::where('id_inscripcion_anterior', $vieja->id)->count(), 'Salieron dos membresías nuevas del mismo cambio.');
        $this->assertSame(1, Pago::where('id_cliente', $socio->id)->count(), 'Se cobró otra vez la diferencia.');
    }

    /** Dos traspasos a la vez de la misma membresía: uno solo en el historial. */
    public function test_traspasar_dos_veces_a_la_vez_deja_un_traspaso(): void
    {
        $origen = $this->socio();
        $destino = $this->socio();
        $otro = $this->socio();
        $membresia = $this->membresia($origen);
        $this->cobrado($membresia, 40000);

        // La otra pestaña se la traspasa a otra persona justo después de que
        // esta la lea.
        $this->alLeer(Inscripcion::class, fn () => Inscripcion::whereKey($membresia->id)->update(['id_cliente' => $otro->id]));

        $this->actingAs($this->administrador())
            ->postJson("/panel/inscripciones/{$membresia->uuid}/traspasar", [
                'id_cliente_destino' => $destino->id,
                'motivo_traspaso' => 'Se la regala a su hermana',
            ])
            ->assertStatus(422);

        $this->assertSame($otro->id, (int) $membresia->fresh()->id_cliente, 'El segundo traspaso pisó al primero.');
        $this->assertSame(0, HistorialTraspaso::where('cliente_destino_id', $destino->id)->count());
    }

    // ───────────────────────────────────────── talleres y mesón

    private function taller(): Taller
    {
        $institucion = Institucion::create(['nombre' => 'Colegio de prueba', 'rut' => '65.154.436-K']);

        return Taller::create([
            'id_institucion' => $institucion->id,
            'nombre' => 'Clases grupales',
            'descripcion_factura' => 'Uso instalaciones para clase grupal',
            'precio_hora' => 30000,
            'horario' => ['lunes' => [['15:30', '16:30']]],
            'activo' => true,
        ]);
    }

    /**
     * Dos «Cerrar el mes» a la vez: el índice único dejaba entrar uno, pero el
     * otro reventaba con un error 500 en vez de decir que ya estaba cerrado.
     */
    public function test_cerrar_el_mismo_mes_dos_veces_a_la_vez_no_revienta(): void
    {
        $taller = $this->taller();
        $admin = $this->administrador();
        $this->actingAs($admin)->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        // La otra pestaña cierra el mes después de que esta comprobara que
        // seguía abierto, mientras lee las horas.
        $this->alLeer(\App\Models\HoraTaller::class, fn () => $taller->cobros()->create([
            'periodo' => '2026-07', 'horas' => 4, 'precio_hora' => 30000,
            'total' => 120000, 'neto' => 100840, 'iva' => 19160, 'id_usuario' => $admin->id,
        ]));

        $this->actingAs($admin)
            ->post("/panel/talleres/{$taller->uuid}/cerrar", ['periodo' => '2026-07'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, CobroTaller::where('id_taller', $taller->id)->count());
    }

    /** Anotar las clases del horario dos veces seguidas no las duplica. */
    public function test_anotar_el_mes_dos_veces_no_duplica_las_horas(): void
    {
        $taller = $this->taller();
        $admin = $this->administrador();

        $this->actingAs($admin)->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);
        $una = $taller->horas()->count();
        $this->actingAs($admin)->post("/panel/talleres/{$taller->uuid}/horas/del-mes", ['periodo' => '2026-07']);

        $this->assertGreaterThan(0, $una);
        $this->assertSame($una, $taller->horas()->count());
    }

    /**
     * Dos «Pagó» a la vez sobre la misma cuenta del mesón.
     *
     * El segundo marcaba otra vez las líneas que el primero ya había cobrado:
     * pisaba la hora y el medio de pago, y la caja del día decía que esos
     * $4.000 entraron por transferencia cuando entraron en efectivo.
     */
    public function test_saldar_dos_veces_a_la_vez_no_pisa_el_primer_cobro(): void
    {
        $admin = $this->administrador();
        $socio = $this->socio();
        foreach ([2500, 1500] as $monto) {
            Fiado::create(['id_cliente' => $socio->id, 'concepto' => 'Bebida', 'monto' => $monto, 'id_usuario' => $admin->id]);
        }

        $efectivo = (int) MetodoPago::where('nombre', 'like', '%fectivo%')->value('id');
        $otro = (int) MetodoPago::where('id', '!=', $efectivo)->value('id');
        $momento = now()->subMinute()->startOfSecond();

        // El primer «Pagó», en efectivo, ya se escribió.
        $this->alAbrirLaTransaccion(fn () => Fiado::where('id_cliente', $socio->id)->update([
            'pagado' => true, 'pagado_en' => $momento, 'id_metodo_pago' => $efectivo, 'id_usuario_cobro' => $admin->id,
        ]));

        $this->actingAs($admin)
            ->post('/panel/fiados/saldar', ['id_cliente' => $socio->id, 'id_metodo_pago' => $otro])
            ->assertSessionHas('error');

        $this->assertSame(2, Fiado::where('id_metodo_pago', $efectivo)->count(), 'El segundo cobro cambió el medio del primero.');
        $this->assertSame(1, Fiado::where('pagado', true)->distinct()->count('pagado_en'));
        $this->assertSame(4000, IngresosDelNegocio::entre(today(), today())['meson']);
    }

    // ───────────────────────────────────────── corregir un pago

    /**
     * Corregir un pago mientras entra otro cobro: no queda sobrepago.
     *
     * El tope («lo que queda del precio») se miraba fuera de la transacción.
     * Si justo entraba otro cobro, subir este hasta el tope viejo dejaba más
     * plata cobrada que lo que vale la membresía.
     */
    public function test_corregir_un_pago_mientras_entra_otro_no_deja_sobrepago(): void
    {
        $socio = $this->socio();
        $membresia = $this->membresia($socio);
        $pago = $this->cobrado($membresia, 10000);

        // Entra un cobro de $20.000 por la otra caja.
        $this->alAbrirLaTransaccion(fn () => $this->cobrado($membresia, 20000));

        $this->actingAs($this->administrador())
            ->put("/panel/pagos/{$pago->uuid}", [
                'form_submit_token' => uniqid('t', true),
                'monto_abonado' => 40000,
                'fecha_pago' => now()->format('Y-m-d'),
                'id_metodo_pago' => MetodoPago::first()->id,
            ])
            ->assertSessionHasErrors('monto_abonado');

        $this->assertSame(10000, (int) $pago->fresh()->monto_abonado, 'Se subió el pago hasta un tope que ya no existía.');
    }

    // ───────────────────────────────────────── las cuentas

    /**
     * La Caja, el informe del año y el listado de pagos cuentan lo mismo, y un
     * mixto entra UNA vez: repartido entre sus medios, no sumado dos veces.
     */
    public function test_las_pantallas_de_dinero_suman_lo_mismo(): void
    {
        $admin = $this->administrador();
        $socio = $this->socio();
        $membresia = $this->membresia($socio, 50000);
        [$uno, $dos] = MetodoPago::orderBy('id')->limit(2)->pluck('id')->all();

        $this->actingAs($admin)->post('/panel/pagos/registrar', [
            'form_submit_token' => uniqid('t', true),
            'id_inscripcion' => $membresia->id,
            'tipo_pago' => 'mixto',
            'id_metodo_pago1' => $uno,
            'id_metodo_pago2' => $dos,
            'monto_metodo1' => 30000,
            'monto_metodo2' => 20000,
            'fecha_pago' => now()->format('Y-m-d'),
        ])->assertSessionHasNoErrors();

        $hoy = today();
        $caja = IngresosDelNegocio::entre($hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth());
        $porMetodo = collect(IngresosDelNegocio::porMetodo($hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()));

        $this->assertSame(50000, $caja['membresias'], 'El mixto se contó dos veces en la caja.');
        $this->assertSame(50000, (int) $porMetodo->sum('total'), 'El reparto por medio no cuadra con la caja.');

        $informe = $this->actingAs($admin)->get('/panel/reportes/ingresos?anio=' . $hoy->year)->viewData('page')['props'];
        $this->assertSame(50000, (int) $informe['totalesPorFuente']['membresias']);

        $pagos = $this->actingAs($admin)->get('/panel/pagos')->viewData('page')['props'];
        $this->assertSame(50000, (int) $pagos['resumen']['recaudado_mes']);
    }
}
