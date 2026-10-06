<?php

namespace Database\Seeders;

use App\Models\Clase;
use App\Models\ContenidoWeb;
use App\Models\Convenio;
use App\Models\Ejercicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Las imágenes de la base de demostración: la galería, la portada, el
 * collage de Arrienda horas, las clases, los logos de convenios y las fotos
 * de algunas máquinas.
 *
 * SALEN DE LAS FOTOS QUE YA TIENE EL GIMNASIO en el disco (las de su web), y
 * se COPIAN con el prefijo «prueba-»: la demo y la web real comparten la
 * carpeta, y si en la demo se borrara una foto que apuntara al mismo archivo,
 * desaparecería también de la web de verdad. Con «prueba-» además no viajan
 * al llevar la configuración al servidor (ver LlevarLaConfiguracion).
 *
 * Si no hay fotos en el disco (otra instalación) no hace nada: la demo se ve
 * igual, solo que sin imágenes. No inventa caras: los socios y los
 * especialistas quedan con sus iniciales.
 */
class DemoFotosSeeder extends Seeder
{
    /** Las fotos de la web real, con lo que muestran (para usarlas donde corresponde). */
    private const FOTOS = [
        'web/RCj5aCNkmpMO5ci4HdqUF0cIaO8TEi1m.webp' => 'Sala de máquinas',
        'web/kolg8IEj2TYwnDjkyFGHfqfbWMCt4qGX.webp' => 'Máquina de pecho en la sala de musculación',
        'web/AnyNsbgY8nBhjrEFLPxDcH5TBXx8fD9C.webp' => 'Jalón al pecho en la zona de poleas',
        'web/6WsiT9atDybEkHutsCOywcptDn3u4LiM.webp' => 'Sentadilla en máquina, zona de piernas',
        'web/tZmsaxe4UQrMy2qKq7T3C7vKNpznhh9x.webp' => 'Zona de máquinas de fuerza',
        'web/qtC6XVPS3D4LbtXRr1ZG6NMD6zoaYIYh.webp' => 'La sala de PRO GYM',
        'web/hWuYkHp59YerLkSUBAOsVAePhTA7ZDvO.webp' => 'Moviendo un disco',
        'web/zXvt13kJ8k5VP2let2iqVsX9b3Irib89.webp' => 'Dominada con banda elástica',
        'web/VIO3SGFQUeOG5N1Ecg5fh4kq5ZwzyEYj.webp' => 'Magnesio antes de levantar',
        'web/yThzgQmekLNSqz8dDE10pN9Tzp01OhQd.webp' => 'Entrenando en la sala',
    ];

    /** Qué foto le va a qué máquina: solo donde la foto muestra justo eso. */
    private const MAQUINAS = [
        'web/kolg8IEj2TYwnDjkyFGHfqfbWMCt4qGX.webp' => ['Press de pecho en máquina', 'Aperturas en máquina', 'Press inclinado en máquina'],
        'web/AnyNsbgY8nBhjrEFLPxDcH5TBXx8fD9C.webp' => ['Jalón al pecho', 'Jalón con agarre cerrado'],
        'web/6WsiT9atDybEkHutsCOywcptDn3u4LiM.webp' => ['Sentadilla en máquina Smith', 'Sentadilla hack'],
        'web/zXvt13kJ8k5VP2let2iqVsX9b3Irib89.webp' => ['Dominadas asistidas', 'Dominadas'],
    ];

    /** Los convenios reales del gimnasio con su logo. */
    private const LOGOS = [
        'IP Virginio Gómez' => 'convenios/wVuM9r6KHpd4QyfSA6DV9VSJfJfORoJXl99I03IV.png',
        'AIEP' => 'convenios/1gyZ6UMauCPDLKygepZGwYh64aXt73FEc1LziHVA.png',
        'Santo Tomás' => 'convenios/Pmdrq5l8oqOIw0P93OBjuCGad8R55daIeXOfUr7k.png',
        'UCSC' => 'convenios/dmPrEdci10vJ77fIm1K5CgqqUrztiY02hCDxEMu6.png',
    ];

    private const CLASES = [
        'judo' => 'web/clases/prueba-judo.webp',
        'lucha' => 'web/clases/prueba-lucha.webp',
        'boxeo' => 'web/clases/prueba-boxeo.webp',
        'kick' => 'web/clases/prueba-kickboxing.webp',
    ];

    public function run(): void
    {
        // Solo en la base de demostración: corrido sobre la de verdad,
        // cambiaría las fotos de las clases, los logos y las máquinas.
        if (! \App\Models\User::where('email', 'admin@demo.test')->exists()) {
            $this->command?->warn('Esta no es la base de demostración: no se tocan las fotos.');

            return;
        }

        $disco = Storage::disk('public');
        $hay = array_filter(array_keys(self::FOTOS), fn ($r) => $disco->exists($r));

        if ($hay === []) {
            $this->command?->warn('Sin fotos del gimnasio en el disco: la demo queda sin imágenes.');

            return;
        }

        $copiar = function (string $origen, string $carpeta) use ($disco): ?string {
            if (! $disco->exists($origen)) {
                return null;
            }

            $destino = $carpeta . '/prueba-demo-' . Str::random(10) . '.' . pathinfo($origen, PATHINFO_EXTENSION);
            $disco->copy($origen, $destino);

            return $destino;
        };

        // La galería de El gimnasio (y de ahí, el fondo que gira en la portada).
        $orden = (int) ContenidoWeb::where('tipo', 'foto')->max('orden');
        foreach (self::FOTOS as $origen => $titulo) {
            if ($ruta = $copiar($origen, 'web')) {
                ContenidoWeb::create(['tipo' => 'foto', 'titulo' => $titulo, 'imagen' => $ruta, 'activo' => true, 'orden' => ++$orden]);
            }
        }

        // El collage de Arrienda horas: grupos y el espacio.
        foreach (array_slice(array_keys(self::FOTOS), 0, 5) as $i => $origen) {
            if ($ruta = $copiar($origen, 'web')) {
                ContenidoWeb::create(['tipo' => 'arriendo', 'titulo' => self::FOTOS[$origen], 'imagen' => $ruta, 'activo' => true, 'orden' => $i + 1]);
            }
        }

        // Los convenios reales, con su logo, y los mismos como instituciones
        // que arriendan horas.
        foreach (self::LOGOS as $nombre => $origen) {
            $logo = $copiar($origen, 'convenios');
            if (! $logo) {
                continue;
            }

            $convenio = Convenio::firstOrNew(['nombre' => $nombre]);
            $convenio->fill([
                'tipo' => $convenio->tipo ?? 'institucion_educativa',
                'activo' => true,
                'logo' => $logo,
                // Como en la web real: con logo y a quién va dirigido.
                'mostrar_en_web' => true,
                'requisito_web' => 'Estudiantes con credencial vigente',
            ]);
            $convenio->save();

            if ($logoInstitucion = $copiar($origen, 'web/instituciones')) {
                ContenidoWeb::create([
                    'tipo' => 'institucion',
                    'titulo' => $nombre,
                    'imagen' => $logoInstitucion,
                    'activo' => true,
                    'orden' => (int) ContenidoWeb::where('tipo', 'institucion')->max('orden') + 1,
                ]);
            }
        }

        // Las clases: su foto y una galería con las fotos de la sala.
        $sala = array_values($hay);
        foreach (Clase::all() as $n => $clase) {
            $clave = collect(array_keys(self::CLASES))->first(fn ($k) => str_contains(Str::lower(Str::ascii($clase->nombre)), $k));
            $principal = $clave ? $copiar(self::CLASES[$clave], 'web/clases') : null;
            $galeria = [];
            for ($i = 0; $i < 4; $i++) {
                if ($ruta = $copiar($sala[($n * 3 + $i) % count($sala)], 'web/clases')) {
                    $galeria[] = $ruta;
                }
            }
            $clase->update(['imagen' => $principal ?? ($galeria[0] ?? null), 'fotos' => $galeria ?: null]);
        }

        // Las máquinas que salen en las fotos.
        foreach (self::MAQUINAS as $origen => $nombres) {
            foreach (Ejercicio::whereIn('nombre', $nombres)->get() as $ejercicio) {
                if ($ruta = $copiar($origen, 'ejercicios')) {
                    $ejercicio->update(['imagen' => $ruta]);
                }
            }
        }
    }
}
