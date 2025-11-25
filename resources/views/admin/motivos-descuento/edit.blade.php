@extends('adminlte::page')

@section('title', 'Editar Motivo de Descuento - EstóicosGym')

@section('content_header')
    <h1>Editar Motivo de Descuento</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Información del Motivo</h3>
                </div>
                <form action="{{ route('admin.motivos-descuento.update', $motivoDescuento) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre', $motivoDescuento->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3">{{ old('descripcion', $motivoDescuento->descripcion) }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="activo">
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $motivoDescuento->activo) ? 'checked' : '' }}>
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.motivos-descuento.show', $motivoDescuento) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar Motivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
