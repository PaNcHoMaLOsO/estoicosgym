@extends('adminlte::page')

@section('title', 'Registrar Pago - EstóicosGym')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    * {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .page-container {
        background: transparent;
    }

    /* Header Principal */
    .header-main {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px 30px;
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }

    .header-main::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .header-main::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .header-main h1 {
        margin: 0;
        font-size: 2.5em;
        font-weight: 700;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .header-main p {
        margin: 10px 0 0 0;
        font-size: 1.1em;
        opacity: 0.95;
        position: relative;
        z-index: 1;
    }

    /* Content Wrapper */
    .content-wrapper-custom {
        max-width: 1100px;
        margin: 0 auto;
        padding-bottom: 50px;
    }

    /* Form Sections */
    .form-section {
        background: white;
        border-radius: 15px;
        padding: 35px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .section-number {
        display: inline-block;
        background: #667eea;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        text-align: center;
        line-height: 40px;
        font-weight: 700;
        margin-right: 12px;
        font-size: 1.1em;
    }

    .section-title {
        font-size: 1.4em;
        font-weight: 700;
        color: #333;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #667eea;
        font-size: 1.3em;
    }

    /* Form Groups Mejorado */
    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        font-size: 0.95em;
        letter-spacing: 0.3px;
    }

    .form-group .form-control,
    .form-group select {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 0.95em;
        transition: all 0.3s ease;
        background: white;
    }

    .form-group .form-control:focus,
    .form-group select:focus {
        border-color: #667eea;
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.2);
        outline: none;
    }

    /* Select2 Custom */
    .select2-container--default .select2-selection--single {
        border: 2px solid #e0e0e0 !important;
        border-radius: 8px !important;
        height: 42px !important;
        padding: 5px 0 !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #667eea !important;
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.2) !important;
    }

    .select2-dropdown {
        border: 2px solid #667eea !important;
        border-radius: 8px !important;
        margin-top: 5px;
    }

    /* Cliente Info Card */
    .cliente-info-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        border: 2px solid #667eea;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        display: none;
    }

    .cliente-info-card.active {
        display: block;
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 1000px;
        }
    }

    .cliente-info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }

    .cliente-info-item {
        background: white;
        padding: 18px;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .cliente-info-label {
        font-size: 0.8em;
        color: #999;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .cliente-info-value {
        font-size: 1.25em;
        font-weight: 700;
        color: #333;
    }

    .cliente-info-value.success {
        color: #28a745;
    }

    /* Historial */
    .historial-section {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 2px solid #e0e0e0;
    }

    .historial-section h5 {
        color: #333;
        font-weight: 700;
        margin-bottom: 15px;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        padding: 15px;
    }

    .historial-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
    }

    .historial-table tr:last-child td {
        border-bottom: none;
    }

    /* Tipo Pago Cards */
    .tipo-pago-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .tipo-pago-label {
        position: relative;
        cursor: pointer;
    }

    .tipo-pago-label input[type="radio"] {
        display: none;
    }

    .tipo-pago-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 25px 15px;
        text-align: center;
        transition: all 0.3s ease;
        background: white;
        cursor: pointer;
    }

    .tipo-pago-card:hover {
        border-color: #667eea;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
    }

    .tipo-pago-label input[type="radio"]:checked + .tipo-pago-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        color: white;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        transform: scale(1.02);
    }

    .tipo-pago-card i {
        display: block;
        font-size: 2.2em;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .tipo-pago-card strong {
        display: block;
        font-size: 0.95em;
        margin-bottom: 5px;
    }

    .tipo-pago-card small {
        display: block;
        font-size: 0.8em;
        opacity: 0.8;
    }

    /* Payment Sections */
    .pago-section {
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .pago-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .pago-section .form-row {
        margin-bottom: 20px;
    }

    .alert-custom {
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border: none;
        font-size: 0.95em;
    }

    .alert-info-custom {
        background: #e3f2fd;
        color: #1565c0;
        border-left: 4px solid #667eea;
    }

    .alert-success-custom {
        background: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #28a745;
    }

    /* Campos Comunes */
    .common-fields-separator {
        border-top: 2px solid #e0e0e0;
        margin: 30px 0;
        padding-top: 30px;
    }

    .common-fields-separator h6 {
        color: #667eea;
        font-weight: 700;
        margin-bottom: 20px;
        text-transform: uppercase;
        font-size: 0.9em;
        letter-spacing: 1px;
    }

    /* Botones */
    .btn-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 35px;
    }

    .btn-submit,
    .btn-cancel {
        padding: 15px 30px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-submit:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(108, 117, 125, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header-main {
            padding: 30px 20px;
        }

        .header-main h1 {
            font-size: 1.8em;
        }

        .form-section {
            padding: 20px;
        }

        .section-title {
            font-size: 1.1em;
        }

        .cliente-info-row {
            grid-template-columns: 1fr;
        }

        .tipo-pago-options {
            grid-template-columns: 1fr;
        }

        .btn-actions {
            grid-template-columns: 1fr;
        }
    }

    .text-danger {
        color: #dc3545;
    }

    .mt-3 {
        margin-top: 20px;
    }

    .hidden {
        display: none !important;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endsection

@section('content')
<div class="header-main">
    <h1><i class="fas fa-money-bill-wave"></i> Registrar Pago</h1>
    <p>Complete el formulario para registrar un nuevo pago de inscripción</p>
</div>

<div class="content-wrapper-custom">
    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf

        <!-- PASO 1: SELECCIONAR CLIENTE -->
        <div class="form-section">
            <div class="section-title">
                <span class="section-number">1</span>
                <i class="fas fa-search"></i> Seleccionar Cliente
            </div>

            <div class="form-group">
                <label for="id_inscripcion">
                    Buscar Cliente <span class="text-danger">*</span>
                </label>
                <select class="form-control select2" id="id_inscripcion" name="id_inscripcion" required>
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
                            👤 {{ $insc->cliente->nombres }} {{ $insc->cliente->apellido_paterno }} - {{ $insc->membresia->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- PASO 2: INFORMACIÓN CLIENTE -->
        <div class="form-section cliente-info-card" id="clienteInfoSection">
            <div class="section-title">
                <i class="fas fa-user-circle"></i> Información del Cliente
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
            <div class="historial-section">
                <h5><i class="fas fa-history"></i> Últimos Pagos</h5>
                <div class="historial-table" id="historialPagos">
                    <p class="text-muted p-3">Cargando...</p>
                </div>
            </div>
        </div>

        <!-- PASO 3: TIPO DE PAGO -->
        <div class="form-section" id="tipoPagoSection">
            <div class="section-title">
                <span class="section-number">2</span>
                <i class="fas fa-credit-card"></i> Tipo de Pago
            </div>

            <div class="tipo-pago-options">
                <label class="tipo-pago-label">
                    <input type="radio" name="tipo_pago" value="abono" checked>
                    <div class="tipo-pago-card">
                        <i class="fas fa-coins"></i>
                        <strong>Abono</strong>
                        <small>Pago Parcial</small>
                    </div>
                </label>

                <label class="tipo-pago-label">
                    <input type="radio" name="tipo_pago" value="completo">
                    <div class="tipo-pago-card">
                        <i class="fas fa-check-circle"></i>
                        <strong>Completo</strong>
                        <small>Pago Total</small>
                    </div>
                </label>

                <label class="tipo-pago-label">
                    <input type="radio" name="tipo_pago" value="mixto">
                    <div class="tipo-pago-card">
                        <i class="fas fa-random"></i>
                        <strong>Mixto</strong>
                        <small>2 Métodos</small>
                    </div>
                </label>
            </div>
        </div>

        <!-- PASO 4: DETALLES DEL PAGO -->
        <div class="form-section" id="datosPagoSection">
            <div class="section-title">
                <span class="section-number">3</span>
                <i class="fas fa-file-invoice-dollar"></i> Detalles del Pago
            </div>

            <!-- ABONO -->
            <div class="pago-section active" id="seccion-abono">
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_abonado_abono">Monto a Abonar ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="monto_abonado_abono"
                                   step="1000" min="1000" placeholder="Ej: 50.000">
                            <small class="text-muted d-block mt-2">Mínimo: $1.000</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_metodo_pago_abono">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_metodo_pago_abono">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="alert-custom alert-info-custom">
                    <strong>✓ Se abonarán $</strong><span id="abono-monto">0</span> <strong>de los $</strong><span id="abono-total">0</span> <strong>pendientes</strong>
                </div>
            </div>

            <!-- COMPLETO -->
            <div class="pago-section" id="seccion-completo">
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_completo">Monto Total</label>
                            <input type="text" class="form-control bg-light" id="monto_completo" disabled>
                            <input type="hidden" id="monto_abonado_completo">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_metodo_pago_completo">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_metodo_pago_completo">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="alert-custom alert-success-custom">
                    <strong>✓ El cliente quedará pagado completamente</strong>
                </div>
            </div>

            <!-- MIXTO -->
            <div class="pago-section" id="seccion-mixto">
                <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> Divide el pago entre 2 métodos diferentes</p>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_metodo1">Monto Método 1 ($)</label>
                            <input type="number" class="form-control monto-mixto" id="monto_metodo1" step="1000" min="0" placeholder="0">
                            <label for="metodo_pago_1" class="mt-3">Seleccionar Método</label>
                            <select class="form-control" id="metodo_pago_1">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_metodo2">Monto Método 2 - Efectivo ($)</label>
                            <input type="number" class="form-control monto-mixto" id="monto_metodo2" step="1000" min="0" placeholder="0">
                            <p class="text-muted mt-3">✓ Método fijo: Efectivo</p>
                        </div>
                    </div>
                </div>
                <div class="alert-custom alert-info-custom">
                    Total: $<strong><span id="total-mixto">0</span></strong> / $<strong><span id="target-mixto">0</span></strong> - <span id="estado-mixto">❌ Incompleto</span>
                </div>
            </div>

            <!-- CAMPOS COMUNES -->
            <div class="common-fields-separator">
                <h6><i class="fas fa-bars"></i> Información Adicional</h6>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referencia_pago">Referencia (Comprobante)</label>
                            <input type="text" class="form-control" id="referencia_pago" placeholder="Ej: Transferencia #123456">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cantidad_cuotas">Cantidad de Cuotas</label>
                            <select class="form-control" id="cantidad_cuotas">
                                <option value="1">1 cuota</option>
                                <option value="2">2 cuotas</option>
                                <option value="3">3 cuotas</option>
                                <option value="4">4 cuotas</option>
                                <option value="6">6 cuotas</option>
                                <option value="12">12 cuotas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" id="monto_abonado" name="monto_abonado" value="0">
        <input type="hidden" id="id_metodo_pago_principal" name="id_metodo_pago_principal" value="">
        <input type="hidden" id="tipo_pago_final" name="tipo_pago" value="abono">

        <!-- Botones -->
        <div class="form-section">
            <div class="btn-actions">
                <a href="{{ route('admin.pagos.index') }}" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                    <i class="fas fa-check"></i> Registrar Pago
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#id_inscripcion').select2({
        language: 'es',
        placeholder: '🔍 Buscar cliente...',
        allowClear: true,
        width: '100%'
    });

    const form = document.getElementById('formPago');
    const selectInscripcion = document.getElementById('id_inscripcion');
    const clienteInfoCard = document.getElementById('clienteInfoSection');
    const tipoPagoSection = document.getElementById('tipoPagoSection');
    const datosPagoSection = document.getElementById('datosPagoSection');
    const btnSubmit = document.getElementById('btnSubmit');

    // Set today's date
    document.getElementById('fecha_pago').valueAsDate = new Date();

    // Select2 event
    $('#id_inscripcion').on('select2:select', loadClient);
    $('#id_inscripcion').on('select2:clear', clearForm);

    function loadClient() {
        const value = selectInscripcion.value;
        if (!value) return;

        try {
            const option = document.querySelector(`#id_inscripcion option[value="${value}"]`);
            const precio = parseFloat(option.getAttribute('data-precio')) || 0;
            const pagos = parseFloat(option.getAttribute('data-pagos')) || 0;
            const cliente = option.getAttribute('data-cliente');
            const membresia = option.getAttribute('data-membresia');
            const vencimiento = option.getAttribute('data-vencimiento');
            const pendiente = precio - pagos;

            // Update display
            document.getElementById('clienteNombre').textContent = cliente;
            document.getElementById('membresiaNombre').textContent = membresia;
            document.getElementById('montoTotal').textContent = '$' + precio.toLocaleString('es-CO');
            document.getElementById('montoAbonado').textContent = '$' + pagos.toLocaleString('es-CO');
            document.getElementById('montoPendiente').textContent = '$' + pendiente.toLocaleString('es-CO');
            document.getElementById('fechaVencimiento').textContent = vencimiento;

            // Show sections
            clienteInfoCard.classList.add('active');
            tipoPagoSection.style.display = 'block';
            datosPagoSection.style.display = 'block';

            // Load history
            loadHistory(value);

            // Update amounts
            document.getElementById('monto_completo').value = '$' + pendiente.toLocaleString('es-CO');
            document.getElementById('monto_abonado_completo').value = pendiente;
            document.getElementById('target-mixto').textContent = pendiente.toLocaleString('es-CO');

            resetPaymentFields();
            updateButton();
        } catch(e) {
            console.error('Error:', e);
        }
    }

    function loadHistory(id) {
        fetch(`/admin/pagos/historial/${id}`)
            .then(r => r.json())
            .then(data => {
                const div = document.getElementById('historialPagos');
                if (!data.pagos || !data.pagos.length) {
                    div.innerHTML = '<p class="text-muted p-3">Sin pagos anteriores</p>';
                    return;
                }
                let html = '<table class="table table-sm mb-0"><thead><tr><th>Fecha</th><th>Monto</th><th>Método</th></tr></thead><tbody>';
                data.pagos.forEach(p => {
                    const fecha = new Date(p.fecha_pago).toLocaleDateString('es-CO');
                    html += `<tr><td>${fecha}</td><td>$${p.monto_abonado.toLocaleString('es-CO')}</td><td>${p.metodoPagoPrincipal?.nombre || 'N/A'}</td></tr>`;
                });
                html += '</tbody></table>';
                div.innerHTML = html;
            });
    }

    function clearForm() {
        clienteInfoCard.classList.remove('active');
        tipoPagoSection.style.display = 'none';
        datosPagoSection.style.display = 'none';
        btnSubmit.disabled = true;
    }

    function resetPaymentFields() {
        document.getElementById('monto_abonado_abono').value = '';
        document.getElementById('id_metodo_pago_abono').value = '';
        document.getElementById('id_metodo_pago_completo').value = '';
        document.getElementById('monto_metodo1').value = '';
        document.getElementById('monto_metodo2').value = '';
        document.getElementById('metodo_pago_1').value = '';
    }

    // Tipo Pago
    document.querySelectorAll('input[name="tipo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.pago-section').forEach(s => s.classList.remove('active'));
            document.getElementById(`seccion-${this.value}`).classList.add('active');
            document.getElementById('tipo_pago_final').value = this.value;
            updateButton();
        });
    });

    // Amount listeners
    ['monto_abonado_abono', 'id_metodo_pago_abono', 'id_metodo_pago_completo', 'monto_metodo1', 'monto_metodo2', 'metodo_pago_1'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input' in el ? 'input' : 'change', updateButton);
    });

    function updateButton() {
        const tipo = document.querySelector('input[name="tipo_pago"]:checked').value;
        const pendiente = parseFloat(document.getElementById('montoPendiente').textContent.replace(/[$,.]/g, '')) || 0;
        let valid = false;

        if (tipo === 'abono') {
            const monto = parseFloat(document.getElementById('monto_abonado_abono').value) || 0;
            const metodo = document.getElementById('id_metodo_pago_abono').value;
            valid = monto > 0 && monto <= pendiente && metodo;
            if (monto > 0) {
                document.getElementById('abono-monto').textContent = monto.toLocaleString('es-CO');
                document.getElementById('abono-total').textContent = pendiente.toLocaleString('es-CO');
            }
        } else if (tipo === 'completo') {
            const metodo = document.getElementById('id_metodo_pago_completo').value;
            valid = !!metodo;
        } else if (tipo === 'mixto') {
            const m1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const m2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            const metodo = document.getElementById('metodo_pago_1').value;
            valid = (m1 + m2) === pendiente && metodo;

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

        btnSubmit.disabled = !valid;
    }

    // Form submit
    form.addEventListener('submit', function(e) {
        const tipo = document.querySelector('input[name="tipo_pago"]:checked').value;
        if (tipo === 'abono') {
            document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_abono').value;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_abono').value;
        } else if (tipo === 'completo') {
            document.getElementById('monto_abonado').value = document.getElementById('monto_abonado_completo').value;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('id_metodo_pago_completo').value;
        } else if (tipo === 'mixto') {
            const m1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
            const m2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
            document.getElementById('monto_abonado').value = m1 + m2;
            document.getElementById('id_metodo_pago_principal').value = document.getElementById('metodo_pago_1').value;
        }
    });
});
</script>
@endsection
