<?php

namespace Tests\Feature\Regresiones;

use App\Models\Cliente;
use App\Models\ContenidoWeb;
use App\Models\Convenio;
use App\Models\ConvenioPrecio;
use App\Models\Ejercicio;
use App\Models\Especialista;
use App\Models\Membresia;
use App\Models\Rutina;
use App\Models\RutinaDia;
use App\Models\RutinaEjercicio;
use App\Models\TextoLegal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\CasoConCatalogos;

/**
 * Llevar la configuración del PC del dueño al servidor.
 *
 * Lo que se vigila: que en el zip no vaya ningún socio, usuario ni secreto;
 * que en una base vacía quede todo lo configurado, con las relaciones bien
 * aunque los ids sean otros; que sin --confirmar no se escriba nada; que
 * importar dos veces no duplique; y que un servidor que ya sembró los mismos
 * planes con otro uuid no termine con dos «Mensual».
 */
class LlevarLaConfiguracionTest extends CasoConCatalogos
{
    private string $zip;

    /** Las tablas de configuración, de hijos a padres, para vaciarlas. */
    private const TABLAS = [
        'rutina_ejercicios', 'rutina_dias', 'rutinas', 'ejercicios',
        'convenio_precios', 'precios_membresias', 'convenios', 'membresias',
        'contenidos_web', 'especialistas', 'tipo_notificaciones', 'ajustes', 'textos_legales',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
        $this->zip = Storage::disk('local')->path('configuracion/prueba.zip');
    }

    /** Lo que configuró el dueño, más un socio y un secreto que no deben salir. */
    private function configurar(): void
    {
        $this->administrador()->update(['email' => 'recepcion@progym.cl', 'name' => 'Usuaria Del Panel']);

        Cliente::factory()->create([
            'nombres' => 'Socialina',
            'apellido_paterno' => 'Deprueba',
            'email' => 'socialina@correo.cl',
        ]);

        DB::table('ajustes')->insert([
            ['clave' => 'gimnasio.nombre', 'valor' => 'PRO GYM Los Ángeles', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'correo.smtp_usuario', 'valor' => 'cuenta.del.gym@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'correo.smtp_clave', 'valor' => Crypt::encryptString('clave-de-aplicacion-secreta'), 'created_at' => now(), 'updated_at' => now()],
        ]);

        Storage::disk('public')->put('convenios/logo-ucsc.png', 'logo');
        $convenio = Convenio::create([
            'nombre' => 'UCSC',
            'tipo' => 'universidad',
            'logo' => 'convenios/logo-ucsc.png',
            'contacto_nombre' => 'Contacta Personal',
            'contacto_email' => 'contacta@ucsc.cl',
            'activo' => true,
        ]);
        ConvenioPrecio::create(['id_convenio' => $convenio->id, 'id_membresia' => 4, 'precio' => 22000]);

        DB::table('tipo_notificaciones')->orderBy('id')->limit(1)->update(['asunto_email' => 'Asunto escrito por el dueño']);

        Storage::disk('public')->put('web/sala.webp', 'foto');
        ContenidoWeb::create(['tipo' => 'foto', 'titulo' => 'La sala de máquinas', 'imagen' => 'web/sala.webp', 'activo' => true]);
        Storage::disk('public')->put('web/prueba-abc.webp', 'foto de prueba');
        ContenidoWeb::create(['tipo' => 'arriendo', 'titulo' => 'PRUEBA · Una foto de muestra', 'imagen' => 'web/prueba-abc.webp', 'activo' => true]);

        Especialista::create(['tipo' => 'especialista', 'nombre' => 'Valeria Nutri', 'slug' => 'valeria-nutri', 'especialidad' => 'Nutricionista', 'activo' => true]);

        $sentadilla = Ejercicio::create(['nombre' => 'Sentadilla', 'zona' => 'piernas', 'equipo' => 'barra', 'activo' => true]);
        $prensa = Ejercicio::create(['nombre' => 'Prensa', 'zona' => 'piernas', 'equipo' => 'maquina', 'activo' => true]);
        $rutina = Rutina::create(['nombre' => 'Piernas de fuerza', 'objetivo' => 'fuerza', 'nivel' => 'intermedio', 'dias_por_semana' => 1, 'activa' => true]);
        $dia = RutinaDia::create(['id_rutina' => $rutina->id, 'numero' => 1, 'titulo' => 'Pierna']);
        RutinaEjercicio::create(['id_dia' => $dia->id, 'id_ejercicio' => $sentadilla->id, 'id_alternativa' => $prensa->id, 'series' => 4, 'repeticiones' => '8', 'orden' => 1]);

        TextoLegal::create(['tipo' => 'terminos', 'version' => 1, 'contenido' => 'Las reglas del gimnasio.']);
    }

    private function exportar(): void
    {
        $this->artisan('configuracion:exportar', ['--archivo' => $this->zip])->assertSuccessful();
    }

    /** Todo lo que hay dentro del zip, como un solo texto. */
    private function contenidoDelZip(): string
    {
        $zip = new \ZipArchive;
        $zip->open($this->zip);
        $todo = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $todo .= $zip->getNameIndex($i) . "\n" . $zip->getFromIndex($i) . "\n";
        }

        $zip->close();

        return $todo;
    }

    /** Deja el «servidor» sin configuración ni fotos, pero con sus socios. */
    private function vaciar(): void
    {
        foreach (self::TABLAS as $tabla) {
            DB::table($tabla)->delete();
        }

        foreach (Storage::disk('public')->allFiles() as $archivo) {
            Storage::disk('public')->delete($archivo);
        }
    }

    public function test_el_zip_no_lleva_socios_usuarios_ni_secretos(): void
    {
        $this->configurar();
        $this->exportar();

        $zip = $this->contenidoDelZip();

        foreach (['Socialina', 'socialina@correo.cl', 'recepcion@progym.cl', 'Usuaria Del Panel',
            'cuenta.del.gym@gmail.com', 'correo.smtp_clave', 'correo.smtp_usuario',
            'Contacta Personal', 'contacta@ucsc.cl', 'PRUEBA ·', 'prueba-abc'] as $noDebe) {
            $this->assertStringNotContainsString($noDebe, $zip, "«{$noDebe}» no debería ir en el zip");
        }

        // Y lo que sí: la configuración con su foto.
        $this->assertStringContainsString('PRO GYM Los Ángeles', $zip);
        $this->assertStringContainsString('imagenes/web/sala.webp', $zip);
        $this->assertStringContainsString('imagenes/convenios/logo-ucsc.png', $zip);
    }

    public function test_en_una_base_vacia_queda_todo_y_dos_veces_no_duplica(): void
    {
        $this->configurar();
        $mensual = Membresia::find(4);
        $precioMensual = (float) DB::table('precios_membresias')->where('id_membresia', 4)->where('activo', true)->value('precio_normal');
        $planes = Membresia::count();
        $this->exportar();

        $this->vaciar();

        // Sin --confirmar: cuenta, pero no escribe ni copia nada.
        $this->artisan('configuracion:importar', ['archivo' => $this->zip])->assertSuccessful();
        $this->assertSame(0, Membresia::count());
        $this->assertSame(0, DB::table('ajustes')->count());
        $this->assertFalse(Storage::disk('public')->exists('web/sala.webp'));

        $this->artisan('configuracion:importar', ['archivo' => $this->zip, '--confirmar' => true])->assertSuccessful();

        // Los planes, con su precio vigente.
        $this->assertSame($planes, Membresia::count());
        $nuevoMensual = Membresia::where('uuid', $mensual->uuid)->firstOrFail();
        $this->assertSame($precioMensual, (float) DB::table('precios_membresias')->where('id_membresia', $nuevoMensual->id)->where('activo', true)->value('precio_normal'));

        // El convenio con su logo y su precio propio, apuntando al plan
        // correcto aunque el id del plan haya cambiado.
        $convenio = Convenio::where('nombre', 'UCSC')->firstOrFail();
        $this->assertSame('convenios/logo-ucsc.png', $convenio->logo);
        $this->assertNull($convenio->contacto_nombre);
        $this->assertTrue(Storage::disk('public')->exists('convenios/logo-ucsc.png'));
        $this->assertSame($nuevoMensual->id, ConvenioPrecio::where('id_convenio', $convenio->id)->value('id_membresia'));

        // Plantillas, web, especialistas, rutinas, textos y ajustes.
        $this->assertTrue(DB::table('tipo_notificaciones')->where('asunto_email', 'Asunto escrito por el dueño')->exists());
        $this->assertSame('web/sala.webp', ContenidoWeb::where('titulo', 'La sala de máquinas')->value('imagen'));
        $this->assertTrue(Storage::disk('public')->exists('web/sala.webp'));
        $this->assertFalse(ContenidoWeb::where('titulo', 'like', 'PRUEBA%')->exists());
        $this->assertFalse(Storage::disk('public')->exists('web/prueba-abc.webp'));
        $this->assertTrue(Especialista::where('slug', 'valeria-nutri')->exists());

        $paso = RutinaEjercicio::firstOrFail();
        $this->assertSame('Sentadilla', Ejercicio::find($paso->id_ejercicio)->nombre);
        $this->assertSame('Prensa', Ejercicio::find($paso->id_alternativa)->nombre);
        $this->assertSame('Piernas de fuerza', RutinaDia::find($paso->id_dia)->rutina->nombre);

        $this->assertSame('Las reglas del gimnasio.', TextoLegal::where('tipo', 'terminos')->value('contenido'));
        $this->assertSame('PRO GYM Los Ángeles', DB::table('ajustes')->where('clave', 'gimnasio.nombre')->value('valor'));
        $this->assertFalse(DB::table('ajustes')->where('clave', 'correo.smtp_clave')->exists());

        // El socio del servidor sigue ahí: importar no toca a nadie.
        $this->assertTrue(Cliente::where('email', 'socialina@correo.cl')->exists());

        // Otra vez: nada nuevo.
        $antes = collect(self::TABLAS)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();
        $this->artisan('configuracion:importar', ['archivo' => $this->zip, '--confirmar' => true])->assertSuccessful();
        $despues = collect(self::TABLAS)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();
        $this->assertSame($antes, $despues);
    }

    public function test_el_servidor_con_los_planes_sembrados_con_otro_uuid_no_los_duplica(): void
    {
        $this->exportar();
        $planes = Membresia::count();
        $precio = (float) DB::table('precios_membresias')->where('id_membresia', 4)->where('activo', true)->value('precio_normal');

        // Así está el servidor: los mismos planes, sembrados por su cuenta
        // (otro uuid), y el mensual con otro precio.
        Membresia::query()->each(fn ($m) => DB::table('membresias')->where('id', $m->id)->update(['uuid' => (string) Str::uuid()]));
        DB::table('precios_membresias')->where('id_membresia', 4)->where('activo', true)->update(['precio_normal' => 1]);

        $this->artisan('configuracion:importar', ['archivo' => $this->zip, '--confirmar' => true])->assertSuccessful();

        $this->assertSame($planes, Membresia::count());

        // El precio no se pisa: se cierra el que había y se abre el del PC.
        $this->assertSame($precio, (float) DB::table('precios_membresias')->where('id_membresia', 4)->where('activo', true)->value('precio_normal'));
        $this->assertTrue(DB::table('precios_membresias')->where('id_membresia', 4)->where('activo', false)->where('precio_normal', 1)->exists());
    }
}
