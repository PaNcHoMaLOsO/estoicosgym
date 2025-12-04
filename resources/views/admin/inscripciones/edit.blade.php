@extends('adminlte::page')

@section('title', 'Editar Inscripción - EstóicosGym')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
        padding: 20px 25px;
        border-radius: 14px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
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
        font-size: 1.4em; 
        font-weight: 700; 
        margin-bottom: 5px;
        letter-spacing: -0.3px;
    }
    .hero-subtitle { 
        font-size: 0.9em; 
        opacity: 0.9;
        font-weight: 400;
    }

    /* MODERN CARDS */
    .modern-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        border: none;
        overflow: hidden;
        margin-bottom: 18px;
        transition: all 0.3s ease;
    }
    .modern-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .modern-card-header {
        padding: 12px 18px;
        border-bottom: 1px solid var(--gray-200);
        font-weight: 600;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 10px;
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
        padding: 16px 18px;
    }

    /* FORM STYLES */
    .form-label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85em;
    }
    .form-label i {
        color: var(--info);
        font-size: 0.85em;
    }
    .form-control, .form-select, select.form-control {
        border-radius: 8px;
        border: 2px solid var(--gray-200);
        padding: 8px 12px;
        transition: all 0.3s ease;
        font-size: 0.9em;
        height: auto;
        min-height: 38px;
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
        border-radius: 8px 0 0 8px;
        font-weight: 600;
        color: var(--gray-600);
        padding: 8px 12px;
        min-height: 38px;
    }
    .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }
    .text-danger { color: var(--accent) !important; }

    /* BUTTONS */
    .btn-modern {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9em;
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
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        transition: all 0.3s ease;
        font-size: 0.9em;
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
        border-radius: 10px;
        padding: 14px;
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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.1em;
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
        border-radius: 10px;
        padding: 14px;
        margin-top: 15px;
    }
    .pause-info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .pause-info-item:last-child {
        border-bottom: none;
    }
    .pause-info-item i {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        color: var(--info);
        font-size: 0.85em;
    }

    /* PAUSE COUNTER */
    .pause-counter {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--gray-100);
        padding: 12px 16px;
        border-radius: 10px;
        margin-top: 15px;
    }
    .pause-counter-number {
        font-size: 1.6em;
        font-weight: 800;
        color: var(--info);
    }
    .pause-counter-label {
        font-size: 0.85em;
        color: var(--gray-600);
    }

    /* ALERT MODERN */
    .alert-modern {
        border-radius: 10px;
        padding: 12px 16px;
        border: none;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9em;
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
        gap: 12px;
        padding: 12px;
        background: var(--gray-100);
        border-radius: 10px;
        border: 1px solid var(--gray-200);
    }
    .info-display-card.compact {
        padding: 10px;
    }
    .info-display-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(67, 97, 238, 0.12);
        color: var(--info);
        font-size: 1em;
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
        gap: 8px;
        margin-bottom: 18px;
        background: var(--gray-100);
        padding: 6px;
        border-radius: 12px;
    }
    .mode-tab {
        flex: 1;
        padding: 12px 18px;
        border: none;
        background: transparent;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.88em;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
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
        border-radius: 12px;
        padding: 14px;
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
        margin-bottom: 10px;
    }
    .plan-card-name {
        font-weight: 700;
        font-size: 1em;
        color: var(--gray-800);
    }
    .plan-card-price {
        font-weight: 800;
        font-size: 1.15em;
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
        border-radius: 12px;
        padding: 16px;
        border: 2px solid var(--gray-200);
    }
    .cambio-resumen-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.9em;
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

    /* DROPDOWN DE BÚSQUEDA TRASPASO */
    #resultadosBusquedaTraspaso {
        position: fixed !important;
        background: #fff;
        border: 2px solid #9b59b6;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        z-index: 999999 !important;
        min-width: 450px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #resultadosBusquedaTraspaso.dropdown-arriba {
        border-radius: 12px 12px 12px 12px;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.25), 0 5px 20px rgba(0,0,0,0.15);
    }
    #resultadosBusquedaTraspaso.dropdown-abajo {
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    }
    #resultadosBusquedaTraspaso .cliente-resultado-item {
        display: block;
        padding: 14px 18px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: all 0.2s;
        background: #fff;
    }
    #resultadosBusquedaTraspaso .cliente-resultado-item:hover {
        background: linear-gradient(135deg, #f8f4ff 0%, #f0e6ff 100%) !important;
    }
    #resultadosBusquedaTraspaso .cliente-resultado-item:last-child {
        border-bottom: none;
    }
    
    /* TOGGLE SWITCH PARA CRÉDITO */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        cursor: pointer;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255,255,255,0.3);
        transition: .3s;
        border-radius: 26px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .toggle-switch input:checked + .toggle-slider {
        background-color: rgba(255,255,255,0.5);
    }
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    .toggle-switch input:not(:checked) + .toggle-slider {
        background-color: rgba(0,0,0,0.2);
    }
    
    /* Card de crédito desactivada */
    .credito-plan-card.desactivado {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%) !important;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3) !important;
    }
    
    /* SWEETALERT2 CUSTOM STYLES */
    .swal2-popup.swal-estoicos {
        border-radius: 16px;
        font-family: inherit;
    }
    .swal2-popup.swal-estoicos .swal2-title {
        color: var(--primary);
        font-weight: 700;
    }
    .swal2-popup.swal-estoicos .swal2-html-container {
        color: var(--gray-600);
    }
    .swal2-popup.swal-estoicos .swal2-confirm {
        border-radius: 10px !important;
        font-weight: 600;
        padding: 12px 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .swal2-popup.swal-estoicos .swal2-cancel {
        border-radius: 10px !important;
        font-weight: 600;
        padding: 12px 24px;
    }
    .swal2-popup.swal-estoicos .swal2-icon {
        border-width: 3px;
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-success {
        border-color: var(--success);
        color: var(--success);
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: var(--success);
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-success .swal2-success-ring {
        border-color: rgba(0, 191, 142, 0.3);
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-warning {
        border-color: var(--warning);
        color: var(--warning);
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-error {
        border-color: var(--accent);
        color: var(--accent);
    }
    .swal2-popup.swal-estoicos .swal2-icon.swal2-question {
        border-color: var(--info);
        color: var(--info);
    }
    
    /* Botón restablecer hover */
    #btnRestablecer:hover {
        background: var(--warning) !important;
        color: white !important;
        border-color: var(--warning) !important;
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
            <i class="fas fa-arrow-circle-up"></i>
            <span>Mejorar Plan</span>
        </button>
        <button type="button" class="mode-tab" data-mode="traspaso">
            <i class="fas fa-exchange-alt"></i>
            <span>Traspasar</span>
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
                                <strong class="d-block mb-2">📋 Guía de Ajustes Permitidos</strong>
                                <ul class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.8;">
                                    <li><strong>Estado:</strong> Cambiar solo si hay motivo válido:
                                        <ul class="ps-3 mt-1" style="font-size: 0.82rem; color: var(--gray-600);">
                                            <li><span style="color: var(--accent);">Cancelar (103):</span> Solicitud del cliente o incumplimiento</li>
                                            <li><span style="color: var(--warning);">Suspender (104):</span> Mora prolongada o conducta</li>
                                            <li><span style="color: var(--info);">Pausar:</span> Usar botón de pausa (no cambiar estado manual)</li>
                                        </ul>
                                    </li>
                                    <li><strong>Fecha de Vencimiento:</strong> Extender únicamente por:
                                        <ul class="ps-3 mt-1" style="font-size: 0.82rem; color: var(--gray-600);">
                                            <li>Compensación por cierre del gimnasio</li>
                                            <li>Cortesía autorizada por gerencia</li>
                                            <li>Acuerdo especial documentado</li>
                                        </ul>
                                        <em style="color: var(--accent);">⚠️ Nunca reducir la fecha de vencimiento.</em>
                                    </li>
                                    <li><strong>Convenio:</strong> Solo disponible para membresía <strong>Mensual</strong>. El descuento se calcula automáticamente según el porcentaje configurado del convenio.</li>
                                    <li><strong>Descuentos Manuales:</strong> Aplicar solo con autorización. Siempre seleccionar el motivo correspondiente para auditoría.</li>
                                    <li><strong>Observaciones:</strong> <span style="color: var(--accent);">OBLIGATORIO</span> documentar el motivo de cada cambio para trazabilidad.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert-modern info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <small><strong>Nota:</strong> Para operaciones especiales, usa las pestañas correspondientes:</small>
                                <ul class="mb-0 mt-1 ps-3" style="font-size: 0.8rem;">
                                    <li><strong>Mejorar Plan:</strong> Cambiar a una membresía de mayor valor (upgrade)</li>
                                    <li><strong>Traspasar:</strong> Transferir la membresía a otro cliente</li>
                                </ul>
                            </div>
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
                                                data-descuento="{{ $convenio->descuento_porcentaje }}"
                                                data-descripcion="{{ $convenio->descripcion }}"
                                                data-tipo="{{ $convenio->tipo }}"
                                                data-monto="{{ $convenio->descuento_monto }}"
                                                {{ old('id_convenio', $inscripcion->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                            {{ $convenio->nombre }} @if($convenio->descuento_porcentaje > 0)({{ $convenio->descuento_porcentaje }}%)@elseif($convenio->descuento_monto > 0)(${{ number_format($convenio->descuento_monto, 0, ',', '.') }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Info del convenio seleccionado -->
                                <div id="convenioInfoBox" class="mt-2 p-2 rounded" style="display: none; background: linear-gradient(135deg, rgba(0,191,142,0.08) 0%, rgba(0,191,142,0.15) 100%); border: 1px solid rgba(0,191,142,0.3); font-size: 0.85rem;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-info-circle text-success"></i>
                                        <span id="convenioDescripcionText" class="text-muted"></span>
                                    </div>
                                    <div class="mt-1" id="convenioDescuentoInfo" style="font-size: 0.8rem;">
                                        <span class="badge" style="background: var(--success); color: white;">
                                            <i class="fas fa-percentage me-1"></i>
                                            <span id="convenioDescuentoValor"></span>
                                        </span>
                                    </div>
                                </div>
                                @if($inscripcion->convenio)
                                    <small class="text-success mt-1 d-block">
                                        <i class="fas fa-check-circle"></i> Actualmente: {{ $inscripcion->convenio->nombre }}
                                        @if($inscripcion->convenio->descuento_porcentaje > 0)
                                            ({{ $inscripcion->convenio->descuento_porcentaje }}% descuento)
                                        @elseif($inscripcion->convenio->descuento_monto > 0)
                                            (${{ number_format($inscripcion->convenio->descuento_monto, 0, ',', '.') }} descuento)
                                        @endif
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
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.inscripciones.index') }}" class="btn-outline-modern">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="button" class="btn-outline-modern" id="btnRestablecer" style="border-color: var(--warning); color: var(--warning);" onclick="restablecerFormulario()">
                            <i class="fas fa-undo me-2"></i> Restablecer
                        </button>
                    </div>
                    <button type="submit" class="btn-modern primary" style="font-size: 1.1em;">
                        <i class="fas fa-save me-2"></i> Actualizar Inscripción
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div><!-- Fin modo-edicion -->

    <!-- ========================================== -->
    <!-- MODO 2: MEJORA DE PLAN (UPGRADE)          -->
    <!-- ========================================== -->
    @if($inscripcion->id_estado == 100 && !$inscripcion->pausada)
    @php
        $infoMejora = [
            'monto_pagado' => $inscripcion->monto_pagado,
            'monto_pendiente' => $inscripcion->monto_pendiente,
            'precio_final' => $inscripcion->precio_final,
            'tiene_deuda' => $inscripcion->monto_pendiente > 0,
            'porcentaje_pagado' => $inscripcion->precio_final > 0 ? round(($inscripcion->monto_pagado / $inscripcion->precio_final) * 100) : 100,
        ];
    @endphp
    <div class="mode-content" id="modo-cambio">
        <div class="row">
            <!-- Columna Principal: Selección de Nuevo Plan -->
            <div class="col-lg-8">
                <div class="modern-card">
                    <div class="modern-card-header success">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Mejorar Plan</span>
                    </div>
                    <div class="modern-card-body">
                        
                        @if($infoMejora['tiene_deuda'])
                        <!-- Alerta de Deuda Pendiente -->
                        <div class="alert-modern danger mb-4" style="border-left: 4px solid var(--accent);">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <div>
                                <strong>⚠️ Deuda Pendiente en Plan Actual</strong>
                                <p class="mb-2">Esta inscripción tiene un saldo pendiente de <strong class="text-danger">${{ number_format($infoMejora['monto_pendiente'], 0, ',', '.') }}</strong></p>
                                <div class="d-flex align-items-center gap-3 mb-2" style="font-size: 0.85rem;">
                                    <span><i class="fas fa-money-bill-wave me-1"></i> Pagado: ${{ number_format($infoMejora['monto_pagado'], 0, ',', '.') }}</span>
                                    <span><i class="fas fa-receipt me-1"></i> Total: ${{ number_format($infoMejora['precio_final'], 0, ',', '.') }}</span>
                                    <span class="badge bg-warning text-dark">{{ $infoMejora['porcentaje_pagado'] }}% pagado</span>
                                </div>
                                <div class="form-check mt-2" style="padding: 10px; background: rgba(255,255,255,0.5); border-radius: 8px;">
                                    <input class="form-check-input" type="checkbox" id="ignorarDeudaMejora" onchange="toggleMejoraPlan()">
                                    <label class="form-check-label fw-bold" for="ignorarDeudaMejora">
                                        <i class="fas fa-unlock me-1"></i> Permitir mejora de plan aunque exista deuda
                                    </label>
                                    <small class="d-block text-muted mt-1">La deuda pendiente se sumará al nuevo plan</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="alert-modern info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>¿Cómo funciona la mejora de plan?</strong>
                                <p class="mb-0">Puedes mejorar a un plan de mayor valor. Como administrador, decides si aplicas el crédito ($<span id="creditoDisponible">{{ number_format($inscripcion->monto_pagado, 0, ',', '.') }}</span>) del plan anterior o si el cliente paga la totalidad del nuevo plan.</p>
                            </div>
                        </div>

                        <h6 class="mb-3 fw-bold"><i class="fas fa-list me-2"></i>Planes de Mayor Valor</h6>
                        
                        <div class="row g-3" id="planesDisponibles" @if($infoMejora['tiene_deuda']) style="opacity: 0.5; pointer-events: none;" @endif>
                            <!-- Los planes se cargarán dinámicamente -->
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                <p class="text-muted mt-2">Cargando planes disponibles...</p>
                            </div>
                        </div>

                        <!-- Motivo del cambio -->
                        <div class="mt-4" id="motivoCambioContainer" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-comment-alt"></i> Motivo de la mejora (opcional)
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
                        <span>Resumen de la Mejora</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="cambio-resumen" id="resumenCambio">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-hand-pointer fa-2x mb-3" style="opacity: 0.3;"></i>
                                <p>Selecciona un plan para ver el resumen</p>
                            </div>
                        </div>

                        <!-- Opciones de Pago para Upgrade -->
                        <div id="seccionPagoDiferencia" style="display: none;" class="mt-4">
                            <!-- Tarjeta de Crédito Disponible - DESTACADO -->
                            <div class="credito-plan-card mb-4" style="background: linear-gradient(135deg, #00bf8e 0%, #00a676 100%); border-radius: 12px; padding: 16px; color: white; box-shadow: 0 4px 15px rgba(0, 191, 142, 0.3);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                                        </div>
                                        <div>
                                            <small style="opacity: 0.9;">Crédito del Plan Anterior</small>
                                            <h4 class="mb-0" style="font-weight: 700;">$<span id="montoCredito">{{ number_format($inscripcion->monto_pagado, 0, ',', '.') }}</span></h4>
                                        </div>
                                    </div>
                                    <div class="credito-toggle-wrapper">
                                        <label class="toggle-switch" style="margin: 0;">
                                            <input type="checkbox" id="aplicarCredito" checked>
                                            <span class="toggle-slider" style="background: rgba(255,255,255,0.3);"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2" style="font-size: 0.85rem; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.2);">
                                    <i class="fas fa-check-circle"></i>
                                    <span id="creditoEstadoTexto">El crédito será aplicado al nuevo plan</span>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Pago</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-control" id="metodoPagoCambio" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodosPago ?? [] as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Pago: Completo o Parcial -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Pago <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check flex-grow-1" style="padding: 12px; background: rgba(0,191,142,0.08); border: 2px solid var(--success); border-radius: 10px;">
                                        <input class="form-check-input" type="radio" name="tipoPagoMejora" id="pagoCompleto" value="completo" checked onchange="actualizarTipoPago()">
                                        <label class="form-check-label fw-bold" for="pagoCompleto" style="color: var(--success);">
                                            <i class="fas fa-check-circle me-1"></i> Pago Completo
                                        </label>
                                        <small class="d-block text-muted mt-1">El cliente paga todo ahora</small>
                                    </div>
                                    <div class="form-check flex-grow-1" style="padding: 12px; background: rgba(240,165,0,0.08); border: 2px solid var(--warning); border-radius: 10px;">
                                        <input class="form-check-input" type="radio" name="tipoPagoMejora" id="pagoParcial" value="parcial" onchange="actualizarTipoPago()">
                                        <label class="form-check-label fw-bold" for="pagoParcial" style="color: var(--warning);">
                                            <i class="fas fa-clock me-1"></i> Pago Parcial
                                        </label>
                                        <small class="d-block text-muted mt-1">Abono inicial, queda saldo</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <span id="labelMontoPago">Monto a Pagar</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="montoAbonoCambio" 
                                           min="0" step="1" placeholder="0" onchange="validarMontoPago()">
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Total a pagar: $<span id="diferenciaPagar">0</span></small>
                                    <small id="saldoPendienteInfo" class="text-warning" style="display: none;">
                                        <i class="fas fa-exclamation-circle me-1"></i>Quedará saldo: $<span id="saldoPendienteMonto">0</span>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Advertencia de pago parcial -->
                            <div id="alertaPagoParcial" class="alert-modern warning mb-3" style="display: none; font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Pago Parcial</strong>
                                    <p class="mb-0">El cliente quedará con un saldo pendiente de $<span id="saldoPendienteAlerta">0</span>. Este monto deberá pagarse antes del vencimiento.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Confirmar Cambio -->
                        <button type="button" class="btn-modern success w-100 mt-3" id="btnConfirmarCambio" disabled>
                            <i class="fas fa-arrow-circle-up me-2"></i> Confirmar Mejora de Plan
                        </button>
                        
                        <p class="text-muted text-center mt-2" style="font-size: 0.8em;">
                            <i class="fas fa-info-circle me-1"></i>
                            El plan actual quedará marcado como "Mejorado"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Fin modo-cambio -->

    <!-- ========================================== -->
    <!-- MODO 3: TRASPASO DE MEMBRESÍA             -->
    <!-- ========================================== -->
    @php
        $infoTraspaso = $inscripcion->getInfoTraspaso();
    @endphp
    <div class="mode-content" id="modo-traspaso">
        <div class="row">
            <!-- Columna Principal: Búsqueda de Cliente -->
            <div class="col-lg-8">
                <div class="modern-card">
                    <div class="modern-card-header" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white;">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Traspasar Membresía</span>
                    </div>
                    <div class="modern-card-body">
                        <!-- Alerta de deuda pendiente -->
                        @if($infoTraspaso['tiene_deuda'])
                        <div class="alert-modern danger mb-3">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>⚠️ Deuda Pendiente</strong>
                                <p class="mb-1">Esta inscripción tiene un saldo pendiente de <strong>${{ number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') }}</strong></p>
                                <small>Pagado: ${{ number_format($infoTraspaso['monto_pagado'], 0, ',', '.') }} de ${{ number_format($infoTraspaso['monto_total'], 0, ',', '.') }} ({{ round($infoTraspaso['porcentaje_pagado']) }}%)</small>
                            </div>
                        </div>
                        
                        <!-- Opciones de flexibilidad -->
                        <div class="card mb-3" style="border: 2px solid var(--warning); border-radius: 10px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                            <div class="card-body py-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="ignorarDeudaCheck" onchange="toggleOpcionesDeuda()">
                                    <label class="form-check-label fw-bold" for="ignorarDeudaCheck">
                                        <i class="fas fa-unlock me-1"></i> Ignorar requisito de pago completo
                                    </label>
                                    <small class="d-block text-muted">Permite traspasar aunque exista deuda pendiente</small>
                                </div>
                                
                                <div id="opcionTransferirDeuda" style="display: none; margin-left: 24px; padding-top: 8px; border-top: 1px dashed #d97706;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="transferirDeudaCheck" checked>
                                        <label class="form-check-label" for="transferirDeudaCheck">
                                            <i class="fas fa-exchange-alt me-1"></i> Transferir deuda al nuevo cliente
                                        </label>
                                        <small class="d-block text-muted">El nuevo cliente asumirá la deuda de ${{ number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert-modern success mb-3">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>✓ Pagos al día</strong>
                                <p class="mb-0">Esta inscripción está completamente pagada. Puede traspasar sin restricciones.</p>
                            </div>
                        </div>
                        @endif

                        <div class="alert-modern warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>⚠️ Acción Irreversible</strong>
                                <p class="mb-0" style="font-size: 0.85em;">El traspaso transferirá la membresía y los días restantes a otro cliente. Esta acción no se puede deshacer.</p>
                            </div>
                        </div>

                        <div class="alert-modern info mb-3">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>¿Cómo funciona el traspaso?</strong>
                                <ul class="mb-0 ps-3" style="font-size: 0.85em;">
                                    <li>El cliente actual perderá acceso inmediatamente</li>
                                    <li>El nuevo cliente recibirá los <strong>{{ $inscripcion->dias_restantes }} días</strong> restantes</li>
                                    <li>Solo clientes sin membresía activa pueden recibir traspaso</li>
                                    <li>Se mantendrá registro para auditoría</li>
                                </ul>
                            </div>
                        </div>

                        <h6 class="mb-2 fw-bold" style="font-size: 0.95em;"><i class="fas fa-search me-2"></i>Buscar Cliente Destino</h6>
                        
                        <div class="position-relative mb-3" id="busquedaTraspasoWrapper">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-plus"></i></span>
                                <input type="text" class="form-control" 
                                       id="buscarClienteTraspaso" 
                                       placeholder="Buscar por nombre, RUT, email o teléfono..."
                                       autocomplete="off"
                                       @if($infoTraspaso['tiene_deuda'] && !$infoTraspaso['puede_traspasar']) disabled @endif>
                            </div>
                        </div>

                        <!-- Cliente Seleccionado -->
                        <div id="clienteSeleccionadoContainer" style="display: none;" class="mb-3">
                            <h6 class="mb-2 fw-bold" style="font-size: 0.95em;"><i class="fas fa-user-check me-2 text-success"></i>Cliente Seleccionado</h6>
                            <div class="card" style="border: 2px solid #28a745; border-radius: 12px; background: linear-gradient(135deg, #f0fff4 0%, #dcfce7 100%);">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.3em;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold" id="clienteDestinoNombre" style="font-size: 1em; color: #1a1a2e;"></h6>
                                            <small class="text-muted" id="clienteDestinoRut"></small>
                                        </div>
                                        <span class="badge" id="clienteDestinoBadge" style="font-size: 0.75em;"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarClienteTraspaso()" title="Quitar selección">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="clienteDestinoId" value="">
                        </div>

                        <!-- Motivo del traspaso -->
                        <div class="mt-3" id="motivoTraspasoContainer" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-comment-alt"></i> Motivo del traspaso <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="motivoTraspaso" rows="2" 
                                      placeholder="Ej: El cliente cede la membresía a un familiar..."
                                      required style="font-size: 0.9em;"></textarea>
                            <small class="text-muted">Este registro quedará guardado para auditoría.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral: Resumen del Traspaso -->
            <div class="col-lg-4">
                <div class="modern-card sticky-top" style="top: 20px;">
                    <div class="modern-card-header" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white;">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Resumen del Traspaso</span>
                    </div>
                    <div class="modern-card-body">
                        <div class="cambio-resumen">
                            <div class="cambio-resumen-row">
                                <span class="cambio-resumen-label">De Cliente</span>
                                <span class="cambio-resumen-value">{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</span>
                            </div>
                            <div class="cambio-resumen-row">
                                <span class="cambio-resumen-label">Membresía</span>
                                <span class="cambio-resumen-value" style="color: var(--info);">{{ $inscripcion->membresia->nombre }}</span>
                            </div>
                            <div class="cambio-resumen-row">
                                <span class="cambio-resumen-label">Días Restantes</span>
                                <span class="cambio-resumen-value" style="color: var(--success); font-weight: bold;">
                                    {{ $inscripcion->dias_restantes }} días
                                </span>
                            </div>
                            <div class="cambio-resumen-row">
                                <span class="cambio-resumen-label">Vencimiento</span>
                                <span class="cambio-resumen-value">{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>
                            
                            @if($infoTraspaso['tiene_deuda'])
                            <div class="cambio-resumen-row" style="background: rgba(233, 69, 96, 0.08); margin: 8px -16px; padding: 8px 16px;">
                                <span class="cambio-resumen-label" style="color: var(--accent);">
                                    <i class="fas fa-exclamation-circle me-1"></i> Deuda
                                </span>
                                <span class="cambio-resumen-value" style="color: var(--accent);">
                                    ${{ number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') }}
                                </span>
                            </div>
                            @endif
                            
                            <div class="cambio-resumen-row" id="resumenClienteDestino" style="display: none; border-top: 2px solid var(--gray-200); padding-top: 8px; margin-top: 8px;">
                                <span class="cambio-resumen-label fw-bold">
                                    <i class="fas fa-arrow-right me-1"></i> A Cliente
                                </span>
                                <span class="cambio-resumen-value" id="resumenNombreDestino" style="color: #9b59b6;"></span>
                            </div>
                            
                            <div id="resumenDeudaTransferida" style="display: none;">
                                <div class="cambio-resumen-row" style="background: rgba(240, 165, 0, 0.1); margin: 8px -16px; padding: 8px 16px;">
                                    <span class="cambio-resumen-label" style="color: #d97706;">
                                        <i class="fas fa-exchange-alt me-1"></i> Deuda a transferir
                                    </span>
                                    <span class="cambio-resumen-value" style="color: #d97706;">
                                        ${{ number_format($infoTraspaso['monto_pendiente'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Confirmar Traspaso -->
                        <button type="button" class="btn-modern w-100 mt-3" id="btnConfirmarTraspaso" disabled
                                style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                            <i class="fas fa-exchange-alt me-2"></i> Confirmar Traspaso
                        </button>
                        
                        <p class="text-muted text-center mt-2" style="font-size: 0.75em;">
                            <i class="fas fa-history me-1"></i>
                            Se guardará registro completo del traspaso
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Fin modo-traspaso -->
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

<!-- Dropdown de resultados de búsqueda - FUERA de cualquier contenedor para máximo z-index -->
<div id="resultadosBusquedaTraspaso" style="display: none;"></div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>
<script>
const INSCRIPCION_UUID = '{{ $inscripcion->uuid }}';
const PRECIO_BASE = {{ $inscripcion->precio_base }};
let diasPausaSeleccionados = null;
let esPausaIndefinida = false;

// Valores originales para restablecer
const VALORES_ORIGINALES = {
    id_estado: '{{ $inscripcion->id_estado }}',
    fecha_vencimiento: '{{ $inscripcion->fecha_vencimiento->format("Y-m-d") }}',
    id_convenio: '{{ $inscripcion->id_convenio ?? "" }}',
    descuento_aplicado: {{ $inscripcion->descuento_aplicado ?? 0 }},
    id_motivo_descuento: '{{ $inscripcion->id_motivo_descuento ?? "" }}',
    observaciones: `{{ addslashes($inscripcion->observaciones ?? '') }}`
};

// Función para restablecer formulario
function restablecerFormulario() {
    Swal.fire({
        title: '¿Restablecer cambios?',
        text: 'Se revertirán todos los campos a los valores originales',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f0a500',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-undo me-1"></i> Sí, restablecer',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Restablecer cada campo
            document.getElementById('id_estado').value = VALORES_ORIGINALES.id_estado;
            document.getElementById('fecha_vencimiento').value = VALORES_ORIGINALES.fecha_vencimiento;
            
            const convenioSelect = document.getElementById('id_convenio');
            if (convenioSelect) {
                convenioSelect.value = VALORES_ORIGINALES.id_convenio;
                // Actualizar Select2 si existe
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(convenioSelect).trigger('change');
                }
            }
            
            document.getElementById('descuento_aplicado').value = VALORES_ORIGINALES.descuento_aplicado;
            
            const motivoSelect = document.getElementById('id_motivo_descuento');
            if (motivoSelect) {
                motivoSelect.value = VALORES_ORIGINALES.id_motivo_descuento;
            }
            
            const observaciones = document.getElementById('observaciones');
            if (observaciones) {
                observaciones.value = VALORES_ORIGINALES.observaciones;
            }
            
            // Actualizar precio final
            const descuentoDisplay = document.getElementById('descuentoDisplay');
            const precioFinalDisplay = document.getElementById('precioFinalDisplay');
            if (descuentoDisplay && precioFinalDisplay) {
                descuentoDisplay.textContent = '$' + formatNumber(VALORES_ORIGINALES.descuento_aplicado);
                precioFinalDisplay.textContent = '$' + formatNumber(PRECIO_BASE - VALORES_ORIGINALES.descuento_aplicado);
            }
            
            // Ocultar info de convenio
            const convenioInfoBox = document.getElementById('convenioInfoBox');
            if (convenioInfoBox) convenioInfoBox.style.display = 'none';
            
            Toast.success('Formulario restablecido', 'Los campos han vuelto a sus valores originales');
        }
    });
}

// Función global para formatear números
function formatNumber(num) {
    return num.toLocaleString('es-CL', { maximumFractionDigits: 0 });
}

// SweetAlert2 con estilos personalizados para Estoicos
const SwalEstoicos = Swal.mixin({
    customClass: {
        popup: 'swal-estoicos'
    },
    buttonsStyling: true
});

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
let mejoraPlanBloqueada = {{ ($infoMejora['tiene_deuda'] ?? false) ? 'true' : 'false' }};

// Función para habilitar/deshabilitar mejora cuando hay deuda
function toggleMejoraPlan() {
    const checkbox = document.getElementById('ignorarDeudaMejora');
    const planesContainer = document.getElementById('planesDisponibles');
    const btnConfirmar = document.getElementById('btnConfirmarCambio');
    
    if (checkbox && checkbox.checked) {
        // Permitir mejora - el admin acepta la deuda pendiente
        mejoraPlanBloqueada = false;
        if (planesContainer) {
            planesContainer.style.opacity = '1';
            planesContainer.style.pointerEvents = 'auto';
        }
        // Cargar planes si aún no se han cargado
        if (!planesDisponibles.length) {
            cargarPlanesDisponibles();
        }
    } else {
        // Bloquear mejora
        mejoraPlanBloqueada = true;
        if (planesContainer) {
            planesContainer.style.opacity = '0.5';
            planesContainer.style.pointerEvents = 'none';
        }
        if (btnConfirmar) {
            btnConfirmar.disabled = true;
        }
    }
}

function cargarPlanesDisponibles() {
    const container = document.getElementById('planesDisponibles');
    if (!container) return;
    
    // Si hay deuda y no se ha ignorado, no cargar
    if (mejoraPlanBloqueada) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert-modern warning">
                    <i class="fas fa-lock"></i>
                    <span>Debes aceptar la condición de deuda para ver los planes disponibles.</span>
                </div>
            </div>
        `;
        return;
    }
    
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
                    <span>No hay planes de mayor valor disponibles para mejorar.</span>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    planesDisponibles.forEach(plan => {
        const duracionTexto = plan.duracion_dias 
            ? `${plan.duracion_dias} días` 
            : `${plan.duracion_meses} ${plan.duracion_meses === 1 ? 'mes' : 'meses'}`;
        
        html += `
            <div class="col-md-6">
                <div class="plan-card" data-plan-id="${plan.id}" onclick="seleccionarPlan(${plan.id})">
                    <span class="plan-card-badge upgrade">UPGRADE</span>
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

// Variables globales para tipo de pago
let tipoPagoMejora = 'completo';
let totalAPagarGlobal = 0;

function actualizarTipoPago() {
    tipoPagoMejora = document.querySelector('input[name="tipoPagoMejora"]:checked')?.value || 'completo';
    
    // Actualizar estilos de selección
    const pagoCompletoDiv = document.getElementById('pagoCompleto').closest('.form-check');
    const pagoParcialDiv = document.getElementById('pagoParcial').closest('.form-check');
    
    if (tipoPagoMejora === 'completo') {
        pagoCompletoDiv.style.borderColor = 'var(--success)';
        pagoCompletoDiv.style.background = 'rgba(0,191,142,0.12)';
        pagoParcialDiv.style.borderColor = '#ccc';
        pagoParcialDiv.style.background = 'rgba(240,165,0,0.03)';
        
        // Pago completo: monto = total
        document.getElementById('montoAbonoCambio').value = totalAPagarGlobal;
        document.getElementById('labelMontoPago').textContent = 'Monto a Pagar (Total)';
    } else {
        pagoCompletoDiv.style.borderColor = '#ccc';
        pagoCompletoDiv.style.background = 'rgba(0,191,142,0.03)';
        pagoParcialDiv.style.borderColor = 'var(--warning)';
        pagoParcialDiv.style.background = 'rgba(240,165,0,0.12)';
        
        // Pago parcial: vaciar para que ingrese monto
        document.getElementById('montoAbonoCambio').value = '';
        document.getElementById('montoAbonoCambio').placeholder = 'Ingresa el monto del abono';
        document.getElementById('labelMontoPago').textContent = 'Monto del Abono Inicial';
    }
    
    validarMontoPago();
}

function validarMontoPago() {
    const montoInput = document.getElementById('montoAbonoCambio');
    const saldoInfo = document.getElementById('saldoPendienteInfo');
    const saldoMonto = document.getElementById('saldoPendienteMonto');
    const alertaParcial = document.getElementById('alertaPagoParcial');
    const saldoAlerta = document.getElementById('saldoPendienteAlerta');
    const btnConfirmar = document.getElementById('btnConfirmarCambio');
    
    const montoIngresado = parseFloat(montoInput.value) || 0;
    const saldoPendiente = totalAPagarGlobal - montoIngresado;
    
    // Validaciones
    if (montoIngresado < 0) {
        montoInput.value = 0;
        return validarMontoPago();
    }
    
    if (montoIngresado > totalAPagarGlobal) {
        montoInput.value = totalAPagarGlobal;
        return validarMontoPago();
    }
    
    if (tipoPagoMejora === 'parcial') {
        if (saldoPendiente > 0) {
            saldoInfo.style.display = 'block';
            saldoMonto.textContent = formatNumber(saldoPendiente);
            alertaParcial.style.display = 'flex';
            saldoAlerta.textContent = formatNumber(saldoPendiente);
        } else {
            saldoInfo.style.display = 'none';
            alertaParcial.style.display = 'none';
        }
        
        // Permitir monto mínimo de $0 para casos especiales, pero advertir si es 0
        if (montoIngresado === 0 && planSeleccionado) {
            btnConfirmar.disabled = true;
        } else if (planSeleccionado) {
            btnConfirmar.disabled = false;
        }
    } else {
        // Pago completo
        saldoInfo.style.display = 'none';
        alertaParcial.style.display = 'none';
        if (planSeleccionado) {
            btnConfirmar.disabled = false;
        }
    }
}

function actualizarResumenCambio() {
    const resumenDiv = document.getElementById('resumenCambio');
    const seccionPago = document.getElementById('seccionPagoDiferencia');
    
    if (!planSeleccionado || !datosInscripcion) return;
    
    const precioNuevo = planSeleccionado.precio;
    const creditoDisponible = datosInscripcion.monto_pagado;
    const aplicarCredito = document.getElementById('aplicarCredito')?.checked ?? true;
    const creditoAplicado = aplicarCredito ? creditoDisponible : 0;
    const totalAPagar = precioNuevo - creditoAplicado;
    
    // Guardar en variable global para las validaciones de pago
    totalAPagarGlobal = Math.max(0, totalAPagar);
    
    let resumenHTML = `
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Plan Actual</span>
            <span class="cambio-resumen-value">${datosInscripcion.membresia_actual}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Nuevo Plan</span>
            <span class="cambio-resumen-value" style="color: var(--success);">${planSeleccionado.nombre}</span>
        </div>
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">Precio Nuevo Plan</span>
            <span class="cambio-resumen-value">$${formatNumber(precioNuevo)}</span>
        </div>`;
    
    if (aplicarCredito && creditoDisponible > 0) {
        resumenHTML += `
        <div class="cambio-resumen-row">
            <span class="cambio-resumen-label">
                <i class="fas fa-minus-circle text-success me-1"></i>
                Crédito Aplicado
            </span>
            <span class="cambio-resumen-value credito">-$${formatNumber(creditoDisponible)}</span>
        </div>`;
    }
    
    resumenHTML += `
        <div class="cambio-resumen-row" style="border-top: 2px solid var(--border); padding-top: 10px; margin-top: 10px;">
            <span class="cambio-resumen-label fw-bold">Total a Pagar</span>
            <span class="cambio-resumen-value diferencia positiva">$${formatNumber(totalAPagar)}</span>
        </div>
    `;
    
    resumenDiv.innerHTML = resumenHTML;
    
    // Siempre mostrar sección de pago (es upgrade obligatorio)
    seccionPago.style.display = 'block';
    document.getElementById('diferenciaPagar').textContent = formatNumber(totalAPagarGlobal);
    document.getElementById('montoAbonoCambio').max = totalAPagarGlobal;
    
    // Actualizar según tipo de pago seleccionado
    actualizarTipoPago();
}

// Listener para el checkbox de aplicar crédito
document.addEventListener('change', function(e) {
    if (e.target.id === 'aplicarCredito') {
        actualizarResumenCambio();
        
        // Actualizar apariencia de la tarjeta de crédito
        const creditoCard = e.target.closest('.credito-plan-card');
        const estadoTexto = document.getElementById('creditoEstadoTexto');
        
        if (e.target.checked) {
            if (creditoCard) creditoCard.classList.remove('desactivado');
            if (estadoTexto) {
                estadoTexto.innerHTML = '<i class="fas fa-check-circle me-1"></i> El crédito será aplicado al nuevo plan';
            }
        } else {
            if (creditoCard) creditoCard.classList.add('desactivado');
            if (estadoTexto) {
                estadoTexto.innerHTML = '<i class="fas fa-times-circle me-1"></i> El crédito NO será aplicado';
            }
        }
    }
});

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
    
    const metodoPago = document.getElementById('metodoPagoCambio').value;
    if (!metodoPago) {
        Toast.warning('Atención', 'Selecciona un método de pago');
        document.getElementById('metodoPagoCambio').focus();
        return;
    }
    
    const aplicarCredito = document.getElementById('aplicarCredito')?.checked ?? true;
    const creditoTexto = aplicarCredito ? 'Se aplicará el crédito del plan anterior' : 'NO se aplicará el crédito anterior';
    const montoAPagar = parseFloat(document.getElementById('montoAbonoCambio').value) || 0;
    const tipoPago = document.querySelector('input[name="tipoPagoMejora"]:checked')?.value || 'completo';
    const saldoPendiente = totalAPagarGlobal - montoAPagar;
    
    // Validar monto mínimo en pago parcial
    if (tipoPago === 'parcial' && montoAPagar <= 0) {
        Toast.warning('Atención', 'Debes ingresar un monto de abono mayor a $0');
        document.getElementById('montoAbonoCambio').focus();
        return;
    }
    
    // Generar mensaje según tipo de pago
    let tipoPagoTexto = '';
    if (tipoPago === 'completo') {
        tipoPagoTexto = '<p><span class="badge bg-success">PAGO COMPLETO</span></p>';
    } else {
        tipoPagoTexto = `
            <p><span class="badge bg-warning text-dark">PAGO PARCIAL</span></p>
            <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> 
            <strong>Saldo pendiente:</strong> $${formatNumber(saldoPendiente)}</p>
        `;
    }
    
    // Confirmar con SweetAlert2
    Swal.fire({
        title: '¿Confirmar Mejora de Plan?',
        html: `
            <div style="text-align: left; font-size: 0.95rem;">
                <p><strong>Plan nuevo:</strong> ${planSeleccionado.nombre}</p>
                <p><strong>Precio:</strong> $${formatNumber(planSeleccionado.precio)}</p>
                <p><strong>Crédito:</strong> ${creditoTexto}</p>
                <p><strong>Total a pagar:</strong> $${formatNumber(totalAPagarGlobal)}</p>
                ${tipoPagoTexto}
                <p><strong>Monto a pagar ahora:</strong> $${formatNumber(montoAPagar)}</p>
            </div>
            <p class="mt-3 text-muted" style="font-size: 0.85rem;">
                <i class="fas fa-info-circle me-1"></i>
                El plan actual quedará marcado como "Mejorado" y no podrá reactivarse.
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00bf8e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-arrow-circle-up me-1"></i> Sí, Mejorar Plan',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'swal-estoicos'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            procesarMejoraPlan(metodoPago, aplicarCredito, montoAPagar, tipoPago);
        }
    });
}

function procesarMejoraPlan(metodoPago, aplicarCredito, montoAPagar, tipoPago) {
    // Mostrar loading
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('active');
    
    // Verificar si se ignoró la deuda
    const ignorarDeuda = document.getElementById('ignorarDeudaMejora')?.checked ?? false;
    
    const body = {
        id_membresia_nueva: planSeleccionado.id,
        motivo_cambio: document.getElementById('motivoCambio')?.value || '',
        id_metodo_pago: metodoPago,
        monto_abonado: montoAPagar,
        aplicar_credito: aplicarCredito,
        tipo_pago: tipoPago,
        total_a_pagar: totalAPagarGlobal,
        ignorar_deuda: ignorarDeuda,
    };
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                      document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/cambiar-plan`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(result => {
        if (overlay) overlay.classList.remove('active');
        
        if (result.ok && result.data.success) {
            Swal.fire({
                title: '¡Plan Mejorado!',
                html: `
                    <div style="text-align: center;">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <p>${result.data.message}</p>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#00bf8e',
                confirmButtonText: 'Ver Nueva Inscripción'
            }).then(() => {
                window.location.href = result.data.redirect_url || '/admin/inscripciones';
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: result.data.message || 'No se pudo mejorar el plan',
                icon: 'error',
                confirmButtonColor: '#e94560'
            });
        }
    })
    .catch(error => {
        if (overlay) overlay.classList.remove('active');
        console.error('Error:', error);
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No se pudo procesar la solicitud. Intenta nuevamente.',
            icon: 'error',
            confirmButtonColor: '#e94560'
        });
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
        // Función para mostrar info del convenio seleccionado
        function mostrarInfoConvenio(option) {
            const infoBox = document.getElementById('convenioInfoBox');
            const descripcionText = document.getElementById('convenioDescripcionText');
            const descuentoValor = document.getElementById('convenioDescuentoValor');
            const descuentoInfo = document.getElementById('convenioDescuentoInfo');
            
            if (!option || !option.value) {
                if (infoBox) infoBox.style.display = 'none';
                return;
            }
            
            const descripcion = option.dataset.descripcion || '';
            const porcentaje = parseFloat(option.dataset.descuento) || 0;
            const monto = parseFloat(option.dataset.monto) || 0;
            const tipo = option.dataset.tipo || '';
            
            // Mostrar tipo legible
            const tiposLegibles = {
                'institucion_educativa': 'Institución Educativa',
                'empresa': 'Empresa',
                'organizacion': 'Organización',
                'otro': 'Otro'
            };
            
            if (infoBox) {
                if (descripcion || porcentaje > 0 || monto > 0) {
                    infoBox.style.display = 'block';
                    
                    // Mostrar descripción o tipo
                    if (descripcion) {
                        descripcionText.textContent = descripcion;
                    } else if (tipo) {
                        descripcionText.textContent = 'Tipo: ' + (tiposLegibles[tipo] || tipo);
                    } else {
                        descripcionText.textContent = '';
                    }
                    
                    // Mostrar descuento
                    if (porcentaje > 0) {
                        descuentoInfo.style.display = 'block';
                        descuentoValor.textContent = `${porcentaje}% de descuento`;
                    } else if (monto > 0) {
                        descuentoInfo.style.display = 'block';
                        descuentoValor.textContent = `$${formatNumber(monto)} de descuento`;
                    } else {
                        descuentoInfo.style.display = 'none';
                    }
                } else {
                    infoBox.style.display = 'none';
                }
            }
        }
        
        // Mostrar info al cargar si ya hay un convenio seleccionado
        if (convenioSelect.selectedIndex > 0) {
            mostrarInfoConvenio(convenioSelect.options[convenioSelect.selectedIndex]);
        }
        
        convenioSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const porcentajeDescuento = parseFloat(selectedOption.dataset.descuento) || 0;
            const montoDescuento = parseFloat(selectedOption.dataset.monto) || 0;
            
            // Mostrar info del convenio
            mostrarInfoConvenio(selectedOption);
            
            if (porcentajeDescuento > 0) {
                // Calcular descuento basado en porcentaje del convenio
                const descuentoConvenio = Math.round(PRECIO_BASE * (porcentajeDescuento / 100));
                descuentoInput.value = descuentoConvenio;
                
                // Mostrar notificación
                Toast.success(
                    'Convenio Aplicado',
                    `Se aplicó ${porcentajeDescuento}% de descuento = $${formatNumber(descuentoConvenio)}`
                );
            } else if (montoDescuento > 0) {
                // Descuento en monto fijo
                descuentoInput.value = montoDescuento;
                
                Toast.success(
                    'Convenio Aplicado',
                    `Se aplicó descuento fijo de $${formatNumber(montoDescuento)}`
                );
            } else if (selectedOption.value === '') {
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

    // Mostrar confirmación de pausa con SweetAlert2 (más robusto que modal Bootstrap)
    if (btnConfirmarPausa) {
        btnConfirmarPausa.addEventListener('click', function() {
            const razonTexto = razonInput ? razonInput.value.trim() : '';
            
            // Validaciones
            if (!esPausaIndefinida && !diasPausaSeleccionados) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar una duración de pausa',
                    icon: 'error',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
            
            if (esPausaIndefinida && razonTexto.length < 5) {
                Swal.fire({
                    title: 'Error',
                    text: 'Para pausa indefinida debe indicar una razón (mínimo 5 caracteres)',
                    icon: 'error',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
            
            // Construir HTML para SweetAlert según tipo de pausa
            let htmlContent = '';
            if (esPausaIndefinida) {
                htmlContent = `
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; background: rgba(233, 69, 96, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e94560; font-size: 2em;">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <h4>Congelamiento Indefinido</h4>
                        <p class="text-muted">La membresía quedará congelada hasta que se reactive manualmente</p>
                        <div style="background: rgba(240, 165, 0, 0.1); padding: 12px; border-radius: 8px; border-left: 4px solid #f0a500; text-align: left; margin-top: 15px;">
                            <strong><i class="fas fa-comment me-1"></i> Motivo:</strong>
                            <p style="margin: 5px 0 0 0;">${razonTexto}</p>
                        </div>
                    </div>
                `;
            } else {
                const fechaFin = new Date();
                fechaFin.setDate(fechaFin.getDate() + diasPausaSeleccionados);
                
                htmlContent = `
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; background: rgba(240, 165, 0, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #f0a500; font-size: 2em;">
                            <i class="fas fa-snowflake"></i>
                        </div>
                        <h4>Congelar por ${diasPausaSeleccionados} días</h4>
                        <div style="display: flex; justify-content: space-around; margin-top: 20px; gap: 20px;">
                            <div style="background: rgba(0,0,0,0.03); padding: 15px 25px; border-radius: 10px;">
                                <i class="fas fa-calendar-check text-success"></i>
                                <small style="display: block; color: #888;">Desde</small>
                                <strong>${new Date().toLocaleDateString('es-CL')}</strong>
                            </div>
                            <div style="background: rgba(0,0,0,0.03); padding: 15px 25px; border-radius: 10px;">
                                <i class="fas fa-calendar-times text-warning"></i>
                                <small style="display: block; color: #888;">Hasta</small>
                                <strong>${fechaFin.toLocaleDateString('es-CL')}</strong>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Mostrar SweetAlert2 de confirmación
            Swal.fire({
                title: '<i class="fas fa-pause-circle me-2"></i> Confirmar Pausa',
                html: htmlContent,
                icon: null,
                showCancelButton: true,
                confirmButtonColor: '#f0a500',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-pause-circle me-1"></i> Confirmar Pausa',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-estoicos'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarPausaInscripcion(razonTexto);
                }
            });
        });
    }
    
    // Función para ejecutar la pausa
    function ejecutarPausaInscripcion(razonTexto) {
        // Mostrar loading
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.add('active');
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                          document.querySelector('input[name="_token"]')?.value;

        console.log('Enviando pausa:', { 
            dias: diasPausaSeleccionados, 
            indefinida: esPausaIndefinida,
            razon: razonTexto,
            uuid: INSCRIPCION_UUID
        });

        fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/pausar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                dias: diasPausaSeleccionados,
                razon: razonTexto,
                indefinida: esPausaIndefinida,
            }),
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json().then(data => ({ ok: response.ok, data }));
        })
        .then(result => {
            if (overlay) overlay.classList.remove('active');
            console.log('Respuesta pausa:', result);
            
            if (result.ok && result.data.success) {
                Swal.fire({
                    title: '¡Membresía Pausada!',
                    html: `
                        <div style="text-align: center;">
                            <i class="fas fa-pause-circle fa-4x mb-3" style="color: var(--warning);"></i>
                            <p>${result.data.message || 'La membresía ha sido pausada exitosamente.'}</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#f0a500',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: result.data.message || 'No se pudo pausar la membresía.',
                    icon: 'error',
                    confirmButtonColor: '#e94560'
                });
            }
        })
        .catch(error => {
            if (overlay) overlay.classList.remove('active');
            console.error('Error pausa:', error);
            Swal.fire({
                title: 'Error de Conexión',
                text: 'No se pudo procesar la solicitud. Intenta nuevamente.',
                icon: 'error',
                confirmButtonColor: '#e94560'
            });
        });
    }

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

    // Ejecutar reactivación - usando SweetAlert2 para mejor UX
    document.addEventListener('click', function(e) {
        if (e.target.closest('#btnEjecutarReanudar')) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Ejecutando reactivación...');
            
            // Cerrar modal de Bootstrap
            document.querySelectorAll('.modal').forEach(modal => {
                if (typeof bootstrap !== 'undefined') {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                } else if (typeof $ !== 'undefined') {
                    $(modal).modal('hide');
                }
            });
            
            // Mostrar loading
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('active');
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                              document.querySelector('input[name="_token"]')?.value;

            fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/reanudar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(result => {
                if (overlay) overlay.classList.remove('active');
                console.log('Respuesta reanudar:', result);
                
                if (result.ok && result.data.success) {
                    Swal.fire({
                        title: '¡Membresía Reactivada!',
                        html: `
                            <div style="text-align: center;">
                                <i class="fas fa-play-circle fa-4x mb-3" style="color: var(--success);"></i>
                                <p>${result.data.message || 'La membresía ha sido reactivada exitosamente.'}</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#00bf8e',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: result.data.message || 'No se pudo reactivar la membresía.',
                        icon: 'error',
                        confirmButtonColor: '#e94560'
                    });
                }
            })
            .catch(error => {
                if (overlay) overlay.classList.remove('active');
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'No se pudo procesar la solicitud. Intenta nuevamente.',
                    icon: 'error',
                    confirmButtonColor: '#e94560'
                });
            });
        }
    });

    // Formateador de precios
    if (typeof PrecioFormatter !== 'undefined') {
        PrecioFormatter.iniciarCampo('descuento_aplicado', false);
    }
});

// ============================================
// FUNCIONALIDAD DE TRASPASO DE MEMBRESÍA
// ============================================

let clienteDestinoSeleccionado = null;
let timeoutBusqueda = null;

// Referencias principales
const inputBusqueda = document.getElementById('buscarClienteTraspaso');
const resultadosContainer = document.getElementById('resultadosBusquedaTraspaso');
const ignorarDeudaCheck = document.getElementById('ignorarDeudaCheck');
const transferirDeudaCheck = document.getElementById('transferirDeudaCheck');
const opcionTransferirDeuda = document.getElementById('opcionTransferirDeuda');
const resumenDeudaTransferida = document.getElementById('resumenDeudaTransferida');

// Función para toggle de opciones de deuda
function toggleOpcionesDeuda() {
    if (!ignorarDeudaCheck) return;
    
    const isChecked = ignorarDeudaCheck.checked;
    
    // Mostrar/ocultar opción de transferir deuda
    if (opcionTransferirDeuda) {
        opcionTransferirDeuda.style.display = isChecked ? 'block' : 'none';
    }
    
    // Habilitar/deshabilitar el buscador de clientes
    if (inputBusqueda) {
        inputBusqueda.disabled = !isChecked && inputBusqueda.hasAttribute('disabled');
        // Si se activa el checkbox, habilitar el buscador siempre
        if (isChecked) {
            inputBusqueda.disabled = false;
        }
    }
    
    // Actualizar resumen de deuda transferida
    actualizarResumenDeuda();
}

// Función para actualizar resumen de deuda
function actualizarResumenDeuda() {
    if (!resumenDeudaTransferida) return;
    
    const mostrarDeuda = ignorarDeudaCheck && ignorarDeudaCheck.checked && 
                         transferirDeudaCheck && transferirDeudaCheck.checked &&
                         clienteDestinoSeleccionado;
    
    resumenDeudaTransferida.style.display = mostrarDeuda ? 'block' : 'none';
}

// Listener para el checkbox de transferir deuda
if (transferirDeudaCheck) {
    transferirDeudaCheck.addEventListener('change', actualizarResumenDeuda);
}

// Función para posicionar el dropdown - detecta espacio disponible
function posicionarDropdown() {
    const wrapper = document.getElementById('busquedaTraspasoWrapper');
    if (!wrapper || !resultadosContainer) return;
    
    const rect = wrapper.getBoundingClientRect();
    const dropdownHeight = 400; // max-height del dropdown
    const viewportHeight = window.innerHeight;
    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;
    
    // Resetear estilos
    resultadosContainer.style.top = 'auto';
    resultadosContainer.style.bottom = 'auto';
    
    // Determinar si hay más espacio arriba o abajo
    if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
        // Mostrar ARRIBA del input
        resultadosContainer.style.bottom = (viewportHeight - rect.top + 5) + 'px';
        resultadosContainer.style.maxHeight = Math.min(spaceAbove - 20, 400) + 'px';
        resultadosContainer.classList.add('dropdown-arriba');
        resultadosContainer.classList.remove('dropdown-abajo');
    } else {
        // Mostrar ABAJO del input (por defecto)
        resultadosContainer.style.top = (rect.bottom + 5) + 'px';
        resultadosContainer.style.maxHeight = Math.min(spaceBelow - 20, 400) + 'px';
        resultadosContainer.classList.add('dropdown-abajo');
        resultadosContainer.classList.remove('dropdown-arriba');
    }
    
    resultadosContainer.style.left = rect.left + 'px';
    resultadosContainer.style.width = Math.max(rect.width, 450) + 'px';
}

if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        const query = this.value.trim();
        
        if (query.length < 2) {
            resultadosContainer.style.display = 'none';
            return;
        }
        
        timeoutBusqueda = setTimeout(() => {
            posicionarDropdown();
            buscarClientesTraspaso(query);
        }, 300);
    });
    
    // Reposicionar al hacer scroll
    window.addEventListener('scroll', function() {
        if (resultadosContainer.style.display === 'block') {
            posicionarDropdown();
        }
    });
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!inputBusqueda.contains(e.target) && !resultadosContainer.contains(e.target)) {
            resultadosContainer.style.display = 'none';
        }
    });
}

function buscarClientesTraspaso(query) {
    fetch(`/admin/inscripciones/${INSCRIPCION_UUID}/buscar-clientes-traspaso?q=${encodeURIComponent(query)}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.clientes.length > 0) {
            renderizarResultadosTraspaso(data.clientes);
        } else {
            resultadosContainer.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #666;">
                    <i class="fas fa-search" style="font-size: 2em; color: #ccc; margin-bottom: 10px;"></i>
                    <p style="margin: 0; font-size: 0.9em;">No se encontraron clientes disponibles</p>
                    <small style="color: #999;">Intenta con otro término de búsqueda</small>
                </div>
            `;
            resultadosContainer.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error buscando clientes:', error);
        resultadosContainer.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #dc3545;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2em; margin-bottom: 10px;"></i>
                <p style="margin: 0; font-size: 0.9em;">Error al buscar clientes</p>
            </div>
        `;
        resultadosContainer.style.display = 'block';
    });
}

function renderizarResultadosTraspaso(clientes) {
    let html = '';
    
    // Guardar clientes en variable global para acceso
    window.clientesTraspasoTemp = clientes;
    
    clientes.forEach((cliente, index) => {
        const badgeColor = cliente.estado === 'nuevo' ? 'success' : 'info';
        const badgeText = cliente.estado === 'nuevo' ? 'Cliente Nuevo' : 'Sin membresía';
        
        html += `
            <div class="cliente-resultado-item" 
                 data-index="${index}"
                 style="display: block; padding: 14px 18px; cursor: pointer; border-bottom: 1px solid #eee; transition: all 0.2s;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2em; flex-shrink: 0;">
                        ${cliente.nombre_completo.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div style="font-weight: 600; font-size: 1em; color: #1a1a2e;">${cliente.nombre_completo}</div>
                        <div style="font-size: 0.85em; color: #666;">
                            <i class="fas fa-id-card me-1"></i> ${cliente.rut || 'Sin RUT'}
                        </div>
                        ${cliente.email ? `<div style="font-size: 0.8em; color: #888;"><i class="fas fa-envelope me-1"></i>${cliente.email}</div>` : ''}
                        ${cliente.ultima_membresia ? `<div style="font-size: 0.75em; color: #999;"><i class="fas fa-history me-1"></i>Última: ${cliente.ultima_membresia}</div>` : ''}
                    </div>
                    <span class="badge bg-${badgeColor}" style="font-size: 0.75em; padding: 6px 12px; flex-shrink: 0;">${badgeText}</span>
                </div>
            </div>
        `;
    });
    
    resultadosContainer.innerHTML = html;
    resultadosContainer.style.display = 'block';
    
    // Agregar event listeners
    document.querySelectorAll('.cliente-resultado-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.background = '#f8f4ff';
            this.style.transform = 'translateX(5px)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.background = '#fff';
            this.style.transform = 'translateX(0)';
        });
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const index = parseInt(this.dataset.index);
            console.log('Click en cliente index:', index);
            const cliente = window.clientesTraspasoTemp[index];
            console.log('Datos del cliente:', cliente);
            if (cliente) {
                seleccionarClienteTraspaso(cliente);
            }
        });
    });
}

function seleccionarClienteTraspaso(cliente) {
    console.log('Seleccionando cliente:', cliente);
    
    try {
        clienteDestinoSeleccionado = cliente;
        
        // Mostrar cliente seleccionado
        const nombreEl = document.getElementById('clienteDestinoNombre');
        const rutEl = document.getElementById('clienteDestinoRut');
        const idEl = document.getElementById('clienteDestinoId');
        const badgeEl = document.getElementById('clienteDestinoBadge');
        
        if (nombreEl) nombreEl.textContent = cliente.nombre_completo;
        if (rutEl) rutEl.textContent = 'RUT: ' + (cliente.rut || 'N/A');
        if (idEl) idEl.value = cliente.id;
        
        if (badgeEl) {
            if (cliente.estado === 'nuevo') {
                badgeEl.className = 'badge bg-success';
                badgeEl.textContent = 'Cliente Nuevo';
            } else {
                badgeEl.className = 'badge bg-info';
                badgeEl.textContent = 'Sin membresía';
            }
        }
        
        // Actualizar resumen lateral
        const resumenNombre = document.getElementById('resumenNombreDestino');
        const resumenCliente = document.getElementById('resumenClienteDestino');
        if (resumenNombre) resumenNombre.textContent = cliente.nombre_completo;
        if (resumenCliente) resumenCliente.style.display = 'flex';
        
        // Mostrar contenedores
        const contenedorCliente = document.getElementById('clienteSeleccionadoContainer');
        const contenedorMotivo = document.getElementById('motivoTraspasoContainer');
        const btnConfirmar = document.getElementById('btnConfirmarTraspaso');
        
        if (contenedorCliente) contenedorCliente.style.display = 'block';
        if (contenedorMotivo) contenedorMotivo.style.display = 'block';
        if (btnConfirmar) btnConfirmar.disabled = false;
        
        // Ocultar resultados y limpiar búsqueda
        if (resultadosContainer) resultadosContainer.style.display = 'none';
        const inputBusq = document.getElementById('buscarClienteTraspaso');
        if (inputBusq) inputBusq.value = '';
        
        // Actualizar resumen de deuda si aplica
        actualizarResumenDeuda();
        
        console.log('Cliente seleccionado correctamente');
    } catch (error) {
        console.error('Error al seleccionar cliente:', error);
    }
}

function limpiarClienteTraspaso() {
    clienteDestinoSeleccionado = null;
    document.getElementById('clienteDestinoId').value = '';
    document.getElementById('clienteSeleccionadoContainer').style.display = 'none';
    document.getElementById('motivoTraspasoContainer').style.display = 'none';
    document.getElementById('resumenClienteDestino').style.display = 'none';
    document.getElementById('btnConfirmarTraspaso').disabled = true;
    document.getElementById('motivoTraspaso').value = '';
    
    // Ocultar resumen de deuda transferida
    if (resumenDeudaTransferida) {
        resumenDeudaTransferida.style.display = 'none';
    }
}

// Confirmar traspaso
document.addEventListener('click', function(e) {
    if (e.target.closest('#btnConfirmarTraspaso')) {
        ejecutarTraspaso();
    }
});

function ejecutarTraspaso() {
    console.log('=== EJECUTAR TRASPASO LLAMADO ===');
    
    if (!clienteDestinoSeleccionado) {
        Toast.error('Error', 'Debes seleccionar un cliente destino');
        return;
    }
    
    const motivo = document.getElementById('motivoTraspaso').value.trim();
    if (!motivo) {
        Toast.warning('Atención', 'Debes ingresar el motivo del traspaso');
        document.getElementById('motivoTraspaso').focus();
        return;
    }
    
    // Determinar si se ignora la deuda y si se transfiere
    const ignorarDeuda = ignorarDeudaCheck ? ignorarDeudaCheck.checked : false;
    const transferirDeuda = transferirDeudaCheck ? transferirDeudaCheck.checked : false;
    
    // Construir mensaje de confirmación
    let mensajeConfirmacion = `¿Confirmar traspaso de membresía a "${clienteDestinoSeleccionado.nombre_completo}"?`;
    let textoAdicional = '';
    
    if (ignorarDeuda && transferirDeuda) {
        textoAdicional = '⚠️ La deuda pendiente se transferirá al nuevo cliente.';
    } else if (ignorarDeuda && !transferirDeuda) {
        textoAdicional = '⚠️ La deuda pendiente se condonará.';
    }
    
    // Guardar valores en variables locales para usar en el callback
    const clienteId = clienteDestinoSeleccionado.id;
    const motivoTraspaso = motivo;
    const ignDeuda = ignorarDeuda;
    const transfDeuda = transferirDeuda;
    
    console.log('Datos a enviar:', { clienteId, motivoTraspaso, ignDeuda, transfDeuda });
    
    // Confirmar con SweetAlert
    Swal.fire({
        title: '¿Confirmar traspaso?',
        html: `${mensajeConfirmacion}${textoAdicional ? '<br><br>' + textoAdicional : ''}<br><br><strong>Esta acción no se puede deshacer.</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, traspasar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then(function(result) {
        console.log('SweetAlert result:', result);
        if (result.isConfirmed) {
            console.log('=== CONFIRMADO - ENVIANDO FETCH ===');
            enviarTraspaso(clienteId, motivoTraspaso, ignDeuda, transfDeuda);
        }
    });
}

function enviarTraspaso(clienteId, motivo, ignorarDeuda, transferirDeuda) {
    console.log('=== ENVIANDO TRASPASO AL SERVIDOR ===');
    
    // Mostrar loading
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('active');
    
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        if (overlay) overlay.classList.remove('active');
        Toast.error('Error', 'No se encontró el token CSRF. Recarga la página.');
        return;
    }
    
    const url = `/admin/inscripciones/${INSCRIPCION_UUID}/traspasar`;
    const datos = {
        id_cliente_destino: clienteId,
        motivo_traspaso: motivo,
        ignorar_deuda: ignorarDeuda,
        transferir_deuda: transferirDeuda,
    };
    
    console.log('URL:', url);
    console.log('Datos:', datos);
    console.log('CSRF:', csrfToken);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(datos),
    })
    .then(function(response) {
        console.log('Response status:', response.status);
        return response.json().then(function(data) {
            return { ok: response.ok, status: response.status, data: data };
        });
    })
    .then(function(result) {
        if (overlay) overlay.classList.remove('active');
        console.log('Resultado:', result);
        
        if (result.ok && result.data.success) {
            Swal.fire({
                title: '¡Traspaso Exitoso!',
                text: result.data.message,
                icon: 'success',
                confirmButtonText: 'Ver nueva inscripción'
            }).then(function() {
                window.location.href = result.data.redirect_url || '/admin/inscripciones';
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: result.data.message || 'No se pudo realizar el traspaso',
                icon: 'error'
            });
        }
    })
    .catch(function(error) {
        if (overlay) overlay.classList.remove('active');
        console.error('Error en fetch:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión: ' + error.message,
            icon: 'error'
        });
    });
}
</script>
@stop
