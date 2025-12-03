@extends('adminlte::page')

@section('title', 'Editar Cliente - EstóicosGym')

@section('css')
    <style>
        /* ============================================
           VARIABLES - PALETA DE COLORES UNIFICADA
           ============================================ */
        :root {
            --primary: #1a1a2e;
            --primary-light: #16213e;
            --primary-dark: #0f0f1a;
            --accent: #e94560;
            --accent-light: #f06e85;
            --success: #00bf8e;
            --success-light: #00d6a0;
            --danger: #dc3545;
            --warning: #f0a500;
            --info: #4361ee;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --muted: #6c757d;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* ============================================
           ANIMACIONES
           ============================================ */
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

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* ============================================
           PÁGINA BASE
           ============================================ */
        .content-wrapper {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }

        .container-fluid {
            padding: 2rem;
        }

        /* ============================================
           ENCABEZADO DE PÁGINA
           ============================================ */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem;
            border-radius: 1.25rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl), 0 0 40px rgba(26, 26, 46, 0.15);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.5s ease;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(233, 69, 96, 0.15) 0%, transparent 70%);
            transform: translate(30%, -30%);
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h1 .icon-wrapper {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-header h1 i {
            font-size: 1.75rem;
        }

        .page-header .client-info {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .page-header .client-name {
            font-size: 1.35rem;
            font-weight: 600;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-header .client-rut {
            font-size: 0.95rem;
            opacity: 0.85;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.4rem 1rem;
            border-radius: 2rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Estado del cliente en header */
        .status-badge-lg {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4);
        }

        .status-inactive {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        /* ============================================
           TARJETA PRINCIPAL
           ============================================ */
        .edit-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideInUp 0.5s ease 0.1s both;
            margin-bottom: 2rem;
        }

        .edit-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .edit-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .edit-card-header .badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.4rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
        }

        .unsaved-indicator {
            display: none;
            align-items: center;
            gap: 0.4rem;
            color: var(--warning);
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(240, 165, 0, 0.15);
            padding: 0.4rem 0.8rem;
            border-radius: 1rem;
            animation: pulse 1.5s infinite;
        }

        .edit-card-body {
            padding: 2.5rem;
        }

        /* ============================================
           ALERTAS DE ERROR
           ============================================ */
        .error-alert {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-left: 5px solid var(--danger);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            animation: shake 0.4s ease;
        }

        .error-alert h5 {
            color: #b91c1c;
            margin: 0 0 1rem 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .error-alert ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #dc2626;
        }

        .error-alert li {
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        /* ============================================
           SECCIONES DEL FORMULARIO
           ============================================ */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section:last-of-type {
            margin-bottom: 0;
        }

        .section-title {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: var(--shadow-md);
        }

        .section-title i {
            font-size: 1.1em;
        }

        /* ============================================
           CONTROLES DE FORMULARIO
           ============================================ */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--muted);
            font-size: 0.9em;
        }

        .form-label .required {
            color: var(--accent);
            font-weight: 700;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.85rem 1.15rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 26, 46, 0.1);
            outline: none;
        }

        .form-control:hover:not(:focus) {
            border-color: var(--primary-light);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            animation: shake 0.4s ease;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15);
        }

        .form-control.is-valid {
            border-color: var(--success);
        }

        .form-control.is-valid:focus {
            box-shadow: 0 0 0 4px rgba(0, 191, 142, 0.15);
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

        .form-text {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        /* ============================================
           INFORMACIÓN DE AUDITORÍA
           ============================================ */
        .audit-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #7dd3fc;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .audit-card .title {
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .audit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .audit-item dt {
            font-weight: 600;
            color: #0369a1;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .audit-item dd {
            margin: 0;
            color: var(--dark);
            font-weight: 500;
        }

        /* ============================================
           GESTIÓN DE ESTADO
           ============================================ */
        .state-management {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border: 2px solid #fcd34d;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 2rem;
        }

        .state-management.is-active {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: var(--success);
        }

        .state-management.is-inactive {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-color: #94a3b8;
        }

        .state-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .state-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .current-state {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
        }

        .current-state.active {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .current-state.inactive {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
        }

        .state-description {
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* ============================================
           BOTONES
           ============================================ */
        .btn {
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 25px rgba(26, 26, 46, 0.35);
            color: white;
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
        }

        .btn-accent:hover {
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.35);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            box-shadow: 0 8px 25px rgba(0, 191, 142, 0.35);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.35);
            color: white;
        }

        .btn-outline-secondary {
            background: white;
            border: 2px solid #cbd5e1;
            color: var(--muted);
        }

        .btn-outline-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: var(--dark);
        }

        .btn-outline-info {
            background: white;
            border: 2px solid var(--info);
            color: var(--info);
        }

        .btn-outline-info:hover {
            background: var(--info);
            color: white;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.05rem;
        }

        .btn-block {
            width: 100%;
        }

        /* ============================================
           FOOTER DE ACCIONES
           ============================================ */
        .form-actions {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .actions-left, .actions-right {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* ============================================
           CONVENIO BOX
           ============================================ */
        .convenio-box {
            background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%);
            border: 2px solid #e879f9;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .convenio-box .title {
            font-weight: 700;
            color: #a21caf;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .convenio-box .current-convenio {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .convenio-box .convenio-badge {
            background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .page-header .client-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .edit-card-body {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .actions-left, .actions-right {
                width: 100%;
                justify-content: stretch;
            }

            .actions-left .btn, .actions-right .btn {
                flex: 1;
            }
        }
    </style>
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

    {{-- ENCABEZADO DE PÁGINA --}}
    <div class="page-header">
        <div class="page-header-content">
            <div>
                <h1>
                    <span class="icon-wrapper">
                        <i class="fas fa-user-edit"></i>
                    </span>
                    Editar Cliente
                </h1>
                <div class="client-info">
                    <span class="client-name">
                        <i class="fas fa-user-circle"></i>
                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno ?? '' }}
                    </span>
                    <span class="client-rut">
                        <i class="fas fa-id-card"></i> 
                        {{ $cliente->run_pasaporte ?? 'Sin documento' }}
                    </span>
                    <span class="status-badge-lg {{ $cliente->activo ? 'status-active' : 'status-inactive' }}">
                        <i class="fas {{ $cliente->activo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i> Ver Perfil
                </a>
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    {{-- TARJETA DE EDICIÓN --}}
    <div class="edit-card">
        <div class="edit-card-header">
            <h3>
                <i class="fas fa-edit"></i> Información del Cliente
            </h3>
            <div class="d-flex align-items-center" style="gap: 1rem;">
                <span class="badge">
                    <i class="fas fa-hashtag"></i> ID: {{ $cliente->id }}
                </span>
                <span class="unsaved-indicator" id="unsaved-indicator">
                    <i class="fas fa-circle"></i> Cambios sin guardar
                </span>
            </div>
        </div>

        <div class="edit-card-body">
            <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" id="editClienteForm" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_submit_token" value="{{ uniqid() }}">

                {{-- SECCIÓN: IDENTIFICACIÓN --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-id-card"></i> Identificación Personal
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="run_pasaporte" class="form-label">
                                    <i class="fas fa-fingerprint"></i> RUT/Pasaporte
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                           id="run_pasaporte" 
                                           name="run_pasaporte" 
                                           placeholder="Ej: 12.345.678-9 o 12.345.678-0 o 12.345.678-K" 
                                           value="{{ old('run_pasaporte', $cliente->run_pasaporte) }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="rut-status" style="min-width: 45px;">
                                            <i class="fas fa-question-circle text-muted" id="rut-icon"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="form-text" id="rut-hint">
                                    <i class="fas fa-info-circle"></i> Formato chileno (incluye dígitos 0 y K)
                                </small>
                                <div id="rut-feedback" class="mt-1" style="display: none;"></div>
                                @error('run_pasaporte')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_nacimiento" class="form-label">
                                    <i class="fas fa-birthday-cake"></i> Fecha de Nacimiento
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
                    </div>
                </div>

                {{-- SECCIÓN: DATOS PERSONALES --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user"></i> Datos Personales
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombres" class="form-label">
                                    <i class="fas fa-user-tag"></i> Nombres <span class="required">*</span>
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
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apellido_paterno" class="form-label">
                                    <i class="fas fa-user"></i> Apellido Paterno <span class="required">*</span>
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
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apellido_materno" class="form-label">
                                    <i class="fas fa-user"></i> Apellido Materno
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
                    </div>
                </div>

                {{-- SECCIÓN: CONTACTO --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-address-book"></i> Información de Contacto
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Correo Electrónico <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       placeholder="ejemplo@correo.com" 
                                       value="{{ old('email', $cliente->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="celular" class="form-label">
                                    <i class="fas fa-mobile-alt"></i> Teléfono Celular <span class="required">*</span>
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
                    </div>
                </div>

                {{-- SECCIÓN: CONTACTO EMERGENCIA --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-heart"></i> Contacto de Emergencia
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contacto_emergencia" class="form-label">
                                    <i class="fas fa-user-friends"></i> Nombre del Contacto
                                </label>
                                <input type="text" 
                                       class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                                       id="contacto_emergencia" 
                                       name="contacto_emergencia" 
                                       placeholder="Ej: María González" 
                                       value="{{ old('contacto_emergencia', $cliente->contacto_emergencia) }}">
                                <small class="form-text">
                                    <i class="fas fa-info-circle"></i> Persona a contactar en caso de emergencia
                                </small>
                                @error('contacto_emergencia')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_emergencia" class="form-label">
                                    <i class="fas fa-phone-alt"></i> Teléfono de Emergencia
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
                    </div>
                </div>

                {{-- SECCIÓN: DOMICILIO --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-home"></i> Dirección de Domicilio
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="direccion" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Dirección Completa
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
                    </div>
                </div>

                {{-- SECCIÓN: CONVENIO --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-handshake"></i> Convenio Asociado
                    </div>
                    <div class="convenio-box">
                        <div class="title">
                            <i class="fas fa-building"></i> Convenio Empresarial
                        </div>
                        @if($cliente->convenio)
                            <div class="current-convenio">
                                <span>Convenio actual:</span>
                                <span class="convenio-badge">
                                    <i class="fas fa-building"></i> {{ $cliente->convenio->nombre }}
                                </span>
                            </div>
                        @endif
                        <div class="form-group mb-0">
                            <label for="id_convenio" class="form-label">
                                <i class="fas fa-exchange-alt"></i> Cambiar Convenio
                            </label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" 
                                    name="id_convenio">
                                <option value="">Sin convenio</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" 
                                            {{ old('id_convenio', $cliente->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} 
                                        @if($convenio->porcentaje_descuento)
                                            ({{ $convenio->porcentaje_descuento }}% descuento)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text">
                                <i class="fas fa-info-circle"></i> El convenio aplica descuentos en nuevas inscripciones
                            </small>
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: OBSERVACIONES --}}
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-comment-alt"></i> Notas y Observaciones
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="observaciones" class="form-label">
                                    <i class="fas fa-pencil-alt"></i> Información Adicional
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" 
                                          name="observaciones" 
                                          rows="4" 
                                          placeholder="Escribe aquí cualquier nota importante sobre el cliente...">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-text">
                                        <i class="fas fa-lock"></i> Solo visible para el personal administrativo
                                    </small>
                                    <small class="text-muted" id="char-count" style="font-weight: 600;">0 caracteres</small>
                                </div>
                                @error('observaciones')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INFORMACIÓN DE AUDITORÍA --}}
                <div class="audit-card">
                    <div class="title">
                        <i class="fas fa-history"></i> Información del Registro
                    </div>
                    <div class="audit-grid">
                        <div class="audit-item">
                            <dt><i class="fas fa-calendar-plus"></i> Fecha de Registro</dt>
                            <dd>{{ $cliente->created_at->format('d \\d\\e F, Y') }}</dd>
                        </div>
                        <div class="audit-item">
                            <dt><i class="fas fa-clock"></i> Hora de Registro</dt>
                            <dd>{{ $cliente->created_at->format('H:i:s') }} hrs</dd>
                        </div>
                        <div class="audit-item">
                            <dt><i class="fas fa-sync-alt"></i> Última Actualización</dt>
                            <dd>{{ $cliente->updated_at->diffForHumans() }}</dd>
                        </div>
                    </div>
                </div>

                {{-- GESTIÓN DE ESTADO --}}
                @php
                    // id_estado almacena el CÓDIGO directamente (100, 200, etc), no el ID del registro Estado
                    $tieneInscripcionActiva = $cliente->inscripciones()->where('id_estado', 100)->exists();
                    $tieneInscripcionPausada = $cliente->inscripciones()->where('id_estado', 101)->exists();
                    $tienePagosPendientes = $cliente->pagos()->whereIn('id_estado', [200, 202])->exists(); // Pendiente o Parcial
                    $puedoDesactivar = !$tieneInscripcionActiva && !$tieneInscripcionPausada && !$tienePagosPendientes;
                @endphp
                <div class="state-management {{ $cliente->activo ? 'is-active' : 'is-inactive' }}">
                    <div class="state-header">
                        <h4>
                            <i class="fas fa-power-off"></i> Gestión del Estado
                        </h4>
                        <span class="current-state {{ $cliente->activo ? 'active' : 'inactive' }}">
                            <i class="fas {{ $cliente->activo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $cliente->activo ? 'CLIENTE ACTIVO' : 'CLIENTE INACTIVO' }}
                        </span>
                    </div>
                    <p class="state-description">
                        @if($cliente->activo)
                            <i class="fas fa-info-circle"></i> El cliente puede acceder a las instalaciones y servicios del gimnasio.
                        @else
                            <i class="fas fa-exclamation-triangle"></i> El cliente no tiene acceso activo a las instalaciones.
                        @endif
                    </p>
                    @if($cliente->activo && !$puedoDesactivar)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-lock"></i> 
                            <strong>No se puede desactivar:</strong>
                            @if($tieneInscripcionActiva)
                                El cliente tiene una membresía activa.
                            @elseif($tieneInscripcionPausada)
                                El cliente tiene una membresía pausada.
                            @endif
                            @if($tienePagosPendientes)
                                El cliente tiene pagos pendientes o parciales.
                            @endif
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            @if($cliente->activo)
                                <button type="button" 
                                        class="btn btn-danger btn-block btn-lg" 
                                        id="btn-desactivar"
                                        data-id="{{ $cliente->uuid }}"
                                        data-nombre="{{ $cliente->nombres }}"
                                        {{ !$puedoDesactivar ? 'disabled' : '' }}>
                                    <i class="fas fa-user-slash"></i> Desactivar Cliente
                                </button>
                                <small class="form-text text-center d-block mt-2">
                                    @if($puedoDesactivar)
                                        El cliente perderá el acceso hasta ser reactivado
                                    @else
                                        Primero debe cancelar la membresía activa
                                    @endif
                                </small>
                            @else
                                <button type="button" 
                                        class="btn btn-success btn-block btn-lg" 
                                        id="btn-reactivar"
                                        data-id="{{ $cliente->uuid }}"
                                        data-nombre="{{ $cliente->nombres }}">
                                    <i class="fas fa-user-check"></i> Reactivar Cliente
                                </button>
                                <small class="form-text text-center d-block mt-2">
                                    Restaurar acceso completo al cliente
                                </small>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="form-actions">
                    <div class="actions-left">
                        <a href="{{ route('admin.clientes.index') }}" 
                           class="btn btn-outline-secondary btn-lg"
                           id="btn-cancelar">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="btn-restaurar">
                            <i class="fas fa-undo"></i> Restaurar
                        </button>
                    </div>
                    <div class="actions-right">
                        <button type="submit" class="btn btn-accent btn-lg" id="btn-guardar">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* SweetAlert2 Custom Theme - EstoicosGym */
    .swal2-popup.swal-estoicos {
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .swal2-popup.swal-estoicos .swal2-title {
        color: #1a1a2e;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .swal2-popup.swal-estoicos .swal2-html-container {
        color: #64748b;
        font-size: 1rem;
    }
    .swal-estoicos .swal2-confirm {
        background: linear-gradient(135deg, #e94560 0%, #c73e55 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4) !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos .swal2-cancel {
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos.swal-success .swal2-confirm {
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%) !important;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4) !important;
    }
    .swal-estoicos.swal-warning .swal2-confirm {
        background: linear-gradient(135deg, #f0a500 0%, #d99400 100%) !important;
        box-shadow: 0 4px 15px rgba(240, 165, 0, 0.4) !important;
    }
    .swal-estoicos.swal-danger .swal2-confirm {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4) !important;
    }
    .swal-estoicos.swal-primary .swal2-confirm {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        box-shadow: 0 4px 15px rgba(26, 26, 46, 0.4) !important;
    }
    .swal-estoicos.swal-info .swal2-confirm {
        background: linear-gradient(135deg, #4361ee 0%, #3651d4 100%) !important;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4) !important;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // FORMATEO AUTOMÁTICO DE RUT CHILENO
    // ============================================
    function formatRut(value) {
        // Eliminar todo excepto números y K/k
        let rut = value.replace(/[^0-9kK]/g, '').toUpperCase();
        
        if (rut.length === 0) return '';
        
        // Separar cuerpo y dígito verificador
        let dv = rut.slice(-1);
        let cuerpo = rut.slice(0, -1);
        
        if (cuerpo.length === 0) return rut;
        
        // Formatear con puntos
        let cuerpoFormateado = '';
        let contador = 0;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            cuerpoFormateado = cuerpo[i] + cuerpoFormateado;
            contador++;
            if (contador === 3 && i > 0) {
                cuerpoFormateado = '.' + cuerpoFormateado;
                contador = 0;
            }
        }
        
        return cuerpoFormateado + '-' + dv;
    }

    // ============================================
    // VALIDACIÓN DE DÍGITO VERIFICADOR RUT (Módulo 11)
    // Soporta dígitos: 0, 1-9, K
    // ============================================
    function validarRutChileno(rut) {
        // Limpiar RUT
        let rutLimpio = rut.replace(/[^0-9kK]/g, '').toUpperCase();
        
        // Si está vacío o muy corto, no validar aún
        if (rutLimpio.length < 8) {
            return { valid: null, message: 'Ingresa el RUT completo' };
        }
        
        // Separar cuerpo y dígito verificador
        let dv = rutLimpio.slice(-1);
        let cuerpo = rutLimpio.slice(0, -1);
        
        // El cuerpo debe ser solo números
        if (!/^\d+$/.test(cuerpo)) {
            return { valid: false, message: 'Formato de RUT inválido' };
        }
        
        // Calcular dígito verificador con Módulo 11
        let suma = 0;
        let multiplicador = 2;
        
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo[i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        
        let resto = suma % 11;
        let dvCalculado = 11 - resto;
        
        // Convertir a caracter
        let dvEsperado;
        if (dvCalculado === 11) {
            dvEsperado = '0';  // ✅ Soporta dígito verificador 0
        } else if (dvCalculado === 10) {
            dvEsperado = 'K';  // ✅ Soporta dígito verificador K
        } else {
            dvEsperado = dvCalculado.toString();
        }
        
        // Comparar
        if (dv === dvEsperado) {
            return { valid: true, message: 'RUT válido ✓', dv: dvEsperado };
        } else {
            return { valid: false, message: `Dígito verificador incorrecto. Debería ser: ${dvEsperado}`, dv: dvEsperado };
        }
    }
    
    // Actualizar UI de validación de RUT
    function actualizarUIValidacionRut(resultado) {
        const input = document.getElementById('run_pasaporte');
        const icon = document.getElementById('rut-icon');
        const status = document.getElementById('rut-status');
        const hint = document.getElementById('rut-hint');
        const feedback = document.getElementById('rut-feedback');
        
        if (!input || !icon || !status || !hint || !feedback) return;
        
        // Limpiar clases previas
        input.classList.remove('is-valid', 'is-invalid');
        status.classList.remove('bg-success', 'bg-danger');
        status.style.borderColor = '';
        
        if (resultado.valid === null) {
            // Estado neutral - aún escribiendo
            icon.className = 'fas fa-question-circle text-muted';
            hint.innerHTML = '<i class="fas fa-info-circle"></i> Formato chileno (incluye dígitos 0 y K)';
            feedback.style.display = 'none';
        } else if (resultado.valid) {
            // ✅ RUT válido
            input.classList.add('is-valid');
            icon.className = 'fas fa-check-circle text-success';
            status.classList.add('bg-success');
            status.style.borderColor = '#28a745';
            hint.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> ' + resultado.message + '</span>';
            feedback.style.display = 'none';
        } else {
            // ❌ RUT inválido
            input.classList.add('is-invalid');
            icon.className = 'fas fa-times-circle text-danger';
            status.classList.add('bg-danger');
            status.style.borderColor = '#dc3545';
            hint.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + resultado.message + '</span>';
            feedback.innerHTML = '<small class="text-danger">' + resultado.message + '</small>';
            feedback.style.display = 'block';
        }
    }

    // Aplicar formateo al campo RUT
    const rutInput = document.getElementById('run_pasaporte');
    if (rutInput) {
        rutInput.addEventListener('input', function() {
            let cursorPos = this.selectionStart;
            let valorAnterior = this.value;
            let valorFormateado = formatRut(valorAnterior);
            
            this.value = valorFormateado;
            
            // Ajustar posición del cursor
            let diff = valorFormateado.length - valorAnterior.length;
            this.setSelectionRange(cursorPos + diff, cursorPos + diff);
            
            // Validar RUT en tiempo real
            if (valorFormateado.length >= 9) {
                let resultado = validarRutChileno(valorFormateado);
                actualizarUIValidacionRut(resultado);
            } else if (valorFormateado.length === 0) {
                actualizarUIValidacionRut({ valid: null });
            } else {
                actualizarUIValidacionRut({ valid: null, message: 'Ingresa el RUT completo' });
            }
        });
        
        // Validar también al perder foco
        rutInput.addEventListener('blur', function() {
            let valor = this.value.trim();
            if (valor.length > 0 && valor.length >= 9) {
                let resultado = validarRutChileno(valor);
                actualizarUIValidacionRut(resultado);
            }
        });
        
        // Validar al cargar si ya tiene valor
        if (rutInput.value.length >= 9) {
            let resultado = validarRutChileno(rutInput.value);
            actualizarUIValidacionRut(resultado);
        }
    }

    // ============================================
    // FORMATEO AUTOMÁTICO DE TELÉFONO CHILENO
    // ============================================
    function formatTelefono(value) {
        // Eliminar todo excepto números
        let numeros = value.replace(/\D/g, '');
        
        // Si empieza con 56, quitarlo para procesar
        if (numeros.startsWith('56')) {
            numeros = numeros.substring(2);
        }
        
        // Si empieza con 9 y tiene 9 dígitos, es celular chileno
        if (numeros.startsWith('9')) {
            numeros = numeros.substring(0, 9); // Máximo 9 dígitos
            
            // Formatear: +56 9 XXXX XXXX
            if (numeros.length <= 1) {
                return '+56 ' + numeros;
            } else if (numeros.length <= 5) {
                return '+56 ' + numeros[0] + ' ' + numeros.substring(1);
            } else {
                return '+56 ' + numeros[0] + ' ' + numeros.substring(1, 5) + ' ' + numeros.substring(5);
            }
        }
        
        // Si no empieza con 9, agregar el 9
        if (numeros.length > 0 && !numeros.startsWith('9')) {
            numeros = '9' + numeros;
        }
        
        numeros = numeros.substring(0, 9); // Máximo 9 dígitos
        
        if (numeros.length === 0) return '+56 9 ';
        if (numeros.length <= 1) {
            return '+56 ' + numeros;
        } else if (numeros.length <= 5) {
            return '+56 ' + numeros[0] + ' ' + numeros.substring(1);
        } else {
            return '+56 ' + numeros[0] + ' ' + numeros.substring(1, 5) + ' ' + numeros.substring(5);
        }
    }

    // Aplicar formateo al campo celular
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        celularInput.addEventListener('input', function() {
            this.value = formatTelefono(this.value);
        });
        
        celularInput.addEventListener('focus', function() {
            if (!this.value || this.value.trim() === '') {
                this.value = '+56 9 ';
            }
        });
    }

    // Aplicar formateo al campo teléfono de emergencia
    const telefonoEmergenciaInput = document.getElementById('telefono_emergencia');
    if (telefonoEmergenciaInput) {
        telefonoEmergenciaInput.addEventListener('input', function() {
            this.value = formatTelefono(this.value);
        });
        
        telefonoEmergenciaInput.addEventListener('focus', function() {
            if (!this.value || this.value.trim() === '') {
                this.value = '+56 9 ';
            }
        });
    }

    // ===== DETECCIÓN DE CAMBIOS =====
    const form = document.getElementById('editClienteForm');
    const initialData = captureFormData(form);
    let hasChanges = false;

    function captureFormData(form) {
        const data = {};
        form.querySelectorAll('input, textarea, select').forEach(field => {
            if(field.id) data[field.id] = field.value;
        });
        return data;
    }

    function checkChanges() {
        const currentData = captureFormData(form);
        hasChanges = JSON.stringify(initialData) !== JSON.stringify(currentData);
        const indicator = document.getElementById('unsaved-indicator');
        indicator.style.display = hasChanges ? 'flex' : 'none';
    }

    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('change', checkChanges);
        field.addEventListener('keyup', checkChanges);
    });

    // Contador de caracteres
    const obsField = document.getElementById('observaciones');
    const charCount = document.getElementById('char-count');
    if(obsField && charCount) {
        obsField.addEventListener('keyup', () => {
            charCount.textContent = obsField.value.length + ' caracteres';
        });
        charCount.textContent = obsField.value.length + ' caracteres';
    }

    // Advertencia al abandonar
    window.addEventListener('beforeunload', function(e) {
        if(hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ===== BOTÓN CANCELAR =====
    document.getElementById('btn-cancelar').addEventListener('click', function(e) {
        if(!hasChanges) return true;
        
        e.preventDefault();
        Swal.fire({
            title: '¿Salir sin guardar?',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-exclamation" style="font-size: 1.8rem; color: #b45309;"></i>
                    </div>
                    <p style="color: #64748b;">Tienes cambios sin guardar que se perderán.</p>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Salir',
            cancelButtonText: '<i class="fas fa-edit"></i> Seguir editando',
            reverseButtons: true,
            customClass: {
                popup: 'swal-estoicos swal-danger',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if(result.isConfirmed) {
                hasChanges = false;
                window.location.href = '{{ route("admin.clientes.index") }}';
            }
        });
    });

    // ===== BOTÓN RESTAURAR =====
    document.getElementById('btn-restaurar').addEventListener('click', function() {
        Swal.fire({
            title: '¿Restaurar valores?',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-undo" style="font-size: 1.8rem; color: #0369a1;"></i>
                    </div>
                    <p style="color: #64748b;">Todos los campos volverán a sus valores originales.</p>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo"></i> Restaurar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'swal-estoicos swal-info',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if(result.isConfirmed) {
                form.reset();
                hasChanges = false;
                document.getElementById('unsaved-indicator').style.display = 'none';
                if(charCount) charCount.textContent = obsField.value.length + ' caracteres';
                
                Swal.fire({
                    title: '¡Restaurado!',
                    html: `
                        <div style="text-align: center; padding: 1rem 0;">
                            <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i class="fas fa-check" style="font-size: 1.8rem; color: #00bf8e;"></i>
                            </div>
                            <p style="color: #64748b;">Los campos han vuelto a sus valores originales.</p>
                        </div>
                    `,
                    icon: null,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: { popup: 'swal-estoicos swal-success' }
                });
            }
        });
    });

    // ===== BOTÓN GUARDAR =====
    document.getElementById('btn-guardar').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validar campos requeridos
        const required = ['nombres', 'apellido_paterno', 'email', 'celular'];
        let valid = true;
        
        required.forEach(id => {
            const field = document.getElementById(id);
            if(field && !field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else if(field) {
                field.classList.remove('is-invalid');
            }
        });

        if(!valid) {
            Swal.fire({
                title: 'Campos requeridos',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-exclamation-circle" style="font-size: 1.8rem; color: #dc3545;"></i>
                        </div>
                        <p style="color: #64748b;">Por favor complete todos los campos obligatorios.</p>
                    </div>
                `,
                icon: null,
                confirmButtonText: 'Entendido',
                customClass: {
                    popup: 'swal-estoicos',
                    confirmButton: 'swal2-confirm'
                },
                buttonsStyling: false
            });
            document.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        Swal.fire({
            title: '¿Guardar cambios?',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-save" style="font-size: 1.8rem; color: #e94560;"></i>
                    </div>
                    <p style="color: #64748b;">Se actualizarán los datos del cliente en la base de datos.</p>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'swal-estoicos',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if(result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando...',
                    html: `
                        <div style="padding: 1.5rem;">
                            <div style="width: 60px; height: 60px; border: 4px solid #fee2e2; border-top-color: #e94560; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                        </div>
                        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    customClass: { popup: 'swal-estoicos' }
                });
                hasChanges = false;
                form.submit();
            }
        });
    });

    // ===== BOTÓN DESACTIVAR =====
    const btnDesactivar = document.getElementById('btn-desactivar');
    if(btnDesactivar) {
        btnDesactivar.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            
            Swal.fire({
                title: '¿Desactivar cliente?',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-user-slash" style="font-size: 2rem; color: #dc3545;"></i>
                        </div>
                        <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">${nombre}</p>
                        <p style="color: #64748b;">El cliente perderá el acceso a las instalaciones.</p>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-user-slash"></i> Desactivar',
                cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-estoicos swal-danger',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        html: `
                            <div style="padding: 1.5rem;">
                                <div style="width: 60px; height: 60px; border: 4px solid #fee2e2; border-top-color: #dc3545; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                            </div>
                            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: { popup: 'swal-estoicos' }
                    });
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/clientes/${id}/desactivar`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PATCH">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    }

    // ===== BOTÓN REACTIVAR =====
    const btnReactivar = document.getElementById('btn-reactivar');
    if(btnReactivar) {
        btnReactivar.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            
            Swal.fire({
                title: '¿Reactivar cliente?',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-user-check" style="font-size: 2rem; color: #00bf8e;"></i>
                        </div>
                        <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">${nombre}</p>
                        <p style="color: #64748b;">El cliente recuperará el acceso completo.</p>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-user-check"></i> Reactivar',
                cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-estoicos swal-success',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        html: `
                            <div style="padding: 1.5rem;">
                                <div style="width: 60px; height: 60px; border: 4px solid #dcfce7; border-top-color: #00bf8e; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                            </div>
                            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: { popup: 'swal-estoicos' }
                    });
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/clientes/${id}/reactivate`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PATCH">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    }
});
</script>
@endpush
