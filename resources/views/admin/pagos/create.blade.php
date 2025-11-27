@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .card-header {
            border-bottom: 2px solid rgba(0,0,0,.125);
        }
        .info-highlight {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-highlight strong {
            color: #007bff;
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
        .dynamic-section {
            transition: all 0.3s ease;
        }
        .dynamic-section.hidden {
            display: none;
        }
        .preview-card {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 6px;
            padding: 15px;
        }
        .cuota-row {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-left: 3px solid #28a745;
            border-radius: 3px;
            font-size: 13px;
        }
        .cuota-row strong {
            color: #28a745;
            margin-right: 10px;
        }
        .badge-custom {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .estado-badge {
            background: #e7f3ff;
            color: #0056b3;
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
    </style>
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-money-bill-wave"></i> Registrar Nuevo Pago
            </h1>
            <small class="text-muted d-block mt-2">Completa el formulario para registrar un pago, abono o plan de cuotas</small>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
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

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago" class="needs-validation">
        @csrf

        <!-- PASO 1: Seleccionar Inscripción -->
        <div class="card card-primary mb-4" id="cardInscripcion">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-step-forward"></i> Paso 1: Seleccionar Inscripción
                </h3>
            </div>
            <div class="card-body">
                @if($inscripcion)
                    <!-- Inscripción ya seleccionada -->
                    <div class="info-highlight">
                        <div class="row">
                            <div class="col-md-6">
                                <div><strong>Cliente:</strong> {{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</div>
                                <div class="mt-2"><strong>Email:</strong> {{ $inscripcion->cliente->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <div><strong>Membresía:</strong> {{ $inscripcion->membresia->nombre }}</div>
                                <div class="mt-2"><strong>Período:</strong> {{ $inscripcion->fecha_inicio->format('d/m/Y') }} - {{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="id_inscripcion" name="id_inscripcion" value="{{ $inscripcion->id }}" />
                @else
                    <div class="form-group form-group-required">
                        <label for="id_inscripcion"><i class="fas fa-search"></i> Buscar Inscripción</label>
                        <select class="form-control select2-inscripcion @error('id_inscripcion') is-invalid @enderror" 
                                id="id_inscripcion" name="id_inscripcion" required style="width: 100%;">
                            <option value="">-- Busca cliente o email (mín. 2 caracteres) --</option>
                        </select>
                        <span class="help-text">
                            <i class="fas fa-info-circle"></i> 
                            Busca por: <strong>nombre cliente</strong>, <strong>apellido</strong>, <strong>email</strong> o <strong>ID inscripción</strong>
                            <br>
                            ⚠️ Solo aparecen inscripciones con saldo pendiente
                        </span>
                        @error('id_inscripcion')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <!-- Información de Saldo (oculta hasta seleccionar inscripción) -->
                <div id="saldoInfo" class="mt-4 p-4 bg-light border rounded" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <small class="text-muted d-block mb-2"><i class="fas fa-info-circle"></i> <strong>Detalles de la Inscripción:</strong></small>
                            <div class="row">
                                <div class="col-md-6">
                                    <div><strong>Membresía:</strong> <span id="membresiaNombre" class="text-primary">-</span></div>
                                    <div class="mt-1"><strong>Período:</strong> <span id="periodoInscripcion" class="text-muted">-</span></div>
                                </div>
                                <div class="col-md-6">
                                    <div><strong>Cliente:</strong> <span id="clienteNombre" class="text-primary">-</span></div>
                                    <div class="mt-1"><strong>Email:</strong> <span id="clienteEmail" class="text-muted">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded border border-primary">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-tag"></i> Total a Pagar
                                </small>
                                <h5 id="totalAPagar" class="text-primary mb-0 font-weight-bold">$ 0.00</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded border border-success">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-check-circle"></i> Ya Abonado
                                </small>
                                <h5 id="totalAbonado" class="text-success mb-0 font-weight-bold">$ 0.00</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded border border-warning">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-exclamation-triangle"></i> Saldo Pendiente
                                </small>
                                <h5 id="saldoPendiente" class="text-warning mb-0 font-weight-bold">$ 0.00</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded border border-info">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-percent"></i> Porcentaje Pagado
                                </small>
                                <h5 id="porcentajePagado" class="text-info mb-0 font-weight-bold">0%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 2: Tipo de Pago y Método -->
        <div class="card card-info mb-4" id="cardMetodo" style="display: none;">
            <div class="card-header bg-info">
                <h3 class="card-title">
                    <i class="fas fa-step-forward"></i> Paso 2: Tipo de Pago y Método
                </h3>
            </div>
            <div class="card-body">
                <!-- Tipo de Pago -->
                <div class="mb-4">
                    <div class="section-title"><i class="fas fa-question-circle"></i> ¿Cómo deseas realizar el pago?</div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="tipoPagoSimple" name="tipo_pago" class="custom-control-input" value="simple" checked>
                                <label class="custom-control-label" for="tipoPagoSimple">
                                    <strong>Pago Simple o Abono</strong>
                                    <br><small class="text-muted">Un único pago de cualquier monto</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="tipoPagoCuotas" name="tipo_pago" class="custom-control-input" value="cuotas">
                                <label class="custom-control-label" for="tipoPagoCuotas">
                                    <strong>Plan de Cuotas</strong>
                                    <br><small class="text-muted">Dividir el pago en cuotas iguales</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="mb-4">
                    <div class="section-title"><i class="fas fa-credit-card"></i> Método de Pago</div>
                    
                    <div class="form-group form-group-required">
                        <select class="form-control @error('id_metodo_pago_principal') is-invalid @enderror" 
                                id="id_metodo_pago_principal" name="id_metodo_pago_principal" required>
                            <option value="">-- Seleccionar Método --</option>
                            @foreach($metodos_pago as $metodo)
                                <option value="{{ $metodo->id }}" title="{{ $metodo->nombre }}" data-codigo="{{ $metodo->codigo }}">
                                    <i class="fas @if($metodo->codigo === 'efectivo') fa-money-bill @elseif($metodo->codigo === 'tarjeta') fa-credit-card @elseif($metodo->codigo === 'transferencia') fa-university @else fa-ellipsis-h @endif"></i> 
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <span class="help-text"><i class="fas fa-info-circle"></i> Selecciona cómo fue pagado</span>
                        @error('id_metodo_pago_principal')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 3: Detalles del Pago -->
        <div class="card card-success mb-4" id="cardDetalles" style="display: none;">
            <div class="card-header bg-success">
                <h3 class="card-title">
                    <i class="fas fa-step-forward"></i> Paso 3: Detalles del Pago
                </h3>
            </div>
            <div class="card-body">
                <!-- Fila 1: Fecha y Monto -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group form-group-required">
                            <label for="fecha_pago"><i class="fas fa-calendar-alt"></i> Fecha del Pago</label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-group-required">
                            <label for="monto_abonado"><i class="fas fa-money-bill-wave"></i> Monto a Abonar</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado') }}" placeholder="0.00" required>
                            </div>
                            <span class="help-text"><i class="fas fa-info-circle"></i> Monto que deseas abonar</span>
                            @error('monto_abonado')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Referencia Pago -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="referencia_pago"><i class="fas fa-fingerprint"></i> Referencia del Pago</label>
                            <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                                   id="referencia_pago" name="referencia_pago" maxlength="100"
                                   placeholder="Ej: TRF-2025-001, Comprobante #123, etc."
                                   value="{{ old('referencia_pago') }}">
                            <span class="help-text"><i class="fas fa-info-circle"></i> Número de comprobante, transferencia, etc. (opcional pero recomendado)</span>
                            @error('referencia_pago')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección de Cuotas (mostrada solo si es plan de cuotas) -->
                <div id="seccionCuotas" class="dynamic-section hidden">
                    <div class="border-top pt-4 mt-4">
                        <div class="section-title"><i class="fas fa-list-ol"></i> Distribución en Cuotas</div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group form-group-required">
                                    <label for="cantidad_cuotas"><i class="fas fa-calculator"></i> Cantidad de Cuotas</label>
                                    <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                                           id="cantidad_cuotas" name="cantidad_cuotas" value="{{ old('cantidad_cuotas', 2) }}" 
                                           min="2" max="12">
                                    <span class="help-text"><i class="fas fa-info-circle"></i> Entre 2 y 12 cuotas</span>
                                    @error('cantidad_cuotas')
                                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="monto_por_cuota"><i class="fas fa-money-bill"></i> Monto por Cuota</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" 
                                               id="monto_por_cuota" step="0.01" readonly>
                                    </div>
                                    <span class="help-text"><i class="fas fa-lock"></i> Calculado automáticamente</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_vencimiento_cuota"><i class="fas fa-calendar-times"></i> Vencimiento 1ª Cuota</label>
                                    <input type="date" class="form-control @error('fecha_vencimiento_cuota') is-invalid @enderror" 
                                           id="fecha_vencimiento_cuota" name="fecha_vencimiento_cuota" 
                                           value="{{ old('fecha_vencimiento_cuota') }}">
                                    @error('fecha_vencimiento_cuota')
                                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Preview de Cuotas -->
                        <div class="preview-card mt-3">
                            <div class="mb-2"><strong><i class="fas fa-list"></i> Vista Previa de Cuotas:</strong></div>
                            <div id="previewCuotas" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-muted text-center py-3">
                                    <small>Completa los campos para ver la distribución de cuotas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones"><i class="fas fa-sticky-note"></i> Observaciones (Opcional)</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3"
                                      placeholder="Notas o comentarios adicionales sobre este pago...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden field para tipo_pago -->
        <input type="hidden" id="es_plan_cuotas" name="es_plan_cuotas" value="0">
        <input type="hidden" id="numero_cuota" name="numero_cuota" value="1">

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
                    <button type="reset" class="btn btn-outline-warning mr-2">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit" disabled>
                        <i class="fas fa-check-circle"></i> Registrar Pago
                    </button>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/pagos-create.js') }}"></script>
@endsection
