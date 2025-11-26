<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\EstadoHelper;
use App\Helpers\PrecioHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('estado-helper', function () {
            return new EstadoHelper();
        });

        $this->app->singleton('precio', function () {
            return new PrecioHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
