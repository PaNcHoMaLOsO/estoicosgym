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

        // Panel nuevo en Inertia + React. Las vistas Blade que siguen vivas no
        // se ven afectadas: el middleware solo actua sobre respuestas Inertia.
        $middleware->appendToGroup('web', \App\Http\Middleware\HandleInertiaRequests::class);

        // Detrás de un túnel o de un proxy en el MISMO equipo —lo que publica el
        // sistema en internet—, los enlaces tienen que salir con https y con la
        // dirección pública, no con http://localhost: si no, el navegador bloquea
        // los estilos y el panel se ve roto. Solo se les cree a esas cabeceras
        // cuando llegan desde el propio equipo; desde afuera nadie puede hacerse
        // pasar por el proxy.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        // Alias para middlewares personalizados
        $middleware->alias([
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'verify.session' => \App\Http\Middleware\VerifyActiveSession::class,
            'puede' => \App\Http\Middleware\VerificaPermiso::class,
            // Las pantallas que no son mas que dinero, cuando el dueno pidio
            // no tenerlo delante. Se enciende y se apaga en Configuracion.
            'sin-dinero' => \App\Http\Middleware\EscondeElDinero::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * LA SESIÓN QUE CADUCA CON EL PANEL ABIERTO.
         *
         * Quien deja el panel abierto y vuelve al rato hace clic en algo, el
         * servidor ve que ya no hay sesión y lo manda a entrar. Pero esa
         * pantalla no es del panel, y el panel la pintaba ENCIMA de sí mismo,
         * en una ventana. Igual que al salir: se va de verdad a la pantalla de
         * entrar.
         */
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::location(route('login'));
            }

            return null;
        });
    })->create();
