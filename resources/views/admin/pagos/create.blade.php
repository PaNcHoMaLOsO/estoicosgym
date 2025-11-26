@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-plus-circle"></i> Registrar Nuevo Pago
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Errores de Validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- Información de Inscripción -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check"></i> Información de Inscripción
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-list-check"></i> Inscripción <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                                id="id_inscripcion" name="id_inscripcion" required>
                            <option value="">-- Seleccionar Inscripción --</option>
                            @foreach($inscripciones as $inscripcion)
                                <option value="{{ $inscripcion->id }}" {{ old('id_inscripcion') == $inscripcion->id ? 'selected' : '' }}>
                                    #{{ $inscripcion->uuid }} - {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_inscripcion')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-credit-card"></i> Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_metodo_pago') is-invalid @enderror" 
                                id="id_metodo_pago" name="id_metodo_pago" required>
                            <option value="">-- Seleccionar Método --</option>
                            @foreach($metodos_pago as $metodo)
                                <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_metodo_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos del Pago -->
        <div class="card card-success mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i> Datos del Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-calendar"></i> Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-money-bill-wave"></i> Monto Abonado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                   value="{{ old('monto_abonado') }}" placeholder="0.00" required>
                        </div>
                        @error('monto_abonado')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><i class="fas fa-paperclip"></i> Referencia (Opcional)</label>
                    <input type="text" class="form-control @error('referencia') is-invalid @enderror" 
                           id="referencia" name="referencia" 
                           placeholder="Ej: Comprobante #123, transferencia XXX" value="{{ old('referencia') }}">
                    @error('referencia')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Información adicional para identificar el pago</small>
                </div>
            </div>
        </div>

        <!-- Nota Importante -->
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i> 
            <strong>Nota:</strong> El estado del pago se asignará automáticamente según el monto abonado y la inscripción seleccionada.
        </div>

        <hr class="my-4">

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Registrar Pago
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Opcional: Inicializar Select2 para mejorar UX si es necesario
    $('#id_inscripcion').select2({
        width: '100%',
        allowClear: true,
    });
});
</script>
@stop
