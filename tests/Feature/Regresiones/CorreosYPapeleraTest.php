<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Support\Ajustes;
use Tests\CasoConCatalogos;

/**
 * El interruptor de los correos automáticos y el borrado desde la papelera.
 *
 * Lo que se vigila: que apagados no salga NINGÚN correo solo, que encendidos
 * sigan saliendo, y que a un socio que está en la papelera se le puedan borrar
 * sus datos personales sin llevarse por delante sus pagos, que son las cuentas
 * del gimnasio.
 */
class CorreosYPapeleraTest extends CasoConCatalogos
{
    private function socioEnLaPapelera(): Cliente
    {
        $cliente = Cliente::factory()->create(['activo' => false]);
        $cliente->delete();

        return $cliente;
    }

    /** EL QUE IMPORTA: apagado, la tarea diaria no manda ni programa nada. */
    public function test_apagados_no_sale_ningun_correo_automatico(): void
    {
        Ajustes::guardar(['tareas.correos_automaticos' => '0']);

        $cliente = Cliente::factory()->create(['activo' => true]);
        Inscripcion::factory()->create([
            'id_cliente' => $cliente->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_vencimiento' => today()->addDays(3),
        ]);

        $this->artisan('notificaciones:enviar --todo')
            ->expectsOutputToContain('apagados')
            ->assertSuccessful();

        $this->assertSame(0, Notificacion::count(), 'Se programaron avisos con los correos apagados.');
    }

    /** Encendido —como viene de fábrica— la tarea corre como siempre. */
    public function test_encendidos_la_tarea_corre(): void
    {
        $this->assertTrue(Ajustes::activo('tareas.correos_automaticos'));

        $this->artisan('notificaciones:enviar --programar')->assertSuccessful();
    }

    /** Se apaga y se enciende desde Configuración. */
    public function test_el_interruptor_se_guarda_desde_configuracion(): void
    {
        $this->actingAs($this->administrador())
            ->put('/panel/configuracion', ['tareas.correos_automaticos' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertFalse(Ajustes::activo('tareas.correos_automaticos'));
    }

    /**
     * EL OTRO QUE IMPORTA: borrar sus datos NO se lleva sus pagos.
     *
     * Los ingresos de años anteriores tienen que seguir cuadrando: la ficha
     * queda como «Socio Borrado» y sus pagos se quedan donde estaban.
     */
    public function test_se_borran_los_datos_de_un_socio_de_la_papelera_y_sus_pagos_se_quedan(): void
    {
        $cliente = $this->socioEnLaPapelera();
        $pagos = $cliente->pagos()->count();

        $this->actingAs($this->administrador())
            ->post("/panel/papelera/clientes/{$cliente->id}/borrar-datos", [
                'motivo' => 'solicitud',
                'confirmacion' => 'BORRAR',
            ])
            ->assertSessionHasNoErrors();

        $borrado = Cliente::withTrashed()->find($cliente->id);

        $this->assertNotNull($borrado->datos_borrados_en);
        $this->assertNull($borrado->run_pasaporte);
        $this->assertNull($borrado->email);
        $this->assertSame('Borrado', $borrado->apellido_paterno);
        $this->assertSame($pagos, $borrado->pagos()->count(), 'Se llevó por delante sus pagos.');
    }

    /** Sin escribir BORRAR no se borra nada. */
    public function test_sin_confirmar_no_se_borra(): void
    {
        $cliente = $this->socioEnLaPapelera();

        $this->actingAs($this->administrador())
            ->post("/panel/papelera/clientes/{$cliente->id}/borrar-datos", [
                'motivo' => 'solicitud',
                'confirmacion' => 'si',
            ])
            ->assertSessionHasErrors('confirmacion');

        $this->assertNull(Cliente::withTrashed()->find($cliente->id)->datos_borrados_en);
    }

    /** Recepción no borra los datos de nadie, aunque entre a la papelera. */
    public function test_recepcion_no_borra_datos_desde_la_papelera(): void
    {
        $cliente = $this->socioEnLaPapelera();

        $this->actingAs($this->recepcionista())
            ->post("/panel/papelera/clientes/{$cliente->id}/borrar-datos", [
                'motivo' => 'solicitud',
                'confirmacion' => 'BORRAR',
            ])
            ->assertForbidden();

        $this->assertNull(Cliente::withTrashed()->find($cliente->id)->datos_borrados_en);
    }
}
