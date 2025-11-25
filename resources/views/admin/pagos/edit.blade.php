{{-- /* eslint-disable */ --}}
@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Editar Pago</h1>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Pago #{{ $pago->id }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pagos.update', $pago) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_inscripcion">Inscripción <span class="text-danger">*</span></label>
                            <select class="form-control select2-inscripcion @error('id_inscripcion') is-invalid @enderror" 
                                    id="id_inscripcion" name="id_inscripcion" required style="width: 100%;">
                                <option value="">-- Seleccionar Inscripción --</option>
                                <option value="{{ $pago->id_inscripcion }}" selected>
                                    #{{ $pago->inscripcion->id }} - {{ $pago->inscripcion->cliente->nombres }} ({{ $pago->inscripcion->estado->nombre }})
                                </option>
                            </select>
                            @error('id_inscripcion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_metodo_pago">Método de Pago <span class="text-danger">*</span></label>
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
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_abonado">Monto Abonado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado', $pago->monto_abonado) }}" required>
                                @error('monto_abonado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="referencia">Referencia (Opcional)</label>
                    <input type="text" class="form-control" id="referencia" name="referencia" 
                           placeholder="Ej: Comprobante #123" value="{{ old('referencia', $pago->referencia) }}">
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 para Inscripción
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
