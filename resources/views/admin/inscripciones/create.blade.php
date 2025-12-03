@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

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
        --danger: #dc3545;
        --gray-50: #fafbfc;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
    }

    /* ===== ANIMACIONES ===== */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
    @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }

    /* ===== WIZARD STEPS MEJORADO ===== */
    .step-indicator { display: none; }
    .step-indicator.active { display: block; animation: fadeIn 0.4s ease-out; }
    
    .wizard-container {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: var(--radius-xl);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .wizard-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .wizard-header h2 {
        color: white;
        font-weight: 800;
        font-size: 1.5rem;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .wizard-header h2 i {
        color: var(--accent);
        font-size: 1.75rem;
    }

    .wizard-header p {
        color: rgba(255,255,255,0.7);
        margin: 0.5rem 0 0 0;
        font-size: 0.95rem;
    }

    .steps-nav { 
        display: flex; 
        gap: 1rem;
        position: relative;
        padding: 0;
        background: transparent;
    }

    /* REMOVIDA la línea que atravesaba los pasos */
    
    .step-btn {
        flex: 1;
        padding: 1.25rem 1rem;
        text-align: center;
        border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255,255,255,0.2);
        cursor: pointer;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: rgba(255,255,255,0.5);
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .step-btn .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 800;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .step-btn .step-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .step-btn .step-icon {
        font-size: 1.2rem;
    }
    
    .step-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        color: rgba(255,255,255,0.9);
    }
    
    /* PASO ACTIVO - Color accent destacado */
    .step-btn.active {
        background: white;
        color: var(--primary);
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(233, 69, 96, 0.5);
        transform: translateY(-2px);
    }

    .step-btn.active .step-number {
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b6b 100%);
        color: white;
        border-color: var(--accent);
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.5);
    }

    .step-btn.active .step-icon {
        color: var(--accent);
    }

    /* PASO COMPLETADO - Color success */
    .step-btn.completed {
        background: rgba(0, 191, 142, 0.2);
        color: white;
        border-color: var(--success);
    }

    .step-btn.completed .step-number {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }

    .step-btn.completed .step-number::after {
        content: '✓';
        font-weight: 900;
        font-size: 1.2rem;
    }

    .step-btn.completed .step-number span {
        display: none;
    }

    .step-btn.completed .step-icon {
        color: var(--success);
    }

    /* PASO DESHABILITADO - menor prioridad que active y completed */
    .step-btn:disabled:not(.active):not(.completed) {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .step-btn:disabled:not(.active):not(.completed):hover {
        transform: none;
        box-shadow: none;
    }

    /* Asegurar que active siempre se vea brillante */
    .step-btn.active {
        opacity: 1 !important;
    }

    /* ===== FORM SECTIONS ===== */
    .form-section-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--primary);
        margin: 2rem 0 1.25rem 0;
        padding: 0.75rem 1rem;
        background: linear-gradient(90deg, rgba(233, 69, 96, 0.1) 0%, transparent 100%);
        border-left: 4px solid var(--accent);
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-section-title i {
        color: var(--accent);
        font-size: 1.1rem;
    }

    /* ===== PRECIO BOX MEJORADO ===== */
    .precio-box {
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        margin-top: 1.5rem;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
    }

    .precio-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .precio-box h5 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .precio-box h5 i {
        color: var(--accent);
    }

    .precio-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-200);
    }

    .precio-row:last-child {
        border-bottom: none;
        padding-top: 1.25rem;
        margin-top: 0.75rem;
        border-top: 2px dashed var(--primary);
        background: rgba(0, 191, 142, 0.05);
        margin: 0.75rem -1rem -1rem -1rem;
        padding: 1rem 1rem;
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }

    .precio-label {
        color: var(--gray-600);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .precio-valor {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1.05rem;
    }

    .precio-total {
        font-size: 1.75rem;
        color: var(--success);
        font-weight: 800;
    }

    /* ===== BUTTONS MEJORADOS ===== */
    .buttons-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2.5rem;
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }

    .buttons-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 700;
        border-radius: var(--radius-md);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    .btn-lg {
        padding: 0.875rem 2rem;
        font-size: 0.9rem;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }

    .btn:active {
        transform: translateY(-1px);
    }

    .btn-info {
        background: var(--info);
        border: none;
        color: white;
    }

    .btn-info:hover {
        background: #3451d4;
        color: white;
    }

    .btn-warning {
        background: var(--warning);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        background: #d99200;
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, var(--success-dark) 0%, #008f6b 100%);
        color: white;
        box-shadow: 0 6px 25px rgba(0, 191, 142, 0.5);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        border: none;
        color: white !important;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        opacity: 1 !important;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #3451d4 0%, #2a41aa 100%);
        color: white !important;
        box-shadow: 0 6px 25px rgba(67, 97, 238, 0.5);
        transform: translateY(-3px);
    }

    .btn-primary:focus,
    .btn-primary:active {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%) !important;
        color: white !important;
        opacity: 1 !important;
    }

    .btn-outline-danger {
        background: transparent;
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
    }

    .btn-outline-danger:hover {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    .btn-secondary {
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(255,255,255,0.2);
        color: white;
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        color: white;
    }

    /* ===== CARD PRINCIPAL ===== */
    .card-inscripcion {
        border: none;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        background: white;
    }

    .card-inscripcion .card-body {
        padding: 2rem;
    }

    /* ===== PAGE HEADER REMOVIDO - Usamos wizard-container ===== */
    .page-header {
        display: none;
    }

    /* ===== FORM CONTROLS MEJORADOS ===== */
    .form-control {
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: var(--gray-50);
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
        background: white;
    }

    .form-control.is-invalid {
        border-color: var(--danger) !important;
        background-color: rgba(220, 53, 69, 0.05) !important;
    }

    .form-control-lg {
        padding: 1rem 1.25rem;
        font-size: 1rem;
        border-radius: var(--radius-md);
    }

    /* ===== SELECT MEJORADO ===== */
    select.form-control {
        height: auto !important;
        min-height: 48px;
        padding: 0.75rem 1rem;
        cursor: pointer;
    }

    select.form-control.form-control-lg {
        min-height: 56px;
        padding: 1rem 1.25rem;
    }

    label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    label .text-danger {
        color: var(--accent) !important;
    }

    /* ===== CLIENTE CARD MEJORADO ===== */
    .clientes-list {
        max-height: 450px;
        overflow-y: auto;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        border: 2px solid var(--gray-200);
    }

    .clientes-list::-webkit-scrollbar {
        width: 8px;
    }

    .clientes-list::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 4px;
    }

    .clientes-list::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 4px;
    }

    .clientes-list::-webkit-scrollbar-thumb:hover {
        background: var(--accent);
    }

    .cliente-card {
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        position: relative;
        overflow: hidden;
    }

    .cliente-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        transition: all 0.3s ease;
    }

    .cliente-card:hover {
        border-color: var(--accent);
        background: white;
        transform: translateX(8px);
        box-shadow: var(--shadow-sm);
    }

    .cliente-card:hover::before {
        background: var(--accent);
    }

    .cliente-card.selected {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.05);
        box-shadow: 0 4px 20px rgba(0, 191, 142, 0.2);
    }

    .cliente-card.selected::before {
        background: var(--success);
    }

    .cliente-card .cliente-nombre {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1.1rem;
    }

    .cliente-card .cliente-rut {
        color: var(--accent);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .cliente-card .cliente-estado {
        font-size: 0.85rem;
    }

    .cliente-card .estado-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .estado-sin-membresia {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
    }

    .estado-vencida {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
    }

    /* ===== SEARCH BOX ===== */
    .search-clientes {
        margin-bottom: 1rem;
    }

    .search-clientes input {
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .search-clientes input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
    }

    .clientes-list {
        max-height: 400px;
        overflow-y: auto;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 1rem;
        background: var(--gray-100);
    }

    /* ===== TIPO PAGO OPCIONES (Nuevo diseño) ===== */
    .tipo-pago-container {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    
    .tipo-pago-option {
        flex: 1;
        min-width: 200px;
        position: relative;
    }
    
    .tipo-pago-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .tipo-pago-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 16px;
        border: 2px solid var(--gray-200);
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        background: white;
        height: 100%;
    }
    
    .tipo-pago-option label:hover {
        border-color: var(--info);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.15);
    }
    
    .tipo-pago-option input:checked + label {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.08);
        box-shadow: 0 8px 30px rgba(0, 191, 142, 0.2);
        transform: translateY(-3px);
    }
    
    .tipo-pago-icon {
        font-size: 2.5em;
        margin-bottom: 12px;
        color: var(--info);
        transition: all 0.3s ease;
    }
    
    .tipo-pago-option input:checked + label .tipo-pago-icon {
        color: var(--success);
        transform: scale(1.1);
    }
    
    .tipo-pago-title {
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 6px;
        font-size: 1.05em;
    }
    
    .tipo-pago-desc {
        font-size: 0.85em;
        color: var(--gray-600);
        line-height: 1.4;
    }

    /* ===== RESUMEN INSCRIPCIÓN ===== */
    .resumen-inscripcion .info-card {
        padding: 1.5rem;
        border-radius: 16px;
        text-align: center;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .resumen-inscripcion .info-card.bg-primary {
        background: var(--primary) !important;
    }

    .resumen-inscripcion .info-card.bg-info {
        background: var(--info) !important;
    }

    .resumen-inscripcion .info-card.bg-success {
        background: var(--success) !important;
    }

    .resumen-inscripcion .info-label {
        font-size: 0.85rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .resumen-inscripcion .info-value {
        font-size: 1.1rem;
        font-weight: 700;
        margin-top: 0.25rem;
    }

    /* ===== CARD TOTAL A PAGAR - DESTACADA ===== */
    .info-card.info-card-total {
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 50%, #008f6b 100%) !important;
        position: relative;
        overflow: hidden;
        animation: pulseGlow 2s ease-in-out infinite;
    }

    .info-card.info-card-total::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        animation: shimmer 3s linear infinite;
    }

    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 5px 20px rgba(0, 191, 142, 0.4); }
        50% { box-shadow: 0 8px 35px rgba(0, 191, 142, 0.6); }
    }

    @keyframes shimmer {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .info-card-total .total-icon-wrapper {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border: 3px solid rgba(255,255,255,0.3);
    }

    .info-card-total .total-icon-wrapper i {
        font-size: 1.8rem;
        color: white;
    }

    .info-card-total .total-amount {
        font-size: 2rem !important;
        font-weight: 800 !important;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        margin: 8px 0;
    }

    .info-card-total .total-badge {
        background: rgba(255,255,255,0.2);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .info-card-total .total-badge i {
        font-size: 0.85rem;
    }

    /* ===== PAGO MIXTO MEJORADO ===== */
    .mixto-section {
        background: var(--gray-100);
        border-radius: 16px;
        padding: 24px;
        margin-top: 15px;
        border: 2px solid var(--gray-200);
    }
    
    .mixto-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.1em;
    }
    
    .mixto-title i { 
        color: var(--warning);
        font-size: 1.2em;
    }

    .mixto-metodo-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 2px solid var(--gray-200);
        transition: all 0.3s ease;
    }

    .mixto-metodo-card:hover {
        border-color: var(--info);
    }

    .mixto-metodo-card .metodo-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        font-weight: 600;
        color: var(--primary);
    }

    .mixto-metodo-card .metodo-header i {
        color: var(--info);
    }

    .resumen-mixto {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 20px;
        border: 2px solid var(--success);
    }

    #mixto-diferencia-box {
        background: rgba(240, 165, 0, 0.15);
    }

    #mixto-diferencia-box.ok {
        background: rgba(0, 191, 142, 0.15);
    }

    #mixto-diferencia-box.ok #mixto-diferencia {
        color: var(--success) !important;
    }

    #mixto-diferencia-box.error {
        background: rgba(233, 69, 96, 0.15);
    }

    #mixto-diferencia-box.error #mixto-diferencia {
        color: var(--accent) !important;
    }

    /* ===== ALERT CUSTOM ===== */
    .alert-info {
        background: rgba(67, 97, 238, 0.1);
        border: none;
        color: var(--info);
        border-radius: 12px;
    }

    .alert-success {
        background: rgba(0, 191, 142, 0.1);
        border: none;
        color: var(--success-dark);
        border-radius: 12px;
    }

    .alert-warning {
        background: rgba(240, 165, 0, 0.1);
        border: none;
        color: #c78800;
        border-radius: 12px;
    }

    .alert-danger {
        background: rgba(233, 69, 96, 0.1);
        border: none;
        color: var(--accent);
        border-radius: 12px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .buttons-container { flex-direction: column; }
        .buttons-group { width: 100%; }
        .buttons-group .btn { flex: 1; justify-content: center; }
        .resumen-inscripcion .col-md-4 { margin-bottom: 1rem; }
    }

    /* ===== SWEETALERT ESTOICOS THEME ===== */
    .swal-estoicos {
        border-radius: 16px !important;
        border: 2px solid var(--accent) !important;
    }
    
    .swal-estoicos .swal2-title {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    
    .swal-estoicos .swal2-html-container {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .swal-estoicos .swal2-icon {
        border-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-success {
        border-color: var(--success) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: var(--success) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-warning {
        border-color: var(--warning) !important;
        color: var(--warning) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-error {
        border-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-error [class^="swal2-x-mark-line"] {
        background-color: var(--accent) !important;
    }
    
    .swal-estoicos .swal2-icon.swal2-question {
        border-color: var(--info) !important;
        color: var(--info) !important;
    }
</style>
@endsection

@section('content_header')
@stop

@section('content')
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: var(--radius-lg); border-left: 4px solid var(--accent);">
    <h5 class="mb-2"><i class="fas fa-exclamation-triangle text-danger"></i> Errores en el Formulario</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<!-- WIZARD CONTAINER -->
<div class="wizard-container">
    <div class="wizard-header">
        <h2><i class="fas fa-clipboard-list"></i> Nueva Inscripción</h2>
        <p>Complete los 3 pasos para registrar una nueva inscripción</p>
    </div>
    
    <div class="steps-nav">
        <button type="button" class="step-btn active" id="step1-btn">
            <div class="step-number"><span>1</span></div>
            <span class="step-icon"><i class="fas fa-user"></i></span>
            <span class="step-label">Cliente</span>
        </button>
        <button type="button" class="step-btn" id="step2-btn" disabled>
            <div class="step-number"><span>2</span></div>
            <span class="step-icon"><i class="fas fa-dumbbell"></i></span>
            <span class="step-label">Membresía</span>
        </button>
        <button type="button" class="step-btn" id="step3-btn" disabled>
            <div class="step-number"><span>3</span></div>
            <span class="step-icon"><i class="fas fa-credit-card"></i></span>
            <span class="step-label">Pago</span>
        </button>
    </div>
</div>

<!-- FORMULARIO PRINCIPAL -->
<div class="card-inscripcion">
    <div class="card-body">
        <form action="{{ route('admin.inscripciones.store') }}" method="POST" id="inscripcionForm">
            @csrf
            <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
            <input type="hidden" name="id_estado" value="{{ $estadoActiva->codigo ?? 100 }}">
            <input type="hidden" id="id_cliente" name="id_cliente" value="">
            <input type="hidden" id="precio_base_hidden" name="precio_base" value="0">
            <input type="hidden" id="precio_final_hidden" name="precio_final" value="0">

            <!-- ========== PASO 1: SELECCIONAR CLIENTE ========== -->
            <div class="step-indicator active" id="step-1">
                <div class="form-section-title">
                    <i class="fas fa-user-check"></i> Seleccionar Cliente
                </div>
                
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle mr-2"></i> 
                    <strong>Clientes disponibles:</strong> Solo clientes activos sin membresía vigente.
                </div>

                <div class="search-clientes mb-3">
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background: var(--primary); color: white; border: none;">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control form-control-lg" id="buscarCliente" 
                               placeholder="Buscar por nombre o RUT..." style="border-left: none;">
                    </div>
                </div>

                <div class="clientes-list" id="clientesList">
                    @forelse($clientes as $cliente)
                        @php
                            // Verificar si tiene inscripción activa
                            $inscripcionActiva = $cliente->inscripciones()
                                ->where('id_estado', 100) // Estado Activa
                                ->where('fecha_vencimiento', '>=', now())
                                ->first();
                            
                            $ultimaInscripcion = $cliente->inscripciones()
                                ->orderBy('fecha_vencimiento', 'desc')
                                ->first();
                            
                            $tieneMembresia = $inscripcionActiva !== null;
                            
                            // Determinar estado del cliente
                            if ($tieneMembresia) {
                                $estadoTexto = 'Membresía Activa';
                                $estadoClase = 'bg-success text-white';
                                $disponible = false;
                            } elseif ($ultimaInscripcion && $ultimaInscripcion->fecha_vencimiento < now()) {
                                $estadoTexto = 'Membresía Vencida';
                                $estadoClase = 'estado-vencida';
                                $disponible = true;
                            } else {
                                $estadoTexto = 'Sin Membresía';
                                $estadoClase = 'estado-sin-membresia';
                                $disponible = true;
                            }
                        @endphp
                        
                        @if($disponible)
                        <div class="cliente-card" data-id="{{ $cliente->id }}" 
                             data-nombre="{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}"
                             data-rut="{{ $cliente->run_pasaporte }}">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="cliente-nombre">
                                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                        @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                                    </div>
                                    <div class="cliente-rut">
                                        <i class="fas fa-id-card"></i> {{ $cliente->run_pasaporte ?? 'Sin RUT' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope"></i> {{ $cliente->email }}
                                    </small>
                                </div>
                                <div class="col-md-3 text-right">
                                    <span class="estado-badge {{ $estadoClase }}">
                                        {{ $estadoTexto }}
                                    </span>
                                    @if($ultimaInscripcion && $ultimaInscripcion->fecha_vencimiento < now())
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-calendar-times"></i> 
                                            Venció: {{ $ultimaInscripcion->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users-slash fa-3x mb-3"></i>
                            <h5>No hay clientes disponibles</h5>
                            <p>Todos los clientes tienen membresías activas o no hay clientes registrados.</p>
                            <a href="{{ route('admin.clientes.create') }}" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Crear Nuevo Cliente
                            </a>
                        </div>
                    @endforelse
                </div>

                <div id="clienteSeleccionado" class="alert alert-success mt-3" style="display: none;">
                    <strong><i class="fas fa-check-circle"></i> Cliente seleccionado:</strong>
                    <span id="clienteNombreDisplay"></span>
                </div>
            </div>

            <!-- ========== PASO 2: MEMBRESÍA ========== -->
            <div class="step-indicator" id="step-2">
                <div class="alert alert-info mb-3">
                    <strong><i class="fas fa-user"></i> Cliente:</strong> 
                    <span id="paso2-cliente-nombre">-</span>
                </div>

                <div class="form-section-title"><i class="fas fa-dumbbell"></i> Seleccionar Membresía</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_membresia">Membresía <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                id="id_membresia" name="id_membresia" required>
                            <option value="">-- Seleccionar Membresía --</option>
                            @foreach($membresias as $membresia)
                                @php
                                    $duracionTexto = $membresia->duracion_meses > 0 
                                        ? ($membresia->duracion_meses == 1 ? '1 mes' : $membresia->duracion_meses . ' meses')
                                        : $membresia->duracion_dias . ' día' . ($membresia->duracion_dias > 1 ? 's' : '');
                                    $precio = $membresia->precios->first()->precio_normal ?? 0;
                                @endphp
                                <option value="{{ $membresia->id }}" 
                                        data-duracion="{{ $membresia->duracion_dias }}"
                                        data-duracion-meses="{{ $membresia->duracion_meses }}"
                                        data-precio="{{ $precio }}"
                                        data-precio-convenio="{{ $membresia->precios->first()->precio_convenio ?? 0 }}"
                                        data-max-pausas="{{ $membresia->max_pausas ?? 2 }}">
                                    {{ $membresia->nombre }} ({{ $duracionTexto }}) - ${{ number_format($precio, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_membresia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required>
                        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_termino_display">Fecha de Término</label>
                        <input type="text" class="form-control" id="fecha_termino_display" readonly 
                               style="background-color: #e9ecef; font-weight: bold; color: #28a745;">
                        <small class="text-muted">Se calcula automáticamente</small>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-handshake"></i> Convenio / Descuento</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_convenio">¿Tiene Convenio?</label>
                        <select class="form-control" id="id_convenio" name="id_convenio">
                            <option value="">-- Sin Convenio --</option>
                            @foreach($convenios as $convenio)
                                <option value="{{ $convenio->id }}">
                                    {{ $convenio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_motivo_descuento">Motivo del Descuento</label>
                        <select class="form-control" id="id_motivo_descuento" name="id_motivo_descuento">
                            <option value="">-- Sin Motivo --</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="descuento_aplicado">Descuento Manual ($)</label>
                        <input type="number" class="form-control" id="descuento_aplicado" name="descuento_aplicado" 
                               value="0" min="0" step="1">
                        <small class="text-muted">Ingrese el monto del descuento adicional</small>
                    </div>
                </div>

                <!-- RESUMEN DE PRECIOS -->
                <div class="precio-box" id="precioBox">
                    <h5 class="mb-3"><i class="fas fa-receipt"></i> Resumen de Precios</h5>
                    <div class="precio-row">
                        <span class="precio-label">Precio Base:</span>
                        <span class="precio-valor" id="display-precio-base">$0</span>
                    </div>
                    <div class="precio-row" id="row-descuento-convenio" style="display: none;">
                        <span class="precio-label">Descuento Convenio:</span>
                        <span class="precio-valor text-success" id="display-descuento-convenio">-$0</span>
                    </div>
                    <div class="precio-row" id="row-descuento-manual" style="display: none;">
                        <span class="precio-label">Descuento Manual:</span>
                        <span class="precio-valor text-success" id="display-descuento-manual">-$0</span>
                    </div>
                    <div class="precio-row">
                        <span class="precio-label"><strong>TOTAL A PAGAR:</strong></span>
                        <span class="precio-valor precio-total" id="display-precio-final">$0</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 mb-3">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                  placeholder="Notas adicionales sobre la inscripción...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 3: PAGO ========== -->
            <div class="step-indicator" id="step-3">
                <!-- Resumen visual del cliente y membresía -->
                <div class="resumen-inscripcion mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-card bg-primary text-white">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <div class="info-label">Cliente</div>
                                <div class="info-value" id="paso3-cliente-nombre">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card bg-info text-white">
                                <i class="fas fa-dumbbell fa-2x mb-2"></i>
                                <div class="info-label">Membresía</div>
                                <div class="info-value" id="paso3-membresia-nombre">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card info-card-total">
                                <div class="total-icon-wrapper">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="info-label">Total a Pagar</div>
                                <div class="info-value total-amount" id="paso3-precio-total">$0</div>
                                <div class="total-badge">
                                    <i class="fas fa-check-circle"></i> Paso Final
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-money-check-alt"></i> Seleccione Tipo de Pago</div>
                
                <!-- TIPO PAGO - Nuevo diseño con radio buttons -->
                <div class="tipo-pago-container">
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago_radio" id="tipo_completo" value="completo">
                        <label for="tipo_completo">
                            <i class="fas fa-money-bill-wave tipo-pago-icon"></i>
                            <span class="tipo-pago-title">Pago Completo</span>
                            <span class="tipo-pago-desc">Pagar el total de la membresía</span>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago_radio" id="tipo_abono" value="abono">
                        <label for="tipo_abono">
                            <i class="fas fa-hand-holding-usd tipo-pago-icon"></i>
                            <span class="tipo-pago-title">Pago Parcial</span>
                            <span class="tipo-pago-desc">Abonar una parte del total</span>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago_radio" id="tipo_mixto" value="mixto">
                        <label for="tipo_mixto">
                            <i class="fas fa-random tipo-pago-icon"></i>
                            <span class="tipo-pago-title">Pago Mixto</span>
                            <span class="tipo-pago-desc">Combinar varios métodos de pago</span>
                        </label>
                    </div>
                    <div class="tipo-pago-option">
                        <input type="radio" name="tipo_pago_radio" id="tipo_pendiente" value="pendiente">
                        <label for="tipo_pendiente">
                            <i class="fas fa-clock tipo-pago-icon"></i>
                            <span class="tipo-pago-title">Pago Pendiente</span>
                            <span class="tipo-pago-desc">Cliente pagará después</span>
                        </label>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg" id="fecha_pago" name="fecha_pago" 
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>

                <input type="hidden" id="tipo_pago" name="tipo_pago" value="">
                <input type="hidden" id="pago_pendiente" name="pago_pendiente" value="0">

                <!-- SECCIÓN PAGO SIMPLE (Completo / Abono) -->
                <div id="seccion-pago-simple" style="display:none;">
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card"></i> Detalles del Pago</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label id="label-monto">Monto a Pagar <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="monto_abonado" name="monto_abonado" 
                                               value="0" min="0" step="1">
                                    </div>
                                    <small class="text-muted" id="hint-monto"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-lg" id="id_metodo_pago" name="id_metodo_pago">
                                        <option value="">-- Seleccionar Método --</option>
                                        @foreach($metodosPago as $metodo)
                                            @if(strtolower($metodo->nombre) !== 'mixto')
                                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="referencia_pago">Referencia / Comprobante</label>
                                    <input type="text" class="form-control" id="referencia_pago" name="referencia_pago"
                                           placeholder="Ej: N° Transferencia, N° Boleta, Recibo...">
                                    <small class="text-muted">Opcional: Ingrese número de comprobante o referencia del pago</small>
                                </div>
                            </div>
                            <div id="seccion-restante" style="display:none;">
                                <div class="alert alert-warning">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Saldo Pendiente:</strong>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <span class="h4 text-danger" id="monto-restante-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN PAGO MIXTO (Diseño simple: 2 métodos) -->
                <div id="seccion-mixto" style="display:none;">
                    <div class="mixto-section">
                        <div class="mixto-title">
                            <i class="fas fa-random"></i>
                            Dividir Pago en Dos Métodos
                        </div>
                        <div class="row">
                            <!-- Método 1 -->
                            <div class="col-md-6 mb-3">
                                <div class="mixto-metodo-card">
                                    <div class="metodo-header">
                                        <i class="fas fa-1"></i> Primer Método
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Método de Pago</label>
                                        <select class="form-control" name="id_metodo_pago1" id="id_metodo_pago1">
                                            <option value="">Seleccione...</option>
                                            @foreach($metodosPago as $metodo)
                                                @if(strtolower($metodo->nombre) !== 'mixto')
                                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Monto</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-success text-white">$</span>
                                            </div>
                                            <input type="number" step="1" min="0" class="form-control" 
                                                   name="monto_metodo1" id="monto_metodo1" placeholder="0">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Referencia</label>
                                        <input type="text" class="form-control" name="referencia_metodo1" id="referencia_metodo1"
                                               placeholder="N° Comprobante...">
                                    </div>
                                </div>
                            </div>
                            <!-- Método 2 -->
                            <div class="col-md-6 mb-3">
                                <div class="mixto-metodo-card">
                                    <div class="metodo-header">
                                        <i class="fas fa-2"></i> Segundo Método
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Método de Pago</label>
                                        <select class="form-control" name="id_metodo_pago2" id="id_metodo_pago2">
                                            <option value="">Seleccione...</option>
                                            @foreach($metodosPago as $metodo)
                                                @if(strtolower($metodo->nombre) !== 'mixto')
                                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Monto</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-info text-white">$</span>
                                            </div>
                                            <input type="number" step="1" min="0" class="form-control" 
                                                   name="monto_metodo2" id="monto_metodo2" placeholder="0">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Referencia</label>
                                        <input type="text" class="form-control" name="referencia_metodo2" id="referencia_metodo2"
                                               placeholder="N° Comprobante...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumen del pago mixto -->
                        <div class="resumen-mixto">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-receipt text-primary mb-2" style="font-size: 1.5em;"></i>
                                        <small class="text-muted d-block">Total a Pagar</small>
                                        <div class="h4 text-primary mb-0" id="mixto-total-pagar">$0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-coins text-success mb-2" style="font-size: 1.5em;"></i>
                                        <small class="text-muted d-block">Suma Ingresada</small>
                                        <div class="h4 text-success mb-0" id="mixto-total-ingresado">$0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 rounded" id="mixto-diferencia-box">
                                        <i class="fas fa-balance-scale text-warning mb-2" style="font-size: 1.5em;"></i>
                                        <small class="text-muted d-block">Diferencia</small>
                                        <div class="h4 mb-0" id="mixto-diferencia">$0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0" style="border-radius: 10px;">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>La suma de ambos montos debe igualar el total a pagar</span>
                            </div>
                        </div>
                        
                        <input type="hidden" id="total-mixto" name="total_mixto" value="0">
                        <input type="hidden" id="detalle-pagos-mixto" name="detalle_pagos_mixto" value="[]">
                    </div>
                </div>

                <!-- SECCIÓN PAGO PENDIENTE -->
                <div id="seccion-pendiente" style="display:none;">
                    <div class="alert alert-warning">
                        <div class="text-center py-3">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h4>Pago Pendiente</h4>
                            <p class="mb-0">La inscripción se creará sin pago. El cliente deberá abonar posteriormente.</p>
                            <p class="h4 text-danger mt-2">Total Pendiente: <span id="total-pendiente-display">$0</span></p>
                        </div>
                    </div>
                </div>

                <!-- INFO ADICIONAL -->
                <div id="info-tipo-pago" style="display:none;">
                    <div class="alert" id="alert-tipo-pago"></div>
                </div>
            </div>

            <!-- ========== BOTONES DE NAVEGACIÓN ========== -->
            <div class="buttons-container">
                <div class="buttons-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-danger btn-lg">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-secondary btn-lg" id="btnAnterior" style="display: none;">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                </div>
                <div class="buttons-group">
                    <button type="button" class="btn btn-primary btn-lg" id="btnSiguiente">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" id="btnGuardar" style="display: none;">
                        <i class="fas fa-check-circle"></i> Confirmar Inscripción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Configuración global de SweetAlert2 con colores del tema
    const SwalEstoicos = Swal.mixin({
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'btn btn-success mx-1',
            cancelButton: 'btn btn-secondary mx-1',
            denyButton: 'btn btn-danger mx-1'
        },
        buttonsStyling: false,
        confirmButtonColor: '#00bf8e',
        cancelButtonColor: '#6c757d',
        background: '#1a1a2e',
        color: '#ffffff',
        iconColor: '#e94560'
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        background: '#1a1a2e',
        color: '#ffffff'
    });

    let pasoActual = 1;
    let clienteSeleccionadoId = null;
    let clienteSeleccionadoNombre = '';
    let precioBase = 0;
    let precioFinal = 0;

    // ========== BUSCAR CLIENTE ==========
    $('#buscarCliente').on('keyup', function() {
        const busqueda = $(this).val().toLowerCase();
        $('.cliente-card').each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            const rut = ($(this).data('rut') || '').toLowerCase();
            if (nombre.includes(busqueda) || rut.includes(busqueda)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ========== SELECCIONAR CLIENTE ==========
    $('.cliente-card').on('click', function() {
        $('.cliente-card').removeClass('selected');
        $(this).addClass('selected');
        
        clienteSeleccionadoId = $(this).data('id');
        clienteSeleccionadoNombre = $(this).data('nombre');
        
        $('#id_cliente').val(clienteSeleccionadoId);
        $('#clienteSeleccionado').show();
        $('#clienteNombreDisplay').text(clienteSeleccionadoNombre);
        $('#paso2-cliente-nombre').text(clienteSeleccionadoNombre);
        $('#paso3-cliente-nombre').text(clienteSeleccionadoNombre);

        // Efecto visual
        Toast.fire({
            icon: 'success',
            title: 'Cliente Seleccionado',
            text: clienteSeleccionadoNombre
        });
    });

    // ========== CALCULAR PRECIOS ==========
    function calcularPrecios() {
        const membresiaSelect = $('#id_membresia option:selected');
        precioBase = parseInt(membresiaSelect.data('precio')) || 0;
        let precioConvenio = parseInt(membresiaSelect.data('precio-convenio')) || 0;
        let descuentoConvenio = 0;
        let descuentoManual = parseInt($('#descuento_aplicado').val()) || 0;

        // Si tiene convenio seleccionado y hay precio convenio
        if ($('#id_convenio').val() && precioConvenio > 0) {
            descuentoConvenio = precioBase - precioConvenio;
            $('#row-descuento-convenio').show();
            $('#display-descuento-convenio').text('-$' + descuentoConvenio.toLocaleString('es-CL'));
        } else {
            $('#row-descuento-convenio').hide();
            descuentoConvenio = 0;
        }

        // Descuento manual
        if (descuentoManual > 0) {
            $('#row-descuento-manual').show();
            $('#display-descuento-manual').text('-$' + descuentoManual.toLocaleString('es-CL'));
        } else {
            $('#row-descuento-manual').hide();
        }

        precioFinal = Math.max(0, precioBase - descuentoConvenio - descuentoManual);

        $('#display-precio-base').text('$' + precioBase.toLocaleString('es-CL'));
        $('#display-precio-final').text('$' + precioFinal.toLocaleString('es-CL'));
        
        // Actualizar paso 3
        $('#paso3-precio-total').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#mixto-total-pagar').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#total-pendiente-display').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#monto_abonado').val(precioFinal);
    }

    // ========== CALCULAR FECHA TÉRMINO ==========
    function calcularFechaTermino() {
        const membresiaSelect = $('#id_membresia option:selected');
        const duracion = parseInt(membresiaSelect.data('duracion')) || 0;
        const fechaInicio = $('#fecha_inicio').val();
        
        if (fechaInicio && duracion > 0) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fecha.getDate() + duracion);
            const fechaFormateada = fecha.toLocaleDateString('es-CL');
            $('#fecha_termino_display').val(fechaFormateada);
        }
    }

    // ========== EVENTOS DE MEMBRESÍA ==========
    $('#id_membresia').on('change', function() {
        calcularPrecios();
        calcularFechaTermino();
        
        const nombreMembresia = $(this).find('option:selected').text();
        $('#paso3-membresia-nombre').text(nombreMembresia);
    });

    $('#fecha_inicio').on('change', calcularFechaTermino);
    $('#id_convenio').on('change', calcularPrecios);
    $('#descuento_aplicado').on('input', calcularPrecios);

    // ========== TIPO DE PAGO (RADIO BUTTONS) ==========
    $('input[name="tipo_pago_radio"]').on('change', function() {
        const tipo = $(this).val();
        $('#tipo_pago').val(tipo);
        
        // Ocultar todas las secciones
        $('#seccion-pago-simple').hide();
        $('#seccion-mixto').hide();
        $('#seccion-pendiente').hide();
        $('#info-tipo-pago').hide();
        
        if (tipo === 'completo') {
            $('#seccion-pago-simple').show();
            $('#pago_pendiente').val('0');
            $('#label-monto').text('Monto Total');
            $('#hint-monto').text('');
            $('#monto_abonado').val(precioFinal);
            $('#seccion-restante').hide();
            mostrarInfoPago('success', '<i class="fas fa-check-circle"></i> Pago completo - Total: $' + precioFinal.toLocaleString('es-CL'));
        } 
        else if (tipo === 'abono') {
            $('#seccion-pago-simple').show();
            $('#pago_pendiente').val('0');
            $('#label-monto').text('Monto a Abonar');
            $('#hint-monto').text('Ingrese el monto que abona el cliente');
            $('#monto_abonado').val('');
            $('#seccion-restante').show();
            mostrarInfoPago('info', '<i class="fas fa-info-circle"></i> Pago parcial - Total a cubrir: $' + precioFinal.toLocaleString('es-CL'));
        } 
        else if (tipo === 'pendiente') {
            $('#seccion-pendiente').show();
            $('#pago_pendiente').val('1');
            mostrarInfoPago('warning', '<i class="fas fa-clock"></i> Sin pago - Se registrará como pendiente');
        } 
        else if (tipo === 'mixto') {
            $('#seccion-mixto').show();
            $('#pago_pendiente').val('0');
            // Actualizar resumen mixto
            actualizarResumenMixto();
            mostrarInfoPago('info', '<i class="fas fa-random"></i> Pago mixto - Divida el pago en 2 métodos');
        }
    });

    function mostrarInfoPago(tipo, mensaje) {
        const alert = $('#alert-tipo-pago');
        alert.removeClass('alert-success alert-info alert-warning alert-danger');
        alert.addClass('alert-' + tipo);
        alert.html(mensaje);
        $('#info-tipo-pago').show();
    }

    // ========== PAGO MIXTO - 2 MÉTODOS SIMPLES ==========
    // Actualizar resumen al cambiar montos
    $('#monto_metodo1, #monto_metodo2').on('input', function() {
        actualizarResumenMixto();
    });

    $('#id_metodo_pago1, #id_metodo_pago2').on('change', function() {
        actualizarResumenMixto();
    });

    function actualizarResumenMixto() {
        const monto1 = parseInt($('#monto_metodo1').val()) || 0;
        const monto2 = parseInt($('#monto_metodo2').val()) || 0;
        const metodo1 = $('#id_metodo_pago1').val();
        const metodo2 = $('#id_metodo_pago2').val();
        const metodo1Nombre = $('#id_metodo_pago1 option:selected').text();
        const metodo2Nombre = $('#id_metodo_pago2 option:selected').text();
        
        const totalIngresado = monto1 + monto2;
        const diferencia = precioFinal - totalIngresado;

        // Construir array de detalles para el hidden field
        const detalles = [];
        if (monto1 > 0 && metodo1) {
            detalles.push({
                monto: monto1,
                id_metodo_pago: metodo1,
                metodo_nombre: metodo1Nombre
            });
        }
        if (monto2 > 0 && metodo2) {
            detalles.push({
                monto: monto2,
                id_metodo_pago: metodo2,
                metodo_nombre: metodo2Nombre
            });
        }

        // Actualizar displays
        $('#mixto-total-pagar').text('$' + precioFinal.toLocaleString('es-CL'));
        $('#mixto-total-ingresado').text('$' + totalIngresado.toLocaleString('es-CL'));
        
        const boxDiferencia = $('#mixto-diferencia-box');
        boxDiferencia.removeClass('ok error');
        
        if (diferencia === 0) {
            $('#mixto-diferencia').text('$0 ✓').css('color', 'var(--success)');
            boxDiferencia.addClass('ok');
        } else if (diferencia > 0) {
            $('#mixto-diferencia').text('-$' + diferencia.toLocaleString('es-CL')).css('color', 'var(--warning)');
        } else {
            $('#mixto-diferencia').text('+$' + Math.abs(diferencia).toLocaleString('es-CL')).css('color', 'var(--accent)');
        }

        // Guardar en hidden fields
        $('#total-mixto').val(totalIngresado);
        $('#detalle-pagos-mixto').val(JSON.stringify(detalles));
    }

    // ========== CALCULAR RESTANTE (PAGO PARCIAL) ==========
    $('#monto_abonado').on('input', function() {
        let monto = parseInt($(this).val()) || 0;
        
        // No permitir más del precio final
        if (monto > precioFinal) {
            monto = precioFinal;
            $(this).val(precioFinal);
        }
        
        const restante = Math.max(0, precioFinal - monto);
        $('#monto-restante-display').text('$' + restante.toLocaleString('es-CL'));
        
        // Actualizar alerta
        if (monto > 0 && monto < precioFinal) {
            mostrarInfoPago('info', `<i class="fas fa-coins"></i> Abono: $${monto.toLocaleString('es-CL')} | Restante: $${restante.toLocaleString('es-CL')}`);
        } else if (monto >= precioFinal) {
            mostrarInfoPago('success', '<i class="fas fa-check-circle"></i> El monto cubre el total');
        }
    });

    // ========== NAVEGACIÓN DE PASOS ==========
    function irAPaso(paso) {
        $('.step-indicator').removeClass('active');
        $('#step-' + paso).addClass('active');
        
        // Remover todas las clases de estado de los botones
        $('.step-btn').removeClass('active completed').prop('disabled', true);
        
        // Marcar el paso actual como activo (sin disabled)
        $('#step' + paso + '-btn').addClass('active').prop('disabled', false);
        
        // Marcar pasos anteriores como completados y habilitarlos
        for (let i = 1; i < paso; i++) {
            $('#step' + i + '-btn').addClass('completed').prop('disabled', false);
        }
        
        // Mostrar/ocultar botones
        if (paso === 1) {
            $('#btnAnterior').hide();
            $('#btnSiguiente').show();
            $('#btnGuardar').hide();
        } else if (paso === 3) {
            $('#btnAnterior').show();
            $('#btnSiguiente').hide();
            $('#btnGuardar').show();
        } else {
            $('#btnAnterior').show();
            $('#btnSiguiente').show();
            $('#btnGuardar').hide();
        }
        
        pasoActual = paso;
    }

    // Validar paso actual
    function validarPaso(paso) {
        if (paso === 1) {
            if (!clienteSeleccionadoId) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Cliente requerido',
                    text: 'Por favor, selecciona un cliente de la lista',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            return true;
        } else if (paso === 2) {
            if (!$('#id_membresia').val()) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Membresía requerida',
                    text: 'Por favor, selecciona una membresía',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            if (!$('#fecha_inicio').val()) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Fecha requerida',
                    text: 'Por favor, ingresa la fecha de inicio',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            return true;
        }
        return true;
    }

    $('#btnSiguiente').on('click', function() {
        if (validarPaso(pasoActual)) {
            irAPaso(pasoActual + 1);
        }
    });

    $('#btnAnterior').on('click', function() {
        irAPaso(pasoActual - 1);
    });

    // Navegación por botones de paso
    $('.step-btn').on('click', function() {
        const pasoDestino = parseInt($(this).attr('id').replace('step', '').replace('-btn', ''));
        if (!$(this).prop('disabled')) {
            irAPaso(pasoDestino);
        }
    });

    // ========== ENVÍO DEL FORMULARIO ==========
    $('#inscripcionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los pasos
        if (!clienteSeleccionadoId) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona un cliente',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            irAPaso(1);
            return false;
        }
        
        if (!$('#id_membresia').val()) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona una membresía',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            irAPaso(2);
            return false;
        }

        const tipoPago = $('#tipo_pago').val();
        
        if (!tipoPago) {
            SwalEstoicos.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, selecciona un tipo de pago',
                confirmButtonText: '<i class="fas fa-check"></i> Entendido'
            });
            return false;
        }
        
        if (tipoPago === 'mixto') {
            const monto1 = parseInt($('#monto_metodo1').val()) || 0;
            const monto2 = parseInt($('#monto_metodo2').val()) || 0;
            const metodo1 = $('#id_metodo_pago1').val();
            const metodo2 = $('#id_metodo_pago2').val();
            const totalMixto = monto1 + monto2;
            
            if (totalMixto <= 0) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error en pago mixto',
                    text: 'Ingrese al menos un monto en alguno de los métodos',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }

            // Verificar que si hay monto, haya método
            if (monto1 > 0 && !metodo1) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Seleccione el método de pago para el primer monto',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            
            if (monto2 > 0 && !metodo2) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Seleccione el método de pago para el segundo monto',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }

            // Verificar que la suma sea igual al total
            if (totalMixto !== precioFinal) {
                SwalEstoicos.fire({
                    icon: 'warning',
                    title: 'Montos no coinciden',
                    text: `La suma ($${totalMixto.toLocaleString('es-CL')}) debe ser igual al total ($${precioFinal.toLocaleString('es-CL')})`,
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
        } 
        else if (tipoPago !== 'pendiente') {
            if (!$('#monto_abonado').val() || parseFloat($('#monto_abonado').val()) <= 0) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Por favor, ingresa el monto del pago',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
            if (!$('#id_metodo_pago').val()) {
                SwalEstoicos.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Por favor, selecciona un método de pago',
                    confirmButtonText: '<i class="fas fa-check"></i> Entendido'
                });
                return false;
            }
        }

        // Determinar texto de tipo de pago
        let tipoPagoTexto = '';
        switch(tipoPago) {
            case 'completo': tipoPagoTexto = '💵 Pago Completo'; break;
            case 'abono': tipoPagoTexto = '💰 Pago Parcial'; break;
            case 'mixto': tipoPagoTexto = '🔀 Pago Mixto'; break;
            case 'pendiente': tipoPagoTexto = '⏰ Pago Pendiente'; break;
        }

        // Confirmar antes de enviar
        SwalEstoicos.fire({
            icon: 'question',
            title: '¿Confirmar inscripción?',
            html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin: 8px 0;"><i class="fas fa-user text-info"></i> <strong>Cliente:</strong> ${clienteSeleccionadoNombre}</p>
                    <p style="margin: 8px 0;"><i class="fas fa-dumbbell text-warning"></i> <strong>Membresía:</strong> ${$('#id_membresia option:selected').text().split(' - ')[0]}</p>
                    <p style="margin: 8px 0;"><i class="fas fa-credit-card text-primary"></i> <strong>Tipo Pago:</strong> ${tipoPagoTexto}</p>
                    <hr style="border-color: rgba(255,255,255,0.2); margin: 12px 0;">
                    <p style="margin: 8px 0; font-size: 1.2em;"><i class="fas fa-dollar-sign text-success"></i> <strong>Total:</strong> <span style="color: #00bf8e;">$${precioFinal.toLocaleString('es-CL')}</span></p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar Inscripción',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                SwalEstoicos.fire({
                    title: 'Registrando inscripción...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Por favor espere...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Desactivar botón para evitar doble envío
                $('#btnGuardar').prop('disabled', true);
                
                // Enviar formulario
                this.submit();
            }
        });
    });
});
</script>
@stop