@extends('adminlte::page')

@section('title', 'Editar Membresía - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Membresía: {{ $membresia->nombre }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-credit-card"></i> Datos de la Membresía
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <!-- Sección Información Básica -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle"></i> Información Básica
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" name="nombre" placeholder="Ej: Plan Básico, Plan Premium" 
                                   value="{{ old('nombre', $membresia->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="duracion_meses" class="form-label">Duración (Meses) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duracion_meses') is-invalid @enderror" 
                                       id="duracion_meses" name="duracion_meses" 
                                       value="{{ old('duracion_meses', $membresia->duracion_meses) }}" min="0" max="12" placeholder="0" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">meses</span>
                                </div>
                            </div>
                            @error('duracion_meses')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="duracion_dias_calculado" class="form-label">Duración Total (Días)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" 
                                       id="duracion_dias_calculado" placeholder="Calculado automáticamente" min="1">
                                <input type="hidden" id="duracion_dias" name="duracion_dias" value="{{ old('duracion_dias', $membresia->duracion_dias) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">días</span>
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mt-1" id="dias_info">Se calcula: (Meses × 30) + 5</small>
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
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="4" 
                                      placeholder="Detalles y características de esta membresía...">{{ old('descripcion', $membresia->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Precio -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-dollar-sign"></i> Precio
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="precio_normal" class="form-label">Precio Normal ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('precio_normal') is-invalid @enderror" 
                                       id="precio_normal" name="precio_normal" 
                                       value="{{ old('precio_normal', $precioActual->precio_normal ?? 0) }}" 
                                       min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            @if ($precioActual)
                                <small class="form-text text-muted d-block mt-1">
                                    Precio actual: ${{ number_format($precioActual->precio_normal, 0, '.', '.') }}
                                </small>
                            @endif
                            @error('precio_normal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección Cambios -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-history"></i> Razón del Cambio
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label for="razon_cambio" class="form-label">Razón del Cambio (si aplica)</label>
                            <input type="text" class="form-control @error('razon_cambio') is-invalid @enderror" 
                                   id="razon_cambio" name="razon_cambio" value="{{ old('razon_cambio') }}" 
                                   placeholder="Ej: Ajuste de precios, Promoción, etc.">
                            @error('razon_cambio')
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
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" 
                                   {{ $membresia->activo ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Membresía Activa</label>
                        </div>
                        <small class="d-block text-muted mt-2">Los clientes podrán contratar esta membresía</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Botones de Acción -->
                <div class="form-group d-flex gap-2 justify-content-between flex-wrap">
                    <div>
                        <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Actualizar Membresía
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== Lógica de Duración de Días ==========
    const duracionMeses = document.getElementById('duracion_meses');
    const duracionDias = document.getElementById('duracion_dias');
    const duracionDiasCalculado = document.getElementById('duracion_dias_calculado');
    const diasInfo = document.getElementById('dias_info');

    function actualizarDias() {
        const meses = parseInt(duracionMeses.value) || 0;
        
        if (meses === 0) {
            // Modo manual: permitir entrada de días
            duracionDiasCalculado.removeAttribute('readonly');
            duracionDiasCalculado.value = '';
            duracionDiasCalculado.placeholder = 'Ingresa duración manual (Ej: Pase Diario=1)';
            diasInfo.textContent = '⚠️ Meses = 0: Ingresa manualmente la duración en días';
            
            // Sincronizar cambios en el campo visible al hidden
            duracionDiasCalculado.addEventListener('input', function() {
                duracionDias.value = this.value;
            });
        } else {
            // Modo automático: calcular días
            const dias = (meses * 30) + 5;
            duracionDias.value = dias;
            duracionDiasCalculado.value = dias;
            duracionDiasCalculado.setAttribute('readonly', 'readonly');
            duracionDiasCalculado.placeholder = 'Calculado automáticamente';
            diasInfo.textContent = `Cálculo automático: (${meses} meses × 30) + 5 = ${dias} días`;
            duracionDiasCalculado.removeEventListener('input', null);
        }
    }

    duracionMeses.addEventListener('change', actualizarDias);
    duracionMeses.addEventListener('input', actualizarDias);
    actualizarDias();

    // ========== Formateo de Precio con Puntos de Miles ==========
    const precioInput = document.getElementById('precio_normal');
    
    function formatearPrecio(valor) {
        // Remover todo excepto números
        const numeros = valor.replace(/\D/g, '');
        // Formatear con puntos de miles
        return numeros.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    precioInput.addEventListener('input', function(e) {
        let valor = e.target.value;
        let formateado = formatearPrecio(valor);
        e.target.value = formateado;
    });

    precioInput.addEventListener('blur', function(e) {
        // Al perder foco, asegurar que el valor esté bien formateado
        let valor = e.target.value;
        let formateado = formatearPrecio(valor);
        e.target.value = formateado;
    });
});
</script>
@endsection
