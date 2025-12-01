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
                    @forelse($clientes as $cliente)
                    @php
                        // Buscar inscripción activa primero, si no, la más reciente que no esté:
                        // 103=Cancelada, 105=Cambiada, 106=Traspasada
                        $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first()
                            ?? $cliente->inscripciones->whereNotIn('id_estado', [103, 105, 106])->first();
                        $estadoClass = 'sin-membresia';
                        $estadoTexto = 'Sin membresía';
                        $membresiaTexto = '-';
                        $vencimientoTexto = '-';
                        
                        if ($inscripcionActiva) {
                            $membresiaTexto = $inscripcionActiva->membresia->nombre ?? '-';
                            $vencimientoTexto = $inscripcionActiva->fecha_vencimiento 
                                ? \Carbon\Carbon::parse($inscripcionActiva->fecha_vencimiento)->format('d/m/Y') 
                                : '-';
                            
                            switch($inscripcionActiva->id_estado) {
                                case 100: // Activa
                                    $estadoClass = 'activo';
                                    $estadoTexto = 'Activo';
                                    break;
                                case 101: // Pausada
                                    $estadoClass = 'pausado';
                                    $estadoTexto = 'Pausado';
                                    break;
                                case 102: // Vencida
                                    $estadoClass = 'vencido';
                                    $estadoTexto = 'Vencido';
                                    break;
                                case 103: // Cancelada
                                    $estadoClass = 'cancelado';
                                    $estadoTexto = 'Cancelado';
                                    break;
                                case 104: // Suspendida
                                    $estadoClass = 'suspendido';
                                    $estadoTexto = 'Suspendido';
                                    break;
                                case 105: // Cambiada
                                    $estadoClass = 'cambiado';
                                    $estadoTexto = 'Cambiado';
                                    break;
                                default:
                                    $estadoClass = 'sin-membresia';
                                    $estadoTexto = 'Sin membresía';
                            }
                        }
                    @endphp
                    <tr class="cliente-row" 
                        data-estado="{{ $estadoClass }}"
                        data-nombre="{{ strtolower($cliente->nombres . ' ' . $cliente->apellido_paterno) }}"
                        data-rut="{{ strtolower($cliente->run_pasaporte ?? '') }}"
                        data-email="{{ strtolower($cliente->email ?? '') }}"
                        data-telefono="{{ $cliente->celular ?? '' }}">
                        <td>
                            <div class="cliente-info">
                                <div class="cliente-avatar">
                                    {{ strtoupper(substr($cliente->nombres, 0, 1) . substr($cliente->apellido_paterno, 0, 1)) }}
                                </div>
                                <div class="cliente-details">
                                    <span class="cliente-nombre">{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</span>
                                    <span class="cliente-rut">{{ $cliente->run_pasaporte ?? 'Sin RUT' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contacto-info">
                                @if($cliente->email)
                                <span class="contacto-item">
                                    <i class="fas fa-envelope"></i> {{ $cliente->email }}
                                </span>
                                @endif
                                @if($cliente->celular)
                                <span class="contacto-item">
                                    <i class="fas fa-phone"></i> {{ $cliente->celular }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="membresia-badge">{{ $membresiaTexto }}</span>
                        </td>
                        <td>
                            <span class="estado-badge estado-{{ $estadoClass }}">
                                {{ $estadoTexto }}
                            </span>
                        </td>
                        <td>
                            <span class="vencimiento-text {{ $estadoClass === 'vencido' ? 'text-danger' : '' }}">
                                {{ $vencimientoTexto }}
                            </span>
                        </td>
                        <td>
                            <div class="acciones-btns">
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn-action btn-view" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn-action btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" class="d-inline form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="empty-content">
                                <i class="fas fa-users-slash"></i>
                                <h3>No hay clientes registrados</h3>
                                <p>Comienza agregando tu primer cliente</p>
                                <a href="{{ route('admin.clientes.create') }}" class="btn-nuevo-cliente-inline">
                                    <i class="fas fa-user-plus"></i> Agregar Cliente
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($clientes->hasPages())
        <div class="pagination-section">
            {{ $clientes->links() }}
        </div>
        @endif
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
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        font-size: 24px;
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

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
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

    .cliente-details {
        display: flex;
        flex-direction: column;
    }

    .cliente-nombre {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 15px;
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

    /* Pagination */
    .pagination-section {
        padding: 20px;
        display: flex;
        justify-content: center;
        border-top: 1px solid var(--border-color);
    }

    .pagination-section .pagination {
        margin: 0;
    }

    .pagination-section .page-link {
        border: none;
        color: var(--text-primary);
        padding: 10px 16px;
        margin: 0 4px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .pagination-section .page-link:hover {
        background: var(--primary);
        color: #fff;
    }

    .pagination-section .page-item.active .page-link {
        background: var(--primary);
        color: #fff;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
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
            grid-template-columns: 1fr;
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
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchValue = $(this).val().toLowerCase();
        
        $('.cliente-row').each(function() {
            const nombre = $(this).data('nombre');
            const rut = $(this).data('rut');
            const email = $(this).data('email');
            const telefono = $(this).data('telefono');
            
            const matchesSearch = nombre.includes(searchValue) || 
                                  rut.includes(searchValue) || 
                                  email.includes(searchValue) || 
                                  telefono.includes(searchValue);
            
            // Check current filter
            const activeFilter = $('.filter-btn.active').data('filter');
            const estado = $(this).data('estado');
            
            let matchesFilter = true;
            if (activeFilter !== 'todos') {
                if (activeFilter === 'activos') matchesFilter = estado === 'activo';
                else if (activeFilter === 'vencidos') matchesFilter = estado === 'vencido';
                else if (activeFilter === 'pausados') matchesFilter = estado === 'pausado';
                else if (activeFilter === 'sin-membresia') matchesFilter = estado === 'sin-membresia';
            }
            
            $(this).toggle(matchesSearch && matchesFilter);
        });
    });

    // Filter functionality
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        const filter = $(this).data('filter');
        const searchValue = $('#searchInput').val().toLowerCase();
        
        $('.cliente-row').each(function() {
            const estado = $(this).data('estado');
            const nombre = $(this).data('nombre');
            const rut = $(this).data('rut');
            const email = $(this).data('email');
            const telefono = $(this).data('telefono');
            
            let matchesFilter = true;
            if (filter !== 'todos') {
                if (filter === 'activos') matchesFilter = estado === 'activo';
                else if (filter === 'vencidos') matchesFilter = estado === 'vencido';
                else if (filter === 'pausados') matchesFilter = estado === 'pausado';
                else if (filter === 'sin-membresia') matchesFilter = estado === 'sin-membresia';
            }
            
            const matchesSearch = !searchValue || 
                                  nombre.includes(searchValue) || 
                                  rut.includes(searchValue) || 
                                  email.includes(searchValue) || 
                                  telefono.includes(searchValue);
            
            $(this).toggle(matchesSearch && matchesFilter);
        });
    });

    // Delete confirmation
    $('.form-delete').on('submit', function(e) {
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
            customClass: {
                popup: 'swal-estoicos',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
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
