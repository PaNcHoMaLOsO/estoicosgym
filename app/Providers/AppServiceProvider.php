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
         * EL LOGIN SE FRENA POR IP Y POR CUENTA. Solo por IP, quien prueba
         * claves desde muchas direcciones no se frenaba nunca. Por cuenta: 20
         * intentos por hora; quien se equivoca un par de veces no lo nota.
         */
        \Illuminate\Support\Facades\RateLimiter::for('login', fn (\Illuminate\Http\Request $request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(8)->by('ip:' . $request->ip()),
            \Illuminate\Cache\RateLimiting\Limit::perHour(20)->by('cuenta:' . mb_strtolower(trim((string) $request->input('email')))),
        ]);
    }
}
