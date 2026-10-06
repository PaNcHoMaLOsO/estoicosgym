<?php

namespace Tests\Feature\Regresiones;

use App\Enums\EstadosCodigo;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\TipoNotificacion;
use App\Services\CorreoService;
use App\Services\EnvioManualService;
use App\Services\NotificacionService;
use App\Support\Ajustes;
use App\Support\PlantillasDeFabrica;
use Database\Seeders\PlantillasProgymSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\CasoConCatalogos;

/**
 * Los avisos automáticos salen de la plantilla guardada en la base.
 *
 * EL BUG. Cada aviso leía un HTML de storage/app/test_emails —que no va en el
 * repositorio: en el servidor no existía y todos fallaban con «Plantilla no
 * encontrada»— y le cambiaba el texto de muestra a mano. Lo que se corregía en
 * Configuración → Plantillas de correo no llegaba nunca al socio, y la
 * plantilla guardada, mandada a mano, decía «Trimestral» y «$65.000» a
 * cualquiera.
 */
class AvisosDesdeLaPlantillaGuardadaTest extends CasoConCatalogos
{
    /** Lo que no puede quedar en un correo de verdad. */
    private const MUESTRAS = [
        'Juan Pérez', 'Juanito Pérez', 'María González', 'Trimestral', '$$', '06/03/2026', '06/12/2025',
        '25.555.666-7', '11.222.333-4', 'Viaje por trabajo', 'progymlosangeles', '5096 3143', 'Transferencia',
    ];

    /** Lo que se mandó de verdad, por si hace falta mirarlo. */
    private array $mandados = [];

    protected function setUp(): void
    {
        parent::setUp();

        // SIN la carpeta vieja: si algo todavía leyera de ahí, fallaría aquí
        // igual que fallaba en el servidor.
        $this->app->useStoragePath(sys_get_temp_dir() . '/sin-storage-' . uniqid());
        $this->assertFalse(is_dir(storage_path('app/test_emails')));

        $this->seed(PlantillasProgymSeeder::class);

        Ajustes::guardar([
            'gimnasio.nombre' => 'PRO GYM',
            'gimnasio.telefono' => '+56 43 212 3456',
            'gimnasio.email' => 'hola@progym.cl',
            'web.instagram' => 'https://www.instagram.com/progym.prueba',
            'horario.lunes' => '07:00-22:00',
            'horario.martes' => '07:00-22:00',
        ]);
    }

    private function servicio(bool $debeMandar = true): NotificacionService
    {
        $correo = Mockery::mock(CorreoService::class);

        if ($debeMandar) {
            $correo->shouldReceive('enviar')->andReturnUsing(function ($para, $asunto, $html) {
                $this->mandados[] = compact('para', 'asunto', 'html');

                return 'id-falso';
            });
        } else {
            $correo->shouldNotReceive('enviar');
        }

        $correo->shouldReceive('cerrar');

        return new NotificacionService($correo);
    }

    private function inscripcion(array $socio = [], array $extra = []): Inscripcion
    {
        $cliente = Cliente::factory()->create($socio + [
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Muñoz',
            'apellido_materno' => 'Soto',
            'email' => 'rosa' . uniqid() . '@correo.cl',
            'activo' => true,
        ]);

        $inscripcion = Inscripcion::factory()->create($extra + [
            'id_cliente' => $cliente->id,
            'id_membresia' => 4, // Mensual
            'id_estado' => EstadosCodigo::INSCRIPCION_ACTIVA,
            'precio_base' => 40000,
            'descuento_aplicado' => 0,
            'precio_final' => 40000,
            'fecha_inicio' => today()->subDays(25),
            'fecha_vencimiento' => today()->addDays(5),
        ]);

        Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'id_cliente' => $cliente->id,
            'monto_total' => 40000,
            'monto_abonado' => 15000,
            'monto_pendiente' => 25000,
            'id_estado' => EstadosCodigo::PAGO_PARCIAL,
            'tipo_pago' => 'parcial',
            'id_metodo_pago' => 1, // Efectivo
            'fecha_pago' => today()->format('Y-m-d'),
        ]);

        return $inscripcion->fresh(['cliente', 'membresia']);
    }

    private function tipo(string $codigo): TipoNotificacion
    {
        return TipoNotificacion::where('codigo', $codigo)->firstOrFail();
    }

    private function assertCorreoLimpio(string $asunto, string $html, string $cual): void
    {
        preg_match_all('/\{([a-z_]+)\}/i', $asunto . ' ' . $html, $sueltas);
        $this->assertSame([], $sueltas[1], "{$cual}: quedaron variables sin rellenar.");

        foreach (self::MUESTRAS as $muestra) {
            $this->assertStringNotContainsString($muestra, $asunto . $html, "{$cual}: lleva el texto de muestra «{$muestra}».");
        }
    }

    public function test_cada_aviso_automatico_sale_de_su_plantilla_con_los_datos_de_verdad(): void
    {
        $servicio = $this->servicio();

        $codigos = [
            TipoNotificacion::MEMBRESIA_POR_VENCER,
            TipoNotificacion::MEMBRESIA_VENCIDA,
            TipoNotificacion::PAGO_COMPLETADO,
            TipoNotificacion::RENOVACION,
            TipoNotificacion::ACTIVACION_INSCRIPCION,
            TipoNotificacion::PAGO_PENDIENTE,
        ];

        foreach ($codigos as $codigo) {
            $aviso = $servicio->crearNotificacion($this->tipo($codigo), $this->inscripcion());

            $this->assertSame(Notificacion::ESTADO_PENDIENTE, (int) $aviso->id_estado, "{$codigo}: no quedó lista para salir.");
            $this->assertCorreoLimpio($aviso->asunto, $aviso->contenido, $codigo);
            $this->assertStringContainsString('Rosa Muñoz', $aviso->asunto . $aviso->contenido, $codigo);
            $this->assertStringContainsString('+56 43 212 3456', $aviso->contenido, "{$codigo}: el teléfono no sale de Configuración.");
            $this->assertStringContainsString('hola@progym.cl', $aviso->contenido, $codigo);
            $this->assertStringContainsString(today()->addDays(5)->format('d/m/Y'), $aviso->asunto . $aviso->contenido, $codigo);
        }

        // Los datos propios de cada uno.
        $porVencer = Notificacion::whereHas('tipoNotificacion', fn ($q) => $q->where('codigo', TipoNotificacion::MEMBRESIA_POR_VENCER))->first();
        $this->assertStringContainsString('5 días', $porVencer->contenido);
        $this->assertStringContainsString('Mensual', $porVencer->asunto);

        $pendiente = Notificacion::whereHas('tipoNotificacion', fn ($q) => $q->where('codigo', TipoNotificacion::PAGO_PENDIENTE))->first();
        $this->assertStringContainsString('$25.000', $pendiente->contenido);
        $this->assertStringContainsString('$40.000', $pendiente->contenido);

        $pagado = Notificacion::whereHas('tipoNotificacion', fn ($q) => $q->where('codigo', TipoNotificacion::PAGO_COMPLETADO))->first();
        $this->assertStringContainsString('$15.000', $pagado->contenido);
        $this->assertStringContainsString('Efectivo', $pagado->contenido);
    }

    public function test_el_aviso_de_pausa_lleva_el_motivo_y_la_vuelta(): void
    {
        $inscripcion = $this->inscripcion([], [
            'id_estado' => EstadosCodigo::INSCRIPCION_PAUSADA,
            'pausada' => true,
            'fecha_pausa_inicio' => today(),
            'fecha_pausa_fin' => today()->addDays(15),
            'razon_pausa' => 'Operación de rodilla',
        ]);

        $aviso = $this->servicio()->crearNotificacion($this->tipo(TipoNotificacion::PAUSA_INSCRIPCION), $inscripcion);

        $this->assertCorreoLimpio($aviso->asunto, $aviso->contenido, 'pausa');
        $this->assertStringContainsString('Operación de rodilla', $aviso->contenido);
        $this->assertStringContainsString(today()->addDays(15)->format('d/m/Y'), $aviso->contenido);
    }

    public function test_la_bienvenida_y_la_constancia_al_tutor_salen_con_datos_reales(): void
    {
        $inscripcion = $this->inscripcion([
            'nombres' => 'Tomás',
            'apellido_paterno' => 'Rojas',
            'email' => 'tomas@correo.cl',
            'es_menor_edad' => true,
            'fecha_nacimiento' => '2012-04-03',
            'apoderado_nombre' => 'Carla Rojas',
            'apoderado_rut' => '9.876.543-3',
            'apoderado_email' => 'carla@correo.cl',
        ]);

        $servicio = $this->servicio();

        $this->assertTrue($servicio->enviarNotificacionBienvenida($inscripcion)['enviada']);
        $this->assertTrue($servicio->enviarNotificacionTutorLegal($inscripcion)['enviada']);

        $this->assertCount(2, $this->mandados);
        [$bienvenida, $tutor] = $this->mandados;

        // Un menor con apoderado recibe por el apoderado, como todo aviso
        // (Cliente::correoParaAvisos): antes la bienvenida era la excepción.
        $this->assertSame('carla@correo.cl', $bienvenida['para']);
        $this->assertCorreoLimpio($bienvenida['asunto'], $bienvenida['html'], 'bienvenida');
        $this->assertStringContainsString('Tomás Rojas', $bienvenida['html']);
        $this->assertStringContainsString('Parcial', $bienvenida['html']);
        // El horario sale de Configuración, en una línea.
        $this->assertStringContainsString('Lunes a martes: 07:00-22:00', $bienvenida['html']);

        $this->assertSame('carla@correo.cl', $tutor['para']);
        $this->assertCorreoLimpio($tutor['asunto'], $tutor['html'], 'tutor legal');
        $this->assertStringContainsString('Carla Rojas', $tutor['asunto']);
        $this->assertStringContainsString('9.876.543-3', $tutor['html']);
        $this->assertStringContainsString('03/04/2012', $tutor['html']);
        $this->assertStringContainsString('Tomás Rojas', $tutor['html']);
    }

    /** Lo que se corrige en Configuración es lo que le llega al socio. */
    public function test_editar_la_plantilla_cambia_lo_que_se_manda(): void
    {
        $this->tipo(TipoNotificacion::MEMBRESIA_VENCIDA)->update([
            'asunto_email' => 'Se te acabó, {nombres}',
            'plantilla_email' => '<p>Hola {nombre}, tu plan {membresia} terminó el {fecha_vencimiento}. Escríbenos a {email_gimnasio}.</p>',
        ]);

        $aviso = $this->servicio()->crearNotificacion($this->tipo(TipoNotificacion::MEMBRESIA_VENCIDA), $this->inscripcion());

        $this->assertSame('Se te acabó, Rosa', $aviso->asunto);
        $this->assertSame(
            '<p>Hola Rosa Muñoz Soto, tu plan Mensual terminó el ' . today()->addDays(5)->format('d/m/Y') . '. Escríbenos a hola@progym.cl.</p>',
            $aviso->contenido
        );
    }

    /**
     * Una plantilla con una variable que no existe NO sale: queda fallida, con
     * el motivo, sin reintentos, y tampoco se puede reenviar a mano.
     */
    public function test_una_plantilla_rota_no_manda_el_correo_roto(): void
    {
        DB::table('tipo_notificaciones')->where('codigo', 'bienvenida')
            ->update(['plantilla_email' => '<p>Hola {nombre}, te debemos {lo_que_no_existe}</p>']);

        $resultado = $this->servicio(debeMandar: false)->enviarNotificacionBienvenida($this->inscripcion());

        $this->assertFalse($resultado['enviada']);

        $aviso = Notificacion::findOrFail($resultado['notificacion_id']);
        $this->assertSame(Notificacion::ESTADO_FALLIDO, (int) $aviso->id_estado);
        $this->assertStringContainsString('{lo_que_no_existe}', $aviso->error_mensaje);
        $this->assertFalse($aviso->puedeReintentar(), 'Se reintentaría el mismo correo roto.');

        $this->expectException(ValidationException::class);
        app(EnvioManualService::class)->reenviar($aviso);
    }

    /** El código ya no lee nada de la carpeta de pruebas vieja. */
    public function test_nada_lee_la_carpeta_de_muestras(): void
    {
        foreach ([
            app_path('Services/NotificacionService.php'),
            database_path('seeders/PlantillasProgymSeeder.php'),
            app_path('Support/PlantillasDeFabrica.php'),
        ] as $archivo) {
            $this->assertDoesNotMatchRegularExpression('/storage_path\([^)]*test_emails/', file_get_contents($archivo), $archivo);
        }

        foreach (array_keys(PlantillasDeFabrica::PLANTILLAS) as $codigo) {
            $this->assertCorreoLimpio('', strtr(PlantillasDeFabrica::contenido($codigo), ['{' => '', '}' => '']), $codigo);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // La actualización de las plantillas que ya están en la base
    // ─────────────────────────────────────────────────────────────────────

    /** La versión vieja tal como quedó en la base: con los colores ya cambiados y CRLF. */
    private function viejaGuardada(): string
    {
        $vieja = file_get_contents(base_path('tests/Fixtures/plantillas-viejas/03_membresia_por_vencer.html'));
        $vieja = str_replace(['#E0001A', '#101010'], ['#d81f26', '#0a0a0b'], $vieja);

        return str_replace("\n", "\r\n", str_replace("\r\n", "\n", $vieja));
    }

    public function test_actualiza_la_que_nadie_toco_y_deja_la_editada(): void
    {
        $vieja = $this->viejaGuardada();
        $asuntoViejo = PlantillasDeFabrica::PLANTILLAS['membresia_por_vencer']['asunto_viejo'];

        DB::table('tipo_notificaciones')->where('codigo', 'membresia_por_vencer')
            ->update(['plantilla_email' => $vieja, 'asunto_email' => $asuntoViejo]);

        // Editada: alguien le cambió una frase, y sigue con «Juan Pérez».
        DB::table('tipo_notificaciones')->where('codigo', 'membresia_vencida')
            ->update(['plantilla_email' => '<p>Hola Juan Pérez, ¡te esperamos de vuelta!</p>', 'asunto_email' => 'Mi asunto']);

        // Sin --confirmar no se toca nada.
        $this->artisan('plantillas:actualizar')
            ->expectsOutputToContain('No se cambió nada')
            ->assertSuccessful();
        $this->assertSame($vieja, $this->tipo('membresia_por_vencer')->plantilla_email);

        $this->artisan('plantillas:actualizar --confirmar')
            ->expectsOutputToContain('«Membresía Vencida» está editada y aún lleva texto de muestra (Juan Pérez)')
            ->assertSuccessful();

        $porVencer = $this->tipo('membresia_por_vencer');
        $this->assertSame(PlantillasDeFabrica::contenido('membresia_por_vencer'), $porVencer->plantilla_email);
        $this->assertSame(PlantillasDeFabrica::PLANTILLAS['membresia_por_vencer']['asunto'], $porVencer->asunto_email);

        $vencida = $this->tipo('membresia_vencida');
        $this->assertSame('<p>Hola Juan Pérez, ¡te esperamos de vuelta!</p>', $vencida->plantilla_email);
        $this->assertSame('Mi asunto', $vencida->asunto_email);

        // Repetirlo no cambia nada.
        $antes = DB::table('tipo_notificaciones')->orderBy('id')->get(['id', 'asunto_email', 'plantilla_email', 'updated_at']);
        $informe = PlantillasDeFabrica::actualizar(true);
        $this->assertEquals($antes, DB::table('tipo_notificaciones')->orderBy('id')->get(['id', 'asunto_email', 'plantilla_email', 'updated_at']));
        $this->assertSame('al_dia', collect($informe)->firstWhere('codigo', 'membresia_por_vencer')['resultado']);
        $this->assertSame('editada', collect($informe)->firstWhere('codigo', 'membresia_vencida')['resultado']);
    }

    /** La huella guardada es la de la versión vieja de verdad. */
    public function test_la_huella_reconoce_la_version_vieja(): void
    {
        $this->assertSame(
            PlantillasDeFabrica::PLANTILLAS['membresia_por_vencer']['huella_vieja'],
            PlantillasDeFabrica::huella($this->viejaGuardada())
        );
        $this->assertSame(
            PlantillasDeFabrica::PLANTILLAS['membresia_por_vencer']['huella_vieja'],
            PlantillasDeFabrica::huella(file_get_contents(base_path('tests/Fixtures/plantillas-viejas/03_membresia_por_vencer.html')))
        );
    }

    public function test_crea_las_que_faltan(): void
    {
        TipoNotificacion::where('codigo', 'evento')->delete();

        $this->artisan('plantillas:actualizar --confirmar')->assertSuccessful();

        $this->assertSame(PlantillasDeFabrica::contenido('evento'), $this->tipo('evento')->plantilla_email);
    }
}
