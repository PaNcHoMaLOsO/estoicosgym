@extends('adminlte::page')

@section('title', 'Detalle de Inscripción')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-clipboard-check text-accent mr-2"></i>Detalle de Inscripción</h1>
@stop

@push('css')
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #25253a;
        --accent: #e94560;
        --accent-hover: #ff6b6b;
        --success: #00bf8e;
        --warning: #f0a500;
        --info: #4361ee;
        --muted: #6c757d;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(26, 26, 46, 0.1);
        --card-hover-shadow: 0 0.5rem 2rem rgba(26, 26, 46, 0.15);
    }

    /* ========== Hero Header ========== */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
        opacity: 0.1;
    }
    .hero-content {
        position: relative;
        z-index: 1;
    }
    .hero-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .hero-subtitle {
        color: rgba(255,255,255,0.8);
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .hero-badge.activa {
        background: linear-gradient(135deg, var(--success), #00d9a0);
        color: white;
    }
    .hero-badge.pausada {
        background: linear-gradient(135deg, var(--warning), #ffc107);
        color: #1a1a2e;
    }
    .hero-badge.vencida {
        background: linear-gradient(135deg, #6c757d, #868e96);
        color: white;
    }
    .hero-badge.cancelada {
        background: linear-gradient(135deg, var(--accent), #ff6b6b);
        color: white;
    }

    /* ========== Stat Cards ========== */
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }
    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 0.75rem;
    }
    .stat-card .stat-icon.primary { background: rgba(26,26,46,0.15); color: var(--primary); }
    .stat-card .stat-icon.success { background: rgba(0,191,142,0.2); color: var(--success); }
    .stat-card .stat-icon.warning { background: rgba(240,165,0,0.2); color: var(--warning); }
    .stat-card .stat-icon.accent { background: rgba(233,69,96,0.2); color: var(--accent); }
    .stat-card .stat-icon.info { background: rgba(67,97,238,0.2); color: var(--info); }
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: 0.85rem;
        color: var(--muted);
        margin-top: 0.25rem;
    }

    /* ========== Info Cards ========== */
    .info-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .info-card .card-header-custom {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
    }
    .info-card .card-header-custom i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    .info-card .card-body-custom {
        padding: 1.5rem;
    }

    /* ========== Info Row ========== */
    .info-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-row .label {
        flex: 0 0 40%;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
    }
    .info-row .value {
        flex: 1;
        color: #495057;
        font-size: 0.9rem;
    }

    /* ========== Estado Badge ========== */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .estado-badge i { margin-right: 0.4rem; }
    .estado-activa { background: rgba(0,191,142,0.15); color: var(--success); }
    .estado-pausada { background: rgba(240,165,0,0.15); color: var(--warning); }
    .estado-vencida { background: rgba(108,117,125,0.15); color: #6c757d; }
    .estado-cancelada { background: rgba(233,69,96,0.15); color: var(--accent); }
    .estado-cambiada { background: rgba(67,97,238,0.15); color: var(--info); }

    /* ========== Progress Bar ========== */
    .progress-container {
        background: #e9ecef;
        border-radius: 10px;
        height: 10px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }
    .progress-bar-custom.success { background: linear-gradient(90deg, var(--success), #00d9a0); }
    .progress-bar-custom.warning { background: linear-gradient(90deg, var(--warning), #ffc107); }
    .progress-bar-custom.danger { background: linear-gradient(90deg, var(--accent), #ff6b6b); }

    /* ========== Tables ========== */
    .table-modern {
        margin-bottom: 0;
    }
    .table-modern thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.875rem 1rem;
        border: none;
    }
    .table-modern tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-color: var(--border-color);
        font-size: 0.875rem;
    }
    .table-modern tbody tr:hover {
        background-color: rgba(67,97,238,0.05);
    }

    /* ========== Timeline ========== */
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary), var(--accent));
    }
    .timeline-item {
        position: relative;
        padding: 0 0 1.5rem 50px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-badge {
        position: absolute;
        left: 10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        z-index: 1;
    }
    .timeline-badge.success { border-color: var(--success); color: var(--success); }
    .timeline-badge.warning { border-color: var(--warning); color: var(--warning); }
    .timeline-badge.info { border-color: var(--info); color: var(--info); }
    .timeline-badge.accent { border-color: var(--accent); color: var(--accent); }
    .timeline-content {
        background: var(--light-bg);
        border-radius: 10px;
        padding: 1rem;
        border-left: 3px solid var(--primary);
    }
    .timeline-content.success { border-left-color: var(--success); }
    .timeline-content.warning { border-left-color: var(--warning); }
    .timeline-content.info { border-left-color: var(--info); }
    .timeline-content.accent { border-left-color: var(--accent); }
    .timeline-title {
        font-weight: 600;
        color: var(--primary);
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }
    .timeline-date {
        font-size: 0.8rem;
        color: var(--muted);
    }
    .timeline-detail {
        font-size: 0.85rem;
        color: #495057;
        margin-top: 0.5rem;
    }

    /* ========== Alert Boxes ========== */
    .alert-box {
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .alert-box i {
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    .alert-box.warning {
        background: linear-gradient(135deg, rgba(240,165,0,0.1) 0%, rgba(255,193,7,0.1) 100%);
        border: 1px solid rgba(240,165,0,0.3);
        color: #856404;
    }
    .alert-box.success {
        background: linear-gradient(135deg, rgba(0,191,142,0.1) 0%, rgba(0,217,160,0.1) 100%);
        border: 1px solid rgba(0,191,142,0.3);
        color: #0d6952;
    }
    .alert-box.info {
        background: linear-gradient(135deg, rgba(67,97,238,0.1) 0%, rgba(99,126,255,0.1) 100%);
        border: 1px solid rgba(67,97,238,0.3);
        color: #2b4acb;
    }
    .alert-box.danger {
        background: linear-gradient(135deg, rgba(233,69,96,0.1) 0%, rgba(255,107,107,0.1) 100%);
        border: 1px solid rgba(233,69,96,0.3);
        color: #a61e35;
    }

    /* ========== Client Avatar ========== */
    .client-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin-right: 1.25rem;
        flex-shrink: 0;
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(26,26,46,0.2);
    }

    /* ========== Buttons ========== */
    .btn-estoicos {
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b6b 100%);
        border: none;
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }
    .btn-estoicos:hover {
        background: linear-gradient(135deg, #ff6b6b 0%, var(--accent) 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(233,69,96,0.4);
    }
    .btn-estoicos i { margin-right: 0.5rem; }

    .btn-secondary-estoicos {
        background: var(--light-bg);
        border: 2px solid var(--border-color);
        color: var(--primary);
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }
    .btn-secondary-estoicos:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .btn-secondary-estoicos i { margin-right: 0.5rem; }

    .btn-success-estoicos {
        background: linear-gradient(135deg, var(--success) 0%, #00d9a0 100%);
        border: none;
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }
    .btn-success-estoicos:hover {
        background: linear-gradient(135deg, #00d9a0 0%, var(--success) 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,191,142,0.4);
    }
    .btn-success-estoicos i { margin-right: 0.5rem; }

    /* ========== Pagos Badge ========== */
    .pago-badge {
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .pago-completo { background: rgba(0,191,142,0.15); color: var(--success); }
    .pago-parcial { background: rgba(240,165,0,0.15); color: var(--warning); }
    .pago-pendiente { background: rgba(233,69,96,0.15); color: var(--accent); }

    /* ========== Section Title ========== */
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
    }
    .section-title i {
        margin-right: 0.75rem;
        color: var(--accent);
    }

    /* ========== Empty State ========== */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--muted);
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    .empty-state p {
        margin: 0;
        font-size: 0.95rem;
    }

    /* ========== Financial Card ========== */
    .financial-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--border-color);
    }
    .financial-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .financial-item .label {
        font-weight: 500;
        color: #495057;
    }
    .financial-item .value {
        font-weight: 700;
        font-size: 1.1rem;
    }
    .financial-item .value.positive { color: var(--success); }
    .financial-item .value.negative { color: var(--accent); }
    .financial-item .value.neutral { color: var(--primary); }

    /* ========== Pausa Info Card ========== */
    .pausa-info-card {
        background: linear-gradient(135deg, rgba(240,165,0,0.08) 0%, rgba(255,193,7,0.08) 100%);
        border: 2px solid var(--warning);
        border-radius: 15px;
        padding: 1.5rem;
    }
    .pausa-info-card .pausa-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    .pausa-info-card .pausa-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--warning);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin-right: 1rem;
    }
    .pausa-info-card .pausa-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary);
        margin: 0;
    }
    .pausa-info-card .pausa-subtitle {
        font-size: 0.85rem;
        color: var(--muted);
        margin: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="hero-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Inscripción #{{ $inscripcion->id }}
                    </h1>
                    <p class="hero-subtitle mb-3">
                        {{ $inscripcion->membresia->nombre ?? 'Sin membresía' }}
                        @if($inscripcion->membresia && $inscripcion->membresia->duracion_meses)
                            <span class="mx-2">•</span>
                            {{ $inscripcion->membresia->duracion_meses }} {{ $inscripcion->membresia->duracion_meses == 1 ? 'mes' : 'meses' }}
                        @endif
                    </p>
                    @php
                        $estadoClase = match($inscripcion->id_estado) {
                            100 => 'activa',
                            101 => 'pausada',
                            102 => 'vencida',
                            103, 104 => 'cancelada',
                            default => 'vencida'
                        };
                        $estadoIcono = match($inscripcion->id_estado) {
                            100 => 'fa-check-circle',
                            101 => 'fa-pause-circle',
                            102 => 'fa-clock',
                            103, 104 => 'fa-times-circle',
                            default => 'fa-question-circle'
                        };
                    @endphp
                    <span class="hero-badge {{ $estadoClase }}">
                        <i class="fas {{ $estadoIcono }} mr-2"></i>
                        {{ $inscripcion->estado->descripcion ?? 'Estado desconocido' }}
                    </span>
                </div>
                <div class="text-right mt-3 mt-md-0">
                    <div class="btn-group">
                        @if($canEdit)
                            <a href="{{ route('admin.inscripciones.edit', $inscripcion->id) }}" class="btn-estoicos">
                                <i class="fas fa-edit"></i>Editar
                            </a>
                        @endif
                        <a href="{{ route('admin.inscripciones.index') }}" class="btn-secondary-estoicos ml-2">
                            <i class="fas fa-arrow-left"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <!-- Días Restantes -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon {{ $inscripcion->dias_restantes > 7 ? 'success' : ($inscripcion->dias_restantes > 0 ? 'warning' : 'accent') }}">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-value">{{ max(0, $inscripcion->dias_restantes) }}</div>
                <div class="stat-label">Días Restantes</div>
                <div class="progress-container">
                    @php
                        $totalDias = $inscripcion->fecha_inicio && $inscripcion->fecha_vencimiento 
                            ? max(1, \Carbon\Carbon::parse($inscripcion->fecha_inicio)->diffInDays(\Carbon\Carbon::parse($inscripcion->fecha_vencimiento)))
                            : 30;
                        $diasRestantes = max(0, $inscripcion->dias_restantes);
                        $porcentaje = min(100, ($diasRestantes / $totalDias) * 100);
                        $colorBarra = $porcentaje > 50 ? 'success' : ($porcentaje > 20 ? 'warning' : 'danger');
                    @endphp
                    <div class="progress-bar-custom {{ $colorBarra }}" style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>
        </div>

        <!-- Pausas -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-pause"></i>
                </div>
                <div class="stat-value">{{ $infoPausa['pausas_realizadas'] }}/{{ $infoPausa['max_pausas'] }}</div>
                <div class="stat-label">Pausas Utilizadas</div>
                @if($infoPausa['pausas_disponibles'] > 0)
                    <small class="text-success"><i class="fas fa-check-circle mr-1"></i>{{ $infoPausa['pausas_disponibles'] }} disponible(s)</small>
                @else
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Sin pausas disponibles</small>
                @endif
            </div>
        </div>

        <!-- Total Pagado -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">${{ number_format($infoFinanciera['total_pagado'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Pagado</div>
                <small class="text-muted">{{ $infoFinanciera['cantidad_pagos'] }} pago(s) registrado(s)</small>
            </div>
        </div>

        <!-- Deuda Pendiente -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon {{ $infoFinanciera['deuda_pendiente'] > 0 ? 'accent' : 'success' }}">
                    <i class="fas {{ $infoFinanciera['deuda_pendiente'] > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                </div>
                <div class="stat-value {{ $infoFinanciera['deuda_pendiente'] > 0 ? 'text-danger' : 'text-success' }}">
                    ${{ number_format($infoFinanciera['deuda_pendiente'], 0, ',', '.') }}
                </div>
                <div class="stat-label">{{ $infoFinanciera['deuda_pendiente'] > 0 ? 'Deuda Pendiente' : 'Sin Deuda' }}</div>
            </div>
        </div>
    </div>

    <!-- Alerta si está pausada -->
    @if($infoPausa['esta_pausada'])
    <div class="alert-box warning mb-4">
        <i class="fas fa-pause-circle"></i>
        <div>
            <strong>Inscripción Pausada</strong>
            <p class="mb-0 mt-1">
                Esta inscripción se encuentra pausada desde el 
                <strong>{{ $infoPausa['fecha_ultima_pausa'] ? \Carbon\Carbon::parse($infoPausa['fecha_ultima_pausa'])->format('d/m/Y') : 'N/A' }}</strong>.
                @if($infoPausa['dias_restantes_pausa'] > 0)
                    Quedan <strong>{{ $infoPausa['dias_restantes_pausa'] }} días</strong> de pausa.
                @endif
            </p>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-8">
            
            <!-- Info Cliente -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-user"></i>Información del Cliente
                </div>
                <div class="card-body-custom">
                    <div class="d-flex align-items-start mb-3">
                        <div class="client-avatar">
                            {{ strtoupper(substr($inscripcion->cliente->nombres ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-1 font-weight-bold" style="color: var(--primary);">
                                {{ $inscripcion->cliente->nombres ?? 'N/A' }} {{ $inscripcion->cliente->apellido_paterno ?? '' }} {{ $inscripcion->cliente->apellido_materno ?? '' }}
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-id-card mr-2"></i>RUT: {{ $inscripcion->cliente->run_pasaporte ?? 'N/A' }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                @if($inscripcion->cliente->email)
                                    <span class="badge badge-light px-3 py-2 mr-2">
                                        <i class="fas fa-envelope mr-1 text-info"></i>{{ $inscripcion->cliente->email }}
                                    </span>
                                @endif
                                @if($inscripcion->cliente->celular)
                                    <span class="badge badge-light px-3 py-2">
                                        <i class="fas fa-phone mr-1 text-success"></i>{{ $inscripcion->cliente->celular }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="label">Estado Cliente:</span>
                                <span class="value">
                                    @if($inscripcion->cliente->activo ?? false)
                                        <span class="estado-badge estado-activa"><i class="fas fa-check-circle"></i>Activo</span>
                                    @else
                                        <span class="estado-badge estado-cancelada"><i class="fas fa-times-circle"></i>Inactivo</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="label">Fecha Nacimiento:</span>
                                <span class="value">{{ $inscripcion->cliente->fecha_nacimiento ? \Carbon\Carbon::parse($inscripcion->cliente->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle Inscripción -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-clipboard-list"></i>Detalle de la Inscripción
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="label"><i class="fas fa-dumbbell mr-2 text-accent"></i>Membresía:</span>
                                <span class="value font-weight-bold">{{ $inscripcion->membresia->nombre ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"><i class="fas fa-calendar-plus mr-2 text-info"></i>Fecha Inicio:</span>
                                <span class="value">{{ $inscripcion->fecha_inicio ? \Carbon\Carbon::parse($inscripcion->fecha_inicio)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"><i class="fas fa-calendar-check mr-2 text-success"></i>Fecha Vencimiento:</span>
                                <span class="value">{{ $inscripcion->fecha_vencimiento ? \Carbon\Carbon::parse($inscripcion->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"><i class="fas fa-clock mr-2 text-warning"></i>Duración:</span>
                                <span class="value">{{ $inscripcion->membresia->duracion_meses ?? 0 }} mes(es)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="label"><i class="fas fa-tag mr-2 text-primary"></i>Precio Base:</span>
                                <span class="value">${{ number_format($inscripcion->precio_base ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label"><i class="fas fa-percentage mr-2 text-warning"></i>Descuento:</span>
                                <span class="value">
                                    @if(($inscripcion->descuento_aplicado ?? 0) > 0)
                                        <span class="text-success font-weight-bold">-${{ number_format($inscripcion->descuento_aplicado, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">Sin descuento</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label"><i class="fas fa-dollar-sign mr-2 text-success"></i>Precio Final:</span>
                                <span class="value font-weight-bold text-success" style="font-size: 1.1rem;">
                                    ${{ number_format($inscripcion->precio_final ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            @if($inscripcion->convenio)
                            <div class="info-row">
                                <span class="label"><i class="fas fa-handshake mr-2 text-info"></i>Convenio:</span>
                                <span class="value">{{ $inscripcion->convenio->nombre ?? 'N/A' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Cambios -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-history"></i>Historial de Cambios
                </div>
                <div class="card-body-custom">
                    @if($historialCambios && $historialCambios->count() > 0)
                        <ul class="timeline">
                            @foreach($historialCambios as $cambio)
                                @php
                                    $tipoCambio = $cambio->tipo_cambio ?? 'cambio';
                                    $colorClase = match($tipoCambio) {
                                        'pausa' => 'warning',
                                        'reanudacion' => 'success',
                                        'cambio_plan', 'mejora' => 'info',
                                        'traspaso' => 'accent',
                                        default => 'info'
                                    };
                                    $icono = match($tipoCambio) {
                                        'pausa' => 'fa-pause',
                                        'reanudacion' => 'fa-play',
                                        'cambio_plan', 'mejora' => 'fa-exchange-alt',
                                        'traspaso' => 'fa-users',
                                        default => 'fa-edit'
                                    };
                                @endphp
                                <li class="timeline-item">
                                    <div class="timeline-badge {{ $colorClase }}">
                                        <i class="fas {{ $icono }}"></i>
                                    </div>
                                    <div class="timeline-content {{ $colorClase }}">
                                        <div class="timeline-title">
                                            {{ ucfirst(str_replace('_', ' ', $tipoCambio)) }}
                                        </div>
                                        <div class="timeline-date">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            {{ $cambio->fecha_cambio ? \Carbon\Carbon::parse($cambio->fecha_cambio)->format('d/m/Y H:i') : 'N/A' }}
                                            @if($cambio->usuario)
                                                <span class="mx-2">•</span>
                                                <i class="far fa-user mr-1"></i>{{ $cambio->usuario->name ?? 'Sistema' }}
                                            @endif
                                        </div>
                                        @if($cambio->descripcion || $cambio->observaciones)
                                            <div class="timeline-detail">
                                                {{ $cambio->descripcion ?? $cambio->observaciones }}
                                            </div>
                                        @endif
                                        @if($cambio->estadoAnterior || $cambio->estadoNuevo)
                                            <div class="timeline-detail">
                                                <small>
                                                    <span class="text-muted">Estado:</span>
                                                    {{ $cambio->estadoAnterior->descripcion ?? 'N/A' }}
                                                    <i class="fas fa-arrow-right mx-1"></i>
                                                    {{ $cambio->estadoNuevo->descripcion ?? 'N/A' }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <p>No hay historial de cambios registrado</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Pagos -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-money-bill-wave"></i>Historial de Pagos
                </div>
                <div class="card-body-custom p-0">
                    @if($inscripcion->pagos && $inscripcion->pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inscripcion->pagos->sortByDesc('fecha_pago') as $index => $pago)
                                        <tr>
                                            <td><strong>{{ $index + 1 }}</strong></td>
                                            <td>{{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                                            <td><strong class="text-success">${{ number_format($pago->monto ?? 0, 0, ',', '.') }}</strong></td>
                                            <td>
                                                <span class="badge badge-light">
                                                    <i class="fas fa-credit-card mr-1"></i>
                                                    {{ $pago->metodoPago->nombre ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    // El estado viene como relación, obtener el nombre
                                                    $estadoPagoObj = $pago->estado;
                                                    $estadoPagoNombre = is_object($estadoPagoObj) ? ($estadoPagoObj->nombre ?? 'Pagado') : ($estadoPagoObj ?? 'Pagado');
                                                    $estadoPagoCodigo = is_object($estadoPagoObj) ? ($estadoPagoObj->codigo ?? 201) : 201;
                                                    
                                                    $clasePago = match(true) {
                                                        $estadoPagoCodigo == 201 || str_contains(strtolower($estadoPagoNombre), 'pagado') || str_contains(strtolower($estadoPagoNombre), 'completo') => 'pago-completo',
                                                        $estadoPagoCodigo == 202 || str_contains(strtolower($estadoPagoNombre), 'parcial') => 'pago-parcial',
                                                        $estadoPagoCodigo == 200 || str_contains(strtolower($estadoPagoNombre), 'pendiente') => 'pago-pendiente',
                                                        default => 'pago-completo'
                                                    };
                                                @endphp
                                                <span class="pago-badge {{ $clasePago }}">{{ $estadoPagoNombre }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $pago->observacion ?? '-' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>No hay pagos registrados</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Estado Actual -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-info-circle"></i>Estado Actual
                </div>
                <div class="card-body-custom">
                    <div class="text-center mb-3">
                        @php
                            $estadoIconoBig = match($inscripcion->id_estado) {
                                100 => ['fa-check-circle', 'text-success'],
                                101 => ['fa-pause-circle', 'text-warning'],
                                102 => ['fa-clock', 'text-secondary'],
                                103, 104 => ['fa-times-circle', 'text-danger'],
                                105 => ['fa-exchange-alt', 'text-info'],
                                106 => ['fa-users', 'text-info'],
                                default => ['fa-question-circle', 'text-muted']
                            };
                        @endphp
                        <i class="fas {{ $estadoIconoBig[0] }} {{ $estadoIconoBig[1] }}" style="font-size: 3rem;"></i>
                        <h4 class="mt-2 mb-1" style="color: var(--primary);">{{ $inscripcion->estado->descripcion ?? 'Desconocido' }}</h4>
                        <p class="text-muted mb-0">Estado de la inscripción</p>
                    </div>
                    
                    @if($inscripcion->id_estado == 100)
                        <div class="alert-box success">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Inscripción Activa</strong>
                                <p class="mb-0 small">El cliente puede acceder a todos los servicios del gimnasio.</p>
                            </div>
                        </div>
                    @elseif($inscripcion->id_estado == 101)
                        <div class="alert-box warning">
                            <i class="fas fa-pause-circle"></i>
                            <div>
                                <strong>Inscripción Pausada</strong>
                                <p class="mb-0 small">Los días de membresía están congelados temporalmente.</p>
                            </div>
                        </div>
                    @elseif($inscripcion->id_estado == 102)
                        <div class="alert-box danger">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Inscripción Vencida</strong>
                                <p class="mb-0 small">El período de membresía ha finalizado.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info Pausa (solo si está pausada) -->
            @if($infoPausa['esta_pausada'])
            <div class="pausa-info-card mb-4">
                <div class="pausa-header">
                    <div class="pausa-icon">
                        <i class="fas fa-pause"></i>
                    </div>
                    <div>
                        <h5 class="pausa-title">Información de Pausa</h5>
                        <p class="pausa-subtitle">Detalles del período de pausa</p>
                    </div>
                </div>
                <div class="info-row">
                    <span class="label">Fecha de Pausa:</span>
                    <span class="value">{{ $infoPausa['fecha_ultima_pausa'] ? \Carbon\Carbon::parse($infoPausa['fecha_ultima_pausa'])->format('d/m/Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Días de Pausa:</span>
                    <span class="value">{{ $infoPausa['duracion_pausa'] ?? 7 }} días</span>
                </div>
                <div class="info-row">
                    <span class="label">Días Restantes:</span>
                    <span class="value font-weight-bold text-warning">{{ $infoPausa['dias_restantes_pausa'] ?? 0 }} días</span>
                </div>
                @if($infoPausa['razon_pausa'])
                <div class="info-row">
                    <span class="label">Razón:</span>
                    <span class="value">{{ $infoPausa['razon_pausa'] }}</span>
                </div>
                @endif
            </div>
            @endif

            <!-- Resumen Financiero -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-chart-pie"></i>Resumen Financiero
                </div>
                <div class="card-body-custom">
                    <div class="financial-item">
                        <span class="label">Precio Base:</span>
                        <span class="value neutral">${{ number_format($inscripcion->precio_base ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(($inscripcion->descuento_aplicado ?? 0) > 0)
                    <div class="financial-item">
                        <span class="label">Descuento:</span>
                        <span class="value positive">-${{ number_format($inscripcion->descuento_aplicado ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="financial-item">
                        <span class="label"><strong>Precio Final:</strong></span>
                        <span class="value neutral"><strong>${{ number_format($inscripcion->precio_final ?? 0, 0, ',', '.') }}</strong></span>
                    </div>
                    <hr>
                    <div class="financial-item">
                        <span class="label">Total Pagado:</span>
                        <span class="value positive">${{ number_format($infoFinanciera['total_pagado'], 0, ',', '.') }}</span>
                    </div>
                    <div class="financial-item">
                        <span class="label"><strong>Saldo Pendiente:</strong></span>
                        <span class="value {{ $infoFinanciera['deuda_pendiente'] > 0 ? 'negative' : 'positive' }}">
                            <strong>${{ number_format($infoFinanciera['deuda_pendiente'], 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Información de Mejora (si aplica) -->
            @if($inscripcion->es_mejora_plan || $inscripcion->inscripcion_anterior_id)
            <div class="info-card">
                <div class="card-header-custom" style="background: linear-gradient(135deg, var(--info) 0%, #637bff 100%);">
                    <i class="fas fa-level-up-alt"></i>Mejora de Plan
                </div>
                <div class="card-body-custom">
                    <div class="alert-box info mb-0">
                        <i class="fas fa-arrow-up"></i>
                        <div>
                            <strong>Plan Mejorado</strong>
                            <p class="mb-0 small">Esta inscripción es resultado de una mejora de plan.</p>
                            @if($inscripcion->inscripcionAnterior)
                                <p class="mb-0 small mt-1">
                                    Plan anterior: <strong>{{ $inscripcion->inscripcionAnterior->membresia->nombre ?? 'N/A' }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Información de Traspaso (si aplica) -->
            @if($inscripcion->es_traspaso || $inscripcion->traspaso_origen_id)
            <div class="info-card">
                <div class="card-header-custom" style="background: linear-gradient(135deg, #6f42c1 0%, #9561e2 100%);">
                    <i class="fas fa-exchange-alt"></i>Información de Traspaso
                </div>
                <div class="card-body-custom">
                    <div class="alert-box info mb-0" style="background: linear-gradient(135deg, rgba(111,66,193,0.1) 0%, rgba(149,97,226,0.1) 100%); border-color: rgba(111,66,193,0.3); color: #563d7c;">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Inscripción Traspasada</strong>
                            @if($inscripcion->inscripcionOrigen && $inscripcion->inscripcionOrigen->cliente)
                                <p class="mb-0 small">
                                    Cliente origen: <strong>{{ $inscripcion->inscripcionOrigen->cliente->nombres ?? 'N/A' }} {{ $inscripcion->inscripcionOrigen->cliente->apellido_paterno ?? '' }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Acciones Rápidas -->
            @if($canEdit)
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-bolt"></i>Acciones Rápidas
                </div>
                <div class="card-body-custom">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.inscripciones.edit', $inscripcion->id) }}" class="btn btn-block btn-estoicos mb-2">
                            <i class="fas fa-edit"></i>Editar Inscripción
                        </a>
                        @if($inscripcion->id_estado == 100)
                            <a href="{{ route('admin.inscripciones.edit', ['inscripcion' => $inscripcion->id, 'mode' => 'mejorar']) }}" class="btn btn-block btn-success-estoicos mb-2">
                                <i class="fas fa-level-up-alt"></i>Mejorar Plan
                            </a>
                        @endif
                        <a href="{{ route('admin.clientes.show', $inscripcion->id_cliente) }}" class="btn btn-block btn-secondary-estoicos">
                            <i class="fas fa-user"></i>Ver Cliente
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Metadatos -->
            <div class="info-card">
                <div class="card-header-custom">
                    <i class="fas fa-database"></i>Metadatos
                </div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <span class="label">ID Inscripción:</span>
                        <span class="value">#{{ $inscripcion->id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Creado:</span>
                        <span class="value">{{ $inscripcion->created_at ? $inscripcion->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Actualizado:</span>
                        <span class="value">{{ $inscripcion->updated_at ? $inscripcion->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración SweetAlert2 para el estilo Estoicos
    const SwalEstoicos = Swal.mixin({
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'btn btn-estoicos mx-1',
            cancelButton: 'btn btn-secondary-estoicos mx-1'
        },
        buttonsStyling: false
    });

    // Animación de entrada para las cards
    const cards = document.querySelectorAll('.stat-card, .info-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });

    console.log('Show inscripción inicializado correctamente');
});
</script>
@endpush
