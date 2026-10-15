<?php

namespace Tests\Feature\Regresiones;

use App\Services\ResenasDeGoogle;
use App\Support\Ajustes;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\CasoConCatalogos;

/**
 * Las reseñas de Google Maps, solas en la portada.
 *
 * Lo que se vigila: que la ficha se busque una vez y quede anotada, que solo
 * salgan las de 4 y 5 estrellas con texto (antes de los testimonios a mano),
 * que una llave mala se explique en el panel sin romper la portada, y que sin
 * llave no se le pregunte nada a Google.
 */
class ResenasDeGoogleTest extends CasoConCatalogos
{
    private function conGoogle(): void
    {
        Ajustes::guardar(['web.google_llave' => 'llave-de-prueba']);
        // Que la portada no salga a buscar por su cuenta: aquí se cuentan las consultas.
        Cache::put('resenas.revisado', 1, 3600);

        Http::fake([
            'places.googleapis.com/v1/places:searchText' => Http::response(['places' => [['id' => 'ChIJ-progym']]]),
            'places.googleapis.com/v1/places/ChIJ-progym*' => Http::response([
                'displayName' => ['text' => 'ProGym'],
                'formattedAddress' => 'Rengo 386, Los Ángeles',
                'rating' => 4.86,
                'userRatingCount' => 37,
                'googleMapsUri' => 'https://maps.google.com/?cid=123',
                'reviews' => [
                    ['rating' => 5, 'originalText' => ['text' => 'Excelente gimnasio, los profes siempre atentos.'], 'authorAttribution' => ['displayName' => 'Camila R.', 'uri' => 'https://www.google.com/maps/contrib/1'], 'relativePublishTimeDescription' => 'hace 2 semanas'],
                    ['rating' => 2, 'originalText' => ['text' => 'Muy lleno en la tarde, falta ventilación.'], 'authorAttribution' => ['displayName' => 'Pedro'], 'relativePublishTimeDescription' => 'hace un mes'],
                    ['rating' => 5, 'authorAttribution' => ['displayName' => 'Sin texto']],
                    ['rating' => 4, 'text' => ['text' => 'Buenas máquinas y buen precio.'], 'authorAttribution' => ['displayName' => 'Javiera', 'uri' => 'javascript:alert(1)']],
                ],
            ]),
        ]);
    }

    public function test_la_portada_muestra_la_nota_y_las_buenas_resenas(): void
    {
        $this->conGoogle();

        $this->assertSame(2, ResenasDeGoogle::traer());

        $this->get('/')
            ->assertOk()
            ->assertSee('4,9')
            ->assertSee('37 reseñas en Google')
            ->assertSee('href="https://maps.google.com/?cid=123"', false)
            ->assertSeeInOrder(['Excelente gimnasio', 'Camila R.', 'Buenas máquinas', 'Javiera'])
            ->assertDontSee('falta ventilación')
            ->assertDontSee('Sin texto')
            ->assertDontSee('javascript:alert', false);

        // La ficha se buscó una vez y quedó anotada.
        ResenasDeGoogle::traer();
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $r) => $r->hasHeader('X-Goog-Api-Key', 'llave-de-prueba'));
    }

    public function test_el_panel_dice_como_salio_y_trae_al_pedirlo(): void
    {
        $this->conGoogle();

        $this->actingAs($this->administrador())
            ->post('/panel/web/resenas/traer')
            ->assertSessionHas('success', 'Listo: 2 reseñas de Google en la portada.');

        $this->actingAs($this->administrador())->get('/panel/web/testimonio')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('google.ficha', 'Rengo 386, Los Ángeles')
                ->where('google.resenas.0.titulo', 'Camila R.')
                ->etc());
    }

    public function test_una_llave_mala_se_explica_y_la_portada_sigue(): void
    {
        Ajustes::guardar(['web.google_llave' => 'llave-mala']);
        Http::fake(['places.googleapis.com/*' => Http::response(['error' => ['code' => 403]], 403)]);

        $this->actingAs($this->administrador())
            ->post('/panel/web/resenas/traer')
            ->assertSessionHas('error');
        $this->assertStringContainsString('Places API (New)', ResenasDeGoogle::ultimaVuelta()['mensaje']);

        $this->get('/')->assertOk()->assertDontSee('reseñas en Google');
    }

    public function test_sin_llave_no_se_le_pregunta_a_google(): void
    {
        Http::fake();
        Cache::forget('resenas.revisado');

        $this->get('/')->assertOk()->assertDontSee('reseñas en Google');
        $this->artisan('resenas:traer')->assertSuccessful();

        Http::assertNothingSent();
    }
}
