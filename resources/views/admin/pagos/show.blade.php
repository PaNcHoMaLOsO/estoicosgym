@extends('adminlte::page')

@section('title', 'Detalle de Pago - EstóicosGym')

@section('meta_tags')
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
@stop

@section('content')
<div class="pago-detail-container">
    <!-- Hero Header -->
    <div class="detail-hero">
        <div class="hero-content">
            <a href="{{ route('admin.pagos.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="hero-info">
                <div class="hero-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="hero-text">
                    <h1>Pago #{{ $pago->id }}</h1>
                    <p>
                        <i class="fas fa-calendar-alt"></i> 
                        Registrado el {{ $pago->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="hero-status">
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
            <span class="estado-badge-hero {{ $estadoClass }}">
                <i class="fas fa-{{ $estadoClass === 'pagado' ? 'check-circle' : ($estadoClass === 'parcial' ? 'hourglass-half' : 'times-circle') }}"></i>
                {{ $pago->estado->nombre ?? 'Sin estado' }}
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $porcentajePagado = $pago->monto_total > 0 
            ? min(100, ($pago->monto_abonado / $pago->monto_total) * 100) 
            : 0;
    @endphp
    <div class="stats-row">
        <div class="stat-card stat-total">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <span class="stat-label">Monto Total</span>
                <span class="stat-number">${{ number_format($pago->monto_total, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="stat-card stat-abonado">
            <div class="stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <span class="stat-label">Abonado</span>
                <span class="stat-number">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="stat-card stat-pendiente">
            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-info">
                <span class="stat-label">Pendiente</span>
                <span class="stat-number">${{ number_format($pago->monto_pendiente, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="stat-card stat-progress">
            <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-info">
                <span class="stat-label">Progreso</span>
                <span class="stat-number">{{ number_format($porcentajePagado, 0) }}%</span>
            </div>
            <div class="mini-progress">
                <div class="mini-progress-fill {{ $porcentajePagado >= 100 ? 'complete' : 'partial' }}" 
                     style="width: {{ $porcentajePagado }}%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Column - Info Cards -->
        <div class="main-column">
            <!-- Información del Pago -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Información del Pago</span>
                </div>
                <div class="info-card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar"></i> Fecha de Pago</span>
                            <span class="info-value">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'No registrada' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-tag"></i> Tipo de Pago</span>
                            <span class="info-value tipo-badge {{ $pago->tipo_pago ?? 'normal' }}">
                                {{ ucfirst($pago->tipo_pago ?? 'Normal') }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-credit-card"></i> Método de Pago</span>
                            @if($pago->tipo_pago === 'mixto')
                                <div class="metodos-mixtos">
                                    <span class="metodo-tag">
                                        <i class="fas fa-money-bill-wave"></i>
                                        {{ $pago->metodoPago->nombre ?? '-' }}: ${{ number_format($pago->monto_metodo1, 0, ',', '.') }}
                                    </span>
                                    <span class="metodo-tag">
                                        <i class="fas fa-university"></i>
                                        {{ $pago->metodoPago2->nombre ?? '-' }}: ${{ number_format($pago->monto_metodo2, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <span class="metodo-tag">
                                    @php
                                        $metodoNombre = strtolower($pago->metodoPago->nombre ?? '');
                                    @endphp
                                    <i class="fas fa-{{ str_contains($metodoNombre, 'efectivo') ? 'money-bill-wave' : (str_contains($metodoNombre, 'tarjeta') ? 'credit-card' : 'university') }}"></i>
                                    {{ $pago->metodoPago->nombre ?? 'No especificado' }}
                                </span>
                            @endif
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-barcode"></i> Referencia</span>
                            <span class="info-value">{{ $pago->referencia_pago ?? 'Sin referencia' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-sticky-note"></i>
                    <span>Observaciones</span>
                </div>
                <div class="info-card-body">
                    @if($pago->observaciones)
                        <div class="observaciones-content">
                            <p>{{ $pago->observaciones }}</p>
                        </div>
                    @else
                        <div class="empty-obs">
                            <i class="fas fa-comment-slash"></i>
                            <span>No hay observaciones registradas</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información de la Inscripción -->
            @if($pago->inscripcion)
            <div class="info-card inscripcion-card">
                <div class="info-card-header">
                    <i class="fas fa-id-card"></i>
                    <span>Inscripción Asociada</span>
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn-ver-mas" title="Ver detalles">
                        <i class="fas fa-external-link-alt"></i> Ver
                    </a>
                </div>
                <div class="info-card-body">
                    <!-- Membresía -->
                    <div class="inscripcion-membresia">
                        <div class="membresia-badge">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="membresia-datos">
                            <span class="membresia-nombre">{{ $pago->inscripcion->membresia->nombre ?? 'Sin membresía' }}</span>
                            <span class="inscripcion-numero">Inscripción #{{ $pago->inscripcion->id }}</span>
                        </div>
                        @php
                            $estadoInscripcion = strtolower($pago->inscripcion->estado->nombre ?? '');
                            $estadoClass = 'default';
                            if (str_contains($estadoInscripcion, 'activ')) $estadoClass = 'activa';
                            elseif (str_contains($estadoInscripcion, 'vencid')) $estadoClass = 'vencida';
                            elseif (str_contains($estadoInscripcion, 'pausa')) $estadoClass = 'pausada';
                            elseif (str_contains($estadoInscripcion, 'cancel')) $estadoClass = 'cancelada';
                            elseif (str_contains($estadoInscripcion, 'traspas')) $estadoClass = 'traspasada';
                        @endphp
                        <span class="estado-tag {{ $estadoClass }}">{{ $pago->inscripcion->estado->nombre ?? 'N/A' }}</span>
                    </div>
                    
                    <!-- Fechas y Días -->
                    <div class="inscripcion-fechas">
                        <div class="fecha-item">
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <small>Inicio</small>
                                <strong>{{ $pago->inscripcion->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="fecha-separador">→</div>
                        <div class="fecha-item">
                            <i class="fas fa-calendar-times"></i>
                            <div>
                                <small>Vencimiento</small>
                                <strong>{{ $pago->inscripcion->fecha_vencimiento?->format('d/m/Y') ?? 'N/A' }}</strong>
                            </div>
                        </div>
                        @php
                            $diasRestantes = $pago->inscripcion->fecha_vencimiento 
                                ? (int) now()->diffInDays($pago->inscripcion->fecha_vencimiento, false) 
                                : 0;
                            $diasClass = $diasRestantes < 0 ? 'vencido' : ($diasRestantes <= 7 ? 'proximo' : 'ok');
                        @endphp
                        <div class="dias-restantes {{ $diasClass }}">
                            <span class="dias-num">{{ abs($diasRestantes) }}</span>
                            <span class="dias-text">{{ $diasRestantes < 0 ? 'días vencida' : 'días restantes' }}</span>
                        </div>
                    </div>
                    
                    <!-- Resumen Financiero de la Inscripción -->
                    @php
                        $montoTotalInscripcion = $pago->inscripcion->precio_final ?? 0;
                        $totalPagadoInscripcion = $pago->inscripcion->pagos->sum('monto_abonado') ?? 0;
                        $saldoPendiente = max(0, $montoTotalInscripcion - $totalPagadoInscripcion);
                        $porcentajePagado = $montoTotalInscripcion > 0 ? min(100, ($totalPagadoInscripcion / $montoTotalInscripcion) * 100) : 0;
                    @endphp
                    <div class="financiero-inscripcion">
                        <div class="financiero-header">
                            <i class="fas fa-chart-pie"></i>
                            <span>Resumen Financiero</span>
                        </div>
                        <div class="financiero-cards">
                            <div class="financiero-card total-card">
                                <div class="financiero-card-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="financiero-card-content">
                                    <span class="financiero-card-label">Monto Total</span>
                                    <span class="financiero-card-value">${{ number_format($montoTotalInscripcion, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="financiero-card pagado-card">
                                <div class="financiero-card-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="financiero-card-content">
                                    <span class="financiero-card-label">Total Pagado</span>
                                    <span class="financiero-card-value">${{ number_format($totalPagadoInscripcion, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="financiero-card pendiente-card {{ $saldoPendiente > 0 ? 'tiene-deuda' : 'sin-deuda' }}">
                                <div class="financiero-card-icon">
                                    <i class="fas fa-{{ $saldoPendiente > 0 ? 'exclamation-triangle' : 'check-double' }}"></i>
                                </div>
                                <div class="financiero-card-content">
                                    <span class="financiero-card-label">Saldo Pendiente</span>
                                    <span class="financiero-card-value">${{ number_format($saldoPendiente, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="financiero-progress">
                            <div class="progress-info">
                                <span>Progreso de pago</span>
                                <span class="progress-percent">{{ number_format($porcentajePagado, 0) }}%</span>
                            </div>
                            <div class="progress-bar-full">
                                <div class="progress-bar-fill {{ $porcentajePagado >= 100 ? 'complete' : ($porcentajePagado >= 50 ? 'medio' : 'bajo') }}" style="width: {{ $porcentajePagado }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Client Card -->
        <div class="side-column">
            @php
                // Cliente actual de la inscripción (dueño actual de la membresía)
                $clienteActual = $pago->inscripcion?->cliente;
                // Cliente original que pagó (puede ser diferente si hubo traspaso)
                $clienteOriginal = $pago->cliente;
                // Detectar si es un traspaso (cliente actual diferente al que pagó)
                $esTraspaso = $clienteActual && $clienteOriginal && $clienteActual->id !== $clienteOriginal->id;
                // Usar cliente actual si existe, sino el original
                $cliente = $clienteActual ?? $clienteOriginal;
            @endphp
            @if($cliente)
                @php
                    $iniciales = strtoupper(substr($cliente->nombres ?? 'N', 0, 1) . substr($cliente->apellido_paterno ?? 'A', 0, 1));
                @endphp
                <div class="client-card {{ $esTraspaso ? 'traspaso' : '' }}">
                    @if($esTraspaso)
                    <div class="traspaso-banner">
                        <i class="fas fa-exchange-alt"></i>
                        Membresía Traspasada
                    </div>
                    @endif
                    <div class="client-avatar {{ $esTraspaso ? 'traspaso' : '' }}">{{ $iniciales }}</div>
                    <h3 class="client-name">{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</h3>
                    <p class="client-email">{{ $cliente->email ?? 'Sin correo' }}</p>
                    <span class="client-role">{{ $esTraspaso ? 'Dueño Actual de Membresía' : 'Cliente' }}</span>
                    <div class="client-details">
                        @if($cliente->run_pasaporte)
                        <div class="detail-item">
                            <i class="fas fa-id-card"></i>
                            <span>{{ $cliente->run_pasaporte }}</span>
                        </div>
                        @endif
                        @if($cliente->celular)
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>{{ $cliente->celular }}</span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn-ver-cliente">
                        <i class="fas fa-user"></i>
                        Ver Perfil Completo
                    </a>
                    
                    @if($esTraspaso && $clienteOriginal)
                    <div class="cliente-original-info">
                        <h5><i class="fas fa-history"></i> Pagado originalmente por:</h5>
                        <div class="cliente-original-card">
                            <span class="nombre">{{ $clienteOriginal->nombres }} {{ $clienteOriginal->apellido_paterno }}</span>
                            <a href="{{ route('admin.clientes.show', $clienteOriginal) }}" class="btn-ver-original">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Acciones Rápidas -->
            <div class="quick-actions">
                <h4><i class="fas fa-bolt"></i> Acciones</h4>
                <a href="{{ route('admin.pagos.edit', $pago) }}" class="action-btn edit">
                    <i class="fas fa-edit"></i>
                    Editar Pago
                </a>
                <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete">
                        <i class="fas fa-trash"></i>
                        Eliminar Pago
                    </button>
                </form>
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
        --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 25px -5px rgb(0 0 0 / 0.15);
    }

    .content-wrapper { background: var(--gray-50) !important; }
    .content { padding: 0 !important; }

    .pago-detail-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ===== HERO HEADER ===== */
    .detail-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    .detail-hero::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 150px;
        height: 150px;
        background: var(--success);
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
    .btn-back {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.1);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        transform: translateX(-3px);
    }
    .hero-info { display: flex; align-items: center; gap: 14px; }
    .hero-icon {
        width: 50px;
        height: 50px;
        background: rgba(0, 191, 142, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4em;
        color: var(--success);
    }
    .hero-text h1 {
        color: white;
        font-size: 1.4em;
        font-weight: 700;
        margin: 0;
    }
    .hero-text p {
        color: rgba(255,255,255,0.7);
        margin: 4px 0 0;
        font-size: 0.85em;
    }
    .hero-status { position: relative; z-index: 1; }
    .estado-badge-hero {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.85em;
        text-transform: uppercase;
    }
    .estado-badge-hero.pagado { background: rgba(0, 191, 142, 0.2); color: #38ef7d; border: 1px solid rgba(0, 191, 142, 0.4); }
    .estado-badge-hero.parcial { background: rgba(240, 165, 0, 0.2); color: #ffc107; border: 1px solid rgba(240, 165, 0, 0.4); }
    .estado-badge-hero.pendiente { background: rgba(233, 69, 96, 0.2); color: #ff6b6b; border: 1px solid rgba(233, 69, 96, 0.4); }
    .estado-badge-hero.vencido { background: rgba(233, 69, 96, 0.2); color: #ff6b6b; border: 1px solid rgba(233, 69, 96, 0.4); }

    /* ===== STATS ROW ===== */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow);
        border-left: 4px solid var(--gray-300);
        position: relative;
        overflow: hidden;
    }
    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1em;
    }
    .stat-card .stat-info { display: flex; flex-direction: column; }
    .stat-card .stat-label { font-size: 0.7em; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    .stat-card .stat-number { font-size: 1.3em; font-weight: 800; color: var(--gray-800); }
    
    .stat-total { border-left-color: var(--info); }
    .stat-total .stat-icon { background: rgba(67, 97, 238, 0.2); color: #2541b2; }
    .stat-total .stat-icon i { color: #2541b2 !important; }
    .stat-total .stat-number { color: var(--info); }
    
    .stat-abonado { border-left-color: var(--success); }
    .stat-abonado .stat-icon { background: rgba(0, 191, 142, 0.2); color: #00896b; }
    .stat-abonado .stat-icon i { color: #00896b !important; }
    .stat-abonado .stat-number { color: var(--success); }
    
    .stat-pendiente { border-left-color: var(--accent); }
    .stat-pendiente .stat-icon { background: rgba(233, 69, 96, 0.2); color: #c9304c; }
    .stat-pendiente .stat-icon i { color: #c9304c !important; }
    .stat-pendiente .stat-number { color: var(--accent); }
    
    .stat-progress { border-left-color: var(--warning); }
    .stat-progress .stat-icon { background: rgba(240, 165, 0, 0.2); color: #c78500; }
    .stat-progress .stat-icon i { color: #c78500 !important; }
    .stat-progress .stat-number { color: var(--warning); }
    .mini-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gray-200);
    }
    .mini-progress-fill { height: 100%; transition: width 0.5s ease; }
    .mini-progress-fill.complete { background: var(--success); }
    .mini-progress-fill.partial { background: var(--warning); }

    /* ===== CONTENT GRID ===== */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
    }

    /* ===== INFO CARDS ===== */
    .info-card {
        background: white;
        border-radius: 14px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .info-card-header {
        background: var(--primary);
        color: white;
        padding: 14px 18px;
        font-weight: 600;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-card-header i { color: var(--accent); }
    .info-card-body { padding: 18px; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .info-label {
        font-size: 0.75em;
        color: var(--gray-500);
        font-weight: 600;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .info-label i { color: var(--info); font-size: 0.9em; }
    .info-value { font-weight: 600; color: var(--gray-800); font-size: 0.95em; }

    .tipo-badge {
        display: inline-flex;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.85em;
        background: var(--gray-100);
    }
    .tipo-badge.mixto { background: rgba(67, 97, 238, 0.1); color: var(--info); }
    .tipo-badge.completo { background: rgba(0, 191, 142, 0.1); color: var(--success); }

    .metodos-mixtos { display: flex; flex-direction: column; gap: 6px; }
    .metodo-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(67, 97, 238, 0.08);
        color: var(--info);
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 600;
    }

    /* ===== OBSERVACIONES ===== */
    .observaciones-content {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(233, 69, 96, 0.03) 100%);
        border-left: 4px solid var(--info);
        border-radius: 8px;
        padding: 14px 16px;
    }
    .observaciones-content p { margin: 0; color: var(--gray-700); line-height: 1.6; }
    .empty-obs {
        text-align: center;
        padding: 20px;
        color: var(--gray-500);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .empty-obs i { font-size: 1.5em; opacity: 0.5; }

    /* ===== INSCRIPCIÓN CARD ===== */
    .inscripcion-card .info-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .btn-ver-mas {
        font-size: 0.75em;
        padding: 4px 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 6px;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }
    .btn-ver-mas:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }
    
    .inscripcion-membresia {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--gray-200);
        margin-bottom: 12px;
    }
    .membresia-badge {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1em;
    }
    .membresia-datos {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .membresia-datos .membresia-nombre {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1em;
    }
    .membresia-datos .inscripcion-numero {
        font-size: 0.75em;
        color: var(--gray-500);
    }
    .estado-tag {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7em;
        font-weight: 700;
        text-transform: uppercase;
    }
    .estado-tag.activa { background: rgba(0, 191, 142, 0.15); color: var(--success); }
    .estado-tag.vencida { background: rgba(233, 69, 96, 0.15); color: var(--accent); }
    .estado-tag.pausada { background: rgba(240, 165, 0, 0.15); color: var(--warning); }
    .estado-tag.cancelada { background: rgba(108, 117, 125, 0.15); color: var(--gray-600); }
    .estado-tag.traspasada { background: rgba(124, 58, 237, 0.15); color: #7c3aed; }
    .estado-tag.default { background: var(--gray-100); color: var(--gray-600); }
    
    .inscripcion-fechas {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: var(--gray-50);
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 12px;
    }
    .fecha-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fecha-item i {
        color: var(--primary);
        font-size: 1em;
    }
    .fecha-item div {
        display: flex;
        flex-direction: column;
    }
    .fecha-item small {
        font-size: 0.65em;
        color: var(--gray-500);
        text-transform: uppercase;
    }
    .fecha-item strong {
        font-size: 0.85em;
        color: var(--gray-800);
    }
    .fecha-separador {
        color: var(--gray-400);
        font-size: 1.2em;
    }
    .dias-restantes {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 12px;
        border-radius: 8px;
        min-width: 70px;
    }
    .dias-restantes.ok { background: rgba(0, 191, 142, 0.1); }
    .dias-restantes.proximo { background: rgba(240, 165, 0, 0.1); }
    .dias-restantes.vencido { background: rgba(233, 69, 96, 0.1); }
    .dias-restantes .dias-num {
        font-size: 1.3em;
        font-weight: 800;
    }
    .dias-restantes.ok .dias-num { color: var(--success); }
    .dias-restantes.proximo .dias-num { color: var(--warning); }
    .dias-restantes.vencido .dias-num { color: var(--accent); }
    .dias-restantes .dias-text {
        font-size: 0.6em;
        text-transform: uppercase;
        color: var(--gray-600);
    }
    
    .inscripcion-resumen {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 10px 0;
        border-top: 1px dashed var(--gray-200);
    }
    .resumen-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
    .resumen-item span {
        font-size: 0.65em;
        color: var(--gray-500);
        text-transform: uppercase;
    }
    .resumen-item strong {
        font-size: 0.95em;
        color: var(--gray-700);
    }
    .resumen-item.descuento strong { color: var(--success); }
    .resumen-item.total strong { color: var(--primary); font-size: 1.1em; }
    
    /* ===== FINANCIERO INSCRIPCIÓN ===== */
    .financiero-inscripcion {
        margin-top: 16px;
        padding: 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        border: 1px solid var(--gray-200);
    }
    .financiero-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px dashed var(--gray-300);
    }
    .financiero-header i {
        color: var(--primary);
        font-size: 1em;
    }
    .financiero-header span {
        font-weight: 700;
        font-size: 0.85em;
        color: var(--gray-700);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .financiero-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }
    .financiero-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px 10px;
        border-radius: 10px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .financiero-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 10px 10px 0 0;
    }
    .financiero-card.total-card {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.08) 0%, rgba(67, 97, 238, 0.04) 100%);
        border: 1px solid rgba(67, 97, 238, 0.2);
    }
    .financiero-card.total-card::before { background: var(--info); }
    .financiero-card.total-card .financiero-card-icon { color: var(--info); }
    .financiero-card.total-card .financiero-card-value { color: var(--info); }
    
    .financiero-card.pagado-card {
        background: linear-gradient(135deg, rgba(0, 191, 142, 0.08) 0%, rgba(0, 191, 142, 0.04) 100%);
        border: 1px solid rgba(0, 191, 142, 0.2);
    }
    .financiero-card.pagado-card::before { background: var(--success); }
    .financiero-card.pagado-card .financiero-card-icon { color: var(--success); }
    .financiero-card.pagado-card .financiero-card-value { color: var(--success); }
    
    .financiero-card.pendiente-card.tiene-deuda {
        background: linear-gradient(135deg, rgba(233, 69, 96, 0.08) 0%, rgba(233, 69, 96, 0.04) 100%);
        border: 1px solid rgba(233, 69, 96, 0.2);
    }
    .financiero-card.pendiente-card.tiene-deuda::before { background: var(--accent); }
    .financiero-card.pendiente-card.tiene-deuda .financiero-card-icon { color: var(--accent); }
    .financiero-card.pendiente-card.tiene-deuda .financiero-card-value { color: var(--accent); }
    
    .financiero-card.pendiente-card.sin-deuda {
        background: linear-gradient(135deg, rgba(0, 191, 142, 0.08) 0%, rgba(0, 191, 142, 0.04) 100%);
        border: 1px solid rgba(0, 191, 142, 0.2);
    }
    .financiero-card.pendiente-card.sin-deuda::before { background: var(--success); }
    .financiero-card.pendiente-card.sin-deuda .financiero-card-icon { color: var(--success); }
    .financiero-card.pendiente-card.sin-deuda .financiero-card-value { color: var(--success); }
    
    .financiero-card-icon {
        font-size: 1.3em;
        margin-bottom: 6px;
    }
    .financiero-card-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .financiero-card-label {
        font-size: 0.65em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        font-weight: 600;
    }
    .financiero-card-value {
        font-size: 1.1em;
        font-weight: 800;
    }
    
    .financiero-progress {
        padding-top: 10px;
        border-top: 1px dashed var(--gray-300);
    }
    .progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .progress-info span {
        font-size: 0.7em;
        color: var(--gray-500);
        font-weight: 600;
        text-transform: uppercase;
    }
    .progress-percent {
        font-weight: 800 !important;
        color: var(--gray-700) !important;
    }
    .progress-bar-full {
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
    .progress-bar-fill.complete { background: linear-gradient(90deg, var(--success), #2dd4bf); }
    .progress-bar-fill.medio { background: linear-gradient(90deg, var(--warning), #fbbf24); }
    .progress-bar-fill.bajo { background: linear-gradient(90deg, var(--accent), #f472b6); }
    
    .btn-ver-inscripcion {
        width: 36px;
        height: 36px;
        background: var(--primary);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    .btn-ver-inscripcion:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        color: white;
    }

    /* ===== CLIENT CARD ===== */
    .client-card {
        background: white;
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 24px;
        text-align: center;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .client-card.traspaso {
        border: 2px solid #7c3aed;
    }
    .traspaso-banner {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        color: white;
        padding: 6px 10px;
        font-size: 0.75em;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .client-card.traspaso .client-avatar {
        margin-top: 20px;
    }
    .client-avatar {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5em;
        font-weight: 800;
        margin: 0 auto 14px;
        box-shadow: 0 6px 20px rgba(26, 26, 46, 0.3);
    }
    .client-avatar.traspaso {
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
    }
    .client-name { font-size: 1.1em; font-weight: 700; color: var(--gray-800); margin: 0 0 4px; }
    .client-email { font-size: 0.85em; color: var(--gray-500); margin: 0 0 6px; }
    .client-role {
        display: inline-block;
        background: var(--gray-100);
        color: var(--gray-600);
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.7em;
        font-weight: 600;
        margin-bottom: 12px;
    }
    .client-details { margin-bottom: 16px; }
    .detail-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 6px 0;
        font-size: 0.85em;
        color: var(--gray-600);
    }
    .detail-item i { color: var(--info); }
    .btn-ver-cliente {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px;
        background: var(--gray-100);
        color: var(--primary);
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }
    .btn-ver-cliente:hover {
        background: var(--primary);
        color: white;
    }
    
    /* Cliente Original Info (Traspaso) */
    .cliente-original-info {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--gray-200);
    }
    .cliente-original-info h5 {
        font-size: 0.75em;
        color: var(--gray-500);
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .cliente-original-info h5 i { color: #7c3aed; }
    .cliente-original-card {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: var(--gray-50);
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
    }
    .cliente-original-card .nombre {
        font-size: 0.85em;
        font-weight: 600;
        color: var(--gray-700);
    }
    .btn-ver-original {
        width: 28px;
        height: 28px;
        background: #7c3aed;
        color: white;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75em;
        transition: all 0.3s ease;
    }
    .btn-ver-original:hover {
        background: #6d28d9;
        color: white;
        transform: scale(1.05);
    }

    /* ===== QUICK ACTIONS ===== */
    .quick-actions {
        background: white;
        border-radius: 14px;
        box-shadow: var(--shadow);
        padding: 18px;
    }
    .quick-actions h4 {
        margin: 0 0 14px;
        font-size: 0.9em;
        color: var(--gray-700);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .quick-actions h4 i { color: var(--warning); }
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9em;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-bottom: 10px;
    }
    .action-btn:last-child { margin-bottom: 0; }
    .action-btn.edit { background: var(--warning); color: white; }
    .action-btn.edit:hover { background: #d99400; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(240, 165, 0, 0.3); color: white; }
    .action-btn.delete { background: var(--accent); color: white; }
    .action-btn.delete:hover { background: #d63650; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(233, 69, 96, 0.3); }
    .delete-form { width: 100%; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .content-grid { grid-template-columns: 1fr; }
        .side-column { order: -1; }
        .client-card { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; }
        .client-avatar { margin: 0; }
        .client-details { display: flex; gap: 16px; margin: 0; }
    }
    @media (max-width: 768px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
        .detail-hero { flex-direction: column; gap: 16px; text-align: center; }
        .hero-content { flex-direction: column; }
        .info-grid { grid-template-columns: 1fr; }
        .inscripcion-fechas { flex-wrap: wrap; justify-content: center; }
        .fecha-separador { display: none; }
        .dias-restantes { width: 100%; margin-top: 8px; }
        .financiero-cards { grid-template-columns: 1fr; gap: 10px; }
        .financiero-card { flex-direction: row; justify-content: flex-start; gap: 12px; padding: 12px 14px; }
        .financiero-card-icon { margin-bottom: 0; }
        .financiero-card-content { align-items: flex-start; text-align: left; }
    }
    @media (max-width: 576px) {
        .stats-row { grid-template-columns: 1fr; }
        .pago-detail-container { padding: 12px; }
        .inscripcion-membresia { flex-wrap: wrap; }
        .inscripcion-resumen { flex-wrap: wrap; gap: 16px; }
        .financiero-inscripcion { padding: 12px; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Confirmación de eliminación
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: '¿Eliminar pago?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e94560',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Animación de barras de progreso
    setTimeout(function() {
        $('.mini-progress-fill').each(function() {
            $(this).css('width', $(this).data('width') || $(this).attr('style').match(/width:\s*(\d+)/)?.[1] + '%');
        });
    }, 100);
});
</script>
@stop
