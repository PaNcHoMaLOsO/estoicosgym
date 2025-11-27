@extends('adminlte::page')

@section('title', 'Registrar Abono - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .cliente-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        .cliente-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .cliente-header .content {
            position: relative;
            z-index: 1;
        }
        .cliente-nombre {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .cliente-nombre i {
            font-size: 1.3em;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .stat-item {
            background: rgba(255,255,255,0.15);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .stat-label {
            font-size: 0.85em;
            opacity: 0.85;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 1.5em;
            font-weight: 700;
        }
        .progreso-pago {
            margin-top: 20px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .progress-bar-custom {
            height: 12px;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }
        .progress {
            background-color: rgba(255,255,255,0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .form-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .form-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px;
        }
        .form-card .card-header h3 {
            color: white;
            margin: 0;
        }
        .form-card .card-body {
            padding: 30px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-control, .form-control:focus {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-registrar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.1em;
            font-weight: 700;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-registrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }
        .btn-cancelar {
            background: #6c757d;
            color: white;
            padding: 15px 40px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
        }
        .info-box-custom {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-plus-circle"></i> Registrar Nuevo Abono
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
            <i class="fas fa-exclamation-circle"></i> <strong>¡Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
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
    <div class="cliente-header">
        <div class="content">
            <div class="cliente-nombre">
                <i class="fas fa-user-circle"></i>
                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-label">Membresía</div>
                    <div class="stat-value">{{ $pago->inscripcion->membresia->nombre }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total a Pagar</div>
                    <div class="stat-value">${{ number_format($total, 0, '.', '.') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Ya Abonado</div>
                    <div class="stat-value">💰 ${{ number_format($abonado, 0, '.', '.') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pendiente</div>
                    <div class="stat-value" style="color: #ffd700;">💳 ${{ number_format($pendiente, 0, '.', '.') }}</div>
                </div>
            </div>

            <div class="progreso-pago">
                <div style="margin-bottom: 10px; font-weight: 600; font-size: 0.95em;">Progreso de Pago</div>
                <div class="progress" style="height: 14px;">
                    <div class="progress-bar-custom" style="width: {{ $porcentaje }}%"></div>
                </div>
                <div style="text-align: center; font-weight: 700; font-size: 1.1em;">
                    {{ $porcentaje }}% Completado
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULARIO DE ABONO -->
    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formPago">
        @csrf
        @method('PUT')

        <div class="form-card card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i> Datos del Nuevo Abono
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">
                                <i class="fas fa-calendar-alt"></i> Fecha del Abono <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" 
                                   value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_metodo_pago_principal">
                                <i class="fas fa-credit-card"></i> Método de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                    id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}" 
                                            {{ $pago->id_metodo_pago_principal == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_metodo_pago_principal')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_abonado">
                                <i class="fas fa-money-bill-wave"></i> Monto del Abono <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado', $pago->monto_abonado) }}" required>
                            </div>
                            <small class="text-muted">Máximo: ${{ number_format($pendiente, 0, '.', '.') }}</small>
                            @error('monto_abonado')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referencia_pago">
                                <i class="fas fa-fingerprint"></i> Referencia/Comprobante
                            </label>
                            <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                                   id="referencia_pago" name="referencia_pago" maxlength="100"
                                   placeholder="TRF-2025-001"
                                   value="{{ old('referencia_pago', $pago->referencia_pago) }}">
                            @error('referencia_pago')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </label>
                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                              id="observaciones" name="observaciones" rows="3"
                              placeholder="Notas adicionales...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                    @error('observaciones')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="id_inscripcion" name="id_inscripcion" value="{{ $pago->id_inscripcion }}">
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">
        <input type="hidden" id="cantidad_cuotas" name="cantidad_cuotas" value="1">

        <!-- Botones -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-registrar ml-2">
                    <i class="fas fa-check"></i> Registrar Abono
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const montoInput = document.getElementById('monto_abonado');
    const pendiente = {{ $pendiente }};

    montoInput.addEventListener('change', function() {
        const monto = parseFloat(this.value);
        if (monto > pendiente) {
            alert('El monto no puede exceder $' + pendiente.toLocaleString('es-CO'));
            this.value = pendiente;
        }
    });
});
</script>
@endsection


