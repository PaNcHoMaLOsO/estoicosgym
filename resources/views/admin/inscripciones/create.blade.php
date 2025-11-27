@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="{{ asset('js/precio-formatter.js') }}"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* ===== PAGE CONTAINER ===== */
        .content-wrapper {
            background-color: #f8f9fa;
        }

        /* ===== STEP PROGRESS - MINIMALISTA ===== */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px;
            position: relative;
        }
        .step-progress::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 5%;
            right: 5%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            margin: 0 auto 8px;
            background: #ffffff;
            border: 2px solid #d0d0d0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #999;
            font-size: 0.9em;
            transition: all 0.25s ease;
        }
        .step-item.active .step-number {
            background: #667eea;
            border-color: #667eea;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.25);
        }
        .step-item.completed .step-number {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        .step-label {
            font-size: 0.8em;
            color: #666;
            font-weight: 500;
        }
        .step-item.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        /* ===== CARDS - LIMPIO Y ELEGANTE ===== */
        .step-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: box-shadow 0.25s ease;
            overflow: hidden;
            background: white;
        }
        .step-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        
        .step-header {
            background: white;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            padding: 18px 20px;
            margin: 0;
        }
        .step-header h3 {
            margin: 0;
            font-size: 1.05em;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .step-header i {
            color: #667eea;
            font-size: 1.1em;
        }
        .step-body {
            padding: 20px;
        }

        /* ===== FORM GROUPS ===== */
        .form-group label {
            font-weight: 500;
            color: #333;
            font-size: 0.95em;
            margin-bottom: 6px;
        }
        .form-group small {
            color: #999;
            font-size: 0.85em;
        }

        /* ===== TIPO PAGO CARDS ===== */
        .tipo-pago-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .tipo-pago-card {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            background: white;
        }
        .tipo-pago-card:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .tipo-pago-card.active {
            border-color: #667eea;
            background: #f0f3ff;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.15);
        }
        .tipo-pago-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
        }
        .tipo-pago-icon {
            font-size: 1.3em;
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tipo-pago-title {
            font-weight: 600;
            font-size: 0.9em;
            color: #333;
        }

        /* ===== PRICE SUMMARY ===== */
        .price-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-top: 15px;
            border-left: 3px solid #667eea;
        }
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95em;
            color: #555;
        }
        .price-item.total {
            border-top: 1px solid #ddd;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 600;
            font-size: 1.05em;
            color: #28a745;
        }

        /* ===== FORM STYLING - LIMPIO ===== */
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 6px;
            font-size: 0.95em;
        }
        
        .form-control {
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            padding: 9px 12px;
            transition: all 0.2s ease;
            font-size: 0.95em;
            height: auto;
            min-height: 40px;
            background-color: #fff;
            color: #333;
            font-weight: 400;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .form-control:hover:not(:disabled) {
            border-color: #999;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.08);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #bbb;
            font-weight: 400;
        }
        
        .form-control:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            border-color: #e0e0e0;
        }
        
        .form-control[readonly] {
            background-color: #f5f5f5;
            color: #999;
            cursor: default;
        }

        /* ===== SELECT2 UNIFIED ===== */
        .select2-container-custom {
            width: 100% !important;
        }
        
        .select2-container-custom .select2-selection--single {
            border: 1px solid #d0d0d0 !important;
            border-radius: 6px !important;
            padding: 0 !important;
            height: 40px !important;
            min-height: 40px !important;
            display: flex !important;
            align-items: center !important;
            background: #fff !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        
        .select2-container-custom .select2-selection--single:hover {
            border-color: #999 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        
        .select2-container-custom.select2-container--open .select2-selection--single {
            border-color: #667eea !important;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.08) !important;
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
        
        .select2-container-custom .select2-selection__rendered {
            padding: 0 12px !important;
            line-height: 38px !important;
            font-size: 0.95em !important;
            color: #333 !important;
            font-weight: 400 !important;
        }
        
        .select2-container-custom .select2-selection__placeholder {
            color: #bbb !important;
        }
        
        .select2-container-custom .select2-selection__arrow {
            height: 100% !important;
            right: 0 !important;
            width: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .select2-container-custom .select2-selection__arrow b {
            border-color: #667eea transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        
        .select2-dropdown-custom {
            border: 1px solid #d0d0d0 !important;
            border-top: none !important;
            border-radius: 0 0 6px 6px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            z-index: 9999 !important;
            background: #fff !important;
            margin-top: -1px !important;
        }
        
        .select2-dropdown-custom .select2-results__options {
            padding: 4px 0 !important;
            max-height: 300px !important;
        }
        
        .select2-dropdown-custom .select2-results__option {
            padding: 8px 12px !important;
            font-size: 0.95em !important;
            line-height: 1.5 !important;
            color: #333 !important;
            transition: all 0.15s ease !important;
            font-weight: 400 !important;
        }
        
        .select2-dropdown-custom .select2-results__option:hover {
            background-color: #f0f0f0 !important;
            color: #333 !important;
        }
        
        .select2-dropdown-custom .select2-results__option--highlighted[aria-selected] {
            background-color: #667eea !important;
            color: white !important;
        }
        
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d0d0d0 !important;
            border-radius: 4px !important;
            padding: 6px 10px !important;
            font-size: 0.95em !important;
            margin: 4px !important;
            background-color: #f8f9fa !important;
        }
        
        .select2-search--dropdown .select2-search__field:focus {
            outline: none !important;
            border-color: #667eea !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 2px rgba(102,126,234,0.08) !important;
        }

        /* ===== BUTTONS ===== */
        .btn-registrar {
            background: #28a745;
            border: none;
            color: white;
            padding: 10px 32px;
            font-size: 1em;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
        }
        .btn-registrar:hover {
            background: #218838;
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.95em;
            transition: all 0.2s ease;
        }
        
        .btn-light {
            border: 1px solid #d0d0d0;
            color: #555;
        }
        
        .btn-light:hover {
            background: #f5f5f5;
            color: #333;
            border-color: #999;
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
    const priceSummary = document.getElementById('priceSummary');
    const idConvenio = document.getElementById('id_convenio');
    const idMotivoDescuento = document.getElementById('id_motivo_descuento');
    
    // Elementos para mostrar precios
    const precioBaseEl = document.getElementById('precioBase');
    const precioDescuentoEl = document.getElementById('precioDescuento');
    const precioTotalEl = document.getElementById('precioTotal');
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
            font-size: 1.15rem !important;
            color: #333 !important;
            font-weight: 600 !important;
        }
        
        .select2-membresia .select2-selection--single .select2-selection__rendered {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
        }
        
        .select2-container-custom .select2-selection--single {
            min-height: 44px !important;
            height: 44px !important;
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

    // ===== ACTUALIZAR MONTOS DE CUOTAS ===== (ELIMINADO - YA NO HAY CUOTAS)
    // Los abonos se acumulan directamente sin necesidad de cuotas
    
    // ===== VALIDAR PAGO COMPLETO =====
    function validarPagoCompleto() {
        const montoAbonado = parseFloat(document.getElementById('monto_abonado')?.value) || 0;
        // Ya no controlamos cuotas - los abonos simplemente se acumulan
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
