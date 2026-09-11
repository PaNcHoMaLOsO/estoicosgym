@extends('layouts.acceso')

@section('titulo', 'Nueva contraseña')

@section('contenido')
    <div class="cabecera">
        <div class="icono"><i class="fas fa-key" aria-hidden="true"></i></div>
        <h1>Nueva contraseña</h1>
        <p>Escribe tu nueva contraseña para volver a entrar.</p>
    </div>

    @if ($errors->any())
        <div class="alerta alerta-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="campo">
            <label for="email">Correo electrónico</label>
            <div class="entrada">
                <i class="fas fa-envelope icono-campo" aria-hidden="true"></i>
                <input type="email"
                       id="email"
                       name="email"
                       class="control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email ?? '') }}"
                       placeholder="tu@correo.cl"
                       autocomplete="email"
                       required>
            </div>
            @error('email')
                <p class="error-campo"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="password">Nueva contraseña</label>
            <div class="entrada">
                <i class="fas fa-lock icono-campo" aria-hidden="true"></i>
                <input type="password"
                       id="password"
                       name="password"
                       class="control @error('password') is-invalid @enderror"
                       placeholder="Mínimo 8 caracteres"
                       autocomplete="new-password"
                       required>
                <button type="button" class="ver-clave" onclick="togglePassword('password', 'toggleIcon1')" aria-label="Mostrar u ocultar la contraseña">
                    <i class="fas fa-eye" id="toggleIcon1" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <p class="error-campo"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <div class="entrada">
                <i class="fas fa-lock icono-campo" aria-hidden="true"></i>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="control"
                       placeholder="Repite la contraseña"
                       autocomplete="new-password"
                       required>
                <button type="button" class="ver-clave" onclick="togglePassword('password_confirmation', 'toggleIcon2')" aria-label="Mostrar u ocultar la confirmación">
                    <i class="fas fa-eye" id="toggleIcon2" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="boton" id="btnSubmit">
            <span class="btn-text"><i class="fas fa-save" aria-hidden="true"></i> Guardar contraseña</span>
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </form>

    <a href="{{ route('login') }}" class="enlace volver">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión
    </a>
@endsection

@section('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('resetForm').addEventListener('submit', function () {
            const btn = document.getElementById('btnSubmit');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
@endsection
