@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="{{ asset('js/precio-formatter.js') }}"></script>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Pago #{{ $pago->id }}
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-info mr-2">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
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

    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formPago">
        @csrf
        @method('PUT')

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
                        <select class="form-control select2-inscripcion @error('id_inscripcion') is-invalid @enderror" 
                                id="id_inscripcion" name="id_inscripcion" required style="width: 100%;">
                            <option value="">-- Seleccionar Inscripción --</option>
                            <option value="{{ $pago->id_inscripcion }}" selected>
                                #{{ $pago->inscripcion->uuid }} - {{ $pago->inscripcion->cliente->nombres }} ({{ $pago->inscripcion->estado->nombre }})
                            </option>
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
                                <option value="{{ $metodo->id }}" {{ old('id_metodo_pago', $pago->id_metodo_pago) == $metodo->id ? 'selected' : '' }}>
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
                               id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
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
                                   value="{{ old('monto_abonado', $pago->monto_abonado) }}" required>
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
                           placeholder="Ej: Comprobante #123" value="{{ old('referencia', $pago->referencia_pago) }}">
                    @error('referencia')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Información adicional para identificar el pago</small>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Pago
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar formateador de precios
    PrecioFormatter.iniciarCampo('monto_abonado', false);
    
    $('.select2-inscripcion').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        ajax: {
            url: '/api/inscripciones/search',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                };
            },
            processResults: function (data) {
                return {
                    results: data,
                };
            },
        },
        minimumInputLength: 2,
    });
});
</script>
@stop
