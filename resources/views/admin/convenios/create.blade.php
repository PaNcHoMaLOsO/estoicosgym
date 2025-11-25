@extends('adminlte::page')

@section('title', 'Crear Convenio - EstóicosGym')

@section('content_header')
    <h1>Crear Nuevo Convenio</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información del Convenio</h3>
                </div>
                <form action="{{ route('admin.convenios.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre') }}" placeholder="Ej: Convenio Empresa XYZ" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3" placeholder="Descripción del convenio">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descuento Porcentaje -->
                        <div class="form-group">
                            <label for="descuento_porcentaje">Descuento (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('descuento_porcentaje') is-invalid @enderror" 
                                id="descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0" max="100"
                                value="{{ old('descuento_porcentaje', 0) }}" required>
                            @error('descuento_porcentaje')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descuento Cantidad -->
                        <div class="form-group">
                            <label for="descuento_cantidad">Descuento (Monto Fijo)</label>
                            <input type="number" class="form-control @error('descuento_cantidad') is-invalid @enderror" 
                                id="descuento_cantidad" name="descuento_cantidad" step="0.01" min="0" value="{{ old('descuento_cantidad', 0) }}">
                            @error('descuento_cantidad')
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
                        <a href="{{ route('admin.convenios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Convenio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
