<?php

namespace App\Support;

use App\Models\ContenidoWeb;
use App\Models\Convenio;
use App\Models\Membresia;
use App\Models\MetodoPago;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Cache;

/**
 * Lo que falta configurar, en una lista.
 *
 * Reemplaza las tarjetas de «Catálogos», que decían cuántos había de cada cosa
 * pero no lo que importaba: que un plan no tiene precio, que los correos no
 * salen, que las tareas automáticas nunca corrieron. Cada punto dice qué pasa
 * y lleva a donde se arregla.
 *
 * «falta» es lo que impide trabajar o deja mal parada la web; «mejora», lo que
 * ayuda y se puede dejar para después; «ok», lo que ya está.
 */
class EstadoDeConfiguracion
{
    private const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    /**
     * @return list<array{clave:string, grupo:string, titulo:string, href:string, estado:string, detalle:string}>
     */
    public static function puntos(): array
    {
        return [
            self::datosDelGimnasio(),
            self::horario(),
            self::planes(),
            self::metodosDePago(),
            self::convenios(),
            self::correo(),
            self::tareas(),
            self::enInternet(),
            self::fichaDeGoogle(),
            self::fotos(),
            self::analitica(),
            self::textosLegales(),
        ];
    }

    /**
     * Lo pendiente de cada sección, para marcarla en el menú de Configuración.
     *
     * Solo lo que «falta»: si también se marcara lo que «mejora», medio menú
     * saldría con el triángulo y dejaría de llamar la atención.
     *
     * @return array<string,string> dirección => qué falta
     */
    public const CACHE_AVISOS = 'configuracion:avisos-del-menu';

    /**
     * Los triángulos del menú de Configuración.
     *
     * Se guardan cinco minutos: calcularlos son veintisiete consultas, y se
     * pedían en CADA pantalla del panel. Guardar un ajuste los recalcula al
     * tiro; lo demás —un convenio sin logo, un plan sin precio— puede tardar
     * cinco minutos en verse en el menú, y la portada de Configuración sigue
     * calculándose al momento.
     */
    public static function avisosDelMenu(): array
    {
        return Cache::memo()->remember(self::CACHE_AVISOS, now()->addMinutes(5), function () {
            $avisos = [];

            foreach (self::puntos() as $punto) {
                if ($punto['estado'] === 'falta') {
                    $avisos[$punto['href']] ??= $punto['detalle'];
                }
            }

            return $avisos;
        });
    }

    /**
     * ¿Tiene el correo de salida con qué conectarse?
     *
     * No dice si la clave es buena —eso solo se sabe cuando falla un envío—,
     * y NUNCA devuelve los datos: solo si están.
     */
    public static function correoConfigurado(): bool
    {
        $mailer = config('mail.default');

        if (in_array($mailer, [null, 'log', 'array'], true)) {
            return false;
        }

        if ($mailer === 'smtp') {
            $smtp = config('mail.mailers.smtp', []);

            return ! empty($smtp['host']) && ! empty($smtp['username']) && ! empty($smtp['password']);
        }

        return true;
    }

    private static function datosDelGimnasio(): array
    {
        $faltan = array_keys(array_filter([
            'la dirección' => trim((string) Ajustes::obtener('gimnasio.direccion')) === '',
            'el teléfono' => trim((string) Ajustes::obtener('gimnasio.telefono')) === '',
            'el correo' => trim((string) Ajustes::obtener('gimnasio.email')) === '',
        ]));

        return self::punto(
            'gimnasio', 'El gimnasio', 'Datos del gimnasio', '/panel/configuracion/gimnasio',
            $faltan ? 'falta' : 'ok',
            $faltan
                ? 'Falta ' . self::enumerar($faltan) . '. Salen en los correos, en la web y en Google.'
                : 'Nombre, dirección, teléfono y correo cargados.'
        );
    }

    private static function horario(): array
    {
        $abiertos = collect(self::DIAS)
            ->filter(fn (string $dia) => trim((string) Ajustes::obtener("horario.{$dia}")) !== '')
            ->count();

        return self::punto(
            'horario', 'El gimnasio', 'Horario', '/panel/configuracion/horario',
            $abiertos ? 'ok' : 'falta',
            $abiertos
                ? ($abiertos === 1 ? 'Abre un día a la semana.' : "Abre {$abiertos} días a la semana.")
                : 'Sin horario: la web dice «Pregunta en el mesón» y Google no puede mostrarlo.'
        );
    }

    private static function planes(): array
    {
        $activos = Membresia::where('activo', true)->count();
        $sinPrecio = Membresia::where('activo', true)
            ->whereDoesntHave('precios', fn ($q) => $q
                ->where('activo', true)
                ->where('fecha_vigencia_desde', '<=', now()))
            ->count();

        [$estado, $detalle] = match (true) {
            $activos === 0 => ['falta', 'No hay ningún plan activo: no se puede inscribir a nadie.'],
            $sinPrecio === 1 => ['falta', 'Un plan activo no tiene precio: no se puede vender.'],
            $sinPrecio > 1 => ['falta', "{$sinPrecio} planes activos no tienen precio: no se pueden vender."],
            $activos === 1 => ['ok', 'Un plan a la venta, con su precio.'],
            default => ['ok', "{$activos} planes a la venta, todos con precio."],
        };

        return self::punto('planes', 'Ventas', 'Planes y precios', '/panel/membresias', $estado, $detalle);
    }

    private static function metodosDePago(): array
    {
        $activos = MetodoPago::where('activo', true)->count();

        return self::punto(
            'metodos', 'Ventas', 'Métodos de pago', '/panel/metodos-pago',
            $activos ? 'ok' : 'falta',
            match (true) {
                $activos === 0 => 'Sin ningún método de pago activo no se puede cobrar.',
                $activos === 1 => 'Una forma de pago activa.',
                default => "{$activos} formas de pago activas.",
            }
        );
    }

    private static function convenios(): array
    {
        $enLaWeb = Convenio::where('activo', true)->where('mostrar_en_web', true);
        $publicados = (clone $enLaWeb)->count();
        $sinLogo = (clone $enLaWeb)->whereNull('logo')->count();

        [$estado, $detalle] = match (true) {
            $publicados === 0 => ['mejora', 'Ningún convenio sale en la web: marca «Mostrarlo en la web» en los que quieras publicar.'],
            $sinLogo === 1 => ['falta', 'Un convenio de la web no tiene logo: sale solo con el nombre.'],
            $sinLogo > 1 => ['falta', "{$sinLogo} convenios de la web no tienen logo: salen solo con el nombre."],
            $publicados === 1 => ['ok', 'Un convenio en la web, con su logo.'],
            default => ['ok', "{$publicados} convenios en la web, todos con su logo."],
        };

        return self::punto('convenios', 'Ventas', 'Convenios', '/panel/convenios', $estado, $detalle);
    }

    private static function correo(): array
    {
        if (! self::correoConfigurado()) {
            return self::punto(
                // A la pantalla donde se arregla, no a la de al lado.
                'correo', 'Correos', 'Cuenta de correo', '/panel/configuracion/correo', 'falta',
                'El correo del gimnasio no está configurado: no le llega ningún aviso a los socios.'
            );
        }

        $fallidos = Notificacion::where('id_estado', Notificacion::ESTADO_FALLIDO)
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return self::punto(
            'correo', 'Correos', 'Cuenta de correo', '/panel/configuracion/correo',
            $fallidos ? 'falta' : 'ok',
            match (true) {
                $fallidos === 1 => 'Un correo no salió esta semana. Si empiezan a fallar todos, lo más probable es que haya cambiado la clave del correo.',
                $fallidos > 1 => "{$fallidos} correos no salieron esta semana. Si fallan todos, lo más probable es que haya cambiado la clave del correo.",
                default => 'Configurado, y esta semana no falló ningún envío.',
            }
        );
    }

    private static function tareas(): array
    {
        $ultimo = Programador::ultimoLatido();

        [$estado, $detalle] = match (true) {
            Programador::corriendo() => ['ok', 'Funcionando: las revisiones y los avisos salen solos a su hora.'],
            $ultimo === null => ['falta', 'Nunca han corrido: no se marcan los vencimientos ni salen los avisos por correo. Aquí está cómo activarlas.'],
            default => ['falta', 'No corren desde el ' . $ultimo->format('d/m/Y \a \l\a\s H:i') . ': no se marcan los vencimientos ni salen los avisos.'],
        };

        return self::punto('tareas', 'Correos', 'Avisos automáticos', '/panel/configuracion/tareas', $estado, $detalle);
    }

    private static function enInternet(): array
    {
        $host = (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');
        $local = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local');

        return self::punto(
            'internet', 'Página web', 'La página en internet', '/panel/configuracion/web',
            $local ? 'mejora' : 'ok',
            $local
                ? 'Todavía no está publicada: se ve solo en este computador. Para que Google la encuentre hacen falta un dominio y un hosting.'
                : "Publicada en {$host}."
        );
    }

    private static function fichaDeGoogle(): array
    {
        $faltan = array_keys(array_filter([
            'el enlace de Google Maps' => ! Ajustes::obtener('web.google_maps'),
            'la ubicación exacta' => ! Ajustes::obtener('web.coordenadas'),
            'el enlace para dejar reseñas' => ! Ajustes::obtener('web.resenas'),
        ]));

        $ciudad = Ajustes::obtener('web.ciudad') ?: 'tu ciudad';

        return self::punto(
            'google', 'Página web', 'Google Maps y reseñas', '/panel/configuracion/web',
            $faltan ? 'mejora' : 'ok',
            $faltan
                ? 'Falta ' . self::enumerar($faltan) . ". Ayudan a salir en el mapa cuando alguien busca «gimnasio en {$ciudad}»."
                : 'Mapa, ubicación y reseñas enlazados.'
        );
    }

    private static function fotos(): array
    {
        $fotos = ContenidoWeb::where('tipo', 'foto')->where('activo', true)->count();

        return self::punto(
            'fotos', 'Página web', 'Fotos del gimnasio', '/panel/web/foto',
            $fotos ? 'ok' : 'mejora',
            match (true) {
                $fotos === 0 => 'La web no tiene fotos del gimnasio. La primera que subas queda de fondo en la portada y sale al compartir la página.',
                $fotos === 1 => 'Una foto en la web.',
                default => "{$fotos} fotos en la web.",
            }
        );
    }

    private static function analitica(): array
    {
        $id = Ajustes::obtener('web.google_analytics');

        return self::punto(
            'analitica', 'Página web', 'Google Analytics', '/panel/configuracion/web',
            $id ? 'ok' : 'mejora',
            $id ? "Midiendo las visitas con {$id}." : 'Sin Google Analytics no se sabe cuánta gente entra a la web ni qué mira.'
        );
    }

    /**
     * El contrato, los términos y la privacidad.
     *
     * «Falta» mientras alguno siga siendo el texto base sin revisar: es lo que
     * se le hace firmar a cada socio y lo que se publica en la web, y el texto
     * base no conoce las reglas de este gimnasio.
     */
    private static function textosLegales(): array
    {
        $sinRevisar = collect(\App\Support\TextosLegales::TIPOS)
            ->filter(fn (array $tipo, string $clave) => \App\Support\TextosLegales::esElTextoBase(\App\Support\TextosLegales::vigente($clave)))
            ->map(fn (array $tipo) => mb_strtolower($tipo['titulo']))
            ->values()
            ->all();

        return self::punto(
            'textos_legales', 'Contrato y legales', 'Contrato, términos y privacidad', '/panel/textos-legales/contrato',
            $sinRevisar ? 'falta' : 'ok',
            $sinRevisar
                ? 'Siguen con el texto base: ' . self::enumerar($sinRevisar) . '. Revísalos antes de mandar contratos a firmar.'
                : 'Revisados por el gimnasio. La web publica los términos y la privacidad vigentes.'
        );
    }

    /** @return array{clave:string, grupo:string, titulo:string, href:string, estado:string, detalle:string} */
    private static function punto(string $clave, string $grupo, string $titulo, string $href, string $estado, string $detalle): array
    {
        return compact('clave', 'grupo', 'titulo', 'href', 'estado', 'detalle');
    }

    /** «la dirección, el teléfono y el correo». */
    private static function enumerar(array $cosas): string
    {
        if (count($cosas) <= 1) {
            return $cosas[0] ?? '';
        }

        return implode(', ', array_slice($cosas, 0, -1)) . ' y ' . end($cosas);
    }
}
