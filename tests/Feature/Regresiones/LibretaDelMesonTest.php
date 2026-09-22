<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Fiado;
use App\Models\Nota;
use App\Models\Pago;
use App\Models\User;
use Tests\CasoConCatalogos;

/**
 * La libreta del mesón: las notas del día y lo fiado.
 *
 * Lo que más importa de aquí es que LO FIADO NO ES UN PAGO DE MEMBRESÍA. Una
 * bebida de $1.500 no puede aparecer en la caja del día ni en el saldo del
 * socio: son dos libretas distintas y mezclarlas descuadraría las dos.
 */
class LibretaDelMesonTest extends CasoConCatalogos
{
    private $admin = null;

    private function usuario(): User
    {
        return $this->admin ??= $this->administrador();
    }

    private function como()
    {
        return $this->actingAs($this->usuario());
    }

    // ---------- Notas ----------

    /**
     * Una nota escrita hace tantos dias.
     *
     * `created_at` NO esta en el fillable, asi que pasarlo a create() lo ignora
     * y la nota nace con la fecha de hoy: hay que forzarlo despues, o la prueba
     * comprueba otra cosa distinta de la que dice.
     */
    private function notaDeHace(int $dias, array $extra = []): Nota
    {
        $nota = Nota::create($extra + [
            'texto' => 'Una nota',
            'id_usuario' => $this->usuario()->id,
        ]);

        $nota->forceFill(['created_at' => now()->subDays($dias)])->saveQuietly();

        return $nota->refresh();
    }

    public function test_se_apunta_una_nota(): void
    {
        $this->como()->post('/panel/notas', ['texto' => 'Llamar al técnico'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notas', [
            'texto' => 'Llamar al técnico',
            'hecha' => false,
            'id_usuario' => $this->usuario()->id,
        ]);
    }

    public function test_una_nota_vacia_no_se_guarda(): void
    {
        $this->como()->post('/panel/notas', ['texto' => '  '])
            ->assertSessionHasErrors('texto');

        $this->assertSame(0, Nota::count());
    }

    /**
     * El bloc es COMPARTIDO: en el mesón se turnan varias personas y lo que
     * deja escrito la de la mañana tiene que verlo la de la tarde.
     */
    public function test_el_bloc_lo_ve_todo_el_mundo_no_solo_quien_escribio(): void
    {
        $otro = $this->recepcionista();

        Nota::create(['texto' => 'Lo apuntó otro', 'id_usuario' => $otro->id]);

        $respuesta = $this->como()->get('/panel');

        $notas = collect($respuesta->viewData('page')['props']['notas']);

        $this->assertCount(1, $notas);
        $this->assertSame('Lo apuntó otro', $notas->first()['texto']);
        $this->assertSame($otro->name, $notas->first()['autor']);
    }

    public function test_tachar_una_nota_deja_quien_y_cuando(): void
    {
        $nota = Nota::create(['texto' => 'Pedir toallas', 'id_usuario' => $this->usuario()->id]);

        $this->como()->patch("/panel/notas/{$nota->uuid}")->assertSessionHasNoErrors();

        $nota->refresh();

        $this->assertTrue($nota->hecha);
        $this->assertNotNull($nota->hecha_en);
        $this->assertSame($this->usuario()->id, $nota->id_usuario_hecha);
    }

    /** Tacharla es un clic y equivocarse también: se puede destachar. */
    public function test_destachar_borra_el_rastro_de_que_estaba_hecha(): void
    {
        $nota = Nota::create([
            'texto' => 'Ups',
            'id_usuario' => $this->usuario()->id,
            'hecha' => true,
            'hecha_en' => now(),
            'id_usuario_hecha' => $this->usuario()->id,
        ]);

        $this->como()->patch("/panel/notas/{$nota->uuid}");

        $nota->refresh();

        $this->assertFalse($nota->hecha);
        // Dejar el rastro haría creer que sigue hecha.
        $this->assertNull($nota->hecha_en);
        $this->assertNull($nota->id_usuario_hecha);
    }

    /**
     * Una tarea sin hacer NO caduca sola. Las hechas sí desaparecen al día
     * siguiente, o el bloc se convierte en un archivo histórico.
     */
    public function test_lo_pendiente_de_ayer_sigue_ahi_y_lo_hecho_de_ayer_no(): void
    {
        $this->notaDeHace(3, ['texto' => 'Sigue pendiente de ayer']);

        $this->notaDeHace(2, [
            'texto' => 'Se hizo anteayer',
            'hecha' => true,
            'hecha_en' => now()->subDays(2),
        ]);

        $textos = collect($this->como()->get('/panel')->viewData('page')['props']['notas'])
            ->pluck('texto');

        $this->assertTrue($textos->contains('Sigue pendiente de ayer'));
        $this->assertFalse($textos->contains('Se hizo anteayer'));
    }

    // ---------- Fiado ----------

    private function fiar(array $datos = [])
    {
        return $this->como()->post('/panel/fiados', array_merge([
            'nombre' => 'Un visitante',
            'concepto' => 'Bebida',
            'monto' => 1500,
        ], $datos));
    }

    public function test_se_apunta_algo_fiado(): void
    {
        $this->fiar()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('fiados', [
            'nombre' => 'Un visitante',
            'concepto' => 'Bebida',
            'monto' => 1500,
            'pagado' => false,
        ]);
    }

    /** Sin socio ni nombre no se sabe de quién es la cuenta. */
    public function test_hay_que_decir_de_quien_es(): void
    {
        $this->fiar(['nombre' => '', 'id_cliente' => null])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(0, Fiado::count());
    }

    /**
     * EL QUE IMPORTA.
     *
     * Una bebida del mesón NO es un pago de membresía. `pagos` mueve la caja
     * del día, los informes de ingresos y el saldo del socio; meter ahí $1.500
     * descuadraría las tres cosas y ningún informe sabría separarlas después.
     */
    public function test_lo_fiado_no_toca_la_caja_ni_el_saldo_del_socio(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $pagosAntes = Pago::count();
        $cajaAntes = (int) Pago::ingresos()->sum('monto_abonado');

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 3000]);

        $this->assertSame($pagosAntes, Pago::count(), 'Lo fiado creó un pago de membresía.');
        $this->assertSame($cajaAntes, (int) Pago::ingresos()->sum('monto_abonado'));
    }

    /** Cada cosa que se lleva se suma a su cuenta. */
    public function test_lo_fiado_se_va_sumando(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'nombres' => 'Pablo']);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Barra', 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Bebida', 'monto' => 1500]);

        $cuentas = collect($this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas']);

        $this->assertCount(1, $cuentas, 'Las dos líneas del mismo socio son una sola cuenta.');
        $this->assertSame(4000, $cuentas->first()['total']);
        $this->assertCount(2, $cuentas->first()['lineas']);
    }

    /**
     * Dos personas distintas son dos cuentas, aunque una esté a nombre suelto.
     */
    public function test_cada_persona_tiene_su_cuenta(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1000]);
        $this->fiar(['nombre' => 'El hermano de alguien', 'monto' => 2000]);

        $cuentas = collect($this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas']);

        $this->assertCount(2, $cuentas);
    }

    /**
     * Se salda la cuenta ENTERA. Marcarlas de una en una es la forma de
     * dejarse una sin querer y que esa persona arrastre $1.500 para siempre.
     */
    public function test_saldar_paga_toda_la_cuenta_de_esa_persona(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1500]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Fiado::debiendo()->count());
        $this->assertSame(2, Fiado::where('pagado', true)->count());
    }

    /** Y saldar a uno no toca la cuenta de otro. */
    public function test_saldar_a_uno_no_salda_al_otro(): void
    {
        $uno = Cliente::factory()->create(['activo' => true]);
        $otro = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $uno->id, 'nombre' => null, 'monto' => 1000]);
        $this->fiar(['id_cliente' => $otro->id, 'nombre' => null, 'monto' => 2000]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $uno->id]);

        $this->assertSame(
            2000,
            (int) Fiado::debiendo()->where('id_cliente', $otro->id)->sum('monto')
        );
    }

    public function test_saldar_deja_constancia_de_quien_cobro(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1000]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id]);

        $fiado = Fiado::first();

        $this->assertNotNull($fiado->pagado_en);
        $this->assertSame($this->usuario()->id, $fiado->id_usuario_cobro);
    }

    /** Una línea apuntada por error se quita; una ya cobrada no. */
    public function test_se_quita_una_linea_apuntada_por_error(): void
    {
        $this->fiar();
        $fiado = Fiado::firstOrFail();

        $this->como()->delete("/panel/fiados/{$fiado->uuid}")->assertSessionHasNoErrors();

        $this->assertSame(0, Fiado::count());
    }

    public function test_no_se_borra_algo_que_ya_se_cobro(): void
    {
        $this->fiar();
        $fiado = Fiado::firstOrFail();
        $fiado->update(['pagado' => true, 'pagado_en' => now()]);

        $this->como()->delete("/panel/fiados/{$fiado->uuid}")->assertSessionHas('error');

        $this->assertSame(1, Fiado::count());
    }

    /** Lo pagado sale de la lista: la libreta enseña lo que se debe. */
    public function test_lo_pagado_desaparece_de_la_libreta(): void
    {
        $this->fiar();
        Fiado::query()->update(['pagado' => true, 'pagado_en' => now()]);

        $this->assertSame([], $this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas']);
    }

    /**
     * Una nota pendiente que lleva dias ahi SE MARCA, no se borra.
     *
     * Borrarla sola es lo peor que puede pasar: alguien la escribio porque
     * importaba y nadie se entera de que ya no esta. Marcarla obliga a que
     * quien pase por el meson decida: se hace, o se quita.
     */
    public function test_una_nota_que_lleva_dias_se_marca_pero_no_desaparece(): void
    {
        $this->notaDeHace(7, ['texto' => 'Esto lleva una semana']);

        $notas = collect($this->como()->get('/panel')->viewData('page')['props']['notas']);

        $this->assertCount(1, $notas, 'La nota vieja desaparecio sola.');
        $this->assertTrue($notas->first()['vieja']);
        $this->assertSame(7, $notas->first()['dias']);
    }

    public function test_una_nota_de_hoy_no_esta_marcada_como_vieja(): void
    {
        Nota::create(['texto' => 'Recien apuntada', 'id_usuario' => $this->usuario()->id]);

        $notas = collect($this->como()->get('/panel')->viewData('page')['props']['notas']);

        $this->assertFalse($notas->first()['vieja']);
        $this->assertSame(0, $notas->first()['dias']);
    }

    // ---------- La pantalla propia de fiados ----------

    public function test_la_pantalla_de_fiados_separa_lo_que_se_debe_de_lo_cobrado(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Debe esto', 'monto' => 3000]);
        $this->fiar(['nombre' => 'Otro', 'concepto' => 'Ya pago esto', 'monto' => 2000]);

        Fiado::where('concepto', 'Ya pago esto')->update([
            'pagado' => true,
            'pagado_en' => now(),
        ]);

        $props = $this->como()->get('/panel/fiados')->viewData('page')['props'];

        $this->assertCount(1, $props['cuentas']);
        $this->assertSame('Debe esto', $props['cuentas'][0]['lineas'][0]['concepto']);

        $this->assertCount(1, $props['cobrado']);
        $this->assertSame('Ya pago esto', $props['cobrado'][0]['concepto']);
    }

    public function test_la_pantalla_cuenta_lo_que_se_debe_y_lo_cobrado_este_mes(): void
    {
        $this->fiar(['monto' => 3000]);
        $this->fiar(['nombre' => 'Otra persona', 'monto' => 2000]);

        Fiado::where('monto', 2000)->update(['pagado' => true, 'pagado_en' => now()]);

        $cifras = $this->como()->get('/panel/fiados')->viewData('page')['props']['cifras'];

        $this->assertSame(3000, $cifras['se_debe']);
        $this->assertSame(1, $cifras['personas']);
        $this->assertSame(2000, $cifras['cobrado_mes']);
    }

    /** Lo cobrado el mes pasado no cuenta en el de este. */
    public function test_lo_cobrado_el_mes_pasado_no_cuenta_en_este(): void
    {
        $this->fiar(['monto' => 5000]);

        Fiado::query()->update([
            'pagado' => true,
            'pagado_en' => now()->subMonthNoOverflow()->startOfMonth(),
        ]);

        $cifras = $this->como()->get('/panel/fiados')->viewData('page')['props']['cifras'];

        $this->assertSame(0, $cifras['cobrado_mes']);
    }

    /**
     * Las dos pantallas agrupan IGUAL.
     *
     * Si cada una lo hiciera a su manera, la portada podria decir «Juan debe
     * $4.000» y la de fiados repartirlo en dos deudores distintos.
     */
    public function test_la_caja_y_la_pantalla_de_fiados_cuentan_lo_mismo(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1500]);
        $this->fiar(['nombre' => 'Un visitante', 'monto' => 1000]);

        // La caja da el total y la pantalla de fiado lo reparte por persona:
        // las dos tienen que sumar lo mismo y contar las mismas personas.
        $enLaCaja = $this->como()->get('/panel/caja')->viewData('page')['props']['fiado'];
        $enSuPantalla = collect($this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas']);

        $this->assertSame(
            [5000, 2],
            [$enLaCaja['total'], $enLaCaja['personas']]
        );
        $this->assertSame(
            [$enLaCaja['total'], $enLaCaja['personas']],
            [(int) $enSuPantalla->sum('total'), $enSuPantalla->count()]
        );
    }

    // ---------- Deshacer un cobro ----------

    /**
     * HACE FALTA PODER DESHACERLO.
     *
     * «Pago» es un boton y equivocarse de fila es un clic. Sin esto, la deuda
     * de esa persona desaparece y la unica forma de recuperarla es apuntarsela
     * otra vez a mano, inventando conceptos y montos que ya nadie recuerda.
     */
    public function test_se_puede_deshacer_un_cobro_mal_dado(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1500]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id]);
        $this->assertSame(0, Fiado::debiendo()->count());

        $unaLinea = Fiado::where('id_cliente', $socio->id)->first();

        $this->como()->patch("/panel/fiados/{$unaLinea->uuid}/reabrir")
            ->assertSessionHasNoErrors();

        // Se reabre el COBRO ENTERO, no solo la linea que se pulso: cobrar es
        // un gesto y deshacerlo tiene que deshacer el gesto entero.
        $this->assertSame(4000, (int) Fiado::debiendo()->sum('monto'));
    }

    /**
     * Un cobro deja UNA marca de tiempo, no una por línea.
     *
     * `pagado_en` es lo que agrupa las líneas de un mismo gesto, y es por ahí
     * por donde se deshace entero. La hora se pedía dentro del bucle, así que un
     * cobro que cruzara el cambio de segundo —la columna guarda segundos—
     * quedaba partido en dos marcas: deshacerlo reabría solo una parte y el
     * resto de la deuda se quedaba dada por pagada sin que nadie la volviera a
     * ver.
     *
     * Esta prueba fija la invariante y no falla con el código anterior: para eso
     * el cobro tendría que caer justo en el cambio de segundo. Lo que impide es
     * que alguien vuelva a repartir la marca por línea.
     */
    public function test_un_cobro_deja_una_sola_marca_de_tiempo(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1000]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id]);

        $marcas = Fiado::where('id_cliente', $socio->id)
            ->get()
            ->map(fn (Fiado $f) => (string) $f->pagado_en)
            ->unique();

        $this->assertCount(
            1,
            $marcas,
            'Las líneas de un mismo cobro tienen que compartir la marca de tiempo, o deshacerlo solo reabre una parte.'
        );
    }

    /** Deshacer a uno no reabre la cuenta de otro. */
    public function test_deshacer_no_toca_el_cobro_de_otra_persona(): void
    {
        $uno = Cliente::factory()->create(['activo' => true]);
        $otro = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $uno->id, 'nombre' => null, 'monto' => 1000]);
        $this->fiar(['id_cliente' => $otro->id, 'nombre' => null, 'monto' => 2000]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $uno->id]);
        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $otro->id]);

        $deUno = Fiado::where('id_cliente', $uno->id)->first();
        $this->como()->patch("/panel/fiados/{$deUno->uuid}/reabrir");

        $this->assertSame(1000, (int) Fiado::debiendo()->sum('monto'));
        $this->assertTrue(Fiado::where('id_cliente', $otro->id)->first()->pagado);
    }

    public function test_no_se_reabre_algo_que_no_estaba_cobrado(): void
    {
        $this->fiar();
        $fiado = Fiado::firstOrFail();

        $this->como()->patch("/panel/fiados/{$fiado->uuid}/reabrir")
            ->assertSessionHas('error');
    }

    // ---------- El aviso en la ficha del socio ----------

    /**
     * Si viene a pagar su mensualidad y ademas debe tres bebidas, hay que
     * saberlo con la persona delante, no dos semanas despues.
     */
    public function test_la_ficha_del_socio_avisa_de_lo_que_debe_del_meson(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Barra', 'monto' => 2500]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Bebida', 'monto' => 1500]);

        $fiado = $this->como()->get("/panel/clientes/{$socio->uuid}")
            ->viewData('page')['props']['fiado'];

        $this->assertSame(4000, $fiado['total']);
        $this->assertSame(2, $fiado['cuantas']);
    }

    /** Sin deuda no hay aviso: uno que diga «debe $0» es peor que ninguno. */
    public function test_la_ficha_no_avisa_si_no_debe_nada(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);

        $this->assertNull(
            $this->como()->get("/panel/clientes/{$socio->uuid}")->viewData('page')['props']['fiado']
        );
    }

    /** Y lo ya cobrado deja de avisar. */
    public function test_la_ficha_deja_de_avisar_cuando_paga(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 1000]);

        $this->como()->post('/panel/fiados/saldar', ['id_cliente' => $socio->id]);

        $this->assertNull(
            $this->como()->get("/panel/clientes/{$socio->uuid}")->viewData('page')['props']['fiado']
        );
    }

    /**
     * La deuda del meson NO se suma a la de su membresia.
     *
     * Son dos deudas que se cobran por sitios distintos: sumarlas daria una
     * cifra que no se puede cobrar de una vez y que no cuadra con ningun
     * informe.
     */
    public function test_la_deuda_del_meson_va_aparte_de_la_de_la_membresia(): void
    {
        $socio = Cliente::factory()->create(['activo' => true]);
        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'monto' => 3000]);

        $props = $this->como()->get("/panel/clientes/{$socio->uuid}")->viewData('page')['props'];

        $this->assertSame(3000, $props['fiado']['total']);
        $this->assertSame(0, $props['resumen']['debe'], 'Lo fiado se colo en la deuda de membresias.');
    }

    // ---------- El dinero y quién lo ve ----------

    /**
     * EL RESUMEN NO LLEVA PLATA, para nadie.
     *
     * Es lo primero que abre quien atiende el mesón. La caja del día, lo que se
     * debe y lo fiado se fueron a Caja: aquí no viajan ni tapados.
     */
    public function test_el_resumen_no_lleva_plata(): void
    {
        $this->fiar(['monto' => 2500]);

        $props = $this->como()->get('/panel')->assertOk()->viewData('page')['props'];

        $this->assertArrayNotHasKey('caja', $props, 'La caja volvió al resumen.');
        $this->assertArrayNotHasKey('fiados', $props, 'Lo fiado volvió al resumen.');
    }

    /**
     * Recepción NO entra a la caja. No es que se le tape la cifra: la página no
     * se le abre, porque la plata es el permiso `reportes.ver`.
     */
    public function test_recepcion_no_entra_a_la_caja(): void
    {
        $this->actingAs($this->recepcionista())
            ->get('/panel/caja')
            ->assertForbidden();
    }

    public function test_quien_puede_ver_los_informes_ve_la_caja(): void
    {
        $caja = $this->como()->get('/panel/caja')->assertOk()->viewData('page')['props']['caja'];

        $this->assertIsArray($caja);
        $this->assertArrayHasKey('hoy', $caja);
        $this->assertArrayHasKey('mes', $caja);
        $this->assertArrayHasKey('por_cobrar', $caja);
    }

    /** Y la cifra de «por cobrar» es de membresías, sin lo fiado del mesón. */
    public function test_lo_fiado_no_entra_en_el_por_cobrar_de_membresias(): void
    {
        $antes = $this->como()->get('/panel/caja')->viewData('page')['props']['caja']['por_cobrar'];

        $this->fiar(['monto' => 9999]);

        $despues = $this->como()->get('/panel/caja')->viewData('page')['props']['caja']['por_cobrar'];

        $this->assertSame($antes, $despues);
    }
    // ---------- Lo que más se fía ----------

    /**
     * LAS COSAS QUE MÁS SE FÍAN, para no teclearlas otra vez.
     *
     * En el mesón se venden siempre las mismas cinco cosas. Lo que sale aquí
     * es lo repetido con el precio de la última vez; lo que se fió una sola
     * vez no sale, porque una lista de cosas sueltas no es un atajo.
     */
    public function test_lo_que_mas_se_fia_sale_con_su_ultimo_precio(): void
    {
        $this->fiar(['concepto' => 'Barra de proteína', 'monto' => 2000]);
        $this->fiar(['concepto' => 'BARRA DE PROTEÍNA', 'monto' => 2000]);
        // Escrita de otra forma, es la misma cosa: si no, «Bebida» y «bebida»
        // serían dos atajos distintos y ninguno llegaría a repetirse.
        $this->fiar(['concepto' => 'barra de proteína', 'monto' => 2500]);
        $this->fiar(['concepto' => 'Bebida', 'monto' => 1500]);
        $this->fiar(['concepto' => 'Bebida', 'monto' => 1500]);
        $this->fiar(['concepto' => 'Muñequeras', 'monto' => 9000]);

        $frecuentes = $this->como()->getJson('/panel/fiados/frecuentes')->json('frecuentes');

        // La barra, primero: tres veces contra dos, y con el precio de la
        // última vez, no con el viejo.
        $this->assertSame(['barra de proteína', 'Bebida'], array_column($frecuentes, 'concepto'));
        $this->assertSame(2500, $frecuentes[0]['monto']);

        // Lo que se fió una sola vez no es un atajo.
        $this->assertNotContains('Muñequeras', array_column($frecuentes, 'concepto'));
    }

    /** La pantalla dice lo cobrado HOY, que es lo que se cuadra al cerrar. */
    public function test_la_pantalla_cuenta_lo_cobrado_hoy(): void
    {
        $this->fiar(['nombre' => 'De hoy', 'monto' => 2000]);
        $this->fiar(['nombre' => 'De antes', 'monto' => 5000]);

        Fiado::where('nombre', 'De hoy')->update(['pagado' => true, 'pagado_en' => now()]);
        Fiado::where('nombre', 'De antes')->update([
            'pagado' => true,
            'pagado_en' => now()->subDays(3),
            'created_at' => now()->subDays(3),
        ]);

        $cifras = $this->como()->get('/panel/fiados')->viewData('page')['props']['cifras'];

        $this->assertSame(2000, $cifras['cobrado_hoy']);
        // Lo de hace tres días sigue contando en el mes, pero no en el día.
        $this->assertSame(7000, $cifras['cobrado_mes']);
        $this->assertSame(2000, $cifras['anotado_hoy']);
    }

    /**
     * CON QUÉ COBRAR: la cuenta lleva el celular y la foto del socio.
     *
     * Sin el celular no se le puede recordar por WhatsApp, y recordárselo por
     * escrito es lo que evita tener que pedirle plata de frente a alguien que
     * viene a entrenar.
     */
    public function test_la_cuenta_trae_con_que_reconocerlo_y_con_que_escribirle(): void
    {
        $socio = Cliente::factory()->create(['activo' => true, 'celular' => '912345678']);

        $this->fiar(['id_cliente' => $socio->id, 'nombre' => null, 'concepto' => 'Bebida', 'monto' => 1500]);

        $cuenta = $this->como()->get('/panel/fiados')->viewData('page')['props']['cuentas'][0];

        $this->assertSame('912345678', $cuenta['celular']);
        $this->assertArrayHasKey('foto', $cuenta);
    }
}
