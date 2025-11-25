@extends('adminlte::page')

@section('title', 'Crear Método de Pago - EstóicosGym')

@section('content_header')
    <h1>Crear Nuevo Método de Pago</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Información del Método de Pago</h3>
                </div>
                <form action="{{ route('admin.metodos-pago.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" 
                                value="{{ old('nombre') }}" placeholder="Ej: Transferencia Bancaria" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" 
                                rows="3" placeholder="Descripción del método de pago">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Requiere Referencia -->
                        <div class="form-group">
                            <label for="requiere_referencia">
                                <input type="checkbox" id="requiere_referencia" name="requiere_referencia" value="1" {{ old('requiere_referencia') ? 'checked' : '' }}>
                                Requiere Número de Referencia (Ej: Número de Transferencia)
                            </label>
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
                        <a href="{{ route('admin.metodos-pago.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Método
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
