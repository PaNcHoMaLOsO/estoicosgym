<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Agregar middleware de no-cache para rutas autenticadas
        $middleware->appendToGroup('web', \App\Http\Middleware\NoCacheMiddleware::class);
        
        // Alias para middlewares personalizados
        $middleware->alias([
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'verify.session' => \App\Http\Middleware\VerifyActiveSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
