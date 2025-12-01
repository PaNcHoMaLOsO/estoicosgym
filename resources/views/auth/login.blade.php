<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Estoicos Gym - Panel Administrativo</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-medium: #16213e;
            --primary-light: #0f3460;
            --accent: #e94560;
            --accent-light: #ff6b6b;
            --accent-dark: #c73e54;
            --text-light: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
            --success: #00bf8e;
            --error: #ff4757;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 50%, var(--primary-light) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-shapes .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        .bg-shapes .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .bg-shapes .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }

        .bg-shapes .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: -10s;
        }

        .bg-shapes .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 10%;
            animation-delay: -15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-20px) rotate(5deg);
            }
            50% {
                transform: translateY(0) rotate(0deg);
            }
            75% {
                transform: translateY(20px) rotate(-5deg);
            }
        }

        /* Login container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-section img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 30px rgba(233, 69, 96, 0.3));
        }

        .logo-section h1 {
            color: var(--text-light);
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .logo-section h1 span {
            color: var(--accent);
        }

        .logo-section p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 300;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 16px 50px 16px 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-light);
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 20px rgba(233, 69, 96, 0.2);
        }

        .form-control:focus ~ i.input-icon {
            color: var(--accent);
        }

        .form-control.is-invalid {
            border-color: var(--error);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--accent);
        }

        /* Remember me */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            display: none;
        }

        .remember-me .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .remember-me input:checked + .checkmark {
            background: var(--accent);
            border-color: var(--accent);
        }

        .remember-me .checkmark i {
            color: white;
            font-size: 12px;
            opacity: 0;
            transform: scale(0);
            transition: all 0.2s ease;
        }

        .remember-me input:checked + .checkmark i {
            opacity: 1;
            transform: scale(1);
        }

        .remember-me span {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-login .spinner {
            display: none;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        /* Error messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.15);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff6b7a;
        }

        .alert-success {
            background: rgba(0, 191, 142, 0.15);
            border: 1px solid rgba(0, 191, 142, 0.3);
            color: #00bf8e;
        }

        .alert i {
            font-size: 18px;
        }

        .invalid-feedback {
            color: var(--error);
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-footer p {
            color: var(--text-muted);
            font-size: 13px;
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--accent-light);
        }

        /* Dumbbell icon decoration */
        .dumbbell-decoration {
            position: absolute;
            font-size: 150px;
            color: rgba(233, 69, 96, 0.03);
            z-index: 0;
        }

        .dumbbell-decoration.top-right {
            top: 5%;
            right: 5%;
            transform: rotate(45deg);
        }

        .dumbbell-decoration.bottom-left {
            bottom: 5%;
            left: 5%;
            transform: rotate(-45deg);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 25px;
            }

            .logo-section h1 {
                font-size: 24px;
            }

            .form-options {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Loading animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Dumbbell decorations -->
    <i class="fas fa-dumbbell dumbbell-decoration top-right"></i>
    <i class="fas fa-dumbbell dumbbell-decoration bottom-left"></i>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="{{ asset('vendor/adminlte/dist/img/Logo_Estoicos_Gym.svg') }}" alt="Estoicos Gym">
                <h1>ESTOICOS <span>GYM</span></h1>
                <p>Panel Administrativo</p>
            </div>

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}"
                               placeholder="admin@estoicosgym.cl"
                               required 
                               autofocus>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="••••••••"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                        <span>Recordarme</span>
                    </label>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="login-footer">
                <p>© {{ date('Y') }} Estoicos Gym. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    <script>
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

        // Form submit animation
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // Input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>