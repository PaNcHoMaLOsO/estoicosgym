@extends('adminlte::page')

@section('title', 'Editar Método de Pago - EstóicosGym')

@section('content_header')
    <h1>Editar Método de Pago</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Información del Método de Pago</h3>
                </div>
                <form action="{{ route('admin.metodos-pago.update', $metodoPago) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre', $metodoPago->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3">{{ old('descripcion', $metodoPago->descripcion) }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Requiere Referencia -->
                        <div class="form-group">
                            <label for="requiere_referencia">
                                <input type="checkbox" id="requiere_referencia" name="requiere_referencia" value="1" {{ old('requiere_referencia', $metodoPago->requiere_referencia) ? 'checked' : '' }}>
                                Requiere Número de Referencia (Ej: Número de Transferencia)
                            </label>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="activo">
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $metodoPago->activo) ? 'checked' : '' }}>
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.metodos-pago.show', $metodoPago) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar Método
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
