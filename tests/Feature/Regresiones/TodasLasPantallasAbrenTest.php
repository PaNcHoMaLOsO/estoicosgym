<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use Illuminate\Support\Facades\Route;
use Tests\CasoConCatalogos;

/**
 * Cada pantalla del panel abre.
 *
 * Es la prueba más tonta y la que más veces ha servido: una pantalla que revienta
 * al abrirse no la detecta ninguna prueba de negocio, porque el fallo está en un
 * dato que no llega, un método que ya no existe o una variable de plantilla mal
 * escrita. Aquí se recorren TODAS las rutas GET del panel con datos de verdad
 * detrás, y cualquiera que se añada mañana entra sola en la lista.
 */
class TodasLasPantallasAbrenTest extends CasoConCatalogos
{
    /**
     * Un gimnasio en miniatura, para que las pantallas tengan qué pintar.
     *
     * Con la base vacía casi todo devuelve 200 sin haber ejecutado nada: las
     * consultas no encuentran filas y los bucles no dan una vuelta. El fallo
     * aparece con datos.
     *
     * @return array<string,string>
     */
    private function sembrar(): array
    {
        $socio = Cliente::factory()->create([
            'activo' => true,
            'email' => 'socio@progym.cl',
            'foto_perfil' => null,
        ]);

        $convenio = Convenio::create([
            'nombre' => 'Empresa de prueba',
            'tipo' => 'empresa',
            'activo' => true,
        ]);

        $inscripcion = Inscripcion::factory()->create([
            'id_cliente' => $socio->id,
            'id_membresia' => 4,
            'id_estado' => 100,
            'fecha_inicio' => now()->subDays(20),
            'fecha_vencimiento' => now()->addDays(5),
            'precio_base' => 40000,
            'precio_final' => 40000,
        ]);

        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $socio->id,
            'monto_total' => 40000,
            'monto_abonado' => 20000,
            'monto_pendiente' => 20000,
            'id_estado' => 202,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => MetodoPago::first()->id,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $plantilla = TipoNotificacion::first() ?? TipoNotificacion::create([
            'codigo' => 'prueba',
            'nombre' => 'Aviso',
            'asunto_email' => 'Hola {nombre}',
            'plantilla_email' => '<p>Hola</p>',
            'activo' => true,
        ]);

        $notificacion = Notificacion::create([
            'id_tipo_notificacion' => $plantilla->id,
            'id_cliente' => $socio->id,
            'email_destino' => $socio->email,
            'asunto' => 'Hola',
            'contenido' => '<p>Hola</p>',
            'id_estado' => 601,
            'fecha_programada' => today(),
            'tipo_envio' => 'manual',
        ]);

        // Algo en la papelera, para que esa pantalla tenga filas.
        $borrable = Cliente::factory()->create(['activo' => true]);
        $borrable->delete();

        return [
            'cliente' => $socio->uuid,
            'inscripcion' => $inscripcion->uuid,
            'pago' => $pago->uuid,
            'membresia' => Membresia::find(4)->uuid,
            'convenio' => $convenio->uuid,
            // La página web: cualquiera de sus cuatro tipos.
            'tipo' => 'servicio',
            'notificacion' => $notificacion->uuid,
            'tipoNotificacion' => (string) $plantilla->id,
            'modulo' => 'pagos',
        ];
    }

    public function test_todas_las_pantallas_del_panel_abren(): void
    {
        $valores = $this->sembrar();
        $usuario = $this->administrador();

        $rotas = [];
        $miradas = 0;

        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();

            if (! $nombre || ! str_starts_with($nombre, 'panel.')) {
                continue;
            }

            if (! in_array('GET', $ruta->methods(), true)) {
                continue;
            }

            $url = $this->rellenar($ruta->uri(), $valores);

            // Una ruta con un parámetro que esta prueba no sabe rellenar se
            // salta EN VOZ ALTA: callarlo dejaría un hueco sin que se note.
            if ($url === null) {
                $rotas[] = "{$nombre}: no sé con qué rellenar «{$ruta->uri()}»";

                continue;
            }

            $miradas++;
            $respuesta = $this->actingAs($usuario)->get($url);

            // getStatusCode y no status(): la descarga del CSV devuelve una
            // respuesta que se escribe a medida que sale y no tiene ese atajo.
            $codigo = $respuesta->baseResponse->getStatusCode();

            // 302 vale: hay pantallas que redirigen a propósito, como renovar
            // una membresía a la que le sobran días.
            if (! in_array($codigo, [200, 302], true)) {
                $rotas[] = "{$nombre} ({$url}) devolvió {$codigo}";
            }
        }

        $this->assertSame([], $rotas, "Pantallas que no abren:\n" . implode("\n", $rotas));
        $this->assertGreaterThan(20, $miradas, 'Se miraron muy pocas pantallas: algo falla en la prueba.');
    }

    /**
     * Cambia los {parametros} de la ruta por valores de verdad.
     *
     * @param array<string,string> $valores
     */
    private function rellenar(string $uri, array $valores): ?string
    {
        preg_match_all('/\{(\w+)\??\}/', $uri, $encontrados);

        foreach ($encontrados[1] as $parametro) {
            if (! isset($valores[$parametro])) {
                return null;
            }

            $uri = preg_replace('/\{' . $parametro . '\??\}/', $valores[$parametro], $uri);
        }

        return '/' . ltrim($uri, '/');
    }
}
