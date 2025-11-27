@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="{{ asset('js/precio-formatter.js') }}"></script>
    <style>
        /* ===== STEP PROGRESS INDICATOR ===== */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        .step-progress::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #6c757d 100%);
            z-index: 0;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-number {
            width: 45px;
            height: 45px;
            margin: 0 auto 10px;
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .step-item.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .step-item.completed .step-number {
            background: #28a745;
            border-color: #20c997;
            color: white;
        }
        .step-label {
            font-size: 0.85em;
            color: #6c757d;
            font-weight: 600;
        }
        .step-item.active .step-label {
            color: #667eea;
            font-weight: 700;
        }

        /* ===== STEP CARDS ===== */
        .step-card {
            border: none;
            border-top: 4px solid #667eea;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .step-card.primary { border-top-color: #667eea; }
        .step-card.info { border-top-color: #17a2b8; }
        .step-card.warning { border-top-color: #ffc107; }
        .step-card.success { border-top-color: #28a745; }
        .step-card.secondary { border-top-color: #6c757d; }
        
        .step-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0;
            padding: 20px;
            margin: 0;
        }
        .step-header h3 {
            margin: 0;
            font-size: 1.2em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-header.info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
        .step-header.warning { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
        .step-header.success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .step-header.secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); }

        /* ===== CONDITIONAL SECTIONS ===== */
        .pago-section {
            animation: slideDown 0.3s ease;
        }
        .d-none {
            display: none !important;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== TIPO PAGO CARDS ===== */
        .tipo-pago-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .tipo-pago-card {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            background: white;
        }
        .tipo-pago-card:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }
        .tipo-pago-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .tipo-pago-card input[type="radio"] {
            margin-right: 8px;
        }
        .tipo-pago-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }
        .tipo-pago-icon {
            font-size: 1.5em;
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tipo-pago-text {
            text-align: left;
        }
        .tipo-pago-title {
            font-weight: 700;
            font-size: 0.95em;
        }
        .tipo-pago-desc {
            font-size: 0.8em;
            color: #6c757d;
        }

        /* ===== PRICE SUMMARY ===== */
        .price-summary {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border-left: 4px solid #667eea;
        }
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-size: 0.95em;
        }
        .price-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.15em;
            color: #28a745;
            padding-top: 15px;
            border-top: 2px solid #667eea;
        }

        /* ===== ALERTS ===== */
        .info-alert {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: #1565c0;
            font-weight: 500;
        }
        .resumen-pago {
            background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%);
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-weight: 600;
            color: #333;
        }

        /* ===== FORM STYLING ===== */
        .form-label {
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            font-size: 1rem;
            display: block;
        }
        
        .form-control {
            border: 2px solid #d1d5db;
            border-radius: 8px;
            padding: 11px 14px;
            transition: all 0.3s ease;
            font-size: 1rem;
            height: auto;
            min-height: 44px;
            background-color: #fff;
            color: #333;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .form-control:hover:not(:disabled) {
            border-color: #667eea;
            box-shadow: 0 1px 4px rgba(102, 126, 234, 0.15);
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #999;
            font-weight: normal;
        }
        
        .form-control:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
            border-color: #e5e7eb;
        }
        
        .form-control[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: default;
        }
        
        /* Input group styling */
        .input-group-text {
            background-color: #f3f4f6;
            border: 2px solid #d1d5db;
            border-right: none;
            color: #667eea;
            font-weight: 600;
            padding: 9px 14px;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group .form-control:focus {
            border-left: 2px solid #667eea;
        }

        /* ===== BUTTONS ===== */
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
    </style>
@stop

@section('content_header')
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="row">
                <div class="col-md-8">
                    <div class="hero-title"><i class="fas fa-plus-circle"></i> Crear Nueva Inscripción</div>
                    <div class="hero-subtitle">Completa el formulario paso a paso</div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Errores:</strong>
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

        <!-- STEP PROGRESS INDICATOR -->
        <div class="step-progress mb-5">
            <div class="step-item active" id="step-1-indicator">
                <div class="step-number">1</div>
                <div class="step-label">Cliente y Membresía</div>
            </div>
            <div class="step-item" id="step-2-indicator">
                <div class="step-number">2</div>
                <div class="step-label">Fechas</div>
            </div>
            <div class="step-item" id="step-3-indicator">
                <div class="step-number">3</div>
                <div class="step-label">Tipo de Pago</div>
            </div>
            <div class="step-item" id="step-4-indicator">
                <div class="step-number">4</div>
                <div class="step-label">Confirmar</div>
            </div>
        </div>

        <!-- PASO 1: INFORMACIÓN DEL CLIENTE Y MEMBRESÍA -->
        <div class="card step-card primary mb-4">
            <div class="step-header">
                <h3><i class="fas fa-user-check"></i> Paso 1: Selecciona Cliente y Membresía</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label"><i class="fas fa-user"></i> Cliente <span class="text-danger">*</span></label>
                        <select class="form-control select2-cliente @error('id_cliente') is-invalid @enderror" 
                                id="id_cliente" name="id_cliente" required style="width: 100%;">
                            <option value="">-- Seleccionar Cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} 
                                    @if($cliente->apellido_materno) {{ $cliente->apellido_materno }} @endif
                                    @if($cliente->run_pasaporte)- {{ $cliente->run_pasaporte }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label"><i class="fas fa-layer-group"></i> Membresía <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                id="id_membresia" name="id_membresia" required>
                            <option value="">-- Seleccionar Membresía --</option>
                            @foreach($membresias as $membresia)
                                <option value="{{ $membresia->id }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                    {{ $membresia->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_membresia')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- RESUMEN DE PRECIOS (Dinámico) -->
                <div id="priceSummary" class="price-summary" style="display: none; margin-top: 25px;">
                    <div class="price-item">
                        <span><i class="fas fa-dollar-sign"></i> Precio Base:</span>
                        <span id="precioBase">$0.00</span>
                    </div>
                    <div class="price-item">
                        <span><i class="fas fa-minus-circle"></i> Descuentos:</span>
                        <span id="precioDescuento">$0.00</span>
                    </div>
                    <div class="price-item">
                        <span><i class="fas fa-check-circle"></i> Precio Total:</span>
                        <span id="precioTotal">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 2: FECHAS Y DESCUENTOS -->
        <div class="card step-card info mb-4">
            <div class="step-header info">
                <h3><i class="fas fa-calendar-alt"></i> Paso 2: Fechas y Descuentos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" 
                               required>
                        @error('fecha_inicio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label"><i class="fas fa-calendar-times"></i> Fecha Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" readonly>
                        <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Se calcula automáticamente</small>
                    </div>
                </div>

                <hr class="my-4">

                @if($convenios->count() > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-handshake"></i> Convenio (Solo membresía mensual)</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" data-nombre="{{ $convenio->nombre }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} - {{ $convenio->tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">Se aplicará descuento automático de $15.000 si aplica</small>
                        </div>
                    </div>
                    <hr class="my-4">
                @endif

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Descuento Manual (Opcional)</label>
                        <div class="input-group" style="height: 44px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" 
                                   id="descuento_adicional" name="descuento_aplicado" step="0.01" min="0" 
                                   placeholder="0.00" value="0">
                        </div>
                        <small class="text-muted d-block mt-2" id="descuento_info">El descuento de convenio se aplica automáticamente</small>
                    </div>

                    <div class="col-lg-6 mb-4">
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

        <!-- Estado está oculto, siempre será "Activa" -->
        <input type="hidden" name="id_estado" value="{{ $estadoActiva->id }}">

        <!-- PASO 3: TIPO DE PAGO -->
        <div class="card step-card warning mb-4">
            <div class="step-header warning">
                <h3><i class="fas fa-hand-holding-usd"></i> Paso 3: Selecciona Tipo de Pago</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4"><i class="fas fa-info-circle"></i> Elige cómo deseas realizar el pago de la membresía.</p>
                
                <div class="tipo-pago-group">
                    <label class="tipo-pago-card active" id="card-abono">
                        <input type="radio" name="tipo_pago" value="abono" checked>
                        <div class="tipo-pago-label">
                            <div class="tipo-pago-icon" style="color: #fbbf24;">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="tipo-pago-text">
                                <div class="tipo-pago-title">Abono Parcial</div>
                                <div class="tipo-pago-desc">Pagar una cantidad menor</div>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-completo">
                        <input type="radio" name="tipo_pago" value="completo">
                        <div class="tipo-pago-label">
                            <div class="tipo-pago-icon" style="color: #10b981;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="tipo-pago-text">
                                <div class="tipo-pago-title">Pago Completo</div>
                                <div class="tipo-pago-desc">Pagar todo ahora</div>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-mixto">
                        <input type="radio" name="tipo_pago" value="mixto">
                        <div class="tipo-pago-label">
                            <div class="tipo-pago-icon" style="color: #8b5cf6;">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="tipo-pago-text">
                                <div class="tipo-pago-title">Pago Mixto</div>
                                <div class="tipo-pago-desc">Múltiples métodos</div>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-pendiente">
                        <input type="radio" name="tipo_pago" value="pendiente">
                        <div class="tipo-pago-label">
                            <div class="tipo-pago-icon" style="color: #ef4444;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="tipo-pago-text">
                                <div class="tipo-pago-title">Pago Pendiente</div>
                                <div class="tipo-pago-desc">Pagar después</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- PASO 4: DETALLE DEL PAGO -->
        <div class="card step-card success mb-4">
            <div class="step-header success">
                <h3><i class="fas fa-money-bill-wave"></i> Paso 4: Confirmar Pago</h3>
            </div>
            <div class="card-body">
                <!-- SECCIÓN ABONO PARCIAL -->
                <div id="seccion-abono" class="pago-section">
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-calendar"></i> Fecha Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                            @error('fecha_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-money-bill-wave"></i> Monto Abonado <span class="text-danger">*</span></label>
                            <div class="input-group" style="height: 44px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" step="0.01" min="0.01" 
                                       value="{{ old('monto_abonado') }}" placeholder="0.00" required>
                            </div>
                            @error('monto_abonado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-credit-card"></i> Método Pago <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_metodo_pago') is-invalid @enderror" 
                                    id="id_metodo_pago" name="id_metodo_pago" required>
                                <option value="">-- Seleccionar Método --</option>
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                        {{ $metodo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_metodo_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Cuotas para abono parcial -->
                    <div id="cuotasSection" class="conditional-field" style="margin-top: 30px; padding-top: 25px; border-top: 2px solid #e9ecef;">
                        <h5 class="mb-4"><i class="fas fa-receipt"></i> Información de Cuotas</h5>
                        <div class="row">
                            <div class="col-lg-4 mb-4">
                                <label class="form-label"><i class="fas fa-divide"></i> Cantidad Cuotas</label>
                                <input type="number" class="form-control @error('cantidad_cuotas') is-invalid @enderror" 
                                       id="cantidad_cuotas" name="cantidad_cuotas" min="2" max="12" value="1">
                                <small class="text-muted d-block mt-2">Mínimo 2, máximo 12</small>
                                @error('cantidad_cuotas')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4 mb-4">
                                <label class="form-label"><i class="fas fa-receipt"></i> Monto por Cuota</label>
                                <div class="input-group" style="height: 44px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="monto_cuota" readonly>
                                </div>
                                <small class="text-muted d-block mt-2">Calculado automáticamente</small>
                            </div>

                            <div class="col-lg-4 mb-4">
                                <label class="form-label"><i class="fas fa-calendar-times"></i> Vencimiento Cuota</label>
                                <input type="date" class="form-control @error('fecha_vencimiento_cuota') is-invalid @enderror" 
                                       id="fecha_vencimiento_cuota" name="fecha_vencimiento_cuota">
                                @error('fecha_vencimiento_cuota')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="resumen-pago alert alert-info mt-4" id="resumen-abono" style="display: none;">
                        <strong><i class="fas fa-info-circle"></i> Resumen:</strong> 
                        Abonado: $<span id="nuevo-abonado">0</span> | Pendiente: $<span id="nuevo-pendiente">0</span>
                    </div>
                </div>

                <!-- SECCIÓN PAGO COMPLETO -->
                <div id="seccion-completo" class="pago-section d-none">
                    <div class="info-alert">
                        <i class="fas fa-check-circle"></i> Se pagará el saldo completo de la membresía
                    </div>
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-calendar"></i> Fecha Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago_completo" 
                                   value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-money-bill-wave"></i> Monto (Automático)</label>
                            <div class="input-group" style="height: 44px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control bg-light" id="monto_completo" disabled>
                            </div>
                            <small class="text-muted d-block mt-2">✓ Se pagará automáticamente</small>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <label class="form-label"><i class="fas fa-credit-card"></i> Método Pago <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_metodo_pago_completo" name="id_metodo_pago_completo" required>
                                <option value="">-- Seleccionar Método --</option>
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="resumen-pago alert alert-success mt-4">
                        <strong>✓ Resultado:</strong> El cliente quedará PAGADO COMPLETAMENTE
                    </div>
                </div>

                <!-- SECCIÓN PAGO MIXTO -->
                <div id="seccion-mixto" class="pago-section d-none">
                    <div class="info-alert">
                        <i class="fas fa-info-circle"></i> Divide el pago entre diferentes métodos. La suma debe ser exacta.
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <label class="form-label"><i class="fas fa-calendar"></i> Fecha Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago_mixto" 
                                   value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-lg-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <label class="form-label"><i class="fas fa-credit-card"></i> Método 1 - Monto</label>
                            <div class="input-group" style="height: 44px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control monto-mixto" id="monto_metodo1" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label">Seleccionar Método</label>
                            <select class="form-control" id="metodo_pago_1">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <label class="form-label"><i class="fas fa-credit-card"></i> Método 2 - Monto</label>
                            <div class="input-group" style="height: 44px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control monto-mixto" id="monto_metodo2" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label">Seleccionar Método</label>
                            <select class="form-control" id="metodo_pago_2">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="resumen-pago alert alert-warning mt-3">
                        <strong><i class="fas fa-exclamation-triangle"></i> Verificar:</strong> 
                        Total: $<span id="total-mixto">0</span> debe ser igual al monto total de la inscripción
                    </div>
                </div>

                <!-- SECCIÓN PAGO PENDIENTE -->
                <div id="seccion-pendiente" class="pago-section d-none">
                    <div class="alert alert-warning">
                        <i class="fas fa-clock"></i> <strong>Pago Pendiente</strong> - El cliente pagará la membresía posteriormente
                    </div>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> La inscripción se creará sin pago. Podrás registrar el pago después desde la vista de inscripción.</p>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <hr class="my-4">
        <div class="row">
            <div class="col-12">
                <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-registrar btn-lg">
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
    // ===== ELEMENTOS DEL FORMULARIO =====
    const idMembresia = document.getElementById('id_membresia');
    const fechaInicio = document.getElementById('fecha_inicio');
    const descuentoAdicional = document.getElementById('descuento_adicional');
    const cantidadCuotas = document.getElementById('cantidad_cuotas');
    const cuotasSection = document.getElementById('cuotasSection');
    const priceSummary = document.getElementById('priceSummary');
    const idConvenio = document.getElementById('id_convenio');
    const idMotivoDescuento = document.getElementById('id_motivo_descuento');
    
    // Elementos para mostrar precios
    const precioBaseEl = document.getElementById('precioBase');
    const precioDescuentoEl = document.getElementById('precioDescuento');
    const precioTotalEl = document.getElementById('precioTotal');
    const montoCuotaEl = document.getElementById('monto_cuota');
    const fechaVencimientoEl = document.getElementById('fecha_vencimiento');

    // Radio buttons tipo de pago
    const tipoPagoRadios = document.querySelectorAll('input[name="tipo_pago"]');
    const seccionAbono = document.getElementById('seccion-abono');
    const seccionCompleto = document.getElementById('seccion-completo');
    const seccionMixto = document.getElementById('seccion-mixto');
    const seccionPendiente = document.getElementById('seccion-pendiente');

    let precioTotalInscripcion = 0;

    // ===== INICIALIZAR SELECT2 =====
    $('#id_cliente').select2({
        width: '100%',
        allowClear: true,
        language: 'es',
        placeholder: '-- Seleccionar Cliente --',
        minimumInputLength: 0,
        dropdownAutoWidth: false,
        containerCssClass: 'select2-container-custom',
        dropdownCssClass: 'select2-dropdown-custom'
    });
    
    // Agregar CSS personalizado COMPLETO para Select2 y Form Controls
    const style = document.createElement('style');
    style.textContent = `
        /* ===== SELECT2 STYLING ===== */
        .select2-container-custom {
            width: 100% !important;
        }
        
        .select2-container-custom .select2-selection--single {
            border: 2px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 0 !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fff !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        
        .select2-container-custom .select2-selection--single:hover {
            border-color: #667eea !important;
        }
        
        .select2-container-custom.select2-container--open .select2-selection--single {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }
        
        .select2-container-custom .select2-selection--single .select2-selection__rendered {
            padding: 0 12px !important;
            line-height: 42px !important;
            font-size: 1.1rem !important;
            color: #333 !important;
            font-weight: 600 !important;
        }
        
        .select2-membresia .select2-selection--single .select2-selection__rendered {
            font-size: 1.2rem !important;
            font-weight: 700 !important;
        }
        
        .select2-container-custom .select2-selection--single .select2-selection__placeholder {
            color: #999 !important;
            font-weight: normal !important;
        }
        
        .select2-container-custom .select2-selection__clear {
            margin-right: 8px !important;
            cursor: pointer !important;
        }
        
        .select2-container-custom .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 12px !important;
            width: auto !important;
        }
        
        .select2-container-custom .select2-selection--single .select2-selection__arrow b {
            border-color: #667eea transparent transparent transparent !important;
            margin-top: 8px !important;
            border-width: 5px 4px 0 4px !important;
        }
        
        /* Dropdown styling */
        .select2-dropdown-custom {
            border: 2px solid #d1d5db !important;
            border-top: none !important;
            border-radius: 0 0 8px 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            z-index: 9999 !important;
            background: #fff !important;
        }
        
        .select2-dropdown-custom .select2-results__options {
            padding: 8px 0 !important;
        }
        
        .select2-dropdown-custom .select2-results__option {
            padding: 14px 18px !important;
            font-size: 1.1rem !important;
            line-height: 1.6 !important;
            color: #333 !important;
            transition: background-color 0.2s ease !important;
            font-weight: 500 !important;
        }
        
        .select2-dropdown-custom .select2-results__option:hover {
            background-color: #f0f4ff !important;
            color: #333 !important;
        }
        
        .select2-dropdown-custom .select2-results__option--highlighted[aria-selected] {
            background-color: #667eea !important;
            color: white !important;
        }
        
        .select2-dropdown-custom .select2-results__option[aria-selected=true] {
            background-color: #f0f0f0 !important;
            color: #333 !important;
        }
        
        .select2-dropdown-custom .select2-results__option[aria-selected=true]:hover {
            background-color: #667eea !important;
            color: white !important;
        }
        
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            padding: 8px 12px !important;
            font-size: 1rem !important;
            margin: 8px !important;
            background-color: #f9fafb !important;
        }
        
        .select2-search--dropdown .select2-search__field:focus {
            outline: none !important;
            border-color: #667eea !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }
        
        /* ===== FORM CONTROLS STYLING ===== */
        .form-control {
            border: 2px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 10px 14px !important;
            font-size: 1rem !important;
            height: auto !important;
            min-height: 44px !important;
            background-color: #fff !important;
            transition: all 0.3s ease !important;
            font-weight: 500 !important;
            color: #333 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        
        .form-control:hover {
            border-color: #667eea !important;
            box-shadow: 0 1px 4px rgba(102, 126, 234, 0.15) !important;
        }
        
        .form-control:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
            outline: none !important;
        }
        
        .form-control::placeholder {
            color: #999 !important;
            font-weight: normal !important;
        }
        
        .form-control:disabled {
            background-color: #f3f4f6 !important;
            color: #6b7280 !important;
            cursor: not-allowed !important;
            border-color: #e5e7eb !important;
        }
        
        /* Input group styling */
        .input-group-text {
            background-color: #f3f4f6 !important;
            border: 2px solid #d1d5db !important;
            border-right: none !important;
            color: #667eea !important;
            font-weight: 600 !important;
        }
        
        .input-group .form-control {
            border-left: none !important;
        }
        
        .input-group .form-control:focus {
            border-left: 2px solid #667eea !important;
        }
        
        /* Readonly input styling */
        .form-control[readonly] {
            background-color: #f3f4f6 !important;
            color: #6b7280 !important;
            cursor: default !important;
        }
        
        .bg-light {
            background-color: #f3f4f6 !important;
        }
    `;
    document.head.appendChild(style);
    
    // ===== FORZAR FECHA HOY AL CARGAR =====
    // Asegurar que siempre sea hoy
    fechaInicio.value = new Date().toISOString().split('T')[0];

    // ===== INICIALIZAR SELECT2 PARA OTROS SELECTS =====
    // Membresía
    $('#id_membresia').select2({
        width: '100%',
        language: 'es',
        placeholder: '-- Seleccionar Membresía --',
        containerCssClass: 'select2-container-custom select2-membresia',
        dropdownCssClass: 'select2-dropdown-custom',
        minimumResultsForSearch: -1
    });
    
    // Convenios
    if ($('#id_convenio').length) {
        $('#id_convenio').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    // Motivo Descuento
    if ($('#id_motivo_descuento').length) {
        $('#id_motivo_descuento').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    // Método Pago (Abono)
    if ($('#id_metodo_pago').length) {
        $('#id_metodo_pago').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    // Método Pago Completo
    if ($('#id_metodo_pago_completo').length) {
        $('#id_metodo_pago_completo').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    // Método Pago Mixto 1
    if ($('#metodo_pago_1').length) {
        $('#metodo_pago_1').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    // Método Pago Mixto 2
    if ($('#metodo_pago_2').length) {
        $('#metodo_pago_2').select2({
            width: '100%',
            language: 'es',
            containerCssClass: 'select2-container-custom',
            dropdownCssClass: 'select2-dropdown-custom'
        });
    }
    
    PrecioFormatter.iniciarCampo('descuento_adicional', false);

    // ===== EVENT LISTENERS PARA TIPO DE PAGO =====
    tipoPagoRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            cambiarTipoPago(this.value);
        });
    });

    // ===== FUNCIÓN: CAMBIAR TIPO DE PAGO =====
    function cambiarTipoPago(tipoPago) {
        // Actualizar clases activas en cards
        document.querySelectorAll('.tipo-pago-card').forEach(card => {
            card.classList.remove('active');
        });
        document.querySelector(`#card-${tipoPago}`).classList.add('active');

        // Mostrar/ocultar secciones
        seccionAbono.classList.add('d-none');
        seccionCompleto.classList.add('d-none');
        seccionMixto.classList.add('d-none');
        seccionPendiente.classList.add('d-none');

        // Limpiar validaciones requeridas
        document.getElementById('fecha_pago')?.removeAttribute('required');
        document.getElementById('monto_abonado')?.removeAttribute('required');
        document.getElementById('id_metodo_pago')?.removeAttribute('required');
        document.getElementById('fecha_pago_completo')?.removeAttribute('required');
        document.getElementById('id_metodo_pago_completo')?.removeAttribute('required');
        document.getElementById('fecha_pago_mixto')?.removeAttribute('required');

        switch(tipoPago) {
            case 'abono':
                seccionAbono.classList.remove('d-none');
                document.getElementById('fecha_pago').setAttribute('required', 'required');
                document.getElementById('monto_abonado').setAttribute('required', 'required');
                document.getElementById('id_metodo_pago').setAttribute('required', 'required');
                break;
            case 'completo':
                seccionCompleto.classList.remove('d-none');
                document.getElementById('fecha_pago_completo').setAttribute('required', 'required');
                document.getElementById('id_metodo_pago_completo').setAttribute('required', 'required');
                actualizarMontoCompleto();
                break;
            case 'mixto':
                seccionMixto.classList.remove('d-none');
                document.getElementById('fecha_pago_mixto').setAttribute('required', 'required');
                break;
            case 'pendiente':
                seccionPendiente.classList.remove('d-none');
                break;
        }
    }

    // ===== CARGAR PRECIO MEMBRESÍA =====
    async function cargarPrecioMembresia() {
        if (!idMembresia.value) {
            priceSummary.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/membresias/${idMembresia.value}`);
            const data = await response.json();

            if (response.ok) {
                const precioBase = parseFloat(data.precio_normal) || 0;
                
                let descuentoConvenioPreview = 0;
                if (idConvenio.value && data.id === 1) {
                    descuentoConvenioPreview = 15000;
                }
                
                const descuentoAdicionalManual = parseFloat(descuentoAdicional.value) || 0;
                const descuentoTotalPreview = descuentoConvenioPreview + descuentoAdicionalManual;
                precioTotalInscripcion = precioBase - descuentoTotalPreview;

                precioBaseEl.textContent = '$' + PrecioFormatter.formatear(precioBase);
                precioDescuentoEl.textContent = '$' + PrecioFormatter.formatear(descuentoTotalPreview);
                precioTotalEl.textContent = '$' + PrecioFormatter.formatear(precioTotalInscripcion);
                
                priceSummary.style.display = 'block';
                calcularVencimiento();
                actualizarMontoCompleto();
                actualizarMontosCuotas();
                validarPagoCompleto();
            }
        } catch (error) {
            console.error('Error al cargar precio:', error);
        }
    }

    // ===== CALCULAR FECHA DE VENCIMIENTO =====
    async function calcularVencimiento() {
        if (!idMembresia.value || !fechaInicio.value) return;

        try {
            const response = await fetch(`/api/membresias/${idMembresia.value}`);
            const membresiaData = await response.json();

            if (response.ok) {
                const [year, month, day] = fechaInicio.value.split('-').map(Number);
                const fechaInicioParsed = new Date(year, month - 1, day);
                
                const duracionDias = membresiaData.duracion_dias || (membresiaData.duracion_meses * 30);
                const duracionMeses = membresiaData.duracion_meses || 1;
                
                const fechaVencimientoPreview = new Date(fechaInicioParsed);
                
                if (duracionMeses === 0) {
                    fechaVencimientoPreview.setDate(fechaVencimientoPreview.getDate() + duracionDias - 1);
                } else {
                    fechaVencimientoPreview.setMonth(fechaVencimientoPreview.getMonth() + duracionMeses);
                    fechaVencimientoPreview.setDate(fechaVencimientoPreview.getDate() - 1);
                }
                
                const yearFormato = fechaVencimientoPreview.getFullYear();
                const monthFormato = String(fechaVencimientoPreview.getMonth() + 1).padStart(2, '0');
                const dayFormato = String(fechaVencimientoPreview.getDate()).padStart(2, '0');
                const fechaVencimientoFormato = `${yearFormato}-${monthFormato}-${dayFormato}`;
                
                fechaVencimientoEl.value = fechaVencimientoFormato;
                fechaVencimientoEl.setAttribute('readonly', 'readonly');
            }
        } catch (error) {
            console.error('Error al calcular vencimiento:', error);
        }
    }

    // ===== ACTUALIZAR MONTO COMPLETO =====
    function actualizarMontoCompleto() {
        const montoCompletoEl = document.getElementById('monto_completo');
        if (montoCompletoEl) {
            montoCompletoEl.value = '$' + PrecioFormatter.formatear(precioTotalInscripcion);
        }
    }

    // ===== ACTUALIZAR MONTOS DE CUOTAS =====
    function actualizarMontosCuotas() {
        if (!cuotasSection || cuotasSection.classList.contains('d-none')) return;
        
        const cantidad = parseInt(cantidadCuotas.value) || 1;
        const montoPorCuota = precioTotalInscripcion / cantidad;
        
        montoCuotaEl.value = PrecioFormatter.formatear(montoPorCuota);
        
        // Mostrar resumen
        const resumenAbono = document.getElementById('resumen-abono');
        if (resumenAbono) {
            const montoAbonado = parseFloat(document.getElementById('monto_abonado').value) || 0;
            const pendiente = precioTotalInscripcion - montoAbonado;
            
            document.getElementById('nuevo-abonado').textContent = PrecioFormatter.formatear(montoAbonado);
            document.getElementById('nuevo-pendiente').textContent = PrecioFormatter.formatear(Math.max(0, pendiente));
            resumenAbono.style.display = 'block';
        }
    }

    // ===== VALIDAR PAGO COMPLETO =====
    function validarPagoCompleto() {
        const montoAbonado = parseFloat(document.getElementById('monto_abonado')?.value) || 0;
        const pendiente = precioTotalInscripcion - montoAbonado;
        
        if (pendiente > 0) {
            cuotasSection?.classList.add('visible');
        } else {
            cuotasSection?.classList.remove('visible');
        }
    }

    // ===== EVENT LISTENERS =====
    idMembresia.addEventListener('change', function() {
        cargarPrecioMembresia();
        calcularVencimiento();
    });
    
    // También escuchar cambios de Select2
    $('#id_membresia').on('change', function() {
        cargarPrecioMembresia();
        calcularVencimiento();
    });
    
    fechaInicio.addEventListener('change', calcularVencimiento);
    descuentoAdicional.addEventListener('change', cargarPrecioMembresia);
    descuentoAdicional.addEventListener('input', cargarPrecioMembresia);
    idConvenio.addEventListener('change', cargarPrecioMembresia);
    
    cantidadCuotas.addEventListener('change', actualizarMontosCuotas);
    cantidadCuotas.addEventListener('input', actualizarMontosCuotas);

    document.getElementById('monto_abonado')?.addEventListener('change', validarPagoCompleto);
    document.getElementById('monto_abonado')?.addEventListener('input', validarPagoCompleto);

    // Evento para actualizar total mixto
    document.querySelectorAll('.monto-mixto').forEach(input => {
        input.addEventListener('change', function() {
            const monto1 = parseFloat(document.getElementById('monto_metodo1')?.value) || 0;
            const monto2 = parseFloat(document.getElementById('monto_metodo2')?.value) || 0;
            const total = monto1 + monto2;
            
            document.getElementById('total-mixto').textContent = PrecioFormatter.formatear(total);
        });
        input.addEventListener('input', function() {
            const monto1 = parseFloat(document.getElementById('monto_metodo1')?.value) || 0;
            const monto2 = parseFloat(document.getElementById('monto_metodo2')?.value) || 0;
            const total = monto1 + monto2;
            
            document.getElementById('total-mixto').textContent = PrecioFormatter.formatear(total);
        });
    });

    // Inicializar primera sección
    cambiarTipoPago('abono');
});
</script>
@stop
