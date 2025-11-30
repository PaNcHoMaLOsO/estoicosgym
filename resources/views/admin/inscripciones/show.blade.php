@extends('adminlte::page')

@section('title', 'Inscripción - EstóicosGym')

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
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* HERO HEADER */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 35px 40px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 15px 40px rgba(26, 26, 46, 0.4);
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 250px;
        height: 250px;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.1;
    }
    .hero-header::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: 30%;
        width: 180px;
        height: 180px;
        background: var(--success);
        border-radius: 50%;
        opacity: 0.08;
    }
    .hero-header-content { position: relative; z-index: 1; }
    .hero-title { 
        font-size: 2em; 
        font-weight: 800; 
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .hero-subtitle { 
        font-size: 1.1em; 
        opacity: 0.9;
        font-weight: 400;
    }

    /* STAT CARDS */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 22px;
        border: none;
        border-left: 5px solid var(--gray-200);
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    .stat-card.primary { border-left-color: var(--info); }
    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }
    .stat-card.info { border-left-color: var(--info); }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3em;
        margin-bottom: 15px;
    }
    .stat-icon.primary { background: rgba(67, 97, 238, 0.12); color: var(--info); }
    .stat-icon.success { background: rgba(0, 191, 142, 0.12); color: var(--success); }
    .stat-icon.warning { background: rgba(240, 165, 0, 0.12); color: var(--warning); }
    .stat-icon.danger { background: rgba(233, 69, 96, 0.12); color: var(--accent); }
    
    .stat-label { 
        font-size: 0.75em; 
        color: var(--gray-600); 
        font-weight: 600; 
        text-transform: uppercase; 
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }
    .stat-value { 
        font-size: 1.7em; 
        font-weight: 800; 
        color: var(--gray-800);
    }
    .stat-value.success { color: var(--success); }
    .stat-value.danger { color: var(--accent); }
    .stat-value.warning { color: var(--warning); }
    .stat-value.info { color: var(--info); }

    /* SECTION TITLES */
    .section-title {
        font-size: 1.25em;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i {
        color: var(--accent);
    }

    /* INFO CARDS */
    .info-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        height: 100%;
    }
    .info-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 18px 22px;
        font-weight: 700;
        font-size: 1em;
    }
    .info-card-body {
        padding: 22px;
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .info-item:last-child { border-bottom: none; }
    .info-item-label {
        color: var(--gray-600);
        font-size: 0.9em;
        font-weight: 500;
    }
    .info-item-value {
        font-weight: 700;
        color: var(--gray-800);
    }

    /* ESTADO BADGE */
    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .estado-badge.activa {
        background: rgba(0, 191, 142, 0.15);
        color: var(--success);
        border: 2px solid var(--success);
    }
    .estado-badge.vencida {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
        border: 2px solid var(--accent);
    }
    .estado-badge.pendiente {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
        border: 2px solid var(--warning);
    }

    /* PROGRESS BAR */
    .progress-container {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    }
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .progress-custom {
        height: 14px;
        background: var(--gray-200);
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-custom .progress-bar {
        background: linear-gradient(90deg, var(--success) 0%, var(--success-dark) 100%);
        border-radius: 10px;
        transition: width 0.6s ease;
    }
    .progress-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--gray-200);
    }
    .progress-stat {
        text-align: center;
    }
    .progress-stat-value {
        font-size: 1.4em;
        font-weight: 800;
        color: var(--gray-800);
    }
    .progress-stat-label {
        font-size: 0.75em;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* TABLE */
    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    /* MODERN CARD */
    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        margin-bottom: 20px;
        border: none;
        overflow: hidden;
    }
    .modern-card-header {
        padding: 18px 25px;
        border-bottom: 1px solid var(--gray-200);
        font-weight: 700;
        font-size: 1.05em;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .modern-card-body {
        padding: 25px;
    }

    .table-custom {
        margin-bottom: 0;
    }
    .table-custom thead {
        background: var(--primary);
    }
    .table-custom thead th {
        border: none;
        color: white;
        font-weight: 600;
        padding: 16px 20px;
        font-size: 0.85em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-custom tbody tr {
        transition: background 0.2s ease;
    }
    .table-custom tbody tr:hover {
        background: var(--gray-100);
    }
    .table-custom tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-color: var(--gray-200);
    }

    /* MEMBERSHIP TIMELINE */
    .membership-timeline {
        position: relative;
        padding-left: 30px;
    }
    .membership-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--accent) 0%, var(--info) 100%);
        border-radius: 2px;
    }
    .timeline-item {
        position: relative;
        background: white;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 15px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.06);
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .timeline-item:hover {
        transform: translateX(5px);
        box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    }
    .timeline-item.current {
        border-left-color: var(--accent);
        background: linear-gradient(135deg, rgba(233, 69, 96, 0.03) 0%, rgba(255, 255, 255, 1) 100%);
    }
    .timeline-item.past {
        border-left-color: var(--info);
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 22px;
        width: 14px;
        height: 14px;
        background: white;
        border: 3px solid var(--accent);
        border-radius: 50%;
    }
    .timeline-item.past::before {
        border-color: var(--info);
    }
    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .timeline-title {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1.05em;
    }
    .timeline-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.7em;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .timeline-badge.current {
        background: var(--accent);
        color: white;
    }
    .timeline-badge.past {
        background: var(--gray-200);
        color: var(--gray-600);
    }
    .timeline-dates {
        color: var(--gray-600);
        font-size: 0.85em;
    }
    .timeline-price {
        font-weight: 800;
        color: var(--success);
        font-size: 1.1em;
        margin-top: 8px;
    }

    /* BUTTONS */
    .btn-custom {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-custom-primary {
        background: var(--primary);
        color: white;
        border: none;
    }
    .btn-custom-primary:hover {
        background: var(--primary-light);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 26, 46, 0.3);
    }
    .btn-custom-success {
        background: var(--success);
        color: white;
        border: none;
    }
    .btn-custom-success:hover {
        background: var(--success-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 191, 142, 0.3);
    }
    .btn-custom-outline {
        background: transparent;
        color: white;
        border: 2px solid rgba(255,255,255,0.5);
    }
    .btn-custom-outline:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-color: white;
    }
    .btn-custom-outline-dark {
        background: transparent;
        color: var(--gray-600);
        border: 2px solid var(--gray-200);
    }
    .btn-custom-outline-dark:hover {
        background: var(--gray-100);
        color: var(--gray-800);
        border-color: var(--gray-300);
    }

    /* ALERT CUSTOM */
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .alert-custom.success {
        background: rgba(0, 191, 142, 0.1);
        color: var(--success-dark);
    }
    .alert-custom.warning {
        background: rgba(240, 165, 0, 0.1);
        color: #c78800;
    }
    .alert-custom.info {
        background: rgba(67, 97, 238, 0.1);
        color: var(--info);
    }

    /* CLIENT CARD */
    .client-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .client-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 25px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .client-avatar {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8em;
        border: 3px solid rgba(255,255,255,0.3);
    }
    .client-info h3 {
        margin: 0;
        font-weight: 700;
        font-size: 1.3em;
    }
    .client-info p {
        margin: 5px 0 0;
        opacity: 0.85;
        font-size: 0.9em;
    }
    .client-body {
        padding: 25px;
    }
    .client-detail {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .client-detail:last-child { border-bottom: none; }
    .client-detail-icon {
        width: 40px;
        height: 40px;
        background: var(--gray-100);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--info);
    }
    .client-detail-text strong {
        display: block;
        color: var(--gray-800);
        font-size: 0.95em;
    }
    .client-detail-text small {
        color: var(--gray-600);
    }
</style>
@stop

@section('content_header')
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="hero-title">
                        <i class="fas fa-file-invoice"></i> Inscripción #{{ $inscripcion->id }}
                    </div>
                    <div class="hero-subtitle">
                        <i class="fas fa-user"></i> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-dumbbell"></i> {{ $inscripcion->membresia?->nombre ?? 'Sin membresía' }}
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-custom btn-custom-outline mr-2">
                        <i class="fas fa-pencil-alt"></i> Editar
                    </a>
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-custom btn-custom-outline">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $total = $inscripcion->precio_final ?? $inscripcion->precio_base;
        $pagos = $inscripcion->pagos()->sum('monto_abonado');
        $pendiente = max(0, $total - $pagos);
        $porcentaje = ($total > 0) ? round(($pagos / $total) * 100) : 0;
        $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
        
        // Determinar clase del estado
        $estadoClase = 'pendiente';
        $estadoNombre = strtolower($inscripcion->estado?->nombre ?? '');
        if (str_contains($estadoNombre, 'activ')) $estadoClase = 'activa';
        elseif (str_contains($estadoNombre, 'venc') || str_contains($estadoNombre, 'cancel')) $estadoClase = 'vencida';
    @endphp

    <!-- ROW 1: CARDS DE ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="stat-label">Precio Base</div>
                <div class="stat-value">${{ number_format($inscripcion->precio_base, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-label">Precio Final</div>
                <div class="stat-value success">${{ number_format($total, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card info">
                <div class="stat-icon success">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-label">Total Pagado</div>
                <div class="stat-value info">${{ number_format($pagos, 0, '.', '.') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card {{ $pendiente > 0 ? 'warning' : 'success' }}">
                <div class="stat-icon {{ $pendiente > 0 ? 'warning' : 'success' }}">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-label">Pendiente</div>
                <div class="stat-value {{ $pendiente > 0 ? 'warning' : 'success' }}">${{ number_format($pendiente, 0, '.', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- ROW 2: CLIENTE + ESTADO -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="client-card">
                <div class="client-header">
                    <div class="client-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="client-info">
                        <h3>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }} {{ $inscripcion->cliente->apellido_materno ?? '' }}</h3>
                        <p><i class="fas fa-id-card"></i> RUT: {{ $inscripcion->cliente->rut ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="client-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="client-detail">
                                <div class="client-detail-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="client-detail-text">
                                    <strong>{{ $inscripcion->cliente->email ?? 'Sin email' }}</strong>
                                    <small>Correo electrónico</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="client-detail">
                                <div class="client-detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="client-detail-text">
                                    <strong>{{ $inscripcion->cliente->telefono ?? 'Sin teléfono' }}</strong>
                                    <small>Teléfono</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="client-detail">
                                <div class="client-detail-icon">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                                <div class="client-detail-text">
                                    <strong>{{ $inscripcion->membresia?->nombre ?? 'Sin membresía' }}</strong>
                                    <small>Membresía</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="client-detail">
                                <div class="client-detail-icon">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div class="client-detail-text">
                                    <strong>{{ $inscripcion->convenio?->nombre ?? 'Sin convenio' }}</strong>
                                    <small>Convenio</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-info-circle"></i> Estado de la Inscripción
                </div>
                <div class="info-card-body text-center py-4">
                    <div class="estado-badge {{ $estadoClase }} mb-3">
                        @if($estadoClase == 'activa')
                            <i class="fas fa-check-circle"></i>
                        @elseif($estadoClase == 'vencida')
                            <i class="fas fa-times-circle"></i>
                        @else
                            <i class="fas fa-clock"></i>
                        @endif
                        {{ $inscripcion->estado?->nombre ?? 'Sin estado' }}
                    </div>
                    
                    <div class="mt-4">
                        @if($diasRestantes > 0)
                            <h3 class="mb-1" style="color: var(--success); font-weight: 800;">{{ $diasRestantes }}</h3>
                            <p class="text-muted mb-0">días restantes</p>
                        @elseif($diasRestantes == 0)
                            <h3 class="mb-1" style="color: var(--warning); font-weight: 800;">¡Hoy!</h3>
                            <p class="text-muted mb-0">Vence hoy</p>
                        @else
                            <h3 class="mb-1" style="color: var(--accent); font-weight: 800;">{{ abs($diasRestantes) }}</h3>
                            <p class="text-muted mb-0">días vencida</p>
                        @endif
                    </div>
                    
                    <div class="info-item mt-4" style="border-top: 1px solid var(--gray-200); padding-top: 15px;">
                        <span class="info-item-label">Período</span>
                        <span class="info-item-value" style="font-size: 0.85em;">
                            {{ $inscripcion->fecha_inicio->format('d/m/Y') }} - {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: PROGRESO DE PAGO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress-container">
                <div class="progress-header">
                    <h5 class="section-title mb-0"><i class="fas fa-chart-line"></i> Progreso de Pago</h5>
                    <span class="estado-badge {{ $porcentaje >= 100 ? 'activa' : ($porcentaje > 0 ? 'pendiente' : 'vencida') }}" style="font-size: 0.8em;">
                        {{ $porcentaje }}% Completado
                    </span>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar" role="progressbar" style="width: {{ $porcentaje }}%"></div>
                </div>
                <div class="progress-stats">
                    <div class="progress-stat">
                        <div class="progress-stat-value">{{ $inscripcion->pagos->count() }}</div>
                        <div class="progress-stat-label">Pagos Realizados</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value">{{ $inscripcion->fecha_inicio->diffInDays($inscripcion->fecha_vencimiento) }}</div>
                        <div class="progress-stat-label">Días de Membresía</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value">${{ number_format($pagos, 0, '.', '.') }}</div>
                        <div class="progress-stat-label">Monto Abonado</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" style="color: {{ $pendiente > 0 ? 'var(--warning)' : 'var(--success)' }};">
                            ${{ number_format($pendiente, 0, '.', '.') }}
                        </div>
                        <div class="progress-stat-label">Saldo Pendiente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 4: DESCUENTOS Y OBSERVACIONES -->
    @if($inscripcion->descuento_aplicado > 0 || $inscripcion->observaciones)
    <div class="row mb-4">
        @if($inscripcion->descuento_aplicado > 0)
            <div class="col-md-{{ $inscripcion->observaciones ? '6' : '12' }}">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-percent"></i> Descuento Aplicado
                    </div>
                    <div class="info-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 style="color: var(--accent); font-weight: 800; margin: 0;">
                                    -${{ number_format($inscripcion->descuento_aplicado, 0, '.', '.') }}
                                </h3>
                                @if($inscripcion->motivoDescuento)
                                    <p class="text-muted mb-0 mt-2">
                                        <i class="fas fa-tag"></i> {{ $inscripcion->motivoDescuento->nombre }}
                                    </p>
                                @endif
                            </div>
                            <div class="stat-icon danger">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($inscripcion->observaciones)
            <div class="col-md-{{ $inscripcion->descuento_aplicado > 0 ? '6' : '12' }}">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </div>
                    <div class="info-card-body">
                        <p class="mb-0" style="color: var(--gray-600); line-height: 1.6;">
                            {{ $inscripcion->observaciones }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- INFORMACIÓN DE MEJORA DE PLAN (si aplica) -->
    @if($inscripcion->es_cambio_plan)
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card" style="border-left: 5px solid var(--success);">
                <div class="modern-card-header" style="background: linear-gradient(135deg, var(--success) 0%, #00d9a0 100%); color: white;">
                    <i class="fas fa-arrow-circle-up"></i>
                    <span>Mejora de Plan Realizada</span>
                    <span class="badge bg-light text-dark ms-auto">
                        ⬆️ Upgrade
                    </span>
                </div>
                <div class="modern-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="stat-icon success">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Plan Anterior</small>
                                    <strong>
                                        @if($inscripcion->inscripcionAnterior)
                                            {{ $inscripcion->inscripcionAnterior->membresia->nombre ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="stat-icon info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Fecha del Cambio</small>
                                    <strong>{{ $inscripcion->fecha_cambio_plan ? $inscripcion->fecha_cambio_plan->format('d/m/Y H:i') : 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon warning">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Precio Nuevo Plan</small>
                                    <strong>${{ number_format($inscripcion->precio_nuevo_plan ?? 0, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon success">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Crédito Aplicado</small>
                                    <strong class="text-success">-${{ number_format($inscripcion->credito_plan_anterior ?? 0, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon {{ ($inscripcion->diferencia_a_pagar ?? 0) > 0 ? 'danger' : 'success' }}">
                                    <i class="fas fa-{{ ($inscripcion->diferencia_a_pagar ?? 0) > 0 ? 'arrow-up' : 'gift' }}"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">
                                        {{ ($inscripcion->diferencia_a_pagar ?? 0) > 0 ? 'Diferencia Pagada' : 'Crédito a Favor' }}
                                    </small>
                                    <strong class="{{ ($inscripcion->diferencia_a_pagar ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format(abs($inscripcion->diferencia_a_pagar ?? 0), 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($inscripcion->motivo_cambio_plan)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted d-block mb-1"><i class="fas fa-comment me-1"></i>Motivo del cambio:</small>
                            <p class="mb-0">{{ $inscripcion->motivo_cambio_plan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- INFORMACIÓN DE TRASPASO (si aplica) -->
    @if($inscripcion->es_traspaso)
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card" style="border-left: 5px solid #9b59b6;">
                <div class="modern-card-header" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white;">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Membresía Recibida por Traspaso</span>
                    <span class="badge bg-light text-dark ms-auto">
                        <i class="fas fa-gift me-1"></i> Traspaso
                    </span>
                </div>
                <div class="modern-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="stat-icon" style="background: rgba(155, 89, 182, 0.12); color: #9b59b6;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Traspasada desde</small>
                                    <strong>
                                        @if($inscripcion->clienteOriginal)
                                            <a href="{{ route('admin.clientes.show', $inscripcion->clienteOriginal) }}" class="text-decoration-none" style="color: #9b59b6;">
                                                {{ $inscripcion->clienteOriginal->nombre }} {{ $inscripcion->clienteOriginal->apellido }}
                                            </a>
                                        @else
                                            Cliente anterior
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="stat-icon info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Fecha del Traspaso</small>
                                    <strong>{{ $inscripcion->fecha_traspaso ? $inscripcion->fecha_traspaso->format('d/m/Y H:i') : 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($inscripcion->motivo_traspaso)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted d-block mb-1"><i class="fas fa-comment me-1"></i>Motivo del traspaso:</small>
                            <p class="mb-0">{{ $inscripcion->motivo_traspaso }}</p>
                        </div>
                    @endif
                    @if($inscripcion->inscripcionOrigen)
                        <div class="mt-3 pt-3 border-top">
                            <a href="{{ route('admin.inscripciones.show', $inscripcion->inscripcionOrigen) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-history me-1"></i> Ver inscripción original
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SI ESTA INSCRIPCIÓN FUE TRASPASADA (tiene inscripciones de traspaso) -->
    @if($inscripcion->inscripcionesTraspasadas && $inscripcion->inscripcionesTraspasadas->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert" style="background: rgba(155, 89, 182, 0.1); border: 1px solid #9b59b6; border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-share fa-lg" style="color: #9b59b6;"></i>
                    <div>
                        <strong style="color: #9b59b6;">Esta membresía fue traspasada</strong>
                        <p class="mb-0 text-muted">
                            Traspasada a: 
                            @foreach($inscripcion->inscripcionesTraspasadas as $traspasada)
                                <a href="{{ route('admin.inscripciones.show', $traspasada) }}" class="fw-bold" style="color: #9b59b6;">
                                    {{ $traspasada->cliente->nombre ?? 'Cliente' }} {{ $traspasada->cliente->apellido ?? '' }}
                                </a>
                                ({{ $traspasada->fecha_traspaso ? $traspasada->fecha_traspaso->format('d/m/Y') : 'N/A' }})
                                @if(!$loop->last), @endif
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- INSCRIPCIÓN ANTERIOR (si esta fue cambiada desde otra) -->
    @if($inscripcion->inscripcionAnterior)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert" style="background: rgba(67, 97, 238, 0.1); border: 1px solid var(--info); border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-history fa-lg text-info"></i>
                    <div>
                        <strong>Esta inscripción proviene de un cambio de plan</strong>
                        <p class="mb-0 text-muted">
                            Inscripción anterior: 
                            <a href="{{ route('admin.inscripciones.show', $inscripcion->inscripcionAnterior) }}" class="text-info fw-bold">
                                {{ $inscripcion->inscripcionAnterior->membresia->nombre ?? 'Ver inscripción' }} 
                                ({{ $inscripcion->inscripcionAnterior->fecha_inicio->format('d/m/Y') }})
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SI ESTA INSCRIPCIÓN FUE CAMBIADA (tiene inscripciones posteriores) -->
    @if($inscripcion->inscripcionesPosteriores && $inscripcion->inscripcionesPosteriores->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert" style="background: rgba(240, 165, 0, 0.1); border: 1px solid var(--warning); border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-exchange-alt fa-lg text-warning"></i>
                    <div>
                        <strong>Esta inscripción fue reemplazada por un cambio de plan</strong>
                        <p class="mb-0 text-muted">
                            Nueva inscripción: 
                            @foreach($inscripcion->inscripcionesPosteriores as $nueva)
                                <a href="{{ route('admin.inscripciones.show', $nueva) }}" class="text-warning fw-bold">
                                    {{ $nueva->membresia->nombre ?? 'Ver inscripción' }}
                                    ({{ $nueva->fecha_inicio->format('d/m/Y') }})
                                </a>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- HISTORIAL DE PAGOS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="section-title mb-0"><i class="fas fa-credit-card"></i> Historial de Pagos</h5>
                @if($pendiente > 0)
                    <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" class="btn btn-custom btn-custom-success">
                        <i class="fas fa-plus-circle"></i> Nuevo Pago
                    </a>
                @endif
            </div>

            @if($inscripcion->pagos->count() > 0)
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscripcion->pagos->sortByDesc('fecha_pago') as $pagoItem)
                                    <tr>
                                        <td>
                                            <strong style="color: var(--gray-800);">{{ $pagoItem->fecha_pago->format('d/m/Y') }}</strong>
                                            <br><small class="text-muted">{{ $pagoItem->fecha_pago->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span style="color: var(--success); font-weight: 800; font-size: 1.1em;">
                                                ${{ number_format($pagoItem->monto_abonado, 0, '.', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: var(--gray-200); color: var(--gray-800); padding: 6px 12px; border-radius: 6px;">
                                                {{ $pagoItem->metodoPago->nombre ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $pagoItem->estado->color ?? '#6c757d' }}; color: white; padding: 6px 12px; border-radius: 6px;">
                                                {{ $pagoItem->estado->nombre ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.pagos.show', $pagoItem) }}" class="btn btn-sm btn-custom btn-custom-outline-dark" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.pagos.edit', $pagoItem) }}" class="btn btn-sm btn-custom btn-custom-outline-dark" title="Editar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert-custom {{ $pendiente > 0 ? 'warning' : 'success' }} mt-3">
                    <i class="fas {{ $pendiente > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}" style="font-size: 1.2em;"></i>
                    <div>
                        <strong>Total Pagado:</strong> ${{ number_format($pagos, 0, '.', '.') }} de ${{ number_format($total, 0, '.', '.') }}
                        @if($pendiente > 0)
                            — <strong>Pendiente:</strong> ${{ number_format($pendiente, 0, '.', '.') }}
                        @else
                            — <strong>¡Completamente Pagado!</strong>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert-custom warning">
                    <i class="fas fa-info-circle" style="font-size: 1.2em;"></i>
                    <div>
                        No hay pagos registrados para esta inscripción.
                        <a href="{{ route('admin.pagos.create', ['inscripcion_id' => $inscripcion->id]) }}" class="btn btn-sm btn-custom btn-custom-success ml-3">
                            <i class="fas fa-plus-circle"></i> Registrar Primer Pago
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- HISTORIAL DE MEMBRESÍAS -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-history"></i> Historial de Membresías</h5>
            
            @php
                $historicoMembresias = $inscripcion->cliente ? $inscripcion->cliente->inscripciones()
                    ->with('membresia')
                    ->orderByDesc('fecha_inicio')
                    ->get() : collect([]);
            @endphp

            @if($historicoMembresias->count() > 0)
                <div class="membership-timeline">
                    @foreach($historicoMembresias as $index => $insc)
                        <div class="timeline-item {{ $insc->id === $inscripcion->id ? 'current' : 'past' }}">
                            <div class="timeline-header">
                                <div class="timeline-title">
                                    <i class="fas fa-dumbbell"></i> {{ $insc->membresia?->nombre ?? 'Sin membresía' }}
                                </div>
                                <span class="timeline-badge {{ $insc->id === $inscripcion->id ? 'current' : 'past' }}">
                                    @if($insc->id === $inscripcion->id)
                                        <i class="fas fa-star"></i> Actual
                                    @else
                                        Anterior
                                    @endif
                                </span>
                            </div>
                            <div class="timeline-dates">
                                <i class="fas fa-calendar-alt"></i>
                                {{ $insc->fecha_inicio->format('d/m/Y') }} 
                                <i class="fas fa-arrow-right mx-1"></i> 
                                {{ $insc->fecha_vencimiento->format('d/m/Y') }}
                            </div>
                            <div class="timeline-price">
                                ${{ number_format($insc->precio_final ?? $insc->precio_base, 0, '.', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="alert-custom info mt-3">
                    <i class="fas fa-info-circle" style="font-size: 1.2em;"></i>
                    <div>
                        <strong>Total de Membresías:</strong> {{ $historicoMembresias->count() }} registradas para este cliente
                    </div>
                </div>
            @else
                <div class="alert-custom warning">
                    <i class="fas fa-exclamation-triangle" style="font-size: 1.2em;"></i>
                    <div>No hay historial de membresías disponible.</div>
                </div>
            @endif
        </div>
    </div>
@stop
