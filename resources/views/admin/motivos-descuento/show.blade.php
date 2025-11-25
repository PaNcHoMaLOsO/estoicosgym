@extends('adminlte::page')

@section('title', 'Detalle Motivo de Descuento - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detalle del Motivo de Descuento</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.motivos-descuento.edit', $motivoDescuento) }}" class="btn btn-warning float-right">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información del Motivo</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $motivoDescuento->id }}</dd>

                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8"><strong>{{ $motivoDescuento->nombre }}</strong></dd>

                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">{{ $motivoDescuento->descripcion ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($motivoDescuento->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Creado:</dt>
                        <dd class="col-sm-8">{{ $motivoDescuento->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones Acción -->
    <div class="row mt-3">
        <div class="col-md-12">
            <a href="{{ route('admin.motivos-descuento.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('admin.motivos-descuento.edit', $motivoDescuento) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('admin.motivos-descuento.destroy', $motivoDescuento) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro? Esta acción no puede revertirse')">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
@stop
