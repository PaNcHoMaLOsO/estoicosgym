<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\EstadoHelper;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
