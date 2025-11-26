@extends('adminlte::page')

@section('title', 'Detalles Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-receipt"></i> Detalles del Pago
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Información del Pago -->
    <div class="card card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-dollar-sign"></i> Información del Pago
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto Abonado</span>
                            <span class="info-box-number">${{ number_format($pago->monto_abonado, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha de Pago</span>
                            <span class="info-box-number">{{ $pago->fecha_pago->format('d/m/Y') }}</span>
                            <small class="text-muted">{{ $pago->fecha_pago->format('H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $pago->estado->activo ? 'bg-success' : 'bg-danger' }}">
                            <i class="fas fa-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->estado) !!}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-credit-card"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Método de Pago</span>
                            <span class="info-box-number">{{ $pago->metodoPago->nombre }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($pago->referencia_pago)
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-paperclip"></i> Referencia</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $pago->referencia_pago }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Información de la Inscripción -->
    <div class="card card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-check"></i> Información de la Inscripción
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-user"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cliente</span>
                            <span class="info-box-number">
                                <a href="{{ route('admin.clientes.show', $pago->inscripcion->cliente) }}">
                                    {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}
                                </a>
                            </span>
                            <small class="text-muted">{{ $pago->inscripcion->cliente->email }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado Inscripción</span>
                            <span class="info-box-number">
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($pago->inscripcion->estado) !!}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <a href="{{ route('admin.inscripciones.show', $pago->inscripcion) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Ver Inscripción #{{ $pago->inscripcion->uuid }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Inicio</span>
                            <span class="info-box-number">{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar-times"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Vencimiento</span>
                            <span class="info-box-number">{{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Pagos de la Inscripción -->
    <div class="card card-success mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-receipt"></i> Resumen de Pagos de la Inscripción
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pagos</span>
                            <span class="info-box-number">${{ number_format($pago->inscripcion->pagos->sum('monto_abonado'), 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-list-ol"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cantidad de Pagos</span>
                            <span class="info-box-number">{{ $pago->inscripcion->pagos->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="card card-light">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <small class="d-block text-muted">
                        <i class="fas fa-calendar-plus"></i> Creado: {{ $pago->created_at->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="d-block text-muted text-right">
                        <i class="fas fa-sync"></i> Actualizado: {{ $pago->updated_at->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- Botones de Acción -->
    <div class="card card-light">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-between flex-wrap">
                <div>
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.pagos.destroy', $pago) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
