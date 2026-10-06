<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Buscar sin mirar mayúsculas ni tildes también en PostgreSQL, que es
        // lo que usa el servidor: ver App\Support\Parecido.
        \App\Support\Parecido::registrar();

        /*
         * Un texto de la petición, siempre texto. Convertir con (string) lo que llega
         * revienta con un 500 cuando en la dirección viene una lista
         * (?buscar[]=x): cualquiera puede escribirla, y los robots lo hacen.
         * Una lista o un vacío se leen como el valor por defecto.
         */
        \Illuminate\Http\Request::macro('texto', function (string $clave, string $defecto = ''): string {
            $valor = $this->input($clave);

            return is_scalar($valor) ? (string) $valor : $defecto;
        });

        /*
         * UNA SOLA DIRECCIÓN EN INTERNET. Detrás de Cloudflare la petición
         * puede llegar como http o con otro nombre; los enlaces, el canonical
         * y el mapa del sitio tienen que salir siempre con la de APP_URL y con
         * https. Lo que llega por otra dirección lo manda ahí DireccionUnica.
         */
        if ($this->app->environment('production') && config('app.url')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Todo lo que se registre como error va también al registro de fallas
        // del panel (Configuración → Panel → Registro de fallas).
        // Los posibles duplicados se guardan diez minutos: si cambia un
        // socio —alta, edición, juntar, papelera—, se recalculan.
        foreach (['saved', 'deleted', 'restored'] as $evento) {
            \Illuminate\Support\Facades\Event::listen(
                "eloquent.{$evento}: " . \App\Models\Cliente::class,
                fn () => \App\Support\FichasRepetidas::olvidar(),
            );
        }

        // Los avisos del menú de Configuración se guardan cinco minutos; si
        // cambia algo de lo que miran, se recalculan en la próxima pantalla.
        foreach ([\App\Models\Convenio::class, \App\Models\Membresia::class, \App\Models\PrecioMembresia::class,
            \App\Models\MetodoPago::class, \App\Models\ContenidoWeb::class, \App\Models\Especialista::class] as $modelo) {
            foreach (['saved', 'deleted'] as $evento) {
                \Illuminate\Support\Facades\Event::listen(
                    "eloquent.{$evento}: {$modelo}",
                    fn () => \Illuminate\Support\Facades\Cache::forget(\App\Support\EstadoDeConfiguracion::CACHE_AVISOS),
                );
            }
        }

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Log\Events\MessageLogged::class,
            [\App\Support\RegistroDeFallas::class, 'delLog'],
        );

        /*
         * EL LOGIN SE FRENA POR IP. Por cuenta se cuentan solo los FALLOS, en
         * la ruta del login (routes/web.php): antes se contaba todo intento, y
         * cualquiera que supiera el correo del administrador lo dejaba afuera
         * una hora con 20 intentos desde 20 direcciones.
         */
        \Illuminate\Support\Facades\RateLimiter::for('login', fn (\Illuminate\Http\Request $request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(8)->by('ip:' . \App\Support\IpDelCliente::paraLimitar($request)),
        ]);

        // Los demás frenos de lo que se usa sin sesión, por minuto. Con nombre
        // en vez de «throttle:10,1» para que cuenten la red IPv6 entera (ver
        // IpDelCliente) y no cada dirección suelta.
        foreach (['publico' => 10, 'contrato' => 30, 'acceso' => 6, 'reenvio' => 4] as $nombre => $porMinuto) {
            \Illuminate\Support\Facades\RateLimiter::for($nombre, fn (\Illuminate\Http\Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute($porMinuto)
                ->by($nombre . ':' . \App\Support\IpDelCliente::paraLimitar($request)));
        }
    }
}
