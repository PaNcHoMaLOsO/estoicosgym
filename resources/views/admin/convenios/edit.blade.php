@extends('adminlte::page')

@section('title', 'Editar Convenio - EstóicosGym')

@section('content_header')
    <h1>Editar Convenio</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Información del Convenio</h3>
                </div>
                <form action="{{ route('admin.convenios.update', $convenio) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre', $convenio->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3">{{ old('descripcion', $convenio->descripcion) }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descuento Porcentaje -->
                        <div class="form-group">
                            <label for="descuento_porcentaje">Descuento (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('descuento_porcentaje') is-invalid @enderror" 
                                id="descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0" max="100"
                                value="{{ old('descuento_porcentaje', $convenio->descuento_porcentaje) }}" required>
                            @error('descuento_porcentaje')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descuento Cantidad -->
                        <div class="form-group">
                            <label for="descuento_cantidad">Descuento (Monto Fijo)</label>
                            <input type="number" class="form-control @error('descuento_cantidad') is-invalid @enderror" 
                                id="descuento_cantidad" name="descuento_cantidad" step="0.01" min="0" 
                                value="{{ old('descuento_cantidad', $convenio->descuento_cantidad) }}">
                            @error('descuento_cantidad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="activo">
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $convenio->activo) ? 'checked' : '' }}>
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.convenios.show', $convenio) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar Convenio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
