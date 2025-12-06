@extends('adminlte::page')

@section('title', 'Notificaciones - EstóicosGym')

@section('css')
<style>
    .content-wrapper {
        background: #f4f6f9 !important;
    }

    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --purple: #667eea;
    }

    /* Header moderno */
    .notifications-header {
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notifications-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .notifications-header h1 i {
        color: var(--accent);
        margin-right: 12px;
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn-header {
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-header-primary {
        background: linear-gradient(135deg, var(--accent) 0%, #d93654 100%);
        color: white;
    }

    .btn-header-secondary {
        background: #f8f9fa;
        color: #495057;
        border: 2px solid #e9ecef;
    }

    .btn-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Stats Cards mejoradas */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        border-color: var(--accent);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .stat-icon.pending { background: rgba(240, 165, 0, 0.12); color: var(--warning); }
    .stat-icon.sent { background: rgba(0, 191, 142, 0.12); color: var(--success); }
    .stat-icon.failed { background: rgba(233, 69, 96, 0.12); color: var(--accent); }
    .stat-icon.total { background: rgba(67, 97, 238, 0.12); color: var(--info); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #7f8c8d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .stat-card .icon.pending { background: rgba(240, 165, 0, 0.15); color: var(--warning); }
    .stat-card .icon.sent { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .stat-card .icon.failed { background: rgba(233, 69, 96, 0.15); color: var(--accent); }
    .stat-card .icon.total { background: rgba(67, 97, 238, 0.15); color: var(--info); }

    .stat-card .info .number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--gray-800);
    }

    .stat-card .info .label {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    /* Quick Actions Grid */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .action-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transition: height 0.3s ease;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .action-card:hover::before {
        height: 100%;
        opacity: 0.05;
    }

    .action-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        transition: transform 0.3s ease;
    }

    .action-card:hover .action-icon {
        transform: scale(1.1);
    }

    .action-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #2c3e50;
        margin: 0;
    }

    .action-card.nueva .action-icon { background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); }
    .action-card.nueva::before { background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%); }

    .action-card.programar .action-icon { background: linear-gradient(135deg, #f0a500 0%, #e09400 100%); }
    .action-card.programar::before { background: linear-gradient(135deg, #f0a500 0%, #e09400 100%); }

    .action-card.plantillas .action-icon { background: linear-gradient(135deg, #4361ee 0%, #3451d4 100%); }
    .action-card.plantillas::before { background: linear-gradient(135deg, #4361ee 0%, #3451d4 100%); }

    .action-card.historial .action-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .action-card.historial::before { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

    /* CRON Info Banner */
    .cron-banner {
        background: white;
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        border-left: 5px solid #2196F3;
    }

    .cron-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #2196F3;
        flex-shrink: 0;
    }

    .cron-content {
        flex: 1;
    }

    .cron-title {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 4px 0;
    }

    .cron-description {
        font-size: 0.9rem;
        color: #7f8c8d;
        margin: 0;
        line-height: 1.5;
    }

    .cron-badge {
        background: #e8f5e9;
        color: #2e7d32;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Table Container */
    .table-wrapper {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 2px solid #f0f0f0;
    }

    .table-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .table-title i {
        color: var(--accent);
        margin-right: 10px;
    }

    /* Filters */
    .filters-section {
        padding: 20px 24px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .filter-item {
        flex: 1;
        min-width: 180px;
    }

    .filter-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        background: white;
    }

    .filter-input:focus,
    .filter-select:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
    }

    .filter-btn {
        padding: 10px 20px;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-btn:hover {
        background: #d93654;
        transform: translateY(-1px);
    }

    /* Table Styles */
    .data-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        background: #f8f9fa;
        padding: 16px 20px;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
    }

    .data-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        color: #495057;
    }

    .data-table tbody tr {
        transition: all 0.2s ease;
    }

    .data-table tbody tr:hover {
        background-color: #f8f9fb;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Cliente Info */
    .cliente-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cliente-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .cliente-avatar.avatar-menor {
        background: linear-gradient(135deg, #f0a500 0%, #e09400 100%);
    }

    .cliente-details {
        flex: 1;
    }

    .cliente-nombre {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .cliente-email {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .badge-sm {
        font-size: 0.65rem;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
    }

    /* Badges */
    .badge-tipo,
    .badge-estado {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-pendiente { 
        background: rgba(240, 165, 0, 0.12); 
        color: #b37a00;
        border: 1px solid rgba(240, 165, 0, 0.3);
    }
    
    .badge-enviada { 
        background: rgba(0, 191, 142, 0.12); 
        color: #008060;
        border: 1px solid rgba(0, 191, 142, 0.3);
    }
    
    .badge-fallida { 
        background: rgba(233, 69, 96, 0.12); 
        color: #c23655;
        border: 1px solid rgba(233, 69, 96, 0.3);
    }
    
    .badge-cancelada { 
        background: rgba(108, 117, 125, 0.12); 
        color: #495057;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .tipo-por-vencer { background: rgba(240, 165, 0, 0.1); color: var(--warning); }
    .tipo-vencida { background: rgba(233, 69, 96, 0.1); color: var(--accent); }
    .tipo-bienvenida { background: rgba(0, 191, 142, 0.1); color: var(--success); }
    .tipo-pago { background: rgba(67, 97, 238, 0.1); color: var(--info); }

    /* Action Buttons */
    .table-actions {
        display: flex;
        gap: 6px;
    }

    .btn-table-action {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    .btn-table-action.view {
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
    }

    .btn-table-action.resend {
        background: rgba(0, 191, 142, 0.1);
        color: var(--success);
    }

    .btn-table-action.cancel {
        background: rgba(233, 69, 96, 0.1);
        color: var(--accent);
    }

    .btn-table-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: #f8f9fa;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .empty-state p {
        color: #7f8c8d;
        font-size: 0.95rem;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: center;
    }

    .pagination {
        margin: 0;
        justify-content: center;
    }

    .page-link {
        border-radius: 8px;
        margin: 0 3px;
        border: 1px solid #dee2e6;
        color: var(--primary);
    }

    .page-link:hover {
        background-color: var(--gray-100);
        border-color: var(--primary);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-color: var(--primary);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }

        .header-actions {
            flex-direction: column;
            width: 100%;
        }

        .btn-header {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .notifications-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-item {
            min-width: 100%;
        }

        .data-table {
            font-size: 0.85rem;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 12px 14px;
        }

        .cliente-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .cron-banner {
            flex-direction: column;
            text-align: center;
        }

        .cron-badge {
            align-self: center;
        }
    }

    @media (max-width: 576px) {
        .quick-actions {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .action-card {
            padding: 16px;
        }

        .action-icon {
            width: 48px;
            height: 48px;
            font-size: 1.3rem;
        }
    }
</style>
@stop

@section('content_header')
@stop

@section('content')
    {{-- Header --}}
    <div class="notifications-header">
        <div>
            <h1><i class="fas fa-bell"></i> Notificaciones</h1>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.notificaciones.crear') }}" class="btn-header btn-header-primary">
                <i class="fas fa-plus-circle"></i>
                Nueva Notificación
            </a>
            <a href="{{ route('admin.notificaciones.plantillas') }}" class="btn-header btn-header-secondary">
                <i class="fas fa-cog"></i>
                Configurar
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-number">{{ $estadisticas['pendientes'] }}</div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon sent">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-number">{{ $estadisticas['enviadas_hoy'] }}</div>
            <div class="stat-label">Enviadas Hoy</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon failed">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="stat-number">{{ $estadisticas['fallidas'] }}</div>
            <div class="stat-label">Fallidas</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon total">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-number">{{ $estadisticas['enviadas_mes'] }}</div>
            <div class="stat-label">Este Mes</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="quick-actions">
        <a href="{{ route('admin.notificaciones.crear') }}" class="action-card nueva">
            <div class="action-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <p class="action-title">Nueva Notificación</p>
        </a>
        <a href="{{ route('admin.notificaciones.programar') }}" class="action-card programar">
            <div class="action-icon">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <p class="action-title">Programar Envío</p>
        </a>
        <a href="{{ route('admin.notificaciones.plantillas') }}" class="action-card plantillas">
            <div class="action-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <p class="action-title">Plantillas</p>
        </a>
        <a href="{{ route('admin.notificaciones.historial') }}" class="action-card historial">
            <div class="action-icon">
                <i class="fas fa-history"></i>
            </div>
            <p class="action-title">Historial</p>
        </a>
    </div>

    {{-- CRON Banner --}}
    <div class="cron-banner">
        <div class="cron-icon">
            <i class="fas fa-robot"></i>
        </div>
        <div class="cron-content">
            <h3 class="cron-title">Sistema Automático Activo</h3>
            <p class="cron-description">Las notificaciones se ejecutan vía CRON todos los días a las 08:00 AM. No requiere intervención manual.</p>
        </div>
        <div class="cron-badge">
            <i class="fas fa-check-circle"></i> Activo
        </div>
    </div>

    {{-- Historial de Ejecuciones Automáticas --}}
    @if(session('errores_detalle'))
    <div class="alert alert-warning alert-dismissible fade show" style="border-left: 4px solid #f0a500; border-radius: 10px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="fas fa-exclamation-triangle mr-2"></i> Algunas notificaciones fueron rechazadas</h5>
        <ul class="mb-0 pl-3">
            @foreach(session('errores_detalle') as $error)
                <li><small>{{ $error }}</small></li>
            @endforeach
        </ul>
        @if(count(session('errores_detalle')) >= 10)
            <small class="text-muted d-block mt-2"><em>* Solo se muestran los primeros 10 errores</em></small>
        @endif
    </div>
    @endif

    @if(isset($ultimaEjecucion))
    <div class="alert" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: none; border-left: 4px solid #2196F3; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1" style="color: #1565C0; font-weight: 600;">
                    <i class="fas fa-clock mr-2"></i> Última Ejecución Automática
                </h5>
                <p class="mb-0 text-muted">
                    <strong>{{ $ultimaEjecucion->fecha }}</strong> - 
                    Programadas: <span class="badge badge-info">{{ $ultimaEjecucion->programadas }}</span>
                    Enviadas: <span class="badge badge-success">{{ $ultimaEjecucion->enviadas }}</span>
                    @if($ultimaEjecucion->fallidas > 0)
                        Fallidas: <span class="badge badge-danger">{{ $ultimaEjecucion->fallidas }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.notificaciones.historial') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-history"></i> Ver Historial Completo
            </a>
        </div>
    </div>
    @endif

    {{-- Tabla de Notificaciones --}}
    <div class="table-wrapper">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-list"></i> Historial de Notificaciones</h2>
        </div>

        {{-- Filtros --}}
        <div class="filters-section">
            <form action="{{ route('admin.notificaciones.index') }}" method="GET" class="d-flex flex-wrap align-items-end gap-3">
                <div class="filter-item">
                    <label class="filter-label">Estado</label>
                    <select name="estado" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="600" {{ request('estado') == '600' ? 'selected' : '' }}>Pendientes</option>
                        <option value="601" {{ request('estado') == '601' ? 'selected' : '' }}>Enviadas</option>
                        <option value="602" {{ request('estado') == '602' ? 'selected' : '' }}>Fallidas</option>
                        <option value="603" {{ request('estado') == '603' ? 'selected' : '' }}>Canceladas</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label class="filter-label">Tipo</label>
                    <select name="tipo" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach ($tiposNotificacion as $tipo)
                            <option value="{{ $tipo->id }}" {{ request('tipo') == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item" style="flex: 1; min-width: 200px;">
                    <label class="filter-label">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o email..." class="filter-input">
                </div>
                <div>
                    <button type="submit" class="filter-btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            @if ($notificaciones->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Estado</th>
                            <th>Programada</th>
                            <th>Enviada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notificaciones as $notificacion)
                            <tr>
                                <td>
                                    <div class="cliente-info">
                                        <div class="cliente-avatar {{ $notificacion->cliente->es_menor_edad ? 'avatar-menor' : '' }}">
                                            {{ substr($notificacion->cliente->nombres ?? 'N', 0, 1) }}
                                        </div>
                                        <div class="cliente-details">
                                            <div class="cliente-nombre">
                                                {{ $notificacion->cliente->nombre_completo ?? 'N/A' }}
                                                @if($notificacion->cliente->es_menor_edad)
                                                    <span class="badge badge-warning badge-sm ml-1">
                                                        <i class="fas fa-child"></i> Menor
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="cliente-email">
                                                {{ $notificacion->email_destino }}
                                                @if($notificacion->cliente->es_menor_edad && $notificacion->email_destino === $notificacion->cliente->apoderado_email)
                                                    <small class="text-muted ml-1">(Apoderado)</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $tipoClase = match($notificacion->tipoNotificacion->codigo ?? '') {
                                            'membresia_por_vencer' => 'badge-tipo-por-vencer',
                                            'membresia_vencida' => 'badge-tipo-vencida',
                                            'bienvenida' => 'badge-tipo-bienvenida',
                                            'pago_pendiente' => 'badge-tipo-pago',
                                            default => 'badge-tipo'
                                        };
                                    @endphp
                                    <span class="badge-tipo {{ $tipoClase }}">
                                        {{ $notificacion->tipoNotificacion->nombre ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span title="{{ $notificacion->asunto }}">
                                        {{ Str::limit($notificacion->asunto, 40) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $estadoClase = match($notificacion->id_estado) {
                                            600 => 'badge-pendiente',
                                            601 => 'badge-enviada',
                                            602 => 'badge-fallida',
                                            603 => 'badge-cancelada',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="badge-estado {{ $estadoClase }}">
                                        {{ $notificacion->estado->nombre ?? 'N/A' }}
                                    </span>
                                    @if ($notificacion->intentos > 0)
                                        <small class="text-muted d-block">
                                            Intentos: {{ $notificacion->intentos }}/{{ $notificacion->max_intentos }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $notificacion->fecha_programada->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if ($notificacion->fecha_envio)
                                        {{ $notificacion->fecha_envio->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.notificaciones.show', $notificacion) }}" 
                                           class="btn-table-action view" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if ($notificacion->id_estado == 602)
                                            <form action="{{ route('admin.notificaciones.reenviar', $notificacion) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-table-action resend" title="Reenviar">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if ($notificacion->id_estado == 600)
                                            <form action="{{ route('admin.notificaciones.cancelar', $notificacion) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-table-action cancel" title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="pagination-wrapper">
                    {{ $notificaciones->withQueryString()->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>No hay notificaciones</h4>
                    <p>Las notificaciones aparecerán aquí cuando se programen automáticamente.</p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirmación para ejecutar
    document.querySelectorAll('form[action*="ejecutar"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Ejecutar notificaciones?',
                text: 'Se programarán y enviarán las notificaciones pendientes',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ejecutar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    // Confirmación para cancelar
    document.querySelectorAll('form[action*="cancelar"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cancelar notificación?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
</script>
@stop
