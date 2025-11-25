@extends('adminlte::page')

@section('title', 'Editar Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Editar Inscripción</h1>
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
        <div class="card-body">
            <form action="{{ route('admin.inscripciones.update', $inscripcion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                            <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                    id="id_cliente" name="id_cliente" required style="width: 100%;">
                                <option value="">-- Seleccionar Cliente --</option>
                                <option value="{{ $inscripcion->id_cliente }}" selected>
                                    {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }} ({{ $inscripcion->cliente->email }})
                                </option>
                            </select>
                            @error('id_cliente')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_membresia">Tipo de Membresía <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                    id="id_membresia" name="id_membresia" required>
                                <option value="">-- Seleccionar Membresía --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" {{ old('id_membresia', $inscripcion->id_membresia) == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }} ({{ $membresia->duracion_meses }} meses)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_membresia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_convenio">Convenio (Opcional)</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio', $inscripcion->id_convenio) == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_estado') is-invalid @enderror" 
                                    id="id_estado" name="id_estado" required>
                                <option value="">-- Seleccionar Estado --</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ old('id_estado', $inscripcion->id_estado) == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $inscripcion->fecha_inicio->format('Y-m-d')) }}" 
                                   readonly required>
                            @error('fecha_inicio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                   id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $inscripcion->fecha_vencimiento->format('Y-m-d')) }}" 
                                   readonly required>
                            @error('fecha_vencimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Se calcula automáticamente</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="precio_base">Precio Base <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('precio_base') is-invalid @enderror" 
                                       id="precio_base" name="precio_base" step="0.01" min="0.01" 
                                       value="{{ old('precio_base', $inscripcion->precio_base) }}" placeholder="0.00" readonly required>
                                @error('precio_base')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted">Se carga automáticamente al seleccionar membresía</small>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descuento_aplicado">Descuento (Opcional)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                       id="descuento_aplicado" name="descuento_aplicado" step="0.01" min="0" 
                                       value="{{ old('descuento_aplicado', $inscripcion->descuento_aplicado) }}" placeholder="0.00">
                                @error('descuento_aplicado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_motivo_descuento">Motivo del Descuento</label>
                            <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                    id="id_motivo_descuento" name="id_motivo_descuento">
                                <option value="">-- Sin Motivo --</option>
                                @foreach($motivos as $motivo)
                                    <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento', $inscripcion->id_motivo_descuento) == $motivo->id ? 'selected' : '' }}>
                                        {{ $motivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motivo_descuento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales...">{{ old('observaciones', $inscripcion->observaciones) }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Inscripción
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
    // Inicializar Select2 para Cliente
    $('.select2-cliente').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        ajax: {
            url: '/api/clientes/search',
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

    // Auto-calcular descuentos y vencimiento
    function calcularInscripcion() {
        const idMembresia = document.getElementById('id_membresia').value;
        const idConvenio = document.getElementById('id_convenio').value;
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const precioBase = document.getElementById('precio_base').value;

        if (!idMembresia || !fechaInicio || !precioBase) {
            return;
        }

        fetch('/api/inscripciones/calcular', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                id_membresia: idMembresia,
                id_convenio: idConvenio || null,
                fecha_inicio: fechaInicio,
                precio_base: parseFloat(precioBase),
            }),
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('fecha_vencimiento').value = data.fecha_vencimiento;
            document.getElementById('descuento_aplicado').value = data.descuento_aplicado;
            
            if (data.precio_final !== undefined) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `Descuento calculado: $${data.descuento_aplicado} | Precio final: $${data.precio_final} <button type="button" class="close" data-dismiss="alert">&times;</button>`;
                document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('form'));
                setTimeout(() => alertDiv.remove(), 5000);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Escuchar cambios en campos relevantes
    document.getElementById('id_membresia').addEventListener('change', calcularInscripcion);
    document.getElementById('id_convenio').addEventListener('change', calcularInscripcion);
    document.getElementById('fecha_inicio').addEventListener('change', calcularInscripcion);
    document.getElementById('precio_base').addEventListener('change', calcularInscripcion);
});
</script>
@stop
