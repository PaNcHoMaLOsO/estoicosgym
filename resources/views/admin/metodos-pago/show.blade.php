@extends('adminlte::page')

@section('title', 'Detalle Método de Pago - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-credit-card"></i> {{ $metodoPago->nombre }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.metodos-pago.edit', $metodoPago) }}" class="btn btn-warning mr-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.metodos-pago.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
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
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-tag"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Nombre</span>
                            <span class="info-box-number">{{ $metodoPago->nombre }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $metodoPago->activo ? 'bg-success' : 'bg-secondary' }}">
                            <i class="fas fa-toggle-{{ $metodoPago->activo ? 'on' : 'off' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                @if($metodoPago->activo)
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
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon {{ $metodoPago->requiere_referencia ? 'bg-warning' : 'bg-info' }}">
                            <i class="fas fa-{{ $metodoPago->requiere_referencia ? 'check-circle' : 'times-circle' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Requiere Referencia</span>
                            <span class="info-box-number">
                                @if($metodoPago->requiere_referencia)
                                    <span class="badge badge-warning">Sí</span>
                                @else
                                    <span class="badge badge-info">No</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Creado</span>
                            <span class="info-box-number">{{ $metodoPago->created_at->format('d/m/Y') }}</span>
                            <small class="text-muted">{{ $metodoPago->created_at->format('H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-align-left"></i> Descripción</label>
                        <div class="border rounded p-3 bg-light">
                            @if ($metodoPago->descripcion)
                                {{ $metodoPago->descripcion }}
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
                        <i class="fas fa-sync"></i> Actualizado: {{ $metodoPago->updated_at->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="card card-light">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-between flex-wrap">
                <div>
                    <a href="{{ route('admin.metodos-pago.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.metodos-pago.edit', $metodoPago) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.metodos-pago.destroy', $metodoPago) }}" method="POST" style="display:inline;">
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
