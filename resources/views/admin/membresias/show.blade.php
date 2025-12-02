@extends('adminlte::page')

@section('title', $membresia->nombre . ' - EstóicosGym')

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

    /* ===== HERO HEADER ===== */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.3);
    }

    .page-header h1 {
        color: white;
        margin: 0;
        font-weight: 700;
    }

    .page-header h1 i {
        color: var(--accent);
    }

    .page-header small {
        color: rgba(255,255,255,0.7);
    }

    /* ===== STAT CARDS ===== */
    .stat-card {
        border: 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-radius: 16px;
        background: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 5px solid var(--info);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }

    .stat-card.success { border-left-color: var(--success); }
    .stat-card.warning { border-left-color: var(--warning); }
    .stat-card.danger { border-left-color: var(--accent); }

    .stat-number {
        font-size: 2.2em;
        font-weight: 800;
        color: var(--info);
    }

    .stat-card.success .stat-number { color: var(--success); }
    .stat-card.warning .stat-number { color: var(--warning); }
    .stat-card.danger .stat-number { color: var(--accent); }

    .stat-label {
        color: var(--gray-600);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .stat-icon {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 2.5rem;
        opacity: 0.1;
        color: var(--primary);
    }

    /* ===== CARD STYLING ===== */
    .card {
        border: 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: var(--primary);
        color: white;
        border-radius: 16px 16px 0 0 !important;
        border-bottom: none;
        padding: 1rem 1.25rem;
    }

    .card-header .card-title {
        color: white;
        font-weight: 600;
    }

    .card-header.bg-info-custom {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
    }

    .card-header.bg-success-custom {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    }

    .card-header.bg-warning-custom {
        background: linear-gradient(135deg, var(--warning) 0%, #d99200 100%);
    }

    /* ===== INFO BOX ===== */
    .info-box-custom {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--info);
    }

    .info-box-custom.accent { border-left-color: var(--accent); }
    .info-box-custom.success { border-left-color: var(--success); }
    .info-box-custom.warning { border-left-color: var(--warning); }

    .info-box-icon-custom {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
    }

    .info-box-icon-custom.bg-accent { background: var(--accent); }
    .info-box-icon-custom.bg-success { background: var(--success); }
    .info-box-icon-custom.bg-info { background: var(--info); }
    .info-box-icon-custom.bg-warning { background: var(--warning); }

    .info-box-content-custom {
        flex: 1;
    }

    .info-box-text-custom {
        color: var(--gray-600);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }

    .info-box-number-custom {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    /* ===== STATUS BADGES ===== */
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-active {
        background: rgba(0, 191, 142, 0.15);
        color: var(--success);
        border: 2px solid var(--success);
    }

    .status-inactive {
        background: rgba(108, 117, 125, 0.15);
        color: var(--gray-600);
        border: 2px solid var(--gray-600);
    }

    /* ===== PRECIO GRANDE ===== */
    .precio-grande {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--success);
    }

    .precio-convenio-badge {
        background: rgba(67, 97, 238, 0.15);
        color: var(--info);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
    }

    /* ===== DESCRIPCION BOX ===== */
    .descripcion-box {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 1.25rem;
        border-left: 4px solid var(--accent);
    }

    /* ===== TIMELINE ===== */
    .timeline-custom {
        position: relative;
        padding-left: 30px;
    }

    .timeline-custom::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gray-200);
    }

    .timeline-item-custom {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item-custom::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--info);
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .timeline-item-custom.active::before {
        background: var(--success);
    }

    .timeline-date {
        font-size: 0.8rem;
        color: var(--gray-600);
        margin-bottom: 0.25rem;
    }

    .timeline-content {
        background: white;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .timeline-price {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--gray-800);
    }

    /* ===== TABLE STYLING ===== */
    .table thead {
        background: var(--primary);
    }

    .table thead th {
        color: white;
        font-weight: 600;
        padding: 0.85rem 0.75rem;
        border: 0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--gray-200);
    }

    .table tbody tr:hover {
        background-color: var(--gray-100);
    }

    /* ===== BUTTONS ===== */
    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 10px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-edit {
        background: var(--warning);
        border-color: var(--warning);
        color: white;
    }

    .btn-edit:hover {
        background: #d99200;
        color: white;
    }

    .btn-back {
        background: var(--gray-200);
        border-color: var(--gray-200);
        color: var(--gray-800);
    }

    .btn-back:hover {
        background: var(--gray-600);
        color: white;
    }

    /* ===== ALERT STYLING ===== */
    .alert {
        border-radius: 12px;
        border: none;
    }

    .alert-success-custom {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        border-left: 5px solid #007a5e;
    }

    .alert-info-custom {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
        color: white;
        border-left: 5px solid #2942b8;
    }

    /* ===== METADATA ===== */
    .metadata {
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .metadata i {
        width: 20px;
        color: var(--accent);
    }

    /* ===== ACTION BUTTONS ===== */
    .action-buttons {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.4rem 0.65rem;
        font-size: 0.8rem;
        border-radius: 8px;
        border: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-view {
        background: var(--primary);
        color: white;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--gray-200);
        margin-bottom: 1rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            padding: 15px 20px;
        }
        
        .precio-grande {
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 1.6em;
        }
    }
</style>
@stop

@section('content_header')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1>
                    <i class="fas fa-credit-card"></i> {{ $membresia->nombre }}
                </h1>
                <small>Detalles de la membresía</small>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.membresias.edit', $membresia) }}" class="btn btn-edit mr-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('admin.membresias.index') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success-custom alert-dismissible fade show shadow-lg" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ session('success') }}
        </div>
    @endif

    @php
        $precioActual = $membresia->precios->where('activo', true)->first() ?? $membresia->precios->last();
        $totalInscripciones = $membresia->inscripciones->count();
        $inscripcionesActivas = $membresia->inscripciones->filter(fn($i) => in_array($i->estado?->codigo, [101]))->count();
    @endphp

    <!-- TARJETAS DE RESUMEN -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign stat-icon"></i>
                    <div class="stat-number">
                        @if ($precioActual)
                            ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                        @else
                            $0
                        @endif
                    </div>
                    <div class="stat-label">Precio Actual</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt stat-icon"></i>
                    <div class="stat-number">{{ $membresia->duracion_dias }}</div>
                    <div class="stat-label">Días de Duración</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning">
                <div class="card-body text-center">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-number">{{ $totalInscripciones }}</div>
                    <div class="stat-label">Total Inscripciones</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card danger">
                <div class="card-body text-center">
                    <i class="fas fa-user-check stat-icon"></i>
                    <div class="stat-number">{{ $inscripcionesActivas }}</div>
                    <div class="stat-label">Inscripciones Activas</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- INFORMACIÓN PRINCIPAL -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información General
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box-custom accent">
                                <div class="info-box-icon-custom bg-accent">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="info-box-content-custom">
                                    <span class="info-box-text-custom">Nombre</span>
                                    <span class="info-box-number-custom">{{ $membresia->nombre }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box-custom">
                                <div class="info-box-icon-custom bg-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-box-content-custom">
                                    <span class="info-box-text-custom">Duración</span>
                                    <span class="info-box-number-custom">
                                        {{ $membresia->duracion_dias }} días
                                        @if ($membresia->duracion_meses > 0)
                                            <small class="text-muted">({{ $membresia->duracion_meses }} {{ $membresia->duracion_meses == 1 ? 'mes' : 'meses' }})</small>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box-custom success">
                                <div class="info-box-icon-custom bg-success">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="info-box-content-custom">
                                    <span class="info-box-text-custom">Precio Normal</span>
                                    <span class="info-box-number-custom" style="color: var(--success);">
                                        @if ($precioActual)
                                            ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                                        @else
                                            Sin precio
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box-custom warning">
                                <div class="info-box-icon-custom bg-warning">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div class="info-box-content-custom">
                                    <span class="info-box-text-custom">Precio Convenio</span>
                                    <span class="info-box-number-custom" style="color: var(--info);">
                                        @if ($precioActual && $precioActual->precio_convenio)
                                            ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }}
                                        @else
                                            No aplica
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <strong>Estado de la Membresía:</strong>
                                </div>
                                <div>
                                    @if ($membresia->activo)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i> Activa
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-pause-circle"></i> Inactiva
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if ($membresia->descripcion)
                        <div class="mt-4">
                            <label class="font-weight-bold text-muted text-uppercase" style="font-size: 0.8rem;">
                                <i class="fas fa-align-left" style="color: var(--accent);"></i> Descripción
                            </label>
                            <div class="descripcion-box">
                                {{ $membresia->descripcion }}
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="metadata">
                                <i class="fas fa-calendar-plus"></i> Creado: {{ $membresia->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="metadata">
                                <i class="fas fa-sync-alt"></i> Actualizado: {{ $membresia->updated_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HISTORIAL DE CAMBIOS DE PRECIOS -->
            @if ($historialPrecios->count() || $historialPrecios->total() > 0)
                <div class="card">
                    <div class="card-header bg-warning-custom">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> Historial de Cambios de Precios
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-user"></i> Usuario</th>
                                        <th><i class="fas fa-arrow-left"></i> Anterior</th>
                                        <th><i class="fas fa-arrow-right"></i> Nuevo</th>
                                        <th><i class="fas fa-exchange-alt"></i> Cambio</th>
                                        <th><i class="fas fa-comment"></i> Razón</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historialPrecios as $cambio)
                                        <tr>
                                            <td>
                                                <small>
                                                    <strong>{{ $cambio->created_at ? $cambio->created_at->format('d/m/Y') : 'N/A' }}</strong><br>
                                                    <span class="text-muted">{{ $cambio->created_at ? $cambio->created_at->format('H:i') : '' }}</span>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge" style="background: var(--info); color: white;">
                                                    {{ $cambio->usuario_cambio }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>${{ number_format($cambio->precio_anterior, 0, '.', '.') }}</strong>
                                            </td>
                                            <td>
                                                <strong>${{ number_format($cambio->precio_nuevo, 0, '.', '.') }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $diferencia = $cambio->precio_nuevo - $cambio->precio_anterior;
                                                    $clase = $diferencia > 0 ? 'danger' : ($diferencia < 0 ? 'success' : 'secondary');
                                                    $icono = $diferencia > 0 ? 'arrow-up' : ($diferencia < 0 ? 'arrow-down' : 'equals');
                                                @endphp
                                                <span class="badge badge-{{ $clase }}">
                                                    <i class="fas fa-{{ $icono }}"></i>
                                                    {{ $diferencia >= 0 ? '+' : '' }}${{ number_format($diferencia, 0, '.', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    @if ($cambio->razon_cambio)
                                                        {{ $cambio->razon_cambio }}
                                                    @else
                                                        <span class="text-muted">Sin especificar</span>
                                                    @endif
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($historialPrecios->hasPages())
                            <div class="card-footer">
                                <div class="d-flex justify-content-center">
                                    {{ $historialPrecios->withQueryString()->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- SIDEBAR -->
        <div class="col-md-4">
            <!-- Historial de Precios Timeline -->
            <div class="card">
                <div class="card-header bg-info-custom">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Historial de Precios
                    </h3>
                </div>
                <div class="card-body">
                    @if ($membresia->precios->count())
                        <div class="timeline-custom">
                            @foreach ($membresia->precios->sortByDesc('fecha_vigencia_desde') as $precio)
                                <div class="timeline-item-custom {{ $precio->activo ? 'active' : '' }}">
                                    <div class="timeline-date">
                                        {{ $precio->fecha_vigencia_desde->format('d/m/Y') }}
                                        @if ($precio->activo)
                                            <span class="badge badge-success ml-1">Vigente</span>
                                        @endif
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-price">
                                            ${{ number_format($precio->precio_normal, 0, '.', '.') }}
                                        </div>
                                        @if ($precio->fecha_vigencia_hasta)
                                            <small class="text-muted">
                                                Hasta: {{ $precio->fecha_vigencia_hasta->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <p>Sin historial de precios</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- INSCRIPCIONES -->
    @if ($membresia->inscripciones->count())
        <div class="card">
            <div class="card-header bg-success-custom">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Inscripciones ({{ $membresia->inscripciones->count() }})
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><i class="fas fa-user"></i> Cliente</th>
                                <th><i class="fas fa-calendar-plus"></i> Inicio</th>
                                <th><i class="fas fa-calendar-check"></i> Vencimiento</th>
                                <th><i class="fas fa-dollar-sign"></i> Precio</th>
                                <th><i class="fas fa-info-circle"></i> Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($membresia->inscripciones->take(10) as $inscripcion)
                                <tr>
                                    <td><small class="text-muted">{{ Str::limit($inscripcion->uuid, 8) }}</small></td>
                                    <td>
                                        <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}" class="font-weight-bold">
                                            {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                        </a>
                                    </td>
                                    <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td><strong style="color: var(--success);">${{ number_format($inscripcion->precio_base, 0, '.', '.') }}</strong></td>
                                    <td>
                                        @php
                                            $estadoClase = match($inscripcion->estado?->codigo ?? 0) {
                                                101 => 'success',
                                                102 => 'secondary',
                                                103 => 'danger',
                                                104 => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $estadoClase }}">
                                            {{ $inscripcion->estado?->nombre ?? 'Sin estado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" 
                                           class="btn-action btn-view" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($membresia->inscripciones->count() > 10)
                    <div class="card-footer">
                        <div class="alert alert-info-custom mb-0">
                            <i class="fas fa-info-circle"></i> Mostrando 10 de {{ $membresia->inscripciones->count() }} inscripciones
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h4>No hay inscripciones</h4>
                    <p class="text-muted">Esta membresía aún no tiene inscripciones asociadas</p>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
