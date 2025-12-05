@extends('adminlte::page')

@section('title', 'Inscripciones - EstóicosGym')

@section('meta_tags')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
@stop

@section('content')
<div class="inscripciones-container">
    <!-- Hero Header -->
    <div class="inscripciones-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="hero-text">
                <h1>Gestión de Inscripciones</h1>
                <p>Administra las membresías de los clientes</p>
            </div>
        </div>
        <div class="hero-actions">
            @if(isset($totalEliminadas) && $totalEliminadas > 0)
            <a href="{{ route('admin.inscripciones.trashed') }}" class="btn-ver-papelera">
                <i class="fas fa-trash-alt"></i>
                <span>Papelera ({{ $totalEliminadas }})</span>
            </a>
            @endif
            <a href="{{ route('admin.pagos.index') }}" class="btn-ver-pagos">
                <i class="fas fa-money-bill-wave"></i>
                <span>Ver Pagos</span>
            </a>
            <a href="{{ route('admin.inscripciones.create') }}" class="btn-nueva-inscripcion">
                <i class="fas fa-plus-circle"></i>
                <span>Nueva Inscripción</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalInscripciones }}</span>
                <span class="stat-label">Total</span>
            </div>
        </div>
        
        <div class="stat-card stat-activos">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $activas }}</span>
                <span class="stat-label">Activas</span>
            </div>
        </div>
        
        <div class="stat-card stat-vencidos">
            <div class="stat-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $vencidas }}</span>
                <span class="stat-label">Vencidas</span>
            </div>
        </div>
        
        <div class="stat-card stat-pausados">
            <div class="stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $pausadas }}</span>
                <span class="stat-label">Pausadas</span>
            </div>
        </div>
        
        <div class="stat-card stat-cancelados">
            <div class="stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $canceladas }}</span>
                <span class="stat-label">Canceladas</span>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por nombre, RUT o membresía..." autocomplete="off">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="todos">
                <i class="fas fa-list"></i> Todos
            </button>
            <button class="filter-btn" data-filter="activa">
                <i class="fas fa-check-circle"></i> Activas
            </button>
            <button class="filter-btn" data-filter="vencida">
                <i class="fas fa-calendar-times"></i> Vencidas
            </button>
            <button class="filter-btn" data-filter="pausada">
                <i class="fas fa-pause-circle"></i> Pausadas
            </button>
            <button class="filter-btn" data-filter="cancelada">
                <i class="fas fa-ban"></i> Canceladas
            </button>
            <button class="filter-btn" data-filter="suspendida">
                <i class="fas fa-exclamation-triangle"></i> Suspendidas
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-responsive">
            <table class="inscripciones-table">
                <thead>
                    <tr>
                        <th>Cliente / Membresía</th>
                        <th>Período</th>
                        <th>Precio</th>
                        <th>Estado Pago</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="inscripcionesTableBody">
                    <!-- Se llena con JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <div class="pagination-info">
                Mostrando <span id="showingFrom">0</span> - <span id="showingTo">0</span> de <span id="totalFiltered">0</span> inscripciones
            </div>
            <div class="pagination-controls">
                <button id="btnFirst" class="pagination-btn" title="Primera página"><i class="fas fa-angle-double-left"></i></button>
                <button id="btnPrev" class="pagination-btn" title="Anterior"><i class="fas fa-angle-left"></i></button>
                <div id="paginationPages" class="pagination-pages"></div>
                <button id="btnNext" class="pagination-btn" title="Siguiente"><i class="fas fa-angle-right"></i></button>
                <button id="btnLast" class="pagination-btn" title="Última página"><i class="fas fa-angle-double-right"></i></button>
            </div>
            <div class="per-page-selector">
                <select id="perPageSelect">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>por página</span>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--accent);"></i>
            <p style="margin-top: 10px; color: var(--text-secondary);">Cargando más inscripciones...</p>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --danger: #dc3545;
        --text-primary: #2c3e50;
        --text-secondary: #6c757d;
        --bg-light: #f8f9fa;
        --border-color: #e9ecef;
        --shadow: 0 4px 20px rgba(0,0,0,0.08);
        --shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
    }

    .content-wrapper {
        background: var(--bg-light) !important;
    }

    .inscripciones-container {
        padding: 20px;
        width: 100%;
    }

    /* Hero Header */
    .inscripciones-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        padding: 30px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        box-shadow: var(--shadow);
    }

    .hero-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .hero-icon {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    .hero-icon i {
        font-size: 32px;
        color: #fff;
    }

    .hero-text h1 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .hero-text p {
        color: rgba(255,255,255,0.8);
        margin: 5px 0 0;
        font-size: 15px;
    }

    .hero-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn-ver-papelera {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(239, 68, 68, 0.2);
        color: #fff;
        border: 2px solid rgba(239, 68, 68, 0.4);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-ver-papelera:hover {
        background: rgba(239, 68, 68, 0.4);
        border-color: rgba(239, 68, 68, 0.6);
        transform: translateY(-2px);
        color: #fff;
    }

    .btn-ver-pagos {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: rgba(0, 191, 142, 0.2);
        color: #fff;
        border: 2px solid rgba(0, 191, 142, 0.4);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-ver-pagos:hover {
        background: rgba(0, 191, 142, 0.4);
        border-color: rgba(0, 191, 142, 0.6);
        transform: translateY(-2px);
        color: #fff;
    }

    .btn-nueva-inscripcion {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b8a 100%);
        color: #fff;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4);
    }

    .btn-nueva-inscripcion:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(233, 69, 96, 0.5);
        color: #fff;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-card .stat-icon i {
        font-size: 18px;
        color: #fff;
    }

    .stat-card.stat-total .stat-icon {
        background: linear-gradient(135deg, var(--info) 0%, #6366f1 100%);
    }

    .stat-card.stat-activos .stat-icon {
        background: linear-gradient(135deg, var(--success) 0%, #00a67e 100%);
    }

    .stat-card.stat-vencidos .stat-icon {
        background: linear-gradient(135deg, var(--danger) 0%, #c82333 100%);
    }

    .stat-card.stat-pausados .stat-icon {
        background: linear-gradient(135deg, var(--warning) 0%, #d69200 100%);
    }

    .stat-card.stat-cancelados .stat-icon {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .stat-card.stat-suspendidos .stat-icon {
        background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .stat-number {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 2px;
        white-space: nowrap;
    }

    /* Filters Section */
    .filters-section {
        background: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow);
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 280px;
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 16px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 48px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 18px;
        border: 2px solid var(--border-color);
        background: #fff;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .filter-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }

    /* Table Section */
    .table-section {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .inscripciones-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .inscripciones-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .inscripciones-table thead th {
        color: #fff;
        font-weight: 700;
        padding: 18px 16px;
        text-align: left;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        white-space: nowrap;
    }

    .inscripciones-table thead th:first-child {
        padding-left: 24px;
        border-radius: 0;
    }

    .inscripciones-table thead th:last-child {
        padding-right: 24px;
        text-align: center;
    }

    .inscripciones-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .inscripciones-table tbody tr:hover {
        background: linear-gradient(90deg, rgba(233, 69, 96, 0.04) 0%, rgba(67, 97, 238, 0.02) 100%);
    }

    .inscripciones-table tbody tr:last-child {
        border-bottom: none;
    }

    .inscripciones-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
    }

    .inscripciones-table tbody td:first-child {
        padding-left: 24px;
    }

    .inscripciones-table tbody td:last-child {
        padding-right: 24px;
    }

    /* Cliente / Membresía Info */
    .cliente-membresia-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cliente-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b8a 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .cliente-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 15px;
    }

    .cliente-rut {
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .cliente-rut i {
        margin-right: 4px;
        color: var(--info);
    }

    .membresia-nombre {
        font-size: 13px;
        color: var(--accent);
        font-weight: 500;
    }

    .membresia-nombre i {
        margin-right: 4px;
    }

    .convenio-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        padding: 2px 8px;
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
        border-radius: 6px;
        font-weight: 600;
    }

    /* Periodo Info */
    .periodo-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .fechas {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .fecha-item {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .fecha-item i {
        margin-right: 6px;
        width: 14px;
    }

    .dias-restantes {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dias-ok {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }

    .dias-warning {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
    }

    .dias-danger {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }

    .dias-vencido {
        background: rgba(220, 53, 69, 0.12);
        color: var(--danger);
    }

    /* Precio Info */
    .precio-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .precio-base-tachado {
        font-size: 12px;
        color: var(--text-secondary);
        text-decoration: line-through;
    }

    .descuento-tag {
        font-size: 11px;
        color: var(--success);
        font-weight: 600;
    }

    .precio-final {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
    }

    /* Estado Pago */
    .estado-pago-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .pago-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .pago-completo {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }

    .pago-parcial {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
    }

    .pago-pendiente {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }

    .pago-detalle {
        font-size: 11px;
        color: var(--text-secondary);
    }

    .pago-progress {
        width: 100%;
        height: 4px;
        background: var(--border-color);
        border-radius: 2px;
        overflow: hidden;
    }

    .pago-progress-bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .pago-progress-bar.completo {
        background: linear-gradient(90deg, var(--success), #00d9a0);
    }

    .pago-progress-bar.parcial {
        background: linear-gradient(90deg, var(--warning), #ffb700);
    }

    .pago-progress-bar.pendiente {
        background: linear-gradient(90deg, var(--accent), #ff6b8a);
    }

    /* Estado Badge */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .estado-activa {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }

    .estado-pausada {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
    }

    .estado-vencida {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }

    .estado-cancelada {
        background: rgba(108, 117, 125, 0.15);
        color: #6c757d;
    }

    .estado-suspendida {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .estado-cambiada {
        background: rgba(23, 162, 184, 0.15);
        color: #17a2b8;
    }

    .estado-traspasada {
        background: rgba(102, 16, 242, 0.15);
        color: #6610f2;
    }

    .pausa-info {
        display: block;
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    /* Estado Container para estados combinados */
    .estado-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .estado-secundario {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        background: rgba(240, 165, 0, 0.08);
        color: var(--warning);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .estado-secundario i {
        font-size: 9px;
    }

    /* Acciones */
    .acciones-btns {
        display: flex;
        gap: 6px;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        border: 2px solid transparent;
        cursor: pointer;
        position: relative;
    }

    .btn-action i {
        font-size: 15px;
        transition: transform 0.2s ease;
    }

    .btn-action:hover i {
        transform: scale(1.15);
    }

    /* Tooltip personalizado */
    .btn-action[data-tooltip]::before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(5px);
        padding: 6px 12px;
        background: var(--primary);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 100;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-action[data-tooltip]::after {
        content: '';
        position: absolute;
        bottom: calc(100% + 2px);
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: var(--primary);
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }

    .btn-action[data-tooltip]:hover::before,
    .btn-action[data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .btn-view {
        background: rgba(67, 97, 238, 0.15);
        color: var(--info);
        border-color: rgba(67, 97, 238, 0.3);
    }

    .btn-view:hover {
        background: var(--info);
        color: #fff;
        border-color: var(--info);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    .btn-edit {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
        border-color: rgba(240, 165, 0, 0.3);
    }

    .btn-edit:hover {
        background: var(--warning);
        color: #fff;
        border-color: var(--warning);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(240, 165, 0, 0.4);
    }

    .btn-pay {
        background: rgba(0, 191, 142, 0.15);
        color: var(--success);
        border-color: rgba(0, 191, 142, 0.3);
    }

    .btn-pay:hover {
        background: var(--success);
        color: #fff;
        border-color: var(--success);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 191, 142, 0.4);
    }

    .btn-pause {
        background: rgba(240, 165, 0, 0.15);
        color: #d97706;
        border-color: rgba(240, 165, 0, 0.3);
    }

    .btn-pause:hover {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: #fff;
        border-color: var(--warning);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(240, 165, 0, 0.4);
    }

    .btn-resume {
        background: rgba(0, 191, 142, 0.15);
        color: #059669;
        border-color: rgba(0, 191, 142, 0.3);
    }

    .btn-resume:hover {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: #fff;
        border-color: var(--success);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 191, 142, 0.4);
    }

    .btn-delete {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
        border-color: rgba(233, 69, 96, 0.3);
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, var(--accent) 0%, #dc2626 100%);
        color: #fff;
        border-color: var(--accent);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(233, 69, 96, 0.4);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    .empty-content i {
        font-size: 64px;
        color: var(--border-color);
    }

    .empty-content h3 {
        font-size: 20px;
        color: var(--text-primary);
        margin: 0;
    }

    .empty-content p {
        color: var(--text-secondary);
        margin: 0;
    }

    .btn-nueva-inscripcion-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--accent);
        color: #fff;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-nueva-inscripcion-inline:hover {
        background: #d13652;
        color: #fff;
        transform: translateY(-2px);
    }

    /* Pagination */
    .pagination-section {
        padding: 20px;
        display: flex;
        justify-content: center;
        border-top: 1px solid var(--border-color);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .inscripciones-hero {
            flex-direction: column;
            gap: 20px;
            text-align: center;
            padding: 24px;
        }

        .hero-content {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filters-section {
            flex-direction: column;
        }

        .filter-buttons {
            width: 100%;
            justify-content: center;
        }
    }

    /* SweetAlert Estoicos - Diseño Mejorado */
    .swal-estoicos {
        border-radius: 24px !important;
        padding: 0 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    
    .swal-estoicos .swal2-popup {
        border-radius: 24px;
        padding: 2rem 2rem 1.5rem;
    }
    
    .swal-estoicos .swal2-title {
        color: var(--primary);
        font-weight: 800;
        font-size: 1.4rem;
        padding: 0 0 0.5rem 0;
    }
    
    .swal-estoicos .swal2-html-container {
        margin: 0;
        padding: 0.5rem 0;
    }
    
    .swal-estoicos .swal2-actions {
        margin-top: 1.5rem;
        gap: 12px;
    }
    
    .swal-estoicos .swal2-confirm {
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b8a 100%) !important;
        border-radius: 12px !important;
        padding: 14px 36px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4) !important;
        transition: all 0.3s ease !important;
    }
    
    .swal-estoicos .swal2-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(233, 69, 96, 0.5) !important;
    }
    
    .swal-estoicos .swal2-cancel {
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 14px 36px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    
    .swal-estoicos .swal2-cancel:hover {
        background: #e2e8f0 !important;
        transform: translateY(-2px) !important;
    }
    
    .swal-estoicos .swal2-validation-message {
        background: rgba(233, 69, 96, 0.1) !important;
        color: var(--accent) !important;
        border-radius: 10px !important;
        padding: 12px 16px !important;
        font-weight: 600 !important;
        margin: 1rem 0 0 0 !important;
    }
    
    .swal-estoicos .swal2-validation-message::before {
        color: var(--accent) !important;
    }

    /* ===== PAGINATION STYLES ===== */
    .pagination-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination-info {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .pagination-info span {
        font-weight: 600;
        color: var(--primary);
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pagination-btn {
        width: 36px;
        height: 36px;
        border: none;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-pages {
        display: flex;
        gap: 5px;
    }

    .pagination-num {
        min-width: 36px;
        height: 36px;
        border: none;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        color: var(--text-secondary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .pagination-num:hover {
        background: var(--accent);
        color: white;
        transform: translateY(-2px);
    }

    .pagination-num.active {
        background: var(--accent);
        color: white;
    }

    .pagination-ellipsis {
        display: flex;
        align-items: center;
        padding: 0 8px;
        color: var(--text-secondary);
    }

    .per-page-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .per-page-selector select {
        padding: 6px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-weight: 600;
    }

    .per-page-selector select:focus {
        outline: none;
        border-color: var(--accent);
    }

    @media (max-width: 768px) {
        .pagination-section {
            flex-direction: column;
            gap: 15px;
        }
        .pagination-controls {
            order: -1;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // ============================================
    // SISTEMA DE LAZY LOADING CON PAGINACIÓN LOCAL
    // ============================================
    
    // Datos globales
    let allInscripciones = @json($inscripcionesData);
    let hasMoreData = {{ $totalInscripciones > 100 ? 'true' : 'false' }};
    let nextOffset = 100;
    let isLoading = false;
    
    // Estado de la paginación
    let currentPage = 1;
    let perPage = 20;
    let currentFilter = 'todos';
    let currentSearch = '';
    
    // CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    /**
     * Formatear número con separador de miles (siempre entero)
     */
    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /**
     * Obtener inscripciones filtradas
     */
    function getFilteredInscripciones() {
        return allInscripciones.filter(insc => {
            // Filtro por estado
            let matchesFilter = true;
            if (currentFilter !== 'todos') {
                matchesFilter = insc.estado_class === currentFilter;
            }
            
            // Filtro por búsqueda
            let matchesSearch = true;
            if (currentSearch) {
                const searchLower = currentSearch.toLowerCase();
                const nombre = ((insc.cliente_nombres || '') + ' ' + (insc.cliente_apellido || '')).toLowerCase();
                const rut = (insc.cliente_rut || '').toLowerCase();
                const membresia = (insc.membresia_nombre || '').toLowerCase();
                
                matchesSearch = nombre.includes(searchLower) || 
                               rut.includes(searchLower) || 
                               membresia.includes(searchLower);
            }
            
            return matchesFilter && matchesSearch;
        });
    }

    /**
     * Renderizar una fila de inscripción
     */
    function renderInscripcionRow(insc) {
        // Determinar clase de días restantes
        let diasClass = 'dias-ok';
        if (insc.dias_restantes <= 0) diasClass = 'dias-vencido';
        else if (insc.dias_restantes <= 7) diasClass = 'dias-danger';
        else if (insc.dias_restantes <= 30) diasClass = 'dias-warning';

        // Texto de días restantes
        let diasTexto = '';
        if (insc.dias_restantes > 0) {
            diasTexto = `<i class="fas fa-hourglass-half"></i> ${insc.dias_restantes} días`;
        } else if (insc.dias_restantes === 0) {
            diasTexto = `<i class="fas fa-exclamation-triangle"></i> Vence hoy`;
        } else {
            diasTexto = `<i class="fas fa-times-circle"></i> Vencida hace ${Math.abs(insc.dias_restantes)}d`;
        }

        // Estado pago badge
        let pagoBadge = '';
        if (insc.estado_pago === 'pagado') {
            pagoBadge = `<span class="pago-badge pago-completo"><i class="fas fa-check-circle"></i> Pagado</span>`;
        } else if (insc.estado_pago === 'parcial') {
            pagoBadge = `
                <span class="pago-badge pago-parcial"><i class="fas fa-hourglass-half"></i> Parcial</span>
                <span class="pago-detalle">$${formatNumber(insc.total_abonado)} / $${formatNumber(insc.total_abonado + insc.pago_pendiente)}</span>
            `;
        } else {
            pagoBadge = `<span class="pago-badge pago-pendiente"><i class="fas fa-exclamation-circle"></i> Pendiente</span>`;
        }

        // Progress bar class
        let progressClass = 'pendiente';
        if (insc.porcentaje_pagado >= 100) progressClass = 'completo';
        else if (insc.porcentaje_pagado >= 50) progressClass = 'parcial';

        // Estado icon - usar el que viene del servidor
        let estadoIcon = `<i class="fas ${insc.estado_icon}"></i>`;

        // Botones de acción
        let actionButtons = `
            <a href="${insc.showUrl}" class="btn-action btn-view" data-tooltip="Ver Detalles"><i class="fas fa-eye"></i></a>
            <a href="${insc.editUrl}" class="btn-action btn-edit" data-tooltip="Editar"><i class="fas fa-pen"></i></a>
        `;

        if (insc.esta_pausada) {
            actionButtons += `
                <button type="button" class="btn-action btn-resume btn-reanudar" 
                    data-uuid="${insc.uuid}" data-cliente="${insc.cliente_nombres} ${insc.cliente_apellido}"
                    data-tooltip="Reanudar"><i class="fas fa-play"></i></button>
            `;
        } else if (insc.estado_class === 'activa') {
            actionButtons += `
                <button type="button" class="btn-action btn-pause btn-pausar" 
                    data-uuid="${insc.uuid}" data-cliente="${insc.cliente_nombres} ${insc.cliente_apellido}"
                    data-pausas-usadas="${insc.pausas_realizadas}" data-pausas-max="${insc.max_pausas_permitidas}"
                    data-tooltip="Pausar"><i class="fas fa-pause"></i></button>
            `;
        }

        if (insc.estado_pago !== 'pagado') {
            actionButtons += `
                <a href="${insc.pagoUrl}" class="btn-action btn-pay" data-tooltip="Registrar Pago"><i class="fas fa-dollar-sign"></i></a>
            `;
        }

        actionButtons += `
            <form action="${insc.deleteUrl}" method="POST" class="d-inline form-delete">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn-action btn-delete" data-tooltip="Eliminar"><i class="fas fa-trash"></i></button>
            </form>
        `;

        return `
            <tr class="inscripcion-row" data-id="${insc.id}">
                <td>
                    <div class="cliente-membresia-info">
                        <div class="cliente-avatar">${insc.cliente_initials}</div>
                        <div class="cliente-details">
                            <span class="cliente-nombre">${insc.cliente_nombres} ${insc.cliente_apellido}</span>
                            <span class="cliente-rut"><i class="fas fa-id-card"></i> ${insc.cliente_rut}</span>
                            <span class="membresia-nombre"><i class="fas fa-dumbbell"></i> ${insc.membresia_nombre}</span>
                            ${insc.convenio_nombre ? `<span class="convenio-badge"><i class="fas fa-handshake"></i> ${insc.convenio_nombre}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="periodo-info">
                        <div class="fechas">
                            <span class="fecha-item"><i class="fas fa-calendar-plus"></i> ${insc.fecha_inicio}</span>
                            <span class="fecha-item"><i class="fas fa-calendar-times"></i> ${insc.fecha_vencimiento}</span>
                        </div>
                        <div class="dias-restantes ${diasClass}">${diasTexto}</div>
                    </div>
                </td>
                <td>
                    <div class="precio-info">
                        ${insc.descuento_aplicado > 0 ? `
                            <span class="precio-base-tachado">$${formatNumber(insc.precio_base)}</span>
                            <span class="descuento-tag"><i class="fas fa-tag"></i> -$${formatNumber(insc.descuento_aplicado)}</span>
                        ` : ''}
                        <span class="precio-final">$${formatNumber(insc.precio_final)}</span>
                    </div>
                </td>
                <td>
                    <div class="estado-pago-info">
                        ${pagoBadge}
                        <div class="pago-progress">
                            <div class="pago-progress-bar ${progressClass}" style="width: ${Math.min(insc.porcentaje_pagado, 100)}%"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="estado-container">
                        <span class="estado-badge estado-${insc.estado_class}">
                            ${estadoIcon} ${insc.estado_display}
                        </span>
                        ${insc.estado_secundario ? `<span class="estado-secundario"><i class="fas fa-clock"></i> ${insc.estado_secundario}</span>` : ''}
                    </div>
                </td>
                <td>
                    <div class="acciones-btns">${actionButtons}</div>
                </td>
            </tr>
        `;
    }

    /**
     * Renderizar tabla con paginación
     */
    function renderTable() {
        const filtered = getFilteredInscripciones();
        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / perPage);
        
        if (currentPage > totalPages) currentPage = totalPages || 1;
        
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = Math.min(startIndex + perPage, totalFiltered);
        const pageInscripciones = filtered.slice(startIndex, endIndex);
        
        const tbody = $('#inscripcionesTableBody');
        if (pageInscripciones.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-content">
                            <i class="fas fa-id-card"></i>
                            <h3>No se encontraron inscripciones</h3>
                            <p>Intenta con otros filtros o términos de búsqueda</p>
                        </div>
                    </td>
                </tr>
            `);
        } else {
            tbody.html(pageInscripciones.map(renderInscripcionRow).join(''));
        }
        
        // Actualizar info
        $('#showingFrom').text(totalFiltered > 0 ? startIndex + 1 : 0);
        $('#showingTo').text(endIndex);
        $('#totalFiltered').text(totalFiltered);
        
        renderPaginationNumbers(totalPages);
        
        $('#btnFirst, #btnPrev').prop('disabled', currentPage <= 1);
        $('#btnNext, #btnLast').prop('disabled', currentPage >= totalPages);
        
        checkAndLoadMore();
        bindDeleteEvents();
    }

    /**
     * Renderizar números de página
     */
    function renderPaginationNumbers(totalPages) {
        const container = $('#paginationPages');
        let html = '';
        
        const maxVisible = 5;
        let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages, start + maxVisible - 1);
        
        if (end - start < maxVisible - 1) {
            start = Math.max(1, end - maxVisible + 1);
        }
        
        if (start > 1) {
            html += `<button class="pagination-num" data-page="1">1</button>`;
            if (start > 2) html += `<span class="pagination-ellipsis">...</span>`;
        }
        
        for (let i = start; i <= end; i++) {
            html += `<button class="pagination-num ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        if (end < totalPages) {
            if (end < totalPages - 1) html += `<span class="pagination-ellipsis">...</span>`;
            html += `<button class="pagination-num" data-page="${totalPages}">${totalPages}</button>`;
        }
        
        container.html(html);
    }

    /**
     * Verificar y cargar más datos si es necesario
     */
    function checkAndLoadMore() {
        if (hasMoreData && !isLoading && currentPage >= 5 && (currentPage * perPage) > (allInscripciones.length - 50)) {
            loadMoreInscripciones();
        }
    }

    /**
     * Cargar más inscripciones via AJAX
     */
    function loadMoreInscripciones() {
        if (isLoading || !hasMoreData) return;
        
        isLoading = true;
        $('#loadingIndicator').show();
        
        $.ajax({
            url: '{{ route("admin.inscripciones.index") }}',
            method: 'GET',
            data: { offset: nextOffset },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.inscripciones && response.inscripciones.length > 0) {
                    allInscripciones = allInscripciones.concat(response.inscripciones);
                    hasMoreData = response.hasMore;
                    nextOffset = response.nextOffset;
                    renderTable();
                } else {
                    hasMoreData = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando más inscripciones:', error);
            },
            complete: function() {
                isLoading = false;
                $('#loadingIndicator').hide();
            }
        });
    }

    /**
     * Vincular eventos de eliminación
     */
    function bindDeleteEvents() {
        $('.form-delete').off('submit').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const clienteNombre = $(this).closest('tr').find('.cliente-nombre').text();
            
            Swal.fire({
                title: '¿Eliminar inscripción?',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-id-card" style="font-size: 2rem; color: #dc3545;"></i>
                        </div>
                        <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">${clienteNombre}</p>
                        <p style="color: #64748b; font-size: 0.9rem;">La inscripción se moverá a la papelera y podrás restaurarla después.</p>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm', cancelButton: 'swal2-cancel' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        html: '<div style="padding: 2rem;"><div style="width: 50px; height: 50px; border: 4px solid #fee2e2; border-top-color: #dc3545; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div></div><style>@keyframes spin { to { transform: rotate(360deg); } }</style>',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: { popup: 'swal-estoicos' }
                    });
                    form.submit();
                }
            });
        });
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    // Búsqueda
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = $(this).val();
            currentPage = 1;
            renderTable();
        }, 300);
    });
    
    // Filtros
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        currentPage = 1;
        renderTable();
    });
    
    // Navegación de páginas
    $('#btnFirst').on('click', () => { currentPage = 1; renderTable(); });
    $('#btnPrev').on('click', () => { currentPage--; renderTable(); });
    $('#btnNext').on('click', () => { currentPage++; renderTable(); });
    $('#btnLast').on('click', () => { 
        const filtered = getFilteredInscripciones();
        currentPage = Math.ceil(filtered.length / perPage); 
        renderTable(); 
    });
    
    $(document).on('click', '.pagination-num', function() {
        currentPage = parseInt($(this).data('page'));
        renderTable();
    });
    
    // Cambiar cantidad por página
    $('#perPageSelect').on('change', function() {
        perPage = parseInt($(this).val());
        currentPage = 1;
        renderTable();
    });

    // ============================================
    // PAUSAR / REANUDAR INSCRIPCIÓN
    // ============================================
    
    $(document).on('click', '.btn-pausar', function() {
        const uuid = $(this).data('uuid');
        const cliente = $(this).data('cliente');
        const pausasUsadas = parseInt($(this).data('pausas-usadas')) || 0;
        const pausasMax = parseInt($(this).data('pausas-max')) || 2;
        
        if (pausasUsadas >= pausasMax) {
            Swal.fire({
                title: 'Límite de pausas alcanzado',
                html: `<p>Esta inscripción ya ha utilizado todas sus pausas permitidas (${pausasMax}/${pausasMax}).</p>`,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
                buttonsStyling: false
            });
            return;
        }
        
        Swal.fire({
            title: 'Pausar Inscripción',
            html: `
                <div style="text-align: center; margin-bottom: 1rem;">
                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>${cliente}</strong></p>
                    <p style="color: #6c757d; font-size: 0.9rem;">Pausas utilizadas: ${pausasUsadas}/${pausasMax}</p>
                </div>
                <p style="font-weight: 600; margin-bottom: 0.75rem;">Selecciona la duración:</p>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 1rem;">
                    <div class="pause-option-swal" data-dias="7" style="cursor: pointer; padding: 15px 10px; border: 2px solid #dee2e6; border-radius: 12px; text-align: center; transition: all 0.2s;">
                        <i class="fas fa-hourglass-start" style="font-size: 1.5rem; color: #f0a500; margin-bottom: 5px; display: block;"></i>
                        <div style="font-size: 1.2rem; font-weight: 700;">7</div>
                        <div style="font-size: 0.75rem; color: #6c757d;">días</div>
                    </div>
                    <div class="pause-option-swal" data-dias="14" style="cursor: pointer; padding: 15px 10px; border: 2px solid #dee2e6; border-radius: 12px; text-align: center; transition: all 0.2s;">
                        <i class="fas fa-hourglass-half" style="font-size: 1.5rem; color: #f0a500; margin-bottom: 5px; display: block;"></i>
                        <div style="font-size: 1.2rem; font-weight: 700;">14</div>
                        <div style="font-size: 0.75rem; color: #6c757d;">días</div>
                    </div>
                    <div class="pause-option-swal" data-dias="30" style="cursor: pointer; padding: 15px 10px; border: 2px solid #dee2e6; border-radius: 12px; text-align: center; transition: all 0.2s;">
                        <i class="fas fa-hourglass-end" style="font-size: 1.5rem; color: #f0a500; margin-bottom: 5px; display: block;"></i>
                        <div style="font-size: 1.2rem; font-weight: 700;">30</div>
                        <div style="font-size: 0.75rem; color: #6c757d;">días</div>
                    </div>
                    <div class="pause-option-swal" data-dias="indefinida" style="cursor: pointer; padding: 15px 10px; border: 2px solid #dee2e6; border-radius: 12px; text-align: center; transition: all 0.2s;">
                        <i class="fas fa-infinity" style="font-size: 1.5rem; color: #6610f2; margin-bottom: 5px; display: block;"></i>
                        <div style="font-size: 0.85rem; font-weight: 700;">Hasta</div>
                        <div style="font-size: 0.7rem; color: #6c757d;">nuevo aviso</div>
                    </div>
                </div>
                <div id="razonPausaContainer" style="display: none;">
                    <label style="font-weight: 500; display: block; margin-bottom: 5px;">Motivo (requerido para indefinida):</label>
                    <textarea id="razonPausa" class="swal2-textarea" placeholder="Describe el motivo de la pausa..." style="width: 100%; margin: 0;"></textarea>
                </div>
                <input type="hidden" id="diasPausaSeleccionados" value="">
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-pause"></i> Pausar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm', cancelButton: 'swal2-cancel' },
            buttonsStyling: false,
            didOpen: () => {
                // Agregar eventos a las opciones de pausa
                document.querySelectorAll('.pause-option-swal').forEach(opt => {
                    opt.addEventListener('click', function() {
                        document.querySelectorAll('.pause-option-swal').forEach(o => {
                            o.style.borderColor = '#dee2e6';
                            o.style.background = 'white';
                        });
                        this.style.borderColor = '#4361ee';
                        this.style.background = 'rgba(67, 97, 238, 0.08)';
                        document.getElementById('diasPausaSeleccionados').value = this.dataset.dias;
                        
                        // Mostrar/ocultar razón
                        const razonContainer = document.getElementById('razonPausaContainer');
                        if (this.dataset.dias === 'indefinida') {
                            razonContainer.style.display = 'block';
                        } else {
                            razonContainer.style.display = 'none';
                        }
                    });
                });
            },
            preConfirm: () => {
                const dias = document.getElementById('diasPausaSeleccionados').value;
                const razon = document.getElementById('razonPausa')?.value || '';
                if (!dias) {
                    Swal.showValidationMessage('Selecciona una duración de pausa');
                    return false;
                }
                if (dias === 'indefinida' && !razon.trim()) {
                    Swal.showValidationMessage('La pausa indefinida requiere un motivo');
                    return false;
                }
                return { dias: dias === 'indefinida' ? 0 : dias, razon, esIndefinida: dias === 'indefinida' };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/inscripciones/${uuid}/pausar`,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        dias_pausa: result.value.dias,
                        razon_pausa: result.value.razon
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Pausada!',
                            text: response.message || 'La inscripción ha sido pausada correctamente.',
                            icon: 'success',
                            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
                            buttonsStyling: false
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || xhr.responseJSON?.error || 'No se pudo pausar la inscripción',
                            icon: 'error',
                            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-reanudar', function() {
        const uuid = $(this).data('uuid');
        const cliente = $(this).data('cliente');
        
        Swal.fire({
            title: '¿Reanudar inscripción?',
            html: `<p>Se reanudará la inscripción de <strong>${cliente}</strong></p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-play"></i> Reanudar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm', cancelButton: 'swal2-cancel' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/inscripciones/${uuid}/reanudar`,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Reanudada!',
                            text: response.message || 'La inscripción ha sido reanudada.',
                            icon: 'success',
                            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
                            buttonsStyling: false
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || xhr.responseJSON?.error || 'No se pudo reanudar la inscripción',
                            icon: 'error',
                            customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    // Renderizar tabla inicial
    renderTable();

    // Mensajes de sesión
    @if(session('success'))
    Swal.fire({
        title: '¡Éxito!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 1.8rem; color: #155724;"></i>
                </div>
                <p style="color: #64748b; font-size: 1rem;">{{ session('success') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Continuar',
        customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
        buttonsStyling: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: 'Error',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-times" style="font-size: 1.8rem; color: #b91c1c;"></i>
                </div>
                <p style="color: #64748b; font-size: 1rem;">{{ session('error') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Entendido',
        customClass: { popup: 'swal-estoicos', confirmButton: 'swal2-confirm' },
        buttonsStyling: false
    });
    @endif
});
</script>
@stop