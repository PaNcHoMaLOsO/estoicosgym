@extends('adminlte::page')

@section('title', 'Editar Membresía')

@section('content_header')
    <h1>Editar Membresía: {{ $membresia->nombre }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos de Membresía</h3>
                </div>
                <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" name="nombre" value="{{ old('nombre', $membresia->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Duración -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="duracion_meses">Duración (Meses)</label>
                                <input type="number" class="form-control @error('duracion_meses') is-invalid @enderror" 
                                       id="duracion_meses" name="duracion_meses" 
                                       value="{{ old('duracion_meses', $membresia->duracion_meses) }}" min="0">
                                @error('duracion_meses')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="duracion_dias">Duración (Días) *</label>
                                <input type="number" class="form-control @error('duracion_dias') is-invalid @enderror" 
                                       id="duracion_dias" name="duracion_dias" 
                                       value="{{ old('duracion_dias', $membresia->duracion_dias) }}" min="1" required>
                                @error('duracion_dias')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $membresia->descripcion) }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Precio Normal -->
                        <div class="form-group">
                            <label for="precio_normal">Precio Normal ($) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('precio_normal') is-invalid @enderror" 
                                       id="precio_normal" name="precio_normal" 
                                       value="{{ old('precio_normal', $precioActual->precio_normal ?? 0) }}" 
                                       min="0" step="0.01" required>
                                @if ($precioActual)
                                    <div class="input-group-append">
                                        <span class="input-group-text text-muted">
                                            Precio actual: ${{ number_format($precioActual->precio_normal, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            @error('precio_normal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Razón del Cambio -->
                        <div class="form-group">
                            <label for="razon_cambio">Razón del Cambio (si aplica)</label>
                            <input type="text" class="form-control @error('razon_cambio') is-invalid @enderror" 
                                   id="razon_cambio" name="razon_cambio" value="{{ old('razon_cambio') }}" 
                                   placeholder="Ej: Ajuste de precios, Promoción, etc.">
                            @error('razon_cambio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Activo -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" 
                                       value="1" {{ $membresia->activo ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
