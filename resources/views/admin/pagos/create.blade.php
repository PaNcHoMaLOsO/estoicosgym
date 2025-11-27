@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

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
        .select2-container--default .select2-selection--single {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            height: 44px;
            padding: 6px;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
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
            <i class="fas fa-exclamation-circle"></i> <strong>¡Error!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- INFORMACIÓN DEL CLIENTE (dinámica) -->
    <div id="clienteHeader" class="cliente-header d-none">
        <div class="content">
            <div class="cliente-nombre">
                <i class="fas fa-user-circle"></i>
                <span id="clienteNombre"></span>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-label">Membresía</div>
                    <div class="stat-value"><span id="membresiaNombre"></span></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total a Pagar</div>
                    <div class="stat-value"><span id="montoTotal"></span></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Vencimiento</div>
                    <div class="stat-value"><span id="fechaVencimiento"></span></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Período</div>
                    <div class="stat-value"><span id="periodo"></span></div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- Selección de Inscripción -->
        <div class="form-card card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-search"></i> Buscar Cliente y Membresía
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="id_inscripcion">
                        <i class="fas fa-user-check"></i> Seleccionar Inscripción <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($inscripciones as $insc)
                            <option value="{{ $insc->id }}" 
                                    data-precio="{{ $insc->precio_final ?? $insc->precio_base }}"
                                    data-cliente="{{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }}"
                                    data-membresia="{{ $insc->membresia->nombre }}"
                                    data-inicio="{{ $insc->fecha_inicio->format('d/m/Y') }}"
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}">
                                {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Datos del Pago -->
        <div class="form-card card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i> Datos del Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">
                                <i class="fas fa-calendar-alt"></i> Fecha del Pago <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" 
                                   value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
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
                                            {{ old('id_metodo_pago_principal') == $metodo->id ? 'selected' : '' }}>
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
                                <i class="fas fa-money-bill-wave"></i> Monto a Abonar <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado') }}" placeholder="0.00" required>
                            </div>
                            @error('monto_abonado')
                                <small class="text-danger">{{ $message }}</small>
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
                                   value="{{ old('referencia_pago') }}">
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
                              placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">
        <input type="hidden" id="cantidad_cuotas" name="cantidad_cuotas" value="1">

        <!-- Botones -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-registrar ml-2">
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
    const clienteHeader = document.getElementById('clienteHeader');

    $('#id_inscripcion').select2({
        width: '100%',
        language: 'es',
        placeholder: '-- Buscar cliente --',
        allowClear: true
    });

    selectInscripcion.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const precio = parseFloat(option.getAttribute('data-precio'));
            const cliente = option.getAttribute('data-cliente');
            const membresia = option.getAttribute('data-membresia');
            const inicio = option.getAttribute('data-inicio');
            const vencimiento = option.getAttribute('data-vencimiento');

            document.getElementById('clienteNombre').textContent = cliente;
            document.getElementById('membresiaNombre').textContent = membresia;
            document.getElementById('montoTotal').textContent = '$' + precio.toLocaleString('es-CO', {minimumFractionDigits: 0});
            document.getElementById('fechaVencimiento').textContent = vencimiento;
            document.getElementById('periodo').textContent = inicio + ' a ' + vencimiento;

            clienteHeader.classList.remove('d-none');
        } else {
            clienteHeader.classList.add('d-none');
        }
    });
});
</script>
@endsection
