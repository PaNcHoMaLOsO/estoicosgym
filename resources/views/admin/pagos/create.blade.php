@extends('adminlte::page')

@section('title', 'Nuevo Pago - EstóicosGym')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        --success: #00bf8e;
        --success-dark: #00a67d;
        --warning: #f0a500;
        --info: #4361ee;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-800: #343a40;
    }

    /* HERO HEADER */
    .hero-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 30px 35px;
        border-radius: 16px;
        margin-bottom: 25px;
        box-shadow: 0 15px 40px rgba(26, 26, 46, 0.4);
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 250px;
        height: 250px;
        background: var(--accent);
        border-radius: 50%;
        opacity: 0.1;
    }
    .hero-header-content { position: relative; z-index: 1; }
    .hero-title { 
        font-size: 1.8em; 
        font-weight: 800; 
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    .hero-subtitle { 
        font-size: 1em; 
        opacity: 0.9;
        font-weight: 400;
    }
    .btn-back {
        background: transparent;
        color: white;
        border: 2px solid rgba(255,255,255,0.5);
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.1);
        border-color: white;
        color: white;
    }

    /* FORM CARD */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        margin-bottom: 25px;
        border: none;
        overflow: hidden;
    }
    .form-card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        padding: 18px 25px;
        font-weight: 700;
        font-size: 1.05em;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .form-card-header i { color: var(--accent); }
    .form-card-body {
        padding: 25px;
    }

    /* FORM ELEMENTS */
    .form-label {
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 8px;
        font-size: 0.9em;
    }
    .form-control, .form-select {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95em;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--info);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--accent);
    }
    .invalid-feedback {
        color: var(--accent);
        font-weight: 500;
    }

    /* TIPO PAGO OPTIONS */
    .tipo-pago-container {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    .tipo-pago-option {
        flex: 1;
        min-width: 200px;
        position: relative;
    }
    .tipo-pago-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    .tipo-pago-option label {
        display: block;
        padding: 20px;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    .tipo-pago-option label:hover {
        border-color: var(--info);
        transform: translateY(-2px);
    }
    .tipo-pago-option input:checked + label {
        border-color: var(--success);
        background: rgba(0, 191, 142, 0.08);
        box-shadow: 0 5px 20px rgba(0, 191, 142, 0.15);
    }
    .tipo-pago-icon {
        font-size: 2em;
        margin-bottom: 10px;
        color: var(--info);
    }
    .tipo-pago-option input:checked + label .tipo-pago-icon {
        color: var(--success);
    }
    .tipo-pago-title {
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 5px;
    }
    .tipo-pago-desc {
        font-size: 0.85em;
        color: var(--gray-600);
    }

    /* PRECIO BOX */
    .precio-box {
        background: linear-gradient(135deg, rgba(0, 191, 142, 0.05) 0%, rgba(67, 97, 238, 0.05) 100%);
        border: 2px solid var(--success);
        border-radius: 16px;
        padding: 20px;
        margin-top: 15px;
    }
    .precio-box-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .precio-box-title i { color: var(--success); }
    .precio-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed var(--gray-200);
    }
    .precio-row:last-child {
        border-bottom: none;
        padding-top: 15px;
        margin-top: 10px;
        border-top: 2px solid var(--success);
    }
    .precio-label {
        color: var(--gray-600);
        font-weight: 500;
    }
    .precio-valor {
        font-weight: 700;
        color: var(--gray-800);
    }
    .precio-total {
        font-size: 1.5em;
        color: var(--success);
    }

    /* SECTION HIDDEN */
    .section-hidden { display: none; }
    .section-visible { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* MIXTO SECTION */
    .mixto-section {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 20px;
        margin-top: 15px;
    }
    .mixto-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .mixto-title i { color: var(--warning); }

    /* BUTTONS */
    .btn-custom {
        border-radius: 10px;
        padding: 12px 28px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-custom-primary {
        background: var(--primary);
        color: white;
        border: none;
    }
    .btn-custom-primary:hover {
        background: var(--primary-light);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26, 26, 46, 0.3);
    }
    .btn-custom-success {
        background: var(--success);
        color: white;
        border: none;
    }
    .btn-custom-success:hover {
        background: var(--success-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 191, 142, 0.3);
    }
    .btn-custom-outline {
        background: transparent;
        border: 2px solid var(--gray-200);
        color: var(--gray-600);
    }
    .btn-custom-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* INSCRIPCION PREVIEW */
    .inscripcion-preview {
        background: var(--gray-100);
        border-radius: 12px;
        padding: 20px;
        margin-top: 15px;
        display: none;
    }
    .inscripcion-preview.visible { display: block; }
    .preview-title {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 12px;
        font-size: 0.95em;
    }
    .preview-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .preview-item {
        flex: 1;
        min-width: 120px;
    }
    .preview-label {
        font-size: 0.75em;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }
    .preview-value {
        font-weight: 700;
        color: var(--gray-800);
    }
    .preview-value.success { color: var(--success); }
    .preview-value.danger { color: var(--accent); }

    /* ACTIONS BAR */
    .actions-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* SELECT2 STYLES */
    .select2-container--bootstrap-5 .select2-selection {
        border: 2px solid var(--gray-200) !important;
        border-radius: 10px !important;
        min-height: 48px !important;
        padding: 6px 10px !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 6px !important;
        line-height: 34px !important;
    }
    .select2-container--bootstrap-5 .select2-selection:focus,
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: var(--info) !important;
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1) !important;
    }

    /* ALERT */
    .alert-custom {
        border-radius: 12px;
        padding: 16px 20px;
        border: none;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .alert-custom.danger {
        background: rgba(233, 69, 96, 0.12);
        color: var(--accent);
    }
    .alert-custom.success {
        background: rgba(0, 191, 142, 0.12);
        color: var(--success);
    }
</style>
@stop

@section('content')
<div class="container-fluid py-4">
    
    {{-- HERO HEADER --}}
    <div class="hero-header">
        <div class="hero-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="hero-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Registrar Nuevo Pago
                    </h1>
                    <p class="hero-subtitle mb-0">
                        Selecciona la inscripción y el tipo de pago a registrar
                    </p>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver a Pagos
                </a>
            </div>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if ($errors->any())
        <div class="alert-custom danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Error:</strong>
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('admin.pagos.store') }}" method="POST" id="formPago">
        @csrf
        <input type="hidden" name="form_submit_token" value="{{ uniqid() }}">

        <div class="row">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                
                {{-- INSCRIPCIÓN --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-file-contract"></i>
                        Seleccionar Inscripción
                    </div>
                    <div class="form-card-body">
                        <div class="mb-3">
                            <label for="id_inscripcion" class="form-label">
                                Inscripción con Saldo Pendiente <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-inscripcion @error('id_inscripcion') is-invalid @enderror" 
                                    name="id_inscripcion" id="id_inscripcion" required>
                                <option value="">Seleccione una inscripción...</option>
                                @foreach($inscripciones as $insc)
                                    @php
                                        $total = $insc->precio_final ?? $insc->precio_base;
                                        $pagado = $insc->pagos()->sum('monto_abonado');
                                        $pendiente = $total - $pagado;
                                    @endphp
                                    <option value="{{ $insc->id }}" 
                                            data-total="{{ $total }}"
                                            data-pagado="{{ $pagado }}"
                                            data-pendiente="{{ $pendiente }}"
                                            data-cliente="{{ $insc->cliente->nombres ?? '' }} {{ $insc->cliente->apellido_paterno ?? '' }}"
                                            data-membresia="{{ $insc->membresia->nombre ?? '' }}"
                                            {{ (old('id_inscripcion') == $insc->id || $inscripcion_id_preselect == $insc->id) ? 'selected' : '' }}>
                                        {{ $insc->cliente->nombres ?? 'Sin nombre' }} {{ $insc->cliente->apellido_paterno ?? '' }} 
                                        - {{ $insc->membresia->nombre ?? 'Sin membresía' }} 
                                        (Pendiente: ${{ number_format($pendiente, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_inscripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PREVIEW INSCRIPCION --}}
                        <div class="inscripcion-preview" id="inscripcionPreview">
                            <div class="preview-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Información de la Inscripción
                            </div>
                            <div class="preview-info">
                                <div class="preview-item">
                                    <div class="preview-label">Cliente</div>
                                    <div class="preview-value" id="previewCliente">-</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Membresía</div>
                                    <div class="preview-value" id="previewMembresia">-</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Total</div>
                                    <div class="preview-value" id="previewTotal">-</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Pagado</div>
                                    <div class="preview-value success" id="previewPagado">-</div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-label">Pendiente</div>
                                    <div class="preview-value danger" id="previewPendiente">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TIPO DE PAGO --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-hand-holding-usd"></i>
                        Tipo de Pago
                    </div>
                    <div class="form-card-body">
                        <div class="tipo-pago-container">
                            <div class="tipo-pago-option">
                                <input type="radio" name="tipo_pago" id="tipo_abono" value="abono" 
                                       {{ old('tipo_pago', 'abono') == 'abono' ? 'checked' : '' }}>
                                <label for="tipo_abono">
                                    <div class="tipo-pago-icon"><i class="fas fa-coins"></i></div>
                                    <div class="tipo-pago-title">Abono Parcial</div>
                                    <div class="tipo-pago-desc">Pagar una parte del monto</div>
                                </label>
                            </div>
                            <div class="tipo-pago-option">
                                <input type="radio" name="tipo_pago" id="tipo_completo" value="completo"
                                       {{ old('tipo_pago') == 'completo' ? 'checked' : '' }}>
                                <label for="tipo_completo">
                                    <div class="tipo-pago-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="tipo-pago-title">Pago Completo</div>
                                    <div class="tipo-pago-desc">Pagar el saldo pendiente</div>
                                </label>
                            </div>
                            <div class="tipo-pago-option">
                                <input type="radio" name="tipo_pago" id="tipo_mixto" value="mixto"
                                       {{ old('tipo_pago') == 'mixto' ? 'checked' : '' }}>
                                <label for="tipo_mixto">
                                    <div class="tipo-pago-icon"><i class="fas fa-random"></i></div>
                                    <div class="tipo-pago-title">Pago Mixto</div>
                                    <div class="tipo-pago-desc">Dividir entre 2 métodos</div>
                                </label>
                            </div>
                        </div>

                        {{-- MONTO ABONO --}}
                        <div id="seccionAbono" class="mt-4 section-visible">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="monto_abonado" class="form-label">
                                        Monto a Abonar <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="1" min="0" 
                                               class="form-control @error('monto_abonado') is-invalid @enderror" 
                                               name="monto_abonado" id="monto_abonado"
                                               value="{{ old('monto_abonado') }}"
                                               placeholder="0">
                                    </div>
                                    @error('monto_abonado')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="id_metodo_pago" class="form-label">
                                        Método de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('id_metodo_pago') is-invalid @enderror" 
                                            name="id_metodo_pago" id="id_metodo_pago" required>
                                        <option value="">Seleccione método...</option>
                                        @foreach($metodos_pago as $metodo)
                                            <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                                {{ $metodo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_metodo_pago')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- PAGO MIXTO --}}
                        <div id="seccionMixto" class="section-hidden">
                            <div class="mixto-section">
                                <div class="mixto-title">
                                    <i class="fas fa-random"></i>
                                    Dividir Pago en Dos Métodos
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Método 1</label>
                                        <select class="form-select" name="id_metodo_pago1" id="id_metodo_pago1">
                                            <option value="">Seleccione...</option>
                                            @foreach($metodos_pago as $metodo)
                                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Monto Método 1</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="1" min="0" class="form-control" 
                                                   name="monto_metodo1" id="monto_metodo1" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Método 2</label>
                                        <select class="form-select" name="id_metodo_pago2" id="id_metodo_pago2">
                                            <option value="">Seleccione...</option>
                                            @foreach($metodos_pago as $metodo)
                                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Monto Método 2</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="1" min="0" class="form-control" 
                                                   name="monto_metodo2" id="monto_metodo2" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0" style="border-radius: 10px;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="mixtoTotal">La suma de ambos montos debe igualar el saldo pendiente</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DETALLES DEL PAGO --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-calendar-alt"></i>
                        Detalles del Pago
                    </div>
                    <div class="form-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago" class="form-label">
                                    Fecha de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                       name="fecha_pago" id="fecha_pago"
                                       value="{{ old('fecha_pago', date('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('fecha_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="referencia_pago" class="form-label">
                                    Referencia / Comprobante
                                </label>
                                <input type="text" class="form-control @error('referencia_pago') is-invalid @enderror" 
                                       name="referencia_pago" id="referencia_pago"
                                       value="{{ old('referencia_pago') }}"
                                       placeholder="Ej: N° Transferencia, Recibo...">
                                @error('referencia_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="observaciones" class="form-label">
                                    Observaciones
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          name="observaciones" id="observaciones" rows="3"
                                          placeholder="Notas adicionales sobre el pago...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN - RESUMEN --}}
            <div class="col-lg-4">
                <div class="form-card" style="position: sticky; top: 20px;">
                    <div class="form-card-header">
                        <i class="fas fa-calculator"></i>
                        Resumen del Pago
                    </div>
                    <div class="form-card-body">
                        <div class="precio-box">
                            <div class="precio-box-title">
                                <i class="fas fa-receipt"></i>
                                Detalle de Montos
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Monto Total:</span>
                                <span class="precio-valor" id="resumenTotal">$0</span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Ya Pagado:</span>
                                <span class="precio-valor" style="color: var(--success);" id="resumenPagado">$0</span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Saldo Pendiente:</span>
                                <span class="precio-valor" style="color: var(--accent);" id="resumenPendiente">$0</span>
                            </div>
                            <div class="precio-row">
                                <span class="precio-label">Este Pago:</span>
                                <span class="precio-valor precio-total" id="resumenEstePago">$0</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-custom btn-custom-success w-100" id="btnRegistrar">
                                <i class="fas fa-save"></i>
                                Registrar Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-inscripcion').select2({
        theme: 'bootstrap-5',
        language: 'es',
        placeholder: 'Buscar inscripción por cliente...',
        allowClear: true,
        width: '100%'
    });

    // Variables globales
    let montoPendiente = 0;
    let montoTotal = 0;
    let montoPagado = 0;

    // Inscripción change
    $('#id_inscripcion').on('change', function() {
        const selected = $(this).find(':selected');
        const preview = $('#inscripcionPreview');
        
        if (selected.val()) {
            montoTotal = parseFloat(selected.data('total')) || 0;
            montoPagado = parseFloat(selected.data('pagado')) || 0;
            montoPendiente = parseFloat(selected.data('pendiente')) || 0;

            // Update preview
            $('#previewCliente').text(selected.data('cliente') || '-');
            $('#previewMembresia').text(selected.data('membresia') || '-');
            $('#previewTotal').text('$' + formatNumber(montoTotal));
            $('#previewPagado').text('$' + formatNumber(montoPagado));
            $('#previewPendiente').text('$' + formatNumber(montoPendiente));
            preview.addClass('visible');

            // Update resumen
            $('#resumenTotal').text('$' + formatNumber(montoTotal));
            $('#resumenPagado').text('$' + formatNumber(montoPagado));
            $('#resumenPendiente').text('$' + formatNumber(montoPendiente));

            // Set max for monto_abonado
            $('#monto_abonado').attr('max', montoPendiente);
            
            updateEstePago();
        } else {
            preview.removeClass('visible');
            montoPendiente = 0;
            montoTotal = 0;
            montoPagado = 0;
            resetResumen();
        }
    });

    // Trigger change if preselected
    if ($('#id_inscripcion').val()) {
        $('#id_inscripcion').trigger('change');
    }

    // Tipo pago change
    $('input[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        
        $('#seccionAbono').removeClass('section-visible').addClass('section-hidden');
        $('#seccionMixto').removeClass('section-visible').addClass('section-hidden');
        
        if (tipo === 'abono') {
            $('#seccionAbono').removeClass('section-hidden').addClass('section-visible');
            $('#id_metodo_pago').prop('required', true);
        } else if (tipo === 'completo') {
            $('#seccionAbono').removeClass('section-hidden').addClass('section-visible');
            $('#id_metodo_pago').prop('required', true);
            $('#monto_abonado').val('');
        } else if (tipo === 'mixto') {
            $('#seccionMixto').removeClass('section-hidden').addClass('section-visible');
            $('#id_metodo_pago').prop('required', false);
        }
        
        updateEstePago();
    });

    // Monto abonado change
    $('#monto_abonado').on('input', function() {
        updateEstePago();
    });

    // Mixto montos change
    $('#monto_metodo1, #monto_metodo2').on('input', function() {
        updateEstePago();
    });

    // Validar que no se seleccionen los mismos métodos de pago en mixto
    $('#id_metodo_pago1, #id_metodo_pago2').on('change', function() {
        const metodo1 = $('#id_metodo_pago1').val();
        const metodo2 = $('#id_metodo_pago2').val();
        
        if (metodo1 && metodo2 && metodo1 === metodo2) {
            Swal.fire({
                icon: 'warning',
                title: 'Métodos iguales',
                text: 'No puedes seleccionar el mismo método de pago en ambos campos. Por favor, elige métodos diferentes.',
                confirmButtonColor: '#e94560'
            });
            
            // Limpiar el segundo select
            $(this).val('');
            $(this).addClass('is-invalid');
        } else {
            $('#id_metodo_pago1, #id_metodo_pago2').removeClass('is-invalid');
        }
    });

    // Función para formatear números al estilo chileno (miles con punto)
    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateEstePago() {
        const tipo = $('input[name="tipo_pago"]:checked').val();
        let estePago = 0;

        if (tipo === 'abono') {
            estePago = parseFloat($('#monto_abonado').val()) || 0;
        } else if (tipo === 'completo') {
            estePago = montoPendiente;
        } else if (tipo === 'mixto') {
            const m1 = parseFloat($('#monto_metodo1').val()) || 0;
            const m2 = parseFloat($('#monto_metodo2').val()) || 0;
            estePago = m1 + m2;
            $('#mixtoTotal').html('Total: <strong>$' + formatNumber(estePago) + '</strong> de <strong>$' + formatNumber(montoPendiente) + '</strong> pendiente');
        }

        $('#resumenEstePago').text('$' + formatNumber(estePago));
    }

    function resetResumen() {
        $('#resumenTotal').text('$0');
        $('#resumenPagado').text('$0');
        $('#resumenPendiente').text('$0');
        $('#resumenEstePago').text('$0');
        $('#estadoResultante').remove();
    }

    // Initial tipo pago check
    $('input[name="tipo_pago"]:checked').trigger('change');

    // ========================================
    // VALIDACIONES Y SWEETALERTS
    // ========================================
    
    // Validar monto en tiempo real
    $('#monto_abonado').on('input', function() {
        const monto = parseFloat($(this).val()) || 0;
        
        if (monto > montoPendiente) {
            $(this).addClass('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'Monto excede el pendiente',
                text: `El monto máximo a abonar es $${formatNumber(montoPendiente)}`,
                confirmButtonColor: '#e94560'
            });
            $(this).val(montoPendiente);
        } else if (monto > 0 && monto < 1000) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
        
        // Sugerir pago completo si el monto iguala el pendiente
        if (monto === montoPendiente && $('input[name="tipo_pago"]:checked').val() === 'abono') {
            Swal.fire({
                icon: 'info',
                title: '¡Pago completo detectado!',
                text: 'El monto ingresado cubre el total pendiente. ¿Deseas cambiar a "Pago Completo"?',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'No, mantener abono',
                confirmButtonColor: '#00bf8e',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#tipo_completo').prop('checked', true).trigger('change');
                }
            });
        }
        
        updateEstePago();
    });

    // Validación del formulario antes de enviar
    $('#formPago').on('submit', function(e) {
        e.preventDefault();
        
        const tipo = $('input[name="tipo_pago"]:checked').val();
        const inscripcion = $('#id_inscripcion').val();
        const metodoPago = $('#id_metodo_pago option:selected').text();
        
        // Validaciones básicas
        if (!inscripcion) {
            Swal.fire({
                icon: 'error',
                title: 'Inscripción requerida',
                text: 'Debes seleccionar una inscripción',
                confirmButtonColor: '#e94560'
            });
            return;
        }
        
        let montoAPagar = 0;
        if (tipo === 'abono') {
            montoAPagar = parseFloat($('#monto_abonado').val()) || 0;
            if (montoAPagar < 1000) {
                Swal.fire({
                    icon: 'error',
                    title: 'Monto inválido',
                    text: 'El monto mínimo a abonar es $1.000',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
        } else if (tipo === 'completo') {
            montoAPagar = montoPendiente;
        } else if (tipo === 'mixto') {
            const m1 = parseFloat($('#monto_metodo1').val()) || 0;
            const m2 = parseFloat($('#monto_metodo2').val()) || 0;
            const metodo1 = $('#id_metodo_pago1').val();
            const metodo2 = $('#id_metodo_pago2').val();
            montoAPagar = m1 + m2;
            
            // Validar que se seleccionen ambos métodos
            if (!metodo1 || !metodo2) {
                Swal.fire({
                    icon: 'error',
                    title: 'Métodos requeridos',
                    text: 'Debes seleccionar ambos métodos de pago',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
            
            // Validar que los métodos sean diferentes
            if (metodo1 === metodo2) {
                Swal.fire({
                    icon: 'error',
                    title: 'Métodos iguales',
                    text: 'Los métodos de pago deben ser diferentes',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
            
            // Validar que los montos sean mayores a 0
            if (m1 <= 0 || m2 <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Montos inválidos',
                    text: 'Ambos montos deben ser mayores a $0',
                    confirmButtonColor: '#e94560'
                });
                return;
            }
            
            if (montoAPagar !== montoPendiente) {
                Swal.fire({
                    icon: 'error',
                    title: 'Suma incorrecta',
                    html: `La suma de los montos (<strong>$${formatNumber(montoAPagar)}</strong>) debe ser exactamente <strong>$${formatNumber(montoPendiente)}</strong>`,
                    confirmButtonColor: '#e94560'
                });
                return;
            }
        }
        
        if (!$('#id_metodo_pago').val() && tipo !== 'mixto') {
            Swal.fire({
                icon: 'error',
                title: 'Método de pago requerido',
                text: 'Debes seleccionar un método de pago',
                confirmButtonColor: '#e94560'
            });
            return;
        }
        
        // Determinar si quedará pagado completamente
        const nuevoSaldo = montoPendiente - montoAPagar;
        const estadoFinal = nuevoSaldo <= 0 ? 'PAGADO ✅' : `PARCIAL (Quedará $${formatNumber(nuevoSaldo)} pendiente)`;
        const estadoColor = nuevoSaldo <= 0 ? '#00bf8e' : '#f0a500';
        
        // Mostrar confirmación con SweetAlert
        Swal.fire({
            title: '¿Confirmar registro de pago?',
            html: `
                <div style="text-align: left; padding: 10px 0;">
                    <p><strong>📋 Cliente:</strong> ${$('#previewCliente').text()}</p>
                    <p><strong>🏋️ Membresía:</strong> ${$('#previewMembresia').text()}</p>
                    <hr>
                    <p><strong>💵 Monto a registrar:</strong> <span style="color: #00bf8e; font-size: 1.2em;">$${formatNumber(montoAPagar)}</span></p>
                    <p><strong>💳 Método de pago:</strong> ${metodoPago || 'Mixto'}</p>
                    <hr>
                    <p><strong>📊 Estado resultante:</strong> <span style="color: ${estadoColor}; font-weight: bold;">${estadoFinal}</span></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check me-1"></i> Sí, registrar pago',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancelar',
            confirmButtonColor: '#00bf8e',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Registrando pago...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar formulario
                this.submit();
            }
        });
    });

    // Mostrar indicador de estado resultante en resumen
    function updateEstadoResultante() {
        const tipo = $('input[name="tipo_pago"]:checked').val();
        let montoAPagar = 0;
        
        if (tipo === 'abono') {
            montoAPagar = parseFloat($('#monto_abonado').val()) || 0;
        } else if (tipo === 'completo') {
            montoAPagar = montoPendiente;
        } else if (tipo === 'mixto') {
            montoAPagar = (parseFloat($('#monto_metodo1').val()) || 0) + (parseFloat($('#monto_metodo2').val()) || 0);
        }
        
        const nuevoSaldo = montoPendiente - montoAPagar;
        
        // Remover indicador anterior
        $('#estadoResultante').remove();
        
        if (montoAPagar > 0) {
            let html = '';
            if (nuevoSaldo <= 0) {
                html = `<div id="estadoResultante" class="alert mt-3" style="background: rgba(0, 191, 142, 0.15); border: 1px solid #00bf8e; border-radius: 10px; color: #00bf8e;">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>¡Quedará completamente pagado!</strong>
                </div>`;
            } else {
                html = `<div id="estadoResultante" class="alert mt-3" style="background: rgba(240, 165, 0, 0.15); border: 1px solid #f0a500; border-radius: 10px; color: #f0a500;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Quedará un saldo de <strong>$${formatNumber(nuevoSaldo)}</strong>
                </div>`;
            }
            $('.precio-box').after(html);
        }
    }

    // Actualizar estado resultante cuando cambie el monto
    $('#monto_abonado, #monto_metodo1, #monto_metodo2').on('input', updateEstadoResultante);
    $('input[name="tipo_pago"]').on('change', updateEstadoResultante);
});
</script>
@stop
