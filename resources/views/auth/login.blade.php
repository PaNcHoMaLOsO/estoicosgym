<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión | Estoicos Gym</title>
    
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

        .cypress-1 {
            left: 2%;
            width: 40px;
            height: 180px;
        }

        .cypress-2 {
            right: 3%;
            width: 35px;
            height: 160px;
        }

        .cypress-3 {
            left: 8%;
            width: 30px;
            height: 140px;
        }

        /* Arbustos de olivo */
        .olive-bush {
            position: absolute;
            bottom: 0;
        }

        .olive-1 {
            left: 15%;
            width: 80px;
            height: 50px;
        }

        .olive-2 {
            right: 12%;
            width: 70px;
            height: 45px;
        }

        .olive-3 {
            left: 35%;
            width: 60px;
            height: 40px;
        }

        .olive-4 {
            right: 30%;
            width: 65px;
            height: 42px;
        }

        /* Hierba decorativa */
        .grass-patch {
            position: absolute;
            bottom: 0;
        }

        .grass-1 { left: 0; }
        .grass-2 { left: 20%; }
        .grass-3 { left: 45%; }
        .grass-4 { right: 25%; }
        .grass-5 { right: 5%; }

        /* Pilares griegos - Eliminado, usando temple-pillars */
        .pillars-container {
            display: none;
        }

        .pillar {
            display: none;
        }

        /* Contenido central del ágora */
        .agora-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 500px;
        }

        /* ============================================
           CIELO NOCTURNO - ESTRELLAS Y LUNA
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
                0 0 40px rgba(241, 230, 191, 0.2),
                0 0 60px rgba(241, 230, 191, 0.1);
            animation: moonGlow 4s ease-in-out infinite;
        }

        .moon::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 12px;
            width: 12px;
            height: 12px;
            background: rgba(212, 201, 154, 0.6);
            border-radius: 50%;
        }

        .moon::after {
            content: '';
            position: absolute;
            top: 25px;
            left: 30px;
            width: 8px;
            height: 8px;
            background: rgba(212, 201, 154, 0.4);
            border-radius: 50%;
        }

        @keyframes moonGlow {
            0%, 100% { 
                box-shadow: 
                    0 0 20px rgba(241, 230, 191, 0.4),
                    0 0 40px rgba(241, 230, 191, 0.2),
                    0 0 60px rgba(241, 230, 191, 0.1);
            }
            50% { 
                box-shadow: 
                    0 0 30px rgba(241, 230, 191, 0.6),
                    0 0 50px rgba(241, 230, 191, 0.3),
                    0 0 80px rgba(241, 230, 191, 0.15);
            }
        }

        /* Estrellas */
        .star {
            position: absolute;
            background: #f1e6bf;
            border-radius: 50%;
            animation: twinkle 2s ease-in-out infinite;
        }

        .star::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #f1e6bf, transparent);
        }

        .star::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 2px;
            height: 200%;
            background: linear-gradient(180deg, transparent, #f1e6bf, transparent);
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Diferentes tamaños y delays para estrellas */
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
        .star-16 { width: 4px; height: 4px; top: 3%; left: 30%; animation-delay: 0.7s; }
        .star-17 { width: 3px; height: 3px; top: 7%; left: 80%; animation-delay: 1.2s; }
        .star-18 { width: 5px; height: 5px; top: 42%; left: 92%; animation-delay: 0.1s; }
        .star-19 { width: 3px; height: 3px; top: 50%; left: 12%; animation-delay: 1.9s; }
        .star-20 { width: 4px; height: 4px; top: 55%; left: 78%; animation-delay: 0.4s; }
        .star-21 { width: 3px; height: 3px; top: 48%; left: 45%; animation-delay: 1.7s; }
        .star-22 { width: 5px; height: 5px; top: 60%; left: 8%; animation-delay: 0.2s; }
        .star-23 { width: 4px; height: 4px; top: 58%; left: 55%; animation-delay: 1.0s; }
        .star-24 { width: 3px; height: 3px; top: 65%; left: 88%; animation-delay: 0.6s; }
        .star-25 { width: 4px; height: 4px; top: 52%; left: 32%; animation-delay: 1.3s; }
        .star-26 { width: 3px; height: 3px; top: 68%; left: 22%; animation-delay: 0.8s; }
        .star-27 { width: 5px; height: 5px; top: 72%; left: 68%; animation-delay: 1.5s; }
        .star-28 { width: 4px; height: 4px; top: 75%; left: 42%; animation-delay: 0.3s; }
        .star-29 { width: 3px; height: 3px; top: 78%; left: 95%; animation-delay: 1.1s; }
        .star-30 { width: 4px; height: 4px; top: 82%; left: 5%; animation-delay: 0.9s; }
        .star-31 { width: 3px; height: 3px; top: 85%; left: 58%; animation-delay: 1.4s; }
        .star-32 { width: 5px; height: 5px; top: 2%; left: 52%; animation-delay: 0.5s; }
        .star-33 { width: 3px; height: 3px; top: 88%; left: 35%; animation-delay: 1.8s; }
        .star-34 { width: 4px; height: 4px; top: 33%; left: 48%; animation-delay: 0.2s; }
        .star-35 { width: 3px; height: 3px; top: 70%; left: 15%; animation-delay: 1.6s; }

        /* Lluvia de estrellas cayendo en diagonal */
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
            0% {
                opacity: 0;
                transform: rotate(35deg) translateY(-100px);
            }
            5% {
                opacity: 1;
            }
            80% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: rotate(35deg) translateY(1200px);
            }
        }

        /* Múltiples meteoros con diferentes posiciones y delays */
        .meteor-1 { left: 5%; top: -100px; animation-delay: 0s; animation-duration: 2.5s; }
        .meteor-2 { left: 15%; top: -100px; animation-delay: 0.8s; animation-duration: 3s; }
        .meteor-3 { left: 25%; top: -100px; animation-delay: 1.5s; animation-duration: 2.8s; }
        .meteor-4 { left: 35%; top: -100px; animation-delay: 0.3s; animation-duration: 3.2s; }
        .meteor-5 { left: 45%; top: -100px; animation-delay: 2s; animation-duration: 2.6s; }
        .meteor-6 { left: 55%; top: -100px; animation-delay: 1.2s; animation-duration: 2.9s; }
        .meteor-7 { left: 65%; top: -100px; animation-delay: 0.6s; animation-duration: 3.1s; }
        .meteor-8 { left: 75%; top: -100px; animation-delay: 1.8s; animation-duration: 2.7s; }
        .meteor-9 { left: 85%; top: -100px; animation-delay: 0.4s; animation-duration: 3.3s; }
        .meteor-10 { left: 95%; top: -100px; animation-delay: 1s; animation-duration: 2.4s; }
        .meteor-11 { left: 10%; top: -100px; animation-delay: 2.2s; animation-duration: 2.8s; }
        .meteor-12 { left: 30%; top: -100px; animation-delay: 1.6s; animation-duration: 3s; }
        .meteor-13 { left: 50%; top: -100px; animation-delay: 0.9s; animation-duration: 2.5s; }
        .meteor-14 { left: 70%; top: -100px; animation-delay: 2.5s; animation-duration: 3.2s; }
        .meteor-15 { left: 90%; top: -100px; animation-delay: 1.3s; animation-duration: 2.6s; }

        /* Partículas flotantes */
        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(193, 64, 212, 0.5);
            border-radius: 50%;
            animation: floatParticle 10s ease-in-out infinite;
        }

        .particle-1 { left: 10%; animation-delay: 0s; }
        .particle-2 { left: 25%; animation-delay: 2s; }
        .particle-3 { left: 40%; animation-delay: 4s; }
        .particle-4 { left: 60%; animation-delay: 1s; }
        .particle-5 { left: 75%; animation-delay: 3s; }
        .particle-6 { left: 85%; animation-delay: 5s; }

        @keyframes floatParticle {
            0%, 100% { 
                bottom: -10px;
                opacity: 0;
                transform: translateX(0);
            }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% { 
                bottom: 100%;
                opacity: 0;
                transform: translateX(20px);
            }
        }

        /* Logo sin anillo */
        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .agora-logo {
            width: 200px;
            height: 200px;
            filter: drop-shadow(0 15px 50px rgba(193, 64, 212, 0.8));
            animation: float 6s ease-in-out infinite;
            position: relative;
            z-index: 5;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* ============================================
           TEMPLO GRIEGO MAJESTUOSO
           ============================================ */
        .temple-scene {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 45%;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .temple-svg {
            width: 100%;
            max-width: 600px;
            height: 100%;
            opacity: 0.15;
        }

        /* Laureles laterales eliminados - ahora corona en logo */

        .agora-title {
            font-family: 'Cinzel', serif;
            font-size: 2.8rem;
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
            font-size: 3.2rem;
            letter-spacing: 12px;
        }

        .agora-subtitle {
            font-family: 'Philosopher', serif;
            font-size: 1.1rem;
            color: var(--gym-cream);
            opacity: 0.8;
            font-style: italic;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        /* Cita estoica */
        .stoic-quote {
            background: rgba(241, 230, 191, 0.1);
            border-left: 3px solid var(--gym-purple);
            padding: 15px 20px;
            border-radius: 0 12px 12px 0;
            max-width: 380px;
            margin: 0 auto;
        }

        .stoic-quote p {
            font-family: 'Philosopher', serif;
            font-size: 0.95rem;
            color: var(--gym-cream);
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .stoic-quote cite {
            font-family: 'Cinzel', serif;
            color: var(--gym-purple-light);
            font-size: 0.8rem;
            font-style: normal;
            letter-spacing: 2px;
        }

        /* Decoración de laurel */
        .laurel-decoration {
            display: none; /* Reemplazado por SVG laureles */
        }

        /* ============================================
           PANEL DERECHO - FORMULARIO
           ============================================ */
        .login-panel {
            width: 500px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--gym-cream-light) 0%, var(--gym-cream) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
        }

        /* Borde decorativo */
        .login-panel::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
            background: linear-gradient(180deg, var(--gym-purple) 0%, var(--gym-navy) 50%, var(--gym-purple) 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gym-navy);
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .login-header p {
            font-family: 'Philosopher', serif;
            color: var(--gym-gray);
            font-size: 1rem;
        }

        /* Formulario */
        .login-form {
            width: 100%;
        }

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
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gym-purple);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 18px 20px 18px 55px;
            background: white;
            border: 2px solid var(--gym-cream-dark);
            border-radius: 12px;
            color: var(--gym-navy);
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gym-purple);
            box-shadow: 0 0 0 4px rgba(193, 64, 212, 0.15);
        }

        .form-control:focus ~ i.input-icon {
            color: var(--gym-purple-dark);
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gym-gray);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--gym-purple);
        }

        /* Opciones del formulario */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
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
            width: 22px;
            height: 22px;
            border: 2px solid var(--gym-navy);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: white;
        }

        .remember-me input:checked + .checkmark {
            background: var(--gym-purple);
            border-color: var(--gym-purple);
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
            color: var(--gym-gray);
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .forgot-password {
            color: var(--gym-purple);
            font-size: 14px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--gym-purple-dark);
            text-decoration: underline;
        }

        /* Botón de login */
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--gym-navy) 0%, var(--gym-navy-dark) 100%);
            border: none;
            border-radius: 12px;
            color: var(--gym-cream);
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(193, 64, 212, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37, 58, 91, 0.4);
            background: linear-gradient(135deg, var(--gym-purple) 0%, var(--gym-purple-dark) 100%);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(241, 230, 191, 0.3);
            border-top-color: var(--gym-cream);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-login.loading .btn-text { display: none; }
        .btn-login.loading .spinner { display: inline-block; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.3);
            color: #c82333;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid rgba(40, 167, 69, 0.3);
            color: #1e7e34;
        }

        .alert i {
            font-size: 20px;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--gym-cream-dark);
        }

        .login-footer p {
            color: var(--gym-gray);
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
        }

        /* Shield decoration */
        .shield-icon {
            position: absolute;
            bottom: 30px;
            right: 30px;
            font-size: 60px;
            color: var(--gym-navy);
            opacity: 0.08;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .agora-panel {
                display: none;
            }
            
            .login-panel {
                width: 100%;
                min-height: 100vh;
            }

            .login-panel::before {
                display: none;
            }

            body {
                background: linear-gradient(180deg, var(--gym-cream-light) 0%, var(--gym-cream) 100%);
            }
        }

        @media (max-width: 480px) {
            .login-panel {
                padding: 30px 20px;
            }

            .agora-title {
                font-size: 2.5rem;
            }

            .agora-title span {
                font-size: 3rem;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Mobile logo */
        .mobile-logo {
            display: none;
            text-align: center;
            margin-bottom: 30px;
        }

        .mobile-logo img {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 8px 20px rgba(193, 64, 212, 0.4));
        }

        .mobile-logo h1 {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            color: var(--gym-navy);
            margin-top: 15px;
            letter-spacing: 3px;
        }

        .mobile-logo h1 span {
            color: var(--gym-purple);
        }

        @media (max-width: 1024px) {
            .mobile-logo {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Panel Izquierdo - Ágora Griega -->
    <div class="agora-panel">
        <!-- Vegetación Griega - Cipreses, Olivos y Arbustos -->
        <div class="foliage-container">
            <!-- Cipreses (árboles altos típicos griegos) -->
            <svg class="cypress cypress-1" viewBox="0 0 40 180">
                <ellipse cx="20" cy="90" rx="12" ry="85" fill="rgba(34, 60, 34, 0.7)"/>
                <ellipse cx="20" cy="85" rx="10" ry="80" fill="rgba(45, 80, 45, 0.8)"/>
                <ellipse cx="20" cy="80" rx="8" ry="75" fill="rgba(55, 95, 55, 0.6)"/>
                <rect x="17" y="165" width="6" height="15" fill="rgba(101, 67, 33, 0.8)"/>
            </svg>
            <svg class="cypress cypress-2" viewBox="0 0 35 160">
                <ellipse cx="17" cy="80" rx="10" ry="75" fill="rgba(34, 60, 34, 0.6)"/>
                <ellipse cx="17" cy="75" rx="8" ry="70" fill="rgba(45, 80, 45, 0.7)"/>
                <rect x="14" y="145" width="6" height="15" fill="rgba(101, 67, 33, 0.7)"/>
            </svg>
            <svg class="cypress cypress-3" viewBox="0 0 30 140">
                <ellipse cx="15" cy="70" rx="9" ry="65" fill="rgba(40, 70, 40, 0.5)"/>
                <ellipse cx="15" cy="65" rx="7" ry="60" fill="rgba(50, 85, 50, 0.6)"/>
                <rect x="12" y="125" width="5" height="12" fill="rgba(101, 67, 33, 0.6)"/>
            </svg>

            <!-- Arbustos de olivo -->
            <svg class="olive-bush olive-1" viewBox="0 0 80 50">
                <ellipse cx="40" cy="35" rx="35" ry="18" fill="rgba(85, 107, 47, 0.6)"/>
                <ellipse cx="30" cy="30" rx="25" ry="14" fill="rgba(107, 142, 35, 0.5)"/>
                <ellipse cx="55" cy="32" rx="20" ry="12" fill="rgba(85, 107, 47, 0.5)"/>
                <ellipse cx="40" cy="28" rx="18" ry="10" fill="rgba(128, 128, 0, 0.4)"/>
            </svg>
            <svg class="olive-bush olive-2" viewBox="0 0 70 45">
                <ellipse cx="35" cy="30" rx="30" ry="16" fill="rgba(85, 107, 47, 0.5)"/>
                <ellipse cx="25" cy="28" rx="20" ry="12" fill="rgba(107, 142, 35, 0.4)"/>
                <ellipse cx="48" cy="26" rx="18" ry="11" fill="rgba(85, 107, 47, 0.4)"/>
            </svg>
            <svg class="olive-bush olive-3" viewBox="0 0 60 40">
                <ellipse cx="30" cy="28" rx="25" ry="14" fill="rgba(75, 97, 37, 0.4)"/>
                <ellipse cx="25" cy="25" rx="18" ry="10" fill="rgba(95, 120, 45, 0.35)"/>
            </svg>
            <svg class="olive-bush olive-4" viewBox="0 0 65 42">
                <ellipse cx="32" cy="28" rx="28" ry="15" fill="rgba(85, 107, 47, 0.45)"/>
                <ellipse cx="40" cy="26" rx="20" ry="11" fill="rgba(107, 142, 35, 0.4)"/>
            </svg>

            <!-- Hierba decorativa -->
            <svg class="grass-patch grass-1" width="100" height="25" viewBox="0 0 100 25">
                <path d="M0,25 Q5,10 8,25 Q12,5 15,25 Q20,8 22,25 Q28,12 30,25 Q35,6 38,25 Q42,15 45,25 Q50,8 52,25 Q58,12 60,25 Q65,5 68,25 Q72,10 75,25 Q80,7 82,25 Q88,14 90,25 Q95,9 100,25 Z" fill="rgba(85, 107, 47, 0.3)"/>
            </svg>
            <svg class="grass-patch grass-2" width="80" height="20" viewBox="0 0 80 20">
                <path d="M0,20 Q5,8 8,20 Q12,3 15,20 Q20,10 22,20 Q28,5 30,20 Q35,12 38,20 Q42,4 45,20 Q50,9 52,20 Q58,6 60,20 Q65,11 68,20 Q72,3 75,20 Q78,8 80,20 Z" fill="rgba(75, 97, 37, 0.25)"/>
            </svg>
            <svg class="grass-patch grass-3" width="90" height="22" viewBox="0 0 90 22">
                <path d="M0,22 Q6,9 10,22 Q15,4 18,22 Q25,11 28,22 Q35,5 38,22 Q45,12 48,22 Q55,6 58,22 Q65,10 68,22 Q75,4 78,22 Q85,9 90,22 Z" fill="rgba(95, 120, 45, 0.25)"/>
            </svg>
            <svg class="grass-patch grass-4" width="75" height="18" viewBox="0 0 75 18">
                <path d="M0,18 Q5,6 8,18 Q14,3 16,18 Q22,8 25,18 Q32,4 35,18 Q42,10 45,18 Q52,5 55,18 Q62,9 65,18 Q70,4 75,18 Z" fill="rgba(85, 107, 47, 0.25)"/>
            </svg>
            <svg class="grass-patch grass-5" width="85" height="20" viewBox="0 0 85 20">
                <path d="M0,20 Q6,7 10,20 Q16,4 20,20 Q28,10 32,20 Q40,5 44,20 Q52,11 56,20 Q64,6 68,20 Q76,10 80,20 Q82,5 85,20 Z" fill="rgba(75, 97, 37, 0.3)"/>
            </svg>
        </div>

        <!-- Cielo Nocturno con Estrellas y Luna -->
        <div class="night-sky">
            <!-- Luna -->
            <div class="moon"></div>
            
            <!-- Estrellas -->
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
            <div class="star star-16"></div>
            <div class="star star-17"></div>
            <div class="star star-18"></div>
            <div class="star star-19"></div>
            <div class="star star-20"></div>
            <div class="star star-21"></div>
            <div class="star star-22"></div>
            <div class="star star-23"></div>
            <div class="star star-24"></div>
            <div class="star star-25"></div>
            <div class="star star-26"></div>
            <div class="star star-27"></div>
            <div class="star star-28"></div>
            <div class="star star-29"></div>
            <div class="star star-30"></div>
            <div class="star star-31"></div>
            <div class="star star-32"></div>
            <div class="star star-33"></div>
            <div class="star star-34"></div>
            <div class="star star-35"></div>
            
            <!-- Lluvia de estrellas cayendo en diagonal -->
            <div class="meteor meteor-1"></div>
            <div class="meteor meteor-2"></div>
            <div class="meteor meteor-3"></div>
            <div class="meteor meteor-4"></div>
            <div class="meteor meteor-5"></div>
            <div class="meteor meteor-6"></div>
            <div class="meteor meteor-7"></div>
            <div class="meteor meteor-8"></div>
            <div class="meteor meteor-9"></div>
            <div class="meteor meteor-10"></div>
            <div class="meteor meteor-11"></div>
            <div class="meteor meteor-12"></div>
            <div class="meteor meteor-13"></div>
            <div class="meteor meteor-14"></div>
            <div class="meteor meteor-15"></div>
            
            <!-- Partículas flotantes púrpuras -->
            <div class="particle particle-1"></div>
            <div class="particle particle-2"></div>
            <div class="particle particle-3"></div>
            <div class="particle particle-4"></div>
            <div class="particle particle-5"></div>
            <div class="particle particle-6"></div>
        </div>

        <!-- Templo Griego Majestuoso SVG -->
        <div class="temple-scene">
            <svg class="temple-svg" viewBox="0 0 500 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Base/Plataforma del templo (Crepidoma - 3 escalones) -->
                <rect x="20" y="285" width="460" height="15" fill="#f1e6bf"/>
                <rect x="30" y="275" width="440" height="12" fill="#d4c99a"/>
                <rect x="40" y="265" width="420" height="12" fill="#f1e6bf"/>
                
                <!-- Columnas Dóricas -->
                <!-- Columna 1 -->
                <rect x="55" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="50" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="48" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Columna 2 -->
                <rect x="120" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="115" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="113" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Columna 3 -->
                <rect x="185" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="180" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="178" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Columna 4 -->
                <rect x="280" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="275" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="273" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Columna 5 -->
                <rect x="345" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="340" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="338" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Columna 6 -->
                <rect x="410" y="95" width="35" height="170" fill="#f1e6bf"/>
                <rect x="405" y="85" width="45" height="15" fill="#d4c99a"/>
                <rect x="403" y="78" width="49" height="10" fill="#f1e6bf"/>
                
                <!-- Arquitrabe (viga horizontal) -->
                <rect x="35" y="60" width="430" height="20" fill="#d4c99a"/>
                <rect x="35" y="55" width="430" height="8" fill="#f1e6bf"/>
                
                <!-- Friso con triglifos y metopas -->
                <rect x="35" y="40" width="430" height="18" fill="#f1e6bf"/>
                <!-- Triglifos -->
                <rect x="60" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="120" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="180" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="240" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="300" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="360" y="42" width="15" height="14" fill="#d4c99a"/>
                <rect x="420" y="42" width="15" height="14" fill="#d4c99a"/>
                
                <!-- Cornisa -->
                <rect x="25" y="32" width="450" height="10" fill="#f1e6bf"/>
                
                <!-- Frontón (techo triangular) -->
                <polygon points="250,0 25,32 475,32" fill="#f1e6bf"/>
                <polygon points="250,8 40,30 460,30" fill="#d4c99a"/>
                
                <!-- Decoración central del frontón - Escudo estoico -->
                <circle cx="250" cy="20" r="8" fill="#c140d4" opacity="0.6"/>
                
                <!-- Acróteras (ornamentos del techo) -->
                <polygon points="250,-8 242,5 258,5" fill="#f1e6bf"/>
                <polygon points="30,28 22,38 38,38" fill="#f1e6bf"/>
                <polygon points="470,28 462,38 478,38" fill="#f1e6bf"/>
                
                <!-- Sombras sutiles en columnas -->
                <rect x="85" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
                <rect x="150" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
                <rect x="215" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
                <rect x="310" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
                <rect x="375" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
                <rect x="440" y="95" width="5" height="170" fill="#d4c99a" opacity="0.5"/>
            </svg>
        </div>

        <!-- Contenido central -->
        <div class="agora-content">
            <!-- Logo -->
            <div class="logo-wrapper">
                <img src="{{ asset('vendor/adminlte/dist/img/Logo_Estoicos_Gym.svg') }}" alt="Estoicos Gym" class="agora-logo">
            </div>
            
            <h1 class="agora-title">
                ESTOICOS
                <span>GYM</span>
            </h1>
            
            <p class="agora-subtitle">Fortaleza del Cuerpo y la Mente</p>

            <div class="stoic-quote">
                <p>"No es el hombre más fuerte quien sobrevive, sino el que mejor se adapta al cambio."</p>
                <cite>— Marco Aurelio</cite>
            </div>
        </div>
    </div>

    <!-- Panel Derecho - Formulario -->
    <div class="login-panel">
        <!-- Logo móvil -->
        <div class="mobile-logo">
            <img src="{{ asset('vendor/adminlte/dist/img/Logo_Estoicos_Gym.svg') }}" alt="Estoicos Gym">
            <h1>ESTOICOS <span>GYM</span></h1>
        </div>

        <div class="login-header">
            <h2>Bienvenido</h2>
            <p>Ingresa a tu panel de administración</p>
        </div>

        <!-- Alertas -->
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

        <!-- Formulario de Login -->
        <form method="POST" action="{{ route('login') }}" id="loginForm" class="login-form">
            @csrf

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-wrapper">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}"
                           placeholder="correo@estoicosgym.cl"
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
                <a href="{{ route('password.request') }}" class="forgot-password">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="login-footer">
            <p>© {{ date('Y') }} Estoicos Gym. Forjando guerreros.</p>
        </div>

        <!-- Shield decoration -->
        <i class="fas fa-shield-alt shield-icon"></i>
    </div>

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
        window.addEventListener('pageshow', function(event) {
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

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
