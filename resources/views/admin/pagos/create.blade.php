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
                                $diasRestantes = max(0, now()->diffInDays($insc->fecha_vencimiento, false));
                            @endphp
                            <option value="{{ $insc->id }}" 
                                    data-precio="{{ $total }}"
                                    data-pagos="{{ $pagos }}"
                                    data-cliente="{{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }}"
                                    data-rut="{{ $insc->cliente->rut }}"
                                    data-email="{{ $insc->cliente->email }}"
                                    data-membresia="{{ $insc->membresia->nombre }}"
                                    data-inicio="{{ $insc->fecha_inicio->format('d/m/Y') }}"
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}"
                                    data-dias="{{ $diasRestantes }}">
                                👤 {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} ({{ $insc->membresia->nombre }})
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
                                <select class="form-control mt-2" id="metodo_pago_1">
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
        <input type="hidden" id="id_metodo_pago_principal" name="id_metodo_pago_principal" value="">

        <!-- Botones -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-lg ml-2" id="btnSubmit" disabled>
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
    console.log('🚀 Script iniciado - DOMContentLoaded');
    
    // Obtener elementos del DOM
    const selectInscripcion = document.getElementById('id_inscripcion');
    const clienteInfoSection = document.getElementById('clienteInfoSection');
    const tipoPagoSection = document.getElementById('tipoPagoSection');
    const datosPagoSection = document.getElementById('datosPagoSection');
    const formPago = document.getElementById('formPago');
    const btnSubmit = document.getElementById('btnSubmit');

    // Validación inicial
    console.log('✓ selectInscripcion:', selectInscripcion?.id);
    console.log('✓ clienteInfoSection:', clienteInfoSection?.id);
    console.log('✓ tipoPagoSection:', tipoPagoSection?.id);
    console.log('✓ datosPagoSection:', datosPagoSection?.id);
    console.log('✓ formPago:', formPago?.id);
    console.log('✓ btnSubmit:', btnSubmit?.id);

    // Función para RESETEAR todos los campos
    function resetearFormulario() {
        console.log('🔄 Reseteando formulario...');
        try {
            const elementos = [
                'monto_abonado_abono', 'monto_metodo1', 'monto_metodo2',
                'id_metodo_pago_abono', 'id_metodo_pago_completo', 'metodo_pago_1',
                'referencia_pago', 'observaciones', 'cantidad_cuotas', 'fecha_pago'
            ];
            
            elementos.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    if (el.type === 'hidden' || el.type === 'text' || el.type === 'date') {
                        el.value = '';
                    } else if (el.tagName === 'SELECT') {
                        el.value = id === 'cantidad_cuotas' ? '1' : '';
                    }
                    console.log(`  ✓ Limpiado: ${id}`);
                } else {
                    console.warn(`  ⚠️ Elemento no encontrado: ${id}`);
                }
            });
            
            // Fecha actual
            const fechaHoy = new Date().toISOString().split('T')[0];
            const fechaPagoEl = document.getElementById('fecha_pago');
            if (fechaPagoEl) {
                fechaPagoEl.value = fechaHoy;
                console.log(`  ✓ Fecha establecida: ${fechaHoy}`);
            }
            
            // Resetear tipo de pago a abono
            const radioAbono = document.querySelector('input[name="tipo_pago"][value="abono"]');
            if (radioAbono) {
                radioAbono.checked = true;
                console.log('  ✓ Tipo pago: abono');
            }
            
            // Resetear vistas
            document.querySelectorAll('.pago-section').forEach(s => s.classList.add('d-none'));
            document.querySelectorAll('.tipo-pago-card').forEach(c => c.classList.remove('active'));
            
            const seccionAbono = document.getElementById('seccion-abono');
            const cardAbono = document.getElementById('card-abono');
            if (seccionAbono) seccionAbono.classList.remove('d-none');
            if (cardAbono) cardAbono.classList.add('active');
            console.log('  ✓ Secciones reseteadas');
            
            // Limpiar resumen
            const elementos_resumen = ['nuevo-abonado', 'nuevo-pendiente', 'total-mixto', 'estado-mixto'];
            elementos_resumen.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    if (id === 'estado-mixto') {
                        el.innerHTML = '❌ Monto incompleto';
                    } else {
                        el.textContent = '0';
                    }
                }
            });
            
            console.log('✓ Formulario reseteado exitosamente - updateSubmitButton será llamado');
            // NO deshabilitamos el botón aquí - será actualizado después por mostrarCliente
        } catch(error) {
            console.error('❌ Error al resetear formulario:', error);
            btnSubmit.disabled = true;
        }
    }

    // Función para cargar historial de pagos
    function cargarHistorial(inscripcionId) {
        console.log(`📋 Cargando historial para inscripción: ${inscripcionId}`);
        const url = `/admin/pagos/historial/${inscripcionId}`;
        console.log(`  URL: ${url}`);
        
        fetch(url)
            .then(response => {
                console.log(`  Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('✓ Historial cargado:', data);
                const historialDiv = document.getElementById('historialPagos');
                
                if (!data.pagos || data.pagos.length === 0) {
                    historialDiv.innerHTML = '<p class="text-muted p-3 mb-0"><i class="fas fa-info-circle"></i> Sin pagos registrados</p>';
                    console.log('  Info: No hay pagos registrados');
                    return;
                }
                
                let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                html += '<thead class="bg-light"><tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Estado</th></tr></thead><tbody>';
                
                data.pagos.forEach(pago => {
                    const fecha = new Date(pago.fecha_pago).toLocaleDateString('es-CO');
                    const estado = pago.id_estado === 102 ? '<span class="badge badge-success">Pagado</span>' : '<span class="badge badge-warning">Parcial</span>';
                    html += `<tr>
                        <td>${fecha}</td>
                        <td>$${pago.monto_abonado.toLocaleString('es-CO')}</td>
                        <td>${pago.metodoPagoPrincipal?.nombre || 'N/A'}</td>
                        <td>${estado}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                historialDiv.innerHTML = html;
                console.log(`✓ Historial renderizado: ${data.pagos.length} pagos`);
            })
            .catch(error => {
                console.error('❌ Error cargando historial:', error);
                document.getElementById('historialPagos').innerHTML = '<p class="text-danger p-3 mb-0"><i class="fas fa-exclamation"></i> Error al cargar historial</p>';
            });
    }

    // Función para mostrar el cliente seleccionado
    function mostrarCliente() {
        console.log('👤 mostrarCliente() invocado');
        const value = selectInscripcion.value;
        console.log(`  Valor seleccionado: ${value}`);
        
        if (value) {
            const option = document.querySelector(`option[value="${value}"]`);
            console.log('  Option encontrado:', option);
            
            if (!option) {
                console.error('❌ No se encontró el option element');
                return;
            }
            
            try {
                const precio = parseFloat(option.getAttribute('data-precio')) || 0;
                const pagos = parseFloat(option.getAttribute('data-pagos')) || 0;
                const cliente = option.getAttribute('data-cliente') || '';
                const membresia = option.getAttribute('data-membresia') || '';
                const vencimiento = option.getAttribute('data-vencimiento') || '';
                const dias = parseInt(option.getAttribute('data-dias')) || 0;
                const pendiente = precio - pagos;

                console.log(`  Datos obtenidos:`, {precio, pagos, cliente, membresia, vencimiento, dias, pendiente});

                // Llenar información - con validación de elementos
                const elementos_cliente = {
                    'clienteNombre': cliente,
                    'membresiaNombre': membresia,
                    'montoTotal': precio.toLocaleString('es-CO'),
                    'montoAbonado': pagos.toLocaleString('es-CO'),
                    'montoPendiente': pendiente.toLocaleString('es-CO'),
                    'fechaVencimiento': vencimiento,
                    'diasRestantes': dias > 0 ? dias + ' días' : '🔴 Vencido'
                };

                for (const [id, value] of Object.entries(elementos_cliente)) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = value;
                        console.log(`  ✓ ${id}: ${value}`);
                    } else {
                        console.warn(`  ⚠️ Elemento no encontrado: ${id}`);
                    }
                }

                // Actualizar campos de pago
                const elMontoCompleto = document.getElementById('monto_completo');
                const elMontoAbonComplet = document.getElementById('monto_abonado_completo');
                const elTargetMixto = document.getElementById('target-mixto');

                if (elMontoCompleto) {
                    elMontoCompleto.value = '$' + pendiente.toLocaleString('es-CO');
                    console.log(`  ✓ monto_completo: $${pendiente.toLocaleString('es-CO')}`);
                } else {
                    console.warn('  ⚠️ monto_completo no encontrado');
                }

                if (elMontoAbonComplet) {
                    elMontoAbonComplet.value = pendiente;
                    console.log(`  ✓ monto_abonado_completo: ${pendiente}`);
                } else {
                    console.warn('  ⚠️ monto_abonado_completo no encontrado');
                }

                if (elTargetMixto) {
                    elTargetMixto.textContent = pendiente.toLocaleString('es-CO');
                    console.log(`  ✓ target-mixto: ${pendiente.toLocaleString('es-CO')}`);
                } else {
                    console.warn('  ⚠️ target-mixto no encontrado');
                }

                // Mostrar secciones
                console.log('  Mostrando secciones...');
                clienteInfoSection.classList.remove('d-none');
                tipoPagoSection.classList.remove('d-none');
                datosPagoSection.classList.remove('d-none');
                console.log('  ✓ Secciones visibles');

                // RESETEAR FORMULARIO
                resetearFormulario();

                // Cargar historial
                cargarHistorial(value);
                
                // ACTUALIZAR ESTADO DEL BOTÓN (importante después de resetear)
                updateSubmitButton();
                
                console.log('✓ mostrarCliente() completado exitosamente');
            } catch(error) {
                console.error('❌ Error en mostrarCliente():', error);
            }
        } else {
            console.log('  Sin cliente seleccionado - ocultando secciones');
            clienteInfoSection.classList.add('d-none');
            tipoPagoSection.classList.add('d-none');
            datosPagoSection.classList.add('d-none');
            btnSubmit.disabled = true;
        }
    }

    // Select2 Initialization
    console.log('⚙️ Inicializando Select2...');
    $('#id_inscripcion').select2({
        width: '100%',
        language: 'es',
        placeholder: '🔍 Buscar por nombre, RUT o email',
        allowClear: true,
        matcher: function(params, data) {
            if (!params.term) { return data; }
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const rut = $(data.element).attr('data-rut') ? $(data.element).attr('data-rut').toLowerCase() : '';
            const email = $(data.element).attr('data-email') ? $(data.element).attr('data-email').toLowerCase() : '';
            if (text.includes(term) || rut.includes(term) || email.includes(term)) {
                return data;
            }
            return null;
        }
    });
    console.log('✓ Select2 inicializado');

    // Eventos Select2
    console.log('📌 Agregando event listeners...');
    $('#id_inscripcion').on('select2:select', function(e) {
        console.log('📌 Evento select2:select disparado:', e.params.data);
        mostrarCliente();
    });
    
    $('#id_inscripcion').on('select2:clear', function() {
        console.log('📌 Evento select2:clear disparado');
        clienteInfoSection.classList.add('d-none');
        tipoPagoSection.classList.add('d-none');
        datosPagoSection.classList.add('d-none');
        btnSubmit.disabled = true;
    });
    console.log('✓ Event listeners agregados');

    // Cambio de tipo de pago
    document.querySelectorAll('input[name="tipo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log(`🔘 Tipo de pago cambiado a: ${this.value}`);
            document.querySelectorAll('.pago-section').forEach(s => s.classList.add('d-none'));
            document.querySelectorAll('.tipo-pago-card').forEach(c => c.classList.remove('active'));
            
            if (this.value === 'abono') {
                console.log('  → Mostrando sección ABONO');
                document.getElementById('seccion-abono').classList.remove('d-none');
                document.getElementById('card-abono').classList.add('active');
            } else if (this.value === 'completo') {
                console.log('  → Mostrando sección COMPLETO');
                document.getElementById('seccion-completo').classList.remove('d-none');
                document.getElementById('card-completo').classList.add('active');
                // Calcular monto completo (pendiente total)
                const elMontoPendiente = document.getElementById('montoPendiente');
                if (elMontoPendiente) {
                    const montoPendiente = parseFloat(elMontoPendiente.textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
                    document.getElementById('monto_abonado_completo').value = montoPendiente;
                    console.log(`  Monto completo establecido: ${montoPendiente}`);
                }
            } else if (this.value === 'mixto') {
                console.log('  → Mostrando sección MIXTO');
                document.getElementById('seccion-mixto').classList.remove('d-none');
                document.getElementById('card-mixto').classList.add('active');
            }
            updateSubmitButton();
        });
    });

    // Validación Abono
    document.getElementById('monto_abonado_abono').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('montoPendiente').textContent.replace(/\./g, '').replace(/,/g, ''));
        const monto = parseFloat(this.value) || 0;
        const nuevoAbonado = parseFloat(document.getElementById('montoAbonado').textContent.replace(/\./g, '').replace(/,/g, '')) + monto;
        const nuevoPendiente = total - monto;
        
        document.getElementById('nuevo-abonado').textContent = nuevoAbonado.toLocaleString('es-CO');
        document.getElementById('nuevo-pendiente').textContent = Math.max(0, nuevoPendiente).toLocaleString('es-CO');
        
        if (monto > 0 && monto <= total) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
        updateSubmitButton();
    });

    // Validación Pago Mixto
    const validarMixto = function() {
        const monto1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
        const monto2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
        const total = monto1 + monto2;
        const target = parseFloat(document.getElementById('target-mixto').textContent.replace(/\./g, '').replace(/,/g, ''));
        
        document.getElementById('total-mixto').textContent = total.toLocaleString('es-CO');
        const estado = document.getElementById('estado-mixto');
        
        if (total === target) {
            estado.innerHTML = '✓ Monto correcto';
            estado.style.color = '#10b981';
            document.getElementById('monto_metodo1').classList.add('is-valid');
            document.getElementById('monto_metodo2').classList.add('is-valid');
        } else if (total > target) {
            estado.innerHTML = '❌ Monto excede';
            estado.style.color = '#dc3545';
            document.getElementById('monto_metodo1').classList.remove('is-valid');
            document.getElementById('monto_metodo2').classList.remove('is-valid');
        } else {
            estado.innerHTML = '❌ Monto incompleto';
            estado.style.color = '#fbbf24';
            document.getElementById('monto_metodo1').classList.remove('is-valid');
            document.getElementById('monto_metodo2').classList.remove('is-valid');
        }
        updateSubmitButton();
    };

    document.querySelectorAll('.monto-mixto').forEach(input => {
        input.addEventListener('input', validarMixto);
    });

    // Eventos de cambio en métodos de pago
    document.getElementById('id_metodo_pago_abono').addEventListener('change', updateSubmitButton);
    document.getElementById('id_metodo_pago_completo').addEventListener('change', updateSubmitButton);
    document.getElementById('metodo_pago_1').addEventListener('change', updateSubmitButton);

    function updateSubmitButton() {
        try {
            const tipoPago = document.querySelector('input[name="tipo_pago"]:checked');
            if (!tipoPago) {
                console.log('⚠️ updateSubmitButton: Sin tipo de pago seleccionado');
                btnSubmit.disabled = true;
                return;
            }

            console.log(`🔘 updateSubmitButton: Tipo = ${tipoPago.value}`);

            if (tipoPago.value === 'abono') {
                const elMonto = document.getElementById('monto_abonado_abono');
                const elTotal = document.getElementById('montoPendiente');
                const elMetodo = document.getElementById('id_metodo_pago_abono');
                
                if (!elMonto || !elTotal || !elMetodo) {
                    console.warn('⚠️ Elementos faltantes en abono:', {elMonto, elTotal, elMetodo});
                    btnSubmit.disabled = true;
                    return;
                }
                
                const monto = parseFloat(elMonto.value) || 0;
                const total = parseFloat(elTotal.textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
                const metodo = elMetodo.value;
                
                const isValid = monto > 0 && monto <= total && metodo;
                console.log(`  Abono: monto=${monto}, total=${total}, metodo=${metodo}, valid=${isValid}`);
                btnSubmit.disabled = !isValid;
                
            } else if (tipoPago.value === 'completo') {
                const elMetodo = document.getElementById('id_metodo_pago_completo');
                if (!elMetodo) {
                    console.warn('⚠️ Elemento faltante: id_metodo_pago_completo');
                    btnSubmit.disabled = true;
                    return;
                }
                const metodo = elMetodo.value;
                console.log(`  Completo: metodo=${metodo}, valid=${!!metodo}`);
                btnSubmit.disabled = !metodo;
                
            } else if (tipoPago.value === 'mixto') {
                const el1 = document.getElementById('monto_metodo1');
                const el2 = document.getElementById('monto_metodo2');
                const elTarget = document.getElementById('target-mixto');
                const elMetodo = document.getElementById('metodo_pago_1');
                
                if (!el1 || !el2 || !elTarget || !elMetodo) {
                    console.warn('⚠️ Elementos faltantes en mixto:', {el1, el2, elTarget, elMetodo});
                    btnSubmit.disabled = true;
                    return;
                }
                
                const monto1 = parseFloat(el1.value) || 0;
                const monto2 = parseFloat(el2.value) || 0;
                const target = parseFloat(elTarget.textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
                const metodo = elMetodo.value;
                
                const isValid = (monto1 + monto2) === target && metodo;
                console.log(`  Mixto: monto1=${monto1}, monto2=${monto2}, target=${target}, metodo=${metodo}, valid=${isValid}`);
                btnSubmit.disabled = !isValid;
            }
            
            console.log(`  Resultado: botón ${btnSubmit.disabled ? 'DESHABILITADO' : 'HABILITADO'}`);
        } catch(error) {
            console.error('❌ Error en updateSubmitButton():', error);
            btnSubmit.disabled = true;
        }
    }

    // Validación al enviar
    formPago.addEventListener('submit', function(e) {
        console.log('📤 Formulario siendo enviado');
        try {
            const tipoPago = document.querySelector('input[name="tipo_pago"]:checked').value;
            console.log(`  Tipo de pago: ${tipoPago}`);
            
            if (tipoPago === 'abono') {
                document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_abono').value;
                document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_abono').value;
            } else if (tipoPago === 'completo') {
                document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_completo').value;
                document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_completo').value;
            } else if (tipoPago === 'mixto') {
                const monto1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
                const monto2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
                
                document.getElementById('monto_abonado').value = monto1 + monto2;
                document.getElementById('id_metodo_pago_principal').value = document.getElementById('metodo_pago_1').value;
            }
            
            console.log('✓ Valores de formulario populados');
        } catch(error) {
            console.error('❌ Error al enviar formulario:', error);
            e.preventDefault();
        }
    });
    
    console.log('🎉 Script completamente inicializado');
});
</script>
@endsection
