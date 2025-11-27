@extends('adminlte::page')

@section('title', 'Registrar Pago - EstóicosGym')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { background: #f5f5f5; }
        .page-container { background: white; }
        
        /* Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            margin: -20px -15px 30px -15px;
            border-radius: 0 0 12px 12px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.2em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        /* Secciones */
        .form-section {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.1em;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #667eea;
            font-size: 1.2em;
        }
        
        /* Cliente Info Card */
        .cliente-info-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .cliente-info-card.hidden { display: none; }
        
        .cliente-info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .cliente-info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .cliente-info-label {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cliente-info-value {
            font-size: 1.3em;
            font-weight: 700;
            color: #333;
        }
        
        .cliente-info-value.danger { color: #dc3545; }
        .cliente-info-value.success { color: #28a745; }
        
        /* Payment Type Cards */
        .tipo-pago-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .tipo-pago-label {
            cursor: pointer;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }
        
        .tipo-pago-label:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        
        .tipo-pago-label input[type="radio"] {
            display: none;
        }
        
        .tipo-pago-label input[type="radio"]:checked + .tipo-pago-content {
            color: white;
        }
        
        .tipo-pago-label input[type="radio"]:checked ~ .tipo-pago-card {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .tipo-pago-card {
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tipo-pago-card.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .tipo-pago-card i {
            display: block;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .tipo-pago-card strong {
            font-size: 0.95em;
            display: block;
        }
        
        /* Payment Methods */
        .metodo-pago-select {
            margin: 15px 0;
        }
        
        /* Amount Input */
        .monto-input-group {
            margin: 20px 0;
        }
        
        .monto-input-group label {
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        
        .monto-input-group input {
            font-size: 1.1em;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .monto-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }
        
        .monto-input-group input.is-valid {
            border-color: #28a745;
            background-color: #f0f9ff;
        }
        
        .monto-input-group input.is-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }
        
        /* Resumen */
        .resumen-pago {
            background: #f0f9ff;
            border: 1px solid #667eea;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 0.95em;
        }
        
        .resumen-pago.success {
            background: #f0fdf4;
            border-color: #28a745;
        }
        
        /* Historial */
        .historial-section {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #eee;
        }
        
        .historial-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .historial-table table {
            margin: 0;
        }
        
        .historial-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #333;
        }
        
        .historial-table td {
            padding: 12px;
            vertical-align: middle;
        }
        
        /* Botones */
        .btn-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-primary-custom {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom.btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary-custom.btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-custom.btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-primary-custom.btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-primary-custom.btn-cancel:hover {
            background: #5a6268;
        }
        
        /* Hidden Payment Sections */
        .pago-section { display: none; }
        .pago-section.active { display: block; }
        
        /* Helpers */
        .text-muted { color: #999; }
        .mt-3 { margin-top: 20px; }
        .d-none { display: none; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .gap-10 { gap: 10px; }
        
        @media (max-width: 768px) {
            .page-header { padding: 20px; }
            .page-header h1 { font-size: 1.5em; }
            .tipo-pago-options { grid-template-columns: 1fr; }
            .cliente-info-row { grid-template-columns: 1fr; }
            .btn-actions { flex-direction: column; }
        }
    </style>
@endsection

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-money-bill-wave"></i> Registrar Pago de Inscripción</h1>
        <p class="text-white mt-2">Complete el formulario para registrar un nuevo pago</p>
    </div>

    <div class="container-fluid">
        <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
            @csrf

            <!-- SECCIÓN 1: SELECCIONAR CLIENTE -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-search"></i> Paso 1: Seleccionar Cliente
                </div>
                
                <div class="form-group">
                    <label for="id_inscripcion" class="font-weight-bold">
                        Buscar Cliente por Nombre, RUT o Email <span class="text-danger">*</span>
                    </label>
                    <select class="form-control form-control-lg select2 @error('id_inscripcion') is-invalid @enderror" 
                            id="id_inscripcion" name="id_inscripcion" required>
                        <option value="">-- Seleccionar cliente --</option>
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
                                    data-vencimiento="{{ $insc->fecha_vencimiento->format('d/m/Y') }}"
                                    data-dias="{{ $diasRestantes }}">
                                {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inscripcion')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- SECCIÓN 2: INFORMACIÓN DEL CLIENTE (Oculta inicialmente) -->
            <div class="form-section cliente-info-card hidden" id="clienteInfoSection">
                <div class="section-title">
                    <i class="fas fa-user"></i> Información del Cliente
                </div>
                
                <div class="cliente-info-row">
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Cliente</div>
                        <div class="cliente-info-value" id="clienteNombre">-</div>
                    </div>
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Membresía</div>
                        <div class="cliente-info-value" id="membresiaNombre">-</div>
                    </div>
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Total a Pagar</div>
                        <div class="cliente-info-value" id="montoTotal">$0</div>
                    </div>
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Ya Pagado</div>
                        <div class="cliente-info-value" id="montoAbonado">$0</div>
                    </div>
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Pendiente</div>
                        <div class="cliente-info-value success" id="montoPendiente">$0</div>
                    </div>
                    <div class="cliente-info-item">
                        <div class="cliente-info-label">Vencimiento</div>
                        <div class="cliente-info-value" id="fechaVencimiento">-</div>
                    </div>
                </div>
                
                <!-- Historial -->
                <div class="historial-section" id="historialSection">
                    <h5><i class="fas fa-history"></i> Últimos Pagos</h5>
                    <div class="historial-table" id="historialPagos">
                        <p class="text-muted p-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: TIPO DE PAGO -->
            <div class="form-section" id="tipoPagoSection">
                <div class="section-title">
                    <i class="fas fa-credit-card"></i> Paso 2: Tipo de Pago
                </div>
                
                <div class="tipo-pago-options">
                    <!-- Abono -->
                    <label class="tipo-pago-label">
                        <input type="radio" name="tipo_pago" value="abono" checked>
                        <div class="tipo-pago-card active">
                            <i class="fas fa-coins"></i>
                            <strong>Abono</strong>
                            <small class="d-block text-muted mt-1">Pago parcial</small>
                        </div>
                    </label>
                    
                    <!-- Completo -->
                    <label class="tipo-pago-label">
                        <input type="radio" name="tipo_pago" value="completo">
                        <div class="tipo-pago-card">
                            <i class="fas fa-check-circle"></i>
                            <strong>Completo</strong>
                            <small class="d-block text-muted mt-1">Pago total</small>
                        </div>
                    </label>
                    
                    <!-- Mixto -->
                    <label class="tipo-pago-label">
                        <input type="radio" name="tipo_pago" value="mixto">
                        <div class="tipo-pago-card">
                            <i class="fas fa-random"></i>
                            <strong>Mixto</strong>
                            <small class="d-block text-muted mt-1">Múltiples métodos</small>
                        </div>
                    </label>
                </div>
            </div>

            <!-- SECCIÓN 4: DETALLES DEL PAGO -->
            <div class="form-section" id="datosPagoSection">
                <div class="section-title">
                    <i class="fas fa-file-alt"></i> Paso 3: Detalles del Pago
                </div>
                
                <!-- ABONO -->
                <div class="pago-section active" id="seccion-abono">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="monto_abonado_abono" class="font-weight-bold">
                                Monto a Abonar ($) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg" 
                                   id="monto_abonado_abono" name="monto_abonado_abono_tmp"
                                   step="1000" min="1000" placeholder="Ej: 50000" required>
                            <small class="text-muted d-block mt-2">Mínimo: $1.000</small>
                        </div>
                        <div class="form-group col-md-6">
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
                    <div class="alert alert-info mt-3">
                        <strong>Resumen:</strong> Se abonarán $<span id="abono-monto">0</span> de los $<span id="abono-total">0</span> pendientes
                    </div>
                </div>

                <!-- COMPLETO -->
                <div class="pago-section" id="seccion-completo">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="monto_completo" class="font-weight-bold">Monto Total</label>
                            <input type="text" class="form-control form-control-lg bg-light" 
                                   id="monto_completo" disabled>
                            <input type="hidden" id="monto_abonado_completo" name="monto_abonado_completo_tmp">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="id_metodo_pago_completo" class="font-weight-bold">
                                Método de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg" id="id_metodo_pago_completo" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-success mt-3">
                        <strong>✓ El cliente quedará pagado completamente</strong>
                    </div>
                </div>

                <!-- MIXTO -->
                <div class="pago-section" id="seccion-mixto">
                    <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> Divide el pago entre 2 métodos. La suma debe ser exacta.</p>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="monto_metodo1" class="font-weight-bold">Método 1 - Monto ($)</label>
                            <input type="number" class="form-control form-control-lg monto-mixto" 
                                   id="monto_metodo1" step="1000" min="0" placeholder="0">
                            <select class="form-control mt-2" id="metodo_pago_1">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="monto_metodo2" class="font-weight-bold">Método 2 (Efectivo) - Monto ($)</label>
                            <input type="number" class="form-control form-control-lg monto-mixto" 
                                   id="monto_metodo2" step="1000" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        Total: $<span id="total-mixto">0</span> / $<span id="target-mixto">0</span> - 
                        <span id="estado-mixto">❌ Monto incompleto</span>
                    </div>
                </div>

                <!-- CAMPOS COMUNES -->
                <hr class="my-4">
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecha_pago" class="font-weight-bold">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg" 
                               id="fecha_pago" name="fecha_pago" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="referencia_pago" class="font-weight-bold">Referencia (Comprobante, etc)</label>
                        <input type="text" class="form-control form-control-lg" 
                               id="referencia_pago" name="referencia_pago" placeholder="Ej: Transferencia #123456">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cantidad_cuotas" class="font-weight-bold">Cantidad de Cuotas</label>
                        <select class="form-control form-control-lg" id="cantidad_cuotas" name="cantidad_cuotas">
                            <option value="1">1 cuota</option>
                            <option value="2">2 cuotas</option>
                            <option value="3">3 cuotas</option>
                            <option value="4">4 cuotas</option>
                            <option value="6">6 cuotas</option>
                            <option value="12">12 cuotas</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="observaciones" class="font-weight-bold">Observaciones</label>
                        <textarea class="form-control form-control-lg" id="observaciones" 
                                  name="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <!-- CAMPOS OCULTOS -->
            <input type="hidden" id="monto_abonado" name="monto_abonado" value="0">
            <input type="hidden" id="id_metodo_pago_principal" name="id_metodo_pago_principal" value="">
            <input type="hidden" id="tipo_pago_final" name="tipo_pago_final" value="abono">

            <!-- BOTONES -->
            <div class="btn-actions">
                <a href="{{ route('admin.pagos.index') }}" class="btn-primary-custom btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-primary-custom btn-submit" id="btnSubmit" disabled>
                    <i class="fas fa-check"></i> Registrar Pago
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando formulario de pagos v2...');
    
    // DOM Elements
    const selectInscripcion = document.getElementById('id_inscripcion');
    const clienteInfoSection = document.getElementById('clienteInfoSection');
    const tipoPagoSection = document.getElementById('tipoPagoSection');
    const datosPagoSection = document.getElementById('datosPagoSection');
    const formPago = document.getElementById('formPago');
    const btnSubmit = document.getElementById('btnSubmit');

    // Initialize Select2
    console.log('Inicializando Select2...');
    $('#id_inscripcion').select2({
        width: '100%',
        language: 'es',
        placeholder: '🔍 Buscar cliente...',
        allowClear: true,
        minimumInputLength: 0
    });

    // Select2 Event
    $('#id_inscripcion').on('select2:select', cargarCliente);
    $('#id_inscripcion').on('select2:clear', function() {
        ocultarFormulario();
    });

    // Radio button changes
    document.querySelectorAll('input[name="tipo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log(`Tipo de pago: ${this.value}`);
            // Hide all sections
            document.querySelectorAll('.pago-section').forEach(s => s.classList.remove('active'));
            // Show selected
            document.getElementById(`seccion-${this.value}`).classList.add('active');
            // Update hidden field
            document.getElementById('tipo_pago_final').value = this.value;
            validarFormulario();
        });
    });

    // Listeners for amount changes
    document.getElementById('monto_abonado_abono').addEventListener('input', validarFormulario);
    document.getElementById('id_metodo_pago_abono').addEventListener('change', validarFormulario);
    document.getElementById('id_metodo_pago_completo').addEventListener('change', validarFormulario);
    document.getElementById('monto_metodo1').addEventListener('input', validarFormulario);
    document.getElementById('monto_metodo2').addEventListener('input', validarFormulario);
    document.getElementById('metodo_pago_1').addEventListener('change', validarFormulario);

    // Set today's date
    document.getElementById('fecha_pago').valueAsDate = new Date();

    // Load client
    function cargarCliente() {
        const value = selectInscripcion.value;
        if (!value) {
            ocultarFormulario();
            return;
        }

        try {
            const option = document.querySelector(`#id_inscripcion option[value="${value}"]`);
            const precio = parseFloat(option.getAttribute('data-precio')) || 0;
            const pagos = parseFloat(option.getAttribute('data-pagos')) || 0;
            const cliente = option.getAttribute('data-cliente');
            const membresia = option.getAttribute('data-membresia');
            const vencimiento = option.getAttribute('data-vencimiento');
            const pendiente = precio - pagos;

            // Update info
            document.getElementById('clienteNombre').textContent = cliente;
            document.getElementById('membresiaNombre').textContent = membresia;
            document.getElementById('montoTotal').textContent = '$' + precio.toLocaleString('es-CO');
            document.getElementById('montoAbonado').textContent = '$' + pagos.toLocaleString('es-CO');
            document.getElementById('montoPendiente').textContent = '$' + pendiente.toLocaleString('es-CO');
            document.getElementById('fechaVencimiento').textContent = vencimiento;

            // Show sections
            clienteInfoSection.classList.remove('hidden');
            tipoPagoSection.style.display = 'block';
            datosPagoSection.style.display = 'block';

            // Load history
            cargarHistorial(value);

            // Update amounts
            document.getElementById('monto_completo').value = '$' + pendiente.toLocaleString('es-CO');
            document.getElementById('monto_abonado_completo').value = pendiente;
            document.getElementById('target-mixto').textContent = pendiente.toLocaleString('es-CO');

            // Reset payment inputs
            resetearCamposPago();
            
            validarFormulario();
        } catch(e) {
            console.error('Error cargando cliente:', e);
        }
    }

    function ocultarFormulario() {
        clienteInfoSection.classList.add('hidden');
        tipoPagoSection.style.display = 'none';
        datosPagoSection.style.display = 'none';
        btnSubmit.disabled = true;
    }

    function cargarHistorial(inscripcionId) {
        const url = `/admin/pagos/historial/${inscripcionId}`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                const div = document.getElementById('historialPagos');
                if (!data.pagos || data.pagos.length === 0) {
                    div.innerHTML = '<p class="text-muted p-3">Sin pagos anteriores</p>';
                    return;
                }
                let html = '<table class="table table-sm mb-0"><thead><tr><th>Fecha</th><th>Monto</th><th>Método</th></tr></thead><tbody>';
                data.pagos.forEach(p => {
                    const fecha = new Date(p.fecha_pago).toLocaleDateString('es-CO');
                    const metodo = p.metodoPagoPrincipal?.nombre || 'N/A';
                    html += `<tr><td>${fecha}</td><td>$${p.monto_abonado.toLocaleString('es-CO')}</td><td>${metodo}</td></tr>`;
                });
                html += '</tbody></table>';
                div.innerHTML = html;
            })
            .catch(e => console.error('Error historial:', e));
    }

    function resetearCamposPago() {
        document.getElementById('monto_abonado_abono').value = '';
        document.getElementById('id_metodo_pago_abono').value = '';
        document.getElementById('id_metodo_pago_completo').value = '';
        document.getElementById('monto_metodo1').value = '';
        document.getElementById('monto_metodo2').value = '';
        document.getElementById('metodo_pago_1').value = '';
        document.getElementById('referencia_pago').value = '';
        document.getElementById('observaciones').value = '';
        document.getElementById('cantidad_cuotas').value = '1';
        
        // Reset display
        document.getElementById('abono-monto').textContent = '0';
        document.getElementById('total-mixto').textContent = '0';
        document.getElementById('estado-mixto').innerHTML = '❌ Incompleto';
    }

    function validarFormulario() {
        const tipoPago = document.querySelector('input[name="tipo_pago"]:checked').value;
        const pendiente = parseFloat(document.getElementById('montoPendiente').textContent.replace(/[$,.]/g, '')) || 0;

        let valido = false;

        if (tipoPago === 'abono') {
            const monto = parseFloat(document.getElementById('monto_abonado_abono').value) || 0;
            const metodo = document.getElementById('id_metodo_pago_abono').value;
            valido = monto > 0 && monto <= pendiente && metodo;
            
            // Update display
            if (monto > 0) {
                document.getElementById('abono-monto').textContent = monto.toLocaleString('es-CO');
                document.getElementById('abono-total').textContent = pendiente.toLocaleString('es-CO');
            }
        } else if (tipoPago === 'completo') {
            const metodo = document.getElementById('id_metodo_pago_completo').value;
            valido = !!metodo;
        } else if (tipoPago === 'mixto') {
            const m1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const m2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            const metodo = document.getElementById('metodo_pago_1').value;
            valido = (m1 + m2) === pendiente && metodo;
            
            const total = m1 + m2;
            document.getElementById('total-mixto').textContent = total.toLocaleString('es-CO');
            if (total === pendiente) {
                document.getElementById('estado-mixto').innerHTML = '✓ Correcto';
                document.getElementById('estado-mixto').style.color = '#28a745';
            } else if (total > pendiente) {
                document.getElementById('estado-mixto').innerHTML = '❌ Excede';
                document.getElementById('estado-mixto').style.color = '#dc3545';
            } else {
                document.getElementById('estado-mixto').innerHTML = '❌ Incompleto';
                document.getElementById('estado-mixto').style.color = '#fbbf24';
            }
        }

        btnSubmit.disabled = !valido;
    }

    // Form submit
    formPago.addEventListener('submit', function(e) {
        const tipoPago = document.querySelector('input[name="tipo_pago"]:checked').value;
        
        if (tipoPago === 'abono') {
            document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_abono').value;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_abono').value;
        } else if (tipoPago === 'completo') {
            document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_completo').value;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_completo').value;
        } else if (tipoPago === 'mixto') {
            const m1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const m2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            document.getElementById('monto_abonado').value = m1 + m2;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('metodo_pago_1').value;
        }
    });

    console.log('✓ Formulario inicializado correctamente');
});
</script>
@endsection
