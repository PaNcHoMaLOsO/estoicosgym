<?php

namespace Tests\Feature\Regresiones;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Tests\CasoConCatalogos;

/**
 * Los accesos rápidos del Resumen se abren en una ventana.
 *
 * La ventana (`resources/js/components/ModalDePagina.jsx`) no tiene una copia de
 * cada formulario: le pide al servidor la pantalla de siempre por el protocolo
 * de Inertia y pinta ese componente dentro. Esta prueba amarra ese contrato: si
 * una de estas direcciones deja de responder así, el acceso rápido se queda en
 * «Cargando…» o salta a la página entera sin que nadie lo note.
 */
class AccesosRapidosEnVentanaTest extends CasoConCatalogos
{
    /** @return array<string, array{0:string,1:string}> */
    public static function accesos(): array
    {
        return [
            'nuevo socio' => ['/panel/clientes/crear', 'Clientes/Crear'],
            'cobrar' => ['/panel/pagos/cobrar', 'Pagos/Crear'],
            'nueva inscripción' => ['/panel/inscripciones/crear', 'Inscripciones/Crear'],
            'anotar fiado' => ['/panel/fiados', 'Fiados'],
            'entrada por canje' => ['/panel/canje', 'Canje'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('accesos')]
    public function test_cada_acceso_responde_con_su_pantalla_y_sus_datos(string $url, string $componente): void
    {
        $version = app(HandleInertiaRequests::class)->version(Request::create($url));

        $respuesta = $this->actingAs($this->administrador())->get($url, [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) $version,
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $respuesta->assertOk()->assertHeader('X-Inertia', 'true');
        $this->assertSame($componente, $respuesta->json('component'));
        $this->assertIsArray($respuesta->json('props'));
        // El archivo de la pantalla existe: es el que la ventana va a cargar.
        $this->assertFileExists(resource_path("js/pages/{$componente}.jsx"));
    }
}
