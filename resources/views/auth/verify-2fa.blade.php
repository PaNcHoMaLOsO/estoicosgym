<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificación | Estoicos Gym</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800&family=Philosopher:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Philosopher', serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gym-navy-dark) 0%, var(--gym-navy) 50%, var(--gym-navy-light) 100%);
            padding: 20px;
        }

        .verify-container {
            width: 100%;
            max-width: 450px;
        }

        .verify-card {
            background: var(--gym-cream-light);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .verify-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--gym-purple) 0%, var(--gym-navy) 50%, var(--gym-purple) 100%);
        }

        .header-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .icon-circle {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--gym-navy) 0%, var(--gym-navy-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 30px rgba(37, 58, 91, 0.4);
        }

        .icon-circle i {
            font-size: 40px;
            color: var(--gym-cream);
        }

        .header-section h1 {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gym-navy);
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .header-section p {
            font-family: 'Poppins', sans-serif;
            color: var(--gym-gray);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .phone-display {
            background: var(--gym-cream);
            border: 2px solid var(--gym-cream-dark);
            border-radius: 10px;
            padding: 12px 20px;
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .phone-display i {
            color: var(--gym-purple);
        }

        .phone-display span {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--gym-navy);
            letter-spacing: 1px;
        }

        /* Code inputs */
        .code-inputs {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 30px 0;
        }

        .code-input {
            width: 55px;
            height: 65px;
            text-align: center;
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gym-navy);
            background: white;
            border: 3px solid var(--gym-cream-dark);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--gym-purple);
            box-shadow: 0 0 0 4px rgba(193, 64, 212, 0.15);
            transform: scale(1.05);
        }

        .code-input.filled {
            border-color: var(--gym-navy);
            background: var(--gym-cream-light);
        }

        .code-input.error {
            border-color: #dc3545;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        /* Timer */
        .timer-section {
            text-align: center;
            margin: 25px 0;
        }

        .timer {
            font-family: 'Cinzel', serif;
            font-size: 1.3rem;
            color: var(--gym-navy);
            font-weight: 600;
        }

        .timer.expired {
            color: #dc3545;
        }

        .resend-link {
            display: inline-block;
            margin-top: 10px;
            color: var(--gym-purple);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .resend-link:hover {
            color: var(--gym-purple-dark);
            text-decoration: underline;
        }

        .resend-link.disabled {
            color: var(--gym-gray);
            pointer-events: none;
            opacity: 0.5;
        }

        /* Button */
        .btn-verify {
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
            margin-top: 20px;
        }

        .btn-verify:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(37, 58, 91, 0.4);
            background: linear-gradient(135deg, var(--gym-purple) 0%, var(--gym-purple-dark) 100%);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-verify .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(241, 230, 191, 0.3);
            border-top-color: var(--gym-cream);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-verify.loading .btn-text { display: none; }
        .btn-verify.loading .spinner { display: inline-block; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Alerts */
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

        /* Back link */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--gym-gray);
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--gym-purple);
        }

        /* Channel icon */
        .channel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--gym-navy);
            color: var(--gym-cream);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
        }

        .channel-badge.whatsapp {
            background: #25D366;
        }

        .channel-badge.sms {
            background: var(--gym-purple);
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="header-section">
                <div class="icon-circle">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Verificación</h1>
                <p>Ingresa el código de 6 dígitos que enviamos a tu teléfono</p>
                
                <div class="phone-display">
                    <i class="fab fa-{{ $channel ?? 'whatsapp' }}"></i>
                    <span>{{ $maskedPhone ?? '***-***-***' }}</span>
                </div>
                
                <span class="channel-badge {{ $channel ?? 'whatsapp' }}">
                    <i class="fab fa-{{ $channel ?? 'whatsapp' }}"></i>
                    {{ $channel === 'sms' ? 'SMS' : 'WhatsApp' }}
                </span>
            </div>

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

            @if (app()->environment('local', 'development') && session('dev_2fa_code'))
                <div class="alert alert-success" style="background: rgba(193, 64, 212, 0.1); border-color: rgba(193, 64, 212, 0.3); color: #c140d4;">
                    <i class="fas fa-bug"></i>
                    <span><strong>DEV MODE:</strong> Código: <strong>{{ session('dev_2fa_code') }}</strong></span>
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" id="verifyForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $userId ?? '' }}">
                <input type="hidden" name="type" value="{{ $type ?? 'login' }}">
                <input type="hidden" name="code" id="fullCode">

                <div class="code-inputs">
                    <input type="text" class="code-input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]*" autofocus>
                    <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]*">
                    <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]*">
                    <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]*">
                    <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]*">
                    <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]*">
                </div>

                <div class="timer-section">
                    <div class="timer" id="timer">
                        <i class="fas fa-clock"></i> Expira en <span id="countdown">10:00</span>
                    </div>
                    <a href="#" class="resend-link disabled" id="resendLink" onclick="resendCode(event)">
                        <i class="fas fa-redo"></i> Reenviar código
                    </a>
                </div>

                <button type="submit" class="btn-verify" id="btnVerify" disabled>
                    <span class="btn-text">
                        <i class="fas fa-check-circle"></i> Verificar Código
                    </span>
                    <div class="spinner"></div>
                </button>
            </form>

            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
            </a>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.code-input');
        const fullCodeInput = document.getElementById('fullCode');
        const btnVerify = document.getElementById('btnVerify');
        const form = document.getElementById('verifyForm');
        
        // Timer
        let timeLeft = {{ $expiresIn ?? 600 }}; // segundos
        const countdownEl = document.getElementById('countdown');
        const timerEl = document.getElementById('timer');
        const resendLink = document.getElementById('resendLink');

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                timerEl.classList.add('expired');
                countdownEl.textContent = 'Código expirado';
                resendLink.classList.remove('disabled');
                inputs.forEach(input => input.disabled = true);
                btnVerify.disabled = true;
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }
        updateTimer();

        // Code input handling
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = value;

                if (value) {
                    e.target.classList.add('filled');
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }

                updateFullCode();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                
                pastedData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('filled');
                    }
                });

                if (pastedData.length > 0) {
                    inputs[Math.min(pastedData.length, 5)].focus();
                }

                updateFullCode();
            });
        });

        function updateFullCode() {
            const code = Array.from(inputs).map(input => input.value).join('');
            fullCodeInput.value = code;
            btnVerify.disabled = code.length !== 6;
        }

        // Form submit
        form.addEventListener('submit', function(e) {
            if (fullCodeInput.value.length !== 6) {
                e.preventDefault();
                inputs.forEach(input => input.classList.add('error'));
                setTimeout(() => inputs.forEach(input => input.classList.remove('error')), 500);
                return;
            }
            
            btnVerify.classList.add('loading');
            btnVerify.disabled = true;
        });

        // Resend code
        function resendCode(e) {
            e.preventDefault();
            if (resendLink.classList.contains('disabled')) return;
            
            resendLink.classList.add('disabled');
            resendLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            fetch('{{ route("2fa.resend") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    user_id: '{{ $userId ?? "" }}',
                    type: '{{ $type ?? "login" }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeLeft = 600;
                    timerEl.classList.remove('expired');
                    inputs.forEach(input => {
                        input.disabled = false;
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    inputs[0].focus();
                    resendLink.innerHTML = '<i class="fas fa-check"></i> Código reenviado';
                    setTimeout(() => {
                        resendLink.innerHTML = '<i class="fas fa-redo"></i> Reenviar código';
                    }, 3000);
                } else {
                    alert(data.message || 'Error al reenviar el código');
                    resendLink.classList.remove('disabled');
                    resendLink.innerHTML = '<i class="fas fa-redo"></i> Reenviar código';
                }
            })
            .catch(() => {
                alert('Error de conexión');
                resendLink.classList.remove('disabled');
                resendLink.innerHTML = '<i class="fas fa-redo"></i> Reenviar código';
            });
        }
    </script>
</body>
</html>
