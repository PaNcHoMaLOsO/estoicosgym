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
        <div class="form-card card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-search"></i> 1. Buscar Cliente
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="id_inscripcion">
                        <i class="fas fa-user-check"></i> Seleccionar Cliente y Membresía <span class="text-danger">*</span>
                    </label>
                    <select class="form-control select2 @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        <option value="">-- Buscar por nombre, RUT o email --</option>
                        @foreach($inscripciones as $insc)
                            @php
                                $total = $insc->precio_final ?? $insc->precio_base;
                                $diasRestantes = max(0, now()->diffInDays($insc->fecha_vencimiento, false));
                            @endphp
                            <option value="{{ $insc->id }}" 
                                    data-precio="{{ $total }}"
                                    data-abonado="0"
                                    data-cliente="{{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }}"
                                    data-rut="{{ $insc->cliente->rut }}"
                                    data-email="{{ $insc->cliente->email }}"
                                    data-membresia="{{ $insc->membresia->nombre }}"
                                    data-inicio="{{ $insc->fecha_inicio->format('d/m/Y') }}"
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}"
                                    data-dias="{{ $diasRestantes }}">
                                📋 {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- 2. INFORMACIÓN DEL CLIENTE (dinámica) -->
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
                        <div class="stat-value">$<span id="montoTotal"></span></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Abonado</div>
                        <div class="stat-value" style="color: #4ade80;">$<span id="montoAbonado">0</span></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Pendiente</div>
                        <div class="stat-value" style="color: #fbbf24;">$<span id="montoPendiente"></span></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Días Restantes</div>
                        <div class="stat-value"><span id="diasRestantes"></span></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Vencimiento</div>
                        <div class="stat-value"><span id="fechaVencimiento"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TIPO DE PAGO -->
        <div class="form-card card d-none" id="tipoPagoSection">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hand-holding-usd"></i> 2. Tipo de Pago
                </h3>
            </div>
            <div class="card-body">
                <div class="tipo-pago-group">
                    <label class="tipo-pago-card active" id="card-abono">
                        <input type="radio" name="tipo_pago" value="abono" checked>
                        <div class="tipo-pago-label">
                            <i class="fas fa-plus-circle" style="font-size: 1.5em;"></i>
                            <div>
                                <div style="font-weight: 700;">Abono Parcial</div>
                                <small>Suma al saldo anterior</small>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-completo">
                        <input type="radio" name="tipo_pago" value="completo">
                        <div class="tipo-pago-label">
                            <i class="fas fa-check-double" style="font-size: 1.5em;"></i>
                            <div>
                                <div style="font-weight: 700;">Pago Completo</div>
                                <small>Monto exacto restante</small>
                            </div>
                        </div>
                    </label>

                    <label class="tipo-pago-card" id="card-mixto">
                        <input type="radio" name="tipo_pago" value="mixto">
                        <div class="tipo-pago-label">
                            <i class="fas fa-random" style="font-size: 1.5em;"></i>
                            <div>
                                <div style="font-weight: 700;">Pago Mixto</div>
                                <small>Múltiples métodos</small>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 4. DATOS DEL PAGO -->
        <div class="form-card card d-none" id="datosPagoSection">
            <div class="card-header">
                <h3 class="card-title">
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
                                <label for="monto_abonado_abono">
                                    <i class="fas fa-money-bill-wave"></i> Monto a Abonar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control monto-input" 
                                           id="monto_abonado_abono" name="monto_abonado" 
                                           step="1000" min="1000" placeholder="Ej: 10000" required>
                                </div>
                                <small id="max-abono" class="text-muted"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_metodo_pago_abono">
                                    <i class="fas fa-credit-card"></i> Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="id_metodo_pago_abono" name="id_metodo_pago_principal" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago" id="resumen-abono">
                        Nuevo abonado: $<span id="nuevo-abonado">0</span> | Pendiente: $<span id="nuevo-pendiente">0</span>
                    </div>
                </div>

                <!-- SECCIÓN PAGO COMPLETO -->
                <div id="seccion-completo" class="pago-section d-none">
                    <div class="section-header">
                        <i class="fas fa-check-double"></i> Pago Completo
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-money-bill-wave"></i> Monto a Pagar (Automático)
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control" id="monto_completo" disabled>
                                </div>
                                <small class="text-muted">Este es el saldo pendiente exacto</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_metodo_pago_completo">
                                    <i class="fas fa-credit-card"></i> Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="id_metodo_pago_completo" required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="monto_abonado_completo" id="monto_abonado_completo">
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago">
                        ✓ Estado: <strong>PAGADO COMPLETAMENTE</strong>
                    </div>
                </div>

                <!-- SECCIÓN PAGO MIXTO -->
                <div id="seccion-mixto" class="pago-section d-none">
                    <div class="section-header">
                        <i class="fas fa-random"></i> Pago Mixto
                    </div>
                    <div class="metodo-pago-row">
                        <div class="metodo-box">
                            <h5><i class="fas fa-money-check-alt"></i> Transferencia / Débito / Crédito</h5>
                            <div class="form-group">
                                <label>Monto $</label>
                                <input type="number" class="form-control monto-mixto" id="monto_metodo1" 
                                       step="1000" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="metodo-box">
                            <h5><i class="fas fa-coins"></i> Efectivo</h5>
                            <div class="form-group">
                                <label>Monto $</label>
                                <input type="number" class="form-control monto-mixto" id="monto_metodo2" 
                                       step="1000" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="resumen-pago">
                        Total: $<span id="total-mixto">0</span> / $<span id="target-mixto">0</span>
                        <span id="estado-mixto" class="ml-3" style="color: #dc3545;">❌ Monto incompleto</span>
                    </div>
                </div>

                <!-- CAMPOS COMUNES -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referencia_pago">
                                <i class="fas fa-fingerprint"></i> Referencia/Comprobante (Opcional)
                            </label>
                            <input type="text" class="form-control" id="referencia_pago" 
                                   name="referencia_pago" maxlength="100" placeholder="TRF-2025-001">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">
                                <i class="fas fa-calendar-alt"></i> Fecha de Pago
                            </label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                   value="{{ now()->format('Y-m-d') }}" required>
                            <small class="text-muted">Se registra automáticamente hoy si no cambias</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">
                        <i class="fas fa-sticky-note"></i> Observaciones (Opcional)
                    </label>
                    <textarea class="form-control" id="observaciones" name="observaciones" 
                              rows="2" placeholder="Notas adicionales..."></textarea>
                </div>

                <!-- CUOTAS (CHECKBOX OPCIONAL) -->
                <div class="cuotas-section d-none" id="cuotasSection">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="mostrarCuotas" name="mostrar_cuotas">
                        <label class="custom-control-label" for="mostrarCuotas">
                            <i class="fas fa-calculator"></i> Dividir en cuotas
                        </label>
                    </div>
                    <div id="cuotasForm" class="d-none mt-3">
                        <div class="form-group">
                            <label for="cantidad_cuotas">
                                <i class="fas fa-list-ol"></i> Cantidad de Cuotas
                            </label>
                            <select class="form-control" id="cantidad_cuotas" name="cantidad_cuotas">
                                <option value="1" selected>1 cuota</option>
                                @for ($i = 2; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }} cuotas</option>
                                @endfor
                            </select>
                            <small id="monto-cuota" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="es_pago_simple" name="es_pago_simple" value="1">

        <!-- Botones -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-registrar ml-2" id="btnSubmit" disabled>
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
    const tipoPagoSection = document.getElementById('tipoPagoSection');
    const datosPagoSection = document.getElementById('datosPagoSection');
    const formPago = document.getElementById('formPago');
    const btnSubmit = document.getElementById('btnSubmit');

    // Select2
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

    // Cambio de cliente
    selectInscripcion.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const precio = parseFloat(option.getAttribute('data-precio'));
            const cliente = option.getAttribute('data-cliente');
            const membresia = option.getAttribute('data-membresia');
            const vencimiento = option.getAttribute('data-vencimiento');
            const dias = parseInt(option.getAttribute('data-dias'));

            document.getElementById('clienteNombre').textContent = cliente;
            document.getElementById('membresiaNombre').textContent = membresia;
            document.getElementById('montoTotal').textContent = precio.toLocaleString('es-CO');
            document.getElementById('montoPendiente').textContent = precio.toLocaleString('es-CO');
            document.getElementById('fechaVencimiento').textContent = vencimiento;
            document.getElementById('diasRestantes').textContent = dias > 0 ? dias + ' días' : 'Vencido';

            document.getElementById('monto_completo').value = '$' + precio.toLocaleString('es-CO');
            document.getElementById('monto_abonado_completo').value = precio;
            document.getElementById('target-mixto').textContent = precio.toLocaleString('es-CO');

            clienteHeader.classList.remove('d-none');
            tipoPagoSection.classList.remove('d-none');
            datosPagoSection.classList.remove('d-none');

            document.getElementById('cuotasSection').classList.remove('d-none');
        } else {
            clienteHeader.classList.add('d-none');
            tipoPagoSection.classList.add('d-none');
            datosPagoSection.classList.add('d-none');
            document.getElementById('cuotasSection').classList.add('d-none');
            btnSubmit.disabled = true;
        }
    });

    // Cambio de tipo de pago
    document.querySelectorAll('input[name="tipo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.pago-section').forEach(s => s.classList.add('d-none'));
            document.querySelectorAll('.tipo-pago-card').forEach(c => c.classList.remove('active'));
            
            if (this.value === 'abono') {
                document.getElementById('seccion-abono').classList.remove('d-none');
                document.getElementById('card-abono').classList.add('active');
                document.getElementById('id_metodo_pago_abono').name = 'id_metodo_pago_principal';
            } else if (this.value === 'completo') {
                document.getElementById('seccion-completo').classList.remove('d-none');
                document.getElementById('card-completo').classList.add('active');
                document.getElementById('id_metodo_pago_completo').name = 'id_metodo_pago_principal';
            } else if (this.value === 'mixto') {
                document.getElementById('seccion-mixto').classList.remove('d-none');
                document.getElementById('card-mixto').classList.add('active');
            }
        });
    });

    // Validación Abono
    document.getElementById('monto_abonado_abono').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('montoTotal').textContent.replace(/\./g, ''));
        const monto = parseFloat(this.value) || 0;
        const pendiente = total - monto;
        document.getElementById('nuevo-abonado').textContent = (0 + monto).toLocaleString('es-CO');
        document.getElementById('nuevo-pendiente').textContent = Math.max(0, pendiente).toLocaleString('es-CO');
        updateSubmitButton();
    });

    // Validación Pago Mixto
    document.querySelectorAll('.monto-mixto').forEach(input => {
        input.addEventListener('input', function() {
            const monto1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const monto2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            const total = monto1 + monto2;
            const target = parseFloat(document.getElementById('target-mixto').textContent.replace(/\./g, ''));
            
            document.getElementById('total-mixto').textContent = total.toLocaleString('es-CO');
            const estado = document.getElementById('estado-mixto');
            
            if (total === target) {
                estado.innerHTML = '✓ Monto correcto';
                estado.style.color = '#22c55e';
            } else if (total > target) {
                estado.innerHTML = '❌ Monto excede';
                estado.style.color = '#dc3545';
            } else {
                estado.innerHTML = '❌ Monto incompleto';
                estado.style.color = '#dc3545';
            }
            updateSubmitButton();
        });
    });

    // Cuotas
    document.getElementById('mostrarCuotas').addEventListener('change', function() {
        document.getElementById('cuotasForm').classList.toggle('d-none');
    });

    function updateSubmitButton() {
        const tipoPago = document.querySelector('input[name="tipo_pago"]:checked');
        if (!tipoPago) {
            btnSubmit.disabled = true;
            return;
        }

        if (tipoPago.value === 'abono') {
            const monto = parseFloat(document.getElementById('monto_abonado_abono').value) || 0;
            const metodo = document.getElementById('id_metodo_pago_abono').value;
            btnSubmit.disabled = !monto || !metodo;
        } else if (tipoPago.value === 'completo') {
            const metodo = document.getElementById('id_metodo_pago_completo').value;
            btnSubmit.disabled = !metodo;
        } else if (tipoPago.value === 'mixto') {
            const monto1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const monto2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            const target = parseFloat(document.getElementById('target-mixto').textContent.replace(/\./g, ''));
            btnSubmit.disabled = (monto1 + monto2) !== target;
        }
    }

    // Validación al enviar
    formPago.addEventListener('submit', function(e) {
        const tipoPago = document.querySelector('input[name="tipo_pago"]:checked');
        
        if (tipoPago.value === 'abono') {
            document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_abono').value;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_abono').value;
        } else if (tipoPago.value === 'completo') {
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_completo').value;
        }
    });
});
</script>
@endsection
