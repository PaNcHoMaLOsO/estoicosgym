@extends('adminlte::page')

@section('title', 'Editar Cliente - EstóicosGym')

@section('css')
    <style>
        /* ============================================
           VARIABLES Y ESTILOS BASE ELEGANTES
           ============================================ */
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #f59e0b;
            --accent-light: #fbbf24;
            --success: #10b981;
            --success-light: #34d399;
            --danger: #ef4444;
            --danger-light: #f87171;
            --warning: #f59e0b;
            --info: #06b6d4;
            --light: #f8fafc;
            --dark: #1e293b;
            --muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* ============================================
           ANIMACIONES ELEGANTES
           ============================================ */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============================================
           PÁGINA BASE
           ============================================ */
        .content-wrapper {
            background: linear-gradient(135deg, #f0f4ff 0%, #faf5ff 50%, #fff7ed 100%);
            min-height: 100vh;
        }

        /* ============================================
           HERO SECTION PREMIUM
           ============================================ */
        .hero-cliente {
            background: linear-gradient(135deg, var(--accent) 0%, #ea580c 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl), 0 0 40px rgba(245, 158, 11, 0.2);
            animation: slideInUp 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .hero-cliente::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            transform: translate(30%, -30%);
        }

        .hero-cliente::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: translate(-30%, 30%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-cliente h2 {
            margin: 0 0 0.75rem 0;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero-cliente h2 i {
            font-size: 2.5rem;
            animation: float 3s ease-in-out infinite;
        }

        .hero-cliente .hero-rut {
            opacity: 0.95;
            font-size: 1rem;
            margin: 0.5rem 0 0 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hero-cliente .hero-status {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            flex-wrap: wrap;
        }

        .state-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2.5rem;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            transition: all 0.3s ease;
        }

        .state-active {
            background: linear-gradient(135deg, #059669 0%, var(--success) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .state-active:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .state-inactive {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
        }

        .hero-member-since {
            margin-left: auto;
            font-size: 0.9rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            backdrop-filter: blur(10px);
        }

        /* ============================================
           CARD PRINCIPAL ELEGANTE
           ============================================ */
        .card {
            border: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-xl);
            border-radius: 1.5rem;
            overflow: hidden;
            animation: slideInUp 0.5s ease 0.1s both;
        }

        .card-header {
            background: linear-gradient(135deg, var(--accent) 0%, #ea580c 100%);
            border-bottom: none;
            color: white;
            padding: 1.75rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: translate(30%, -30%);
        }

        .card-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.35rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .card-header .badge {
            margin-left: auto;
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 2rem;
            backdrop-filter: blur(10px);
        }

        .card-body {
            padding: 2.5rem;
        }

        /* ============================================
           ALERTAS DE ERROR ELEGANTES
           ============================================ */
        .error-alert {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-left: 5px solid var(--danger);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            animation: slideDown 0.4s ease;
            box-shadow: var(--shadow-md);
        }

        .error-alert h5 {
            color: #b91c1c;
            margin: 0 0 1rem 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }

        .error-alert ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #dc2626;
            line-height: 1.8;
        }

        .error-alert li {
            margin-bottom: 0.4rem;
            font-weight: 500;
        }

        /* ============================================
           SECCIONES CON DISEÑO PREMIUM
           ============================================ */
        .section-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            margin: 2.5rem 0 1.5rem 0;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.05rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .section-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            transform: translate(20%, -20%);
        }

        .section-header i {
            font-size: 1.25em;
            flex-shrink: 0;
        }

        .section-header:first-of-type {
            margin-top: 0;
        }

        /* ============================================
           CONTROLES DE FORMULARIO PREMIUM
           ============================================ */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label .text-danger {
            color: var(--danger);
            font-weight: 700;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.85rem 1.15rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            outline: none;
        }

        .form-control:hover:not(:focus) {
            border-color: var(--primary-light);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background-image: none;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .form-control.is-invalid:focus {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .form-control.is-valid {
            border-color: var(--success);
            background-image: none;
        }

        .form-control.is-valid:focus {
            border-color: var(--success);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .invalid-feedback {
            color: var(--danger);
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .invalid-feedback::before {
            content: '\f071';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 0.75rem;
        }

        .form-text.text-muted {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Input con icono */
        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper .form-control {
            padding-left: 3rem;
        }

        .input-icon-wrapper .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-icon-wrapper:focus-within .input-icon {
            color: var(--primary);
        }

        /* ============================================
           INFORMACIÓN DE AUDITORÍA ELEGANTE
           ============================================ */
        .audit-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #7dd3fc;
            border-radius: 1rem;
            padding: 1.75rem;
            margin: 2.5rem 0;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .audit-info::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%);
        }

        .audit-info dt {
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .audit-info dd {
            margin-left: 0;
            color: #334155;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        .audit-info dd:last-child {
            margin-bottom: 0;
        }

        /* ============================================
           ESTADO ACTUAL DEL CLIENTE
           ============================================ */
        .client-state-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .client-state-box:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow-md);
        }

        .client-state-box .state-label {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .client-state-box .badge {
            font-size: 1rem;
            padding: 0.75rem 1.25rem;
            border-radius: 2rem;
        }

        /* ============================================
           BOTONES ELEGANTES
           ============================================ */
        .btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            white-space: nowrap;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--accent) 0%, #ea580c 100%);
            color: white;
        }

        .btn-warning:hover:not(:disabled) {
            background: linear-gradient(135deg, #d97706 0%, #c2410c 100%);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.35);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.35);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #4338ca 100%);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid #cbd5e1;
            color: var(--muted);
            background: white;
        }

        .btn-outline-secondary:hover:not(:disabled) {
            background: #f1f5f9;
            color: var(--dark);
            border-color: #94a3b8;
        }

        .btn-outline-info {
            border: 2px solid var(--info);
            color: var(--info);
            background: white;
        }

        .btn-outline-info:hover:not(:disabled) {
            background: var(--info);
            color: white;
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.25);
        }

        .btn-block {
            width: 100%;
        }

        .btn-lg-custom {
            padding: 1rem 2.5rem;
            font-size: 1.05rem;
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        /* ============================================
           CONTENEDOR DE BOTONES DE ACCIÓN PREMIUM
           ============================================ */
        .btn-actions {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding: 2rem 2.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 1.25rem;
            border: 2px solid #e2e8f0;
            box-shadow: var(--shadow-sm);
        }

        .btn-actions-left, .btn-actions-right {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn-actions-left {
            flex: 0 1 auto;
        }

        .btn-actions-right {
            flex: 1 1 auto;
            justify-content: flex-end;
        }

        /* ============================================
           SECCIÓN DE ESTADO DEL CLIENTE
           ============================================ */
        .state-actions-section {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-radius: 1rem;
            padding: 2rem;
            margin: 2.5rem 0;
            border: 2px solid #fcd34d;
            box-shadow: var(--shadow-sm);
        }

        .state-actions-section .btn {
            width: 100%;
        }

        /* ============================================
           CAMBIOS NO GUARDADOS
           ============================================ */
        .unsaved-indicator {
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 700;
            display: none;
            animation: pulse 1.5s infinite;
            background: rgba(245, 158, 11, 0.15);
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
        }

        .unsaved-indicator i {
            margin-right: 0.35rem;
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        @media (max-width: 768px) {
            .hero-cliente {
                padding: 1.75rem;
            }

            .hero-cliente h2 {
                font-size: 1.5rem;
                flex-wrap: wrap;
            }

            .hero-cliente h2 i {
                font-size: 2rem;
            }

            .hero-status {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .hero-member-since {
                margin-left: 0 !important;
                margin-top: 1rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .btn-actions {
                flex-direction: column;
                padding: 1.5rem;
            }

            .btn-actions-left, .btn-actions-right {
                width: 100%;
                justify-content: stretch;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .section-header {
                margin: 2rem 0 1rem 0;
                font-size: 0.95rem;
                padding: 0.85rem 1rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control {
                padding: 0.75rem 1rem;
            }

            .audit-info {
                padding: 1.25rem;
            }

            .audit-info dt,
            .audit-info dd {
                font-size: 0.9rem;
            }
        }

        /* ============================================
           MODO IMPRESIÓN
           ============================================ */
        @media print {
            .btn-actions, .state-actions-section {
                display: none;
            }

            .card, .hero-cliente {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }

            .section-header {
                background: #f1f5f9;
                color: var(--dark);
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* ============================================
           EFECTOS ADICIONALES PREMIUM
           ============================================ */
        .row {
            animation: fadeIn 0.4s ease;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Tooltip personalizado */
        [data-tooltip] {
            position: relative;
        }

        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 100;
            animation: fadeIn 0.2s ease;
        }
    </style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 1rem;">
        <div>
            <h1 class="m-0 d-flex align-items-center" style="gap: 0.75rem; font-weight: 700; color: #1e293b;">
                <span style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); padding: 0.5rem; border-radius: 0.75rem; display: inline-flex;">
                    <i class="fas fa-user-edit" style="color: white; font-size: 1.5rem;"></i>
                </span>
                Editar Cliente
            </h1>
            <p class="text-muted mt-2 mb-0" style="font-size: 0.95rem;">
                <i class="fas fa-pencil-alt"></i> Actualiza la información del cliente
            </p>
        </div>
        <div class="d-flex" style="gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline-info" title="Ver detalles del cliente">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary" title="Volver al listado">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- ALERTAS DE ERROR --}}
    @if ($errors->any())
        <div class="error-alert">
            <h5>
                <i class="fas fa-exclamation-triangle"></i> 
                {{ $errors->count() }} Error{{ $errors->count() > 1 ? 'es' : '' }} en el Formulario
            </h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- HERO CON DATOS DEL CLIENTE --}}
    <div class="hero-cliente">
        <div class="hero-content">
            <h2>
                <i class="fas fa-user-circle"></i>
                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno ?? '' }}
            </h2>
            <div class="hero-rut">
                <i class="fas fa-id-card"></i> RUT/Pasaporte: <strong>{{ $cliente->run_pasaporte ?? 'No registrado' }}</strong>
            </div>
            <div class="hero-status">
                <span class="state-badge {{ $cliente->activo ? 'state-active' : 'state-inactive' }}">
                    <i class="fas {{ $cliente->activo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                    {{ $cliente->activo ? 'Cliente Activo' : 'Cliente Inactivo' }}
                </span>
                <span class="hero-member-since">
                    <i class="fas fa-calendar-alt"></i> 
                    Miembro desde {{ $cliente->created_at->format('d M, Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- TARJETA PRINCIPAL CON FORMULARIO --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="flex-grow: 1;">
                <i class="fas fa-user-edit"></i> Información del Cliente
            </h3>
            <div class="card-tools d-flex align-items-center" style="gap: 0.75rem;">
                <span class="badge">
                    <i class="fas fa-hashtag"></i> ID: {{ $cliente->id }}
                </span>
                <span class="unsaved-indicator" id="unsaved-indicator">
                    <i class="fas fa-circle"></i> Cambios sin guardar
                </span>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" id="editClienteForm" autocomplete="off" onsubmit="return handleEditFormSubmit(event)">
                @csrf
                @method('PUT')

                {{-- Token anti-CSRF y detección de cambios --}}
                <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
                <input type="hidden" id="initial_data" value="">

                {{-- ===== SECCIÓN: IDENTIFICACIÓN ===== --}}
                <div class="section-header">
                    <i class="fas fa-id-card"></i> Identificación Personal
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="run_pasaporte" class="form-label">
                            <i class="fas fa-fingerprint text-muted"></i> RUT/Pasaporte
                        </label>
                        <input type="text" 
                               class="form-control @error('run_pasaporte') is-invalid @enderror" 
                               id="run_pasaporte" 
                               name="run_pasaporte" 
                               placeholder="Ej: 12.345.678-9" 
                               value="{{ old('run_pasaporte', $cliente->run_pasaporte) }}"
                               onblur="validarRutAjax(this)">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Campo opcional - Formato chileno o pasaporte
                        </small>
                        @error('run_pasaporte')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="fecha_nacimiento" class="form-label">
                            <i class="fas fa-birthday-cake text-muted"></i> Fecha de Nacimiento
                        </label>
                        <input type="date" 
                               class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                               id="fecha_nacimiento" 
                               name="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}">
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== SECCIÓN: DATOS PERSONALES ===== --}}
                <div class="section-header">
                    <i class="fas fa-user"></i> Datos Personales
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label for="nombres" class="form-label">
                            <i class="fas fa-user-tag text-muted"></i> Nombres <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nombres') is-invalid @enderror" 
                               id="nombres" 
                               name="nombres" 
                               placeholder="Ej: Juan Pablo"
                               value="{{ old('nombres', $cliente->nombres) }}" 
                               required>
                        @error('nombres')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="apellido_paterno" class="form-label">
                            <i class="fas fa-user text-muted"></i> Apellido Paterno <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('apellido_paterno') is-invalid @enderror" 
                               id="apellido_paterno" 
                               name="apellido_paterno" 
                               placeholder="Ej: González"
                               value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}" 
                               required>
                        @error('apellido_paterno')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="apellido_materno" class="form-label">
                            <i class="fas fa-user text-muted"></i> Apellido Materno
                        </label>
                        <input type="text" 
                               class="form-control @error('apellido_materno') is-invalid @enderror" 
                               id="apellido_materno" 
                               name="apellido_materno" 
                               placeholder="Ej: Pérez"
                               value="{{ old('apellido_materno', $cliente->apellido_materno) }}">
                        @error('apellido_materno')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== SECCIÓN: CONTACTO ===== --}}
                <div class="section-header">
                    <i class="fas fa-address-book"></i> Información de Contacto
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope text-muted"></i> Correo Electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               placeholder="ejemplo@correo.com" 
                               value="{{ old('email', $cliente->email) }}" 
                               required
                               onblur="validarEmail(this)">
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="celular" class="form-label">
                            <i class="fas fa-mobile-alt text-muted"></i> Teléfono Celular <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control @error('celular') is-invalid @enderror" 
                               id="celular" 
                               name="celular" 
                               placeholder="+56 9 1234 5678" 
                               value="{{ old('celular', $cliente->celular) }}" 
                               required>
                        @error('celular')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== SECCIÓN: CONTACTO DE EMERGENCIA ===== --}}
                <div class="section-header">
                    <i class="fas fa-heart"></i> Contacto de Emergencia
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="contacto_emergencia" class="form-label">
                            <i class="fas fa-user-friends text-muted"></i> Nombre del Contacto
                        </label>
                        <input type="text" 
                               class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                               id="contacto_emergencia" 
                               name="contacto_emergencia" 
                               placeholder="Ej: María González" 
                               value="{{ old('contacto_emergencia', $cliente->contacto_emergencia) }}">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Persona a contactar en caso de emergencia
                        </small>
                        @error('contacto_emergencia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="telefono_emergencia" class="form-label">
                            <i class="fas fa-phone-alt text-muted"></i> Teléfono de Emergencia
                        </label>
                        <input type="tel" 
                               class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                               id="telefono_emergencia" 
                               name="telefono_emergencia" 
                               placeholder="+56 9 8765 4321" 
                               value="{{ old('telefono_emergencia', $cliente->telefono_emergencia) }}">
                        @error('telefono_emergencia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== SECCIÓN: DOMICILIO ===== --}}
                <div class="section-header">
                    <i class="fas fa-home"></i> Dirección de Domicilio
                </div>
                <div class="row">
                    <div class="col-12 mb-4">
                        <label for="direccion" class="form-label">
                            <i class="fas fa-map-marker-alt text-muted"></i> Dirección Completa
                        </label>
                        <input type="text" 
                               class="form-control @error('direccion') is-invalid @enderror" 
                               id="direccion" 
                               name="direccion" 
                               placeholder="Ej: Av. Providencia 1234, Depto 501, Providencia" 
                               value="{{ old('direccion', $cliente->direccion) }}">
                        @error('direccion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== SECCIÓN: OBSERVACIONES ===== --}}
                <div class="section-header">
                    <i class="fas fa-comment-alt"></i> Notas y Observaciones
                </div>
                <div class="row">
                    <div class="col-12 mb-4">
                        <label for="observaciones" class="form-label">
                            <i class="fas fa-pencil-alt text-muted"></i> Información Adicional
                        </label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" 
                                  name="observaciones" 
                                  rows="4" 
                                  placeholder="Escribe aquí cualquier nota importante sobre el cliente: condiciones médicas, preferencias, restricciones, etc...">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="form-text text-muted">
                                <i class="fas fa-lightbulb"></i> Estas notas son visibles solo para el personal administrativo
                            </small>
                            <small class="text-muted" id="char-count" style="font-weight: 600;">
                                0 caracteres
                            </small>
                        </div>
                        @error('observaciones')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ===== INFORMACIÓN DE AUDITORÍA ===== --}}
                <div class="audit-info">
                    <div class="row">
                        <div class="col-md-4">
                            <dt>
                                <i class="fas fa-user-plus"></i> Fecha de Registro
                            </dt>
                            <dd>{{ $cliente->created_at->format('d \\d\\e F, Y') }}</dd>
                        </div>
                        <div class="col-md-4">
                            <dt>
                                <i class="fas fa-clock"></i> Hora de Registro
                            </dt>
                            <dd>{{ $cliente->created_at->format('H:i:s') }} hrs</dd>
                        </div>
                        <div class="col-md-4">
                            <dt>
                                <i class="fas fa-sync-alt"></i> Última Actualización
                            </dt>
                            <dd>{{ $cliente->updated_at->diffForHumans() }}</dd>
                        </div>
                    </div>
                </div>

                {{-- ===== SECCIÓN: ESTADO DEL CLIENTE ===== --}}
                <div class="section-header">
                    <i class="fas fa-power-off"></i> Gestión del Estado
                </div>

                <div class="client-state-box">
                    <div class="state-label">
                        <i class="fas fa-signal"></i> Estado Actual del Cliente
                    </div>
                    @if($cliente->activo)
                        <span class="badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; font-size: 1rem; padding: 0.85rem 1.5rem; border-radius: 2rem;">
                            <i class="fas fa-check-circle"></i> CLIENTE ACTIVO
                        </span>
                        <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> El cliente puede acceder a las instalaciones y servicios del gimnasio
                        </p>
                    @else
                        <span class="badge" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white; font-size: 1rem; padding: 0.85rem 1.5rem; border-radius: 2rem;">
                            <i class="fas fa-times-circle"></i> CLIENTE INACTIVO
                        </span>
                        <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i> El cliente no tiene acceso activo a las instalaciones
                        </p>
                    @endif
                </div>

                <div class="state-actions-section">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            @if($cliente->activo)
                                <button type="button" 
                                        class="btn btn-danger btn-block btn-lg-custom" 
                                        id="btn-desactivar-cliente"
                                        data-cliente-id="{{ $cliente->id }}"
                                        data-cliente-nombre="{{ addslashes($cliente->nombres) }}">
                                    <i class="fas fa-user-slash"></i> Desactivar Cliente
                                </button>
                                <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.85rem;">
                                    El cliente perderá el acceso hasta ser reactivado
                                </p>
                            @else
                                <form method="POST" 
                                      action="{{ route('admin.clientes.reactivate', $cliente->id) }}" 
                                      style="display: inline; width: 100%;"
                                      onsubmit="return confirmarReactivacion(event)">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-block btn-lg-custom">
                                        <i class="fas fa-user-check"></i> Reactivar Cliente
                                    </button>
                                    <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.85rem;">
                                        Restaurar acceso completo al cliente
                                    </p>
                                </form>
                            @endif
                        </div>
                        <div class="col-md-6 d-none d-md-block text-center">
                            <div style="font-size: 4rem; opacity: 0.15;">
                                <i class="fas {{ $cliente->activo ? 'fa-user-check' : 'fa-user-times' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== BOTONES DE ACCIÓN ===== --}}
                <div class="btn-actions">
                    <div class="btn-actions-left">
                        <a href="{{ route('admin.clientes.index') }}" 
                           class="btn btn-outline-secondary btn-lg-custom" 
                           onclick="return confirmarCancelar(event)"
                           title="Volver sin guardar cambios">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <a href="{{ route('admin.clientes.show', $cliente) }}" 
                           class="btn btn-outline-info btn-lg-custom d-none d-md-inline-flex" 
                           title="Ver perfil completo">
                            <i class="fas fa-eye"></i> Ver Perfil
                        </a>
                    </div>
                    <div class="btn-actions-right">
                        <button type="button" 
                                class="btn btn-outline-secondary btn-lg-custom d-none d-md-inline-flex"
                                id="btn-restaurar"
                                title="Restaurar valores originales">
                            <i class="fas fa-undo"></i> Restaurar
                        </button>
                        <button type="submit" 
                                class="btn btn-warning btn-lg-custom" 
                                id="btn-guardar-cambios"
                                title="Guardar cambios en la base de datos">
                            <i class="fas fa-save"></i> 
                            <span id="btn-text">Guardar Cambios</span>
                            <span id="btn-spinner" style="display: none; margin-left: 0.5rem;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
{{-- Cargar SweetAlert2 desde CDN por si no está en el layout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    {{-- ===== VERIFICAR QUE SWEETALERT2 ESTÁ DISPONIBLE ===== --}}
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado correctamente');
    }

    {{-- ===== DETECCIÓN DE CAMBIOS ===== --}}
    let formDataInicial = {};
    let haysCambios = false;

    // Capturar datos iniciales al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editClienteForm');
        formDataInicial = captureFormData(form);
        
        // Escuchar cambios en todos los inputs
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('change', detailsFormChange);
            field.addEventListener('keyup', detailsFormChange);
        });

        {{-- ===== BOTÓN DESACTIVAR CLIENTE ===== --}}
        const btnDesactivar = document.getElementById('btn-desactivar-cliente');
        if (btnDesactivar) {
            btnDesactivar.addEventListener('click', function() {
                const clienteId = this.dataset.clienteId;
                const clienteNombre = this.dataset.clienteNombre;
                confirmarDesactivacion(clienteId, clienteNombre);
            });
        }

        {{-- ===== BOTÓN RESTAURAR ===== --}}
        const btnRestaurar = document.getElementById('btn-restaurar');
        if (btnRestaurar) {
            btnRestaurar.addEventListener('click', function() {
                confirmarRestaurar();
            });
        }

        // Contar caracteres en observaciones
        const obsField = document.getElementById('observaciones');
        if(obsField) {
            obsField.addEventListener('keyup', function() {
                document.getElementById('char-count').textContent = this.value.length + ' caracteres';
            });
            document.getElementById('char-count').textContent = obsField.value.length + ' caracteres';
        }

        // Advertencia al abandonar con cambios sin guardar
        window.addEventListener('beforeunload', function(e) {
            if(haysCambios) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });

    function captureFormData(form) {
        const data = {};
        form.querySelectorAll('input, textarea, select').forEach(field => {
            if(field.id) {
                data[field.id] = field.value;
            }
        });
        return data;
    }

    function detailsFormChange() {
        const form = document.getElementById('editClienteForm');
        const currentData = captureFormData(form);
        haysCambios = JSON.stringify(formDataInicial) !== JSON.stringify(currentData);
        
        const indicator = document.getElementById('unsaved-indicator');
        if(indicator) {
            if(haysCambios) {
                indicator.style.display = 'inline-block';
                indicator.style.color = '#ffa500';
            } else {
                indicator.style.display = 'none';
            }
        }
    }

    {{-- ===== VALIDACIONES ===== --}}

    // Validar email en tiempo real
    function validarEmail(input) {
        const email = input.value.trim();
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if(!email) {
            input.classList.remove('is-invalid', 'is-valid');
            return true;
        }

        if(regexEmail.test(email)) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            return true;
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            return false;
        }
    }

    // Validar RUT con AJAX
    function validarRutAjax(input) {
        const rut = input.value.trim();
        
        if(!rut) {
            input.classList.remove('is-invalid', 'is-valid');
            return;
        }

        // Validación básica de formato RUT
        const rutRegex = /^(\d{1,2}\.)?\d{3}\.\d{3}-[0-9kK]$|^\d+$/;
        if(!rutRegex.test(rut)) {
            input.classList.add('is-invalid');
            return;
        }

        input.classList.add('is-valid');
    }

    // Validar campos requeridos
    function validarCamposRequeridos() {
        const camposRequeridos = ['nombres', 'apellido_paterno', 'email', 'celular'];
        let errores = [];

        camposRequeridos.forEach(id => {
            const campo = document.getElementById(id);
            if(campo && !campo.value.trim()) {
                campo.classList.add('is-invalid');
                errores.push(`${campo.previousElementSibling.textContent || id} es requerido`);
            } else if(campo) {
                campo.classList.remove('is-invalid');
            }
        });

        // Validar email
        const email = document.getElementById('email');
        if(email && email.value.trim()) {
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!regexEmail.test(email.value)) {
                email.classList.add('is-invalid');
                errores.push('El email no es válido');
            }
        }

        return errores;
    }

    {{-- ===== MANEJADOR DEL FORMULARIO ===== --}}

    let formSubmitInProgress = false;

    function handleEditFormSubmit(event) {
        event.preventDefault();

        if(formSubmitInProgress) {
            mostrarErrorValidacion('Por favor espere, el formulario se está enviando...');
            return false;
        }

        // Validar campos requeridos
        const errores = validarCamposRequeridos();
        if(errores.length > 0) {
            mostrarErrorValidacion('Por favor corrija los siguientes errores:\n\n' + errores.join('\n'));
            return false;
        }

        // Mostrar confirmación
        confirmarGuardiarCambios(event);
        return false;
    }

    {{-- ===== ALERTAS CON SWEETALERT2 PREMIUM ===== --}}

    // 1. Confirmación para guardar cambios
    function confirmarGuardiarCambios(event) {
        Swal.fire({
            title: '💾 ¿Guardar cambios?',
            html: `
                <div style="text-align: left; padding: 1rem; background: #f8fafc; border-radius: 0.75rem; margin-top: 1rem;">
                    <p style="margin: 0; color: #475569;">
                        <i class="fas fa-info-circle" style="color: #6366f1;"></i> 
                        Se actualizarán los datos del cliente en la base de datos.
                    </p>
                </div>
            `,
            icon: 'question',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar Cambios',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-warning btn-lg mx-2',
                cancelButton: 'btn btn-outline-secondary btn-lg mx-2'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarLoadingState();
                document.getElementById('editClienteForm').submit();
            }
        });
    }

    // 2. Confirmación para desactivar
    function confirmarDesactivacion(clienteId, clienteNombre) {
        Swal.fire({
            title: '⚠️ ¿Desactivar cliente?',
            html: `
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.7;">👤</div>
                    <p style="font-weight: 700; font-size: 1.2rem; color: #1e293b; margin-bottom: 0.5rem;">${clienteNombre}</p>
                    <p style="color: #64748b; margin: 0;">El cliente perderá el acceso a las instalaciones</p>
                </div>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; text-align: left;">
                    <small style="color: #991b1b;"><i class="fas fa-exclamation-triangle"></i> Esta acción puede revertirse posteriormente</small>
                </div>
            `,
            icon: 'warning',
            iconColor: '#ef4444',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-user-slash"></i> Sí, Desactivar',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-danger btn-lg mx-2',
                cancelButton: 'btn btn-outline-secondary btn-lg mx-2'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarLoadingState();
                // Crear formulario dinámico para enviar la petición
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/clientes/${clienteId}/desactivar`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PATCH">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 3. Confirmación para reactivar
    function confirmarReactivacion(event) {
        event.preventDefault();
        
        Swal.fire({
            title: '✅ ¿Reactivar cliente?',
            html: `
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                    <p style="color: #475569; margin: 0;">El cliente recuperará el acceso completo a las instalaciones del gimnasio.</p>
                </div>
            `,
            icon: 'question',
            iconColor: '#10b981',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-user-check"></i> Sí, Reactivar',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-success btn-lg mx-2',
                cancelButton: 'btn btn-outline-secondary btn-lg mx-2'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarLoadingState();
                event.target.submit();
            }
        });
        
        return false;
    }

    // 4. Confirmación para cancelar
    function confirmarCancelar(event) {
        if(!haysCambios) {
            return true;
        }

        event.preventDefault();
        
        Swal.fire({
            title: '🚪 ¿Salir sin guardar?',
            html: `
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; text-align: left;">
                    <p style="margin: 0; color: #92400e;"><i class="fas fa-exclamation-triangle"></i> Tienes cambios sin guardar que se perderán si sales ahora.</p>
                </div>
            `,
            icon: 'warning',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Salir sin guardar',
            cancelButtonText: '<i class="fas fa-edit"></i> Seguir editando',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-danger btn-lg mx-2',
                cancelButton: 'btn btn-outline-secondary btn-lg mx-2'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("admin.clientes.index") }}';
            }
        });
        
        return false;
    }

    // 5. Confirmación para restaurar valores originales
    function confirmarRestaurar() {
        Swal.fire({
            title: '🔄 ¿Restaurar valores?',
            html: `
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">↩️</div>
                    <p style="color: #475569; margin: 0;">Todos los campos volverán a sus valores originales.</p>
                </div>
                <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; text-align: left;">
                    <small style="color: #0369a1;"><i class="fas fa-info-circle"></i> Los cambios no guardados se perderán</small>
                </div>
            `,
            icon: 'question',
            iconColor: '#0ea5e9',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, Restaurar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-primary btn-lg mx-2',
                cancelButton: 'btn btn-outline-secondary btn-lg mx-2'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Resetear el formulario
                document.getElementById('editClienteForm').reset();
                // Actualizar el indicador de cambios
                haysCambios = false;
                const indicator = document.getElementById('unsaved-indicator');
                if(indicator) {
                    indicator.style.display = 'none';
                }
                // Actualizar contador de caracteres
                const obsField = document.getElementById('observaciones');
                if(obsField) {
                    document.getElementById('char-count').textContent = obsField.value.length + ' caracteres';
                }
                // Mostrar mensaje de éxito
                Swal.fire({
                    title: '✅ Valores restaurados',
                    html: '<p style="color: #475569;">Los campos han vuelto a sus valores originales.</p>',
                    icon: 'success',
                    iconColor: '#10b981',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal2-popup-custom',
                        confirmButton: 'btn btn-success btn-lg'
                    },
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        });
    }

    {{-- ===== FUNCIONES DE ALERTA REUTILIZABLES ===== --}}

    // Error de validación
    function mostrarErrorValidacion(mensaje) {
        Swal.fire({
            title: '❌ Errores de Validación',
            html: `
                <div style="text-align: left; padding: 1rem; background: #fef2f2; border-radius: 0.75rem; margin-top: 1rem;">
                    <p style="margin: 0; color: #991b1b; white-space: pre-line;">${mensaje}</p>
                </div>
            `,
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-check"></i> Entendido',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-primary btn-lg'
            }
        });

        // Scroll al primer error
        const firstError = document.querySelector('.is-invalid');
        if(firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }

    // Estado de carga
    function mostrarLoadingState() {
        Swal.fire({
            title: 'Procesando...',
            html: `
                <div style="padding: 2rem;">
                    <div style="width: 60px; height: 60px; border: 4px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    <p style="margin-top: 1.5rem; color: #64748b;">Por favor espere un momento...</p>
                </div>
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'swal2-popup-custom'
            }
        });
    }

    // Éxito
    function mostrarExitoAlerta(titulo, redirigir = null) {
        Swal.fire({
            title: '✅ ' + titulo,
            html: `
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎊</div>
                    <p style="color: #64748b; margin: 0;">La operación se completó exitosamente</p>
                </div>
            `,
            icon: 'success',
            iconColor: '#10b981',
            confirmButtonText: '<i class="fas fa-check"></i> Continuar',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-success btn-lg'
            },
            timer: 2500,
            timerProgressBar: true
        }).then(() => {
            if(redirigir) {
                window.location.href = redirigir;
            } else {
                location.reload();
            }
        });
    }

    // Error general
    function mostrarErrorAlerta(titulo, mensaje) {
        Swal.fire({
            title: '❌ ' + titulo,
            html: `
                <div style="text-align: left; padding: 1rem; background: #fef2f2; border-radius: 0.75rem; margin-top: 1rem;">
                    <p style="margin: 0; color: #991b1b;">${mensaje}</p>
                </div>
            `,
            icon: 'error',
            iconColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-check"></i> Entendido',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-popup-custom',
                confirmButton: 'btn btn-primary btn-lg'
            }
        });
    }
</script>
@endpush