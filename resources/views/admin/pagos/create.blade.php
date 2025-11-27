@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .info-cliente-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .info-cliente-box h2 {
            color: white;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        .info-label {
            font-weight: 600;
            opacity: 0.9;
        }
        .info-value {
            font-weight: bold;
            font-size: 1.15em;
        }
        .monto-total { color: #fff; }
        .porcentaje {
            font-size: 2em;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-plus-circle"></i> Nuevo Pago
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

    <!-- INFORMACIÓN DEL CLIENTE (mostrada dinámicamente) -->
    <div id="infoClienteBox" class="info-cliente-box d-none">
        <h2 style="margin-bottom: 25px;">
            <i class="fas fa-user-circle"></i>
            <span id="clienteCompleto"></span>
        </h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-row">
                    <span class="info-label">Membresía:</span>
                    <span class="info-value"><span id="membresiaNombre"></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Desde:</span>
                    <span class="info-value"><span id="fechaInicio"></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vencimiento:</span>
                    <span class="info-value"><span id="fechaVencimiento"></span></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-row">
                    <span class="info-label">Total a Pagar:</span>
                    <span class="info-value monto-total" id="montoTotal"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ya Abonado:</span>
                    <span class="info-value"><span id="montoAbonado"></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pendiente:</span>
                    <span class="info-value"><span id="montoPendiente"></span></span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- Selección de Inscripción -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-check"></i> Seleccionar Inscripción
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="id_inscripcion"><i class="fas fa-user-check"></i> Inscripción <span class="text-danger">*</span></label>
                    <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        <option value="">-- Seleccionar Inscripción --</option>
                        @foreach($inscripciones as $insc)
                            <option value="{{ $insc->id }}" 
                                    data-precio="{{ $insc->precio_final ?? $insc->precio_base }}"
                                    data-cliente="{{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }}"
                                    data-membresia="{{ $insc->membresia->nombre }}"
                                    data-inicio="{{ $insc->fecha_inicio->format('d/m/Y') }}"
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}">
                                #{{ $insc->id }} - {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }} (${{ number_format($insc->precio_final ?? $insc->precio_base, 0, '.', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Datos del Pago -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i> Datos del Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_pago"><i class="fas fa-calendar"></i> Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="id_metodo_pago_principal"><i class="fas fa-credit-card"></i> Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($metodos_pago as $metodo)
                                <option value="{{ $metodo->id }}" {{ old('id_metodo_pago_principal') == $metodo->id ? 'selected' : '' }}>
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_metodo_pago_principal')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto_abonado"><i class="fas fa-money-bill-wave"></i> Monto a Abonar <span class="text-danger">*</span></label>
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

                    <div class="col-md-6 mb-3">
                        <label for="referencia_pago"><i class="fas fa-fingerprint"></i> Referencia/Comprobante</label>
                        <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                               id="referencia_pago" name="referencia_pago" maxlength="100"
                               placeholder="TRF-2025-001 o Nº comprobante"
                               value="{{ old('referencia_pago') }}">
                        @error('referencia_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones (Opcional)</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="2"
                              placeholder="Notas o comentarios adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">
        <input type="hidden" id="cantidad_cuotas" name="cantidad_cuotas" value="1">

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check"></i> Registrar Pago
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectInscripcion = document.getElementById('id_inscripcion');
    const infoClienteBox = document.getElementById('infoClienteBox');

    // Inicializar Select2
    $('#id_inscripcion').select2({
        width: '100%',
        allowClear: true,
        language: 'es',
        placeholder: '-- Seleccionar Inscripción --'
    });

    // Mostrar información cuando se selecciona una inscripción
    selectInscripcion.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const precio = parseFloat(option.getAttribute('data-precio'));
            const cliente = option.getAttribute('data-cliente');
            const membresia = option.getAttribute('data-membresia');
            const inicio = option.getAttribute('data-inicio');
            const vencimiento = option.getAttribute('data-vencimiento');

            document.getElementById('clienteCompleto').textContent = cliente;
            document.getElementById('membresiaNombre').textContent = membresia;
            document.getElementById('fechaInicio').textContent = inicio;
            document.getElementById('fechaVencimiento').textContent = vencimiento;
            document.getElementById('montoTotal').textContent = '$' + precio.toLocaleString('es-CO', {minimumFractionDigits: 0});
            document.getElementById('montoAbonado').textContent = '$0';
            document.getElementById('montoPendiente').textContent = '$' + precio.toLocaleString('es-CO', {minimumFractionDigits: 0});

            infoClienteBox.classList.remove('d-none');
        } else {
            infoClienteBox.classList.add('d-none');
        }
    });
});
</script>
@endsection
