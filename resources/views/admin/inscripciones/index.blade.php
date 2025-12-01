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
                <i class="fas fa-clock"></i>
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
                <i class="fas fa-clock"></i> Vencidas
            </button>
            <button class="filter-btn" data-filter="pausada">
                <i class="fas fa-pause-circle"></i> Pausadas
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
                    @forelse($inscripciones as $inscripcion)
                    @php
                        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                        $estadoClass = strtolower($inscripcion->estado?->nombre ?? 'pendiente');
                        $estadoPago = $inscripcion->obtenerEstadoPago();
                    @endphp
                    <tr class="inscripcion-row" 
                        data-estado="{{ $estadoClass }}"
                        data-cliente="{{ strtolower(($inscripcion->cliente?->nombres ?? '') . ' ' . ($inscripcion->cliente?->apellido_paterno ?? '')) }}"
                        data-rut="{{ strtolower($inscripcion->cliente?->rut ?? '') }}"
                        data-membresia="{{ strtolower($inscripcion->membresia?->nombre ?? '') }}">
                        <td>
                            <div class="cliente-membresia-info">
                                <div class="cliente-avatar">
                                    {{ strtoupper(substr($inscripcion->cliente?->nombres ?? 'N', 0, 1) . substr($inscripcion->cliente?->apellido_paterno ?? 'A', 0, 1)) }}
                                </div>
                                <div class="cliente-details">
                                    <span class="cliente-nombre">
                                        {{ $inscripcion->cliente?->nombres ?? 'Sin cliente' }} 
                                        {{ $inscripcion->cliente?->apellido_paterno ?? '' }}
                                    </span>
                                    <span class="cliente-rut">
                                        <i class="fas fa-id-card"></i> {{ $inscripcion->cliente?->rut ?? 'Sin RUT' }}
                                    </span>
                                    <span class="membresia-nombre">
                                        <i class="fas fa-dumbbell"></i> {{ $inscripcion->membresia?->nombre ?? 'Sin membresía' }}
                                    </span>
                                    @if($inscripcion->convenio)
                                    <span class="convenio-badge">
                                        <i class="fas fa-handshake"></i> {{ $inscripcion->convenio->nombre }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="periodo-info">
                                <div class="fechas">
                                    <span class="fecha-item">
                                        <i class="fas fa-calendar-plus"></i>
                                        {{ $inscripcion->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}
                                    </span>
                                    <span class="fecha-item">
                                        <i class="fas fa-calendar-times"></i>
                                        {{ $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="dias-restantes {{ $diasRestantes > 30 ? 'dias-ok' : ($diasRestantes > 7 ? 'dias-warning' : ($diasRestantes > 0 ? 'dias-danger' : 'dias-vencido')) }}">
                                    @if($diasRestantes > 0)
                                        <i class="fas fa-hourglass-half"></i> {{ $diasRestantes }} días
                                    @elseif($diasRestantes == 0)
                                        <i class="fas fa-exclamation-triangle"></i> Vence hoy
                                    @else
                                        <i class="fas fa-times-circle"></i> Vencida hace {{ abs($diasRestantes) }}d
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="precio-info">
                                @if($inscripcion->descuento_aplicado > 0)
                                <span class="precio-base-tachado">
                                    ${{ number_format($inscripcion->precio_base ?? 0, 0, ',', '.') }}
                                </span>
                                <span class="descuento-tag">
                                    <i class="fas fa-tag"></i> -${{ number_format($inscripcion->descuento_aplicado, 0, ',', '.') }}
                                </span>
                                @endif
                                <span class="precio-final">
                                    ${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="estado-pago-info">
                                @if($estadoPago['estado'] === 'pagado')
                                    <span class="pago-badge pago-completo">
                                        <i class="fas fa-check-circle"></i> Pagado
                                    </span>
                                @elseif($estadoPago['estado'] === 'parcial')
                                    <span class="pago-badge pago-parcial">
                                        <i class="fas fa-hourglass-half"></i> Parcial
                                    </span>
                                    <span class="pago-detalle">
                                        ${{ number_format($estadoPago['total_abonado'], 0, ',', '.') }} / ${{ number_format($estadoPago['total_abonado'] + $estadoPago['pendiente'], 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="pago-badge pago-pendiente">
                                        <i class="fas fa-exclamation-circle"></i> Pendiente
                                    </span>
                                @endif
                                <div class="pago-progress">
                                    <div class="pago-progress-bar {{ $estadoPago['porcentaje_pagado'] >= 100 ? 'completo' : ($estadoPago['porcentaje_pagado'] >= 50 ? 'parcial' : 'pendiente') }}" 
                                         style="width: {{ min($estadoPago['porcentaje_pagado'], 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="estado-badge estado-{{ $estadoClass }}">
                                @if($estadoClass === 'activa')
                                    <i class="fas fa-check-circle"></i>
                                @elseif($estadoClass === 'pausada')
                                    <i class="fas fa-pause-circle"></i>
                                @elseif($estadoClass === 'vencida')
                                    <i class="fas fa-clock"></i>
                                @else
                                    <i class="fas fa-info-circle"></i>
                                @endif
                                {{ $inscripcion->estado?->nombre ?? 'Sin estado' }}
                            </span>
                            @if($inscripcion->estaPausada())
                            <span class="pausa-info">
                                <i class="fas fa-calendar-day"></i> {{ $inscripcion->dias_pausa }}d
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="acciones-btns">
                                <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" 
                                   class="btn-action btn-view" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" 
                                   class="btn-action btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($inscripcion->estaPausada())
                                <button type="button" 
                                        class="btn-action btn-resume btn-reanudar" 
                                        data-id="{{ $inscripcion->id }}"
                                        data-cliente="{{ $inscripcion->cliente?->nombres }} {{ $inscripcion->cliente?->apellido_paterno }}"
                                        title="Reanudar Inscripción">
                                    <i class="fas fa-play"></i>
                                </button>
                                @elseif($estadoClass === 'activa')
                                <button type="button" 
                                        class="btn-action btn-pause btn-pausar" 
                                        data-id="{{ $inscripcion->id }}"
                                        data-cliente="{{ $inscripcion->cliente?->nombres }} {{ $inscripcion->cliente?->apellido_paterno }}"
                                        data-pausas-usadas="{{ $inscripcion->pausas_realizadas ?? 0 }}"
                                        data-pausas-max="{{ $inscripcion->max_pausas_permitidas ?? 2 }}"
                                        title="Pausar Inscripción">
                                    <i class="fas fa-pause"></i>
                                </button>
                                @endif
                                <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" 
                                   class="btn-action btn-pay" title="Registrar Pago">
                                    <i class="fas fa-dollar-sign"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="empty-content">
                                <i class="fas fa-id-card"></i>
                                <h3>No hay inscripciones registradas</h3>
                                <p>Comienza creando una nueva inscripción</p>
                                <a href="{{ route('admin.inscripciones.create') }}" class="btn-nueva-inscripcion-inline">
                                    <i class="fas fa-plus-circle"></i> Nueva Inscripción
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($inscripciones->hasPages())
        <div class="pagination-section">
            {{ $inscripciones->links() }}
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
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }

    .stat-card .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card .stat-icon i {
        font-size: 24px;
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

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 4px;
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

    .inscripciones-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inscripciones-table thead {
        background: var(--primary);
    }

    .inscripciones-table thead th {
        color: #fff;
        font-weight: 600;
        padding: 16px 20px;
        text-align: left;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .inscripciones-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .inscripciones-table tbody tr:hover {
        background: rgba(233, 69, 96, 0.03);
    }

    .inscripciones-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
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

    .pausa-info {
        display: block;
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    /* Acciones */
    .acciones-btns {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-action i {
        font-size: 14px;
    }

    .btn-view {
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
    }

    .btn-view:hover {
        background: var(--info);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-edit {
        background: rgba(240, 165, 0, 0.1);
        color: var(--warning);
    }

    .btn-edit:hover {
        background: var(--warning);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-pay {
        background: rgba(0, 191, 142, 0.1);
        color: var(--success);
    }

    .btn-pay:hover {
        background: var(--success);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-pause {
        background: rgba(240, 165, 0, 0.1);
        color: var(--warning);
    }

    .btn-pause:hover {
        background: var(--warning);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-resume {
        background: rgba(0, 191, 142, 0.1);
        color: var(--success);
    }

    .btn-resume:hover {
        background: var(--success);
        color: #fff;
        transform: translateY(-2px);
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

    /* SweetAlert Estoicos */
    .swal-estoicos {
        border-radius: 20px !important;
        padding: 0 !important;
    }
    .swal-estoicos .swal2-popup {
        border-radius: 20px;
    }
    .swal-estoicos .swal2-title {
        color: var(--primary);
        font-weight: 700;
    }
    .swal-estoicos .swal2-confirm {
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b8a 100%) !important;
        border-radius: 10px !important;
        padding: 12px 32px !important;
        font-weight: 600 !important;
        border: none !important;
    }
    .swal-estoicos .swal2-cancel {
        background: #6c757d !important;
        border-radius: 10px !important;
        padding: 12px 32px !important;
        font-weight: 600 !important;
        border: none !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // CSRF Token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Filtro en tiempo real
    function filterTable() {
        var searchText = $('#searchInput').val().toLowerCase();
        var activeFilter = $('.filter-btn.active').data('filter');
        var visibleCount = 0;
        var totalCount = $('#inscripcionesTableBody tr.inscripcion-row').length;

        $('#inscripcionesTableBody tr.inscripcion-row').each(function() {
            var row = $(this);
            var cliente = row.data('cliente') || '';
            var rut = row.data('rut') || '';
            var membresia = row.data('membresia') || '';
            var estado = row.data('estado') || '';
            
            var matchesSearch = cliente.includes(searchText) || rut.includes(searchText) || membresia.includes(searchText);
            var matchesFilter = activeFilter === 'todos' || estado === activeFilter;

            if (matchesSearch && matchesFilter) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
            }
        });
    }

    $('#searchInput').on('keyup', filterTable);
    
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterTable();
    });

    // ======== PAUSAR INSCRIPCIÓN ========
    $(document).on('click', '.btn-pausar', function() {
        const id = $(this).data('id');
        const cliente = $(this).data('cliente');
        const pausasUsadas = parseInt($(this).data('pausas-usadas')) || 0;
        const pausasMax = parseInt($(this).data('pausas-max')) || 2;
        const pausasDisponibles = pausasMax - pausasUsadas;

        Swal.fire({
            title: '<i class="fas fa-pause-circle" style="color: #f0a500;"></i> Pausar Membresía',
            html: `
                <div style="text-align: left; padding: 1rem 0;">
                    <p style="color: #64748b; margin-bottom: 1rem;">
                        <strong>Cliente:</strong> ${cliente}
                    </p>
                    
                    <!-- Contador de pausas -->
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%); border-radius: 12px; margin-bottom: 1.5rem;">
                        <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #4361ee 0%, #6366f1 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; font-weight: 800;">${pausasDisponibles}</div>
                        <div>
                            <div style="font-weight: 600; color: #1e293b;">Pausas Disponibles</div>
                            <small style="color: #64748b;">de ${pausasMax} permitidas</small>
                        </div>
                    </div>
                    
                    <h6 style="font-weight: 700; color: #1e293b; margin-bottom: 1rem;"><i class="fas fa-clock" style="margin-right: 8px; color: #f0a500;"></i>Selecciona duración</h6>
                    
                    <!-- Opciones de días -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 1rem;">
                        <div class="pause-option-swal" data-dias="7" style="padding: 16px 10px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-hourglass-start" style="font-size: 1.5rem; color: #f0a500; display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1.3rem; font-weight: 800; color: #1e293b;">7</div>
                            <div style="font-size: 0.75rem; color: #64748b;">días</div>
                        </div>
                        <div class="pause-option-swal" data-dias="14" style="padding: 16px 10px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-hourglass-half" style="font-size: 1.5rem; color: #f0a500; display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1.3rem; font-weight: 800; color: #1e293b;">14</div>
                            <div style="font-size: 0.75rem; color: #64748b;">días</div>
                        </div>
                        <div class="pause-option-swal" data-dias="30" style="padding: 16px 10px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-hourglass-end" style="font-size: 1.5rem; color: #f0a500; display: block; margin-bottom: 8px;"></i>
                            <div style="font-size: 1.3rem; font-weight: 800; color: #1e293b;">30</div>
                            <div style="font-size: 0.75rem; color: #64748b;">días</div>
                        </div>
                    </div>
                    
                    <!-- Opción indefinida -->
                    <div class="pause-option-swal pause-indefinida-option" data-dias="indefinida" style="padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #e94560 0%, #ff6b8a 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-infinity" style="color: white; font-size: 1rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: #1e293b;">Hasta Nuevo Aviso</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Pausa indefinida (requiere descripción)</div>
                        </div>
                    </div>
                    
                    <!-- Campo de razón (oculto por defecto) -->
                    <div id="razon-container" style="display: none;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            <i class="fas fa-comment-alt" style="margin-right: 6px;"></i>Descripción / Razón <span style="color: #e94560;">*</span>
                        </label>
                        <textarea id="swal-razon-pausa" class="swal2-textarea" placeholder="Describa el motivo de la pausa indefinida (obligatorio)..." style="width: 100%; margin: 0; min-height: 80px; border-radius: 10px;"></textarea>
                        <small style="color: #64748b;">Esta descripción es obligatoria para pausas indefinidas.</small>
                    </div>
                    
                    <input type="hidden" id="swal-dias-pausa" value="">
                    <input type="hidden" id="swal-indefinida" value="false">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-pause"></i> Confirmar Pausa',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-estoicos',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false,
            didOpen: () => {
                // Manejar selección de opciones
                $('.pause-option-swal').on('click', function() {
                    const dias = $(this).data('dias');
                    
                    // Remover selección anterior
                    $('.pause-option-swal').css({
                        'border-color': '#e2e8f0',
                        'background': 'transparent'
                    });
                    
                    // Marcar seleccionada
                    $(this).css({
                        'border-color': dias === 'indefinida' ? '#e94560' : '#f0a500',
                        'background': dias === 'indefinida' ? 'rgba(233, 69, 96, 0.08)' : 'rgba(240, 165, 0, 0.08)'
                    });
                    
                    if (dias === 'indefinida') {
                        $('#swal-dias-pausa').val('');
                        $('#swal-indefinida').val('true');
                        $('#razon-container').slideDown(200);
                    } else {
                        $('#swal-dias-pausa').val(dias);
                        $('#swal-indefinida').val('false');
                        $('#razon-container').slideUp(200);
                    }
                });
            },
            preConfirm: () => {
                const dias = $('#swal-dias-pausa').val();
                const indefinida = $('#swal-indefinida').val() === 'true';
                const razon = $('#swal-razon-pausa').val();
                
                if (!dias && !indefinida) {
                    Swal.showValidationMessage('Debe seleccionar una duración para la pausa');
                    return false;
                }
                
                if (indefinida && (!razon || razon.length < 5)) {
                    Swal.showValidationMessage('Para pausas indefinidas, debe indicar una razón (mínimo 5 caracteres)');
                    return false;
                }
                
                return {
                    dias: dias,
                    razon: razon || '',
                    indefinida: indefinida
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar petición al servidor como JSON
                $.ajax({
                    url: `/api/pausas/${id}/pausar`,
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(result.value),
                    success: function(response) {
                        Swal.fire({
                            title: '¡Membresía Pausada!',
                            html: `
                                <div style="text-align: center; padding: 1rem 0;">
                                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                        <i class="fas fa-pause" style="font-size: 1.8rem; color: #b45309;"></i>
                                    </div>
                                    <p style="color: #64748b; font-size: 1rem;">
                                        ${response.data.pausa_indefinida ? 'La membresía ha sido pausada indefinidamente' : 'La membresía se reanudará el <strong>' + response.data.fecha_pausa_fin + '</strong>'}
                                    </p>
                                </div>
                            `,
                            icon: null,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                popup: 'swal-estoicos',
                                confirmButton: 'swal2-confirm'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'No se pudo pausar la membresía';
                        Swal.fire({
                            title: 'Error',
                            html: `
                                <div style="text-align: center; padding: 1rem 0;">
                                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                        <i class="fas fa-times" style="font-size: 1.8rem; color: #b91c1c;"></i>
                                    </div>
                                    <p style="color: #64748b; font-size: 1rem;">${errorMsg}</p>
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
                    }
                });
            }
        });
    });

    // ======== REANUDAR INSCRIPCIÓN ========
    $(document).on('click', '.btn-reanudar', function() {
        const id = $(this).data('id');
        const cliente = $(this).data('cliente');

        Swal.fire({
            title: '<i class="fas fa-play-circle" style="color: var(--success);"></i> Reanudar Membresía',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <p style="color: #64748b; margin-bottom: 1rem;">
                        ¿Deseas reanudar la membresía de <strong>${cliente}</strong>?
                    </p>
                    <p style="color: #94a3b8; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Se extenderá la fecha de vencimiento por los días que estuvo pausada.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-play"></i> Reanudar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'swal-estoicos',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/pausas/${id}/reanudar`,
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Membresía Reanudada!',
                            html: `
                                <div style="text-align: center; padding: 1rem 0;">
                                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                        <i class="fas fa-play" style="font-size: 1.8rem; color: #047857;"></i>
                                    </div>
                                    <p style="color: #64748b; font-size: 1rem;">
                                        La membresía está activa nuevamente.<br>
                                        Nueva fecha de vencimiento: <strong>${response.data.nueva_fecha_vencimiento}</strong>
                                    </p>
                                </div>
                            `,
                            icon: null,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                popup: 'swal-estoicos',
                                confirmButton: 'swal2-confirm'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'No se pudo reanudar la membresía';
                        Swal.fire({
                            title: 'Error',
                            html: `
                                <div style="text-align: center; padding: 1rem 0;">
                                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                        <i class="fas fa-times" style="font-size: 1.8rem; color: #b91c1c;"></i>
                                    </div>
                                    <p style="color: #64748b; font-size: 1rem;">${errorMsg}</p>
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
                    }
                });
            }
        });
    });

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
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'swal2-confirm'
        },
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
