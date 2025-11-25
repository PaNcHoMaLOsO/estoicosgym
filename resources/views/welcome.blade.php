<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EstóicosGym') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Estilos personalizados -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Figtree', sans-serif;
            }
            .welcome-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .welcome-card {
                background: white;
                border-radius: 12px;
                padding: 3rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 600px;
                width: 100%;
            }
            .logo-text {
                font-size: 2rem;
                font-weight: 700;
                color: #1a73e8;
                margin-bottom: 1.5rem;
            }
            .welcome-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #212529;
                margin-bottom: 1rem;
            }
            .welcome-description {
                color: #6c757d;
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .nav-links {
                display: flex;
                gap: 1rem;
                margin-bottom: 2rem;
                flex-wrap: wrap;
            }
            .btn-link-default {
                display: inline-flex;
                align-items: center;
                padding: 0.75rem 1.5rem;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                text-decoration: none;
                color: #495057;
                font-weight: 500;
                transition: all 0.2s;
            }
            .btn-link-default:hover {
                border-color: #1a73e8;
                color: #1a73e8;
                background-color: #f8f9fa;
            }
        </style>
    </head>
    <body>
        <div class="welcome-container">
            <div class="welcome-card">
                <div class="logo-text">EstóicosGym</div>

                @auth
                    <div class="welcome-title">¡Bienvenido de vuelta!</div>
                    <p class="welcome-description">Accede a tu panel de control para gestionar tus actividades.</p>
                    <div class="nav-links">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            Ir al Dashboard
                        </a>
                    </div>
                @else
                    <div class="welcome-title">Sistema de Gestión de Gimnasio</div>
                    <p class="welcome-description">
                        Bienvenido al sistema integrado de gestión de membresías, pagos, inscripciones y administración de clientes.
                    </p>

                    <div class="nav-links">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="btn-link-default">Iniciar Sesión</a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
                        @endif
                    </div>

                    <hr>

                    <h3 style="font-size: 1rem; font-weight: 600; margin-top: 2rem; margin-bottom: 1rem;">Características</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 0.5rem 0; color: #495057;">
                            ✓ Gestión de clientes y membresías
                        </li>
                        <li style="padding: 0.5rem 0; color: #495057;">
                            ✓ Registro de pagos e inscripciones
                        </li>
                        <li style="padding: 0.5rem 0; color: #495057;">
                            ✓ Panel de control con estadísticas
                        </li>
                        <li style="padding: 0.5rem 0; color: #495057;">
                            ✓ Auditoría y notificaciones
                        </li>
                    </ul>
                @endauth
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- JavaScript personalizado -->
        <script src="{{ asset('js/main.js') }}"></script>
    </body>
</html>
