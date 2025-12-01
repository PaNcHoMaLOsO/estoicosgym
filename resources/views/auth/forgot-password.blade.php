<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar Contraseña | Estoicos Gym</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Philosopher:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Paleta Estoicos Gym */
            --gym-purple: #c140d4;
            --gym-purple-dark: #9a32a8;
            --gym-purple-light: #d466e3;
            --gym-cream: #f1e6bf;
            --gym-cream-light: #f7f0d8;
            --gym-cream-dark: #d4c99a;
            --gym-navy: #253a5b;
            --gym-navy-dark: #1a2940;
            --gym-navy-light: #344d6e;
            --gym-gray: #434750;
            --gym-gold: #d4af37;
            --gym-bronze: #cd7f32;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Philosopher', serif;
            min-height: 100vh;
            display: flex;
            background: var(--gym-navy-dark);
            overflow-x: hidden;
        }

        /* ============================================
           PANEL IZQUIERDO - ÁGORA GRIEGA
           ============================================ */
        .agora-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--gym-navy-dark) 0%, var(--gym-navy) 50%, var(--gym-navy-light) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            overflow: hidden;
        }

        /* ============================================
           ARBUSTOS Y VEGETACIÓN GRIEGA
           ============================================ */
        .foliage-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            pointer-events: none;
            z-index: 5;
        }

        /* Ciprés griego */
        .cypress {
            position: absolute;
            bottom: 0;
        }

        .cypress-1 { left: 2%; width: 40px; height: 180px; }
        .cypress-2 { right: 3%; width: 35px; height: 160px; }
        .cypress-3 { left: 8%; width: 30px; height: 140px; }

        /* Arbustos de olivo */
        .olive-bush {
            position: absolute;
            bottom: 0;
        }

        .olive-1 { left: 15%; width: 80px; height: 50px; }
        .olive-2 { right: 12%; width: 70px; height: 45px; }
        .olive-3 { left: 35%; width: 60px; height: 40px; }
        .olive-4 { right: 30%; width: 65px; height: 42px; }

        /* Hierba decorativa */
        .grass-patch { position: absolute; bottom: 0; }
        .grass-1 { left: 0; }
        .grass-2 { left: 20%; }
        .grass-3 { left: 45%; }
        .grass-4 { right: 25%; }
        .grass-5 { right: 5%; }

        /* ============================================
           CIELO NOCTURNO
           ============================================ */
        .night-sky {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        /* Luna */
        .moon {
            position: absolute;
            top: 8%;
            right: 12%;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle at 35% 35%, #f1e6bf 0%, #d4c99a 50%, #c9bc8e 100%);
            border-radius: 50%;
            box-shadow: 
                0 0 20px rgba(241, 230, 191, 0.4),
                0 0 40px rgba(241, 230, 191, 0.2);
            animation: moonGlow 4s ease-in-out infinite;
        }

        @keyframes moonGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(241, 230, 191, 0.4), 0 0 40px rgba(241, 230, 191, 0.2); }
            50% { box-shadow: 0 0 30px rgba(241, 230, 191, 0.6), 0 0 50px rgba(241, 230, 191, 0.3); }
        }

        /* Estrellas */
        .star {
            position: absolute;
            background: #f1e6bf;
            border-radius: 50%;
            animation: twinkle 2s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .star-1 { width: 4px; height: 4px; top: 5%; left: 10%; animation-delay: 0s; }
        .star-2 { width: 3px; height: 3px; top: 12%; left: 25%; animation-delay: 0.5s; }
        .star-3 { width: 5px; height: 5px; top: 8%; left: 45%; animation-delay: 1s; }
        .star-4 { width: 3px; height: 3px; top: 15%; left: 60%; animation-delay: 1.5s; }
        .star-5 { width: 4px; height: 4px; top: 20%; left: 8%; animation-delay: 0.3s; }
        .star-6 { width: 3px; height: 3px; top: 25%; left: 35%; animation-delay: 0.8s; }
        .star-7 { width: 5px; height: 5px; top: 18%; left: 55%; animation-delay: 1.3s; }
        .star-8 { width: 4px; height: 4px; top: 30%; left: 15%; animation-delay: 0.2s; }
        .star-9 { width: 3px; height: 3px; top: 35%; left: 5%; animation-delay: 1.8s; }
        .star-10 { width: 4px; height: 4px; top: 28%; left: 75%; animation-delay: 0.6s; }
        .star-11 { width: 3px; height: 3px; top: 40%; left: 85%; animation-delay: 1.1s; }
        .star-12 { width: 5px; height: 5px; top: 10%; left: 70%; animation-delay: 0.4s; }
        .star-13 { width: 3px; height: 3px; top: 45%; left: 20%; animation-delay: 1.6s; }
        .star-14 { width: 4px; height: 4px; top: 22%; left: 90%; animation-delay: 0.9s; }
        .star-15 { width: 3px; height: 3px; top: 38%; left: 65%; animation-delay: 1.4s; }

        /* Lluvia de meteoros */
        .meteor {
            position: absolute;
            width: 2px;
            height: 80px;
            background: linear-gradient(180deg, transparent, rgba(241, 230, 191, 0.3), rgba(241, 230, 191, 0.9));
            transform: rotate(35deg);
            opacity: 0;
            animation: meteorFall 3s linear infinite;
        }

        .meteor::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: -1px;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 6px 2px rgba(241, 230, 191, 0.8);
        }

        @keyframes meteorFall {
            0% { opacity: 0; transform: rotate(35deg) translateY(-100px); }
            5% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; transform: rotate(35deg) translateY(1200px); }
        }

        .meteor-1 { left: 10%; top: -100px; animation-delay: 0s; animation-duration: 2.5s; }
        .meteor-2 { left: 30%; top: -100px; animation-delay: 1.2s; animation-duration: 3s; }
        .meteor-3 { left: 50%; top: -100px; animation-delay: 2.4s; animation-duration: 2.8s; }
        .meteor-4 { left: 70%; top: -100px; animation-delay: 0.8s; animation-duration: 3.2s; }
        .meteor-5 { left: 90%; top: -100px; animation-delay: 1.8s; animation-duration: 2.6s; }

        /* Contenido central */
        .agora-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 500px;
        }

        /* Logo */
        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .agora-logo {
            width: 180px;
            height: 180px;
            filter: drop-shadow(0 15px 50px rgba(193, 64, 212, 0.8));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .agora-title {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gym-cream);
            text-transform: uppercase;
            letter-spacing: 6px;
            margin-bottom: 5px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
        }

        .agora-title span {
            color: var(--gym-purple);
            display: block;
            font-size: 2.8rem;
            letter-spacing: 12px;
        }

        .agora-subtitle {
            font-family: 'Philosopher', serif;
            font-size: 1rem;
            color: var(--gym-cream);
            opacity: 0.8;
            font-style: italic;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        /* Templo */
        .temple-scene {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40%;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .temple-svg {
            width: 100%;
            max-width: 500px;
            height: 100%;
            opacity: 0.12;
        }

        /* ============================================
           PANEL DERECHO - FORMULARIO
           ============================================ */
        .form-panel {
            width: 500px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--gym-cream-light) 0%, var(--gym-cream) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
        }

        .form-panel::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
            background: linear-gradient(180deg, var(--gym-purple) 0%, var(--gym-navy) 50%, var(--gym-purple) 100%);
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gym-purple) 0%, var(--gym-purple-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 30px rgba(193, 64, 212, 0.3);
        }

        .form-header .icon-circle i {
            font-size: 32px;
            color: white;
        }

        .form-header h2 {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gym-navy);
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .form-header p {
            font-family: 'Philosopher', serif;
            color: var(--gym-gray);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-family: 'Cinzel', serif;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gym-navy);
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gym-gray);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 16px 50px;
            background: white;
            border: 2px solid var(--gym-cream-dark);
            border-radius: 12px;
            color: var(--gym-navy);
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gym-purple);
            box-shadow: 0 0 0 4px rgba(193, 64, 212, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        /* Botones */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--gym-purple) 0%, var(--gym-purple-dark) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-family: 'Cinzel', serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(193, 64, 212, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            background: transparent;
            border: 2px solid var(--gym-navy);
            border-radius: 12px;
            color: var(--gym-navy);
            font-family: 'Cinzel', serif;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: var(--gym-navy);
            color: var(--gym-cream);
        }

        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Philosopher', serif;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .form-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gym-cream-dark);
        }

        .form-footer p {
            font-family: 'Philosopher', serif;
            color: var(--gym-gray);
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .agora-panel { display: none; }
            .form-panel { width: 100%; min-height: 100vh; }
        }

        @media (max-width: 480px) {
            .form-panel { padding: 30px 20px; }
            .form-header h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Panel Izquierdo - Ágora Griega -->
    <div class="agora-panel">
        <!-- Vegetación Griega -->
        <div class="foliage-container">
            <svg class="cypress cypress-1" viewBox="0 0 40 180">
                <ellipse cx="20" cy="90" rx="12" ry="85" fill="rgba(34, 60, 34, 0.7)"/>
                <ellipse cx="20" cy="85" rx="10" ry="80" fill="rgba(45, 80, 45, 0.8)"/>
                <rect x="17" y="165" width="6" height="15" fill="rgba(101, 67, 33, 0.8)"/>
            </svg>
            <svg class="cypress cypress-2" viewBox="0 0 35 160">
                <ellipse cx="17" cy="80" rx="10" ry="75" fill="rgba(34, 60, 34, 0.6)"/>
                <ellipse cx="17" cy="75" rx="8" ry="70" fill="rgba(45, 80, 45, 0.7)"/>
                <rect x="14" y="145" width="6" height="15" fill="rgba(101, 67, 33, 0.7)"/>
            </svg>
            <svg class="cypress cypress-3" viewBox="0 0 30 140">
                <ellipse cx="15" cy="70" rx="9" ry="65" fill="rgba(40, 70, 40, 0.5)"/>
                <rect x="12" y="125" width="5" height="12" fill="rgba(101, 67, 33, 0.6)"/>
            </svg>
            <svg class="olive-bush olive-1" viewBox="0 0 80 50">
                <ellipse cx="40" cy="35" rx="35" ry="18" fill="rgba(85, 107, 47, 0.6)"/>
                <ellipse cx="30" cy="30" rx="25" ry="14" fill="rgba(107, 142, 35, 0.5)"/>
            </svg>
            <svg class="olive-bush olive-2" viewBox="0 0 70 45">
                <ellipse cx="35" cy="30" rx="30" ry="16" fill="rgba(85, 107, 47, 0.5)"/>
                <ellipse cx="25" cy="28" rx="20" ry="12" fill="rgba(107, 142, 35, 0.4)"/>
            </svg>
        </div>

        <!-- Cielo Nocturno -->
        <div class="night-sky">
            <div class="moon"></div>
            <div class="star star-1"></div>
            <div class="star star-2"></div>
            <div class="star star-3"></div>
            <div class="star star-4"></div>
            <div class="star star-5"></div>
            <div class="star star-6"></div>
            <div class="star star-7"></div>
            <div class="star star-8"></div>
            <div class="star star-9"></div>
            <div class="star star-10"></div>
            <div class="star star-11"></div>
            <div class="star star-12"></div>
            <div class="star star-13"></div>
            <div class="star star-14"></div>
            <div class="star star-15"></div>
            <div class="meteor meteor-1"></div>
            <div class="meteor meteor-2"></div>
            <div class="meteor meteor-3"></div>
            <div class="meteor meteor-4"></div>
            <div class="meteor meteor-5"></div>
        </div>

        <!-- Templo Griego -->
        <div class="temple-scene">
            <svg class="temple-svg" viewBox="0 0 500 300" preserveAspectRatio="xMidYMax meet">
                <!-- Base del templo -->
                <rect x="20" y="285" width="460" height="15" fill="#f1e6bf"/>
                <rect x="30" y="275" width="440" height="12" fill="#d4c99a"/>
                <rect x="40" y="265" width="420" height="12" fill="#f1e6bf"/>
                
                <!-- Columnas dóricas -->
                <rect x="55" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="120" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="185" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="280" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="345" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="410" y="95" width="35" height="170" fill="#f1e6bf"/>
                
                <!-- Arquitrabe -->
                <rect x="35" y="75" width="430" height="22" fill="#f1e6bf"/>
                <rect x="35" y="70" width="430" height="8" fill="#d4c99a"/>
                
                <!-- Frontón -->
                <polygon points="250,15 35,75 465,75" fill="#f1e6bf"/>
            </svg>
        </div>

        <!-- Contenido central -->
        <div class="agora-content">
            <div class="logo-wrapper">
                <img src="{{ asset('vendor/adminlte/dist/img/Logo_Estoicos_Gym.svg') }}" alt="Estoicos Gym" class="agora-logo">
            </div>
            <h1 class="agora-title">
                ESTOICOS
                <span>GYM</span>
            </h1>
            <p class="agora-subtitle">Fortaleza del Cuerpo y la Mente</p>
        </div>
    </div>

    <!-- Panel Derecho - Formulario -->
    <div class="form-panel">
        <div class="form-header">
            <div class="icon-circle">
                <i class="fas fa-unlock-alt"></i>
            </div>
            <h2>Recuperar Contraseña</h2>
            <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
        </div>

        <!-- Alertas -->
        @if (session('status'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('password.email') }}" id="resetForm">
            @csrf

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-wrapper">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}"
                           placeholder="tu@correo.com"
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

            <button type="submit" class="btn-submit" id="btnSubmit">
                <span class="btn-text">
                    <i class="fas fa-paper-plane"></i> Enviar Enlace
                </span>
                <div class="spinner"></div>
            </button>

            <a href="{{ route('login') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver al Ágora
            </a>
        </form>

        <div class="form-footer">
            <p>© {{ date('Y') }} Estoicos Gym. Forjando guerreros.</p>
        </div>
    </div>

    <script>
        // Prevenir problemas de navegación
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.reload(true);
        }
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        document.getElementById('resetForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
