@extends('adminlte::page')

@section('title', 'EstóicosGym - Bienvenido')

@section('content_header')
    <h1>
        <i class="fas fa-dumbbell"></i> EstóicosGym
    </h1>
@endsection

@section('content')
    @auth
        <!-- Usuario autenticado -->
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">¡Bienvenido de vuelta, {{ auth()->user()->name }}!</h3>
                    </div>
                    <div class="card-body text-center py-5">
                        <p class="lead text-muted mb-4">
                            Accede a tu panel de control para gestionar tus actividades
                        </p>
                        <a href="{{ route('dashboard') }}" class="btn btn-lg btn-success">
                            <i class="fas fa-chart-line"></i> Ir al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Usuario no autenticado -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Tarjeta principal de bienvenida -->
                <div class="card card-primary card-outline">
                    <div class="card-body text-center py-5">
                        <h2 class="mb-3">
                            <i class="fas fa-dumbbell text-primary"></i> Sistema de Gestión
                        </h2>
                        <p class="text-muted lead mb-4">
                            Bienvenido al sistema integrado de gestión de membresías, pagos, inscripciones y administración de clientes.
                        </p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="text-secondary mb-2">¿Ya eres miembro?</p>
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-secondary mb-2">¿Nuevo usuario?</p>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-user-plus"></i> Registrarse
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de características -->
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card card-info">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar"></i> Características
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i> Gestión de clientes y membresías
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i> Registro de pagos e inscripciones
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success"></i> Panel de control con estadísticas
                                    </li>
                                    <li>
                                        <i class="fas fa-check text-success"></i> Auditoría y notificaciones
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-shield-alt"></i> Ventajas
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-lock text-warning"></i> Seguridad total de datos
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-tachometer-alt text-warning"></i> Interfaz rápida y eficiente
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-mobile-alt text-warning"></i> Acceso desde cualquier dispositivo
                                    </li>
                                    <li>
                                        <i class="fas fa-headset text-warning"></i> Soporte profesional
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
@endsection

@section('css')
    <style>
        .card {
            box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
            transition: box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 3px 6px rgba(0,0,0,.15), 0 2px 4px rgba(0,0,0,.12);
        }
        .btn-lg {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
        }
        .lead {
            font-size: 1.1rem;
        }
    </style>
@endsection
