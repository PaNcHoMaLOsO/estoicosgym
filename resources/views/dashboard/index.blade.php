@extends('adminlte::page')

@section('title', 'Dashboard - EstóicosGym')

@section('content_header')
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
        --danger: #dc3545;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
        --shadow: 0 5px 20px rgba(0,0,0,0.06);
        --shadow-hover: 0 12px 30px rgba(0,0,0,0.1);
    }

    .content-wrapper {
        background: var(--gray-100) !important;
    }

    .dashboard-container {
        padding: 20px;
        max-width: 100%;
    }

    /* ===== HERO HEADER ===== */
    .dashboard-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        padding: 28px 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .hero-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .hero-icon {
        width: 65px;
        height: 65px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-icon i {
        font-size: 28px;
        color: var(--accent);
    }

    .hero-text h1 {
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        margin: 0;
    }

    .hero-text p {
        color: rgba(255,255,255,0.8);
        margin: 4px 0 0;
        font-size: 14px;
    }

    .hero-date {
        background: rgba(255,255,255,0.1);
        padding: 10px 18px;
        border-radius: 10px;
        color: #fff;
        font-weight: 500;
        font-size: 14px;
    }

    .hero-date i {
        color: var(--accent);
        margin-right: 8px;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
        border-left: 5px solid var(--info);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }
    .stat-card.primary { border-left-color: var(--primary); }
    .stat-card.info { border-left-color: var(--info); }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 22px;
        color: #fff;
    }

    .stat-card.success .stat-icon { background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%); }
    .stat-card.info .stat-icon { background: linear-gradient(135deg, var(--info) 0%, #3451d1 100%); }
    .stat-card.warning .stat-icon { background: linear-gradient(135deg, var(--warning) 0%, #d69200 100%); }
    .stat-card.primary .stat-icon { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); }
    .stat-card.danger .stat-icon { background: linear-gradient(135deg, var(--accent) 0%, #d63655 100%); }

    .stat-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .stat-number {
        font-size: 26px;
        font-weight: 800;
        color: var(--gray-800);
        line-height: 1.2;
    }

    .stat-label {
        font-size: 12px;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-sublabel {
        font-size: 11px;
        color: var(--gray-600);
        margin-top: 2px;
    }

    /* ===== METRICS GRID ===== */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .metric-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
    }

    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .metric-header {
        background: var(--gray-100);
        padding: 12px 16px;
        border-bottom: 1px solid var(--gray-200);
    }

    .metric-header h6 {
        margin: 0;
        font-size: 0.82rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .metric-header.info h6 { color: var(--info); }
    .metric-header.success h6 { color: var(--success); }
    .metric-header.warning h6 { color: var(--warning); }
    .metric-header.danger h6 { color: var(--accent); }

    .metric-body {
        padding: 18px;
        text-align: center;
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
    }

    .metric-value.info { color: var(--info); }
    .metric-value.success { color: var(--success); }
    .metric-value.warning { color: var(--warning); }
    .metric-value.danger { color: var(--accent); }

    .metric-desc {
        color: var(--gray-600);
        font-size: 0.78rem;
        margin-top: 4px;
    }

    /* ===== ALERT GRID ===== */
    .alert-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .alert-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow);
        border-left: 5px solid var(--accent);
        overflow: hidden;
    }

    .alert-card.success { border-left-color: var(--success); }
    .alert-card.warning { border-left-color: var(--warning); }
    .alert-card.info { border-left-color: var(--info); }

    .alert-content {
        padding: 20px;
    }

    .alert-title {
        font-size: 0.82rem;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-title i { color: var(--accent); }
    .alert-card.success .alert-title i { color: var(--success); }
    .alert-card.warning .alert-title i { color: var(--warning); }
    .alert-card.info .alert-title i { color: var(--info); }

    .alert-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--accent);
        line-height: 1.2;
    }

    .alert-card.success .alert-value { color: var(--success); }
    .alert-card.warning .alert-value { color: var(--warning); }
    .alert-card.info .alert-value { color: var(--info); }

    .alert-sub {
        color: var(--gray-600);
        font-size: 0.85rem;
        margin-top: 4px;
    }

    .alert-extra {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--gray-200);
    }

    .alert-extra-label {
        font-size: 0.75rem;
        color: var(--gray-600);
    }

    .alert-extra-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--accent);
    }

    /* ===== COMPARISON GRID ===== */
    .comparison-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
    }

    .comparison-item {
        text-align: center;
        padding: 12px;
        border-radius: 10px;
    }

    .comparison-item.positive { background: rgba(0, 191, 142, 0.1); }
    .comparison-item.negative { background: rgba(233, 69, 96, 0.1); }

    .comparison-label {
        font-size: 0.78rem;
        color: var(--gray-600);
        margin-bottom: 4px;
    }

    .comparison-value {
        font-size: 1.3rem;
        font-weight: 800;
    }

    .comparison-item.positive .comparison-value { color: var(--success); }
    .comparison-item.negative .comparison-value { color: var(--accent); }

    /* ===== LIST ITEMS ===== */
    .list-item {
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-item:last-child { border-bottom: none; }

    .list-item-name {
        font-weight: 600;
        color: var(--gray-800);
    }

    .list-item-value {
        font-weight: 700;
        font-size: 1rem;
        color: var(--success);
    }

    /* ===== PANEL CARDS ===== */
    .panel-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 25px;
        border: 1px solid var(--gray-200);
    }

    .panel-header {
        background: var(--primary);
        color: #fff;
        padding: 15px 20px;
    }

    .panel-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-header h5 i { color: var(--accent); }

    .panel-header-light {
        background: var(--gray-100);
        padding: 14px 20px;
        border-bottom: 1px solid var(--gray-200);
    }

    .panel-header-light h5 {
        margin: 0;
        font-weight: 600;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-header-light.success h5 { color: var(--success); }
    .panel-header-light.warning h5 { color: var(--warning); }
    .panel-header-light.danger h5 { color: var(--accent); }
    .panel-header-light.info h5 { color: var(--info); }
    .panel-header-light.primary h5 { color: var(--primary); }

    .panel-body { padding: 0; }

    /* ===== TABLE STYLES ===== */
    .dashboard-table {
        width: 100%;
        margin: 0;
    }

    .dashboard-table thead {
        background: var(--gray-100);
    }

    .dashboard-table thead th {
        padding: 13px 16px;
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--gray-200);
    }

    .dashboard-table tbody tr {
        transition: background 0.2s ease;
        border-bottom: 1px solid var(--gray-200);
    }

    .dashboard-table tbody tr:hover { background: var(--gray-100); }
    .dashboard-table tbody tr:last-child { border-bottom: none; }

    .dashboard-table tbody td {
        padding: 13px 16px;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .client-link {
        color: var(--info);
        text-decoration: none;
        font-weight: 600;
    }

    .client-link:hover {
        color: var(--primary);
        text-decoration: underline;
    }

    /* ===== PRIORITY BADGES ===== */
    .priority-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .priority-critical {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
        border: 2px solid var(--accent);
    }

    .priority-high {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
        border: 2px solid var(--warning);
    }

    .priority-normal {
        background: rgba(67, 97, 238, 0.15);
        color: var(--info);
        border: 2px solid var(--info);
    }

    /* ===== STATUS GRID ===== */
    .status-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 20px;
    }

    .status-item {
        text-align: center;
        padding: 16px;
        border-radius: 12px;
    }

    .status-item.success { background: rgba(0, 191, 142, 0.1); }
    .status-item.warning { background: rgba(240, 165, 0, 0.1); }
    .status-item.danger { background: rgba(233, 69, 96, 0.1); }
    .status-item.gray { background: var(--gray-100); }

    .status-value {
        font-size: 1.8rem;
        font-weight: 800;
    }

    .status-item.success .status-value { color: var(--success); }
    .status-item.warning .status-value { color: var(--warning); }
    .status-item.danger .status-value { color: var(--accent); }
    .status-item.gray .status-value { color: var(--gray-600); }

    .status-label {
        font-size: 0.78rem;
        color: var(--gray-600);
        margin-top: 4px;
    }

    /* ===== PROGRESS BARS ===== */
    .progress-item {
        padding: 15px 20px;
        border-bottom: 1px solid var(--gray-200);
    }

    .progress-item:last-child { border-bottom: none; }

    .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .progress-name {
        font-weight: 600;
        color: var(--gray-800);
    }

    .progress-value {
        font-weight: 700;
    }

    .progress-value.info { color: var(--info); }
    .progress-value.success { color: var(--success); }

    .progress-bar-container {
        height: 8px;
        background: var(--gray-200);
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .progress-bar-fill.info { background: linear-gradient(90deg, var(--info), #6d83f2); }
    .progress-bar-fill.gray { background: var(--gray-600); }

    .progress-percent {
        font-size: 0.72rem;
        color: var(--gray-600);
        margin-top: 5px;
    }

    /* ===== CHART CONTAINER ===== */
    .chart-container {
        padding: 20px;
        height: 280px;
        position: relative;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        padding: 35px;
        text-align: center;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 2.2rem;
        margin-bottom: 10px;
        display: block;
        color: var(--success);
    }

    .empty-state strong {
        font-size: 1rem;
        display: block;
        margin-bottom: 5px;
    }

    /* ===== ROW GRID ===== */
    .row-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .row-grid-half {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1400px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .metrics-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 992px) {
        .row-grid { grid-template-columns: 1fr; }
        .row-grid-half { grid-template-columns: 1fr; }
        .alert-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .dashboard-hero {
            flex-direction: column;
            text-align: center;
            gap: 15px;
            padding: 20px;
        }

        .hero-content { flex-direction: column; }

        .stats-grid,
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .comparison-grid,
        .status-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop

@section('content')
<div class="dashboard-container">
    
    <!-- ===== HERO HEADER ===== -->
    <div class="dashboard-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="hero-text">
                <h1>Dashboard</h1>
                <p>Panel de control y métricas del gimnasio</p>
            </div>
        </div>
        <div class="hero-date">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </div>
    </div>

    <!-- ===== STATS PRINCIPALES ===== -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $inscripcionesActivas }}</span>
                <span class="stat-label">Miembros Activos</span>
                <span class="stat-sublabel">Hoy</span>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">${{ number_format($ingresosMes, 0, ',', '.') }}</span>
                <span class="stat-label">Ingresos del Mes</span>
                <span class="stat-sublabel">Recaudado</span>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalInscripcionesEsteMes }}</span>
                <span class="stat-label">Nuevos Clientes</span>
                <span class="stat-sublabel">Este mes</span>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number">{{ $totalClientes }}</span>
                <span class="stat-label">Total Registrados</span>
                <span class="stat-sublabel">Base de datos</span>
            </div>
        </div>
    </div>

    <!-- ===== MÉTRICAS OPERACIONALES ===== -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-header info">
                <h6><i class="fas fa-calculator"></i> Ticket Promedio</h6>
            </div>
            <div class="metric-body">
                <div class="metric-value info">${{ number_format($ticketPromedio, 0, ',', '.') }}</div>
                <div class="metric-desc">monto promedio por pago</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header success">
                <h6><i class="fas fa-percent"></i> Tasa de Cobranza</h6>
            </div>
            <div class="metric-body">
                <div class="metric-value success">{{ number_format($tasaCobranza, 1) }}%</div>
                <div class="metric-desc">pagos completados</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header warning">
                <h6><i class="fas fa-chart-pie"></i> Conversión Mes</h6>
            </div>
            <div class="metric-body">
                <div class="metric-value warning">{{ number_format($tasaConversion, 1) }}%</div>
                <div class="metric-desc">nuevos / activos</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header info">
                <h6><i class="fas fa-user-check"></i> Ingreso por Miembro</h6>
            </div>
            <div class="metric-body">
                <div class="metric-value info">${{ number_format($inscripcionesActivas > 0 ? $ingresosMes / $inscripcionesActivas : 0, 0, ',', '.') }}</div>
                <div class="metric-desc">ingresos / miembro</div>
            </div>
        </div>
    </div>

    <!-- ===== ALERTAS Y CRÍTICOS ===== -->
    <div class="alert-grid">
        <!-- Pagos Vencidos -->
        <div class="alert-card">
            <div class="alert-content">
                <div class="alert-title">
                    <i class="fas fa-exclamation-circle"></i> Pagos Vencidos
                </div>
                <div class="alert-value">{{ $pagosVencidos }}</div>
                <div class="alert-sub">pagos sin cobrar</div>
                <div class="alert-extra">
                    <div class="alert-extra-label">Monto en riesgo:</div>
                    <div class="alert-extra-value">${{ number_format($montoPagosVencidos, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Vencen Esta Semana -->
        <div class="alert-card warning">
            <div class="alert-content">
                <div class="alert-title">
                    <i class="fas fa-hourglass-half"></i> Membresías Vencen en 7 días
                </div>
                <div class="alert-value">{{ $porVencer7Dias }}</div>
                <div class="alert-sub">clientes requieren renovación</div>
            </div>
        </div>

        <!-- Top Membresías por Ingresos -->
        <div class="alert-card success">
            <div class="alert-content">
                <div class="alert-title">
                    <i class="fas fa-money-bill-wave"></i> Top Membresías por Ingresos
                </div>
                @if($membresiasIngresos->count() > 0)
                    @foreach($membresiasIngresos->take(3) as $item)
                        <div class="list-item">
                            <span class="list-item-name">{{ $item->nombre }}</span>
                            <span class="list-item-value">${{ number_format($item->totalIngresos, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="alert-sub">Sin datos disponibles</div>
                @endif
            </div>
        </div>

        <!-- Comparativa vs Mes Anterior -->
        <div class="alert-card info">
            <div class="alert-content">
                <div class="alert-title">
                    <i class="fas fa-chart-bar"></i> Comparativa vs Mes Anterior
                </div>
                <div class="comparison-grid">
                    <div class="comparison-item {{ $variacionIngresos >= 0 ? 'positive' : 'negative' }}">
                        <div class="comparison-label">Ingresos</div>
                        <div class="comparison-value">
                            @if($variacionIngresos >= 0) ↑ @else ↓ @endif {{ abs(number_format($variacionIngresos, 1)) }}%
                        </div>
                    </div>
                    <div class="comparison-item {{ $variacionClientes >= 0 ? 'positive' : 'negative' }}">
                        <div class="comparison-label">Clientes Nuevos</div>
                        <div class="comparison-value">
                            @if($variacionClientes >= 0) ↑ @else ↓ @endif {{ abs(number_format($variacionClientes, 1)) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== CLIENTES POR CONTACTAR ===== -->
    <div class="panel-card">
        <div class="panel-header">
            <h5><i class="fas fa-phone"></i> Clientes para Contactar Esta Semana</h5>
        </div>
        <div class="panel-body">
            @if($clientesPorVencer->count() > 0)
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Membresía</th>
                            <th>Teléfono</th>
                            <th>Vencimiento</th>
                            <th style="text-align: center;">Prioridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientesPorVencer->take(10) as $inscripcion)
                            @php 
                                $dias = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                $telefono = $inscripcion->cliente->celular ?? $inscripcion->cliente->telefono ?? 'N/A';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="client-link">
                                        {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                    </a>
                                </td>
                                <td>{{ $inscripcion->membresia->nombre ?? '-' }}</td>
                                <td>{{ $telefono }}</td>
                                <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td style="text-align: center;">
                                    @if($dias <= 2)
                                        <span class="priority-badge priority-critical">CRÍTICO ({{ $dias }}d)</span>
                                    @elseif($dias <= 5)
                                        <span class="priority-badge priority-high">ALTO ({{ $dias }}d)</span>
                                    @else
                                        <span class="priority-badge priority-normal">Normal ({{ $dias }}d)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <strong>¡Excelente!</strong>
                    <small>No hay clientes con vencimientos próximos</small>
                </div>
            @endif
        </div>
    </div>

    <!-- ===== GRÁFICOS Y ESTADO ===== -->
    <div class="row-grid">
        <!-- Gráfico Ingresos -->
        <div class="panel-card">
            <div class="panel-header-light success">
                <h5><i class="fas fa-chart-line"></i> Ingresos - Últimos 6 Meses</h5>
            </div>
            <div class="chart-container">
                <canvas id="chartIngresos"></canvas>
            </div>
        </div>

        <!-- Estado Inscripciones -->
        <div class="panel-card">
            <div class="panel-header-light info">
                <h5><i class="fas fa-chart-pie"></i> Estado de Inscripciones</h5>
            </div>
            <div class="status-grid">
                <div class="status-item success">
                    <div class="status-value">{{ $inscripcionesActivas }}</div>
                    <div class="status-label">Activas</div>
                </div>
                <div class="status-item warning">
                    <div class="status-value">{{ $inscripcionesPausadas }}</div>
                    <div class="status-label">Pausadas</div>
                </div>
                <div class="status-item danger">
                    <div class="status-value">{{ $inscripcionesVencidas }}</div>
                    <div class="status-label">Vencidas</div>
                </div>
                <div class="status-item gray">
                    <div class="status-value">{{ $inscripcionesCanceladas + $inscripcionesSuspendidas }}</div>
                    <div class="status-label">Canceladas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TOP MEMBRESÍAS Y MÉTODOS DE PAGO ===== -->
    <div class="row-grid-half">
        <div class="panel-card">
            <div class="panel-header-light info">
                <h5><i class="fas fa-star"></i> Membresías Más Vendidas</h5>
            </div>
            <div class="panel-body">
                @if($topMembresias->count() > 0)
                    @php $total = $topMembresias->sum('total'); @endphp
                    @foreach($topMembresias as $item)
                        @php $pct = $total > 0 ? ($item->total / $total) * 100 : 0; @endphp
                        <div class="progress-item">
                            <div class="progress-header">
                                <span class="progress-name">{{ $item->membresia->nombre ?? 'Sin nombre' }}</span>
                                <span class="progress-value info">{{ $item->total }} inscritos</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill info" style="width: {{ $pct }}%;"></div>
                            </div>
                            <div class="progress-percent">{{ number_format($pct, 1) }}% del total</div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <small>Sin datos disponibles</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header-light primary">
                <h5><i class="fas fa-credit-card"></i> Métodos de Pago Usados</h5>
            </div>
            <div class="panel-body">
                @if(count($etiquetasMetodosPago) > 0)
                    @php $totalMetodos = array_sum($datosMetodosPago); @endphp
                    @foreach(array_combine($etiquetasMetodosPago, $datosMetodosPago) as $metodo => $count)
                        @php $pct = $totalMetodos > 0 ? ($count / $totalMetodos) * 100 : 0; @endphp
                        <div class="progress-item">
                            <div class="progress-header">
                                <span class="progress-name">{{ $metodo ?? 'Desconocido' }}</span>
                                <span class="progress-value" style="color: var(--gray-600);">{{ $count }}</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill gray" style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <small>Sin datos disponibles</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ===== ÚLTIMOS PAGOS ===== -->
    <div class="panel-card">
        <div class="panel-header">
            <h5><i class="fas fa-history"></i> Últimos Pagos Registrados</h5>
        </div>
        <div class="panel-body">
            @if($ultimosPagos->count() > 0)
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Membresía</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th style="text-align: right;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosPagos->take(10) as $pago)
                            @if($pago->inscripcion && $pago->inscripcion->cliente)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}" class="client-link">
                                        {{ Str::limit($pago->inscripcion->cliente->nombres . ' ' . $pago->inscripcion->cliente->apellido_paterno, 25) }}
                                    </a>
                                </td>
                                <td>{{ $pago->inscripcion->membresia->nombre ?? '-' }}</td>
                                <td style="font-weight: 700; color: var(--success);">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</td>
                                <td>{{ $pago->metodoPago->nombre ?? '-' }}</td>
                                <td style="text-align: right;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <small>Sin pagos registrados</small>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const etiquetasIngresos = @json($etiquetasIngresos ?? []);
        const datosIngresos = @json($datosIngresosBarras ?? []);

        const canvas = document.getElementById('chartIngresos');
        if (canvas && etiquetasIngresos.length > 0) {
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: etiquetasIngresos,
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: datosIngresos,
                        borderColor: '#00bf8e',
                        backgroundColor: 'rgba(0, 191, 142, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#00bf8e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'top', 
                            labels: { 
                                usePointStyle: true, 
                                padding: 15,
                                font: { weight: '600' }
                            } 
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                callback: function(value) { 
                                    return '$' + value.toLocaleString('es-CL'); 
                                },
                                font: { weight: '500' }
                            },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: '500' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
