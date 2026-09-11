@extends('layouts.acceso')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <div class="cabecera">
        <div class="icono"><i class="fas fa-unlock-alt" aria-hidden="true"></i></div>
        <h1>Recuperar contraseña</h1>
        <p>Ingresa tu correo y te enviaremos un enlace para crear una nueva.</p>
    </div>

    @if (session('status'))
        <div class="alerta alerta-ok" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alerta alerta-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="resetForm">
        @csrf

        <div class="campo">
            <label for="email">Correo electrónico</label>
            <div class="entrada">
                <i class="fas fa-envelope icono-campo" aria-hidden="true"></i>
                <input type="email"
                       id="email"
                       name="email"
                       class="control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="tu@correo.cl"
                       autocomplete="email"
                       required
                       autofocus>
            </div>
            @error('email')
                <p class="error-campo"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="boton" id="btnSubmit">
            <span class="btn-text"><i class="fas fa-paper-plane" aria-hidden="true"></i> Enviar enlace</span>
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </form>

    <a href="{{ route('login') }}" class="enlace volver">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión
    </a>
@endsection

@section('scripts')
    <script>
        // Prevenir problemas de navegación
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.reload(true);
        }

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        document.getElementById('resetForm').addEventListener('submit', function () {
            const btn = document.getElementById('btnSubmit');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
@endsection
