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
                            <option value="{{ $insc->id }}" data-precio="{{ $insc->precio_final ?? $insc->precio_base }}">
                                #{{ $insc->id }} - {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }} (${{ number_format($insc->precio_final ?? $insc->precio_base, 0, '.', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Información de la inscripción seleccionada -->
                <div id="infoInscripcion" class="alert alert-light border border-primary d-none">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Cliente:</strong> <span id="clienteNombre"></span><br>
                            <strong>Membresía:</strong> <span id="membresiaNombre"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Total a Pagar:</strong> <span id="precioTotal" style="font-size: 1.2em; color: #007bff;"></span><br>
                            <strong>Vencimiento:</strong> <span id="vencimiento"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipo de Pago -->
        <div class="card card-warning mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-grip-horizontal"></i> Tipo de Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input tipoPago" type="radio" id="tipoPagoSimple" 
                           name="tipoPago" value="simple" checked>
                    <label class="form-check-label" for="tipoPagoSimple">
                        <strong>☑️ Pago Simple</strong> - El abono se suma hasta completar el total
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input tipoPago" type="radio" id="tipoPagoCuotas" 
                           name="tipoPago" value="cuotas">
                    <label class="form-check-label" for="tipoPagoCuotas">
                        <strong>☑️ Pago por Cuotas</strong> - Dividir el abono en múltiples cuotas
                    </label>
                </div>

                <!-- Campo cantidad de cuotas (oculto inicialmente) -->
                <div id="camposCuotas" class="mt-3 d-none">
                    <div class="form-group">
                        <label for="cantidad_cuotas"><i class="fas fa-divide"></i> Cantidad de Cuotas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                               id="cantidad_cuotas" name="cantidad_cuotas" value="{{ old('cantidad_cuotas', 1) }}" 
                               min="2" max="12">
                        @error('cantidad_cuotas')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Mínimo 2, máximo 12 cuotas</small>
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
                    <label for="observaciones"><i class="fas fa-paperclip"></i> Observaciones (Opcional)</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="2"
                              placeholder="Notas o comentarios adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hidden field para pago simple -->
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">

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
    const selectInscripcion = document.getElementById('id_inscripcion');
    const tipoPagoRadios = document.querySelectorAll('.tipoPago');
    const camposCuotas = document.getElementById('camposCuotas');
    const esPagoSimple = document.getElementById('es_pago_simple');

    // Inicializar Select2
    $('#id_inscripcion').select2({
        width: '100%',
        allowClear: true,
        language: 'es',
        placeholder: '-- Seleccionar Inscripción --'
    });

    // Mostrar información cuando se selecciona una inscripción
    selectInscripcion.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const precio = option.getAttribute('data-precio');

        if (this.value) {
            document.getElementById('infoInscripcion').classList.remove('d-none');
            document.getElementById('precioTotal').textContent = '$' + formatPrecio(precio);
        } else {
            document.getElementById('infoInscripcion').classList.add('d-none');
        }
    });

    // Alternar campos de cuotas según tipo de pago
    tipoPagoRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'cuotas') {
                camposCuotas.classList.remove('d-none');
                esPagoSimple.value = '0';
                document.getElementById('cantidad_cuotas').setAttribute('required', 'required');
            } else {
                camposCuotas.classList.add('d-none');
                esPagoSimple.value = '1';
                document.getElementById('cantidad_cuotas').removeAttribute('required');
            }
        });
    });

    // Función para formatear precio
    function formatPrecio(precio) {
        return parseFloat(precio).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
});
</script>
@endsection
