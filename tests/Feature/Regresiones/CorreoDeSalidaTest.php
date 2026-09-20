<?php

namespace Tests\Feature\Regresiones;

use App\Services\CorreoService;
use App\Support\Ajustes;
use Illuminate\Support\Facades\DB;
use Tests\CasoConCatalogos;

/**
 * La cuenta de correo del gimnasio se cambia desde el panel.
 *
 * Antes vivía solo en un archivo del servidor: cambiar de correo —o renovar la
 * contraseña de aplicación de Gmail, que caduca— obligaba a entrar al equipo, y
 * eso no lo puede hacer quien lleva el gimnasio.
 *
 * Lo que se vigila: que lo guardado en el panel mande sobre el archivo, que la
 * contraseña se guarde CIFRADA y no vuelva nunca al navegador, que dejar el
 * campo vacío no la borre, y que recepción no pueda tocar nada de esto.
 */
class CorreoDeSalidaTest extends CasoConCatalogos
{
    private function guardar(array $valores): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->administrador())->put('/panel/configuracion', $valores);
    }

    /** EL QUE IMPORTA: lo que se escribe en el panel es por donde sale el correo. */
    public function test_la_cuenta_del_panel_manda_sobre_la_del_archivo(): void
    {
        config([
            'mail.mailers.smtp.host' => 'smtp.viejo.cl',
            'mail.from.address' => 'viejo@progym.cl',
        ]);

        $this->guardar([
            'correo.remitente' => 'nuevo@progym.cl',
            'correo.smtp_host' => 'smtp.nuevo.cl',
            'correo.smtp_usuario' => 'nuevo@progym.cl',
            'correo.smtp_clave' => 'clave-de-aplicacion',
        ])->assertSessionHasNoErrors();

        $this->assertStringContainsString('smtp.nuevo.cl', app(CorreoService::class)->descripcion('smtp'));
        $this->assertSame('nuevo@progym.cl', Ajustes::obtener('correo.remitente'));
    }

    /** La contraseña se guarda cifrada: en la tabla no está lo que se escribió. */
    public function test_la_contrasena_se_guarda_cifrada(): void
    {
        $this->guardar(['correo.smtp_clave' => 'secreto-de-gmail'])->assertSessionHasNoErrors();

        $guardado = DB::table('ajustes')->where('clave', 'correo.smtp_clave')->value('valor');

        $this->assertNotSame('secreto-de-gmail', $guardado);
        $this->assertStringNotContainsString('secreto-de-gmail', (string) $guardado);
        // Y se puede volver a leer para poder enviar.
        $this->assertSame('secreto-de-gmail', Ajustes::obtener('correo.smtp_clave'));
    }

    /** La pantalla NUNCA recibe la contraseña: solo si hay una guardada. */
    public function test_la_pantalla_no_recibe_la_contrasena(): void
    {
        $this->guardar(['correo.smtp_clave' => 'secreto-de-gmail']);

        $props = $this->actingAs($this->administrador())
            ->get('/panel/configuracion/correo')
            ->viewData('page')['props'];

        $clave = collect($props['grupo']['ajustes'])->firstWhere('clave', 'correo.smtp_clave');

        $this->assertSame('', $clave['valor']);
        $this->assertTrue($clave['guardado']);
        $this->assertStringNotContainsString('secreto-de-gmail', json_encode($props));
    }

    /**
     * Dejar el campo vacío NO borra la contraseña.
     *
     * La pantalla nunca la recibe, así que el campo llega en blanco cada vez
     * que se guarda cualquier otra cosa: si eso la borrara, el gimnasio se
     * quedaría sin correo por cambiar el nombre del remitente.
     */
    public function test_guardar_con_el_campo_vacio_conserva_la_contrasena(): void
    {
        $this->guardar(['correo.smtp_clave' => 'secreto-de-gmail']);
        $this->guardar(['correo.smtp_clave' => '', 'correo.nombre_remitente' => 'PRO GYM']);

        $this->assertSame('secreto-de-gmail', Ajustes::obtener('correo.smtp_clave'));
    }

    /** Para quitarla hay que escribir BORRAR: no se hace sin querer. */
    public function test_se_borra_escribiendo_borrar(): void
    {
        $this->guardar(['correo.smtp_clave' => 'secreto-de-gmail']);
        $this->guardar(['correo.smtp_clave' => 'BORRAR']);

        $this->assertSame('', Ajustes::obtener('correo.smtp_clave'));
    }

    /** Un remitente sin arroba se rechaza antes de dejar al gimnasio sin correo. */
    public function test_un_remitente_mal_escrito_se_rechaza(): void
    {
        $this->guardar(['correo.remitente' => 'progym punto cl'])->assertSessionHasErrors('correo.remitente');
    }

    /** Recepción no cambia la cuenta de correo del gimnasio. */
    public function test_recepcion_no_toca_el_correo(): void
    {
        $this->actingAs($this->recepcionista())
            ->put('/panel/configuracion', ['correo.smtp_host' => 'smtp.mio.cl'])
            ->assertForbidden();

        $this->actingAs($this->recepcionista())
            ->get('/panel/configuracion/correo')
            ->assertForbidden();
    }

    /** El correo de prueba dice a qué dirección salió, y si no salió, por qué. */
    public function test_el_correo_de_prueba_necesita_una_direccion(): void
    {
        $this->actingAs($this->administrador())
            ->post('/panel/configuracion/correo/probar', ['para' => 'no-es-un-correo'])
            ->assertSessionHasErrors('para');
    }
}
