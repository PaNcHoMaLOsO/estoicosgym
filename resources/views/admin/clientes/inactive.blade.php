@extends('adminlte::page')

@section('title', 'Clientes Inactivos')

@section('content_header')
@stop

@section('content')
<div class="clientes-inactivos-container">
    <!-- Hero Header -->
    <div class="clientes-hero inactive">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="hero-text">
                <h1>Clientes Inactivos</h1>
                <p>Gestiona los clientes desactivados del sistema</p>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.clientes.index') }}" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                <span>Volver a Activos</span>
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if ($message = Session::get('success'))
        <div class="alert-custom success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito!</strong>
                <p>{{ $message }}</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert-custom error">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>Error</strong>
                <p>{{ $message }}</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-inactivos">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $clientes->total() }}</span>
                <span class="stat-label">Total Inactivos</span>
            </div>
        </div>
        
        <div class="stat-card stat-porcentaje">
            <div class="stat-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ round((($clientes->total() / (\App\Models\Cliente::count() ?: 1)) * 100), 1) }}%</span>
                <span class="stat-label">Del Total</span>
            </div>
        </div>

        <div class="stat-card stat-activos-total">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ \App\Models\Cliente::where('activo', true)->count() }}</span>
                <span class="stat-label">Clientes Activos</span>
            </div>
        </div>

        <div class="stat-card stat-total-general">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ \App\Models\Cliente::count() }}</span>
                <span class="stat-label">Total General</span>
            </div>
        </div>
    </div>

    <!-- Search Box -->
    <div class="search-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por nombre, RUT, email o teléfono..." autocomplete="off">
        </div>
    </div>

    <!-- Clientes Grid -->
    @if($clientes->count() > 0)
        <div class="clientes-grid" id="clientesGrid">
            @foreach($clientes as $cliente)
                <div class="cliente-card inactive-card" data-search="{{ strtolower($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . ($cliente->apellido_materno ?? '') . ' ' . $cliente->run_pasaporte . ' ' . $cliente->email . ' ' . ($cliente->celular ?? '')) }}">
                    <div class="card-header-section">
                        <div class="cliente-avatar inactive">
                            {{ strtoupper(substr($cliente->nombres, 0, 1)) }}{{ strtoupper(substr($cliente->apellido_paterno, 0, 1)) }}
                        </div>
                        <div class="cliente-main-info">
                            <h3 class="cliente-nombre">
                                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                            </h3>
                            <span class="cliente-rut">{{ $cliente->run_pasaporte }}</span>
                        </div>
                        <div class="status-badge inactive">
                            <i class="fas fa-ban"></i>
                            Inactivo
                        </div>
                    </div>

                    <div class="card-body-section">
                        <div class="info-row">
                            <i class="fas fa-envelope"></i>
                            <span>{{ $cliente->email }}</span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <span>{{ $cliente->celular ?? 'Sin teléfono' }}</span>
                        </div>
                        <div class="info-row deactivated">
                            <i class="fas fa-calendar-times"></i>
                            <span>Desactivado: {{ $cliente->updated_at->format('d/m/Y') }}</span>
                            <small class="time-ago">{{ $cliente->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn-card-action view">
                            <i class="fas fa-eye"></i>
                            Ver Detalles
                        </a>
                        <button type="button" class="btn-card-action reactivate" 
                                onclick="confirmarReactivacion('{{ $cliente->uuid }}', '{{ addslashes($cliente->nombres) }} {{ addslashes($cliente->apellido_paterno) }}')">
                            <i class="fas fa-undo"></i>
                            Reactivar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($clientes->hasPages())
            <div class="pagination-wrapper">
                {{ $clientes->links('pagination::bootstrap-4') }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-smile-beam"></i>
            </div>
            <h3>¡Excelente!</h3>
            <p>No hay clientes inactivos en el sistema</p>
            <span class="empty-subtitle">Todos tus clientes están activos y al día</span>
            <a href="{{ route('admin.clientes.index') }}" class="btn-empty-action">
                <i class="fas fa-users"></i>
                Ver Clientes Activos
            </a>
        </div>
    @endif

    <!-- Form Reactivar (Hidden) -->
    <form id="formReactivar" method="POST" style="display:none;">
        @csrf
        @method('PATCH')
    </form>
</div>
@endsection

@section('css')
<style>
    :root {
        --primary: #667eea;
        --primary-dark: #5a67d8;
        --accent: #e94560;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --inactive: #6b7280;
        --inactive-dark: #4b5563;
        --dark: #1a1a2e;
        --light: #f8fafc;
        --border-color: #e2e8f0;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .content-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important;
    }

    .clientes-inactivos-container {
        padding: 0;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Hero Header */
    .clientes-hero.inactive {
        background: linear-gradient(135deg, #4b5563 0%, #374151 50%, #1f2937 100%);
        border-radius: 20px;
        padding: 30px 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 10px 40px rgba(75, 85, 99, 0.3);
        position: relative;
        overflow: hidden;
    }

    .clientes-hero.inactive::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        pointer-events: none;
    }

    .hero-content {
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 1;
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
        z-index: 1;
    }

    .btn-volver {
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

    .btn-volver:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.5);
        transform: translateY(-2px);
        color: #fff;
        text-decoration: none;
    }

    /* Alertas */
    .alert-custom {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        animation: slideIn 0.3s ease;
    }

    .alert-custom.success {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #10b981;
    }

    .alert-custom.error {
        background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        border: 1px solid #ef4444;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .alert-custom.success .alert-icon {
        background: #10b981;
        color: #fff;
    }

    .alert-custom.error .alert-icon {
        background: #ef4444;
        color: #fff;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content strong {
        display: block;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .alert-content p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
    }

    .alert-close {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 5px;
        transition: color 0.2s;
    }

    .alert-close:hover {
        color: #64748b;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
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
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-inactivos .stat-icon {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: #fff;
    }

    .stat-porcentaje .stat-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
    }

    .stat-activos-total .stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
    }

    .stat-total-general .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        color: #fff;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark);
        line-height: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }

    /* Search Section */
    .search-section {
        margin-bottom: 25px;
    }

    .search-box {
        background: #fff;
        border-radius: 14px;
        padding: 6px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--shadow);
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .search-box:focus-within {
        border-color: var(--inactive);
        box-shadow: 0 0 0 4px rgba(107, 114, 128, 0.1);
    }

    .search-box i {
        color: #94a3b8;
        font-size: 18px;
    }

    .search-box input {
        border: none;
        outline: none;
        flex: 1;
        font-size: 15px;
        padding: 14px 0;
        background: transparent;
    }

    .search-box input::placeholder {
        color: #94a3b8;
    }

    /* Clientes Grid */
    .clientes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .cliente-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .cliente-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .inactive-card {
        border-left: 4px solid var(--inactive);
    }

    .card-header-section {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border-color);
    }

    .cliente-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        color: #fff;
        flex-shrink: 0;
    }

    .cliente-avatar.inactive {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .cliente-main-info {
        flex: 1;
        min-width: 0;
    }

    .cliente-nombre {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark);
        margin: 0 0 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cliente-rut {
        font-size: 13px;
        color: var(--inactive);
        font-weight: 500;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

    .status-badge.inactive {
        background: rgba(107, 114, 128, 0.15);
        color: var(--inactive-dark);
    }

    .card-body-section {
        padding: 20px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #475569;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row i {
        width: 20px;
        color: var(--inactive);
        font-size: 14px;
    }

    .info-row span {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .info-row.deactivated {
        flex-wrap: wrap;
        background: rgba(107, 114, 128, 0.05);
        border-radius: 8px;
        padding: 10px;
        margin-top: 5px;
    }

    .info-row.deactivated i {
        color: var(--danger);
    }

    .time-ago {
        width: 100%;
        margin-left: 30px;
        font-size: 12px;
        color: #94a3b8;
    }

    .card-actions {
        display: flex;
        gap: 10px;
        padding: 15px 20px;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
    }

    .btn-card-action {
        flex: 1;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
    }

    .btn-card-action.view {
        background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        color: #fff;
    }

    .btn-card-action.view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: #fff;
        text-decoration: none;
    }

    .btn-card-action.reactivate {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
    }

    .btn-card-action.reactivate:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    /* Empty State */
    .empty-state {
        background: #fff;
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .empty-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }

    .empty-icon i {
        font-size: 45px;
        color: #fff;
    }

    .empty-state h3 {
        font-size: 24px;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 10px;
    }

    .empty-state p {
        font-size: 16px;
        color: #64748b;
        margin: 0 0 5px;
    }

    .empty-subtitle {
        font-size: 14px;
        color: #94a3b8;
        display: block;
        margin-bottom: 25px;
    }

    .btn-empty-action {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        color: #fff;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-empty-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: #fff;
        text-decoration: none;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    .pagination-wrapper .pagination {
        margin: 0;
    }

    .pagination-wrapper .page-link {
        border-radius: 10px !important;
        margin: 0 4px;
        border: none;
        color: var(--inactive);
        padding: 10px 16px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: #fff;
    }

    .pagination-wrapper .page-link:hover {
        background: #f1f5f9;
        color: var(--inactive-dark);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .clientes-hero.inactive {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .hero-content {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .clientes-grid {
            grid-template-columns: 1fr;
        }

        .card-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Búsqueda en tiempo real
    document.getElementById('searchInput')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.cliente-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const searchData = card.dataset.search;
            if (searchData.includes(searchTerm)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay resultados
        const grid = document.getElementById('clientesGrid');
        let noResults = document.getElementById('noResults');
        
        if (visibleCount === 0 && searchTerm.length > 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.id = 'noResults';
                noResults.className = 'empty-state';
                noResults.innerHTML = `
                    <div class="empty-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Sin resultados</h3>
                    <p>No se encontraron clientes con "${searchTerm}"</p>
                `;
                grid.parentNode.insertBefore(noResults, grid.nextSibling);
            }
            grid.style.display = 'none';
        } else {
            if (noResults) noResults.remove();
            grid.style.display = '';
        }
    });

    // Función para confirmar reactivación
    function confirmarReactivacion(clienteId, nombre) {
        if (typeof Swal === 'undefined') {
            if (confirm('¿Deseas reactivar a ' + nombre + '?')) {
                const actionUrl = "{{ url('admin/clientes') }}/" + clienteId + "/reactivar";
                document.getElementById('formReactivar').action = actionUrl;
                document.getElementById('formReactivar').submit();
            }
            return;
        }
        
        Swal.fire({
            title: '¿Reactivar Cliente?',
            html: `
                <div style="text-align: left;">
                    <p style="margin-bottom: 1rem;">Estás a punto de <strong>reactivar a ${nombre}</strong>.</p>
                    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; padding: 16px; border: 1px solid #10b981;">
                        <h6 style="color: #059669; font-weight: 700; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i> ¿Qué sucede al reactivar?
                        </h6>
                        <ul style="color: #475569; margin: 0; padding-left: 20px; font-size: 14px;">
                            <li style="margin-bottom: 6px;">El cliente volverá al estado <strong>ACTIVO</strong></li>
                            <li style="margin-bottom: 6px;">Aparecerá en el <strong>listado principal</strong></li>
                            <li style="margin-bottom: 6px;">Todo su <strong>historial se preserva</strong></li>
                            <li>Podrá crear <strong>nuevas inscripciones</strong></li>
                        </ul>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, Reactivar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                popup: 'swal-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const actionUrl = "{{ url('admin/clientes') }}/" + clienteId + "/reactivar";
                document.getElementById('formReactivar').action = actionUrl;
                
                Swal.fire({
                    title: 'Procesando...',
                    html: '<div style="display: flex; justify-content: center;"><div class="spinner-border text-success" role="status"></div></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        document.getElementById('formReactivar').submit();
                    }
                });
            }
        });
    }
</script>

<style>
    .swal-wide {
        max-width: 450px !important;
    }
</style>
@endpush
