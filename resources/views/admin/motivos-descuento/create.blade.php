@extends('adminlte::page')

@section('title', 'Crear Motivo de Descuento - EstóicosGym')

@section('content_header')
    <h1>Crear Nuevo Motivo de Descuento</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información del Motivo</h3>
                </div>
                <form action="{{ route('admin.motivos-descuento.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre') }}" placeholder="Ej: Descuento por Referencia" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3" placeholder="Descripción del motivo de descuento">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="activo">
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.motivos-descuento.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Motivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
