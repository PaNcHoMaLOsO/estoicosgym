@extends('adminlte::page')

@section('title', 'Detalle de Pago - EstóicosGym')

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
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-card-body {
        padding: 22px;
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .info-item:last-child { border-bottom: none; }
    .info-item-label {
        color: var(--gray-600);
        font-size: 0.9em;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-item-label i {
        color: var(--info);
        font-size: 0.9em;
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
    .estado-badge.pagado {
        background: rgba(0, 191, 142, 0.15);
        color: var(--success);
        border: 2px solid var(--success);
    }
    .estado-badge.pendiente {
        background: rgba(240, 165, 0, 0.15);
        color: var(--warning);
        border: 2px solid var(--warning);
    }
    .estado-badge.parcial {
        background: rgba(67, 97, 238, 0.15);
        color: var(--info);
        border: 2px solid var(--info);
    }
    .estado-badge.vencido, .estado-badge.cancelado {
        background: rgba(233, 69, 96, 0.15);
        color: var(--accent);
        border: 2px solid var(--accent);
    }

    /* PROGRESS CONTAINER */
    .progress-container {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .progress-title {
        font-weight: 700;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .progress-title i {
        color: var(--success);
    }
    .progress-percentage {
        font-size: 1.3em;
        font-weight: 800;
        color: var(--success);
    }
    .progress-custom {
        height: 16px;
        background: var(--gray-200);
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-custom .progress-bar {
        background: linear-gradient(90deg, var(--success) 0%, var(--success-dark) 100%);
        border-radius: 10px;
        transition: width 0.6s ease;
    }
    .progress-custom .progress-bar.warning {
        background: linear-gradient(90deg, var(--warning) 0%, #e09000 100%);
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
    .progress-stat-value.success { color: var(--success); }
    .progress-stat-value.danger { color: var(--accent); }
    .progress-stat-label {
        font-size: 0.75em;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* CLIENT CARD */
    .client-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        padding: 25px;
        text-align: center;
    }
    .client-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2em;
        font-weight: 800;
        margin: 0 auto 15px;
        box-shadow: 0 8px 25px rgba(26, 26, 46, 0.3);
    }
    .client-name {
        font-size: 1.3em;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 5px;
    }
    .client-email {
        color: var(--gray-600);
        font-size: 0.9em;
        margin-bottom: 15px;
    }
    .client-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--gray-100);
        color: var(--info);
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .client-link:hover {
        background: var(--info);
        color: white;
        transform: translateY(-2px);
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
        color: var(--gray-800);
    }
    .modern-card-header i {
        color: var(--accent);
    }
    .modern-card-body {
        padding: 25px;
    }

    /* OBSERVACIONES */
    .observaciones-box {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(233, 69, 96, 0.03) 100%);
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid var(--info);
    }
    .observaciones-box p {
        margin: 0;
        color: var(--gray-800);
        line-height: 1.6;
    }
    .no-observaciones {
        color: var(--gray-600);
        font-style: italic;
    }

    /* BUTTONS */
    .btn-custom {
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
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
        box-shadow: 0 8px 20px rgba(26, 26, 46, 0.3);
    }
    .btn-custom-warning {
        background: var(--warning);
        color: white;
        border: none;
    }
    .btn-custom-warning:hover {
        background: #d99500;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(240, 165, 0, 0.3);
    }
    .btn-custom-danger {
        background: var(--accent);
        color: white;
        border: none;
    }
    .btn-custom-danger:hover {
        background: #d73a55;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(233, 69, 96, 0.3);
    }
    .btn-custom-outline {
        background: transparent;
        border: 2px solid var(--gray-200);
        color: var(--gray-600);
    }
    .btn-custom-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* ACTIONS BAR */
    .actions-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* METODO PAGO BADGE */
    .metodo-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9em;
    }
    .metodo-badge.efectivo {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }
    .metodo-badge.tarjeta, .metodo-badge.transferencia {
        background: rgba(67, 97, 238, 0.12);
        color: var(--info);
    }
    .metodo-badge.default {
        background: rgba(108, 117, 125, 0.12);
        color: var(--gray-600);
    }

    /* INSCRIPCION LINK */
    .inscripcion-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .inscripcion-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26, 26, 46, 0.3);
        color: white;
    }

    /* ALERT */
    .alert-custom {
        border-radius: 12px;
        padding: 16px 20px;
        border: none;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .alert-custom.success {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }
    .alert-custom.warning {
        background: rgba(240, 165, 0, 0.12);
        color: var(--warning);
    }
    .alert-custom.danger {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }
</style>
@stop

@section('content')
<div class="container-fluid py-4">
    
    {{-- HERO HEADER --}}
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-receipt me-2"></i>
                        Pago #{{ substr($pago->uuid, 0, 8) }}
                    </h1>
                    <p class="hero-subtitle mb-0">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Registrado el {{ $pago->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    @php
                        $estadoNombre = strtolower($pago->estado->nombre ?? '');
                        $estadoClass = 'pendiente';
                        if (str_contains($estadoNombre, 'pagado') || str_contains($estadoNombre, 'completo')) {
                            $estadoClass = 'pagado';
                        } elseif (str_contains($estadoNombre, 'parcial')) {
                            $estadoClass = 'parcial';
                        } elseif (str_contains($estadoNombre, 'vencid') || str_contains($estadoNombre, 'cancelad')) {
                            $estadoClass = 'vencido';
                        }
                    @endphp
                    <span class="estado-badge {{ $estadoClass }}">
                        <i class="fas fa-circle" style="font-size: 0.5em;"></i>
                        {{ $pago->estado->nombre ?? 'Sin estado' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-label">Monto Total</div>
                <div class="stat-value">${{ number_format($pago->monto_total, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-label">Monto Abonado</div>
                <div class="stat-value info">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card {{ $pago->monto_pendiente > 0 ? 'warning' : 'success' }}">
                <div class="stat-icon {{ $pago->monto_pendiente > 0 ? 'warning' : 'success' }}">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-label">Monto Pendiente</div>
                <div class="stat-value {{ $pago->monto_pendiente > 0 ? 'warning' : 'success' }}">
                    ${{ number_format($pago->monto_pendiente, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card danger">
                <div class="stat-icon danger">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-label">Fecha de Pago</div>
                <div class="stat-value" style="font-size: 1.3em;">
                    {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'No registrada' }}
                </div>
            </div>
        </div>
    </div>

    {{-- PROGRESS BAR --}}
    @php
        $porcentajePagado = $pago->monto_total > 0 
            ? min(100, ($pago->monto_abonado / $pago->monto_total) * 100) 
            : 0;
    @endphp
    <div class="progress-container">
        <div class="progress-header">
            <div class="progress-title">
                <i class="fas fa-chart-line"></i>
                Progreso del Pago
            </div>
            <div class="progress-percentage">{{ number_format($porcentajePagado, 1) }}%</div>
        </div>
        <div class="progress progress-custom">
            <div class="progress-bar {{ $porcentajePagado < 100 ? 'warning' : '' }}" 
                 role="progressbar" 
                 style="width: {{ $porcentajePagado }}%"></div>
        </div>
        <div class="progress-stats">
            <div class="progress-stat">
                <div class="progress-stat-value success">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</div>
                <div class="progress-stat-label">Pagado</div>
            </div>
            <div class="progress-stat">
                <div class="progress-stat-value danger">${{ number_format($pago->monto_pendiente, 0, ',', '.') }}</div>
                <div class="progress-stat-label">Pendiente</div>
            </div>
            <div class="progress-stat">
                <div class="progress-stat-value">${{ number_format($pago->monto_total, 0, ',', '.') }}</div>
                <div class="progress-stat-label">Total</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- INFO PAGO --}}
        <div class="col-md-8">
            <div class="info-card mb-4">
                <div class="info-card-header">
                    <i class="fas fa-info-circle"></i>
                    Información del Pago
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-hashtag"></i>
                            UUID
                        </span>
                        <span class="info-item-value" style="font-family: monospace; font-size: 0.85em;">
                            {{ $pago->uuid }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-tag"></i>
                            Tipo de Pago
                        </span>
                        <span class="info-item-value">
                            {{ ucfirst($pago->tipo_pago ?? 'Normal') }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-credit-card"></i>
                            Método de Pago
                        </span>
                        @php
                            $metodoNombre = strtolower($pago->metodoPago->nombre ?? '');
                            $metodoClass = 'default';
                            if (str_contains($metodoNombre, 'efectivo')) {
                                $metodoClass = 'efectivo';
                            } elseif (str_contains($metodoNombre, 'tarjeta') || str_contains($metodoNombre, 'transfer')) {
                                $metodoClass = 'transferencia';
                            }
                        @endphp
                        <span class="metodo-badge {{ $metodoClass }}">
                            <i class="fas fa-{{ $metodoClass == 'efectivo' ? 'money-bill-wave' : ($metodoClass == 'transferencia' ? 'university' : 'wallet') }}"></i>
                            {{ $pago->metodoPago->nombre ?? 'No especificado' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-barcode"></i>
                            Referencia
                        </span>
                        <span class="info-item-value">
                            {{ $pago->referencia_pago ?? 'Sin referencia' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-file-contract"></i>
                            Inscripción Asociada
                        </span>
                        @if($pago->inscripcion)
                            <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="inscripcion-link">
                                <i class="fas fa-external-link-alt"></i>
                                {{ $pago->inscripcion->membresia->nombre ?? 'Inscripción' }} - 
                                {{ $pago->inscripcion->cliente->user->name ?? 'Cliente' }}
                            </a>
                        @else
                            <span class="info-item-value text-muted">Sin inscripción asociada</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">
                            <i class="fas fa-clock"></i>
                            Última Actualización
                        </span>
                        <span class="info-item-value">
                            {{ $pago->updated_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- OBSERVACIONES --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="fas fa-sticky-note"></i>
                    Observaciones
                </div>
                <div class="modern-card-body">
                    @if($pago->observaciones)
                        <div class="observaciones-box">
                            <p>{{ $pago->observaciones }}</p>
                        </div>
                    @else
                        <p class="no-observaciones">No hay observaciones registradas para este pago.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- CLIENTE INFO --}}
        <div class="col-md-4">
            @if($pago->inscripcion && $pago->inscripcion->cliente)
                @php
                    $cliente = $pago->inscripcion->cliente;
                    $user = $cliente->user;
                    $iniciales = '';
                    if ($user && $user->name) {
                        $palabras = explode(' ', $user->name);
                        foreach($palabras as $palabra) {
                            $iniciales .= strtoupper(substr($palabra, 0, 1));
                        }
                        $iniciales = substr($iniciales, 0, 2);
                    }
                @endphp
                <div class="client-card mb-4">
                    <div class="client-avatar">
                        {{ $iniciales ?: 'CL' }}
                    </div>
                    <h3 class="client-name">{{ $user->name ?? 'Cliente' }}</h3>
                    <p class="client-email">{{ $user->email ?? 'Sin correo' }}</p>
                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="client-link">
                        <i class="fas fa-user"></i>
                        Ver Perfil del Cliente
                    </a>
                </div>
            @endif

            {{-- MEMBRESÍA INFO --}}
            @if($pago->inscripcion && $pago->inscripcion->membresia)
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-id-card"></i>
                        Membresía Asociada
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <span class="info-item-label">
                                <i class="fas fa-tag"></i>
                                Tipo
                            </span>
                            <span class="info-item-value">
                                {{ $pago->inscripcion->membresia->nombre }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">
                                <i class="fas fa-calendar-check"></i>
                                Vigencia
                            </span>
                            <span class="info-item-value">
                                {{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }} - 
                                {{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">
                                <i class="fas fa-check-circle"></i>
                                Estado
                            </span>
                            <span class="info-item-value">
                                {{ $pago->inscripcion->estado->nombre ?? 'Sin estado' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ACTIONS BAR --}}
    <div class="actions-bar mt-4">
        <div>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-custom btn-custom-outline">
                <i class="fas fa-arrow-left"></i>
                Volver a la Lista
            </a>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-custom btn-custom-warning">
                <i class="fas fa-edit"></i>
                Editar Pago
            </a>
            <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('¿Estás seguro de eliminar este pago?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-custom btn-custom-danger">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </form>
        </div>
    </div>

</div>
@stop

@section('js')
<script>
    // Animación de la barra de progreso al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            const width = progressBar.style.width;
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = width;
            }, 100);
        }
    });
</script>
@stop
