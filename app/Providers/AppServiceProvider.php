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
