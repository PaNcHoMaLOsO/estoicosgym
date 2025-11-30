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

    /* ===== CARD STYLING ===== */
    .card-primary .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .card-info .card-header {
        background: linear-gradient(135deg, var(--info) 0%, #3451d4 100%);
    }

    .card-success .card-header {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
    }

    .card-header h3 {
        color: white;
    }

    /* ===== INFO BOX ===== */
    .info-box-icon.bg-accent {
        background: var(--accent) !important;
    }

    .info-box-icon.bg-success-custom {
        background: var(--success) !important;
    }

    .info-box-icon.bg-info-custom {
        background: var(--info) !important;
    }

    /* ===== BUTTONS ===== */
    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 8px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-warning {
        background: var(--warning);
        border-color: var(--warning);
        color: white;
    }

    /* ===== ALERT STYLING ===== */
    .alert {
        border-radius: 12px;
        border: none;
    }

    .alert-success {
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
    }

    .alert-success .close {
        color: white;
    }

    /* ===== HELPER CLASSES ===== */
    .text-accent {
        color: var(--accent) !important;
    }

    .precio-grande {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--success);
    }
</style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-credit-card text-accent"></i> {{ $membresia->nombre }}
            </h1>
            <small class="text-muted">Detalles de la membresía</small>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.membresias.edit', $membresia) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.membresias.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-check-circle"></i> Éxito
            </h5>
            {{ session('success') }}
        </div>
    @endif

    <!-- Información Principal -->
    <div class="card card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Información General
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-accent">
                            <i class="fas fa-tag"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Nombre</span>
                            <span class="info-box-number">{{ $membresia->nombre }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info-custom">
                            <i class="fas fa-calendar-days"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Duración</span>
                            <span class="info-box-number">{{ $membresia->duracion_dias }} días</span>
                            @if ($membresia->duracion_meses > 0)
                                <small class="text-muted">({{ $membresia->duracion_meses }} meses)</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    @php
                        $precioActual = $membresia->precios->where('activo', true)->first() ?? $membresia->precios->last();
                    @endphp
                    <div class="info-box">
                        <span class="info-box-icon bg-success-custom">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Precio Actual</span>
                            <span class="info-box-number precio-grande">
                                @if ($precioActual)
                                    ${{ number_format($precioActual->precio_normal, 0, ',', '.') }}
                                @else
                                    Sin precio
                                @endif
                            </span>
                            @if ($precioActual && $precioActual->precio_convenio)
                                <small class="text-info">
                                    <i class="fas fa-handshake"></i> Con convenio: ${{ number_format($precioActual->precio_convenio, 0, ',', '.') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $membresia->activo ? 'bg-success-custom' : 'bg-secondary' }}">
                            <i class="fas fa-toggle-{{ $membresia->activo ? 'on' : 'off' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                @if ($membresia->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-align-left text-accent"></i> Descripción</label>
                        <div class="border rounded p-3 bg-light">
                            @if ($membresia->descripcion)
                                {{ $membresia->descripcion }}
                            @else
                                <span class="text-muted">Sin descripción</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <small class="d-block text-muted mt-2">
                        <i class="fas fa-calendar"></i> Creado: {{ $membresia->created_at->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-muted mt-2">
                        <i class="fas fa-sync"></i> Actualizado: {{ $membresia->updated_at->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Precios -->
    <div class="card card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Historial de Precios
            </h3>
        </div>
        <div class="card-body">
            @if ($membresia->precios->count())
                <div class="timeline">
                    @foreach ($membresia->precios->sortByDesc('fecha_vigencia_desde') as $precio)
                        <div class="time-label">
                            <span class="bg-{{ $precio->activo ? 'success' : 'secondary' }}">
                                {{ $precio->fecha_vigencia_desde->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign bg-{{ $precio->activo ? 'success' : 'gray' }}"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">
                                    <strong>${{ number_format($precio->precio_normal, 0, '.', '.') }}</strong>
                                    @if ($precio->activo)
                                        <span class="badge badge-success ml-2">Vigente</span>
                                    @endif
                                </h3>
                                @if ($precio->fecha_vigencia_hasta)
                                    <div class="timeline-body">
                                        <small>Hasta: {{ $precio->fecha_vigencia_hasta->format('d/m/Y') }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> Sin historial de precios
                </p>
            @endif
        </div>
    </div>

    <!-- Historial de Cambios de Precios -->
    @if ($historialPrecios->count() || $historialPrecios->total() > 0)
        <div class="card card-warning mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Historial de Cambios de Precios
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 15%"><i class="fas fa-calendar"></i> Fecha/Hora</th>
                                <th style="width: 12%"><i class="fas fa-user"></i> Usuario</th>
                                <th style="width: 12%"><i class="fas fa-arrow-left"></i> Anterior</th>
                                <th style="width: 12%"><i class="fas fa-arrow-right"></i> Nuevo</th>
                                <th style="width: 12%"><i class="fas fa-exchange-alt"></i> Cambio</th>
                                <th style="width: 37%"><i class="fas fa-comment"></i> Razón del Cambio</th>
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
                                        <span class="badge badge-info">{{ $cambio->usuario_cambio }}</span>
                                    </td>
                                    <td>
                                        <small class="font-weight-bold">
                                            ${{ number_format($cambio->precio_anterior, 0, '.', '.') }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="font-weight-bold">
                                            ${{ number_format($cambio->precio_nuevo, 0, '.', '.') }}
                                        </small>
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
                                                <strong>{{ $cambio->razon_cambio }}</strong>
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

                <!-- Pagination -->
                @if ($historialPrecios->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $historialPrecios->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Inscripciones de esta Membresía -->
    @if ($membresia->inscripciones->count())
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Inscripciones ({{ $membresia->inscripciones->count() }})
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th><i class="fas fa-user"></i> Cliente</th>
                                <th><i class="fas fa-calendar-plus"></i> Fecha Inicio</th>
                                <th><i class="fas fa-calendar-check"></i> Fecha Vencimiento</th>
                                <th><i class="fas fa-dollar-sign"></i> Precio</th>
                                <th><i class="fas fa-info-circle"></i> Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($membresia->inscripciones->take(10) as $inscripcion)
                                <tr>
                                    <td><small>{{ $inscripcion->uuid }}</small></td>
                                    <td>
                                        <a href="{{ route('admin.clientes.show', $inscripcion->cliente) }}">
                                            {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                        </a>
                                    </td>
                                    <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td>${{ number_format($inscripcion->precio_base, 0, '.', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ in_array($inscripcion->estado->nombre, ['Activa']) ? 'success' : (in_array($inscripcion->estado->nombre, ['Pausada']) ? 'warning' : 'secondary') }}">
                                            {{ $inscripcion->estado->nombre }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($membresia->inscripciones->count() > 10)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> Mostrando 10 de {{ $membresia->inscripciones->count() }} inscripciones
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No hay inscripciones para esta membresía
        </div>
    @endif
@stop
