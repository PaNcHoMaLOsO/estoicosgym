@extends('adminlte::page')

@section('title', 'Registrar Abono - EstóicosGym')

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
        .monto-abonado { color: #a8e6cf; }
        .monto-pendiente { color: #ffd3b6; }
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
                <i class="fas fa-dollar-sign"></i> Registrar Nuevo Abono
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

    @php
        $cliente = $pago->inscripcion->cliente;
        $total = $pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base;
        $abonado = $pago->monto_abonado;
        $pendiente = $total - $abonado;
        $porcentaje = round(($abonado / $total) * 100);
    @endphp

    <!-- INFORMACIÓN DEL CLIENTE -->
    <div class="info-cliente-box">
        <h2 style="margin-bottom: 25px;">
            <i class="fas fa-user-circle"></i>
            {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
        </h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-row">
                    <span class="info-label">Membresía:</span>
                    <span class="info-value">{{ $pago->inscripcion->membresia->nombre }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Desde:</span>
                    <span class="info-value">{{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vencimiento:</span>
                    <span class="info-value">{{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-row">
                    <span class="info-label">Total a Pagar:</span>
                    <span class="info-value monto-total">${{ number_format($total, 0, '.', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ya Abonado:</span>
                    <span class="info-value monto-abonado">${{ number_format($abonado, 0, '.', '.') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pendiente:</span>
                    <span class="info-value monto-pendiente">${{ number_format($pendiente, 0, '.', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="porcentaje">
            <i class="fas fa-chart-pie"></i> {{ $porcentaje }}% Pagado
        </div>
    </div>

    <!-- FORMULARIO DE ABONO -->
    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formPago">
        @csrf
        @method('PUT')

        <!-- Datos del Nuevo Abono -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Abono
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_pago"><i class="fas fa-calendar"></i> Fecha de Abono <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
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
                                <option value="{{ $metodo->id }}" {{ $pago->id_metodo_pago_principal == $metodo->id ? 'selected' : '' }}>
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
                        <label for="monto_abonado"><i class="fas fa-money-bill-wave"></i> Monto del Abono <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                   id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                   value="{{ old('monto_abonado', $pago->monto_abonado) }}" required>
                        </div>
                        <small class="text-muted">Máximo permitido: ${{ number_format($pendiente, 0, '.', '.') }}</small>
                        @error('monto_abonado')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="referencia_pago"><i class="fas fa-fingerprint"></i> Referencia/Comprobante</label>
                        <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                               id="referencia_pago" name="referencia_pago" maxlength="100"
                               placeholder="TRF-2025-001 o Nº comprobante"
                               value="{{ old('referencia_pago', $pago->referencia_pago) }}">
                        @error('referencia_pago')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones (Opcional)</label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="2"
                              placeholder="Notas o comentarios adicionales...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="id_inscripcion" name="id_inscripcion" value="{{ $pago->id_inscripcion }}">
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">
        <input type="hidden" id="cantidad_cuotas" name="cantidad_cuotas" value="1">

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check"></i> Registrar Abono
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
// Validación de monto máximo
document.getElementById('monto_abonado').addEventListener('change', function() {
    const pendiente = {{ $pendiente }};
    const monto = parseFloat(this.value);
    
    if (monto > pendiente) {
        alert('El monto no puede exceder $' + pendiente.toLocaleString('es-CO'));
        this.value = pendiente;
    }
});
</script>
@endsection

