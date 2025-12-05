@extends('adminlte::page')

@section('title', 'Pagos - EstóicosGym')

@section('meta_tags')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
@stop

@section('content')
<div class="pagos-container">
    <!-- Hero Header -->
    <div class="pagos-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="hero-text">
                <h1>Gestión de Pagos</h1>
                <p>Control y seguimiento de pagos de membresías</p>
            </div>
        </div>
        <div class="hero-actions">
            @if(isset($totalEliminados) && $totalEliminados > 0)
            <a href="{{ route('admin.pagos.trashed') }}" class="btn-ver-papelera">
                <i class="fas fa-trash-alt"></i>
                <span>Papelera ({{ $totalEliminados }})</span>
            </a>
            @endif
            <a href="{{ route('admin.inscripciones.index') }}" class="btn-ver-inscripciones">
                <i class="fas fa-id-card"></i>
                <span>Ver Inscripciones</span>
            </a>
            <a href="{{ route('admin.pagos.create') }}" class="btn-nuevo-pago">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Pago</span>
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert-estoicos success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito!</strong>
                <span>{{ $message }}</span>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="statTotalPagos">{{ $totalPagos ?? 0 }}</span>
                <span class="stat-label">Total Pagos</span>
            </div>
        </div>
        
        <div class="stat-card stat-recaudado">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="statRecaudado">${{ number_format($estadisticas['total_recaudado'] ?? 0, 0, ',', '.') }}</span>
                <span class="stat-label">Recaudado</span>
            </div>
        </div>
        
        <div class="stat-card stat-completados">
            <div class="stat-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="statCompletados">{{ $estadisticas['completados'] ?? 0 }}</span>
                <span class="stat-label">Completados</span>
            </div>
        </div>
        
        <div class="stat-card stat-pendientes">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="statParciales">{{ $estadisticas['parciales'] ?? 0 }}</span>
                <span class="stat-label">Parciales</span>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por cliente, membresía o referencia..." autocomplete="off">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="todos">
                <i class="fas fa-list"></i> Todos
            </button>
            <button class="filter-btn" data-filter="pagado">
                <i class="fas fa-check-circle"></i> Pagados
            </button>
            <button class="filter-btn" data-filter="parcial">
                <i class="fas fa-hourglass-half"></i> Parciales
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-list-alt"></i> Listado de Pagos</h3>
            <span class="results-count" id="resultCount">0 de {{ $totalPagos ?? 0 }}</span>
        </div>
        <div class="table-responsive">
            <table class="pagos-table">
                <thead>
                    <tr>
                        <th>Cliente / Membresía</th>
                        <th>Fecha</th>
                        <th>Montos</th>
                        <th>Estado</th>
                        <th>Método</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="pagosTableBody">
                    <!-- Renderizado por JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="pagination-section" id="paginationSection">
            <div class="pagination-info">
                <span id="paginationInfo">Mostrando 0 de 0 pagos</span>
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" id="btnFirst" onclick="goToPage(1)" disabled>
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button class="pagination-btn" id="btnPrev" onclick="goToPage(currentPage - 1)" disabled>
                    <i class="fas fa-angle-left"></i>
                </button>
                <span class="pagination-pages" id="paginationPages"></span>
                <button class="pagination-btn" id="btnNext" onclick="goToPage(currentPage + 1)" disabled>
                    <i class="fas fa-angle-right"></i>
                </button>
                <button class="pagination-btn" id="btnLast" onclick="goToPage(totalPages)" disabled>
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
            <div class="pagination-per-page">
                <label>Mostrar:</label>
                <select id="perPageSelect" onchange="changePerPage(this.value)">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Loading indicator -->
        <div class="loading-more" id="loadingMore" style="display: none;">
            <div class="spinner"></div>
            <span>Cargando más pagos...</span>
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
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --success-dark: #00a67d;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 25px -5px rgb(0 0 0 / 0.15);
    }

    .content-wrapper { background: var(--gray-50) !important; }
    .content { padding: 0 !important; }

    .pagos-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ===== HERO HEADER ===== */
    .pagos-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    .pagos-hero::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 180px;
        height: 180px;
        background: var(--success);
        border-radius: 50%;
        opacity: 0.3;
    }
    .pagos-hero::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 30%;
        width: 120px;
        height: 120px;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.3;
    }
    .hero-content {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .hero-icon {
        width: 56px;
        height: 56px;
        background: rgba(0, 191, 142, 0.2);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6em;
        color: var(--success);
    }
    .hero-text h1 {
        color: white;
        font-size: 1.6em;
        font-weight: 700;
        margin: 0;
    }
    .hero-text p {
        color: rgba(255,255,255,0.8);
        margin: 4px 0 0;
        font-size: 0.9em;
    }
    .hero-actions { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; }
    .btn-ver-papelera {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    .btn-ver-papelera:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .btn-ver-inscripciones {
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }
    .btn-ver-inscripciones:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);
    }
    .btn-nuevo-pago {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.3);
    }
    .btn-nuevo-pago:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 191, 142, 0.4);
        color: white;
        text-decoration: none;
    }

    /* ===== ALERT ===== */
    .alert-estoicos {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        position: relative;
    }
    .alert-estoicos.success {
        background: rgba(0, 191, 142, 0.1);
        border: 1px solid rgba(0, 191, 142, 0.3);
    }
    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1em;
    }
    .alert-estoicos.success .alert-icon {
        background: var(--success);
        color: white;
    }
    .alert-content strong {
        display: block;
        color: var(--gray-800);
        margin-bottom: 2px;
    }
    .alert-content span { color: var(--gray-600); font-size: 0.9em; }
    .alert-close {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        padding: 4px;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--gray-300);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2em;
    }
    .stat-card .stat-info { display: flex; flex-direction: column; }
    .stat-card .stat-number { font-size: 1.5em; font-weight: 800; color: var(--gray-800); }
    .stat-card .stat-label { font-size: 0.75em; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    
    .stat-total { border-left-color: var(--info); }
    .stat-total .stat-icon { background: rgba(67, 97, 238, 0.2); color: #2541b2; }
    .stat-total .stat-icon i { color: #2541b2 !important; }
    .stat-total .stat-number { color: var(--info); }
    
    .stat-recaudado { border-left-color: var(--success); }
    .stat-recaudado .stat-icon { background: rgba(0, 191, 142, 0.2); color: #00896b; }
    .stat-recaudado .stat-icon i { color: #00896b !important; }
    .stat-recaudado .stat-number { color: var(--success); }
    
    .stat-completados { border-left-color: var(--warning); }
    .stat-completados .stat-icon { background: rgba(240, 165, 0, 0.2); color: #c78500; }
    .stat-completados .stat-icon i { color: #c78500 !important; }
    .stat-completados .stat-number { color: var(--warning); }
    
    .stat-pendientes { border-left-color: var(--accent); }
    .stat-pendientes .stat-icon { background: rgba(233, 69, 96, 0.2); color: #c9304c; }
    .stat-pendientes .stat-icon i { color: #c9304c !important; }
    .stat-pendientes .stat-number { color: var(--accent); }

    /* ===== FILTERS SECTION ===== */
    .filters-section {
        background: white;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow);
    }
    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
    }
    .search-box input {
        width: 100%;
        padding: 10px 14px 10px 42px;
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }
    .search-box input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }
    .filter-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-btn {
        padding: 8px 16px;
        border: 2px solid var(--gray-200);
        background: white;
        border-radius: 8px;
        font-size: 0.85em;
        font-weight: 600;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .filter-btn:hover { border-color: var(--accent); color: var(--accent); }
    .filter-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    /* ===== TABLE SECTION ===== */
    .table-section {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    .table-header {
        background: var(--primary);
        color: white;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-header h3 {
        margin: 0;
        font-size: 1em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .table-header h3 i { color: var(--accent); }
    .results-count {
        background: rgba(255,255,255,0.15);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
    }
    .pagos-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pagos-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: table-header-group !important;
        visibility: visible !important;
    }
    .pagos-table thead tr {
        display: table-row !important;
    }
    .pagos-table thead th {
        padding: 14px 16px;
        text-align: left;
        font-size: 0.8em;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: none;
        display: table-cell !important;
    }
    .pagos-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }
    .pagos-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }
    .pagos-table tbody tr {
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.2s ease;
    }
    .pagos-table tbody tr:hover { background: var(--gray-50); }
    .pagos-table tbody td { padding: 14px 16px; vertical-align: middle; }

    /* ===== CLIENTE INFO ===== */
    .cliente-pago-info { display: flex; align-items: center; gap: 12px; }
    .cliente-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9em;
        position: relative;
    }
    .cliente-avatar.traspaso {
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
    }
    .traspaso-indicator {
        position: absolute;
        bottom: -4px;
        right: -4px;
        width: 18px;
        height: 18px;
        background: #7c3aed;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        border: 2px solid white;
        color: white;
    }
    .badge-traspaso {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.65em;
        font-weight: 600;
        margin-left: 6px;
        cursor: help;
    }
    .badge-traspaso i { font-size: 0.85em; }
    .cliente-details { display: flex; flex-direction: column; gap: 2px; }
    .cliente-nombre { font-weight: 700; color: var(--gray-800); font-size: 0.95em; display: flex; align-items: center; flex-wrap: wrap; }
    .pago-id { font-size: 0.75em; color: var(--info); font-weight: 600; }
    .pago-id i { font-size: 0.8em; }
    .membresia-nombre { font-size: 0.8em; color: var(--gray-500); }
    .membresia-nombre i { color: var(--accent); margin-right: 4px; }

    /* ===== FECHA INFO ===== */
    .fecha-info { display: flex; flex-direction: column; gap: 4px; }
    .fecha-principal { font-size: 0.9em; color: var(--gray-700); font-weight: 500; }
    .fecha-principal i { color: var(--info); margin-right: 4px; }
    .referencia { font-size: 0.75em; color: var(--gray-500); }
    .referencia i { margin-right: 4px; }

    /* ===== MONTOS INFO ===== */
    .montos-info { display: flex; flex-direction: column; gap: 3px; }
    .monto-total { font-weight: 700; color: var(--gray-800); font-size: 0.95em; }
    .monto-abonado { font-size: 0.8em; color: var(--success); font-weight: 600; }
    .monto-abonado i { margin-right: 3px; }
    .monto-pendiente { font-size: 0.75em; color: var(--accent); }
    .monto-pendiente i { margin-right: 3px; }
    .progress-bar-mini {
        height: 4px;
        background: var(--gray-200);
        border-radius: 2px;
        margin-top: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .progress-fill.complete { background: linear-gradient(90deg, var(--success) 0%, #38ef7d 100%); }
    .progress-fill.partial { background: linear-gradient(90deg, var(--warning) 0%, #f5af19 100%); }

    /* ===== BADGES ===== */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
        text-transform: uppercase;
    }
    .estado-badge.pagado {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
        border: 1px solid rgba(0, 191, 142, 0.3);
    }
    .estado-badge.parcial {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
        border: 1px solid rgba(240, 165, 0, 0.3);
    }
    .estado-badge.traspasado {
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(124, 58, 237, 0.3);
    }
    .estado-badge.vencido {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    .estado-badge.cancelado {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }
    .metodo-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
        border-radius: 8px;
        font-size: 0.8em;
        font-weight: 600;
    }

    /* ===== ACTION BUTTONS ===== */
    .action-buttons { display: flex; gap: 6px; justify-content: center; }
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85em;
        text-decoration: none;
    }
    .btn-action:hover { transform: translateY(-2px); }
    .btn-view { background: var(--primary); color: white; }
    .btn-view:hover { background: var(--primary-light); color: white; box-shadow: 0 4px 12px rgba(26, 26, 46, 0.3); }
    .btn-edit { background: var(--warning); color: white; }
    .btn-edit:hover { background: #d99400; color: white; box-shadow: 0 4px 12px rgba(240, 165, 0, 0.3); }
    .btn-delete { background: var(--accent); color: white; }
    .btn-delete:hover { background: #d63650; color: white; box-shadow: 0 4px 12px rgba(233, 69, 96, 0.3); }
    .delete-form { display: inline; }

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
    }
    .swal-estoicos .swal2-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(233, 69, 96, 0.5) !important;
    }
    .swal-estoicos .swal2-cancel {
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
    }
    .swal-estoicos .swal2-cancel:hover {
        background: #e2e8f0 !important;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
    }
    .empty-icon {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2em;
        color: var(--gray-400);
    }
    .empty-state h4 { color: var(--gray-700); margin-bottom: 8px; }
    .empty-state p { color: var(--gray-500); margin-bottom: 20px; }
    .btn-nuevo-pago-inline {
        background: var(--success);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-nuevo-pago-inline:hover { background: var(--success-dark); color: white; text-decoration: none; }

    /* ===== PAGINATION ===== */
    .pagination-section {
        padding: 16px 20px;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .pagination-info {
        font-size: 0.85em;
        color: var(--gray-600);
    }
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pagination-btn {
        width: 36px;
        height: 36px;
        border: 1px solid var(--gray-200);
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--gray-600);
    }
    .pagination-btn:hover:not(:disabled) {
        border-color: var(--accent);
        color: var(--accent);
    }
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .pagination-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    .pagination-pages {
        display: flex;
        gap: 4px;
    }
    .page-num {
        width: 36px;
        height: 36px;
        border: 1px solid var(--gray-200);
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.85em;
        font-weight: 600;
        color: var(--gray-600);
        transition: all 0.2s ease;
    }
    .page-num:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    .page-num.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    .pagination-per-page {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85em;
        color: var(--gray-600);
    }
    .pagination-per-page select {
        padding: 6px 10px;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        font-size: 0.9em;
        cursor: pointer;
    }
    .pagination-per-page select:focus {
        outline: none;
        border-color: var(--accent);
    }

    /* ===== LOADING ===== */
    .loading-more {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 20px;
        color: var(--gray-500);
    }
    .spinner {
        width: 24px;
        height: 24px;
        border: 3px solid var(--gray-200);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 992px) {
        .pagos-hero { flex-direction: column; gap: 16px; text-align: center; }
        .hero-content { flex-direction: column; }
        .filters-section { flex-direction: column; }
        .search-box { width: 100%; }
        .filter-buttons { justify-content: center; }
        .pagination-section { justify-content: center; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .pagos-container { padding: 12px; }
        .pagos-table thead { display: none !important; }
        .pagos-table tbody tr {
            display: block;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            padding: 12px;
        }
        .pagos-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-100);
        }
        .pagos-table tbody td:last-child { border-bottom: none; }
        .pagos-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--gray-600);
            font-size: 0.8em;
        }
        .action-buttons { justify-content: flex-end; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // =====================================================
    // VARIABLES Y DATOS
    // =====================================================
    let allPagos = @json($pagosData ?? []);
    let filteredPagos = [...allPagos];
    let currentPage = 1;
    let perPage = 50;
    let totalPages = 1;
    let totalPagos = {{ $totalPagos ?? 0 }};
    let isLoading = false;
    let hasMoreData = true;
    let currentLoadedPage = 1;
    
    // Estadísticas del servidor (valores reales totales)
    const serverStats = {
        totalPagos: {{ $totalPagos ?? 0 }},
        totalRecaudado: {{ $estadisticas['total_recaudado'] ?? 0 }},
        completados: {{ $estadisticas['completados'] ?? 0 }},
        parciales: {{ $estadisticas['parciales'] ?? 0 }}
    };
    
    // Flag para saber si hay filtros activos
    let hasActiveFilters = false;

    // =====================================================
    // FUNCIONES DE RENDERIZADO
    // =====================================================
    function renderTable() {
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = startIndex + perPage;
        const pagosToShow = filteredPagos.slice(startIndex, endIndex);
        
        const tbody = $('#pagosTableBody');
        tbody.empty();
        
        if (pagosToShow.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4>No hay pagos registrados</h4>
                        <p>Crea un nuevo pago para comenzar</p>
                        <a href="{{ route('admin.pagos.create') }}" class="btn-nuevo-pago-inline">
                            <i class="fas fa-plus"></i> Nuevo Pago
                        </a>
                    </td>
                </tr>
            `);
            return;
        }
        
        pagosToShow.forEach(pago => {
            tbody.append(renderPagoRow(pago));
        });
        
        updatePaginationInfo();
        updatePaginationControls();
        updateStats();
        
        // Check if we need to load more data
        checkAndLoadMore();
    }

    function renderPagoRow(pago) {
        const traspasoClass = pago.es_traspaso ? 'traspaso' : '';
        const traspasoIndicator = pago.es_traspaso ? `
            <span class="traspaso-indicator" title="Membresía traspasada">
                <i class="fas fa-exchange-alt"></i>
            </span>
        ` : '';
        const traspasoBadge = pago.es_traspaso ? `
            <span class="badge-traspaso" title="Pagado originalmente por ${pago.cliente_original_nombre}">
                <i class="fas fa-exchange-alt"></i> Traspaso
            </span>
        ` : '';
        
        const referenciaHtml = pago.referencia_pago ? `
            <span class="referencia">
                <i class="fas fa-file-invoice"></i> ${pago.referencia_pago}
            </span>
        ` : '';
        
        const montoPendienteHtml = pago.monto_pendiente > 0 ? `
            <span class="monto-pendiente">
                <i class="fas fa-clock"></i> ${pago.monto_pendiente_formatted}
            </span>
        ` : '';
        
        const progressClass = pago.porcentaje >= 100 ? 'complete' : 'partial';
        
        return `
            <tr class="pago-row" 
                data-estado="${pago.estado_pago}"
                data-cliente="${pago.cliente_nombre.toLowerCase()}"
                data-membresia="${pago.membresia_nombre.toLowerCase()}"
                data-referencia="${(pago.referencia_pago || '').toLowerCase()}">
                <td data-label="Cliente / Membresía">
                    <div class="cliente-pago-info">
                        <div class="cliente-avatar ${traspasoClass}">
                            ${pago.cliente_iniciales}
                            ${traspasoIndicator}
                        </div>
                        <div class="cliente-details">
                            <span class="cliente-nombre">
                                ${pago.cliente_nombre}
                                ${traspasoBadge}
                            </span>
                            <span class="pago-id">
                                <i class="fas fa-hashtag"></i> Pago #${pago.id}
                            </span>
                            <span class="membresia-nombre">
                                <i class="fas fa-dumbbell"></i> ${pago.membresia_nombre}
                            </span>
                        </div>
                    </div>
                </td>
                <td data-label="Fecha">
                    <div class="fecha-info">
                        <span class="fecha-principal">
                            <i class="fas fa-calendar-alt"></i> ${pago.fecha_pago}
                        </span>
                        ${referenciaHtml}
                    </div>
                </td>
                <td data-label="Montos">
                    <div class="montos-info">
                        <span class="monto-total">${pago.monto_total_formatted}</span>
                        <span class="monto-abonado">
                            <i class="fas fa-check"></i> ${pago.monto_abonado_formatted}
                        </span>
                        ${montoPendienteHtml}
                        <div class="progress-bar-mini">
                            <div class="progress-fill ${progressClass}" style="width: ${Math.min(pago.porcentaje, 100)}%"></div>
                        </div>
                    </div>
                </td>
                <td data-label="Estado">
                    <span class="estado-badge ${pago.estado_pago}">
                        <i class="fas ${pago.estado_icono}"></i> ${pago.estado_texto}
                    </span>
                </td>
                <td data-label="Método">
                    <span class="metodo-badge">
                        <i class="fas ${pago.metodo_icono}"></i> ${pago.metodo_nombre}
                    </span>
                </td>
                <td data-label="Acciones">
                    <div class="action-buttons">
                        <a href="${pago.show_url}" class="btn-action btn-view" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="${pago.edit_url}" class="btn-action btn-edit" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn-action btn-delete" 
                                onclick="confirmDelete('${pago.delete_url}', '${pago.cliente_nombre}', '${pago.monto_abonado_formatted}')" 
                                title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    // =====================================================
    // ESTADÍSTICAS
    // =====================================================
    function updateStats() {
        // Si no hay filtros activos y no se han cargado todos los datos,
        // mostrar estadísticas del servidor (valores totales reales)
        if (!hasActiveFilters) {
            $('#statTotalPagos').text(serverStats.totalPagos.toLocaleString('es-CL'));
            $('#statRecaudado').text('$' + serverStats.totalRecaudado.toLocaleString('es-CL'));
            $('#statCompletados').text(serverStats.completados.toLocaleString('es-CL'));
            $('#statParciales').text(serverStats.parciales.toLocaleString('es-CL'));
            return;
        }
        
        // Si hay filtros activos, calcular desde los datos filtrados
        let totalRecaudado = 0;
        let completados = 0;
        let parciales = 0;
        
        filteredPagos.forEach(pago => {
            totalRecaudado += pago.monto_abonado;
            if (pago.estado_pago === 'pagado') {
                completados++;
            } else if (pago.estado_pago === 'parcial') {
                parciales++;
            }
        });
        
        $('#statTotalPagos').text(filteredPagos.length.toLocaleString('es-CL'));
        $('#statRecaudado').text('$' + totalRecaudado.toLocaleString('es-CL'));
        $('#statCompletados').text(completados.toLocaleString('es-CL'));
        $('#statParciales').text(parciales.toLocaleString('es-CL'));
    }

    // =====================================================
    // PAGINACIÓN
    // =====================================================
    function updatePaginationInfo() {
        totalPages = Math.ceil(filteredPagos.length / perPage);
        const startItem = filteredPagos.length === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const endItem = Math.min(currentPage * perPage, filteredPagos.length);
        
        $('#paginationInfo').text(`Mostrando ${startItem} - ${endItem} de ${filteredPagos.length} pagos`);
        $('#resultCount').text(`${filteredPagos.length} de ${totalPagos}`);
    }

    function updatePaginationControls() {
        totalPages = Math.ceil(filteredPagos.length / perPage);
        
        $('#btnFirst, #btnPrev').prop('disabled', currentPage <= 1);
        $('#btnNext, #btnLast').prop('disabled', currentPage >= totalPages);
        
        // Render page numbers
        let pagesHtml = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            pagesHtml += `<span class="page-num ${activeClass}" onclick="goToPage(${i})">${i}</span>`;
        }
        
        $('#paginationPages').html(pagesHtml);
    }

    window.goToPage = function(page) {
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
        
        // Scroll to top of table
        $('.table-section')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window.changePerPage = function(value) {
        perPage = parseInt(value);
        currentPage = 1;
        renderTable();
    };

    // =====================================================
    // LAZY LOADING
    // =====================================================
    function checkAndLoadMore() {
        const totalLoadedFromServer = allPagos.length;
        const remainingPages = Math.ceil(filteredPagos.length / perPage) - currentPage;
        
        // Load more if we're getting close to the end and there's more data
        if (remainingPages <= 2 && hasMoreData && !isLoading && totalLoadedFromServer < totalPagos) {
            loadMorePagos();
        }
    }

    function loadMorePagos() {
        if (isLoading || !hasMoreData) return;
        
        isLoading = true;
        currentLoadedPage++;
        $('#loadingMore').show();
        
        $.ajax({
            url: '{{ route("admin.pagos.json") }}',
            method: 'GET',
            data: {
                page: currentLoadedPage,
                per_page: 50
            },
            success: function(response) {
                if (response.pagos && response.pagos.length > 0) {
                    // Add new pagos avoiding duplicates
                    const existingIds = new Set(allPagos.map(p => p.id));
                    const newPagos = response.pagos.filter(p => !existingIds.has(p.id));
                    
                    allPagos = [...allPagos, ...newPagos];
                    applyFilters();
                    
                    // Check if there's more data
                    hasMoreData = response.pagination.current_page < response.pagination.last_page;
                } else {
                    hasMoreData = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading more pagos:', error);
                hasMoreData = false;
            },
            complete: function() {
                isLoading = false;
                $('#loadingMore').hide();
            }
        });
    }

    // =====================================================
    // FILTROS Y BÚSQUEDA
    // =====================================================
    function applyFilters() {
        const searchText = $('#searchInput').val().toLowerCase();
        const activeFilter = $('.filter-btn.active').data('filter');
        
        // Detectar si hay filtros activos
        hasActiveFilters = searchText !== '' || activeFilter !== 'todos';
        
        filteredPagos = allPagos.filter(pago => {
            // Search filter
            const matchesSearch = !searchText || 
                pago.cliente_nombre.toLowerCase().includes(searchText) ||
                pago.membresia_nombre.toLowerCase().includes(searchText) ||
                (pago.referencia_pago || '').toLowerCase().includes(searchText);
            
            // Status filter
            const matchesFilter = activeFilter === 'todos' || pago.estado_pago === activeFilter;
            
            return matchesSearch && matchesFilter;
        });
        
        currentPage = 1;
        renderTable();
    }

    // Search input with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });

    // Filter buttons
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        applyFilters();
    });

    // =====================================================
    // CONFIRMACIÓN DE ELIMINACIÓN
    // =====================================================
    window.confirmDelete = function(deleteUrl, clienteNombre, monto) {
        Swal.fire({
            title: '¿Eliminar pago?',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #dc3545;"></i>
                    </div>
                    <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">${clienteNombre}</p>
                    <p style="color: #64748b; font-size: 0.9rem;">Monto: ${monto}</p>
                    <p style="color: #64748b; font-size: 0.9rem; margin-top: 0.5rem;">El pago se moverá a la papelera y podrás restaurarlo después.</p>
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
                // Show loading
                Swal.fire({
                    title: 'Eliminando...',
                    html: '<div style="padding: 2rem;"><div style="width: 50px; height: 50px; border: 4px solid #fee2e2; border-top-color: #dc3545; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div></div><style>@keyframes spin { to { transform: rotate(360deg); } }</style>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    customClass: { popup: 'swal-estoicos' }
                });
                
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    // =====================================================
    // INICIALIZACIÓN
    // =====================================================
    renderTable();
    
    // Session success message
    @if(session('success'))
    Swal.fire({
        title: '¡Operación exitosa!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="fas fa-check" style="font-size: 1.8rem; color: #22c55e;"></i>
                </div>
                <p style="color: #1e293b; font-size: 1rem;">{{ session('success') }}</p>
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

