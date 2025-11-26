@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-plus-circle"></i> Crear Nueva Inscripción
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('admin.inscripciones.store') }}" method="POST" id="formInscripcion">
        @csrf

        <!-- Información Principal -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check"></i> Información Principal
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> Cliente <span class="text-danger">*</span></label>
                        <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                id="id_cliente" name="id_cliente" required style="width: 100%;">
                            <option value="">-- Seleccionar Cliente --</option>
                            @forelse($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} ({{ $cliente->email }})
                                </option>
                            @empty
                                <option value="" disabled>No hay clientes disponibles</option>
                            @endforelse
                        </select>
                        @error('id_cliente')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Se muestran solo clientes sin inscripción activa</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-layer-group"></i> Membresía <span class="text-danger">*</span></label>
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
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-handshake"></i> Convenio (Opcional)</label>
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
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-toggle-on"></i> Estado <span class="text-danger">*</span></label>
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
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Fechas y Precios -->
        <div class="card card-info mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Fechas y Precios
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" 
                               required>
                        @error('fecha_inicio')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Fecha en que inicia la membresía (puede ser futura)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-divide"></i> Cantidad Cuotas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                               id="cantidad_cuotas" name="cantidad_cuotas" value="{{ old('cantidad_cuotas', 1) }}" 
                               min="1" max="12" required>
                        @error('cantidad_cuotas')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Número de cuotas (1-12)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Cuota</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" 
                                   id="monto_cuota" name="monto_cuota" step="0.01" min="0.01" 
                                   readonly>
                        </div>
                        <small class="text-muted d-block mt-1">Se calcula automáticamente</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descuentos -->
        <div class="card card-warning mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-percent"></i> Descuentos
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Monto Descuento (Opcional)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control @error('descuento_aplicado') is-invalid @enderror" 
                                   id="descuento_aplicado" name="descuento_aplicado" step="0.01" min="0" 
                                   value="{{ old('descuento_aplicado', 0) }}" placeholder="0.00">
                        </div>
                        @error('descuento_aplicado')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-tag"></i> Motivo Descuento</label>
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
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="card card-secondary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-align-left"></i> Observaciones
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Información General -->
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i> 
            <strong>Nota:</strong> La inscripción se crea con la fecha de hoy. El precio final se calcula automáticamente como: Precio Base - Descuento.
        </div>

        <hr class="my-4">

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Crear Inscripción
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formInscripcion = document.getElementById('formInscripcion');
    const idMembresia = document.getElementById('id_membresia');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const precioBase = document.getElementById('precio_base');
    const cantidadCuotas = document.getElementById('cantidad_cuotas');
    const montoCuota = document.getElementById('monto_cuota');

    // Inicializar Select2 para Cliente
    $('#id_cliente').select2({
        width: '100%',
        allowClear: true,
        language: 'es',
    });

    // Cargar precio al seleccionar membresía
    async function cargarPrecioMembresia() {
        if (!idMembresia.value) {
            precioBase.value = '';
            montoCuota.value = '';
            return;
        }

        try {
            const response = await fetch(`/api/membresias/${idMembresia.value}`);
            const data = await response.json();

            if (response.ok) {
                precioBase.value = data.precio_normal || 0;
                calcularMontoCuota();
                calcularInscripcion();
            }
        } catch (error) {
            console.error('Error al cargar precio:', error);
        }
    }

    // Calcular monto de cuota
    function calcularMontoCuota() {
        if (!precioBase.value || !cantidadCuotas.value) {
            montoCuota.value = '';
            return;
        }

        const precio = parseFloat(precioBase.value);
        const cuotas = parseInt(cantidadCuotas.value);
        const monto = precio / cuotas;
        montoCuota.value = monto.toFixed(2);
    }

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
                    fecha_inicio: fechaInicio.value,
                    precio_base: parseFloat(precioBase.value),
                }),
            });

            const data = await response.json();

            if (response.ok) {
                fechaVencimiento.value = data.fecha_vencimiento;
                document.getElementById('descuento_aplicado').value = data.descuento_aplicado || 0;
            }
        } catch (error) {
            console.error('Error al calcular:', error);
        }
    }

    // Escuchar cambios
    idMembresia.addEventListener('change', cargarPrecioMembresia);
    fechaInicio.addEventListener('change', calcularInscripcion);
    cantidadCuotas.addEventListener('change', calcularMontoCuota);
});
</script>
@endsection
