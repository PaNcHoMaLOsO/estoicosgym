@extends('layouts.acceso')

@section('titulo', 'Verificación')

@php
    $canal = $channel ?? 'whatsapp';
@endphp

@section('contenido')
    <div class="cabecera">
        <div class="icono"><i class="fas fa-shield-alt" aria-hidden="true"></i></div>
        <h1>Verificación</h1>
        <p>Ingresa el código de 6 dígitos que enviamos a tu teléfono.</p>
        <div class="telefono">
            {{-- «fab fa-sms» no existe: el de SMS está entre los iconos sólidos. --}}
            <i class="{{ $canal === 'sms' ? 'fas fa-comment-sms' : 'fab fa-whatsapp' }}" aria-hidden="true"></i>
            <span>{{ $maskedPhone ?? '***-***-***' }}</span>
            <span class="canal">· {{ $canal === 'sms' ? 'SMS' : 'WhatsApp' }}</span>
        </div>
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

    @if (app()->environment('local', 'development') && session('dev_2fa_code'))
        <div class="alerta alerta-dev">
            <i class="fas fa-bug" aria-hidden="true"></i>
            <span><strong>DEV MODE:</strong> Código: <strong>{{ session('dev_2fa_code') }}</strong></span>
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.verify') }}" id="verifyForm">
        @csrf
        <input type="hidden" name="user_id" value="{{ $userId ?? '' }}">
        <input type="hidden" name="type" value="{{ $type ?? 'login' }}">
        <input type="hidden" name="code" id="fullCode">

        <div class="codigo">
            <input type="text" class="code-input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" aria-label="Dígito 1" autofocus>
            <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]*" aria-label="Dígito 2">
            <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]*" aria-label="Dígito 3">
            <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]*" aria-label="Dígito 4">
            <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]*" aria-label="Dígito 5">
            <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]*" aria-label="Dígito 6">
        </div>

        <div class="reloj">
            <span class="timer" id="timer">
                <i class="fas fa-clock" aria-hidden="true"></i> Expira en <span id="countdown">10:00</span>
            </span>
            <a href="#" class="enlace resend-link disabled" id="resendLink" onclick="resendCode(event)">
                <i class="fas fa-redo" aria-hidden="true"></i> Reenviar código
            </a>
        </div>

        <button type="submit" class="boton" id="btnVerify" disabled>
            <span class="btn-text"><i class="fas fa-check-circle" aria-hidden="true"></i> Verificar código</span>
            <span class="spinner" aria-hidden="true"></span>
        </button>
    </form>

    <a href="{{ route('login') }}" class="enlace volver">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión
    </a>
@endsection

@section('scripts')
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
        form.addEventListener('submit', function (e) {
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
@endsection
