@extends('adminlte::page')

@section('title', 'Editar Inscripción - EstóicosGym')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="{{ asset('js/precio-formatter.js') }}"></script>
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
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    body { background: var(--gray-100); }

    /* Select2 Custom Styles */
    .select2-container--bootstrap-5 .select2-selection {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        min-height: 44px;
        padding: 6px 12px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
    }

    /* HERO HEADER */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 30px 35px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 15px 40px rgba(26, 26, 46, 0.4);
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 250px;
        height: 250px;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.1;
    }
    .hero-header::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: 30%;
        width: 180px;
        height: 180px;
        background: var(--success);
        border-radius: 50%;
        opacity: 0.08;
    }
    .hero-header-content { position: relative; z-index: 1; }
    .hero-title { 
        font-size: 1.8em; 
        font-weight: 800; 
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .hero-subtitle { 
        font-size: 1em; 
        opacity: 0.9;
        font-weight: 400;
    }

    /* MODERN CARDS */
    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border: none;
        overflow: hidden;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }
    .modern-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .modern-card-header {
        padding: 18px 25px;
        border-bottom: 1px solid var(--gray-200);
        font-weight: 700;
        font-size: 1.05em;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .modern-card-header.primary { 
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }
    .modern-card-header.info { 
        background: linear-gradient(135deg, var(--info) 0%, #5a7bff 100%);
        color: white;
    }
    .modern-card-header.warning { 
        background: linear-gradient(135deg, var(--warning) 0%, #ffb800 100%);
        color: var(--gray-800);
    }
    .modern-card-header.success { 
        background: linear-gradient(135deg, var(--success) 0%, #00d9a0 100%);
        color: white;
    }
    .modern-card-header.danger { 
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
    }
    .modern-card-header i {
        font-size: 1.1em;
    }
    .modern-card-body {
        padding: 25px;
    }

    /* FORM STYLES */
    .form-label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9em;
    }
    .form-label i {
        color: var(--info);
        font-size: 0.9em;
    }
    .form-control, .form-select, select.form-control {
        border-radius: 10px;
        border: 2px solid var(--gray-200);
        padding: 12px 15px;
        transition: all 0.3s ease;
        font-size: 0.95em;
        height: auto;
        min-height: 48px;
        line-height: 1.4;
    }
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-right: 40px;
    }
    .form-control:focus, .form-select:focus, select.form-control:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        outline: none;
    }
    .form-control:read-only {
        background-color: var(--gray-100);
        cursor: not-allowed;
    }
    .input-group-text {
        background: var(--gray-200);
        border: 2px solid var(--gray-200);
        border-radius: 10px 0 0 10px;
        font-weight: 600;
        color: var(--gray-600);
        padding: 12px 15px;
        min-height: 48px;
    }
    .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }
    .text-danger { color: var(--accent) !important; }

    /* BUTTONS */
    .btn-modern {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .btn-modern.primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
    }
    .btn-modern.success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
    }
    .btn-modern.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #cc8400 100%);
        color: var(--gray-800);
    }
    .btn-modern.danger {
        background: linear-gradient(135deg, var(--accent) 0%, #c13050 100%);
        color: white;
    }
    .btn-modern.info {
        background: linear-gradient(135deg, var(--info) 0%, #3451d1 100%);
        color: white;
    }
    .btn-outline-modern {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        transition: all 0.3s ease;
    }
    .btn-outline-modern:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: white;
    }

    /* PAUSE SYSTEM */
    .pause-status-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        border-left: 5px solid var(--warning);
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }
    .pause-status-card.active {
        border-left-color: var(--success);
    }
    .pause-status-card.paused {
        border-left-color: var(--warning);
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    .pause-status-card.indefinite {
        border-left-color: var(--accent);
        background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
    }

    .pause-option {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    .pause-option:hover {
        border-color: var(--info);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .pause-option.selected {
        border-color: var(--info);
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.08) 0%, rgba(67, 97, 238, 0.04) 100%);
    }
    .pause-option.indefinite {
        border-color: var(--accent);
    }
    .pause-option.indefinite:hover {
        border-color: var(--accent);
    }
    .pause-option.indefinite.selected {
        border-color: var(--accent);
        background: linear-gradient(135deg, rgba(233, 69, 96, 0.08) 0%, rgba(233, 69, 96, 0.04) 100%);
    }
    .pause-option-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.3em;
    }
    .pause-option-icon.days {
        background: rgba(67, 97, 238, 0.12);
        color: var(--info);
    }
    .pause-option-icon.indefinite {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }
    .pause-option-title {
        font-weight: 700;
        font-size: 1.1em;
        color: var(--gray-800);
        margin-bottom: 5px;
    }
    .pause-option-desc {
        font-size: 0.85em;
        color: var(--gray-600);
    }

    /* PAUSE INFO BOX */
    .pause-info-box {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }
    .pause-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .pause-info-item:last-child {
        border-bottom: none;
    }
    .pause-info-item i {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        color: var(--info);
    }

    /* PAUSE COUNTER */
    .pause-counter {
        display: flex;
        align-items: center;
        gap: 15px;
        background: var(--gray-100);
        padding: 15px 20px;
        border-radius: 12px;
        margin-top: 20px;
    }
    .pause-counter-number {
        font-size: 2em;
        font-weight: 800;
        color: var(--info);
    }
    .pause-counter-label {
        font-size: 0.9em;
        color: var(--gray-600);
    }

    /* ALERT MODERN */
    .alert-modern {
        border-radius: 12px;
        padding: 15px 20px;
        border: none;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .alert-modern.warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        color: #92400e;
    }
    .alert-modern.danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        color: #991b1b;
    }
    .alert-modern.success {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #065f46;
    }
    .alert-modern.info {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1e40af;
    }

    /* MODAL MODERN */
    .modal-modern .modal-content {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }
    .modal-modern .modal-header {
        padding: 25px 30px;
        border-bottom: 1px solid var(--gray-200);
    }
    .modal-modern .modal-header.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ffb800 100%);
    }
    .modal-modern .modal-header.danger {
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        color: white;
    }
    .modal-modern .modal-body {
        padding: 30px;
    }
    .modal-modern .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid var(--gray-200);
    }

    /* BACK BUTTON */
    .btn-back {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.3);
        color: white;
        transform: translateY(-2px);
    }

    /* REQUIRED INDICATOR */
    .required-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: var(--accent);
        border-radius: 50%;
        margin-left: 5px;
    }

    /* TEXTAREA */
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* LOADING OVERLAY */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .loading-overlay.active {
        display: flex;
    }
    .loading-spinner {
        background: white;
        padding: 30px 50px;
        border-radius: 16px;
        text-align: center;
    }

    /* INFO DISPLAY CARDS */
    .info-display-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: var(--gray-100);
        border-radius: 12px;
        border: 1px solid var(--gray-200);
    }
    .info-display-card.compact {
        padding: 12px;
    }
    .info-display-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(67, 97, 238, 0.12);
        color: var(--info);
        font-size: 1.2em;
        flex-shrink: 0;
    }
    .info-display-icon.small {
        width: 40px;
        height: 40px;
        font-size: 1em;
    }
    .info-display-content {
        flex: 1;
    }
    .info-display-content strong {
        color: var(--gray-800);
        font-size: 1em;
    }

    /* PRECIO FINAL BOX */
    .precio-final-box {
        background: linear-gradient(135deg, var(--gray-100) 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        border: 2px dashed var(--gray-200);
    }
    .precio-calculo {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
    }
    .precio-calculo .precio-base {
        color: var(--gray-600);
        font-weight: 600;
    }
    .precio-calculo .operador {
        color: var(--gray-600);
        font-weight: bold;
    }
    .precio-calculo .descuento {
        color: var(--accent);
        font-weight: 600;
    }
    .precio-final-valor {
        font-size: 2em;
        font-weight: 800;
        color: var(--success);
    }

    /* MODE TABS */
    .mode-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        background: var(--gray-100);
        padding: 8px;
        border-radius: 16px;
    }
    .mode-tab {
        flex: 1;
        padding: 16px 24px;
        border: none;
        background: transparent;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95em;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .mode-tab:hover {
        background: rgba(255,255,255,0.5);
        color: var(--gray-800);
    }
    .mode-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .mode-tab.active.upgrade {
        background: linear-gradient(135deg, var(--success) 0%, #00d9a0 100%);
        color: white;
    }
    .mode-tab i {
        font-size: 1.1em;
    }
    .mode-content {
        display: none;
    }
    .mode-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* PLAN CARDS */
    .plan-card {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 16px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    .plan-card:hover {
        border-color: var(--info);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .plan-card.selected {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.05);
    }
    .plan-card.selected::after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--success);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8em;
    }
    .plan-card.current {
        border-color: var(--warning);
        background: rgba(240, 165, 0, 0.05);
        cursor: not-allowed;
        opacity: 0.7;
    }
    .plan-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .plan-card-name {
        font-weight: 700;
        font-size: 1.1em;
        color: var(--gray-800);
    }
    .plan-card-price {
        font-weight: 800;
        font-size: 1.3em;
        color: var(--success);
    }
    .plan-card-duration {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        font-size: 0.9em;
    }
    .plan-card-badge {
        position: absolute;
        top: -10px;
        left: 20px;
        background: var(--warning);
        color: var(--gray-800);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
    }
    .plan-card-badge.upgrade {
        background: var(--success);
        color: white;
    }
    .plan-card-badge.downgrade {
        background: var(--accent);
        color: white;
    }

    /* RESUMEN CAMBIO */
    .cambio-resumen {
        background: linear-gradient(135deg, var(--gray-100) 0%, white 100%);
        border-radius: 16px;
        padding: 25px;
        border: 2px solid var(--gray-200);
    }
    .cambio-resumen-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .cambio-resumen-row:last-child {
        border-bottom: none;
        padding-top: 20px;
        margin-top: 10px;
        border-top: 2px dashed var(--gray-300);
    }
    .cambio-resumen-label {
        color: var(--gray-600);
        font-weight: 500;
    }
    .cambio-resumen-value {
        font-weight: 700;
        color: var(--gray-800);
    }
    .cambio-resumen-value.credito {
        color: var(--success);
    }
    .cambio-resumen-value.diferencia {
        font-size: 1.4em;
    }
    .cambio-resumen-value.diferencia.positiva {
        color: var(--accent);
    }
    .cambio-resumen-value.diferencia.negativa {
        color: var(--success);
    }

    /* TOAST NOTIFICATIONS */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
    }
    .toast-notification {
        min-width: 320px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
        color: white;
    }
    .toast-notification.success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    }
    .toast-notification.error {
        background: linear-gradient(135deg, var(--accent) 0%, #c13050 100%);
    }
    .toast-notification.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #cc8400 100%);
        color: var(--gray-800);
    }
    .toast-notification i {
        font-size: 1.3em;
    }
    .toast-notification .toast-content {
        flex: 1;
    }
    .toast-notification .toast-title {
        font-weight: 700;
        margin-bottom: 2px;
    }
    .toast-notification .toast-message {
        font-size: 0.9em;
        opacity: 0.9;
    }
    .toast-notification .toast-close {
        background: none;
        border: none;
        color: inherit;
        opacity: 0.7;
        cursor: pointer;
        padding: 5px;
    }
    .toast-notification .toast-close:hover {
        opacity: 1;
    }
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
@stop

@section('content_header')
@stop

@section('content')
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
            <p class="mb-0">Procesando...</p>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-edit me-2"></i> Editar Inscripción
                    </h1>
                    <p class="hero-subtitle">
                        <i class="fas fa-hashtag me-1"></i> ID: {{ $inscripcion->id }} &nbsp;|&nbsp;
                        <i class="fas fa-user me-1"></i> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                    </p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn-back">
                        <i class="fas fa-eye me-1"></i> Ver Detalles
                    </a>
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-modern danger mb-4">
            <i class="fas fa-exclamation-circle fa-lg"></i>
            <div>
                <strong>Errores de Validación:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Información del Cliente y Membresía (Solo Lectura) -->
    <div class="modern-card mb-4">
        <div class="modern-card-header primary">
            <i class="fas fa-info-circle"></i>
            <span>Información de la Inscripción</span>
            <span class="badge bg-light text-dark ms-auto" style="font-size: 0.75em;">Solo lectura</span>
        </div>
        <div class="modern-card-body">
            <div class="row">
                <!-- Datos del Cliente -->
                <div class="col-md-6">
                    <div class="info-display-card">
                        <div class="info-display-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-display-content">
                            <small class="text-muted d-block">Cliente</small>
                            <strong>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</strong>
                            <small class="text-muted d-block">{{ $inscripcion->cliente->email }}</small>
                            @if($inscripcion->cliente->telefono)
                                <small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $inscripcion->cliente->telefono }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Datos de la Membresía -->
                <div class="col-md-6">
                    <div class="info-display-card">
                        <div class="info-display-icon" style="background: rgba(0, 191, 142, 0.12); color: var(--success);">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="info-display-content">
                            <small class="text-muted d-block">Membresía</small>
                            <strong>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</strong>
                            <small class="text-muted d-block">{{ $inscripcion->membresia->duracion_meses ?? 0 }} meses</small>
                            @if($inscripcion->convenio)
                                <span class="badge bg-info mt-1"><i class="fas fa-handshake me-1"></i>{{ $inscripcion->convenio->nombre }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <!-- Fechas -->
                <div class="col-md-4">
                    <div class="info-display-card compact">
                        <div class="info-display-icon small" style="background: rgba(67, 97, 238, 0.12); color: var(--info);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="info-display-content">
                            <small class="text-muted d-block">Fecha Inicio</small>
                            <strong>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-display-card compact">
                        <div class="info-display-icon small" style="background: rgba(240, 165, 0, 0.12); color: var(--warning);">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="info-display-content">
                            <small class="text-muted d-block">Fecha Vencimiento</small>
                            <strong class="{{ $inscripcion->dias_restantes < 7 ? 'text-danger' : '' }}">
                                {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-display-card compact">
                        <div class="info-display-icon small" style="background: rgba(0, 191, 142, 0.12); color: var(--success);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="info-display-content">
                            <small class="text-muted d-block">Precio Base</small>
                            <strong>${{ number_format($inscripcion->precio_base, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS: Elegir modo de edición -->
    @if($inscripcion->id_estado == 100 && !$inscripcion->pausada)
    <div class="mode-tabs">
        <button type="button" class="mode-tab active" data-mode="edicion">
            <i class="fas fa-edit"></i>
            <span>Edición Simple</span>
        </button>
        <button type="button" class="mode-tab" data-mode="cambio">
            <i class="fas fa-exchange-alt"></i>
            <span>Cambiar Plan</span>
        </button>
    </div>
    @endif

    <!-- ========================================== -->
    <!-- MODO 1: EDICIÓN SIMPLE (formulario actual) -->
    <!-- ========================================== -->
    <div class="mode-content active" id="modo-edicion">
        <form action="{{ route('admin.inscripciones.update', $inscripcion) }}" method="POST" id="formInscripcion">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_submit_token" value="{{ uniqid('edit_', true) }}">

            <!-- Campos ocultos para mantener los valores que no se editan -->
            <input type="hidden" name="id_cliente" value="{{ $inscripcion->id_cliente }}">
            <input type="hidden" name="id_membresia" value="{{ $inscripcion->id_membresia }}">
            <input type="hidden" name="fecha_inicio" value="{{ $inscripcion->fecha_inicio->format('Y-m-d') }}">
            <input type="hidden" name="precio_base" value="{{ $inscripcion->precio_base }}">

            <div class="row">
                <!-- Columna Principal -->
            <div class="col-lg-8">
                <!-- Ajustes Permitidos -->
                <div class="modern-card">
                    <div class="modern-card-header info">
                        <i class="fas fa-edit"></i>
                        <span>Ajustes Permitidos</span>
                    </div>
                    <div class="modern-card-body">
                        <!-- Guía para el Administrador -->
                        <div class="alert-modern warning mb-4" style="border-left: 4px solid var(--warning);">
                            <i class="fas fa-book-open"></i>
                            <div>
                                <strong class="d-block mb-2">📋 Guía de Edición Simple</strong>
                                <ul class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.8;">
                                    <li><strong>Estado:</strong> Cambiar solo si hay motivo válido (ej: Cancelar por solicitud del cliente, Suspender por mora).</li>
                                    <li><strong>Fecha de Vencimiento:</strong> Extender únicamente por cortesía, compensación o acuerdo especial. <em>No reducir.</em></li>
                                    <li><strong>Convenio:</strong> Solo disponible para membresía <strong>Mensual</strong>. Asociar si el cliente pertenece a una empresa con descuento.</li>
                                    <li><strong>Descuentos:</strong> Aplicar solo con autorización. Seleccionar motivo correspondiente.</li>
                                    <li><strong>Observaciones:</strong> Documentar SIEMPRE el motivo del cambio para trazabilidad.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert-modern info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <small><strong>Nota:</strong> Para cambiar de membresía (upgrade/downgrade), usa la pestaña "Cambiar Plan".</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on"></i> Estado
                                    <span class="required-indicator"></span>
                                </label>
                                <select class="form-control @error('id_estado') is-invalid @enderror" 
                                        id="id_estado" name="id_estado" required>
                                    <option value="">-- Seleccionar Estado --</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado->codigo }}" {{ old('id_estado', $inscripcion->id_estado) == $estado->codigo ? 'selected' : '' }}>
                                            {{ $estado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_estado')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-calendar-plus"></i> Fecha Vencimiento
                                    <span class="required-indicator"></span>
                                </label>
                                <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                       id="fecha_vencimiento" name="fecha_vencimiento" 
                                       value="{{ old('fecha_vencimiento', $inscripcion->fecha_vencimiento->format('Y-m-d')) }}" 
                                       min="{{ $inscripcion->fecha_inicio->format('Y-m-d') }}"
                                       required>
                                <small class="text-muted mt-1">Puedes extender la fecha si es necesario</small>
                                @error('fecha_vencimiento')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Convenio y Descuentos -->
                <div class="modern-card">
                    <div class="modern-card-header warning">
                        <i class="fas fa-percent"></i>
                        <span>Convenio y Descuentos</span>
                    </div>
                    <div class="modern-card-body">
                        @php
                            $esMensual = strtolower($inscripcion->membresia->nombre ?? '') === 'mensual';
                        @endphp
                        
                        <!-- Convenio (Solo para Mensual) -->
                        @if($esMensual)
                        <div class="alert-modern success mb-4" style="border-left: 4px solid var(--success);">
                            <i class="fas fa-building"></i>
                            <div>
                                <strong>Convenio Empresarial</strong>
                                <p class="mb-0" style="font-size: 0.85rem;">Esta membresía mensual puede asociarse a un convenio con descuento especial.</p>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-handshake"></i> Convenio
                                </label>
                                <select class="form-control select2-convenio @error('id_convenio') is-invalid @enderror" 
                                        id="id_convenio" name="id_convenio">
                                    <option value="">-- Sin Convenio --</option>
                                    @foreach($convenios as $convenio)
                                        <option value="{{ $convenio->id }}" 
                                                data-descuento="{{ $convenio->porcentaje_descuento }}"
                                                {{ old('id_convenio', $inscripcion->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                            {{ $convenio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($inscripcion->convenio)
                                    <small class="text-success mt-1 d-block">
                                        <i class="fas fa-check-circle"></i> Actualmente: {{ $inscripcion->convenio->nombre }}
                                    </small>
                                @else
                                    <small class="text-muted mt-1 d-block">Busca y selecciona un convenio</small>
                                @endif
                                @error('id_convenio')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr class="my-4">
                        @else
                        <input type="hidden" name="id_convenio" value="">
                        <div class="alert-modern info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <small>Los convenios solo aplican para membresías <strong>Mensuales</strong>.</small>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign"></i> Monto Descuento Manual
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                           id="descuento_aplicado" name="descuento_aplicado" step="1" min="0" 
                                           max="{{ $inscripcion->precio_base }}"
                                           value="{{ old('descuento_aplicado', $inscripcion->descuento_aplicado) }}" 
                                           placeholder="0">
                                </div>
                                <small class="text-muted mt-1">Máximo: ${{ number_format($inscripcion->precio_base, 0, ',', '.') }}</small>
                                @error('descuento_aplicado')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-tag"></i> Motivo Descuento
                                </label>
                                <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                        id="id_motivo_descuento" name="id_motivo_descuento">
                                    <option value="">-- Sin Motivo --</option>
                                    @foreach($motivos as $motivo)
                                        <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento', $inscripcion->id_motivo_descuento) == $motivo->id ? 'selected' : '' }}>
                                            {{ $motivo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_motivo_descuento')
                                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Precio Final Calculado -->
                        <div class="precio-final-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Precio Final</small>
                                    <div class="precio-calculo">
                                        <span class="precio-base">${{ number_format($inscripcion->precio_base, 0, ',', '.') }}</span>
                                        <span class="operador">-</span>
                                        <span class="descuento" id="descuentoDisplay">${{ number_format($inscripcion->descuento_aplicado ?? 0, 0, ',', '.') }}</span>
                                        <span class="operador">=</span>
                                    </div>
                                </div>
                                <div class="precio-final-valor" id="precioFinalDisplay">
                                    ${{ number_format(($inscripcion->precio_base - ($inscripcion->descuento_aplicado ?? 0)), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="modern-card">
                    <div class="modern-card-header" style="background: var(--gray-600); color: white;">
                        <i class="fas fa-align-left"></i>
                        <span>Observaciones</span>
                    </div>
                    <div class="modern-card-body">
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Notas adicionales sobre esta inscripción...">{{ old('observaciones', $inscripcion->observaciones) }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Columna Lateral - Sistema de Pausas -->
            <div class="col-lg-4">
                <!-- Estado de Pausa -->
                <div class="modern-card">
                    <div class="modern-card-header {{ $inscripcion->pausada ? ($inscripcion->pausa_indefinida ? 'danger' : 'warning') : 'success' }}">
                        <i class="fas fa-{{ $inscripcion->pausada ? 'pause-circle' : 'play-circle' }}"></i>
                        <span>Congelar Membresía</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="alert-modern info mb-3" style="font-size: 0.85em;">
                            <i class="fas fa-snowflake"></i>
                            <small>Congela temporalmente la membresía. Los días congelados se agregan al vencimiento al reactivar.</small>
                        </div>
                        @if ($inscripcion->pausada)
                            <!-- Estado: PAUSADA -->
                            <div class="pause-status-card {{ $inscripcion->pausa_indefinida ? 'indefinite' : 'paused' }}">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="pause-option-icon {{ $inscripcion->pausa_indefinida ? 'indefinite' : 'days' }}">
                                        <i class="fas fa-{{ $inscripcion->pausa_indefinida ? 'infinity' : 'hourglass-half' }}"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Membresía Congelada</h5>
                                        <small class="text-muted">
                                            @if($inscripcion->pausa_indefinida)
                                                Hasta nuevo aviso
                                            @else
                                                Por {{ $inscripcion->dias_pausa }} días
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <div class="pause-info-box">
                                    <div class="pause-info-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <div>
                                            <small class="text-muted d-block">Inicio del congelamiento</small>
                                            <strong>{{ $inscripcion->fecha_pausa_inicio ? $inscripcion->fecha_pausa_inicio->format('d/m/Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    @if(!$inscripcion->pausa_indefinida)
                                        <div class="pause-info-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <div>
                                                <small class="text-muted d-block">Fin estimado</small>
                                                <strong>{{ $inscripcion->fecha_pausa_fin ? $inscripcion->fecha_pausa_fin->format('d/m/Y') : 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                    @if($inscripcion->razon_pausa)
                                        <div class="pause-info-item">
                                            <i class="fas fa-comment-alt"></i>
                                            <div>
                                                <small class="text-muted d-block">Razón</small>
                                                <strong>{{ $inscripcion->razon_pausa }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" class="btn-modern success w-100 mt-4" id="btnReanudar">
                                    <i class="fas fa-play-circle"></i>
                                    Reactivar Membresía
                                </button>

                                <div class="alert-modern info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <small>Al reactivar, se extenderá la fecha de vencimiento por los días que estuvo congelada.</small>
                                </div>
                            </div>
                        @else
                            <!-- Estado: ACTIVA -->
                            @php
                                $maxPausas = $inscripcion->max_pausas_permitidas ?? 2;
                                $pausasRealizadas = $inscripcion->pausas_realizadas ?? 0;
                                $puedeUsarPausas = $inscripcion->id_estado == 100 && $pausasRealizadas < $maxPausas;
                            @endphp

                            <div class="pause-status-card active">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="pause-option-icon days" style="background: rgba(0, 191, 142, 0.12); color: var(--success);">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Membresía Activa</h5>
                                        <small class="text-muted">Funcionando normalmente</small>
                                    </div>
                                </div>

                                <!-- Contador de Pausas -->
                                <div class="pause-counter">
                                    <div class="pause-counter-number">
                                        {{ $maxPausas - $pausasRealizadas }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">Pausas Disponibles</div>
                                        <small class="text-muted">de {{ $maxPausas }} permitidas</small>
                                    </div>
                                </div>

                                @if($puedeUsarPausas)
                                    <hr class="my-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-pause me-2"></i>Pausar Membresía
                                    </h6>

                                    <!-- Opciones de Pausa -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-4">
                                            <div class="pause-option" data-dias="7">
                                                <div class="pause-option-icon days">
                                                    <i class="fas fa-hourglass-start"></i>
                                                </div>
                                                <div class="pause-option-title">7</div>
                                                <div class="pause-option-desc">días</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="pause-option" data-dias="14">
                                                <div class="pause-option-icon days">
                                                    <i class="fas fa-hourglass-half"></i>
                                                </div>
                                                <div class="pause-option-title">14</div>
                                                <div class="pause-option-desc">días</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="pause-option" data-dias="30">
                                                <div class="pause-option-icon days">
                                                    <i class="fas fa-hourglass-end"></i>
                                                </div>
                                                <div class="pause-option-title">30</div>
                                                <div class="pause-option-desc">días</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Opción Indefinida -->
                                    <div class="pause-option indefinite mb-3" data-dias="indefinida">
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <div class="pause-option-icon indefinite" style="margin: 0;">
                                                <i class="fas fa-infinity"></i>
                                            </div>
                                            <div class="text-start">
                                                <div class="pause-option-title">Hasta Nuevo Aviso</div>
                                                <div class="pause-option-desc">Pausa indefinida (requiere descripción)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Razón de Pausa (para indefinida) -->
                                    <div id="razonPausaContainer" style="display: none;">
                                        <label class="form-label">
                                            <i class="fas fa-comment-alt"></i> Descripción / Razón
                                            <span class="required-indicator"></span>
                                        </label>
                                        <textarea class="form-control" id="razonPausaInput" rows="3" 
                                                  placeholder="Describa el motivo de la pausa indefinida (obligatorio)..."></textarea>
                                        <small class="text-muted">Esta descripción es obligatoria para pausas indefinidas.</small>
                                    </div>

                                    <!-- Botón Confirmar -->
                                    <button type="button" class="btn-modern warning w-100 mt-3" id="btnConfirmarPausa" disabled>
                                        <i class="fas fa-pause-circle"></i>
                                        Confirmar Pausa
                                    </button>
                                @else
                                    @if($pausasRealizadas >= $maxPausas)
                                        <div class="alert-modern danger mt-4">
                                            <i class="fas fa-ban"></i>
                                            <div>
                                                <strong>Límite alcanzado</strong>
                                                <small class="d-block">No quedan pausas disponibles para esta inscripción.</small>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @if(($inscripcion->dias_compensacion ?? 0) > 0)
                                    <div class="alert-modern info mt-3">
                                        <i class="fas fa-calendar-plus"></i>
                                        <div>
                                            <strong>Días compensados</strong>
                                            <small class="d-block">Se han agregado {{ $inscripcion->dias_compensacion }} días por pausas anteriores.</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Resumen Rápido -->
                <div class="modern-card">
                    <div class="modern-card-header primary">
                        <i class="fas fa-info-circle"></i>
                        <span>Resumen</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="pause-info-box">
                            <div class="pause-info-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <small class="text-muted d-block">Días restantes</small>
                                    <strong class="{{ $inscripcion->dias_restantes < 7 ? 'text-danger' : '' }}">
                                        {{ $inscripcion->dias_restantes }} días
                                    </strong>
                                </div>
                            </div>
                            <div class="pause-info-item">
                                <i class="fas fa-dollar-sign"></i>
                                <div>
                                    <small class="text-muted d-block">Precio final</small>
                                    <strong>${{ number_format($inscripcion->precio_final ?? ($inscripcion->precio_base - $inscripcion->descuento_aplicado), 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="pause-info-item">
                                <i class="fas fa-dumbbell"></i>
                                <div>
                                    <small class="text-muted d-block">Membresía</small>
                                    <strong>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="modern-card">
            <div class="modern-card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn-outline-modern">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-modern primary" style="font-size: 1.1em;">
                        <i class="fas fa-save me-2"></i> Actualizar Inscripción
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div><!-- Fin modo-edicion -->

    <!-- ========================================== -->
    <!-- MODO 2: CAMBIO DE PLAN                    -->
    <!-- ========================================== -->
    @if($inscripcion->id_estado == 100 && !$inscripcion->pausada)
    <div class="mode-content" id="modo-cambio">
        <div class="row">
            <!-- Columna Principal: Selección de Nuevo Plan -->
            <div class="col-lg-8">
                <div class="modern-card">
                    <div class="modern-card-header success">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Seleccionar Nuevo Plan</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="alert-modern info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>¿Cómo funciona el cambio de plan?</strong>
                                <p class="mb-0">El monto que ya pagaste ($<span id="creditoDisponible">{{ number_format($inscripcion->monto_pagado, 0, ',', '.') }}</span>) se usará como crédito para el nuevo plan. Solo pagarás la diferencia.</p>
                            </div>
                        </div>

                        <h6 class="mb-3 fw-bold"><i class="fas fa-list me-2"></i>Planes Disponibles</h6>
                        
                        <div class="row g-3" id="planesDisponibles">
                            <!-- Los planes se cargarán dinámicamente -->
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                <p class="text-muted mt-2">Cargando planes disponibles...</p>
                            </div>
                        </div>

                        <!-- Motivo del cambio -->
                        <div class="mt-4" id="motivoCambioContainer" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-comment-alt"></i> Motivo del cambio (opcional)
                            </label>
                            <textarea class="form-control" id="motivoCambio" rows="2" 
                                      placeholder="Ej: El cliente desea más tiempo de acceso..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral: Resumen del Cambio -->
            <div class="col-lg-4">
                <div class="modern-card sticky-top" style="top: 20px;">
                    <div class="modern-card-header warning">
                        <i class="fas fa-calculator"></i>
                        <span>Resumen del Cambio</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="cambio-resumen" id="resumenCambio">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-hand-pointer fa-2x mb-3" style="opacity: 0.3;"></i>
                                <p>Selecciona un plan para ver el resumen</p>
                            </div>
                        </div>

                        <!-- Pago de Diferencia (solo si upgrade) -->
                        <div id="seccionPagoDiferencia" style="display: none;" class="mt-4">
                            <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Pago de Diferencia</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Método de Pago</label>
                                <select class="form-control" id="metodoPagoCambio">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodosPago ?? [] as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Monto a Abonar</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="montoAbonoCambio" 
                                           min="0" step="1" placeholder="0">
                                </div>
                                <small class="text-muted">Diferencia a pagar: $<span id="diferenciaPagar">0</span></small>
                            </div>
                        </div>

                        <!-- Botón Confirmar Cambio -->
                        <button type="button" class="btn-modern success w-100 mt-3" id="btnConfirmarCambio" disabled>
                            <i class="fas fa-exchange-alt me-2"></i> Confirmar Cambio de Plan
                        </button>
                        
                        <p class="text-muted text-center mt-2" style="font-size: 0.8em;">
                            <i class="fas fa-info-circle me-1"></i>
                            El plan actual quedará marcado como "Cambiado"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Fin modo-cambio -->
    @endif

    <!-- Modal Confirmar Pausa -->
    <div class="modal fade modal-modern" id="modalConfirmarPausa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="modalPausaHeader">
                    <h5 class="modal-title">
                        <i class="fas fa-pause-circle me-2"></i>Confirmar Pausa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="resumenPausaModal"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-modern" data-bs-dismiss="modal" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn-modern warning" id="btnEjecutarPausa">
                        <i class="fas fa-pause-circle me-1"></i> Confirmar Pausa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Reanudación -->
    <div class="modal fade modal-modern" id="modalConfirmarReanudar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-play-circle me-2"></i>Reactivar Membresía
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="pause-option-icon days mx-auto" style="width: 80px; height: 80px; font-size: 2em; background: rgba(0, 191, 142, 0.12); color: var(--success);">
                            <i class="fas fa-play"></i>
                        </div>
                        <h4 class="mt-3">¿Reactivar esta membresía?</h4>
                        <p class="text-muted">La membresía volverá a estar activa inmediatamente.</p>
                    </div>
                    <div class="alert-modern info">
                        <i class="fas fa-calendar-plus"></i>
                        <div>
                            <strong>Compensación automática</strong>
                            <p class="mb-0">La fecha de vencimiento se extenderá por los días que estuvo congelada.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-modern" data-bs-dismiss="modal" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn-modern success" id="btnEjecutarReanudar">
                        <i class="fas fa-play-circle me-1"></i> Reactivar Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
<script>
const INSCRIPCION_UUID = '{{ $inscripcion->uuid }}';
const PRECIO_BASE = {{ $inscripcion->precio_base }};
let diasPausaSeleccionados = null;
let esPausaIndefinida = false;

// Función global para formatear números
function formatNumber(num) {
    return num.toLocaleString('es-CL', { maximumFractionDigits: 0 });
}

// Inicializar Select2 para convenios
$(document).ready(function() {
    $('.select2-convenio').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar convenio...',
        allowClear: true,
        language: 'es',
        width: '100%'
    });
});

// Sistema de Notificaciones Toast
const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    
    show(type, title, message, duration = 4000) {
        this.init();
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <i class="${icons[type] || icons.success}"></i>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        this.container.appendChild(toast);
        
        // Auto-remove after duration
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    success(title, message) {
        this.show('success', title, message);
    },
    
    error(title, message) {
        this.show('error', title, message, 6000);
    },
    
    warning(title, message) {
        this.show('warning', title, message, 5000);
    }
};

// ============================================
// SISTEMA DE TABS (Edición Simple / Cambio Plan)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const modeTabs = document.querySelectorAll('.mode-tab');
    const modeContents = document.querySelectorAll('.mode-content');

    modeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const mode = this.dataset.mode;
            
            // Actualizar tabs activos
            modeTabs.forEach(t => t.classList.remove('active', 'upgrade'));
            this.classList.add('active');
            if (mode === 'cambio') this.classList.add('upgrade');
            
            // Mostrar contenido correspondiente
            modeContents.forEach(content => content.classList.remove('active'));
            document.getElementById(`modo-${mode}`)?.classList.add('active');
            
            // Si es cambio de plan, cargar datos
            if (mode === 'cambio') {
                cargarPlanesDisponibles();
            }
        });
    });
});

// ============================================
// SISTEMA DE CAMBIO DE PLAN
// ============================================
let planSeleccionado = null;
let datosInscripcion = null;
let planesDisponibles = [];

function cargarPlanesDisponibles() {
    const container = document.getElementById('planesDisponibles');
    if (!container) return;
    
    // Mostrar loading
    container.innerHTML = `
        <div class="col-12 text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="text-muted mt-2">Cargando planes disponibles...</p>
        </div>
    `;
    
    fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/info-cambio-plan`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            datosInscripcion = data.inscripcion;
            planesDisponibles = data.membresias_disponibles;
            
            // Actualizar crédito disponible
            document.getElementById('creditoDisponible').textContent = 
                formatNumber(data.credito_disponible);
            
            renderizarPlanes();
        } else {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert-modern danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>${data.message || 'Error al cargar planes'}</span>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="col-12">
                <div class="alert-modern danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Error de conexión al cargar planes</span>
                </div>
            </div>
        `;
    });
}

function renderizarPlanes() {
    const container = document.getElementById('planesDisponibles');
    if (!container || !planesDisponibles.length) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert-modern warning">
                    <i class="fas fa-info-circle"></i>
                    <span>No hay otros planes disponibles para cambiar.</span>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    const precioActual = datosInscripcion.precio_actual;
    
    planesDisponibles.forEach(plan => {
        const diferencia = plan.precio - datosInscripcion.monto_pagado;
        const esUpgrade = plan.precio > precioActual;
        const badgeClass = esUpgrade ? 'upgrade' : 'downgrade';
        const badgeText = esUpgrade ? 'UPGRADE' : 'MENOR';
        
        const duracionTexto = plan.duracion_dias 
            ? `${plan.duracion_dias} días` 
            : `${plan.duracion_meses} ${plan.duracion_meses === 1 ? 'mes' : 'meses'}`;
        
        html += `
            <div class="col-md-6">
                <div class="plan-card" data-plan-id="${plan.id}" onclick="seleccionarPlan(${plan.id})">
                    <span class="plan-card-badge ${badgeClass}">${badgeText}</span>
                    <div class="plan-card-header">
                        <div>
                            <div class="plan-card-name">${plan.nombre}</div>
                            <div class="plan-card-duration">
                                <i class="fas fa-clock"></i>
                                <span>${duracionTexto}</span>
                            </div>
                        </div>
                        <div class="plan-card-price">$${formatNumber(plan.precio)}</div>
                    </div>
                    ${plan.descripcion ? `<p class="text-muted mb-0" style="font-size: 0.85em;">${plan.descripcion}</p>` : ''}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function seleccionarPlan(planId) {
    // Deseleccionar todos
    document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Seleccionar el clickeado
    const card = document.querySelector(`.plan-card[data-plan-id="${planId}"]`);
    if (card) card.classList.add('selected');
    
    planSeleccionado = planesDisponibles.find(p => p.id === planId);
    
    if (planSeleccionado) {
        actualizarResumenCambio();
        document.getElementById('motivoCambioContainer').style.display = 'block';
        document.getElementById('btnConfirmarCambio').disabled = false;
    }
}

function actualizarResumenCambio() {
    const resumenDiv = document.getElementById('resumenCambio');
    const seccionPago = document.getElementById('seccionPagoDiferencia');
    
    if (!planSeleccionado || !datosInscripcion) return;
    
    const precioNuevo = planSeleccionado.precio;
    const creditoDisponible = datosInscripcion.monto_pagado;
    const diferencia = precioNuevo - creditoDisponible;
    const esUpgrade = diferencia > 0;
    
    resumenDiv.innerHTML = `
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Plan Actual</span>
            <span class="cambio-resumen-value">${datosInscripcion.membresia_actual}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Nuevo Plan</span>
            <span class="cambio-resumen-value" style="color: var(--info);">${planSeleccionado.nombre}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Precio Nuevo Plan</span>
            <span class="cambio-resumen-value">$${formatNumber(precioNuevo)}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">
                <i class="fas fa-minus-circle text-success me-1"></i>
                Tu Crédito
            </span>
            <span class="cambio-resumen-value credito">-$${formatNumber(creditoDisponible)}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label fw-bold">
                ${esUpgrade ? 'Diferencia a Pagar' : 'Crédito a Favor'}
            </span>
            <span class="cambio-resumen-value diferencia ${esUpgrade ? 'positiva' : 'negativa'}">
                ${esUpgrade ? '' : '+'}$${formatNumber(Math.abs(diferencia))}
            </span>
        </div>
    `;
    
    // Mostrar/ocultar sección de pago
    if (esUpgrade) {
        seccionPago.style.display = 'block';
        document.getElementById('diferenciaPagar').textContent = formatNumber(diferencia);
        document.getElementById('montoAbonoCambio').max = diferencia;
        document.getElementById('montoAbonoCambio').value = diferencia;
    } else {
        seccionPago.style.display = 'none';
    }
}

// Confirmar cambio de plan
document.addEventListener('click', function(e) {
    if (e.target.closest('#btnConfirmarCambio')) {
        ejecutarCambioPlan();
    }
});

function ejecutarCambioPlan() {
    if (!planSeleccionado) {
        Toast.error('Error', 'Debes seleccionar un plan');
        return;
    }
    
    const diferencia = planSeleccionado.precio - datosInscripcion.monto_pagado;
    const esUpgrade = diferencia > 0;
    
    // Validar método de pago si es upgrade
    if (esUpgrade) {
        const metodoPago = document.getElementById('metodoPagoCambio').value;
        if (!metodoPago) {
            Toast.warning('Atención', 'Selecciona un método de pago para la diferencia');
            return;
        }
    }
    
    // Confirmar
    if (!confirm(`¿Confirmar cambio de plan a "${planSeleccionado.nombre}"?`)) {
        return;
    }
    
    mostrarLoading(true);
    
    const body = {
        id_membresia_nueva: planSeleccionado.id,
        motivo_cambio: document.getElementById('motivoCambio')?.value || '',
    };
    
    if (esUpgrade) {
        body.id_metodo_pago = document.getElementById('metodoPagoCambio').value;
        body.monto_abonado = parseFloat(document.getElementById('montoAbonoCambio').value) || 0;
        body.diferencia_positiva = true;
    }
    
    fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/cambiar-plan`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify(body),
    })
    .then(response => response.json())
    .then(data => {
        mostrarLoading(false);
        if (data.success) {
            Toast.success('¡Plan Cambiado!', data.message);
            setTimeout(() => {
                window.location.href = data.redirect_url || '/admin/inscripciones';
            }, 1500);
        } else {
            Toast.error('Error', data.message || 'No se pudo cambiar el plan');
        }
    })
    .catch(error => {
        mostrarLoading(false);
        console.error('Error:', error);
        Toast.error('Error de conexión', 'No se pudo procesar la solicitud');
    });
}

// ============================================
// FUNCIONALIDAD EXISTENTE (Edición Simple)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Calcular precio final dinámicamente
    const descuentoInput = document.getElementById('descuento_aplicado');
    const descuentoDisplay = document.getElementById('descuentoDisplay');
    const precioFinalDisplay = document.getElementById('precioFinalDisplay');
    const convenioSelect = document.getElementById('id_convenio');

    function actualizarPrecioFinal() {
        const descuento = parseFloat(descuentoInput.value) || 0;
        const precioFinal = PRECIO_BASE - descuento;
        
        descuentoDisplay.textContent = '$' + formatNumber(descuento);
        precioFinalDisplay.textContent = '$' + formatNumber(Math.max(0, precioFinal));
        
        if (descuento > 0) {
            precioFinalDisplay.style.color = 'var(--warning)';
        } else {
            precioFinalDisplay.style.color = 'var(--success)';
        }
    }

    if (descuentoInput) {
        descuentoInput.addEventListener('input', actualizarPrecioFinal);
    }

    // Manejar cambio de convenio (solo para membresías mensuales)
    if (convenioSelect) {
        convenioSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const porcentajeDescuento = parseFloat(selectedOption.dataset.descuento) || 0;
            
            if (porcentajeDescuento > 0) {
                // Calcular descuento basado en porcentaje del convenio
                const descuentoConvenio = Math.round(PRECIO_BASE * (porcentajeDescuento / 100));
                descuentoInput.value = descuentoConvenio;
                
                // Mostrar notificación
                Toast.success(
                    'Convenio Aplicado',
                    `Se aplicó ${porcentajeDescuento}% de descuento = $${formatNumber(descuentoConvenio)}`
                );
            } else {
                // Si se quita el convenio, limpiar descuento
                descuentoInput.value = 0;
                Toast.warning('Convenio Removido', 'Se quitó el descuento del convenio');
            }
            
            actualizarPrecioFinal();
        });
    }

    // Sistema de Pausas/Congelamiento
    const pauseOptions = document.querySelectorAll('.pause-option');
    const btnConfirmarPausa = document.getElementById('btnConfirmarPausa');
    const razonContainer = document.getElementById('razonPausaContainer');
    const razonInput = document.getElementById('razonPausaInput');
    const btnReanudar = document.getElementById('btnReanudar');

    pauseOptions.forEach(option => {
        option.addEventListener('click', function() {
            pauseOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');

            const dias = this.dataset.dias;
            esPausaIndefinida = dias === 'indefinida';
            diasPausaSeleccionados = esPausaIndefinida ? null : parseInt(dias);

            if (razonContainer) {
                razonContainer.style.display = esPausaIndefinida ? 'block' : 'none';
            }

            actualizarBotonPausa();
        });
    });

    if (razonInput) {
        razonInput.addEventListener('input', actualizarBotonPausa);
    }

    function actualizarBotonPausa() {
        if (!btnConfirmarPausa) return;

        const tieneSeleccion = diasPausaSeleccionados !== null || esPausaIndefinida;
        const razonValida = !esPausaIndefinida || (razonInput && razonInput.value.trim().length >= 5);

        btnConfirmarPausa.disabled = !(tieneSeleccion && razonValida);
    }

    // Mostrar modal de confirmación de pausa
    if (btnConfirmarPausa) {
        btnConfirmarPausa.addEventListener('click', function() {
            const modalHeader = document.getElementById('modalPausaHeader');
            const resumenModal = document.getElementById('resumenPausaModal');

            if (esPausaIndefinida) {
                modalHeader.className = 'modal-header';
                modalHeader.style.background = 'linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%)';
                modalHeader.style.color = 'white';
                
                resumenModal.innerHTML = `
                    <div class="text-center mb-4">
                        <div class="pause-option-icon indefinite mx-auto" style="width: 80px; height: 80px; font-size: 2em;">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <h4 class="mt-3">Congelamiento Indefinido</h4>
                        <p class="text-muted">La membresía quedará congelada hasta que se reactive manualmente</p>
                    </div>
                    <div class="alert-modern warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Motivo:</strong>
                            <p class="mb-0">${razonInput ? razonInput.value : ''}</p>
                        </div>
                    </div>
                `;
            } else {
                const fechaFin = new Date();
                fechaFin.setDate(fechaFin.getDate() + diasPausaSeleccionados);

                modalHeader.className = 'modal-header';
                modalHeader.style.background = 'linear-gradient(135deg, var(--warning) 0%, #ffb800 100%)';
                modalHeader.style.color = 'var(--gray-800)';

                resumenModal.innerHTML = `
                    <div class="text-center mb-4">
                        <div class="pause-option-icon days mx-auto" style="width: 80px; height: 80px; font-size: 2em;">
                            <i class="fas fa-snowflake"></i>
                        </div>
                        <h4 class="mt-3">Congelar por ${diasPausaSeleccionados} días</h4>
                    </div>
                    <div class="pause-info-box">
                        <div class="pause-info-item">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <small class="text-muted d-block">Desde</small>
                                <strong>${new Date().toLocaleDateString('es-CL')}</strong>
                            </div>
                        </div>
                        <div class="pause-info-item">
                            <i class="fas fa-calendar-times"></i>
                            <div>
                                <small class="text-muted d-block">Hasta</small>
                                <strong>${fechaFin.toLocaleDateString('es-CL')}</strong>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Mostrar modal
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(document.getElementById('modalConfirmarPausa')).show();
            } else {
                $('#modalConfirmarPausa').modal('show');
            }
        });
    }

    // Ejecutar congelamiento - usando delegación de eventos para mejor compatibilidad
    document.addEventListener('click', function(e) {
        if (e.target.closest('#btnEjecutarPausa')) {
            e.preventDefault();
            e.stopPropagation();
            
            // Obtener valor del input de razón directamente del DOM
            const razonInputValue = document.getElementById('razonPausaInput');
            const razonTexto = razonInputValue ? razonInputValue.value : '';
            
            console.log('Ejecutando pausa...', { 
                dias: diasPausaSeleccionados, 
                indefinida: esPausaIndefinida,
                razon: razonTexto 
            });
            
            // Cerrar modal primero
            cerrarModales();
            mostrarLoading(true);

            fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/pausar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    dias: diasPausaSeleccionados,
                    razon: razonTexto,
                    indefinida: esPausaIndefinida,
                }),
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                mostrarLoading(false);
                console.log('Respuesta pausa:', data);
                if (data.success) {
                    Toast.success('¡Membresía Congelada!', data.message || 'La membresía ha sido congelada exitosamente.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Toast.error('Error', data.message || 'No se pudo congelar la membresía.');
                }
            })
            .catch(error => {
                mostrarLoading(false);
                console.error('Error:', error);
                Toast.error('Error de conexión', 'No se pudo procesar la solicitud.');
            });
        }
    });

    // Botón reanudar - mostrar modal
    if (btnReanudar) {
        btnReanudar.addEventListener('click', function() {
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(document.getElementById('modalConfirmarReanudar')).show();
            } else {
                $('#modalConfirmarReanudar').modal('show');
            }
        });
    }

    // Ejecutar reactivación - usando delegación de eventos para mejor compatibilidad
    document.addEventListener('click', function(e) {
        if (e.target.closest('#btnEjecutarReanudar')) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Ejecutando reactivación...');
            
            cerrarModales();
            mostrarLoading(true);

            fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/reanudar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
            })
            .then(response => response.json())
            .then(data => {
                mostrarLoading(false);
                console.log('Respuesta reanudar:', data);
                if (data.success) {
                    Toast.success('¡Membresía Reactivada!', data.message || 'La membresía ha sido reactivada exitosamente.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Toast.error('Error', data.message || 'No se pudo reactivar la membresía.');
                }
            })
            .catch(error => {
                mostrarLoading(false);
                console.error('Error:', error);
                Toast.error('Error de conexión', 'No se pudo procesar la solicitud.');
            });
        }
    });

    // Cerrar todos los modales
    function cerrarModales() {
        document.querySelectorAll('.modal').forEach(modal => {
            if (typeof bootstrap !== 'undefined') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            } else {
                $(modal).modal('hide');
            }
        });
    }

    // Loading overlay
    function mostrarLoading(mostrar) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.toggle('active', mostrar);
        }
    }

    // Formateador de precios
    if (typeof PrecioFormatter !== 'undefined') {
        PrecioFormatter.iniciarCampo('descuento_aplicado', false);
    }
});
</script>
@stop
