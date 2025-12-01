<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restablecer Contraseña | Estoicos Gym</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gym-purple: #c140d4;
            --gym-purple-dark: #a035b5;
            --gym-purple-light: #d466e3;
            --gym-cream: #f1e6bf;
            --gym-navy: #253a5b;
            --gym-navy-dark: #1a2a3f;
            --gym-navy-light: #2f4a6f;
            --gym-gray: #434750;
            
            --primary-dark: #1a2a3f;
            --primary-medium: #253a5b;
            --primary-light: #2f4a6f;
            --accent: #c140d4;
            --accent-light: #d466e3;
            --accent-dark: #a035b5;
            --text-light: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
            --text-cream: #f1e6bf;
            --success: #28a745;
            --error: #dc3545;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .bg-shapes {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
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

        .bg-shapes .shape:nth-child(1) { width: 300px; height: 300px; top: -100px; left: -100px; animation-delay: 0s; }
        .bg-shapes .shape:nth-child(2) { width: 200px; height: 200px; bottom: -50px; right: -50px; animation-delay: -5s; }
        .bg-shapes .shape:nth-child(3) { width: 150px; height: 150px; top: 50%; right: 10%; animation-delay: -10s; }
        .bg-shapes .shape:nth-child(4) { width: 100px; height: 100px; bottom: 20%; left: 10%; animation-delay: -15s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(5deg); }
            50% { transform: translateY(0) rotate(0deg); }
            75% { transform: translateY(20px) rotate(-5deg); }
        }

        .reset-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .header-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .header-section .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gym-purple) 0%, var(--gym-purple-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 30px rgba(193, 64, 212, 0.4);
        }

        .header-section .icon-circle i {
            font-size: 35px;
            color: white;
        }

        .header-section h1 {
            color: var(--text-cream);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .header-section p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
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
            padding: 16px 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-light);
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 20px rgba(193, 64, 212, 0.2);
        }

        .form-control:focus ~ i.input-icon { color: var(--accent); }
        .form-control.is-invalid { border-color: var(--error); }

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

        .password-toggle:hover { color: var(--accent); }

        .btn-submit {
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
            margin-top: 10px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(193, 64, 212, 0.5);
        }

        .btn-submit:hover::before { left: 100%; }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b7a;
        }

        .alert i { font-size: 18px; }

        .invalid-feedback {
            color: var(--error);
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dumbbell-decoration {
            position: absolute;
            font-size: 150px;
            color: rgba(193, 64, 212, 0.05);
            z-index: 0;
        }

        .dumbbell-decoration.top-right { top: 5%; right: 5%; transform: rotate(45deg); }
        .dumbbell-decoration.bottom-left { bottom: 5%; left: 5%; transform: rotate(-45deg); }

        .password-requirements {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .password-requirements h4 {
            color: var(--text-cream);
            font-size: 13px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .password-requirements li {
            color: var(--text-muted);
            font-size: 12px;
            padding: 3px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .password-requirements li i {
            font-size: 10px;
            color: var(--gym-purple);
        }

        .btn-submit .spinner { display: none; }
        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }

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

        @media (max-width: 480px) {
            .reset-card { padding: 40px 25px; }
            .header-section h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <i class="fas fa-dumbbell dumbbell-decoration top-right"></i>
    <i class="fas fa-dumbbell dumbbell-decoration bottom-left"></i>

    <div class="reset-container">
        <div class="reset-card">
            <div class="header-section">
                <div class="icon-circle">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Nueva Contraseña</h1>
                <p>Ingresa tu nueva contraseña para restablecer el acceso a tu cuenta.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ $email ?? old('email') }}"
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
                    <label for="password">Nueva Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="••••••••"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="••••••••"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <div class="password-requirements">
                    <h4><i class="fas fa-shield-alt"></i> Requisitos de contraseña:</h4>
                    <ul>
                        <li><i class="fas fa-circle"></i> Mínimo 8 caracteres</li>
                        <li><i class="fas fa-circle"></i> Al menos una letra mayúscula</li>
                        <li><i class="fas fa-circle"></i> Al menos un número</li>
                    </ul>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span class="btn-text">
                        <i class="fas fa-save"></i> Restablecer Contraseña
                    </span>
                    <div class="spinner"></div>
                </button>
            </form>
        </div>
    </div>

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

        document.getElementById('resetForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
