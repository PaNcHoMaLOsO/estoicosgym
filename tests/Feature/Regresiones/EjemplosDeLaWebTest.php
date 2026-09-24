<?php

namespace Tests\Feature\Regresiones;

use App\Models\ContenidoWeb;
use App\Models\Especialista;
use Tests\CasoConCatalogos;

/**
 * Los ejemplos de la web entran y SALEN sin tocar lo cargado a mano.
 *
 * Son inventados: un testimonio falso publicado es publicidad engañosa. Lo que
 * importa es que quitarlos sea un comando y que no se lleve por delante a un
 * especialista de verdad.
 */
class EjemplosDeLaWebTest extends CasoConCatalogos
{
    public function test_los_ejemplos_se_quitan_sin_tocar_lo_real(): void
    {
        $real = Especialista::create(['tipo' => 'especialista', 'nombre' => 'Persona real', 'especialidad' => 'Nutrición', 'activo' => true]);

        $this->artisan('web:ejemplos')->assertSuccessful();

        $this->assertSame(8, Especialista::count());
        $this->assertSame(4, ContenidoWeb::where('tipo', 'testimonio')->count());

        // Correrlo dos veces no duplica.
        $this->artisan('web:ejemplos')->assertSuccessful();
        $this->assertSame(8, Especialista::count());

        $this->artisan('web:ejemplos --quitar')->assertSuccessful();

        $this->assertSame([$real->id], Especialista::pluck('id')->all());
        $this->assertSame(0, ContenidoWeb::where('tipo', 'testimonio')->count());
    }
}
