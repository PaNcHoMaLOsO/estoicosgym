<?php

namespace Tests\Feature\Regresiones;

use App\Services\CorreoService;
use App\Support\Ajustes;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\CasoConCatalogos;

/**
 * Gmail gratis bloquea la cuenta un día entero pasado de 500 correos. Se para
 * antes, y lo urgente (recuperar la clave) tiene su reserva.
 */
class TopeDiarioDeCorreosTest extends CasoConCatalogos
{
    private function revisar(bool $urgente): void
    {
        $metodo = new \ReflectionMethod(CorreoService::class, 'revisarElTope');
        $metodo->invoke(app(CorreoService::class), $urgente);
    }

    public function test_al_llegar_al_tope_no_sale_nada_que_no_sea_urgente(): void
    {
        Ajustes::guardar(['correo.tope_diario' => '10']);
        Cache::put('correos-enviados:' . now()->toDateString(), 10, now()->endOfDay());

        try {
            $this->revisar(false);
            $this->fail('Tenía que frenar en el tope.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tope del día', $e->getMessage());
        }

        // Lo urgente todavía tiene reserva.
        $this->revisar(true);
        $this->assertTrue(true);
    }

    public function test_bajo_el_tope_sale(): void
    {
        Ajustes::guardar(['correo.tope_diario' => '10']);
        Cache::put('correos-enviados:' . now()->toDateString(), 9, now()->endOfDay());

        $this->revisar(false);
        $this->assertSame(9, CorreoService::enviadosHoy());
    }

    public function test_la_cuenta_de_correo_dice_cuantos_van(): void
    {
        Cache::put('correos-enviados:' . now()->toDateString(), 37, now()->endOfDay());

        $correo = $this->actingAs($this->administrador())->get('/panel/configuracion/correo')
            ->viewData('page')['props']['extra']['correo'];

        $this->assertSame(37, $correo['enviados_hoy']);
        $this->assertSame(400, $correo['tope_diario']);
    }
}
