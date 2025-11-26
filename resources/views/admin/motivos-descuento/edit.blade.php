@extends('adminlte::page')

@section('title', 'Editar Motivo de Descuento - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Motivo de Descuento: {{ $motivoDescuento->nombre }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.motivos-descuento.show', $motivoDescuento) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-circle"></i> Errores en el formulario
            </h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-percent"></i> Datos del Motivo de Descuento
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.motivos-descuento.update', $motivoDescuento) }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <!-- Sección Información -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle"></i> Información del Motivo
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre', $motivoDescuento->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Descripción -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-align-left"></i> Descripción
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="4">{{ old('descripcion', $motivoDescuento->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Estado -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-toggle-on"></i> Estado
                        </h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" {{ $motivoDescuento->activo ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Motivo Activo</label>
                        </div>
                        <small class="d-block text-muted mt-2">Se podrá utilizar para aplicar descuentos</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Botones de Acción -->
                <div class="form-group d-flex gap-2 justify-content-between flex-wrap">
                    <div>
                        <a href="{{ route('admin.motivos-descuento.show', $motivoDescuento) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Actualizar Motivo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
