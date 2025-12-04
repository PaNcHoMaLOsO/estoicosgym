@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
@stop

@section('content')
<div class="clientes-container">
    <!-- Hero Header -->
    <div class="clientes-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="hero-text">
                <h1>Gestión de Clientes</h1>
                <p>Administra todos los clientes del gimnasio</p>
            </div>
        </div>
        <div class="hero-actions">
            @if(isset($totalEliminados) && $totalEliminados > 0)
            <a href="{{ route('admin.clientes.trashed') }}" class="btn-ver-papelera" title="Ver clientes eliminados">
                <i class="fas fa-trash-alt"></i>
                <span>Papelera ({{ $totalEliminados }})</span>
            </a>
            @endif
            <a href="{{ route('admin.clientes.inactive') }}" class="btn-ver-inactivos">
                <i class="fas fa-user-slash"></i>
                <span>Ver Inactivos</span>
            </a>
            <a href="{{ route('admin.clientes.create') }}" class="btn-nuevo-cliente">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Cliente</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalClientes }}</span>
                <span class="stat-label">Total Clientes</span>
            </div>
        </div>
        
        <div class="stat-card stat-activos">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $clientesActivos }}</span>
                <span class="stat-label">Activos</span>
            </div>
        </div>
        
        <div class="stat-card stat-vencidos">
            <div class="stat-icon">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $clientesVencidos }}</span>
                <span class="stat-label">Vencidos</span>
            </div>
        </div>
        
        <div class="stat-card stat-pausados">
            <div class="stat-icon">
                <i class="fas fa-user-minus"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $clientesPausados }}</span>
                <span class="stat-label">Pausados</span>
            </div>
        </div>
        
        <div class="stat-card stat-sin-membresia">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $clientesSinMembresia }}</span>
                <span class="stat-label">Sin Membresía</span>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por nombre, RUT, email o teléfono..." autocomplete="off">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="todos">
                <i class="fas fa-users"></i> Todos
            </button>
            <button class="filter-btn" data-filter="activos">
                <i class="fas fa-user-check"></i> Activos
            </button>
            <button class="filter-btn" data-filter="vencidos">
                <i class="fas fa-user-clock"></i> Vencidos
            </button>
            <button class="filter-btn" data-filter="pausados">
                <i class="fas fa-user-minus"></i> Pausados
            </button>
            <button class="filter-btn" data-filter="sin-membresia">
                <i class="fas fa-user-slash"></i> Sin Membresía
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-responsive">
            <table class="clientes-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Membresía</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientesTableBody">
                    <!-- Los datos se cargan dinámicamente con JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Indicador de carga -->
        <div id="loadingIndicator" class="loading-indicator" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i> Cargando más clientes...
        </div>

        <!-- Paginación JavaScript -->
        <div class="pagination-section" id="paginationSection">
            <div class="pagination-info">
                Mostrando <span id="showingFrom">0</span> - <span id="showingTo">0</span> de <span id="totalFiltered">0</span> clientes
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" id="btnFirst" title="Primera página">
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button class="pagination-btn" id="btnPrev" title="Anterior">
                    <i class="fas fa-angle-left"></i>
                </button>
                <span class="pagination-pages" id="paginationPages"></span>
                <button class="pagination-btn" id="btnNext" title="Siguiente">
                    <i class="fas fa-angle-right"></i>
                </button>
                <button class="pagination-btn" id="btnLast" title="Última página">
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
            <div class="pagination-per-page">
                <label>Por página:</label>
                <select id="perPageSelect">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
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

    .clientes-container {
        padding: 20px;
        width: 100%;
    }

    /* Hero Header */
    .clientes-hero {
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
        justify-content: center;
        width: 48px;
        height: 48px;
        background: rgba(239, 68, 68, 0.2);
        color: #fff;
        border: 2px solid rgba(239, 68, 68, 0.4);
        border-radius: 12px;
        font-size: 18px;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .btn-ver-papelera:hover {
        background: rgba(239, 68, 68, 0.4);
        border-color: rgba(239, 68, 68, 0.6);
        transform: translateY(-2px);
        color: #fff;
        text-decoration: none;
    }

    .btn-ver-inactivos {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 24px;
        background: rgba(255,255,255,0.15);
        color: #fff;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .btn-ver-inactivos:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.5);
        transform: translateY(-2px);
        color: #fff;
        text-decoration: none;
    }

    .btn-ver-inactivos i {
        font-size: 16px;
        opacity: 0.9;
    }

    .btn-nuevo-cliente {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(233,69,96,0.3);
    }

    .btn-nuevo-cliente:hover {
        background: #d63655;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(233,69,96,0.4);
        color: #fff;
        text-decoration: none;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 18px;
        color: #fff;
    }

    .stat-total .stat-icon {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .stat-activos .stat-icon {
        background: linear-gradient(135deg, var(--success) 0%, #00a67e 100%);
    }

    .stat-vencidos .stat-icon {
        background: linear-gradient(135deg, var(--danger) 0%, #c82333 100%);
    }

    .stat-pausados .stat-icon {
        background: linear-gradient(135deg, var(--warning) 0%, #d69200 100%);
    }

    .stat-sin-membresia .stat-icon {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
        line-height: 1.2;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Filters Section */
    .filters-section {
        background: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 46px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,26,46,0.1);
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
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .filter-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }

    /* Table Section */
    .table-section {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .clientes-table {
        width: 100%;
        border-collapse: collapse;
    }

    .clientes-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .clientes-table thead th {
        padding: 16px 20px;
        text-align: left;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .clientes-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .clientes-table tbody tr:hover {
        background: rgba(26,26,46,0.02);
    }

    .clientes-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
    }

    /* Cliente Info */
    .cliente-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cliente-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
    }

    .cliente-avatar.avatar-menor {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
    }

    .cliente-details {
        display: flex;
        flex-direction: column;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge-menor-tabla {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid #f59e0b;
    }

    .badge-menor-tabla i {
        font-size: 9px;
    }

    .cliente-rut {
        font-size: 13px;
        color: var(--text-secondary);
    }

    /* Contacto Info */
    .contacto-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .contacto-item {
        font-size: 13px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .contacto-item i {
        color: var(--primary);
        width: 14px;
    }

    /* Membresía Badge */
    .membresia-badge {
        display: inline-block;
        padding: 6px 14px;
        background: linear-gradient(135deg, var(--info) 0%, #3a53d6 100%);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
    }

    /* Estado Badge */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .estado-activo {
        background: rgba(0,191,142,0.15);
        color: var(--success);
    }

    .estado-vencido {
        background: rgba(220,53,69,0.15);
        color: var(--danger);
    }

    .estado-pausado {
        background: rgba(240,165,0,0.15);
        color: var(--warning);
    }

    .estado-cancelado {
        background: rgba(108,117,125,0.15);
        color: var(--text-secondary);
    }

    .estado-suspendido {
        background: rgba(102,16,242,0.15);
        color: #6610f2;
    }

    .estado-sin-membresia {
        background: rgba(108,117,125,0.1);
        color: var(--text-secondary);
    }

    /* Vencimiento */
    .vencimiento-text {
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .vencimiento-text.text-danger {
        color: var(--danger) !important;
        font-weight: 600;
    }

    /* Action Buttons */
    .acciones-btns {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-view {
        background: rgba(67,97,238,0.12);
        color: var(--info);
    }

    .btn-view:hover {
        background: var(--info);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-edit {
        background: rgba(0,191,142,0.12);
        color: var(--success);
    }

    .btn-edit:hover {
        background: var(--success);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: rgba(233,69,96,0.12);
        color: var(--accent);
    }

    .btn-delete:hover {
        background: var(--accent);
        color: #fff;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
    }

    .empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .empty-content i {
        font-size: 64px;
        color: var(--border-color);
        margin-bottom: 20px;
    }

    .empty-content h3 {
        color: var(--text-primary);
        font-size: 20px;
        margin-bottom: 8px;
    }

    .empty-content p {
        color: var(--text-secondary);
        margin-bottom: 20px;
    }

    .btn-nuevo-cliente-inline {
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

    .btn-nuevo-cliente-inline:hover {
        background: #d63655;
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
    }

    /* Pagination - Nueva versión */
    .pagination-section {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination-info {
        color: var(--text-secondary);
        font-size: 0.9rem;
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
        background: #f8f9fa;
        color: var(--text-primary);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--primary);
        color: #fff;
    }

    .pagination-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pagination-pages {
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0 10px;
    }

    .pagination-num {
        min-width: 36px;
        height: 36px;
        border: none;
        background: #f8f9fa;
        color: var(--text-primary);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .pagination-num:hover {
        background: var(--primary-light);
        color: #fff;
    }

    .pagination-num.active {
        background: var(--primary);
        color: #fff;
    }

    .pagination-ellipsis {
        padding: 0 8px;
        color: var(--text-secondary);
    }

    .pagination-per-page {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pagination-per-page label {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0;
    }

    .pagination-per-page select {
        padding: 6px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        color: var(--text-primary);
        cursor: pointer;
    }

    /* Loading indicator */
    .loading-indicator {
        text-align: center;
        padding: 20px;
        color: var(--text-secondary);
    }

    .loading-indicator i {
        margin-right: 8px;
        color: var(--primary);
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        .stat-card {
            padding: 12px;
            gap: 10px;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
        }
        .stat-icon i {
            font-size: 16px;
        }
        .stat-number {
            font-size: 18px;
        }
        .stat-label {
            font-size: 11px;
        }
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .clientes-hero {
            flex-direction: column;
            text-align: center;
            gap: 20px;
            padding: 24px;
        }

        .hero-content {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }

        .filter-buttons {
            justify-content: center;
        }

        .clientes-table thead {
            display: none;
        }

        .clientes-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 16px;
        }

        .clientes-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .clientes-table tbody td:last-child {
            border-bottom: none;
        }

        .clientes-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
        }
    }
</style>
@stop

@section('js')
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
    .swal-estoicos.swal-success .swal2-confirm {
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%) !important;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4) !important;
    }
    .swal-estoicos.swal-primary .swal2-confirm {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        box-shadow: 0 4px 15px rgba(26, 26, 46, 0.4) !important;
    }
</style>
<script>
$(document).ready(function() {
    // ============================================
    // SISTEMA DE LAZY LOADING CON PAGINACIÓN LOCAL
    // ============================================
    
    // Datos globales
    let allClientes = @json($clientesData);  // Primeros 100 cargados con la página
    let hasMoreData = {{ $totalClientes > 100 ? 'true' : 'false' }};
    let nextOffset = 100;
    let isLoading = false;
    
    // Estado de la paginación
    let currentPage = 1;
    let perPage = 20;
    let currentFilter = 'todos';
    let currentSearch = '';
    
    // CSRF token para peticiones AJAX
    const csrfToken = '{{ csrf_token() }}';
    
    /**
     * Obtener clientes filtrados
     */
    function getFilteredClientes() {
        return allClientes.filter(cliente => {
            // Filtro por estado
            let matchesFilter = true;
            if (currentFilter !== 'todos') {
                if (currentFilter === 'activos') matchesFilter = cliente.estadoClass === 'activo';
                else if (currentFilter === 'vencidos') matchesFilter = cliente.estadoClass === 'vencido';
                else if (currentFilter === 'pausados') matchesFilter = cliente.estadoClass === 'pausado';
                else if (currentFilter === 'sin-membresia') matchesFilter = cliente.estadoClass === 'sin-membresia';
            }
            
            // Filtro por búsqueda
            let matchesSearch = true;
            if (currentSearch) {
                const searchLower = currentSearch.toLowerCase();
                const nombre = ((cliente.nombres || '') + ' ' + (cliente.apellido_paterno || '')).toLowerCase();
                const rut = (cliente.run_pasaporte || '').toLowerCase();
                const email = (cliente.email || '').toLowerCase();
                const telefono = (cliente.celular || '').toLowerCase();
                
                matchesSearch = nombre.includes(searchLower) || 
                               rut.includes(searchLower) || 
                               email.includes(searchLower) || 
                               telefono.includes(searchLower);
            }
            
            return matchesFilter && matchesSearch;
        });
    }
    
    /**
     * Renderizar una fila de cliente
     */
    function renderClienteRow(cliente) {
        const initials = ((cliente.nombres || '?')[0] + (cliente.apellido_paterno || '?')[0]).toUpperCase();
        const nombreCompleto = (cliente.nombres || '') + ' ' + (cliente.apellido_paterno || '');
        const menorBadge = cliente.es_menor_edad ? '<span class="badge-menor-tabla"><i class="fas fa-child"></i> Menor</span>' : '';
        
        return `
            <tr class="cliente-row" data-id="${cliente.id}">
                <td>
                    <div class="cliente-info">
                        <div class="cliente-avatar ${cliente.es_menor_edad ? 'avatar-menor' : ''}">${initials}</div>
                        <div class="cliente-details">
                            <span class="cliente-nombre">${nombreCompleto} ${menorBadge}</span>
                            <span class="cliente-rut">${cliente.run_pasaporte || 'Sin RUT'}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="contacto-info">
                        ${cliente.email ? `<span class="contacto-item"><i class="fas fa-envelope"></i> ${cliente.email}</span>` : ''}
                        ${cliente.celular ? `<span class="contacto-item"><i class="fas fa-phone"></i> ${cliente.celular}</span>` : ''}
                    </div>
                </td>
                <td><span class="membresia-badge">${cliente.membresiaTexto}</span></td>
                <td><span class="estado-badge estado-${cliente.estadoClass}">${cliente.estadoTexto}</span></td>
                <td><span class="vencimiento-text ${cliente.estadoClass === 'vencido' ? 'text-danger' : ''}">${cliente.vencimientoTexto}</span></td>
                <td>
                    <div class="acciones-btns">
                        <a href="${cliente.showUrl}" class="btn-action btn-view" title="Ver detalles"><i class="fas fa-eye"></i></a>
                        <a href="${cliente.editUrl}" class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="${cliente.deleteUrl}" method="POST" class="d-inline form-delete">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    }
    
    /**
     * Renderizar tabla con paginación
     */
    function renderTable() {
        const filtered = getFilteredClientes();
        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / perPage);
        
        // Ajustar página actual si es necesario
        if (currentPage > totalPages) currentPage = totalPages || 1;
        
        // Calcular índices
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = Math.min(startIndex + perPage, totalFiltered);
        const pageClientes = filtered.slice(startIndex, endIndex);
        
        // Renderizar filas
        const tbody = $('#clientesTableBody');
        if (pageClientes.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-content">
                            <i class="fas fa-search"></i>
                            <h3>No se encontraron clientes</h3>
                            <p>Intenta con otros filtros o términos de búsqueda</p>
                        </div>
                    </td>
                </tr>
            `);
        } else {
            tbody.html(pageClientes.map(renderClienteRow).join(''));
        }
        
        // Actualizar información de paginación
        $('#showingFrom').text(totalFiltered > 0 ? startIndex + 1 : 0);
        $('#showingTo').text(endIndex);
        $('#totalFiltered').text(totalFiltered);
        
        // Renderizar números de página
        renderPaginationNumbers(totalPages);
        
        // Habilitar/deshabilitar botones
        $('#btnFirst, #btnPrev').prop('disabled', currentPage <= 1);
        $('#btnNext, #btnLast').prop('disabled', currentPage >= totalPages);
        
        // Verificar si necesitamos cargar más datos
        checkAndLoadMore();
        
        // Re-vincular eventos de eliminación
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
     * Verificar y cargar más datos si es necesario (lazy loading)
     */
    function checkAndLoadMore() {
        const filtered = getFilteredClientes();
        const totalPages = Math.ceil(filtered.length / perPage);
        
        // Si estamos en la página 5+ del bloque actual y hay más datos
        if (hasMoreData && !isLoading && currentPage >= 5 && (currentPage * perPage) > (allClientes.length - 50)) {
            loadMoreClientes();
        }
    }
    
    /**
     * Cargar más clientes via AJAX
     */
    function loadMoreClientes() {
        if (isLoading || !hasMoreData) return;
        
        isLoading = true;
        $('#loadingIndicator').show();
        
        $.ajax({
            url: '{{ route("admin.clientes.index") }}',
            method: 'GET',
            data: { offset: nextOffset },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.clientes && response.clientes.length > 0) {
                    allClientes = allClientes.concat(response.clientes);
                    hasMoreData = response.hasMore;
                    nextOffset = response.nextOffset;
                    
                    console.log(`Cargados ${response.clientes.length} clientes más. Total: ${allClientes.length}`);
                    
                    // Re-renderizar para actualizar conteos
                    renderTable();
                } else {
                    hasMoreData = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando más clientes:', error);
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
                title: '¿Eliminar cliente?',
                html: `
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <i class="fas fa-user-times" style="font-size: 2rem; color: #dc3545;"></i>
                        </div>
                        <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">${clienteNombre}</p>
                        <p style="color: #64748b; font-size: 0.9rem;">Esta acción eliminará también sus inscripciones y pagos.</p>
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
        const filtered = getFilteredClientes();
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
    
    // Renderizar tabla inicial
    renderTable();

    // Success/Error messages from session
    @if(session('success'))
    Swal.fire({
        title: '¡Operación exitosa!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 2rem; color: #00bf8e;"></i>
                </div>
                <p style="color: #64748b;">{{ session('success') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Continuar',
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal-estoicos swal-success',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: '¡Ocurrió un error!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #dc3545;"></i>
                </div>
                <p style="color: #64748b;">{{ session('error') }}</p>
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
    @endif
});
</script>
@stop
