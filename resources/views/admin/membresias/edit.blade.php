@extends('adminlte::page')

@section('title', 'Editar Membresía - EstóicosGym')

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --success-dark: #00a67d;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* ===== ANIMACIONES ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .animate-fade-in {
        animation: fadeInUp 0.5s ease forwards;
    }

    .delay-1 { animation-delay: 0.1s; opacity: 0; }
    .delay-2 { animation-delay: 0.2s; opacity: 0; }
    .delay-3 { animation-delay: 0.3s; opacity: 0; }
    .delay-4 { animation-delay: 0.4s; opacity: 0; }

    /* ===== HERO HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 30px 35px;
        border-radius: 20px;
        margin-bottom: 30px;
        box-shadow: 0 15px 40px rgba(26, 26, 46, 0.4);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(233, 69, 96, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .page-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 800;
        font-size: 1.8rem;
        position: relative;
        z-index: 1;
    }

    .page-header h1 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .page-header .subtitle {
        color: rgba(255,255,255,0.8);
        font-size: 1rem;
        margin-top: 5px;
        position: relative;
        z-index: 1;
    }

    .page-header .subtitle strong {
        color: var(--accent-light);
    }

    .btn-back-header {
        background: rgba(255,255,255,0.15);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .btn-back-header:hover {
        background: white;
        color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* ===== SIDEBAR INFO ===== */
    .sidebar-info {
        position: sticky;
        top: 20px;
    }

    .info-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .info-card-header {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 700;
    }

    .info-card-header i {
        margin-right: 8px;
    }

    .info-card-body {
        padding: 20px;
    }

    .info-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed var(--gray-200);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1rem;
    }

    .info-item-icon.accent { background: rgba(233, 69, 96, 0.15); color: var(--accent); }
    .info-item-icon.success { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .info-item-icon.info { background: rgba(67, 97, 238, 0.15); color: var(--info); }
    .info-item-icon.warning { background: rgba(240, 165, 0, 0.15); color: var(--warning); }

    .info-item-content {
        flex: 1;
    }

    .info-item-label {
        font-size: 0.75rem;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item-value {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1rem;
    }

    /* ===== MAIN CARD ===== */
    .main-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .main-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 20px 25px;
        position: relative;
    }

    .main-card-header::after {
        content: '';
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .main-card-header h3 {
        margin: 0;
        font-weight: 700;
        font-size: 1.2rem;
        position: relative;
        z-index: 1;
    }

    .main-card-header h3 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .main-card-body {
        padding: 30px;
    }

    /* ===== FORM SECTIONS ===== */
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 2px dashed var(--gray-200);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .form-section-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
    }

    .form-section-text h4 {
        margin: 0;
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1.1rem;
    }

    .form-section-text p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    /* ===== FORM ELEMENTS ===== */
    .form-group-modern {
        margin-bottom: 20px;
    }

    .form-label-modern {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-label-modern .required {
        color: var(--accent);
        margin-left: 4px;
    }

    .form-label-modern .optional {
        color: var(--gray-600);
        font-weight: 400;
        font-size: 0.8rem;
        margin-left: 8px;
    }

    .form-control-modern {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
    }

    .form-control-modern::placeholder {
        color: var(--gray-300);
    }

    .form-control-modern.is-invalid {
        border-color: var(--accent);
        background-image: none;
    }

    /* ===== INPUT GROUPS ===== */
    .input-group-modern {
        display: flex;
        align-items: stretch;
    }

    .input-group-modern .form-control-modern {
        border-radius: 12px 0 0 12px;
        border-right: none;
    }

    .input-group-modern .input-addon {
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
        border: 2px solid var(--gray-200);
        border-left: none;
        border-radius: 0 12px 12px 0;
        padding: 0 18px;
        display: flex;
        align-items: center;
        font-weight: 700;
        color: var(--gray-600);
        font-size: 0.9rem;
    }

    .input-group-modern .input-prepend {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        border: none;
        border-radius: 12px 0 0 12px;
        padding: 0 18px;
        display: flex;
        align-items: center;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .input-group-modern .input-prepend + .form-control-modern {
        border-radius: 0 12px 12px 0;
        border-left: none;
    }

    /* ===== FORM HELP TEXT ===== */
    .form-help {
        display: flex;
        align-items: center;
        margin-top: 8px;
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .form-help i {
        margin-right: 6px;
        color: var(--info);
    }

    .form-help.warning {
        color: var(--warning);
    }

    .form-help.warning i {
        color: var(--warning);
    }

    /* ===== PRECIO PREVIEW CARD ===== */
    .precio-preview-card {
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e9 100%);
        border: 2px solid var(--success);
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .precio-preview-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(0, 191, 142, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .precio-preview-card .label {
        font-size: 0.85rem;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .precio-preview-card .value {
        font-size: 3rem;
        font-weight: 800;
        color: var(--success);
        line-height: 1;
        margin-bottom: 10px;
    }

    .precio-preview-card .discount {
        display: inline-flex;
        align-items: center;
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .precio-preview-card .discount i {
        margin-right: 6px;
    }

    /* ===== SWITCH MODERN ===== */
    .switch-container {
        display: flex;
        align-items: center;
        padding: 20px;
        background: var(--gray-100);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .switch-container.active {
        background: rgba(0, 191, 142, 0.1);
        border: 2px solid var(--success);
    }

    .switch-modern {
        position: relative;
        width: 60px;
        height: 32px;
        margin-right: 15px;
    }

    .switch-modern input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--gray-300);
        border-radius: 32px;
        transition: all 0.3s ease;
    }

    .switch-slider::before {
        content: '';
        position: absolute;
        height: 26px;
        width: 26px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .switch-modern input:checked + .switch-slider {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    }

    .switch-modern input:checked + .switch-slider::before {
        transform: translateX(28px);
    }

    .switch-label {
        flex: 1;
    }

    .switch-label strong {
        display: block;
        color: var(--gray-800);
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .switch-label span {
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
        border-radius: 16px;
        padding: 25px;
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .btn-cancel {
        background: white;
        border: 2px solid var(--gray-300);
        color: var(--gray-600);
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-cancel:hover {
        background: var(--gray-200);
        color: var(--gray-800);
        transform: translateY(-2px);
    }

    .btn-restore {
        background: linear-gradient(135deg, var(--warning) 0%, #d99200 100%);
        border: none;
        color: white;
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 5px 20px rgba(240, 165, 0, 0.3);
    }

    .btn-restore:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(240, 165, 0, 0.4);
        color: white;
    }

    .btn-save {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        border: none;
        color: white;
        border-radius: 12px;
        padding: 14px 35px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 5px 20px rgba(0, 191, 142, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 191, 142, 0.4);
        color: white;
    }

    .btn-save:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* ===== ALERTS ===== */
    .alert-modern {
        border-radius: 16px;
        padding: 20px 25px;
        margin-bottom: 25px;
        border: none;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .alert-modern.danger {
        background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
        border-left: 4px solid var(--accent);
    }

    .alert-modern.warning {
        background: linear-gradient(135deg, #fffbf0 0%, #fff3d6 100%);
        border-left: 4px solid var(--warning);
    }

    .alert-modern .alert-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .alert-modern.danger .alert-icon {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
    }

    .alert-modern.warning .alert-icon {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
    }

    .alert-modern .alert-content {
        flex: 1;
    }

    .alert-modern .alert-title {
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--gray-800);
    }

    .alert-modern .alert-text {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin: 0;
    }

    .alert-modern .alert-text ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }

    /* ===== CURRENT PRICE BADGE ===== */
    .current-price-badge {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
        border-radius: 12px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .current-price-badge::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .current-price-badge .icon {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        position: relative;
        z-index: 1;
    }

    .current-price-badge .content {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .current-price-badge .label {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .current-price-badge .value {
        font-size: 1.4rem;
        font-weight: 800;
    }

    .current-price-badge .details {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* ===== TEXTAREA STYLING ===== */
    textarea.form-control-modern {
        min-height: 120px;
        resize: vertical;
    }

    /* ===== HOVER EFFECTS ===== */
    .form-section:hover .form-section-icon {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }

    .form-control-modern:hover {
        border-color: var(--gray-300);
    }

    /* ===== FOCUS STATES ===== */
    .form-group-modern:focus-within .form-label-modern {
        color: var(--accent);
    }

    /* ===== LOADING STATE ===== */
    .btn-save.loading {
        pointer-events: none;
        position: relative;
        color: transparent;
    }

    .btn-save.loading::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        top: 50%;
        left: 50%;
        margin-left: -10px;
        margin-top: -10px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .sidebar-info {
            position: static;
            margin-bottom: 25px;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 1.4rem;
        }

        .main-card-body {
            padding: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn-cancel,
        .form-actions .btn-save {
            width: 100%;
            justify-content: center;
        }

        .precio-preview-card .value {
            font-size: 2rem;
        }
    }
</style>
@stop

@section('content_header')
    <div class="page-header animate-fade-in">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1>
                    <i class="fas fa-edit"></i> Editar Membresía
                </h1>
                <div class="subtitle">Modificando: <strong>{{ $membresia->nombre }}</strong></div>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert-modern danger animate-fade-in">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">Errores en el formulario</div>
                <div class="alert-text">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @php
        $inscripcionesActivas = $membresia->inscripciones()
            ->whereNotIn('id_estado', [102, 103])
            ->count();
    @endphp

    @if ($inscripcionesActivas > 0)
        <div class="alert-modern warning animate-fade-in">
            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title">Atención</div>
                <p class="alert-text">
                    Esta membresía tiene <strong>{{ $inscripcionesActivas }}</strong> inscripción(es) activa(s). 
                    Los cambios de duración o precio afectarán las futuras inscripciones, no las existentes.
                </p>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- SIDEBAR INFO -->
        <div class="col-lg-4 order-lg-2">
            <div class="sidebar-info animate-fade-in delay-1">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-info-circle"></i> Información Actual
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <div class="info-item-icon accent">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Nombre</div>
                                <div class="info-item-value">{{ $membresia->nombre }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-icon info">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Duración</div>
                                <div class="info-item-value">
                                    {{ $membresia->duracion_dias }} días
                                    @if ($membresia->duracion_meses > 0)
                                        <small>({{ $membresia->duracion_meses }} {{ $membresia->duracion_meses == 1 ? 'mes' : 'meses' }})</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-icon success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Precio Actual</div>
                                <div class="info-item-value" style="color: var(--success);">
                                    @if ($precioActual)
                                        ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                                    @else
                                        Sin precio
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-icon warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Inscripciones Activas</div>
                                <div class="info-item-value">{{ $inscripcionesActivas }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview de Precio -->
                <div class="precio-preview-card animate-fade-in delay-2" id="precioPreview" style="display: none;">
                    <div class="label"><i class="fas fa-tag"></i> Nuevo Precio</div>
                    <div class="value" id="precioPreviewValor">$0</div>
                    <div class="discount" id="precioPreviewDescuento" style="display: none;">
                        <i class="fas fa-handshake"></i>
                        <span id="precioPreviewDescuentoText"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN FORM -->
        <div class="col-lg-8 order-lg-1">
            <div class="main-card animate-fade-in delay-1">
                <div class="main-card-header">
                    <h3><i class="fas fa-credit-card"></i> Datos de la Membresía</h3>
                </div>
                <div class="main-card-body">
                    <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST" id="formMembresia">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_submit_token" value="{{ uniqid('membresia_edit_', true) }}_{{ time() }}">

                        <!-- SECCIÓN: INFORMACIÓN BÁSICA -->
                        <div class="form-section animate-fade-in delay-2">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="form-section-text">
                                    <h4>Información Básica</h4>
                                    <p>Nombre y duración de la membresía</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-tag" style="color: var(--accent); margin-right: 6px;"></i>
                                            Nombre de la Membresía
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control-modern @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               name="nombre" 
                                               placeholder="Ej: Plan Mensual, Pase Diario, Plan Premium..." 
                                               value="{{ old('nombre', $membresia->nombre) }}" 
                                               required 
                                               minlength="3" 
                                               maxlength="50">
                                        <div class="form-help">
                                            <i class="fas fa-info-circle"></i>
                                            Nombre único que identifica esta membresía
                                        </div>
                                        @error('nombre')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-calendar-alt" style="color: var(--info); margin-right: 6px;"></i>
                                            Duración en Meses
                                            <span class="required">*</span>
                                        </label>
                                        <div class="input-group-modern">
                                            <input type="number" 
                                                   class="form-control-modern @error('duracion_meses') is-invalid @enderror" 
                                                   id="duracion_meses" 
                                                   name="duracion_meses" 
                                                   value="{{ old('duracion_meses', $membresia->duracion_meses) }}" 
                                                   min="0" 
                                                   max="12" 
                                                   required>
                                            <span class="input-addon">meses</span>
                                        </div>
                                        <div class="form-help">
                                            <i class="fas fa-lightbulb"></i>
                                            Usa 0 para pase diario o plan personalizado
                                        </div>
                                        @error('duracion_meses')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-calendar-day" style="color: var(--warning); margin-right: 6px;"></i>
                                            Duración Total en Días
                                        </label>
                                        <div class="input-group-modern">
                                            <input type="number" 
                                                   class="form-control-modern" 
                                                   id="duracion_dias_calculado" 
                                                   min="1" 
                                                   max="365">
                                            <input type="hidden" id="duracion_dias" name="duracion_dias" value="{{ old('duracion_dias', $membresia->duracion_dias) }}">
                                            <span class="input-addon">días</span>
                                        </div>
                                        <div class="form-help" id="dias_info">
                                            <i class="fas fa-calculator"></i>
                                            Mensual: 31 días | Otros: meses × 30
                                        </div>
                                        @error('duracion_dias')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern" for="max_pausas">
                                            <i class="fas fa-pause-circle"></i>
                                            Máximo de Pausas Permitidas
                                            <span class="required">*</span>
                                        </label>
                                        <div class="input-group-modern">
                                            <input type="number" 
                                                   class="form-control-modern @error('max_pausas') is-invalid @enderror" 
                                                   id="max_pausas" 
                                                   name="max_pausas"
                                                   value="{{ old('max_pausas', $membresia->max_pausas ?? 1) }}"
                                                   min="0" 
                                                   max="12"
                                                   required>
                                            <span class="input-addon"><i class="fas fa-pause-circle"></i></span>
                                        </div>
                                        <div class="form-help">
                                            <i class="fas fa-info-circle"></i>
                                            Cantidad de veces que el cliente puede pausar su membresía
                                        </div>
                                        @error('max_pausas')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN: DESCRIPCIÓN -->
                        <div class="form-section animate-fade-in delay-3">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-align-left"></i>
                                </div>
                                <div class="form-section-text">
                                    <h4>Descripción</h4>
                                    <p>Detalles y beneficios de la membresía</p>
                                </div>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Descripción
                                    <span class="optional">(opcional)</span>
                                </label>
                                <textarea class="form-control-modern @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="4" 
                                          placeholder="Describe los beneficios, horarios, restricciones..."
                                          maxlength="1000">{{ old('descripcion', $membresia->descripcion) }}</textarea>
                                <div class="form-help">
                                    <i class="fas fa-text-width"></i>
                                    Máximo 1000 caracteres
                                </div>
                                @error('descripcion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- SECCIÓN: PRECIOS -->
                        <div class="form-section animate-fade-in delay-4">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="form-section-text">
                                    <h4>Configuración de Precios</h4>
                                    <p>Define el precio normal y con convenio</p>
                                </div>
                            </div>

                            @if ($precioActual)
                                <div class="current-price-badge">
                                    <div class="icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="content">
                                        <div class="label">Precio Actual</div>
                                        <div class="value">${{ number_format($precioActual->precio_normal, 0, ',', '.') }}</div>
                                        <div class="details">
                                            @if ($precioActual->precio_convenio)
                                                Con convenio: ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }} |
                                            @endif
                                            Vigente desde: {{ $precioActual->fecha_vigencia_desde->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-dollar-sign" style="color: var(--success); margin-right: 6px;"></i>
                                            Precio Normal
                                            <span class="required">*</span>
                                        </label>
                                        <div class="input-group-modern">
                                            <span class="input-prepend">$</span>
                                            <input type="text" 
                                                   class="form-control-modern @error('precio_normal') is-invalid @enderror" 
                                                   id="precio_normal_display" 
                                                   placeholder="Ej: 25.000" 
                                                   required>
                                            <input type="hidden" id="precio_normal" name="precio_normal" 
                                                   value="{{ old('precio_normal', $precioActual->precio_normal ?? 0) }}">
                                        </div>
                                        <div class="form-help">
                                            <i class="fas fa-info-circle"></i>
                                            Precio sin descuento
                                        </div>
                                        @error('precio_normal')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-handshake" style="color: var(--info); margin-right: 6px;"></i>
                                            Precio con Convenio
                                            <span class="optional">(opcional)</span>
                                        </label>
                                        <div class="input-group-modern">
                                            <span class="input-prepend">$</span>
                                            <input type="text" 
                                                   class="form-control-modern @error('precio_convenio') is-invalid @enderror" 
                                                   id="precio_convenio_display" 
                                                   placeholder="Ej: 20.000">
                                            <input type="hidden" id="precio_convenio" name="precio_convenio" 
                                                   value="{{ old('precio_convenio', $precioActual->precio_convenio ?? '') }}">
                                        </div>
                                        <div class="form-help">
                                            <i class="fas fa-building"></i>
                                            Para clientes con convenio empresarial
                                        </div>
                                        @error('precio_convenio')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Razón del Cambio -->
                            <div class="form-group-modern" style="margin-top: 15px;">
                                <label class="form-label-modern">
                                    <i class="fas fa-history" style="color: var(--warning); margin-right: 6px;"></i>
                                    Razón del Cambio
                                    <span class="optional">(recomendado si cambia el precio)</span>
                                </label>
                                <input type="text" 
                                       class="form-control-modern @error('razon_cambio') is-invalid @enderror" 
                                       id="razon_cambio" 
                                       name="razon_cambio" 
                                       value="{{ old('razon_cambio') }}" 
                                       placeholder="Ej: Ajuste por inflación, Promoción de temporada..."
                                       maxlength="255">
                                <div class="form-help">
                                    <i class="fas fa-bookmark"></i>
                                    Este texto quedará registrado en el historial
                                </div>
                                @error('razon_cambio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- SECCIÓN: ESTADO -->
                        <div class="form-section animate-fade-in delay-4">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-toggle-on"></i>
                                </div>
                                <div class="form-section-text">
                                    <h4>Estado de la Membresía</h4>
                                    <p>Activa o desactiva esta membresía</p>
                                </div>
                            </div>

                            <div class="switch-container {{ $membresia->activo ? 'active' : '' }}" id="switchContainer">
                                <label class="switch-modern">
                                    <input type="hidden" name="activo" value="0">
                                    <input type="checkbox" id="activo" name="activo" value="1" 
                                           {{ $membresia->activo ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                                <div class="switch-label">
                                    <strong id="switchText">{{ $membresia->activo ? 'Membresía Activa' : 'Membresía Inactiva' }}</strong>
                                    <span id="switchDesc">{{ $membresia->activo ? 'Los clientes pueden contratarla' : 'No disponible para nuevos clientes' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ACCIONES -->
                        <div class="form-actions">
                            <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="button" class="btn-restore" id="btnRestaurar">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                            <button type="submit" class="btn-save" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== Valores originales para restaurar ==========
    const valoresOriginales = {
        nombre: @json($membresia->nombre),
        duracion_meses: {{ $membresia->duracion_meses }},
        duracion_dias: {{ $membresia->duracion_dias }},
        descripcion: @json($membresia->descripcion ?? ''),
        precio_normal: {{ $precioActual->precio_normal ?? 0 }},
        precio_convenio: {{ $precioActual->precio_convenio ?? 0 }},
        activo: {{ $membresia->activo ? 'true' : 'false' }}
    };

    // ========== Referencias a elementos ==========
    const form = document.getElementById('formMembresia');
    const duracionMeses = document.getElementById('duracion_meses');
    const duracionDias = document.getElementById('duracion_dias');
    const duracionDiasCalculado = document.getElementById('duracion_dias_calculado');
    const diasInfo = document.getElementById('dias_info');
    const precioNormalDisplay = document.getElementById('precio_normal_display');
    const precioNormalHidden = document.getElementById('precio_normal');
    const precioConvenioDisplay = document.getElementById('precio_convenio_display');
    const precioConvenioHidden = document.getElementById('precio_convenio');
    const precioPreview = document.getElementById('precioPreview');
    const precioPreviewValor = document.getElementById('precioPreviewValor');
    const precioPreviewDescuento = document.getElementById('precioPreviewDescuento');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnRestaurar = document.getElementById('btnRestaurar');
    const switchActivo = document.getElementById('activo');
    const switchContainer = document.getElementById('switchContainer');
    const switchText = document.getElementById('switchText');
    const switchDesc = document.getElementById('switchDesc');

    // ========== Botón Restaurar ==========
    btnRestaurar.addEventListener('click', function() {
        Swal.fire({
            title: '¿Restaurar valores originales?',
            text: 'Se descartarán todos los cambios realizados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0a500',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Restaurar todos los campos
                document.getElementById('nombre').value = valoresOriginales.nombre;
                duracionMeses.value = valoresOriginales.duracion_meses;
                duracionDias.value = valoresOriginales.duracion_dias;
                duracionDiasCalculado.value = valoresOriginales.duracion_dias;
                document.getElementById('descripcion').value = valoresOriginales.descripcion;
                precioNormalHidden.value = valoresOriginales.precio_normal;
                precioNormalDisplay.value = formatearNumero(valoresOriginales.precio_normal);
                precioConvenioHidden.value = valoresOriginales.precio_convenio;
                precioConvenioDisplay.value = valoresOriginales.precio_convenio > 0 ? formatearNumero(valoresOriginales.precio_convenio) : '';
                document.getElementById('razon_cambio').value = '';
                
                // Restaurar switch
                switchActivo.checked = valoresOriginales.activo;
                if (valoresOriginales.activo) {
                    switchContainer.classList.add('active');
                    switchText.textContent = 'Membresía Activa';
                    switchDesc.textContent = 'Los clientes pueden contratarla';
                } else {
                    switchContainer.classList.remove('active');
                    switchText.textContent = 'Membresía Inactiva';
                    switchDesc.textContent = 'No disponible para nuevos clientes';
                }
                
                // Actualizar días y ocultar preview
                actualizarDias();
                precioPreview.style.display = 'none';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Valores restaurados',
                    text: 'Se han restaurado los valores originales',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    // ========== Switch Toggle ==========
    if (switchActivo) {
        switchActivo.addEventListener('change', function() {
            if (this.checked) {
                switchContainer.classList.add('active');
                switchText.textContent = 'Membresía Activa';
                switchDesc.textContent = 'Los clientes pueden contratarla';
            } else {
                switchContainer.classList.remove('active');
                switchText.textContent = 'Membresía Inactiva';
                switchDesc.textContent = 'No disponible para nuevos clientes';
            }
        });
    }

    // ========== Lógica de Duración de Días ==========
    function actualizarDias() {
        const meses = parseInt(duracionMeses.value) || 0;
        
        if (meses === 0) {
            // Modo manual para pase diario
            duracionDiasCalculado.removeAttribute('readonly');
            duracionDiasCalculado.value = duracionDias.value || 1;
            duracionDiasCalculado.placeholder = 'Ej: 1 para pase diario';
            diasInfo.innerHTML = '<i class="fas fa-hand-pointer"></i> Meses = 0: Ingresa los días manualmente';
            diasInfo.classList.add('text-warning');
            diasInfo.classList.remove('text-muted');
        } else {
            // Modo automático: 1 mes = 31 días (30 + 1 gracia), otros = meses × 30
            const dias = meses === 1 ? 31 : (meses * 30);
            duracionDias.value = dias;
            duracionDiasCalculado.value = dias;
            duracionDiasCalculado.setAttribute('readonly', 'readonly');
            if (meses === 1) {
                diasInfo.innerHTML = `<i class="fas fa-calculator"></i> Mensual: 30 + 1 día de gracia = <strong>${dias} días</strong>`;
            } else {
                diasInfo.innerHTML = `<i class="fas fa-calculator"></i> Cálculo: ${meses} × 30 = <strong>${dias} días</strong>`;
            }
            diasInfo.classList.remove('text-warning');
            diasInfo.classList.add('text-muted');
        }
    }

    // Sincronizar días manual con hidden
    duracionDiasCalculado.addEventListener('input', function() {
        duracionDias.value = this.value;
    });

    duracionMeses.addEventListener('change', actualizarDias);
    duracionMeses.addEventListener('input', actualizarDias);
    
    // Cargar valor inicial de días
    duracionDiasCalculado.value = duracionDias.value;
    actualizarDias();

    // ========== Formateo de Precio ==========
    // Precio actual original para comparar
    const precioOriginal = {{ $precioActual->precio_normal ?? 0 }};
    const precioConvenioOriginal = {{ $precioActual->precio_convenio ?? 0 }};

    function formatearNumero(num) {
        if (!num && num !== 0) return '';
        // Solo separador de miles con punto (números enteros, sin decimales)
        return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function limpiarNumero(str) {
        if (!str) return 0;
        // Remover todo excepto dígitos
        return parseInt(str.toString().replace(/\D/g, '')) || 0;
    }

    function actualizarPrecioDisplay(displayEl, hiddenEl) {
        const valor = displayEl.value;
        const numero = limpiarNumero(valor);
        hiddenEl.value = numero;
        
        // Mantener cursor
        const cursorPos = displayEl.selectionStart;
        const oldLen = displayEl.value.length;
        
        displayEl.value = numero > 0 ? formatearNumero(numero) : '';
        
        // Ajustar cursor
        const newLen = displayEl.value.length;
        const newPos = cursorPos + (newLen - oldLen);
        displayEl.setSelectionRange(Math.max(0, newPos), Math.max(0, newPos));
    }

    function actualizarPreview() {
        const precioNormal = limpiarNumero(precioNormalDisplay.value);
        const precioConvenio = limpiarNumero(precioConvenioDisplay.value);
        
        // Solo mostrar preview si el precio cambió respecto al original
        const precioCambio = precioNormal !== precioOriginal;
        const convenioCambio = precioConvenio !== precioConvenioOriginal;
        
        if (precioNormal > 0 && (precioCambio || convenioCambio)) {
            precioPreview.style.display = 'block';
            precioPreviewValor.textContent = '$' + formatearNumero(precioNormal);
            
            if (precioConvenio > 0 && precioConvenio < precioNormal) {
                const descuento = precioNormal - precioConvenio;
                const porcentaje = Math.round((descuento / precioNormal) * 100);
                precioPreviewDescuento.style.display = 'block';
                document.getElementById('precioPreviewDescuentoText').textContent = 
                    `Con convenio: $${formatearNumero(precioConvenio)} (${porcentaje}% desc.)`;
            } else {
                precioPreviewDescuento.style.display = 'none';
            }
        } else {
            precioPreview.style.display = 'none';
        }
    }

    precioNormalDisplay.addEventListener('input', function() {
        actualizarPrecioDisplay(this, precioNormalHidden);
        actualizarPreview();
    });

    precioConvenioDisplay.addEventListener('input', function() {
        actualizarPrecioDisplay(this, precioConvenioHidden);
        actualizarPreview();
    });

    // Cargar valores iniciales (solo formatear, sin mostrar preview)
    if (precioNormalHidden.value) {
        precioNormalDisplay.value = formatearNumero(precioNormalHidden.value);
    }
    if (precioConvenioHidden.value) {
        precioConvenioDisplay.value = formatearNumero(precioConvenioHidden.value);
    }
    // El preview inicia oculto porque no hay cambios aún
    precioPreview.style.display = 'none';

    // ========== Validación del Formulario con SweetAlert ==========
    form.addEventListener('submit', function(e) {
        let errores = [];

        // Validar nombre
        const nombre = document.getElementById('nombre').value.trim();
        if (nombre.length < 3) {
            errores.push('El nombre debe tener al menos 3 caracteres');
        }

        // Validar duración días
        const dias = parseInt(duracionDias.value) || 0;
        if (dias < 1) {
            errores.push('La duración en días debe ser al menos 1');
        }

        // Validar precio normal
        const precioNormal = limpiarNumero(precioNormalDisplay.value);
        if (precioNormal <= 0) {
            errores.push('El precio normal debe ser mayor a 0');
        }

        // Validar precio convenio (si existe, debe ser menor al normal)
        const precioConvenio = limpiarNumero(precioConvenioDisplay.value);
        if (precioConvenio > 0 && precioConvenio >= precioNormal) {
            errores.push('El precio con convenio debe ser menor al precio normal');
        }

        if (errores.length > 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<i class="fas fa-exclamation-triangle"></i> Error de validación',
                html: '<ul class="text-left">' + errores.map(e => `<li>${e}</li>`).join('') + '</ul>',
                confirmButtonColor: '#e94560',
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        // Confirmar antes de guardar
        e.preventDefault();
        Swal.fire({
            title: '¿Guardar cambios?',
            html: `
                <p>Vas a actualizar la membresía:</p>
                <strong>${nombre}</strong>
                <div class="mt-3" style="font-size: 0.9rem; color: #6c757d;">
                    <div><i class="fas fa-calendar-alt"></i> Duración: ${dias} días</div>
                    <div><i class="fas fa-dollar-sign"></i> Precio: $${formatearNumero(precioNormal)}</div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitar botón para evitar doble envío
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                form.submit();
            }
        });
    });
});
</script>
@endsection
