@extends('adminlte::page')

@section('title', 'Registrar Pago - EstóicosGym')

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
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-item {
            background: rgba(255,255,255,0.15);
            padding: 15px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .stat-label {
            font-size: 0.75em;
            opacity: 0.85;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 1.3em;
            font-weight: 700;
        }
        .progreso-circle {
            position: relative;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: conic-gradient(rgba(255,255,255,0.4) 0deg, rgba(255,255,255,0.4) var(--progress), transparent var(--progress), transparent 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .progreso-circle::after {
            content: '';
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(102, 126, 234, 0.3);
        }
        .progreso-circle-text {
            position: relative;
            z-index: 1;
            font-weight: 700;
            font-size: 0.8em;
            color: white;
        }
        .section-header {
            font-size: 1.2em;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
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
        .tipo-pago-group {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .tipo-pago-card {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .tipo-pago-card:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }
        .tipo-pago-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
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
        .pago-mixto-section {
            display: none;
        }
        .pago-mixto-section.active {
            display: block;
        }
        .metodo-pago-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .metodo-box {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            background: #f8f9fa;
        }
        .metodo-box h5 {
            margin-bottom: 15px;
            font-weight: 700;
            color: #333;
        }
        .info-box-custom {
            background: linear-gradient(135deg, #e7f3ff 0%, #f0e7ff 100%);
            border-left: 5px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.9em;
            color: #333;
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
        .cuotas-section {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 20px;
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
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-credit-card"></i> Registrar Pago
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

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- 1. BÚSQUEDA DE CLIENTE -->
        <div class="form-card card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title m-0">
                    <i class="fas fa-search"></i> 1. Seleccionar Cliente
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="id_inscripcion" class="font-weight-bold">
                        Cliente y Membresía <span class="text-danger">*</span>
                    </label>
                    <select class="form-control form-control-lg select2 @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        <option value="">-- Buscar por nombre, RUT o email --</option>
                        @foreach($inscripciones as $insc)
                            @php
                                $total = $insc->precio_final ?? $insc->precio_base;
                                $pagos = $insc->pagos()->sum('monto_abonado');
                                $pendiente = max(0, $total - $pagos);
                                $diasRestantes = max(0, now()->diffInDays($insc->fecha_vencimiento, false));
                                $isSelected = !empty($inscripcion_id_preselect) && $insc->id == $inscripcion_id_preselect;
                            @endphp
                            <option value="{{ $insc->id }}" 
                                    data-precio="{{ $total }}"
                                    data-pagos="{{ $pagos }}"
                                    data-pendiente="{{ $pendiente }}"
                                    data-cliente="{{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }}"
                                    data-rut="{{ $insc->cliente->rut }}"
                                    data-email="{{ $insc->cliente->email }}"
                                    data-membresia="{{ $insc->membresia->nombre }}"
                                    data-inicio="{{ $insc->fecha_inicio->format('d/m/Y') }}"
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}"
                                    data-dias="{{ $diasRestantes }}"
                                    {{ $isSelected ? 'selected' : '' }}>
                                👤 {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }} - Pendiente: ${{ number_format($pendiente, 0, '.', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <small class="text-danger d-block mt-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- 2. INFORMACIÓN DEL CLIENTE E HISTORIAL -->
        <div id="clienteInfoSection" class="d-none mb-4">
            <!-- Cliente Header -->
            <div id="clienteHeader" class="cliente-header">
                <div class="content">
                    <div class="cliente-nombre">
                        <i class="fas fa-user-circle"></i>
                        <span id="clienteNombre"></span>
                    </div>

                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-label">Total Membresía</div>
                            <div class="stat-value">$<span id="montoTotal"></span></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Ya Pagado</div>
                            <div class="stat-value" style="color: #4ade80;">$<span id="montoAbonado">0</span></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Pendiente</div>
                            <div class="stat-value" style="color: #fbbf24;">$<span id="montoPendiente"></span></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Membresía</div>
                            <div class="stat-value"><span id="membresiaNombre"></span></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Vencimiento</div>
                            <div class="stat-value"><span id="fechaVencimiento"></span></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Días Restantes</div>
                            <div class="stat-value"><span id="diasRestantes"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Pagos -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title m-0">
                        <i class="fas fa-history"></i> Historial de Pagos Recientes
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div id="historialPagos">
                        <p class="text-muted p-3 mb-0"><i class="fas fa-info-circle"></i> Sin pagos registrados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TIPO DE PAGO -->
        <div class="form-card card d-none mb-4" id="tipoPagoSection">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title m-0">
                    <i class="fas fa-hand-holding-usd"></i> 2. Tipo de Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="tipo-pago-group">
                    <label class="tipo-pago-card active" id="card-abono">
                        <input type="radio" name="tipo_pago" value="abono" checked>
                        <div class="tipo-pago-label">
                            <i class="fas fa-plus-circle" style="font-size: 1.5em; color: #fbbf24;"></i>
                            <div>
                                <div style="font-weight: 700;">Abono Parcial</div>
                                <small>Pagar una cantidad menor</small>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-completo">
                        <input type="radio" name="tipo_pago" value="completo">
                        <div class="tipo-pago-label">
                            <i class="fas fa-check-circle" style="font-size: 1.5em; color: #10b981;"></i>
                            <div>
                                <div style="font-weight: 700;">Pago Completo</div>
                                <small>Pagar todo lo pendiente</small>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-mixto">
                        <input type="radio" name="tipo_pago" value="mixto">
                        <div class="tipo-pago-label">
                            <i class="fas fa-random" style="font-size: 1.5em; color: #8b5cf6;"></i>
                            <div>
                                <div style="font-weight: 700;">Pago Mixto</div>
                                <small>Múltiples métodos de pago</small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. DATOS DEL PAGO -->
        <div class="form-card card d-none mb-4" id="datosPagoSection">
            <div class="card-header bg-success text-white">
                <h3 class="card-title m-0">
                    <i class="fas fa-dollar-sign"></i> 3. Registrar Pago
                </h3>
            </div>
            <div class="card-body">
                <!-- SECCIÓN ABONO PARCIAL -->
                <div id="seccion-abono" class="pago-section">
                    <div class="section-header">
                        <i class="fas fa-plus-circle"></i> Abono Parcial
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_abonado_abono" class="font-weight-bold">
                                    Monto a Abonar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control monto-input" 
                                           id="monto_abonado_abono" name="monto_abonado" 
                                           step="1000" min="1000" placeholder="Ej: 50000" required>
                                </div>
                                <small id="max-abono" class="text-muted d-block mt-2"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_metodo_pago_abono" class="font-weight-bold">
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-control form-control-lg" id="id_metodo_pago_abono" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago alert alert-info" id="resumen-abono">
                        <strong>Resumen:</strong> Abonado: $<span id="nuevo-abonado">0</span> | Pendiente: $<span id="nuevo-pendiente">0</span>
                    </div>
                </div>

                <!-- SECCIÓN PAGO COMPLETO -->
                <div id="seccion-completo" class="pago-section d-none">
                    <div class="section-header">
                        <i class="fas fa-check-circle"></i> Pago Completo
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Monto a Pagar (Calculado Automáticamente)
                                </label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control bg-light" id="monto_completo" disabled>
                                </div>
                                <small class="text-muted d-block mt-2">✓ Se pagará automáticamente el saldo exacto pendiente</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_metodo_pago_completo" class="font-weight-bold">
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-control form-control-lg" id="id_metodo_pago_completo" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="monto_abonado_completo" id="monto_abonado_completo">
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago alert alert-success" id="resumen-completo">
                        <strong>✓ Resultado:</strong> El cliente quedará PAGADO COMPLETAMENTE
                    </div>
                </div>

                <!-- SECCIÓN PAGO MIXTO -->
                <div id="seccion-mixto" class="pago-section d-none">
                    <div class="section-header">
                        <i class="fas fa-random"></i> Pago Mixto - Múltiples Métodos
                    </div>
                    <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> Divide el pago entre diferentes métodos. La suma debe ser exacta.</p>
                    
                    <div class="metodo-pago-row">
                        <div class="metodo-box">
                            <h5><i class="fas fa-credit-card"></i> Método 1 (Transf/Débito/Crédito)</h5>
                            <div class="form-group">
                                <label class="font-weight-bold">Monto $</label>
                                <input type="number" class="form-control form-control-lg monto-mixto" id="monto_metodo1" 
                                       step="1000" min="0" placeholder="0">
                                <small class="text-muted d-block mt-2">Seleccionar método de pago:</small>
                                <select class="form-control form-control-lg mt-2" id="metodo_pago_1" style="width: 100%;">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        @if(strtolower($metodo->nombre) !== 'efectivo')
                                            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="metodo-box">
                            <h5><i class="fas fa-coins"></i> Efectivo</h5>
                            <div class="form-group">
                                <label class="font-weight-bold">Monto $</label>
                                <input type="number" class="form-control form-control-lg monto-mixto" id="monto_metodo2" 
                                       step="1000" min="0" placeholder="0">
                                <small class="text-muted d-block mt-2">✓ Método: Efectivo</small>
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago alert alert-warning" id="resumen-mixto">
                        <strong>Suma:</strong> $<span id="total-mixto">0</span> / $<span id="target-mixto">0</span>
                        <span id="estado-mixto" class="ml-3" style="color: #dc3545;">❌ Monto incompleto</span>
                    </div>
                </div>

                <!-- CAMPOS COMUNES A TODOS LOS PAGOS -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_pago" class="font-weight-bold">
                                Fecha del Pago <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="fecha_pago" name="fecha_pago" 
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="referencia_pago" class="font-weight-bold">
                                Referencia/Comprobante (Opcional)
                            </label>
                            <input type="text" class="form-control form-control-lg" id="referencia_pago" 
                                   name="referencia_pago" maxlength="100" placeholder="TRF-2025-001 o Boleta">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cantidad_cuotas" class="font-weight-bold">
                                Cuotas (Opcional)
                            </label>
                            <select class="form-control form-control-lg" id="cantidad_cuotas" name="cantidad_cuotas">
                                <option value="1" selected>Sin cuotas (1)</option>
                                <option value="2">2 cuotas</option>
                                <option value="3">3 cuotas</option>
                                <option value="4">4 cuotas</option>
                                <option value="6">6 cuotas</option>
                                <option value="12">12 cuotas</option>
                            </select>
                            <small class="text-muted d-block mt-2">* Se divide el monto abonado en cuotas</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="observaciones" class="font-weight-bold">
                        Observaciones (Opcional)
                    </label>
                    <textarea class="form-control" id="observaciones" name="observaciones" 
                              rows="3" placeholder="Ej: Cliente reconfirmó... Pago atrasado..."></textarea>
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">
        <input type="hidden" id="monto_abonado" name="monto_abonado" value="0">
        <input type="hidden" id="id_metodo_pago" name="id_metodo_pago" value="">

        <!-- Botones -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-lg ml-2" id="btnSubmit">
                    <i class="fas fa-check"></i> Registrar Pago
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/validacion-pagos.js') }}"></script>
<script src="{{ asset('js/debug-pagos.js') }}"></script>
@endsection
