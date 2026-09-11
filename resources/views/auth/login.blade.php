@extends('layouts.acceso')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="cabecera">
        <h1>Bienvenido</h1>
        <p>Ingresa al panel de administración.</p>
    </div>

    @if ($errors->any())
        <div class="alerta alerta-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if (session('status'))
        <div class="alerta alerta-ok" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
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
                       autocomplete="username"
                       required
                       autofocus>
            </div>
            @error('email')
                <p class="error-campo"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <div class="entrada">
                <i class="fas fa-lock icono-campo" aria-hidden="true"></i>
                <input type="password"
                       id="password"
                       name="password"
                       class="control @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       required>
                <button type="button" class="ver-clave" onclick="togglePassword()" aria-label="Mostrar u ocultar la contraseña">
                    <i class="fas fa-eye" id="toggleIcon" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <p class="error-campo"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</p>
            @enderror
        </div>

        <div class="opciones">
            <label class="recordar">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="enlace">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="boton" id="btnLogin">
            <span class="btn-text"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Ingresar</span>
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </form>

    <a href="{{ url('/') }}" class="enlace volver">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al sitio
    </a>
@endsection

@section('scripts')
    <script>
        // Prevenir problemas de navegación hacia atrás después de logout
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.reload(true);
        }

        // Reemplazar el historial para evitar volver atrás a páginas protegidas
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Prevenir caché de página con bfcache
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
@endsection
