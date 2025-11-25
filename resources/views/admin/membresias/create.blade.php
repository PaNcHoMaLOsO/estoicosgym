@extends('adminlte::page')

@section('title', 'Crear Membresía')

@section('content_header')
    <h1>Nueva Membresía</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Membresía</h3>
                </div>
                <form action="{{ route('admin.membresias.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Duración -->
                        <div class="form-group">
                            <label for="duracion_meses">Duración (Meses) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duracion_meses') is-invalid @enderror" 
                                       id="duracion_meses" name="duracion_meses" 
                                       value="{{ old('duracion_meses', 0) }}" min="0" placeholder="0" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar-alt"></i> meses
                                    </span>
                                </div>
                            </div>
                            @error('duracion_meses')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="duracion_dias_calculado">Duración Total (Días)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" 
                                       id="duracion_dias_calculado" readonly>
                                <input type="hidden" id="duracion_dias" name="duracion_dias">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar"></i> días
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Se calcula automáticamente: (Meses × 30) + 5 días
                            </small>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Precio Normal -->
                        <div class="form-group">
                            <label for="precio_normal">Precio Normal ($) *</label>
                            <input type="number" class="form-control @error('precio_normal') is-invalid @enderror" 
                                   id="precio_normal" name="precio_normal" value="{{ old('precio_normal') }}" 
                                   min="0" step="0.01" required>
                            @error('precio_normal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Activo -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" checked>
                                <label class="custom-control-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('admin.membresias.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const duracionMeses = document.getElementById('duracion_meses');
    const duracionDiasCalculado = document.getElementById('duracion_dias_calculado');
    const duracionDiasHidden = document.getElementById('duracion_dias');

    // Función para calcular días
    function calcularDias() {
        const meses = parseInt(duracionMeses.value) || 0;
        const dias = (meses * 30) + 5; // Cada mes cuenta como 30 días + 5 días adicionales
        duracionDiasCalculado.value = dias;
        duracionDiasHidden.value = dias;
    }

    // Escuchar cambios en meses
    duracionMeses.addEventListener('change', calcularDias);
    duracionMeses.addEventListener('input', calcularDias);

    // Calcular al cargar la página
    calcularDias();
});
</script>
@endsection
