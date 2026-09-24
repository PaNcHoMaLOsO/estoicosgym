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
    }
}
