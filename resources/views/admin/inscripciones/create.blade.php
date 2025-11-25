@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Crear Nueva Inscripción</h1>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
            <form action="{{ route('admin.inscripciones.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                            <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                    id="id_cliente" name="id_cliente" required style="width: 100%;">
                                <option value="">-- Seleccionar Cliente --</option>
                                @if(old('id_cliente'))
                                    @php
                                        $clienteSeleccionado = $clientes->find(old('id_cliente'));
                                    @endphp
                                    @if($clienteSeleccionado)
                                        <option value="{{ $clienteSeleccionado->id }}" selected>
                                            {{ $clienteSeleccionado->nombres }} {{ $clienteSeleccionado->apellido_paterno }} ({{ $clienteSeleccionado->email }})
                                        </option>
                                    @endif
                                @endif
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
                                    <option value="{{ $membresia->id }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
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
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
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
                                    <option value="{{ $estado->id }}" {{ old('id_estado') == $estado->id ? 'selected' : '' }}>
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
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required>
                            @error('fecha_inicio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                   id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required>
                            @error('fecha_vencimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
                                       value="{{ old('precio_base') }}" placeholder="0.00" required>
                                @error('precio_base')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

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
                                       value="{{ old('descuento_aplicado', 0) }}" placeholder="0.00">
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
                                    <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento') == $motivo->id ? 'selected' : '' }}>
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
                              id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Nota:</strong> La inscripción se crea con la fecha de hoy. El precio final se calcula automáticamente como: Precio Base - Descuento.
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Inscripción
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
    const form = document.querySelector('form');
    const idMembresia = document.getElementById('id_membresia');
    const idConvenio = document.getElementById('id_convenio');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const precioBase = document.getElementById('precio_base');
    const descuentoAplicado = document.getElementById('descuento_aplicado');

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

    // Función para calcular automáticamente
    async function calcularInscripcion() {
        if (!idMembresia.value || !fechaInicio.value || !precioBase.value) {
            return;
        }

        try {
            const response = await fetch('/api/inscripciones/calcular', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    id_membresia: idMembresia.value,
                    id_convenio: idConvenio.value,
                    fecha_inicio: fechaInicio.value,
                    precio_base: parseFloat(precioBase.value),
                }),
            });

            const data = await response.json();

            if (response.ok) {
                fechaVencimiento.value = data.fecha_vencimiento;
                descuentoAplicado.value = data.descuento_aplicado;
                
                // Mostrar notificación visual
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle"></i> Fecha y descuento calculados automáticamente.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                `;
                form.parentElement.insertBefore(alert, form);
                
                setTimeout(() => alert.remove(), 3000);
            }
        } catch (error) {
            console.error('Error al calcular:', error);
        }
    }

    // Escuchar cambios
    idMembresia.addEventListener('change', calcularInscripcion);
    idConvenio.addEventListener('change', calcularInscripcion);
    fechaInicio.addEventListener('change', calcularInscripcion);
    precioBase.addEventListener('blur', calcularInscripcion);
});
</script>
@stop
