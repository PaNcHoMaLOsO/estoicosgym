@extends('adminlte::page')

@section('title', 'Editar Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .info-highlight {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .form-group-required label::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
        .read-only-field {
            background: #f8f9fa;
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            color: #495057;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-edit"></i> Editar Pago #{{ $pago->id }}
            </h1>
            <small class="text-muted d-block mt-1">Realizado el {{ $pago->fecha_pago->format('d/m/Y H:i') }}</small>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-info mr-2">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
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

    <form action="{{ route('admin.pagos.update', $pago) }}" method="POST" id="formPago">
        @csrf
        @method('PUT')

        <!-- Información de la Inscripción (Solo Lectura) -->
        <div class="card card-primary mb-4">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-user-check"></i> Información de la Inscripción
                </h3>
            </div>
            <div class="card-body">
                <div class="info-highlight">
                    <div class="row">
                        <div class="col-md-6">
                            <div><strong>Cliente:</strong> {{ $pago->inscripcion->cliente->nombres }} {{ $pago->inscripcion->cliente->apellido_paterno }}</div>
                            <div class="mt-2"><strong>Email:</strong> {{ $pago->inscripcion->cliente->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>Membresía:</strong> {{ $pago->inscripcion->membresia->nombre }}</div>
                            <div class="mt-2"><strong>Período:</strong> {{ $pago->inscripcion->fecha_inicio->format('d/m/Y') }} - {{ $pago->inscripcion->fecha_vencimiento->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Saldo -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Total a Pagar</small>
                            <h4 class="text-primary mb-0">$ {{ number_format($pago->inscripcion->precio_final ?? $pago->inscripcion->precio_base, 0, '.', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Total Abonado (Incl. Este)</small>
                            <h4 class="text-success mb-0">$ {{ number_format($pago->inscripcion->getTotalAbonado(), 0, '.', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <small class="text-muted d-block">Saldo Pendiente</small>
                            <h4 class="text-warning mb-0">$ {{ number_format($pago->inscripcion->getSaldoPendiente(), 0, '.', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos Editable del Pago -->
        <div class="card card-success mb-4">
            <div class="card-header bg-success">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i> Datos del Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="section-title"><i class="fas fa-info-circle"></i> Información de Pago</div>

                <!-- Fila 1: Fecha y Método -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group form-group-required">
                            <label for="fecha_pago"><i class="fas fa-calendar-alt"></i> Fecha del Pago</label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', $pago->fecha_pago->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-group-required">
                            <label for="id_metodo_pago_principal"><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                    id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                                <option value="">-- Seleccionar Método --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}" {{ old('id_metodo_pago_principal', $pago->id_metodo_pago_principal) == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_metodo_pago_principal')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Monto Abonado -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group form-group-required">
                            <label for="monto_abonado"><i class="fas fa-money-bill-wave"></i> Monto Abonado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado', $pago->monto_abonado) }}" required>
                            </div>
                            <span class="help-text"><i class="fas fa-info-circle"></i> Monto registrado en este pago</span>
                            @error('monto_abonado')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referencia_pago"><i class="fas fa-fingerprint"></i> Referencia del Pago</label>
                            <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                                   id="referencia_pago" name="referencia_pago" maxlength="100"
                                   placeholder="Ej: TRF-2025-001, Comprobante #123"
                                   value="{{ old('referencia_pago', $pago->referencia_pago) }}">
                            <span class="help-text"><i class="fas fa-info-circle"></i> Número de comprobante o transferencia (opcional)</span>
                            @error('referencia_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Información de Cuotas (si aplica) -->
                @if($pago->es_plan_cuotas)
                    <div class="border-top pt-4 mt-4">
                        <div class="section-title"><i class="fas fa-list-ol"></i> Información de Plan de Cuotas</div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-list-ol"></i> Cuota Número</label>
                                    <div class="read-only-field">
                                        <strong>#{{ $pago->numero_cuota }} de {{ $pago->cantidad_cuotas }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-money-bill"></i> Monto por Cuota</label>
                                    <div class="read-only-field">
                                        $ {{ number_format($pago->monto_abonado / $pago->cantidad_cuotas, 0, '.', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-times"></i> Vencimiento</label>
                                    <div class="read-only-field">
                                        {{ $pago->fecha_vencimiento_cuota ? $pago->fecha_vencimiento_cuota->format('d/m/Y') : 'Sin especificar' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Observaciones -->
                <div class="mt-4">
                    <div class="form-group">
                        <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" rows="3"
                                  placeholder="Notas o comentarios adicionales...">{{ old('observaciones', $pago->observaciones) }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="card card-light mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="d-block text-muted mb-2">
                            <i class="fas fa-plus-circle"></i> <strong>Creado:</strong> {{ $pago->created_at->format('d/m/Y H:i:s') }}
                        </small>
                        <small class="d-block text-muted">
                            <i class="fas fa-sync"></i> <strong>Última actualización:</strong> {{ $pago->updated_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Botones de Acción -->
        <div class="row sticky-bottom bg-white p-3 border-top">
            <div class="col-12 d-flex justify-content-between">
                <div>
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-outline-info mr-2">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
