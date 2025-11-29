@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('css')
<style>
    /* ===== WIZARD STEPS ===== */
    .step-indicator { display: none; }
    .step-indicator.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    .steps-nav { 
        display: flex; 
        gap: 1rem; 
        margin-bottom: 2rem; 
        flex-wrap: wrap;
        padding: 1rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 0.75rem;
    }
    
    .step-btn {
        flex: 1;
        min-width: 120px;
        padding: 1rem;
        text-align: center;
        border-radius: 0.75rem;
        background: white;
        border: 2px solid #dee2e6;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .step-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .step-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }

    .step-btn.completed {
        background: linear-gradient(135deg, #28a745 0%, #38ef7d 100%);
        color: white;
        border-color: #28a745;
    }

    .step-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== FORM SECTIONS ===== */
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 1.5rem 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .precio-box {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
        border: 2px solid #667eea;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    /* ===== BUTTONS ===== */
    .buttons-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.75rem;
        border: 1px solid #dee2e6;
    }

    .buttons-group {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
        border-radius: 0.5rem;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
        color: #212529;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border: none;
    }

    /* ===== CARD ===== */
    .card-primary .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* ===== FORM CONTROLS ===== */
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-control.is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        animation: shake 0.4s ease;
    }

    .form-control.is-valid {
        border-color: #28a745 !important;
        background-color: #f0fff4 !important;
    }

    select.is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-8px); }
        40% { transform: translateX(8px); }
        60% { transform: translateX(-8px); }
        80% { transform: translateX(8px); }
    }

    /* ===== SWEETALERT CUSTOM ===== */
    .swal-error-popup { border-radius: 12px !important; }
    .swal-error-title { color: #dc3545 !important; font-weight: 700 !important; }
    .swal-confirm-popup { border-radius: 12px !important; }
    .swal-confirm-title { color: #2c3e50 !important; font-weight: 700 !important; }
    .swal-warning-popup { border-radius: 12px !important; }
    .swal-warning-title { color: #ff6b6b !important; font-weight: 700 !important; }

    .swal2-confirm, .swal2-cancel {
        border-radius: 6px !important;
        padding: 10px 24px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .buttons-container { flex-direction: column; }
        .buttons-group { width: 100%; }
        .buttons-group .btn { flex: 1; justify-content: center; }
    }
</style>
@endsection

@section('content_header')
<div class="row mb-4">
    <div class="col-sm-8">
        <h1 class="m-0"><i class="fas fa-user-plus"></i> Crear Nuevo Cliente</h1>
    </div>
    <div class="col-sm-4 text-right">
        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tasks"></i> Registro de Cliente - 3 Pasos</h3>
    </div>

    <div class="card-body">
        <div class="steps-nav">
            <button type="button" class="step-btn active" id="step1-btn">
                <i class="fas fa-user"></i> Paso 1: Datos
            </button>
            <button type="button" class="step-btn" id="step2-btn" disabled>
                <i class="fas fa-dumbbell"></i> Paso 2: Membresía
            </button>
            <button type="button" class="step-btn" id="step3-btn" disabled>
                <i class="fas fa-credit-card"></i> Paso 3: Pago
            </button>
        </div>

        <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm">
            @csrf
            <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
            <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="solo_cliente">
            <input type="hidden" id="precio-final-oculto" name="precio_final_oculto" value="0">

            <!-- ========== PASO 1: DATOS DEL CLIENTE ========== -->
            <div class="step-indicator active" id="step-1">
                <div class="form-section-title"><i class="fas fa-id-card"></i> Identificación</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="run_pasaporte">RUT/Pasaporte</label>
                        <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                               id="run_pasaporte" name="run_pasaporte" placeholder="Ej: 12.345.678-9" 
                               value="{{ old('run_pasaporte') }}">
                        @error('run_pasaporte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-user"></i> Datos Personales</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombres">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                               id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                        @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido_paterno">Apellido Paterno <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                               id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                        @error('apellido_paterno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" 
                               value="{{ old('apellido_materno') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento') }}">
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-phone"></i> Contacto</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="celular">Celular <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                               id="celular" name="celular" value="{{ old('celular') }}" required>
                        @error('celular')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-heart-pulse"></i> Contacto de Emergencia</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contacto_emergencia">Nombre del Contacto</label>
                        <input type="text" class="form-control" id="contacto_emergencia" name="contacto_emergencia" 
                               value="{{ old('contacto_emergencia') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telefono_emergencia">Teléfono del Contacto</label>
                        <input type="tel" class="form-control" id="telefono_emergencia" name="telefono_emergencia" 
                               value="{{ old('telefono_emergencia') }}">
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Domicilio</div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" 
                               value="{{ old('direccion') }}">
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-sticky-note"></i> Observaciones</div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 2: MEMBRESÍA ========== -->
            <div class="step-indicator" id="step-2">
                <div class="alert alert-info">
                    <strong><i class="fas fa-user"></i> Cliente:</strong> 
                    <span id="cliente-nombre">Ingrese datos en Paso 1</span>
                </div>

                <div class="form-section-title"><i class="fas fa-dumbbell"></i> Seleccionar Membresía</div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="id_membresia">Membresía <span class="text-danger">*</span></label>
                        <select class="form-control @error('id_membresia') is-invalid @enderror" id="id_membresia" name="id_membresia">
                            <option value="">-- Seleccionar Membresía --</option>
                            @foreach($membresias as $membresia)
                                <option value="{{ $membresia->id }}" data-duracion="{{ $membresia->duracion_dias }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                    {{ $membresia->nombre }} ({{ $membresia->duracion_dias }} días)
                                </option>
                            @endforeach
                        </select>
                        @error('id_membresia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                               id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}">
                        @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_termino_display">Fecha de Término</label>
                        <input type="text" class="form-control" id="fecha_termino_display" readonly 
                               style="background-color: #e9ecef; font-weight: bold; color: #28a745;">
                        <small class="text-muted">Se calcula automáticamente</small>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-handshake"></i> Convenio / Descuento</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_convenio">¿Tiene Convenio?</label>
                        <select class="form-control" id="id_convenio" name="id_convenio">
                            <option value="">-- Sin Convenio --</option>
                            @foreach($convenios as $convenio)
                                <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                    {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_motivo_descuento">Motivo del Descuento</label>
                        <select class="form-control" id="id_motivo_descuento" name="id_motivo_descuento">
                            <option value="">-- Sin Motivo --</option>
                            @php $motivosDescuento = \App\Models\MotivoDescuento::where('activo', true)->get(); @endphp
                            @foreach($motivosDescuento as $motivo)
                                <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="descuento_manual">Descuento Manual ($)</label>
                        <input type="number" class="form-control" id="descuento_manual" name="descuento_manual" 
                               min="0" step="1" value="{{ old('descuento_manual', 0) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="observaciones_inscripcion">Observaciones</label>
                        <input type="text" class="form-control" id="observaciones_inscripcion" name="observaciones_inscripcion" 
                               placeholder="Notas sobre la inscripción">
                    </div>
                </div>

                <div class="precio-box" id="precioBox" style="display:none;">
                    <h5><i class="fas fa-tag"></i> Resumen de Precios</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Precio Base:</strong> <span id="precio-normal" class="text-primary">$0</span></p>
                            <p><strong>Convenio:</strong> <span id="precio-convenio" class="text-success">$0</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Descuento Manual:</strong> <span id="desc-manual-display" class="text-danger">-$0</span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4><strong>Precio Final: <span id="precio-total" class="text-primary">$0</span></strong></h4>
                        <p class="text-muted"><strong>Fecha de Término:</strong> <span id="fecha-termino">-</span></p>
                    </div>
                </div>
            </div>

            <!-- ========== PASO 3: PAGO ========== -->
            <div class="step-indicator" id="step-3">
                <div class="card card-info mb-3">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Resumen del Registro</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Cliente:</strong> <span id="resumen-cliente">-</span></p>
                                <p><strong>Membresía:</strong> <span id="resumen-membresia">-</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Convenio:</strong> <span id="resumen-convenio">No</span></p>
                                <p><strong>Motivo Descuento:</strong> <span id="resumen-motivo">-</span></p>
                            </div>
                        </div>
                        <p><strong>Descuento Manual:</strong> <span id="resumen-desc-manual">-$0</span></p>
                        <h5 class="text-primary"><strong>Precio Final: <span id="resumen-precio-final">$0</span></strong></h5>
                    </div>
                </div>

                <div class="form-section-title"><i class="fas fa-credit-card"></i> Información de Pago</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo_pago">Tipo de Pago <span class="text-danger">*</span></label>
                        <select class="form-control" id="tipo_pago" name="tipo_pago">
                            <option value="">-- Seleccionar Tipo --</option>
                            <option value="completo">Pago Completo</option>
                            <option value="parcial">Pago Parcial / Abono</option>
                            <option value="pendiente">Pago Pendiente</option>
                            <option value="mixto">Pago Mixto</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                               value="{{ old('fecha_pago', now()->format('Y-m-d')) }}">
                    </div>
                </div>

                <div id="seccion-monto" style="display:none;">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label id="label-monto">Monto a Abonar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="monto_abonado" name="monto_abonado" min="0" step="1">
                            <small class="text-muted" id="hint-monto"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="id_metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_metodo_pago" name="id_metodo_pago">
                                <option value="">-- Seleccionar --</option>
                                @foreach($metodos_pago as $metodo)
                                    @if(strtolower($metodo->nombre) !== 'mixto')
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="seccion-restante" style="display:none;">
                            <label>Restante por Pagar</label>
                            <div class="form-control bg-light" id="monto-restante-display" style="font-weight:bold; color:#dc3545;">$0</div>
                        </div>
                    </div>
                </div>

                <div id="seccion-mixto" style="display:none;">
                    <div class="card card-warning">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-shuffle"></i> Pago Mixto</h5></div>
                        <div class="card-body">
                            <table class="table table-sm" id="tabla-pagos-mixto">
                                <thead><tr><th>Acción</th><th>Monto</th><th>Método</th><th>Total</th></tr></thead>
                                <tbody></tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-primary" onclick="agregarLineaPago()">
                                <i class="fas fa-plus"></i> Agregar Línea
                            </button>
                            <div class="alert alert-light mt-3" id="alerta-total-mixto">
                                <strong>Total:</strong> <span id="resumen-total-mixto">$0</span>
                            </div>
                            <input type="hidden" id="total-mixto" name="total_mixto" value="0">
                            <input type="hidden" id="detalle-pagos-mixto" name="detalle_pagos_mixto" value="[]">
                        </div>
                    </div>
                </div>

                <div id="info-adicional" style="display:none;">
                    <div class="alert alert-info" id="alert-tipo-pago"></div>
                </div>
            </div>

            <!-- ========== BOTONES DE NAVEGACIÓN Y GUARDADO ========== -->
            <div class="buttons-container">
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-lg" id="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <div class="buttons-group">
                    {{-- Botón Anterior (oculto en paso 1) --}}
                    <button type="button" id="btn-anterior" class="btn btn-outline-secondary btn-lg" style="display:none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    
                    {{-- Botón Siguiente (visible en pasos 1 y 2) --}}
                    <button type="button" id="btn-siguiente" class="btn btn-primary btn-lg">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    {{-- PASO 1: Guardar Solo Cliente --}}
                    <button type="submit" id="btn-guardar-solo-cliente" class="btn btn-info btn-lg" data-flujo="solo_cliente">
                        <i class="fas fa-user-check"></i> Guardar Solo Cliente
                    </button>
                    
                    {{-- PASO 2: Guardar con Membresía (sin pago) --}}
                    <button type="submit" id="btn-guardar-con-membresia" class="btn btn-warning btn-lg" style="display:none;" data-flujo="con_membresia">
                        <i class="fas fa-id-card"></i> Guardar con Membresía
                    </button>
                    
                    {{-- PASO 3: Guardar Completo (cliente + membresía + pago) --}}
                    <button type="submit" id="btn-guardar-completo" class="btn btn-success btn-lg" style="display:none;" data-flujo="completo">
                        <i class="fas fa-check-circle"></i> Guardar Todo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
{{-- Cargar SweetAlert2 por si no está en el layout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Timestamp para evitar caché: {{ now()->timestamp }} --}}
<script>
// ===== VERIFICAR CARGA - VERSION 4 =====
console.log('%c[CREATE.BLADE] Script cargado - VERSION 4 - {{ now()->timestamp }}', 'background: green; color: white; padding: 2px 5px;');
console.log('SweetAlert2 disponible:', typeof Swal !== 'undefined');

// ===== MOSTRAR ERRORES DE LARAVEL =====
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const erroresLaravel = @json($errors->all());
    console.log('Errores de Laravel:', erroresLaravel);
    Swal.fire({
        icon: 'error',
        title: 'Error de Validación',
        html: '<ul style="text-align:left;">' + erroresLaravel.map(e => '<li>' + e + '</li>').join('') + '</ul>',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545'
    });
});
@endif

@if(session('error'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545'
    });
});
@endif

@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745'
    });
});
@endif

// ===== VARIABLES GLOBALES =====
let currentStep = 1;
const totalSteps = 3;
let hayDatosNoGuardados = false;

// ===== HELPER PARA OBTENER VALOR DE CAMPO =====
function getFieldValue(id) {
    const field = document.getElementById(id);
    if (!field) {
        console.warn('Campo no encontrado:', id);
        return '';
    }
    return field.value ? field.value.trim() : '';
}

// ===== MARCAR CAMPO CON ERROR =====
function marcarCampoError(id, tieneError) {
    const field = document.getElementById(id);
    if (field) {
        if (tieneError) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    }
}

// ===== LIMPIAR ERRORES VISUALES =====
function limpiarErroresVisuales() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
}

// ===== VALIDACIÓN =====
function validarPaso(paso) {
    console.log('=== VALIDANDO PASO', paso, '===');
    const errores = [];
    
    // Limpiar errores previos
    limpiarErroresVisuales();
    
    if (paso === 1) {
        // Validar Nombres (requerido)
        const nombres = getFieldValue('nombres');
        if (!nombres) {
            errores.push('Nombres es requerido');
            marcarCampoError('nombres', true);
        } else {
            marcarCampoError('nombres', false);
        }
        
        // Validar Apellido Paterno (requerido)
        const apellido = getFieldValue('apellido_paterno');
        if (!apellido) {
            errores.push('Apellido Paterno es requerido');
            marcarCampoError('apellido_paterno', true);
        } else {
            marcarCampoError('apellido_paterno', false);
        }
        
        // Validar Email (solo requerido, sin validar formato)
        const email = getFieldValue('email');
        if (!email) {
            errores.push('Email es requerido');
            marcarCampoError('email', true);
        } else {
            marcarCampoError('email', false);
        }
        
        // Validar Celular (requerido)
        const celular = getFieldValue('celular');
        if (!celular) {
            errores.push('Celular es requerido');
            marcarCampoError('celular', true);
        } else {
            marcarCampoError('celular', false);
        }
        
        // RUT/Pasaporte es OPCIONAL - no validar
    } 
    else if (paso === 2) {
        // Validar Membresía
        const membresia = getFieldValue('id_membresia');
        console.log('Membresía:', membresia);
        if (!membresia) {
            errores.push('Membresía es requerida');
            marcarCampoError('id_membresia', true);
        } else {
            marcarCampoError('id_membresia', false);
        }
        
        // Validar Fecha Inicio
        const fechaInicio = getFieldValue('fecha_inicio');
        console.log('Fecha Inicio:', fechaInicio);
        if (!fechaInicio) {
            errores.push('Fecha de Inicio es requerida');
            marcarCampoError('fecha_inicio', true);
        } else {
            marcarCampoError('fecha_inicio', false);
        }
    } 
    else if (paso === 3) {
        const tipoPago = getFieldValue('tipo_pago');
        console.log('Tipo Pago:', tipoPago);
        if (!tipoPago) {
            errores.push('Tipo de Pago es requerido');
            marcarCampoError('tipo_pago', true);
        } else {
            marcarCampoError('tipo_pago', false);
        }
        
        const fechaPago = getFieldValue('fecha_pago');
        if (!fechaPago) {
            errores.push('Fecha de Pago es requerida');
            marcarCampoError('fecha_pago', true);
        } else {
            marcarCampoError('fecha_pago', false);
        }
        
        if (tipoPago === 'completo' || tipoPago === 'parcial') {
            if (!getFieldValue('id_metodo_pago')) {
                errores.push('Método de Pago es requerido');
                marcarCampoError('id_metodo_pago', true);
            } else {
                marcarCampoError('id_metodo_pago', false);
            }
        }
        if (tipoPago === 'parcial') {
            const monto = parseInt(getFieldValue('monto_abonado') || '0');
            if (monto <= 0) errores.push('El monto debe ser mayor a $0');
        }
        if (tipoPago === 'mixto') {
            const total = parseInt(getFieldValue('total-mixto') || '0');
            if (total <= 0) errores.push('Agregue al menos un pago en modo mixto');
            
            // Validar que cada línea tenga método seleccionado
            let lineasSinMetodo = 0;
            document.querySelectorAll('#tabla-pagos-mixto tbody tr').forEach((fila, idx) => {
                const monto = parseInt(fila.querySelector('.monto-mixto')?.value || '0');
                const metodo = fila.querySelector('.metodo-mixto')?.value;
                if (monto > 0 && !metodo) {
                    lineasSinMetodo++;
                }
            });
            if (lineasSinMetodo > 0) {
                errores.push(`Seleccione método de pago en ${lineasSinMetodo} línea(s) de pago mixto`);
            }
        }
    }
    
    console.log('Errores encontrados:', errores);
    return { valido: errores.length === 0, errores };
}

function mostrarErrores(errores) {
    console.log('Mostrando errores:', errores);
    
    // Hacer scroll al primer campo con error
    const primerCampoError = document.querySelector('.is-invalid');
    if (primerCampoError) {
        primerCampoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        primerCampoError.focus();
    }
    
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 no disponible, usando alert nativo');
        alert('Por favor complete los siguientes campos:\n\n• ' + errores.join('\n• '));
        return;
    }
    
    const html = errores.map(e => `<li style="padding:4px 0;"><i class="fas fa-exclamation-circle text-danger"></i> ${e}</li>`).join('');
    Swal.fire({
        icon: 'error',
        title: '<i class="fas fa-exclamation-triangle"></i> Campos incompletos',
        html: `<ul style="text-align:left;margin:0;padding-left:20px;list-style:none;">${html}</ul>`,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545',
        customClass: {
            popup: 'animated shake'
        }
    });
}

// ===== NAVEGACIÓN =====
function goToStep(step, skipValidation = false) {
    console.log('goToStep llamado:', step, 'skipValidation:', skipValidation, 'currentStep:', currentStep);
    
    if (step < 1 || step > totalSteps) {
        console.log('Paso fuera de rango');
        return;
    }
    
    if (!skipValidation && step > currentStep) {
        console.log('Validando paso:', currentStep);
        const result = validarPaso(currentStep);
        console.log('Resultado validación:', result);
        if (!result.valido) {
            mostrarErrores(result.errores);
            return;
        }
    }
    
    document.querySelectorAll('.step-indicator').forEach(el => el.classList.remove('active'));
    const stepElement = document.getElementById(`step-${step}`);
    console.log('Elemento step:', stepElement);
    if (stepElement) stepElement.classList.add('active');
    currentStep = step;
    
    updateButtons();
    updateStepButtons();
    
    if (step === 2) actualizarNombreCliente();
    if (step === 3) {
        actualizarPrecio();
        setTimeout(actualizarResumenPaso3, 100);
    }
    
    console.log('Navegación completada a paso:', step);
}

function nextStep() { 
    console.log('nextStep llamado');
    goToStep(currentStep + 1, false); 
}
function previousStep() { 
    console.log('previousStep llamado');
    goToStep(currentStep - 1, true); 
}

function updateButtons() {
    const btns = {
        anterior: document.getElementById('btn-anterior'),
        siguiente: document.getElementById('btn-siguiente'),
        soloCliente: document.getElementById('btn-guardar-solo-cliente'),
        conMembresia: document.getElementById('btn-guardar-con-membresia'),
        completo: document.getElementById('btn-guardar-completo')
    };
    const flujo = document.getElementById('flujo_cliente');
    
    // Ocultar todos primero
    Object.values(btns).forEach(b => { if(b) b.style.display = 'none'; });
    
    if (currentStep === 1) {
        // Paso 1: Datos del Cliente
        // Mostrar: Siguiente + Guardar Solo Cliente
        if(btns.siguiente) btns.siguiente.style.display = 'inline-block';
        if(btns.soloCliente) btns.soloCliente.style.display = 'inline-block';
        if(flujo) flujo.value = 'solo_cliente';
    } else if (currentStep === 2) {
        // Paso 2: Membresía
        // Mostrar: Anterior + Siguiente + Guardar con Membresía
        if(btns.anterior) btns.anterior.style.display = 'inline-block';
        if(btns.siguiente) btns.siguiente.style.display = 'inline-block';
        if(btns.conMembresia) btns.conMembresia.style.display = 'inline-block';
        if(flujo) flujo.value = 'con_membresia';
    } else if (currentStep === 3) {
        // Paso 3: Pago
        // Mostrar: Anterior + Guardar Todo
        if(btns.anterior) btns.anterior.style.display = 'inline-block';
        if(btns.completo) btns.completo.style.display = 'inline-block';
        if(flujo) flujo.value = 'completo';
    }
}

function updateStepButtons() {
    for (let i = 1; i <= totalSteps; i++) {
        const btn = document.getElementById(`step${i}-btn`);
        btn.classList.remove('active', 'completed');
        if (i < currentStep) { btn.classList.add('completed'); btn.disabled = false; }
        else if (i === currentStep) { btn.classList.add('active'); btn.disabled = false; }
        else { btn.disabled = true; }
    }
}

// ===== PRECIOS =====
function actualizarPrecio() {
    const membresiaId = document.getElementById('id_membresia')?.value;
    const convenioId = document.getElementById('id_convenio')?.value || '';
    
    if (!membresiaId) {
        document.getElementById('precioBox').style.display = 'none';
        return;
    }
    
    let url = `/api/precio-membresia/${membresiaId}`;
    if (convenioId) url += `?convenio=${convenioId}`;
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            
            const precioBase = parseInt(data.precio_base) || 0;
            const precioConvenio = parseInt(data.precio_final) || precioBase;
            const duracionDias = parseInt(data.duracion_dias) || 30;
            
            document.getElementById('precioBox').style.display = 'block';
            document.getElementById('precio-normal').textContent = '$' + precioBase.toLocaleString('es-CL');
            document.getElementById('precio-convenio').textContent = '$' + precioConvenio.toLocaleString('es-CL');
            
            const fechaInicio = document.getElementById('fecha_inicio')?.value;
            if (fechaInicio) {
                const termino = new Date(fechaInicio);
                termino.setDate(termino.getDate() + duracionDias);
                const fechaFormateada = termino.toLocaleDateString('es-CL');
                document.getElementById('fecha-termino').textContent = fechaFormateada;
                // También actualizar el nuevo campo fecha_termino_display
                const displayField = document.getElementById('fecha_termino_display');
                if (displayField) displayField.value = fechaFormateada;
            }
            
            actualizarPrecioFinal(precioConvenio);
        })
        .catch(console.error);
}

function actualizarPrecioFinal(precioConvenio = null) {
    if (precioConvenio === null) {
        const text = document.getElementById('precio-convenio')?.textContent || '$0';
        precioConvenio = parseInt(text.replace(/\D/g, '')) || 0;
    }
    
    const descuento = parseInt(document.getElementById('descuento_manual')?.value || '0');
    const total = Math.max(0, precioConvenio - descuento);
    
    document.getElementById('desc-manual-display').textContent = '-$' + descuento.toLocaleString('es-CL');
    document.getElementById('precio-total').textContent = '$' + total.toLocaleString('es-CL');
    document.getElementById('precio-final-oculto').value = total;
    
    // Sincronizar con resumen en Paso 3
    const resumenPrecio = document.getElementById('resumen-precio-final');
    if (resumenPrecio) resumenPrecio.textContent = '$' + total.toLocaleString('es-CL');
}

// ===== RESUMEN =====
function actualizarNombreCliente() {
    const nombres = document.getElementById('nombres')?.value || '';
    const apellido = document.getElementById('apellido_paterno')?.value || '';
    document.getElementById('cliente-nombre').textContent = (nombres + ' ' + apellido).trim() || 'Ingrese datos en Paso 1';
}

function actualizarResumenPaso3() {
    const nombres = document.getElementById('nombres')?.value || '';
    const apellido = document.getElementById('apellido_paterno')?.value || '';
    const membresiaEl = document.getElementById('id_membresia');
    const convenioEl = document.getElementById('id_convenio');
    const motivoEl = document.getElementById('id_motivo_descuento');
    const descuento = parseInt(document.getElementById('descuento_manual')?.value || '0');
    const precioFinal = document.getElementById('precio-total')?.textContent || '$0';
    
    document.getElementById('resumen-cliente').textContent = (nombres + ' ' + apellido).trim() || '-';
    document.getElementById('resumen-membresia').textContent = membresiaEl?.options[membresiaEl.selectedIndex]?.text || '-';
    document.getElementById('resumen-convenio').textContent = convenioEl?.value ? convenioEl.options[convenioEl.selectedIndex]?.text : 'No';
    document.getElementById('resumen-motivo').textContent = motivoEl?.value ? motivoEl.options[motivoEl.selectedIndex]?.text : '-';
    document.getElementById('resumen-desc-manual').textContent = '-$' + descuento.toLocaleString('es-CL');
    document.getElementById('resumen-precio-final').textContent = precioFinal;
}

// ===== TIPO DE PAGO =====
function actualizarTipoPago() {
    const tipo = document.getElementById('tipo_pago').value;
    const precioFinal = parseInt(document.getElementById('precio-final-oculto')?.value || '0');
    
    document.getElementById('seccion-monto').style.display = 'none';
    document.getElementById('seccion-mixto').style.display = 'none';
    document.getElementById('info-adicional').style.display = 'none';
    document.getElementById('seccion-restante').style.display = 'none';
    
    const monto = document.getElementById('monto_abonado');
    const label = document.getElementById('label-monto');
    const hint = document.getElementById('hint-monto');
    const alert = document.getElementById('alert-tipo-pago');
    
    if (tipo === 'completo') {
        document.getElementById('seccion-monto').style.display = 'block';
        monto.value = precioFinal;
        monto.readOnly = true;
        label.textContent = 'Monto Total';
        hint.textContent = 'Pago completo';
        document.getElementById('info-adicional').style.display = 'block';
        alert.className = 'alert alert-success';
        alert.innerHTML = '<i class="fas fa-check-circle"></i> Pago completo: $' + precioFinal.toLocaleString('es-CL');
    } 
    else if (tipo === 'parcial') {
        document.getElementById('seccion-monto').style.display = 'block';
        document.getElementById('seccion-restante').style.display = 'block';
        monto.value = '';
        monto.readOnly = false;
        label.textContent = 'Monto a Abonar';
        hint.textContent = 'Total a pagar: $' + precioFinal.toLocaleString('es-CL');
        document.getElementById('monto-restante-display').textContent = '$' + precioFinal.toLocaleString('es-CL');
        document.getElementById('info-adicional').style.display = 'block';
        alert.className = 'alert alert-info';
        alert.innerHTML = '<i class="fas fa-info-circle"></i> Pago parcial - Ingrese el monto a abonar';
    } 
    else if (tipo === 'pendiente') {
        document.getElementById('info-adicional').style.display = 'block';
        alert.className = 'alert alert-warning';
        alert.innerHTML = '<i class="fas fa-clock"></i> Sin pago - Total pendiente: $' + precioFinal.toLocaleString('es-CL');
    } 
    else if (tipo === 'mixto') {
        document.getElementById('seccion-mixto').style.display = 'block';
        const tbody = document.querySelector('#tabla-pagos-mixto tbody');
        if (tbody.children.length === 0) agregarLineaPago();
        document.getElementById('info-adicional').style.display = 'block';
        alert.className = 'alert alert-info';
        alert.innerHTML = '<i class="fas fa-shuffle"></i> Pago mixto - Total: $' + precioFinal.toLocaleString('es-CL');
    }
}

// ===== PAGO MIXTO =====
function agregarLineaPago() {
    const tbody = document.querySelector('#tabla-pagos-mixto tbody');
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td><button type="button" class="btn btn-sm btn-danger" onclick="eliminarLineaPago(this)"><i class="fas fa-trash"></i></button></td>
        <td><input type="number" class="form-control form-control-sm monto-mixto" min="0" placeholder="0"></td>
        <td><select class="form-control form-control-sm metodo-mixto">
            <option value="">-- Método --</option>
            @foreach($metodos_pago as $metodo)
            @if(strtolower($metodo->nombre) !== 'mixto')
            <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
            @endif
            @endforeach
        </select></td>
        <td class="text-right"><span class="monto-display">$0</span></td>
    `;
    tbody.appendChild(fila);
    console.log('[agregarLineaPago] Nueva línea agregada');
}

function eliminarLineaPago(btn) {
    btn.closest('tr').remove();
    setTimeout(recalcularTotalMixto, 10);
}

// La función calcularTotalMixto ahora es un alias definido al final del script

// ===== ENVÍO DEL FORMULARIO =====
// Variable para saber qué botón se presionó
let botonPresionado = null;

// La lógica de submit se configura dentro de DOMContentLoaded más abajo

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CARGADO - INICIALIZANDO ===');
    
    // Inicializar en paso 1
    goToStep(1, true);
    
    // ===== EVENT LISTENERS PARA NAVEGACIÓN =====
    // Botón Siguiente
    const btnSiguiente = document.getElementById('btn-siguiente');
    if (btnSiguiente) {
        console.log('Botón Siguiente encontrado, agregando listener');
        btnSiguiente.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Click en Siguiente');
            nextStep();
        });
    } else {
        console.error('Botón Siguiente NO encontrado');
    }
    
    // Botón Anterior
    const btnAnterior = document.getElementById('btn-anterior');
    if (btnAnterior) {
        btnAnterior.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Click en Anterior');
            previousStep();
        });
    }
    
    // Botones de pasos
    document.getElementById('step1-btn')?.addEventListener('click', () => goToStep(1, true));
    document.getElementById('step2-btn')?.addEventListener('click', () => goToStep(2, false));
    document.getElementById('step3-btn')?.addEventListener('click', () => goToStep(3, false));
    
    // ===== EVENT LISTENERS PARA FORMULARIO =====
    document.getElementById('id_membresia')?.addEventListener('change', actualizarPrecio);
    document.getElementById('id_convenio')?.addEventListener('change', actualizarPrecio);
    document.getElementById('fecha_inicio')?.addEventListener('change', actualizarPrecio);
    document.getElementById('descuento_manual')?.addEventListener('input', () => actualizarPrecioFinal());
    document.getElementById('nombres')?.addEventListener('input', actualizarNombreCliente);
    document.getElementById('apellido_paterno')?.addEventListener('input', actualizarNombreCliente);
    document.getElementById('tipo_pago')?.addEventListener('change', actualizarTipoPago);
    
    // Calcular restante en pago parcial y validar máximo
    document.getElementById('monto_abonado')?.addEventListener('input', function() {
        const precioFinal = parseInt(document.getElementById('precio-final-oculto')?.value || '0');
        let abonado = parseInt(this.value) || 0;
        
        // No permitir monto mayor al precio final
        if (abonado > precioFinal) {
            abonado = precioFinal;
            this.value = precioFinal;
        }
        
        const restante = Math.max(0, precioFinal - abonado);
        document.getElementById('monto-restante-display').textContent = '$' + restante.toLocaleString('es-CL');
        
        // Actualizar alerta
        const alert = document.getElementById('alert-tipo-pago');
        if (abonado > 0 && abonado < precioFinal) {
            alert.className = 'alert alert-info';
            alert.innerHTML = `<i class="fas fa-info-circle"></i> Abono: $${abonado.toLocaleString('es-CL')} | Restante: $${restante.toLocaleString('es-CL')}`;
        } else if (abonado >= precioFinal) {
            alert.className = 'alert alert-success';
            alert.innerHTML = '<i class="fas fa-check-circle"></i> Monto cubre el total';
        }
    });
    
    // Detectar cambios
    document.getElementById('clienteForm')?.addEventListener('input', () => hayDatosNoGuardados = true);
    
    // Advertencia al salir
    window.addEventListener('beforeunload', e => {
        if (hayDatosNoGuardados) { e.preventDefault(); e.returnValue = ''; }
    });
    
    document.getElementById('btn-cancelar')?.addEventListener('click', function(e) {
        if (hayDatosNoGuardados) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '¿Salir sin guardar?',
                text: 'Los datos se perderán',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#dc3545'
            }).then(result => {
                if (result.isConfirmed) window.location.href = e.target.href;
            });
        }
    });
    
    // ===== DELEGACIÓN DE EVENTOS PARA PAGO MIXTO =====
    // Usar setTimeout para asegurar que el valor del input esté actualizado
    const tablaMixto = document.getElementById('tabla-pagos-mixto');
    if (tablaMixto) {
        tablaMixto.addEventListener('input', function(e) {
            if (e.target.classList.contains('monto-mixto')) {
                // Usar setTimeout para esperar que el valor se actualice completamente
                setTimeout(recalcularTotalMixto, 50);
            }
        });
        
        tablaMixto.addEventListener('change', function(e) {
            if (e.target.classList.contains('metodo-mixto') || e.target.classList.contains('monto-mixto')) {
                setTimeout(recalcularTotalMixto, 50);
            }
        });
        console.log('[INIT] Delegación de eventos configurada para tabla-pagos-mixto');
    }
    
    // ===== CONFIGURAR ENVÍO DEL FORMULARIO =====
    // Detectar qué botón submit se presionó
    document.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            botonPresionado = this;
            console.log('[SUBMIT] Botón presionado:', this.id, 'flujo:', this.dataset.flujo);
            const flujo = this.dataset.flujo;
            if (flujo) {
                document.getElementById('flujo_cliente').value = flujo;
            }
        });
    });
    
    const clienteForm = document.getElementById('clienteForm');
    if (clienteForm) {
        clienteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[SUBMIT] Formulario enviado');
            
            const flujo = document.getElementById('flujo_cliente').value;
            console.log('[SUBMIT] Flujo:', flujo);
            
            // Validar según el paso actual y el flujo
            let pasoAValidar = currentStep;
            if (flujo === 'solo_cliente') pasoAValidar = 1;
            else if (flujo === 'con_membresia') pasoAValidar = 2;
            
            const result = validarPaso(pasoAValidar);
            if (!result.valido) {
                mostrarErrores(result.errores);
                return;
            }
            
            // Si es con_membresia, también validar paso 1
            if (flujo === 'con_membresia') {
                const result1 = validarPaso(1);
                if (!result1.valido) {
                    mostrarErrores(result1.errores);
                    return;
                }
            }
            
            // Si es completo, validar todos los pasos
            if (flujo === 'completo') {
                for (let i = 1; i <= 3; i++) {
                    const r = validarPaso(i);
                    if (!r.valido) {
                        mostrarErrores(r.errores);
                        return;
                    }
                }
            }
            
            let titulo = 'Confirmar';
            let mensaje = '';
            let icono = 'question';
            
            if (flujo === 'solo_cliente') {
                titulo = '¿Guardar solo cliente?';
                mensaje = 'Se creará el cliente sin membresía ni pago.';
                icono = 'info';
            } else if (flujo === 'con_membresia') {
                titulo = '¿Guardar cliente con membresía?';
                mensaje = 'Se creará el cliente con membresía. El pago quedará pendiente.';
                icono = 'warning';
            } else {
                titulo = '¿Confirmar registro completo?';
                mensaje = 'Se registrará cliente, membresía y pago.';
                icono = 'success';
            }
            
            Swal.fire({
                icon: icono,
                title: titulo,
                text: mensaje,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({ 
                        title: 'Guardando...', 
                        html: '<i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Por favor espere...</small>',
                        allowOutsideClick: false, 
                        showConfirmButton: false
                    });
                    hayDatosNoGuardados = false;
                    // Enviar formulario nativamente
                    clienteForm.submit();
                }
            });
        });
        console.log('[INIT] Listener de submit configurado');
    }
    
    console.log('=== INICIALIZACIÓN COMPLETA ===');
});

// ===== FUNCIÓN RECALCULAR TOTAL MIXTO - VERSIÓN FINAL =====
function recalcularTotalMixto() {
    const precioFinal = parseInt(document.getElementById('precio-final-oculto')?.value || '0');
    let total = 0;
    const detalles = [];
    
    const filas = document.querySelectorAll('#tabla-pagos-mixto tbody tr');
    
    // Primero calculamos el total sin modificar
    let totalPrevio = 0;
    filas.forEach((fila) => {
        const montoInput = fila.querySelector('.monto-mixto');
        if (montoInput && montoInput.value !== '') {
            totalPrevio += parseInt(montoInput.value, 10) || 0;
        }
    });
    
    filas.forEach((fila, idx) => {
        const montoInput = fila.querySelector('.monto-mixto');
        const metodoSelect = fila.querySelector('.metodo-mixto');
        const montoDisplay = fila.querySelector('.monto-display');
        
        // Leer el valor actual del input
        let monto = 0;
        if (montoInput && montoInput.value !== '') {
            monto = parseInt(montoInput.value, 10) || 0;
        }
        
        // Validar que el total no exceda el precio final
        const otrosMontos = totalPrevio - monto;
        const maxPermitido = precioFinal - otrosMontos;
        
        if (monto > maxPermitido && maxPermitido >= 0) {
            monto = maxPermitido;
            montoInput.value = monto;
            // Mostrar aviso
            Swal.fire({
                icon: 'warning',
                title: 'Monto ajustado',
                text: `El monto máximo permitido es $${maxPermitido.toLocaleString('es-CL')} para no exceder el precio final.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
        
        const metodo = metodoSelect ? metodoSelect.value : '';
        
        console.log(`[MIXTO] Fila ${idx+1}: valor="${montoInput?.value}" => ${monto}`);
        
        // Actualizar display
        if (montoDisplay) {
            montoDisplay.textContent = '$' + monto.toLocaleString('es-CL');
        }
        
        total += monto;
        if (monto > 0) {
            detalles.push({ monto, metodo });
        }
    });
    
    console.log(`[MIXTO] TOTAL CALCULADO: ${total}`);
    
    // Actualizar campos
    const resumenTotal = document.getElementById('resumen-total-mixto');
    const inputTotal = document.getElementById('total-mixto');
    const inputDetalle = document.getElementById('detalle-pagos-mixto');
    const alerta = document.getElementById('alerta-total-mixto');
    
    if (resumenTotal) resumenTotal.textContent = '$' + total.toLocaleString('es-CL');
    if (inputTotal) inputTotal.value = total;
    if (inputDetalle) inputDetalle.value = JSON.stringify(detalles);
    
    if (alerta) {
        if (total === 0) {
            alerta.className = 'alert alert-light';
            alerta.innerHTML = '<strong>Total:</strong> $0';
        } else if (total < precioFinal) {
            alerta.className = 'alert alert-info';
            alerta.innerHTML = `<strong>Abonado:</strong> $${total.toLocaleString('es-CL')} | <strong>Pendiente:</strong> $${(precioFinal - total).toLocaleString('es-CL')}`;
        } else if (total === precioFinal) {
            alerta.className = 'alert alert-success';
            alerta.innerHTML = '<i class="fas fa-check"></i> <strong>Cubierto:</strong> $' + total.toLocaleString('es-CL');
        } else {
            alerta.className = 'alert alert-danger';
            alerta.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> Excede el precio final';
        }
    }
}

// Alias para compatibilidad con código existente
function calcularTotalMixto() {
    recalcularTotalMixto();
}

function calcularTotalMixtoV3() {
    recalcularTotalMixto();
}
</script>
@endpush
