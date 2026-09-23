<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\InformeGuardado;
use App\Models\Inscripcion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Services\ConstructorInformes;
use Tests\CasoConCatalogos;

/**
 * El constructor de informes: sus columnas y los informes guardados.
 *
 * LO QUE SE VIGILA es que una columna del catálogo no salga vacía. Una columna
 * que existe pero no trae nada es peor que no tenerla: quien la elige da por
 * hecho que ese dato no está en el sistema, y termina sacando el informe a mano.
 */
class ConstructorDeInformesTest extends CasoConCatalogos
{
    /** @return array{0:Cliente,1:Inscripcion} */
    private function socioConMembresia(): array
    {
        $socio = Cliente::factory()->create([
            'activo' => true,
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Pinto',
            'apellido_materno' => null,
            'run_pasaporte' => '15.628.245-6',
            'celular' => '912345678',
        ]);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'precio_base' => 40000,
            'precio_final' => 40000,
            'fecha_inicio' => today()->subDays(5),
            'fecha_vencimiento' => today()->addDays(25),
        ]);

        Pago::factory()->create([
            'id_cliente' => $socio->id,
            'id_inscripcion' => $inscripcion->id,
            'monto_total' => 40000,
            'monto_abonado' => 30000,
            'monto_pendiente' => 10000,
            'fecha_pago' => today(),
            'id_metodo_pago' => MetodoPago::value('id'),
            'id_estado' => 202,
        ]);

        return [$socio, $inscripcion];
    }

    /**
     * @param  list<string>  $columnas
     * @return array<string,mixed>
     */
    private function primeraFila(string $modulo, array $columnas): array
    {
        $informe = app(ConstructorInformes::class)->ejecutar($modulo, [
            'columnas' => $columnas,
            'limite' => 10,
        ]);

        return $informe['filas'][0] ?? [];
    }

    /**
     * CON QUÉ LLAMAR A LA GENTE. Una lista de membresías sin RUT ni celular
     * solo sirve para mirarla, y estas listas se hacen para llamar.
     */
    public function test_las_membresias_traen_el_rut_y_el_celular_del_socio(): void
    {
        $this->socioConMembresia();

        $fila = $this->primeraFila('inscripciones', ['socio', 'rut', 'celular', 'plan']);

        $this->assertSame('Rosa Pinto', $fila['socio']);
        $this->assertSame('15.628.245-6', $fila['rut']);
        $this->assertSame('912345678', $fila['celular']);
        $this->assertSame('Mensual', $fila['plan']);
    }

    /** Lo pagado y lo que falta, que es la otra pregunta de una membresía. */
    public function test_las_membresias_dicen_lo_abonado_y_lo_que_se_debe(): void
    {
        $this->socioConMembresia();

        $fila = $this->primeraFila('inscripciones', ['abonado', 'debe', 'dias']);

        // El constructor devuelve el dinero como número, no como texto.
        $this->assertSame(30000.0, $fila['abonado']);
        $this->assertSame(10000.0, $fila['debe']);
        $this->assertSame(25.0, $fila['dias']);
    }

    /** De qué plan era el cobro: antes había que cruzar dos informes a mano. */
    public function test_los_pagos_dicen_de_que_plan_eran(): void
    {
        $this->socioConMembresia();

        $fila = $this->primeraFila('pagos', ['socio', 'rut', 'plan', 'monto_abonado']);

        $this->assertSame('Mensual', $fila['plan']);
        $this->assertSame('15.628.245-6', $fila['rut']);
    }

    /** La última membresía del socio, en la lista de socios. */
    public function test_los_socios_dicen_su_ultimo_plan_y_cuando_vence(): void
    {
        [, $inscripcion] = $this->socioConMembresia();

        $fila = $this->primeraFila('clientes', ['nombres', 'ultimo_plan', 'ultimo_vence']);

        $this->assertSame('Mensual', $fila['ultimo_plan']);
        $this->assertSame($inscripcion->fecha_vencimiento->format('d/m/Y'), $fila['ultimo_vence']);
    }

    // ---------- Informes guardados ----------

    /**
     * SE GUARDA LA RECETA, NO LAS FILAS. Al abrirlo tiene que traer los datos de
     * hoy; guardar el resultado sería una foto que envejece sola en un cajón.
     */
    public function test_se_guarda_un_informe_y_vuelve_con_su_receta(): void
    {
        $usuario = $this->administrador();

        $this->actingAs($usuario)->post('/panel/reportes/constructor/guardados', [
            'nombre' => 'Socios con deuda',
            'modulo' => 'inscripciones',
            'configuracion' => [
                'columnas' => ['socio', 'debe'],
                'filtros' => ['id_estado' => 100],
                'orden' => 'fecha_vencimiento',
                'direccion' => 'asc',
                'limite' => 250,
            ],
        ])->assertSessionHasNoErrors();

        $guardados = $this->actingAs($usuario)->get('/panel/reportes/constructor')
            ->viewData('page')['props']['guardados'];

        $this->assertCount(1, $guardados);
        $this->assertSame('Socios con deuda', $guardados[0]['nombre']);
        $this->assertSame(['socio', 'debe'], $guardados[0]['configuracion']['columnas']);
        $this->assertSame(250, $guardados[0]['configuracion']['limite']);
    }

    /** Con el mismo nombre se pisa: dos «Socios con deuda» no se distinguen. */
    public function test_guardar_con_el_mismo_nombre_no_deja_dos(): void
    {
        $usuario = $this->administrador();

        foreach ([['socio'], ['socio', 'debe']] as $columnas) {
            $this->actingAs($usuario)->post('/panel/reportes/constructor/guardados', [
                'nombre' => 'El mismo',
                'modulo' => 'inscripciones',
                'configuracion' => ['columnas' => $columnas],
            ]);
        }

        $this->assertSame(1, InformeGuardado::where('id_usuario', $usuario->id)->count());
        $this->assertSame(
            ['socio', 'debe'],
            InformeGuardado::first()->configuracion['columnas'],
            'El segundo guardado tiene que reemplazar al primero.'
        );
    }

    /** La lista de cada uno es suya: es su forma de trabajar, no un cajón común. */
    public function test_cada_uno_ve_solo_sus_informes(): void
    {
        $otro = $this->administrador();

        InformeGuardado::create([
            'nombre' => 'Lo mío',
            'modulo' => 'clientes',
            'configuracion' => ['columnas' => ['nombres']],
            'id_usuario' => $otro->id,
        ]);

        $guardados = $this->actingAs($this->administrador())->get('/panel/reportes/constructor')
            ->viewData('page')['props']['guardados'];

        $this->assertSame([], $guardados);
    }

    /** Y no se puede borrar el de otro. */
    public function test_no_se_borra_el_informe_de_otro(): void
    {
        $dueno = $this->administrador();

        $informe = InformeGuardado::create([
            'nombre' => 'Suyo',
            'modulo' => 'clientes',
            'configuracion' => ['columnas' => ['nombres']],
            'id_usuario' => $dueno->id,
        ]);

        $this->actingAs($this->administrador())
            ->delete("/panel/reportes/constructor/guardados/{$informe->uuid}")
            ->assertForbidden();

        $this->assertDatabaseHas('informes_guardados', ['id' => $informe->id]);
    }

    /** Un módulo inventado no se guarda. */
    public function test_no_se_guarda_un_informe_de_algo_que_no_existe(): void
    {
        $this->actingAs($this->administrador())->post('/panel/reportes/constructor/guardados', [
            'nombre' => 'Inventado',
            'modulo' => 'naves_espaciales',
            'configuracion' => ['columnas' => ['nombre']],
        ])->assertNotFound();
    }
}
