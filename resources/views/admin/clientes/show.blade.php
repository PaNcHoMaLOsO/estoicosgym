@extends('adminlte::page')

@section('title', 'Detalle Cliente - EstóicosGym')

@section('content_header')
@stop

@section('content')
@php
    // Obtener inscripción activa
    $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first();
    $inscripcionVencida = $cliente->inscripciones->where('id_estado', 102)->first();
    $ultimaInscripcion = $inscripcionActiva ?? $inscripcionVencida ?? $cliente->inscripciones->first();
@endphp

<div class="cliente-detail-container">
    <!-- Hero Header -->
    <div class="cliente-hero">
        <a href="{{ route('admin.clientes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="hero-main">
            <div class="hero-avatar">
                {{ strtoupper(substr($cliente->nombres, 0, 1) . substr($cliente->apellido_paterno, 0, 1)) }}
            </div>
            <div class="hero-info">
                <h1>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</h1>
                <div class="hero-meta">
                    <span class="meta-item">
                        <i class="fas fa-id-card"></i> {{ $cliente->run_pasaporte ?? 'Sin RUT' }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-phone"></i> {{ $cliente->celular }}
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-envelope"></i> {{ $cliente->email }}
                    </span>
                </div>
                <div class="hero-badges">
                    <span class="badge-status {{ $cliente->activo ? 'active' : 'inactive' }}">
                        <i class="fas {{ $cliente->activo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    @if($cliente->convenio)
                    <span class="badge-convenio">
                        <i class="fas fa-handshake"></i> {{ $cliente->convenio->nombre }}
                    </span>
                    @endif
                    @if($inscripcionActiva)
                    <span class="badge-membresia">
                        <i class="fas fa-dumbbell"></i> {{ $inscripcionActiva->membresia->nombre ?? 'Membresía' }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn-hero btn-edit">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.inscripciones.create', ['cliente' => $cliente->id]) }}" class="btn-hero btn-new">
                <i class="fas fa-plus"></i> Nueva Inscripción
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon icon-primary">
                <i class="fas fa-dumbbell"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $cliente->inscripciones->count() }}</span>
                <span class="stat-label">Inscripciones</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-success">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">${{ number_format($cliente->pagos->sum('monto_abonado'), 0, ',', '.') }}</span>
                <span class="stat-label">Total Pagado</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-info">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $cliente->pagos->count() }}</span>
                <span class="stat-label">Pagos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-warning">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-data">
                <span class="stat-number">{{ $cliente->created_at->format('d/m/Y') }}</span>
                <span class="stat-label">Desde</span>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Left Column -->
        <div class="left-column">
            <!-- Inscripción Activa -->
            @if($ultimaInscripcion)
            <div class="card-active-membership">
                <div class="card-header-accent">
                    <div class="header-title">
                        <i class="fas fa-star"></i>
                        <span>{{ $inscripcionActiva ? 'Membresía Activa' : 'Última Membresía' }}</span>
                    </div>
                    @php
                        $estadoInsc = match($ultimaInscripcion->id_estado) {
                            100 => ['class' => 'activo', 'text' => 'Activa'],
                            101 => ['class' => 'pausado', 'text' => 'Pausada'],
                            102 => ['class' => 'vencido', 'text' => 'Vencida'],
                            103 => ['class' => 'cancelado', 'text' => 'Cancelada'],
                            default => ['class' => 'otro', 'text' => 'Otro']
                        };
                    @endphp
                    <span class="status-pill {{ $estadoInsc['class'] }}">{{ $estadoInsc['text'] }}</span>
                </div>
                <div class="card-body-membership">
                    <div class="membership-name">
                        <i class="fas fa-dumbbell"></i>
                        {{ $ultimaInscripcion->membresia->nombre ?? 'N/A' }}
                    </div>
                    <div class="membership-dates">
                        <div class="date-item">
                            <span class="date-label">Inicio</span>
                            <span class="date-value">{{ $ultimaInscripcion->fecha_inicio->format('d/m/Y') }}</span>
                        </div>
                        <div class="date-separator">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="date-item">
                            <span class="date-label">Vencimiento</span>
                            <span class="date-value {{ $ultimaInscripcion->id_estado == 102 ? 'expired' : '' }}">
                                {{ $ultimaInscripcion->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="membership-price">
                        <div class="price-info">
                            <span class="price-label">Precio</span>
                            <span class="price-value">${{ number_format($ultimaInscripcion->precio_final ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-info">
                            <span class="price-label">Pagado</span>
                            <span class="price-value paid">${{ number_format($ultimaInscripcion->pagos->sum('monto_abonado'), 0, ',', '.') }}</span>
                        </div>
                        @php
                            $pendiente = ($ultimaInscripcion->precio_final ?? 0) - $ultimaInscripcion->pagos->sum('monto_abonado');
                        @endphp
                        @if($pendiente > 0)
                        <div class="price-info">
                            <span class="price-label">Pendiente</span>
                            <span class="price-value pending">${{ number_format($pendiente, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.inscripciones.show', $ultimaInscripcion) }}" class="btn-view-inscription">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                </div>
            </div>
            @else
            <div class="card-no-membership">
                <i class="fas fa-dumbbell"></i>
                <h4>Sin Membresía</h4>
                <p>Este cliente no tiene inscripciones registradas</p>
                <a href="{{ route('admin.inscripciones.create', ['cliente' => $cliente->id]) }}" class="btn-create-inscription">
                    <i class="fas fa-plus"></i> Crear Inscripción
                </a>
            </div>
            @endif

            <!-- Datos de Contacto -->
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-address-book"></i>
                    <h3>Datos de Contacto</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <span class="info-label">Email</span>
                            <a href="mailto:{{ $cliente->email }}" class="info-value link">{{ $cliente->email }}</a>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Celular</span>
                            <a href="tel:{{ $cliente->celular }}" class="info-value link">{{ $cliente->celular }}</a>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-birthday-cake"></i></div>
                        <div class="info-content">
                            <span class="info-label">Fecha Nacimiento</span>
                            <span class="info-value">{{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : 'No registrada' }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Dirección</span>
                            <span class="info-value">{{ $cliente->direccion ?? 'No registrada' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacto de Emergencia -->
            @if($cliente->contacto_emergencia)
            <div class="info-card emergency">
                <div class="info-header">
                    <i class="fas fa-ambulance"></i>
                    <h3>Contacto de Emergencia</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="info-content">
                            <span class="info-label">Nombre</span>
                            <span class="info-value">{{ $cliente->contacto_emergencia }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Teléfono</span>
                            <a href="tel:{{ $cliente->telefono_emergencia }}" class="info-value link">{{ $cliente->telefono_emergencia }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($cliente->observaciones)
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-sticky-note"></i>
                    <h3>Observaciones</h3>
                </div>
                <div class="info-body">
                    <p class="observaciones-text">{{ $cliente->observaciones }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- Historial de Inscripciones -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-title">
                        <i class="fas fa-history"></i>
                        <h3>Historial de Inscripciones</h3>
                    </div>
                    <span class="badge-count">{{ $cliente->inscripciones->count() }}</span>
                </div>
                <div class="table-body">
                    @if($cliente->inscripciones->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Membresía</th>
                                    <th>Período</th>
                                    <th>Estado</th>
                                    <th>Monto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->inscripciones as $inscripcion)
                                @php
                                    $estInsc = match($inscripcion->id_estado) {
                                        100 => ['class' => 'activo', 'text' => 'Activa'],
                                        101 => ['class' => 'pausado', 'text' => 'Pausada'],
                                        102 => ['class' => 'vencido', 'text' => 'Vencida'],
                                        103 => ['class' => 'cancelado', 'text' => 'Cancelada'],
                                        104 => ['class' => 'suspendido', 'text' => 'Suspendida'],
                                        default => ['class' => 'otro', 'text' => 'Otro']
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="date-range">
                                            {{ $inscripcion->fecha_inicio->format('d/m/y') }} - {{ $inscripcion->fecha_vencimiento->format('d/m/y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $estInsc['class'] }}">{{ $estInsc['text'] }}</span>
                                    </td>
                                    <td>
                                        <span class="monto">${{ number_format($inscripcion->precio_final ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="fas fa-dumbbell"></i>
                        <p>Sin inscripciones</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Pagos -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-title">
                        <i class="fas fa-credit-card"></i>
                        <h3>Historial de Pagos</h3>
                    </div>
                    <span class="badge-count">{{ $cliente->pagos->count() }}</span>
                </div>
                <div class="table-body">
                    @if($cliente->pagos->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->pagos->take(10) as $pago)
                                @php
                                    $estPago = match($pago->id_estado) {
                                        200 => ['class' => 'pendiente', 'text' => 'Pendiente'],
                                        201 => ['class' => 'pagado', 'text' => 'Pagado'],
                                        202 => ['class' => 'parcial', 'text' => 'Parcial'],
                                        default => ['class' => 'otro', 'text' => 'Otro']
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                    <td>
                                        @if($pago->inscripcion)
                                            {{ $pago->inscripcion->membresia->nombre ?? 'Membresía' }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td><span class="monto">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</span></td>
                                    <td>{{ $pago->metodoPago?->nombre ?? '-' }}</td>
                                    <td><span class="status-badge {{ $estPago['class'] }}">{{ $estPago['text'] }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($cliente->pagos->count() > 10)
                    <div class="table-footer">
                        <span>Mostrando 10 de {{ $cliente->pagos->count() }} pagos</span>
                    </div>
                    @endif
                    @else
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>Sin pagos registrados</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    @php
        $estadoActiva = \App\Models\Estado::where('codigo', 100)->first();
        $estadoPendiente = \App\Models\Estado::where('codigo', 200)->first();
        $puedoDesactivar = !$cliente->inscripciones()->where('id_estado', $estadoActiva?->id)->exists() && 
                          !$cliente->pagos()->where('id_estado', $estadoPendiente?->id)->exists();
    @endphp
    <div class="danger-zone">
        <div class="danger-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Zona de Peligro</h3>
        </div>
        <div class="danger-content">
            <div class="danger-info">
                <h4>Desactivar Cliente</h4>
                <p>El cliente no será eliminado, solo se ocultará de la lista activa.</p>
                @if(!$puedoDesactivar)
                <p class="warning-text"><i class="fas fa-lock"></i> No disponible: tiene inscripciones activas o pagos pendientes.</p>
                @endif
            </div>
            <button type="button" class="btn-danger-action" id="btnDesactivar" {{ !$puedoDesactivar ? 'disabled' : '' }}>
                <i class="fas fa-user-slash"></i> Desactivar
            </button>
        </div>
    </div>
</div>

<form id="formDesactivar" action="{{ route('admin.clientes.deactivate', $cliente) }}" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>
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
    }

    .content-wrapper { background: var(--bg-light) !important; }

    .cliente-detail-container {
        padding: 20px;
        width: 100%;
    }

    /* Hero Header */
    .cliente-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        position: relative;
    }

    .btn-back {
        width: 42px;
        height: 42px;
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.25);
        color: #fff;
        transform: translateX(-3px);
    }

    .hero-main {
        display: flex;
        align-items: center;
        gap: 20px;
        flex: 1;
    }

    .hero-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .hero-info h1 {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 12px;
    }

    .meta-item {
        color: rgba(255,255,255,0.8);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .badge-status, .badge-convenio, .badge-membresia {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .badge-status.active {
        background: var(--success);
        color: #fff;
    }

    .badge-status.inactive {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }

    .badge-convenio {
        background: var(--warning);
        color: #000;
    }

    .badge-membresia {
        background: var(--info);
        color: #fff;
    }

    .hero-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-hero {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-hero.btn-edit {
        background: var(--accent);
        color: #fff;
    }

    .btn-hero.btn-edit:hover {
        background: #d63655;
        color: #fff;
    }

    .btn-hero.btn-new {
        background: rgba(255,255,255,0.15);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .btn-hero.btn-new:hover {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i { font-size: 22px; color: #fff; }
    .icon-primary { background: linear-gradient(135deg, var(--primary), var(--primary-light)); }
    .icon-success { background: linear-gradient(135deg, var(--success), #00a67e); }
    .icon-info { background: linear-gradient(135deg, var(--info), #3a53d6); }
    .icon-warning { background: linear-gradient(135deg, var(--warning), #d69200); }

    .stat-data { display: flex; flex-direction: column; }
    .stat-number { font-size: 22px; font-weight: 700; color: var(--text-primary); }
    .stat-label { font-size: 13px; color: var(--text-secondary); }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
    }

    /* Active Membership Card */
    .card-active-membership {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .card-header-accent {
        background: linear-gradient(135deg, var(--accent) 0%, #d63655 100%);
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-accent .header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 600;
    }

    .status-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-pill.activo { background: var(--success); color: #fff; }
    .status-pill.vencido { background: var(--danger); color: #fff; }
    .status-pill.pausado { background: var(--warning); color: #000; }
    .status-pill.cancelado { background: #6c757d; color: #fff; }

    .card-body-membership { padding: 20px; }

    .membership-name {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .membership-name i { color: var(--accent); }

    .membership-dates {
        display: flex;
        align-items: center;
        gap: 16px;
        background: var(--bg-light);
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 16px;
    }

    .date-item { flex: 1; text-align: center; }
    .date-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 4px; }
    .date-value { font-size: 16px; font-weight: 600; color: var(--text-primary); }
    .date-value.expired { color: var(--danger); }
    .date-separator { color: var(--text-secondary); }

    .membership-price {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
    }

    .price-info {
        flex: 1;
        text-align: center;
        padding: 12px;
        background: var(--bg-light);
        border-radius: 10px;
    }

    .price-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 4px; }
    .price-value { font-size: 18px; font-weight: 700; color: var(--text-primary); }
    .price-value.paid { color: var(--success); }
    .price-value.pending { color: var(--warning); }

    .btn-view-inscription {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        background: var(--primary);
        color: #fff;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-view-inscription:hover {
        background: var(--primary-light);
        color: #fff;
    }

    /* No Membership Card */
    .card-no-membership {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .card-no-membership i {
        font-size: 48px;
        color: var(--border-color);
        margin-bottom: 16px;
    }

    .card-no-membership h4 {
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .card-no-membership p {
        color: var(--text-secondary);
        margin-bottom: 20px;
    }

    .btn-create-inscription {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--accent);
        color: #fff;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-create-inscription:hover {
        background: #d63655;
        color: #fff;
    }

    /* Info Card */
    .info-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .info-card.emergency .info-header {
        background: linear-gradient(135deg, var(--warning) 0%, #d69200 100%);
    }

    .info-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-header i { color: #fff; font-size: 18px; }
    .info-header h3 { color: #fff; font-size: 15px; font-weight: 600; margin: 0; }

    .info-body { padding: 16px 20px; }

    .info-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child { border-bottom: none; }

    .info-icon {
        width: 36px;
        height: 36px;
        background: rgba(26,26,46,0.08);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-icon i { color: var(--primary); font-size: 14px; }

    .info-content { flex: 1; }
    .info-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 2px; }
    .info-value { font-size: 14px; font-weight: 600; color: var(--text-primary); }
    .info-value.link { color: var(--info); text-decoration: none; }
    .info-value.link:hover { text-decoration: underline; }

    .observaciones-text { color: var(--text-primary); line-height: 1.6; margin: 0; }

    /* Table Card */
    .table-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .table-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header .header-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-header i { color: #fff; font-size: 18px; }
    .table-header h3 { color: #fff; font-size: 15px; font-weight: 600; margin: 0; }

    .badge-count {
        background: rgba(255,255,255,0.2);
        color: #fff;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .table-body { padding: 0; }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead { background: rgba(26,26,46,0.04); }

    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
    }

    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    .data-table tr:last-child td { border-bottom: none; }

    .date-range { color: var(--text-secondary); font-size: 13px; }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.activo, .status-badge.pagado { background: rgba(0,191,142,0.15); color: var(--success); }
    .status-badge.vencido { background: rgba(220,53,69,0.15); color: var(--danger); }
    .status-badge.pausado, .status-badge.parcial { background: rgba(240,165,0,0.15); color: var(--warning); }
    .status-badge.pendiente { background: rgba(67,97,238,0.15); color: var(--info); }
    .status-badge.cancelado { background: rgba(108,117,125,0.15); color: var(--text-secondary); }

    .monto { color: var(--success); font-weight: 600; }

    .btn-action {
        width: 32px;
        height: 32px;
        background: rgba(67,97,238,0.1);
        color: var(--info);
        border: none;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-action:hover {
        background: var(--info);
        color: #fff;
    }

    .table-footer {
        padding: 12px 16px;
        text-align: center;
        background: var(--bg-light);
        color: var(--text-secondary);
        font-size: 13px;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state i { font-size: 32px; opacity: 0.3; margin-bottom: 10px; }
    .empty-state p { margin: 0; }

    /* Danger Zone */
    .danger-zone {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow);
        border: 2px solid rgba(233,69,96,0.2);
        margin-top: 24px;
    }

    .danger-header {
        background: rgba(233,69,96,0.1);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .danger-header i { color: var(--accent); font-size: 18px; }
    .danger-header h3 { color: var(--accent); font-size: 15px; font-weight: 600; margin: 0; }

    .danger-content {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .danger-info h4 { color: var(--text-primary); font-size: 15px; margin: 0 0 6px 0; }
    .danger-info p { color: var(--text-secondary); font-size: 13px; margin: 0; }
    .danger-info .warning-text { color: var(--warning); margin-top: 8px; }

    .btn-danger-action {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-danger-action:hover:not(:disabled) { background: #d63655; }
    .btn-danger-action:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Responsive */
    @media (max-width: 1200px) {
        .content-grid { grid-template-columns: 1fr; }
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .cliente-hero { flex-direction: column; }
        .hero-main { flex-direction: column; text-align: center; }
        .hero-meta { justify-content: center; }
        .hero-badges { justify-content: center; }
        .hero-actions { flex-direction: row; width: 100%; }
        .btn-hero { flex: 1; justify-content: center; }
        .stats-row { grid-template-columns: 1fr; }
        .danger-content { flex-direction: column; text-align: center; }
        .btn-danger-action { width: 100%; justify-content: center; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#btnDesactivar').on('click', function() {
        Swal.fire({
            title: '¿Desactivar cliente?',
            html: `
                <p>Estás a punto de desactivar a <strong>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>.</p>
                <ul style="text-align:left; margin-top:16px; color:#6c757d;">
                    <li>El cliente <strong>NO</strong> será eliminado</li>
                    <li>Su historial se conservará</li>
                    <li>No aparecerá en la lista de activos</li>
                    <li>Podrá ser reactivado después</li>
                </ul>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e94560',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-user-slash"></i> Sí, desactivar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formDesactivar').submit();
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        title: '¡Éxito!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonColor: '#1a1a2e',
        timer: 3000,
        timerProgressBar: true
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: 'Error',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonColor: '#e94560'
    });
    @endif
});
</script>
@stop
