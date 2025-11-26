@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="{{ asset('js/precio-formatter.js') }}"></script>
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
                @if($inscripcion)
                    <!-- Mostrar inscripción seleccionada -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-light border border-primary">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Cliente:</strong> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Membresía:</strong> {{ $inscripcion->membresia->nombre }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Vencimiento:</strong> {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="id_inscripcion" name="id_inscripcion" value="{{ $inscripcion->id }}" />
                        </div>
                    </div>

                    <!-- Información de precio -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Base</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" value="{{ number_format($inscripcion->precio_base, 0, '.', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-percent"></i> Descuento</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" value="{{ number_format($inscripcion->descuento_aplicado, 0, '.', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-receipt"></i> Total a Pagar</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="monto_total_inscripcion" 
                                       value="{{ number_format($inscripcion->precio_base - $inscripcion->descuento_aplicado, 0, '.', '.') }}" readonly>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><i class="fas fa-list-check"></i> Inscripción <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                                    id="id_inscripcion" name="id_inscripcion" required>
                                <option value="">-- Seleccionar Inscripción --</option>
                            </select>
                            @error('id_inscripcion')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-calendar"></i> Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        @error('fecha_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-divide"></i> Cantidad Cuotas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                               id="cantidad_cuotas" name="cantidad_cuotas" value="{{ old('cantidad_cuotas', 1) }}" 
                               min="1" max="12" required>
                        @error('cantidad_cuotas')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-hashtag"></i> Número Cuota <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('numero_cuota') is-invalid @enderror" 
                               id="numero_cuota" name="numero_cuota" value="{{ old('numero_cuota', 1) }}" 
                               min="1" max="12" required>
                        @error('numero_cuota')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">Ej: 1, 2, 3, etc.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
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
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-receipt"></i> Monto por Cuota</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" 
                                   id="monto_cuota" name="monto_cuota" step="0.01" readonly>
                        </div>
                        <small class="text-muted d-block mt-1">Se calcula automáticamente</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="fas fa-calendar-times"></i> Vencimiento Cuota</label>
                        <input type="date" class="form-control @error('fecha_vencimiento_cuota') is-invalid @enderror" 
                               id="fecha_vencimiento_cuota" name="fecha_vencimiento_cuota" 
                               value="{{ old('fecha_vencimiento_cuota') }}">
                        @error('fecha_vencimiento_cuota')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><i class="fas fa-paperclip"></i> Observaciones (Opcional)</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="2"
                              placeholder="Notas o comentarios adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Nota Importante -->
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i> 
            <strong>Nota:</strong> Puedes dividir el pago en múltiples cuotas. Ingresa la cantidad total de cuotas y el número de cuota actual. El monto por cuota se calcula automáticamente.
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
    const cantidadCuotas = document.getElementById('cantidad_cuotas');
    const numeroCuota = document.getElementById('numero_cuota');
    const montoAbonado = document.getElementById('monto_abonado');
    const montoCuota = document.getElementById('monto_cuota');
    const montoTotalInscripcion = document.getElementById('monto_total_inscripcion');

    // Inicializar formateador de precios
    PrecioFormatter.iniciarCampo('monto_abonado', false);
    
    // Calcular monto de cuota
    function calcularMontoCuota() {
        if (!montoAbonado.value || !cantidadCuotas.value) {
            montoCuota.value = '';
            return;
        }

        const monto = parseFloat(montoAbonado.value);
        const cuotas = parseInt(cantidadCuotas.value);
        const montoPorCuota = monto / cuotas;
        montoCuota.value = montoPorCuota.toFixed(2);

        // Validar que numero_cuota no sea mayor que cantidad_cuotas
        const numCuota = parseInt(numeroCuota.value) || 1;
        if (numCuota > cuotas) {
            numeroCuota.value = cuotas;
        }
        numeroCuota.max = cuotas;
    }

    // Actualizar numeroCuota cuando cambia cantidadCuotas
    cantidadCuotas.addEventListener('change', calcularMontoCuota);
    montoAbonado.addEventListener('input', calcularMontoCuota);
    montoAbonado.addEventListener('change', calcularMontoCuota);

    // Inicializar Select2 si no hay inscripción precargada
    const idInscripcion = document.getElementById('id_inscripcion');
    if (!idInscripcion.value) {
        $('#id_inscripcion').select2({
            width: '100%',
            allowClear: true,
            language: 'es',
        });
    }
});
</script>
@endsection
